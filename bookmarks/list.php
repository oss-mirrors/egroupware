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

  $phpgw_info["flags"] = array("currentapp" => "bookmarks", "enable_nextmatchs_class" => True);

  include("../header.inc.php");
  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/plist.inc.php");

  $account_id = $phpgw_info["user"]["account_id"];	// only temp

  $phpgw->db->query("select count(*) from bookmarks_category where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("insert into bookmarks_category (name,username) values ('--','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into bookmarks_category (name,username) values ('Linux','$account_id')",__LINE__,__FILE__);
  }

  $phpgw->db->query("select count(*) from bookmarks_subcategory where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("insert into bookmarks_subcategory (name,username) values ('--','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into bookmarks_subcategory (name,username) values ('development','$account_id')",__LINE__,__FILE__);
  }

  $phpgw->db->query("select count(*) from bookmarks where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("select id from bookmarks_category where username='"
                     . "$account_id' and name='Linux'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $maincat_id = $phpgw->db->f("id");

     $phpgw->db->query("select id from bookmarks_subcategory where username='"
                     . "$account_id' and name='development'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $subcat_id = $phpgw->db->f("id");

     $phpgw->db->query("select id from bookmarks_rating where username='$account_id' and name='"
                     . "excellent'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $rating_id = $phpgw->db->f("id");

     $phpgw->db->query("INSERT INTO bookmarks (url,name,ldesc,keywords,category_id,"
                     . "subcategory_id,rating_id,username,public_f,bm_timestamps) VALUES ('"
                     . "http://www.phpgroupware.org/','phpGroupWare','PHP','php','$maincat_id','"
                     . $subcat_id . "','10','$account_id','N','" . time() . ",,')",__LINE__,__FILE__);
     unset($subcat_id);
     unset($rating_id);
     unset($main_catid);
  }

  $phpgw->template->set_file(array(standard   => "common.standard.tpl",
                                   body       => "list.body.tpl"
//                                   first      => "list.first.tpl",
//                                   prev       => "list.prev.tpl",
//                                   next       => "list.next.tpl",
//                                   last       => "list.last.tpl"
                            ));

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

  print_list($where_clause,$start,sprintf("list.php----page=%s",$page),&$bookmark_list,&$error_msg);

  $phpgw->template->set_var(BOOKMARK_LIST, $bookmark_list);

  set_standard("list ($page of $last_page)", &$phpgw->template);

  $phpgw->common->phpgw_footer();
?>
