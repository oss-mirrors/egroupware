<?php
# ---------------------------------------------------------------
# bookmarker
# A WWW based bookmark management, retrieval, and search tool.
# Copyright (C) 1998  Padraic Renaghan
# Licensed under terms of GNU General Public License
# (see http://www.renaghan.com/bookmarker/source/LICENSE)
# ---------------------------------------------------------------
# $Id$
# ---------------------------------------------------------------
# the following class represents a single bookmark
class bmark  {

  function add(&$id, $url, $name, $ldesc, $keywords, $category, $subcategory, 
                  $rating, $public) {
    global $phpgw_info,$sess, $auth, $perm, $error_msg, $msg, $bookmarker, $validate, $db, $bk_db_callout, $phpgw;

    $db = $phpgw->db;

    if (!$this->validate(&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory, 
                         &$rating, &$public, &$public_db)) return false;

    ## Does the bookmark already exist?
    $query = sprintf("select id from bookmark where url='%s' and username = '%s'",$url, $phpgw_info["user"]["account_id"]);
    $db->query($query,__LINE__,__FILE__);
    if ($db->Errno != 0) return false;

    if ($db->nf() > 0) {
       $error_msg .= sprintf("<br>URL <B>%s</B> already exists!", $url);
       return false;
    }

    ## Get the next available ID key
//    $id = $db->nextid('bookmark');
//    if ($db->Errno != 0) return false;

    ## Insert the bookmark
    $query = sprintf("insert into bookmark (url, name, ldesc, keywords, category_id, 
                      subcategory_id, rating_id, username, public_f, added) 
                      values('%s', '%s', '%s','%s',%s,%s,%s, '%s', '%s', %s)", 
                      $url, addslashes($name), addslashes($ldesc), addslashes($keywords), 
                      $category, $subcategory, $rating, $phpgw_info["user"]["account_id"], $public_db,
                      time());

    $db->query($query,__LINE__,__FILE__);
    if ($db->Errno != 0) return false;

    $maintain_url = "maintain.php3?id=".$id;
    $msg .= sprintf("Bookmark %s created sucessfully.
                     <br>Click <a href=\"%s\">here</a> to modify this bookmark.
                     <a href=\"maillink.php3?id=%s\">
                     <img width=24 height=24 align=top border=0 src=\"%smail.png\"></a>", 
                     $id, $phpgw->link($maintain_url), $id, $bookmarker->image_url_prefix);

