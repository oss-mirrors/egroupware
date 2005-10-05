<?php
	/**************************************************************************\
	* eGroupWare - InfoLog                                                     *
	* http://www.eGroupWare.org                                                *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* originaly based on todo written by Joseph Engo <jengo@phpgroupware.org>  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	include_once(EGW_API_INC.'/class.solink.inc.php');

	/**
	 * storage object / db-layer for InfoLog
	 *
	 * all values passed to this class are run either through intval or addslashes to prevent query-insertion
	 * and for pgSql 7.3 compatibility
	 *
	 * @package infolog
	 * @author RalfBecker-At-outdoor-training.de
	 * @copyright GPL - GNU General Public License
	 */
	class soinfolog 				// DB-Layer
	{
		var $db;
		var $grants;
		var $data = array( );
		var $user;
		var $info_table = 'egw_infolog';
		var $extra_table = 'egw_infolog_extra';

		/**
		 * constructor
		 */
		function soinfolog( $info_id = 0)
		{
			$this->db     = clone($GLOBALS['egw']->db);
			$this->db->set_app('infolog');
			$this->grants = $GLOBALS['egw']->acl->get_grants('infolog');
			$this->user   = $GLOBALS['egw_info']['user']['account_id'];

			$this->links =& new solink();

			$this->tz_offset = $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'];

			$this->read( $info_id );
		}

		/**
		 * checks if user has the $required_rights to access $info_id (private access is handled too)
		 *
		 * @param $info_id Id of InfoLog entry
		 * @param $required_rights EGW_ACL_xyz anded together
		 * @return boolean True if access is granted else False
		 */
		function check_access( $info_id,$required_rights )
		{
			if ($info_id != $this->data['info_id'])      	// already loaded?
			{
				// dont change our own internal data,
				// dont use new as it changes $phpgw->db
				$private_info = $this;                      
				$info = $private_info->read($info_id);
			}
			else
			{
				$info = $this->data;
			}
			if (!$info || !$info_id)
			{
				return False;
			}
			$owner = $info['info_owner'];

			$access_ok = $owner == $this->user ||	// user has all rights
				// ACL only on public entrys || $owner granted _PRIVATE
				(!!($this->grants[$owner] & $required_rights) ||
				// implicite read-rights for responsible user !!!
				in_array($this->user, $info['info_responsible']) && $required_rights == EGW_ACL_READ) &&
				//$info['info_responsible'] == $this->user && $required_rights == EGW_ACL_READ) &&
				($info['info_access'] == 'public' ||
				!!($this->grants[$owner] & EGW_ACL_PRIVATE));

			//echo "<p>check_access(info_id=$info_id (owner=$owner, user=$user),required_rights=$required_rights): access".($access_ok?"Ok":"Denied")."</p>\n";
			return $access_ok;
		}

		/**
		 * generate sql to be AND'ed into a query to ensure ACL is respected (incl. _PRIVATE)
		 *
		 * @param $filter: none|all - list all entrys user have rights to see<br>
		 * 	private|own - list only his personal entrys (incl. those he is responsible for !!!), my = entries the user is responsible for 
		 * @return string the necesary sql
		 */
		function aclFilter($filter = False)
		{
			preg_match('/(my|own|privat|all|none|user)([0-9]*)/',$filter_was=$filter,$vars);
			$filter = $vars[1];
			$f_user   = intval($vars[2]);

			if (isset($this->acl_filter[$filter.$f_user]))
			{
				return $this->acl_filter[$filter.$f_user];  // used cached filter if found
			}
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
			$filtermethod = " (info_owner=$this->user"; // user has all rights
			
			if ($filter == 'my')
			{
				$filtermethod .= " AND info_responsible='0'";
			}
			// implicit read-rights for responsible user
			$filtermethod .= " OR (".$this->db->concat("','",'info_responsible',"'%'")." LIKE '%,$this->user,%' AND info_access='public')";

			// private: own entries plus the one user is responsible for
			if ($filter == 'private' || $filter == 'own')
			{
				$filtermethod .= " OR (".$this->db->concat("','",'info_responsible',"'%'")." LIKE '%,$this->user,%'".
					($filter == 'own' && count($public_user_list) ?	// offer's should show up in own, eg. startpage, but need read-access
						" OR info_status = 'offer' AND info_owner IN(" . implode(',',$public_user_list) . ')' : '').")".
				                 " AND (info_access='public'".($has_private_access?" OR $has_private_access":'').')';
			}
			elseif ($filter != 'my')      				// none --> all entrys user has rights to see
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
				$filtermethod = " ((info_owner=$f_user AND info_responsible=0 OR ".$this->db->concat("','",'info_responsible',"'%'")." LIKE '%,$f_user,%') AND $filtermethod)";
			}
			//echo "<p>aclFilter(filter='$filter_was',user='$user') = '$filtermethod', privat_user_list=".print_r($privat_user_list,True).", public_user_list=".print_r($public_user_list,True)."</p>\n";

			return $this->acl_filter[$filter.$f_user] = $filtermethod;  // cache the filter
		}
	
		/**
		 * generate sql to filter based on the status of the log-entry
		 *
		 * @param $filter done = done or billed, open = not ()done or billed), offer = offer
		 * @return string the necesary sql
		 */
		function statusFilter($filter = '')
		{
			preg_match('/(done|open|offer)/',$filter,$vars);
			$filter = $vars[1];

			switch ($filter)
			{
				case 'done':	return " AND info_status IN ('done','billed')";
				case 'open':	return " AND NOT (info_status IN ('done','billed'))";
				case 'offer':	return " AND info_status = 'offer'";
			}
			return '';
		}

		/**
		 * generate sql to filter based on the start- and enddate of the log-entry
		 *
		 * @param $filter upcoming = startdate is in the future<br>
		 * 	today startdate < tomorrow<br>
		 * 	overdue enddate < tomorrow<br>
		 * 	limitYYYY/MM/DD not older or open 
		 * @return string the necesary sql
		 */
		function dateFilter($filter = '')
		{
			preg_match('/(upcoming|today|overdue|date)([-\\/.0-9]*)/',$filter,$vars);
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
				case 'limit':
					return " AND (info_modified >= '$today' OR NOT (info_status IN ('done','billed')))";
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

			if ($info_id <= 0 || $info_id != $this->data['info_id'] && 
				(!$this->db->select($this->info_table,'*',array('info_id'=>$info_id),__LINE__,__FILE__) ||
				 !(($this->data = $this->db->row(true)))))
			{
				$this->init( );
				return False;
			}
			if (!is_array($this->data['info_responsible']))
			{
				$this->data['info_responsible'] = $this->data['info_responsible'] ? explode(',',$this->data['info_responsible']) : array();
			}
			if ($info_id != $this->data['info_id'])      // data yet read in
			{
				$this->db->select($this->extra_table,'info_extra_name,info_extra_value',array('info_id'=>$info_id),__LINE__,__FILE__);
				while ($this->db->next_record())
				{
					$this->data['#'.$this->db->f(0)] = $this->db->f(1);
				}
			}
			return $this->data;
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
			$this->db->update($this->info_table,array('info_responsible'=>$new_owner),array('info_responsible'=>$owner),__LINE__,__FILE__);
		}

		/**
		 * writes the given $values to InfoLog, a new entry gets created if info_id is not set or 0
		 *
		 * @param array $values with the data of the log-entry
		 * @return int the info_id
		 */
		function write($values)  // did _not_ ensure ACL
		{
			//echo "soinfolog::write()values="; _debug_array($values);
			$info_id = (int) $values['info_id'];

			if (isset($values['info_responsible']))
			{
				$values['info_responsible'] = count($values['info_responsible']) ? implode(',',$values['info_responsible']) : '0';
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
			if (($this->data['info_id'] = $info_id))
			{
				$this->db->update($this->info_table,$to_write,array('info_id'=>$info_id),__LINE__,__FILE__);
			}
			else
			{
				if (!isset($to_write['info_id_parent'])) $to_write['info_id_parent'] = 0;	// must not be null

				$this->db->insert($this->info_table,$to_write,false,__LINE__,__FILE__);
				$this->data['info_id']=$this->db->get_last_insert_id($this->info_table,'info_id');
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
		 * @param $query[order] column-name to sort after
		 * @param $query[sort] sort-order DESC or ASC
		 * @param $query[filter] string with combination of acl-, date- and status-filters, eg. 'own-open-today' or ''
		 * @param $query[cat_id] category to use or 0 or unset
		 * @param $query[search] pattern to search, search is done in info_from, info_subject and info_des
		 * @param $query[action] / $query[action_id] if only entries linked to a specified app/entry show be used
		 * @param &$query[start], &$query[total] nextmatch-parameters will be used and set if query returns less entries
		 * @param $query[col_filter] array with column-name - data pairs, data == '' means no filter (!)
		 * @param $query[subs] boolean return subs or not, if unset the user preference is used
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

			if ($action != '')
			{
				$links = $this->links->get_links($action=='sp'?'infolog':$action,$query['action_id'],'infolog');

				if (count($links))
				{
					$link_extra = ($action == 'sp' ? 'OR' : 'AND')." $this->info_table.info_id IN (".implode(',',$links).')';
				}
			}
			if (!empty($query['order']) && eregi('^[a-z_0-9, ]+$',$query['order']) && (empty($query['sort']) || eregi('^(DESC|ASC)$',$query['sort'])))
			{
				$order = array();
				foreach(explode(',',$query['order']) as $val)
				{
					$val = trim($val);
					$val = (substr($val,0,5) != 'info_' ? 'info_' : '').$val;
					if ($val == 'info_des' && $this->db->Type == 'mssql')
					{
						$val = "CAST($val AS varchar)";
					}
					$order[] = $val;
				}
				$ordermethod = 'ORDER BY ' . implode(',',$order) . ' ' . $query['sort'];
			}
			else
			{
				$ordermethod = 'ORDER BY info_datemodified DESC';   // newest first
			}
			$filtermethod = $this->aclFilter($query['filter']);
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
							$filtermethod .= " AND (".$this->db->concat("','",'info_responsible',"','")." LIKE '%,$data,%' OR info_responsible='0' AND info_owner=$data)";
						}
						else
						{
							if (!$this->table_defs) $this->table_defs = $this->db->get_table_definitions('infolog',$this->info_table);
							$filtermethod .= ' AND '.$col.'='.$this->db->quote($data,$this->table_defs['fd'][$col]['type']);
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
			$join = '';
			if ($query['query']) $query['search'] = $query['query'];	// allow both names
			if ($query['search'])			  // we search in _from, _subject, _des and _extra_value for $query
			{
				$pattern = $this->db->quote('%'.$query['search'].'%');

				$columns = array('info_from','info_subject','info_extra_value');
				switch($this->db->Type)
				{
					case 'sapdb':
					case 'maxdb':
						// at the moment MaxDB 7.5 cant cast nor search text columns, it's suppost to change in 7.6
						break;
					default:
						$columns[] = 'info_des';
				}
				$sql_query = 'AND ('.implode(" LIKE $pattern OR ",$columns)." LIKE $pattern) ";
				$join = "LEFT JOIN $this->extra_table ON $this->info_table.info_id=$this->extra_table.info_id";
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
				$sql_query = "FROM $this->info_table $join WHERE ($filtermethod $pid $sql_query) $link_extra";
				switch($this->db->Type)
				{
					// mssql and others cant use DISTICT if text columns (info_des) are involved
					case 'mssql':
					case 'sapdb':
					case 'maxdb':
						$distinct = '';
						break;
					default:
						$distinct = 'DISTINCT';
				}
				$this->db->query($sql="SELECT $distinct $this->info_table.info_id ".$sql_query,__LINE__,__FILE__);
				$query['total'] = $this->db->num_rows();

				if (!$query['start'] || $query['start'] > $query['total'])
				{
					$query['start'] = 0;
				}
				$this->db->limit_query($sql="SELECT $distinct $this->info_table.* $sql_query $ordermethod",$query['start'],__LINE__,__FILE__);
				//echo "<p>sql='$sql'</p>\n";
				while (($info =& $this->db->row(true)))
				{			
					$info['info_responsible'] = $info['info_responsible'] ? explode(',',$info['info_responsible']) : array();

					$ids[$info['info_id']] =& $info;
				}
			}
			else
			{
				$query['start'] = $query['total'] = 0;
			}
			return $ids;
		}
	}
