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

	class boprojecthours
	{
		var $start;
		var $query;
		var $filter;
		var $order;
		var $sort;
		var $state;

		var $public_functions = array
		(
			'list_hours'			=> True,
			'check_values'			=> True,
			'save_project'			=> True,
			'read_single_project'	=> True,
			'delete_project'		=> True,
			'exists'				=> True
		);

		function boprojecthours($session=False)
		{
			global $phpgw;

			$this->soprojecthours	= CreateObject('projects.soprojecthours');

			if ($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
			}

			global $start, $query, $filter, $order, $sort, $state;

			if(isset($start)) { $this->start = $start; }
			if(isset($query)) { $this->query = $query; }
			if(!empty($filter)) { $this->filter = $filter; }
			if(isset($sort)) { $this->sort = $sort; }
			if(isset($order)) { $this->order = $order; }
			if(isset($state)) { $this->state = $state; }
		}

		function save_sessiondata($data)
		{
			global $phpgw;

			if ($this->use_session)
			{
				$phpgw->session->appsession('session_data','projects_hours',$data);
			}
		}

		function read_sessiondata()
		{
			global $phpgw;

			$data = $phpgw->session->appsession('session_data','projects_hours');

			$this->start	= $data['start'];
			$this->query	= $data['query'];
			$this->filter	= $data['filter'];
			$this->order	= $data['order'];
			$this->sort		= $data['sort'];
			$this->state	= $data['state'];
		}

		function list_hours($start, $limit, $query, $filter, $sort, $order, $state, $project_id)
		{
			$hours_list = $this->soprojecthours->read_hours($start, $limit, $query, $filter, $sort, $order, $state, $project_id);
			$this->total_records = $this->soprojecthours->total_records;
			return $hours_list;
		}

		function read_single_project($project_id)
		{
			$single_pro = $this->soprojects->read_single_project($project_id);

			return $single_pro;
		}

		function check_values($values, $book_activities, $bill_activities)
		{
			global $phpgw;

			if (strlen($values['descr']) >= 8000)
			{
				$error[] = lang('Description can not exceed 8000 characters in length');
			}

			if (! $values['choose'])
			{
				if (! $values['number'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$exists = $this->soprojects->exists($values['number'], $values['project_id']);

					if ($exists)
					{
						$error[] = lang('That ID has been used already !');
					}
				}
			}

			if ((! $book_activities) && (! $bill_activities))
			{
				$error[] = lang('Please choose activities for that project first !');
			}

			if ($values['smonth'] || $values['sday'] || $values['syear'])
			{
					if (! checkdate($values['smonth'],$values['sday'],$values['syear']))
					{
						$error[] = lang('You have entered an starting invalid date');
					}
			}

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				if (! checkdate($values['emonth'],$values['eday'],$values['eyear']))
				{
					$error[] = lang('You have entered an ending invalid date');
				}
			}

/*			if ($values['edate'] < $values['sdate'] && $values['edate'] && $values['sdate'])
			{
				$error[] = lang('Ending date can not be before start date');
			} */

			if (is_array($error))
			{
				return $error;
			}
		}

		function save_project($values, $book_activities, $bill_activities)
		{
			global $phpgw;

			if ($values['choose'])
			{
				$values['number'] = $this->soprojects->create_projectid();
			}

			if ($values['access'])
			{
				$values['access'] = 'private';
			}
			else
			{
				$values['access'] = 'public';
			}

			if ($values['smonth'] || $values['sday'] || $values['syear'])
			{
				$values['sdate'] = mktime(0,0,0,$values['smonth'], $values['sday'], $values['syear']);
			}

            if (!$values['sdate'])
            {
                $values['sdate'] = time();
            }

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				$values['edate'] = mktime(2,0,0,$values['emonth'],$values['eday'],$values['eyear']);
			}

			if ($values['project_id'])
			{
				if ($values['project_id'] != 0)
				{
					$this->soprojects->edit_project($values, $book_activities, $bill_activities);
				}
			}
			else
			{
				$this->soprojects->add_project($values, $book_activities, $bill_activities);
			}
		}
	}
?>
