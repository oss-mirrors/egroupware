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
	include(PHPGW_APP_ROOT . '/inc/plist.inc.php');
	$phpgw->bookmarks       = createobject('bookmarks.bookmarks');

	$phpgw->template->set_file(array(
		'common' => 'common.tpl',
		'body'   => 'list.body.tpl'
	));

	app_header(&$phpgw->template);

	$location_info = $phpgw->bookmarks->read_session_data();
	if (! is_array($location_info))
	{
		$location_info = array(
			'start'    => 0,
			'returnto' => 'list.php'
		);
		$phpgw->bookmarks->save_session_data($location_info);
	}

	if (!isset($start))
	{
		$start = $location_info['start'];
	}
	else
	{
		$location_info = array(
			'start'    => $start,
			'returnto' => 'list.php'
		);
		$phpgw->bookmarks->save_session_data($location_info);
	}

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('filter_action',$phpgw->link('list.php'));
	$phpgw->template->set_var('lang_filter_by',lang('Filter by'));
	$phpgw->template->set_var('lang_none',lang('None'));
	$phpgw->template->set_var('lang_date_added',lang('Date Added'));
	$phpgw->template->set_var('lang_date_changed',lang('Date Changed'));
	$phpgw->template->set_var('lang_date_last_visited',lang('Date Last visited'));
	$phpgw->template->set_var('lang_url',lang('URL'));
	$phpgw->template->set_var('lang_name',lang('Name'));

	$phpgw->template->set_var('lang_asc',lang('Ascending'));
	$phpgw->template->set_var('lang_desc',lang('Descending'));
	$phpgw->template->set_var('lang_filter',lang('Filter'));

  // get/set the $user_last_page as a user variable.
  // we use this to keep the last page nbr that the user
  // was looking at so we can default in the future.
  //if (isset($user))
  //   $user->register("user_last_page");

	$total_bookmarks = $phpgw->bookmarks->get_totalbookmarks();

	$phpgw->template->set_var(array(
		'TOTAL_BOOKMARKS'  => $total_bookmarks,
		'IMAGE_URL_PREFIX' => $bookmarker->image_url_prefix,
		'IMAGE_EXT'        => $bookmarker->image_ext
	));

	$phpgw->template->set_var(next_matchs_left,  $phpgw->nextmatchs->left('/bookmarks/list.php',$start,$total_bookmarks));
	$phpgw->template->set_var(next_matchs_right, $phpgw->nextmatchs->right('/bookmarks/list.php',$start,$total_bookmarks));

	if ($total_bookmarks > $phpgw_info['user']['preferences']['common']['maxmatchs'])
	{
		$total_matchs = lang('showing x - x of x',($start + 1),
			($start + $phpgw_info['user']['preferences']['common']['maxmatchs']),$total_bookmarks);
	}
	else
	{
		$total_matchs = lang('showing x',$total_bookmarks);
	}
	$phpgw->template->set_var('showing',$total_matchs);


  // store the last page this user looked at in
  // a PHPLIB user var.
  $user_last_page = $page;

  // We need to send the $start var instead of the page number
  // Use appsession() to remeber the return page,instead of always passing it ?
  print_list($where_clause,$start,"list.php----start=$start",&$bookmark_list,&$error_msg);

  $phpgw->template->set_var(BOOKMARK_LIST, $bookmark_list);

  $phpgw->common->phpgw_footer();
?>
