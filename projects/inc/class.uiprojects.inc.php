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

	class uiprojects
	{
		var $action;
		var $grants;
		var $start;
		var $filter;
		var $sort;
		var $order;
		var $cat_id;

		var $public_functions = array
		(
			'list_projects'		=> True,
			'add_project'		=> True,
			'edit_project'		=> True,
			'delete_pa'			=> True,
			'view_project'		=> True,
			'list_activities'	=> True,
			'add_activity'		=> True,
			'edit_activity'		=> True,
			'list_admins'		=> True,
			'edit_admins'		=> True,
			'abook'				=> True,
			'preferences'		=> True,
			'archive'			=> True
		);

		function uiprojects()
		{
			global $action;

			$this->boprojects				= CreateObject('projects.boprojects',True, $action);
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->cats						= CreateObject('phpgwapi.categories');
			$this->account					= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->t						= $GLOBALS['phpgw']->template;
			$this->grants					= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

			$this->start					= $this->boprojects->start;
			$this->query					= $this->boprojects->query;
			$this->filter					= $this->boprojects->filter;
			$this->order					= $this->boprojects->order;
			$this->sort						= $this->boprojects->sort;
			$this->cat_id					= $this->boprojects->cat_id;
		}

		function save_sessiondata($action)
		{
			$data = array
			(
				'start'		=> $this->start,
				'query'		=> $this->query,
				'filter'	=> $this->filter,
				'order'		=> $this->order,
				'sort'		=> $this->sort,
				'cat_id'	=> $this->cat_id
			);
			$this->boprojects->save_sessiondata($data, $action);
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
			$this->t->set_var('lang_act_number',lang('Activity ID'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_save',lang('Save'));
			$this->t->set_var('lang_reset',lang('Clear form'));
			$this->t->set_var('lang_budget',lang('Budget'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_bookable_activities',lang('Bookable activities'));
			$this->t->set_var('lang_billable_activities',lang('Billable activities'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_view',lang('View'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('lang_minperae',lang('Minutes per workunit'));
    		$this->t->set_var('lang_billperae',lang('Bill per workunit'));
			$this->t->set_var('lang_remarkreq',lang('Remark required'));
			$this->t->set_var('lang_select',lang('Select per button !'));
			$this->t->set_var('lang_invoices',lang('Invoices'));
			$this->t->set_var('lang_deliveries',lang('Deliveries'));
			$this->t->set_var('lang_stats',lang('Statistics'));
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

			$this->t->set_var('link_billing',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains'));
			$this->t->set_var('lang_billing',lang('Billing'));
			$this->t->set_var('link_jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$this->t->set_var('link_hours',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$this->t->set_var('link_statistics',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains'));
			$this->t->set_var('lang_statistics',lang("Statistics"));
			$this->t->set_var('link_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains'));
			$this->t->set_var('lang_deliveries',lang('Deliveries'));
			$this->t->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function list_projects()
		{
			global $action, $pro_parent;

			$this->display_app_header();

			$this->t->set_file(array('projects_list_t' => 'list.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'cat_id'		=> $this->cat_id
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			if (!$pro_parent)
			{
				$pro_parent = 0;
			}

			$pro = $this->boprojects->list_projects($this->start,True,$this->query,$this->filter,$this->sort,$this->order,'active',$this->cat_id,$action,$pro_parent);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			if ($action == 'mains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="">' . lang('None') . '</option>' . "\n"
							. $this->cats->formated_list('select','all',$this->cat_id,True) . '</select>';
				$this->t->set_var(lang_header,lang('Project list'));
				$this->t->set_var(lang_action,lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains', $status, $pro_parent) . '</select>';
				$this->t->set_var('lang_header',lang('Job list'));
				$this->t->set_var('lang_action',lang('Work hours'));
			}

			$this->t->set_var('action_list',$action_list);
			$this->t->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1,1));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

// ---------------- list header variable template-declarations --------------------------

			$this->t->set_var(sort_number,$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));

			if ($action == 'mains')
			{
				$this->t->set_var(sort_action,$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			}
			else
			{
				$this->t->set_var(sort_action,$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			}

			$this->t->set_var(sort_status,$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status'),$link_data));
			$this->t->set_var(sort_title,$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var(sort_end_date,$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$this->t->set_var(sort_coordinator,$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));


// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$title = $GLOBALS['phpgw']->strip_html($pro[$i]['title']);
				if (! $title) $title = '&nbsp;';

				$edate = $pro[$i]['edate'];
				if ($edate == 0)
				{
					$edateout = '&nbsp;';
				}
				else
				{
					$month  = $GLOBALS['phpgw']->common->show_date(time(),'n');
					$day    = $GLOBALS['phpgw']->common->show_date(time(),'d');
					$year   = $GLOBALS['phpgw']->common->show_date(time(),'Y');

					$edate = $edate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
					$edateout = $GLOBALS['phpgw']->common->show_date($edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					if (mktime(2,0,0,$month,$day,$year) == $edate) { $edateout = '<b>' . $edateout . '</b>'; }
					if (mktime(2,0,0,$month,$day,$year) >= $edate) { $edateout = '<font color="CC0000"><b>' . $edateout . '</b></font>'; }
				}

				if ($action == 'mains')
				{
					if ($pro[$i]['customer'] != 0) 
					{
						$customer = $this->boprojects->read_single_contact($pro[$i]['customer']);
            			if ($customer[0]['org_name'] == '') { $td_action = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            			else { $td_action = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
					}
					else { $td_action = '&nbsp;'; }
				}
				else
				{
					$sdate = $pro[$i]['sdate'];
					if ($sdate == 0) { $sdateout = '&nbsp;'; }
					else
					{
						$month = $GLOBALS['phpgw']->common->show_date(time(),'n');
						$day = $GLOBALS['phpgw']->common->show_date(time(),'d');
						$year = $GLOBALS['phpgw']->common->show_date(time(),'Y');

						$sdate = $sdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$td_action = $GLOBALS['phpgw']->common->show_date($sdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}
				}

				$cached_data = $this->boprojects->cached_accounts($pro[$i]['coordinator']);
				$coordinatorout = $GLOBALS['phpgw']->strip_html($cached_data[$pro[$i]['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro[$i]['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro[$i]['coordinator']]['lastname'] . ' ]');

// --------------- template declaration for list records -------------------------------------

				$this->t->set_var(array
				(
					'number'		=> $GLOBALS['phpgw']->strip_html($pro[$i]['number']),
					'td_action'		=> $td_action,
					'status'		=> lang($pro[$i]['status']),
					'title'			=> $title,
					'end_date'		=> $edateout,
					'coordinator'	=> $coordinatorout
				));

				$link_data['project_id'] = $pro[$i]['project_id'];
				$link_data['menuaction'] = 'projects.uiprojects.edit_project';

				if ($this->boprojects->isprojectadmin() && $pro[$i]['access'] != 'private')
				{
					$showedit = True;
				}
				else if ($this->boprojects->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $this->account)
				{
					$showedit = True;
				}

				if ($showedit)
				{
					$this->t->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$this->t->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$link_data['menuaction'] = 'projects.uiprojects.view_project';
				$this->t->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$this->t->set_var('lang_view_entry',lang('View'));

				if ($action == 'mains')
				{
					$this->t->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&pro_parent='
										. $pro[$i]['project_id'] . '&action=subs'));
					$this->t->set_var('lang_action_entry',lang('Jobs'));
				}
				else
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.list_hours';
					$this->t->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php',$link_data)); 
					$this->t->set_var('lang_action_entry',lang('Work hours'));
				}

				$this->t->parse('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

// --------------- template declaration for Add Form --------------------------

			$link_data['menuaction'] = 'projects.uiprojects.add_project';

			if ($action == 'mains')
			{
				if ($this->cat_id && $this->cat_id != 0)
				{
					$cat = $this->cats->return_single($this->cat_id);
				}

				if ($cat[0]['app_name'] == 'phpgw' || !$this->cat_id)
				{
					$showadd = True;
				}
				else if ($this->boprojects->check_perms($this->grants[$cat[0]['owner']],PHPGW_ACL_ADD) || $cat[0]['owner'] == $this->account)
				{
					$showadd = True;
				}
			}
			else
			{
				if ($pro_parent && $pro_parent != 0)
				{
					$pro = $this->boprojects->read_single_project($pro_parent);

					if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
					{
						$showadd = True;
					}
				}
			}

			if ($showadd)
			{
				$this->t->set_var('add','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
											. '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
			}
			else
			{
				$this->t->set_var('add','');
			}

// ----------------------- end Add form declaration ----------------------------

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}

		function coordinator_format($employee = '')
		{
			if (! $employee)
			{
				$employee = $this->account;
			}

			$employees = $this->boprojects->employee_list();

			while (list($null,$account) = each($employees))
			{
				$coordinator_list .= '<option value="' . $account['account_id'] . '"';
				if($account['account_id'] == $employee)
				$coordinator_list .= ' selected';
				$coordinator_list .= '>' . $account['account_firstname'] . ' ' . $account['account_lastname']
										. ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
			}
			return $coordinator_list;
		}

		function status_format($status = '')
		{
			switch ($status)
			{
				case 'active':		$stat_sel[0]=' selected'; break;
				case 'nonactive':	$stat_sel[1]=' selected'; break;
				case 'archive':		$stat_sel[2]=' selected'; break;
			}

			$status_list = '<option value="active"' . $stat_sel[0] . '>' . lang('Active') . '</option>' . "\n"
						. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n"
						. '<option value="archive"' . $stat_sel[2] . '>' . lang('Archive') . '</option>' . "\n";
			return $status_list;
		}



		function add_project()
		{
			global $submit, $cat_id, $new_cat, $abid, $name, $values, $book_activities, $bill_activities, $pro_parent, $action;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'cat_id'		=> $cat_id
			);

			if ($submit)
			{
				$values['cat']		= $cat_id;
				$values['customer'] = $abid;
				$values['parent']	= $pro_parent;

				$error = $this->boprojects->check_values($action, $values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_project($action, $values, $book_activities, $bill_activities);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('projects_add' => 'form.tpl'));
			$this->t->set_block('projects_add','add','addhandle');
			$this->t->set_block('projects_add','edit','edithandle');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$this->t->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$link_data['menuaction'] = 'projects.uiprojects.add_project';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));

			$this->t->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');

			$this->t->set_var('currency',$prefs['currency']);
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

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																				$this->sbox->getMonthText('values[smonth]',$values['smonth']),
																				$this->sbox->getDays('values[sday]',$values['sday'])));
			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																				$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																				$this->sbox->getDays('values[eday]',$values['eday'])));


			$this->t->set_var('status_list',$this->status_format($values['status']));

			$this->t->set_var('coordinator_list',$this->coordinator_format($values['coordinator']));

			$this->t->set_var('budget',$values['budget']);

			$this->t->set_var('access', '<input type="checkbox" name="values[access]" value="True"'
										. ($values['access'] == 'private'?' checked':'') . '>');

			if ($action == 'mains')
			{
				$this->t->set_var('lang_action',lang('Add project'));
				$cat = '<select name="new_cat"><option value="">' . lang('None') . '</option>'
						.	$this->cats->formated_list('select','all',$cat_id,True) . '</select>';

				$this->t->set_var('cat',$cat);
				$this->t->set_var('lang_parent','');
				$this->t->set_var('pro_parent','');
				$this->t->set_var('lang_choose',lang('Generate Project ID ?'));
				$this->t->set_var('lang_number',lang('Project ID'));

// ------------ activites bookable ----------------------

				$this->t->set_var('book_activities_list',$this->boprojects->select_activities_list($project_id, False));

// -------------- activities billable ---------------------- 

    			$this->t->set_var('bill_activities_list',$this->boprojects->select_activities_list($project_id, True));
			}
			else
			{
				if ($pro_parent && $action == 'subs')
				{
					$parent = $this->boprojects->read_single_project($pro_parent);

					$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($parent['number']) . ' ' . $GLOBALS['phpgw']->strip_html($parent['title']));
					$this->t->set_var('cat',$this->cats->id2name($parent['cat']));
					$cat_id = $parent['cat'];
					$this->t->set_var('book_activities_list',$this->boprojects->select_pro_activities($project_id = '', $pro_parent, False));				
    				$this->t->set_var('bill_activities_list',$this->boprojects->select_pro_activities($project_id = '', $pro_parent, True));

					$abid = $parent['customer'];
				}

				$this->t->set_var('lang_parent',lang('Main project:'));
				$this->t->set_var('lang_action',lang('Add job'));
				$this->t->set_var('lang_choose',lang('Generate Job ID ?'));
				$this->t->set_var('lang_number',lang('Job ID'));
			}

			$customer = $this->boprojects->read_single_contact($abid);
            if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

			$this->t->set_var('name',$name);
			$this->t->set_var('abid',$abid);

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','projects_add');
			$this->t->pfp('addhandle','add');
		}

		function edit_project()
		{
			global $submit, $cat_id, $new_cat, $abid, $name, $values, $book_activities, $bill_activities, $project_id, $action, $pro_parent;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'project_id'	=> $project_id,
				'cat_id'		=> $cat_id
			);

			if ($submit)
			{
				$values['project_id']	= $project_id;
				$values['cat']			= $cat_id;
				$values['customer']		= $abid;
				$values['parent']		= $pro_parent;

				$error = $this->boprojects->check_values($action, $values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_project($action, $values, $book_activities, $bill_activities);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('projects_edit' => 'form.tpl'));
			$this->t->set_block('projects_edit','add','addhandle');
			$this->t->set_block('projects_edit','edit','edithandle');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$this->t->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));

			$link_data['menuaction'] = 'projects.uiprojects.edit_project';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$values = $this->boprojects->read_single_project($project_id);

			$this->t->set_var('lang_choose','');
			$this->t->set_var('choose','');

			$this->t->set_var('currency',$prefs['currency']);
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

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
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

			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																							$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																							$this->sbox->getDays('values[eday]',$values['eday'])));

			$this->t->set_var('status_list',$this->status_format($values['status']));

			$this->t->set_var('coordinator_list',$this->coordinator_format($values['coordinator']));

			$this->t->set_var('budget',$values['budget']);

			$this->t->set_var('access','<input type="checkbox" name="values[access]" value="True"' . ($values['access'] == 'private'?' checked':'') . '>');

			if ($action == 'mains')
			{
				$this->t->set_var('lang_action',lang('Edit project'));
				$cat = '<select name="new_cat"><option value="">' . lang('None') . '</option>'
						.	$this->cats->formated_list('select','all',$values['cat'],True) . '</select>';

				$this->t->set_var('cat',$cat);
				$this->t->set_var('lang_parent','');
				$this->t->set_var('pro_parent','');
				$this->t->set_var('lang_number',lang('Project ID'));

// ------------ activites bookable ----------------------

				$this->t->set_var('book_activities_list',$this->boprojects->select_activities_list($project_id,False));

// -------------- activities billable ---------------------- 

    			$this->t->set_var('bill_activities_list',$this->boprojects->select_activities_list($project_id,True));
			}
			else
			{
				if ($pro_parent && $action == 'subs')
				{
					$parent = $this->boprojects->read_single_project($pro_parent);

					$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($parent['number']) . ' ' . $GLOBALS['phpgw']->strip_html($parent['title']));
					$this->t->set_var('cat',$this->cats->id2name($parent['cat']));
					$cat_id = $parent['cat'];
					$this->t->set_var('book_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, False));				
    				$this->t->set_var('bill_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, True));
				}

				$this->t->set_var('lang_parent',lang('Main project:'));
				$this->t->set_var('lang_action',lang('Edit job'));
				$this->t->set_var('lang_number',lang('Job ID'));
			}

			$abid = $values['customer'];
			$customer = $this->boprojects->read_single_contact($abid);
            if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

			$this->t->set_var('name',$name);
			$this->t->set_var('abid',$abid);

			$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
			$link_data['pa_id'] = $project_id;
			if ($this->boprojects->check_perms($this->grants[$values['coordinator']],PHPGW_ACL_DELETE) || $values['coordinator'] == $this->account)
			{
				$this->t->set_var('delete','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
											. '"><input type="submit" value="' . lang('Delete') .'"></form>');
			}
			else
			{
				$this->t->set_var('delete','&nbsp;');
			}

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','projects_edit');
			$this->t->pfp('edithandle','edit');
		}


		function view_project()
		{
			global $project_id, $action, $pro_parent;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			$this->display_app_header();

			$this->t->set_file(array('view' => 'view.tpl'));

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$this->t->set_var('done_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$values = $this->boprojects->read_single_project($project_id);

			$this->t->set_var('cat',$this->cats->id2name($values['cat']));

			if ($action == 'mains')
			{
				$this->t->set_var('lang_action',lang('View project'));
				$this->t->set_var('lang_number',lang('Project ID'));
				$this->t->set_var('lang_parent','');
				$this->t->set_var('pro_parent','');
// ------------ activites bookable ----------------------

				$this->t->set_var('book_activities_list',$this->boprojects->select_activities_list($project_id,False));

// -------------- activities billable ---------------------- 

    			$this->t->set_var('bill_activities_list',$this->boprojects->select_activities_list($project_id,True));
			}
			else
			{
				if ($pro_parent && $action == 'subs')
				{
					$parent = $this->boprojects->read_single_project($pro_parent);

					$this->t->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($parent['number']) . ' ' . $GLOBALS['phpgw']->strip_html($parent['title']));
					$this->t->set_var('cat',$this->cats->id2name($parent['cat']));
					$this->t->set_var('book_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, False));				
    				$this->t->set_var('bill_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, True));
				}
				$this->t->set_var('lang_action',lang('View job'));
				$this->t->set_var('lang_number',lang('Job ID'));
				$this->t->set_var('lang_parent',lang('Main project:'));
			}

			$this->t->set_var('number',$GLOBALS['phpgw']->strip_html($values['number']));
			$title = $GLOBALS['phpgw']->strip_html($values['title']);
			if (! $title) $title = '&nbsp;';
			$this->t->set_var('title',$title);
			$descr = $GLOBALS['phpgw']->strip_html($values['descr']);
			if (! $descr) $descr = '&nbsp;';
			$this->t->set_var('descr',$descr);
			$this->t->set_var('status',$values['status']);
			$this->t->set_var('budget',$values['budget']);
			$this->t->set_var('currency',$prefs['currency']);

			$sdate = $values['sdate'];
			$edate = $values['edate'];

			if ($sdate != 0)
			{
				$smonth = $GLOBALS['phpgw']->common->show_date(time(),'n');
				$sday = $GLOBALS['phpgw']->common->show_date(time(),'d');
				$syear = $GLOBALS['phpgw']->common->show_date(time(),'Y');
				$sdate = $sdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$sdateout = $GLOBALS['phpgw']->common->show_date($sdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			}
			else
			{
				$sdateout = '&nbsp;';
			}

			$this->t->set_var('sdate',$sdateout);

			if ($edate != 0)
			{
				$emonth = $GLOBALS['phpgw']->common->show_date(time(),'n');
				$eday = $GLOBALS['phpgw']->common->show_date(time(),'d');
				$eyear = $GLOBALS['phpgw']->common->show_date(time(),'Y');
				$edate = $edate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$edateout = $GLOBALS['phpgw']->common->show_date($edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			}
			else
			{
				$edateout = '&nbsp;';
			}

			$this->t->set_var('edate',$edateout);

			$this->t->set_var('coordinator',$GLOBALS['phpgw']->accounts->id2name($values['coordinator']));

// ----------------------------------- customer ------------------------------

			if ($values['customer'] != 0) 
			{
				$customer = $this->boprojects->read_single_contact($values['customer']);
            	if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            	else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
			}
			else { $customerout = '&nbsp;'; }

			$this->t->set_var('customer',$customerout);

			$this->t->pfp('out','view');
//			$phpgw->common->hook('projects_view');
		}

		function delete_pa()
		{
			global $confirm, $pa_id, $subs, $pro_parent, $action;

			switch($action)
			{
				case 'mains'	: $menu = 'projects.uiprojects.list_projects'; break;
				case 'subs'		: $menu = 'projects.uiprojects.list_projects'; break;
				case 'act'		: $menu = 'projects.uiprojects.list_activities'; break;
			}

			$link_data = array
			(
				'menuaction'	=> $menu,
				'pro_parent'	=> $pro_parent,
				'pa_id'			=> $pa_id,
				'action'		=> $action
			);

			if ($confirm)
			{
				if ($subs)
				{
					$this->boprojects->delete_pa($action, $pa_id, True);
				}
				else 
				{
					$this->boprojects->delete_pa($action, $pa_id, False);
				}
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
			}

			$this->display_app_header();
			$this->t->set_file(array('pa_delete' => 'delete.tpl'));

			$this->t->set_var('lang_subs','');
			$this->t->set_var('subs', '');

			$this->t->set_var('nolink',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('deleteheader',lang('Are you sure you want to delete this entry ?'));
			$this->t->set_var('lang_no',lang('No'));
			$this->t->set_var('lang_yes',lang('Yes'));

			if ($action != 'act')
			{
				$exists = $this->boprojects->exists('mains', 'par', $num ='', $pa_id);

				if ($exists)
				{
					$this->t->set_var('lang_subs',lang('Do you also want to delete all sub projects ?'));
					$this->t->set_var('subs','<input type="checkbox" name="subs" value="True">');
				}
			}

			$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->pfp('out','pa_delete');
		}

		function list_activities()
		{
			global $action;

			$this->display_app_header();

			$this->t->set_file(array('activities_list_t' => 'listactivities.tpl'));
			$this->t->set_block('activities_list_t','activities_list','list');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act',
				'cat_id'		=> $this->cat_id
			);

			$this->t->set_var('lang_header',lang('Activities list'));
			$this->t->set_var('project_url',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));

			if (!$this->start)
			{
				$this->start = 0;
			}

			$act = $this->boprojects->list_activities($this->start, True, $this->query, $this->sort, $this->order, $this->cat_id);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

            $this->t->set_var('cat_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('categories_list',$this->cats->formated_list('select','all',$this->cat_id,'True'));
            $this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
            $this->t->set_var('search_list',$this->nextmatchs->search(1));

// ----------------- list header variable template-declarations ---------------------------
  
			$this->t->set_var('currency',$prefs['currency']);
			$this->t->set_var('sort_num',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Activity ID')));
			$this->t->set_var('sort_descr',$this->nextmatchs->show_sort_order($this->sort,'descr',$this->order,'/index.php',lang('Description')));
			$this->t->set_var('sort_billperae',$this->nextmatchs->show_sort_order($this->sort,'billperae',$this->order,'/index.php',lang('Bill per workunit')));
			$this->t->set_var('sort_minperae',$this->nextmatchs->show_sort_order($this->sort,'minperae',$this->order,'/index.php',lang('Minutes per workunit')));

// ---------------------------- end header declaration -------------------------------------

            for ($i=0;$i<count($act);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$descr = $GLOBALS['phpgw']->strip_html($act[$i]['descr']);
				if (! $descr)
				{
					$descr  = '&nbsp;';
				}

// ------------------- template declaration for list records -------------------------
      
				$this->t->set_var(array('num'	=> $GLOBALS['phpgw']->strip_html($act[$i]['number']),
										'descr' => $descr,
									'billperae' => $act[$i]['billperae'],
									'minperae'	=> $act[$i]['minperae']));

				$link_data['menuaction']	= 'projects.uiprojects.edit_activity';
				$link_data['activity_id']	= $act[$i]['activity_id'];
				$this->t->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$link_data['menuaction']	= 'projects.uiprojects.delete_pa';
				$link_data['pa_id']	= $act[$i]['activity_id'];
				$this->t->set_var('delete',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$this->t->fp('list','activities_list',True);

// ------------------------------- end record declaration --------------------------------

			}

// ------------------------- template declaration for Add Form ---------------------------

			$link_data['menuaction'] = 'projects.uiprojects.add_activity';
			$this->t->set_var('add_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->set_var('lang_add',lang('Add'));
			$this->t->set_var('lang_delete',lang('Delete'));
			$this->t->pfp('out','activities_list_t',True);
			$this->save_sessiondata($action);

// -------------------------------- end Add form declaration ------------------------------

//			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function add_activity()
		{
			global $submit, $cat_id, $new_cat, $values;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act',
				'cat_id'		=> $cat_id
			);

			if ($submit)
			{
				$values['cat'] = $cat_id;

				$error = $this->boprojects->check_pa_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_activity($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('activity_add' => 'formactivity.tpl'));
			$this->t->set_block('activity_add','add','addhandle');
			$this->t->set_block('activity_add','edit','edithandle');

			$this->t->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.add_activity'));
			$this->t->set_var('lang_action',lang('Add activity'));
			$this->t->set_var('cats_list',$this->cats->formated_list('select','all',$cat_id,True));

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$this->t->set_var('lang_choose',lang('Generate Activity ID ?'));
			$this->t->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');

			$this->t->set_var('number',$values['number']);
			$this->t->set_var('title',$values['title']);
			$this->t->set_var('descr',$values['descr']);

			$this->t->set_var('lang_num',lang('Activity ID'));
			$this->t->set_var('num',$values['number']);
			$this->t->set_var('descr',$values['descr']);
			$this->t->set_var('minperae',$values['minperae']);
			$this->t->set_var('currency',$prefs['currency']);
    		$this->t->set_var('billperae',$billperae);

			if ($values['remarkreq'] == 'N'):
				$stat_sel[0]=' selected';
			elseif ($values['remarkreq'] == 'Y'):
				$stat_sel[1]=' selected';
			endif;

			$remarkreq_list = '<option value="N"' . $stat_sel[0] . '>' . lang('No') . '</option>' . "\n"
							. '<option value="Y"' . $stat_sel[1] . '>' . lang('Yes') . '</option>' . "\n";

			$this->t->set_var('remarkreq_list',$remarkreq_list);

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','activity_add');
			$this->t->pfp('addhandle','add');
		}


		function edit_activity()
		{
			global $submit, $cat_id, $new_cat, $values, $activity_id;

			if ($new_cat)
			{
				$cat_id = $new_cat;
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act',
				'cat_id'		=> $cat_id
			);

			if ($submit)
			{
				$values['cat'] = $cat_id;
				$values['activity_id'] = $activity_id;

				$error = $this->boprojects->check_pa_values($values);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_activity($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$this->t->set_file(array('activity_edit' => 'formactivity.tpl'));
			$this->t->set_block('activity_edit','add','addhandle');
			$this->t->set_block('activity_edit','edit','edithandle');

			$this->t->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.edit_activity&activity_id=' . $activity_id));
			$this->t->set_var('lang_action',lang('Edit activity'));

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$this->t->set_var('lang_choose','');
			$this->t->set_var('choose','');
			$this->t->set_var('currency',$prefs['currency']);

			$values = $this->boprojects->read_single_activity($activity_id);

			$this->t->set_var('cats_list',$this->cats->formated_list('select','all',$values['cat'],True));
			$this->t->set_var('num',$GLOBALS['phpgw']->strip_html($values['number']));
			$descr  = $GLOBALS['phpgw']->strip_html($values['descr']);
			if (! $descr) $descr = '&nbsp;';
			$this->t->set_var('descr',$descr);

			if ($values['remarkreq'] == 'N'):
				$stat_sel[0]=' selected';
			elseif ($values['remarkreq'] == 'Y'):
				$stat_sel[1]=' selected';
			endif;

			$remarkreq_list = '<option value="N"' . $stat_sel[0] . '>' . lang('No') . '</option>' . "\n"
					. '<option value="Y"' . $stat_sel[1] . '>' . lang('Yes') . '</option>' . "\n";

			$this->t->set_var('remarkreq_list',$remarkreq_list);
			$this->t->set_var('billperae',$values['billperae']);
			$this->t->set_var('minperae',$values['minperae']);

			$link_data['menuaction']	= 'projects.uiprojects.delete_pa';
			$link_data['pa_id']	= $values[$i]['activity_id'];
			$this->t->set_var('deleteurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_delete',lang('Delete'));

			$this->t->set_var('edithandle','');
			$this->t->set_var('addhandle','');
			$this->t->pfp('out','activity_edit');
			$this->t->pfp('edithandle','edit');
		}

		function list_admins()
		{
			global $action;

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_admins',
				'action'		=> 'pad'
			);

			$this->set_app_langs();

			$this->t->set_file(array('admin_list_t' => 'list_admin.tpl'));
			$this->t->set_block('admin_list_t','admin_list','list');

			$this->t->set_var('lang_action',lang('Project administration'));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));
			$this->t->set_var('doneurl',$GLOBALS['phpgw']->link('/admin/index.php'));

			if (!$this->start)
			{
				$this->start = 0;
			}

			$admins = $this->boprojects->list_admins($this->start, $this->query, $this->sort, $this->order);

//--------------------------------- nextmatch --------------------------------------------
 
			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

    		$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));
 
// ------------------------------ end nextmatch ------------------------------------------
 
//------------------- list header variable template-declarations -------------------------

			$this->t->set_var('sort_lid',$this->nextmatchs->show_sort_order($this->sort,'account_lid',$this->order,'/index.php',lang('Username / Group'),$link_data));
			$this->t->set_var('sort_lastname',$this->nextmatchs->show_sort_order($this->sort,'account_lastname',$this->order,'/index.php',lang('Lastname'),$link_data));
			$this->t->set_var('sort_firstname',$this->nextmatchs->show_sort_order($this->sort,'account_firstname',$this->order,'/index.php',lang('Firstname'),$link_data));

// -------------------------- end header declaration --------------------------------------

			for ($i=0;$i<count($admins);$i++)
			{
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$lid = $admins[$i]['lid'];

				if ($admins[$i]['type']=='u')
				{
					$firstname = $admins[$i]['firstname'];
					if (!$firstname) { $firstname = '&nbsp;'; }
					$lastname = $admins[$i]['lastname'];
					if (!$lastname) { $lastname = '&nbsp;'; }
				}
				else
				{
					$firstname = '&nbsp;';
					$lastname = '&nbsp;';
				}

				$this->t->set_var(array('lid' => $lid,
							'firstname' => $firstname,
							'lastname' => $lastname));

				$this->t->fp('list','admin_list',True);
			}
			$link_data['menuaction'] = 'projects.uiprojects.edit_admins';
			$this->t->set_var('addurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->pfp('out','admin_list_t',True);
			$this->save_sessiondata($action);
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function edit_admins()
		{
			global $action, $submit, $users, $groups;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.edit_admins',
				'action'		=> 'pad'
			);

			if ($submit)
			{
				$this->boprojects->edit_admins( $users, $groups);
				$link_data['menuaction'] = 'projects.uiprojects.list_admins';
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->set_app_langs();

			$this->t->set_file(array('admin_add' => 'form_admin.tpl'));

			$this->t->set_var('lang_action',lang('Edit project administrator list'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->set_var('users_list',$this->boprojects->selected_admins('aa'));
			$this->t->set_var('groups_list',$this->boprojects->selected_admins('ag'));
			$this->t->set_var('lang_users_list',lang('Select users'));
			$this->t->set_var('lang_groups_list',lang('Select groups'));

			$this->t->pfp('out','admin_add');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function abook()
		{
			global $start, $cat_id, $sort, $order, $filter, $qfilter;

			$this->t->set_file(array('abook_list_t' => 'addressbook.tpl'));
			$this->t->set_block('abook_list_t','abook_list','list');

			$this->cats->app_name = 'addressbook';

			$this->set_app_langs();

			$this->t->set_var('title',$GLOBALS['phpgw_info']['site_title']);
			$this->t->set_var('lang_action',lang('Address book'));
			$this->t->set_var('charset',$GLOBALS['phpgw']->translation->translate('charset'));
			$this->t->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.abook',
				'start'			=> $start,
				'sort'			=> $sort,
				'order'			=> $order,
				'cat_id'		=> $cat_id,
				'filter'		=> $filter
			);

			if (! $start) { $start = 0; }

			if (!$filter) { $filter = 'none'; }

			$qfilter = 'tid=n';

			switch ($filter)
			{
				case 'none': break;		
				case 'private': $qfilter .= ',access=private'; break;
				case 'yours': $qfilter .= ',owner=' . $this->account; break;
			}

			if ($cat_id)
			{
				$qfilter .= ',cat_id=' . $cat_id;
			}
 
			$entries = $this->boprojects->read_abook($start, $query, $qfilter, $sort, $order);

// --------------------------------- nextmatch ---------------------------

			$left = $this->nextmatchs->left('/index.php',$start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$start,$this->boprojects->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$start));

// -------------------------- end nextmatch ------------------------------------

			$this->t->set_var('cats_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('cats_list',$this->cats->formated_list('select','all',$cat_id,True));
			$this->t->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1,1));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

// ---------------- list header variable template-declarations --------------------------

// -------------- list header variable template-declaration ------------------------

			$this->t->set_var('sort_company',$this->nextmatchs->show_sort_order($sort,'org_name',$order,'/index.php',lang('Company'),$link_data));
			$this->t->set_var('sort_firstname',$this->nextmatchs->show_sort_order($sort,'n_given',$order,'/index.php',lang('Firstname'),$link_data));
			$this->t->set_var('sort_lastname',$this->nextmatchs->show_sort_order($sort,'n_family',$order,'/index.php',lang('Lastname'),$link_data));
			$this->t->set_var('lang_select',lang('Select'));

// ------------------------- end header declaration --------------------------------

			for ($i=0;$i<count($entries);$i++)
			{
				$this->t->set_var('tr_color',$this->nextmatchs->alternate_row_color($tr_color));
				$firstname = $entries[$i]['n_given'];
				if (!$firstname) { $firstname = '&nbsp;'; }
				$lastname = $entries[$i]['n_family'];
				if (!$lastname) { $lastname = '&nbsp;'; }
				$company = $entries[$i]['org_name'];
				if (!$company) { $company = '&nbsp;'; }

// ---------------- template declaration for list records -------------------------- 

				$this->t->set_var(array('company' 	=> $company,
									'firstname' 	=> $firstname,
									'lastname'		=> $lastname,
									'abid'			=> $entries[$i]['id']));

				$this->t->parse('list','abook_list',True);
			}

			$this->t->parse('out','abook_list_t',True);
			$this->t->p('out');

			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function preferences()
		{
			global $submit, $prefs, $abid;

			if ($submit)
			{
				$prefs['abid'] = $abid;
				$this->boprojects->save_prefs($prefs);
				Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file(array('prefs' => 'preferences.tpl'));

			$this->set_app_langs();

			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.preferences'));
			$this->t->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));

			$this->t->set_var('lang_action',lang('Project preferences'));
			$this->t->set_var('lang_select_tax',lang('Select tax for workhours'));
			$this->t->set_var('lang_select',lang('Select per button !'));
			$this->t->set_var('lang_address',lang('Select your address'));

			$prefs = $this->boprojects->read_prefs();

			$this->t->set_var('tax',$prefs['tax']);

			if (isset($prefs['abid']))
			{
				$abid = $prefs['abid'];

				$entry = $this->boprojects->read_single_contact($abid);

				if ($entry[0]['org_name'] == '') { $this->t->set_var('name',$entry[0]['n_given'] . ' ' . $entry[0]['n_family']); }
				else { $this->t->set_var('name',$entry[0]['org_name'] . ' [ ' . $entry[0]['n_given'] . ' ' . $entry[0]['n_family'] . ' ]'); }
			}
			else
			{
				$this->t->set_var('name',$name);
			}

			$this->t->set_var('abid',$abid);

			$this->t->pfp('out','prefs');
		}

		function archive()
		{
			global $action, $pro_parent;

			$this->display_app_header();

			$this->t->set_file(array('projects_list_t' => 'archive.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'amains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.archive',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'cat_id'		=> $this->cat_id
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			if (!$pro_parent)
			{
				$pro_parent = 0;
			}

			$pro = $this->boprojects->list_projects($this->start,True,$this->query,$this->filter,$this->sort,$this->order,'archive',$this->cat_id,$action,$pro_parent);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------------ end nextmatch template ------------------------------------

			if ($action == 'amains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="">' . lang('None') . '</option>' . "\n"
							. $this->cats->formated_list('select','all',$this->cat_id,True) . '</select>';
				$this->t->set_var(lang_header,lang('Project archive'));
				$this->t->set_var(lang_action,lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains', 'archive', $pro_parent) . '</select>';
				$this->t->set_var('lang_header',lang('Job archive'));
				$this->t->set_var('lang_action',lang('Work hours'));
			}

			$this->t->set_var('action_list',$action_list);
			$this->t->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1,1));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

// ---------------- list header variable template-declarations --------------------------

			$this->t->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));

			if ($action == 'amains')
			{
				$this->t->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			}
			else
			{
				$this->t->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			}

			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('title'),$link_data));
			$this->t->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('End date'),$link_data));
			$this->t->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));

// ----------------------------- end header declaration ----------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$title = $GLOBALS['phpgw']->strip_html($pro[$i]['title']);
				if (! $title) $title = '&nbsp;';

				$edate = $pro[$i]['edate'];
				if ($edate == 0)
				{
					$edateout = '&nbsp;';
				}
				else
				{
					$edate = $edate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
					$edateout = $GLOBALS['phpgw']->common->show_date($edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
				}

				if ($action == 'amains')
				{
					if ($pro[$i]['customer'] != 0) 
					{
						$customer = $this->boprojects->read_single_contact($pro[$i]['customer']);
            			if ($customer[0]['org_name'] == '') { $td_action = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            			else { $td_action = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
					}
					else { $td_action = '&nbsp;'; }
				}
				else
				{
					$sdate = $pro[$i]['sdate'];
					if ($sdate == 0) { $sdateout = '&nbsp;'; }
					else
					{
						$sdate = $sdate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$td_action = $GLOBALS['phpgw']->common->show_date($sdate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}
				}

				$cached_data = $this->boprojects->cached_accounts($pro[$i]['coordinator']);
				$coordinatorout = $GLOBALS['phpgw']->strip_html($cached_data[$pro[$i]['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro[$i]['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro[$i]['coordinator']]['lastname'] . ' ]');

// --------------- template declaration for list records -------------------------------------

				$this->t->set_var(array
				(
					'number'		=> $GLOBALS['phpgw']->strip_html($pro[$i]['number']),
					'td_action'		=> $td_action,
					'status'		=> lang($pro[$i]['status']),
					'title'			=> $title,
					'end_date'		=> $edateout,
					'coordinator'	=> $coordinatorout
				));

				$link_data['project_id'] = $pro[$i]['project_id'];

				if ($this->boprojects->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $this->account)
				{
					$link_data['menuaction'] = 'projects.uiprojects.edit_project';
					$this->t->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$this->t->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				if ($action == 'amains')
				{
					$this->t->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&pro_parent='
										. $pro[$i]['project_id'] . '&action=asubs'));
					$this->t->set_var('lang_action_entry',lang('Jobs'));
				}
				else
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.list_hours';
					$link_data['action'] = 'asubs';
					$this->t->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php',$link_data)); 
					$this->t->set_var('lang_action_entry',lang('Work hours'));
				}

				$this->t->set_var('delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_deliveries&project_id='
																	. $pro[$i]['project_id']));
				$this->t->set_var('invoice',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_invoices&project_id='
																	. $pro[$i]['project_id']));
				$this->t->set_var('stats',$GLOBALS['phpgw']->link('/projects/index.php','menuaction=projects.uistatistics.project_stat&project_id='
																	. $pro[$i]['project_id']));
				$this->t->set_var('lang_invoice_entry',lang('Invoices'));
				$this->t->set_var('lang_delivery_entry',lang('Deliveries'));
				$this->t->set_var('lang_stats_entry',lang('Statistics'));

				$this->t->fp('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}
	}
?>
