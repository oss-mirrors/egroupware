<?php
	/**************************************************************************\
	* phpGroupWare - Projects                                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	class uiprojects
	{
		var $db;
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
			'view_project'		=> True
		);

		function uiprojects()
		{
			global $phpgw;

			$this->boprojects				= CreateObject('projects.boprojects',True);
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

		function save_sessiondata()
		{
			$data = array
			(
				'start'		=> $this->start,
				'query'		=> $this->query,
				'filter'	=> $this->filter,
				'order'		=> $this->order,
				'cat_id'	=> $this->cat_id
			);
			$this->boprojects->save_sessiondata($data);
		}

		function set_app_langs()
		{
			global $phpgw, $phpgw_info;

			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
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
			$this->t->set_var('link_jobs',$phpgw->link('/projects/sub_projects.php'));
			$this->t->set_var('link_hours',$phpgw->link('/projects/hours_listhours.php'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('link_statistics',$phpgw->link('/projects/stats_projectlist.php'));
			$this->t->set_var('lang_statistics',lang("Statistics"));
			$this->t->set_var('link_delivery',$phpgw->link('/projects/del_index.php'));
			$this->t->set_var('lang_delivery',lang('Delivery'));
			$this->t->set_var('link_projects',$phpgw->link('/projects/index.php'));
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('link_archiv',$phpgw->link('/projects/archive.php'));
			$this->t->set_var('lang_archiv',lang('archive'));

			$this->t->fp('app_header','projects_header');

			$phpgw->common->phpgw_header();
			echo parse_navbar();
		}

		function list_projects()
		{
			global $phpgw, $phpgw_info;

			$this->display_app_header();

			$this->t->set_file(array('projects_list_t' => 'list.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			$this->t->set_var(lang_action,lang('Project list'));
			$this->t->set_var('lang_all',lang('All'));

			if (!$this->start)
			{
				$this->start = 0;
			}

			$pro = $this->boprojects->list_projects($this->start,True,$this->query,$this->filter,$this->sort,$this->order,'active',$this->cat_id,'mains');

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->boprojects->total_records,'&menuaction=projects.uiprojects.list_projects');
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->boprojects->total_records,'&menuaction=projects.uiprojects.list_projects');
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->boprojects->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			$this->t->set_var('cat_action',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
			$this->t->set_var('categories',$this->cats->formated_list('select','all',$this->cat_id,'True'));
			$this->t->set_var('filter_action',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
			$this->t->set_var('filter_list',$this->nextmatchs->filter(1,1));
			$this->t->set_var('search_action',$phpgw->link('/index.php','menuaction=projects.uiprojects.list_projects'));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

// ---------------- list header variable template-declarations --------------------------

			$this->t->set_var(sort_number,$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID','&menuaction=projects.list_projects')));
			$this->t->set_var(sort_customer,$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer')));
			$this->t->set_var(sort_status,$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status')));
			$this->t->set_var(sort_title,$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title')));
			$this->t->set_var(sort_end_date,$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due')));
			$this->t->set_var(sort_coordinator,$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator')));
			$this->t->set_var(lang_h_jobs,lang('Jobs'));
			$this->t->set_var(lang_edit,lang('Edit'));
			$this->t->set_var(lang_view,lang('View'));

// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$title = $phpgw->strip_html($pro[$i]['title']);
				if (! $title) $title = '&nbsp;';
				$end_date = $pro[$i]['end_date'];

				if ($end_date == 0)
				{
					$end_dateout = '&nbsp;';
				}
				else
				{
					$month  = $phpgw->common->show_date(time(),'n');
					$day    = $phpgw->common->show_date(time(),'d');
					$year   = $phpgw->common->show_date(time(),'Y');

					$end_date = $end_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
					$end_dateout = $phpgw->common->show_date($end_date,$phpgw_info['user']['preferences']['common']['dateformat']);
					if (mktime(2,0,0,$month,$day,$year) == $end_date) { $end_dateout = '<b>' . $end_dateout . '</b>'; }
					if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout = '<font color="CC0000"><b>' . $end_dateout . '</b></font>'; }
				}

				if ($pro[$i]['customer'] == 0) { $customerout = '&nbsp;'; }
				else
				{
					$customer = $this->boprojects->read_customer_data($pro[$i]['customer']);
            		if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            		else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
				}

				$cached_data = $this->boprojects->cached_accounts($pro[$i]['coordinator']);
				$coordinatorout = $phpgw->strip_html($cached_data[$pro[$i]['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro[$i]['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro[$i]['coordinator']]['lastname'] . '>');

// --------------- template declaration for list records -------------------------------------

				$this->t->set_var(array
				(
					'number'		=> $phpgw->strip_html($pro[$i]['number']),
					'customer'		=> $customerout,
					'status'		=> lang($pro[$i]['status']),
					'title'			=> $title,
					'end_date'		=> $end_dateout,
					'coordinator'	=> $coordinatorout
				));

				$this->t->set_var('jobs',$phpgw->link('/projects/sub_projects.php','pro_parent=' . $pro[$i]['id']));

				if ($this->boprojects->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $phpgw_info['user']['account_id'])
				{
					$this->t->set_var('edit',$phpgw->link('/projects/edit.php','id=' . $pro[$i]['id'] . '&cat_id=' . $cat_id));
					$this->t->set_var('lang_edit_entry',lang('Edit'));
				}
				else
				{
					$this->t->set_var('edit','');
					$this->t->set_var('lang_edit_entry','&nbsp;');
				}

				$this->t->set_var('view',$phpgw->link('/projects/view.php','id=' . $pro[$i]['id']));
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
				$this->t->set_var('add','<form method="POST" action="' . $phpgw->link('/projects/add.php','cat_id=' . $this->cat_id)
										. '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
			}
			else
			{
				if ($this->boprojects->check_perms($this->grants[$cat[0]['owner']],PHPGW_ACL_ADD) || $cat[0]['owner'] == $phpgw_info['user']['account_id'])
				{
					$this->t->set_var('add','<form method="POST" action="' . $phpgw->link('/projects/add.php','cat_id=' . $this->cat_id)
											. '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
				}
				else
				{
					$this->t->set_var('add','');
				}
			}

// ----------------------- end Add form declaration ----------------------------

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata();
			$phpgw->common->phpgw_footer();
		}
	}
?>
