<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000 - 2003 Bettina Gille                           *
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
		var $status;

		var $public_functions = array
		(
			'list_projects'		=> True,
			'edit_project'		=> True,
			'delete_pa'			=> True,
			'view_project'		=> True,
			'list_activities'	=> True,
			'edit_activity'		=> True,
			'list_admins'		=> True,
			'edit_admins'		=> True,
			'abook'				=> True,
			'preferences'		=> True,
			'archive'			=> True
		);

		function uiprojects()
		{
			$action = get_var('action',array('GET'));

			$this->boprojects				= CreateObject('projects.boprojects',True, $action);
			$this->nextmatchs				= CreateObject('phpgwapi.nextmatchs');
			$this->sbox						= CreateObject('phpgwapi.sbox');
			$this->cats						= CreateObject('phpgwapi.categories');
			$this->account					= $GLOBALS['phpgw_info']['user']['account_id'];
			$this->grants					= $GLOBALS['phpgw']->acl->get_grants('projects');
			$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

			$this->start					= $this->boprojects->start;
			$this->query					= $this->boprojects->query;
			$this->filter					= $this->boprojects->filter;
			$this->order					= $this->boprojects->order;
			$this->sort						= $this->boprojects->sort;
			$this->cat_id					= $this->boprojects->cat_id;
			$this->status					= $this->boprojects->status;
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
				'cat_id'	=> $this->cat_id,
				'status'	=> $this->status
			);
			$this->boprojects->save_sessiondata($data, $action);
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
			$GLOBALS['phpgw']->template->set_var('lang_act_number',lang('Activity ID'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));
			$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
			$GLOBALS['phpgw']->template->set_var('lang_reset',lang('Clear form'));
			$GLOBALS['phpgw']->template->set_var('lang_budget',lang('Budget'));
			$GLOBALS['phpgw']->template->set_var('lang_customer',lang('Customer'));
			$GLOBALS['phpgw']->template->set_var('lang_coordinator',lang('Coordinator'));
			$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
			$GLOBALS['phpgw']->template->set_var('lang_bookable_activities',lang('Bookable activities'));
			$GLOBALS['phpgw']->template->set_var('lang_billable_activities',lang('Billable activities'));
			$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
			$GLOBALS['phpgw']->template->set_var('lang_view',lang('View'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Work hours'));
			$GLOBALS['phpgw']->template->set_var('lang_remarkreq',lang('Remark required'));
			$GLOBALS['phpgw']->template->set_var('lang_select',lang('Select per button !'));
			$GLOBALS['phpgw']->template->set_var('lang_invoices',lang('Invoices'));
			$GLOBALS['phpgw']->template->set_var('lang_deliveries',lang('Deliveries'));
			$GLOBALS['phpgw']->template->set_var('lang_stats',lang('Statistics'));
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
				$GLOBALS['phpgw']->template->set_var('space1','&nbsp;&nbsp;&nbsp;');
				$GLOBALS['phpgw']->template->set_var('link_activities',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));                                                                                                         
				$GLOBALS['phpgw']->template->set_var('lang_activities',lang('Activities'));                                                                                                                               
			}

			if ($this->boprojects->isprojectadmin('pbo'))
			{
				$GLOBALS['phpgw']->template->set_var('book_info',lang('Bookkeeper'));
				$GLOBALS['phpgw']->template->set_var('break','&nbsp;|&nbsp;');
				$GLOBALS['phpgw']->template->set_var('space2','&nbsp;&nbsp;&nbsp;');
				$GLOBALS['phpgw']->template->set_var('link_billing',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains'));
				$GLOBALS['phpgw']->template->set_var('lang_billing',lang('Billing'));
				$GLOBALS['phpgw']->template->set_var('link_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains'));
				$GLOBALS['phpgw']->template->set_var('lang_delivery',lang('Deliveries'));
			}

			$GLOBALS['phpgw']->template->set_var('link_jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs'));
			$GLOBALS['phpgw']->template->set_var('link_hours',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours'));
			$GLOBALS['phpgw']->template->set_var('link_statistics',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains'));
			$GLOBALS['phpgw']->template->set_var('lang_statistics',lang("Statistics"));
			$GLOBALS['phpgw']->template->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$GLOBALS['phpgw']->template->set_var('lang_projects',lang('Projects'));
			$GLOBALS['phpgw']->template->set_var('link_archiv',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains'));
			$GLOBALS['phpgw']->template->set_var('lang_archiv',lang('archive'));

			$GLOBALS['phpgw']->template->fp('app_header','projects_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function status_format($status = '')
		{
			if (!$status)
			{
				$status = $this->status = 'active';
			}

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

		function list_projects()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_parent = get_var('pro_parent',array('POST','GET'));

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'list.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_parent?lang('list jobs'):lang('list projects'));

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

			if (! $this->status)
			{
				$this->status = 'active';
			}

			if (!$pro_parent)
			{
				$pro_parent = 0;
			}

			$pro = $this->boprojects->list_projects($this->start,True,$this->query,$this->filter,$this->sort,$this->order,$this->status,$this->cat_id,$action,$pro_parent);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			if ($action == 'mains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="none">' . lang('Select category') . '</option>' . "\n"
							. $this->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var(lang_header,lang('Project list'));
				$GLOBALS['phpgw']->template->set_var(lang_action,lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains', $status, $pro_parent) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_header',lang('Job list'));
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Work hours'));
			}

			$GLOBALS['phpgw']->template->set_var('action_list',$action_list);
			$GLOBALS['phpgw']->template->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('filter_list',$this->nextmatchs->new_filter($this->filter));
			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$GLOBALS['phpgw']->template->set_var('status_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('status_list',$this->status_format($this->status));

// ---------------- list header variable template-declarations --------------------------

			$GLOBALS['phpgw']->template->set_var(sort_number,$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));

			if ($action == 'mains')
			{
				$GLOBALS['phpgw']->template->set_var(sort_action,$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var(sort_action,$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			}

			$GLOBALS['phpgw']->template->set_var(sort_status,$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status'),$link_data));
			$GLOBALS['phpgw']->template->set_var(sort_title,$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$GLOBALS['phpgw']->template->set_var(sort_end_date,$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$GLOBALS['phpgw']->template->set_var(sort_coordinator,$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));


// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
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

				$GLOBALS['phpgw']->template->set_var(array
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
					$GLOBALS['phpgw']->template->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('edit','');
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry','&nbsp;');
				}

				$link_data['menuaction'] = 'projects.uiprojects.view_project';
				$GLOBALS['phpgw']->template->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->template->set_var('lang_view_entry',lang('View'));

				if ($action == 'mains')
				{
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&pro_parent='
										. $pro[$i]['project_id'] . '&action=subs'));
					$GLOBALS['phpgw']->template->set_var('lang_action_entry',lang('Jobs'));
				}
				else
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.list_hours';
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php',$link_data)); 
					$GLOBALS['phpgw']->template->set_var('lang_action_entry',lang('Work hours'));
				}

				$GLOBALS['phpgw']->template->parse('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

// --------------- template declaration for Add Form --------------------------

			$link_data['menuaction'] = 'projects.uiprojects.edit_project';
			unset($link_data['project_id']);

			if ($action == 'mains')
			{
				if ($this->cat_id && $this->cat_id != 0)
				{
					$cat = $this->cats->return_single($this->cat_id);
				}

				if ($cat[0]['app_name'] == 'phpgw' || $cat[0]['owner'] == '-1' || !$this->cat_id)
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
					$coordinator = $this->boprojects->return_value('co',$pro_parent);

					if ($this->boprojects->check_perms($this->grants[$coordinator],PHPGW_ACL_ADD) || $coordinator == $this->account)
					{
						$showadd = True;
					}
				}
			}

			if ($showadd)
			{
				$GLOBALS['phpgw']->template->set_var('add','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
											. '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
			}

// ----------------------- end Add form declaration ----------------------------

			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
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

		function edit_project()
		{
			$action				= get_var('action',array('POST','GET'));
			$pro_parent 		= get_var('pro_parent',array('POST','GET'));

			$abid 				= get_var('abid',array('POST'));

			$book_activities	= get_var('book_activities',array('POST'));
			$bill_activities	= get_var('bill_activities',array('POST'));

			$project_id			= get_var('project_id',array('POST','GET'));
			$name				= get_var('name',array('POST'));
			$values				= get_var('values',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			if ($values['submit'])
			{
				$this->cat_id = ($values['new_cat']?$values['new_cat']:'');

				$values['project_id']	= $project_id;
				$values['cat']			= $this->cat_id;
				$values['customer']		= $abid;
				$values['parent']		= $pro_parent;

				$error = $this->boprojects->check_values($action, $values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_project($action, $values, $book_activities, $bill_activities);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$form = ($project_id?'edit':'add');

			$GLOBALS['phpgw']->template->set_file(array('projects_' . $form => 'form.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_' . $form,'add','addhandle');
			$GLOBALS['phpgw']->template->set_block('projects_' . $form,'edit','edithandle');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));

			$link_data['menuaction'] = 'projects.uiprojects.edit_project';
			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if ($project_id)
			{
				$values = $this->boprojects->read_single_project($project_id);
				$GLOBALS['phpgw']->template->set_var('lang_choose','');
				$GLOBALS['phpgw']->template->set_var('choose','');
				$this->cat_id = $values['cat'];

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
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');

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
			}

			$GLOBALS['phpgw']->template->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
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

			$GLOBALS['phpgw']->template->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																							$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																							$this->sbox->getDays('values[eday]',$values['eday'])));

			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);
			$GLOBALS['phpgw']->template->set_var('number',$values['number']);
			$GLOBALS['phpgw']->template->set_var('title',$values['title']);
			$GLOBALS['phpgw']->template->set_var('descr',$values['descr']);

			$GLOBALS['phpgw']->template->set_var('status_list',$this->status_format($values['status']));

			$GLOBALS['phpgw']->template->set_var('coordinator_list',$this->coordinator_format($values['coordinator']));

			$GLOBALS['phpgw']->template->set_var('budget',$values['budget']);

			$GLOBALS['phpgw']->template->set_var('access','<input type="checkbox" name="values[access]" value="True"' . ($values['access'] == 'private'?' checked':'') . '>');

			if ($action == 'mains' || $action == 'amains')
			{
				$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($project_id?lang('edit project'):lang('add project'));

				$cat = '<select name="new_cat"><option value="">' . lang('None') . '</option>'
						.	$this->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';

				$GLOBALS['phpgw']->template->set_var('cat',$cat);
				$GLOBALS['phpgw']->template->set_var('lang_parent','');
				$GLOBALS['phpgw']->template->set_var('pro_parent','');
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Project ID'));
				$GLOBALS['phpgw']->template->set_var('lang_choose',($project_id?'':lang('generate project id ?')));

// ------------ activites bookable ----------------------

				$GLOBALS['phpgw']->template->set_var('book_activities_list',$this->boprojects->select_activities_list($project_id,False));

// -------------- activities billable ---------------------- 

    			$GLOBALS['phpgw']->template->set_var('bill_activities_list',$this->boprojects->select_activities_list($project_id,True));
			}
			else
			{
				if ($pro_parent && ($action == 'subs' || $action == 'asubs'))
				{
					$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($project_id?lang('edit job'):lang('add job'));
					$GLOBALS['phpgw']->template->set_var('lang_choose',($project_id?'':lang('generate job id ?')));
					$parent = $this->boprojects->read_single_project($pro_parent);

					$GLOBALS['phpgw']->template->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($parent['number']) . ' ' . $GLOBALS['phpgw']->strip_html($parent['title']));
					$GLOBALS['phpgw']->template->set_var('cat',$this->cats->id2name($parent['cat']));
					$this->cat_id = $parent['cat'];
					$GLOBALS['phpgw']->template->set_var('book_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, False));				
    				$GLOBALS['phpgw']->template->set_var('bill_activities_list',$this->boprojects->select_pro_activities($project_id, $pro_parent, True));
				}

				$GLOBALS['phpgw']->template->set_var('lang_parent',lang('Main project:'));
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Edit job'));
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Job ID'));
			}

			$abid = $values['customer'];
			$customer = $this->boprojects->read_single_contact($abid);
            if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

			$GLOBALS['phpgw']->template->set_var('name',$name);
			$GLOBALS['phpgw']->template->set_var('abid',$abid);

			$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
			$link_data['pa_id'] = $project_id;
			if ($this->boprojects->check_perms($this->grants[$values['coordinator']],PHPGW_ACL_DELETE) || $values['coordinator'] == $this->account)
			{
				$GLOBALS['phpgw']->template->set_var('delete','<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data)
											. '"><input type="submit" value="' . lang('Delete') .'"></form>');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('delete','&nbsp;');
			}

			$GLOBALS['phpgw']->template->set_var('edithandle','');
			$GLOBALS['phpgw']->template->set_var('addhandle','');

			$GLOBALS['phpgw']->template->pfp('out','projects_' . $form);
			$GLOBALS['phpgw']->template->pfp($form . 'handle',$form);
		}

		function view_project()
		{
			$action				= get_var('action',array('GET'));
			$pro_parent 		= get_var('pro_parent',array('GET'));
			$project_id			= get_var('project_id',array('GET'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('view' => 'view.tpl'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_parent?lang('view job'):lang('view project'));

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('done_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$values = $this->boprojects->read_single_project($project_id);

			$GLOBALS['phpgw']->template->set_var('cat',$this->cats->id2name($values['cat']));

// ------------ activites bookable ----------------------

			$boact = $this->boprojects->activities_list($project_id,False);
			if (is_array($boact))
			{
				while (list($null,$bo) = each($boact))
				{
					$boact_list .=	$bo['descr'] . ' [' . $bo['num'] . ']' . '<br>';
				}
			}

			$GLOBALS['phpgw']->template->set_var('book_activities_list',$boact_list);
// -------------- activities billable ---------------------- 

			$billact = $this->boprojects->activities_list($project_id,True);
			if (is_array($billact))
			{
				while (list($null,$bill) = each($billact))
				{
					$billact_list .=	$bill['descr'] . ' [' . $bill['num'] . ']' . "\n";
				}
			}

			$GLOBALS['phpgw']->template->set_var('bill_activities_list',$billact_list);

			if ($action == 'mains')
			{
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Project ID'));
				$GLOBALS['phpgw']->template->set_var('lang_parent','');
				$GLOBALS['phpgw']->template->set_var('pro_parent','');
			}
			else
			{
				if ($pro_parent && $action == 'subs')
				{
					$parent = $this->boprojects->read_single_project($pro_parent);

					$GLOBALS['phpgw']->template->set_var('pro_parent',$GLOBALS['phpgw']->strip_html($parent['number']) . ' ' . $GLOBALS['phpgw']->strip_html($parent['title']));
					$GLOBALS['phpgw']->template->set_var('cat',$this->cats->id2name($parent['cat']));
				}
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Job ID'));
				$GLOBALS['phpgw']->template->set_var('lang_parent',lang('Main project:'));
			}

			$GLOBALS['phpgw']->template->set_var('number',$GLOBALS['phpgw']->strip_html($values['number']));
			$title = $GLOBALS['phpgw']->strip_html($values['title']);
			if (! $title) $title = '&nbsp;';
			$GLOBALS['phpgw']->template->set_var('title',$title);
			$descr = $GLOBALS['phpgw']->strip_html($values['descr']);
			if (! $descr) $descr = '&nbsp;';
			$GLOBALS['phpgw']->template->set_var('descr',$descr);
			$GLOBALS['phpgw']->template->set_var('status',lang($values['status']));
			$GLOBALS['phpgw']->template->set_var('budget',$values['budget']);
			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

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

			$GLOBALS['phpgw']->template->set_var('sdate',$sdateout);

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

			$GLOBALS['phpgw']->template->set_var('edate',$edateout);

			$GLOBALS['phpgw']->template->set_var('coordinator',$GLOBALS['phpgw']->accounts->id2name($values['coordinator']));

// ----------------------------------- customer ------------------------------

			if ($values['customer'] != 0) 
			{
				$customer = $this->boprojects->read_single_contact($values['customer']);
            	if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            	else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
			}
			else { $customerout = '&nbsp;'; }

			$GLOBALS['phpgw']->template->set_var('customer',$customerout);

			$GLOBALS['phpgw']->template->pfp('out','view');
			$GLOBALS['phpgw']->hooks->process('projects_view');
		}

		function delete_pa()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_parent = get_var('pro_parent',array('POST','GET'));

			$subs		= get_var('subs',array('POST'));
			$pa_id		= get_var('pa_id',array('POST','GET'));

			$confirm	= get_var('confirm',array('POST'));

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
				$del = $pa_id;

				if ($subs)
				{
					$this->boprojects->delete_pa($action, $del, True);
				}
				else 
				{
					$this->boprojects->delete_pa($action, $del, False);
				}
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
			}

			$this->display_app_header();
			$GLOBALS['phpgw']->template->set_file(array('pa_delete' => 'delete.tpl'));

			$GLOBALS['phpgw']->template->set_var('lang_subs','');
			$GLOBALS['phpgw']->template->set_var('subs', '');

			$GLOBALS['phpgw']->template->set_var('nolink',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('deleteheader',lang('Are you sure you want to delete this entry ?'));
			$GLOBALS['phpgw']->template->set_var('lang_no',lang('No'));
			$GLOBALS['phpgw']->template->set_var('lang_yes',lang('Yes'));

			if ($action != 'act')
			{
				$exists = $this->boprojects->exists('mains', 'par', $num ='', $pa_id);

				if ($exists)
				{
					$GLOBALS['phpgw']->template->set_var('lang_subs',lang('Do you also want to delete all sub projects ?'));
					$GLOBALS['phpgw']->template->set_var('subs','<input type="checkbox" name="subs" value="True">');
				}
			}

			$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->pfp('out','pa_delete');
		}

		function list_activities()
		{
			$action = get_var('action',array('POST','GET'));

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('activities_list_t' => 'listactivities.tpl'));
			$GLOBALS['phpgw']->template->set_block('activities_list_t','activities_list','list');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act'
			);

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('list activities');

			if (!$this->start)
			{
				$this->start = 0;
			}

			$act = $this->boprojects->list_activities($this->start, True, $this->query, $this->sort, $this->order, $this->cat_id);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

            $GLOBALS['phpgw']->template->set_var('cat_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('categories_list',$this->cats->formatted_list('select','all',$this->cat_id,'True'));
            $GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
            $GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));

			switch($prefs['bill'])
			{
				case 'wu':	$bill = lang('Bill per workunit'); break;
				case 'h':	$bill = lang('Bill per hour'); break;
				default :	$bill = lang('Bill per hour'); break;
			}

// ----------------- list header variable template-declarations ---------------------------
  
			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);
			$GLOBALS['phpgw']->template->set_var('sort_num',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Activity ID')));
			$GLOBALS['phpgw']->template->set_var('sort_descr',$this->nextmatchs->show_sort_order($this->sort,'descr',$this->order,'/index.php',lang('Description')));
			$GLOBALS['phpgw']->template->set_var('sort_billperae',$this->nextmatchs->show_sort_order($this->sort,'billperae',$this->order,'/index.php',$bill));

			if ($prefs['bill'] == 'wu')
			{
				$GLOBALS['phpgw']->template->set_var('sort_minperae','<td width="10%" align="right">' . $this->nextmatchs->show_sort_order($this->sort,'minperae',
									$this->order,'/index.php',lang('Minutes per workunit') . '</td>'));
			}

// ---------------------------- end header declaration -------------------------------------

            for ($i=0;$i<count($act);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
				$descr = $GLOBALS['phpgw']->strip_html($act[$i]['descr']);
				if (! $descr)
				{
					$descr  = '&nbsp;';
				}

// ------------------- template declaration for list records -------------------------
      
				$GLOBALS['phpgw']->template->set_var(array('num'	=> $GLOBALS['phpgw']->strip_html($act[$i]['number']),
										'descr' => $descr,
									'billperae' => $act[$i]['billperae']));

				if ($prefs['bill'] == 'wu')
				{
					$GLOBALS['phpgw']->template->set_var('minperae','<td align="right">' . $act[$i]['minperae'] . '</td>');
				}

				$link_data['menuaction']	= 'projects.uiprojects.edit_activity';
				$link_data['activity_id']	= $act[$i]['activity_id'];
				$GLOBALS['phpgw']->template->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$link_data['menuaction']	= 'projects.uiprojects.delete_pa';
				$link_data['pa_id']	= $act[$i]['activity_id'];
				$GLOBALS['phpgw']->template->set_var('delete',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$GLOBALS['phpgw']->template->fp('list','activities_list',True);

// ------------------------------- end record declaration --------------------------------

			}

// ------------------------- template declaration for Add Form ---------------------------

			$link_data['menuaction'] = 'projects.uiprojects.edit_activity';
			unset($link_data['activity_id']);
			$GLOBALS['phpgw']->template->set_var('add_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));
			$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));
			$GLOBALS['phpgw']->template->pfp('out','activities_list_t',True);
			$this->save_sessiondata($action);

// -------------------------------- end Add form declaration ------------------------------

		}

		function edit_activity()
		{
			$activity_id	= get_var('activity_id',array('POST','GET'));
			$values			= get_var('values',array('POST'));

			if ($values['new_cat'])
			{
				$this->cat_id = $values['new_cat'];
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act'
			);

			if ($values['submit'])
			{
				$this->cat_id			= ($values['new_cat']?$values['new_cat']:'');
				$values['cat']			= $this->cat_id;
				$values['activity_id']	= $activity_id;

				$error = $this->boprojects->check_pa_values($values);
				if (is_array($error))
				{
					$GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->boprojects->save_activity($values);
					Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				}
			}

			$this->display_app_header();

			$form = ($activity_id?'edit':'add');

			$GLOBALS['phpgw']->template->set_file(array('activity_' . $form => 'formactivity.tpl'));
			$GLOBALS['phpgw']->template->set_block('activity_' . $form,'add','addhandle');
			$GLOBALS['phpgw']->template->set_block('activity_' . $form,'edit','edithandle');

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($activity_id?lang('edit activity'):lang('add activity'));
			$GLOBALS['phpgw']->template->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.edit_activity&activity_id=' . $activity_id));

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

			if ($activity_id)
			{
				$values = $this->boprojects->read_single_activity($activity_id);
				$this->cat_id = $values['cat'];
				$GLOBALS['phpgw']->template->set_var('lang_choose','');
				$GLOBALS['phpgw']->template->set_var('choose','');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('lang_choose',lang('Generate Activity ID ?'));
				$GLOBALS['phpgw']->template->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');
			}

			$GLOBALS['phpgw']->template->set_var('cats_list',$this->cats->formatted_list('select','all',$this->cat_id,True));
			$GLOBALS['phpgw']->template->set_var('num',$GLOBALS['phpgw']->strip_html($values['number']));
			$descr  = $GLOBALS['phpgw']->strip_html($values['descr']);
			if (! $descr) $descr = '&nbsp;';
			$GLOBALS['phpgw']->template->set_var('descr',$descr);

			if ($values['remarkreq'] == 'N'):
				$stat_sel[0]=' selected';
			elseif ($values['remarkreq'] == 'Y'):
				$stat_sel[1]=' selected';
			endif;

			$remarkreq_list = '<option value="N"' . $stat_sel[0] . '>' . lang('No') . '</option>' . "\n"
					. '<option value="Y"' . $stat_sel[1] . '>' . lang('Yes') . '</option>' . "\n";

			$GLOBALS['phpgw']->template->set_var('remarkreq_list',$remarkreq_list);

			if ($prefs['bill'] == 'wu')
			{
    			$GLOBALS['phpgw']->template->set_var('lang_billperae',lang('Bill per workunit'));
				$GLOBALS['phpgw']->template->set_var('lang_minperae',lang('Minutes per workunit'));
				$GLOBALS['phpgw']->template->set_var('minperae','<input type="text" name="values[minperae]" value="' . $values['minperae'] . '">');
			}
			else
			{
    			$GLOBALS['phpgw']->template->set_var('lang_billperae',lang('Bill per hour'));
			}

			$GLOBALS['phpgw']->template->set_var('billperae',$values['billperae']);

			$link_data['menuaction']	= 'projects.uiprojects.delete_pa';
			$link_data['pa_id']	= $values[$i]['activity_id'];
			$GLOBALS['phpgw']->template->set_var('deleteurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));

			$GLOBALS['phpgw']->template->set_var('edithandle','');
			$GLOBALS['phpgw']->template->set_var('addhandle','');

			$GLOBALS['phpgw']->template->pfp('out','activity_' . $form);
			$GLOBALS['phpgw']->template->pfp($form . 'handle',$form);
		}

		function list_admins()
		{
			$action = get_var('action',array('POST','GET'));

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_admins',
				'action'		=> $action
			);

			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_file(array('admin_list_t' => 'list_admin.tpl'));
			$GLOBALS['phpgw']->template->set_block('admin_list_t','admin_list','list');

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'pad')?lang('administration'):lang('accountancy'));

			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$GLOBALS['phpgw']->template->set_var('doneurl',$GLOBALS['phpgw']->link('/admin/index.php'));

			if (!$this->start)
			{
				$this->start = 0;
			}

			$admins = $this->boprojects->list_admins($action, 'both', $this->start, $this->query, $this->sort, $this->order);

//--------------------------------- nextmatch --------------------------------------------
 
			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

    		$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));
 
// ------------------------------ end nextmatch ------------------------------------------
 
//------------------- list header variable template-declarations -------------------------

			$GLOBALS['phpgw']->template->set_var('sort_lid',$this->nextmatchs->show_sort_order($this->sort,'account_lid',$this->order,'/index.php',lang('Username / Group'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_lastname',$this->nextmatchs->show_sort_order($this->sort,'account_lastname',$this->order,'/index.php',lang('Lastname'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_firstname',$this->nextmatchs->show_sort_order($this->sort,'account_firstname',$this->order,'/index.php',lang('Firstname'),$link_data));

// -------------------------- end header declaration --------------------------------------

			for ($i=0;$i<count($admins);$i++)
			{
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
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

				$GLOBALS['phpgw']->template->set_var(array('lid' => $lid,
							'firstname' => $firstname,
							'lastname' => $lastname));

				$GLOBALS['phpgw']->template->fp('list','admin_list',True);
			}
			$link_data['menuaction'] = 'projects.uiprojects.edit_admins';
			$GLOBALS['phpgw']->template->set_var('addurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->pfp('out','admin_list_t',True);
			$this->save_sessiondata($action);
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function edit_admins()
		{
			$action = get_var('action',array('POST','GET'));
			$submit = get_var('submit',array('POST'));
			$users	= get_var('users',array('POST'));
			$groups = get_var('groups',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.edit_admins',
				'action'		=> $action
			);

			if ($submit)
			{
				$this->boprojects->edit_admins($action, $users, $groups);
				$link_data['menuaction'] = 'projects.uiprojects.list_admins';
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_file(array('admin_add' => 'form_admin.tpl'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'pad')?lang('edit administrator list'):lang('edit bookkeeper list'));

			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('users_list',$this->boprojects->selected_admins($action,'aa'));
			$GLOBALS['phpgw']->template->set_var('groups_list',$this->boprojects->selected_admins($action,'ag'));
			$GLOBALS['phpgw']->template->set_var('lang_users_list',lang('Select users'));
			$GLOBALS['phpgw']->template->set_var('lang_groups_list',lang('Select groups'));

			$GLOBALS['phpgw']->template->pfp('out','admin_add');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function abook()
		{
			$start		= get_var('start',array('POST'));
			$cat_id 	= get_var('cat_id',array('POST'));
			$sort		= get_var('sort',array('POST'));
			$order		= get_var('order',array('POST'));
			$filter		= get_var('filter',array('POST'));
			$qfilter	= get_var('qfilter',array('POST'));
			$query		= get_var('query',array('POST'));

			$GLOBALS['phpgw']->template->set_file(array('abook_list_t' => 'addressbook.tpl'));
			$GLOBALS['phpgw']->template->set_block('abook_list_t','abook_list','list');

			$this->cats->app_name = 'addressbook';

			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_var('title',$GLOBALS['phpgw_info']['site_title']);
			$GLOBALS['phpgw']->template->set_var('lang_action',lang('Address book'));
			$GLOBALS['phpgw']->template->set_var('charset',$GLOBALS['phpgw']->translation->translate('charset'));
			$GLOBALS['phpgw']->template->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.abook',
				'start'			=> $start,
				'sort'			=> $sort,
				'order'			=> $order,
				'cat_id'		=> $cat_id,
				'filter'		=> $filter,
				'query'			=> $query
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
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$start));

// -------------------------- end nextmatch ------------------------------------

			$GLOBALS['phpgw']->template->set_var('cats_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('cats_list',$this->cats->formatted_list('select','all',$cat_id,True));
			$GLOBALS['phpgw']->template->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('filter_list',$this->nextmatchs->new_filter($filter));
			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $query)));

// ---------------- list header variable template-declarations --------------------------

// -------------- list header variable template-declaration ------------------------

			$GLOBALS['phpgw']->template->set_var('sort_company',$this->nextmatchs->show_sort_order($sort,'org_name',$order,'/index.php',lang('Company'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_firstname',$this->nextmatchs->show_sort_order($sort,'n_given',$order,'/index.php',lang('Firstname'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_lastname',$this->nextmatchs->show_sort_order($sort,'n_family',$order,'/index.php',lang('Lastname'),$link_data));
			$GLOBALS['phpgw']->template->set_var('lang_select',lang('Select'));

// ------------------------- end header declaration --------------------------------

			for ($i=0;$i<count($entries);$i++)
			{
				$GLOBALS['phpgw']->template->set_var('tr_color',$this->nextmatchs->alternate_row_color($tr_color));
				$firstname = $entries[$i]['n_given'];
				if (!$firstname) { $firstname = '&nbsp;'; }
				$lastname = $entries[$i]['n_family'];
				if (!$lastname) { $lastname = '&nbsp;'; }
				$company = $entries[$i]['org_name'];
				if (!$company) { $company = '&nbsp;'; }

// ---------------- template declaration for list records -------------------------- 

				$GLOBALS['phpgw']->template->set_var(array('company' 	=> $company,
									'firstname' 	=> $firstname,
									'lastname'		=> $lastname,
									'abid'			=> $entries[$i]['id']));

				$GLOBALS['phpgw']->template->parse('list','abook_list',True);
			}

			$GLOBALS['phpgw']->template->parse('out','abook_list_t',True);
			$GLOBALS['phpgw']->template->p('out');

			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function preferences()
		{
			$submit		= get_var('submit',array('POST'));
			$prefs		= get_var('prefs',array('POST'));
			$abid		= get_var('abid',array('POST'));
			$oldbill	= get_var('oldbill',array('POST'));

			if ($submit)
			{
				$prefs['abid']		= $abid;
				$prefs['oldbill']	= $oldbill;
				$obill = $this->boprojects->save_prefs($prefs);

				if ($obill == False)
				{
					Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
					$GLOBALS['phpgw']->common->phpgw_exit();
				}
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			if ($this->boprojects->isprojectadmin('pbo') || $this->boprojects->isprojectadmin('pad'))
			{
				$GLOBALS['phpgw']->template->set_file(array('book_prefs' => 'preferences.tpl'));
				$GLOBALS['phpgw']->template->set_block('book_prefs','book','bookhandle');
				$GLOBALS['phpgw']->template->set_block('book_prefs','no','nohandle');

				$this->set_app_langs();

				$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('preferences for accountancy');

				if ($obill == True)
				{
					$GLOBALS['phpgw']->template->set_var('bill_message',lang('Please set the minutes per workunit for each activity now !'));
				}

				$GLOBALS['phpgw']->template->set_var('lang_layout',lang('Invoice layout'));
				$GLOBALS['phpgw']->template->set_var('lang_select_font',lang('Select font'));
				$GLOBALS['phpgw']->template->set_var('lang_select_mysize',lang('Select font size for own address'));
				$GLOBALS['phpgw']->template->set_var('lang_select_allsize',lang('Select font size for customer address'));
				$GLOBALS['phpgw']->template->set_var('lang_bill',lang('Invoicing of work time'));
				$GLOBALS['phpgw']->template->set_var('lang_select_tax',lang('Select tax for work time'));
				$GLOBALS['phpgw']->template->set_var('lang_address',lang('Select own address'));

				$prefs = $this->boprojects->read_prefs();

				$oldbill = $prefs['bill'];

				$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.preferences&oldbill=' . $oldbill));
				$GLOBALS['phpgw']->template->set_var('doneurl',$GLOBALS['phpgw']->link('/preferences/index.php'));
				$GLOBALS['phpgw']->template->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));

				$GLOBALS['phpgw']->template->set_var('tax',$prefs['tax']);

				$bill = '<input type="radio" name="prefs[bill]" value="wu"' . ($prefs['bill'] == 'wu'?' checked':'') . '>'
							. lang('per workunit') . '<br>';
				$bill .= '<input type="radio" name="prefs[bill]" value="h"' . ($prefs['bill'] == 'h'?' checked':'') . '>'
							. lang('exactly accounting') . '&nbsp;[hh:mm]';

				$GLOBALS['phpgw']->template->set_var('bill',$bill);

				switch($prefs['ifont'])
				{
					case'Arial,Helvetica,sans-serif': $font_sel[0]=' selected'; break;
					case'Times New Roman,Times,serif': $font_sel[1]=' selected'; break;
					case'Verdana,Arial,Helvetica,sans-serif': $font_sel[2]=' selected'; break; 
					case'Georgia,Times New Roman,Times,serif': $font_sel[3]=' selected'; break;
					case'Courier New,Courier,mono': $font_sel[4]=' selected'; break;
					case'Helvetica,Arial,sans-serif': $font_sel[5]=' selected'; break; 
					case'Tahoma,Verdana,Arial,Helvetica,sans-serif': $font_sel[6]=' selected'; break;
				}

				$ifont = '<option value="Arial,Helvetica,sans-serif"' . $font_sel[0] . '>' . lang('Arial') . '</option>' . "\n"
					. '<option value="Times New Roman,Times,serif"' . $font_sel[1] . '>' . lang('Times New Roman') . '</option>' . "\n"
					. '<option value="Verdana,Arial,Helvetica,sans-serif"' . $font_sel[2] . '>' . lang('Verdana') . '</option>' . "\n"
					. '<option value="Georgia,Times New Roman,Times,serif"' . $font_sel[3] . '>' . lang('Georgia') . '</option>' . "\n"
					. '<option value="Courier New,Courier,mono"' . $font_sel[4] . '>' . lang('Courier New') . '</option>' . "\n"
					. '<option value="Helvetica,Arial,sans-serif"' . $font_sel[5] . '>' . lang('Helvetica') . '</option>' . "\n"
					. '<option value="Tahoma,Verdana,Arial,Helvetica,sans-serif"' . $font_sel[6] . '>' . lang('Tahoma') . '</option>' . "\n";

				$GLOBALS['phpgw']->template->set_var('ifont',$ifont);

				switch($prefs['mysize'])
				{
					case 1: $my_sel[0]=' selected'; break;
					case 2: $my_sel[1]=' selected'; break;
					case 3: $my_sel[2]=' selected'; break;
					case 4: $my_sel[3]=' selected'; break;
					case 5: $my_sel[4]=' selected'; break;
				}

				$mysize = '<option value="1"' . $my_sel[0] . '>' . lang('Very Small') . '</option>' . "\n"
						. '<option value="2"' . $my_sel[1] . '>' . lang('Small') . '</option>' . "\n"
						. '<option value="3"' . $my_sel[2] . '>' . lang('Medium') . '</option>' . "\n"
						. '<option value="4"' . $my_sel[3] . '>' . lang('Large') . '</option>' . "\n"
						. '<option value="5"' . $my_sel[4] . '>' . lang('Very Large') . '</option>' . "\n";

				$GLOBALS['phpgw']->template->set_var('mysize',$mysize);

				switch($prefs['allsize'])
				{
					case 1: $all_sel[0]=' selected'; break;
					case 2: $all_sel[1]=' selected'; break;
					case 3: $all_sel[2]=' selected'; break;
					case 4: $all_sel[3]=' selected'; break;
					case 5: $all_sel[4]=' selected'; break;
				}

				$allsize = '<option value="1"' . $all_sel[0] . '>' . lang('Very Small') . '</option>' . "\n"
						. '<option value="2"' . $all_sel[1] . '>' . lang('Small') . '</option>' . "\n"
						. '<option value="3"' . $all_sel[2] . '>' . lang('Medium') . '</option>' . "\n"
						. '<option value="4"' . $all_sel[3] . '>' . lang('Large') . '</option>' . "\n"
						. '<option value="5"' . $all_sel[4] . '>' . lang('Very Large') . '</option>' . "\n";

				$GLOBALS['phpgw']->template->set_var('allsize',$allsize);

				if (isset($prefs['abid']))
				{
					$abid = $prefs['abid'];

					$entry = $this->boprojects->read_single_contact($abid);

					if ($entry[0]['org_name'] == '') { $GLOBALS['phpgw']->template->set_var('name',$entry[0]['n_given'] . ' ' . $entry[0]['n_family']); }
					else { $GLOBALS['phpgw']->template->set_var('name',$entry[0]['org_name'] . ' [ ' . $entry[0]['n_given'] . ' ' . $entry[0]['n_family'] . ' ]'); }
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('name',$name);
				}

				$GLOBALS['phpgw']->template->set_var('abid',$abid);

				$GLOBALS['phpgw']->template->set_var('bookhandle','');
				$GLOBALS['phpgw']->template->set_var('nohandle','');
				$GLOBALS['phpgw']->template->pfp('out','book_prefs');
				$GLOBALS['phpgw']->template->pfp('bookhandle','book');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_file(array('no_prefs' => 'preferences.tpl'));
				$GLOBALS['phpgw']->template->set_block('no_prefs','book','bookhandle');
				$GLOBALS['phpgw']->template->set_block('no_prefs','no','nohandle');

				$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/preferences/index.php'));
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Project preferences'));
				$GLOBALS['phpgw']->template->set_var('lang_no_prefs',lang('Sorry, no preferences to set for you :)'));
				$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
				$GLOBALS['phpgw']->template->set_var('bookhandle','');
				$GLOBALS['phpgw']->template->set_var('nohandle','');
				$GLOBALS['phpgw']->template->pfp('out','no_prefs');
				$GLOBALS['phpgw']->template->pfp('nohandle','no');
			}
		}

		function archive()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_parent	= get_var('pro_parent',array('POST','GET'));

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'archive.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');

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
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------------ end nextmatch template ------------------------------------

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'amains')?lang('project archive'):lang('job archive'));
			if ($action == 'amains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="">' . lang('None') . '</option>' . "\n"
							. $this->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains', 'archive', $pro_parent) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Work hours'));
			}

			$GLOBALS['phpgw']->template->set_var('action_list',$action_list);
			$GLOBALS['phpgw']->template->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('filter_list',$this->nextmatchs->new_filter($this->filter));
			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));

