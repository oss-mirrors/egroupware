<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	* This program is part of the GNU project, see http://www.gnu.org/	*
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright 2000 - 2003 Free Software Foundation, Inc               *
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
	/* $Source$ */

	class sostatistics
	{
		function sostatistics()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function stat_filter($values)
		{
			if (checkdate($values['smonth'],$values['sday'],$values['syear']))
			{
				$values['sdate'] = mktime(2,0,0,$values['smonth'],$values['sday'],$values['syear']);
			}

			if (checkdate($values['emonth'],$values['eday'],$values['eyear']))
			{
				$values['edate'] = mktime(2,0,0,$values['emonth'],$values['eday'],$values['eyear']);
			}

			if ($values['billed'])
			{
				$filter = " AND status='billed'";
			}

			if ($values['sdate'])
			{
				$filter .= ' AND start_date >=' . $values['sdate'];
			}

			if ($values['edate'])
			{
				$filter .= ' AND end_date <=' . $values['edate'];
			}

		//	_debug_array($values);
		//	exit;
			return $filter;
		}

		function user_stat_pro($account_id, $values)
		{
			if ($GLOBALS['phpgw_info']['server']['db_type']=='pgsql')
			{
				$join = ' JOIN ';
			}
			elseif ($GLOBALS['phpgw_info']['server']['db_type']=='mysql')
			{
				$join = ' LEFT JOIN ';
			}

			$this->db->query('SELECT title,num,phpgw_p_projects.id as id FROM phpgw_p_projects' . $join . 'phpgw_p_hours ON '
							. 'phpgw_p_hours.employee=' . $account_id . ' GROUP BY title,num,phpgw_p_projects.id',__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$pro[] = array
				(
					'project_id'	=> $this->db->f('id'),
					'num'			=> $this->db->f('num'),
					'title'			=> $this->db->f('title')
				);
			}
//			_debug_array($pro);
//			exit;
			return $pro;
		}

		function stat_hours($type = 'account', $account_id = '', $project_id = '', $values)
		{
			switch($type)
			{
				case 'account': $idfilter = 'WHERE employee=' . $account_id; break;
				case 'project': $idfilter = 'WHERE project_id=' . $project_id; break;
				case 'both':	$idfilter = 'WHERE employee=' . $account_id . ' AND  project_id=' . $project_id; break;
			}

			$this->db->query('SELECT SUM(minutes) as min,num,descr FROM phpgw_p_hours,phpgw_p_activities ' . $idfilter
							. ' AND phpgw_p_hours.activity_id=phpgw_p_activities.id' . $this->stat_filter($values)
							. ' GROUP BY phpgw_p_activities.descr,phpgw_p_activities.num',__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'min'	=> $this->db->f('min'),
					'descr'	=> $this->db->f('descr'),
					'num'	=> $this->db->f('num')
				);
			}
			return $hours;
		}

		function pro_stat_employees($project_id, $values)
		{

			$this->db->query('SELECT employee from phpgw_p_hours WHERE project_id=' . $project_id . $this->stat_filter($values)
							. ' GROUP BY employee',__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$employees[] = array
				(
					'employee'	=> $this->db->f('employee')
				);
			}
			return $employees;
		}
	}
?>
