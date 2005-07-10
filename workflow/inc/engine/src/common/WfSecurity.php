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
  * @public
  * You should always call this function after operations on an WfSecurity object to test if everything seems ok
  * if you give a true parameter the result will be send as an array of errors or an empty array.
  * Else, if you do not give any parameter or give a false parameter you will obtain a single string which can be empty
  * or will contain error messages with <br /> html tags.
  * errors are erased after you've been calling this function.
  */
  function get_error($as_array=false) 
  {
    if ($as_array)
    {
      $result = $this->error;
      $this->error = Array();
      return $result;
    }
    else
    {
      $result_str = implode('<br />',$this->error);
      $this->error = Array();
      return $result_str;
    }
  }

  //! load the config values for a given process
  /*!
  * config values for a given process are cached while this WfSecurity object stay alive
  * @param $pId is the process id
  * @private
  */
  function loadConfigValues($pId)
  {
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
      $this->processesConfig[$pId] = $myProcess->getConfigValues($arrayConf);
      unset($myProcess);
    }
  }



  //! Checks if a user has a access to an activity,
  /*!
  * To do so it checks if the user is in the users having the roles associated with the activity
  * or if he is in the groups having roles associated with the activity
  * @public
  * @param $user is the user id
  * @param $activityId is the activity id
  * @return true if access is granted false in other case. Errors are stored in the object.
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
      $this->error[]= tra('Access denied for user %1 on activity %2, no role', $user, $activity_id);
      return false;
    }
  }

  //! Return true if a given user is authorized for a given action on a given activity/instance
  /*!
  * @public
  * @param $user is the user id
  * @param $activityId is the activity id, can be 0
  * @param $instanceId is the instanceId, can be 0
  * @param $action is a string containing ONE action asked, it must be one of 'grab', 'release', exception', 'resume', 'abort', 'run', 'send', 'view', 'monitor'
  * @return true if action access is granted false in other case. Errors are stored in the object.
  */
  function checkUserAction($user,$activityId, $instanceId,$action)
  {
    //Warning: 
    //start and standalone activities have no instances associated
    //aborted and completed instances have no activities associated
                
    $this->loadConfigValues($pId);
    
    //1 - load data -----------------------------------------------------------------
    $_no_activity=false;
    $_no_instance=false;
    //retrieve some activity datas and process data
    if ($activityId==0)
    {
      $_no_activity = true;
    }
    else
    {
      $query = "select ga.wf_activity_id, ga.wf_type, ga.wf_is_interactive, ga.wf_is_autorouted, 
              gia.wf_instance_id, gia.wf_user, gia.wf_status, gp.wf_name as wf_procname, gp.wf_is_active
              from ".GALAXIA_TABLE_PREFIX."instance_activities gia
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gia.wf_activity_id = ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."processes gp ON gp.wf_p_id=ga.wf_p_id
                where ga.wf_activity_id = ?";
      $resactivity = $this->getOne($query, array($activityId));
      if (!(isset($resactivity)))
      {
        $_no_activity = true;
      }
    }
    //retrieve some instance and process data (need process data here as well if there is no activity)
    if ($instanceId==0)
    {
      $_no_instance = true;
    }
    else
    {
      $query = "select gi.wf_instance_id, gi.wf_user, gi.wf_owner, gi.wf_status, gp.wf_name as wf_procname, gp.wf_is_active,
              from ".GALAXIA_TABLE_PREFIX."instances gi
              INNER JOIN ".GALAXIA_TABLE_PREFIX."processes gp ON gp.wf_p_id=gi.wf_p_id
              where gi.wf_instance_id=?";
      $resinstance = $this->getOne($query,array($instanceId));
      if (!(isset($resinstance)))
      {
        $_no_instance = true;
      }
    }
    if ($_no_instance && $_no_activity)
    {
      $this->error[] = tra('no action avaible beacuse no activity and no instance are given!');
      return false;
    }

    //2 - decide which tests must be done ------------------------------------------------
    //init tests
    $_check_active_process = false; // is the process is valid?
    $_check_instance = false; //have we got an instance?
    $_check_instance_status = array(); //use to test some status between 'active','exception','aborted','completed'
    $_fail_on_exception = false; //no comment
    $_check_activity = false; //have we got an activity?
    //is there a realtionship between instance and activity? this one can be decided already
    $_check_instance_activity =  !(($_no_instance) || ($_no_activity));
    $_bypass_user_role_if_owner = false; //if our user is the owner we ignore user tests
    $_bypass_user_on_non_interactive = false; //if activty is not interactive we do not perform user tests
    $_bypass_user_if_admin = false; //is our user a special rights user?
    $_check_is_user = false; //is the actual_user our user?
    $_check_is_not_star = false; //is the actual <>*?
    $_check_is_in_role = false; //is our user in associated roles (done only if check_is_user is false)?
    $_check_is_in_role_if_star = false; //perform the role test only if actual user is '*'

    //first have a look at the action asked
    switch($action)
    {
      case 'view':
        //process can be inactive
        //no activity needed
        //we just need an existing instance
        // TODO: add conf setting to refuse view if no (at least read-only-)role
        $_check_instance = true;
        $_bypass_user_if_admin	= true;
        break;
      case 'grab': 
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, role, never owner actually
        // TODO: add conf setting to give grab access to owner (that mean run access as well maybe)
        // current user MUST be '*' or $user, '*' is handled by the $_check_is_in_role
        $_check_active_process	= true;
        $_check_activity	= true;
        $_check_instance	= true;
        $_check_is_user		= true;
        $_bypass_user_if_admin	= true;
        $_check_is_in_role	= true;
        break;
      case 'release' :
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, maybe role, maybe owner,
        // current must not be '*'
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_is_not_star 	= true;
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_release_right']) $_check_is_in_role 		= true;
        if ($this->processesConfig[$pId]['ownership_give_release_right']) $_bypass_user_role_if_owner 	= true;
        break;
      case 'exception':
        // we need an activity 'in_flow' ie: not start or standalone that means we need an instance
        // we need an instance not completed or aborted that means we need an activity
        // authorization are given to currentuser, maybe role, maybe owner,
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_instance_status = array('active');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_exception_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_exception_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'resume':
        // like exception but inversed activity status
        $_check_active_process	= true;
        $_check_activity        = true;
        $_check_instance        = true;
        $_check_instance_status = array('exception');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_exception_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_exception_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'abort':
        // process can be inactive
        // we do not need an activity
        // we need an instance
        // authorization are given to currentuser, maybe role, maybe owner,
        // TODO: add conf setting to refuse abort by user
        $_check_instance        = true;
        $_check_instance_status = array('active','exception','completed');
        $_bypass_user_if_admin	= true;
        $_check_is_user		= true;
        if ($this->processesConfig[$pId]['role_give_abort_right']) $_check_is_in_role                 = true;
        if ($this->processesConfig[$pId]['ownership_give_abort_right']) $_bypass_user_role_if_owner   = true;
        break;
      case 'run':
        // the hell door:
        // all activities can be runned, even without instance, even if non interactive
        // if we have one we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // for interactive activities (except start and standalone), instance user need to be the actual user
        // run is ok if user is in role and actual user is '*', no rights for owner actually
        // no user bypassing on admin user, admin must grab (release if needed) the instance before
        $_check_active_process		= true;
        $_check_activity	        = true;
        $_fail_on_exception		= true;
        $_bypass_user_on_non_interactive = true;
        $_check_is_user			= true;
        $_check_is_in_role_if_star	= true;
        break;
      case 'send':
        // we need an instance not completed or aborted that means we need an activity
        // but if we have an instance it musn't be in 'exception' as well
        // authorization are given to currentuser, maybe role, no rights for owner actually
        // run is ok if user is in role and actual user is '*'
        $_check_active_process          = true;
        $_check_activity                = true;
        $_fail_on_exception             = true;
        $_bypass_user_if_admin		= true;
        $_check_is_user                 = true;
        $_check_is_in_role_if_star	= true;
        break;
      case 'monitor':
        // process can be invalid
        // no test on activity
        // we need an instance, at least
        // authorization is given to special user rights
        $_check_instance 	= true;
        $_bypass_user_if_admin	= true;
        break;
    }
    
    //3- now perform asked tests ---------------------------------------------------------------------
    if ($_check_active_process) // require an active process?
    {
      if ($_no_instance) //we need an instance or an activity to perfom the check
      {
        //we cannot be there without instance and without activity, we now we have one activity at least
        if (!($resactivity['wf_is_active']=='y'))
        {
          $this->error[] = tra('Process %1 is not active, action %2 is impossible', $resactivity['wf_procname'], $action);
          return false;
        }
      }
      else
      {
        if (!($resinstance['wf_is_active']=='y'))
        {
          $this->error[] = tra('Process %1 is not active, action %2 is impossible', $resinstance['wf_procname'], $action);
          return false;
        }
      }
    }
    
    if ($_check_instance)
    {
      if ($_no_instance)
      {
        $this->error[] = tra('Action %1 needs and instance and instance %2 does not exists', $action, $instanceId);
        return false;
      }
    }
    
    if ($_check_activity)
    {
      if ($_no_activity)
      {
        $this->error[] = tra('Action %1 needs and activity and activity %2 does not exists', $action, $activityId);
        return false;
      }
    }
    
    if ($_check_instance_activity) //is there a realtionship between instance and activity
    {
      if (!($resactivity['wf_instance_id']==$resinstance['wf_instance_id']))
      {
        $this->error[] = tra('Instance %1 is not associated with activity %2, action %3 is impossible.', $instanceId, $activityId, $action);
        return false;
      }
    }
    
    if (!(count($_check_instance_status) == 0)) //use to test some status between 'active','exception','aborted','completed'
    {
      if (!(in_array($resinstance['wf_status'],$_check_instance_status)))
      {
        $this->error[] = tra('Instance %1 is in %2 state, action %3 is impossible.', $instanceId, $resinstance['wf_status'], $action);
        return false;
      }
    }
    if (($_fail_on_exception) && ($resinstance['wf_status']=='exception'))
    {
        $this->error[] = tra('Instance %1 is in exception, action %2 is not possible.', $instanceId, $action);
        return false;
    }
    
    // user tests ---------------
    $checks = true;
    //is our user a special rights user?
    if (!( ($_bypass_user_if_admin) && (galaxia_user_can_admin_instance($user)) ))
    {
      //if our user is the owner we ignore user tests
      if (!( ($_bypass_user_role_if_owner) && ((int)$resinstance['wf_owner']==(int)$user) ))
      {
        //if activity is not interactive we do not perform user tests
        if (!( (!($_no_activity)) && ($_bypass_user_on_non_interactive) && ($resactivity['wf_is_interactive']=='n') ))
        {
          //is the actual_user our user?
          if ($_check_is_user) 
          {
            if (!((int)$resinstance['wf_user']==(int)$user))
            {
              //user test was false, but maybe we'll have better chance later
              $checks = false;
            }
          }
          // special '*' user
          if ($resinstance['wf_user']=='*')
          {
            //is the actual <>*?
            if ($_check_is_not_star)
            {
              //no redemption here
              $this->error[] = tra('Action %1 is impossible, there are no user assigned to this activity for this instance', $action);
              return false;
            }
            //perform the role test only if actual user is '*'
            if ($_check_is_in_role_if_star)
            {
              $checks=$this->checkUserAccess($user, $activityId);
            }
          }
          //is our user in associated roles (done only if check_is_user is false)
          if ( (!($checks)) && (!($_check_is_in_role_if_star)) && ($_check_is_in_role))
          {
            $checks=$this->checkUserAccess($user, $activityId);
          }
        }
      }
    }
    return checks;
  }

  //! Return avaible actions for a given user on a given activity and a given instance assuming he already have access to it.
  /*!
  * To be able to decide this function needs all the parameters, use the GUI object equivalent function if you want less parameters.
  * @public
  * @param $user is the user id
  * @param $instanceId is the instance id
  * @param $activityId is the activity id
  * @param $pId is the process id
  * @param $actType is the activity type
  * @param $actInteractive is 'y' or 'n' and is the activity interactivity
  * @param $actAutorouted is 'y' or 'n' and is the activity routage 
  * @param $actStatus is the activity status ('running' or 'completed')
  * @param $instanceOwner is the instance owner id
  * @param $instanceStatus is the instance status ('running', 'completed', 'aborted' or 'exception')
  * @param $currentUser is the actual instance/activity user id or '*'.
  * @return an array of this form:
  * 	array('action name' => 'action description')
  * 'actions names' are: 'grab', 'release', 'run', 'send', 'view', 'exception', 'resume', 'monitor' and 'admin'
  * Some config values can change theses rules but basically here they are:
  ** 'grab'	: be the user of this activity. User has access to it and instance status is ok.
  ** 'release'	: let * be the user of this activity. Must be the actual user or the owner of the instance.
  ** 'run'	: run an associated form. This activity is interactive, user has access, instance status is ok.
  ** 'send'	: send this instance, activity was non-autorouted and he has access and status is ok.
  ** 'view'	: view the instance, activity ok, always avaible except for start or standalone act.
  ** 'abort'	: abort an instance, ok when we are the user
  ** 'exception' : set the instance status to exception, need to be the user 
  ** 'resume'	: back to running when instance status was exception, need to be the user
  * 'actions description' are translated explanations like 'release access to this activity'
  * This function will as well load process configuration which could have some impact on the rights. 
  * Theses config data will be cached during the existence of this WfSecurity object.
  * WARNING: this is a snapshot, the engine give you a snaphsot of the rights a user have on an instance-activity
  * at a given time, this is not meaning theses rights will still be there when the user launch the action.
  * You should absolutely use the GUI Object to execute theses actions (except monitor and admin) and they could be rejected.
  * the GUI object call the checkUserAction() method of this object to check the rights at the real runtime
  * WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via a 
  * GUI object theses access rights are allready checked.
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

    $this->loadConfigValues($pId);
    
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
