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

	class uibilling
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
			'invoice'			=> True,
			'list_invoices'		=> True,
			'fail'				=> True,
			'show_invoice'		=> True
		);

		function uibilling()
		{
			global $action;

			$this->boprojects				= CreateObject('projects.boprojects',True, $action);
			$this->bobilling				= CreateObject('projects.bobilling');
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
			$this->t->set_var('lang_projects',lang('Projects'));
			$this->t->set_var('lang_project',lang('Project'));
			$this->t->set_var('lang_jobs',lang('Jobs'));
			$this->t->set_var('lang_title',lang('Title'));
			$this->t->set_var('lang_status',lang('Status'));
			$this->t->set_var('lang_customer',lang('Customer'));
			$this->t->set_var('lang_coordinator',lang('Coordinator'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_hours',lang('Work hours'));
			$this->t->set_var('lang_minperae',lang('Minutes per workunit'));
			$this->t->set_var('lang_invoices',lang('Invoices'));
			$this->t->set_var('lang_invoice_num',lang('Invoice ID'));
			$this->t->set_var('lang_invoice_date',lang('Invoice date'));
			$this->t->set_var('lang_stats',lang('Statistics'));
			$this->t->set_var('lang_activity',lang('Activity'));
			$this->t->set_var('lang_aes',lang('Workunits'));
			$this->t->set_var('lang_billperae',lang('Bill per workunit'));
			$this->t->set_var('lang_sum',lang('Sum'));
			$this->t->set_var('lang_print_invoice',lang('Print invoice'));
			$this->t->set_var('lang_netto',lang('Sum net'));
			$this->t->set_var('lang_tax',lang('tax'));
			$this->t->set_var('lang_position',lang('Position'));
			$this->t->set_var('lang_work_date',lang('Work date'));
			$this->t->set_var('lang_workunits',lang('Workunits'));
			$this->t->set_var('lang_per',lang('per workunit'));
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

		function format_tax($tax = '')
		{
			$comma = strrpos($tax,',');
			if (is_string($comma) && !$comma)
			{
				$newtax = $tax;
			}
			else
			{
				$newtax = str_replace(',','.',$tax);
			}
			return $newtax;
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
				'menuaction'	=> 'projects.uibilling.list_projects',
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
				$this->t->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
				$lang_action = '<td width="5%" align="center">' . lang('Jobs') . '</td>' . "\n";
				$this->t->set_var('lang_action',$lang_action);
			}
			else
			{
				$this->t->set_var('sort_action',$this->nextmatchs->show_sort_order($this->sort,'start_date',$this->order,'/index.php',lang('Start date'),$link_data));
				$this->t->set_var('lang_action','');
			}

			$this->t->set_var('sort_status',$this->nextmatchs->show_sort_order($this->sort,'status',$this->order,'/index.php',lang('Status'),$link_data));
			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var('sort_end_date',$this->nextmatchs->show_sort_order($this->sort,'end_date',$this->order,'/index.php',lang('Date due'),$link_data));
			$this->t->set_var('sort_coordinator',$this->nextmatchs->show_sort_order($this->sort,'coordinator',$this->order,'/index.php',lang('Coordinator'),$link_data));
			$this->t->set_var('h_lang_part',lang('Invoice'));
			$this->t->set_var('h_lang_partlist',lang('Invoice list'));

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
				$link_data['menuaction'] = 'projects.uibilling.invoice';

				$this->t->set_var('part',$GLOBALS['phpgw']->link('/index.php',$link_data));
				$this->t->set_var('lang_part',lang('Invoice'));

				$this->t->set_var('partlist',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_invoices&action=bill'
											. '&project_id=' . $pro[$i]['project_id']));
				$this->t->set_var('lang_partlist',lang('Invoice list'));

				if ($action == 'mains')
				{
					$action_entry = '<td align="center"><a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&pro_parent='
																. $pro[$i]['project_id'] . '&action=subs') . '">' . lang('Jobs')
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

			$this->t->set_var('lang_all_partlist',lang('All invoices'));
			$this->t->set_var('all_partlist',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_invoices&action=bill'
											. '&project_id='));
			$this->t->set_var('lang_all_part2list','');
			$this->t->set_var('all_part2list','');

			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata($action);
		}

		function list_invoices()
		{
			global $project_id, $action;

			$this->display_app_header();

			$this->t->set_file(array('projects_list_t' => 'bill_listinvoice.tpl'));
			$this->t->set_block('projects_list_t','projects_list','list');

			$link_data = array
			(
				'menuaction'	=> 'projects.uibilling.list_invoices',
				'action'		=> $action,
				'project_id'	=> $project_id
			);

			if (!$this->start)
			{
				$this->start = 0;
			}

			$this->t->set_var('lang_action',lang('Invoice list'));
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

			$bill = $this->bobilling->read_invoices($this->start, $this->query, $this->sort, $this->order, True, $project_id);

// -------------------- nextmatch variable template-declarations -----------------------------

			$left = $this->nextmatchs->left('/index.php',$this->start,$this->bobilling->total_records,$link_data);
			$right = $this->nextmatchs->right('/index.php',$this->start,$this->bobilling->total_records,$link_data);
			$this->t->set_var('left',$left);
			$this->t->set_var('right',$right);

			$this->t->set_var('lang_showing',$this->nextmatchs->show_hits($this->bobilling->total_records,$this->start));

// ------------------------ end nextmatch template -------------------------------------------

// ------------- list header variable template-declarations ------------------

			$this->t->set_var('sort_num',$this->nextmatchs->show_sort_order($this->sort,'num',$this->order,'/index.php',lang('Invoice ID'),$link_data));
			$this->t->set_var('sort_customer',$this->nextmatchs->show_sort_order($this->sort,'customer',$this->order,'/index.php',lang('Customer'),$link_data));
			$this->t->set_var('sort_title',$this->nextmatchs->show_sort_order($this->sort,'title',$this->order,'/index.php',lang('Title'),$link_data));
			$this->t->set_var('sort_date',$this->nextmatchs->show_sort_order($this->sort,'date',$this->order,'/index.php',lang('Date'),$link_data));
			$this->t->set_var('sort_sum','<td width="10%" align="right" bgcolor="' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '">'
			. $currency . '&nbsp;' . $this->nextmatchs->show_sort_order($this->sort,'sum',$this->order,'/index.php',lang('Sum'),$link_data) . '</td>');
			$this->t->set_var('lang_data',lang('Invoice'));

// ----------------------- end header declaration -----------------------------

			if (is_array($bill))
			{
				while (list($null,$inv) = each($bill))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);
					$title = $GLOBALS['phpgw']->strip_html($inv['title']);
					if (! $title) $title = '&nbsp;';

					$date = $inv['date'];
					if ($date == 0)
						$dateout = '&nbsp;';
					else
					{
						$date = $date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$dateout = $GLOBALS['phpgw']->common->show_date($date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					if ($inv['customer'] != 0) 
					{
						$customer = $this->boprojects->read_single_contact($inv['customer']);
            			if (!$customer[0]['org_name']) { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            			else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
					}
					else { $customerout = '&nbsp;'; }

					$this->t->set_var('sum','<td align="right">' . $inv['sum'] . '</td>');

// --------------------- template declaration for list records ----------------------------

					$this->t->set_var(array('num' => $GLOBALS['phpgw']->strip_html($inv['invoice_num']),
										'customer' => $customerout,
										'title' => $title,
										'date' => $dateout));

					$link_data['invoice_id']	= $inv['invoice_id'];
					$link_data['project_id']	= $inv['project_id'];
					$link_data['menuaction']	= 'projects.uibilling.invoice';
					$this->t->set_var('td_data',$GLOBALS['phpgw']->link('/index.php',$link_data));
					$this->t->set_var('lang_td_data',lang('Invoice'));
					$this->t->fp('list','projects_list',True);

// ------------------------- end record declaration --------------------------------------
				}
			}
			$this->t->pfp('out','projects_list_t',True);
			$this->save_sessiondata('bill');
		}

		function invoice()
		{
			global $action, $Invoice, $project_id, $action, $invoice_id, $values, $select, $referer;

			if (! $Invoice)
			{
				$referer = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'] : $GLOBALS['HTTP_REFERER'];
			}

			if (!$project_id)
			{
				Header('Location: ' . $referer);
			}

			$this->display_app_header();

			$this->t->set_file(array('hours_list_t' => 'bill_listhours.tpl'));
			$this->t->set_block('hours_list_t','hours_list','list');

			$nopref = $this->boprojects->check_prefs();
			if (is_array($nopref))
			{
				$this->t->set_var('pref_message',$GLOBALS['phpgw']->common->error_list($nopref));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
			}

			if ($Invoice)
			{
				$values['project_id']	= $project_id;

				$pro = $this->boprojects->read_single_project($project_id);
				$values['customer']		= $pro['customer'];

				$error = $this->bobilling->check_values($values,$select);
				if (is_array($error))
				{
					$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
				}
				else
				{
					if ($invoice_id)
					{
						$values['invoice_id'] = $invoice_id;
						$this->bobilling->update_invoice($values,$select);
					}
					else
					{
						$invoice_id = $this->bobilling->invoice($values,$select);
					}
				}
			}

			$link_data = array
			(
				'menuaction'	=> 'projects.uibilling.invoice',
				'action'		=> $action,
				'project_id'	=> $project_id,
				'invoice_id'	=> $invoice_id,
				'action'		=> $action
			);

			$this->t->set_var('lang_action',lang('Invoice'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('currency',$prefs['currency']);

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

			if (!$invoice_id)
			{
				$this->t->set_var('lang_choose',lang('Generate Invoice ID ?'));
				$this->t->set_var('choose','<input type="checkbox" name="values[choose]" value="True">');
				$this->t->set_var('print_invoice',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.fail'));
				$this->t->set_var('invoice_num',$values['invoice_num']);
				$hours = $this->bobilling->read_hours($project_id, $action);
			}
			else
			{
				$this->t->set_var('lang_choose','');
				$this->t->set_var('choose','');
				$this->t->set_var('print_invoice',$GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.show_invoice'
																		. '&invoice_id=' . $invoice_id));
				$bill = $this->bobilling->read_single_invoice($invoice_id);
				$this->t->set_var('invoice_num',$bill['invoice_num']);
				$hours = $this->bobilling->read_invoice_hours($project_id, $invoice_id, $action);
			}

			if ($bill['date'])
			{
				$values['month'] = date('m',$bill['date']);
				$values['day'] = date('d',$bill['date']);
				$values['year'] = date('Y',$bill['date']);
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
				while (list($null,$inv) = each($hours))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);

					$select = '<input type="checkbox" name="select[' . $inv['hours_id'] . ']" value="True" checked>';

					$activity = $GLOBALS['phpgw']->strip_html($inv['descr']);
					if (! $activity)  $activity  = '&nbsp;';

					$hours_descr = $GLOBALS['phpgw']->strip_html($inv['hours_descr']);
					if (! $hours_descr)  $hours_descr  = '&nbsp;';

					$start_date = $inv['sdate'];
					if ($start_date == 0) { $start_dateout = '&nbsp;'; }
					else
					{
						$start_date = $start_date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$start_dateout = $GLOBALS['phpgw']->common->show_date($start_date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					if ($inv['minperae'] != 0)
					{
						$aes = ceil($inv['minutes']/$inv['minperae']);
					}
					$sumaes += $aes;
					$summe += $inv['billperae']*$aes;

// --------------------- template declaration for list records ---------------------------

					$this->t->set_var(array('select' => $select,
										'activity' => $activity,
									'hours_descr' => $hours_descr,
										'status' => lang($inv['status']),
									'start_date' => $start_dateout,
											'aes' => $aes,
									'billperae' => $inv['billperae'],
										'sum' => sprintf ("%01.2f",$inv['billperae']*$aes)));

					if (($inv['status'] != 'billed') && ($inv['status'] != 'closed'))
					{
						if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_EDIT) || $pro['coordinator'] == $this->account)
						{
							$link_data['menuaction']	= 'projects.uiprojecthours.edit_hours';
							$link_data['hours_id']		= $inv['hours_id'];
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

			if ($invoice_id && ($action != 'amains') && ($action != 'asubs'))
			{
				$hours = $this->bobilling->read_hours($project_id, $action);
				if (is_array($hours))
				{
					while (list($null,$inv) = each($hours))
					{
						$this->nextmatchs->template_alternate_row_color(&$this->t);

						$select = '<input type="checkbox" name="select[' . $inv['hours_id'] . ']" value="True">';

						$activity = $GLOBALS['phpgw']->strip_html($inv['descr']);
						if (! $activity)  $activity  = '&nbsp;';

						$hours_descr = $GLOBALS['phpgw']->strip_html($inv['hours_descr']);
						if (! $hours_descr)  $hours_descr  = '&nbsp;';

						$start_date = $inv['sdate'];
						if ($start_date == 0) { $start_dateout = '&nbsp;'; }
						else
						{
							$start_date = $start_date + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
							$start_dateout = $GLOBALS['phpgw']->common->show_date($start_date,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
						}

						if ($inv['minperae'] != 0)
						{
							$aes = ceil($inv['minutes']/$inv['minperae']);
						}
					//	$sumaes += $aes;
					//	$summe += $inv['billperae']*$aes;

// --------------------- template declaration for list records ---------------------------

						$this->t->set_var(array('select' => $select,
											'activity' => $activity,
										'hours_descr' => $hours_descr,
											'status' => lang($inv['status']),
										'start_date' => $start_dateout,
												'aes' => $aes,
										'billperae' => $inv['billperae'],
											'sum' => sprintf ("%01.2f",$inv['billperae']*$aes)));

						if (($inv['status'] != 'billed') && ($inv['status'] != 'closed'))
						{
							if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_EDIT) || $pro['coordinator'] == $this->account)
							{
								$link_data['menuaction']	= 'projects.uiprojecthours.edit_hours';
								$link_data['hours_id']		= $inv['hours_id'];
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

			$this->t->set_var('sum_aes',$sumaes);
			$this->t->set_var('sum_sum',sprintf("%01.2f",$summe));

			if (! $invoice_id)
			{
				if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
				{
					$this->t->set_var('invoice','<input type="submit" name="Invoice" value="' . lang('Create invoice') . '">');
				}
			}
 			else
			{
				if ($this->boprojects->check_perms($this->grants[$pro['coordinator']],PHPGW_ACL_ADD) || $pro['coordinator'] == $this->account)
				{
					$this->t->set_var('invoice','<input type="submit" name="Invoice" value="' . lang('Update invoice') . '">');
				}
			}

			if ($action == 'amains' || $action == 'asubs')
			{
				$this->t->set_var('invoice','');
			}

			$this->t->pfp('out','hours_list_t',True);
		}

		function fail()
		{
			echo '<p><center>' . lang('You have to CREATE a delivery or invoice first !');
			echo '</center>';
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function show_invoice()
		{
			global $invoice_id;

			$this->set_app_langs();

			$this->t->set_file(array('bill_list_t' => 'bill_invoiceform.tpl'));
			$this->t->set_block('bill_list_t','bill_list','list');

			$error = $this->boprojects->check_prefs();
			if (is_array($error))
			{
				$this->t->set_var('message',$GLOBALS['phpgw']->common->error_list($error));
			}
			else
			{
				$prefs = $this->boprojects->get_prefs();
				$this->t->set_var('currency',$prefs['currency']);
				$this->t->set_var('myaddress',$this->bodeliveries->get_address_data($prefs['abid']));
			}

			$this->t->set_var('site_title',$GLOBALS['phpgw_info']['site_title']);
			$charset = $GLOBALS['phpgw']->translation->translate('charset');
			$this->t->set_var('charset',$charset);
			$this->t->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);
			$this->t->set_var('img_src',$GLOBALS['phpgw_info']['server']['webserver_url'] . '/projects/doc/logo.jpg');

			$bill = $this->bobilling->read_single_invoice($invoice_id);

			$this->t->set_var('customer',$this->bodeliveries->get_address_data($bill['customer']));

			$bill['date'] = $bill['date'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$invoice_dateout = $GLOBALS['phpgw']->common->show_date($bill['date'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			$this->t->set_var('invoice_date',$invoice_dateout);

			$this->t->set_var('invoice_num',$GLOBALS['phpgw']->strip_html($bill['invoice_num']));
			$title = $GLOBALS['phpgw']->strip_html($bill['title']);
			if (! $title) { $title  = '&nbsp;'; }
			$this->t->set_var('title',$title);

			$pos = 0;
			$sum_netto = 0;
			$hours = $this->bobilling->read_invoice_pos($invoice_id);

			if (is_array($hours))
			{
				while (list($null,$inv) = each($hours))
				{
					$pos++;
					$this->t->set_var('pos',$pos);

					if ($inv['sdate'] == 0)
					{
						$hours_dateout = '&nbsp;';
					}
					else
					{
						$inv['sdate'] = $inv['sdate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
						$hours_dateout = $GLOBALS['phpgw']->common->show_date($inv['sdate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
					}

					$this->t->set_var('hours_date',$hours_dateout);

					if ($inv['minperae'] != 0)
					{
						$aes = ceil($inv['minutes']/$inv['minperae']);
					}
					$sumaes += $aes;
					$sumpos = $inv['billperae']*$aes;

					$this->t->set_var('billperae',$inv['billperae']);
					$this->t->set_var('sumpos',$sumpos);
					$this->t->set_var('aes',$aes);

					$act_descr = $GLOBALS['phpgw']->strip_html($inv['descr']);
					if (! $act_descr) { $act_descr  = '&nbsp;'; }
					$this->t->set_var('act_descr',$act_descr);

					$this->t->set_var('billperae',$inv['billperae']);

					$hours_descr = $GLOBALS['phpgw']->strip_html($inv['hours_descr']);
					if (! $hours_descr) { $hours_descr  = '&nbsp;'; }
					$this->t->set_var('hours_descr',$hours_descr);

        			$sum_netto += $sumpos;

					$this->t->fp('list','bill_list',True);
				}
			}
			/*	if ($sum == $sum_netto) { $t->set_var('error_hint',''); }
			else { $t->set_var('error_hint',lang('Error in calculation sum does not match !')); } */
			$this->t->set_var('error_hint','');

			$tax = $this->format_tax($prefs['tax']);
			$taxpercent = ($tax/100);
			$this->t->set_var('tax',$taxpercent);
			$sum_tax = round($sum_netto*$taxpercent,2);
			$this->t->set_var('sum_netto',sprintf("%01.2f",$sum_netto));
			$this->t->set_var('sum_tax',$sum_tax);
			$sum_sum = $sum_tax + $sum_netto;
			$this->t->set_var('sum_sum',sprintf("%01.2f",$sum_sum));
			$this->t->set_var('sumaes',$sumaes);

			$this->t->pfp('out','bill_list_t',True);
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}
?>
