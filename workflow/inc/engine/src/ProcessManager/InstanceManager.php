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
  function InstanceManager(&$db) 
  {
    parent::BaseManager($db);
    $this->child_name = 'InstanceManager';
  }

  /*!
  * @public
  * @param $iid is the instance Id
  * @return an associative array describing activities and their relation with the instance
  */
  function get_instance_activities($iid)
  {
    $query = 'select ga.wf_type,ga.wf_is_interactive,ga.wf_is_autorouted,ga.wf_activity_id,ga.wf_name,
            gi.wf_p_id,gi.wf_instance_id,gi.wf_status,gi.wf_started,
            gia.wf_activity_id,gia.wf_user,gia.wf_status as wf_act_status 
            from '.GALAXIA_TABLE_PREFIX.'activities ga,
            INNER JOIN '.GALAXIA_TABLE_PREFIX.'instances gi ON ga.wf_instance_id=gi.wf_instance_id,
            INNER JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
            where gi.wf_instance_id=?';
    $result = $this->query($query, array($iid));
    $ret = Array();
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        // Number of active instances
        $ret[] = $res;
      }
    }
    return $ret;
  }

  /*!
  * @public
  * @param $iid is the instance Id
  * @return an associative array describing the instance
  */
  function get_instance($iid)
  {
    $query = 'select * from '.GALAXIA_TABLE_PREFIX.'instances gi where wf_instance_id=?';
    $result = $this->query($query, array($iid));
    $res = Array();
    if (!(empty($result)))
    {
      $res = $result->fetchRow();
      $result['wf_next_activity']=unserialize($result['wf_next_activity']);
      $res['wf_workitems']=$this->getOne('select count(*) from '.GALAXIA_TABLE_PREFIX.'workitems where wf_instance_id=?', array($iid));
    }
    return $res;
  }

  /*!
  * @public
  * @param $iid is the instance Id
  * @return an associative array describing the instance properties
  */
  function get_instance_properties($iid)
  {
    $prop = unserialize($this->getOne('select wf_properties from '.GALAXIA_TABLE_PREFIX.'instances gi where wf_instance_id=?',array($iid)));
    return $prop;
  }
  
  /*!
  * @public
  * Save the given instance properties
  * @param $iid is the instance Id
  * @param $prop is an associative array describing the instance properties
  * @return true or false
  */
  function set_instance_properties($iid,&$prop)
  {
    $props = addslashes(serialize($prop));
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_properties=? where wf_instance_id=?';
    $this->query($query, array($prop,$iid));
    return true;
  }
  
  /*!
  * @public
  * Save the given instance name
  * @param $iid is the instance Id
  * @param $name is the name of the instance
  * @return true or false
  */
  function set_instance_name($iid,$name)
  {
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_name=? where wf_instance_id=?';
    $this->query($query, array($name,$iid));
    return true;
  }

  /*!
  * @public
  * Save the given instance priority
  * @param $iid is the instance Id
  * @param $priority is the instance priority
  * @return true or false
  */
  function set_instance_priority($iid,$priority)
  {
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_priority=? where wf_instance_id=?';
    $this->query($query, array((int)$priority, (int)$iid));
    return true;
  }

  /*!
  * @public
  * Save the given instance category
  * @param $iid is the instance Id
  * @param $category is the instance category
  * @return true or false
  */
  function set_instance_category($iid,$category)
  {
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_category=? where wf_instance_id=?';
    $this->query($query, array((int)$category, (int)$iid));
    return true;
  }

  /*!
  * @public
  * Save the given instance owner
  * @param $iid is the instance Id
  * @param $owner is the owner id of the instance
  * @return true or false
  */
  function set_instance_owner($iid,$owner)
  {
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_instance_id=?';
    $this->query($query, array($owner, $iid));
    return true;
  }
  
  /*!
  * @public
  * Save the given instance status
  * @param $iid is the instance Id
  * @param $status is the instance status, should be one of 'running', 'completed', 'exception' or 'aborted
  * @return true or false
  */
  function set_instance_status($iid,$status)
  {
    if (!(($status=='completed') || ($status=='active') || ($status=='aborted') || ($status=='exception')))
    {
      $this->error[] = tra('unknown status');
      return false;
    }
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_status=? where wf_instance_id=?';
    $this->query($query, array($status,$iid)); 
    return true;
  }
  
  /*!
  * @public 
  * Remove all previous activities on this instance and create a new activity on the activity given
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
  * @public
  * set $user as the new user of activity $activityId if this activity is really related to the instance.
  * @param $iid is the instance Id
  * @param $activityId is the activity Id
  * @param $user is the new user id
  * @return true or false
  */
  function set_instance_user($iid,$activityId,$user)
  {
    $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities set wf_user=? where wf_instance_id=? and wf_activity_id=?";
    $this->query($query, array($user, $iid, $activityId));
    return true;
  }
  
  //! Removes an user from all fields where he could be on every instances
  /*!
  * This function delete all references on the given user on all instances.
  * It will concern: wf_user, wf_owner and wf_next_user fields
  * @param $user is the user id to remove
  * @return true or false
  */
  function remove_user($user)  
  {
    // user=id => user='*'
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=? where wf_user=?';
    $this->query($query,array('*',$user));
    // owner=id => owner=0
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_owner=?';
    $this->query($query,array(0,$user));
    // next_user=id => next_user=NULL
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_next_user=? where wf_next_user=?';
    $this->query($query,array(NULL,$user));
    return true;
  }
  
  //! Transfer all references to one user to another one
  /*!
  * This function transfer all references concerning one user to another user
  * It will concern: wf_user, wf_owner and wf_next_user fields
  * This function will not check access on the instance for the new user, it is the task
  * of the admin to ensure the new user will have the necessary access rights
  * @param $old_user is the actual user id
  * @param $new_user is the new user id
  * @return true or false
  */
  function transfer_user($old_user, $new_user)  
  {
    // user
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instance_activities set wf_user=? where wf_user=?';
    $this->query($query,array($new_user,$old_user));
    // owner
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_owner=? where wf_owner=?';
    $this->query($query,array($new_user,$old_user));
    // next_user
    $query = 'update '.GALAXIA_TABLE_PREFIX.'instances set wf_next_user=? where wf_next_user=?';
    $this->query($query,array($new_user,$old_user));
    return true;
  }
  
  /*!
  * @public
  * Normalizes a property name
  * @param $name is the name you want to normalize
  * @return the property name
  */
  function normalize_name($name)
  {
    $name = trim($name);
    $name = str_replace(" ","_",$name);
    $name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
    return $name;
  }

}    

?>
