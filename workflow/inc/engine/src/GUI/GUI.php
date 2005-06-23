<?php
include_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
include_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'Process.php');
//!! GUI
//! A GUI class for use in typical user interface scripts
/*!
This class provides methods for use in typical user interface scripts
*/
class GUI extends Base {

  var $processesConfig = Array();

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

  //! List start activities avaible for a given user
  function gui_list_user_start_activities($user,$offset,$maxRecords,$sort_mode,$find,$where='')
  {
    // FIXME: this doesn't support multiple sort criteria
    $sort_mode = str_replace("__"," ",$sort_mode);

    $mid = "where gp.wf_is_active=? and ga.wf_type=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";

    $bindvars = array('y','start',$user);
    if($find)
    {
      //search on activities and processes
      $findesc = '%'.$find.'%';
      $mid .= " and ((ga.wf_name like ?) or (ga.wf_description like ?) or (gp.wf_name like ?) or (gp.wf_description like ?))";
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
      $bindvars[] = $findesc;
    }
    if($where) 
    {
      $mid.= " and ($where) ";
    }

    $query = "select distinct(ga.wf_activity_id), 
                              ga.wf_name,
                              ga.wf_is_interactive,
                              ga.wf_is_autorouted,
                              gp.wf_p_id,
                              gp.wf_name as wf_procname,
                              gp.wf_version
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
	$mid";
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $ret = Array();
    while($res = $result->fetchRow()) 
    {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"]= $ret;
    $retval["cant"]= $this->getOne($query_cant,$bindvars);
    
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
  
  //! gets all informations about a given instance and a given user, list activities and status
  /*!
  result is an associative array containing :
  ['instance'] =>
      ['instance_id'], ['instance_status'], ['owner'], ['started'], ['ended'], ['priority'], ['instance_name'], 
      ['process_name'], ['process_version'], ['process_id']
  ['activities'] =>
      ['activity'] =>
          ['user']		: actual user
          ['id']		: activity Id
          ['name']
          ['type']
          ['is_interactive']	: 'y' or 'n'
          ['is_autorouted']	: 'y' or 'n'
          ['status']
  We list activities for which the user is the owner or the actual user or in a role giving him access to the activity
  $add_[completed/exception/aborted]_instances args can give you some results on non interactive instances.
  */
  function gui_get_user_instance_status($user,$instance_id, $add_completed_instances=false,$add_exception_instances=false, $add_aborted_instances=false)
  {
    $mid = "where gp.wf_is_active=?";
    // instance selection
    $mid .= " and (gi.wf_instance_id=?)";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and (  ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";
    // this collect non interactive instances we are owner of
    $mid .= " 	or (gi.wf_owner=?)"; 
    // and this collect completed instances when asked which haven't got any user anymore
    if ($add_completed_instances)
    {
      $mid .= "   or (gur.wf_user is NULL) )";
    }
    
    $bindvars = array('y',$instance_id,$user,$user);
    
    // we need LEFT JOIN because aborted and completed instances are not showned 
    // in instance_activities, they're only in instances
    $query = "select distinct(gi.wf_instance_id) as instance_id,
                     gi.wf_status as instance_status,
                     gi.wf_owner as owner,
                     gi.wf_started as started,
                     gi.wf_ended as ended,
                     gi.wf_priority as priority,
                     gi.wf_name as instance_name,
                     gp.wf_name as process_name,
                     gp.wf_version as process_version,
                     gp.wf_p_id as process_id,
                     gia.wf_user as user,
                     ga.wf_activity_id as id,
                     ga.wf_name as name,
                     ga.wf_type as type,
                     ga.wf_is_interactive as is_interactive,
                     ga.wf_is_autorouted as is_autorouted,
                     gia.wf_status as status
              from ".GALAXIA_TABLE_PREFIX."instances gi
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gia.wf_activity_id = ga.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."processes gp ON gp.wf_p_id=gi.wf_p_id
              $mid";
    $result = $this->query($query,$bindvars);
    $retinst = Array();
    $retacts = Array();
    while($res = $result->fetchRow()) 
    {
      // Get instances per activity
      if (count($retinst)==0)
      {//the first time we retain instance data
        $retinst[] = array_slice($res,0,-7);
      }
      $retacts[] = array_slice($res,10);
    }
    $retval = Array();
    $retval["instance"] = $retinst{0};
    $retval["activities"] = $retacts;
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
  
  //! Return avaible actions for a given user on a given activity and a given instance assuming he already have access to it.
  /*!
  To be able to decide this function needs the user id, instance_id and activity_id. 
  All other datas can be retrieved by internal queries BUT if you want this function to be fast and if you already 
  have theses datas you should give as well theses fields: 
  process id, activity type,  activity interactivity (y/n), activity routage (y/n), activity status, instance owner, 
  instance status and  finally the current user of this activity.
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
  WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via this
  GUI object theses access rights are allready checked.
  */
  function getUserActions($user, $instanceId, $activityId, $pId=0, $actType='not_set', $actInteractive='not_set', $actAutorouted='not_set', $actStatus='not_set', $instanceOwner=0, $instanceStatus='not_set', $currentUser='not_set') 
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

    //check if we have all the args and retrieve the ones whe did not have:
    if ((!($pId)) ||
      ($actType=='not_set') || 
      ($actInteractive=='not_set') || 
      ($actAutorouted=='not_set') || 
      ($actStatus=='not_set') ||
      (!($instanceOwner)) ||
      ($currentUser=='not_set') ||
      ($instanceStatus=='not_set'))
    {
      // get process_id, type, interactivity, autorouting and act status and others for this instance
      // we retrieve info even if ended or in exception but not for aborted instances
      $array_info = $this->gui_get_user_instance_status($user,$instanceId,true,true);
      
      //now set our needed values
      $instance = $array_info['instance'];
      $pId = $instance['instance_id'];
      $instanceStatus = $instance['instance_status'];
      $instanceOwner = $instance['owner'];
      
      $find=false;
      foreach ($array_info['activities'] as $activity)
      {
      //_debug_array($activity);
      //echo "<br> ==>".$activity['id']." : ".$activityId;
        if ((int)$activity['id']==(int)$activityId)
        {
          $actType = $activity['type'];
          $actInteractive = $activity['is_interactive'];
          $actAutorouted = $activity['is_autorouted'];
          $actstatus = $activity['status'];
          $currentUser = $activity['user'];
          $find = true;
          break;
        }
      }
      //if the activity_id can't be find we return emty actions
      if (!($find)) return $result;
    }
    
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

