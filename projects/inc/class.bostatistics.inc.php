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

	class bostatistics
	{
		var $start;
		var $query;
		var $order;
		var $sort;
		var $type;

		var $public_functions = array
		(
			'get_userstat_pro'		=> True,
			'get_userstat_hours'	=> True,
			'get_userstat_all'		=> True,
			'get_users'				=> True
		);

		function bostatistics()
		{
			$this->sostatistics	= CreateObject('projects.sostatistics');
		}

		function get_users($type, $start, $sort, $order, $query)
		{
			$users = $GLOBALS['phpgw']->accounts->get_list($type, $start, $sort, $order, $query);
			$this->total_records = $GLOBALS['phpgw']->accounts->total;
			return $users;
		}

		function get_userstat_pro($account_id, $filter)
		{
			$pro = $this->sostatistics->user_stat_pro($account_id, $filter);
			return $pro;
		}

		function get_userstat_hours($account_id, $project_id, $filter)
		{
			$hours = $this->sostatistics->user_stat_hours($account_id, $project_id, $filter);
			return $hours;
		}
	}
?>
