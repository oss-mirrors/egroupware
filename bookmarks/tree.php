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

//  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  $phpgw_info["flags"]["currentapp"] = "bookmarks";
  $phpgw_info["flags"]["enabled_nextmatchs_class"] = True;
  include("../header.inc.php");

  # if mode is not GET, then we want to redirect
  # back to ourselves which will put us in GET mode.
  # The reason is the javascript reload() and go()
  # functions give the annoying
  # "the page cannot be refreshed without resending..."
  # message.
  # Note: we should only be here in POST mode if
  # user clicked tree view without already being
  # authenticated, then the login will do a POST back
  # to itself - in this case the tree.php page.
  if (strtoupper($REQUEST_METHOD) != "GET") {
  
  # use a 303 since http spec states only a 303,
  # not a 302 or 301 is allowed after a POST.
    header("Status: 303 See Other");
  
  # netscape bug won't do redirect a page to itself
  # after a POST, therefore we add the id string to the url.
  $url = ((isset($HTTPS)&&$HTTPS=='on')?"https":"http")
    ."://"
    .$HTTP_HOST
    .$phpgw->link("","id=" . time());
//    .(strstr($phpgw->link(),"?")?"&":"?")
//    ."id="
//    .time();
  header("Location: ".$url);
  $phpgw->common->phpgw_exit();
}

  $phpgw->template->set_file(array("common" => "common.tpl",
                                   "body"   => "tree.body.tpl"
                            ));

  app_header(&$phpgw->template);
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("lang_tree_view",lang("Tree view"));

  # we keep a user variable that holds the last selection
  # the user made for the groupby option
  //$user->register("last_groupby");
  
  # if no action, then show the same list as last time
  # this page was viewed. the session start variables 
  # should be set by the register function
  //$bk_c = new bk_db;

  ## Check if there was a submission
  if (!empty($bks_load)) {
     ## if form submitted, then if groupby has no value it
     ## means the user deselected it, set it to FALSE
    if (!isset($groupby)) {
       $groupby = FALSE;
    }

  ## Do we have all necessary data?
  ## if search isn't greater than zero, then it is "NONE"
  ## or not defined, in this case reset the where clause vars
  ## and exit the loop. this will cause no where clause to
  ## be applied - in effect resetting the query to ALL.
  if ($search > 0 ) {
    ## get the saved search
    $query = sprintf("select query from search where id=%s and username='%s'",$search,$phpgw_info["user"]["account_id"]);
    $phpgw->db->query($query,__LINE__,__FILE__);
    if ($phpgw->db->Errno == 0) {
       if ($bphpgw->db->next_record()){
          $saved_search = $phpgw->db->f("query");
       } else {
         $error_msg .= "Saved Search not found in database!";
       }
    }
  } else {
    unset ($saved_search);
    unset ($where);
  }
}

# if the groupby var is still not set by now, that means that
# this was not a form submission. in that case we default the
# groupby var to whatever the user last selected (stored in a 
# user variable). if the user variable is not set yet, default
# to groupby ON.
if (!isset($groupby)) {
  if (isset($last_groupby)) {
    $groupby = $last_groupby;
  } else {
    $groupby = TRUE;
  }
}
$last_groupby = $groupby;

  //$public_sql = " or bookmarks.public_f='Y' ";


$query = sprintf("select * from phpgw_bookmarks where bm_owner= '%s' %s", $phpgw_info['user']['account_id'], $public_sql);

# if saved search loaded then use it first
if (isset($saved_search)) {
  $query .= " and (" . $saved_search . ")";
  $filter_msg = "Filter " . htmlspecialchars($saved_search);
} elseif (isset($where)) {
# else if a WHERE clause was specified in the URL, then use it
  $query .= " and (" . base64_decode($where) . ")";
  $filter_msg = "Filter " . htmlspecialchars(base64_decode($where));
}

if ($groupby) {
//  $query .= " order by category_name, subcategory_name, bookmark_name";
  $groupby_default = "checked";
} else {
  $query .= " order by bookmarks.name, bookmarks.url";
  $groupby_default = "";
}

