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
		'enable_categories_class'  => True,
		'noheader'                 => True,
		'nonavbar'                 => True
	);
	include('../header.inc.php');
	$phpgw->bookmarks = createobject('bookmarks.bookmarks');

	$location_info = $phpgw->bookmarks->read_session_data();
	if ($cancel_x || $cancel_y)
	{
		$phpgw->redirect($phpgw->link('/bookmarks/' . $location_info['returnto']));
	}

	if ($edit_x || $edit_y)
	{
		$location_info['returnto'] = 'view.php';
		$location_info['bm_id']    = $bm_id;
		$phpgw->bookmarks->save_session_data($location_info);
		$phpgw->redirect($phpgw->link('/bookmarks/maintain.php','bm_id=' . $bm_id));
	}

	$phpgw->template->set_file(array(
		'common'   => 'common.tpl',
		'body'     => 'form.tpl',
		'info'     => 'form_info.tpl',
		'standard' => 'common.standard.tpl'
	));

	if (! $phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_READ))
	{
		$phpgw->redirect($phpgw->link('/bookmarks/list.php'));
	}

	$phpgw->common->phpgw_header();
	include(PHPGW_APP_INC . '/header.inc.php');
	echo parse_navbar();

	app_header(&$phpgw->template);

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);

	$phpgw->db->query("select * from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
	$phpgw->db->next_record();

	date_information(&$phpgw->template,$phpgw->db->f('bm_info'));
	$phpgw->template->set_var('total_visits',$phpgw->db->f('bm_visits'));

	$phpgw->template->set_var('lang_owner',lang('Created by'));

	$account = createobject('phpgwapi.accounts',$phpgw->db->f('bm_owner'));
	$ad      = $account->read_repository();
	$phpgw->template->set_var('owner_value',$phpgw->common->display_fullname($ad['account_lid'],$ad['firstname'],$ad['lastname']));

	$phpgw->template->set_var('lang_added',lang('Date added'));
	$phpgw->template->set_var('lang_updated',lang('Date last updated'));
	$phpgw->template->set_var('lang_visited',lang('Date last visited'));
	$phpgw->template->set_var('lang_visits',lang('Total visits'));

	$phpgw->template->parse('info','info');

	$phpgw->template->set_var('form_action',$phpgw->link('/bookmarks/view.php','bm_id=' . $bm_id));
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

	$phpgw->template->set_var('cancel_button','<input type="image" name="cancel" title="' . lang('Done') . '" src="' . PHPGW_IMAGES . '/cancel.gif" border="0">');

	if ($phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_EDIT))
	{
		$phpgw->template->set_var('edit_button','<input type="image" name="edit" title="' . lang('Edit') . '" src="' . PHPGW_IMAGES . '/edit.gif" border="0">');
	}

	if ($phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
	{
		$phpgw->template->set_var('delete_button','<input type="image" name="delete" title="' . lang('Delete') . '" src="' . PHPGW_IMAGES . '/delete.gif" border="0">');
	}

	$phpgw->common->phpgw_footer();
?>