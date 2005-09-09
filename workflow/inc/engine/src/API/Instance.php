<?php
require_once (GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfSecurity.php');
require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ActivityManager.php');

//!! Instance
//! A class representing a process instance.
/*!
This class represents a process instance, it is used when any activity is
executed. The $instance object is created representing the instance of a
process being executed in the activity or even a to-be-created instance
if the activity is a start activity.
*/
class Instance extends Base {
  var $properties = Array();
  var $owner = '';
  var $status = '';
  var $started;
  var $nextActivity;
  var $nextUser;
  var $ended;
  var $name='';
  var $category;
  /// Array of assocs(activityId, status, started, ended, user, name, interactivity, autorouting)
  var $activities = Array();
  var $pId;
  var $instanceId = 0;
  var $priority = 1;
  /// An array of workitem ids, date, duration, activity name, user, activity type and interactivity
  var $workitems = Array(); 
  // this is an internal reminder
  var $__activity_completed=false;
  
  function Instance($db) 
  {
    $this->child_name = 'Instance';
    parent::Base($db);
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
  }
  
  /*!
  Method used to load an instance data from the database.
  */
  function getInstance($instanceId) {
    // Get the instance data
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instances` where `wf_instance_id`=?";
    $result = $this->query($query,array((int)$instanceId));
    if( empty($result) || (!$result->numRows())) return false;
    $res = $result->fetchRow();

    //Populate 
    $this->properties = unserialize($res['wf_properties']);
    $this->status = $res['wf_status'];
    $this->pId = $res['wf_p_id'];
    $this->instanceId = $res['wf_instance_id'];
    $this->priority = $res['wf_priority'];
    $this->owner = $res['wf_owner'];
    $this->started = $res['wf_started'];
    $this->ended = $res['wf_ended'];
    $this->nextActivity = $res['wf_next_activity'];
    $this->nextUser = $res['wf_next_user'];
    $this->name = $res['wf_name'];
    $this->category = $res['wf_category'];
    // Get the activities where the instance is
    $query = "select gia.wf_activity_id, gia.wf_instance_id, wf_started, wf_ended, wf_started, wf_user, wf_status,
              ga.wf_is_autorouted, ga.wf_is_interactive, ga.wf_name
              from ".GALAXIA_TABLE_PREFIX."instance_activities gia
              INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON ga.wf_activity_id = gia.wf_activity_id
              where wf_instance_id=?";
    $result = $this->query($query,array((int)$instanceId));
    if (!(empty($result)))
    {
      while($res = $result->fetchRow())
      {
        $this->activities[]=$res;
      }
    }
    // Get the workitems where the instance is
    $query = "select wf_item_id, wf_order_id, gw.wf_instance_id, gw.wf_activity_id, wf_started, wf_ended, gw.wf_user,
              ga.wf_name, ga.wf_type, ga.wf_is_interactive
              from ".GALAXIA_TABLE_PREFIX."workitems gw
              INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON ga.wf_activity_id = gw.wf_activity_id
              where wf_instance_id=? order by wf_order_id ASC";
    $result = $this->query($query,array((int)$instanceId));
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        $this->workitems[]=$res;
      }
    }
    return true;
  }
  
  /*! 
  Sets the next activity to be executed, if the current activity is
  a switch activity the complete() method will use the activity setted
  in this method as the next activity for the instance. 
  Note that this method receives an activity name as argument. (Not an Id)
  */
  function setNextActivity($actname) {
    $pId = $this->pId;
    $actname=trim($actname);
    $aid = $this->getOne("select `wf_activity_id` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_p_id`=? and `wf_name`=?",array($pId,$actname));
    if(!$this->getOne("select count(*) from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=? and `wf_p_id`=?",array($aid,$pId))) {
      trigger_error(tra('Fatal error: setting next activity to an unexisting activity'),E_USER_WARNING);
    }
    $this->nextActivity=$aid;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_next_activity`=? where `wf_instance_id`=?";
      $this->query($query,array((int)$aid,(int)$this->instanceId));
    }
  }

  /*!
  This method can be used to set the user that must perform the next 
  activity of the process. this effectively "assigns" the instance to
  some user.
  */
  function setNextUser($user) {
    $this->nextUser = $user;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_next_user`=? where `wf_instance_id`=?";
      $this->query($query,array($user,(int)$this->instanceId));
    }
  }

  /*!
  This method can be used to get the user that must perform the next 
  activity of the process. This can be empty if no setNextUser was done before.
  It wont return the default user but inly the user which was assigned by a setNextUser.
  */
  function getNextUser() 
  {
    return $this->nextUser;
  }
 
  /*!
  * @private
  * Creates a new instance.
  * This method is called in start activities when the activity is completed
  * to create a new instance representing the started process.
  */
  function _createNewInstance($activityId,$user) {
    // Creates a new instance setting up started,ended,user
    // and status
    $pid = $this->getOne("select `wf_p_id` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));
    $this->status = 'active';
    $this->nextActivity = 0;
    $this->setNextUser('');
    $this->pId = $pid;
    $now = date("U");
    $this->started=$now;
    $this->owner = $user;
    $name = $this->getName();
    $category = $this->getCategory();
    $query = "insert into `".GALAXIA_TABLE_PREFIX."instances`
      (`wf_started`,`wf_ended`,`wf_status`,`wf_p_id`,`wf_owner`,`wf_properties`,`wf_name`,`wf_category`,`wf_priority`) 
      values(?,?,?,?,?,?,?,?,?)";
    $this->query($query,array($now,0,'active',$pid,$user,$props,$name,$category,$this->priority));
    $this->instanceId = $this->getOne("select max(`wf_instance_id`) from `".GALAXIA_TABLE_PREFIX."instances` where `wf_started`=? and `wf_owner`=?",array((int)$now,$user));
    $iid=$this->instanceId;
    
    // Now update the properties!
    $props = serialize($this->properties);
    $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_properties`=? where `wf_instance_id`=?";
    $this->query($query,array($props,(int)$iid));

    // Then add in ".GALAXIA_TABLE_PREFIX."instance_activities an entry for the
    // activity the user and status running and started now
    $query = "insert into `".GALAXIA_TABLE_PREFIX."instance_activities`(`wf_instance_id`,`wf_activity_id`,`wf_user`,`wf_started`,`wf_status`) values(?,?,?,?,?)";
    $this->query($query,array((int)$iid,(int)$activityId,$user,(int)$now,'running'));
  }
  
  /*!
  Sets the name of this instance.
  */
  function setName($value) {
    $this->name = $value;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_name=? where wf_instance_id=?";
      $this->query($query,array($value,(int)$this->instanceId));
    }
  }

  /*!
  Get the name of this instance.
  */
  function getName() {
    return $this->name;
  }

  /*!
  * Sets the category of this instance.
  */
  function setCategory($value) {
    $this->category = $value;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_category=? where wf_instance_id=?";
      $this->query($query,array($value,(int)$this->instanceId));
    }
  }

  /*!
  * Get the category of this instance.
  */
  function getCategory() {
    return $this->category;
  }
  
  /*! 
  Sets a property in this instance. This method is used in activities to
  set instance properties. Instance properties are immediately serialized.
  */
  function set($name,$value) {
    $this->properties[$name] = $value;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $props = serialize($this->properties);
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_properties`=? where `wf_instance_id`=?";
      $this->query($query,array($props,$this->instanceId));
    }
  }
  
  /*! 
  Gets the value of an instance property.
  */
  function get($name) {
    if(isset($this->properties[$name])) {
      return $this->properties[$name];
    } else {
      return false;
    }
  }
  
  /*! 
  Returns an array of assocs describing the activities where the instance
  is present, can be more than one activity if the instance was "splitted"
  */
  function getActivities() {
    return $this->activities;
  }
  
  /*! 
  Gets the instance status can be
  'completed', 'active', 'aborted' or 'exception'
  */
  function getStatus() {
    return $this->status;
  }
  
  /*! 
  Sets the instance status , the value can be:
  'completed', 'active', 'aborted' or 'exception'
  */
  function setStatus($status) {
    $this->status = $status; 
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      // and update the database
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_status`=? where `wf_instance_id`=?";
      $this->query($query,array($status,(int)$this->instanceId));
    }
  }
  
  /*!
  Gets the instance priority, it's an integer
  */
  function getPriority()
  {
    return $this->priority;
  } 

  /*!
  Sets the instance priority , the value should be an integer
  */
  function setPriority($priority)
  {
    $mypriority = (int)$priority;
    $this->priority = $mypriority;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      // and update the database
      $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_priority=? where wf_instance_id=?";
      $this->query($query,array($this->priority,(int)$this->instanceId));
    }
  }
   
  /*!
  Returns the instanceId
  */
  function getInstanceId() {
    return $this->instanceId;
  }
  
  /*! 
  Returns the processId for this instance
  */
  function getProcessId() {
    return $this->pId;
  }
  
  /*! 
  Returns the user that created the instance
  */
  function getOwner() {
    return $this->owner;
  }
  
  /*! 
  Sets the instance creator user 
  */
  function setOwner($user) {
    $this->owner = $user;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      // save database
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_owner`=? where `wf_instance_id`=?";
      $this->query($query,array($this->owner,(int)$this->instanceId));
    }
  }
  
  /*!
  * Sets the user that must execute the activity indicated by the activityId.
  * Note that the instance MUST be present in the activity to set the user,
  * you can't program who will execute an activity.
  * If the user is empty then the activity user is setted to *, allowing any
  * authorised user to take the token later
  * 
  * concurrent access to this function is normally handled by WfRuntime and WfSecurity
  * theses objects are the only ones which should call this function. WfRuntime is handling the
  * current transaction and WfSecurity is Locking the instance and instance_activities table on
  * a 'run' action which is the action leading to this setActivityUser call (could be a release 
  * as well on auto-release)
  * @param $activityId is the activity Id
  * @param $theuser is the user id or '*' (or 0, '' or null which will be set to '*')
  * @return false if something was wrong
  */
  function setActivityUser($activityId,$theuser) {
    if(empty($theuser)) $theuser='*';
    $found = false;
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        // here we are in the good activity
        $found = true;

        // prepare queries
        $where = ' where wf_activity_id=? and wf_instance_id=?';
        $bindvars = array((int)$activityId,(int)$this->instanceId);
        if(!($theuser=='*')) 
        {
          $where .= ' and (wf_user=? or wf_user=?)';
          $bindvars[]= $theuser;
          $bindvars[]= '*';
        }
        
        // update the user
        $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=?';
        $query .= $where;
        $bindvars_update = array_merge(array($theuser),$bindvars);
        $this->query($query,$bindvars_update);
        $this->activities[$i]['wf_user']=$theuser;
        return true;
      }
    }
    // if we didn't find the activity it will be false
    return $found;
  }

  /*!
  Returns the user that must execute or is already executing an activity
  wherethis instance is present.
  */  
  function getActivityUser($activityId) {
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_user'];
      }
    }  
    return false;
  }

  /*!
  Sets the status of the instance in some activity, can be
  'running' or 'completed'
  */  
  function setActivityStatus($activityId,$status) {
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        $this->activities[$i]['wf_status']=$status;
        $query = "update `".GALAXIA_TABLE_PREFIX."instance_activities` set `wf_status`=? where `wf_activity_id`=? and `wf_instance_id`=?";
        $this->query($query,array($status,(int)$activityId,(int)$this->instanceId));
      }
    }  
  }
  
  
  /*!
  Gets the status of the instance in some activity, can be
  'running' or 'completed'
  */
  function getActivityStatus($activityId) {
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_status'];
      }
    }  
    return false;
  }
  
  /*!
  Resets the start time of the activity indicated to the current time.
  */
  function setActivityStarted($activityId) {
    $now = date("U");
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        $this->activities[$i]['wf_started']=$now;
        $query = "update `".GALAXIA_TABLE_PREFIX."instance_activities` set `wf_started`=? where `wf_activity_id`=? and `wf_instance_id`=?";
        $this->query($query,array($now,(int)$activityId,(int)$this->instanceId));
      }
    }  
  }
  
  /*!
  Gets the Unix timstamp of the starting time for the given activity.
  */
  function getActivityStarted($activityId) {
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i]['wf_started'];
      }
    }  
    return false;
  }
  
  /*!
  \private
  Gets an activity from the list of activities of the instance
  the result is an array describing the instance
  */
  function _get_instance_activity($activityId) {
    for($i=0;$i<count($this->activities);$i++) {
      if($this->activities[$i]['wf_activity_id']==$activityId) {
        return $this->activities[$i];
      }
    }  
    return false;
  }

  /*!
  Sets the time where the instance was started.    
  */
  function setStarted($time) {
    $this->started = $time;
    //no need to save on pseudo-instances
    if (!!($this->instanceId))
    {
      $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_started`=? where `wf_instance_id`=?";
      $this->query($query,array((int)$time,(int)$this->instanceId));    
    }
  }
  
  /*!
  Gets the time where the instance was started (Unix timestamp)
  */
  function getStarted() {
    return $this->started;
  }
  
  /*!
  Sets the end time of the instance (when the process was completed)
  */
  function setEnded($time) {
    $this->ended=$time;
    $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_ended`=? where `wf_instance_id`=?";
    $this->query($query,array((int)$time,(int)$this->instanceId));    
  }
  
  /*!
  Gets the end time of the instance (when the process was completed)
  */
  function getEnded() {
    return $this->ended;
  }
  
  /*!
  * This set to true or false the 'Activity Completed' status which will
  * be important to know if the user code has completed the current activity
  * @param $bool is true by default, it will be the next status of the 'Activity Completed' indicator
  */
  function setActivityCompleted($bool)
  {
    $this->__activity_completed = $bool;
  }
  
  /*!
  * This set to true or false the 'Activity Completed' status which will
  * be important to know if the user code has completed the current activity
  * @param $bool is true by default, it will be the next status of the 'Activity Completed' indicator
  */
  function getActivityCompleted()
  {
    return $this->__activity_completed;
  }
  
  //! Completes an activity, normally from any activity you should call this function without arguments.
  /*!
  * YOU MUST NOT CALL complete() for non-interactive activities since
  * the engine does automatically complete automatic activities after
  * executing them.
  * The arguments are explained just in case. YOU SHOULD NOT USE THEM in your activity code.
  * @param $activityId is the activity that is being completed, when this is not
  * passed the engine takes it from the $_REQUEST array,all activities
  * are executed passing the activityId in the URI.
  * @param $addworkitem indicates if a workitem should be added for the completed
  * activity (true by default).
  * @return true or false, if false it means the complete was not done for some internal reason
  * consult $instance->get_error() for more informations
  */
  function complete($activityId=0,$addworkitem=true) {
    global $user;

    //ensure it's false at first
    $this->setActivityCompleted(false);
    //the complete will be well done or not done at all
    $this->db->StartTrans();
    
    if(empty($user)) 
    {
      $theuser='*';
    } 
    else 
    {
      $theuser=$user;
    }
    
    //TODO: this is maybe a bad idea, it could lead to strange bugs. avoid $_REQUEST when this object
    // will really be only an internal object
    if($activityId==0) 
    {
      $activityId=$_REQUEST['activity_id'];
    }  
    
    //Lock Rows (always the same order instances -> instance-activities -> activities to avoid deadlocks)
    if ($this->instanceId > 0)
    {
      $where = 'wf_instance_id='.(int)$this->instanceId;
      if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instances',$where)))
      {
        $this->error[] = tra('failed to obtain a lock on database (table %1), aborting',GALAXIA_TABLE_PREFIX.'instances');
        return $this->db->CompleteTrans();
      }
      $where .= ' and wf_activity_id='.(int)$activityId;
      if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'instance_activities',$where)))
      {
        $this->error[] = tra('failed to obtain a lock on database (table %1), aborting',GALAXIA_TABLE_PREFIX.'instance_activities');
        return $this->db->CompleteTrans();
      }
    }
    $where = 'wf_activity_id='.(int)$activityId;
    if (!($this->db->RowLock(GALAXIA_TABLE_PREFIX.'activities',$where)))
    {
        $this->error[] = tra('failed to obtain a lock on database (table %1), aborting',GALAXIA_TABLE_PREFIX.'activities');
        return $this->db->CompleteTrans();
    }
    // If we are completing a start activity then the instance must 
    // be created first!
    $type = $this->getOne('select wf_type from '.GALAXIA_TABLE_PREFIX.'activities where wf_activity_id=?',array((int)$activityId));    
    if($type=='start') {
      $this->_createNewInstance((int)$activityId,$theuser);
    }
      
    // Now set ended
    $now = date("U");
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_ended=? where wf_activity_id=? and wf_instance_id=?';
    $this->query($query,array((int)$now,(int)$activityId,(int)$this->instanceId));
    //Add a workitem to the instance 
    $iid = $this->instanceId;
    if($addworkitem) {
      $max = $this->getOne('select max(wf_order_id) from '.GALAXIA_TABLE_PREFIX.'workitems where wf_instance_id=?',array((int)$iid));
      if(!$max) {
        $max=1;
      } else {
        $max++;
      }
      $act = $this->_get_instance_activity($activityId);
      if(!$act) {
        //Then this is a start activity ending
        $started = $this->getStarted();
        $putuser = $this->getOwner();
      } else {
        $started=$act['wf_started'];
        $putuser = $act['wf_user'];
      }
      $ended = date("U");
      $properties = serialize($this->properties);
      $query='insert into '.GALAXIA_TABLE_PREFIX.'workitems
        (wf_instance_id,wf_order_id,wf_activity_id,wf_started,wf_ended,wf_properties,wf_user) values(?,?,?,?,?,?,?)';    
      $this->query($query,array((int)$iid,(int)$max,(int)$activityId,(int)$started,(int)$ended,$properties,$putuser));
    }
    
    //Set the status for the instance-activity to completed
    $this->setActivityStatus($activityId,'completed');
    
    //If this and end actt then terminate the instance
    if($type=='end') 
    {
      if (!($this->terminate()))
      {
        $this->db->FailTrans();
      }
    }

    //TODO: avoid unnecessary GLOBALS
    $this->setActivityCompleted($this->db->CompleteTrans());

    return $this->getActivityCompleted();
    
  }
  //! Send autorouted activities to the next one(s). Private engine function
  /*
  * The arguments are explained just in case.
  * @param $activityId is the activity that is being completed, when this is not
  * passed the engine takes it from the $_REQUEST array,all activities
  * are executed passing the activityId in the URI.
  * @param $force indicates that the instance must be routed no matter if the
  * activity is auto-routing or not. This is used when "sending" an
  * instance from a non-auto-routed activity to the next activity.
  * @private
  * YOU MUST NOT CALL sendAutorouted() for non-interactive activities since
  * the engine does automatically complete and send automatic activities after
  * executing them.
  * This function is in fact a Private function runned by the engine. You should
  * never use it without knowing very very well what you're doing.
  * @return false or an array with ['transition']['failure'] set in case of any problem, 
  * true if nothing was done and an array if something done, like walk on transition 
  * and execution of an activity (see sendTo comments) or if this activity was a split 
  * activity (in this case the array contains a row for each following activity)
  */
  function sendAutorouted($activityId,$force=false)
  {
    $returned_value = Array();
    $type = $this->getOne("select `wf_type` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));    
    //on a end activity we have nothing to do
    if ($type == 'end')
    {
      return true;
    }
    //If the activity ending is not autorouted then we have nothing to do
    if (!(($force) || ($this->getOne("select `wf_is_autorouted` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array($activityId)) == 'y')))
    {
      $returned_value['transition']['status'] = 'not autorouted';
      return $returned_value;
    }
    //If the activity ending is autorouted then send to the activity
    // Now determine where to send the instance
    $query = "select `wf_act_to_id` from `".GALAXIA_TABLE_PREFIX."transitions` where `wf_act_from_id`=?";
    $result = $this->query($query,array((int)$activityId));
    $candidates = Array();
    while ($res = $result->fetchRow()) 
    {
      $candidates[] = $res['wf_act_to_id'];
    }  
    if($type == 'split') 
    {
      $erase_from = false;
      $num_candidates = count($candidates);
      $returned_data = Array();
      $i = 1;
      foreach ($candidates as $cand) 
      {
        // only erase split activity in instance when all the activities comming from the split have been set up
        if ($i == $num_candidates)
        { 
          $erase_from = true;
        }
        $returned_data[$i] = $this->sendTo($activityId,$cand,$erase_from);
        $i++;
      }
      return $returned_data;
    } 
    elseif($type == 'switch') 
    {
      if (in_array($this->nextActivity,$candidates))
      {
        return $this->sendTo((int)$activityId,(int)$this->nextActivity);
      } 
      else 
      {
        $returned_value['transition']['failure'] = tra('Error: nextActivity does not match any candidate in autorouting switch activity');
        return $returned_value;
        //trigger_error(tra('Fatal error: nextActivity does not match any candidate in autorouting switch activity'),E_USER_WARNING);
      }
    } 
    else 
    {
      if (count($candidates)>1) 
      {
        $returned_value['transition']['failure'] = tra('Error: non-deterministic decision for autorouting activity');
        return $returned_value;
        //trigger_error(tra('Fatal error: non-deterministic decision for autorouting activity'),E_USER_WARNING);
      }
      else 
      {
        return $this->sendTo((int)$activityId,(int)$candidates[0]);
      }
    }
  }
  
  /*!
  Aborts an activity and terminates the whole instance. We still create a workitem to keep track
  of where in the process the instance was aborted
  */
  function abort($activityId=0,$theuser = '',$addworkitem=true) {
    if(empty($theuser)) {
      global $user;
      if (empty($user)) {$theuser='*';} else {$theuser=$user;}
    }
    
    if($activityId==0) {
      $activityId=$_REQUEST['wf_activity_id'];
    }  
    
    // If we are completing a start activity then the instance must 
    // be created first!
    $type = $this->getOne("select `wf_type` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));    
    if($type=='start') {
      $this->_createNewInstance((int)$activityId,$theuser);
    }
      
    // Now set ended
    $now = date("U");
    $query = "update `".GALAXIA_TABLE_PREFIX."instance_activities` set `wf_ended`=? where `wf_activity_id`=? and `wf_instance_id`=?";
    $this->query($query,array((int)$now,(int)$activityId,(int)$this->instanceId));
    
    //Add a workitem to the instance 
    $iid = $this->instanceId;
    if($addworkitem) {
      $max = $this->getOne("select max(`wf_order_id`) from `".GALAXIA_TABLE_PREFIX."workitems` where `wf_instance_id`=?",array((int)$iid));
      if(!$max) {
        $max=1;
      } else {
        $max++;
      }
      $act = $this->_get_instance_activity($activityId);
      if(!$act) {
        //Then this is a start activity ending
        $started = $this->getStarted();
        $putuser = $this->getOwner();
      } else {
        $started=$act['wf_started'];
        $putuser = $act['wf_user'];
      }
      $ended = date("U");
      $properties = serialize($this->properties);
      $query="insert into `".GALAXIA_TABLE_PREFIX."workitems`(`wf_instance_id`,`wf_order_id`,`wf_activity_id`,`wf_started`,`wf_ended`,`wf_properties`,`wf_user`) values(?,?,?,?,?,?,?)";    
      $this->query($query,array((int)$iid,(int)$max,(int)$activityId,(int)$started,(int)$ended,$properties,$putuser));
    }
    
    //Set the status for the instance-activity to aborted
// TODO: support 'aborted' if we keep activities after termination some day
    //$this->setActivityStatus($activityId,'aborted');

    // terminate the instance with status 'aborted'
    return $this->terminate('aborted');
  }
  
  /*!
  * Terminates the instance marking the instance and the process
  * as completed. This is the end of a process.
  * Normally you should not call this method since it is automatically
  * called when an end activity is completed.
  * @param $status is the final status, 'completed' by default
  * @return true if everything was ok, false else
  */
  function terminate($status = 'completed') {
    //Set the status of the instance to completed
    $now = date("U");
    $query = "update `".GALAXIA_TABLE_PREFIX."instances` set `wf_status`=?, `wf_ended`=?, `wf_priority`=0 where `wf_instance_id`=?";
    $this->query($query,array($status,(int)$now,(int)$this->instanceId));
    $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=?";
    $this->query($query,array((int)$this->instanceId));
    $this->status = $status;
    $this->activities = Array();
    return true;
  }
  
  
  /*!
  * Sends the instance from some activity to another activity. (walk on a transition)
  * You should not call this method unless you know very very well what
  * you are doing.
  * @param $from is the activity id at the start of the transition
  * @param $activityId is the activity id at the end of the transition
  * @param $erase_from is true by default, if true the coming activity row will be erased from
  * instance_activities table. You should set it to false for example with split activities while
  * you still want to re-call this function
  * @return false if anything goes wrong, true if we are at the end of the execution tree and an array
  * if a part of the process was automatically runned at the end of the transition. this array contains
  * 2 keys 'transition' is the transition we walked on, 'activity' is the result of the run part if it was an automatic activity.
  * 'activity' value is an associated array containing several usefull keys:
  *	* 'completed' is a boolean indicating that the activity was completed or not
  *	* 'debug contains debug messages
  *	* 'info' contains some usefull infos about the activity-instance running (like names)
  *	* 'next' is the result of a SendAutorouted part which could in fact be the result of a call to this function, etc.
  */
  function sendTo($from,$activityId,$erase_from=true) {
    //we will use an array for return value
    $returned_data = Array();
    //1: if we are in a join check
    //if this instance is also in
    //other activity if so do
    //nothing
    $query = 'select wf_type, wf_name from '.GALAXIA_TABLE_PREFIX.'activities where wf_activity_id=?';
    $result = $this->query($query,array($activityId));
    if (empty($result))
    {
      $returned_data['transition']['failure'] = tra('Error: trying to send an instance to an activity but it was impossible to get this activity');
      return $returned_data;
    }
    while ($res = $result->fetchRow())
    {
      $type = $res['wf_type'];
      $targetname = $res['wf_name'];
    }
    $returned_data['transition']['target_id'] = $activityId;
    $returned_data['transition']['target_name'] = $targetname;
    
    // Verify the existance of a transition
    if(!$this->getOne("select count(*) from `".GALAXIA_TABLE_PREFIX."transitions` where `wf_act_from_id`=? and `wf_act_to_id`=?",array($from,(int)$activityId))) {
      $returned_data['transition']['failure'] = tra('Error: trying to send an instance to an activity but no transition found');
      return $returned_data;
      //trigger_error(tra('Fatal error: trying to send an instance to an activity but no transition found'),E_USER_WARNING);
    }

    //init
    $putuser=0;
    
    //try to determine the user or *
    //Use the nextUser
    if($this->nextUser) 
    {
      //we check rights for this user on the next activity
      $wf_security = new WfSecurity($this->db);
      if ($wf_security->checkUserAccess($this->nextUser,$activityId))
      {
        $putuser = $this->nextUser;
      }
    }
    if ($putuser==0)
    {
      // If no nextUser is set, then see if only
      // one user is in the role for this activity
      // and assign ownership to him if this is the case
      $query = "select `wf_role_id` from `".GALAXIA_TABLE_PREFIX."activity_roles` where `wf_activity_id`=?";
      $result = $this->query($query,array((int)$activityId)); 
      while ($res = $result->fetchRow()) 
      {
        $roleId = $res['wf_role_id'];
        //regis: group role mapping as an impact here, we need to count real user corresponding to this role
        // and we obtain users 'u' and groups 'g' in user_roles
        // we consider number of members on each group is subject to too much changes and so we do not even try 
        // to look in members of the group to find if there is a unique real user candidate for this role
        // you could try it if you want but it's quite complex for something not really usefull
        // if there's at least one group in the roles we then won't even try to get this unique user
        $query_group = "select count(*) from ".GALAXIA_TABLE_PREFIX."user_roles 
            where wf_role_id=? and wf_account_type='g'";
        if ($this->getOne($query_group,array((int)$roleId)) > 0 )
        { //we have groups
          //we can break the while, we wont search the candidate
          $putuser=0;
          break;
        }
        else
        {// we have no groups
          $query2 = "select distinct wf_user, wf_account_type from ".GALAXIA_TABLE_PREFIX."user_roles 
              where wf_role_id=?";
          $result2 = $this->query($query2,array((int)$roleId)); 
          while ($res2 = $result2->fetchRow()) 
          {
            if (!($putuser==0))
            { // we already have one candidate
              // we have another one in $res2['wf_user'] but it means we don't have only one
              // we can unset our job and break the wile
              $putuser=0;
              break;
            }
            else
            {
              // set the first candidate
              $putuser = $res2['wf_user'];
            }
          }
        }
      }

      if ($putuser==0) // no decisions yet
      {
        // then check to see if there is a default user
        $activity_manager =& new ActivityManager($this->db);
        //get_default_user will give us '*' if there is no default_user or if the default user has no role
        //mapped anymore
        $default_user = $activity_manager->get_default_user($activityId,true);
        unset($activity_manager);
        // if they were no nextUser, no unique user avaible, no default_user then we'll have '*'
        // which will let user having the good role mapping grab this activity later
        $putuser = $default_user;
      }
    }
    
    //update the instance_activities table
    //if not splitting delete first
    //please update started,status,user
    if (($erase_from) && (!empty($this->instanceId)))
    {
      $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=? and `wf_activity_id`=?";
      $this->query($query,array((int)$this->instanceId,$from));
    }
  
    if ($type == 'join') {
      if (count($this->activities)>1) {
        // This instance will have to wait!
        $returned_data['transition']['status'] = 'waiting';
        return $returned_data;
      }
    }    

    //create the new instance-activity
    $returned_data['transition']['target_id'] = $activityId;
    $returned_data['transition']['target_name'] = $targetname;
    $now = date("U");
    $iid = $this->instanceId;
    $query="delete from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=? and `wf_activity_id`=?";
    $this->query($query,array((int)$iid,(int)$activityId));
    $query="insert into `".GALAXIA_TABLE_PREFIX."instance_activities`(`wf_instance_id`,`wf_activity_id`,`wf_user`,`wf_status`,`wf_started`) values(?,?,?,?,?)";
    $this->query($query,array((int)$iid,(int)$activityId,$putuser,'running',(int)$now));
    
    //record the transition walk
    $returned_data['transition']['status'] = 'done';

    
    //we are now in a new activity
    $this->activities=Array();
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instance_activities` where `wf_instance_id`=?";
    $result = $this->query($query,array((int)$iid));
    while ($res = $result->fetchRow()) {
      $this->activities[]=$res;
    }    

    //if the activity is not interactive then
    //execute the code for the activity and
    //complete the activity
    $isInteractive = $this->getOne("select `wf_is_interactive` from `".GALAXIA_TABLE_PREFIX."activities` where `wf_activity_id`=?",array((int)$activityId));
    if ($isInteractive=='n') 
    {
      // Now execute the code for the activity (function defined in galaxia's config.php)
      $returned_data['activity'] =& galaxia_execute_activity($activityId, $iid , 1);
      //we should have some info in $returned_data now. if it is false there's a problem
      if ((!(is_array($returned_data['activity']))) && (!($returned_data['activity'])) )
      {
        $this->error[] = tra('failed to execute automatic activity');
        //record the failure
        $returned_data['activity']['failure'] = true;
        return $returned_data;
      }
      else
      {
        //ok, we have an array, but it can still be a bad result
        //this one is just for debug info
        if (isset($returned_data['activity']['debug']))
        {
          //we retrieve this info here, in this object
          $this->error[] = $returned_data['activity']['debug'];
        }
        //and this really test if it worked, if not we have a nice failure message (better than just failure=true)
        if (isset($returned_data['activity']['failure']))
        {
          $this->error[] = tra('failed to execute automatic activity');
          $this->error[] = $returned_data['activity']['failure'];
          //record the failure
          return $returned_data;
        }
      }
      // Reload in case the activity did some change
      $this->getInstance($this->instanceId);
      //complete the activity
      if ($this->complete($activityId))
      {
        $returned_data['activity']['completed'] = true;
        
        //and send the next autorouted activity if any
        $returned_data['activity']['next'] = $this->sendAutorouted($activityId);
      }
      else
      {
        $returned_data['activity']['failure'] = $this->get_error();
      }
    }
    return $returned_data;
  }
  
  /*! 
  Gets a comment for this instance 
  */
  function get_instance_comment($cId) {
    $iid = $this->instanceId;
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? and `wf_c_id`=?";
    $result = $this->query($query,array((int)$iid,(int)$cId));
    $res = $result->fetchRow();
    return $res;
  }
  
  /*! 
  Inserts or updates an instance comment 
  */
  function replace_instance_comment($cId, $activityId, $activity, $user, $title, $comment) {
    if (!$user) {
      $user = 'Anonymous';
    }
    $iid = $this->instanceId;
    if ($cId) {
      $query = "update `".GALAXIA_TABLE_PREFIX."instance_comments` set `wf_title`=?,`wf_comment`=? where `wf_instance_id`=? and `wf_c_id`=?";
      $this->query($query,array($title,$comment,(int)$iid,(int)$cId));
    } else {
      $hash = md5($title.$comment);
      if ($this->getOne("select count(*) from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? and `wf_hash`=?",array($iid,$hash))) {
        return false;
      }
      $now = date("U");
      $query ="insert into `".GALAXIA_TABLE_PREFIX."instance_comments`(`wf_instance_id`,`wf_user`,`wf_activity_id`,`wf_activity`,`wf_title`,`wf_comment`,`wf_timestamp`,`wf_hash`) values(?,?,?,?,?,?,?,?)";
      $this->query($query,array((int)$iid,$user,(int)$activityId,$activity,$title,$comment,(int)$now,$hash));
    }  
  }
  
  /*!
  Removes an instance comment
  */
  function remove_instance_comment($cId) {
    $iid = $this->instanceId;
    $query = "delete from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_c_id`=? and `wf_instance_id`=?";
    $this->query($query,array((int)$cId,(int)$iid));
  }
 
  /*!
  Lists instance comments
  */
  function get_instance_comments() {
    $iid = $this->instanceId;
    $query = "select * from `".GALAXIA_TABLE_PREFIX."instance_comments` where `wf_instance_id`=? order by ".$this->convert_sortmode("timestamp_desc");
    $result = $this->query($query,array((int)$iid));    
    $ret = Array();
    while($res = $result->fetchRow()) {    
      $ret[] = $res;
    }
    return $ret;
  }
}
?>