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

	$account_id = $phpgw_info['user']['account_id'];	// only temp

	$phpgw->template->set_file(array('common' => 'common.tpl',
                                    'body'   => 'list.body.tpl'
//                                   first      => "list.first.tpl",
//                                   prev       => "list.prev.tpl",
//                                   next       => "list.next.tpl",
//                                   last       => "list.last.tpl"
                            ));

  app_header(&$phpgw->template);

  $phpgw->template->set_var("filter_action",$phpgw->link("list.php"));
  $phpgw->template->set_var("lang_filter_by",lang("Filter by"));
  $phpgw->template->set_var("lang_none",lang("None"));
  $phpgw->template->set_var("lang_date_added",lang("Date Added"));
  $phpgw->template->set_var("lang_date_changed",lang("Date Changed"));
  $phpgw->template->set_var("lang_date_last_visited",lang("Date Last visited"));
  $phpgw->template->set_var("lang_url",lang("URL"));
  $phpgw->template->set_var("lang_name",lang("Name"));

  $phpgw->template->set_var("lang_asc",lang("Ascending"));
  $phpgw->template->set_var("lang_desc",lang("Descending"));
  $phpgw->template->set_var("lang_filter",lang("Filter"));

  // get/set the $user_last_page as a user variable.
  // we use this to keep the last page nbr that the user
  // was looking at so we can default in the future.
  //if (isset($user))
  //   $user->register("user_last_page");

$total_public = 0;
//if ($auth->auth["include_public"] == 'Y' ||  $auth->is_nobody() ) {
  # need to find out how many public bookmarks exist from
  # users other than this user. need this to get an accurate
  # total of bookmarks being displayed by this page.
/*  $phpgw->db->query("select sum(total_public_bookmarks) as total_public from auth_user where "
                  . "username != '" . $phpgw_info["user"]["account_id"] . "'");
  if ($phpgw->db->Errno == 0) {
    if ($phpgw->db->next_record()) $total_public = $phpgw->db->f("total_public");
  } */
//}
  $bmark = new bmark;
  $bmark->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);
  $total_bookmarks = $total_public + $bmark->getUserTotalBookmarks();

  $phpgw->template->set_var(array(TOTAL_BOOKMARKS  => $total_bookmarks,
                                  IMAGE_URL_PREFIX => $bookmarker->image_url_prefix,
                                  IMAGE_EXT        => $bookmarker->image_ext
                                 ));

  $phpgw->template->set_var(next_matchs_left,  $phpgw->nextmatchs->left("list.php",$start,$total_bookmarks));
  $phpgw->template->set_var(next_matchs_right, $phpgw->nextmatchs->right("list.php",$start,$total_bookmarks));

  $phpgw->template->set_var(PAGE_NBR, $page);
  $phpgw->template->set_var(TOTAL_PAGES, $last_page);


  // store the last page this user looked at in
  // a PHPLIB user var.
  $user_last_page = $page;

  // We need to send the $start var instead of the page number
  // Use appsession() to remeber the return page,instead of always passing it ?
  print_list($where_clause,$start,"list.php----start=$start",&$bookmark_list,&$error_msg);

  $phpgw->template->set_var(BOOKMARK_LIST, $bookmark_list);

  // There needs to be a function in the nextmatchs class to handle this
  //set_standard("list ($page of $last_page)", &$phpgw->template);

  $phpgw->common->phpgw_footer();
?>
