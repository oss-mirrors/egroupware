<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');

//!! WfRuntime
//! A class to handle instances at runtime
/*!
*
*/
class WfRuntime extends Base 
{
  
  // processes config values cached for this object life duration
  // init is done at first use for each process
  var $processesConfig= Array();
  
  //instance and activity are the two most important object of the runtime
  var $activity = null;
  var $instance = null;
  var $instance_id = 0;
  var $activity_id = 0;
  //process object is used, for example, to retrieve the compiled code
  var $process = null;
  //security Object
  var $security = null;
  //boolean, wether or not we are in a transaction
  var $transaction_in_progress = false; 
  //boolean, wether or not we are in debug mode 
  var $debug=false;
  
  /*!
  * Constructor takes a PEAR::Db object
  */
  function WfRuntime(&$db) 
  {
    $this->child_name = 'WfRuntime';
    parent::Base($db);
    require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
    require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'Process.php');
    require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'Instance.php');
    require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'WfSecurity.php');
    
    //first the activity is not set
    $this->activity = null;
    $this->instance =& new Instance($this->db);
    $this->process =& new Process($this->db);
    $this->security =& new WfSecurity($this->db); 
  }

  /*!
  * Collect errors from all linked objects which could have been used by this object
  * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
  * for example if you had a $this->process_manager created in the constructor you shoudl call
  * $this->error[] = $this->process_manager->get_error(false, $debug);
  * @param $debug is false by default, if true debug messages can be added to 'normal' messages
  */
  function collect_errors($debug=false)
  {
    parent::collect_errors($debug);
    if (isset($this->instance)) $this->error[] = $this->instance->get_error(false, $debug);
    if (isset($this->process)) $this->error[] = $this->process->get_error(false, $debug);
    if (isset($this->security)) $this->error[] = $this->security->get_error(false, $debug);
    if (isset($this->activity)) $this->error[] = $this->activity->get_error(false, $debug);
  }

  /*!
  * Call this function to end-up dying and giving a last message
  * @param $last_message is your last sentence
  * @param $include_errors is a false boolean by default, if true we'll include error messages
  * @param $debug is false by default, if true you will obtain more messages, if false you could obtain theses
  * messages as well if this object has been placed in debug maode with setDebug(true)
  * recorded by this runtme object.
  * @return nothing, it die!
  */
  function fail($last_message, $include_errors = false, $debug=false)
  {
    $the_end = '';
    if ($this->debug) $debug = true;
    if ($include_errors)
    {
      $the_end = $this->get_error(false, $debug).'<br />';
    }
    $the_end .= $last_message;
    if ($this->transaction_in_progress)
    {
      //we had a transaction actually, we mark a fail, this will force Rollback
      $this->db->FailTrans();
      $this->db->CompleteTrans();
    }
    //this will make the session die
    galaxia_show_error($the_end);
  }

  /*!
  * retrieve the activity of the right type from a baseActivity Object
  * @param $activity_id is the activity_id you want
  * @param $with_roles will load the roles links on the object
  * @param $with_agents will load the agents links on the object
  * @return an Activity Object of the right type or false
  */
  function loadActivity($activity_id, $with_roles= true,$with_agents=false)
  {
    if ( (empty($activity_id)) || (!($activity_id)) )
    {
      $this->fail(tra('No activity indicated'),true);
    }
    $base_activity =& new BaseActivity($this->db);
    $this->activity =& $base_activity->getActivity($activity_id, $with_roles, $with_agents);
    $this->activity_id = $activity_id;
    $this->process->getProcess($this->activity->getProcessId());
    $this->error[] =  $base_activity->get_error();
    $this->error[] =  $this->process->get_error();
    return $this->activity;
  }
  
  /*!
  * retrieve the instance which could be an empty object
  * @param $instanceId is the instance id
  * @return an Instance Object which can be empty
  */
  function loadInstance($instanceId)
  {
    $this->instance_id = $instanceId;
    $this->instance->getInstance($instanceId);
    if (($this->instance->getInstanceId()==0) && (!($this->activity->getType()=='standalone')))
    {
      $this->fail(tra('no instance avaible'), true);
    }
    return $this->instance;
  }
  
  /*!
  * Perform necessary security checks at runtime
  * @param $user is the user id
  * @param return true if ok, false if the user has no runtime access
  * instance and activity are unsetted in case of false check
  */
  function checkUserRun($user)
  {
    if ($this->activity->getType()=='view')
    {
      //on view activities  the run action is a special action
      $action = 'viewrun';
    }
    else
    {
      $action = 'run';
    }
    //this will test the action rights and lock the necessary rows in tables in case of 'run'
    $result = $this->security->checkUserAction($this->activity_id,$this->instance_id,$action);
    $this->error[] =  $this->security->get_error();
    if ($result)
    {
      return true;
    }
    else
    {
      unset($this->activity);
      unset($this->instance);
      return false;
    }
  }
  
  /*!
  * This part of the runtime object will load the compiled activity code and include it
  * this is in fact an 'execution' of the activity code
  */
/*  function executeCompiledActivity()
  {
    $source = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'compiled' . SEP . $this->activity->getNormalizedName(). '.php';
    require_once ($source);
  }
*/
  /*!
  *
  */
  function setDebug($debug_mode=true)
  {
    $this->debug = $debug_mode;
  }
  
  /*!
  *
  */
  function StartRun()
  {
    $this->transaction_in_progress =true;
    $this->db->StartTrans();
  }
  
  /*!
  *
  */
  function EndStartRun()
  {
    if ($this->transaction_in_progress) 
    {
      $this->db->CompleteTrans();
      $this->transaction_in_progress =false;
    }
  }
  
  /*!
  * For interactive activities this function
  * will set the current user
  */
  function setActivityUser()
  {
    if(isset($GLOBALS['user']) && !empty($this->instance->instanceId) && !empty($this->activity_id)) 
    {
      if ($this->activity->isInteractive())
      {// activity is interactive and we want the form, we'll try to grab the ticket on this instance-activity
        if (!$this->instance->setActivityUser($this->activity_id,$GLOBALS['user']))
        {
           $this->fail(lang("You do not have the right to run this activity anymore, maybe a concurrent access problem, refresh your datas.", true));
        }
      }// if activity is not interactive there's no need to grab the token
    }
    else
    {
      $this->fail(lang("We cannot run this activity, maybe this instance or this activity do not exists anymore.", true));
    }    
  }
  
}


?>
