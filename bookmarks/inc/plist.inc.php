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

  function print_list_break (&$list_tpl, $category, $subcategory)
  {
    global $phpgw;
  
    // construct URLs that include WHERE clauses for linking to the
    // search page. The Category link will show a search WHERE the
    // category matches. The sub-cat link will show a search WHERE
    // the subcategory matches. Need to encode the URL since it contains
    // single-quotes, equal sign, and possibly spaces.
    // we use base64 coding rather than urlencode and rawencode since
    // it seems to be more reliable.

    $cat_search    = $phpgw->link("/bookmarks/search.php","where=" . urlencode("category.name='$category'"));
    $subcat_search = $phpgw->link("/bookmarks/search.php","where=" . urlencode("subcategory.name='$subcategory'"));

    $list_tpl->set_var(array(CATEGORY           => htmlspecialchars(stripslashes($category)),
//                           CATEGORY_SEARCH    => $cat_search,
                             SUBCATEGORY        => htmlspecialchars(stripslashes($subcategory)),
//                           SUBCATEGORY_SEARCH => $subcat_search
                      ));
        
    $list_tpl->parse(LIST_HDR, "header");
    $list_tpl->parse(LIST_FTR, "footer");
    $list_tpl->parse(CONTENT, "list_section", TRUE);
    $list_tpl->set_var("LIST_ITEMS", "");
  }

  function print_list ($where_clause, $start, $returnto, &$content, &$error_msg)
  {
    global $bookmarker, $phpgw, $phpgw_info;

    $list_tpl = $phpgw->template;
  
    // if no action, then show the same list as last time
    // this page was viewed. the session start variables 
    // should be set by the register function

    // every bookmarker page uses templates to generate HTML.

    $list_tpl->set_unknowns("remove");

    $list_tpl->set_file(array(list_section   => "common.list.section.tpl",
                              header         => "common.list.hdr.tpl",
                              footer         => "common.list.ftr.tpl",
                              list_item      => "common.list.item.tpl",
                              item_keyw      => "common.list.item_keyw.tpl"
                       ));

		$list_tpl->set_var('list_mass_select_form',$phpgw->link('/bookmarks/mass_maintain.php'));
		$list_tpl->set_var('lang_massupdate',lang('Mass update'));

   // db callout to set big temporary tables option
   // $bk_db_callout->set_big_temp_tables ($bk_c);

   // you can see/search anything that you own, and anything that others
   // have marked as public if you have indicated so on your auth_user record.
   //  if ($auth->auth["include_public"] == "Y" || $auth->is_nobody()) 
   //     $public_sql = " or bookmark.public_f='Y' ";


   $query = sprintf("select * from phpgw_bookmarks where (bm_owner='%s' or bm_access='public' %s)",
                    $phpgw_info["user"]["account_id"],
                    $phpgw->common->sql_search("bm_access"));  

   if ($where_clause != "") {
      $where_clause_sql = " and " . $where_clause;
   } else {
      $where_clause_sql = " ";
   }

//   $order_by_sql = " order by bookmarks_category.name, bookmarks_subcategory.name, bookmarks.name, bookmarks.id";
  
   $query .= $where_clause_sql . $order_by_sql . $phpgw->db->limit($start);
  
   $phpgw->db->query($query,__LINE__,__FILE__);

   $prev_category_id = -1;
   $prev_subcategory_id = -1;
   $rows_printed = 0;

   while ($phpgw->db->next_record()) {
      $category_name    = $phpgw->categories->return_name($phpgw->db->f("bm_category"));
      $subcategory_name = $phpgw->categories->return_name($phpgw->db->f("bm_subcategory"));

      $rows_printed++;

      if (($category_name != $prev_category) or ($subcategory_name != $prev_subcategory)) {

         if ($rows_printed > 1) {
            print_list_break(&$list_tpl, $prev_category, $prev_subcategory);
         }
         $prev_category       = $category_name;
         $prev_subcategory    = $subcategory_name;
      }

      if ($phpgw->db->f("bm_keywords")) {
         $list_tpl->set_var(BOOKMARK_KEYW, htmlspecialchars(stripslashes($phpgw->db->f("bm_keywords"))));
         $list_tpl->parse(KEYWORDS,"item_keyw");
      } else {
         $list_tpl->set_var(KEYWORDS, "");
      }

      // Check owner
      if ($phpgw->db->f("bm_owner") == $phpgw_info["user"]["account_id"]) {
         $maintain_url  = $phpgw->link("/bookmarks/maintain.php","bm_id=" . $phpgw->db->f("bm_id") . "&returnto=" . urlencode($returnto));
         $maintain_link = sprintf("<a href=\"%s\"><img src=\"%s%s.%s\" align=top border=0 alt=\"Edit this Bookmark\"></a>", $maintain_url, $bookmarker->image_url_prefix, "edit", $bookmarker->image_ext);

         $view_url  = $phpgw->link("/bookmarks/view.php","bm_id=" . $phpgw->db->f("bm_id") . "&returnto=" . urlencode($returnto));
         $view_link     = sprintf("<a href=\"%s\"><img src=\"" . $phpgw_info["server"]["app_images"] . "/document.gif\" align=top border=0 alt=\"" . lang("View this Bookmark") . "\"></a>", $view_url);
      } else {
         $maintain_link = sprintf("<!-- owned by: %s -->&nbsp;", $phpgw->db->f("bm_owner"));
      }

		$list_tpl->set_var('checkbox','<input type="checkbox" name="item_cb[]" value="' . $phpgw->db->f('bm_id') . '">');
      if ($phpgw->acl->check("anonymous",1,"bookmarks")) {
         $list_tpl->set_var("MAIL_THIS_LINK_URL",$phpgw->link("/bookmarks/maillink.php","bm_id=".$phpgw->db->f("bm_id")));
      } else {
         $list_tpl->set_var("MAIL_THIS_LINK_URL","&nbsp;");
      }

      $list_tpl->set_var(array(MAINTAIN_LINK      => $maintain_link,
                               "img_root"         => $phpgw_info["server"]["app_images"],
                               VIEW_LINK          => $view_link,
                               RATING             => $phpgw->db->f("bm_rating"),
//                               MAIL_THIS_LINK_URL => $phpgw->link("maillink.php","id=".$phpgw->db->f("id")),
                               BOOKMARK_USERNAME  => $phpgw->db->f("bm_owner"),
                               BOOKMARK_ID        => $phpgw->db->f("bm_id"),
                               BOOKMARK_URL       => $phpgw->link('/bookmarks/redirect.php','bm_id=' . $phpgw->db->f('bm_id')),
                               BOOKMARK_RATING    => htmlspecialchars(stripslashes($phpgw->db->f("bm_rating"))),
                               BOOKMARK_RATING_ID => $phpgw->db->f("bm_rating"),
                               BOOKMARK_NAME      => htmlspecialchars(stripslashes($phpgw->db->f("bm_name"))),
                               BOOKMARK_DESC      => nl2br(htmlspecialchars(stripslashes($phpgw->db->f("bm_desc")))),
                               IMAGE_URL_PREFIX   => $bookmarker->image_url_prefix,
                               IMAGE_EXT          => $bookmarker->image_ext
                        ));

      $list_tpl->parse(LIST_ITEMS, "list_item", TRUE);
   }

   if ($rows_printed > 0) {
      print_list_break(&$list_tpl, $prev_category, $prev_subcategory);
      $content = $list_tpl->get("CONTENT");
   }
 }
?>
