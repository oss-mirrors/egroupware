<?php
include_once(GALAXIA_LIBRARY.'/src/common/Base.php');
//!! GUI
//! A GUI class for use in typical user interface scripts
/*!
This class provides methods for use in typical user interface scripts
*/
class GUI extends Base {

  /*!
  List user processes, user processes should follow one of these conditions:
  1) The process has an instance assigned to the user
  2) The process has a begin activity with a role compatible to the
     user roles
  3) The process has an instance assigned to '*' and the
     roles for the activity match the roles assigned to
     the user
  The method returns the list of processes that match this
  and it also returns the number of instances that are in the
  process matching the conditions.
  */
  /*
  TODO: 
   *) more options in list_user_instances, they should not be added by the external modules
   *) still some group mappings
   */
  function gui_list_user_processes($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = "where gp.wf_is_active=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= "and ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "	or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";
    $bindvars = array('y',$user);
    if($find) {
      $findesc = '%'.$find.'%';
      $mid .= " and ((gp.wf_name like ?) or (gp.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) {
      $mid.= " and ($where) ";
    }
    
    $query = "select distinct(gp.wf_p_id), 
                     gp.wf_is_active,                    
                     gp.wf_name as wf_procname, 
                     gp.wf_normalized_name as normalized_name, 
                     gp.wf_version as wf_version,
                     gp.wf_version as version
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              $mid order by $sort_mode";
    $query_cant = "select count(distinct(gp.wf_p_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              $mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    while($res = $result->fetchRow()) {
      // Get instances and activities per process,
      $pId=$res['wf_p_id'];
      $res['wf_activities']=$this->getOne("select count(distinct(ga.wf_activity_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
              where gp.wf_p_id=? 
              and (  ((gur.wf_user=? and gur.wf_account_type='u')
                or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g')))",
              array($pId,$user));
      //we are counting here instances which are completed/exception or actives
      // TODO: maybe we should add a second counter with only running instances
      $res['wf_instances']=$this->getOne("select count(distinct(gi.wf_instance_id))
              from ".GALAXIA_TABLE_PREFIX."instances gi
                INNER JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gar.wf_role_id=gur.wf_role_id
              where gi.wf_p_id=? 
              and (
                   (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g')
                   or (gi.wf_owner=?) 
                   or ((gur.wf_user=?) and gur.wf_account_type='u')
                  )",
              array($pId,$user,$user));
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }

  /*
    $user is the real user id
  */
  function gui_list_user_activities($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);
    $mid = "where gp.wf_is_active=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "	or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";

    $bindvars = array('y',$user);
    if($find) {
      $findesc = '%'.$find.'%';
      $mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) {
      $mid.= " and ($where) ";
    }
    
    $query = "select distinct(ga.wf_activity_id),                     
                     ga.wf_name,
                     ga.wf_type,
                     gp.wf_name as wf_procname, 
                     ga.wf_is_interactive,
                     ga.wf_is_autorouted,
                     ga.wf_activity_id,
                     gp.wf_version as wf_version,
                     gp.wf_p_id,
                     gp.wf_is_active
                from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
                $mid order by $sort_mode";
              
    $query_cant = "select count(distinct(ga.wf_activity_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
                $mid ";
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    while($res = $result->fetchRow()) {
      // Get instances per activity
      $res['wf_instances']=$this->getOne("select count(distinct(gi.wf_instance_id))
              from ".GALAXIA_TABLE_PREFIX."instances gi
                INNER JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gar.wf_role_id=gur.wf_role_id
              where gia.wf_activity_id=? 
              and (
                   (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g')
                   or (gi.wf_owner=?) 
                   or ((gur.wf_user=?) and gur.wf_account_type='u')
                  )",
              array($res['wf_activity_id'],$user,$user));
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }

	function gui_list_user_activities_by_unique_name($user,$offset,$maxRecords,$sort_mode,$find,$where='')
	{
		// FIXME: this doesn't support multiple sort criteria
		//$sort_mode = $this->convert_sortmode($sort_mode);
		$sort_mode = str_replace("__"," ",$sort_mode);
		$mid = "where gp.wf_is_active=?";
		// add group mapping, warning groups and user can have the same id
		$groups = galaxia_retrieve_user_groups($user);
		$mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
		$mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";

		$bindvars = array('y',$user);
		if($find) 
		{
			$findesc = '%'.$find.'%';
			$mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?))";
			$bindvars[] = $findesc;
			$bindvars[] = $findesc;
		}
		if($where) 
		{
			$mid.= " and ($where) ";
		}

		$query = "select distinct(ga.wf_name)
			from ".GALAXIA_TABLE_PREFIX."processes gp
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
			$mid order by $sort_mode";

		$query_cant = "select count(distinct(ga.wf_name))
			from ".GALAXIA_TABLE_PREFIX."processes gp
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
			INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
			$mid";
		$result = $this->query($query,$bindvars,$maxRecords,$offset);
		$cant = $this->getOne($query_cant,$bindvars);
		$ret = Array();
		while($res = $result->fetchRow()) 
		{
			$ret[] = $res;
		}

		$retval = Array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}


  function gui_list_user_instances($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    //$sort_mode = $this->convert_sortmode($sort_mode);
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = "where gp.wf_is_active=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and (  ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";
    // this collect non interactive instances we are owner of
    $mid .= " 	or (gi.wf_owner=?)"; 
    // and this collect completed instances when asked which haven't got any user anymore
    $mid .= "   or (gur.wf_user is NULL) )";
    
    $bindvars = array('y',$user,$user); 
    
    if($find) {
      $findesc = '%'.$find.'%';
      $mid .= " and ( (upper(ga.wf_name) like upper(?))";
      $mid .= "       or (upper(ga.wf_description) like upper(?))";
      $mid .= "       or (upper(gi.wf_name) like upper(?)))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) {
      $mid.= " and ($where) ";
    }

    // (regis) we need LEFT JOIN because aborted and completed instances are not showned 
    // in instance_activities, they're only in instances
    $query = "select distinct(gi.wf_instance_id),                     
                     gi.wf_started,
                     gi.wf_owner,
                     gia.wf_user,
                     gi.wf_status,
                     gia.wf_status as wf_act_status,
                     ga.wf_name,
                     ga.wf_type,
                     gp.wf_name as wf_procname, 
                     ga.wf_is_interactive,
                     ga.wf_is_autorouted,
                     ga.wf_activity_id,
                     gp.wf_version as wf_version,
                     gp.wf_p_id,
                     gi.wf_name as insname,
                     gi.wf_priority
              from ".GALAXIA_TABLE_PREFIX."instances gi 
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gia.wf_activity_id = ga.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."processes gp ON gp.wf_p_id=gi.wf_p_id
              $mid order by $sort_mode";
    // (regis) this count query as to count global -unlimited- (instances/activities) not just instances
    // as we can have multiple activities for one instance and we will show all of them 
    // and the problem is that a user having memberships in several groups having the rights
    // is counted several times. If we count instance_id without distinct we'll have
    // several time the same line.
    // the solution is to count distinct instance_id for each activity and to sum theses results
    $query_cant = "select count(distinct(gi.wf_instance_id)) as cant, gia.wf_activity_id
              from ".GALAXIA_TABLE_PREFIX."instances gi 
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gia.wf_activity_id = ga.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."processes gp ON gp.wf_p_id=gi.wf_p_id
              $mid
                GROUP BY gia.wf_activity_id";
 
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $resultcant = $this->query($query_cant,$bindvars);
    $ret = Array();
    while($res = $result->fetchRow()) 
    {
      // Get instances per activity
      $ret[] = $res;
    }
    $cant=0;
    while($rescant = $resultcant->fetchRow()) 
    {
      // Get number of distinct instances per activity
      $cant += $rescant['cant'];
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
  
  //!Abort an instance - this terminates the instance with status 'aborted', and removes all running activities
  /*!
  Users can only abort instances they're currently running, or instances that they're the owner of
  */
  function gui_abort_instance($user,$activityId,$instanceId)
  {
    if(!$this->getOne("select count(*)
                       from ".GALAXIA_TABLE_PREFIX."instance_activities gia, ".GALAXIA_TABLE_PREFIX."instances gi
                       where gia.wf_instance_id=gi.wf_instance_id 
                       and wf_activity_id=? and gia.wf_instance_id=? 
                       and ((wf_user = ?) or (wf_owner = ?))",
                       array($activityId,$instanceId, $user, $user)))
    {
      return false;
    } 
    else 
    {
      include_once(GALAXIA_LIBRARY.'/src/API/Instance.php');
      $instance = new Instance($this->db);
      $instance->getInstance($instanceId);
      if (!empty($instance->instanceId)) 
      {
          $instance->abort($activityId,$user);
      }
      unset($instance);
      return true;
    }
  }
  
  //!Exception handling for an instance - this sets the instance status to 'exception', but keeps all running activities.
  /*!
  The instance can be resumed afterwards via gui_resume_instance().
  Users can only do exception handling for instances they're currently running, or instances that they're the owner of
  */
  function gui_exception_instance($user,$activityId,$instanceId)
  {
    
    if(!$this->getOne("select count(*)
                       from ".GALAXIA_TABLE_PREFIX."instance_activities gia, ".GALAXIA_TABLE_PREFIX."instances gi
                       where gia.wf_instance_id=gi.wf_instance_id and wf_activity_id=? and gia.wf_instance_id=? and (wf_user = ? or wf_owner = ?)",
                       array($activityId,$instanceId,$user,$user))) 
    {
      return false;
    } 
    else 
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?
              and wf_owner=?";
      $this->query($query, array('exception',$instanceId, $user));
      return true;
    }
  }

  /*!
  Resume an instance - this sets the instance status from 'exception' back to 'active'
  */
  function gui_resume_instance($user,$activityId,$instanceId)
  {
  //TODO group mapping
    // Users can only resume instances they're currently running, or instances that they're the owner of
    if(!$this->getOne("select count(*)
                       from ".GALAXIA_TABLE_PREFIX."instance_activities gia, ".GALAXIA_TABLE_PREFIX."instances gi
                       where gia.wf_instance_id=gi.wf_instance_id and wf_activity_id=? and gia.wf_instance_id=? and (wf_user in (".$user.") or wf_owner in (".$user."))",
                       array($activityId,$instanceId))) {
      return false;
    } 
    else 
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?";
      $this->query($query, array('active',$instanceId));
      return true;
    }
  }

  
  function gui_send_instance($user,$activityId,$instanceId)
  {
    // to check rights we try to see if we are setted as the current user
    // OR if the user is '*' we check for user/group mapping rights
    $groups = galaxia_retrieve_user_groups($user);
    if(!(
      ($this->getOne("select count(*)
                      from ".GALAXIA_TABLE_PREFIX."instance_activities gia
                      where wf_activity_id=? and wf_instance_id=? and wf_user=?",
                      array($activityId,$instanceId, $user)))
      ||
      ($this->getOne("select count(*) 
                      from ".GALAXIA_TABLE_PREFIX."instance_activities gia
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=gia.wf_activity_id
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gar.wf_role_id=gur.wf_role_id
                      where gia.wf_instance_id=? and gia.wf_activity_id=?
                      and gia.wf_user='*'
                      and ( (gur.wf_user=? and gur.wf_account_type='u')
                        or  (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))",
                      array($instanceId,$activityId,$user))))
                    ) 
    { 
      return false;
    } 
    else 
    {
      include_once(GALAXIA_LIBRARY.'/src/API/Instance.php');
      $instance =& new Instance($this->db);
      $instance->getInstance($instanceId);
      $instance->complete($activityId,false);
      // we force the continuation of the flow
      $instance->sendAutorouted($activityId,true);
      unset($instance);
      return true;
    }
  }
  
  function gui_release_instance($user,$activityId,$instanceId)
  {
   // release an instance if we are the current user
    if(!$this->getOne("select count(*)
                       from ".GALAXIA_TABLE_PREFIX."instance_activities
                       where wf_activity_id=? and wf_instance_id=? and wf_user =?",
                       array($activityId,$instanceId,$user))){
      return false;
    } 
    else 
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user='*'
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array($instanceId,$activityId));
      return true;
    }
  }
  
  function gui_grab_instance($user,$activityId,$instanceId)
  {
    // Grab is ok if we are already the user or if the user is * and we've got the role
    // we check as well if we are in a group which has the role
    $groups = galaxia_retrieve_user_groups($user);
    if(!$this->getOne("select count(*) 
                      from ".GALAXIA_TABLE_PREFIX."instance_activities gia
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=gia.wf_activity_id
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gar.wf_role_id=gur.wf_role_id
                      where gia.wf_instance_id=? 
                      and gia.wf_activity_id=? 
                      and (
                            (gia.wf_user=?) 
                            or (
                                (gia.wf_user='*') 
                                and (
                                     (gur.wf_user=? and gur.wf_account_type='u')
                                     or  (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g')
                                )
                            )
                      )
                      ",
                      array($instanceId,$activityId,$user, $user))) 
    {
      return false;
    } 
    else
    {
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user = ? 
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array($user,$instanceId,$activityId));
      return true;
    }
  }
}
?>