if ($bookmarker->show_bk_in_tree && ($groupby || !(isset($groupby)))) {
  $output .= sprintf("dbAdd( true , \"bookmarker\" , \"\" , 0 , \"bk_app\" , 0, 0)\n");
  $output .= sprintf("dbAdd( false , \"start\" , \"index.php\" , 1 , \"bk_app\" , 0, 0)\n");
  $output .= sprintf("dbAdd( false , \"plain list\" , \"list.php\" , 1 , \"bk_app\" , 0, 0)\n");
  $output .= sprintf("dbAdd( false , \"create\" , \"create.php\" , 1 , \"bk_app\" , 0, 0)\n");
  $output .= sprintf("dbAdd( false , \"search\" , \"search.php\" , 1 , \"bk_app\" , 0, 0)\n");
}

$phpgw->db->query($query,__LINE__,__FILE__);
if ($phpgw->db->Errno == 0) {

   $prev_category = " ";
   $prev_subcategory = " ";
   $first_time = 1;

   while ($phpgw->db->next_record()) {
 
   # only do the category subcategory breaks if the user wants them
      if ($groupby) {
         $category_break = 0;
         $subcategory_break = 0;

        if ($phpgw->db->f("category_name") != $prev_category) {
           $prev_category = $phpgw->db->f("category_name");
           $category_break = 1;
        }
        if ($phpgw->db->f("subcategory_name") != $prev_subcategory) {
           $prev_subcategory = $phpgw->db->f("subcategory_name");
           $subcategory_break = 1;
        }

        if ($category_break or $subcategory_break and !$first_time) {
           $first_time = 0;
        }

        if ($category_break) {
           $output .= sprintf("dbAdd( true , \"%s\" , \"\" , 0 , \"bk_target\", 0,%s)\n",htmlspecialchars(stripslashes($prev_category)), $phpgw->db->f("id"));
           $output .= sprintf("dbAdd( true , \"%s\" , \"\" , 1 , \"bk_target\", 0,%s)\n",htmlspecialchars(stripslashes($prev_subcategory)), $phpgw->db->f("id"));
        } elseif ($subcategory_break) {
           $output .= sprintf("dbAdd( true , \"%s\" , \"\" , 1 , \"bk_target\", 0,%s)\n",htmlspecialchars(stripslashes($prev_subcategory)), $phpgw->db->f("id"));
        }    

        $output .= sprintf("dbAdd( false , \"%s\" , \"%s\" , 2 , \"bk_target\", 0,%s)\n",htmlspecialchars(stripslashes($phpgw->db->f("bookmark_name"))), $phpgw->db->f("url"), $phpgw->db->f("id"));
    } else {
        # the user doesn't want category/subcategory breaks, so just print the
        # urls on the first level
        $output .= sprintf("dbAdd( false , \"%s\" , \"%s\" , 0 , \"bk_target\", 0,%s)\n",htmlspecialchars(stripslashes($phpgw->db->f("bookmark_name"))), $phpgw->db->f("url"), $phpgw->db->f("id"));
    }
  }
}

  # load the list of previously saved searches
  # and prepare the save search form

  if ($search > 0) {
     $default_search = $search;
  } else {
     $default_search = "NONE";
  }

  load_ddlb("bookmarks_search", $default_search, &$search_select, TRUE);
  $phpgw->template->set_var(array(SEARCH_SELECT => $search_select,
                                  FORM_ACTION   => $phpgw->link('/bookmarks/tree.php')
                           ));

  $phpgw->template->set_var(array(FILTER_MSG       => $filter_msg,
                                  GROUPBY_DEFAULT  => $groupby_default,
                                  BOOKMARK_JS      => $output,
                                  IMAGE_URL_PREFIX => $bookmarker->image_url_prefix,
                                  IMAGE_EXT        => $bookmarker->image_ext
                           ));


  // standard error message, and message handler.
/*  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/messages.inc.php");
  if (isset ($bk_output_html)) {
     $phpgw->template->set_var(MESSAGES, $bk_output_html);
  }

  $phpgw->template->parse("BODY", "body");
<<<<<<< tree.php
  $phpgw->template->p("BODY"); */

  $phpgw->common->phpgw_footer();
?>
