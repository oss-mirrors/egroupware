<?php
	/**************************************************************************\
	* phpGroupWare - projects/projectbilling                                   *
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

	$phpgw_info['flags'] = array('currentapp' => 'projects',
									'noheader' => True, 
									'nonavbar' => True);         
	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('invoice_list_t' => 'bill_invoiceform.tpl',
						'invoicepos_list' => 'bill_invoiceform.tpl'));
    $t->set_block('invoice_list_t','invoicepos_list','list');

	$d = CreateObject('phpgwapi.contacts');
//  $taxpercent = 0.16;
//  $eurtodm = 1.95583;

	if (isset($phpgw_info['user']['preferences']['projects']['tax']) && (isset($phpgw_info['user']['preferences']['common']['currency']) && (isset($phpgw_info['user']['preferences']['projects']['abid']))))
	{
		$tax = $phpgw_info['user']['preferences']['projects']['tax'];
		$tax = ((float)$tax);
		$taxpercent = ($tax/100);
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
		$myaddress = $phpgw_info['user']['preferences']['projects']['abid'];

		$cols = array('n_given' => 'n_given',
					'n_family' => 'n_family',
					'org_name' => 'org_name',
				'adr_street' => 'adr_street',
				'adr_locality' => 'adr_locality',
				'adr_postalcode' => 'adr_postalcode',
				'adr_region' => 'adr_region',
			'adr_countryname' => 'adr_countryname');

		$address = $d->read($myaddress,$cols);

		$t->set_var('ad_company',$address[0]['org_name']);
		$t->set_var('ad_firstname',$address[0]['n_given']);
		$t->set_var('ad_lastname',$address[0]['n_family']);
		$t->set_var('ad_street',$address[0]['adr_street']);
		$t->set_var('ad_zip',$address[0]['adr_postalcode']);
		$t->set_var('ad_city',$address[0]['adr_locality']);
		$t->set_var('ad_state',$address[0]['adr_region']);
		$t->set_var('ad_country',$address[0]['adr_countryname']);
	}
	else
	{
		$t->set_var('error',lang('Please select currency,tax and your address in preferences !'));
		$taxpercent = ((int)0);
	}

	$charset = $phpgw->translation->translate("charset");
	$t->set_var('charset',$charset);
	$t->set_var('site_title',$phpgw_info['site_title']);
	$t->set_var('font',$phpgw_info['theme']['font']);
	$t->set_var('lang_invoice',lang('Invoice ID'));
	$t->set_var('lang_project',lang('Project'));
	$t->set_var('lang_pos',lang('Position'));
	$t->set_var('lang_workunits',lang('Workunits'));
	$t->set_var('lang_invoice_date',lang('Invoice date'));
	$t->set_var('lang_hours_date',lang('Job date'));
	$t->set_var('lang_descr',lang('Job description'));
	$t->set_var('currency',$currency);
	$t->set_var('lang_sum',lang('Sum'));
	$t->set_var('lang_per',lang('per'));
	$t->set_var('lang_mwst',lang('tax'));
	$t->set_var('lang_netto',lang('net'));

	$phpgw->db->query("SELECT phpgw_p_invoice.customer,phpgw_p_invoice.num,phpgw_p_invoice.project_id,phpgw_p_invoice.date,phpgw_p_invoice.sum as sum_netto, "
					. "round(sum*$taxpercent,2) as sum_tax,round(sum*(1+$taxpercent),2) as sum_sum,"
					. "phpgw_p_projects.title FROM phpgw_p_invoice,phpgw_p_projects WHERE "
					. "phpgw_p_invoice.id='$invoice_id' AND phpgw_p_invoice.project_id=phpgw_p_projects.id");
	$phpgw->db->next_record();

	$custadr = $phpgw->db->f('customer');

	$cols = array('n_given' => 'n_given',
				'n_family' => 'n_family',
				'org_name' => 'org_name',
				'adr_street' => 'adr_street',
			'adr_locality' => 'adr_locality',
		'adr_postalcode' => 'adr_postalcode',
			'adr_region' => 'adr_region',
		'adr_countryname' => 'adr_countryname',
				'title' => 'title');

	$customer = $d->read($custadr,$cols);                                                                                                                                                                                 

	$t->set_var('title',$customer[0]['title']);
	$t->set_var('firstname',$customer[0]['n_given']);
	$t->set_var('lastname',$customer[0]['n_family']);
	$t->set_var('company',$customer[0]['org_name']);
	$t->set_var('street',$customer[0]['adr_street']);
	$t->set_var('zip',$customer[0]['adr_postalcode']);
	$t->set_var('city',$customer[0]['adr_locality']);
	$t->set_var('state',$customer[0]['adr_region']);
	$t->set_var('country',$customer[0]['adr_countryname']);

	$invoice_date = $phpgw->db->f('date');
	$month = $phpgw->common->show_date(time(),'n');
	$day = $phpgw->common->show_date(time(),'d');
	$year = $phpgw->common->show_date(time(),'Y');
	$invoice_date = $invoice_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
	$invoice_dateout = $phpgw->common->show_date($invoice_date,$phpgw_info['user']['preferences']['common']['dateformat']);

	$t->set_var('invoice_date',$invoice_dateout);                                                                                                                                  

	$t->set_var('invoice_num',$phpgw->strip_html($phpgw->db->f('num')));
	$title = $phpgw->strip_html($phpgw->db->f('title'));
	if (! $title) { $title  = '&nbsp;'; }
	$t->set_var('title',$title);
	$t->set_var('sum_netto',$phpgw->db->f('sum_netto'));
	$t->set_var('tax_percent',$taxpercent);
	$t->set_var('sum_tax',$phpgw->db->f('sum_tax'));
	$t->set_var('sum_sum',$phpgw->db->f('sum_sum'));

	$sum_netto = $phpgw->db->f('sum_netto');

	$pos = 0;
	$sum = 0;
	$phpgw->db->query("SELECT ceiling(phpgw_p_hours.minutes/phpgw_p_hours.minperae) as aes,phpgw_p_hours.hours_descr,phpgw_p_hours.billperae,"
					. "phpgw_p_hours.billperae*(ceiling(phpgw_p_hours.minutes/phpgw_p_hours.minperae)) as sumpos,"
					. "phpgw_p_activities.descr,phpgw_p_hours.start_date FROM phpgw_p_hours,phpgw_p_activities,phpgw_p_invoicepos "
					. "WHERE phpgw_p_invoicepos.hours_id=phpgw_p_hours.id AND phpgw_p_invoicepos.invoice_id='$invoice_id' "
					. "AND phpgw_p_hours.activity_id=phpgw_p_activities.id");

	while ($phpgw->db->next_record())
	{
		$pos++;
		$t->set_var('pos',$pos);

		if ($phpgw->db->f('start_date') == 0)
		{
			$hours_dateout = '&nbsp;';
		}
		else
		{
			$month = $phpgw->common->show_date(time(),'n');
			$day = $phpgw->common->show_date(time(),'d');
			$year = $phpgw->common->show_date(time(),'Y');
			$hours_date = $hours_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
			$hours_dateout = $phpgw->common->show_date($hours_date,$phpgw_info['user']['preferences']['common']['dateformat']);
		}

		$t->set_var('hours_date',$hours_dateout);
		$t->set_var('aes',$phpgw->db->f('aes'));
		$act_descr = $phpgw->strip_html($phpgw->db->f('descr'));                                                                                                                               
		if (! $act_descr)  $act_descr  = '&nbsp;';
		$t->set_var('act_descr',$act_descr);
		$t->set_var('billperae',$phpgw->db->f('billperae'));
		$t->set_var('sumperpos',$phpgw->db->f('sumpos'));
		$sum += $phpgw->db->f('sumpos');
		$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));
		if (! $hours_descr) { $hours_descr  = '&nbsp;'; }
		$t->set_var('hours_descr',$hours_descr);

		$t->parse('list','invoicepos_list',True);
	}

	if($sum==$sum_netto) { $t->set_var('error_hint',''); }
	else { $t->set_var('error_hint',lang('Error in calculation sum doesn't match')); } 

	$t->parse('out','invoice_list_t',True);
	$t->p('out');
?>
