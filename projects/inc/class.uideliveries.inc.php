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

	class uideliveries
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
			'delivery'			=> True,
			'list_deliveries'	=> True,
			'show_delivery'		=> True,
			'fail'				=> True
		);

		function uideliveries()
		{
			global $action;

			$this->boprojects				= CreateObject('projects.boprojects',True, $action);
			$this->bodeliveries				= CreateObject('projects.bodeliveries');
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
			$this->t->set_var('lang_work_date',lang('Work date'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_budget',lang('Budget'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('lang_project',lang('Project'));
			$this->t->set_var('lang_stats',lang('Statistics'));
			$this->t->set_var('lang_delivery_num',lang('Delivery ID'));
			$this->t->set_var('lang_delivery_date',lang('Delivery date'));
			$this->t->set_var('lang_activity',lang('Activity'));
			$this->t->set_var('lang_aes',lang('Workunits'));
			$this->t->set_var('lang_select',lang('Select'));
			$this->t->set_var('lang_print_delivery',lang('Print delivery'));
			$this->t->set_var('lang_sumaes',lang('Sum workunits'));
			$this->t->set_var('lang_position',lang('Position'));
			$this->t->set_var('lang_workunits',lang('Workunits'));
			$this->t->set_var('lang_delivery_date',lang('Delivery date'));
			$this->t->set_var('lang_work_date',lang('Work date'));
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
			$this->t->set_var('lang_jobs',lang('Jobs'));
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

			$this->t->set_file(array('projects_list_t' => 'bill_list.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			if (!$action)
			{
				$action = 'mains';
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uideliveries.list_projects',
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
			$this->t->set_var('h_lang_part',lang('Delivery note'));
			$this->t->set_var('h_lang_partlist',lang('Delivery list'));

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
            			if (!$customer[0]['org_name']) { $td_action = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
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
				$link_data['menuaction'] = 'projects.uideliveries.delivery';

				$this->t->set_var('part',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$this->t->set_var('lang_part',lang('Delivery'));

				$this->t->set_var('partlist',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_deliveries&action=del'
											. '&project_id=' . $pro[$i]['project_id']));
				$this->t->set_var('lang_partlist',lang('Delivery list'));

				if ($action == 'mains')
				{
					$action_entry = '<td align="center"><a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects'
																. '&pro_parent=' . $pro[$i]['project_id'] . '&action=subs') . '">' . lang('Jobs')
																. '</a></td>' . "\n";
					$this->t->set_var('action_entry',$action_entry);
				}
				else
				{
					$this->t->set_var('action_entry','');
				}

				$this->t->parse('list','projects_list',True);
			}

// ------------------------- end record declaration ------------------------

			$this->t->set_var('lang_all_partlist',lang('All delivery notes'));
			$this->t->set_var('all_partlist',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_deliveries&action=del'
											. '&project_id='));

			$this->t->set_var('lang_all_part2list','');
			$this->t->set_var('all_part2list','');

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}

		function delivery()
		{
			global $action, $Delivery, $project_id, $delivery_id, $values, $select, $referer;

			if (! $Delivery)
			{
				$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];
			}

			if (!$project_id)
			{
				Header('Location: ' . $referer);
			}

			if ($Delivery)
			{
				$values['project_id']	= $project_id;
				$pro = $this->boprojects->read_single_project($project_id);
				$values['customer']		= $pro['customer'];

				$error = $this->bodeliveries->check_values($values, $select);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					if ($delivery_id)
					{
						$values['delivery_id'] = $delivery_id;
						$this->bodeliveries->update_delivery($values, $select);
					}
					else
					{
						$delivery_id = $this->bodeliveries->delivery($values, $select);
					}
				}
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uideliveries.delivery',
				'pro_parent'	=> $pro_parent,
				'action'		=> $action,
				'project_id'	=> $project_id,
				'delivery_id'	=> $delivery_id
			);

			$this->display_app_header();

			$this->t->set_file(array('hours_list_t' => 'del_listhours.tpl'));
			$this->t->set_block('hours_list_t','hours_list','list');

			$this->t->set_var('lang_action',lang('Delivery'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->set_var('hidden_vars','<input type="hidden" name="referer" value="' . $referer . '">');
			$this->t->set_var('doneurl',$referer);

			$pro = $this->boprojects->read_single_project($project_id);

			$title = $GLOBALS['phpgw']->strip_html($pro['title']);
			if (! $title)  $title  = '&nbsp;';
			$this->t->set_var('project',$title . ' [' . $GLOBALS['phpgw']->strip_html($pro['number']) . ']');

			if (!$pro['customer'])
			{
				$this->t->set_var('customer',lang('You have no customer selected !'));
			}
			else
			{
				$customer = $this->boprojects->read_single_contact($pro['customer']);
				if (!$customer[0]['org_name']) { $customername = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
				else { $customername = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
				$this->t->set_var('customer',$customername);
			}

			if(!$delivery_id)
			{
				$this->t->set_var('lang_choose',lang('Generate Delivery ID ?'));
				$this->t->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');
				$this->t->set_var('print_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.fail'));
				$this->t->set_var('delivery_num',$values['delivery_num']);
				$hours = $this->bodeliveries->read_hours($project_id);
			}
			else
			{
				$this->t->set_var('lang_choose','');
				$this->t->set_var('choose','');
				$this->t->set_var('print_delivery',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.show_delivery'
																		. '&delivery_id=' . $delivery_id));
				$del = $this->bodeliveries->read_single_delivery($delivery_id);
				$this->t->set_var('delivery_num',$del['delivery_num']);
				$hours = $this->bodeliveries->read_delivery_hours($project_id,$delivery_id);
			}

			if ($del['date'])
			{
				$values['month'] = date('m',$del['date']);
				$values['day'] = date('d',$del['date']);
				$values['year'] = date('Y',$del['date']);
			}
			else
			{
				$values['month'] = date('m',time());
				$values['day'] = date('d',time());
				$values['year'] = date('Y',time());
			}

			$this->t->set_var('date_select',$GLOBALS['phpgw']->common->dateformatorder($this->sbox->getYears('values[year]',$values['year']),
																				$this->sbox->getMonthText('values[month]',$values['month']),
																				$this->sbox->getDays('values[day]',$values['day'])));    

			$sumaes=0;
			if (is_array($hours))
			{
				while (list($null,$note) = each($hours))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);

					$select = '<input type="checkbox" name="select[' . $note['hours_id'] . ']" value="True" checked>';

					$activity = $GLOBALS['phpgw']->strip_html($note['descr']);
					if (! $activity)  $activity  = '&nbsp;';

					$hours_descr = $GLOBALS['phpgw']->strip_html($note['hours_descr']);
					if (! $hours_descr)  $hours_descr  = '&nbsp;';

					$start_date = $note['sdate'];
					if ($start_date == 0) { $start_dateout = '&nbsp;'; }
					else
					{
						$start_date = $start_date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$start_dateout = $GLOBALS['phpgw']->common->show_date($start_date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					if ($note['minperae'] != 0)
					{
						$aes = ceil($note['minutes']/$note['minperae']);
					}
					$sumaes += $aes;

// --------------------- template declaration for list records ---------------------------

					$this->t->set_var(array('select' => $select,
										'activity' => $activity,
									'hours_descr' => $hours_descr,
										'status' => lang($note['status']),
									'start_date' => $start_dateout,
											'aes' => $aes));

					if (($note['status'] != 'billed') && ($note['status'] != 'closed'))
					{
						if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_EDIT) || $pro['coordinator'] == $this->account)
						{
							$link_data['menuaction']	= 'projects.uiprojecthours.edit_hours';
							$link_data['hours_id']		= $note['hours_id'];
							$this->t->set_var('edithour',$GLOBALS['phpgw']->link('/index.php',$link_data));
							$this->t->set_var('lang_edit_entry',lang('Edit'));
						}
					}
					else
					{
						$this->t->set_var('edithour','');
						$this->t->set_var('lang_edit_entry','&nbsp;');
					}
					$this->t->fp('list','hours_list',True);

// -------------------------- end record declaration --------------------------
				}
			}

			if ($delivery_id)
			{
				$hours = $this->bodeliveries->read_hours($project_id);
				if (is_array($hours))
				{
					while (list($null,$note) = each($hours))
					{
						$this->nextmatchs->template_alternate_row_color(&$this->t);

						$select = '<input type="checkbox" name="select[' . $note['hours_id'] . ']" value="True">';

						$activity = $GLOBALS['phpgw']->strip_html($note['descr']);
						if (! $activity)  $activity  = '&nbsp;';
	
						$hours_descr = $GLOBALS['phpgw']->strip_html($note['hours_descr']);
						if (! $hours_descr)  $hours_descr  = '&nbsp;';

						$start_date = $note['sdate'];
						if ($start_date == 0) { $start_dateout = '&nbsp;'; }
						else
						{
							$start_date = $start_date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
							$start_dateout = $GLOBALS['phpgw']->common->show_date($start_date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
						}

						if ($note['minperae'] != 0)
						{
							$aes = ceil($note['minutes']/$note['minperae']);
						}
					//	$sumaes += $aes;

// --------------------- template declaration for list records ---------------------------

						$this->t->set_var(array('select' => $select,
											'activity' => $activity,
										'hours_descr' => $hours_descr,
											'status' => lang($note['status']),
										'start_date' => $start_dateout,
											'	aes' => $aes));

						if (($note['status'] != 'billed') && ($note['status'] != 'closed'))
						{
							if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_EDIT) || $pro['coordinator'] == $this->account)
							{
								$link_data['menuaction']	= 'projects.uiprojecthours.edit_hours';
								$link_data['hours_id']		= $note['hours_id'];
								$this->t->set_var('edithour',$GLOBALS['phpgw']->link('/index.php',$link_data));
								$this->t->set_var('lang_edit_entry',lang('Edit'));
							}
						}
						else
						{
							$this->t->set_var('edithour','');
							$this->t->set_var('lang_edit_entry','&nbsp;');
						}
						$this->t->fp('list','hours_list',True);

// -------------------------- end record declaration --------------------------
					}
				}
			}

			$this->t->set_var(sum_aes,$sumaes);

			if (! $delivery_id)
			{
				if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
				{
					$this->t->set_var('delivery','<input type="submit" name="Delivery" value="' . lang('Create delivery') . '">');
				}
			}
 			else
			{
				if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
				{
					$this->t->set_var('delivery','<input type="submit" name="Delivery" value="' . lang('Update delivery') . '">');
				}
			}

			$this->t->pfp('out','hours_list_t',True);
		}

		function list_deliveries()
		{
			global $project_id;

			$this->display_app_header();

			$this->t->set_file(array('projects_list_t' => 'bill_listinvoice.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uideliveries.list_deliveries',
				'action'		=> 'del',
				'project_id'	=> $project_id
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			$this->t->set_var('lang_action',lang('Delivery list'));
			$this->t->set_var('search_action',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('search_list',$this->nextmatchs->search(1));

			if (! $this->start)
			{
				$this->start = 0;
			}

			if (! $project_id)
			{
				$project_id = '';
			}

			$del = $this->bodeliveries->read_deliveries($this->query, $this->sort, $this->order, True, $project_id);

// -------------------- nextmatch variable template-declarations -----------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bodeliveries->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bodeliveries->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->bodeliveries->total_records,$this->start));

// ------------------------ end nextmatch template -------------------------------------------

// ---------------- list header variable template-declarations -------------------------------

			$this->t->set_var('sort_num',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Delivery ID'),$link_data));
			$this->t->set_var('sort_customer',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var('sort_date',$this->nextmatchs->show_sort_order($this->sort,'date',$this->order,'/index.php',lang('Date'),$link_data));
			$this->t->set_var('sort_sum','');
			$this->t->set_var('lang_data',lang('Delivery'));

// -------------- end header declaration -----------------

			if (is_array($del))
			{
				while (list($null,$note) = each($del))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);
					$title = $GLOBALS['phpgw']->strip_html($note['title']);
					if (! $title) $title  = '&nbsp;';

					$date = $note['date'];
					if ($date == 0)
						$dateout = '&nbsp;';
					else
					{
						$date = $date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$dateout = $GLOBALS['phpgw']->common->show_date($date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					if ($note['customer'] != 0) 
					{
						$customer = $this->boprojects->read_single_contact($note['customer']);
            			if (!$customer[0]['org_name']) { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            			else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
					}
					else { $customerout = '&nbsp;'; }

					$this->t->set_var('sum','');

// ------------------ template declaration for list records ----------------------------------

					$this->t->set_var(array('num' => $GLOBALS['phpgw']->strip_html($note['delivery_num']),
									'customer' => $customerout,
										'title' => $title,
										'date' => $dateout));

					$link_data['delivery_id']	= $note['delivery_id'];
					$link_data['project_id']	= $note['project_id'];
					$link_data['menuaction']	= 'projects.uideliveries.delivery';
					$this->t->set_var('td_data',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$this->t->set_var('lang_td_data',lang('Delivery'));

					$this->t->fp('list','projects_list',True);

// ------------------------ end record declaration --------------------------------------------
				}
			}
			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}

		function show_delivery()
		{
			global $delivery_id;

			$this->set_app_langs();

			$this->t->set_file(array('del_list_t' => 'del_deliveryform.tpl'));
			$this->t->set_block('del_list_t','del_list','list');

			$error = $this->boprojects->check_prefs();
			if (is_array($error))
			{
				$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
			}
			else
			{
				$prefs = $this->boprojects->read_prefs();
        		$this->t->set_var('myaddress',$this->bodeliveries->get_address_data($prefs['abid']));
			}

			$this->t->set_var('site_title',$GLOBALS['phpgw_info']['site_title']);
			$charset = $GLOBALS['phpgw']->translation->translate('charset');
			$this->t->set_var('charset',$charset);
			$this->t->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);
			$this->t->set_var('img_src',$GLOBALS['phpgw_info']['server']['webserver_url'] . '/projects/doc/logo.jpg');

			$del = $this->bodeliveries->read_single_delivery($delivery_id);

			$this->t->set_var('customer',$this->bodeliveries->get_address_data($del['customer']));

			$del['date'] = $del['date'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$delivery_dateout = $GLOBALS['phpgw']->common->show_date($del['date'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			$this->t->set_var('delivery_date',$delivery_dateout);

			$this->t->set_var('delivery_num',$GLOBALS['phpgw']->strip_html($del['delivery_num']));
			$title = $GLOBALS['phpgw']->strip_html($del['title']);
			if (! $title) { $title  = '&nbsp;'; }
			$this->t->set_var('title',$title);

			$pos = 0;
			$hours = $this->bodeliveries->read_delivery_pos($delivery_id);

			if (is_array($hours))
			{
				while (list($null,$note) = each($hours))
				{
					$pos++;
					$this->t->set_var('pos',$pos);

					if ($note['sdate'] == 0)
					{
						$hours_dateout = '&nbsp;';
					}
					else
					{
						$note['sdate'] = $note['sdate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$hours_dateout = $GLOBALS['phpgw']->common->show_date($note['sdate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					$this->t->set_var('hours_date',$hours_dateout);

					if ($note['minperae'] != 0)
					{
						$aes = ceil($note['minutes']/$note['minperae']);
					}
					$sumaes += $aes;

					$this->t->set_var('aes',$aes);
					$act_descr = $GLOBALS['phpgw']->strip_html($note['descr']);
					if (! $act_descr) { $act_descr  = '&nbsp;'; }
					$this->t->set_var('act_descr',$act_descr);
					$this->t->set_var('billperae',$note['billperae']);
					$hours_descr = $GLOBALS['phpgw']->strip_html($note['hours_descr']);
					if (! $hours_descr) { $hours_descr  = '&nbsp;'; }
					$this->t->set_var('hours_descr',$hours_descr);
					$this->t->fp('list','del_list',True);
				}
			}
			$this->t->set_var('sumaes',$sumaes);

			$this->t->pfp('out','del_list_t',True);
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function fail()
		{
			echo '<p><center>' . lang('You have to CREATE a delivery or invoice first !');
			echo '</center>';
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}
?>
