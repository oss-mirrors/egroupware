<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'               => 'bookmarks',
		'enable_nextmatchs_class'  => True,
		'enable_categories_class'  => True
	);
	include('../header.inc.php');

	$phpgw->template->set_file(array(
		'common'   => 'common.tpl',
		'body'     => 'form.tpl',
		'info'     => 'form_info.tpl',
		'standard' => 'common.standard.tpl'
	));

	app_header(&$phpgw->template);

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);

	$phpgw->db->query("select * from phpgw_bookmarks where bm_owner='"
			. $phpgw_info['user']['account_id'] . "' and bm_id='$bm_id'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	date_information(&$phpgw->template,$phpgw->db->f('bm_info'));
	$phpgw->template->set_var('total_visits',$phpgw->db->f('bm_visits'));

	$phpgw->template->set_var('lang_added',lang('Date added'));
	$phpgw->template->set_var('lang_updated',lang('Date last updated'));
	$phpgw->template->set_var('lang_visited',lang('Date last visited'));
	$phpgw->template->set_var('lang_visits',lang('Total visits'));

	$phpgw->template->parse('info','info');

	$phpgw->template->set_var('form_action',$phpgw->link());
	$phpgw->template->set_var('lang_url',lang('URL'));
	$phpgw->template->set_var('lang_name',lang('Name'));
	$phpgw->template->set_var('lang_desc',lang('Description'));
	$phpgw->template->set_var('lang_keywords',lang('Keywords'));

	$phpgw->template->set_var('lang_category',lang('Category'));
	$phpgw->template->set_var('lang_subcategory',lang('Sub Category'));
	$phpgw->template->set_var('lang_rating',lang('Rating'));

	$phpgw->template->set_var('lang_access',lang('Access'));
	$phpgw->template->set_var('input_access',lang($phpgw->db->f('bm_access')));

	$phpgw->template->set_var('lang_header',lang('View bookmark'));

	$phpgw->template->set_var('input_url','<a href="' . $phpgw->link('/bookmarks/redirect.php','bm_id=' . $phpgw->db->f('bm_id')) . '" target="_new">' . $phpgw->db->f('bm_url') . '</a>');
	$phpgw->template->set_var('input_name',$phpgw->db->f('bm_name'));
	$phpgw->template->set_var('input_desc',$phpgw->db->f('bm_desc'));
	$phpgw->template->set_var('input_keywords',$phpgw->db->f('bm_keywords'));
	$phpgw->template->set_var('input_rating','<img src="' . $phpgw->common->get_image_path('bookmarks') . '/bar-' . $phpgw->db->f('bm_rating') . '.jpg">');
	$phpgw->template->set_var('input_category',$phpgw->categories->return_name($phpgw->db->f('bm_category')));
	$phpgw->template->set_var('input_subcategory',$phpgw->categories->return_name($phpgw->db->f('bm_subcategory')));

	$phpgw->template->set_var('delete_link','');
	$phpgw->template->set_var('cancel_link','');
	$phpgw->template->set_var('edit_link','');

	$phpgw->common->phpgw_footer();
?>