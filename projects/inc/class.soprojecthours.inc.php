<?php
	/*******************************************************************\
	* eGroupWare - Projects                                             *
	* http://www.egroupware.org                                         *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* Written by Lars Kneschke [lkneschke@linux-at-work.de]             *
	* -----------------------------------------------                   *
	* Copyright 2000 - 2004 Free Software Foundation, Inc               *
	* Copyright 2004 Lars Kneschke                                      *
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

	class soprojecthours
	{
		var $db;
		var $grants;

		function soprojecthours()
		{
			$this->db		= $GLOBALS['phpgw']->db;
			$this->db2		= $this->db;
			$this->account	= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->column_array = array();
		}

		function db2hours($column = False)
		{
			$i = 0;
			while ($this->db->next_record())
			{
				if($column)
				{
					$hours[$i] = array();
					for($k=0;$k<count($this->column_array);$k++)
					{
						$hours[$i][$this->column_array[$k]] = $this->db->f($this->column_array[$k]);
					}
					$i++;
				}
				else
				{
				$hours[] = array
				(
					'hours_id'	=> $this->db->f('id'),
					'project_id'	=> $this->db->f('project_id'),
					'cost_id'	=> $this->db->f('cost_id'),
					'pro_parent'	=> $this->db->f('pro_parent'),
					'pro_main'	=> $this->db->f('pro_main'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'status'	=> $this->db->f('status'),
					'minutes'	=> $this->db->f('minutes'),
					'sdate'		=> $this->db->f('start_date'),
					'edate'		=> $this->db->f('end_date'),
					'employee'	=> $this->db->f('employee'),
					'activity_id'	=> $this->db->f('activity_id'),
					'remark'	=> $this->db->f('remark'),
					'billable'	=> $this->db->f('billable'),
					'km_distance'	=> $this->db->f('km_distance'),
					't_journey'	=> $this->db->f('t_journey')
				);
				}
			}
			return $hours;
		}

		function read_hours($values)
		{
			$start			= intval($values['start']);
			$limit			= $values['limit']?$values['limit']:True;
			$filter			= $values['filter']?$values['filter']:'none';
			$sort			= $values['sort']?$values['sort']:'ASC';
			$order			= $values['order']?$values['order']:'start_date';
			$status			= $values['status']?$values['status']:'all';
			$project_id		= intval($values['project_id']);
			$query			= $this->db->db_addslashes($values['query']);
			$column			= (isset($values['column'])?$values['column']:False);
			$parent_select	= isset($values['parent_select'])?True:False;

			//_debug_array($values);

			$ordermethod = " order by $order $sort";

			$filtermethod = ($parent_select?' pro_parent=' . $project_id:' project_id=' . $project_id);

			if ($status != 'all')
			{
				$filtermethod .= " AND status='$status'";
			}

			if ($filter == 'yours')
			{
				$filtermethod .= ' AND employee=' . $this->account;
			}

			if ($query)
			{
				$querymethod = " AND (remark like '%$query%' OR minutes like '%$query%' OR hours_descr like '%$query%')";
			}

			$column_select = ((is_string($column) && $column != '')?$column:'*');
			$this->column_array = explode(',',$column);

			$sql = "SELECT $column_select FROM phpgw_p_hours WHERE $filtermethod $querymethod";

			if($limit)
			{
				$this->db2->query($sql,__LINE__,__FILE__);
				$this->total_records = $this->db2->num_rows();
				$this->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $ordermethod,__LINE__,__FILE__);
			}
			//echo $sql;
			return $this->db2hours();
		}

		function read_single_hours($hours_id)
		{
			$this->db->query('SELECT * from phpgw_p_hours WHERE id=' . intval($hours_id),__LINE__,__FILE__);
			list($hours) = $this->db2hours();

			return $hours;
		}

		function add_hours($values)
		{
			$values['hours_descr']	= $this->db->db_addslashes($values['hours_descr']);
			$values['remark']	= $this->db->db_addslashes($values['remark']);
			$values['km_distance']	= $values['km_distance'] + 0.0;
			$values['t_journey']	= $values['t_journey'] + 0.0;

			$this->db->query('INSERT into phpgw_p_hours (project_id,activity_id,cost_id,entry_date,start_date,end_date,hours_descr,remark,billable,minutes,'
							. 'status,employee,pro_parent,pro_main,km_distance,t_journey) VALUES (' . intval($values['project_id']) . ',' 
							. intval($values['activity_id']) . ',' . (int)$values['cost_id'] . ','
							. time() . ',' . intval($values['sdate']) . ',' . intval($values['edate']) . ",'" . $values['hours_descr'] . "','"
							. $values['remark'] . "','" . (isset($values['billable'])?'N':'Y') . "'," . intval($values['w_minutes']) . ",'"
							. $values['status'] . "'," . intval($values['employee']) . ',' . intval($values['pro_parent']) . ','
							. intval($values['pro_main']) . ',' . $values['km_distance'] . ',' . $values['t_journey'] . ')',__LINE__,__FILE__); 
		}

		function edit_hours($values)
		{
			$values['hours_descr']	= $this->db->db_addslashes($values['hours_descr']);
			$values['remark']	= $this->db->db_addslashes($values['remark']);
			$values['km_distance']	= $values['km_distance'] + 0.0;
			$values['t_journey']	= $values['t_journey'] + 0.0;

			$this->db->query('UPDATE phpgw_p_hours SET activity_id=' . intval($values['activity_id']) . ',cost_id=' .(int)$values['cost_id'] . ',entry_date=' . time() . ',start_date='
							. intval($values['sdate']) . ',end_date=' . intval($values['edate']) . ",hours_descr='" . $values['hours_descr'] . "',remark='"
							. $values['remark'] . "', billable='" . (isset($values['billable'])?'N':'Y') . "', minutes=" . intval($values['w_minutes'])
							. ",status='" . $values['status'] . "',employee=" . intval($values['employee']) . ', km_distance=' . $values['km_distance']
							. ', t_journey=' . $values['t_journey'] . ' where id=' . intval($values['hours_id']),__LINE__,__FILE__);
		}

		function delete_hours($values)
		{
			switch($values['action'])
			{
				case 'track':	$h_table = 'phpgw_p_ttracker'; $column = 'track_id'; break;
				default:		$h_table = 'phpgw_p_hours'; $column = 'id'; break;
			}

			$this->db->query("Delete from $h_table where $column=" . intval($values['id']),__LINE__,__FILE__);
		}

		/*function update_hours_act($activity_id, $minperae)
		{
			$this->db->query('SELECT id,minperae from phpgw_p_hours where activity_id=' . intval($activity_id),__LINE__,__FILE__); 

			while ($this->db->next_record())
			{
				if ($this->db->f('minperae') == 0)
				{
					$hours[] = $this->db->f('id');
				}
			}

			if (is_array($hours))
			{
				for ($i=0;$i<=count($hours);$i++)
				{
					$this->db->query('UPDATE phpgw_p_hours set minperae=' . intval($minperae) . ' WHERE id=' . intval($hours[$i]),__LINE__,__FILE__);
				}
			}
		}*/

		function format_wh($minutes = 0)
		{
			if($minutes)
			{
				$wh = array
				(
					'whours_formatted'	=> floor($minutes/60),
					'wmin_formatted'	=> ($minutes-(floor($minutes/60)*60)),
					'wminutes'			=> $minutes
					//'whwm'				=> floor($minutes/60) . ':' . ($minutes-(floor($minutes/60)*60))
				);
				$wh['wmin_formatted'] = $wh['wmin_formatted']<=9?'0' . $wh['wmin_formatted']:$wh['wmin_formatted'];
				$wh['whwm']	= $wh['whours_formatted'] . '.' . (($wh['wmin_formatted']==0)?'00':$wh['wmin_formatted']);
			}
			else
			{
				$wh = array
				(
					'whours_formatted'	=> 0,
					'wmin_formatted'	=> 0,
					'wminutes'			=> 0,
					'whwm'				=> 0
				);
			}
			return $wh;
		}

		function calculate_activity_budget($params = 0)
		{
			$project_id		= intval($params['project_id']);
			$project_array	= $params['project_array'];

			if(is_array($project_array))
			{
				$select = ' project_id in(' . implode(',',$project_array) . ')';
			}
			else
			{
				$select = ' project_id=' . $project_id;
			}

			$this->db->query('SELECT id,activity_id,billable from phpgw_p_projectactivities where ' . $select,__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$act[] = array
				(
					'activity_id'	=> $this->db->f('activity_id'),
					'id'			=> $this->db->f('id'),
					'billable'		=> $this->db->f('billable')
				);
			}

			if(is_array($act))
			{
				$i = 0;
				foreach($act as $a)
				{
					$this->db->query('SELECT minperae, billperae from phpgw_p_activities where id=' . $a['activity_id'],__LINE__,__FILE__);
					$this->db->next_record();
					$activity[$i] = array
					(
						'minperae'		=> $this->db->f('minperae'),
						'billperae'		=> $this->db->f('billperae'),
						'activity_id'	=> $a['activity_id'],
						'id'			=> $a['id'],
						'billable'		=> $a['billable']
					);

					$this->db->query('SELECT SUM(minutes) as utime from phpgw_p_hours where' . $select . ' AND activity_id=' . $a['id'],__LINE__,__FILE__);
					$this->db->next_record();
					$activity[$i]['utime'] = $this->db->f('utime');
					$i++;
				}

				if(is_array($activity))
				{
					$bbudget = $budget = 0;
					foreach($activity as $activ)
					{
						$factor_per_minute = $activ['billperae']/60;
						if($activ['billable'] == 'Y')
						{
							$bbudget += round($factor_per_minute*$activ['utime'],2);
						}
						$budget += round($factor_per_minute*$activ['utime'],2);
					}
					return array('bbuget' => $bbudget,'budget' => $budget);
				}
			}
		}

		function get_activity_time_used($params = 0)
		{
			$project_id		= intval($params['project_id']);
			$project_array	= $params['project_array'];
			$no_billable	= isset($params['no_billable'])?$params['no_billable']:False;
			$is_billable	= isset($params['is_billable'])?$params['is_billable']:False;

			$sql = 'SELECT SUM(minutes) as utime from phpgw_p_hours where';

			if(is_array($project_array))
			{
				$select = ' project_id in(' . implode(',',$project_array) . ')';
			}
			else
			{
				$select = ' project_id=' . $project_id;
			}

			if($no_billable || $is_billable)
			{
				$this->db->query('SELECT activity_id from phpgw_p_projectactivities where ' . $select . " AND billable='" . ($no_billable?'N':'Y') . "'",__LINE__,__FILE__);
				$i = 0;
				while($this->db->next_record())
				{
				 	$act[$i] = $this->db->f('activity_id');
					$i++;
				}

				if(is_array($act))
				{
					$select .= ' AND activity_id in(' . implode(',',$act) . ')';
				}
			}
			$this->db->query($sql . $select,__LINE__,__FILE__);

			if($this->db->next_record())
			{
				return $this->db->f('utime');
				//return $this->format_wh($hours);
			}
			return False;
		}

		function get_time_used($params = 0)
		{
			$project_id		= intval($params['project_id']);
			$project_array	= $params['project_array'];
			$hours			= isset($params['hours'])?$params['hours']:True;
			$action			= $params['action']?$params['action']:'subs';
			$no_billable	= isset($params['no_billable'])?$params['no_billable']:False;
			$is_billable	= isset($params['is_billable'])?$params['is_billable']:False;

			$sql = 'SELECT SUM(minutes) as utime from phpgw_p_hours where';

			switch($action)
			{
				case 'mains':
					$select = ' pro_main=' . $project_id;
					break;
				default:
					if(is_array($project_array))
					{
						$select = ' project_id in(' . implode(',',$project_array) . ')';
					}
					else
					{
						$select = ' project_id=' . $project_id;
					}
					break;
			}

			if($no_billable)
			{
				$select .= " AND billable='N'";
			}
			else if($is_billable)
			{
				$select .= " AND billable='Y'";
			}

			$this->db->query($sql . $select,__LINE__,__FILE__);

			if($this->db->next_record())
			{
				return $this->db->f('utime');
				//return $this->format_wh($hours);
			}
			return False;
		}

		function get_project_employees($params = 0)
		{
			$project_id		= intval($params['project_id']);
			$project_array	= $params['project_array'];
			$action			= $params['action']?$params['action']:'subs';

			switch($action)
			{
				case 'mains':
					$select = ' pro_main=' . $project_id;
					break;
				default:
			if(is_array($project_array))
			{
				$select = ' project_id in(' . implode(',',$project_array) . ')';
			}
			else
			{
				$select = ' project_id=' . $project_id;
			}
					break;
			}

			$sql = 'SELECT employee from phpgw_p_hours where ' . $select;

			$this->db->query($sql,__LINE__,__FILE__);

			$emps = array();
			$i = 0;
			while($this->db->next_record())
			{
				if(!in_array($this->db->f('employee'),$emps))
				{
					$emps[$i] = $this->db->f('employee');
					$i++;
				}
			}
			return $emps;
		}

		function get_employee_time_used($params = 0)
		{
			$project_id		= intval($params['project_id']);
			$project_array	= $params['project_array'];
			$no_billable	= isset($params['no_billable'])?$params['no_billable']:False;
			$is_billable	= isset($params['is_billable'])?$params['is_billable']:False;

			$emps = $this->get_project_employees($params);

			if(is_array($project_array))
			{
				$select = ' and project_id in(' . implode(',',$project_array) . ')';
			}
			else
			{
				$select = ' and project_id=' . $project_id;
			}

			if($no_billable)
			{
				$select .= " AND billable='N'";
			}
			else if($is_billable)
			{
				$select .= " AND billable='Y'";
			}

			for($i=0;$i<count($emps);$i++)
			{
				$sql = 'SELECT SUM(minutes) as utime from phpgw_p_hours where employee=' . $emps[$i] . $select;
				$this->db->query($sql,__LINE__,__FILE__);
				if($this->db->next_record())
				{
					//$minutes = $this->db->f('utime');
					$bemp[] = array
					(
						'employee'	=> $emps[$i],
						'utime'		=> $this->db->f('utime')
					);
				}
			}
			return $bemp;
		}

		function db2track()
		{
			while ($this->db->next_record())
			{
				$track[] = array
				(
					'track_id'	=> $this->db->f('track_id'),
					'project_id'	=> $this->db->f('project_id'),
					'cost_id'	=> $this->db->f('cost_id'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'status'	=> $this->db->f('status'),
					'minutes'	=> $this->db->f('minutes'),
					'sdate'		=> $this->db->f('start_date'),
					'edate'		=> $this->db->f('end_date'),
					'employee'	=> $this->db->f('employee'),
					'activity_id'	=> $this->db->f('activity_id'),
					'remark'	=> $this->db->f('remark'),
					'km_distance'	=> $this->db->f('km_distance'),
					't_journey'	=> $this->db->f('t_journey') 
				);
			}
			return $track;
		}


		function list_ttracker()
		{
			$ordermethod = ' order by project_id,start_date ASC';
			$sql = 'SELECT * from phpgw_p_ttracker where employee=' . $this->account;

			$this->db->query($sql . $ordermethod,__LINE__,__FILE__);
			return $this->db2track();
		}

		function read_single_track($track_id)
		{
			$this->db->query('SELECT * from phpgw_p_ttracker WHERE track_id=' . intval($track_id),__LINE__,__FILE__);
			list($hours) = $this->db2track();
			return $hours;
		}

		function format_ttime($diff)
		{
			$tdiff = array();
			$tdiff['days'] = floor($diff/60/60/24);
			$diff -= $tdiff['days']*60*60*24;
			$tdiff['hrs'] = floor($diff/60/60);
			$diff -= $tdiff['hrs']*60*60;
			$tdiff['mins'] = round($diff/60);
			//$diff -= $minsDiff*60;
			//$secsDiff = $diff;
			return $tdiff;
		}

		function get_max_track($project_id = '',$status = False)
		{
			if($status)
			{
				$status_select = " and status != 'apply'";
			}

			$this->db->query('SELECT max(track_id) as max from phpgw_p_ttracker where project_id=' . intval($project_id) . ' and employee='
							. $this->account . $status_select,__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f('max');
		}

		function check_ttracker($project_id = '',$status='active')
		{
			$track_id = $this->get_max_track($project_id);
			//echo 'MAX: ' . $track_id;

			switch($status)
			{
				case 'active':		$status_select = " and (status='start' or status='continue') and end_date = 0"; break;
				case 'inactive':	$status_select = " and status='stop'"; break;
			}
			$this->db->query('SELECT minutes from phpgw_p_ttracker where track_id=' . intval($track_id) . $status_select,__LINE__,__FILE__);
			if($this->db->next_record())
			{
				return True;
			}
			return False;
		}

		function ttracker($values)
		{
			$values['hours_descr']	= $this->db->db_addslashes($values['hours_descr']);
			$values['remark']	= $this->db->db_addslashes($values['remark']);
			$values['km_distance']	= $values['km_distance'] + 0.0;
			$values['t_journey']	= $values['t_journey'] + 0.0;
			$project_id				= intval($values['project_id']);

			#_debug_array($values);

			switch($values['action'])
			{
				case 'start':
				case 'continue':
					$this->db2->query('SELECT track_id,start_date,project_id from phpgw_p_ttracker where employee=' . $this->account . ' and project_id !=' . $project_id
									. " and (status='start' or status='continue') and minutes=0",__LINE__,__FILE__);
					while($this->db2->next_record())
					{
						$wtime = $this->format_ttime(time() - $this->db2->f('start_date'));
						$work_time = ($wtime['hrs']*60)+$wtime['mins'];
						$this->db->query('UPDATE phpgw_p_ttracker set end_date=' . time() . ',minutes=' . $work_time . ' where track_id='
										. $this->db2->f('track_id'),__LINE__,__FILE__);


						$this->db->query('INSERT into phpgw_p_ttracker (project_id,activity_id,start_date,end_date,employee,status,hours_descr,remark) '
										.' values(' . $this->db2->f('project_id') . ',0,' . time() . ',0,' . $this->account
										. ",'pause','pause','')",__LINE__,__FILE__);
					};
					break;
			}

			switch($values['action'])
			{
				case 'start':
				case 'pause':
				case 'stop':
				case 'continue':
					$max = intval($this->get_max_track($project_id));
					$this->db->query('UPDATE phpgw_p_ttracker set end_date=' . time() . ' where track_id=' . $max,__LINE__,__FILE__);

					$this->db->query('SELECT start_date,end_date from phpgw_p_ttracker where track_id=' . $max,__LINE__,__FILE__);
					$this->db->next_record();
					$sdate = $this->db->f('start_date');
					$edate = $this->db->f('end_date');
					$wtime = $this->format_ttime($edate - $sdate);
					$work_time = ($wtime['hrs']*60)+$wtime['mins'];

					$this->db->query('UPDATE phpgw_p_ttracker set minutes=' . $work_time . ' where track_id=' . $max,__LINE__,__FILE__);

					$this->db->query('INSERT into phpgw_p_ttracker (project_id,activity_id,cost_id,start_date,end_date,employee,status,hours_descr,remark) '
										.' values(' . $project_id . ',' . intval($values['activity_id']) . ',' . (int)$values['cost_id'] . ',' . time() . ',0,' . $this->account . ",'" . $values['action']
										. "','" . ($values['hours_descr']?$values['hours_descr']:$values['action']) . "','" . $values['remark'] . "')",__LINE__,__FILE__);

					if($values['action'] == 'stop')
					{
						$this->db->query("UPDATE phpgw_p_ttracker set stopped='Y' where employee=" . $this->account . ' and project_id=' . $project_id,__LINE__,__FILE__);
					}
					break;
				case 'edit':
					$this->db->query('UPDATE phpgw_p_ttracker set activity_id=' . intval($values['activity_id']) . ',start_date=' . intval($values['sdate']) . ',end_date='
									. intval($values['edate']) . ',minutes=' . intval($values['w_minutes']) . ", hours_descr='" . $values['hours_descr'] . "',remark='"
									. $values['remark'] . "' where track_id=" . intval($values['track_id']),__LINE__,__FILE__);
					break;
			}

			switch($values['action'])
			{
				//case 'start':
				case 'apply':
					$this->db->query('INSERT into phpgw_p_ttracker (project_id,activity_id,cost_id,employee,start_date,end_date,minutes,hours_descr,status,'
									. 'remark,t_journey,km_distance,stopped) values(' . $project_id . ',' . intval($values['activity_id'])
									. ',' . (int)$values['cost_id']
									. ',' . $this->account . ',' . intval($values['sdate']) . ',' . intval($values['sdate']) . ','
									. intval($values['w_minutes']) . ",'" . $values['hours_descr'] . "','" . $values['action'] . "','" . $values['remark']
									. "'," . $values['t_journey'] . ',' . $values['km_distance'] . ",'Y')",__LINE__,__FILE__);

					//return $this->db->get_last_insert_id('phpgw_p_ttracker','track_id');
					break;
			}
		}

		function save_ttracker()
		{
			$query = "SELECT * from phpgw_p_ttracker where status !='pause' and status != 'stop' and end_date > 0 and minutes > 0 and stopped='Y' and employee="
							. $this->account;
			$this->db->query($query,__LINE__,__FILE__);
			$hours = $this->db2track();

			while(is_array($hours) && list(,$hour) = each($hours))
			{
				$hour['hours_descr']	= $this->db->db_addslashes($hour['hours_descr']);
				$hour['remark']		= $this->db->db_addslashes($hour['remark']);
				$hour['pro_parent']	= $this->return_value('pro_parent',$hour['project_id']);
				$hour['pro_main']	= $this->return_value('pro_main',$hour['project_id']);
				$hour['km_distance']	= $hour['km_distance'] + 0.0;
				$hour['t_journey']	= $hour['t_journey'] + 0.0;

				$this->db->query('INSERT into phpgw_p_hours (project_id,activity_id,cost_id,entry_date,start_date,end_date,hours_descr,remark,minutes,'
							. 'status,employee,pro_parent,pro_main,billable,t_journey,km_distance) VALUES (' . intval($hour['project_id']) . ','
							. intval($hour['activity_id']) . ',' .(int)$hour['cost_id']. ','. time() . ',' . intval($hour['sdate']) . ',' . intval($hour['edate']) . ",'"
							. $hour['hours_descr'] . "','" . $hour['remark'] . "'," . intval($hour['minutes']) . ",'done'," . intval($hour['employee'])
							. ',' . intval($hour['pro_parent']) . ',' . intval($hour['pro_main']) . ",'Y'," . $hour['t_journey'] . ','
							. $hour['km_distance'] . ')',__LINE__,__FILE__);

				$this->db->query('DELETE from phpgw_p_ttracker where track_id=' . intval($hour['track_id']),__LINE__,__FILE__);
			}
			$this->db->query('DELETE from phpgw_p_ttracker where employee=' . $this->account . " and (status='pause' or status='stop') and stopped='Y'",__LINE__,__FILE__);
		}

		function return_value($action,$pro_id)
		{
			switch ($action)
			{
				case 'pro_main':	$column = 'main'; break;
				case 'pro_parent':	$column = 'parent'; break;
			}

			$this->db->query("SELECT $column from phpgw_p_projects where project_id=$pro_id",__LINE__,__FILE__);
			if ($this->db->next_record())
			{
				return $GLOBALS['phpgw']->strip_html($this->db->f($column));
			}
		}
	}
?>
