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
			'user_stat'		=> True
		);

		function uistatistics()
		{
			global $action;

			$this->boprojects				= CreateObject('projects.boprojects',True,$action);
			$this->bostatistics				= CreateObject('projects.bostatistics');
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
			$this->t->set_var('lang_calculate',lang('Calculate'));
			$this->t->set_var('lang_descr',lang('Description'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_none',lang('None'));
			$this->t->set_var('lang_start_date',lang('Start Date'));
			$this->t->set_var('lang_end_date',lang('End Date'));
			$this->t->set_var('lang_date_due',lang('Date due'));
			$this->t->set_var('lang_project',lang('Project'));
			$this->t->set_var('lang_hours',lang('Hours'));
			$this->t->set_var('lang_jobs',lang('Jobs'));
			$this->t->set_var('lang_activity',lang('Activity'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_budget',lang('Budget'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_firstname',lang('Firstname'));
			$this->t->set_var('lang_lastname',lang('Lastname'));
			$this->t->set_var('lang_lid',lang('Username'));
			$this->t->set_var('lang_billedonly',lang('Billed only'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('lang_minperae',lang('Minutes per workunit'));
    		$this->t->set_var('lang_billperae',lang('Bill per workunit'));
			$this->t->set_var('lang_stat',lang('Statistic'));
			$this->t->set_var('lang_userstats',lang('User statistics'));
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

			$this->t->set_file(array('projects_list_t' => 'stats_projectlist.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.list_projects',
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
			}
			else
			{
				$action_list= '<form method="POST" action="' . $GLOBALS['phpgw']->link('/index.php',$link_data) .'" name="form">' . "\n"
							. '<select name="pro_parent" onChange="this.form.submit();"><option value="">' . lang('Select main project') . '</option>' . "\n"
							. $this->boprojects->select_project_list('mains', $status, $pro_parent) . '</select>';
				$this->t->set_var('lang_header',lang('Job list'));
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
				$lang_action = '<td width="5%" align="center">' . lang('Jobs') . '</td>' . "\n";
				$this->t->set_var('lang_action',$lang_action);
			}
			else
			{
				$this->t->set_var(sort_action,$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
				$this->t->set_var('lang_action','');
			}

			$this->t->set_var('sort_status',$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status'),$link_data));
			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$this->t->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));

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
						$customer = $this->boprojects->read_customer_data($pro[$i]['customer']);
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

				$this->t->set_var('stat',$GLOBALS['phpgw']->link('/projects/stats_projectstat.php','id=' . $pro[$i]['project_id']));
				$this->t->set_var('lang_stat_entry',lang('Statistic'));

				if ($action == 'mains')
				{
					$action_entry = '<td align="center"><a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&pro_parent='
																. $pro[$i]['project_id'] . '&action=subs') . '">' . lang('Jobs')
																. '</a></td>' . "\n";
					$this->t->set_var('action_entry',$action_entry);
				}
				else
				{
					$this->t->set_var('action_entry','');
				}

				$this->t->fp('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$this->t->set_var('userstats_action',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_users&action=ustat'));

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

		function list_users()
		{
			$this->display_app_header();

			$this->t->set_file(array('user_list_t' => 'stats_userlist.tpl'));
			$this->t->set_block('user_list_t','user_list','list');

			$this->t->set_var('lang_action',lang('User statistics'));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

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
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->bostatistics->total_records,$this->start));

// ------------------------ end nextmatch template --------------------------------------

// --------------- list header variable template-declarations ---------------------------

			$this->t->set_var('sort_lid',$this->nextmatchs->show_sort_order($this->sort,'account_lid',$this->order,'/index.php',lang('Username'),$link_data));
			$this->t->set_var('sort_firstname',$this->nextmatchs->show_sort_order($this->sort,'account_firstname',$this->order,'/index.php',lang('Firstname'),$link_data));
			$this->t->set_var('sort_lastname',$this->nextmatchs->show_sort_order($this->sort,'account_lastname',$this->order,'/index.php',lang('Lastname'),$link_data));
			$this->t->set_var('lang_stat',lang('Statistic'));

// ------------------------- end header declaration -------------------------------------

			for ($i=0;$i<count($users);$i++)
			{
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$firstname = $users[$i]['account_firstname'];
				if (!$firstname) { $firstname = '&nbsp;'; }
				$lastname = $users[$i]['account_lastname'];
				if (!$lastname) { $lastname = '&nbsp;'; }

// --------------------- template declaration for list records ---------------------------

				$this->t->set_var(array('lid' => $users[$i]['account_lid'],
							'firstname' => $firstname,
							'lastname' => $lastname));
	
				$link_data['account_id'] = $users[$i]['account_id'];
				$link_data['menuaction'] = 'projects.uistatistics.user_stat';
				$this->t->set_var('stat',$GLOBALS['phpgw']->link('/index.php',$link_data));

				$this->t->set_var('lang_stat_entry',lang('Statistic'));
				$this->t->fp('list','user_list',True);
			}

// ------------------------------- end record declaration ---------------------------------

			$this->t->pfp('out','user_list_t',True);
			$this->save_sessiondata($action);
		}

		function user_stat()
		{
			global $submit, $billed, $account_id;

			$link_data = array
			(
				'menuaction'	=> 'projects.uistatistics.user_stat',
				'action'		=> 'ustat',
				'account_id'	=> $account_id,
				'billed'		=> $billed
			);

			if (! $account_id)
			{
				Header('Location: ' . $phpgw->link('/index.php','menuaction=projects.uistatistics.list_users&action=ustat'));
			}

			$this->display_app_header();

			$this->t->set_file(array('user_stat_t' => 'stats_userstat.tpl'));
			$this->t->set_block('user_stat_t','user_stat','stat');

			$this->t->set_var('lang_action',lang('User statistic'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$cached_data = $this->boprojects->cached_accounts($account_id);

			$this->t->set_var('lid',$GLOBALS['phpgw']->strip_html($cached_data[$account_id]['account_lid']));
			$this->t->set_var('firstname',$GLOBALS['phpgw']->strip_html($cached_data[$account_id]['account_firstname']));
			$this->t->set_var('lastname',$GLOBALS['phpgw']->strip_html($cached_data[$account_id]['account_lastname']));

			$this->nextmatchs->alternate_row_color(&$this->t);

			if (!$submit)
			{
				$emonth = date('m',time());
				$eday = date('d',time());
				$eyear = date('Y',time());
				$edate = mktime(2,0,0,$emonth,$eday,$eyear);
			}

			if (!$sdate)
			{
				$smonth = 0;
				$sday = 0;
				$syear = 0;
			}
			else
			{
				$smonth = date('m',$sdate);
				$sday = date('d',$sdate);
				$syear = date('Y',$sdate);
			}

			if (!$edate)
			{
				$emonth = 0;
				$eday = 0;
				$eyear = 0;
			}
			else
			{
				$emonth = date('m',$edate);
				$eday = date('d',$edate);
				$eyear = date('Y',$edate);
			}

			$this->t->set_var('start_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('syear',$syear),
																						$this->sbox->getMonthText('smonth',$smonth),
																						$this->sbox->getDays('sday',$sday)));
			$this->t->set_var('end_date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('eyear',$eyear),
																					$this->sbox->getMonthText('emonth',$emonth),
																					$this->sbox->getDays('eday',$eday)));

// -------------- calculate statistics --------------------------

			if($billed)
			{
				$this->t->set_var('billed','checked');
				$filter .= " AND phpgw_p_hours.status='billed' ";
			}

			if (checkdate($smonth,$sday,$syear))
			{
				$sdate = mktime(2,0,0,$smonth,$sday,$syear);
				$filter .= " AND phpgw_p_hours.start_date>='$sdate' ";
			}

			if (checkdate($emonth,$eday,$eyear))
			{
				$edate = mktime(2,0,0,$emonth,$eday,$eyear);
				$filter .= " AND phpgw_p_hours.end_date<='$edate' ";
			}

			$pro = $this->bostatistics->get_userstat_pro($account_id, $filter);

			while (list($null,$userpro) = each($pro))
			{
				$summin = 0;
				$this->nextmatchs->template_alternate_row_color(&$this->t);
				$this->t->set_var('e_project',$GLOBALS['phpgw']->strip_html($userpro['title']));
				$this->t->set_var('e_activity','');
				$this->t->set_var('e_hours','');
				$this->t->fp('stat','user_stat',True);

				$hours = $this->bostatistics->get_userstat_hours($account_id, $userpro['project_id'], $filter); 
				for ($i=0;$i<=count($hours);$i++)
				{
//					$this->nextmatchs->template_alternate_row_color(&$this->t);
					$this->t->set_var('e_project','');
					$this->t->set_var('e_activity',$GLOBALS['phpgw']->strip_html($hours[$i]['descr']));
					$summin += $hours[$i]['min'];
					$hrs = floor($hours[$i]['min']/60) . ':' . sprintf ("%02d",(int)($hours[$i]['min']-floor($hours[$i]['min']/60)*60));
					$this->t->set_var('e_hours',$hrs);
					$this->t->fp('stat','user_stat',True);
				}

				$this->t->set_var('e_project','');
				$this->t->set_var('e_activity','');
				$hrs = floor($summin/60) . ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60)); 
				$this->t->set_var('e_hours',$hrs);
				$this->t->fp('stat','user_stat',True);
			}

			$allhours = $this->bostatistics->get_userstat_hours($account_id, $project_id ='', $filter);

			$summin=0;
			$this->nextmatchs->template_alternate_row_color(&$this->t);
			$this->t->set_var('e_project',lang('Overall'));
			$this->t->set_var('e_activity','');
			$this->t->set_var('e_hours','');
			$this->t->fp('stat','user_stat',True);

			while (list($null,$userall) = each($allhours))
			{
				$this->t->set_var('e_project','');
				$this->t->set_var('e_activity',$GLOBALS['phpgw']->strip_html($userall['descr']));
				$summin += $userall['min'];
				$hrs = floor($userall['min']/60) . ':' . sprintf ("%02d",(int)($userall['min']-floor($userall['min']/60)*60));
				$this->t->set_var('e_hours',$hrs);
				$this->t->fp('stat','user_stat',True);
			}

			$this->nextmatchs->template_alternate_row_color(&$this->t);
			$this->t->set_var('e_project',lang('Sum'));
			$this->t->set_var('e_activity','');
			$hrs = floor($summin/60) . ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60)); 
			$this->t->set_var('e_hours',$hrs);
			$this->t->fp('stat','user_stat',True);

			$this->t->pfp('out','user_stat_t',True);
		}
	}
?>
