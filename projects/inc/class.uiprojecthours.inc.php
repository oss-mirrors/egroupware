<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000,2001,2002 Bettina Gille                        *
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

	class uiprojecthours
	{
		var $grants;
		var $start;
		var $filter;
		var $sort;
		var $order;
		var $state;

		var $public_functions = array
		(
			'list_hours'	=> True,
			'add_hours'		=> True,
			'edit_hours'	=> True,
			'delete_hours'	=> True,
			'view_hours'	=> True
		);

		function uiprojecthours()
		{
			$this->boprojecthours			= CreateObject('projects.boprojecthours',True);
			$this->boprojects				= CreateObject('projects.boprojects');
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->account					= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->t						= $GLOBALS['phpgw']->template;
			$this->grants					= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

			$this->start					= $this->boprojecthours->start;
			$this->query					= $this->boprojecthours->query;
			$this->filter					= $this->boprojecthours->filter;
			$this->order					= $this->boprojecthours->order;
			$this->sort						= $this->boprojecthours->sort;
			$this->state					= $this->boprojecthours->state;
		}

		function save_sessiondata()
		{
			$data = array
			(
				'start'		=> $this->start,
				'query'		=> $this->query,
				'filter'	=> $this->filter,
				'order'		=> $this->order,
				'sort'		=> $this->sort,
				'state'		=> $this->state
			);
			$this->boprojecthours->save_sessiondata($data);
		}

		function set_app_langs()
		{
			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->t->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$this->t->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);

			$this->t->set_var('lang_category',lang('Category'));
			$this->t->set_var('lang_select',lang('Select'));
			$this->t->set_var('lang_descr',lang('Description'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_none',lang('None'));
			$this->t->set_var('lang_start_date',lang('Start Date'));
			$this->t->set_var('lang_end_date',lang('End Date'));
			$this->t->set_var('lang_date_due',lang('Date due'));
			$this->t->set_var('lang_access',lang('Private'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('lang_jobs',lang('Jobs'));
			$this->t->set_var('lang_number',lang('Project ID'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_save',lang('Save'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_budget',lang('Budget'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_save',lang('Save'));
			$this->t->set_var('lang_view',lang('View'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('lang_activity',lang('Activity'));
			$this->t->set_var('lang_project',lang('Project'));
			$this->t->set_var('lang_descr',lang('Short description'));
			$this->t->set_var('lang_remark',lang('Remark'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_employee',lang('Employee'));
			$this->t->set_var('lang_work_date',lang('Work date'));
			$this->t->set_var('lang_start_date',lang('Start date'));
			$this->t->set_var('lang_end_date',lang('End date'));
			$this->t->set_var('lang_work_time',lang('Work time'));
			$this->t->set_var('lang_start_time',lang('Start time'));
			$this->t->set_var('lang_end_time',lang('End time'));
			$this->t->set_var('lang_select_project',lang('Select project'));
			$this->t->set_var('lang_reset',lang('Clear Form'));
			$this->t->set_var('lang_minperae',lang('Minutes per workunit'));
			$this->t->set_var('lang_billperae',lang('Bill per workunit'));
		}

		function display_app_header()
		{
			$this->t->set_file(array('header' => 'header.tpl'));
			$this->t->set_block('header','projects_header');

			$this->set_app_langs();

			if ($this->boprojects->isprojectadmin('pad'))
			{
				$this->t->set_var('admin_info',lang('Administrator'));
				$this->t->set_var('space1','&nbsp;&nbsp;&nbsp;');
				$this->t->set_var('link_activities',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));                                                                                                         
				$this->t->set_var('lang_activities',lang('Activities'));
			}

			if ($this->boprojects->isprojectadmin('pbo'))
			{
				$this->t->set_var('book_info',lang('Bookkeeper'));
				$this->t->set_var('break','&nbsp;|&nbsp;');
				$this->t->set_var('space2','&nbsp;&nbsp;&nbsp;');
				$this->t->set_var('link_billing',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains'));
				$this->t->set_var('lang_billing',lang('Billing'));
				$this->t->set_var('link_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains'));
				$this->t->set_var('lang_deliveries',lang('Deliveries'));
			}

			$this->t->set_var('link_jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$this->t->set_var('link_hours',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$this->t->set_var('link_statistics',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains'));
			$this->t->set_var('lang_statistics',lang('Statistics'));
			$this->t->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function format_htime($hdate = '')
		{
			if (!$hdate || $hdate == 0)
			{
				$htime['date'] = '&nbsp;';
				$htime['time'] = '&nbsp;';
			}
			else
			{
				$hour = date('H',$hdate);
				$min = date('i',$hdate);

				$hdate = $hdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$htime['date'] = $GLOBALS['phpgw']->common->show_date($hdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
				$htime['time'] = $GLOBALS['phpgw']->common->formattime($hour,$min);
			}
			return $htime;
		}

		function list_hours()
		{
			global $project_id, $action, $pro_parent;

			$this->display_app_header();

			$this->t->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
			$this->t->set_block('hours_list_t','hours_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id,
				'pro_parent'	=> $pro_parent,
				'action'		=> $action
			);

			$this->t->set_var('project_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));
			$this->t->set_var('state_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if ($action != 'asubs')
			{
				$this->t->set_var(lang_action,lang('Work hours list'));
				$this->t->set_var('project_list',$this->boprojects->select_project_list('all',$status,$project_id));
			}
			else
			{
				$this->t->set_var(lang_action,lang('Work hours archive'));
				$this->t->set_var('project_list',$this->boprojects->select_project_list('all','archive',$project_id));
			}

			switch($this->state)
			{
				case 'all': $state_sel[0]=' selected';break;
				case 'open': $state_sel[1]=' selected';break;
				case 'done': $state_sel[2]=' selected';break;
				case 'billed': $state_sel[3]=' selected';break;
			}

			$state_list = '<option value="all"' . $state_sel[0] . '>' . lang('Show all') . '</option>' . "\n"
						. '<option value="open"' . $state_sel[1] . '>' . lang('Open') . '</option>' . "\n"
						. '<option value="done"' . $state_sel[2] . '>' . lang('Done') . '</option>' . "\n"
						. '<option value="billed"' . $state_sel[3] . '>' . lang('Billed') . '</option>' . "\n";

			$this->t->set_var('state_list',$state_list);

			if (!$this->start)
			{
				$this->start = 0;
			}

			if (!$this->state)
			{
				$this->state = 'all';
			}

			$hours = $this->boprojecthours->list_hours($this->start, True, $this->query, $this->filter, $this->sort, $this->order, $this->state, $project_id);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojecthours->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojecthours->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojecthours->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

// ---------------- list header variable template-declarations --------------------------

			$this->t->set_var('sort_hours_descr',$this->nextmatchs->show_sort_order($this->sort,'hours_descr',$this->order,'/index.php',lang('Description')));
			$this->t->set_var('sort_status',$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status')));
			$this->t->set_var('sort_start_date',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Work date')));
			$this->t->set_var('sort_start_time',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start time')));
			$this->t->set_var('sort_end_time',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('End time')));
			$this->t->set_var('sort_hours',$this->nextmatchs->show_sort_order($this->sort,'minutes',$this->order,'/index.php',lang('Hours')));
			$this->t->set_var('sort_employee',$this->nextmatchs->show_sort_order($this->sort,'employee',$this->order,'/index.php',lang('Employee')));

// -------------- end header declaration ---------------------------------------

			for ($i=0;$i<count($hours);$i++)
			{
				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);

				$hours_descr = $GLOBALS['phpgw']->strip_html($hours[$i]['hours_descr']);
				if (! $hours_descr) $hours_descr = '&nbsp;';

				$status = $hours[$i]['status'];
				$statusout = lang($status);
				$this->t->set_var('tr_color',$tr_color);

				$sdate = $this->format_htime($hours[$i]['sdate']);
				$edate = $this->format_htime($hours[$i]['edate']);

				$minutes = floor($hours[$i]['minutes']/60) . ':'
						. sprintf ("%02d",(int)($hours[$i]['minutes']-floor($hours[$i]['minutes']/60)*60));

				$cached_data = $this->boprojects->cached_accounts($hours[$i]['employee']);
				$employeeout = $GLOBALS['phpgw']->strip_html($cached_data[$hours[$i]['employee']]['account_lid']
                                        . ' [' . $cached_data[$hours[$i]['employee']]['firstname'] . ' '
                                        . $cached_data[$hours[$i]['employee']]['lastname'] . ' ]');


// ---------------- template declaration for list records ------------------------------

				$this->t->set_var(array('employee' => $employeeout,
									'hours_descr' => $hours_descr,
										'status' => $statusout,
									'start_date' => $sdate['date'],
									'start_time' => $sdate['time'],
										'end_time' => $edate['time'],
										'minutes' => $minutes));

				$link_data['hours_id'] = $hours[$i]['hours_id'];

				$coordinator = $this->boprojects->return_value('co',$project_id);

				if ($this->state != 'billed')
				{
					if ($hours[$i]['employee'] == $this->account)
					{
						$edithour = True;
					}
					else if ($this->boprojects->check_perms($this->grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $this->account)
					{
						$edithour = True;
					}
				}

				if ($edithour)
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.edit_hours';
					$this->t->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$this->t->set_var('lang_edit',lang('Edit'));
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$link_data['menuaction'] = 'projects.uiprojecthours.view_hours';
				$this->t->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$this->t->set_var('lang_view_entry',lang('View'));

				$this->t->fp('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------

			}

			if ($action != 'asubs')
			{
				if ($this->boprojects->check_perms($this->grants[$coordinator],PHPGW_ACL_ADD) || $coordinator == $this->account)
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.add_hours';
					$this->t->set_var('action','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
																	. '"><input type="submit" value="' . lang('Add') . '"></form>');
				}
			}
			else
			{
				$this->t->set_var('action','');
			}

			$this->t->pfp('out','hours_list_t',True);
			$this->save_sessiondata();
		}

		function status_format($status = '')
		{
			switch ($status)
			{
				case 'open'	:	$stat_sel[0]=' selected'; break;
				case 'done'	:	$stat_sel[1]=' selected'; break;
				default		:	$stat_sel[1]=' selected'; break;
			}

			$status_list = '<option value="open"' . $stat_sel[0] . '>' . lang('Open') . '</option>' . "\n"
						. '<option value="done"' . $stat_sel[1] . '>' . lang('Done') . '</option>' . "\n";

			return $status_list;
		}

		function employee_format($employee = '')
		{
			if (! $employee)
			{
				$employee = $this->account;
			}

			$employees = $this->boprojects->employee_list();

			while (list($null,$account) = each($employees))
			{
				$employee_list .= '<option value="' . $account['account_id'] . '"';
				if($account['account_id'] == $employee)
				$employee_list .= ' selected';
				$employee_list .= '>' . $account['account_firstname'] . ' ' . $account['account_lastname']
										. ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
			}
			return $employee_list;
		}

		function hdate_format($hdate = '')
		{
			if (!$hdate)
			{
				$dateval['month'] = date('m',time());
				$dateval['day'] = date('d',time()); 
				$dateval['year'] = date('Y',time());
				$dateval['hour'] = date('H',time());
				$dateval['min'] = date('i',time());
			}
			else
			{
				$dateval['month'] = date('m',$hdate);
				$dateval['day'] = date('d',$hdate);
				$dateval['year'] = date('Y',$hdate);
				$dateval['hour'] = date('H',$hdate);
				$dateval['min'] = date('i',$hdate);
			}
			return $dateval;
		}

		function add_hours()
		{
			global $project_id, $pro_parent, $values, $submit;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id,
				'pro_parent'	=> $pro_parent
			);

			if ($submit)
			{
				$values['project_id'] = $project_id;
				$values['pro_parent'] = $pro_parent;
				$error = $this->boprojecthours->check_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojecthours->save_hours($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('hours_add' => 'hours_formhours.tpl'));
			$this->t->set_block('hours_add','add','addhandle');
			$this->t->set_block('hours_add','edit','edithandle');

			$this->t->set_var('doneurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$link_data['menuaction'] = 'projects.uiprojecthours.add_hours';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_action',lang('Add work hours'));

			$this->t->set_var('project_name',$this->boprojects->return_value('pro',$project_id));

			if ($pro_parent)
			{
				$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$pro_parent)));
				$this->t->set_var('lang_pro_parent',lang('Main project:'));
			}

			$this->t->set_var('activity_list',$this->boprojects->select_hours_activities($project_id, $values['activity_id']));

			$sdate = $this->hdate_format($values['sdate']);

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$sdate['year']),
																			$this->sbox->getMonthText('values[smonth]',$sdate['month']),
																			$this->sbox->getDays('values[sday]',$sdate['day'])));

			$amsel = ' checked';
			$pmsel = '';

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($sdate['hour'] >= 12)
				{
					$amsel = '';
					$pmsel = ' checked'; 
					if ($sdate['hour'] > 12)
					{
						$sdate['hour'] = $sdate['hour'] - 12;
					}
				}

				if ($sdate['hour'] == 0)
				{
					$sdate['hour'] = 12;
				}

				$sradio = '<input type="radio" name="values[sampm]" value="am"' . $amsel . '>am';
				$sradio .= '<input type="radio" name="values[sampm]" value="pm"' . $pmsel . '>pm';
				$this->t->set_var('sradio',$sradio);
			}
			else
			{
				$this->t->set_var('sradio','');
			}

			$this->t->set_var('shour',$sdate['hour']);
			$this->t->set_var('smin',$sdate['min']);

			$edate = $this->hdate_format($values['edate']);

			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$edate['year']),
																		$this->sbox->getMonthText('values[emonth]',$edate['month']),
																		$this->sbox->getDays('values[eday]',$edate['day'])));

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($edate['hour'] >= 12)
				{
					$amsel = '';
					$pmsel = ' checked';

					if ($edate['hour'] > 12)
					{
						$edate['hour'] = $edate['hour'] - 12;
					}
				}
				if ($edate['hour'] == 0)
				{
					$edate['hour'] = 12;
				}

				$eradio = '<input type="radio" name="values[eampm]" value="am"' . $amsel . '>am';
				$eradio .= '<input type="radio" name="values[eampm]" value="pm"' . $pmsel . '>pm';
				$this->t->set_var('eradio',$eradio);
			}
			else
			{
				$this->t->set_var('eradio','');
			}

			$this->t->set_var('ehour',$edate['hour']);
			$this->t->set_var('emin',$edate['min']);

			$this->t->set_var('remark',$values['remark']);
			$this->t->set_var('hours_descr',$values['hours_descr']);

			$this->t->set_var('hours',$values['hours']);
			$this->t->set_var('minutes',$values['minutes']);

			$this->t->set_var('status_list',$this->status_format($values['status']));

			$this->t->set_var('employee_list',$this->employee_format($values['employee']));

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','hours_add');
			$this->t->pfp('addhandle','add');
		}

		function edit_hours()
		{
			global $project_id, $pro_parent, $hours_id, $values, $submit, $referer;

			if (! $submit)
			{
				$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];
			}

			if (!$hours_id)
			{
				Header('Location: ' . $referer);
			}

			if ($submit)
			{
				$values['hours_id']		= $hours_id;
				$error = $this->boprojecthours->check_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojecthours->save_hours($values);
					Header('Location: ' . $referer);
				}
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.edit_hours',
				'hours_id'		=> $hours_id,
				'project_id'	=> $project_id,
				'pro_parent'	=> $pro_parent,
				'delivery_id'	=> $delivery_id,
				'invoice_id'	=> $invoice_id
			);

			$this->display_app_header();

			$this->t->set_file(array('hours_edit' => 'hours_formhours.tpl'));
			$this->t->set_block('hours_edit','add','addhandle');
			$this->t->set_block('hours_edit','edit','edithandle');

			$this->t->set_var('hidden_vars','<input type="hidden" name="referer" value="' . $referer . '">');

			$this->t->set_var('doneurl',$referer);

			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_action',lang('Edit work hours'));

			$values = $this->boprojecthours->read_single_hours($hours_id);

			$this->t->set_var('status_list',$this->status_format($values['status']));
			$this->t->set_var('employee_list',$this->employee_format($values['employee']));

			$sdate = $this->hdate_format($values['sdate']);

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$sdate['year']),
																			$this->sbox->getMonthText('values[smonth]',$sdate['month']),
																			$this->sbox->getDays('values[sday]',$sdate['day'])));

			$amsel = ' checked';
			$pmsel = '';

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($sdate['hour'] >= 12)
				{
					$amsel = '';
					$pmsel = ' checked'; 
					if ($sdate['hour'] > 12)
					{
						$sdate['hour'] = $sdate['hour'] - 12;
					}
				}

				if ($sdate['hour'] == 0)
				{
					$sdate['hour'] = 12;
				}

				$sradio = '<input type="radio" name="values[sampm]" value="am"' . $amsel . '>am';
				$sradio .= '<input type="radio" name="values[sampm]" value="pm"' . $pmsel . '>pm';
				$this->t->set_var('sradio',$sradio);
			}
			else
			{
				$this->t->set_var('sradio','');
			}

			$this->t->set_var('shour',$sdate['hour']);
			$this->t->set_var('smin',$sdate['min']);

			$edate = $this->hdate_format($values['edate']);

			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$edate['year']),
																		$this->sbox->getMonthText('values[emonth]',$edate['month']),
																		$this->sbox->getDays('values[eday]',$edate['day'])));

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($edate['hour'] >= 12)
				{
					$amsel = '';
					$pmsel = ' checked';

					if ($edate['hour'] > 12)
					{
						$edate['hour'] = $edate['hour'] - 12;
					}
				}
				if ($edate['hour'] == 0)
				{
					$edate['hour'] = 12;
				}

				$eradio = '<input type="radio" name="values[eampm]" value="am"' . $amsel . '>am';
				$eradio .= '<input type="radio" name="values[eampm]" value="pm"' . $pmsel . '>pm';
				$this->t->set_var('eradio',$eradio);
			}
			else
			{
				$this->t->set_var('eradio','');
			}

			$this->t->set_var('ehour',$edate['hour']);
			$this->t->set_var('emin',$edate['min']);


			$this->t->set_var('remark',$GLOBALS['phpgw']->strip_html($values['remark']));
			$this->t->set_var('hours_descr',$GLOBALS['phpgw']->strip_html($values['hours_descr']));

			$this->t->set_var('hours',floor($values['ae_minutes']/60));
			$this->t->set_var('minutes',($values['ae_minutes']-((floor($values['ae_minutes']/60)*60))));

			if ($values['pro_parent'] != 0)
			{
				$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['pro_parent'])));
				$this->t->set_var('lang_pro_parent',lang('Main project:'));
			}

			$this->t->set_var('project_name',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['project_id'])));

			$this->t->set_var('activity_list',$this->boprojects->select_hours_activities($values['project_id'],$values['activity_id']));

			$coordinator = $this->boprojects->return_value('co',$values['project_id']);

			if ($this->boprojects->check_perms($grants[$coordinator],PHPGW_ACL_DELETE) || $coordinator == $this->account)
			{
				$deletehour = True;
			}
			else if ($values['employee'] == $this->account)
			{
				$deletehour = True;
			}

			if ($deletehour)
			{
				$link_data['menuaction'] = 'projects.uiprojecthours.delete_hours';
				$this->t->set_var('delete','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
										. '"><input type="submit" value="' . lang('Delete') .'"></form>');
			}
			else
			{
				$this->t->set_var('delete','&nbsp;');
			}

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','hours_edit');
			$this->t->pfp('edithandle','edit');
		}

		function view_hours()
		{
			global $hours_id, $referer;

			$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];

			if (!$hours_id)
			{
				Header('Location: ' . $referer);
			}

			$this->display_app_header();

			$this->t->set_file(array('hours_view' => 'hours_view.tpl'));
			$this->t->set_var('lang_action',lang('View work hours'));
			$this->t->set_var('doneurl',$referer);

			$nopref = $this->boprojects->check_prefs();
			if ($nopref)
			{
				$this->t->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$values = $this->boprojecthours->read_single_hours($hours_id);

			$this->t->set_var('status',lang($values['status']));

			$sdate = $this->format_htime($values['sdate']);
			$edate = $this->format_htime($values['edate']);

			$this->t->set_var('sdate',$sdate['date']);
			$this->t->set_var('stime',$sdate['time']);

			$this->t->set_var('edate',$edate['date']);
			$this->t->set_var('etime',$edate['time']);

			$this->t->set_var('remark',$GLOBALS['phpgw']->strip_html($values['remark']));
			$this->t->set_var('hours_descr',$GLOBALS['phpgw']->strip_html($values['hours_descr']));

			$this->t->set_var('hours',floor($values['ae_minutes']/60));
			$this->t->set_var('minutes',($values['ae_minutes']-(floor($values['ae_minutes']/60)*60)));

			$this->t->set_var('currency',$prefs['currency']);
			$this->t->set_var('minperae',$values['minperae']);
			$this->t->set_var('billperae',$values['billperae']);

			$cached_data = $this->boprojects->cached_accounts($values['employee']);
			$employeeout = $GLOBALS['phpgw']->strip_html($cached_data[$values['employee']]['account_lid']
                                        			. ' [' . $cached_data[$values['employee']]['firstname'] . ' '
                                        			. $cached_data[$values['employee']]['lastname'] . ' ]');
			$this->t->set_var('employee',$employeeout);

			$this->t->set_var('project_name',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['project_id'])));

			if ($values['pro_parent'] != 0)
			{
				$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['pro_parent'])));
				$this->t->set_var('lang_pro_parent',lang('Main project:'));
			}

			$this->t->set_var('activity',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('act',$values['activity_id'])));

			$this->t->pfp('out','hours_view');
		}

		function delete_hours()
		{
			global $confirm, $hours_id, $project_id;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'hours_id'		=> $hours_id,
				'project_id'	=> $project_id
			);

			if ($confirm)
			{
				$this->boprojecthours->delete_hours($hours_id);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
			}

			$this->display_app_header();

			$this->t->set_file(array('hours_delete' => 'delete.tpl'));

			$this->t->set_var('lang_subs','');
			$this->t->set_var('subs', '');
			$this->t->set_var('nolink',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('deleteheader',lang('Are you sure you want to delete this entry ?'));
			$this->t->set_var('lang_no',lang('No'));
			$this->t->set_var('lang_yes',lang('Yes'));

			$link_data['menuaction'] = 'projects.uiprojecthours.delete_hours';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->pfp('out','hours_delete');
		}
	}
?>
