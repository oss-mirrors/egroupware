<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* ------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_nextmatchs_class' => True);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('sub_list' => 'sub_list.tpl',
					'sub_list_t' => 'sub_list.tpl'));
	$t->set_block('sub_list_t','sub_list','list');

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');
	$grants[$phpgw_info['user']['account_id']] = PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="pro_parent" value="' . $pro_parent . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n";

	$t->set_var('lang_action',lang('Job list'));
	$t->set_var('add_url',$phpgw->link('/projects/add.php'));
	$t->set_var('search_url',$phpgw->link('/projects/sub_projects.php'));
	$t->set_var('project_action',$phpgw->link('/projects/sub_projects.php'));
	$t->set_var('hidden_vars',$hidden_vars);

	if (! $start) { $start = 0; }

	if ($pro_parent)
	{
		$pro = $projects->read_projects($start,True,$query,$filter,$sort,$order,'active',$cat_id,'subs',$pro_parent);
	}
	else
	{
		$projects->total_records = 0;
	}

//---------------------- nextmatch variable template-declarations ---------------------------

	$left = $phpgw->nextmatchs->left('/projects/sub_projects.php',$start,$projects->total_records);
	$right = $phpgw->nextmatchs->right('/projects/sub_projects.php',$start,$projects->total_records);
	$t->set_var('left',$left);
	$t->set_var('right',$right);

	$t->set_var('lang_showing',$phpgw->nextmatchs->show_hits($projects->total_records,$start));

// ------------------------------ end nextmatch template ------------------------------------

// ------------------list header variable template-declarations -------------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('sort_number',$phpgw->nextmatchs->show_sort_order($sort,'num',$order,'/projects/sub_projects.php',lang('Job ID')));
	$t->set_var('sort_status',$phpgw->nextmatchs->show_sort_order($sort,'status',$order,'/projects/sub_projects.php',lang('Status')));
	$t->set_var('sort_title',$phpgw->nextmatchs->show_sort_order($sort,'title',$order,'/projects/sub_projects.php',lang('Title')));
	$t->set_var('sort_start_date',$phpgw->nextmatchs->show_sort_order($sort,'start_date',$order,'/projects/sub_projects.php',lang('Start date')));
	$t->set_var('sort_end_date',$phpgw->nextmatchs->show_sort_order($sort,'end_date',$order,'/projects/sub_projects.php',lang('Date due')));
	$t->set_var('sort_coordinator',$phpgw->nextmatchs->show_sort_order($sort,'coordinator',$order,'/projects/sub_projects.php',lang('Coordinator')));
	$t->set_var('lang_h_hours',lang('Work hours'));
	$t->set_var('lang_edit',lang('Edit'));
	$t->set_var('lang_view',lang('View'));
	$t->set_var('lang_search',lang('Search'));
    $t->set_var('project_list',$projects->select_project_list('mains',$pro_parent));
    $t->set_var('lang_select_project',lang('Select main project'));

// -------------- end header declaration -----------------

	$d = CreateObject('phpgwapi.contacts');

	for ($i=0;$i<count($pro);$i++)
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$title = $phpgw->strip_html($pro[$i]['title']);
		if (! $title) $title = '&nbsp;';

		$number = $phpgw->strip_html($pro[$i]['number']);
		$status = lang($pro[$i]['status']);
		$t->set_var('tr_color',$tr_color);

		$end_date = $pro[$i]['end_date'];
		if ($end_date == 0) { $end_dateout = '&nbsp;'; }
		else
		{
			$month = $phpgw->common->show_date(time(),'n');
			$day = $phpgw->common->show_date(time(),'d');
			$year = $phpgw->common->show_date(time(),'Y');

			$end_date = $end_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$end_dateout = $phpgw->common->show_date($end_date,$phpgw_info['user']['preferences']['common']['dateformat']);
			if (mktime(2,0,0,$month,$day,$year) == $end_date) { $end_dateout = '<b>' . $end_dateout . '</b>'; }
			if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout = '<font color="CC0000"><b>' . $end_dateout . '</b></font>'; }
		}

		$start_date = $pro[$i]['start_date'];
		if ($start_date == 0) { $start_dateout = '&nbsp;'; }
		else
		{
			$month = $phpgw->common->show_date(time(),'n');
			$day = $phpgw->common->show_date(time(),'d');
			$year = $phpgw->common->show_date(time(),'Y');

			$start_date = $start_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$start_dateout = $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
		}

		$cached_data = $projects->cached_accounts($pro[$i]['coordinator']);
		$coordinatorout = $phpgw->strip_html($cached_data[$pro[$i]['coordinator']]['account_lid']
						. ' [' . $cached_data[$pro[$i]['coordinator']]['firstname'] . ' '
						. $cached_data[$pro[$i]['coordinator']]['lastname'] . ']');

		$id = $pro[$i]['id'];

// ------------------ template declaration for list records -----------------------------------

		$t->set_var(array('number' => $number,
						'start_date' => $start_dateout,
							'status' => $status,
							'title' => $title,
						'end_date' => $end_dateout,
					'coordinator' => $coordinatorout));

// ------------------------- end record declaration -------------------------------------------

		$t->set_var('hours',$phpgw->link('/projects/hours_listhours.php','project_id=' . $id)); 
		$t->set_var('lang_hours',lang('Work hours'));

		if ($projects->check_perms($grants[$pro[$i]['coordinator']],PHPGW_ACL_EDIT) || $pro[$i]['coordinator'] == $phpgw_info['user']['account_id'])
		{
			$t->set_var('edit',$phpgw->link('/projects/edit_sub.php','pro_parent=' . $pro_parent . '&id=' . $id . '&cat_id=' . $cat_id . '&sort=' . $sort . '&order=' . $order
											. '&query=' . $query . '&start=' . $start . '&filter=' . $filter));
			$t->set_var('lang_edit_entry',lang('Edit'));
		}
		else
		{
			$t->set_var('edit','');
			$t->set_var('lang_edit_entry','&nbsp;');
		}

		$t->set_var('view',$phpgw->link('/projects/view.php','id=' . $id . '&sort=' . $sort . '&order=' . $order . '&query=' . $query
										. '&start=' . $start . '&filter=' . $filter . '&cat_id=' . $cat_id . '&pro_parent=' . $pro_parent));
		$t->set_var('lang_view_entry',lang('View'));

		$t->parse('list','sub_list',True);
	}

// ------------------ template declaration for Add Form ---------------------------------------

	if ($pro_parent && $pro_parent != 0)
	{
		$parent = $projects->read_single_project($pro_parent);
	}

	if ($projects->check_perms($grants[$parent[0]['coordinator']],PHPGW_ACL_ADD) || $parent[0]['coordinator'] == $phpgw_info['user']['account_id'])
	{
		$t->set_var('add','<form method="POST" action="' . $phpgw->link('/projects/add_sub.php','pro_parent=' . $pro_parent . '&cat_id=' . $cat_id . '&start=' . $start . '&sort=' . $sort
					. '&order=' . $order . '&query=' . $query . '&filter=' . $filter) . '"><input type="submit" name="Add" value="' . lang('Add') .'"></form>');
	}
	else
	{
		$t->set_var('add','');
	}

	$t->parse('out','sub_list_t',True);
	$t->p('out');

// ---------------------- end Add form declaration --------------------------------------------

	$phpgw->common->phpgw_footer();
?>
