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
			'list_hours'		=> True
		);

		function uiprojecthours()
		{
			global $phpgw, $phpgw_info;

			$this->boprojecthours			= CreateObject('projects.boprojecthours',True);
			$this->boprojects				= CreateObject('projects.boprojects');
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->account					= $phpgw_info['user']['account_id'];
			$this->t						= $phpgw->template;
			$this->grants					= $phpgw->acl->get_grants('projects');
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
			global $phpgw, $phpgw_info;

			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
			$this->t->set_var('row_on',$phpgw_info['theme']['row_on']);
			$this->t->set_var('row_off',$phpgw_info['theme']['row_off']);

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
			$this->t->set_var('lang_select',lang('Select per button !'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_bookable_activities',lang('Bookable activities'));
			$this->t->set_var('lang_billable_activities',lang('Billable activities'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_view',lang('View'));
			$this->t->set_var('lang_hours',lang('Work hours'));
		}

		function display_app_header()
		{
			global $phpgw, $phpgw_info;

			$this->t->set_file(array('header' => 'header.tpl'));
			$this->t->set_block('header','projects_header');

			$this->set_app_langs();

			$isadmin = $this->boprojects->isprojectadmin();

			if ($isadmin)
			{
				$this->t->set_var('admin_info',lang('Administrator'));
				$this->t->set_var('link_activities',$phpgw->link('/projects/activities.php'));                                                                                                         
				$this->t->set_var('lang_activities',lang('Activities'));                                                                                                                               
			}
			else
			{
				$this->t->set_var('admin_info','');
				$this->t->set_var('link_activities','');
				$this->t->set_var('lang_activities','');
			}

			$this->t->set_var('link_billing',$phpgw->link('/projects/bill_index.php'));
			$this->t->set_var('lang_billing',lang('Billing'));
			$this->t->set_var('link_jobs',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_sub_projects'));
			$this->t->set_var('link_hours',$phpgw->link('/index.php','menuaction=projects.uihours.list_hours'));
			$this->t->set_var('link_statistics',$phpgw->link('/projects/stats_projectlist.php'));
			$this->t->set_var('lang_statistics',lang("Statistics"));
			$this->t->set_var('link_delivery',$phpgw->link('/projects/del_index.php'));
			$this->t->set_var('lang_delivery',lang('Delivery'));
			$this->t->set_var('link_projects',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$phpgw->link('/projects/archive.php'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$phpgw->common->phpgw_header();
			echo parse_navbar();
		}

		function list_hours()
		{
			global $phpgw, $phpgw_info, $project_id;

			$this->display_app_header();

			$this->t->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
			$this->t->set_block('hours_list_t','hours_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojecthours.list_hours',
				'project_id'	=> $project_id
			);

			$this->t->set_var('project_action',$phpgw->link('/index.php',$link_data));
			$this->t->set_var('project_list',$this->boprojects->select_project_list('all',$project_id));
			$this->t->set_var('filter_action',$phpgw->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1));
			$this->t->set_var('search_action',$phpgw->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));
			$this->t->set_var('state_action',$phpgw->link('/index.php',$link_data));

			$this->t->set_var(lang_action,lang('Work hours list'));
    		$this->t->set_var('lang_select_project',lang('Select project'));

			switch($this->state)
			{
				case 'all': $state_sel[0]=' selected';break;
				case 'open': $state_sel[1]=' selected';break;
				case 'closed': $state_sel[2]=' selected';break;
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

				$hours_descr = $phpgw->strip_html($hours[$i]['hours_descr']);
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
					$smonth = $phpgw->common->show_date(time(),'n');
					$sday = $phpgw->common->show_date(time(),'d');
					$syear = $phpgw->common->show_date(time(),'Y');
					$shour = date('H',$start_date);
					$smin = date('i',$start_date);

					$start_date = $start_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
					$start_dateout = $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
					$start_timeout = $phpgw->common->formattime($shour,$smin);
				}

				$end_date = $hours[$i]['end_date'];
				if ($end_date == 0) { $end_timeout = '&nbsp;'; }
				else
				{
					$emonth = $phpgw->common->show_date(time(),'n');
					$eday = $phpgw->common->show_date(time(),'d');
					$eyear = $phpgw->common->show_date(time(),'Y');
					$ehour = date('H',$end_date);
					$emin = date('i',$end_date);

					$end_timeout = $phpgw->common->formattime($ehour,$emin);
				}

				$minutes = floor($hours[$i]['minutes']/60) . ':'
						. sprintf ("%02d",(int)($hours[$i]['minutes']-floor($hours[$i]['minutes']/60)*60));

				$cached_data = $this->boprojects->cached_accounts($hours[$i]['employee']);
				$employeeout = $phpgw->strip_html($cached_data[$hours[$i]['employee']]['account_lid']
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
					if ($this->boprojects->check_perms($grants[$hours[$i]['employee']],PHPGW_ACL_EDIT) || $hours[$i]['employee'] == $this->account)
					{
						$this->t->set_var('edit',$phpgw->link('/projects/hours_edithour.php','id=' . $hours[$i]['id'] . '&pro_parent=' . $pro[0]['parent']
													. '&filter=' . $filter . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&sort=' . $sort));
						$this->t->set_var('lang_edit',lang('Edit'));
					}
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$this->t->set_var('view',$phpgw->link('/projects/viewhours.php','id=' . $hours[$i]['id'] . '&pro_parent=' . $pro[0]['parent']
										. '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter));
				$this->t->set_var('lang_view_entry',lang('View'));

				$this->t->fp('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------

			}

			$pro = $this->boprojects->read_single_project($project_id);

			if ($this->boprojects->check_perms($grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
			{
				$this->t->set_var('action','<form method="POST" action="' . $phpgw->link('/projects/hours_addhour.php','project_id=' . $pro['project_id']) . '&pro_parent=' . $pro[0]['parent']
															. '"><input type="submit" value="' . lang('Add') .'"></form>');
			}
			else { $this->t->set_var('action',''); }

			$this->t->pfp('out','hours_list_t',True);
			$this->save_sessiondata();
		}

		function add_project()
		{
			global $phpgw, $phpgw_info, $submit, $cat_id, $new_cat, $abid, $name, $values, $book_activities, $bill_activities;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			if ($submit)
			{
				$values['cat'] = $cat_id;
				$values['customer'] = $abid;

				$error = $this->boprojects->check_values($values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$this->t->set_var('message',$phpgw->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_project($values, $book_activities, $bill_activities);
					Header('Location: ' . $phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&cat_id=' . $cat_id));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('projects_add' => 'form.tpl'));
			$this->t->set_block('projects_add','add','addhandle');
			$this->t->set_block('projects_add','edit','edithandle');

			$this->t->set_var('actionurl',$phpgw->link('/index.php','menuaction=projects.uiprojects.add_project'));
			$this->t->set_var('addressbook_link',$phpgw->link('/projects/addressbook.php','query='));
			$this->t->set_var('lang_action',lang('Add project'));
			$this->t->set_var('cats_list',$this->cats->formated_list('select','all',$cat_id,True));

			if (isset($phpgw_info['user']['preferences']['common']['currency']))
			{
				$currency = $phpgw_info['user']['preferences']['common']['currency'];
				$this->t->set_var('error','');
				$this->t->set_var('currency',$currency);
			}
			else
            {
				$this->t->set_var('error',lang('Please set your preferences for this application'));
			}

			$this->t->set_var('lang_choose',lang('Generate Project ID ?'));
			$this->t->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');

			$this->t->set_var('number',$values['number']);
			$this->t->set_var('title',$values['title']);
			$this->t->set_var('descr',$values['descr']);

			if (!$values['smonth'])
			{
				$values['smonth'] = date('m',time());
			}

			if (!$values['sday'])
			{
				$values['sday'] = date('d',time());
			}

			if (!$values['syear'])
			{
				$values['syear'] = date('Y',time());
			}

			$this->t->set_var('start_date_select',$phpgw->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																				$this->sbox->getMonthText('values[smonth]',$values['smonth']),
																				$this->sbox->getDays('values[sday]',$values['sday'])));
			$this->t->set_var('end_date_select',$phpgw->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																				$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																				$this->sbox->getDays('values[eday]',$values['eday'])));


			switch ($values['status'])
			{
				case 'active':		$stat_sel[0]=' selected'; break;
				case 'nonactive':	$stat_sel[1]=' selected'; break;
				case 'archive':		$stat_sel[2]=' selected'; break;
			}

			$status_list = '<option value="active"' . $stat_sel[0] . '>' . lang('Active') . '</option>' . "\n"
						. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n"
						. '<option value="archive"' . $stat_sel[2] . '>' . lang('Archive') . '</option>' . "\n";

			$this->t->set_var('status_list',$status_list);

			$employees = $this->boprojects->coordinator_list();
	
			while (list($null,$account) = each($employees))
			{
				$coordinator_list .= '<option value="' . $account['account_id'] . '"';
				if($account['account_id'] == $this->account)
				$coordinator_list .= ' selected';
				$coordinator_list .= '>' . $account['account_firstname'] . ' ' . $account['account_lastname']
										. ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
			}

			$this->t->set_var('coordinator_list',$coordinator_list);

			$this->t->set_var('abid',$abid);

			if (! $submit)
			{
				$this->t->set_var('name',$name);
			}
			else
			{
				$customer = $this->boprojects->read_customer_data($abid);
            	if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            	else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

				$this->t->set_var('name',$name);
			}

			$this->t->set_var('budget',$values['budget']);

			$this->t->set_var('access', '<input type="checkbox" name="values[access]" value="True"' . ($values['access'] == 'private'?' checked':'') . '>');

// ------------ activites bookable ----------------------

			$this->t->set_var('book_activities_list',$this->boprojects->select_activities_list($values['p_id'],False));

// -------------- activities billable ---------------------- 

    		$this->t->set_var('bill_activities_list',$this->boprojects->select_activities_list($values['p_id'],True));

			$this->t->set_var('done_url',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&cat_id=' . $cat_id));

			$this->t->set_var('lang_reset',lang('Clear form'));
			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','projects_add');
			$this->t->pfp('addhandle','add');

//			$phpgw->common->phpgw_footer();
		}

		function edit_project()
		{
			global $phpgw, $phpgw_info, $submit, $cat_id, $new_cat, $abid, $name, $values, $book_activities, $bill_activities, $project_id;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			if ($submit)
			{
				$values['project_id'] = $project_id;
				$values['cat'] = $cat_id;
				$values['customer'] = $abid;

				$error = $this->boprojects->check_values($values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$this->t->set_var('message',$phpgw->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_project($values, $book_activities, $bill_activities);
					Header('Location: ' . $phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&cat_id=' . $cat_id));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('projects_edit' => 'form.tpl'));
			$this->t->set_block('projects_edit','add','addhandle');
			$this->t->set_block('projects_edit','edit','edithandle');

			if (isset($phpgw_info['user']['preferences']['common']['currency']))
			{
				$currency = $phpgw_info['user']['preferences']['common']['currency'];
				$this->t->set_var('error','');
				$this->t->set_var('currency',$currency);
			}
			else
            {
				$this->t->set_var('error',lang('Please set your preferences for this application'));
			}

			$this->t->set_var('actionurl',$phpgw->link('/index.php','menuaction=projects.uiprojects.edit_project&project_id=' . $project_id));
			$this->t->set_var('addressbook_link',$phpgw->link('/projects/addressbook.php','query='));
			$this->t->set_var('lang_action',lang('Edit project'));

			$values = $this->boprojects->read_single_project($project_id);

			$this->t->set_var('cats_list',$this->cats->formated_list('select','all',$values['cat'],True));

			$this->t->set_var('lang_choose','');
			$this->t->set_var('choose','');

			$this->t->set_var('number',$values['number']);
			$this->t->set_var('title',$values['title']);
			$this->t->set_var('descr',$values['descr']);

			if ($values['sdate'] == 0)
			{
				$values['sday'] = 0;
				$values['smonth'] = 0;
				$values['syear'] = 0;
			}
			else
			{
				$values['sday'] = date('d',$values['sdate']);
				$values['smonth'] = date('m',$values['sdate']);
				$values['syear'] = date('Y',$values['sdate']);
			}

			$this->t->set_var('start_date_select',$phpgw->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																				$this->sbox->getMonthText('values[smonth]',$values['smonth']),
																				$this->sbox->getDays('values[sday]',$values['sday'])));
			if ($values['edate'] == 0)
			{
				$values['eday'] = 0;
				$values['emonth'] = 0;
				$values['eyear'] = 0;
			}
			else
			{
				$values['eday'] = date('d',$values['edate']);
				$values['emonth'] = date('m',$values['edate']);
				$values['eyear'] = date('Y',$values['edate']);
			}

			$this->t->set_var('end_date_select',$phpgw->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																				$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																				$this->sbox->getDays('values[eday]',$values['eday'])));


			switch ($values['status'])
			{
				case 'active':		$stat_sel[0]=' selected'; break;
				case 'nonactive':	$stat_sel[1]=' selected'; break;
				case 'archive':		$stat_sel[2]=' selected'; break;
			}

			$status_list = '<option value="active"' . $stat_sel[0] . '>' . lang('Active') . '</option>' . "\n"
						. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n"
						. '<option value="archive"' . $stat_sel[2] . '>' . lang('Archive') . '</option>' . "\n";

			$this->t->set_var('status_list',$status_list);

			$employees = $this->boprojects->coordinator_list();
	
			while (list($null,$account) = each($employees))
			{
				$coordinator_list .= '<option value="' . $account['account_id'] . '"';
				if($account['account_id'] == $values['coordinator'])
				$coordinator_list .= ' selected';
				$coordinator_list .= '>' . $account['account_firstname'] . ' ' . $account['account_lastname']
										. ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
			}

			$this->t->set_var('coordinator_list',$coordinator_list);

			$abid = $values['customer'];

			if (! $abid)
			{
				$this->t->set_var('name',$name);
			}
			else
			{
				$customer = $this->boprojects->read_customer_data($abid);
            	if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            	else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

				$this->t->set_var('name',$name);
			}

			$this->t->set_var('abid',$abid);

			$this->t->set_var('budget',$values['budget']);

			$this->t->set_var('access','<input type="checkbox" name="values[access]" value="True"' . ($values['access'] == 'private'?' checked':'') . '>');

// ------------ activites bookable ----------------------

			$this->t->set_var('book_activities_list',$this->boprojects->select_activities_list($project_id,False));

// -------------- activities billable ---------------------- 

    		$this->t->set_var('bill_activities_list',$this->boprojects->select_activities_list($project_id,True));

			if ($this->boprojects->check_perms($this->grants[$values['coordinator']],PHPGW_ACL_DELETE) || $values['coordinator'] == $this->account)
			{
				$this->t->set_var('delete','<form method="POST" action="' . $phpgw->link('/projects/delete.php','id=' . $project_id)
															. '"><input type="submit" value="' . lang('Delete') .'"></form>');
			}
			else
			{
				$this->t->set_var('delete','&nbsp;');
			}

			$this->t->set_var('done_url',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&cat_id=' . $cat_id));

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','projects_edit');
			$this->t->pfp('edithandle','edit');

//			$phpgw->common->phpgw_footer();
		}
	}
?>
