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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'               => 'bookmarks',
		'enable_nextmatchs_class'  => True,
		'enable_categories_class'  => True,
		'noheader'                 => True,
		'nonavbar'                 => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');

	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();
	if ($cancel_x || $cancel_y)
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/' . $location_info['returnto'],'bm_cat=' . $location_info['bm_cat']));
	}

	if ($edit_x || $edit_y)
	{
		$location_info['returnto'] = 'view.php';
		$location_info['bm_id']    = $bm_id;
		$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/maintain.php','bm_id=' . $bm_id));
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'common_'  => 'common.tpl',
		'form'     => 'form.tpl',
		'standard' => 'common.standard.tpl'
	));
	$GLOBALS['phpgw']->template->set_block('form','body');
	$GLOBALS['phpgw']->template->set_block('form','form_info');

	if (! $GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_READ))
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/list.php','bm_cat=' . $location_info['bm_cat']));
	}

	$GLOBALS['phpgw']->common->phpgw_header();
	include(PHPGW_APP_INC . '/header.inc.php');
	echo parse_navbar();

	app_header(&$GLOBALS['phpgw']->template);

	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);

	$GLOBALS['phpgw']->db->query("select * from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();

	date_information(&$GLOBALS['phpgw']->template,$GLOBALS['phpgw']->db->f('bm_info'));
	$GLOBALS['phpgw']->template->set_var('total_visits',$GLOBALS['phpgw']->db->f('bm_visits'));

	$GLOBALS['phpgw']->template->set_var('lang_owner',lang('Created by'));

	$account = createobject('phpgwapi.accounts',$GLOBALS['phpgw']->db->f('bm_owner'));
	$ad      = $account->read_repository();
	$GLOBALS['phpgw']->template->set_var('owner_value',$GLOBALS['phpgw']->common->display_fullname($ad['account_lid'],$ad['firstname'],$ad['lastname']));

	$GLOBALS['phpgw']->template->set_var('lang_added',lang('Date added'));
	$GLOBALS['phpgw']->template->set_var('lang_updated',lang('Date last updated'));
	$GLOBALS['phpgw']->template->set_var('lang_visited',lang('Date last visited'));
	$GLOBALS['phpgw']->template->set_var('lang_visits',lang('Total visits'));

	$GLOBALS['phpgw']->template->parse('info','form_info');

	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/bookmarks/view.php','bm_id=' . $bm_id));
	$GLOBALS['phpgw']->template->set_var('lang_url',lang('URL'));
	$GLOBALS['phpgw']->template->set_var('lang_name',lang('Name'));
	$GLOBALS['phpgw']->template->set_var('lang_desc',lang('Description'));
	$GLOBALS['phpgw']->template->set_var('lang_keywords',lang('Keywords'));

	$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
	$GLOBALS['phpgw']->template->set_var('lang_rating',lang('Rating'));

	$GLOBALS['phpgw']->template->set_var('lang_access',lang('Access'));
	$GLOBALS['phpgw']->template->set_var('input_access',lang($GLOBALS['phpgw']->db->f('bm_access')));

	$GLOBALS['phpgw']->template->set_var('lang_header',lang('View bookmark'));

	$GLOBALS['phpgw']->template->set_var('input_url','<a href="' . $GLOBALS['phpgw']->link('/bookmarks/redirect.php','bm_id=' . $GLOBALS['phpgw']->db->f('bm_id'))
													. '" target="_new">' . $GLOBALS['phpgw']->db->f('bm_url') . '</a>');
	$GLOBALS['phpgw']->template->set_var('input_name',$GLOBALS['phpgw']->db->f('bm_name'));
	$GLOBALS['phpgw']->template->set_var('input_desc',$GLOBALS['phpgw']->db->f('bm_desc'));
	$GLOBALS['phpgw']->template->set_var('input_keywords',$GLOBALS['phpgw']->db->f('bm_keywords'));
	$GLOBALS['phpgw']->template->set_var('input_rating','<img src="' . $GLOBALS['phpgw']->common->get_image_path('bookmarks') . '/bar-' . $GLOBALS['phpgw']->db->f('bm_rating'). '.jpg">');

	$category    = $GLOBALS['phpgw']->strip_html($GLOBALS['phpgw']->categories->return_name($GLOBALS['phpgw']->db->f('bm_category')));
	$subcategory = $GLOBALS['phpgw']->strip_html($GLOBALS['phpgw']->categories->return_name($GLOBALS['phpgw']->db->f('bm_subcategory')));
	if ($subcategory)
	{
		$category .= ' :: ' . $subcategory;
	}
	$GLOBALS['phpgw']->template->set_var('input_category',$category);

	$GLOBALS['phpgw']->template->set_var('cancel_button','<input type="image" name="cancel" title="' . lang('Done') . '" src="'
										. $GLOBALS['phpgw']->common->image('bookmarks','cancel') . '" border="0">');

	if ($GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_EDIT))
	{
		$GLOBALS['phpgw']->template->set_var('edit_button','<input type="image" name="edit" title="' . lang('Edit') . '" src="'
										. $GLOBALS['phpgw']->common->image('bookmarks','edit') . '" border="0">');
	}

	if ($GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
	{
		$GLOBALS['phpgw']->template->set_var('delete_button','<input type="image" name="delete" title="' . lang('Delete') . '" src="'
										. $GLOBALS['phpgw']->common->image('bookmarks','delete') . '" border="0">');
	}

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
