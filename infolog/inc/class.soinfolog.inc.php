<?php
/**
 * InfoLog - Storage object
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package infolog
 * @copyright (c) 2003-6 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

include_once(EGW_API_INC.'/class.solink.inc.php');

/**
 * storage object / db-layer for InfoLog
 *
 * all values passed to this class are run either through intval or addslashes to prevent query-insertion
 * and for pgSql 7.3 compatibility
 *
 * @package infolog
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright (c) by Ralf Becker <RalfBecker@outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class soinfolog 				// DB-Layer
{
	/**
	 * Instance of the db class
	 *
	 * @var egw_db
	 */
	var $db;
	/**
	 * Instance of the solink class
	 *
	 * @var solink
	 */
	var $links;
	/**
	 * Grants from other users
	 *
	 * @var array
	 */
	var $grants;
	/**
	 * Internal data array
	 *
	 * @var array
	 */
	var $data = array( );
	/**
	 * Current user (account_id)
	 *
	 * @var int
	 */
	var $user;
	/**
	 * Infolog table-name
	 *
	 * @var string
	 */
	var $info_table = 'egw_infolog';
	/**
	 * Infolog custom fileds table-name
	 *
	 * @var string
	 */
	var $extra_table = 'egw_infolog_extra';
	/**
	 * Offset between server- and user-time in h
	 *
	 * @var int
	 */
	var $tz_offset;
	
	/**
	 * Constructor
	 *
	 * @param array $grants
	 * @return soinfolog
	 */
	function soinfolog( &$grants )
	{
		$this->db     = clone($GLOBALS['egw']->db);
		$this->db->set_app('infolog');
		$this->grants =& $grants;
		$this->user   = $GLOBALS['egw_info']['user']['account_id'];

		$this->links =& new solink();

		$this->tz_offset = $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'];
	}

	/**
	 * Check if use is responsible for an entry: he or one of his memberships is in responsible
	 *
	 * @param array $info infolog entry as array
	 * @return boolean
	 */
	function is_responsible($info)
	{
		static $user_and_memberships;
		if (is_null($user_and_memberships))
		{
			$user_and_memberships = $GLOBALS['egw']->accounts->memberships($this->user,true);
			$user_and_memberships[] = $this->user;
		}
		return $info['info_responsible'] && array_intersect($info['info_responsible'],$user_and_memberships);
	}

	/**
	 * checks if user has the $required_rights to access $info_id (private access is handled too)
	 *
	 * @param array/int $info data or info_id of InfoLog entry
	 * @param int $required_rights EGW_ACL_xyz anded together
	 * @param boolean $implicit_edit=false responsible has only implicit read and add rigths, unless this is set to true
	 * @return boolean True if access is granted else False
	 */
	function check_access( $info,$required_rights,$implicit_edit=false )
	{
		if (is_array($info))
		{
			
		}
		elseif ((int) $info != $this->data['info_id'])      	// already loaded?
		{
			// dont change our own internal data,
			// dont use new as it changes $phpgw->db
			$private_info = $this;                      
			$info = $private_info->read($info);
		}
		else
		{
			$info = $this->data;
		}
		if (!$info)
		{
			return False;
		}
		$owner = $info['info_owner'];
		
		$access_ok = $owner == $this->user ||	// user has all rights
			// ACL only on public entrys || $owner granted _PRIVATE
			(!!($this->grants[$owner] & $required_rights) ||
			$this->is_responsible($info) &&			// implicite rights for responsible user(s) and his memberships
			($required_rights == EGW_ACL_READ || $required_rights == EGW_ACL_ADD || $implicit_edit && $required_rights == EGW_ACL_EDIT)) &&
			($info['info_access'] == 'public' || !!($this->grants[$owner] & EGW_ACL_PRIVATE));

		//echo "<p align=right>check_access(info_id=$info_id,requited=$required_rights,implicit_edit=$implicit_edit) owner=$owner, responsible=(".implode(',',$info['info_responsible'])."): access".($access_ok?"Ok":"Denied")."</p>\n";
		return $access_ok;
	}
	
	/**
	 * Filter for a given responsible user: info_responsible either contains a the user or one of his memberships
	 *
	 * @param int $user
	 * @return string
	 * 
	 * @todo make the responsible a second table and that filter a join with the responsible table
	 */
	function responsible_filter($user)
	{
		if (!$user) return '0';

		$responsible = $user > 0 ? $GLOBALS['egw']->accounts->memberships($user,true) : 
			$GLOBALS['egw']->accounts->members($user,true);

		$responsible[] = $user;
		foreach($responsible as $key => $uid)
		{
			$responsible[$key] = $this->db->concat("','",'info_responsible',"','")." LIKE '%,$uid,%'";
		}
		//echo "<p align=right>responsible_filter($user) = ".'('.implode(' OR ',$responsible).')'."</p>\n";
		return '('.implode(' OR ',$responsible).')';
	}

	/**
	 * generate sql to be AND'ed into a query to ensure ACL is respected (incl. _PRIVATE)
	 *
	 * @param string $filter: none|all - list all entrys user have rights to see<br>
	 * 	private|own - list only his personal entrys (incl. those he is responsible for !!!), 
	 *  responsible|my = entries the user is responsible for 
	 *  delegated = entries the user delegated to someone else
	 * @return string the necesary sql
	 */
	function aclFilter($filter = False)
	{
		preg_match('/(my|responsible|delegated|own|privat|all|none|user)([0-9]*)/',$filter_was=$filter,$vars);
		$filter = $vars[1];
		$f_user   = intval($vars[2]);

		if (isset($this->acl_filter[$filter.$f_user]))
		{
			return $this->acl_filter[$filter.$f_user];  // used cached filter if found
		}

		$filtermethod = " (info_owner=$this->user"; // user has all rights
		
		if ($filter == 'my' || $filter == 'responsible')
		{
			$filtermethod .= " AND info_responsible='0'";
		}
		if ($filter == 'delegated')
		{
			$filtermethod .= " AND info_responsible<>'0')";
		}
		else
		{
			if (is_array($this->grants))
			{
				foreach($this->grants as $user => $grant)
				{
					// echo "<p>grants: user=$user, grant=$grant</p>";
					if ($grant & (EGW_ACL_READ|EGW_ACL_EDIT))
					{
						$public_user_list[] = $user;
					}
					if ($grant & EGW_ACL_PRIVATE)
					{
						$private_user_list[] = $user;
					}
				}
				if (count($private_user_list))
				{
					$has_private_access = 'info_owner IN ('.implode(',',$private_user_list).')';
				}
			}
			// implicit read-rights for responsible user
			$filtermethod .= " OR (".$this->responsible_filter($this->user)." AND info_access='public')";

			// private: own entries plus the one user is responsible for
			if ($filter == 'private' || $filter == 'own')
			{
				$filtermethod .= " OR (".$this->responsible_filter($this->user).
					($filter == 'own' && count($public_user_list) ?	// offer's should show up in own, eg. startpage, but need read-access
						" OR info_status = 'offer' AND info_owner IN(" . implode(',',$public_user_list) . ')' : '').")".
				                 " AND (info_access='public'".($has_private_access?" OR $has_private_access":'').')';
			}
			elseif ($filter != 'my' && $filter != 'responsible')	// none --> all entrys user has rights to see
			{
				if ($has_private_access)
				{
					$filtermethod .= " OR $has_private_access";
				}
				if (count($public_user_list))
				{
					$filtermethod .= " OR (info_access='public' AND info_owner IN(" . implode(',',$public_user_list) . '))';
				}
			}
			$filtermethod .= ') ';
	
			if ($filter == 'user' && $f_user > 0)
			{
				$filtermethod .= " AND (info_owner=$f_user AND info_responsible=0 OR ".$this->responsible_filter($f_user).')';
			}
		}
		//echo "<p>aclFilter(filter='$filter_was',user='$user') = '$filtermethod', privat_user_list=".print_r($privat_user_list,True).", public_user_list=".print_r($public_user_list,True)."</p>\n";
		return $this->acl_filter[$filter.$f_user] = $filtermethod;  // cache the filter
	}

	/**
	 * generate sql to filter based on the status of the log-entry
	 *
	 * @param string $filter done = done or billed, open = not (done, billed, cancelled or deleted), offer = offer
	 * @param boolean $prefix_and=true if true prefix the fileter with ' AND '
	 * @return string the necesary sql
	 */
	function statusFilter($filter = '',$prefix_and=true)
	{
		preg_match('/(done|open|offer|deleted)/',$filter,$vars);
		$filter = $vars[1];

		switch ($filter)
		{
			case 'done':	$filter = "info_status IN ('done','billed','cancelled')"; break;
			case 'open':	$filter = "NOT (info_status IN ('done','billed','cancelled','deleted'))"; break;
			case 'offer':	$filter = "info_status = 'offer'";    break;
			case 'deleted': $filter = "info_status = 'deleted'";  break;
			default:        $filter = "info_status <> 'deleted'"; break;
		}
		return ($prefix_and ? ' AND ' : '').$filter;
	}

	/**
	 * generate sql to filter based on the start- and enddate of the log-entry
	 *
	 * @param string $filter upcoming = startdate is in the future
	 * 	today: startdate < tomorrow
	 * 	overdue: enddate < tomorrow
	 *  date: today <= startdate && startdate < tomorrow
	 *  enddate: today <= enddate && enddate < tomorrow
	 * 	limitYYYY/MM/DD not older or open 
	 * @return string the necesary sql
	 */
	function dateFilter($filter = '')
	{
		preg_match('/(upcoming|today|overdue|date|enddate)([-\\/.0-9]*)/',$filter,$vars);
		$filter = $vars[1];

		if (isset($vars[2]) && !empty($vars[2]) && ($date = split('[-/.]',$vars[2])))
		{
			$today = mktime(-$this->tz_offset,0,0,intval($date[1]),intval($date[2]),intval($date[0]));
			$tomorrow = mktime(-$this->tz_offset,0,0,intval($date[1]),intval($date[2])+1,intval($date[0]));
		}
		else
		{
			$now = getdate(time()-60*60*$this->tz_offset);
			$tomorrow = mktime(-$this->tz_offset,0,0,$now['mon'],$now['mday']+1,$now['year']);
		}
		switch ($filter)
		{
			case 'upcoming':
				return " AND info_startdate >= '$tomorrow'";
			case 'today':
				return " AND info_startdate < '$tomorrow'";
			case 'overdue':
				return " AND (info_enddate != 0 AND info_enddate < '$tomorrow')";
			case 'date':
				if (!$today || !$tomorrow)
				{
					return '';
				}
				return " AND ($today <= info_startdate AND info_startdate < $tomorrow)";
			case 'enddate':
				if (!$today || !$tomorrow)
				{
					return '';
				}
				return " AND ($today <= info_enddate AND info_enddate < $tomorrow)";
			case 'limit':
				return " AND (info_modified >= '$today' OR NOT (info_status IN ('done','billed','cancelled')))";
		}
		return '';
	}

	/**
	 * initialise the internal $this->data to be empty
	 *
	 * only non-empty values got initialised
	 */
	function init()
	{
		$this->data = array(
			'info_owner'    => $this->user,
			'info_priority' => 1,
			'info_responsible' => array(),
		);
	}

	/**
	 * read InfoLog entry $info_id
	 *
	 * some cacheing is done to prevent multiple reads of the same entry
	 *
	 * @param $info_id id of log-entry
	 * @return array/boolean the entry as array or False on error (eg. entry not found)
	 */
	function read($info_id)		// did _not_ ensure ACL
	{
		$info_id = (int) $info_id;
		
		if ($info_id && $info_id == $this->data['info_id'])
		{
			return $this->data;		// return the already read entry
		}
		if ($info_id <= 0 || !$this->db->select($this->info_table,'*',array('info_id'=>$info_id),__LINE__,__FILE__) ||
			 !(($this->data = $this->db->row(true))))
		{
			$this->init( );
			return False;
		}
		if (!is_array($this->data['info_responsible']))
		{
			$this->data['info_responsible'] = $this->data['info_responsible'] ? explode(',',$this->data['info_responsible']) : array();
		}
		$this->db->select($this->extra_table,'info_extra_name,info_extra_value',array('info_id'=>$info_id),__LINE__,__FILE__);
		while ($this->db->next_record())
		{
			$this->data['#'.$this->db->f(0)] = $this->db->f(1);
		}
		return $this->data;
	}
	
	/**
	 * Read the status of the given infolog-ids
	 *
	 * @param array $ids array with id's
	 * @return array with id => status pairs
	 */
	function get_status($ids)
	{
		$this->db->select($this->info_table,'info_id,info_type,info_status,info_percent',array('info_id'=>$ids),__LINE__,__FILE__);
		while (($info = $this->db->row(true)))
		{
			switch ($info['info_type'].'-'.$info['info_status'])
			{
				case 'phone-not-started':
					$status = 'call';
					break;
				case 'phone-ongoing':
					$status = 'will-call';
					break;
				default:
					$status = $info['info_status'] == 'ongoing' ? $info['info_percent'].'%' : $info['info_status'];
			}
			$stati[$info['info_id']] = $status;
		}
		return $stati;
	}	

	/**
	 * delete InfoLog entry $info_id AND the links to it
	 *
	 * @param int $info_id id of log-entry
	 * @param bool $delete_children delete the children, if not set there parent-id to $new_parent
	 * @param int $new_parent new parent-id to set for subs
	 */
	function delete($info_id,$delete_children=True,$new_parent=0)  // did _not_ ensure ACL
	{
		//echo "<p>soinfolog::delete($info_id,'$delete_children',$new_parent)</p>\n";
		if ((int) $info_id <= 0)
		{
			return;
		}
		$this->db->delete($this->info_table,array('info_id'=>$info_id),__LINE__,__FILE__);
		$this->db->delete($this->extra_table,array('info_id'=>$info_id),__LINE__,__FILE__);
		$this->links->unlink(0,'infolog',$info_id);

		if ($this->data['info_id'] == $info_id)
		{
			$this->init( );            
		}
		// delete children, if they are owned by the user
		if ($delete_children)
		{
			$db2 = clone($this->db);	// we need an extra result-set
			$db2->select($this->info_table,'info_id',array(
					'info_id_parent'	=> $info_id,
					'info_owner'		=> $this->user,
				),__LINE__,__FILE__);
			while ($db2->next_record())
			{
				$this->delete($db2->f(0),$delete_children);
			}
		}
		// set parent_id to $new_parent or 0 for all not deleted children
		$this->db->update($this->info_table,array('info_id_parent'=>$new_parent),array('info_id_parent'=>$info_id),__LINE__,__FILE__);
	}
	
	/**
	 * Return array with children of $info_id as info_id => info_owner pairs
	 *
	 * @param int $info_id
	 * @return array with info_id => info_owner pairs
	 */
	function get_children($info_id)
	{
		$this->db->select($this->info_table,'info_id,info_owner',array(
			'info_id_parent'	=> $info_id,
		),__LINE__,__FILE__);
		
		$children = array();
		while (($row = $this->db->row(true)))
		{
			$children[$row['info_id']] = $row['info_owner'];
		}
		return $children;
	}

	/**
	 * changes or deletes entries with a spezified owner (for hook_delete_account)
	 *
	 * @param $owner old owner
	 * @param $new_owner new owner or 0 if entries should be deleted
	 */
	function change_delete_owner($owner,$new_owner=0)  // new_owner=0 means delete
	{
		if (!(int) $new_owner)
		{
			$db2 = clone($this->db);	// we need an extra result-set
			$db2->select($this->info_table,'info_id',array('info_owner'=>$owner),__LINE__,__FILE__);
			while($db2->next_record())
			{
				$this->delete($this->db->f(0),False);
			}
		}
		else
		{
			$this->db->update($this->info_table,array('info_owner'=>$new_owner),array('info_owner'=>$owner),__LINE__,__FILE__);
		}
		// ToDo: does not work with multiple owners!!!
		$this->db->update($this->info_table,array('info_responsible'=>$new_owner),array('info_responsible'=>$owner),__LINE__,__FILE__);
	}

	/**
	 * writes the given $values to InfoLog, a new entry gets created if info_id is not set or 0
	 *
	 * @param array $values with the data of the log-entry
	 * @param int $check_modified=0 old modification date to check before update (include in WHERE)
	 * @return int/boolean info_id, false on error or 0 if the entry has been updated in the meantime
	 */
	function write($values,$check_modified=0)  // did _not_ ensure ACL
	{
		//echo "soinfolog::write(,$check_modified) values="; _debug_array($values);
		$info_id = (int) $values['info_id'];

		if (array_key_exists('info_responsible',$values))	// isset($values['info_responsible']) returns false for NULL!
		{
			$values['info_responsible'] = $values['info_responsible'] ? implode(',',$values['info_responsible']) : '0';
		}
		$table_def = $this->db->get_table_definitions('infolog',$this->info_table);
		$to_write = array();
		foreach($values as $key => $val)
		{
			if ($key != 'info_id' && isset($table_def['fd'][$key]))
			{
				$to_write[$key] = $this->data[$key] = $val;   // update internal data
			}
		}
		// writing no price as SQL NULL (required by postgres)
		if ($to_write['info_price'] === '') $to_write['info_price'] = NULL;

		if (($this->data['info_id'] = $info_id))
		{
			$where = array('info_id' => $info_id);
			if ($check_modified) $where['info_datemodified'] = $check_modified;
			if (!$this->db->update($this->info_table,$to_write,$where,__LINE__,__FILE__))
			{
				return false;	// Error
			}
			if ($this->db->affected_rows() < 1) return 0;	// someone else updated the modtime or deleted the entry
		}
		else
		{
			if (!isset($to_write['info_id_parent'])) $to_write['info_id_parent'] = 0;	// must not be null

			$this->db->insert($this->info_table,$to_write,false,__LINE__,__FILE__);
			$info_id = $this->data['info_id'] = $this->db->get_last_insert_id($this->info_table,'info_id');
		}
		//echo "<p>soinfolog.write values= "; _debug_array($values);

		// write customfields now
		foreach($values as $key => $val)
		{
			if ($key[0] != '#')
			{
				continue;	// no customfield
			}
			$this->data[$key] = $val;	// update internal data

			$this->db->insert($this->extra_table,array(
					'info_extra_value'=>$val
				),array(
					'info_id'			=> $info_id,
					'info_extra_name'	=> substr($key,1),
				),__LINE__,__FILE__);
		}
		// echo "<p>soinfolog.write this->data= "; _debug_array($this->data);

		return $this->data['info_id'];
	}

	/**
	 * count the sub-entries of $info_id
	 *
	 * This is done now be search too (in key info_anz_subs), if DB can use sub-queries
	 *
	 * @param $info_id id of log-entry
	 * @return int the number of sub-entries
	 */
	function anzSubs( $info_id )
	{
		if (($info_id = intval($info_id)) <= 0)
		{
			return 0;
		}
		$this->db->select($this->info_table,'count(*)',array(
				'info_id_parent' => $info_id,
				$this->aclFilter()
			),__LINE__,__FILE__);

		$this->db->next_record();
		//echo "<p>anzSubs($info_id) = ".$this->db->f(0)." ($sql)</p>\n";
		return $this->db->f(0);
	}
	
	/**
	 * searches InfoLog for a certain pattern in $query
	 *
	 * If DB can use sub-queries, the number of subs are under the key info_anz_subs.
	 *
	 * @param $query[order] column-name to sort after
	 * @param $query[sort] sort-order DESC or ASC
	 * @param $query[filter] string with combination of acl-, date- and status-filters, eg. 'own-open-today' or ''
	 * @param $query[cat_id] category to use or 0 or unset
	 * @param $query[search] pattern to search, search is done in info_from, info_subject and info_des
	 * @param $query[action] / $query[action_id] if only entries linked to a specified app/entry show be used
	 * @param &$query[start], &$query[total] nextmatch-parameters will be used and set if query returns less entries
	 * @param $query[col_filter] array with column-name - data pairs, data == '' means no filter (!)
	 * @param $query[subs] boolean return subs or not, if unset the user preference is used
	 * @param $query[num_rows] number of rows to return if $query[start] is set, default is to use the value from the general prefs
	 * @return array with id's as key of the matching log-entries
	 */
	function search(&$query)
	{
		//echo "<p>soinfolog.search(".print_r($query,True).")</p>\n";
		$action2app = array(
			'addr'        => 'addressbook',
			'proj'        => 'projects',
			'event'       => 'calendar'
		);
		$action = isset($action2app[$query['action']]) ? $action2app[$query['action']] : $query['action'];
		$action_id = ( strpos($query['action_id'],',')!==false) ? explode(',',$query['action_id']) : $query['action_id'];

		if ($action != '')
		{
			$links = $this->links->get_links($action=='sp'?'infolog':$action,$action_id,'infolog');

			if (count($links))
			{
				$link_extra = ($action == 'sp' ? 'OR' : 'AND')." main.info_id IN (".implode(',',$links).')';
			}
		}
		if (!empty($query['order']) && eregi('^[a-z_0-9, ]+$',$query['order']) && (empty($query['sort']) || eregi('^(DESC|ASC)$',$query['sort'])))
		{
			$order = array();
			foreach(explode(',',$query['order']) as $val)
			{
				$val = trim($val);
				$val = (substr($val,0,5) != 'info_' ? 'info_' : '').$val;
				if ($val == 'info_des' && $this->db->capabilities['order_on_text'] !== true)
				{
					if (!$this->db->capabilities['order_on_text']) continue;

					$val = sprintf($this->db->capabilities['order_on_text'],$val);
				}
				$order[] = $val;
			}
			$ordermethod = 'ORDER BY ' . implode(',',$order) . ' ' . $query['sort'];
		}
		else
		{
			$ordermethod = 'ORDER BY info_datemodified DESC';   // newest first
		}
		$acl_filter = $filtermethod = $this->aclFilter($query['filter']);
		$filtermethod .= $this->statusFilter($query['filter']);
		$filtermethod .= $this->dateFilter($query['filter']);

		if (is_array($query['col_filter']))
		{
			foreach($query['col_filter'] as $col => $data)
			{
				if (substr($col,0,5) != 'info_') $col = 'info_'.$col;
				if (!empty($data) && eregi('^[a-z_0-9]+$',$col))
				{
					if ($col == 'info_responsible')
					{
						$data = (int) $data;
						if (!$data) continue;
						$filtermethod .= " AND (".$this->responsible_filter($data)." OR info_responsible='0' AND ".
							$this->db->expression($this->info_table,array(
								'info_owner' => $data > 0 ? $data : $GLOBALS['egw']->accounts->members($data,true)
							)).')';
					}
					else
					{
						$filtermethod .= ' AND '.$this->db->expression($this->info_table,array($col => $data));
					}	
				}
			}
		}
		//echo "<p>filtermethod='$filtermethod'</p>";

		if ((int)$query['cat_id'])
		{
			//$filtermethod .= ' AND info_cat='.intval($query['cat_id']).' ';
			if (!is_object($GLOBALS['egw']->categories))
			{
				$GLOBALS['egw']->categories =& CreateObject('phpgwapi.categories');
			}
			$cats = $GLOBALS['egw']->categories->return_all_children((int)$query['cat_id']);
			$filtermethod .= ' AND info_cat'.(count($cats)>1? ' IN ('.implode(',',$cats).') ' : '='.(int)$query['cat_id']);
		}
		$join = $distinct = $count_subs = '';
		if ($query['query']) $query['search'] = $query['query'];	// allow both names
		if ($query['search'])			  // we search in _from, _subject, _des and _extra_value for $query
		{
			$pattern = $this->db->quote('%'.$query['search'].'%');

			$columns = array('info_from','info_addr','info_location','info_subject','info_extra_value');
			// at the moment MaxDB 7.5 cant cast nor search text columns, it's suppost to change in 7.6
			if ($this->db->capabilities['like_on_text']) $columns[] = 'info_des';

			$sql_query = 'AND ('.(is_numeric($query['search']) ? 'main.info_id='.(int)$query['search'].' OR ' : '').
				implode(" LIKE $pattern OR ",$columns)." LIKE $pattern) ";
			$join = "LEFT JOIN $this->extra_table ON main.info_id=$this->extra_table.info_id";
			// mssql and others cant use DISTICT if text columns (info_des) are involved
			$distinct = $this->db->capabilities['distinct_on_text'] ? 'DISTINCT' : '';
		}
		$pid = 'AND info_id_parent='.($action == 'sp' ? $query['action_id'] : 0);

		if (!$GLOBALS['egw_info']['user']['preferences']['infolog']['listNoSubs'] &&
			 $action != 'sp' || isset($query['subs']) && $query['subs'])
		{
			$pid = '';
		}
		$ids = array( );
		if ($action == '' || $action == 'sp' || count($links))
		{
			$sql_query = "FROM $this->info_table main $join WHERE ($filtermethod $pid $sql_query) $link_extra";
			
			$this->db->query($sql="SELECT $distinct main.info_id ".$sql_query,__LINE__,__FILE__);
			$query['total'] = $this->db->num_rows();

			if (isset($query['start']) && $query['start'] > $query['total'])
			{
				$query['start'] = 0;
			}
			if ($this->db->capabilities['sub_queries'])
			{
				$count_subs = ",(SELECT count(*) FROM $this->info_table sub WHERE sub.info_id_parent=main.info_id AND $acl_filter) AS info_anz_subs";
			}
			$this->db->query($sql="SELECT $distinct main.* $count_subs $sql_query $ordermethod",__LINE__,__FILE__,
				(int) $query['start'],isset($query['start']) ? (int) $query['num_rows'] : -1);
			//echo "<p>db::query('$sql',,,".(int)$query['start'].','.(isset($query['start']) ? (int) $query['num_rows'] : -1).")</p>\n";
			while (($info =& $this->db->row(true)))
			{			
				$info['info_responsible'] = $info['info_responsible'] ? explode(',',$info['info_responsible']) : array();

				$ids[$info['info_id']] = $info;
			}
			if ($ids && $query['custom_fields'])
			{
				$this->db->select($this->extra_table,'*',array('info_id'=>array_keys($ids)),__LINE__,__FILE__);
				while ($row = $this->db->row(true))
				{
					$ids[$row['info_id']]['#'.$row['info_extra_name']] = $row['info_extra_value'];
				}
			}
		}
		else
		{
			$query['start'] = $query['total'] = 0;
		}
		return $ids;
	}
	
	/**
	 * Query infolog for users with open entries, either own or responsible, with start or end within 4 days
	 * 
	 * This functions tries to minimize the users really checked with the complete filters, as creating a
	 * user enviroment and running the specific check costs ...
	 *
	 * @return array with acount_id's groups get resolved to there memebers
	 */
	function users_with_open_entries()
	{
		$users = array();
		
		$this->db->select($this->info_table,'DISTINCT info_owner',array(
			str_replace(' AND ','',$this->statusFilter('open')),
			'(ABS(info_startdate-'.time().')<'.(4*24*60*60).' OR '.	// start_day within 4 days
			'ABS(info_enddate-'.time().')<'.(4*24*60*60).')',		// end_day within 4 days
		),__LINE__,__FILE__);
		while ($this->db->next_record())
		{
			$users[] = $this->db->f(0);
		}
		$this->db->select($this->info_table,'DISTINCT info_responsible',$this->statusFilter('open',false),__LINE__,__FILE__);
		while ($this->db->next_record())
		{
			foreach(explode(',',$this->db->f(0)) as $responsible)
			{
				if ($GLOBALS['egw']->accounts->get_type($responsible) == 'g')
				{
					$responsible = $GLOBALS['egw']->accounts->members($responsible,true);
				}
				if ($responsible)
				{
					foreach(is_array($responsible) ? $responsible : array($responsible) as $user)
					{
						if ($user && !in_array($user,$users)) $users[] = $user;
					}
				}
			}
		}
		return $users;
	}
}
