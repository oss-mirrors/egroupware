<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000, 2001 Bettina Gille                            *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */

	class soprojects
	{
		var $db;
		var $grants;

		function soprojects()
		{
			$this->db		= $GLOBALS['phpgw']->db;
			$this->db2		= $this->db;
			$this->grants	= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->account	= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->currency = $GLOBALS['phpgw_info']['user']['preferences']['common']['currency'];
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
				$filtermethod = " ( coordinator=" . $this->account;
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
				$filtermethod = " coordinator='" . $this->account . "'";
			}
			else
			{
				$filtermethod = " coordinator='" . $this->account . "' AND access='private'";
			}

			if ($cat_id)
			{
				$filtermethod .= " AND category='$cat_id' ";
			}

			switch($type)
			{
				case 'mains':	$filtermethod .= " AND parent = '0' "; break;
				case 'subs' :	$filtermethod .= " AND parent = '$pro_parent' AND parent != '0' "; break;
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
				$projects[$i]['project_id']		= $this->db->f('id');
				$projects[$i]['parent']			= $this->db->f('parent');
				$projects[$i]['number']			= $this->db->f('num');
				$projects[$i]['access']			= $this->db->f('access');
				$projects[$i]['cat']			= $this->db->f('category');
				$projects[$i]['sdate']			= $this->db->f('start_date');
				$projects[$i]['edate']			= $this->db->f('end_date');
				$projects[$i]['coordinator']	= $this->db->f('coordinator');
				$projects[$i]['customer']		= $this->db->f('customer');
				$projects[$i]['status']			= $this->db->f('status');
				$projects[$i]['descr']			= $this->db->f('descr');
				$projects[$i]['title']			= $this->db->f('title');
				$projects[$i]['budget']			= $this->db->f('budget');
				$i++;
			}
			return $projects;
		}

		function read_single_project($project_id)
		{
			$this->db->query("SELECT * from phpgw_p_projects WHERE id='$project_id'",__LINE__,__FILE__);
	
			if ($this->db->next_record())
			{
				$project['project_id']	= $this->db->f('id');
				$project['owner']		= $this->db->f('owner');
				$project['parent']		= $this->db->f('parent');
				$project['number']		= $this->db->f('num');
				$project['access']		= $this->db->f('access');
				$project['cat']			= $this->db->f('category');
				$project['sdate']		= $this->db->f('start_date');
				$project['edate']		= $this->db->f('end_date');
				$project['coordinator']	= $this->db->f('coordinator');
				$project['customer']	= $this->db->f('customer');
				$project['status']		= $this->db->f('status');
				$project['descr']		= $this->db->f('descr');
				$project['title']		= $this->db->f('title');
				$project['budget']		= $this->db->f('budget');
			}
			return $project;
		}

		function select_project_list($type,$selected = '')
		{
			$projects = $this->read_projects($start, False, $query, $filter, $sort, $order, $status, $cat_id, $type);

			for ($i=0;$i<count($projects);$i++)
			{
				$pro_select .= '<option value="' . $projects[$i]['project_id'] . '"';
				if ($projects[$i]['project_id'] == $selected)
				{
					$pro_select .= ' selected';
				}
				if ($projects[$i]['title'])
				{
					$pro_select .= '>' . $GLOBALS['phpgw']->strip_html($projects[$i]['title']) . ' [ '
								. $GLOBALS['phpgw']->strip_html($projects[$i]['number']) . ' ]';
				}
				else
				{
					$pro_select .= '>' . $GLOBALS['phpgw']->strip_html($projects[$i]['number']);
				}
				$pro_select .= '</option>';
			}
			return $pro_select;
		}

		function add_project($values, $book_activities, $bill_activities)
		{
			if (!$values['budget'])
			{
				$values['budget'] = 0;
			}

			$values['owner'] = $this->account;
			$values['descr'] = addslashes($values['descr']);
			$values['title'] = addslashes($values['title']);
			$values['number'] = addslashes($values['number']);

			$table = 'phpgw_p_projects';

			$this->db->lock($table);

			$this->db->query("insert into phpgw_p_projects (owner,access,category,entry_date,start_date,end_date,coordinator,customer,status,"
							. "descr,title,budget,num,parent) values ('" . $values['owner'] . "','" . $values['access'] . "','" . $values['cat'] . "','"
							. time() ."','" . $values['sdate'] . "','" . $values['edate'] . "','" . $values['coordinator'] . "','" . $values['customer']
							. "','" . $values['status'] . "','" . $values['descr'] . "','" . $values['title'] . "','" . $values['budget'] . "','"
							. $values['number'] . "','" . $values['parent'] . "')",__LINE__,__FILE__);

			$this->db->query("SELECT max(id) AS max FROM phpgw_p_projects");
			if($this->db->next_record())
			{
				$p_id = $this->db->f('max');
			}

			$this->db->unlock();

	//		if ($action == 'mains')
	//		{
				if ($p_id && ($p_id != 0))
				{
					if (count($book_activities) != 0)
					{
						while($activ=each($book_activities))
						{
							$this->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','"
										. $activ[1] . "','N')",__LINE__,__FILE__);
						}
					}

					if (count($bill_activities) != 0)
					{
						while($activ=each($bill_activities))
						{
							$this->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','"
										. $activ[1] . "','Y')",__LINE__,__FILE__);
						}
					}
				}
