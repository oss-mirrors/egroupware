<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000,2001,2002 Bettina Gille                        *
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
			$this->year		= $GLOBALS['phpgw']->common->show_date(time(),'Y');
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

		function bill_lang()
		{
			switch ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'])
			{
				case 'wu':	$l = lang('per workunit'); break;
				case 'h':	$l = lang('per hour'); break;
				default	:	$l = lang('per hour/workunit');
            }
			return $l;
		}


		function read_projects($start, $limit = True, $query = '', $filter = '', $sort = '', $order = '', $status = '', $cat_id = '', $type = 'mains', $pro_parent = '')
		{
			if ($status)
			{
				$statussort = " AND status = '" . $status . "' ";
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
				if ($this->isprojectadmin('pad') || $this->isbookkeeper('pbo'))
				{
					$filtermethod = " ( access != 'private' OR coordinator ='" . $this->account . "' )";
				}
				else
				{
					$filtermethod = " ( coordinator='" . $this->account . "'";
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
				case 'amains':	$filtermethod .= " AND parent = '0' "; break;
				case 'asubs':	$filtermethod .= " AND parent = '$pro_parent' AND parent != '0' "; break;
			}

			if ($query)
			{
				$querymethod = " AND (title like '%$query%' OR num like '%$query%' OR descr like '%$query%') ";
			}

			$sql = "SELECT * from phpgw_p_projects WHERE $filtermethod $statussort $querymethod";

			if ($limit)
			{
				$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $ordermethod,__LINE__,__FILE__);
			}

			$this->total_records = $this->db->num_rows();

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

		function select_project_list($type, $status, $selected = '')
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
			$values['owner']	= $this->account;
			$values['descr']	= $this->db->db_addslashes($values['descr']);
			$values['title']	= $this->db->db_addslashes($values['title']);
			$values['number']	= $this->db->db_addslashes($values['number']);

			$table = 'phpgw_p_projects';

			$this->db->lock($table);

			$this->db->query("insert into phpgw_p_projects (owner,access,category,entry_date,start_date,end_date,coordinator,customer,status,"
							. "descr,title,budget,num,parent) values ('" . $values['owner'] . "','" . $values['access'] . "','" . $values['cat'] . "','"
							. time() ."','" . $values['sdate'] . "','" . $values['edate'] . "','" . $values['coordinator'] . "','" . $values['customer']
							. "','" . $values['status'] . "','" . $values['descr'] . "','" . $values['title'] . "','" . $values['budget'] . "','"
							. $values['number'] . "','" . $values['parent'] . "')",__LINE__,__FILE__);

			$this->db->query("SELECT max(id) FROM phpgw_p_projects");
			if($this->db->next_record())
			{
				$p_id = $this->db->f(0);
			}

			$this->db->unlock();

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
		}

		function edit_project($values, $book_activities, $bill_activities)
		{
			$values['descr']	= $this->db->db_addslashes($values['descr']);
			$values['title']	= $this->db->db_addslashes($values['title']);
			$values['number']	= $this->db->db_addslashes($values['number']);

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
					$this->db->query("insert into phpgw_p_projectactivities (project_id, activity_id, billable) values ('" . $values['project_id']
									. "','$activ[1]','Y')",__LINE__,__FILE__);
				}
			}
		}

		function activities_list($project_id = '',$billable = False)
		{
			if ($billable)
			{
				$bill_filter = " AND billable='Y'";
			}
			else
			{
				$bill_filter = " AND billable='N'";
			}

			$this->db->query("SELECT phpgw_p_activities.id,num,descr,billperae,activity_id from phpgw_p_activities,phpgw_p_projectactivities "
							. "WHERE phpgw_p_projectactivities.project_id='" . $project_id . "' AND phpgw_p_activities.id="
							. "phpgw_p_projectactivities.activity_id" . $bill_filter,__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$act[] = array
				(
					'num'		=> $this->db->f('num'),
					'descr'		=> $this->db->f('descr'),
					'billperae'	=> $this->db->f('billperae')
				);
			}
			return $act;
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
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
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
					$activities_list .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
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
					$hours_act .= ' ' . $this->currency . ' ' . $this->db->f('billperae') . ' ' . $this->bill_lang();
				}
				$hours_act .= '</option>' . "\n";
			}
			return $hours_act;
		}

		function return_value($action,$item)
		{
			if ($action == 'act')
			{			
				$this->db->query("SELECT num,descr from phpgw_p_activities where id='" . $item . "'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$thing = $GLOBALS['phpgw']->strip_html($this->db->f('descr')) . ' [' . $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']';
				}
			}
			elseif ($action == 'co')
			{
				$this->db->query("SELECT coordinator from phpgw_p_projects where id='" . $item . "'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					$thing = $this->db->f('coordinator');
				}
			}
			else
			{
				switch ($action)
				{
					case 'pro':		$column = ' num,title '; break;
					case 'edate':	$column = ' end_date '; break;
					case 'sdate':	$column = ' start_date '; break;
				}

				$this->db->query('SELECT ' . $column . "from phpgw_p_projects where id='" . $item . "'",__LINE__,__FILE__);
				if ($this->db->next_record())
				{
					switch ($action)
					{
						case 'pro':		$thing = $GLOBALS['phpgw']->strip_html($this->db->f('title')) . ' ['
											. $GLOBALS['phpgw']->strip_html($this->db->f('num')) . ']'; break;
						case 'edate':	$thing = $this->db->f('end_date'); break;
						case 'sdate':	$thing = $this->db->f('start_date'); break;
					}
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

		function return_admins($action, $type = 'all')
		{
			if ($action == 'pad')
			{
				switch ($type)
				{
					case 'all': $filter = " type='aa' or type='ag'"; break;
					case 'aa': $filter = " type='aa'"; break;
					case 'ag': $filter = " type='ag'"; break;
				}
			}
			else
			{
				switch ($type)
				{
					case 'all': $filter = " type='ba' or type='bg'"; break;
					case 'aa': $filter = " type='ba'"; break;
					case 'ag': $filter = " type='bg'"; break;
				}
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

		function isprojectadmin($action)
		{
			$admin_groups = $GLOBALS['phpgw']->accounts->membership($this->account);
			$admins = $this->return_admins($action);

			for ($i=0;$i<count($admins);$i++)
			{
				if ($admins[$i]['type']=='aa')
				{
					if ($admins[$i]['account_id'] == $this->account)
					return True;
				}
				elseif ($admins[$i]['type']=='ag')
				{
					if (is_array($admin_groups))
					{
						for ($j=0;$j<count($admin_groups);$j++)
						{
							if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
							return True;
						}
					}
				}
				else
				{
					return False;
				}
			}
		}

		function isbookkeeper($action)
		{
			$admin_groups = $GLOBALS['phpgw']->accounts->membership($this->account);
			$admins = $this->return_admins($action);

			for ($i=0;$i<count($admins);$i++)
			{
				if ($admins[$i]['type']=='ba')
				{
					if ($admins[$i]['account_id'] == $this->account)
					return True;
				}
				elseif ($admins[$i]['type']=='bg')
				{
					if (is_array($admin_groups))
					{
						for ($j=0;$j<count($admin_groups);$j++)
						{
							if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
							return True;
						}
					}
				}
				else
				{
					return False;
				}
			}
		}

		function edit_admins($action, $users = '', $groups = '')
		{
			if ($action == 'pad')
			{
				$ag = 'ag';
				$aa = 'aa';
			}
			else
			{
				$ag = 'bg';
				$aa = 'ba';
			}

			$this->db->query("DELETE from phpgw_p_projectmembers WHERE type='" . $aa . "' OR type='" . $ag . "'",__LINE__,__FILE__);

			if (count($users) != 0)
			{
				while($activ=each($users))
				{
					$this->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'" . $activ[1] . "','"
									. $aa . "')",__LINE__,__FILE__);
				}
			}

			if (count($groups) != 0)
			{
				while($activ=each($groups))
				{
					$this->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'" . $activ[1] . "','"
									. $ag . "')",__LINE__,__FILE__);
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
			$prefix = 'P-' . $this->year . '-';

			$this->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%') and parent=0");
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
			$prefix = 'A-' . $this->year . '-';

			$this->db->query("select max(num) from phpgw_p_activities where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_deliveryid()
		{
			$prefix = 'D-' . $this->year . '-';
			$this->db->query("select max(num) from phpgw_p_delivery where num like ('$prefix%')");
			$this->db->next_record();
			$max = $this->add_leading_zero(substr($this->db->f(0),-4));

			return $prefix . $max;
		}

		function create_invoiceid()
		{
			$prefix = 'I-' . $this->year . '-';
			$this->db->query("select max(num) from phpgw_p_invoice where num like ('$prefix%')");
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
			$values['number']	= $this->db->db_addslashes($values['number']);
			$values['descr'] 	= $this->db->db_addslashes($values['descr']);

			$this->db->query("insert into phpgw_p_activities (num,category,descr,remarkreq,billperae,minperae) values ('"
							. $values['number'] . "','" . $values['cat'] . "','" . $values['descr'] . "','" . $values['remarkreq'] . "','"
							. $values['billperae'] . "','" . $values['minperae'] . "')",__LINE__,__FILE__);
		}

		function edit_activity($values)
		{
			$values['number']	= $this->db->db_addslashes($values['number']);
			$values['descr']	= $this->db->db_addslashes($values['descr']);

			$this->db->query("update phpgw_p_activities set num='" . $values['number'] . "', category='" . $values['cat']
							. "',remarkreq='" . $values['remarkreq'] . "',descr='" . $values['descr'] . "',billperae='"
							. $values['billperae'] . "',minperae='" . $values['minperae'] . "' where id='" . $values['activity_id']
							. "'",__LINE__,__FILE__);
		}

		function delete_pa($action, $pa_id, $subs = False)
		{
			switch ($action)
			{
				case 'mains': $p_table = ' phpgw_p_projects'; break;
				case 'subs'	: $p_table = ' phpgw_p_projects'; break;
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
			}

			if ($subs)
			{
				$subdelete = " OR parent ='" . $pa_id . "'";
			}

			$this->db->query("DELETE from $p_table where id='" . $pa_id . "'" . $subdelete,__LINE__,__FILE__);

			if ($action == 'act')
			{
				$this->db->query("DELETE from phpgw_p_projectactivities where activity_id='" . $pa_id . "'",__LINE__,__FILE__); 
			}

			if ($action == 'mains' || $action == 'subs')
			{
				if ($subs)
				{
					$subdelete = " or pro_parent='" . $pa_id . "'";
				}

				$this->db->query("DELETE from phpgw_p_hours where project_id='" . $pa_id . "'" . $subdelete,__LINE__,__FILE__); 

				$this->db->query("select id from phpgw_delivery where project_id='" . $pa_id . "'",__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$del[] = array
					(
						'id'	=> $this->db->f('id')
					);
				}

				for ($i=0;$i<=count($del);$i++)
				{
					$this->db->query("Delete from phpgw_p_deliverypos where delivery_id='" . $del[$i]['id'] . "'",__LINE__,__FILE__);
				}

				$this->db->query("DELETE from phpgw_p_delivery where project_id='" . $pa_id . "'",__LINE__,__FILE__);

				$this->db->query("select id from phpgw_invoice where project_id='" . $pa_id . "'",__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$del[] = array
					(
						'id'	=> $this->db->f('id')
					);
				}

				for ($i=0;$i<=count($del);$i++)
				{
					$this->db->query("Delete from phpgw_p_invoicepos where invoice_id='" . $del[$i]['id'] . "'",__LINE__,__FILE__);
				}

				$this->db->query("DELETE from phpgw_p_invoice where project_id='" . $pa_id . "'",__LINE__,__FILE__);
			}
		}

		function delete_pa($action, $pa_id, $subs = False)
		{
			if ($action == 'co')
			{
				$pro_id = $pa_id;
				$this->db->query("delete from phpgw_p_hours where employee='" . $pro_id . "'",__LINE__,__FILE__);
				$this->db->query("select id from phpgw_p_projects where coordinator='" . $pro_id . "'",__LINE__,__FILE__);

				while ($this->db->next_record())
				{
					$pa_id[] = array
					(
						'id' => $this->db->f('id')
					);
				}

				if (is_array($pa_id))
				{
					$action = 'subs';
				}
			}

			switch ($action)
			{
				case 'mains': $p_table = ' phpgw_p_projects'; break;
				case 'subs'	: $p_table = ' phpgw_p_projects'; break;
				case 'act'	: $p_table = ' phpgw_p_activities '; break;
			}

			if (is_array($pa_id))
			{
				while (list($null,$drop) = each($pa_id))
				{
					$drop_list[] = $drop;
				}
				reset($drop_list);
//				_debug_array($drop_list);
//				exit;

				if ($subs)
				{
					$subdelete = " OR parent in (" . implode(',',$drop_list) . ")";
				}

				$this->db->query("DELETE from $p_table where id in (" . implode(',',$drop_list) . ")" . $subdelete,__LINE__,__FILE__);

				if ($action == 'act')
				{	
					$this->db->query("DELETE from phpgw_p_projectactivities where activity_id in ("
									. implode(',',$drop_list) . ")",__LINE__,__FILE__); 
				}

				if ($action == 'mains' || $action == 'subs')
				{
					if ($subs)
					{
						$subdelete = " or pro_parent in (" . implode(',',$drop_list) . ")";
					}

					$this->db->query("DELETE from phpgw_p_hours where project_id in (" . implode(',',$drop_list) . ")"
									. $subdelete,__LINE__,__FILE__); 

					$this->db->query("select id from phpgw_p_delivery where project_id in (" . implode(',',$drop_list) . ")",__LINE__,__FILE__);

					while ($this->db->next_record())
					{
						$del[] = array
						(
							'id'	=> $this->db->f('id')
						);
					}

					for ($i=0;$i<=count($del);$i++)
					{
						$this->db->query("Delete from phpgw_p_deliverypos where delivery_id='" . $del[$i]['id'] . "'",__LINE__,__FILE__);
					}

					$this->db->query("DELETE from phpgw_p_delivery where project_id in (" . implode(',',$drop_list) . ")",__LINE__,__FILE__);

					$this->db->query("select id from phpgw_p_invoice where project_id in (" . implode(',',$drop_list) . ")",__LINE__,__FILE__);

					while ($this->db->next_record())
					{
						$del[] = array
						(
							'id'	=> $this->db->f('id')
						);
					}

					for ($i=0;$i<=count($del);$i++)
					{
						$this->db->query("Delete from phpgw_p_invoicepos where invoice_id='" . $del[$i]['id'] . "'",__LINE__,__FILE__);
					}

					$this->db->query("DELETE from phpgw_p_invoice where project_id in (" . implode(',',$drop_list) . ")",__LINE__,__FILE__);
				}
			}
		}

		function change_owner($old, $new)
		{
			$this->db->query("UPDATE phpgw_p_projects set coordinator='" . $new . "' where coordinator='" . $old . "'",__LINE__,__FILE__);
			$this->db->query("UPDATE phpgw_p_hours set employee='" . $new . "' where employee='" . $old . "'",__LINE__,__FILE__);
			$this->db->query("UPDATE phpgw_p_projectmembers set account_id='" . $new . "' where (account_id='" . $old
							. "' AND type='aa')",__LINE__,__FILE__);
		}
	}
?>
