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

  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/class.Validator.inc");

  class bktemplate extends Template
  {
     var $classname = "bktemplate";
  
     /* if set, echo assignments */
     /* 1 = debug set, 2 = debug get, 4 = debug internals */
     var $debug     = false;
     
     /* "yes" => halt, "report" => report error, continue, 
      * "no" => ignore error quietly 
     */
     var $halt_on_error  = "yes";

     // override the finish function to better handle with javascript.
     // we don't have whitespace in our var names, so no need to be
     // so all encompassing with the remove.

     function finish($str)
     {
        switch ($this->unknowns) {
          case "keep":
          break;
          
          case "remove":
            $str = preg_replace("/\{[-_a-zA-Z0-9]+\}/", "", $str);
          break; 
          
          case "comment":
            $str = preg_replace("/\{([-_a-zA-Z0-9]+)\}/", "<!-- Template $handle: Variable \\1 undefined -->", $str);
          break; 
        } 
        return $str;
     } 
  }

  function date_information(&$tpl, $raw_string)
  {
     global $phpgw;

     $ts = explode(",",$phpgw->db->f("bm_info"));

     $tpl->set_var("added_value",$phpgw->common->show_date($ts[0]));
     $tpl->set_var("visited_value",($ts[1]?$phpgw->common->show_date($ts[1]):lang("Never")));
     $tpl->set_var("updated_value",($ts[2]?$phpgw->common->show_date($ts[2]):lang("Never")));
  }

  function  set_standard($title, &$p_tpl) 
  {
     global $bookmarker, $SERVER_NAME, $phpgw;

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
  function load_ddlb($table, $selected = "")
  {
     global $phpgw, $phpgw_info;
     $db = $phpgw->db;

     $query = sprintf("select id, name from %s where username='%s' order by name", $table,
                      $phpgw_info["user"]["account_id"]);
     $db->query($query,__LINE__,__FILE__);
     while ($db->next_record()) {
        $s .= '<option value="' . $db->f("id") . '"';
        if ($selected == $db->f("id")) {
           $s .= " selected";
        }        
        $s .= '>' . $phpgw->strip_html($db->f("name")) . '</option>';
        $s .= "\n";
     }
     return $s;
  }

  // function to determine what type of browser the user has.
  // code idea from http://www.php.net/
  function check_browser()
  {
    global $HTTP_USER_AGENT;
  
    $browser= "UNKNOWN";
  
    if (ereg("MSIE",$HTTP_USER_AGENT)) {
       $browser = "MSIE";
    } elseif (ereg("Mozilla",$HTTP_USER_AGENT)) {
       $browser = "NETSCAPE";
    } else {
       $browser = "UNKNOWN";
    }
  
    return $browser;
  }




  class bmark
  {

     function add(&$id,$url,$name,$ldesc,$keywords,$category,$subcategory,$rating,$access,$groups)
     {
        global $phpgw_info,$error_msg, $msg, $bookmarker, $phpgw;

        $db = $phpgw->db;

/*      if (! $this->validate(&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory, 
                         &$rating, &$public, &$public_db)) {
           return False;
        } */

        // Does the bookmark already exist?
        $query = sprintf("select count(*) from phpgw_bookmarks where bm_url='%s' and bm_owner='%s'",$url, $phpgw_info["user"]["account_id"]);
        $db->query($query,__LINE__,__FILE__);

        if ($db->f(0)) {
           $error_msg .= sprintf("<br>URL <B>%s</B> already exists!", $url);
           return False;
        }

        if ($access != "private" && $access != "public") {
           $access = $phpgw->accounts->array_to_string($access,$groups);
        }

        // Insert the bookmark
        $query = sprintf("insert into phpgw_bookmarks (bm_url, bm_name, bm_desc, bm_keywords, bm_category,"
                       . "bm_subcategory, bm_rating, bm_owner, bm_access, bm_info, bm_visits) "
                       . "values ('%s', '%s', '%s','%s',%s,%s,%s, '%s', '%s','%s,0,0',0)", 
                          $url, addslashes($name), addslashes($ldesc), addslashes($keywords), 
                          $category, $subcategory, $rating, $phpgw_info["user"]["account_id"], $access,
                          time());
    
        $db->query($query,__LINE__,__FILE__);

  //    $maintain_url = "maintain.php?id=".$id;
        $msg .= "Bookmark created sucessfully.";

        // Update the PHPLIB user variable that keeps track of how
        // many bookmarks this user has.
        // NOTE: I need to move this into appsessions
        $this->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);
    
        return true;
    }

    function update($id, $url, $name, $ldesc, $keywords, $category, $subcategory, $rating, $public)
    {
       global $error_msg, $msg, $bookmarker, $validate, $phpgw_info, $added, $visted, $phpgw;

       if (!$this->validate(&$url, &$name, &$ldesc, &$keywords, &$category, &$subcategory,
                        &$rating, &$public, &$public_db)) {
          return False;
       }

       if ($visted == 1) {
          $visted = 0;
       }

       $timestamps = sprintf("%s,%s,%s",$added,$visted,time());
   
       // Update bookmark information.
       $query = sprintf("update phpgw_bookmarks set bm_url='%s', bm_name='%s', bm_desc='%s', "
                      . "bm_keywords='%s', bm_category='%s', bm_subcategory='%s', bm_rating='%s',"
                      . "bm_info='%s' where bm_id='%s' and bm_owner='%s'", 
                         $url, addslashes($name), addslashes($ldesc), addslashes($keywords), 
                         $category, $subcategory, $rating, $public_db, $timestamps, $id, $phpgw_info["user"]["account_id"]);
   
       $phpgw->db->query($query,__LINE__,__FILE__);
   
       $msg .= "Bookmark changed sucessfully.";
    
       // Update the PHPLIB user variable that keeps track of how
       // many bookmarks this user has.
       // NOTE: This needs to be moved into appsessions
       $this->update_user_total_bookmarks($phpgw_info["user"]["acount_id"]);
   
       return true;
    }

    function delete($id)
    {
       global $error_msg, $msg, $phpgw, $phpgw_info;
   
       $db = $phpgw->db;
       
       // Delete that bookmark.
       $query = sprintf("delete from bookmarks where id='%s' and username='%s'", $id, $phpgw_info["user"]["account_id"]);
       $db->query($query,__LINE__,__FILE__);
       if ($db->Errno != 0) {
          return False;
       }
       
       $msg .= "Bookmark deleted sucessfully.";
   
       // Update the PHPLIB user variable that keeps track of how
       // many bookmarks this user has.
       // NOTE: This needs to be moved into appsessions
       $this->update_user_total_bookmarks($phpgw_info["user"]["account_id"]);
   
       return true;
    }

    function validate (&$url,&$name,&$ldesc,&$keywords,&$category,&$subcategory,&$rating,&$public,&$public_db)
    {
       global $error_msg, $msg, $bookmarker, $validate;


       // trim the form fields
       // $url = $validate->strip_space($url);
       $name = trim($name);
       $desc = trim($ldesc);
       $keyw = trim($keywords);
       
       // Do we have all necessary data?
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
   
/*
       if (isset($rating) && $rating >= 0 ) {
       } else {
          $error_msg .= "<br>Rating is required.";
       }
*/

       // does the admin want us to check URL format
       if ($bookmarker->url_format_check > 0) {
          // Is the URL format valid
          if (!$validate->is_url($url))  { 
             $format_msg = "<br>URL invalid. Format must be <strong>http://</strong> or 
                            <strong>ftp://</strong> followed by a valid hostname and 
                            URL!<br><small> $validate->ERROR </small>";
  
            // does the admin want this formatted as a warning or an error?
            if ($bookmarker->url_format_check == 2) {
               $error_msg .= $format_msg;
            } else {
               $msg .= $format_msg;
            }
         }
      }    

      if ($public == "on") {
         $public_db = "Y";
      } else {
         $public_db = "N";
      }

      // if we found an error, then return false
      if (!empty($error_msg)) {
         return False;
      } else {
         return True;
      }
   }

   function update_user_total_bookmarks($uname)
   {
      global $user_total_bookmarks, $phpgw, $phpgw_info;

      $db = $phpgw->db;

      $db->query("select count(*) as total_bookmarks from bookmarks where username = '"
               . $phpgw_info["user"]["account_id"] . "' or bookmarks.public_f='Y'",__LINE__,__FILE__);
      $db->next_record();
      $phpgw->common->appsession($db->f("total_bookmarks"));
 
         // need to find out how many public bookmarks exist from
         // this user so other users can correctly calculate pages
         // on the list page.
/*
         $total_public = 0;
         $query = sprintf("select count(id) as total_public from bookmarks where username = '%s' and public_f='Y'",$phpgw_info["user"]["account_id"]);
         $db->query($query,__LINE__,__FILE__);
         if ($db->Errno == 0) {
            if ($db->next_record()) {
//               $total_public = $db->f("total_public");
               echo "TEST: " . $db->f("total_public");
               $phpgw->common->appsession($db->f("total_public"));
            } else {
               echo "TEST: False";
               return False;
            } */
 
//            $phpgw->common->appsession($total_public);
/*
            $query = sprintf("update auth_user set total_public_bookmarks=%s where username = '%s'",$total_public, $uname);
            $db->query($query,__LINE__,__FILE__);
            if ($db->Errno != 0) {
               return False;
            }
            return true;*/
       //}
   }

   // get the total nbr of bookmarks for this user.
   // stored as session variable so re-calculated at
   // least once per session.
   function getUserTotalBookmarks()
   {
      global $user_total_bookmarks, $phpgw;

      return $phpgw->common->appsession();

/*    # get/set the $user_total_bookmarks as a session variable.
    # we use this to keep the total nbr of bookmarks this
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



# the following class sets various configuration variables
# used throughout the application.
class bookmarker_class  {
  var $version        = "2.8.0";

# directory where templates are located on this server
  var $template_dir   = "./lib/templates";

# image URL - string added to the begining of an image file
# (for example, I set this to "./images/" which makes bookmarker
# build image URLs like <img src="./images/mailto.png"...)
  var $image_url_prefix;

# URL format checking. bookmarker can check the format of
# URLs entered on the create/maintain pages. This option
# lets you control this checking. Possible values:
#  0 = no checking of URL format
#  1 = URL format is checked, problems reported as warnings
#  2 = URL format is checked, problems reported as errors
  var $url_format_check = 2;

# URL response checking. bookmarker can check that the URL
# responds to a request and show a warning if it does not
# respond.
  var $url_responds_check = true;

# how many characters after the scheme(http://) and hostname
# (www.mydomain.com) to match when checking for possible
# duplicates on the create page.
# Zero means to just match on scheme and hostname - this is
# what I prefer.
  var $possible_dup_chars = 0;

# level of access required for user to use the mail-this-link
# page. The default is to only allow registered users to send
# email using bookmarker - anything else is asking for abuse!
# if you allow guest, you may want to bcc yourself by using the
# site header variable below.
  var $mail_this_link_permission_required = "editor";

# this var controls if the bookmarker links (start, create, search...)
# are displayed in the tree view. NOTE: these links are only displayed
# if 'group by category/subcategory' is also selected.
  var $show_bk_in_tree = 0; # set to 0 for 'off' 1 for 'on'


  function bookmarker_class()
  {
    global $SERVER_NAME, $SERVER_ADMIN, $REMOTE_ADDR, $PHP_SELF, $phpgw_info;

    $this->image_url_prefix = $phpgw_info["server"]["app_images"] . "/";

    $where_am_i = sprintf("http://%s%s/", $SERVER_NAME, dirname($PHP_SELF));

# used for quik-mark bookmark
    $this->create_url   = $where_am_i . "create.php";

# used for mail-this-link bookmark
    $this->maillink_url = $where_am_i . "maillink.php";

# the following wording is automatically added to all outgoing
# mail-this-link email messages
    $this->site_footer  = sprintf("--\nThis message sent from the bookmarker bookmark manager\nat %s\nPlease contact the server administrator at\n%s to report abuse of this service.", $where_am_i, $SERVER_ADMIN);

# this var controls the headers that are added to the mail-this-link
# email message. You may choose to bcc: yourself, record the senders IP...
# the headers should be separated by a newline ("\n")
    $this->site_headers = sprintf("X-Sender: bookmarker at %s\nX-Sender-IP: $REMOTE_ADDR", $SERVER_NAME);
  }
     
}

# instantiate the bookmarker class so we can access
# the variables.
$bookmarker = new bookmarker_class ();

# if the user's browser is a 5.0 or later version, then
# use PNG images. otherwise use GIF images.
$bookmarker->image_ext="gif";

if (ereg( "MSIE ([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";

} elseif (ereg( "Opera/([0-9]+)",$HTTP_USER_AGENT,$version)) {
    # $opera=true;

} elseif (ereg( "Mozilla/([0-9]+)",$HTTP_USER_AGENT,$version)) {
  $ver=(int)$version[1];
  if ($ver>=5) $bookmarker->image_ext="png";
}

# every bookmarker page uses templates to generate HTML.
//$tpl = new bktemplate;
//$tpl->set_root(TEMPLATEDIR);
#$phpgw->template->set_unknowns("remove");

# create an instance of the data validation class
$validate = new Validator ();


?>
