<?php
	/**************************************************************************\
	* phpGroupWare - projects/projecthours                                     *
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
	$t->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
	$t->set_block('hours_list_t','hours_list','list');

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n";

	if (! $start) { $start = 0; }
	if ($order) { $ordermethod = "order by $order $sort"; }
	else { $ordermethod = "order by phpgw_p_hours.start_date asc"; }

	if (!$status) { $statussort = " (status='open' OR status='done' OR status='billed' OR status='closed') "; } 
	else { $statussort = " status='$status' "; }

	if ($access == 'private') { $filtermethod = " AND employee='" . $phpgw_info['user']['account_id'] . "' "; }

	if ($query)
	{
		$querymethod = "AND (remark like '%$query%' OR start_date like '%$query%' OR end_date like '%$query%' OR minutes like '%$query%') ";
	}

	if (! $filter)
	{
		$phpgw->db->query("SELECT project_id from phpgw_p_hours WHERE $statussort $filtermethod $querymethod");
		$phpgw->db->next_record();
		$pro = $projects->read_single_project($phpgw->db->f("project_id"));
		if ($pro)
		{
			if ($projects->check_perms($grants[$pro[0]['coordinator']],PHPGW_ACL_READ) || $pro[0]['coordinator'] == $phpgw_info['user']['account_id'])
			{
				$filter = $phpgw->db->f('project_id');
			}
		}
		else { $filter = "999"; }
	}
	else { $pro = $projects->read_single_project($filter); }

	$hours = $projects->read_hours($start,True,$query,$filter,$sort,$order,$access,$status);

// ------------ nextmatch variable template-declarations ----------------------------

	$left = $phpgw->nextmatchs->left('/projects/hours_listhours.php',$start,$projects->total_records);
	$right = $phpgw->nextmatchs->right('/projects/hours_listhours.php',$start,$projects->total_records);
	$t->set_var('left',$left);
	$t->set_var('right',$right);

	$t->set_var('lang_showing',$phpgw->nextmatchs->show_hits($projects->total_records,$start));

// ----------------------- end nextmatch template -------------------------------------

// ---------------- list header variable template-declarations ------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('sort_activity',$phpgw->nextmatchs->show_sort_order($sort,'a.descr',$order,'/projects/hours_listhours.php',lang('Activity')));
	$t->set_var('sort_hours_descr',$phpgw->nextmatchs->show_sort_order($sort,'h.hours_descr',$order,'/projects/hours_listhours.php',lang('Job')));
	$t->set_var('sort_status',$phpgw->nextmatchs->show_sort_order($sort,'h.status',$order,'/projects/hours_listhours.php',lang("Status")));
	$t->set_var('sort_start_date',$phpgw->nextmatchs->show_sort_order($sort,'h.start_date',$order,'/projects/hours_listhours.php',lang('Work date')));
	$t->set_var('sort_start_time',$phpgw->nextmatchs->show_sort_order($sort,'h.start_date',$order,'/projects/hours_listhours.php',lang('Start time')));
	$t->set_var('sort_end_time',$phpgw->nextmatchs->show_sort_order($sort,'h.end_date',$order,'/projects/hours_listhours.php',lang('End time')));
	$t->set_var('sort_hours',$phpgw->nextmatchs->show_sort_order($sort,'h.minutes',$order,'/projects/hours_listhours.php',lang('Hours')));
	$t->set_var('h_lang_edit',lang('Edit'));
	$t->set_var('h_lang_view',lang('View'));
	$t->set_var('lang_action',lang('Job list'));
	$t->set_var('lang_search',lang('Search'));
	$t->set_var('search_action',$phpgw->link('/projects/hours_listhours.php'));
	$t->set_var('project_action',$phpgw->link('/projects/hours_listhours.php'));
	$t->set_var('lang_submit',lang('Submit'));
	$t->set_var('project_list',$projects->select_project_list($filter));
	$t->set_var('lang_select_project',lang('Select project'));

// -------------- end header declaration -----------------

	for ($i=0;$i<count($hours);$i++)
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$activity  = $phpgw->strip_html($hours[$i]['descr']);                                                                                                                             
		if (! $activity) $activity = '&nbsp;';                                                                                                                                                

		$hours_descr = $phpgw->strip_html($hours[$i]['hours_descr']);                                                                                                                             
		if (! $hours_descr) $hours_descr = '&nbsp;';                                                                                                                                                

		$status = $hours[$i]['status'];
		$statusout = lang($status);
		$t->set_var('tr_color',$tr_color);

		$ampm = 'am';

		$start_date = $hours[$i]['start_date'];
		if ($start_date == 0)
		{
			$start_dateout = '&nbsp;';
			$start_time = '&nbsp;';
		}
		else
		{
			$smonth = $phpgw->common->show_date(time(),'n');
			$sday = $phpgw->common->show_date(time(),'d');
			$syear = $phpgw->common->show_date(time(),'Y');
			$shour = date('H',$start_date);
			$smin = date('i',$start_date);

			$start_date = $start_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$start_dateout = $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
			$start_timeout = $phpgw->common->formattime($shour,$smin);
		}

		$end_date = $hours[$i]['end_date'];
		if ($end_date == 0) { $end_timeout = '&nbsp;'; }
		else
		{
			$emonth = $phpgw->common->show_date(time(),'n');
			$eday = $phpgw->common->show_date(time(),'d');
			$eyear = $phpgw->common->show_date(time(),'Y');
			$ehour = date('H',$end_date);
			$emin = date('i',$end_date);

			$end_timeout = $phpgw->common->formattime($ehour,$emin);
		}

		$minutes = floor($hours[$i]['minutes']/60) . ':'
				. sprintf ("%02d",(int)($hours[$i]['minutes']-floor($hours[$i]['minutes']/60)*60));

		$id = $hours[$i]['id'];

// ---------------- template declaration for list records ------------------------------

		$t->set_var(array('activity' => $activity,
						'hours_descr' => $hours_descr,
							'status' => $statusout,
						'start_date' => $start_dateout,
						'start_time' => $start_timeout,
							'end_time' => $end_timeout,
							'minutes' => $minutes));

		if (($status != 'billed') && ($status != 'closed'))
		{
			if ($projects->check_perms($grants[$hours[$i]['employee']],PHPGW_ACL_EDIT) || $hours[$i]['employee'] == $phpgw_info['user']['account_id'])
			{
				$t->set_var('edit',$phpgw->link('/projects/hours_edithour.php','id=' . $id . '&filter=' . $filter . '&order=' . $order
												. '&query=' . $query . '&start=' . $start . '&sort=' . $sort));
				$t->set_var('lang_edit',lang('Edit'));
			}
		}
		else
		{
			$t->set_var('edit','');
			$t->set_var('lang_edit','&nbsp;');
		}

		$t->set_var('view',$phpgw->link('/projects/viewhours.php','id=' . $id . '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start
											. '&filter=' . $filter));
		$t->set_var('lang_view',lang('View'));

		$t->parse('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------

	}

	if ($projects->check_perms($grants[$pro[0]['coordinator']],PHPGW_ACL_ADD) || $pro[0]['coordinator'] == $phpgw_info['user']['account_id'])
	{
		$t->set_var('action','<form method="POST" action="' . $phpgw->link('/projects/hours_addhour.php','filter=' . $filter) . '"><input type="submit" value="' . lang('Add') .'"></form>');
	}
	else { $t->set_var('action',''); }

	$t->parse('out','hours_list_t',True);
	$t->p('out');

	$phpgw->common->phpgw_footer();
?>