    ## Update the PHPLIB user variable that keeps track of how
    ## many bookmarks this user has.
    $this->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);

    return true;
  }

  function update($id, $url, $name, $ldesc, $keywords, $category, $subcategory, 
                  $rating, $public) {
    global $auth, $perm, $error_msg, $msg, $bookmarker, $validate, $db, $phpgw_info;

    if (!$this->validate(&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory, 
                        &$rating, &$public, &$public_db)) return false;

    ## Update bookmark information.
    $query = sprintf("update bookmark set url='%s', name='%s', ldesc='%s' , keywords='%s', 
                      category_id='%s', subcategory_id='%s', rating_id='%s', public_f='%s' 
                      where id='%s' and username='%s'", 
                      $url, addslashes($name), addslashes($ldesc), addslashes($keywords), 
                      $category, $subcategory, $rating, $public_db, $id, $phpgw_info["user"]["account_id"]);

    $db->query($query,__LINE__,__FILE__);
    if ($db->Errno != 0) return false;

    $msg .= "Bookmark changed sucessfully.";
    
    ## Update the PHPLIB user variable that keeps track of how
    ## many bookmarks this user has.
    $this->update_user_total_bookmarks($phpgw_info["user"]["acount_id"]);

    return true;
  }

  function delete($id) {
    global $perm, $error_msg, $msg, $phpgw, $phpgw_info;

    $db = $phpgw->db;

    ## Do we have permission to do so?
/*    if (!$perm->have_perm("editor")) {
      $error_msg .= "You do not have permission to delete bookmarks.";
      return false;
    } */
    
    ## Delete that bookmark.
    $query = sprintf("delete from bookmark where id='%s' and username='%s'", $id, $phpgw_info["user"]["account_id"]);
    $db->query($query,__LINE__,__FILE__);
    if ($db->Errno != 0) return false;
    
    $msg .= "Bookmark deleted sucessfully.";

    ## Update the PHPLIB user variable that keeps track of how
    ## many bookmarks this user has.
    $this->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);

    return true;
  }

  function validate (&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory, 
                     &$rating, &$public, &$public_db) {
    global $perm, $error_msg, $msg, $bookmarker, $validate;

    ## Do we have permission to do so?
/*    if (!$perm->have_perm("editor")) {
       $error_msg .= "<br>You do not have permission to maintain bookmarks.";
    } */

    ## trim the form fields
//    $url = $validate->strip_space($url);
    $name = trim($name);
    $desc = trim($ldesc);
    $keyw = trim($keywords);
    
    ## Do we have all necessary data?
    if (empty($url)) {
       $error_msg .= "<br>URL is required.";
    }

    if (empty($name)) {
       $error_msg .= "<br>Name is required.";
    }

    if (isset($category) && $category >= 0 ) {
    } else {
       $error_msg .= "<br>Category is required.";
    }

    if (isset($subcategory) && $subcategory >= 0 ) {
    } else {
       $error_msg .= "<br>Subcategory is required.";
    }

    if (isset($rating) && $rating >= 0 ) {
    } else {
       $error_msg .= "<br>Rating is required.";
    }

     ## does the admin want us to check URL format
     if ($bookmarker->url_format_check > 0) {
     ## Is the URL format valid
        if (!$validate->is_url($url))  { 
           $format_msg = "<br>URL invalid. Format must be <strong>http://</strong> or 
                          <strong>ftp://</strong> followed by a valid hostname and 
                          URL!<br><small> $validate->ERROR </small>";

           ## does the admin want this formatted as a warning or an error?
          if ($bookmarker->url_format_check == 2) {
             $error_msg .= $format_msg;
          } else {
             $msg .= $format_msg;
          }
       }
    }    

/*    ## Does the URL respond
    if ($bookmarker->url_responds_check) {
//      if (!$validate->url_responds($url))  { 
        $msg .= "<br><strong>**WARNING**</strong>: The URL you entered is not responding.
               <br><small> $validate->ERROR </small>";
//      }
    } */

    if ($public == "on") {
       $public_db = "Y";
    } else {
       $public_db = "N";
    }

    ## if we found an error, then return false
    if (!empty($error_msg)) {
       return false;
    } else {
       return true;
    }
  }

  function update_user_total_bookmarks($uname) {
    global $user_total_bookmarks, $phpgw, $phpgw_info;

    $db = $phpgw->db;

    if ($uname == $phpgw_info["user"]["account_id"]) {
       $user_total_bookmarks = 0;
       $db = $phpgw->db;

       $db->query("select count(id) as total_bookmarks from bookmark where username = '"
                . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
       if ($db->Errno == 0) {
          if ($db->next_record()) 
             $user_total_bookmarks = $db->f("total_bookmarks");
          } else {
             return False;
          }
       }

       // need to find out how many public bookmarks exist from
       // this user so other users can correctly calculate pages
       // on the list page.
       $total_public = 0;
       //$db = $phpgw->db;
       $query = sprintf("select count(id) as total_public from bookmark where username = '%s' and public_f='Y'",$uname);
       $db->query($query,__LINE__,__FILE__);
       if ($db->Errno == 0) {
          if ($db->next_record()) {
             $total_public = $db->f("total_public");
          } else {
             return False;
          }

          $query = sprintf("update auth_user set total_public_bookmarks=%s where username = '%s'",$total_public, $uname);
          $db->query($query,__LINE__,__FILE__);
          if ($db->Errno != 0) {
             return False;
          }
          return true;
      }
  }

      # get the total nbr of bookmarks for this user.
      # stored as session variable so re-calculated at
      # least once per session.
  function getUserTotalBookmarks() {
    global $user_total_bookmarks, $sess, $auth;

    # get/set the $user_total_bookmarks as a session variable.
/*    # we use this to keep the total nbr of bookmarks this
    # user has so we can calculate the list pages correctly.
    $sess->register("user_total_bookmarks");

    if ($auth->is_nobody()) {
      return 0;

    } else if (isset($user_total_bookmarks) &&
               $user_total_bookmarks > 0) {
      return $user_total_bookmarks;

    } else {
      $this->update_user_total_bookmarks($auth->auth["uname"]);
      return (isset($user_total_bookmarks)?$user_total_bookmarks:0);
    } */
  } 
}
