<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          * 
	* ------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_nextmatchs_class' => True,
					'enable_categories_class' => True);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('projects_list' => 'archive.tpl',
					'projects_list_t' => 'archive.tpl'));
    $t->set_block('projects_list_t','projects_list','list');

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="cat_id" value="' . $cat_id . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n";

	$t->set_var('lang_action',lang('Project archive'));
	$t->set_var('searchurl',$phpgw->link('/projects/archive.php'));
	$t->set_var('cat_url',$phpgw->link('/projects/archive.php'));
	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('category_list',$phpgw->categories->formated_list('select','all',$cat_id,'True'));
	$t->set_var('lang_all',lang('All'));
	$t->set_var('lang_category',lang('Category'));

	if (! $start) { $start = 0; }

	if($phpgw_info['user']['preferences']['common']['maxmatchs'] && $phpgw_info['user']['preferences']['common']['maxmatchs'] > 0)
	{
		$limit = $phpgw_info['user']['preferences']['common']['maxmatchs'];
	}
	else { $limit = 15; }

	$pro = $projects->read_projects($start,True,$query,$filter,$sort,$order,'archive',$cat_id);

//---------------------- nextmatch variable template-declarations ---------------------------

	$left = $phpgw->nextmatchs->left('/projects/archive.php',$start,$projects->total_records);
	$right = $phpgw->nextmatchs->right('/projects/archive.php',$start,$projects->total_records);
	$t->set_var('left',$left);
	$t->set_var('right',$right);

	if ($projects->total_records > $limit)
	{
		$t->set_var('lang_showing',lang('showing x - x of x',($start + 1),($start + $limit),$projects->total_records));
	}
	else
	{
		$t->set_var('lang_showing',lang('showing x',$projects->total_records));
	}

// ------------------------------ end nextmatch template ------------------------------------

// ------------------list header variable template-declarations -------------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('sort_number',$phpgw->nextmatchs->show_sort_order($sort,'num',$order,'/projects/archive.php',lang('Project ID')));
	$t->set_var('sort_customer',$phpgw->nextmatchs->show_sort_order($sort,'customer',$order,'/projects/archive.php',lang('Customer')));
	$t->set_var('sort_title',$phpgw->nextmatchs->show_sort_order($sort,'title',$order,'/projects/archive.php',lang('Title')));
	$t->set_var('sort_end_date',$phpgw->nextmatchs->show_sort_order($sort,'end_date',$order,'/projects/archive.php',lang('Date due')));
	$t->set_var('sort_coordinator',$phpgw->nextmatchs->show_sort_order($sort,'coordinator',$order,'/projects/archive.php',lang('Coordinator')));
	$t->set_var('lang_jobs',lang('Jobs'));
	$t->set_var('lang_invoice',lang('Invoices'));
	$t->set_var('lang_delivery',lang('Deliveries'));
	$t->set_var('lang_stats',lang('Statistics'));
	$t->set_var('lang_edit',lang('Edit'));
	$t->set_var('lang_search',lang('Search'));

// -------------- end header declaration -----------------

	$d = CreateObject('phpgwapi.contacts');

	for ($i=0;$i<count($pro);$i++)
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$title = $phpgw->strip_html($pro[$i]['title']);
		if (! $title)  $title  = '&nbsp;';
		$number = $phpgw->strip_html($pro[$i]['number']);
		$status = lang($pro[$i]['status']);
		$t->set_var('tr_color',$tr_color);

		$end_date = $pro[$i]['end_date'];
		if ($end_date == 0)
		{
			$end_dateout = '&nbsp;';
		}
		else
		{
			$month = $phpgw->common->show_date(time(),'n');
			$day = $phpgw->common->show_date(time(),'d');
			$year = $phpgw->common->show_date(time(),'Y');

			$end_date = $end_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$end_dateout =  $phpgw->common->show_date($end_date,$phpgw_info['user']['preferences']['common']['dateformat']);
			if (mktime(2,0,0,$month,$day,$year) == $end_date) { $end_dateout = '<b>' . $end_dateout . '</b>'; }
			if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout = '<font color="CC0000"><b>' . $end_dateout . '</b></font>'; }
		}

		$ab_customer = $pro[$i]['customer'];
		if (!$ab_customer)
		{
			$customerout = '&nbsp;';
		}
		else
		{
			$cols = array('n_given' => 'n_given',
						'n_family' => 'n_family',
						'org_name' => 'org_name');
			$customer = $d->read_single_entry($ab_customer,$cols);    
			if ($customer[0]['org_name'] == '')
			{
				$customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family'];
			}
			else
			{
				$customerout = $customer[0]['org_name'];
			}
		}

		$coordinatorout = $pro[$i]['lid'] . ' [ ' . $pro[$i]['firstname'] . ' ' . $pro[$i]['lastname'] . ' ]';

		$id = $pro[$i]['id'];

// ------------------ template declaration for list records -----------------------------------
      
		$t->set_var(array('number' => $number,
						'customer' => $customerout,
							'title' => $title,
						'end_date' => $end_dateout,
					'coordinator' => $coordinatorout));

// ------------------------- end record declaration -------------------------------------------

		$t->set_var('jobs',$phpgw->link('/projects/hours_listhours.php',"filter=$id"));
		$t->set_var('delivery',$phpgw->link('/projects/del_deliverylist.php',"project_id=$id"));
		$t->set_var('invoice',$phpgw->link('/projects/bill_invoicelist.php',"project_id=$id"));
		$t->set_var('stats',$phpgw->link('/projects/stats_projectstat.php',"id=$id")); 

		if ($projects->check_perms($grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $phpgw_info['user']['account_id'])
		{
			$t->set_var('edit',$phpgw->link('/projects/edit.php',"id=$id"));
			$t->set_var('lang_edit_entry',lang('Edit'));
		}
		else
		{
			$t->set_var('edit','');
			$t->set_var('lang_edit_entry','&nbsp;');
		}

		$t->parse('list','projects_list',True);
	}

	$t->parse('out','projects_list_t',True);
	$t->p('out');

	$phpgw->common->phpgw_footer();
?>
