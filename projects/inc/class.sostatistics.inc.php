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

	class sostatistics
	{
		function sostatistics()
		{
			$this->db		= $GLOBALS['phpgw']->db;
		}

		function user_stat_pro($account_id, $filter)
		{
			if ($GLOBALS['phpgw_info']['server']['db_type']=='pgsql')
			{
				$join = " JOIN ";
			}
			else
			{
				$join = " LEFT JOIN ";
			}

			$this->db->query("SELECT title,phpgw_p_projects.id as id FROM phpgw_p_projects $join phpgw_p_hours ON "
							."phpgw_p_hours.employee='$account_id' $filter GROUP BY title,phpgw_p_projects.id");

			while ($this->db->next_record())
			{
				$pro[] = array
				(
					'project_id'	=> $this->db->f('id'),
					'title'			=> $this->db->f('title')
				);
			}
//			_debug_array($pro);
//			exit;
			return $pro;
		}

		function user_stat_hours($account_id, $project_id = '', $filter)
		{
			if ($project_id)
			{
				$project_filter = " AND project_id='" . $project_id . "'";
			}

			$this->db->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE employee='"
							. $account_id .  "'" . $project_filter . " AND phpgw_p_hours.activity_id="
							. "phpgw_p_activities.id " . $filter . " GROUP BY phpgw_p_activities.descr",__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'min'	=> $this->db->f('min'),
					'descr'	=> $this->db->f('descr')
				);
			}
			return $hours;
		}

	/*	function user_stat_all($account_id, $filter)
		{
			$this->db->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE employee='"
							. $account_id . "' AND phpgw_p_hours.activity_id=phpgw_p_activities.id " . $filter
							. " GROUP BY phpgw_p_activities.descr",__LINE__,__FILE__);
		} */
	}
?>
