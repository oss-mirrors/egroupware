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
	// $Source$

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
			'list_hours'		=> True,
			'check_values'		=> True,
			'save_hours'		=> True,
			'read_single_hours'	=> True,
			'delete_hours'		=> True
		);

		function boprojecthours($session=False)
		{
			$this->soprojecthours	= CreateObject('projects.soprojecthours');
			$this->boprojects		= CreateObject('projects.boprojects');

			if ($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
			}

			$start		= get_var('start',array('POST','GET'));
			$query		= get_var('query',array('POST','GET'));
			$sort		= get_var('sort',array('POST','GET'));
			$order		= get_var('order',array('POST','GET'));
			$state		= get_var('state',array('POST','GET'));
			$filter		= get_var('filter',array('POST','GET'));
			$project_id	= get_var('project_id',array('POST','GET'));


			if(isset($start)) { $this->start = $start; }
			if(isset($query)) { $this->query = $query; }
			if(!empty($filter)) { $this->filter = $filter; }
			if(isset($sort)) { $this->sort = $sort; }
			if(isset($order)) { $this->order = $order; }
			if(isset($state)) { $this->state = $state; }
			if(isset($project_id)) { $this->project_id = $project_id; }
		}

		function save_sessiondata($data)
		{
			if ($this->use_session)
			{
				$GLOBALS['phpgw']->session->appsession('session_data','projects_hours',$data);
			}
		}

		function read_sessiondata()
		{
			$data = $GLOBALS['phpgw']->session->appsession('session_data','projects_hours');

			$this->start		= $data['start'];
			$this->query		= $data['query'];
			$this->filter		= $data['filter'];
			$this->order		= $data['order'];
			$this->sort			= $data['sort'];
			$this->state		= $data['state'];
			$this->project_id	= $data['project_id'];
		}

		function list_hours($start, $limit, $query, $filter, $sort, $order, $state, $project_id)
		{
			$hours_list = $this->soprojecthours->read_hours($start, $limit, $query, $filter, $sort, $order, $state, $project_id);
			$this->total_records = $this->soprojecthours->total_records;
			return $hours_list;
		}

		function read_single_hours($hours_id)
		{
			$hours = $this->soprojecthours->read_single_hours($hours_id);
			return $hours;
		}

		function member()
		{
			return $this->boprojects->member($this->project_id);
		}

		function check_values($values)
		{
			if (strlen($values['hours_descr']) >= 255)
			{
				$error[] = lang('Description can not exceed 255 characters in length !');
			}

			if (strlen($values['remark']) >= 8000)
			{
				$error[] = lang('Remark can not exceed 8000 characters in length !');
			}

			if ($values['shour'] && ($values['shour'] != 0) && ($values['shour'] != 12))
			{
				if ($values['sampm']=='pm')
				{
					$values['shour'] = $values['shour'] + 12;
				}
			}

			if ($values['shour'] && ($values['shour'] == 12))
			{
				if ($values['sampm']=='am')
				{
					$values['shour'] = 0;
				}
			}

			if ($values['ehour'] && ($values['ehour'] != 0) && ($values['ehour'] != 12))
			{
				if ($values['eampm']=='pm')
				{
					$values['ehour'] = $values['ehour'] + 12;
				}
			}

			if ($values['ehour'] && ($values['ehour'] == 12))
			{
				if ($values['eampm']=='am')
				{
					$values['ehour'] = 0;
				}
			}

			if (! checkdate($values['smonth'],$values['sday'],$values['syear']))
			{
				$error[] = lang('You have entered an invalid start date !');
			}

			if (! checkdate($values['emonth'],$values['eday'],$values['eyear']))
			{
				$error[] = lang('You have entered an invalid end date !');
			}

			$activity = $this->boprojects->read_single_activity($values['activity_id']);

			if (! is_array($activity))		
			{
				$error[] = lang('You have selected an invalid activity !');
			}
			else
			{
				if ($activity['remarkreq']=='Y' && (!$values['remark']))
				{
					$error[] = lang('Please enter a remark !');
				}
			}

			if (is_array($error))
			{
				return $error;
			}
		}

		function save_hours($values)
		{
			$activity = $this->boprojects->read_single_activity($values['activity_id']);

			$values['minperae']		= $activity['minperae'];
			$values['billperae']	= $activity['billperae'];

			if ($values['shour'] && ($values['shour'] != 0) && ($values['shour'] != 12))
			{
				if ($values['sampm']=='pm')
				{
					$values['shour'] = $values['shour'] + 12;
				}
			}

			if ($values['shour'] && ($values['shour'] == 12))
			{
				if ($values['sampm']=='am')
				{
					$values['shour'] = 0;
				}
			}

			if ($values['ehour'] && ($values['ehour'] != 0) && ($values['ehour'] != 12))
			{
				if ($values['eampm']=='pm')
				{
					$values['ehour'] = $values['ehour'] + 12;
				}
			}

			if ($values['ehour'] && ($values['ehour'] == 12))
			{
				if ($values['eampm']=='am')
				{
					$values['ehour'] = 0;
				}
			}

			if ($values['smonth'] || $values['sday'] || $values['syear'])
			{
				$values['sdate'] = mktime($values['shour'],$values['smin'],0,$values['smonth'], $values['sday'], $values['syear']);
			}

            if (!$values['sdate'])
            {
                $values['sdate'] = time();
            }

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				$values['edate'] = mktime($values['ehour'],$values['emin'],0,$values['emonth'],$values['eday'],$values['eyear']);
			}

			if ($values['hours_id'] && $values['hours_id'] != 0)
			{
				$this->soprojecthours->edit_hours($values);
			}
			else
			{
				$this->soprojecthours->add_hours($values);
			}
		}

		function delete_hours($hours_id)
		{
			$this->soprojecthours->delete_hours($hours_id);
		}
	}
?>
