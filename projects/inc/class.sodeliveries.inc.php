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

	class sodeliveries
	{
		var $db;

		function sodeliveries()
		{
			$this->db	= $GLOBALS['phpgw']->db;
			$this->db2	= $this->db;
		}

		function return_join()
		{
			$dbtype = $GLOBALS['phpgw_info']['server']['db_type'];

			switch ($dbtype)
			{
				case 'psql':	$join = " JOIN "; break;
				case 'mysql':	$join = " LEFT JOIN "; break;
			}
			return $join;
		}

		function delivery($values)
		{
			$this->db->query("INSERT INTO phpgw_p_delivery (num,project_id,date,customer) VALUES ('" . $values['delivery_num'] . "','"
							. $values['project_id'] . "','" . time() . "','" . $values['customer'] . "')",__LINE__,__FILE__);

			$this->db2->query("SELECT id from phpgw_p_delivery WHERE num='" . $values['delivery_num'] . "'",__LINE__,__FILE__);
			$this->db2->next_record();
			$delivery_id = $this->db2->f('id');

		//	$phpgw->db->query("DELETE FROM phpgw_p_deliverypos WHERE delivery_id='$delivery_id'");

			while($values['select'] && $entry=each($values['select']))
			{
				$this->db->query("INSERT INTO phpgw_p_deliverypos (delivery_id,hours_id) VALUES ('$delivery_id','" . $entry[0]
								. "')",__LINE__,__FILE__);
				$this->db2->query("UPDATE phpgw_p_hours set status='closed' WHERE status='done' AND id='" . $entry[0] . "'",__LINE__,__FILE__);
				$this->db2->query("UPDATE phpgw_p_hours set dstatus='d' WHERE id='" . $entry[0] . "'",__LINE__,__FILE__);
			}
			return $delivery_id;
		}

		function read_hours($project_id)
		{
			$ordermethod = "order by end_date asc";

			$this->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
							. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae FROM phpgw_p_hours " . $this->return_join()
							. "phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id WHERE phpgw_p_hours.dstatus='o' "
							. "AND phpgw_p_hours.project_id='$project_id' $ordermethod",__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'hours_id'		=> $this->db->f('id'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'act_descr'		=> $this->db->f('descr'),
					'status'		=> $this->db->f('status'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'minutes'		=> $this->db->f('minutes'),
					'minperae'		=> $this->db->f('minperae')
				);
			}
			return $hours;
		}

		function read_delivery_hours($project_id, $delivery_id)
		{
			$ordermethod = "order by end_date asc";

			$this->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
							. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae FROM phpgw_p_hours " . $this->return_join()
							."phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id " . $this->return_join() . "phpgw_p_deliverypos "
							. "ON phpgw_p_hours.id=phpgw_p_deliverypos.hours_id WHERE phpgw_p_hours.project_id='$project_id' AND "
							. "phpgw_p_deliverypos.delivery_id='$delivery_id' $ordermethod",__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'hours_id'		=> $this->db->f('id'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'act_descr'		=> $this->db->f('descr'),
					'status'		=> $this->db->f('status'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'minutes'		=> $this->db->f('minutes'),
					'minperae'		=> $this->db->f('minperae')
				);
			}
			return $hours;
		}

		function get_date($delivery_id)
		{
			$this->db->query("SELECT date FROM phpgw_p_delivery WHERE id='$delivery_id'",__LINE__,__FILE__);
			if ($phpgw->db->next_record())
			{
				$date = $this->db->f('date');
			}    
			return $date;
		}

		function exists($num)
		{
			$this->db->query("select count(*) from phpgw_p_delivery where num = '$num'",__LINE__,__FILE__);

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
