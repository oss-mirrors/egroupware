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

function  set_standard($title, &$p_tpl)  {
  global $sess, $auth, $bookmarker, $SERVER_NAME;
  global $phpgw;
  
//  $p_tpl = $phpgw->template;

/*  if (isset($auth) 
  && $auth->is_authenticated()
  && $auth->auth["uid"] != "nobody" ) {
    $name_html = sprintf("as %s", $auth->auth["name"]);
    $logout_html = sprintf("<a class=hdr href=\"%s\"><img width=24 height=24 src=\"%slogout.%s\" border=0 alt=\"Logout\"></a>"
      ,$sess->url("logout.php")
      ,$bookmarker->image_url_prefix
      ,$bookmarker->image_ext);
  } else {
    $name_html = "as Guest";
    $logout_html = sprintf("<a class=hdr href=\"%s\"><img width=24 height=24 src=\"%slogin.%s\" border=0 alt=\"Login\"></a>"
      ,$sess->url("index.php?login=YES")
      ,$bookmarker->image_url_prefix
      ,$bookmarker->image_ext);
  } */

  $p_tpl->set_var(array(
    TITLE            => $title,
    START_URL        => $phpgw->link("index.php"),
    TREE_URL         => $phpgw->link("tree.php"),
    LIST_URL         => $phpgw->link("list.php"),
    CREATE_URL       => $phpgw->link("create.php"),
    MAINTAIN_URL     => $phpgw->link("maintain.php"),
    MAILLINK_URL     => $phpgw->link("maillink.php"),
    SEARCH_URL       => $phpgw->link("search.php"),
    FAQ_URL          => $phpgw->link("faq.php"),
    CATEGORY_URL     => $phpgw->link("codes.php","codetable=bookmarks_category"),
    SUBCATEGORY_URL  => $phpgw->link("codes.php","codetable=bookmarks_subcategory"),
    RATINGS_URL      => $phpgw->link("codes.php","codetable=bookmarks_rating"),
    USER_URL         => $phpgw->link("useropt.php"),
    USER_SETTINGS_URL=> $phpgw->link("user.php"),
    IMPORT_URL       => $phpgw->link("import.php"),
    LOGOUT_HTML      => $logout_html,
    DOWNLOAD_URL     => $phpgw->link("download.php"),
    BUGS_URL         => $phpgw->link("bugs.php"),
    MAILLIST_URL     => $phpgw->link("maillist.php"),
    VERSION          => $bookmarker->version,
    IMAGE_URL_PREFIX => $bookmarker->image_url_prefix,
    IMAGE_EXT        => $bookmarker->image_ext,
    NAME_HTML        => $name_html,
    SERVER_NAME      => $SERVER_NAME
  ));
}

  // function to load a drop down list box from one
  // of the standard id-name formatted tables. this
  // routine will insert the <option> tags, it does
  // not insert the <select> tags.
  function load_ddlb($table, $selected_id, &$content, $blank)
  {
     global $bookmarker, $phpgw, $phpgw_info;

     $db_ddlb = $phpgw->db;

     $option_tpl = $phpgw->template;

     $option_tpl->set_file(array(option_list => "common.option.tpl",
                                 select      => "common.select.tpl"
                          ));

     // if the user wants a blank option, add that first
     // the blank can be selected.
     if ($blank) {
        $opt_value =  "NONE";
        $opt_name  =  "----";
        if ($selected_id == $opt_value) {
           $opt_selected = "selected";
        } else {
           $opt_selected = "";
        }
        $option_tpl->set_var(array(OPTION_SELECTED => $opt_selected,
                                   OPTION_VALUE    => $opt_value,
                                   OPTION_NAME     => $opt_name
                            ));
        $option_tpl->parse(OPTION_LIST, "option_list", TRUE);
     }

     $query = sprintf("select id, name from %s where username='%s' order by name", $table, $phpgw_info["user"]["account_id"]);
     //echo "<br><b>TEST:</b> $query";
     $db_ddlb->query($query,__LINE__,__FILE__);
     while ($db_ddlb->next_record()) {
        $cnt = $cnt + 1;
        $opt_value =  $db_ddlb->f("id");
        $opt_name  =  htmlspecialchars(stripslashes($db_ddlb->f("name")));

        if ($selected_id == $opt_value) {
           $opt_selected = "selected";
        } else {
           $opt_selected = "";
        }

        $option_tpl->set_var(array(OPTION_SELECTED => $opt_selected,
                                   OPTION_VALUE    => $opt_value,
                                   OPTION_NAME     => $opt_name
                            ));
        $option_tpl->parse(OPTION_LIST, "option_list", TRUE);
     }

     if ( $cnt > 0 ) {
        $option_tpl->set_var(FIELD_NAME, $table);
        $option_tpl->parse(MAIN, "select");
        $content = $option_tpl->get("MAIN");
     } else {
        $content = " ";
     }
  }

# function to determine what type of browser the user has.
# code idea from http://www.php.net/
function check_browser() {
  global $HTTP_USER_AGENT;

  $browser= "UNKNOWN";

  if (ereg( "MSIE",$HTTP_USER_AGENT)) {
    $browser = "MSIE";
  } elseif (ereg( "Mozilla",$HTTP_USER_AGENT)) {
    $browser = "NETSCAPE";
  } else {
    $browser = "UNKNOWN";
  }

  return $browser;
}
?>
