<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'ProcessManager'.SEP.'BaseManager.php');
//!! InstanceManager
//! A class to maniplate instances
/*!
  This class is used to add,remove,modify and list
  instances.
*/
class InstanceManager extends BaseManager {
  
  /*!
    Constructor takes a PEAR::Db object to be used
    to manipulate roles in the database.
  */
  function InstanceManager($db) 
  {
    if(!$db) {
      die("Invalid db object passed to InstanceManager constructor");  
    }
    $this->db = $db;  
  }
  
  function get_instance_activities($iid)
  {
    $query = "select ga.wf_type,ga.wf_is_interactive,ga.wf_is_autorouted,gi.wf_p_id,ga.wf_activity_id,ga.wf_name,gi.wf_instance_id,gi.wf_status,gia.wf_activity_id,gia.wf_user,gi.wf_started,gia.wf_status as wf_act_status from ".GALAXIA_TABLE_PREFIX."activities ga,".GALAXIA_TABLE_PREFIX."instances gi,".GALAXIA_TABLE_PREFIX."instance_activities gia where ga.wf_activity_id=gia.wf_activity_id and gi.wf_instance_id=gia.wf_instance_id and gi.wf_instance_id=$iid";
    $result = $this->query($query);
    $ret = Array();
    while($res = $result->fetchRow()) {
      // Number of active instances
      $ret[] = $res;
    }
    return $ret;
  }

  function get_instance($iid)
  {
    $query = "select * from ".GALAXIA_TABLE_PREFIX."instances gi where wf_instance_id=$iid";
    $result = $this->query($query);
    $res = $result->fetchRow();
    $res['wf_workitems']=$this->getOne("select count(*) from ".GALAXIA_TABLE_PREFIX."workitems where wf_instance_id=$iid");
    return $res;
  }

  function get_instance_properties($iid)
  {
    $prop = unserialize($this->getOne("select wf_properties from ".GALAXIA_TABLE_PREFIX."instances gi where wf_instance_id=$iid"));
    return $prop;
  }
  
  function set_instance_properties($iid,&$prop)
  {
    $props = addslashes(serialize($prop));
    $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_properties='$props' where wf_instance_id=$iid";
    $this->query($query);
  }
  
  function set_instance_name($iid,$name)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_name='$name' where wf_instance_id=$iid";
    $this->query($query);
  }

  function set_instance_priority($iid,$priority)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_priority=? where wf_instance_id=?";
    $this->query($query, array((int)$priority, (int)$iid));
  }

  function set_instance_owner($iid,$owner)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_owner='$owner' where wf_instance_id=$iid";
    $this->query($query);
  }
  
  function set_instance_status($iid,$status)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instances set wf_status='$status' where wf_instance_id=$iid";
    $this->query($query); 
  }
  
  /*! remove all previous activities on this instance and create a new activity on the activity given
  * @param $iid is the instance id
  * @param $activityId is the activity id
  * @param $user is '*' by default and could be an user id
  * @param $status is 'running' by default but you could send 'completed' as well
  * @return false if any problems was encoutered (the database is then intact). Return true if everything was ok.
  * WARNING: if they were multiple activities ALL previous activities avaible on this instance are deleted
  */
  function set_instance_destination($iid,$activityId, $user='*', $status='running')
  {
    //Start a Transaction
    $this->db->StartTrans();
    $query = 'delete from '.GALAXIA_TABLE_PREFIX.'instance_activities where wf_instance_id=?';
    $this->query($query, array($iid));
    $query = 'insert into '.GALAXIA_TABLE_PREFIX.'instance_activities(wf_instance_id,wf_activity_id,wf_user,wf_status, wf_started, wf_ended)
    values(?,?,?,?,?,?)';
    $this->query($query, array($iid,$activityId,'*','running',date('U'),0));
    // perform commit (return true) or Rollback (return false)
    return $this->db->CompleteTrans();
  }
 
  /*!
  set $user as the new user of activity $activityId
  */
  function set_instance_user($iid,$activityId,$user)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities set wf_user=? where wf_instance_id=$iid and wf_activity_id=$activityId";
    $this->query($query, array($user));  
  }

}    

?>
