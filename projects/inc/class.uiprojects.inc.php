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
			'hook_sidebox_menu'	=> True,
			'list_projects'			=> True,
			'list_projects_home'	=> True,
			'edit_project'			=> True,
			'delete_pa'				=> True,
			'view_project'			=> True,
			'list_activities'		=> True,
			'edit_activity'			=> True,
			'list_admins'			=> True,
			'edit_admins'			=> True,
			'abook'					=> True,
			'preferences'			=> True,
			'archive'				=> True,
			'accounts_popup'		=> True,
			'e_accounts_popup'		=> True,
			'list_budget'			=> True,
			'view_pcosts'			=> True,
			'edit_mstone'			=> True
		);

		function uiprojects()
		{
			$action = get_var('action',array('GET'));

			$this->bo						= CreateObject('projects.boprojects',True, $action);
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
			$this->cat_id					= $this->bo->cat_id;
			$this->status					= $this->bo->status;
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
			$this->bo->save_sessiondata($data, $action);
		}

		function set_app_langs()
		{
			$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$GLOBALS['phpgw']->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
			$GLOBALS['phpgw']->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);

			$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
			$GLOBALS['phpgw']->template->set_var('lang_select',lang('Select'));
			$GLOBALS['phpgw']->template->set_var('lang_select_category',lang('Select category'));

			$GLOBALS['phpgw']->template->set_var('lang_descr',lang('Description'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_none',lang('None'));

			$GLOBALS['phpgw']->template->set_var('lang_start_date',lang('Start Date'));
			$GLOBALS['phpgw']->template->set_var('lang_end_date',lang('End Date'));
			$GLOBALS['phpgw']->template->set_var('lang_cdate',lang('Date created'));
			$GLOBALS['phpgw']->template->set_var('lang_last_update',lang('last update'));

			$GLOBALS['phpgw']->template->set_var('lang_date_due',lang('Date due'));
			$GLOBALS['phpgw']->template->set_var('lang_access',lang('access'));
			$GLOBALS['phpgw']->template->set_var('lang_projects',lang('Projects'));
			$GLOBALS['phpgw']->template->set_var('lang_jobs',lang('Jobs'));
			$GLOBALS['phpgw']->template->set_var('lang_act_number',lang('Activity ID'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));
			$GLOBALS['phpgw']->template->set_var('lang_budget',lang('Budget'));
			$GLOBALS['phpgw']->template->set_var('lang_pcosts',lang('planned costs'));

			$GLOBALS['phpgw']->template->set_var('lang_investment_nr',lang('investment nr'));
			$GLOBALS['phpgw']->template->set_var('lang_customer',lang('Customer'));
			$GLOBALS['phpgw']->template->set_var('lang_coordinator',lang('Coordinator'));
			$GLOBALS['phpgw']->template->set_var('lang_employees',lang('Employees'));
			$GLOBALS['phpgw']->template->set_var('lang_creator',lang('creator'));
			$GLOBALS['phpgw']->template->set_var('lang_processor',lang('processor'));
			$GLOBALS['phpgw']->template->set_var('lang_previous',lang('previous project'));
			$GLOBALS['phpgw']->template->set_var('lang_bookable_activities',lang('Bookable activities'));
			$GLOBALS['phpgw']->template->set_var('lang_billable_activities',lang('Billable activities'));
			$GLOBALS['phpgw']->template->set_var('lang_edit',lang('edit'));
			$GLOBALS['phpgw']->template->set_var('lang_view',lang('View'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Work hours'));
			$GLOBALS['phpgw']->template->set_var('lang_remarkreq',lang('Remark required'));

			$GLOBALS['phpgw']->template->set_var('lang_invoices',lang('Invoices'));
			$GLOBALS['phpgw']->template->set_var('lang_deliveries',lang('Deliveries'));
			$GLOBALS['phpgw']->template->set_var('lang_stats',lang('Statistics'));
			$GLOBALS['phpgw']->template->set_var('lang_ptime',lang('time planned'));
			$GLOBALS['phpgw']->template->set_var('lang_utime',lang('time used'));
			$GLOBALS['phpgw']->template->set_var('lang_month',lang('month'));

			$GLOBALS['phpgw']->template->set_var('lang_done',lang('done'));
			$GLOBALS['phpgw']->template->set_var('lang_save',lang('save'));
			$GLOBALS['phpgw']->template->set_var('lang_apply',lang('apply'));
			$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('cancel'));
			$GLOBALS['phpgw']->template->set_var('lang_search',lang('search'));

			$GLOBALS['phpgw']->template->set_var('lang_parent',lang('Parent project'));
			$GLOBALS['phpgw']->template->set_var('lang_main',lang('Main project'));

			$GLOBALS['phpgw']->template->set_var('lang_add_milestone',lang('add milestone'));
			$GLOBALS['phpgw']->template->set_var('lang_milestones',lang('milestones'));
		}

		function display_app_header()
		{
			$this->set_app_langs();

			if ($GLOBALS['phpgw_info']['user']['preferences']['common']['template_set'] != 'idots')
			{
				$GLOBALS['phpgw']->template->set_file(array('header' => 'header.tpl'));
				$GLOBALS['phpgw']->template->set_block('header','projects_header');

				if ($this->bo->isprojectadmin('pad'))
				{
					$GLOBALS['phpgw']->template->set_var('admin_info',lang('Administrator'));
					$GLOBALS['phpgw']->template->set_var('break1','&nbsp;|&nbsp;');
					$GLOBALS['phpgw']->template->set_var('space1','&nbsp;&nbsp;&nbsp;');
					$GLOBALS['phpgw']->template->set_var('link_activities',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act'));
					$GLOBALS['phpgw']->template->set_var('lang_activities',lang('Activities'));
					$GLOBALS['phpgw']->template->set_var('link_budget',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_budget&action=mains'));
					$GLOBALS['phpgw']->template->set_var('lang_budget',lang('budget'));
				}

				if ($this->bo->isprojectadmin('pbo'))
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
			}
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function hook_sidebox_menu()
		{
			$appname = 'projects';
			$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
			$file = array();
			if ($this->bo->isprojectadmin('pad'))
			{
				$file['Activities'] = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act');
			}
			$file['Projects']       = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains');

			if ($this->bo->isprojectadmin('pbo'))
			{
				$file['Billing']    = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains');
				$file['Deliveries'] = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains');
			}
			$file['Jobs']           = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs');
			$file['Work hours']          = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours');

			if ($this->bo->isprojectadmin('pad'))
			{
				$file['Budget']     = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_budget&action=mains');
			}
			$file['Statistics']     = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains');
			$file['Archive']        = $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains');

			display_sidebox($appname,$menu_title,$file);

			if ($GLOBALS['phpgw_info']['user']['apps']['preferences'])
			{
				$menu_title = lang('Preferences');
				$file = Array(
					'Preferences'     => $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.preferences'),
					'Grant Access'    => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
					'Edit categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=projects&cats_level=True&global_cats=True')
				);
				display_sidebox($appname,$menu_title,$file);
			}

			if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				$menu_title = lang('Administration');
				$file = Array(
					'Administration'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pad'),
					'Accountancy'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pbo'),
					'Global Categories'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname)
				);
				display_sidebox($appname,$menu_title,$file);
			}
		}

		function status_format($status = '', $showarchive = False)
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
						. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n";

			if ($showarchive)
			{
				$status_list .= '<option value="archive"' . $stat_sel[2] . '>' . lang('Archive') . '</option>' . "\n";
			}
			return $status_list;
		}

		function list_projects()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_main	= get_var('pro_main',array('POST','GET'));

			if ($_GET['cat_id'])
			{
				$this->cat_id = $_GET['cat_id'];
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_main?lang('list jobs'):lang('list projects'));

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'list.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_main'		=> $pro_main,
				'action'		=> $action
			);

			if (! $this->status)
			{
				$this->status = 'active';
			}

			$pro = $this->bo->list_projects(array('type' => $action,'parent' => $pro_main));

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			if ($action == 'mains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="none">' . lang('Select category') . '</option>' . "\n"
							. $this->bo->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_main" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->bo->select_project_list(array('status' => $status, 'selected' => $pro_main)) . '</select>';
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

			$GLOBALS['phpgw']->template->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));

			/*if ($action == 'mains')
			{
				$GLOBALS['phpgw']->template->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			}*/

			$GLOBALS['phpgw']->template->set_var('lang_milestones',lang('milestones'));
			$GLOBALS['phpgw']->template->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));


// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
				$edateout = $this->bo->formatted_edate($pro[$i]['edate']);

				if ($action == 'mains')
				{
					$td_action = ($pro[$i]['customerout']?$pro[$i]['customerout']:'&nbsp;');
				}
				else
				{
					$td_action = ($pro[$i]['sdateout']?$pro[$i]['sdateout']:'&nbsp;');
				}

				if ($pro[$i]['level'] > 0)
				{
					$space = '&nbsp;.&nbsp;';
					$spaceset = str_repeat($space,$pro[$i]['level']);
				}

// --------------- template declaration for list records -------------------------------------

				$GLOBALS['phpgw']->template->set_var(array
				(
					'number'		=> $pro[$i]['number'],
					'milestones'	=> (isset($pro[$i]['mstones'])?$pro[$i]['mstones']:'&nbsp;'),
					'title'			=> $spaceset . ($pro[$i]['title']?$pro[$i]['title']:'&nbsp;'),
					'end_date'		=> (isset($pro[$i]['edate'])?$edateout:'&nbsp;'),
					'coordinator'	=> $pro[$i]['coordinatorout']
				));

				$link_data['project_id'] = $pro[$i]['project_id'];

				if ($this->bo->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $this->account)
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
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&pro_main='
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
					$cat = $this->bo->cats->return_single($this->cat_id);
				}

				if ($cat[0]['app_name'] == 'phpgw' || $cat[0]['owner'] == '-1' || !$this->cat_id)
				{
					$showadd = True;
				}
				else if ($this->bo->check_perms($this->grants[$cat[0]['owner']],PHPGW_ACL_ADD) || $cat[0]['owner'] == $this->account)
				{
					$showadd = True;
				}
			}
			else
			{
				if ($pro_main && $pro_main != 0)
				{
					$coordinator = $this->bo->return_value('co',$pro_main);

					if ($this->bo->check_perms($this->grants[$coordinator],PHPGW_ACL_ADD) || $coordinator == $this->account)
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

			$this->save_sessiondata($action);
			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
		}

		function list_projects_home()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_main	= get_var('pro_main',array('POST','GET'));

			if ($_GET['cat_id'])
			{
				$this->cat_id = $_GET['cat_id'];
			}

			$menuaction	= get_var('menuaction',Array('GET'));
			if ($menuaction)
			{
				$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_main?lang('list jobs'):lang('list projects'));
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
			}
			else
			{
				$this->bo->cats->app_name = 'projects';
			}

			$this->t = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('projects'));
			$this->t->set_file(array('projects_list_t' => 'home_list.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);


			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects_home',
				'pro_main'		=> $pro_main,
				'action'		=> $action
			);

			$this->status = 'active';

			$this->bo->filter = 'public';
			//$this->bo->limit = False;
			$pro = $this->bo->list_projects($action,$pro_main);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			if ($action == 'mains')
			{
				$action_list= '<select name="cat_id" onChange="this.form.submit();"><option value="none">' . lang('Select category') . '</option>' . "\n"
							. $this->bo->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$this->t->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<select name="pro_main" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->bo->select_project_list(array('status' => $status, 'selected' => $pro_main)) . '</select>';
				$this->t->set_var('lang_action',lang('Work hours'));
			}

			$this->t->set_var('action_list',$action_list);
			$this->t->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$this->t->set_var('status_list',$this->status_format($this->status));

// ---------------- list header variable template-declarations --------------------------

			$this->t->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));
			$this->t->set_var('lang_milestones',lang('milestones'));
			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$this->t->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));


// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$edateout = $this->bo->formatted_edate($pro[$i]['edate']);

				if ($action == 'mains')
				{
					$td_action = ($pro[$i]['customerout']?$pro[$i]['customerout']:'&nbsp;');
				}
				else
				{
					$td_action = ($pro[$i]['sdateout']?$pro[$i]['sdateout']:'&nbsp;');
				}

				if ($pro[$i]['level'] > 0)
				{
					$space = '&nbsp;.&nbsp;';
					$spaceset = str_repeat($space,$pro[$i]['level']);
				}

