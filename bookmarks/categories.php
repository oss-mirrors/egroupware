<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True
	);

	include('../header.inc.php');
	$phpgw->bookmarks       = createobject('bookmarks.bookmarks');

	$location_info = $phpgw->bookmarks->read_session_data();

	$phpgw->template->set_file(array(
		'common'    => 'common.tpl',
		'body'      => 'categories_list.tpl',
		'row'       => 'categories_list_row.tpl',
		'empty_row' => 'categories_list_row_empty.tpl'
	));
	app_header(&$phpgw->template);

	$phpgw->template->set_var('message',$message);
	$phpgw->template->set_var('sort_name',lang('Name'));
	$phpgw->template->set_var('lang_edit',lang('Edit'));
	$phpgw->template->set_var('lang_delete',lang('Delete'));
	$phpgw->template->set_var('lang_subcategories',lang('Sub categorys'));
	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);

	if ($type == 'category')
	{
		$cats = $phpgw->categories->return_array('mains', $start, $phpgw_info['user']['preferences']['common']['maxmatchs']);
	}

	if ($type == 'subcategory')
	{
		$cats = $phpgw->categories->return_array('subs', $start, $phpgw_info['user']['preferences']['common']['maxmatchs'],'','','',False,$parent_id);
	}

	if (is_array($cats))
	{
		while ($cat = each($cats))
		{
			$phpgw->nextmatchs->template_alternate_row_color(&$phpgw->template);
			$phpgw->template->set_var('cat_name',$cat[1]['name']);
			$phpgw->template->set_var('cat_edit','<a href="' . $phpgw->link('/bookmarks/category_maintain.php','bm_id=' . $cat[1]['id'] . '&type=' . $type . '&method=edit')
	                                        . '">' . lang('Edit') . '</a>');
			$phpgw->template->set_var('cat_delete','<a href="' . $phpgw->link('/bookmarks/category_maintain.php','bm_id=' . $cat[1]['id'] . '&type=' . $type . '&method=delete')
	                                        . '">' . lang('Delete') . '</a>');
			$phpgw->template->set_var('cat_subs','<a href="' . $phpgw->link('/bookmarks/categories.php','type=subcategory&parent_id=' . $cat[1]['id'])
	                                        . '">' . lang('Sub categorys') . '</a>');
	
			$phpgw->template->parse('rows','row',True);
		}
	}
	else
	{
		$phpgw->template->set_var('lang_no_cats',lang('None found'));
		$phpgw->template->parse('rows','empty_row',True);
	}

	$phpgw->template->set_var('add_link','<a href="' . $phpgw->link('/bookmarks/category_maintain.php','type=' . $type . '&method=add&parent_id=' . $parent_id ) . '">' . lang('Add') . '</a>');

	if ($location_info['need_done_button'])
	{
		$phpgw->template->set_var('done_link','<a href="' . $phpgw->link('/bookmarks/' . $location_info['returnto']) . '">' . lang('Done') . '</a>');
	}

	$phpgw->common->phpgw_footer();
?>