// ---------------- list header variable template-declarations --------------------------

			$GLOBALS['phpgw']->template->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));

			if ($action == 'amains')
			{
				$GLOBALS['phpgw']->template->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			}

			$GLOBALS['phpgw']->template->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('title'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('End date'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));

// ----------------------------- end header declaration ----------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
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

				$GLOBALS['phpgw']->template->set_var(array
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
					$GLOBALS['phpgw']->template->set_var('edit',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('edit','');
					$GLOBALS['phpgw']->template->set_var('lang_edit_entry','&nbsp;');
				}

				if ($action == 'amains')
				{
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&pro_parent='
										. $pro[$i]['project_id'] . '&action=asubs'));
					$GLOBALS['phpgw']->template->set_var('lang_action_entry',lang('Jobs'));
				}
				else
				{
					$link_data['menuaction'] = 'projects.uiprojecthours.list_hours';
					$link_data['action'] = 'asubs';
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php',$link_data)); 
					$GLOBALS['phpgw']->template->set_var('lang_action_entry',lang('Work hours'));
				}

				if ($this->boprojects->isprojectadmin('pbo'))
				{
					$GLOBALS['phpgw']->template->set_var('delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_deliveries&project_id='
																	. $pro[$i]['project_id'] . '&action=' . $action));
					$GLOBALS['phpgw']->template->set_var('invoice',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_invoices&project_id='
																	. $pro[$i]['project_id'] . '&action=' . $action));
					$GLOBALS['phpgw']->template->set_var('lang_invoice_entry',lang('Invoices'));
					$GLOBALS['phpgw']->template->set_var('lang_delivery_entry',lang('Deliveries'));
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('lang_invoice_entry','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('lang_delivery_entry','&nbsp;');
				}

				$GLOBALS['phpgw']->template->set_var('stats',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.project_stat&project_id='
																	. $pro[$i]['project_id']));
				$GLOBALS['phpgw']->template->set_var('lang_stats_entry',lang('Statistics'));

				$GLOBALS['phpgw']->template->fp('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}
	}
?>
