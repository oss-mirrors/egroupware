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

	class bodeliveries
	{
		var $public_functions = array
		(
			'check_values'			=> True,
			'read_hours'			=> True,
			'read_delivery_hours'	=> True,
			'get_date'				=> True,
			'delivery'				=> True,
			'read_deliveries'		=> True
		);

		function bodeliveries()
		{
			$this->sodeliveries	= CreateObject('projects.sodeliveries');
		}

		function read_hours($project_id)
		{
			$hours = $this->sodeliveries->read_hours($project_id);
			return $hours;
		}

		function read_delivery_hours($project_id,$delivery_id)
		{
			$hours = $this->sodeliveries->read_hours($project_id,$delivery_id);
			return $hours;
		}

		function read_deliveries($query, $sort, $order, $limit, $project_id)
		{
			$del = $this->sodeliveries->read_deliveries($query, $sort, $order, $limit, $project_id);
			$this->total_records = $this->sodeliveries->total_records;
			return $del;
		}

		function get_date($delivery_id)
		{
			$date = $this->bodeliveries->get_date($delivery_id);
			return $date;
		}

		function check_values($values)
		{
			if (!$values['choose'])
			{
				if (!$values['delivery_num'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$num = $this->sodeliveries->exists($values['delivery_num']);
					if ($num)
					{
						$error[] = lang('That ID has been used already !');
					}
				}
			}

			if (! $values['customer'])
			{
				$error[] = lang('You have no customer selected !');				
			}

			if (! checkdate($values['month'],$values['day'],$values['year']))
			{
				$error[] = lang('You have entered an invalid date !');
			}

			if (is_array($error))
			{
				return $error;
			}
		}

		function delivery($values)
		{
			if ($values['choose'])
			{
				$values['delivery_num'] = $this->sodeliveries->create_deliveryid();
			}

			$values['date'] = mktime(2,0,0,$values['month'],$values['day'],$values['year']);

			$delivery_id = $this->sodeliveries->delivery($values);
			return $delivery_id;
		}
	}
?>
