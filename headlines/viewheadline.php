<?php
	/**************************************************************************\
	* phpGroupWare - Headlines Administration                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'admin',
		'enable_nextmatchs_class' => True,
		'nonavbar'                => True,
		'noheader'                => True
	);
	include('../header.inc.php');

	if (! $con)
	{
		$phpgw->redirect($phpgw->link('/headlines/admin.php'));
	}
	
	$phpgw->common->phpgw_header();
	echo parse_navbar();

	$phpgw->db->query("select * from phpgw_headlines_sites where con='$con'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	// This is done for a reason (jengo)
	$phpgw->template->set_root($phpgw->common->get_tpl_dir('headlines'));

	$phpgw->template->set_file(array(
		'admin_form' => 'admin_form.tpl'
	));
	$phpgw->template->set_block('admin_form','form');
	$phpgw->template->set_block('admin_form','listing_row');
	$phpgw->template->set_block('admin_form','listing_rows');

	$phpgw->template->set_var('title',lang('Headlines Administration'));
	$phpgw->template->set_var('lang_header',lang('View headline'));
	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('row_on',$phpgw_info['theme']['row_on']);
	$phpgw->template->set_var('row_off',$phpgw_info['theme']['row_off']);
	$phpgw->template->set_var('lang_display',lang('Display'));
	$phpgw->template->set_var('lang_base_url',lang('Base URL'));
	$phpgw->template->set_var('lang_news_file',lang('News File'));
	$phpgw->template->set_var('lang_minutes',lang('Minutes between refresh'));
	$phpgw->template->set_var('lang_listings',lang('Listings Displayed'));
	$phpgw->template->set_var('lang_type',lang('News Type'));

	$phpgw->template->set_var('input_display',$phpgw->db->f('display'));
	$phpgw->template->set_var('input_base_url',$phpgw->db->f('base_url'));
	$phpgw->template->set_var('input_news_file',$phpgw->db->f('newsfile'));
	$phpgw->template->set_var('input_minutes',$phpgw->common->show_date($phpgw->db->f('lastread')));
	$phpgw->template->set_var('input_listings',$phpgw->db->f('cachetime'));
	$phpgw->template->set_var('input_type',$phpgw->db->f('newstype'));


	$phpgw->db->query("select title,link from phpgw_headlines_cached where site='$con'",__LINE__,__FILE__);

	$phpgw->template->set_var('th_bg2',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('lang_current_cache',lang('Current headlines in cache'));

	if ($phpgw->db->num_rows() == 0)
	{
		$phpgw->nextmatchs->template_alternate_row_color($phpgw->template);
		$phpgw->template->set_var('value',lang('None'));
		$phpgw->template->parse('listing_rows','listing_row',True);
	}

	while ($phpgw->db->next_record())
	{
		$phpgw->nextmatchs->template_alternate_row_color($phpgw->template);
		$phpgw->template->set_var('value','<a href="' . $phpgw->db->f('link') . '" target="_new">' . $phpgw->db->f('title') . '</a>');
		$phpgw->template->parse('listing_rows','listing_row',True);
	}

	$phpgw->template->pfp('out','form');
	$phpgw->common->phpgw_footer();
?>
