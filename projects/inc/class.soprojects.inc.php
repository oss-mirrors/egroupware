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

	class soprojects
	{
		var $db;
		var $grants;

		function soprojects()
		{
			global $phpgw, $phpgw_info;

			$this->db				= $phpgw->db;
			$this->db2				= $this->db;
			$this->grants			= $phpgw->acl->get_grants('projects');
			$this->coordinator		= $phpgw_info['user']['account_id'];
		}

		function project_filter($type)
		{
			switch ($type)
			{
				case 'subs':			$s = " and parent != '0'"; break;
				case 'mains':			$s = " and parent = '0'"; break;
				default: return False;
            }
			return $s;
		}

		function read_projects($start, $limit = True, $query = '', $filter = '', $sort = '', $order = '', $status = 'active', $cat_id = '', $type = 'mains', $pro_parent = '')
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

			if ($filter == 'none')
			{
				$filtermethod = " ( coordinator=" . $this->coordinator;
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
			elseif ($filter == 'yours')
			{
				$filtermethod = " coordinator='" . $this->coordinator . "'";
			}
			else
			{
				$filtermethod = " coordinator='" . $this->coordinator . "' AND access='private'";
			}

			if ($cat_id)
			{
				$filtermethod .= " AND category='$cat_id' ";
			}

			switch($type)
			{
				case 'mains':	$filtermethod .= " AND parent = '0' "; break;
				case 'subs' :	$filtermethod .= " AND parent = '$pro_parent' "; break;
			}

			if ($query)
			{
				$querymethod = " AND (title like '%$query%' OR num like '%$query%' OR descr like '%$query%') ";
			}

			$sql = "SELECT * from phpgw_p_projects WHERE $filtermethod $statussort $querymethod";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();

			if ($limit)
			{
				$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $ordermethod,__LINE__,__FILE__);
			}

			$i = 0;
			while ($this->db->next_record())
			{
				$projects[$i]['id']				= $this->db->f('id');
				$projects[$i]['parent']			= $this->db->f('parent');
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
				$i++;
			}
			return $projects;
		}

		function read_single_project($id = '')
		{
	
			$this->db->query("SELECT * from phpgw_p_projects WHERE id='$id'",__LINE__,__FILE__);
	
			while($this->db->next_record())
			{
				$project[0]['id']			= $this->db->f('id');
				$project[0]['parent']		= $this->db->f('parent');
				$project[0]['number']		= $this->db->f('num');
				$project[0]['access']		= $this->db->f('access');
				$project[0]['category']		= $this->db->f('category');
				$project[0]['entry_date']	= $this->db->f('entry_date');
				$project[0]['start_date']	= $this->db->f('start_date');
				$project[0]['end_date']		= $this->db->f('end_date');
				$project[0]['coordinator']	= $this->db->f('coordinator');
				$project[0]['customer']		= $this->db->f('customer');
				$project[0]['status']		= $this->db->f('status');
				$project[0]['description']	= $this->db->f('descr');
				$project[0]['title']		= $this->db->f('title');
				$project[0]['budget']		= $this->db->f('budget');
			}
			return $project;
		}

		function select_project_list($type,$selected = '')
		{
			global $phpgw;

			$projects = $this->read_projects($start, False, $query, $filter, $sort, $order, $status, $cat_id, $type);

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

			if ($status)
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
			$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$hours[$i]['id']			= $this->db->f('id');
				$hours[$i]['project_id']	= $this->db->f('project_id');
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

		function select_activities_list($project_id = '',$billable = False)
		{
			global $phpgw,$phpgw_info;
			$currency = $phpgw_info['user']['preferences']['common']['currency'];

			if ($billable)
			{
				$bill_filter = " AND billable='Y'";
			}
			else
			{
				$bill_filter = " AND billable='N'";
			}

			$this->db2->query("SELECT activity_id from phpgw_p_projectactivities WHERE project_id='$project_id' $bill_filter",__LINE__,__FILE__);
			while ($this->db2->next_record())
			{
				$selected[] = array('activity_id' => $this->db2->f('activity_id'));
			}

			$this->db->query("SELECT id,descr,billperae FROM phpgw_p_activities ORDER BY descr asc");
			while ($this->db->next_record())
			{
				$activities_list .= '<option value="' . $this->db->f('id') . '"';
				for ($i=0;$i<count($selected);$i++)
				{
					if($selected[$i]['activity_id'] == $this->db->f('id'))
					{
						$activities_list .= ' selected';
					}
				}
				$activities_list .= '>' . $phpgw->strip_html($this->db->f('descr'));

				if($billable)
				{
					$activities_list .= ' ' . $currency . ' ' . $this->db->f('billperae') . ' ' . lang('per workunit');
				}

				$activities_list .= '</option>' . "\n";
			}
			return $activities_list;
		}

		function return_value($item = 'num', $value = '')
		{
			$this->db->query("select num from phpgw_p_projects where id='$value'",__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				$item = $this->db->f('num');
			}
			return $item;
		}

		function return_admins()
		{
			$this->db->query("select account_id,type from phpgw_p_projectmembers WHERE type='aa' OR type='ag'");
			while ($this->db->next_record())
			{
				$admins[] = array('account_id' => $this->db->f('account_id'),
										'type' => $this->db->f('type'));
			}
			return $admins;
		}
	}
?>
