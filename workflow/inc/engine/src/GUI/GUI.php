<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Base.php');
//!! GUI
//! A GUI class for use in typical user interface scripts
/*!
This class provides methods for use in typical user interface scripts
*/
class GUI extends Base {

  //security object used to obtain access for the user on certain actions from the engine
  var $wf_security;

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
  function gui_list_user_activities($user,$offset,$maxRecords,$sort_mode,$find,$where='', $remove_activities_without_instances=false)
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
    if ($remove_activities_without_instances)
    {
      $more_tables = "INNER JOIN ".GALAXIA_TABLE_PREFIX."instance_activities gia ON gia.wf_activity_id=gar.wf_activity_id
                      INNER JOIN ".GALAXIA_TABLE_PREFIX."instances gi ON gia.wf_instance_id=gi.wf_instance_id";
    }
    else
    {
	$more_tables = "";
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
                $more_tables
                $mid order by $sort_mode";
              
    $query_cant = "select count(distinct(ga.wf_activity_id))
              from ".GALAXIA_TABLE_PREFIX."processes gp
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
                INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
                $more_tables
                $mid ";
    $result = $this->query($query,$bindvars,$maxRecords,$offset);
    $cant = $this->getOne($query_cant,$bindvars);
    $ret = Array();
    $removed_instances = 0;
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
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        $ret[] = $res;
      }
    }
    $retval = Array();
    $retval["data"]= $ret;
    $retval["cant"]= $this->getOne($query_cant,$bindvars);
    
    return $retval;
  }

  function gui_list_user_instances($user,$offset,$maxRecords,$sort_mode,$find,$where='',$add_properties=false)
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
    $query = 'select distinct(gi.wf_instance_id),                     
                     gi.wf_started,
                     gi.wf_owner,
                     gia.wf_user,
                     gi.wf_status,
                     gi.wf_category,
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
                     gi.wf_priority';
    $query .= ($add_properties)? ', gi.wf_properties' : '';
    $query .= ' from '.GALAXIA_TABLE_PREFIX."instances gi 
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
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        // Get instances per activity
        $ret[] = $res;        
      }
    }
    $cant=0;
    if (!(empty($resultcant)))
    {
      while($rescant = $resultcant->fetchRow()) 
      {
        // Get number of distinct instances per activity
        $cant += $rescant['cant'];
      }
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
  
  //! get the view activity id avaible for a given user and a given process
  function gui_get_process_user_view_activity($pId, $user)
  {
    $mid = "where gp.wf_is_active=? and gp.wf_p_id=? and ga.wf_type=?";
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= " and ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";
    $bindvars = array('y',$pId,'view',$user);

    $query = "select ga.wf_activity_id
        from ".GALAXIA_TABLE_PREFIX."processes gp
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activities ga ON gp.wf_p_id=ga.wf_p_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."activity_roles gar ON gar.wf_activity_id=ga.wf_activity_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."roles gr ON gr.wf_role_id=gar.wf_role_id
	INNER JOIN ".GALAXIA_TABLE_PREFIX."user_roles gur ON gur.wf_role_id=gr.wf_role_id
	$mid";
    $result = $this->query($query,$bindvars);
    $ret = Array();
    $retval = false;
    if (!(empty($result)))
    {
      while($res = $result->fetchRow()) 
      {
        $retval = $res['wf_activity_id'];
      }
    }
    return $retval;
  }

  //! gets all informations about a given instance and a given user, list activities and status
  /*!
  * We list activities for which the user is the owner or the actual user or in a role giving him access to the activity
  * notice that completed and aborted instances aren't associated with activities and that start and standalone activities
  * aren't associated with an instance ==> if instanceId is 0 you'll get all standalone and start activities in the result.
  * this is the reason why you can give --if you have it-- the process id, to restrict results to start and standalone
  * activities to this process.
  * @param $user is the user id
  * @param $instance_id is the instance id
  * @param $pId is the process id, 0 by default, in such case it is ignored
  * @param $add_completed_instances false by default, if true we add completed instances in the result
  * @param $add_exception_instances false by default, if true we add instances in exception in the result
  * @param $add_aborted_instances false by default, if true we add aborted instances in the result
  * @return an associative array containing :
  * ['instance'] =>
  *     ['instance_id'], ['instance_status'], ['owner'], ['started'], ['ended'], ['priority'], ['instance_name'], 
  *    ['process_name'], ['process_version'], ['process_id']
  * ['activities'] =>
  *     ['activity'] =>
  *         ['user']		: actual user
  *         ['id']		: activity Id
  *         ['name']
  *         ['type']
  *         ['is_interactive']	: 'y' or 'n'
  *         ['is_autorouted']	: 'y' or 'n'
  *         ['status']
  */
  function gui_get_user_instance_status($user,$instance_id, $pId=0, $add_completed_instances=false,$add_exception_instances=false, $add_aborted_instances=false)
  {
    $bindvars =Array();
    $mid = "\n where gp.wf_is_active=?";
    $bindvars[] = 'y';
    if (!($pId==0))
    {
      // process restriction
      $mid.= " and gp.wf_p_id=?";
      $bindvars[] = $pId;
    }
    if (!($instance_id==0))
    {
      // instance selection
      $mid .= " and (gi.wf_instance_id=?)";
      $bindvars[] = $instance_id;
      $statuslist[]='active';
      if ($add_exception_instances) $statuslist[]='exception';
      if ($add_aborted_instances) $statuslist[]='aborted';
      if ($add_completed_instances) $statuslist[]='completed';
      $status_list = implode ($statuslist,',');
      $mid .= " and (gi.wf_status in ('".implode ("','",$statuslist)."'))\n";
    }
    else
    {
      // collect NULL instances for start and standalone activities
      $mid .= " and (gi.wf_instance_id is NULL)";
    }
    // add group mapping, warning groups and user can have the same id
    $groups = galaxia_retrieve_user_groups($user);
    $mid .= "\n and (  ((gur.wf_user=? and gur.wf_account_type='u')";
    $mid .= "		or (gur.wf_user in (".implode(",",$groups).") and gur.wf_account_type='g'))";
    $bindvars[] = $user;
    // this collect non interactive instances we are owner of
    $mid .= "\n or (gi.wf_owner=?)"; 
    $bindvars[] = $user;
    // and this collect completed/aborted instances when asked which haven't got any user anymore
    if (($add_completed_instances) || ($add_aborted_instances))
    {
      $mid .= "\n or (gur.wf_user is NULL)";
    }
    $mid .= ")";
    
    // we need LEFT JOIN because aborted and completed instances are not showned 
    // in instance_activities, they're only in instances
    $query = 'select distinct(gi.wf_instance_id) as instance_id,
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
                     gia.wf_status as status';
    if ($instance_id==0)
    {//TODO: this gives all activities, rstrict to standalone and start
      $query.=' from '.GALAXIA_TABLE_PREFIX.'activities ga
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON ga.wf_activity_id=gia.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instances gi ON gia.wf_activity_id = gi.wf_instance_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=ga.wf_p_id '.$mid;
    }
    else
    {
      $query.=' from '.GALAXIA_TABLE_PREFIX.'instances gi
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'instance_activities gia ON gi.wf_instance_id=gia.wf_instance_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activities ga ON gia.wf_activity_id = ga.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'activity_roles gar ON gia.wf_activity_id=gar.wf_activity_id
                LEFT JOIN '.GALAXIA_TABLE_PREFIX.'user_roles gur ON gur.wf_role_id=gar.wf_role_id
                INNER JOIN '.GALAXIA_TABLE_PREFIX.'processes gp ON gp.wf_p_id=gi.wf_p_id '.$mid;
    }
    $result = $this->query($query,$bindvars);
    $retinst = Array();
    $retacts = Array();
    if (!!$result)
    {
      while($res = $result->fetchRow()) 
      {
        // Get instances per activity
        if (count($retinst)==0)
        {//the first time we retain instance data
          $retinst[] = array_slice($res,0,-7);
        }
        $retacts[] = array_slice($res,10);
      }
    }
    $retval = Array();
    $retval["instance"] = $retinst{0};
    $retval["activities"] = $retacts;
    return $retval;
  }
  
  //!Abort an instance - this terminates the instance with status 'aborted', and removes all running activities
  function gui_abort_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'abort')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $instance = new Instance($this->db);
      $instance->getInstance($instanceId);
      if (!empty($instance->instanceId)) 
      {
          $instance->abort($activityId,$user);
      }
      unset($instance);
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }
  
  //!Exception handling for an instance - this sets the instance status to 'exception', but keeps all running activities.
  /*!
  * The instance can be resumed afterwards via gui_resume_instance().
  */
  function gui_exception_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'exception')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?";
      $this->query($query, array('exception',$instanceId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  /*!
  Resume an instance - this sets the instance status from 'exception' back to 'active'
  */
  function gui_resume_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'resume')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instances
              set wf_status=?
              where wf_instance_id=?";
      $this->query($query, array('active',$instanceId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  
  function gui_send_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'send')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $instance =& new Instance($this->db);
      $instance->getInstance($instanceId);
      $instance->complete($activityId,false);
      // we force the continuation of the flow
      $instance->sendAutorouted($activityId,true);
      unset($instance);
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  
  function gui_release_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'release')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user = ? 
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array('*',$instanceId,$activityId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }
  
  //! grab the instance for this activity and user if the security object agreed
  function gui_grab_instance($user,$activityId,$instanceId)
  {
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    // start a transaction
    $this->db->StartTrans();
    if (!($this->wf_security->checkUserAction($activityId, $instanceId,'grab')))
    {
      $this->error[] = ($this->wf_security->get_error());
      $this->db->FailTrans();
    }
    else
    {
      //the security object said everything was fine
      $query = "update ".GALAXIA_TABLE_PREFIX."instance_activities
                set wf_user = ? 
                where wf_instance_id=? and wf_activity_id=?";
      $this->query($query, array($user,$instanceId,$activityId));
    }
    // perform commit (return true) or Rollback (return false) if Failtrans it will automatically rollback
    return $this->db->CompleteTrans();
  }

  
  //! Return avaible actions for a given user on a given activity and a given instance assuming he already have access to it.
  /*!
  * @public
  * To be able to decide this function needs the user id, instance_id and activity_id. 
  * @param $user must be the user id
  * @param $instanceId must be the instance id (can be 0 if you have no instance - for start or standalone activities)
  * @param $activityId must be the activity id (can be 0 if you have no activity - for aborted or completed instances)
  * All other datas can be retrieved by internal queries BUT if you want this function to be fast and if you already 
  * have theses datas you should give as well theses fields (all or none): 
  * @param $pId the process id
  * @param $actType is the activity type string ('split', 'activity', 'switch', etc.)
  * @param $actInteractive is the activity interactivity ('y' or 'n')
  * @param $actAutorouted is the activity routage ('y' or 'n')
  * @param $actStatus is tha activity status ('completed' or 'running')
  * @param $instanceOwner is the instance owner user id
  * @param $instanceStatus is the instance status ('completed', 'active', 'exception', 'aborted')
  * @param $currentUser is the actual user of the instance (user id or '*')
  * @return an array of this form:
  * array('action name' => 'action description')
  * 'actions names' are: 'grab', 'release', 'run', 'send', 'view', 'exception', 'resume' and 'monitor'
  * Some config values can change theses rules but basically here they are:
  * * 'grab'	: be the user of this activity. User has access to it and instance status is ok.
  * * 'release'	: let * be the user of this activity. Must be the actual user or the owner of the instance.
  * * 'run'	: run an associated form. This activity is interactive, user has access, instance status is ok.
  * * 'send'	: send this instance, activity was non-autorouted and he has access and status is ok.
  * * 'view'	: view the instance, activity ok, always avaible except for start or standalone act.
  * * 'abort'	: abort an instance, ok when we are the user
  * * 'exception' : set the instance status to exception, need to be the user 
  * * 'resume'	: back to running when instance status was exception, need to be the user
  * * 'monitor' : special user rights to administer the instance
  * 'actions description' are translated explanations like 'release access to this activity'
  * WARNING: this is a snapshot, the engine give you a snaphsots of the rights a user have on an instance-activity
  * at a given time, this is not meaning theses rights will still be there when the user launch the action.
  * You should absolutely use the GUI Object to execute theses actions (except monitor) and they could be rejected.
  * WARNING: we do not check the user access rights. If you launch this function for a list of instances obtained via this
  * GUI object theses access rights are allready checked.
  */
  function getUserActions($user, $instanceId, $activityId, $pId=0, $actType='not_set', $actInteractive='not_set', $actAutorouted='not_set', $actStatus='not_set', $instanceOwner='not_set', $instanceStatus='not_set', $currentUser='not_set') 
  {
    $result= array();//returned array

    //check if we have all the args and retrieve the ones whe did not have:
    if ((!($pId)) ||
      ($actType=='not_set') || 
      ($actInteractive=='not_set') || 
      ($actAutorouted=='not_set') || 
      ($actStatus=='not_set') ||
      ($instanceOwner=='not_set') ||
      ($currentUser=='not_set') ||
      ($instanceStatus=='not_set'))
    {
      // get process_id, type, interactivity, autorouting and act status and others for this instance
      // we retrieve info even if ended or in exception or aborted instances
      // and if $instanceId is 0 we get all standalone and start activities
      //echo '<br> call gui_get_user_instance_status:'.$pId.':'.$actType.':'.$actInteractive.':'.$actAutorouted.':'.$actStatus.':'.$instanceOwner.':'.$currentUser.':'.$instanceStatus;
      $array_info = $this->gui_get_user_instance_status($user,$instanceId,0,true,true,true);
      
      //now set our needed values
      $instance = $array_info['instance'];
      $pId = $instance['instance_id'];
      $instanceStatus = $instance['instance_status'];
      $instanceOwner = $instance['owner'];
      
      if (!((int)$activityId))
      {
        //we have no activity Id, like for aborted or completed instances, we set default values
        $actType = '';
        $actInteractive = 'n';
        $actAutorouted = 'n';
        $actstatus = '';
        $currentUser = 0;
      }
      else
      {
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
        //if the activity_id can't be find we return empty actions
        if (!($find))
        {
          return array();
        }
      }
    }
    
    //now use the security object to get actions avaible, this object know the rules
    if (!(isset($this->wf_security)))
    {
      $this->wf_security = new WfSecurity($this->db);
    }
    $result =& $this->wf_security->getUserActions($user, $instanceId, $activityId, $pId, $actType, $actInteractive, $actAutorouted, $actStatus, $instanceOwner, $instanceStatus, $currentUser);
    return $result;
  }

  
}
?>

