<?php
	/**************************************************************************\
	* phpGroupWare - Projects                                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	class projects
	{
		var $db;
		var $projects;
		var $grants;
		var $total_records;

		function projects()
		{
			global $phpgw;
			$this->db				= $phpgw->db;
			$this->db2				= $this->db;
			$this->total_records	= $this->db->num_rows();
			$this->grants			= $phpgw->acl->get_grants('projects');
			$this->projects			= $this->read_projects($start, $limit, $query, $filter, $sort, $order, $status, $cat_id);

		}

		function check_perms($has, $needed)
		{
			return (!!($has & $needed) == True);
		}

		function read_projects( $start, $limit = True, $query = '', $filter = '', $sort = '', $order = '', $status = 'active', $cat_id)
		{
			global $phpgw, $phpgw_info;

			if ($status == 'archive')
			{
				$statussort = " AND status = 'archive' ";
			}
			else
			{
				$statussort = " AND status != 'archive' ";
			}

			if (!$sort)
			{
				$sort = "ASC";
			}

			if ($order)
			{
				$ordermethod = "order by $order $sort";
			}
			else
			{
				$ordermethod = "order by start_date asc";
			}

			if (! $filter)
			{
				$filter = 'none';
			}

			if ($filter != 'private')
			{
				if ($filter != 'none')
				{
					$filtermethod = " access like '%,$filter,%' ";
				}
				else
				{
					$filtermethod = " ( coordinator=" . $phpgw_info['user']['account_id'];
					if (is_array($this->grants))
					{
						$grants = $this->grants;
						while (list($user) = each($grants))
						{
							$public_user_list[] = $user;
						}
						reset($public_user_list);
						$filtermethod .= " OR (access='public' AND coordinator in(" . implode(',',$public_user_list) . ")))";
					}
					else
					{
						$filtermethod .= ' )';
					}
				}
			}
			else
			{
				$filtermethod = ' coordinator=' . $phpgw_info['user']['account_id'] . ' ';
			}

			if ($cat_id)
			{
				$filtermethod .= " AND category='$cat_id' ";
			}

			if ($query)
			{
				$querymethod = " AND (title like '%$query%' OR num like '%$query%' OR descr like '%$query%') ";
			}

			$sql = "SELECT p.id,p.num,p.access,p.category,p.entry_date,p.start_date,p.end_date,p.coordinator,p.customer,p.status, "
				. "p.descr,p.title,p.budget,a.account_lid,a.account_firstname,a.account_lastname FROM "
				. "phpgw_p_projects AS p,phpgw_accounts AS a WHERE a.account_id=p.coordinator $statussort $querymethod AND $filtermethod "
				. "$ordermethod";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();
			$this->db->query($sql. " " . $this->db->limit($start),__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$projects[$i]['id']				= $this->db->f('id');
				$projects[$i]['number']			= $this->db->f('num');
				$projects[$i]['access']			= $this->db->f('access');
				$projects[$i]['category']		= $this->db->f('category');
				$projects[$i]['entry_date']		= $this->db->f('entry_date');
				$projects[$i]['start_date']		= $this->db->f('start_date');
				$projects[$i]['end_date']		= $this->db->f('end_date');
				$projects[$i]['coordinator']	= $this->db->f('coordinator');
				$projects[$i]['customer']		= $this->db->f('customer');
				$projects[$i]['status']			= $this->db->f('status');
				$projects[$i]['description']	= $this->db->f('descr');
				$projects[$i]['title']			= $this->db->f('title');
				$projects[$i]['budget']			= $this->db->f('budget');
				$projects[$i]['lid']			= $this->db->f('account_lid');
				$projects[$i]['firstname']		= $this->db->f('account_firstname');
				$projects[$i]['lastname']		= $this->db->f('account_lastname');
				$i++;
			}
			return $projects;
		}

		function read_single_project($id = '')
		{
	
			$this->db->query("SELECT * from phpgw_p_projects WHERE id='$id'",__LINE__,__FILE__);
	
			while($this->db->next_record())
			{
				$projects[0]['id']				= $this->db->f('id');
				$projects[0]['number']			= $this->db->f('num');
				$projects[0]['access']			= $this->db->f('access');
				$projects[0]['category']		= $this->db->f('category');
				$projects[0]['entry_date']		= $this->db->f('entry_date');
				$projects[0]['start_date']		= $this->db->f('start_date');
				$projects[0]['end_date']		= $this->db->f('end_date');
				$projects[0]['coordinator']		= $this->db->f('coordinator');
				$projects[0]['customer']		= $this->db->f('customer');
				$projects[0]['status']			= $this->db->f('status');
				$projects[0]['description']		= $this->db->f('descr');
				$projects[0]['title']			= $this->db->f('title');
				$projects[0]['budget']			= $this->db->f('budget');
			}
			return $projects;
		}

		function select_project_list($selected = '')
		{
			global $phpgw;

			$projects = $this->read_projects($start, False, $query, $filter, $sort, $order, $status, $cat_id);

			for ($i=0;$i<count($projects);$i++)
			{
				$pro_select .= '<option value="' . $projects[$i]['id'] . '"';
				if ($projects[$i]['id'] == $selected)
				{
					$pro_select .= ' selected';
				}
				$pro_select .= '>' . $phpgw->strip_html($projects[$i]['title']) . ' [ ' . $projects[$i]['number'] . ' ]';
				$pro_select .= '</option>';
			}
			return $pro_select;
		}

		function read_hours($start, $limit = True, $query = '', $filter, $sort = '', $order = '',$access = 'all',$status)
		{
			global $phpgw, $phpgw_info;

			if ($phpgw_info['server']['db_type']=='pgsql')
			{
				$join = " JOIN ";
			}
			else
			{
				$join = " LEFT JOIN ";
			}

			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = " order by h.start_date asc";
			}

			if (!$status)
			{
				$filtermethod = " AND (h.status='open' OR h.status='done' OR h.status='billed')";
			}
			else
			{
				$filtermethod = " AND h.status='$status'";
			}

			if ($access == 'private')
			{
				$filtermethod .= " AND h.employee='" . $phpgw_info['user']['account_id'] . "'";
			}

			if ($query)
			{
				$querymethod = " AND (h.remark like '%$query%' OR h.start_date like '%$query%' OR h.end_date like '%$query%' OR h.minutes like '%$query%' "
							. "OR h.hours_descr like '%$query%')";
			}

			$sql = "SELECT h.id as id,h.hours_descr,a.descr,h.status,h.start_date,h.end_date,h.minutes,h.employee FROM phpgw_p_hours AS h"
				. "$join phpgw_p_activities AS a ON h.activity_id=a.id WHERE h.project_id='$filter' $filtermethod $querymethod";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();
			$this->db->query($sql . $ordermethod . " " . $this->db->limit($start),__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$hours[$i]['id']			= $this->db->f('id');
				$hours[$i]['hours_descr']	= $this->db->f('hours_descr');
				$hours[$i]['descr']			= $this->db->f('descr');
				$hours[$i]['status']		= $this->db->f('status');
				$hours[$i]['start_date']	= $this->db->f('start_date');
				$hours[$i]['end_date']		= $this->db->f('end_date');
				$hours[$i]['minutes']		= $this->db->f('minutes');
				$hours[$i]['employee']		= $this->db->f('employee');
				$i++;
			}
			return $hours;
		}
	}
?>
