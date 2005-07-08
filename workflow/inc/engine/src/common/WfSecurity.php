<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');

//!! WfSecurity
//! A class to handle most security checks in the engine
/*!
  This class is used to ...
*/
class WfSecurity extends Base {
  
  // error messages
  var $error= Array();
  
  // processes config values cached for this object life duration
  // init is done at first use for each process
  var $processesConfig= Array();
      
  /*!
    Constructor takes a PEAR::Db object to be used
    to manipulate activities in the database.
  */
  function WfSecurity($db) {
    if(!$db) {
      die("Invalid db object passed to WfSecurity constructor");  
    }
    $this->db = $db;  
  }
  
  //! return errors recorded by this object
  /*!
  You should always call this function after operations on an WfSecurity object to test if everything seems ok
  if you give a true parameter the result will be send as an array of errors or an empty array.
  Else, if you do not give any parameter or give a false parameter you will obtain a single string which can be empty
  or will contain error messages with <br /> html tags.
  */
  function get_error($as_array=false) 
  {
    if (as_array)
    {
      return $this->error;
    }
    else
    {
      $result_str = implode('<br />',$this->error);
      return $result_str;
    }
  }

  //! Checks if a user has a access to an activity,
  /*!
  To do so it checks if the user is in the users having the roles associated with the activity
  or if he is in the groups having roles associated with the activity
  */
  function checkUserAccess($user, $activity_id) 
  {
    //group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
        
    $result= $this->getOne("select count(*) from ".GALAXIA_TABLE_PREFIX."activity_roles gar, 
        ".GALAXIA_TABLE_PREFIX."user_roles gur, 
        ".GALAXIA_TABLE_PREFIX."roles gr 
        where gar.wf_role_id=gr.wf_role_id 
        and gur.wf_role_id=gr.wf_role_id
        and gar.wf_activity_id=? 
        and ( (gur.wf_user=? and gur.wf_account_type='u') 
              or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g') 
            )"
        ,array($activity_id, $user));
    if ($result >= 1)
    {
      //echo "<br>Access granted for ".$user;
      return true;
    }
    else
    {
      $this->errors[]= tra('Access denied for user %1 on activity %2', $user, $activity_id);
      return false;
    }
  }

