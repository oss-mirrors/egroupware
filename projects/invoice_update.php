<?php
	/**************************************************************************\
	* phpGroupWare - projects/projectbilling                                   *
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
	$t->set_file(array('projecthours_list_t' => 'bill_listhours.tpl'));
	$t->set_block('projecthours_list_t','projecthours_list','list');

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');
	$grants[$phpgw_info['user']['account_id']] = PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

	if ($phpgw_info['server']['db_type']=='pgsql')
	{
		$join = " JOIN ";
	}
	else
	{
		$join = " LEFT JOIN ";
	}

	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
	}
	else
	{
		$t->set_var('error',lang('Please set your preferences for this application'));
	}

	$db2 = $phpgw->db;
  
	if($Invoice)
	{
		$errorcount = 0;
		$db2->query("Select customer from phpgw_p_projects where id='$project_id'");
		$db2->next_record();
		$customer = $db2->f('customer');

		$invoice_num = addslashes($invoice_num);
		$phpgw->db->query("SELECT num FROM phpgw_p_invoice WHERE num='$invoice_num' AND id != '$invoice_id'"); 
		if ($phpgw->db->next_record())
		{
			$error[$errorcount++] = lang('That ID has been used already !');
		}
		if (!$invoice_num)
		{
			$error[$errorcount++] = lang('Please enter an ID !');
		}
		if (!$customer)
		{
			$error[$errorcount++] = lang('You have no customer selected !');
		}
		if (checkdate($month,$day,$year)) { $date = mktime(2,0,0,$month,$day,$year); }
		else
		{
			if ($month && $day && $year) { $error[$errorcount++] = lang('You have entered an invalid date !') . '<br>' . $month .  '/' . $day . '/' . $year; }
		}

		if (! $error)
		{ 
			$phpgw->db->query("UPDATE phpgw_p_invoice set num='$invoice_num',date='$date',customer='$customer' WHERE id='$invoice_id'");

			$db2->query("DELETE FROM phpgw_p_invoicepos WHERE invoice_id='$invoice_id'");
			while($select && $entry=each($select))
			{
				$phpgw->db->query("INSERT INTO phpgw_p_invoicepos (invoice_id,hours_id) VALUES ('$invoice_id','$entry[0]')");
				$db2->query("UPDATE phpgw_p_hours SET status='billed' WHERE id='$entry[0]'");
			}

			$phpgw->db->query("SELECT billperae,minutes,minperae FROM phpgw_p_hours,phpgw_p_invoicepos "
							."WHERE phpgw_p_invoicepos.invoice_id='$invoice_id' AND phpgw_p_hours.id=phpgw_p_invoicepos.hours_id");
			while($phpgw->db->next_record())
			{
				$aes = ceil($phpgw->db->f('minutes')/$phpgw->db->f('minperae'));
				$sum = $phpgw->db->f('billperae')*$aes;
				$sum_sum += $sum;
			}
			$db2->query("UPDATE phpgw_p_invoice SET sum=round(" . $sum_sum . ",2) WHERE id='$invoice_id'");
		}
	}
	if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
	if (($Invoice) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Invoice x has been updated !',$invoice_num)); }
	if ((! $Invoice) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

	$hidden_vars = '<input type="hidden" name="invoice_id" value="' . $invoice_id . '">' . "\n"
				. '<input type="hidden" name="project_id" value="' . $project_id . '">' . "\n";

	$t->set_var('hidden_vars',$hidden_vars);   
	$t->set_var('lang_action',lang('Invoice'));

	if (! $start) $start = 0;
	$ordermethod = "order by end_date asc";

//-------------- list header variable template-declarations------------------------

	$t->set_var(th_bg,$phpgw_info['theme']['th_bg']);
	$t->set_var('currency',$currency);
	$t->set_var('sort_activity',lang('Activity'));
	$t->set_var('sort_hours_descr',lang('Job'));
	$t->set_var('sort_status',lang('Status'));
	$t->set_var('sort_start_date',lang('Work date'));
	$t->set_var('sort_aes',lang('Workunits'));
	$t->set_var('sort_billperae',lang('Bill per workunit'));
	$t->set_var('sort_sum',lang('Sum'));
	$t->set_var('h_lang_select',lang('Select'));
	$t->set_var('h_lang_edithour',lang('Edit hours'));
	$t->set_var('actionurl',$phpgw->link('/projects/invoice_update.php'));
	$t->set_var('lang_print_invoice',lang('Print invoice'));

	if (!$invoice_id) { $t->set_var('print_invoice',$phpgw->link('/projects/fail.php')); }
	else { $t->set_var('print_invoice',$phpgw->link('/projects/bill_invoiceshow.php','invoice_id=' . $invoice_id)); }

// ------------------------ end header declaration ------------------------------------

	$d = CreateObject('phpgwapi.contacts');
	$phpgw->db->query("SELECT title,customer,coordinator FROM phpgw_p_projects WHERE id='$project_id'");
	$phpgw->db->next_record();
	$coordinator = $phpgw->db->f('coordinator');
	$title = $phpgw->strip_html($phpgw->db->f('title'));
	if (! $title) $title = '&nbsp;';
	$t->set_var('project',$title);
	$ab_customer = $phpgw->db->f('customer');
	if (!$ab_customer) { $t->set_var('customer',lang('You have no customer selected !')); }
	else
	{
		$cols = array('n_given' => 'n_given',
					'n_family' => 'n_family',
					'org_name' => 'org_name');
		$customer = $d->read_single_entry($ab_customer,$cols);
		if ($customer[0]['org_name'] = '') { $customername = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
		else { $customername = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
		$t->set_var('customer',$customername);
	}

	$t->set_var('title_project',lang('Title'));
	$t->set_var('title_customer',lang('Customer'));

	$t->set_var('title_invoice_num',lang('Invoice ID'));
	$t->set_var('invoice_num',$invoice_num);  

	$t->set_var('lang_choose','');
	$t->set_var('choose','');

	if ($invoice_id)
	{
		$phpgw->db->query("SELECT date FROM phpgw_p_invoice WHERE id=$invoice_id");
		$phpgw->db->next_record();
		$date=$phpgw->db->f('date');    
		$phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
						. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae,phpgw_p_hours.billperae FROM "
						. "phpgw_p_hours $join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id $join phpgw_p_invoicepos "
						. "ON phpgw_p_invoicepos.hours_id=phpgw_p_hours.id WHERE phpgw_p_hours.project_id='$project_id' AND "
						. "phpgw_p_invoicepos.invoice_id='$invoice_id' $ordermethod");
	}

	if ($date != 0)
	{
		$month = date('m',$date); 
		$day = date('d',$date);
		$year = date('Y',$date);
	}
	else
	{
		$month = date('m',time());
		$day = date('d',time());
		$year = date('Y',time());
	}
	$sm = CreateObject('phpgwapi.sbox');
	$t->set_var('date_select',$phpgw->common->dateformatorder($sm->getYears('year',$year),$sm->getMonthText('month',$month),$sm->getDays('day',$day)));

	$t->set_var('lang_invoice_date',lang('Invoice date'));

	$summe=0;
	$sumaes=0;
	while ($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$select = '<input type="checkbox" name="select[' . $phpgw->db->f('id') . ']" value="True" checked>';

		$activity = $phpgw->strip_html($phpgw->db->f('descr'));
		if (! $activity) $activity = '&nbsp;';

		$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));
		if (! $hours_descr) $hours_descr = '&nbsp;';

		$statusout = lang($phpgw->db->f('status'));
		$t->set_var('tr_color',$tr_color);

		$start_date = $phpgw->db->f('start_date');
		if ($start_date == 0) { $start_dateout = '&nbsp;'; } 
		else
		{
			$month = $phpgw->common->show_date(time(),'n');
			$day = $phpgw->common->show_date(time(),'d');
			$year = $phpgw->common->show_date(time(),'Y');

			$start_date = $start_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$start_dateout = $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
		}

		if ($phpgw->db->f('minperae') != 0)
		{
			$aes = ceil($phpgw->db->f('minutes')/$phpgw->db->f('minperae'));
		}

		$sumaes += $aes;
		$summe += $phpgw->db->f('billperae')*$aes;

// -------------------- declaration for list records ---------------------------

		$t->set_var(array('select' => $select,
						'activity' => $activity,
					'hours_descr' => $hours_descr,
							'status' => $statusout,
					'start_date' => $start_dateout,
							'aes' => $aes,
					'billperae' => $phpgw->db->f('billperae'),
							'sum' => sprintf ("%01.2f", (float)$phpgw->db->f('billperae')*$aes)));

		$t->set_var('edithour','');
		$t->set_var('lang_edit_entry','&nbsp;');

		$t->parse('list','projecthours_list',True);

// ------------------------ end record declaration ------------------------
	}

	$t->set_var('sum_sum',sprintf("%01.2f",$summe));
	$t->set_var('sum_aes',$sumaes);
	$t->set_var('title_netto',lang('Sum net'));

// ----------------------------- na_list ------------------------------------

	if($invoice_id)
	{
		$phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
						. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae,phpgw_p_hours.billperae FROM "
						. "phpgw_p_hours $join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id $join phpgw_p_projectactivities "
						. "ON phpgw_p_hours.activity_id=phpgw_p_projectactivities.activity_id WHERE (phpgw_p_hours.status='done' OR phpgw_p_hours.status='closed') "
						. "AND phpgw_p_hours.project_id='$project_id' AND phpgw_p_projectactivities.project_id='$project_id' "
						. "AND phpgw_p_projectactivities.billable='Y' AND phpgw_p_projectactivities.activity_id=phpgw_p_hours.activity_id $ordermethod");

		while ($phpgw->db->next_record())
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$select = '<input type="checkbox" name="select[' . $phpgw->db->f('id') . ']" value="True">';

			$activity = $phpgw->strip_html($phpgw->db->f('descr'));
			if (! $activity) $activity = '&nbsp;';

			$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));
			if (! $hours_descr) $hours_descr = '&nbsp;';

			$status = $phpgw->db->f('status');
			$statusout = lang($status);
			$t->set_var('tr_color',$tr_color);

			$start_date = $phpgw->db->f('start_date');  

			if ($start_date == 0) { $start_dateout = '&nbsp;'; }
			else
			{
				$month = $phpgw->common->show_date(time(),'n');
				$day = $phpgw->common->show_date(time(),'d');
				$year = $phpgw->common->show_date(time(),'Y');

				$start_date = $start_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
				$start_dateout = $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
			}

			if ($phpgw->db->f('minperae') != 0)
			{
				$aes = ceil($phpgw->db->f('minutes')/$phpgw->db->f('minperae'));
			}
//			$sumaes += $aes;
//			$summe += (float)($phpgw->db->f('billperae')*$aes);

// ------------------------- template declaration for list records ----------------------------

			$t->set_var(array('select' => $select,
							'activity' => $activity,
						'hours_descr' => $hours_descr,
							'status' => $statusout,
						'start_date' => $start_dateout,
								'aes' => $aes,
						'billperae' => $phpgw->db->f('billperae'),
								'sum' => sprintf ("%01.2f", (float)$phpgw->db->f('billperae')*$aes)));


			if (($status != 'billed') && ($status != 'closed'))
			{
				if ($projects->check_perms($grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $phpgw_info['user']['account_id'])
				{
					$t->set_var('edithour',$phpgw->link('/projects/hours_edithour.php','id=' . $phpgw->db->f('id') . '&invoice_id=' . $invoice_id . '&sort=' . $sort
                    		                     . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter . '&status=' . $status));
					$t->set_var('lang_edit_entry',lang('Edit hours'));
				}
			}
			else
			{
				$t->set_var('edithour','');
				$t->set_var('lang_edit_entry','&nbsp;');
			}
			$t->parse('list','projecthours_list',True);

// ---------------------------------- end record declaration -------------------------------------
		}
	}
// ------------------------------- na_list_end ---------------------------------------------------

	if ($projects->check_perms($grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $phpgw_info['user']['account_id'])
	{
		$t->set_var('invoice','<input type="submit" name="Invoice" value="' . lang('Update invoice') . '">');
    }
    else { $t->set_var('invoice',''); }

    $t->parse('out','projecthours_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>
