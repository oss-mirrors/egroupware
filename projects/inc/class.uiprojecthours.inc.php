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
			'add_hours'		=> True
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
			$this->t->set_var('lang_hours',lang('Hours'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_employee',lang('Employee'));
			$this->t->set_var('lang_work_date',lang('Work date'));
			$this->t->set_var('lang_start_date',lang('Start date'));
			$this->t->set_var('lang_end_date',lang('End date'));
			$this->t->set_var('lang_work_time',lang('Work time'));
			$this->t->set_var('lang_start_time',lang('Start time'));
			$this->t->set_var('lang_end_time',lang('End time'));
			$this->t->set_var('lang_select_project',lang('Select project'));
			$t->set_var('lang_minperae',lang('Minutes per workunit'));
			$t->set_var('lang_billperae',lang('Bill per workunit'));
			$t->set_var('lang_reset',lang('Clear Form'));
		}

		function display_app_header()
		{
			$this->t->set_file(array('header' => 'header.tpl'));
			$this->t->set_block('header','projects_header');

			$this->set_app_langs();

			$isadmin = $this->boprojects->isprojectadmin();

			if ($isadmin)
			{
				$this->t->set_var('admin_info',lang('Administrator'));
				$this->t->set_var('link_activities',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));                                                                                                         
				$this->t->set_var('lang_activities',lang('Activities'));
			}
			else
			{
				$this->t->set_var('admin_info','');
				$this->t->set_var('link_activities','');
				$this->t->set_var('lang_activities','');
			}

			$this->t->set_var('link_billing',$GLOBALS['phpgw']->link('/projects/bill_index.php'));
			$this->t->set_var('lang_billing',lang('Billing'));
			$this->t->set_var('link_jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$this->t->set_var('link_hours',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$this->t->set_var('link_statistics',$GLOBALS['phpgw']->link('/projects/stats_projectlist.php'));
			$this->t->set_var('lang_statistics',lang('Statistics'));
			$this->t->set_var('link_delivery',$GLOBALS['phpgw']->link('/projects/del_index.php'));
			$this->t->set_var('lang_delivery',lang('Delivery'));
			$this->t->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$GLOBALS['phpgw']->link('/projects/archive.php'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function list_hours()
		{
			global $project_id;

			$this->display_app_header();

			$this->t->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
			$this->t->set_block('hours_list_t','hours_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id
			);

			$this->t->set_var('project_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('project_list',$this->boprojects->select_project_list('all',$project_id));
			$this->t->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));
			$this->t->set_var('state_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->set_var(lang_action,lang('Work hours list'));
    		$this->t->set_var('lang_select_project',lang('Select project'));

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

				$ampm = 'am';

				$start_date = $hours[$i]['start_date'];
				if ($start_date == 0)
				{
					$start_dateout = '&nbsp;';
					$start_time = '&nbsp;';
				}
				else
				{
					$smonth = $GLOBALS['phpgw']->common->show_date(time(),'n');
					$sday = $GLOBALS['phpgw']->common->show_date(time(),'d');
					$syear = $GLOBALS['phpgw']->common->show_date(time(),'Y');
					$shour = date('H',$start_date);
					$smin = date('i',$start_date);

					$start_date = $start_date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
					$start_dateout = $GLOBALS['phpgw']->common->show_date($start_date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					$start_timeout = $GLOBALS['phpgw']->common->formattime($shour,$smin);
				}

				$end_date = $hours[$i]['end_date'];
				if ($end_date == 0) { $end_timeout = '&nbsp;'; }
				else
				{
					$emonth = $GLOBALS['phpgw']->common->show_date(time(),'n');
					$eday = $GLOBALS['phpgw']->common->show_date(time(),'d');
					$eyear = $GLOBALS['phpgw']->common->show_date(time(),'Y');
					$ehour = date('H',$end_date);
					$emin = date('i',$end_date);

					$end_timeout = $GLOBALS['phpgw']->common->formattime($ehour,$emin);
				}

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
								'start_date' => $start_dateout,
								'start_time' => $start_timeout,
									'end_time' => $end_timeout,
									'minutes' => $minutes));

				if ($this->state != 'billed')
				{
					if ($this->boprojects->check_perms($this->grants[$hours[$i]['employee']],PHPGW_ACL_EDIT) || $hours[$i]['employee'] == $this->account)
					{
						$this->t->set_var('edit',$GLOBALS['phpgw']->link('/projects/hours_edithour.php','id=' . $hours[$i]['id'] . '&pro_parent=' . $pro[0]['parent']
													. '&filter=' . $filter . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&sort=' . $sort));
						$this->t->set_var('lang_edit',lang('Edit'));
					}
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$this->t->set_var('view',$GLOBALS['phpgw']->link('/projects/viewhours.php','id=' . $hours[$i]['id'] . '&pro_parent=' . $pro[0]['parent']
										. '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter));
				$this->t->set_var('lang_view_entry',lang('View'));

				$this->t->fp('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------

			}

			$pro = $this->boprojects->read_single_project($project_id);

			if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
			{
				$this->t->set_var('action','<form method="POST" action="' . $GLOBALS['phpgw']->link('/projects/hours_addhour.php','project_id=' . $pro['project_id']) . '&pro_parent=' . $pro[0]['parent']
															. '"><input type="submit" value="' . lang('Add') .'"></form>');
			}
			else { $this->t->set_var('action',''); }

			$this->t->pfp('out','hours_list_t',True);
			$this->save_sessiondata();
		}

		function status_format($status = '')
		{
			switch ($status)
			{
				case 'open':	$stat_sel[0]=' selected'; break;
				case 'done':	$stat_sel[1]=' selected'; break;
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

		function add_hours()
		{
			global $project_id, $values, $submit;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id
			);

			if ($submit)
			{
				$error = $this->boprojecthours->check_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojecthours->save_project($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('hours_add' => 'hours_formhours.tpl'));
			$this->t->set_block('hours_add','add','addhandle');
			$this->t->set_block('hours_add','edit','edithandle');

			$this->t->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$nopref = $this->boprojects->check_prefs();
			if ($nopref)
			{
				$this->t->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$currency = $this->boprojects->get_prefs();
			}

			$link_data['menuaction'] = 'projects.uiprojects.add_hours';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_action',lang('Add project hours'));

			$this->t->set_var('project_name',$this->boprojects->return_value($project_id));

			$this->t->set_var('activity_list',$this->boprojects->select_hours_activities($project_id));

			$values['amsel'] = ' checked'; $values['pmsel'] = '';

			if (!$values['sdate'])
			{
				$values['smonth'] = date('m',time());
				$values['sday'] = date('d',time()); 
				$values['syear'] = date('Y',time());
				$values['shour'] = date('H',time());
				$values['smin'] = date('i',time());
			}
			else
			{
				$values['smonth'] = date('m',$values['sdate']);
				$values['sday'] = date('d',$values['sdate']);
				$values['syear'] = date('Y',$values['sdate']);
				$values['shour'] = date('H',$values['sdate']);
				$values['smin'] = date('i',$values['sdate']);
			}

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																							$this->sbox->getMonthText('values[smonth]',$valuessmonth),
																							$this->sbox->getDays('values[sday]',$values['sday'])));

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($values['shour'] >= 12)
				{
					$values['amsel'] = ''; $values['pmsel'] = ' checked'; 
					if ($values['shour'] > 12)
					{
						$values['shour'] = $values['shour'] - 12;
					}
				}

				if ($values['shour'] == 0)
				{
					$values['shour'] = 12;
				}

				$sradio = '<input type="radio" name="sampm" value="am"' . $values['amsel'] . '>am';
				$sradio .= '<input type="radio" name="sampm" value="pm"' . $values['pmsel'] . '>pm';
				$this->t->set_var('sradio',$sradio);
			}
			else
			{
				$this->t->set_var('sradio','');
			}

			$this->t->set_var('shour',$values['shour']);
			$this->t->set_var('smin',$values['smin']);

			if (!$values['edate'])
			{
				$values['emonth'] = date('m',time());
				$values['eday'] = date('d',time());
				$values['eyear'] = date('Y',time());
				$values['ehour'] = date('H',time());
				$values['emin'] = date('i',time());
			}
			else
			{
				$values['emonth'] = date('m',$values['edate']);
				$values['eday'] = date('m',$values['edate']);
				$values['eyear'] = date('Y',$values['edate']);
				$values['ehour'] = date('H',$values['edate']);
				$values['emin'] = date('i',$values['edate']);
			}

			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																				$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																				$this->sbox->getDays('values[eday]',$values['eday'])));

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['timeformat'] == '12')
			{
				if ($values['ehour'] >= 12)
				{
					$values['amsel'] = '';
					$values['pmsel'] = ' checked';

					if ($values['ehour'] > 12)
					{
						$values['ehour'] = $values['ehour'] - 12;
					}
				}
				if ($values['ehour'] == 0)
				{
					$values['ehour'] = 12;
				}

				$eradio = '<input type="radio" name="eampm" value="am"' . $values['amsel'] . '>am';
				$eradio .= '<input type="radio" name="eampm" value="pm"' . $values['pmsel'] . '>pm';
				$this->t->set_var('eradio',$eradio);
			}
			else
			{
				$this->t->set_var('eradio','');
			}

			$this->t->set_var('ehour',$values['ehour']);
			$this->t->set_var('emin',$values['emin']);

			$this->t->set_var('remark',$values['remark']);
			$this->t->set_var('hours_descr',$values['hours_descr']);

			$this->t->set_var('hours',$values['hours']);
			$this->t->set_var('minutes',$values['minutes']);

			$this->t->set_var('status_list',$this->status_format($values['status']);

			$this->t->set_var('employee_list',$this->employee_format($values['employee']));

			$this->t->set_var('minperae',$minperae);
			$this->t->set_var('billperae',$billperae);

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','hours_add');
			$this->t->pfp('addhandle','add');
		}
	}
?>
