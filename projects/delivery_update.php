<?php
	/**************************************************************************\
	* phpGroupWare - projects/projectdelivery                                  *
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
	$t->set_file(array('projecthours_list_t' => 'del_listhours.tpl'));
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

	$db2 = $phpgw->db;

	if($Delivery)
	{
		$errorcount = 0;
		$db2->query("Select customer from phpgw_p_projects where id='$project_id'");
		$db2->next_record();
		$customer = $db2->f('customer'); 

		$phpgw->db->query("SELECT num FROM phpgw_p_delivery WHERE num='$delivery_num' AND id != '$delivery_id'"); 
		if ($phpgw->db->next_record()) { $error[$errorcount++] = lang('That ID has been used already !'); }
		if (!$delivery_num) { $error[$errorcount++] = lang('Please enter an ID !'); }
		if (!$customer) { $error[$errorcount++] = lang('You have no customer selected !'); }

		if (checkdate($month,$day,$year)) { $date = mktime(2,0,0,$month,$day,$year); }
		else
		{
			if ($month && $day && $year) { $error[$errorcount++] = lang('You have entered an invalid date !') . '<br>' . $month . '/' . $day . '/' . $year; }
		}

		if (! $error)
		{
			$phpgw->db->query("UPDATE phpgw_p_delivery set num='$delivery_num',date='$date',customer='$customer' where id='$delivery_id'");

			$db2->query("DELETE FROM phpgw_p_deliverypos WHERE delivery_id='$delivery_id'");
			while($select && $entry=each($select))
			{
				$phpgw->db->query("INSERT INTO phpgw_p_deliverypos (delivery_id,hours_id) VALUES ('$delivery_id','$entry[0]')");
				$db2->query("UPDATE phpgw_p_hours set status='closed' WHERE status='done' AND id='$entry[0]'");
				$db2->query("UPDATE phpgw_p_hours set dstatus='d' WHERE id='$entry[0]'");
			}
		}
	}

	if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
	if (($Delivery) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Delivery x has been updated !',$delivery_num)); }
	if ((! $Delivery) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="delivery_id" value="' . $delivery_id . '">' . "\n"
				. '<input type="hidden" name="project_id" value="' . $project_id . '">' . "\n";

	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('lang_action',lang('Delivery'));
	$t->set_var('lang_choose','');
	$t->set_var('choose','');

	if (! $start) { $start = 0; }
	$ordermethod = "order by end_date asc";

// ------------------ list header variable template-declarations ----------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('sort_activity',lang('Activity'));
	$t->set_var('sort_hours_descr',lang('Job'));
	$t->set_var('sort_status',lang('Status'));
	$t->set_var('sort_start_date',lang('Work date'));
	$t->set_var('sort_aes',lang('Workunits'));
	$t->set_var('h_lang_select',lang('Select'));
	$t->set_var('h_lang_edithour',lang('Edit hours'));
	$t->set_var('actionurl',$phpgw->link('/projects/delivery_update.php'));
	$t->set_var('lang_print_delivery',lang('Print delivery'));

	if (!$delivery_id) { $t->set_var(print_delivery,$phpgw->link('/projects/fail.php')); }
	else { $t->set_var(print_delivery,$phpgw->link('/projects/del_deliveryshow.php','delivery_id=' . $delivery_id)); }
  
// ----------------------- end header declaration ------------------------------

	$d = CreateObject('phpgwapi.contacts');
	$phpgw->db->query("SELECT title,customer,coordinator FROM phpgw_p_projects WHERE id='$project_id'");

	$phpgw->db->next_record();
	$coordinator = $phpgw->db->f('coordinator');
	$title = $phpgw->strip_html($phpgw->db->f('title'));
	if (! $title)  $title  = '&nbsp;';
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

	$t->set_var(title_project,lang('Title'));
	$t->set_var(title_customer,lang('Customer'));
	$t->set_var(title_delivery_num,lang('Delivery ID'));
	$t->set_var(delivery_num,$phpgw->strip_html($delivery_num));

	if ($delivery_id)
	{
		$phpgw->db->query("SELECT date FROM phpgw_p_delivery WHERE id='$delivery_id'");
		$phpgw->db->next_record();
		$date = $phpgw->db->f('date');    
		$phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
						. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae FROM phpgw_p_hours $join phpgw_p_activities ON "
						. "phpgw_p_hours.activity_id=phpgw_p_activities.id $join phpgw_p_deliverypos ON phpgw_p_hours.id=phpgw_p_deliverypos.hours_id "
						. "WHERE phpgw_p_hours.project_id='$project_id' AND phpgw_p_deliverypos.delivery_id='$delivery_id' $ordermethod");
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
	$t->set_var('lang_delivery_date',lang('Delivery date')); 

	$sumaes = 0;
	while ($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$select = '<input type="checkbox" name="select[' . $phpgw->db->f('id') . ']" value="True" checked>';

		$activity = $phpgw->strip_html($phpgw->db->f('descr'));
		if (! $activity)  $activity  = '&nbsp;';

		$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));
		if (! $hours_descr)  $hours_descr  = '&nbsp;';

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
		$sumaes += $aes;

// ------------------- template declaration for list records ----------------------------------

		$t->set_var(array('select' => $select,
						'activity' => $activity,
					'hours_descr' => $hours_descr,
						'status' => $statusout,
					'start_date' => $start_dateout,
							'aes' => $aes));

		$t->set_var('edithour','');
		$t->set_var('lang_edit_entry','&nbsp;');

		$t->parse('list','projecthours_list',True);

// -------------------------- end record declaration -------------------------------------------

	}

// ----------------------------------- na_list -------------------------------------------------

	if($project_id)
	{
		$phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status,"
						. "phpgw_p_hours.start_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae FROM "
						. "phpgw_p_hours $join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id "
						. "WHERE phpgw_p_hours.dstatus='o' AND phpgw_p_hours.project_id='$project_id' $ordermethod");

		$sumaes2 = 0;
		while ($phpgw->db->next_record())
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$select = '<input type="checkbox" name="select[' . $phpgw->db->f('id') . ']" value="True">';

			$activity = $phpgw->strip_html($phpgw->db->f('descr'));
			if (! $activity)  $activity  = '&nbsp;';

			$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));
			if (! $hours_descr)  $hours_descr  = '&nbsp;';
  
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
				$start_dateout =  $phpgw->common->show_date($start_date,$phpgw_info['user']['preferences']['common']['dateformat']);
			}

			if ($phpgw->db->f('minperae') != 0)
			{
				$aes = ceil($phpgw->db->f('minutes')/$phpgw->db->f('minperae'));
			}
//			$sumaes2 += $aes;

// ---------------- template declaration for list records -------------------------
  
			$t->set_var(array('select' => $select,
							'activity' => $activity,
						'hours_descr' => $hours_descr,
							'status' => $statusout,
						'start_date' => $start_dateout,
								'aes' => $aes));

			if (($phpgw->db->f('status') != billed) && ($phpgw->db->f('status') != closed))
			{
				if ($projects->check_perms($grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $phpgw_info['user']['account_id'])
				{
					$t->set_var('edithour',$phpgw->link('/projects/hours_edithour.php','id=' . $phpgw->db->f('id') . '&delivery_id=' . $delivery_id
                                         . '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start . '&filter=' . $filter . '&status=' . $status));
					$t->set_var('lang_edit_entry',lang('Edit hours'));
				}
			}
			else
			{
				$t->set_var('edithour','');
				$t->set_var('lang_edit_entry','&nbsp;');
			}

			$t->parse('list','projecthours_list',True);

// -------------------------- end record declaration ------------------------

		}
	}

// -------------------------- na_list_end -----------------------------------

	$t->set_var('lang_aes',lang('Sum workunits'));
	$t->set_var('sum_aes',$sumaes);

	if ($projects->check_perms($grants[$coordinator],PHPGW_ACL_EDIT) || $coordinator == $phpgw_info['user']['account_id'])
	{
		$t->set_var('delivery','<input type="submit" name="Delivery" value="' . lang('Update delivery') . '">');
	}
	else { $t->set_var('delivery',''); }

	$t->parse('out','projecthours_list_t',True);
	$t->p('out');

	$phpgw->common->phpgw_footer();
?>
