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
	/* $Source$ */

	class uistatistics
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
			'list_projects'	=> True,
			'list_users'	=> True,
			'user_stat'		=> True,
			'project_stat'	=> True,
			'show_stat'		=> True
		);

		function uistatistics()
		{
			$action = get_var('action',array('POST','GET'));

			$this->boprojects				= CreateObject('projects.boprojects',True,'pstat');
			$this->bostatistics				= CreateObject('projects.bostatistics');
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
			$GLOBALS['phpgw']->template->set_var('lang_calculate',lang('Calculate'));
			$GLOBALS['phpgw']->template->set_var('lang_descr',lang('Description'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_none',lang('None'));
			$GLOBALS['phpgw']->template->set_var('lang_start_date',lang('Start Date'));
			$GLOBALS['phpgw']->template->set_var('lang_end_date',lang('End Date'));
			$GLOBALS['phpgw']->template->set_var('lang_date_due',lang('Date due'));
			$GLOBALS['phpgw']->template->set_var('lang_project',lang('Project'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Hours'));
			$GLOBALS['phpgw']->template->set_var('lang_jobs',lang('Jobs'));
			$GLOBALS['phpgw']->template->set_var('lang_activity',lang('Activity'));
			$GLOBALS['phpgw']->template->set_var('lang_title',lang('Title'));
			$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));
			$GLOBALS['phpgw']->template->set_var('lang_budget',lang('Budget'));
			$GLOBALS['phpgw']->template->set_var('lang_customer',lang('Customer'));
			$GLOBALS['phpgw']->template->set_var('lang_coordinator',lang('Coordinator'));
			$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
			$GLOBALS['phpgw']->template->set_var('lang_firstname',lang('Firstname'));
			$GLOBALS['phpgw']->template->set_var('lang_lastname',lang('Lastname'));
			$GLOBALS['phpgw']->template->set_var('lang_employee',lang('Employee'));
			$GLOBALS['phpgw']->template->set_var('lang_billedonly',lang('Billed only'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Work hours'));
			$GLOBALS['phpgw']->template->set_var('lang_minperae',lang('Minutes per workunit'));
    		$GLOBALS['phpgw']->template->set_var('lang_billperae',lang('Bill per workunit'));
			$GLOBALS['phpgw']->template->set_var('lang_stat',lang('Statistic'));
			$GLOBALS['phpgw']->template->set_var('lang_userstats',lang('User statistics'));
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
			$GLOBALS['phpgw']->template->set_var('lang_statistics',lang("Statistics"));
			$GLOBALS['phpgw']->template->set_var('link_projects',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains'));
			$GLOBALS['phpgw']->template->set_var('lang_projects',lang('Projects'));
			$GLOBALS['phpgw']->template->set_var('link_archiv',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains'));
			$GLOBALS['phpgw']->template->set_var('lang_archiv',lang('archive'));

			$GLOBALS['phpgw']->template->fp('app_header','projects_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
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

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . ($pro_main?lang('list jobs'):lang('list projects'));
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('projects_list_t' => 'stats_projectlist.tpl'));
			$GLOBALS['phpgw']->template->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.list_projects',
				'pro_main'		=> $pro_main,
				'action'		=> $action,
				'cat_id'		=> $this->cat_id
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			$pro = $this->boprojects->list_projects(array('type' => $action,'parent' => $pro_main));

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
							. $this->boprojects->cats->formatted_list('select','all',$this->cat_id,True) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Jobs'));
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_main" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list(array('status' => $status, 'selected' => $pro_main)) . '</select>';
				$GLOBALS['phpgw']->template->set_var('lang_action',lang('Work hours'));
			}

			$GLOBALS['phpgw']->template->set_var('action_list',$action_list);
			$GLOBALS['phpgw']->template->set_var('filter_list',$this->nextmatchs->new_filter($this->filter));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(array('query' => $this->query)));
			$GLOBALS['phpgw']->template->set_var('status_list',$this->status_format($this->status));

			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

// ---------------- list header variable template-declarations --------------------------

			$GLOBALS['phpgw']->template->set_var('sort_number',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Project ID'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_sdate',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_edate',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));

// -------------- end header declaration ---------------------------------------

            for ($i=0;$i<count($pro);$i++)
            {
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);

				$edateout = $this->boprojects->formatted_edate($pro[$i]['edate']);
				$sdateout = $this->boprojects->formatted_edate($pro[$i]['sdate'],False);

				if ($action == 'mains')
				{
					$td_action = ($pro[$i]['customerout']?$pro[$i]['customerout']:'&nbsp;');
				}

// --------------- template declaration for list records -------------------------------------

				$GLOBALS['phpgw']->template->set_var(array
				(
					'number'		=> $pro[$i]['number'],
					'td_action'		=> $td_action,
					'title'			=> ($pro[$i]['title']?$pro[$i]['title']:'&nbsp;'),
					'sdate'			=> (isset($pro[$i]['sdate'])?$sdateout:'&nbsp;'),
					'edate'			=> (isset($pro[$i]['edate'])?$edateout:'&nbsp;'),
					'coordinator'	=> $pro[$i]['coordinatorout']
				));

				$link_data['project_id'] = $pro[$i]['project_id'];
				$link_data['menuaction'] = 'projects.uistatistics.project_stat';
				$GLOBALS['phpgw']->template->set_var('stat',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->template->set_var('lang_stat_entry',lang('Statistic'));

				if ($action == 'mains')
				{
					$action_entry = '<td align="center"><a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&pro_main='
																. $pro[$i]['project_id'] . '&action=subs') . '">' . lang('Jobs')
																. '</a></td>' . "\n";
					$GLOBALS['phpgw']->template->set_var('action_entry',$action_entry);
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('action_entry','');
				}

				$GLOBALS['phpgw']->template->fp('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$GLOBALS['phpgw']->template->set_var('userstats_action',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_users&action=ustat'));

			$this->save_sessiondata('pstat');
			$GLOBALS['phpgw']->template->pfp('out','projects_list_t',True);
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

		function list_users()
		{
			$action	= get_var('action',array('POST','GET'));

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('User statistics');
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('user_list_t' => 'stats_userlist.tpl'));
			$GLOBALS['phpgw']->template->set_block('user_list_t','user_list','list');

			$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$GLOBALS['phpgw']->template->set_var('search_list',$this->nextmatchs->search(1));

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.list_users',
				'action'		=> 'ustat'
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			$users = $this->bostatistics->get_users('accounts', $this->start, $this->sort, $this->order, $this->query);

// ------------- nextmatch variable template-declarations -------------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bostatistics->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bostatistics->total_records,$link_data);
			$GLOBALS['phpgw']->template->set_var('left',$left);
			$GLOBALS['phpgw']->template->set_var('right',$right);

			$GLOBALS['phpgw']->template->set_var('lang_showing',$this->nextmatchs->show_hits($this->bostatistics->total_records,$this->start));

// ------------------------ end nextmatch template --------------------------------------

// --------------- list header variable template-declarations ---------------------------

			$GLOBALS['phpgw']->template->set_var('sort_lid',$this->nextmatchs->show_sort_order($this->sort,'account_lid',$this->order,'/index.php',lang('Username'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_firstname',$this->nextmatchs->show_sort_order($this->sort,'account_firstname',$this->order,'/index.php',lang('Firstname'),$link_data));
			$GLOBALS['phpgw']->template->set_var('sort_lastname',$this->nextmatchs->show_sort_order($this->sort,'account_lastname',$this->order,'/index.php',lang('Lastname'),$link_data));
			$GLOBALS['phpgw']->template->set_var('lang_stat',lang('Statistic'));

// ------------------------- end header declaration -------------------------------------

			for ($i=0;$i<count($users);$i++)
			{
				$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
				$firstname = $users[$i]['account_firstname'];
				if (!$firstname) { $firstname = '&nbsp;'; }
				$lastname = $users[$i]['account_lastname'];
				if (!$lastname) { $lastname = '&nbsp;'; }

// --------------------- template declaration for list records ---------------------------

				$GLOBALS['phpgw']->template->set_var(array('lid' => $users[$i]['account_lid'],
							'firstname' => $firstname,
							'lastname' => $lastname));
	
				$link_data['account_id'] = $users[$i]['account_id'];
				$link_data['menuaction'] = 'projects.uistatistics.user_stat';
				$GLOBALS['phpgw']->template->set_var('stat',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$GLOBALS['phpgw']->template->set_var('lang_stat_entry',lang('Statistic'));
				$GLOBALS['phpgw']->template->fp('list','user_list',True);
			}

// ------------------------------- end record declaration ---------------------------------

			$GLOBALS['phpgw']->template->pfp('out','user_list_t',True);
			$this->save_sessiondata($action);
		}

		function user_stat()
		{
			$submit		= get_var('submit',array('POST'));
			$values		= get_var('values',array('POST','GET'));
			$account_id	= get_var('account_id',array('POST','GET'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.user_stat',
				'action'		=> 'ustat',
				'account_id'	=> $account_id
			);

			if (! $account_id)
			{
				$phpgw->redirect_link('/index.php','menuaction=projects.uistatistics.list_users&action=ustat');
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('User statistics');
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('user_stat_t' => 'stats_userstat.tpl'));
			$GLOBALS['phpgw']->template->set_block('user_stat_t','user_stat','stat');

			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$cached_data = $this->boprojects->cached_accounts($account_id);
			$employee = $GLOBALS['phpgw']->strip_html($cached_data[$account_id]['firstname']
                                        . ' ' . $cached_data[$account_id]['lastname'] . ' ['
                                        . $cached_data[$account_id]['account_lid'] . ' ]');

			$GLOBALS['phpgw']->template->set_var('employee',$employee);

			$this->nextmatchs->alternate_row_color(&$GLOBALS['phpgw']->template);

			if (!$values['sdate'])
			{
				$values['smonth']	= 0;
				$values['sday']		= 0;
				$values['syear']	= 0;
			}
			else
			{
				$values['smonth']	= date('m',$values['sdate']);
				$values['sday']		= date('d',$values['sdate']); 
				$values['syear']	= date('Y',$values['sdate']);
			}

			if (!$values['edate'])
			{
				$values['emonth']	= 0;
				$values['eday']		= 0;
				$values['eyear']	= 0;
			}
			else
			{
				$values['emonth']	= date('m',$values['edate']);
				$values['eday']		= date('d',$values['edate']); 
				$values['eyear']	= date('Y',$values['edate']);
			}

			$GLOBALS['phpgw']->template->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																							$this->sbox->getMonthText('values[smonth]',$values['smonth']),
																							$this->sbox->getDays('values[sday]',$values['sday'])));
			$GLOBALS['phpgw']->template->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																							$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																							$this->sbox->getDays('values[eday]',$values['eday'])));

// -------------- calculate statistics --------------------------

			$GLOBALS['phpgw']->template->set_var('billed','<input type="checkbox" name="values[billed]" value="True"'
										. ($values['billed'] == 'private'?' checked':'') . '>');

			$pro = $this->bostatistics->get_userstat_pro($account_id, $values);

			if (is_array($pro))
			{
				while (list($null,$userpro) = each($pro))
				{
					$summin = 0;
					$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
					$GLOBALS['phpgw']->template->set_var('e_project',$GLOBALS['phpgw']->strip_html($userpro['title']) . ' ['
											. $GLOBALS['phpgw']->strip_html($userpro['num']) . ']');
					$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_hours','&nbsp;');
					$GLOBALS['phpgw']->template->fp('stat','user_stat',True);

					$hours = $this->bostatistics->get_stat_hours('both', $account_id, $userpro['project_id'], $values); 
					for ($i=0;$i<=count($hours);$i++)
					{
						if ($hours[$i]['num'] != '')
						{
							$GLOBALS['phpgw']->template->set_var('e_project','&nbsp;');
							$GLOBALS['phpgw']->template->set_var('e_activity',$GLOBALS['phpgw']->strip_html($hours[$i]['descr']) . ' ['
													. $GLOBALS['phpgw']->strip_html($hours[$i]['num']) . ']');
							$summin += $hours[$i]['min'];
							$hrs = floor($hours[$i]['min']/60) . ':' . sprintf ("%02d",(int)($hours[$i]['min']-floor($hours[$i]['min']/60)*60));
							$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);
							$GLOBALS['phpgw']->template->fp('stat','user_stat',True);
						}
					}

					$GLOBALS['phpgw']->template->set_var('e_project','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
					$hrs = floor($summin/60) . ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60)); 
					$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);
					$GLOBALS['phpgw']->template->fp('stat','user_stat',True);
				}
			}

			$allhours = $this->bostatistics->get_stat_hours('account', $account_id, $project_id ='', $values);

			$summin=0;
			$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
			$GLOBALS['phpgw']->template->set_var('e_project','<b>' . lang('Overall') . '</b>');
			$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
			$GLOBALS['phpgw']->template->set_var('e_hours','&nbsp;');
			$GLOBALS['phpgw']->template->fp('stat','user_stat',True);

			if (is_array($allhours))
			{
				while (list($null,$userall) = each($allhours))
				{
					$GLOBALS['phpgw']->template->set_var('e_project','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_activity',$GLOBALS['phpgw']->strip_html($userall['descr']) . ' ['
													. $GLOBALS['phpgw']->strip_html($userall['num']) . ']');
					$summin += $userall['min'];
					$hrs = floor($userall['min']/60) . ':' . sprintf ("%02d",(int)($userall['min']-floor($userall['min']/60)*60));
					$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);
					$GLOBALS['phpgw']->template->fp('stat','user_stat',True);
				}
			}
			
			$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
			$GLOBALS['phpgw']->template->set_var('e_project','<b>' . lang('Sum') . '</b>');
			$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
			$hrs = floor($summin/60) . ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60)); 
			$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);
			$GLOBALS['phpgw']->template->fp('stat','user_stat',True);
			$GLOBALS['phpgw']->template->pfp('out','user_stat_t',True);
		}

		function show_stat($project_id)
		{
			$this->bostatistics->show_graph($project_id);
		}

		function project_stat()
		{
			$project_id	= get_var('project_id',array('GET','POST'));
			$smonth		= get_var('smonth',array('GET','POST'));
			$syear		= get_var('syear',array('GET','POST'));

			if (! $project_id)
			{
				$GLOBALS['phpgw']->redirect_link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains');
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.project_stat',
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('gantt chart for project %1', $this->boprojects->return_value('pro',$project_id));
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('project_stat' => 'stats_gant.tpl'));

			if (!$smonth)
			{
				$smonth = date('m',time());
			}

			if (!$syear)
			{
				$syear = date('Y',time());
			}

			$GLOBALS['phpgw']->template->set_var('date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('syear',$syear),
																										$this->sbox->getMonthText('smonth',$smonth),''));
			$GLOBALS['phpgw']->template->set_var('project_id',$project_id);
			$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->bostatistics->show_graph(array('project_id' => $project_id,'syear' => $syear, 'smonth' => $smonth));

			$GLOBALS['phpgw']->template->set_var('pix_src',$GLOBALS['phpgw_info']['server']['webserver_url'] . SEP . 'phpgwapi' . SEP . 'images' . SEP . 'draw_tmp.png');

			$GLOBALS['phpgw']->template->pfp('out','project_stat');
		}

		/*function project_stat()
		{
			$submit		= get_var('submit',array('POST'));
			$values		= get_var('values',array('POST','GET'));
			$project_id	= get_var('project_id',array('POST','GET'));
			$action		= get_var('action',array('POST','GET'));

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.project_stat',
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			if (! $project_id)
			{
				$GLOBALS['phpgw']->redirect_link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains');
			}

			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('projects') . ': ' . lang('project statistic');
			$this->display_app_header();

			$GLOBALS['phpgw']->template->set_file(array('project_stat' => 'stats_projectstat.tpl'));
			$GLOBALS['phpgw']->template->set_block('project_stat','stat_list','list');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$GLOBALS['phpgw']->template->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			$pro = $this->boprojects->read_single_project($project_id);

			$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$title = $GLOBALS['phpgw']->strip_html($pro['title']);
			if (! $title) $title = '&nbsp;';
			$GLOBALS['phpgw']->template->set_var('project',$title . ' [' . $GLOBALS['phpgw']->strip_html($pro['number']) . ']');
			$GLOBALS['phpgw']->template->set_var('status',lang($pro['status']));
			$GLOBALS['phpgw']->template->set_var('budget',$pro['budget']);
			$GLOBALS['phpgw']->template->set_var('currency',$prefs['currency']);

			$GLOBALS['phpgw']->template->set_var('lang_account',lang('Account'));
			$GLOBALS['phpgw']->template->set_var('lang_activity',lang('Activity'));
			$GLOBALS['phpgw']->template->set_var('lang_hours',lang('Hours'));

			if (!$values['sdate'])
			{
				if (! $pro['sdate'] || $pro['sdate'] == 0)
				{
					$values['smonth']	= 0;
					$values['sday']		= 0; 
					$values['syear']	= 0;
				}
				else
				{
					$values['smonth']	= date('m',$pro['sdate']);
					$values['sday']		= date('d',$pro['sdate']); 
					$values['syear']	= date('Y',$pro['sdate']);
				}
			}
			else
			{
				$values['smonth']	= date('m',$values['sdate']);
				$values['sday']		= date('d',$values['sdate']); 
				$values['syear']	= date('Y',$values['sdate']);
			}

			if (!$values['edate'])
			{
				if (! $pro['edate'] || $pro['edate'] == 0)
				{
					$values['emonth']	= 0;
					$values['eday']		= 0; 
					$values['eyear']	= 0;
				}
				else
				{
					$values['emonth']	= date('m',$pro['edate']);
					$values['eday']		= date('d',$pro['edate']); 
					$values['eyear']	= date('Y',$pro['edate']);
				}
			}
			else
			{
				$values['emonth']	= date('m',$values['edate']);
				$values['eday']		= date('d',$values['edate']); 
				$values['eyear']	= date('Y',$values['edate']);
			}

			$GLOBALS['phpgw']->template->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[syear]',$values['syear']),
																							$this->sbox->getMonthText('values[smonth]',$values['smonth']),
																							$this->sbox->getDays('values[sday]',$values['sday'])));
			$GLOBALS['phpgw']->template->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[eyear]',$values['eyear']),
																							$this->sbox->getMonthText('values[emonth]',$values['emonth']),
																							$this->sbox->getDays('values[eday]',$values['eday'])));

			$cached_data = $this->boprojects->cached_accounts($pro['coordinator']);
			$coordinatorout = $GLOBALS['phpgw']->strip_html($cached_data[$pro['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro['coordinator']]['lastname'] . ' ]');
			$GLOBALS['phpgw']->template->set_var('coordinator',$coordinatorout);

			if ($pro['customer'] != 0) 
			{
				$customer = $this->boprojects->read_single_contact($pro[$i]['customer']);
            	if ($customer[0]['org_name'] == '')
				{
					$GLOBALS['phpgw']->template->set_var('customer',$customer[0]['n_given'] . ' ' . $customer[0]['n_family']);
				}
            	else
				{
					$GLOBALS['phpgw']->template->set_var('customer',$customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]');
				}
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('customer','&nbsp;');
			}

			$GLOBALS['phpgw']->template->set_var('billed','<input type="checkbox" name="values[billed]" value="True"'
										. ($values['billed'] == 'private'?' checked':'') . '>');

// -------------------------------- calculate statistics -----------------------------------------

			$employees = $this->bostatistics->get_employees($project_id, $values);

			if (is_array($employees))
			{
				while (list($null,$employee) = each($employees))
				{
					$account_data = $this->boprojects->cached_accounts($employee['employee']);
					$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);

					$account_id = $account_data[$employee['employee']]['account_id']; 

					$summin = 0;
					$GLOBALS['phpgw']->template->set_var('e_account',$GLOBALS['phpgw']->strip_html($account_data[$employee['employee']]['firstname']) . ' '
											. $GLOBALS['phpgw']->strip_html($account_data[$employee['employee']]['lastname']) . ' ['
											. $GLOBALS['phpgw']->strip_html($account_data[$employee['employee']]['account_lid']) . ']');

					$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_hours','&nbsp;');
					$GLOBALS['phpgw']->template->fp('list','stat_list',True);

					$hours = $this->bostatistics->get_stat_hours('both', $account_id, $project_id, $values);

					for ($i=0;$i<=count($hours);$i++)
					{
						if ($hours[$i]['num'] != '')
						{
							$GLOBALS['phpgw']->template->set_var('e_account','&nbsp;');
							$GLOBALS['phpgw']->template->set_var('e_activity',$GLOBALS['phpgw']->strip_html($hours[$i]['descr']) . ' ['
														. $GLOBALS['phpgw']->strip_html($hours[$i]['num']) . ']');
							$hrs = floor($hours[$i]['min']/60). ':' . sprintf ("%02d",(int)($hours[$i]['min']-floor($hours[$i]['min']/60)*60));
							$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);
							$summin += $hours[$i]['min'];
							$GLOBALS['phpgw']->template->fp('list','stat_list',True);
						}
					}

					$GLOBALS['phpgw']->template->set_var('e_account','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
					$sumhours = floor($summin/60). ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
					$GLOBALS['phpgw']->template->set_var('e_hours',$sumhours); 
					$GLOBALS['phpgw']->template->fp('list','stat_list',True);
				}
			}

			$prohours = $this->bostatistics->get_stat_hours('project', $account_id = '', $project_id, $values); 

			$summin=0;
			$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
			$GLOBALS['phpgw']->template->set_var('e_account','<b>' . lang('Overall') . '</b>');
			$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
			$GLOBALS['phpgw']->template->set_var('e_hours','&nbsp;');

			$GLOBALS['phpgw']->template->fp('list','stat_list',True);

			if (is_array($prohours))
			{
				while (list($null,$proall) = each($prohours))
				{
					$GLOBALS['phpgw']->template->set_var('e_account','&nbsp;');
					$GLOBALS['phpgw']->template->set_var('e_activity',$GLOBALS['phpgw']->strip_html($proall['descr']) . ' ['
												. $GLOBALS['phpgw']->strip_html($proall['num']) . ']');
					$summin += $proall['min'];
					$hrs = floor($proall['min']/60). ':' . sprintf ("%02d",(int)($proall['min']-floor($proall['min']/60)*60));
					$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);

					$GLOBALS['phpgw']->template->fp('list','stat_list',True);
				}
			}
			$this->nextmatchs->template_alternate_row_color(&$GLOBALS['phpgw']->template);
			$GLOBALS['phpgw']->template->set_var('e_account','<b>' . lang('sum') . '</b>');
			$GLOBALS['phpgw']->template->set_var('e_activity','&nbsp;');
			$hrs = floor($summin/60). ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
			$GLOBALS['phpgw']->template->set_var('e_hours',$hrs);

			$GLOBALS['phpgw']->template->fp('list','stat_list',True);
			$GLOBALS['phpgw']->template->pfp('out','project_stat');
		}*/
	}
?>
