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

		function check_values($values)
		{
			if (strlen($values['descr']) >= 8000)
			{
				$error[] = lang('Description can not exceed 8000 characters in length');
			}

			if ($shour && ($shour != 0) && ($shour != 12))
			{
				if ($sampm=='pm') { $shour = $shour + 12; }
			}

			if ($shour && ($shour == 12))
			{
				if ($sampm=='am') { $shour = 0; }
			}

			if ($ehour && ($ehour != 0) && ($ehour != 12))
			{
				if ($eampm=='pm') { $ehour = $ehour + 12; }
			}

			if ($ehour && ($ehour == 12))
			{
				if ($eampm=='am') { $ehour = 0; }
			}

			if (checkdate($smonth,$sday,$syear)) { $sdate = mktime($shour,$smin,0,$smonth,$sday,$syear); } 
			else
		{
			if ($shour && $smin && $smonth && $sday && $syear)
			{
				$error[$errorcount++] = lang('You have entered an invalid start date !') . '<br>' . $shour . ':' . $smin  . ' ' . $smonth . '/' . $sday . '/' . $syear;
			}
		}

		if (checkdate($emonth,$eday,$eyear)) { $edate = mktime($ehour,$emin,0,$emonth,$eday,$eyear); } 
		else
		{
			if ($ehour && $emin && $emonth && $eday && $eyear)
			{
				$error[$errorcount++] = lang('You have entered an invalid end date !') . '<br>' . $ehour . ':' . $emin . ' ' . $emonth . '/' . $eday . '/' . $eyear;
			}
		}

		$phpgw->db->query("SELECT minperae,billperae,remarkreq FROM phpgw_p_activities WHERE id ='$activity'");
		$phpgw->db->next_record();
		if ($phpgw->db->f(0) == 0) { $error[$errorcount++] = lang('You have selected an invalid activity !'); }
		else
		{
			$billperae = $phpgw->db->f('billperae');
			$minperae = $phpgw->db->f('minperae');
			if (($phpgw->db->f('remarkreq')=='Y') and (!$remark)) { $error[$errorcount++] = lang('Please enter a remark !'); }
		}

		if (! $error)
		{
			$remark = addslashes($remark);
			$ae_minutes = $hours*60+$minutes;
//    $ae_minutes = ceil($ae_minutes / $phpgw->db->f("minperae"));

			$phpgw->db->query("INSERT into phpgw_p_hours (project_id,activity_id,entry_date,start_date,end_date,hours_descr,remark,minutes,status,minperae,"
							. "billperae,employee) VALUES ('$project_id','$activity','" . time() . "','$sdate','$edate','$hours_descr','$remark','$ae_minutes',"
							. "'$status','$minperae','$billperae','$employee')");

		}
	}
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
