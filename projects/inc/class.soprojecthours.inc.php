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
			$this->db		= $GLOBALS['phpgw']->db;
			$this->db2		= $this->db;
			$this->account	= $GLOBALS['phpgw_info']['user']['account_id'];
		}

		function read_hours($start, $limit = True, $query = '', $filter, $sort = '', $order = '', $state, $project_id)
		{

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
				$querymethod = " AND (remark like '%$query%' OR minutes like '%$query%' OR hours_descr like '%$query%')";
			}

			$sql = "SELECT * FROM phpgw_p_hours WHERE $filtermethod $querymethod";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();
			$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$hours[$i]['hours_id']		= $this->db->f('id');
				$hours[$i]['project_id']	= $this->db->f('project_id');
				$hours[$i]['hours_descr']	= $this->db->f('hours_descr');
				$hours[$i]['status']		= $this->db->f('status');
				$hours[$i]['sdate']			= $this->db->f('start_date');
				$hours[$i]['edate']			= $this->db->f('end_date');
				$hours[$i]['minutes']		= $this->db->f('minutes');
				$hours[$i]['employee']		= $this->db->f('employee');
				$i++;
			}
			return $hours;
		}

		function read_single_hours($hours_id)
		{
			$this->db->query("SELECT * from phpgw_p_hours WHERE id='$hours_id'",__LINE__,__FILE__);
	
			while($this->db->next_record())
			{
				$hours['hours_id']		= $this->db->f('id');
				$hours['project_id']	= $this->db->f('project_id');
				$hours['pro_parent']	= $this->db->f('pro_parent');
				$hours['hours_descr']	= $this->db->f('hours_descr');
				$hours['status']		= $this->db->f('status');
				$hours['ae_minutes']	= $this->db->f('minutes');
				$hours['sdate']			= $this->db->f('start_date');
				$hours['edate']			= $this->db->f('end_date');
				$hours['employee']		= $this->db->f('employee');
				$hours['activity_id']	= $this->db->f('activity_id');
				$hours['remark']		= $this->db->f('remark');
				$hours['minperae']		= $this->db->f('minperae');
				$hours['billperae']		= $this->db->f('billperae');
			}
			return $hours;
		}

		function add_hours($values)
		{
			$values['ae_minutes']	= $values['hours']*60+$values['minutes'];
			$values['hours_descr']	= addslashes($values['hours_descr']);
			$values['remark']		= addslashes($values['remark']);

			$this->db->query("INSERT into phpgw_p_hours (project_id,activity_id,entry_date,start_date,end_date,hours_descr,remark,minutes,"
							. "status,minperae,billperae,employee,pro_parent) VALUES ('" . $values['project_id'] . "','" . $values['activity_id'] . "','"
							. time() . "','" . $values['sdate'] . "','" . $values['edate'] . "','" . $values['hours_descr'] . "','"
							. $values['remark'] . "','" . $values['ae_minutes'] . "','" . $values['status'] . "','" . $values['minperae']
							. "','" . $values['billperae'] . "','" . $values['employee'] . "','" . $values['pro_parent'] . "')",__LINE__,__FILE__); 
		}

		function edit_hours($values)
		{
			$values['ae_minutes']	= $values['hours']*60+$values['minutes'];
			$values['hours_descr']	= addslashes($values['hours_descr']);
			$values['remark']		= addslashes($values['remark']);

			$this->db->query("UPDATE phpgw_p_hours SET activity_id='" . $values['activity_id'] . "',entry_date='" . time() . "',start_date='"
							. $values['sdate'] . "',end_date='" . $values['edate'] . "',hours_descr='" . $values['hours_descr'] . "',remark='"
							. $values['remark'] . "',minutes='" . $values['ae_minutes'] . "',status='" . $values['status'] . "',minperae='"
							. $values['minperae'] . "',billperae='" . $values['billperae'] . "',employee='" . $values['employee']
							. "' where id='" . $values['hours_id'] . "'",__LINE__,__FILE__);
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

		function delete_hours($hours_id)
		{
			$this->db->query("Delete from phpgw_p_hours where id = '$hours_id'",__LINE__,__FILE__);
		}
	}
?>
