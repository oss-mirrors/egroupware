<?php
	/**************************************************************************\
	* phpGroupWare - projects/projecthours                                     *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* --------------------------------------------------------                 *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags']['currentapp'] = 'projects';
	include('../header.inc.php');

	if (!$submit) { $referer = $HTTP_REFERER; }

	if (!$id) { Header('Location: ' . $HTTP_REFERER); }

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');
	$grants[$phpgw_info['user']['account_id']] = PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="'. $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="referer" value="' . $referer . '">' . "\n"
				. '<input type="hidden" name="id" value="' . $id . '">' . "\n"
				. '<input type="hidden" name="pro_parent" value="' . $pro_parent . '">' . "\n"
				. '<input type="hidden" name="delivery_id" value="' . $delivery_id . '">' . "\n"
				. '<input type="hidden" name="invoice_id" value="' . $invoice_id . '">' . "\n";

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('hours_edit' => 'hours_formhours.tpl'));
	$t->set_block('hours_edit','add','addhandle');
	$t->set_block('hours_edit','edit','edithandle');

	$projects = CreateObject('projects.projects');

	if ($submit)
	{
		$errorcount = 0;

		if ($shour && ($shour != 0) && ($shour != 12))
		{
			if ($sampm=='pm') { $shour = $shour + 12; }
		}

		if ($shour && ($shour == 12))
		{
			if ($sampm=='am') { $shour = 0; }
		}

		if ($ehour && ($ehour != 0) && ($ehour != 12))
		{
			if ($eampm=='pm') { $ehour = $ehour + 12; }
		}

		if ($ehour && ($ehour == 12))
		{
			if ($eampm=='am') { $ehour = 0; }
		}

		if (checkdate($smonth,$sday,$syear)) { $sdate = mktime($shour,$smin,0,$smonth,$sday,$syear); }
		else
		{
			if ($shour && $smin && $smonth && $sday && $syear)
			{
				$error[$errorcount++] = lang('You have entered an invalid start date !') . '<br>' . $shour  . ':' . $smin . ' ' . $smonth . '/' . $sday . '/' . $syear;
			}
		}

		if (checkdate($emonth,$eday,$eyear)) { $edate = mktime($ehour,$emin,0,$emonth,$eday,$eyear); }
		else
		{
			if ($ehour && $emin && $emonth && $eday && $eyear)
			{
				$error[$errorcount++] = lang('You have entered an invalid end date !') . '<br>' . $ehour . ':' . $emin . ' ' . $emonth . '/' . $eday . '/' . $eyear;
			}
		}


		$phpgw->db->query("SELECT minperae,billperae,remarkreq FROM phpgw_p_activities WHERE id = '".$activity."'");
		$phpgw->db->next_record();
		if ($phpgw->db->f(0) == 0) { $error[$errorcount++] = lang('You have selected an invalid activity !'); }
		else
		{
			$billperae = $phpgw->db->f('billperae');
			$minperae = $phpgw->db->f('minperae');
			if (($phpgw->db->f("remarkreq")=="Y") and (!$remark)) { $error[$errorcount++] = lang('Please enter a remark !'); }
		}

		if (! $error)
		{
			$ae_minutes=$hours*60+$minutes;
			$remark = addslashes($remark);
			$hours_descr = addslashes($hours_descr);

			$phpgw->db->query("update phpgw_p_hours set activity_id='$activity',entry_date='" . time() . "',start_date='$sdate',end_date='$edate',"
							. "hours_descr='$hours_descr',remark='$remark',"
							. "minutes='$ae_minutes',status='$status',minperae='$minperae',billperae='$billperae',employee='$employee' where id='$id'");
		}
	}

	if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
	if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Job has been updated !')); }
	if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
	}
	else
	{
		$t->set_var('error',lang('Please set your preferences for this application'));
	}

	$t->set_var('currency',$currency);
	$t->set_var('actionurl',$phpgw->link('/projects/hours_edithour.php','pro_parent=' . $pro_parent));
	$t->set_var('deleteurl',$phpgw->link('/projects/hours_deletehour.php','id=' . $id));
	$t->set_var('doneurl',$referer);
	$t->set_var('lang_action',lang('Edit project hours'));
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
	$t->set_var('lang_edit',lang('Edit'));
	$t->set_var('lang_status',lang('Status'));
	$t->set_var('lang_select_project',lang('Select project'));

	$phpgw->db->query("select * from phpgw_p_hours where id='$id'");
	$phpgw->db->next_record();

	$project_id = $phpgw->db->f('project_id');

	if ($phpgw->db->f('status')=='open'): 
		$stat_sel[0]=' selected';
	elseif ($phpgw->db->f('status')=='done'):
		$stat_sel[1]=' selected';
    endif;

	$status_list = '<option value="open"' . $stat_sel[0] . '>' . lang('Open') . '</option>' . "\n"
				. '<option value="done"' . $stat_sel[1] . '>' . lang('Done') . '</option>' . "\n";

	$t->set_var('status_list',$status_list);

	$sm = CreateObject('phpgwapi.sbox');
	$amsel = ' checked'; $pmsel = '';
	$sdate = $phpgw->db->f('start_date');

	if (!$sdate)
	{
		$smonth = date('m',time());
		$sday = date('d',time());
		$syear = date('Y',time());
		$shour = date('H',time());
		$smin = date('i',time());
	}
	else
	{
		$smonth = date('m',$sdate);
		$sday = date('d',$sdate);
		$syear = date('Y',$sdate);
		$shour = date('H',$sdate);
		$smin = date('i',$sdate);
	}

	$t->set_var('start_date_select',$phpgw->common->dateformatorder($sm->getYears('syear',$syear),$sm->getMonthText('smonth',$smonth),$sm->getDays('sday',$sday)));

	if ($phpgw_info['user']['preferences']['common']['timeformat'] == '12')
	{                                                                                                            
		if ($shour >= 12)
		{
			$amsel = ''; $pmsel = ' checked';
			if ($shour > 12) { $shour = $shour - 12; }
		}
		if ($shour == 0) { $shour = 12; }
		$sradio = '<input type="radio" name="sampm" value="am"'.$amsel.'>am';
		$sradio .= '<input type="radio" name="sampm" value="pm"'.$pmsel.'>pm';
		$t->set_var('sradio',$sradio);
	}
	else { $t->set_var('sradio',''); }
 
	$t->set_var('shour',$shour);
	$t->set_var('smin',$smin);

	$edate = $phpgw->db->f('end_date');

	if (!$edate)
	{
		$emonth = date('m',time());
		$eday = date('d',time());
		$eyear = date('Y',time());
		$ehour = date('H',time());
		$emin = date('i',time());
	}
	else
	{
		$emonth = date('m',$edate);
		$eday = date('d',$edate);
		$eyear = date('Y',$edate);
		$ehour = date('H',$edate);
		$emin = date('i',$edate);
	}

	$t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));

	if ($phpgw_info['user']['preferences']['common']['timeformat'] == '12')
	{                                                                                                            
		if ($ehour >= 12)
		{
			$amsel = ''; $pmsel = ' checked';
			if ($ehour > 12) { $ehour = $ehour - 12; }
		}
		if ($ehour == 0) { $ehour = 12; }
		$eradio = '<input type="radio" name="eampm" value="am"'.$amsel.'>am';
		$eradio .= '<input type="radio" name="eampm" value="pm"'.$pmsel.'>pm';
		$t->set_var('eradio',$eradio);
	}
	else { $t->set_var('eradio',''); }

	$t->set_var('ehour',$ehour);
	$t->set_var('emin',$emin);

	$t->set_var('remark',$phpgw->strip_html($phpgw->db->f('remark')));
	$t->set_var('hours_descr',$phpgw->strip_html($phpgw->db->f('hours_descr')));

	$t->set_var('hours',floor($phpgw->db->f('minutes')/60));
	$t->set_var('minutes',($phpgw->db->f('minutes'))-((floor($phpgw->db->f('minutes')/60)*60)));

	$t->set_var('minperae',$phpgw->db->f('minperae'));
	$t->set_var('billperae',$phpgw->db->f('billperae'));

	$employees = $phpgw->accounts->get_list('accounts', $start = '', $sort = '', $order = '', $query = '');
	while (list($null,$account) = each($employees))
	{
		$employee_list .= '<option value="' . $account['account_id'] . '"';
		if($account['account_id']==$phpgw->db->f('employee'))
			$employee_list .= ' selected';
			$employee_list .= '>'
			. $account['account_firstname'] . ' ' . $account['account_lastname'] . ' [ ' . $account['account_lid'] . ' ]' . '</option>';
	}

	$t->set_var('employee_list',$employee_list);

	$t->set_var('project_name',$phpgw->strip_html($projects->return_value('num',$project_id)));

	if ($pro_parent > 0)
	{
		$pro_activities = $pro_parent;
	}
	else
	{
		$pro_activities = $project_id;
	}

	$db2 = $phpgw->db;
	$db2->query("SELECT activity_id,descr FROM phpgw_p_projectactivities,phpgw_p_activities WHERE project_id ='$pro_activities' "
				. "AND phpgw_p_projectactivities.activity_id=phpgw_p_activities.id");
	while ($db2->next_record())
	{
		$activity_list .= '<option value="' . $db2->f('activity_id') . '"';
		if($db2->f('activity_id') == $phpgw->db->f('activity_id'))
		{
			$activity_list .= ' selected';
		}
		$activity_list .= '>'
		. $phpgw->strip_html($db2->f('descr')) . '</option>';
	}

	$t->set_var('activity_list',$activity_list);

	if ($projects->check_perms($grants[$phpgw->db->f('employee')],PHPGW_ACL_DELETE) || $phpgw->db->f('employee') == $phpgw_info['user']['account_id'])
	{
		$t->set_var('delete','<form method="POST" action="' . $phpgw->link('/projects/hours_deletehour.php','id=' . $id . '&project_id=' . $project_id) . '"><input type="submit" value="' . lang('Delete') .'"></form>');
	}
	else { $t->set_var('delete','&nbsp;'); }

	$t->set_var('edithandle','');
	$t->set_var('addhandle','');
	$t->pparse('out','hours_edit');
	$t->pparse('edithandle','edit');

	$phpgw->common->phpgw_footer();
?>
