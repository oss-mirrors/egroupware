<?php
	/**************************************************************************\
	* phpGroupWare - InfoLog                                                   *
	* http://www.phpgroupware.org                                              *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* originaly based on todo written by Joseph Engo <jengo@phpgroupware.org>  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/*!
	@class soinfolog
	@abstract storage object / db-layer for InfoLog
	@author Ralf Becker
	@copyright GPL - GNU General Public License
	@note all values passed to this class are run either through intval or addslashes to prevent query-inserting
		and for pgSql 7.3 compatibility
	*/
	class soinfolog 				// DB-Layer
	{
		var $db,$db2;
		var $grants;
		var $data = array( );
		var $filters = array( );
		var $user;

		/*!
		@function soinfolog
		@abstract constructor
		*/
		function soinfolog( $info_id = 0)
		{
			$this->db     = $GLOBALS['phpgw']->db;
			$this->grants = $GLOBALS['phpgw']->acl->get_grants('infolog');
			$this->user   = $GLOBALS['phpgw_info']['user']['account_id'];

			$this->links = CreateObject('infolog.solink');

			$this->read( $info_id );
		}

		/*!
		@function check_access
		@abstract checks if user has the $required_rights to access $info_id (private access is handled too)
		@syntax check_access( $info_id,$required_rights )
		@param $info_id Id of InfoLog entry
		@param $required_rights PHPGW_ACL_xyz anded together
		@returns True if access is granted else False
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

			$access_ok = $owner == $this->user ||                // user has all rights
							 // ACL only on public entrys || $owner granted _PRIVATE
							 !!($this->grants[$owner] & $required_rights) &&
							 ($info['info_access'] == 'public' ||
							 !!($this->grants[$owner] & PHPGW_ACL_PRIVATE));   
							
			// echo "check_access(info_id=$info_id (owner=$owner, user=$user),required_rights=$required_rights): access".($access_ok?"Ok":"Denied");
			
			return $access_ok;         
		}

		/*!
		@function aclFilter
		@abstract generate sql to be AND'ed into a query to ensure ACL is respected (incl. _PRIVATE)
		@param $filter: none|all - list all entrys user have rights to see<br>
			private|own - list only his personal entrys (incl. those he is responsible for !!!) 
		@returns the necesary sql
		*/           
		function aclFilter($filter = 'none')
		{
			ereg('.*(own|privat|all|none).*',$filter,$vars);
			$filter = $vars[1];

			if (isset($this->acl_filter[$filter]))
			{
				return $this->acl_filter[$filter];  // used cached filter if found
			}
			if (is_array($this->grants))
			{
				while (list($user,$grant) = each($this->grants))
				{
					// echo "<p>grants: user=$user, grant=$grant</p>";
					if ($grant & (PHPGW_ACL_READ|PHPGW_ACL_EDIT))
					{
						$public_user_list[] = $user;
					}
					if ($grant & PHPGW_ACL_PRIVATE)
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

			// private: own entries plus the one user is responsible for 
			if ($filter == 'private' || $filter == 'own')
			{
				$filtermethod .= " OR (info_responsible=$this->user OR info_status = 'offer')".
				                 " AND (info_access='public'".($has_private_access?" OR $has_private_access":'').')';
			}
			else      				// none --> all entrys user has rights to see
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
			
			return $this->acl_filter[$filter] = $filtermethod;  // cache the filter
		}      
	
		/*!
		@function statusFilter
		@abstract generate sql to filter based on the status of the log-entry
		@syntax statusFilter($filter = '')
		@param $filter done = done or billed, open = not ()done or billed), offer = offer
		@returns the necesary sql
		*/
		function statusFilter($filter = '')
		{
			ereg('.*(done|open|offer).*',$filter,$vars);
			$filter = $vars[1];

			switch ($filter)
			{
				case 'done':	return " AND info_status IN ('done','billed')";
				case 'open':	return " AND NOT (info_status IN ('done','billed'))";
				case 'offer':	return " AND info_status = 'offer'";
			}
			return '';
		}

		/*!
		@function dateFilter
		@abstract generate sql to filter based on the start- and enddate of the log-entry
		@syntax dateFilter($filter = '')
		@param $filter upcoming = startdate is in the future<br>
			today startdate < tomorrow<br>
			overdue enddate < tomorrow
		@returns the necesary sql
		*/
		function dateFilter($filter = '')
		{
			ereg('.*(upcoming|today|overdue).*',$filter,$vars);
			$filter = $vars[1];

			$now = getdate(time());
			$tomorrow = mktime(0,0,0,$now['mon'],$now['mday']+1,$now['year']);

			switch ($filter)
			{
				case 'upcoming':
					return " AND info_startdate >= '$tomorrow'";
				case 'today':
					return " AND info_startdate < '$tomorrow'";
				case 'overdue':
					return " AND (info_enddate != 0 AND info_enddate < '$tomorrow')";
			}
			return '';
		}

		/*!
		@function init
		@abstract initialise the internal $this->data to be empty
		@discussion only non-empty values got initialised
		*/
		function init()
		{
			$this->data = array( 
				'info_owner' => $this->user,
				'info_pri'   => 'normal' 
			);
		}      
		
		/*!
		@function db2data
		@abstract copy data after a query into $data
		@syntax db2data(&$data)
		@param $data array to copy the data
		@description copy only non-numeric keys
		*/
		function db2data(&$data)
		{
			$data = array();
			foreach ($this->db->Record as $key => $val)
			{
				if (!is_numeric($key))
				{
					$data[$key] = $val;
				}
			}
		}

		/*!
		@function read
		@abstract read InfoLog entry $info_id
		@syntax read( $info_id )
		@param $info_id id of log-entry
		@description some cacheing is done to prevent multiple reads of the same entry
		@returns the entry as array
		*/
		function read($info_id)		// did _not_ ensure ACL
		{
			$info_id = intval($info_id);

			if ($info_id <= 0 || $info_id != $this->data['info_id'] && 
				(!$this->db->query("select * FROM phpgw_infolog where info_id=$info_id",__LINE__,__FILE__) ||
				 !$this->db->next_record())) 
			{
				$this->init( );
				return False;
			}
			if ($info_id != $this->data['info_id'])      // data yet read in
			{
				$this->db2data($this->data);
			}
			return $this->data;
		}
		
		/*!
		@function delete
		@abstract delete InfoLog entry $info_id AND the links to it
		@syntax delete( $info_id )
		@param $info_id id of log-entry
		*/
		function delete($info_id)  // did _not_ ensure ACL
		{
			if (($info_id = intval($info_id)) <= 0)
			{
				return;
			}
			$this->db->query("delete FROM phpgw_infolog where info_id=$info_id or info_id_parent=$info_id" .
				" AND ((info_access='public' and info_owner != $this->user) OR (info_owner=$this->user))",
				__LINE__,__FILE__);
				
			$this->links->unlink(0,'infolog',$info_id);

			if ($this->data['info_id'] == $info_id)
			{
				$this->init( );            
			}
		}

		/*!
		@function change_delete_owner
		@abstract changes or deletes entries with a spezified owner (for hook_delete_account)
		@syntax change_delete_owner( $owner,$new_owner=0 )
		@param $owner old owner
		@param $new_owner new owner or 0 if entries should be deleted
		*/
		function change_delete_owner($owner,$new_owner=0)  // new_owner=0 means delete
		{
			$owner = intval($owner);
			if (!($new_owner = intval($new_owner)))
			{
				$sql = "delete FROM phpgw_infolog where info_owner=$owner";
				$sql2 = "update phpgw_infolog set info_responsible=0 where info_responsible=$owner";
			}
			else
			{
				$sql = "update phpgw_infolog set info_owner=$new_owner where info_owner=$owner";
				$sql2 = "update phpgw_infolog set info_responsible=$new_owner where info_responsible=$owner";
			}
			$this->db->query($sql,__LINE__,__FILE__);
			$this->db->query($sql2,__LINE__,__FILE__);
		}

		/*!
		@function write
		@abstract writes the given $values to InfoLog, a new entry gets created if info_id is not set or 0
		@syntax write( $values )
		@param $values array with the data of the log-entry
		@returns nothing direct, but the info_id gets
		*/
		function write($values)  // did _not_ ensure ACL
		{
			include(PHPGW_SERVER_ROOT.'/infolog/setup/tables_current.inc.php');
			$db_cols = $phpgw_baseline['phpgw_infolog']['fd'];
			unset($phpgw_baseline);

			while (list($key,$val) = each($values))
			{
				if ($key != 'info_id')
				{
					if (!isset($db_cols[$key]))
					{
						continue;	// not in infolog-table
					}
					$this->data[$key] = $val;   // update internal data

					switch($val['type'])	// protection against query-insertion
					{
						case 'int': case 'auto':
							$val = intval($val);
							break;
						default:
							$val = "'".$this->db->db_addslashes($val)."'";
							break;
					}
					$cols .= ($cols ? ',' : '').$key;
					$vals .= ($vals ? ',' : '').$val;
					$query .= ($query ? ',' : '')."$key=$val";
				}
			}
			if (($this->data['info_id'] = intval($values['info_id'])) > 0)
			{
				$query = "UPDATE phpgw_infolog SET $query where info_id='".$this->data['info_id']."'";
				$this->db->query($query,__LINE__,__FILE__);
			}
			else
			{
				$query = "INSERT INTO phpgw_infolog ($cols) VALUES ($vals)";
				$this->db->query($query,__LINE__,__FILE__);
				$this->data['info_id']=$this->db->get_last_insert_id('phpgw_infolog','info_id');
			}
			// echo "<p>soinfolog.write values= "; _debug_array($values);
			// echo "<p>soinfolog.write this->data= "; _debug_array($this->data);

			return $this->data['info_id'];
		}

		/*!
		@function anzSubs
		@abstract count the sub-entries of $info_id
		@syntax anzSubs( $info_id )
		@param $info_id id of log-entry
		@returns the number of sub-entries
		*/
		function anzSubs( $info_id )
		{
			if (($info_id = intval($info_id)) <= 0)
			{
				return 0;
			}
			$this->db->query("select count(*) FROM phpgw_infolog where info_id_parent=$info_id",__LINE__,__FILE__);

			$this->db->next_record();

			return $this->db->f(0);
		}

		/*!
		@function search
		@abstract searches InfoLog for a certain pattern in $query
		@syntax search( $order,$sort,$filter,$cat_id,$query,$action,$action_id,$ordermethod,&$start,&$total )
		@param $order comma-separated list of columns to order the result (no 'ORDER BY'), eg. 'info_subject DESC'
		@param $sort comma-separated list of columns to to sort by (incl. 'SORT BY') or ''
		@param $filter string with combination of acl-, date- and status-filters, eg. 'own-open-today' or ''
		@param $cat_id category to use or 0
		@param $query pattern to search, search is done in info_from, info_subject and info_des
		@param $action / $action_id if only entries linked to a specified app/entry show be used
		@param &$start, &$total nextmatch-parameters will be used and set if query returns less entries
		@returns array with id's as key of the matching log-entries
		*/
		function search($order,$sort,$filter,$cat_id,$query,$action,$action_id,$ordermethod,&$start,&$total)
		{
			//echo "<p>soinfolog.search(action='$action/$action_id')</p>\n";
			$action2app = array(
				'addr'        => 'addressbook',
				'proj'        => 'projects',
				'event'       => 'calendar'
			);
			if (isset($action2app[$action]))
			{
				$action = $action2app[$action];
			}
			if ($action != '')
			{
				$links = $this->links->get_links($action=='sp'?'infolog':$action,$action_id,'infolog');
			
				if (count($links))
				{
					$link_extra = ($action == 'sp' ? 'OR' : 'AND').' info_id IN ('.implode(',',$links).')';
				}
			}
			if ($order)
			{
			  $ordermethod = 'ORDER BY ' . $this->db->db_addslashes($order) . ' ' . $this->db->db_addslashes($sort);
			}
			else
			{
			  $ordermethod = 'ORDER BY info_datemodified DESC';   // newest first
			}
			$filtermethod = $this->aclFilter($filter);
			$filtermethod .= $this->statusFilter($filter);
			$filtermethod .= $this->dateFilter($filter);
			// echo "<p>filtermethod='$filtermethod'</p>";

			if (intval($cat_id))
			{
			  $filtermethod .= ' AND info_cat='.intval($cat_id).' ';
			}
			if ($query)			  // we search in _from, _subject and _des for $query
			{
				$query = $this->db->db_addslashes($query);
				$sql_query = "AND (info_from like '%$query%' OR info_subject ".
								 "LIKE '%$query%' OR info_des LIKE '%$query%') ";
			}
			$pid = 'AND info_id_parent='.($action == 'sp' ? $action_id : 0);

			if (!$phpgw_info['user']['preferences']['infolog']['listNoSubs'] &&
				 $action != 'sp')
			{
				$pid = '';
			}
			$ids = array( );
			if ($action == '' || $action == 'sp' || count($links))
			{
				$this->db->query($sql="SELECT COUNT(*) FROM phpgw_infolog i WHERE ($filtermethod $pid $sql_query) $link_extra",__LINE__,__FILE__);
				
				$this->db->next_record();
				$total = $this->db->f(0);

				if (!$start || $start > $total)
				{
					$start = 0;
				}
				$this->db->limit_query($sql="SELECT * FROM phpgw_infolog WHERE ($filtermethod $pid $sql_query) $link_extra $ordermethod",$start,__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$this->db2data(&$info);
					$ids[$info['info_id']] = $info;
				}
			}
			else
			{
				$start = $total = 0;
			}
			return $ids;
		}
	}
