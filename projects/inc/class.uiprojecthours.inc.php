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
			'edit_hours'	=> True,
			'delete_hours'	=> True,
			'view_hours'	=> True
		);

		function uiprojecthours()
		{
			$this->bo						= CreateObject('projects.boprojecthours',True);
			$this->boprojects				= CreateObject('projects.boprojects');
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->account					= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->grants					= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

			$this->start					= $this->bo->start;
			$this->query					= $this->bo->query;
			$this->filter					= $this->bo->filter;
			$this->order					= $this->bo->order;
			$this->sort						= $this->bo->sort;
			$this->state					= $this->bo->state;
			$this->project_id				= $this->bo->project_id;
		}

		function save_sessiondata()
		{
			$data = array
			(
				'start'			=> $this->start,
				'query'			=> $this->query,
				'filter'		=> $this->filter,
				'order'			=> $this->order,
				'sort'			=> $this->sort,
				'state'			=> $this->state,
				'project_id'	=> $this->project_id
			);
			$this->bo->save_sessiondata($data);
		}

		function set_app_langs()
		{
			$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$GLOBALS['phpgw']->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$GLOBALS['phpgw']->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);

			$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
			$GLOBALS['phpgw']->template->set_var('lang_select',lang('Select'));
			$GLOBALS['phpgw']->template->set_var('lang_descr',lang('Description'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_none',lang('None'));
			$GLOBALS['phpgw']->template->set_var('lang_start_date',lang('Start Date'));
			$GLOBALS['phpgw']->template->set_var('lang_end_date',lang('End Date'));
			$GLOBALS['phpgw']->template->set_var('lang_date_due',lang('Date due'));
			$GLOBALS['phpgw']->template->set_var('lang_access',lang('Private'));
			$GLOBALS['phpgw']->template->set_var('lang_projects',lang('Projects'));
			$GLOBALS['phpgw']->template->set_var('lang_jobs',lang('Jobs'));
			$GLOBALS['phpgw']->template->set_var('lang_number',lang('Project ID'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));
			$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
			$GLOBALS['phpgw']->template->set_var('lang_budget',lang('Budget'));
			$GLOBALS['phpgw']->template->set_var('lang_customer',lang('Customer'));
			$GLOBALS['phpgw']->template->set_var('lang_coordinator',lang('Coordinator'));
			$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
			$GLOBALS['phpgw']->template->set_var('lang_done',lang('done'));
			$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['phpgw']->template->set_var('lang_view',lang('View'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Work hours'));
			$GLOBALS['phpgw']->template->set_var('lang_activity',lang('Activity'));
			$GLOBALS['phpgw']->template->set_var('lang_project',lang('Project'));
			$GLOBALS['phpgw']->template->set_var('lang_descr',lang('Short description'));
			$GLOBALS['phpgw']->template->set_var('lang_remark',lang('Remark'));
			$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));
			$GLOBALS['phpgw']->template->set_var('lang_employee',lang('Employee'));
			$GLOBALS['phpgw']->template->set_var('lang_work_date',lang('Work date'));
			$GLOBALS['phpgw']->template->set_var('lang_start_date',lang('Start date'));
			$GLOBALS['phpgw']->template->set_var('lang_end_date',lang('End date'));
			$GLOBALS['phpgw']->template->set_var('lang_work_time',lang('Work time'));
			$GLOBALS['phpgw']->template->set_var('lang_start_time',lang('Start time'));
			$GLOBALS['phpgw']->template->set_var('lang_end_time',lang('End time'));
			$GLOBALS['phpgw']->template->set_var('lang_select_project',lang('Select project'));
			$GLOBALS['phpgw']->template->set_var('lang_minperae',lang('Minutes per workunit'));
			$GLOBALS['phpgw']->template->set_var('lang_billperae',lang('Bill per hour/workunit'));
			$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
		}

		function display_app_header()
		{
			$GLOBALS['phpgw']->template->set_file(array('header' => 'header.tpl'));
			$GLOBALS['phpgw']->template->set_block('header','projects_header');

			$this->set_app_langs();

			if ($this->boprojects->isprojectadmin('pad'))
			{
				$GLOBALS['phpgw']->template->set_var('admin_info',lang('Administrator'));
				$GLOBALS['phpgw']->template->set_var('break1','&nbsp;|&nbsp;');
				$GLOBALS['phpgw']->template->set_var('space1','&nbsp;&nbsp;&nbsp;');
				$GLOBALS['phpgw']->template->set_var('link_activities',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));                                                                                                         
				$GLOBALS['phpgw']->template->set_var('lang_activities',lang('Activities'));
				$GLOBALS['phpgw']->template->set_var('link_budget',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_budget&action=mains'));
				$GLOBALS['phpgw']->template->set_var('lang_budget',lang('budget'));
			}

			if ($this->boprojects->isprojectadmin('pbo'))
			{
				$GLOBALS['phpgw']->template->set_var('book_info',lang('Bookkeeper'));
				$GLOBALS['phpgw']->template->set_var('break2','&nbsp;|&nbsp;');
				$GLOBALS['phpgw']->template->set_var('space2','&nbsp;&nbsp;&nbsp;');
				$GLOBALS['phpgw']->template->set_var('link_billing',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains'));
				$GLOBALS['phpgw']->template->set_var('lang_billing',lang('Billing'));
				$GLOBALS['phpgw']->template->set_var('link_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains'));
				$GLOBALS['phpgw']->template->set_var('lang_delivery',lang('Deliveries'));
			}

			$GLOBALS['phpgw']->template->set_var('link_jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$GLOBALS['phpgw']->template->set_var('link_hours',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$GLOBALS['phpgw']->template->set_var('link_statistics',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains'));
			$GLOBALS['phpgw']->template->set_var('lang_statistics',lang('Statistics'));
			$GLOBALS['phpgw']->template->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$GLOBALS['phpgw']->template->set_var('lang_projects',lang('Projects'));
			$GLOBALS['phpgw']->template->set_var('link_archiv',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains'));
			$GLOBALS['phpgw']->template->set_var('lang_archiv',lang('archive'));

			$GLOBALS['phpgw']->template->fp('app_header','projects_header');

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
			$action		= get_var('action',array('POST','GET'));
			$project_id	= get_var('project_id',array('POST','GET'));
			$pro_parent	= get_var('pro_parent',array('POST','GET'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'asubs')?lang('work hours archive'):lang('list work hours'));
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
			$GLOBALS['phpgw']->template->set_block('hours_list_t','hours_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id,
				'pro_parent'	=> $pro_parent,
				'action'		=> $action
			);

			$GLOBALS['phpgw']->template->set_var('project_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('filter_list',$this->nextmatchs->new_filter(array('format' => 'yours','filter' => $this->filter)));
			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$GLOBALS['phpgw']->template->set_var('state_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('project_list',$this->boprojects->select_project_list(array('type' => 'all','status' => (($action != 'asubs')?$status:'archive'),'selected' => $this->project_id)));

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

			$GLOBALS['phpgw']->template->set_var('state_list',$state_list);

			$coordinator = $this->boprojects->return_value('co',$project_id);

			if (!$this->start)
			{
				$this->start = 0;
			}

			if (!$this->state)
			{
				$this->state = 'all';
			}

			$hours = $this->bo->list_hours($this->start, True, $this->query, $this->filter, $this->sort, $this->order, $this->state, $project_id);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

// ---------------- list header variable template-declarations --------------------------

			$GLOBALS['phpgw']->template->set_var('sort_hours_descr',$this->nextmatchs->show_sort_order($this->sort,'hours_descr',$this->order,'/index.php',lang('Description')));
			$GLOBALS['phpgw']->template->set_var('sort_status',$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status')));
			$GLOBALS['phpgw']->template->set_var('sort_start_date',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Work date')));
			$GLOBALS['phpgw']->template->set_var('sort_start_time',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start time')));
			$GLOBALS['phpgw']->template->set_var('sort_end_time',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('End time')));
			$GLOBALS['phpgw']->template->set_var('sort_hours',$this->nextmatchs->show_sort_order($this->sort,'minutes',$this->order,'/index.php',lang('Hours')));
			$GLOBALS['phpgw']->template->set_var('sort_employee',$this->nextmatchs->show_sort_order($this->sort,'employee',$this->order,'/index.php',lang('Employee')));

// -------------- end header declaration ---------------------------------------

			for ($i=0;$i<count($hours);$i++)
			{
				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);

				$hours_descr = $GLOBALS['phpgw']->strip_html($hours[$i]['hours_descr']);
				if (! $hours_descr) $hours_descr = '&nbsp;';

				$status = $hours[$i]['status'];
				$statusout = lang($status);
				$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);

				$sdate = $this->format_htime($hours[$i]['sdate']);
				$edate = $this->format_htime($hours[$i]['edate']);

				$minutes = floor($hours[$i]['minutes']/60) . ':'
						. sprintf ("%02d",(int)($hours[$i]['minutes']-floor($hours[$i]['minutes']/60)*60));

				$cached_data = $this->boprojects->cached_accounts($hours[$i]['employee']);
				$employeeout = $GLOBALS['phpgw']->strip_html($cached_data[$hours[$i]['employee']]['account_lid']
                                        . ' [' . $cached_data[$hours[$i]['employee']]['firstname'] . ' '
                                        . $cached_data[$hours[$i]['employee']]['lastname'] . ' ]');


// ---------------- template declaration for list records ------------------------------

				$GLOBALS['phpgw']->template->set_var(array('employee' => $employeeout,
									'hours_descr' => $hours_descr,
										'status' => $statusout,
									'start_date' => $sdate['date'],
									'start_time' => $sdate['time'],
										'end_time' => $edate['time'],
										'minutes' => $minutes));

				$link_data['hours_id'] = $hours[$i]['hours_id'];

				if (($hours[$i]['status'] != 'billed') && ($hours[$i]['status'] != 'closed'))
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
				else
				{
					$edithour = False;
				}

				if ($edithour)
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.edit_hours';
					$GLOBALS['phpgw']->template->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('edit','');
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry','&nbsp;');
				}

				$link_data['menuaction'] = 'projects.uiprojecthours.view_hours';
				$GLOBALS['phpgw']->template->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->template->set_var('lang_view_entry',lang('View'));

				$GLOBALS['phpgw']->template->fp('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------

			}

			if ($action != 'asubs' && ($this->boprojects->check_perms($this->grants[$coordinator],PHPGW_ACL_ADD) || $coordinator == $this->account
										|| $this->bo->member()))
			{
				$link_data['menuaction'] = 'projects.uiprojecthours.edit_hours';
				unset($link_data['hours_id']);
				$GLOBALS['phpgw']->template->set_var('action','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
																. '"><input type="submit" value="' . lang('Add') . '"></form>');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('action','');
			}
			$this->save_sessiondata();
			$GLOBALS['phpgw']->template->pfp('out','hours_list_t',True);
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

		function edit_hours()
		{
			$project_id		= get_var('project_id',array('POST','GET'));
			$pro_parent		= get_var('pro_parent',array('POST','GET'));
			$hours_id		= get_var('hours_id',array('POST','GET'));

			$values			= get_var('values',array('POST'));
			$referer		= get_var('referer',array('POST'));

			$delivery_id	= get_var('delivery_id',array('POST','GET'));
			$invoice_id		= get_var('invoice_id',array('POST','GET'));

			if (! $values['submit'])
			{
				$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];
			}

			if ($values['submit'])
			{
				$values['project_id']	= $project_id;
				$values['pro_parent']	= $pro_parent;
				$values['hours_id']		= $hours_id;
				$error = $this->bo->check_values($values);
				if (is_array($error))
				{
					$GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->bo->save_hours($values);
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

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($hours_id?lang('edit work hours'):lang('add work hours'));
			$this->display_app_header();

			$form = ($hours_id?'edit':'add');

			$GLOBALS['phpgw']->template->set_file(array('hours_' . $form => 'hours_formhours.tpl'));
			$GLOBALS['phpgw']->template->set_block('hours_' . $form,'add','addhandle');
			$GLOBALS['phpgw']->template->set_block('hours_' . $form,'edit','edithandle');
			$GLOBALS['phpgw']->template->set_block('hours_' . $form,'emp','emphandle');

			$GLOBALS['phpgw']->template->set_var('hidden_vars','<input type="hidden" name="referer" value="' . $referer . '">');

			$GLOBALS['phpgw']->template->set_var('cancel_url',$referer . '&project_id=' . $project_id);

			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if ($hours_id)
			{
				$values = $this->bo->read_single_hours($hours_id);

				$activity_id	= $values['activity_id'];
				$project_id		= $values['project_id'];
				$pro_parent		= $values['pro_parent'];
			}

			$GLOBALS['phpgw']->template->set_var('status_list',$this->status_format($values['status']));

			$sdate = $this->hdate_format($values['sdate']);

			$GLOBALS['phpgw']->template->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$sdate['year']),
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
				$GLOBALS['phpgw']->template->set_var('sradio',$sradio);
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('sradio','');
			}

			$GLOBALS['phpgw']->template->set_var('shour',$sdate['hour']);
			$GLOBALS['phpgw']->template->set_var('smin',$sdate['min']);

			$edate = $this->hdate_format($values['edate']);

			$GLOBALS['phpgw']->template->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$edate['year']),
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
				$GLOBALS['phpgw']->template->set_var('eradio',$eradio);
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('eradio','');
			}

			$GLOBALS['phpgw']->template->set_var('ehour',$edate['hour']);
			$GLOBALS['phpgw']->template->set_var('emin',$edate['min']);


			$GLOBALS['phpgw']->template->set_var('remark',nl2br($GLOBALS['phpgw']->strip_html($values['remark'])));
			$GLOBALS['phpgw']->template->set_var('hours_descr',$GLOBALS['phpgw']->strip_html($values['hours_descr']));

			$GLOBALS['phpgw']->template->set_var('hours',floor($values['ae_minutes']/60));
			$GLOBALS['phpgw']->template->set_var('minutes',($values['ae_minutes']-((floor($values['ae_minutes']/60)*60))));

			if ($values['pro_parent'] != 0)
			{
				$GLOBALS['phpgw']->template->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$pro_parent)));
				$GLOBALS['phpgw']->template->set_var('lang_pro_parent',lang('Main project:'));
			}

			$GLOBALS['phpgw']->template->set_var('project_name',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$project_id)));

			$GLOBALS['phpgw']->template->set_var('activity_list',$this->boprojects->select_hours_activities($project_id,$activity_id));

			$coordinator = $this->boprojects->return_value('co',$project_id);

			if ($this->boprojects->check_perms($grants[$coordinator],PHPGW_ACL_DELETE) || $coordinator == $this->account)
			{
				$deletehour = True;
			}
			else if ($values['employee'] == $this->account)
			{
				$deletehour = True;
			}

			if ($this->boprojects->check_perms($grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $this->account)
			{
				$GLOBALS['phpgw']->template->set_var('employee_list',$this->employee_format($values['employee']));
				$GLOBALS['phpgw']->template->fp('emphandle','emp',True);
			}

			if ($deletehour)
			{
				$link_data['menuaction'] = 'projects.uiprojecthours.delete_hours';
				$GLOBALS['phpgw']->template->set_var('delete','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
										. '"><input type="submit" value="' . lang('Delete') .'"></form>');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('delete','&nbsp;');
			}

			$this->save_sessiondata();
			$GLOBALS['phpgw']->template->set_var('edithandle','');
			$GLOBALS['phpgw']->template->set_var('addhandle','');

			$GLOBALS['phpgw']->template->pfp('out','hours_' . $form);
			$GLOBALS['phpgw']->template->pfp($form . 'handle',$form);
		}

		function view_hours()
		{
			$hours_id	= get_var('hours_id',array('GET'));
			//$referer	= get_var('referer',array('POST'));
			$project_id	= get_var('project_id',array('GET'));

			$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];

			if (!$hours_id)
			{
				$GLOBALS['phpgw']->redirect_link($referer);
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('view work hours');
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('hours_view' => 'hours_view.tpl'));
			$GLOBALS['phpgw']->template->set_var('doneurl',$referer . '&project_id=' . $project_id);

			$nopref = $this->boprojects->check_prefs();
			if ($nopref)
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$values = $this->bo->read_single_hours($hours_id);

			$GLOBALS['phpgw']->template->set_var('status',lang($values['status']));

			$sdate = $this->format_htime($values['sdate']);
			$edate = $this->format_htime($values['edate']);

			$GLOBALS['phpgw']->template->set_var('sdate',$sdate['date']);
			$GLOBALS['phpgw']->template->set_var('stime',$sdate['time']);

			$GLOBALS['phpgw']->template->set_var('edate',$edate['date']);
			$GLOBALS['phpgw']->template->set_var('etime',$edate['time']);

			$GLOBALS['phpgw']->template->set_var('remark',nl2br($GLOBALS['phpgw']->strip_html($values['remark'])));
			$GLOBALS['phpgw']->template->set_var('hours_descr',$GLOBALS['phpgw']->strip_html($values['hours_descr']));

			$GLOBALS['phpgw']->template->set_var('hours',floor($values['ae_minutes']/60));
			$GLOBALS['phpgw']->template->set_var('minutes',($values['ae_minutes']-(floor($values['ae_minutes']/60)*60)));

			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);
			$GLOBALS['phpgw']->template->set_var('minperae',$values['minperae']);
			$GLOBALS['phpgw']->template->set_var('billperae',$values['billperae']);

			$cached_data = $this->boprojects->cached_accounts($values['employee']);
			$employeeout = $GLOBALS['phpgw']->strip_html($cached_data[$values['employee']]['account_lid']
                                        			. ' [' . $cached_data[$values['employee']]['firstname'] . ' '
                                        			. $cached_data[$values['employee']]['lastname'] . ' ]');
			$GLOBALS['phpgw']->template->set_var('employee',$employeeout);

			$GLOBALS['phpgw']->template->set_var('project_name',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['project_id'])));

			if ($values['pro_parent'] != 0)
			{
				$GLOBALS['phpgw']->template->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('pro',$values['pro_parent'])));
				$GLOBALS['phpgw']->template->set_var('lang_pro_parent',lang('Main project:'));
			}

			$GLOBALS['phpgw']->template->set_var('activity',$GLOBALS['phpgw']->strip_html($this->boprojects->return_value('act',$values['activity_id'])));

			$GLOBALS['phpgw']->template->pfp('out','hours_view');
		}

		function delete_hours()
		{
			$hours_id	= get_var('hours_id',array('POST','GET'));
			$project_id	= get_var('project_id',array('POST','GET'));
			$confirm	= get_var('confirm',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'hours_id'		=> $hours_id,
				'project_id'	=> $project_id
			);

			if ($confirm)
			{
				$this->bo->delete_hours($hours_id);
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('hours_delete' => 'delete.tpl'));

			$GLOBALS['phpgw']->template->set_var('lang_subs','');
			$GLOBALS['phpgw']->template->set_var('subs', '');
			$GLOBALS['phpgw']->template->set_var('nolink',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('deleteheader',lang('Are you sure you want to delete this entry ?'));
			$GLOBALS['phpgw']->template->set_var('lang_no',lang('No'));
			$GLOBALS['phpgw']->template->set_var('lang_yes',lang('Yes'));

			$link_data['menuaction'] = 'projects.uiprojecthours.delete_hours';
			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->pfp('out','hours_delete');
		}
	}
?>
