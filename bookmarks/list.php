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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'bookmarks'
	);

	include('../header.inc.php');
	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');
	$GLOBALS['phpgw']->plist     = createobject('bookmarks.plist');

	$GLOBALS['phpgw']->template->set_file(array(
		'common_' => 'common.tpl',
		'body'    => 'list.body.tpl'
	));

	app_header(&$GLOBALS['phpgw']->template);

	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();
	if (! is_array($location_info))
	{
		$location_info = array(
			'start'     => 0,
			'bm_cat'    => $bm_cat,
			'bm_subcat' => $bm_subcat,
			'returnto'  => 'list.php'
		);
		$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
	}

	if (! $start && $start != 0)
	{
		$start = $location_info['start'];
		if ($bm_cat || $bm_subcat)
		{
			$location_info = array(
				'start'     => $start,
				'bm_cat'    => $bm_cat,
				'bm_subcat' => $bm_subcat,
				'returnto'  => 'list.php'
			);
			$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
		}
	}
	else
	{
		$location_info = array(
			'start'     => $start,
			'bm_cat'    => $bm_cat,
			'bm_subcat' => $bm_subcat,
			'returnto'  => 'list.php'
		);
		$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
	}

	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('filter_action',$GLOBALS['phpgw']->link('list.php'));
	$GLOBALS['phpgw']->template->set_var('lang_filter_by',lang('Filter by'));
	$GLOBALS['phpgw']->template->set_var('lang_none',lang('None'));
	$GLOBALS['phpgw']->template->set_var('lang_date_added',lang('Date Added'));
	$GLOBALS['phpgw']->template->set_var('lang_date_changed',lang('Date Changed'));
	$GLOBALS['phpgw']->template->set_var('lang_date_last_visited',lang('Date Last visited'));
	$GLOBALS['phpgw']->template->set_var('lang_url',lang('URL'));
	$GLOBALS['phpgw']->template->set_var('lang_name',lang('Name'));

	$GLOBALS['phpgw']->template->set_var('lang_asc',lang('Ascending'));
	$GLOBALS['phpgw']->template->set_var('lang_desc',lang('Descending'));
	$GLOBALS['phpgw']->template->set_var('lang_filter',lang('Filter'));

	$total_bookmarks = $GLOBALS['phpgw']->bookmarks->get_totalbookmarks();

	$GLOBALS['phpgw']->template->set_var(array(
		'TOTAL_BOOKMARKS'  => $total_bookmarks,
		'IMAGE_URL_PREFIX' => $bookmarker->image_url_prefix,
		'IMAGE_EXT'        => $bookmarker->image_ext
	));

	$GLOBALS['phpgw']->template->set_var('next_matchs_left',  $GLOBALS['phpgw']->nextmatchs->left('/bookmarks/list.php',$start,$total_bookmarks,'&bm_cat=' . $bm_cat . '&bm_subcat=' . $bm_subcat));
	$GLOBALS['phpgw']->template->set_var('next_matchs_right', $GLOBALS['phpgw']->nextmatchs->right('/bookmarks/list.php',$start,$total_bookmarks,'&bm_cat=' . $bm_cat . '&bm_subcat=' . $bm_subcat));

	if ($total_bookmarks > $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])
	{
		$total_matchs = lang('showing x - x of x',($start + 1),
			($start + $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']),$total_bookmarks);
	}
	else
	{
		$total_matchs = lang('showing x',$total_bookmarks);
	}
	$GLOBALS['phpgw']->template->set_var('showing',$total_matchs);

	// store the last page this user looked at in
	// a PHPLIB user var.
	$user_last_page = $page;

	// We need to send the $start var instead of the page number
	// Use appsession() to remeber the return page,instead of always passing it ?
	print_list($where_clause,$start,"list.php----start=$start",&$bookmark_list,&$error_msg);

	$GLOBALS['phpgw']->template->set_var('BOOKMARK_LIST', $bookmark_list);

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
