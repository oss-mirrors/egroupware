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
  $phpgw_info["flags"]["enabled_nextmatchs_class"] = True;
  include("../header.inc.php");

  // I am disabling this for temp, I need to spend more time on it.
?>
  <p><center> <b>Not available yet.</b> </center>
<?php 
  exit;

  include(LIBDIR . "plist.inc");

  $phpgw->template->set_file(array(standard   => "common.standard.tpl",
                                   body       => "search.body.tpl",
                                   results    => "search.results.tpl"
                            ));


  // the following fields are selectable
  $field = array("bookmark.name"       => "Name",
                 "bookmark.keywords"   => "Keywords",
                 "bookmark.url"        => "URL",
                 "bookmark.ldesc"      => "Description",
                 "category.name"       => "Category",
                 "subcategory.name"    => "Sub Category",
                 "rating.name"         => "Rating",
                 "bookmark.id"         => "ID");

  # PHPLIB's sqlquery class loads this string when
  # no query has been specified.
  $noquery = "1=0";

  # if we don't have a query object for this session yet,
  # then create one and save as a session variable.
  if (!isset($q)) {
     $q = new bk_Sql_Query;
     $sess->register("q");
  }

  # if a WHERE clause was specified in the URL, then use it
  if (isset($where)) {
     $q->query = base64_decode($where);
  }

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
    $query = sprintf("select query from search where id=%s and username='%s'",$search,$phpgw_info["user"]["account_id"]);
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
    $query = sprintf("update search set query='%s' where id=%s and username='%s'", addslashes($q->query), $search, $auth->auth["uname"]);
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
    $query = sprintf("delete from search where id='%s' and username='%s'", $search, $auth->auth["uname"]);
    $db->query($query);
    if ($db->Errno == 0) {
      $msg .= "<br>Saved Search deleted sucessfully.";
    }
  break;

  ## Create a new saved search
  case "bks_create":

    ## Do we have permission to do so?
    if (!$perm->have_perm("editor")) {
      $error_msg .= "<br>You do not have permission to create Saved Searches.";
      break;
    }

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
    $query = sprintf("select id from search where name='%s' and username = '%s'",addslashes($name), $auth->auth["uname"]);
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
    $query = sprintf("insert into search (id, name, query, username) 
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
load_ddlb("search", $search, &$search_select, FALSE);
$tpl->set_var(array(
  SEARCH_SELECT => $search_select,
  FORM_ACTION   => $sess->url("search.php")
));

# build the search form
$tpl->set_var(QUERY_FORM, $q->form("x", $field, "qry", $sess->url("search.php")));

if ($q->query == $noquery) {
} else {
  
  $limit = 0;
  $offset = 0;

# db callout to allow database specific override to the
# generated query syntax.
  $q->query = $bk_db_callout->fix_search_sql ($q->query);

  print_list ($q->query, $limit, $offset, "search.php", &$bookmark_list, &$error_msg);
  
  $tree_search_url = $sess->url( "tree.php?where=" . base64_encode($q->query));
  $tpl->set_var(array(
    QUERY_CONDITION => htmlspecialchars($q->query),
    BOOKMARK_LIST   => $bookmark_list,
    TREE_SEARCH_URL => $tree_search_url
  ));
  $tpl->parse(QUERY_RESULTS, "results");
}

set_standard("search", &$tpl);

include(LIBDIR . "bkend.inc");
?>