//			}
		}

		function edit_project($values, $book_activities, $bill_activities)
		{
			if (!$values['budget'])
			{
				$values['budget'] = 0;
			}

			$values['descr'] = addslashes($values['descr']);
			$values['title'] = addslashes($values['title']);
			$values['number'] = addslashes($values['number']);

			$this->db->query("update phpgw_p_projects set access='" . $values['access'] . "', category='" . $values['cat'] . "', entry_date='"
							. time() . "', start_date='" . $values['sdate'] . "', end_date='" . $values['edate'] . "', coordinator='"
							. $values['coordinator'] . "', customer='" . $values['customer'] . "', status='" . $values['status'] . "', descr='"
							. $values['descr'] . "', title='" . $values['title'] . "', budget='" . $values['budget'] . "', num='"
							. $values['number'] . "' where id='" . $values['project_id'] . "'",__LINE__,__FILE__);


			if (count($book_activities) != 0)
			{
				$this->db2->query("delete from phpgw_p_projectactivities where project_id='" . $values['project_id']
								. "' and billable='N'",__LINE__,__FILE__);

				while($activ=each($book_activities))
				{
					$this->db->query("insert into phpgw_p_projectactivities (project_id, activity_id, billable) values ('" . $values['project_id']
									. "','$activ[1]','N')",__LINE__,__FILE__);
				}
			}


			if (count($bill_activities) != 0)
			{
				$this->db2->query("delete from phpgw_p_projectactivities where project_id='" . $values['project_id']
								. "' and billable='Y'",__LINE__,__FILE__);

				while($activ=each($bill_activities))
				{
					$phpgw->db->query("insert into phpgw_p_projectactivities (project_id, activity_id, billable) values ('" . $values['project_id']
									. "','$activ[1]','Y')",__LINE__,__FILE__);
				}
			}
		}

		function select_activities_list($project_id = '',$billable = False)
		{
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

			$this->db->query("SELECT id,num,descr,billperae FROM phpgw_p_activities ORDER BY descr asc");
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
				$activities_list .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
										. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				if($billable)
				{
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . lang('per workunit');
				}

				$activities_list .= '</option>' . "\n";
			}
			return $activities_list;
		}

		function select_pro_activities($project_id = '', $pro_parent, $billable = False)
		{
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

			$this->db->query("SELECT a.id, a.num, a.descr, a.billperae, pa.activity_id FROM phpgw_p_activities as a, phpgw_p_projectactivities as pa"
							. " WHERE pa.project_id='$pro_parent' $bill_filter AND pa.activity_id=a.id ORDER BY a.descr asc");
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

				if (! is_array($selected))
				{
					$activities_list .= ' selected';
				}

				$activities_list .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
										. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';

				if($billable)
				{
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . lang('per workunit');
				}

				$activities_list .= '</option>' . "\n";
			}
			return $activities_list;
		}

		function select_hours_activities($project_id, $activity = '')
		{
			$this->db->query("SELECT activity_id,num, descr,billperae,billable FROM phpgw_p_projectactivities,phpgw_p_activities WHERE project_id ='"
							. $project_id . "' AND phpgw_p_projectactivities.activity_id=phpgw_p_activities.id order by descr asc",__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours_act .= '<option value="' . $this->db->f('activity_id') . '"';
				if($this->db->f('activity_id') == $activity)
				{
					$hours_act .= ' selected';
				}
				$hours_act .= '>' . $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' ['
									. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';

				if($this->db->f('billable') == 'Y')
				{
					$hours_act .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . lang('per workunit');
				}
				$hours_act .= '</option>' . "\n";
			}
			return $hours_act;
		}

		function return_value($action,$item)
		{
			if ($action == 'pro')
			{
				$this->db->query("select num, title from phpgw_p_projects where id='$item'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$thing = $GLOBALS['phpgw']->strip_html($this->db->f('title')) . ' [' . $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				}
			}
			if ($action == 'act')
			{			
				$this->db->query("select num, descr from phpgw_p_activities where id='$item'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$thing = $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' [' . $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				}
			}
			return $thing;
		}

		function exists($action, $check = 'number', $num = '', $pa_id = '')
		{
			switch ($action)
			{
				case 'mains': $p_table = ' phpgw_p_projects'; break;
				case 'subs'	: $p_table = ' phpgw_p_projects'; break;
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
			}

			if ($check == 'number')
			{
				if ($pa_id && ($pa_id != 0))
				{
					$editexists = " and id != '$pa_id'";
				}

				$this->db->query("select count(*) from $p_table where num = '$num' $editexists",__LINE__,__FILE__);
			}

			if ($check == 'par')
			{
				$this->db->query("select count(*) from phpgw_p_projects where parent = '$pa_id'",__LINE__,__FILE__);
			}
			$this->db->next_record();

			if ($this->db->f(0))
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function return_admins($type = 'all')
		{
			switch ($type)
			{
				case 'all': $filter = " type='aa' or type='ag'"; break;
				case 'aa': $filter = " type='aa'"; break;
				case 'ag': $filter = " type='ag'"; break;
			}

			$this->db->query("select account_id,type from phpgw_p_projectmembers WHERE $filter");
			$this->total_records = $this->db->num_rows();
			while ($this->db->next_record())
			{
				$admins[] = array('account_id' => $this->db->f('account_id'),
										'type' => $this->db->f('type'));
			}
			return $admins;
		}

		function edit_admins($users = '', $groups = '')
		{
			$this->db->query("DELETE from phpgw_p_projectmembers WHERE type='aa' OR type='ag'",__LINE__,__FILE__);

			if (count($users) != 0)
			{
				while($activ=each($users))
				{
					$this->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'$activ[1]','aa')",__LINE__,__FILE__);
				}
			}

			if (count($groups) != 0)
			{
				while($activ=each($groups))
				{
					$this->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'$activ[1]','ag')",__LINE__,__FILE__);
				}
			}
		}

// returns project-,invoice- and delivery-ID

		function add_leading_zero($num)  
		{
/*			if ($id_type == "hex")
			{
				$num = hexdec($num);
				$num++;
				$num = dechex($num);
			}
			else
			{
				$num++;
			} */

			$num++;

			if (strlen($num) == 4)
				$return = $num;
			if (strlen($num) == 3)
				$return = "0$num";
			if (strlen($num) == 2)
				$return = "00$num";
			if (strlen($num) == 1)
				$return = "000$num";
			if (strlen($num) == 0)
				$return = "0001";

			return strtoupper($return);
		}

		function create_projectid()
		{
			$year = $GLOBALS['phpgw']->common->show_date(time(),'Y');
			$prefix = 'P-' . $year . '-';

			$this->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_jobid($pro_parent)
		{
			$this->db->query("select num from phpgw_p_projects where id='$pro_parent'");
			$this->db->next_record();
			$prefix = $this->db->f('num') . '/';

			$this->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_activityid()
		{
			$year = $GLOBALS['phpgw']->common->show_date(time(),'Y');
			$prefix = 'A-' . $year . '-';

			$this->db->query("select max(num) from phpgw_p_activities where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function read_activities($start, $limit = True, $query = '', $sort = '', $order = '', $cat_id = '')
		{
			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = " order by num asc";
			}

			if ($query)
			{
				$filtermethod = " where (descr like '%$query%' or num like '%$query%' or minperae like '%$query%' or billperae like '%$query%')";

				if ($cat_id)
				{
					$filtermethod .= " AND category='$cat_id' ";
				}
			}
			else
			{
				if ($cat_id)
				{
					$filtermethod = " WHERE category='$cat_id' ";
				}
			}

			$sql = "select * from phpgw_p_activities $filtermethod";
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
				$act[$i]['activity_id']	= $this->db->f('id');
				$act[$i]['cat']			= $this->db->f('category');
				$act[$i]['number']		= $this->db->f('num');
				$act[$i]['descr']		= $this->db->f('descr');
				$act[$i]['remarkreq']	= $this->db->f('remarkreq');
				$act[$i]['billperae']	= $this->db->f('billperae');
				$act[$i]['minperae']	= $this->db->f('minperae');
				$i++;
			}
			return $act;
		}

		function read_single_activity($activity_id)
		{
			$this->db->query("SELECT * from phpgw_p_activities WHERE id='$activity_id'",__LINE__,__FILE__);
	
			if ($this->db->next_record())
			{
				$act['activity_id']	= $this->db->f('id');
				$act['cat']			= $this->db->f('category');
				$act['number']		= $this->db->f('num');
				$act['descr']		= $this->db->f('descr');
				$act['remarkreq']	= $this->db->f('remarkreq');
				$act['billperae']	= $this->db->f('billperae');
				$act['minperae']	= $this->db->f('minperae');
				return $act;
			}
		}

		function add_activity($values)
		{
			$values['number']	= addslashes($values['number']);
			$values['descr'] 	= addslashes($values['descr']);

			$this->db->query("insert into phpgw_p_activities (num,category,descr,remarkreq,billperae,minperae) values ('"
							. $values['number'] . "','" . $values['cat'] . "','" . $values['descr'] . "','" . $values['remarkreq'] . "','"
							. $values['billperae'] . "','" . $values['minperae'] . "')",__LINE__,__FILE__);
		}


		function edit_activity($values)
		{
			$values['number']	= addslashes($values['number']);
			$values['descr']	= addslashes($values['descr']);

			$this->db->query("update phpgw_p_activities set num='" . $values['number'] . "', category='" . $values['cat']
							. "',remarkreq='" . $values['remarkreq'] . "',descr='" . $values['descr'] . "',billperae='"
							. $values['billperae'] . "',minperae='" . $values['minperae'] . "' where id='" . $values['activity_id']
							. "'",__LINE__,__FILE__);
		}

		function delete_pa($action, $pa_id, $subs = '')
		{
			switch ($action)
			{
				case 'mains': $p_table = ' phpgw_p_projects'; break;
				case 'subs'	: $p_table = ' phpgw_p_projects'; break;
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
			}

			if ($subs)
			{
				$subdelete = " or parent = '$pa_id'";
			}

			$this->db->query("Delete from $p_table where id = '$pa_id' $subdelete",__LINE__,__FILE__);
		}
	}
?>
