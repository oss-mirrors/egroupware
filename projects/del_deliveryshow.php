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
									'noheader' => True,
									'nonavbar' => True);
	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('delivery_list_t' => 'del_deliveryform.tpl',
					'deliverypos_list' => 'del_deliveryform.tpl'));
	$t->set_block('delivery_list_t','deliverypos_list','list');

	$d = CreateObject('phpgwapi.contacts');

	if (isset($phpgw_info['user']['preferences']['projects']['abid']))
	{
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
		$t->set_var('error',lang('Please select your address in preferences !'));                                                                                  
	}

	$t->set_var('site_title',$phpgw_info['site_title']);
	$charset = $phpgw->translation->translate('charset');
	$t->set_var('charset',$charset);
	$t->set_var('font',$phpgw_info['theme']['font']);
	$t->set_var('lang_delivery',lang('Delivery ID'));
	$t->set_var('lang_project',lang('Project'));
	$t->set_var('lang_pos',lang('Position'));
	$t->set_var('lang_workunits',lang('Workunits'));
	$t->set_var('lang_delivery_date',lang('Delivery date'));
	$t->set_var('lang_hours_date',lang('Job date'));      
	$t->set_var('lang_descr',lang('Job description'));

	$phpgw->db->query("SELECT phpgw_p_delivery.customer,phpgw_p_delivery.num,phpgw_p_delivery.project_id,phpgw_p_delivery.date, "
					. "phpgw_p_projects.title FROM phpgw_p_delivery,phpgw_p_projects WHERE "
					. "phpgw_p_delivery.id='$delivery_id' AND phpgw_p_delivery.project_id=phpgw_p_projects.id");
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

	$delivery_date = $phpgw->db->f('date');
	$month = $phpgw->common->show_date(time(),'n');
	$day = $phpgw->common->show_date(time(),'d');
	$year = $phpgw->common->show_date(time(),'Y');
	$delivery_date = $delivery_date + (60*60) * $phpgw_info['user']['preferences']['common']['tz_offset'];
	$delivery_dateout = $phpgw->common->show_date($delivery_date,$phpgw_info['user']['preferences']['common']['dateformat']);
	$t->set_var('delivery_date',$delivery_dateout);

	$t->set_var('delivery_num',$phpgw->strip_html($phpgw->db->f('num')));
	$title = $phpgw->strip_html($phpgw->db->f('title'));
	if (! $title) { $title  = '&nbsp;'; }
	$t->set_var('title',$title);

	$pos = 0;

	$phpgw->db->query("SELECT phpgw_p_hours.hours_descr,phpgw_p_hours.minperae,phpgw_p_hours.minutes,"                                                                                                        
					. "phpgw_p_activities.descr,phpgw_p_hours.start_date FROM phpgw_p_hours,phpgw_p_activities,phpgw_p_deliverypos "                                                                                          
					. "WHERE phpgw_p_deliverypos.hours_id=phpgw_p_hours.id AND phpgw_p_deliverypos.delivery_id='$delivery_id' "                                                                               
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

		if ($phpgw->db->f('minperae') != 0)
		{
			$aes = ceil($phpgw->db->f('minutes')/$phpgw->db->f('minperae'));
		}
		$sumaes += $aes;

		$t->set_var('aes',$aes);
		$act_descr = $phpgw->strip_html($phpgw->db->f('descr'));                                                                                                                         
		if (! $act_descr) { $act_descr  = '&nbsp;'; }                                                                                                                                       
		$t->set_var('act_descr',$act_descr);
		$t->set_var('billperae',$phpgw->db->f('billperae'));
		$hours_descr = $phpgw->strip_html($phpgw->db->f('hours_descr'));                                                                                                                           
		if (! $hours_descr) { $hours_descr  = '&nbsp;'; }                                                                                                                                             
		$t->set_var('hours_descr',$hours_descr);
		$t->parse('list','deliverypos_list',True);
	}

	$t->set_var('lang_sumaes',lang('Sum workunits'));
	$t->set_var('sumaes',$sumaes);

	$t->parse('out','delivery_list_t',True);
	$t->p('out');
?>
