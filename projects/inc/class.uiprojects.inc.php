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
			'delete_project'	=> True,
			'view_project'		=> True,
			'list_activities'	=> True
		);

		function uiprojects()
		{
			global $phpgw, $phpgw_info, $action;

			$this->boprojects				= CreateObject('projects.boprojects',True, $action);
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->cats						= CreateObject('phpgwapi.categories');
			$this->account					= $phpgw_info['user']['account_id'];
			$this->t						= $phpgw->template;
			$this->grants					= $phpgw->acl->get_grants('projects');
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
				$this->t->set_var('link_activities',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));                                                                                                         
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
			$this->t->set_var('link_jobs',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$this->t->set_var('link_hours',$phpgw->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$this->t->set_var('link_statistics',$phpgw->link('/projects/stats_projectlist.php'));
			$this->t->set_var('lang_statistics',lang("Statistics"));
			$this->t->set_var('link_delivery',$phpgw->link('/projects/del_index.php'));
			$this->t->set_var('lang_delivery',lang('Delivery'));
			$this->t->set_var('link_projects',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$phpgw->link('/projects/archive.php'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$phpgw->common->phpgw_header();
			echo parse_navbar();
		}

		function list_projects()
		{
			global $phpgw, $phpgw_info, $action, $pro_parent;

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
				'action'		=> $action
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
				$action_list= '<form method="POST" action="' . $phpgw->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="">' . lang('None') . '</option>' . "\n"
							. $this->cats->formated_list('select','all',$this->cat_id,True) . '</select>';
				$this->t->set_var(lang_header,lang('Project list'));
				$this->t->set_var(lang_action,lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $phpgw->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="project_id" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains',$pro_parent) . '</select>';
				$this->t->set_var('lang_header',lang('Job list'));
				$this->t->set_var('lang_action',lang('Work hours'));
			}

			$this->t->set_var('action_list',$action_list);
			$this->t->set_var('filter_action',$phpgw->link('/index.php',$link_data));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1,1));
			$this->t->set_var('search_action',$phpgw->link('/index.php',$link_data));
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
				$title = $phpgw->strip_html($pro[$i]['title']);
				if (! $title) $title = '&nbsp;';

				$edate = $pro[$i]['edate'];
				if ($edate == 0)
				{
					$edateout = '&nbsp;';
				}
				else
				{
					$month  = $phpgw->common->show_date(time(),'n');
					$day    = $phpgw->common->show_date(time(),'d');
					$year   = $phpgw->common->show_date(time(),'Y');

					$edate = $edate + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
					$edateout = $phpgw->common->show_date($edate,$phpgw_info['user']['preferences']['common']['dateformat']);
					if (mktime(2,0,0,$month,$day,$year) == $edate) { $edateout = '<b>' . $edateout . '</b>'; }
					if (mktime(2,0,0,$month,$day,$year) >= $edate) { $edateout = '<font color="CC0000"><b>' . $edateout . '</b></font>'; }
				}

				if ($action == 'mains')
				{
					if ($pro[$i]['customer'] == 0) { $customerout = '&nbsp;'; }
					else
					{
						$customer = $this->boprojects->read_customer_data($pro[$i]['customer']);
            			if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            			else { $td_action = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
					}
				}
				else
				{
					$sdate = $pro[$i]['sdate'];
					if ($sdate == 0) { $sdateout = '&nbsp;'; }
					else
					{
						$month = $phpgw->common->show_date(time(),'n');
						$day = $phpgw->common->show_date(time(),'d');
						$year = $phpgw->common->show_date(time(),'Y');

						$sdate = $sdate + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
						$td_action = $phpgw->common->show_date($sdate,$phpgw_info['user']['preferences']['common']['dateformat']);
					}
				}

				$cached_data = $this->boprojects->cached_accounts($pro[$i]['coordinator']);
				$coordinatorout = $phpgw->strip_html($cached_data[$pro[$i]['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro[$i]['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro[$i]['coordinator']]['lastname'] . ' ]');

// --------------- template declaration for list records -------------------------------------

				$this->t->set_var(array
				(
					'number'		=> $phpgw->strip_html($pro[$i]['number']),
					'td_action'		=> $td_action,
					'status'		=> lang($pro[$i]['status']),
					'title'			=> $title,
					'end_date'		=> $edateout,
					'coordinator'	=> $coordinatorout
				));

				if ($action == 'mains')
				{
					$link_data['pro_parent']	= $pro[$i]['project_id'];
					$link_data['action']		= 'subs';
					$this->t->set_var('action_entry',$phpgw->link('/index.php',$link_data));
					$this->t->set_var('lang_action_entry',lang('Jobs'));
				}
				else
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.list_hours';
					$link_data['project_id'] = $pro[$i]['project_id'];
					$this->t->set_var('action_entry',$phpgw->link('/index.php',$link_data)); 
					$this->t->set_var('lang_action_entry',lang('Work hours'));
				}

				if ($this->boprojects->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $this->account)
				{
					$this->t->set_var('edit',$phpgw->link('/index.php','menuaction=projects.uiprojects.edit_project&project_id=' . $pro[$i]['project_id']));
					$this->t->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$this->t->set_var('view',$phpgw->link('/projects/view.php','id=' . $pro[$i]['project_id']));
				$this->t->set_var('lang_view_entry',lang('View'));

				$this->t->parse('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

// --------------- template declaration for Add Form --------------------------

			if ($this->cat_id && $this->cat_id != 0)
			{
				$cat = $this->cats->return_single($this->cat_id);
			}

			if ($cat[0]['app_name'] == 'phpgw' || !$this->cat_id)
			{
				$this->t->set_var('add','<form method="POST" action="' . $phpgw->link('/index.php','menuaction=projects.uiprojects.add_project&cat_id='
										. $this->cat_id) . '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
			}
			else
			{
				if ($this->boprojects->check_perms($this->grants[$cat[0]['owner']],PHPGW_ACL_ADD) || $cat[0]['owner'] == $this->account)
				{
					$this->t->set_var('add','<form method="POST" action="' . $phpgw->link('/index.php','menuaction=projects.uiprojects.add_project&cat_id='
											. $this->cat_id) . '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
				}
				else
				{
					$this->t->set_var('add','');
				}
			}

// ----------------------- end Add form declaration ----------------------------

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
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

			if ($nopref)
			{
				$this->t->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$currency = $this->boprojects->get_prefs();
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

			if ($nopref)
			{
				$this->t->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$currency = $this->boprojects->get_prefs();
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
		}

		function delete_project()
		{
			global $phpgw, $confirm, $project_id, $subs, $pro_parent, $action;

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'project_id'	=> $project_id,
				'action'		=> $action
			);

			if ($confirm)
			{
				if ($subs)
				{
					$this->boprojects->delete_project($project_id,True);
				}
				else
				{
					$this->boprojects->delete_projects($project_id);
				}

				Header('Location: ' . $phpgw->link('/index.php',$link_data));
			}

			$phpgw->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file(array('projects_delete' => 'delete.tpl'));
			$exists = $this->boprojects->exists('par', $num ='', $project_id);

			if ($exists)
			{
				$this->t->set_var('lang_subs',lang('Do you also want to delete all sub projects ?'));
				$this->t->set_var('subs','<input type="checkbox" name="subs" value="True">');
			}
			else
			{
				$this->t->set_var('lang_subs','');
				$this->t->set_var('subs', '');
			}

			$this->t->set_var('nolink',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
			$this->t->set_var('deleteheader',lang('Are you sure you want to delete this entry'));
			$this->t->set_var('lang_no',lang('No'));
			$this->t->set_var('lang_yes',lang('Yes'));

			$link_data['menuaction'] = 'projects.uiprojects.delete_project';
			$this->t->set_var('actionurl',$phpgw->link('/index.php',$link_data));

			$this->t->pfp('out','projects_delete');
		}

		function list_activities()
		{
			global $phpgw, $phpgw_info, $action;

			$this->display_app_header();

			$this->t->set_file(array('activities_list_t' => 'listactivities.tpl'));
			$this->t->set_block('activities_list_t','activities_list','list');

			$nopref = $this->boprojects->check_prefs();

			if ($nopref)
			{
				$this->t->set_var('pref_message',lang('Please set your preferences for this application !'));
			}
			else
			{
				$currency = $this->boprojects->get_prefs();
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act'
			);

			$this->t->set_var('lang_header',lang('Activities list'));
			$this->t->set_var('add_url',$phpgw->link('/projects/addactivity.php'));
			$this->t->set_var('project_url',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));

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

            $this->t->set_var('cat_action',$phpgw->link('/index.php',$link_data));
			$this->t->set_var('categories_list',$this->cats->formated_list('select','all',$this->cat_id,'True'));
            $this->t->set_var('search_action',$phpgw->link('/index.php',$link_data));
            $this->t->set_var('search_list',$this->nextmatchs->search(1));

// ----------------- list header variable template-declarations ---------------------------
  
			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
			$this->t->set_var('currency',$currency);
			$this->t->set_var('sort_num',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Activity ID')));
			$this->t->set_var('sort_descr',$this->nextmatchs->show_sort_order($this->sort,'descr',$this->order,'/index.php',lang('Description')));
			$this->t->set_var('sort_billperae',$this->nextmatchs->show_sort_order($this->sort,'billperae',$this->order,'/index.php',lang('Bill per workunit')));
			$this->t->set_var('sort_minperae',$this->nextmatchs->show_sort_order($this->sort,'minperae',$this->order,'/index.php',lang('Minutes per workunit')));

// ---------------------------- end header declaration -------------------------------------

            for ($i=0;$i<count($act);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$descr = $phpgw->strip_html($act[$i]['descr']);
				if (! $descr)
				{
					$descr  = '&nbsp;';
				}

// ------------------- template declaration for list records -------------------------
      
				$this->t->set_var(array('num'	=> $phpgw->strip_html($act[$i]['number']),
										'descr' => $descr,
									'billperae' => $act[$i]['billperae'],
									'minperae'	=> $act[$i]['minperae']));

				$this->t->set_var('edit',$phpgw->link('/projects/editactivity.php','id=' . $act[$i]['activity_id'] . "&sort=$this->sort&order=$this->order&query=$this->query&start=$start&cat_id=$this->cat_id"));
				$this->t->set_var('delete',$phpgw->link('/projects/deleteactivity.php','id=' . $act[$i]['activity_id'] . "&sort=$this->sort&order=$this->order&query=$this->query&start=$this->start&cat_id=$cat_id"));
				$this->t->set_var('lang_delete',lang('Delete'));
				$this->t->fp('list','activities_list',True);

// ------------------------------- end record declaration --------------------------------
			}

// ------------------------- template declaration for Add Form ---------------------------

			$this->t->set_var('lang_add',lang('Add'));
			$this->t->pfp('out','activities_list_t',True);
			$this->save_sessiondata($action);

// -------------------------------- end Add form declaration ------------------------------

//			$phpgw->common->phpgw_footer();
		}
	}
?>
