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

	class soprojecthours
	{
		var $db;
		var $grants;

		function soprojecthours()
		{
			global $phpgw, $phpgw_info;

			$this->db		= $phpgw->db;
			$this->db2		= $this->db;
			$this->account	= $phpgw_info['user']['account_id'];
		}

		function read_hours($start, $limit = True, $query = '', $filter, $sort = '', $order = '', $state, $project_id)
		{
			global $phpgw, $phpgw_info;

/*			if ($phpgw_info['server']['db_type']=='pgsql')
			{
				$join = " JOIN ";
			}
			else
			{
				$join = " LEFT JOIN ";
			} */

			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = " order by start_date asc";
			}

			$filtermethod = " project_id = '$project_id'";

			if ($state != 'all')
			{
				$filtermethod .= " AND status='$state'";
			}

			if ($filter == 'private')
			{
				$filtermethod .= " AND employee='" . $this->account . "'";
			}

			if ($query)
			{
				$querymethod = " AND (remark like '%$query%' OR start_date like '%$query%' OR end_date like '%$query%' OR minutes like '%$query%' "
							. "OR hours_descr like '%$query%')";
			}

			$sql = "SELECT * FROM phpgw_p_hours WHERE $filtermethod $querymethod";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();
			$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$hours[$i]['id']			= $this->db->f('id');
				$hours[$i]['project_id']	= $this->db->f('project_id');
				$hours[$i]['hours_descr']	= $this->db->f('hours_descr');
				$hours[$i]['status']		= $this->db->f('status');
				$hours[$i]['start_date']	= $this->db->f('start_date');
				$hours[$i]['end_date']		= $this->db->f('end_date');
				$hours[$i]['minutes']		= $this->db->f('minutes');
				$hours[$i]['employee']		= $this->db->f('employee');
				$i++;
			}
			return $hours;
		}

		function read_single_project($project_id)
		{
			$this->db->query("SELECT * from phpgw_p_projects WHERE id='$project_id'",__LINE__,__FILE__);
	
			while($this->db->next_record())
			{
				$project['project_id']	= $this->db->f('id');
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


		function add_project($values, $book_activities, $bill_activities)
		{
			global $phpgw;

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
							. "descr,title,budget,num) values ('" . $values['owner'] . "','" . $values['access'] . "','" . $values['cat'] . "','"
							. time() ."','" . $values['sdate'] . "','" . $values['edate'] . "','" . $values['coordinator'] . "','" . $values['customer']
							. "','" . $values['status'] . "','" . $values['descr'] . "','" . $values['title'] . "','" . $values['budget'] . "','"
							. $values['number'] . "')",__LINE__,__FILE__);

			$this->db->query("SELECT max(id) AS max FROM phpgw_p_projects");
			if($this->db->next_record())
			{
				$p_id = $this->db->f('max');
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
			global $phpgw;

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

				while($activ=each($book__activities))
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

		function return_value($item)
		{
			$this->db->query("select num from phpgw_p_projects where id='$item'",__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				$thing = $this->db->f('num');
			}
			return $thing;
		}

		function exists($num, $project_id = '')
		{
			if ($project_id && ($project_id != 0))
			{
				$editexists = " and id != '$project_id'";
			}

			$this->db->query("select count(*) from phpgw_p_projects where num = '$num' $editexists",__LINE__,__FILE__);
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
	}
?>
