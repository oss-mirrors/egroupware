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

  $phpgw_info["flags"]["currentapp"] = "bookmarks";
  $phpgw_info["flags"]["enable_nextmatchs_class"] = True;
  include("../header.inc.php");

  $phpgw->template->set_file(array(standard   => "common.standard.tpl",
                                   body       => "search.body.tpl",
                                   results    => "search.results.tpl"
                            ));


  // the following fields are selectable
  $field = array("bookmarks.name"       => "Name",
                 "bookmarks.keywords"   => "Keywords",
                 "bookmarks.url"        => "URL",
                 "bookmarks.ldesc"      => "Description",
                 "bookmarks_category.name"       => "Category",
                 "bookmarks_subcategory.name"    => "Sub Category",
//                 "rating.name"         => "Rating",
                 "bookmarks.id"         => "ID");

  # PHPLIB's sqlquery class loads this string when
  # no query has been specified.
  $noquery = "1=0";

  # if we don't have a query object for this session yet,
  # then create one and save as a session variable.

  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/sqlquery.inc.php");
  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/plist.inc.php");
class bk_Sql_Query extends Sql_Query {
  var $classname = "bk_Sql_Query";
  var $persistent_slots = array(
    "conditions", "input_size", "input_max", "method", "lang", "translate", "container", "variable", "query"
  );
  var $query = "1=0";       ## last WHERE clause used
  var $conditions = 1;      ## Allow for that many Query Conditions
  var $input_size = 35;     ## Used in text input field creation
  var $input_max  = 80;

  var $method     = "post"; ## Generate get or post form...
  var $lang       = "en";   ## HTML Widget language

  var $translate = "on";    ## If set, translate column names
  var $container = "";      ## If set, create a container table
  var $variable  = "on";    ## if set, create variable size buttons
}


//  if (!isset($q)) {
       $q = new bk_Sql_Query;
//     $sess->register("q");
//  }

  # if a WHERE clause was specified in the URL, then use it
//  if (isset($where)) {
//     $q->query = base64_decode($where);
//  }

  ## Check if there was a submission
  while (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {

  ## Load a Saved Search
  case "bks_load":
    ## Do we have all necessary data?
    if ($search > 0 ) {
    } else {
      $error_msg .= "<br>Please select a <strong>Saved Search</strong> to load!";
      break;
    }

    ## get the saved search
    $query = sprintf("select query from bookmarks_search where id=%s and username='%s'",$search,$phpgw_info["user"]["account_id"]);
    $phpgw->db->query($query,__LINE__,__FILE__);
    if ($phpgw->db->Errno == 0) {
       if ($phpgw->db->next_record()){
          $q->query = $phpgw->db->f("query");
       } else {
          $error_msg .= "<br>Saved Search not found in database!";
          break;
      }
    
      $msg .= "<br>Saved Search loaded sucessfully.";
    }
    break;

    ## Change Saved Search
    case "bks_save":
    ## Do we have permission to do so?
/*
    if (! $perm->have_perm("editor")) {
       $error_msg .= "<br>You do not have permission to change Saved Searches.";
       break;
    }
*/

    ## Do we have all necessary data?
    if ($search > 0 ) {
    } else {
       $error_msg .= "<br>Please select a <strong>Saved Search</strong> to update!";
       break;
    }

    if ($q->query == $noquery) {
       $error_msg .= "<br>No query to save!";
       break;
    }

    ## Update bookmark information.
    $query = sprintf("update bookmarks_search set query='%s' where id=%s and username='%s'", addslashes($q->query), $search, $phpgw_info["user"]["account_id"]);
    $phpgw->db->query($query,__LINE__,__FILE__);
    if ($phpgw->db->Errno == 0) {
       $msg .= "<br>Saved Search changed sucessfully.";
    }    
    break;

    ## Delete the saved search
    case "bks_delete":
    ## Do we have permission to do so?
    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to delete Saved Searches.";
      break;
    }

    ## Do we have all necessary data?
    if ($search > 0 ) {
    } else {
      $error_msg .= "<br>Please select a <strong>Saved Search</strong> to delete!";
      break;
    }

    ## Delete that bookmark.
    $query = sprintf("delete from bookmarks_search where id='%s' and username='%s'", $search, $phpgw_info["user"]["account_id"]);
    $db->query($query);
    if ($db->Errno == 0) {
      $msg .= "<br>Saved Search deleted sucessfully.";
    }
  break;

  ## Create a new saved search
  case "bks_create":

    ## Do we have permission to do so?
/*    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to create Saved Searches.";
      break;
    } */

    ## Trim form fields
    $name = trim($name);
    
    ## Do we have all necessary data?
    if (empty($name)) {
      $error_msg .= "<br>Please enter a <B>Name</B> for the Saved Search!";
      break;
    }

    if ($q->query == $noquery) {
      $error_msg .= "<br>No query to save!";
      break;
    }

    ## Does the search already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $query = sprintf("select id from bookmarks_search where name='%s' and username = '%s'",addslashes($name), $auth->auth["uname"]);
    $db->query($query);
    if ($db->Errno == 0) {
      if ($db->nf() > 0) {
        $error_msg .= sprintf("<br>Saved Search named <B>%s</B> already exists!", $url);
        break;
      }
    }

    ## Get the next available ID key
    $id = $db->nextid('search');
    if ($db->Errno != 0) break;

    ## Insert the search
    $query = sprintf("insert into bookmarks_search (id, name, query, username) 
      values(%s, '%s', '%s', '%s')", 
      $id, addslashes($name), addslashes($q->query), $auth->auth["uname"]);
    $db->query($query);
    if ($db->Errno == 0) {
      $msg .= "<br>Saved Search created sucessfully.";
    }
    break;
  
  default:
  break;
 }
}

# build the where clause based on user entered fields
if (isset($x)) {
#
# we need to pre-process the input fields so we can
# handle quotes properly. we can't put an addslashes
# on the resulting sql because the sql_query object
# doesn't do the quotes correctly
  reset($x);
  while (list($key, $value) = each ($x)) {
    $y[$key] = addslashes($value);
  }
  $q->query = $q->where("y", 1);
}

# load the list of previously saved searches
# and prepare the save search form
load_ddlb("bookmarks_search", $search, &$search_select, FALSE);
$phpgw->template->set_var(array(
  SEARCH_SELECT => $search_select,
  FORM_ACTION   => $phpgw->link("search.php")
));

# build the search form
$phpgw->template->set_var(QUERY_FORM, $q->form("x", $field, "qry", $phpgw->link("search.php")));

if ($q->query == $noquery) {
} else {
  
  $limit = 0;
  $offset = 0;

# db callout to allow database specific override to the
# generated query syntax.
//  $q->query = $bk_db_callout->fix_search_sql ($q->query);

  print_list ($q->query, $start, "search.php", &$bookmark_list, &$error_msg);
  
  $tree_search_url = $phpgw->link("tree.php","where=" . base64_encode($q->query));
  $phpgw->template->set_var(array(
    QUERY_CONDITION => htmlspecialchars($q->query),
    BOOKMARK_LIST   => $bookmark_list,
    TREE_SEARCH_URL => $tree_search_url
  ));
  $phpgw->template->parse(QUERY_RESULTS, "results");
}

set_standard("search", &$phpgw->template);

  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/footer.inc.php");
?>
