<?php
	/**************************************************************************\
	* phpGroupWare - projects/projectstatistics                                *
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

	if (! $id)
	{
		Header('Location: ' . $phpgw->link('/projects/stats_projectlist.php','sort=' . $sort . '&order=' . $order . '&query=' . $query
										. '&start=' . $start . '&filter=' . $filter));
	}

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_nextmatchs_class' => True);

	include('../header.inc.php');

	if ($phpgw_info['server']['db_type']=='pgsql')
	{
		$join = " JOIN ";
	}
	else
	{
		$join = " LEFT JOIN ";
	}

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="'. $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="sdate" value="' . $sdate . '">' . "\n"
				. '<input type="hidden" name="edate" value="' . $edate . '">' . "\n"
				. '<input type="hidden" name="id" value="' . $id . '">' . "\n";

	$phpgw->db->query("select * from phpgw_p_projects where id='$id'");
	$phpgw->db->next_record();

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('project_stat' => 'stats_projectstat.tpl'));
	$t->set_block('project_stat','stat_list','list');

	$t->set_var('actionurl',$phpgw->link('/projects/stats_projectstat.php','sdate=' . $sdate . '&edate=' . $edate));
	$t->set_var('lang_action',lang('Project statistic'));
	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('lang_num',lang('Project ID'));
	$t->set_var('num',$phpgw->strip_html($phpgw->db->f('num')));
	$t->set_var('lang_title',lang('Title'));
	$title = $phpgw->strip_html($phpgw->db->f('title'));                                                                                                                                   
	if (! $title) $title = '&nbsp;';
	$t->set_var('title',$title);
	$t->set_var('lang_status',lang('Status'));
	$t->set_var('status',lang($phpgw->db->f('status')));
	$t->set_var('lang_budget',lang('Budget'));
	$t->set_var('budget',$phpgw->db->f('budget'));
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);
	$t->set_var('hd_account',lang('Account'));
	$t->set_var('hd_activity',lang('Activity'));
	$t->set_var('hd_hours',lang('Hours'));

	$t->set_var('lang_start_date',lang('Start date'));
	$t->set_var('lang_end_date',lang('Date due'));

	$sm = CreateObject('phpgwapi.sbox');

	if (!$submit)
	{
		$sdate = $phpgw->db->f('start_date');
		$edate = $phpgw->db->f('end_date');
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

	$t->set_var('start_date_select',$phpgw->common->dateformatorder($sm->getYears('syear',$syear),$sm->getMonthText('smonth',$smonth),$sm->getDays('sday',$sday)));
	$t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));

	$t->set_var('lang_coordinator',lang('Coordinator'));

	$db2 = $phpgw->db;
	$db2->query("SELECT account_id,account_firstname,account_lastname,account_lid FROM phpgw_accounts where "
				. "account_status != 'L' ORDER BY account_lid,account_lastname,account_firstname asc");
	while ($db2->next_record())
	{
		if($db2->f('account_id')==$phpgw->db->f('coordinator'))
		{
			$coordinator = $phpgw->strip_html($db2->f('account_lid') . ' [ ' . $db2->f('account_firstname') . ' '
						. $db2->f('account_lastname') . ' ]');
		}
	}
	$t->set_var('coordinator',$coordinator);

// customer

	$t->set_var('lang_customer',lang('Customer'));
	$d = CreateObject('phpgwapi.contacts');
	$ab_customer = $phpgw->db->f('customer');
	if (!$ab_customer)
	{
		$t->set_var('customer','');
	}
	else
	{
		$cols = array('n_given' => 'n_given',
						'n_family' => 'n_family',
						'org_name' => 'org_name');
		$customer = $d->read_single_entry($ab_customer,$cols);
		if ($customer[0]['org_name']=='')
		{
			$t->set_var('customer',$customer[0]['n_given'] . ' ' . $customer[0]['n_family']);
		}
		else
		{
			$t->set_var('customer',$customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]');
		}
	}

	if($billed)
	{
		$t->set_var('billed','checked');
	}

	$t->set_var('billedonly',lang('Billed only'));

	$t->set_var('lang_calcb',lang('Calculate'));

// -------------------------------- calculate statistics -------------------------------------------                                                                                                                                         

//    $filter= '';

	if($billed) { $filter .= " AND phpgw_p_hours.status='billed' "; }

	if (checkdate($smonth,$sday,$syear))
	{
		$sdate = mktime(2,0,0,$smonth,$sday,$syear);
		$filter .= " AND start_date >= '$sdate' ";
	}

	if (checkdate($emonth,$eday,$eyear))
	{
		$edate = mktime(2,0,0,$emonth,$eday,$eyear);
		$filter .= " AND end_date <= '$edate' ";
	}

	$phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts $join phpgw_p_hours ON "
					. "phpgw_p_hours.employee=account_id WHERE project_id='$id' $filter GROUP BY account_id,account_firstname,account_lastname");

	while ($phpgw->db->next_record())
	{
		$account_id = $phpgw->db->f('account_id'); 
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$t->set_var('tr_color',$tr_color);
		$summin = 0;
		$t->set_var('e_account',$phpgw->db->f('account_firstname') . ' ' . $phpgw->db->f('account_lastname'));
		$t->set_var('e_activity','');
		$t->set_var('e_hours','');
		$t->parse('list','stat_list',True);

		$db2->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE project_id='$id' AND employee='$account_id' "
					. "AND phpgw_p_hours.activity_id=phpgw_p_activities.id $filter GROUP BY phpgw_p_activities.descr");

		while ($db2->next_record())
		{
			$t->set_var('e_account','');
			$t->set_var('e_activity',$db2->f('descr'));
			$summin += $db2->f('min');
			$hrs = floor($db2->f('min')/60). ':' . sprintf ("%02d",(int)($db2->f('min')-floor($db2->f('min')/60)*60));
			$t->set_var('e_hours',$hrs);

			$t->parse('list','stat_list',True);
		}

		$t->set_var('e_account','');
		$t->set_var('e_activity','');
		$hrs = floor($summin/60). ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
		$t->set_var('e_hours',$hrs);

		$t->parse('list','stat_list',True);
	}

	$db2->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE project_id='$id' AND "
				. "phpgw_p_hours.activity_id=phpgw_p_activities.id $filter GROUP BY phpgw_p_activities.descr");

	$summin=0;
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);
	$t->set_var('e_account',lang('Overall'));
	$t->set_var('e_activity','');
	$t->set_var('e_hours','');

	$t->parse('list','stat_list',True);

	while ($db2->next_record())
	{
		$t->set_var('e_account','');
		$t->set_var('e_activity',$phpgw->strip_html($db2->f('descr')));
		$summin += $db2->f('min');
		$hrs = floor($db2->f('min')/60). ':' . sprintf ("%02d",(int)($db2->f('min')-floor($db2->f('min')/60)*60));
		$t->set_var('e_hours',$hrs);

		$t->parse('list','stat_list',True);
	}

	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);
	$t->set_var('e_account',lang('sum'));
	$t->set_var('e_activity','');
	$hrs = floor($summin/60). ':' . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
	$t->set_var('e_hours',$hrs);

	$t->parse('list','stat_list',True);
	$t->pparse('out','project_stat');

	$phpgw->common->phpgw_footer();
?>