  //! Return avaible actions for a given user on a given activity and a given instance assuming he already have access to it.
  /*!
  To be able to decide this function needs the all parameters, use the GUI object equivalent function if you want less parameters.
  user id, instance_id, activity_id, process id, activity type,  activity interactivity (y/n), activity routage (y/n), 
  activity status, instance owner, instance status and  finally the current user of this activity.
  This function will as well load process configuration which could have some impact on the rights. 
  Theses config data will be cached during the existence of this GUI object.
  The result is an array of this form:
  array('action name' => 'action description')
  'actions names' are: 'grab', 'release', 'run', 'send', 'view', 'exception', 'resume', 'monitor' and 'admin'
  Some config values can change theses rules but basically here they are:
  * 'grab'	: be the user of this activity. User has access to it and instance status is ok.
  * 'release'	: let * be the user of this activity. Must be the actual user or the owner of the instance.
  * 'run'	: run an associated form. This activity is interactive, user has access, instance status is ok.
  * 'send'	: send this instance, activity was non-autorouted and he has access and status is ok.
  * 'view'	: view the instance, activity ok, always avaible except for start or standalone act.
  * 'abort'	: abort an instance, ok when we are the user
  * 'exception' : set the instance status to exception, need to be the user 
  * 'resume'	: back to running when instance status was exception, need to be the user
  'actions description' are translated explanations like 'release access to this activity'
  WARNING: this is a snapshot, the engine give you a snaphsots of the rights a user have on an instance-activity
  at a given time, this is not meaning theses rights will still be there when the user launch the action.
  You should absolutely use the GUI Object to execute theses actions (except monitor and admin) and they could be rejected.
  WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via a 
  GUI object theses access rights are allready checked.
  */
  function getUserActions($user, $instanceId, $activityId, $pId, $actType, $actInteractive, $actAutorouted, $actStatus, $instanceOwner, $instanceStatus, $currentUser) 
  {
    $result= array();//returned array
    $stopflow=false;//true when the instance is in a state where the flow musn't advance
                    //ie: we can't send or run it
    $deathflow=false;//true when the instance is in a state where the flow will never advance anymore
                    //ie: we can't send, run, grab, release, exception or resume it
    $associated_instance=true;//false when no instance is associated with the activity
                    // ie: we cannot send, grab, release, exception, resume or view the instance but we can run
                    // it covers standalone activities and start activities not completed
    $_run  = false;
    $_send = false;
    $_grab = false;
    $_release = false;
    $_abort = false;
    $_view = false;
    $_resume = false;
    $_exception = false;
    $_monitor = false;
    $_admin = false;

    //check if we already have the config values for this processId
    if (!(isset($this->processesConfig[$pId])))
    {
      //define conf values we need
      $arrayConf=array(
        'ownership_give_abort_right'		=>1,
        'ownership_give_exception_right'	=>1,
        'ownership_give_release_right'		=>1,
        'role_give_abort_right'           	=>0,
        'role_give_release_right'		=>0,
        'role_give_exception_right'		=>0,
      );
      //check theses values for this process and store the result for this object life duration
      $myProcess =& new Process($this->db);
      $myProcess->getProcess($pId);
      $this->processesConfig[$pId] =& $myProcess->getConfigValues($arrayConf);
      unset($myProcess);
    }
    
    // check the instance status
    // 'completed' => no action except 'view' or 'abort'
    // 'aborted' =>  no action except 'view'
    // 'active' => ok first add 'exception'    
    // 'exception' => first add 'resume', no 'run' or 'send' after
    $_view = true;
    if ($instanceStatus == 'aborted')
    {
      $deathflow=true;
    }
    else
    {
      // first check ABORT
      if ( ($user==$currentUser) ||
           (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_abort_right'])) ||
           ($this->processesConfig[$pId]['role_give_abort_right']))
      {// we are the assigned user 
       //OR we are the owner and it gives rights
       //OR we have the role and it gives rights
       $_abort =true;
      }
      // now handle resume and exception but before detect completed instances
      if ($instanceStatus == 'completed')
      {
        $deathflow=true;
      }
      else
      {
        if ($instanceStatus == 'exception')
        {
          $stopflow = true;
          if ( ($user==$currentUser) ||
               (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_exception_right'])) ||
               ($this->processesConfig[$pId]['role_give_exception_right']))
          {// we are the assigned user OR we are the owner and it gives rights
            $_resume = true;
          }
        }
        elseif ($instanceStatus == 'active')
        {
          //handle rules about ownership
          if ( ($user==$currentUser) ||
              (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_exception_right'])) ||
              ($this->processesConfig[$pId]['role_give_exception_right']))
          {// we are the assigned user OR we are the owner and it gives rights
            $_exception = true;
          }
        }
      }
    }
  
    //now we check the activity
    // start (only uncompleted) and standalone activities have no instance associated.
    // If we are not in a 'stop' or 'death' flow we can check interactivity
    // interactive -> run
    // not interactive -> send (except for 'standalone')
    // if we are not in a 'death flow' we can add grab and release actions
    if ( ($actType=='standalone') || (($actType=='start') && (!($actStatus=='completed'))) )
    {
      $associated_instance=false;
      // there's no instance to view in fact
      $_view = false;
    }
    if (($actInteractive=='y') && (!($deathflow)))
    {
      if ($associated_instance)
      {
          if ($currentUser=='*')
          {
            $_grab = true;
          }
          else
          {
            if ( ($user==$currentUser) ||
               (($user==$instanceOwner)&&($this->processesConfig[$pId]['ownership_give_release_right'])) ||
               ($this->processesConfig[$pId]['role_give_release_right']))
            {// we are the assigned user 
             //OR we are the owner and it gives rights
             //OR we have the role and it gives rights
              $_release = true;
            }
          }
      }
      if (($actStatus=='running') && !($stopflow) && !($deathflow))
      {
        if (($currentUser=='*') || ($currentUser==$user))
        {
          $_run = true;
        }
      }
    }
    //for non autorouted activities we'll have to send, useless on standalone but usefull for start
    //activities which can be sended if completed and of course for all other activities
    if ($actAutorouted=='n')
    {
      if ($associated_instance)
      {
        if (($actStatus=='completed') && !($stopflow) && !($deathflow))
        {
          $_send = true;
        }
      }
    }
    
    //build final array
    if ($_run) $result['run']=tra('Execute this activity');
    if ($_send) $result['send']=tra('Send this instance to the next activity');
    if ($_grab) $result['grab']=tra('Assign me this activity');
    if ($_release) $result['release']=tra('Release access to this activity');
    if ($_abort) $result['abort']=tra('Abort this instance');
    if ($_view) $result['view']=tra('View this instance');
    if ($_resume) $result['resume']=tra('Resume this exception instance');
    if ($_exception) $result['exception']=tra('Exception this instance');
    if ($_monitor) $result['monitor']=tra('Monitor this instance');
    if ($_admin) $result['admin']=tra('Admin this instance');
    
    return $result;
  }

  
}


?>