// --------------- template declaration for list records -------------------------------------

				$this->t->set_var(array
				(
					'number'		=> $pro[$i]['number'],
					'milestones'	=> (isset($pro[$i]['mstones'])?$pro[$i]['mstones']:'&nbsp;'),
					'title'			=> $spaceset . ($pro[$i]['title']?$pro[$i]['title']:'&nbsp;'),
					'end_date'		=> (isset($pro[$i]['edate'])?$edateout:'&nbsp;'),
					'coordinator'	=> $pro[$i]['coordinatorout']
				));

				$link_data['project_id'] = $pro[$i]['project_id'];
				$link_data['public_view'] = True;
				$link_data['menuaction'] = 'projects.uiprojects.view_project';
				$this->t->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$this->t->set_var('lang_view_entry',lang('View'));

				if ($action == 'mains')
				{
					$this->t->set_var('jobs',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects_home&pro_main='
										. $pro[$i]['project_id'] . '&action=subs'));
					$this->t->set_var('lang_jobs_entry',lang('Jobs'));
				}
				else
				{
					$this->t->set_var('action_entry',''); 
					$this->t->set_var('lang_action_entry','');
				}

				$this->t->fp('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$this->save_sessiondata($action);

			$menuaction	= get_var('menuaction',Array('GET'));
			if ($menuaction)
			{
				list($app,$class,$method) = explode('.',$menuaction);
				$var['app_tpl']	= $method;
				$this->t->pfp('out','projects_list_t',True);
			}
			else
			{
				return $this->t->fp('out','projects_list_t',True);
			}
		}

		function coordinator_format($employee = '')
		{
			if (! $employee)
			{
				$employee = $this->account;
			}

			$employees = $this->bo->employee_list();

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

		function employee_format($data)
		{
			$type		= ($data['type']?$data['type']:'list');
			$project_id	= ($data['project_id']?$data['project_id']:0);

			$selected = $this->bo->get_acl_for_project($project_id);
			if (!is_array($selected))
			{
				$selected = array();
			}

			switch($type)
			{
				case 'list':
					$employees = $this->bo->employee_list();
					break;
				case 'field':
					$employees	= $this->bo->selected_employees($project_id);
					break;
			}

			//_debug_array($employees);
			//_debug_array($selected);
			while (is_array($employees) && list($null,$account) = each($employees))
			{
				$s .= '<option value="' . $account['account_id'] . '"';
				if (in_array($account['account_id'],$selected))
				{
					$s .= ' selected';
				}
				$s .= '>';
				$s .= $account['account_firstname'] . ' ' . $account['account_lastname']
										. ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
			}
			return $s;
		}

		function accounts_popup()
		{
			$GLOBALS['phpgw']->accounts->accounts_popup('projects');
		}

		function e_accounts_popup()
		{
			$GLOBALS['phpgw']->accounts->accounts_popup('e_projects');
		}

		function edit_project()
		{
			$action				= get_var('action',array('GET','POST'));
			$pro_main			= get_var('pro_main',array('GET','POST'));

			$book_activities	= get_var('book_activities',array('POST'));
			$bill_activities	= get_var('bill_activities',array('POST'));

			$project_id			= get_var('project_id',array('GET','POST'));
			$name				= get_var('name',array('POST'));
			$values				= get_var('values',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_projects',
				'pro_main'		=> $pro_main,
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			if ($_POST['save'] || $_POST['apply'])
			{
				$this->cat_id = ($values['cat']?$values['cat']:'');
				$values['coordinator']	= $_POST['accountid'];
				$values['employees']	= $_POST['employees'];

				$values['project_id']	= $project_id;
				$values['customer']		= $_POST['abid'];

				$error = $this->bo->check_values($action, $values, $book_activities, $bill_activities);
				if (is_array($error))
				{
					$GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$project_id = $this->bo->save_project($action, $values, $book_activities, $bill_activities);

					if($_POST['save'])
					{
						$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
					}
					else
					{
						$GLOBALS['phpgw']->template->set_var('message',lang('project %1 has been saved',$values['title']));
					}
				}
			}

			if($_POST['cancel'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if($_POST['delete'])
			{
				$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
				$link_data['pa_id'] = $project_id;
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if($_POST['mstone'])
			{
				$link_data['menuaction'] = 'projects.uiprojects.edit_mstone';
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($action == 'mains' || $action == 'amains')
			{
				$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($project_id?lang('edit project'):lang('add project'));
			}
			else
			{
				$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($project_id?lang('edit job'):lang('add job'));
			}

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('edit_form' => 'form.tpl'));
			$GLOBALS['phpgw']->template->set_block('edit_form','clist','clisthandle');
			$GLOBALS['phpgw']->template->set_block('edit_form','cfield','cfieldhandle');

			$GLOBALS['phpgw']->template->set_block('edit_form','elist','elisthandle');
			$GLOBALS['phpgw']->template->set_block('edit_form','efield','efieldhandle');

			$GLOBALS['phpgw']->template->set_block('edit_form','msfield1','msfield1handle');
			$GLOBALS['phpgw']->template->set_block('edit_form','msfield2','msfield2handle');
			$GLOBALS['phpgw']->template->set_block('edit_form','mslist','mslisthandle');
			$nopref = $this->bo->check_prefs();

			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->bo->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('addressbook_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.abook'));
			$GLOBALS['phpgw']->template->set_var('accounts_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.accounts_popup'));
			$GLOBALS['phpgw']->template->set_var('e_accounts_link',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.e_accounts_popup'));

			$GLOBALS['phpgw']->template->set_var('lang_open_popup',lang('open popup window'));
			$link_data['menuaction'] = 'projects.uiprojects.edit_project';
			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if ($project_id)
			{
				$values = $this->bo->read_single_project($project_id);
				$GLOBALS['phpgw']->template->set_var('old_status',$values['status']);
				$GLOBALS['phpgw']->template->set_var('old_parent',$values['parent']);
				$GLOBALS['phpgw']->template->set_var('old_edate',$values['edate']);
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

				$GLOBALS['phpgw']->template->fp('msfield1handle','msfield1',True);
				$mstones = $this->bo->get_mstones($project_id);

				$link_data['menuaction'] = 'projects.uiprojects.edit_mstone';

				while (is_array($mstones) && list(,$ms) = each($mstones))
				{
					$link_data['s_id'] = $ms['s_id'];
					$GLOBALS['phpgw']->template->set_var('s_title',$ms['title']);
					$GLOBALS['phpgw']->template->set_var('mstone_edit_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$GLOBALS['phpgw']->template->set_var('s_edateout',$this->bo->formatted_edate($ms['edate']));
					$GLOBALS['phpgw']->template->fp('mslisthandle','mslist',True);
				}
				$GLOBALS['phpgw']->template->fp('msfield2handle','msfield2',True);
			}
			else
			{
				$values = array
				(
					'parent'	=> $pro_main,
					'status'	=> 'active'
				);

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
			$GLOBALS['phpgw']->template->set_var('budget',$values['budget']);

			$GLOBALS['phpgw']->template->set_var('number',$values['number']);
			$GLOBALS['phpgw']->template->set_var('title',$values['title']);
			$GLOBALS['phpgw']->template->set_var('descr',$values['descr']);
			$GLOBALS['phpgw']->template->set_var('phours',$values['phours']);

			$month = $this->bo->return_date();
			$GLOBALS['phpgw']->template->set_var('month',$month['monthformatted']);

			$GLOBALS['phpgw']->template->set_var('status_list',$this->status_format($values['status'],(($action == 'mains' || $action == 'amains')?True:False)));

			$aradio = '<input type="radio" name="values[access]" value="private"' . ($values['access'] == 'private'?' checked':'') . '>' . lang('private');
			$aradio .= '<input type="radio" name="values[access]" value="public"' . ($values['access'] == 'public'?' checked':'') . '>' . lang('public');
			$aradio .= '<input type="radio" name="values[access]" value="anonym"' . ($values['access'] == 'anonym'?' checked':'') . '>' . lang('anonymous public');

			$GLOBALS['phpgw']->template->set_var('access',$aradio);

			$GLOBALS['phpgw']->template->set_var('previous_select',$this->bo->select_project_list(array('type' => 'all',
																										'status' => $values['status'],
																										'self' => $project_id,
																									'selected' => $values['previous'])));

			if ($action == 'mains' || $action == 'amains')
			{
				$cat = '<select name="values[cat]"><option value="">' . lang('None') . '</option>'
						.	$this->bo->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';

				$GLOBALS['phpgw']->template->set_var('cat',$cat);
				$GLOBALS['phpgw']->template->set_var('lang_main','');
				$GLOBALS['phpgw']->template->set_var('lang_parent','');
				$GLOBALS['phpgw']->template->set_var('pro_main','');
				$GLOBALS['phpgw']->template->set_var('parent_select','');
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Project ID'));
				$GLOBALS['phpgw']->template->set_var('lang_choose',($project_id?'':lang('generate project id ?')));

				$GLOBALS['phpgw']->template->set_var('investment_nr','<input type="text" name="values[investment_nr]" value="' . $values['investment_nr']
													 . '" size="50" maxlength="45">');

				$GLOBALS['phpgw']->template->set_var('pcosts','<input type="text" name="values[pcosts]" value="' . $values['pcosts'] . '">');

// ------------ activites bookable ----------------------

				$GLOBALS['phpgw']->template->set_var('book_activities_list',$this->bo->select_activities_list($project_id,False));

// -------------- activities billable ---------------------- 

    			$GLOBALS['phpgw']->template->set_var('bill_activities_list',$this->bo->select_activities_list($project_id,True));
			}
			else
			{
				if ($pro_main && ($action == 'subs' || $action == 'asubs'))
				{
					$GLOBALS['phpgw']->template->set_var('lang_choose',($project_id?'':lang('generate job id ?')));

					$main = $this->bo->read_single_project($pro_main);
    				$GLOBALS['phpgw']->template->set_var('parent_select','<select name="values[parent]">' . $this->bo->select_project_list(array('type' => 'mainandsubs',
																																				'status' => $values['status'],
																																				'self' => $project_id,
																																				'selected' => $values['parent'],
																																				'main' => $pro_main)) . '</select>');

					$GLOBALS['phpgw']->template->set_var('pro_main',$GLOBALS['phpgw']->strip_html($main['number']) . ' ' . $GLOBALS['phpgw']->strip_html($main['title']));
					$GLOBALS['phpgw']->template->set_var('cat',$this->bo->cats->id2name($main['cat']));
					$this->cat_id = $parent['cat'];
					$GLOBALS['phpgw']->template->set_var('book_activities_list',$this->bo->select_pro_activities($project_id, $pro_main, False));				
    				$GLOBALS['phpgw']->template->set_var('bill_activities_list',$this->bo->select_pro_activities($project_id, $pro_main, True));
				}

				$GLOBALS['phpgw']->template->set_var('lang_main',lang('Main project:'));
				$GLOBALS['phpgw']->template->set_var('lang_parent',lang('Parent project:'));
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Edit job'));
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Job ID'));
				$GLOBALS['phpgw']->template->set_var('investment_nr',$main['investment_nr']);
				$GLOBALS['phpgw']->template->set_var('pcosts',$main['pcosts']);

				$GLOBALS['phpgw']->template->set_var('lang_ptime_main',lang('time planned main project') . ':&nbsp;' . lang('work hours'));
				$GLOBALS['phpgw']->template->set_var('ptime_main',$main['phours']);

				$GLOBALS['phpgw']->template->set_var('lang_budget_main',lang('budget main project') . ':&nbsp;' . $prefs['currency']);
				$GLOBALS['phpgw']->template->set_var('budget_main',$main['budget']);

				if(!$values['coordinator'])
				{
					$values['coordinator'] = $main['coordinator'];
				}

				if(!$values['customer'])
				{
					$values['customer'] = $main['customer'];
				}
			}

			switch($GLOBALS['phpgw_info']['user']['preferences']['common']['account_selection'])
			{
				case 'popup':
					if ($values['coordinator'])
					{
						$GLOBALS['phpgw']->template->set_var('accountid',$values['coordinator']);
						$co = $GLOBALS['phpgw']->accounts->get_account_data($values['coordinator']);
						$GLOBALS['phpgw']->template->set_var('accountname',$GLOBALS['phpgw']->common->display_fullname($co[$values['coordinator']]['lid'],
																			$co[$values['coordinator']]['firstname'],$co[$values['coordinator']]['lastname']));
					}
					$GLOBALS['phpgw']->template->set_var('clisthandle','');
					$GLOBALS['phpgw']->template->fp('cfieldhandle','cfield',True);

					$GLOBALS['phpgw']->template->set_var('employee_list',$this->employee_format(array('type' => 'field','project_id' => $project_id)));

					$GLOBALS['phpgw']->template->set_var('elisthandle','');
					$GLOBALS['phpgw']->template->fp('efieldhandle','efield',True);
					break;
				default:
					$GLOBALS['phpgw']->template->set_var('coordinator_list',$this->coordinator_format($values['coordinator']));
						$GLOBALS['phpgw']->template->set_var('cfieldhandle','');
						$GLOBALS['phpgw']->template->fp('clisthandle','clist',True);

					$GLOBALS['phpgw']->template->set_var('employee_list',$this->employee_format(array('project_id' => $project_id)));
						$GLOBALS['phpgw']->template->set_var('efieldhandle','');
						$GLOBALS['phpgw']->template->fp('elisthandle','elist',True);
					break;
			}

			$abid = $values['customer'];
			$customer = $this->bo->read_single_contact($abid);
            if ($customer[0]['org_name'] == '') { $name = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            else { $name = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }

			$GLOBALS['phpgw']->template->set_var('name',$name);
			$GLOBALS['phpgw']->template->set_var('abid',$abid);

			if ($this->bo->check_perms($this->grants[$values['coordinator']],PHPGW_ACL_DELETE) || $values['coordinator'] == $this->account)
			{
				$GLOBALS['phpgw']->template->set_var('delete','<input type="submit" name="delete" value="' . lang('Delete') .'">');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('delete','&nbsp;');
			}
			$this->save_sessiondata($action);
			$GLOBALS['phpgw']->template->pfp('out','edit_form');
		}

		function view_project()
		{
			$action			= $_GET['action'];
			$pro_main		= $_GET['pro_main'];
			$project_id		= $_GET['project_id'];
			$public_view	= $_GET['public_view'];

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_main?lang('view job'):lang('view project'));

			if (isset($public_view))
			{
				$menuaction = 'projects.uiprojects.list_projects_home';
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				$this->set_app_langs();
			}
			else
			{
				$menuaction = 'projects.uiprojects.list_projects';
				$this->display_app_header();
			}

			$link_data = array
			(
				'menuaction'	=> $menuaction,
				'pro_main'		=> $pro_main,
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			$GLOBALS['phpgw']->template->set_file(array('view' => 'view.tpl'));
			$GLOBALS['phpgw']->template->set_block('view','sub','subhandle');
			$GLOBALS['phpgw']->template->set_block('view','nonanonym','nonanonymhandle');
			$GLOBALS['phpgw']->template->set_block('view','mslist','mslisthandle');

			$nopref = $this->bo->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->bo->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('done_action',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$values = $this->bo->read_single_project($project_id);

			$GLOBALS['phpgw']->template->set_var('cat',$this->bo->cats->id2name($values['cat']));

// ------------ activites bookable ----------------------

			$boact = $this->bo->activities_list($project_id,False);
			if (is_array($boact))
			{
				while (list($null,$bo) = each($boact))
				{
					$boact_list .=	$bo['descr'] . ' [' . $bo['num'] . ']' . '<br>';
				}
			}

			$GLOBALS['phpgw']->template->set_var('book_activities_list',$boact_list);
// -------------- activities billable ---------------------- 

			$billact = $this->bo->activities_list($project_id,True);
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
				$GLOBALS['phpgw']->template->set_var('investment_nr',$values['investment_nr']);
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Project ID'));
				$GLOBALS['phpgw']->template->set_var('pcosts',$values['pcosts']);
			}
			else if($pro_main && $action == 'subs')
			{
				$main = $this->bo->read_single_project($pro_main);

				$GLOBALS['phpgw']->template->set_var('pro_main',$GLOBALS['phpgw']->strip_html($main['title']) . ' [' . $GLOBALS['phpgw']->strip_html($main['number']) . ']');
				$GLOBALS['phpgw']->template->set_var('cat',$this->bo->cats->id2name($main['cat']));
				$GLOBALS['phpgw']->template->set_var('investment_nr',($main['investment_nr']?$main['investment_nr']:'&nbsp;'));
				$GLOBALS['phpgw']->template->set_var('pcosts',$main['pcosts']);
				$GLOBALS['phpgw']->template->set_var('lang_number',lang('Job ID'));

				$GLOBALS['phpgw']->template->set_var('pro_parent',$this->bo->return_value('pro',$values['parent']));	
				$GLOBALS['phpgw']->template->fp('subhandle','sub',True);
			}

			if ($values['previous'])
			{
				$GLOBALS['phpgw']->template->set_var('previous',$this->bo->return_value('pro',$values['previous']));	
			}
			$GLOBALS['phpgw']->template->set_var('number',$GLOBALS['phpgw']->strip_html($values['number']));
			$GLOBALS['phpgw']->template->set_var('title',($values['title']?$values['title']:'&nbsp;'));
			$GLOBALS['phpgw']->template->set_var('descr',($values['descr']?$values['descr']:'&nbsp;'));
			$GLOBALS['phpgw']->template->set_var('status',lang($values['status']));
			$GLOBALS['phpgw']->template->set_var('budget',$values['budget']);
			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

			$month = $this->bo->return_date();
			$GLOBALS['phpgw']->template->set_var('month',$month['monthformatted']);

			$GLOBALS['phpgw']->template->set_var('phours',$values['phours']);

			$GLOBALS['phpgw']->template->set_var('uhours',$values['uhours']);

			$GLOBALS['phpgw']->template->set_var('sdate',$values['sdate_formatted']);
			$GLOBALS['phpgw']->template->set_var('edate',$values['edate_formatted']);
			$GLOBALS['phpgw']->template->set_var('udate',$values['udate_formatted']);
			$GLOBALS['phpgw']->template->set_var('cdate',$values['cdate_formatted']);

			$GLOBALS['phpgw']->template->set_var('coordinator',$GLOBALS['phpgw']->accounts->id2name($values['coordinator']));
			$GLOBALS['phpgw']->template->set_var('owner',$GLOBALS['phpgw']->accounts->id2name($values['owner']));
			$GLOBALS['phpgw']->template->set_var('processor',$GLOBALS['phpgw']->accounts->id2name($values['processor']));

// ----------------------------------- customer ------------------------------

			if ($values['customer'] != 0) 
			{
				$customer = $this->bo->read_single_contact($values['customer']);
            	if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            	else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
			}
			else { $customerout = '&nbsp;'; }

			$GLOBALS['phpgw']->template->set_var('customer',$customerout);

			$mstones = $this->bo->get_mstones($project_id);
			//$link_data['menuaction'] = 'projects.uiprojects.edit_mstone';

			while (is_array($mstones) && list(,$ms) = each($mstones))
			{
				//$link_data['s_id'] = $ms['s_id'];
				$GLOBALS['phpgw']->template->set_var('s_title',$ms['title']);
				//$GLOBALS['phpgw']->template->set_var('mstone_edit_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->template->set_var('s_edateout',$this->bo->formatted_edate($ms['edate']));
				$GLOBALS['phpgw']->template->fp('mslisthandle','mslist',True);
			}

			if (!isset($public_view))
			{
				$GLOBALS['phpgw']->template->fp('nonanonymhandle','nonanonym',True);
				$GLOBALS['phpgw']->hooks->process(array
				(
					'location'   => 'projects_view',
					'project_id' => $project_id
				));
			}
			$GLOBALS['phpgw']->template->pfp('out','view');
		}

		function delete_pa()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_parent = get_var('pro_parent',array('POST','GET'));

			$subs		= get_var('subs',array('POST'));
			$pa_id		= get_var('pa_id',array('POST','GET'));

			switch($action)
			{
				case 'mains'	:	$menu = 'projects.uiprojects.list_projects';
									$deleteheader = lang('are you sure you want to delete this project');
									break;
				case 'subs'		:	$menu = 'projects.uiprojects.list_projects';
									$deleteheader = lang('are you sure you want to delete this project');
									break;
				case 'act'		:	$menu = 'projects.uiprojects.list_activities';
									$deleteheader = lang('are you sure you want to delete this activity');
									break;
			}

			$link_data = array
			(
				'menuaction'	=> $menu,
				'pro_parent'	=> $pro_parent,
				'pa_id'			=> $pa_id,
				'action'		=> $action
			);

			if ($_POST['yes'])
			{
				$del = $pa_id;

				if ($subs)
				{
					$this->bo->delete_pa($action, $del, True);
				}
				else
				{
					$this->bo->delete_pa($action, $del, False);
				}
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($_POST['no'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			$this->display_app_header();
			$GLOBALS['phpgw']->template->set_file(array('pa_delete' => 'delete.tpl'));

			$GLOBALS['phpgw']->template->set_var('lang_subs','');
			$GLOBALS['phpgw']->template->set_var('subs', '');

			$GLOBALS['phpgw']->template->set_var('deleteheader',$deleteheader);
			$GLOBALS['phpgw']->template->set_var('lang_no',lang('No'));
			$GLOBALS['phpgw']->template->set_var('lang_yes',lang('Yes'));

			if ($action != 'act')
			{
				$exists = $this->bo->exists('mains', 'par', $num ='', $pa_id);

				if ($exists)
				{
					$GLOBALS['phpgw']->template->set_var('lang_subs',lang('Do you also want to delete all sub projects ?'));
					$GLOBALS['phpgw']->template->set_var('subs','<input type="checkbox" name="subs" value="True">');
				}
			}

			$link_data['menuaction'] = 'projects.uiprojects.delete_pa';
			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->pfp('out','pa_delete');
		}

		function list_activities()
		{
			$action = 'act';

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('list activities');
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('activities_list_t' => 'listactivities.tpl'));
			$GLOBALS['phpgw']->template->set_block('activities_list_t','activities_list','list');

			$nopref = $this->bo->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->bo->get_prefs();
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act'
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			$act = $this->bo->list_activities($this->start, True, $this->query, $this->sort, $this->order, $this->cat_id);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

            $GLOBALS['phpgw']->template->set_var('cat_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('categories_list',$this->bo->cats->formatted_list('select','all',$this->cat_id,'True'));
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
			$this->save_sessiondata('act');
			$GLOBALS['phpgw']->template->pfp('out','activities_list_t',True);

// -------------------------------- end Add form declaration ------------------------------

		}

		function edit_activity()
		{
			$activity_id	= get_var('activity_id',array('POST','GET'));
			$values			= get_var('values',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_activities',
				'action'		=> 'act'
			);

			if ($_POST['save'])
			{
				$this->cat_id			= ($values['cat']?$values['cat']:'');
				$values['activity_id']	= $activity_id;

				$error = $this->bo->check_pa_values($values);
				if (is_array($error))
				{
					$GLOBALS['phpgw']->template->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					$this->bo->save_activity($values);
					$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
				}
			}

			if($_POST['cancel'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($activity_id?lang('edit activity'):lang('add activity'));
			$this->display_app_header();

			$form = ($activity_id?'edit':'add');

			$GLOBALS['phpgw']->template->set_file(array('edit_activity' => 'formactivity.tpl'));

			$GLOBALS['phpgw']->template->set_var('done_url',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.edit_activity&activity_id=' . $activity_id));

			$nopref = $this->bo->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->bo->get_prefs();
			}

			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

			if ($activity_id)
			{
				$values = $this->bo->read_single_activity($activity_id);
				$this->cat_id = $values['cat'];
				$GLOBALS['phpgw']->template->set_var('lang_choose','');
				$GLOBALS['phpgw']->template->set_var('choose','');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('lang_choose',lang('Generate Activity ID ?'));
				$GLOBALS['phpgw']->template->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');
			}

			$GLOBALS['phpgw']->template->set_var('cats_list',$this->bo->cats->formatted_list('select','all',$this->cat_id,True));
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

			$this->save_sessiondata('act');
			$GLOBALS['phpgw']->template->pfp('out','edit_activity');
		}

		function list_admins()
		{
			$action = get_var('action',array('GET','POST'));
			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.edit_admins',
				'action'		=> $action
			);

			if ($_POST['add'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($_POST['done'])
			{
				$GLOBALS['phpgw']->redirect_link('/admin/index.php');
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'pad')?lang('administration'):lang('accountancy'));
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_file(array('admin_list_t' => 'list_admin.tpl'));
			$GLOBALS['phpgw']->template->set_block('admin_list_t','admin_list','list');

			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$link_data['menuaction'] = 'projects.uiprojects.list_admins';
			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if (!$this->start)
			{
				$this->start = 0;
			}

			$admins = $this->bo->list_admins($action, 'both', $this->start, $this->query, $this->sort, $this->order);

//--------------------------------- nextmatch --------------------------------------------
 
			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

    		$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));
 
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

			$GLOBALS['phpgw']->template->pfp('out','admin_list_t',True);
			$this->save_sessiondata($action);
		}

		function edit_admins()
		{
			$action = get_var('action',array('GET','POST'));
			$users	= get_var('users',array('POST'));
			$groups = get_var('groups',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_admins',
				'action'		=> $action
			);

			if ($_POST['save'])
			{
				$this->bo->edit_admins($action, $users, $groups);
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($_POST['cancel'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'pad')?lang('edit administrator list'):lang('edit bookkeeper list'));
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_file(array('admin_add' => 'form_admin.tpl'));

			$link_data['menuaction'] = 'projects.uiprojects.edit_admins';
			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('users_list',$this->bo->selected_admins($action,'aa'));
			$GLOBALS['phpgw']->template->set_var('groups_list',$this->bo->selected_admins($action,'ag'));
			$GLOBALS['phpgw']->template->set_var('lang_users_list',lang('Select users'));
			$GLOBALS['phpgw']->template->set_var('lang_groups_list',lang('Select groups'));

			$GLOBALS['phpgw']->template->pfp('out','admin_add');
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

			$this->bo->cats->app_name = 'addressbook';

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
 
			$entries = $this->bo->read_abook($start, $query, $qfilter, $sort, $order);

// --------------------------------- nextmatch ---------------------------

			$left = $this->nextmatchs->left('/index.php',$start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$start));

// -------------------------- end nextmatch ------------------------------------

			$GLOBALS['phpgw']->template->set_var('cats_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('cats_list',$this->bo->cats->formatted_list('select','all',$cat_id,True));
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
			$prefs		= get_var('prefs',array('POST'));
			$abid		= get_var('abid',array('POST'));

			if ($_POST['save'])
			{
				$prefs['abid']		= $abid;
				$obill = $this->bo->save_prefs($prefs);

				if (!$obill)
				{
					$GLOBALS['phpgw']->redirect_link('/preferences/index.php');
				}
			}

			if ($_POST['done'])
			{
				$GLOBALS['phpgw']->redirect_link('/preferences/index.php');
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.preferences',
			);

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('preferences');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$GLOBALS['phpgw']->template->set_file(array('prefs' => 'preferences.tpl'));
			$GLOBALS['phpgw']->template->set_block('prefs','book','bookhandle');
			$GLOBALS['phpgw']->template->set_block('prefs','all','allhandle');
			$this->set_app_langs();

			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('lang_notify_mstone',lang('would you like to get notified if milestones date due change'));
			$GLOBALS['phpgw']->template->set_var('lang_notify_pro',lang('would you like to get notified if projects data get updated'));
			$GLOBALS['phpgw']->template->set_var('lang_notify_assign',lang('would you like to get notified if you get assigned to a project'));
			$GLOBALS['phpgw']->template->set_var('lang_mainscreen_show',lang('would you like to view your assigned projects on the main screen'));

			$GLOBALS['phpgw']->template->set_var('lang_notifications',lang('email notifications'));

			$prefs = $this->bo->read_prefs();
			$GLOBALS['phpgw']->template->set_var('notify_mstone_selected',($prefs['notify_mstone'] == 'yes'? ' checked':''));
			$GLOBALS['phpgw']->template->set_var('notify_pro_selected',($prefs['notify_pro'] == 'yes'? ' checked':''));
			$GLOBALS['phpgw']->template->set_var('notify_assign_selected',($prefs['notify_assign'] == 'yes'? ' checked':''));
			$options = array(
					0 => lang('No'),
					1 => lang('Yes'),
					/*NOT YET: 2 => lang('Yes').' - '.lang('small view'),*/
			);
			$mainscreen_options = '';
			foreach($options as $i=>$opt)
			{
				$sel = ($prefs['mainscreen_showevents']==$i)?" selected":"";
				$mainscreen_options .= '<option'.$sel.' value="'.$i.'">'.$opt.'</a>' . "\n";
			}
			$GLOBALS['phpgw']->template->set_var('mainscreen_options',$mainscreen_options);
			//unset($mainscreen_options);

			if ($this->bo->isprojectadmin('pbo') || $this->bo->isprojectadmin('pad'))
			{
				if ($obill)
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

				$GLOBALS['phpgw']->template->set_var('oldbill',$prefs['bill']);

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

					$entry = $this->bo->read_single_contact($abid);

					if ($entry[0]['org_name'] == '') { $GLOBALS['phpgw']->template->set_var('name',$entry[0]['n_given'] . ' ' . $entry[0]['n_family']); }
					else { $GLOBALS['phpgw']->template->set_var('name',$entry[0]['org_name'] . ' [ ' . $entry[0]['n_given'] . ' ' . $entry[0]['n_family'] . ' ]'); }
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('name',$name);
				}

				$GLOBALS['phpgw']->template->set_var('abid',$abid);

				$GLOBALS['phpgw']->template->set_var('bookhandle','');
				$GLOBALS['phpgw']->template->set_var('allhandle','');

				$GLOBALS['phpgw']->template->pfp('out','prefs');
				$GLOBALS['phpgw']->template->pfp('bookhandle','book');
				$GLOBALS['phpgw']->template->pfp('allhandle','all');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('bookhandle','');
				$GLOBALS['phpgw']->template->set_var('allhandle','');

				$GLOBALS['phpgw']->template->pfp('out','prefs');
				$GLOBALS['phpgw']->template->pfp('allhandle','all');
			}
		}

		function archive()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_main	= get_var('pro_main',array('POST','GET'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (($action == 'amains')?lang('project archive'):lang('job archive'));
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
				'pro_main'		=> $pro_main,
				'action'		=> $action,
				'cat_id'		=> $this->cat_id
			);

			if (!$pro_main)
			{
				$pro_main = 0;
			}

			$this->bo->status = 'archive';
			$pro = $this->bo->list_projects($action,$pro_main);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------------ end nextmatch template ------------------------------------

			if ($action == 'amains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="">' . lang('Select category') . '</option>' . "\n"
							. $this->bo->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->bo->select_project_list(array('status' => 'archive','selected' => $pro_main)) . '</select>';
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
					$td_action = ($pro[$i]['customerout']?$pro[$i]['customerout']:'&nbsp;');
				}
				else
				{
					$td_action = ($pro[$i]['sdateout']?$pro[$i]['sdateout']:'&nbsp;');
				}

// --------------- template declaration for list records -------------------------------------

				$GLOBALS['phpgw']->template->set_var(array
				(
					'number'		=> $GLOBALS['phpgw']->strip_html($pro[$i]['number']),
					'td_action'		=> $td_action,
					'status'		=> lang($pro[$i]['status']),
					'title'			=> $title,
					'end_date'		=> $edateout,
					'coordinator'	=> $pro[$i]['coordinatorout']
				));

				$link_data['project_id'] = $pro[$i]['project_id'];

				if ($this->bo->check_perms($this->grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $this->account)
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
					$GLOBALS['phpgw']->template->set_var('action_entry',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&pro_main='
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

				if ($this->bo->isprojectadmin('pbo'))
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

		function list_budget()
		{
			$action		= get_var('action',array('POST','GET'));
			$pro_main	= get_var('pro_main',array('POST','GET'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_parent?lang('list budget'):lang('list budget'));

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'list_budget.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');

			$nopref = $this->bo->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->bo->get_prefs();
			}
			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.list_budget',
				'pro_main'		=> $pro_main,
				'action'		=> $action
			);

			if (!$pro_main)
			{
				$pro_main = 0;
			}

			$pro = $this->bo->list_projects($action,$pro_main);

// --------------------- nextmatch variable template-declarations ------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bo->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bo->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bo->total_records,$this->start));

// ------------------------- end nextmatch template --------------------------------------

			if ($action == 'mains')
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) . '" name="form">' . "\n"
							. '<select name="cat_id" onChange="this.form.submit();"><option value="none">' . lang('Select category') . '</option>' . "\n"
							. $this->bo->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->bo->select_project_list(array('status' => $this->status, 'selected' => $pro_main)) . '</select>';
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

			$GLOBALS['phpgw']->template->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_investment_nr',$this->nextmatchs->show_sort_order($this->sort,'investment_nr',$this->order,'/index.php',lang('investment nr'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_pcosts',$this->nextmatchs->show_sort_order($this->sort,'pcosts',$this->order,'/index.php',lang('planned costs'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_budget',$this->nextmatchs->show_sort_order($this->sort,'budget',$this->order,'/index.php',lang('budget'),$link_data));

// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);

// --------------- template declaration for list records -------------------------------------

				$GLOBALS['phpgw']->template->set_var(array
				(
					'number'		=> $GLOBALS['phpgw']->strip_html($pro[$i]['number']),
					'investment_nr'	=> ($pro[$i]['investment_nr']?$pro[$i]['investment_nr']:'&nbsp;'),
					'title'			=> ($pro[$i]['title']?$pro[$i]['title']:'&nbsp;'),
					'budget'		=> ($pro[$i]['budget']?$pro[$i]['budget']:'&nbsp;'),
					'pcosts'		=> ($pro[$i]['pcosts']?$pro[$i]['pcosts']:'&nbsp;')
				));

				$link_data['project_id'] = $pro[$i]['project_id'];
				$link_data['menuaction'] = 'projects.uiprojects.view_pcosts';
				$GLOBALS['phpgw']->template->set_var('view',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->template->set_var('lang_view_entry',lang('View'));

				$GLOBALS['phpgw']->template->parse('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

// --------------- template declaration for sum  --------------------------

			$GLOBALS['phpgw']->template->set_var('lang_sum_pcosts',lang('sum pcosts'));
			$GLOBALS['phpgw']->template->set_var('lang_sum_budget',lang('sum budget'));
			$GLOBALS['phpgw']->template->set_var('sum_pcosts',$this->bo->sum_budget('pcosts'));
			$GLOBALS['phpgw']->template->set_var('sum_budget',$this->bo->sum_budget());

// ----------------------- end sum declaration ----------------------------

			$this->save_sessiondata($action);
			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
		}

		function view_pcosts()
		{
			$project_id = get_var('project_id',array('GET'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('list pcosts');

			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'list_pcosts.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');
			$GLOBALS['phpgw']->template->set_var('done_action',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_budget&action=mains'));
			$pcosts = $this->bo->list_pcosts($project_id);

			for ($i=0;$i<count($pcosts);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);

// --------------- template declaration for list records -------------------------------------

				if ($pcosts[$i]['month'] > 0)
				{
					$pcosts[$i]['month'] = $pcosts[$i]['month'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
					$pdateout = $GLOBALS['phpgw']->common->show_date($pcosts[$i]['month'],'n/Y');
				}
				else
				{
					$pdateout = '&nbsp;';
				}

				$GLOBALS['phpgw']->template->set_var(array
				(
					'month'		=> $pdateout,
					'pcosts'	=> ($pcosts[$i]['pcosts']?$pcosts[$i]['pcosts']:'&nbsp;')
				));

				$GLOBALS['phpgw']->template->parse('list','projects_list',True);
			}
			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
		}

		function edit_mstone()
		{
			$action		= get_var('action',array('GET','POST'));
			$s_id		= get_var('s_id',array('GET','POST'));
			$project_id	= get_var('project_id',array('GET','POST'));
			$values		= get_var('values',array('POST'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uiprojects.edit_project',
				'action'		=> $action,
				'project_id'	=> $project_id,
				's_id'			=> $s_id
			);

			if ($_POST['save'])
			{
				$values['s_id']			= $s_id;
				$values['project_id']	= $project_id;
				$this->bo->save_mstone($values);
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($_POST['cancel'])
			{
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			if ($_POST['delete'])
			{
				$this->bo->delete_mstone($values);
				$GLOBALS['phpgw']->redirect_link('/index.php',$link_data);
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . (isset($s_id)?lang('edit milestone'):lang('add milestone'));
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('mstone_edit' => 'form_mstone.tpl'));

			if($s_id)
			{
				$values = $this->bo->get_single_mstone($s_id);

				$GLOBALS['phpgw']->template->set_var('old_edate',$values['edate']);

				//if ($this->bo->check_perms($this->grants[$values['coordinator']],PHPGW_ACL_DELETE) || $values['coordinator'] == $this->account)
				//{
				$GLOBALS['phpgw']->template->set_var('delete','<input type="submit" name="delete" value="' . lang('Delete') .'">');
				/*}
				else
				{
					$GLOBALS['phpgw']->template->set_var('delete','&nbsp;');
				}*/
			}

			$link_data['menuaction'] = 'projects.uiprojects.edit_mstone';
			$GLOBALS['phpgw']->template->set_var('edit_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$GLOBALS['phpgw']->template->set_var('title',$GLOBALS['phpgw']->strip_html($values['title']));

			if (!$values['edate'])
			{
				$values['emonth'] = date('m',time());
				$values['eday'] = date('d',time());
				$values['eyear'] = date('Y',time());
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

			$GLOBALS['phpgw']->template->pfp('out','mstone_edit');
		}
	}
?>
