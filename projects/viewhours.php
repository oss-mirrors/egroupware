<?php
	/**************************************************************************\
	* phpGroupWare - projects/projecthours                                     *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
	* --------------------------------------------------------                 *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_nextmatchs_class' => True);
	include('../header.inc.php');

	if (!$submit) { $referer = $HTTP_REFERER; }

	if (!$id) { Header('Location: ' . $HTTP_REFERER); }

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="referer" value="' . $referer . '">' . "\n"
				. '<input type="hidden" name="id" value="' . $id . '">' . "\n"
				. '<input type="hidden" name="delivery_id" value="' . $delivery_id '">' . "\n"
				. '<input type="hidden" name="invoice_id" value="' . $invoice_id . '">' . "\n";

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('hours_view' => 'hours_view.tpl'));

	$projects = CreateObject('projects.projects');

	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
		$t->set_var('currency',$currency);
	}
	else
	{
		$t->set_var('error',lang('Please select your currency in preferences !'));
	}

	$t->set_var('doneurl',$referer);
	$t->set_var('lang_action',lang('View job'));
	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('lang_project',lang('Project'));
	$t->set_var('lang_activity',lang('Activity'));
	$t->set_var('lang_work_date',lang('Work date'));
	$t->set_var('lang_start_date',lang('Start date'));
	$t->set_var('lang_end_date',lang('End date'));
	$t->set_var('lang_work_time',lang('Work time'));
	$t->set_var('lang_start_time',lang('Start time'));
	$t->set_var('lang_end_time',lang('End time'));
	$t->set_var('lang_remark',lang('Remark'));
	$t->set_var('lang_descr',lang('Short description'));
	$t->set_var('lang_hours',lang('Hours')); 
	$t->set_var('lang_employee',lang('Employee'));
	$t->set_var('lang_minperae',lang('Minutes per workunit'));
	$t->set_var('lang_billperae',lang('Bill per workunit'));
	$t->set_var('lang_done',lang('Done'));
	$t->set_var('lang_status',lang('Status'));
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);
	$t->set_var('tr_color1',$phpgw_info['theme']['row_on']);
	$t->set_var('tr_color2',$phpgw_info['theme']['row_off']);

	$phpgw->db->query("select * from phpgw_p_hours where id='$id'");
	$phpgw->db->next_record();

	$t->set_var('status',lang($phpgw->db->f('status')));

	$sdate = $phpgw->db->f('start_date');
	if ($sdate == 0)
	{
		$sdateout = '&nbsp;';
		$stimeout = '&nbsp;';
	}
	else
	{
		$smonth = $phpgw->common->show_date(time(),'n');
		$sday = $phpgw->common->show_date(time(),'d');
		$syear = $phpgw->common->show_date(time(),'Y');
		$shour = date('H',$sdate);
		$smin = date('i',$sdate);

		$sdate = $sdate + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
		$sdateout = $phpgw->common->show_date($sdate,$phpgw_info['user']['preferences']['common']['dateformat']);
		$stimeout = $phpgw->common->formattime($shour,$smin);
	}

	$t->set_var('sdate',$sdateout);
	$t->set_var('stime',$stimeout);

	$edate = $phpgw->db->f('end_date');

	if ($edate == 0)
	{
		$edateout = '&nbsp;';
		$etimeout = '&nbsp;';
	}
	else
	{
		$emonth = $phpgw->common->show_date(time(),'n');
		$eday = $phpgw->common->show_date(time(),'d');
		$eyear = $phpgw->common->show_date(time(),'Y');
		$ehour = date('H',$edate);
		$emin = date('i',$edate);

		$edate = $edate + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
		$edateout = $phpgw->common->show_date($edate,$phpgw_info['user']['preferences']['common']['dateformat']);
		$etimeout = $phpgw->common->formattime($ehour,$emin);
	}

	$t->set_var('edate',$edateout);
	$t->set_var('etime',$etimeout);

	$t->set_var('remark',$phpgw->strip_html($phpgw->db->f('remark')));
	$t->set_var('hours_descr',$phpgw->strip_html($phpgw->db->f('hours_descr')));

	$t->set_var('hours',floor($phpgw->db->f('minutes')/60));
	$t->set_var('minutes',($phpgw->db->f('minutes'))-((floor($phpgw->db->f('minutes')/60)*60)));

	$t->set_var('minperae',$phpgw->db->f('minperae'));
	$t->set_var('billperae',$phpgw->db->f('billperae'));

	$db2 = $phpgw->db;
	$db2->query("SELECT account_lid,account_firstname,account_lastname from phpgw_accounts where account_id='"
				. $phpgw->db->f('employee') . "'");
	$db2->next_record();
	$t->set_var('employee',$db2->f('account_firstname') . ' ' . $db2->f('account_lastname') . ' [ ' . $db2->f('account_lid') . ' ]');

	$db2->query("SELECT num,title from phpgw_p_projects where id='" . $phpgw->db->f('project_id') . "'");
	$db2->next_record();
	$t->set_var('project',$phpgw->strip_html($db2->f('title')) . ' [ ' . $phpgw->strip_html($db2->f('num')) . ' ]');

	$db2->query("SELECT descr FROM phpgw_p_activities WHERE id ='" . $phpgw->db->f('activity_id') . "'");
	$db2->next_record();
	$t->set_var('activity',$phpgw->strip_html($db2->f('descr')));

	$t->pparse('out','hours_view');
	$phpgw->common->phpgw_footer();
?>
