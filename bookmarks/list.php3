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

  $phpgw_info["flags"] = array("currentapp" => "bookmarks", "enabled_nextmatchs_class" => True);
  include("../header.inc.php");

  include(dirname(__FILE__)."/inc/bkprepend.inc");
  include(LIBDIR . "plist.inc");
  
  $account_id = $phpgw_info["user"]["account_id"];
  $phpgw->db->query("select count(*) from category where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("insert into category (name,username) values ('--','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into category (name,username) values ('Linux','$account_id')",__LINE__,__FILE__);
  }

  $phpgw->db->query("select count(*) from subcategory where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("insert into subcategory (name,username) values ('--','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into subcategory (name,username) values ('development','$account_id')",__LINE__,__FILE__);
  }

  $phpgw->db->query("select count(*) from rating where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("insert into rating (name,username) values ('--','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into rating (name,username) values ('weak','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into rating (name,username) values ('good','$account_id')",__LINE__,__FILE__);
     $phpgw->db->query("insert into rating (name,username) values ('excellent','$account_id')",__LINE__,__FILE__);
  }

  $phpgw->db->query("select count(*) from bookmark where username='$account_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();
  if ($phpgw->db->f(0) == 0) {
     $phpgw->db->query("select category.id as main, subcategory.id as sub, rating.id as rating "
                     . "from category,subcategory,rating where category.username='$account_id' "
                     . "and subcategory.username='$account_id' and rating.username='$account_id' "
                     . "and category.name='Linux' and subcategory.name='development' "
                     . "and rating.name='excellent'",__LINE__,__FILE__);
     $phpgw->db->next_record();
     $phpgw->db->query("INSERT INTO bookmark (url,name,ldesc,keywords,category_id,"
                     . "subcategory_id,rating_id,username,public_f) VALUES ('"
                     . "http://www.phpgroupware.org/','phpGroupWare','PHP','php','"
                     . $phpgw->db->f("main") . "','"
                     . $phpgw->db->f("sub") . "','"
                     . $phpgw->db->f("rating") . "','" . $account_id . "','N')",__LINE__,__FILE__);
//     $bmark = new bmark;
//     $bmark->add(&$id,"http://www.phpgroupware.org","phpGroupWare","","phpGroupWare",$phpgw->db->f("main"),$phpgw->db->f("sub"),$phpgw->db->f("rating"),"n");
  }

  $phpgw->template->set_file(array(standard   => "common.standard.tpl",
                                   body       => "list.body.tpl",
                                   first      => "list.first.tpl",
                                   prev       => "list.prev.tpl",
                                   next       => "list.next.tpl",
                                   last       => "list.last.tpl"
                            ));

# get/set the $user_last_page as a user variable.
# we use this to keep the last page nbr that the user
# was looking at so we can default in the future.
if (isset($user))
  $user->register("user_last_page");

$total_public = 0;
//if ($auth->auth["include_public"] == 'Y' ||  $auth->is_nobody() ) {
  # need to find out how many public bookmarks exist from
  # users other than this user. need this to get an accurate
  # total of bookmarks being displayed by this page.
  $phpgw->db->query("select sum(total_public_bookmarks) as total_public from auth_user where "
                  . "username != '" . $phpgw_info["user"]["account_id"] . "'");
  if ($phpgw->db->Errno == 0) {
    if ($phpgw->db->next_record()) $total_public = $phpgw->db->f("total_public");
  }
//}
$bmark = new bmark;
$total_bookmarks = $total_public + $bmark->getUserTotalBookmarks();

$phpgw->template->set_var(array(
  TOTAL_BOOKMARKS  => $total_bookmarks,
  IMAGE_URL_PREFIX => $bookmarker->image_url_prefix,
  IMAGE_EXT        => $bookmarker->image_ext
));

# get the user defined nbr of bookmarks per page
# the local admin can set this to 0 if the database
# doesn't support the use of the "LIMIT offset, nbr"
# statement.
$limit = $bookmarker->urls_per_page;

# the first page is page one
$first_page = 1;

# calculate the page number of the last page
# (divide and round UP)
if ( $limit > 0 ) {
  $last_page = ceil($total_bookmarks / $limit);
} else {
  $last_page = $first_page;
}

# if page specified in URL, then use it
if ( $page > 0 ) {

# otherwise try and bring up the last page
# this user looked at.
} elseif ( $user_last_page > 0 && $user_last_page <= $last_page ) {
  $page = $user_last_page;

# as a last resort, start at page 1
} else {
  $page = 1;
}

# if page greater than one then set first and prev page stuff
if ( $page > 1 ) {
  $first_url = $sess->url(sprintf("%s?page=%s", "list.php3", $first_page));
  $phpgw->template->set_var(FIRST_URL, $first_url);
  $phpgw->template->parse(FIRST_LINK, "first");

  $prev_page = $page - 1;
  $prev_url = $sess->url(sprintf("%s?page=%s", "list.php3", $prev_page));
  $phpgw->template->set_var(PREV_URL, $prev_url);
  $phpgw->template->parse(PREV_LINK, "prev");

# otherwise prev page stuff is null
} else {
  unset($prev_page);
}

$phpgw->template->set_var(PAGE_NBR, $page);
$phpgw->template->set_var(TOTAL_PAGES, $last_page);

# calculate the row offset (what row number do
# we start printing for this page)
$offset = ( ($page - 1) * $limit ) + $bk_db_callout->db_first_row_offset;

# if we are on the last page, set the limit to
# the max so that we can be sure we get everything
if ($page < $last_page ) {
  $last_url = $phpgw->link("list.php3","page=$last_page");
  $phpgw->template->set_var(LAST_URL, $last_url);
  $phpgw->template->parse(LAST_LINK, "last");
  
  $next_page = $page + 1;
  $next_url = $phpgw->link("list.php3","page=$last_page");
  $phpgw->template->set_var(NEXT_URL, $next_url);
  $phpgw->template->parse(NEXT_LINK, "next");
} else {
  unset($next_page);
  $limit = $total_bookmarks;
}

# store the last page this user looked at in
# a PHPLIB user var.
$user_last_page = $page;

print_list ($where_clause, $limit, $offset, sprintf("list.php3----page=%s", $page) , &$bookmark_list, &$error_msg);

$phpgw->template->set_var(BOOKMARK_LIST, $bookmark_list);

set_standard("list ($page of $last_page)", &$phpgw->template);

include(LIBDIR . "bkend.inc");
?>
