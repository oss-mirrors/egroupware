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
  
  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  $phpgw_info["flags"]["currentapp"] = "bookmarks";
  $phpgw_info["flags"]["enabled_nextmatchs_class"] = True;
  include("../header.inc.php");

  include(dirname(__FILE__)."/inc/bkprepend.inc");

  function return_to()
  {
     global $returnto, $msg, $error_msg, $sess_msg, $sess_error_msg, $phpgw;

     if (!empty($returnto)) {
        list($file,$vars) = explode("----",urldecode($returnto));
        $sess_msg       = $msg;
        $sess_error_msg = $error_msg;
        header("Location: " . $phpgw->link($file,$vars));
        page_close();
        exit;
     }
  }

  $phpgw->template->set_file(array(standard   => "common.standard.tpl",
                                   body       => "maintain.body.tpl"
                            ));

  set_standard("maintain", &$phpgw->template);

  $db    = $phpgw->db;
  $bmark = new bmark;

  ## Check if there was a submission
  while (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
     switch ($key) {

     ## Change bookmark
     case "bk_edit":
     case "bk_edit_x":
     if (!$bmark->update($id, $url, $name, $ldesc, $keywords, $category, $subcategory, $rating, $public)) break;

     return_to();
     break;

     ## Delete the bookmark
     case "bk_delete":
     case "bk_delete_x":
     if (!$bmark->delete($id))
        break;
     return_to();
     break;
  
     ## Cancel the changes, send user back to referring page.
     case "bk_cancel":
     case "bk_cancel_x":
      $msg .= "Bookmark maintain cancelled.";
      return_to();
      break;

     default: break;
 }
}

if (empty($error_msg)) {
## get record to update
  $query = sprintf("select * from bookmark where id ='%s' and username='%s'", $id, $phpgw_info["user"]["account_id"]);
  $db->query($query,__LINE__,__FILE__);

  if ($db->Errno == 0) {
     if ($db->next_record()) {
        $id          = $db->f("id");
        $url         = $db->f("url");
        $name        = $db->f("name");
        $ldesc       = $db->f("ldesc");
        $keywords    = $db->f("keywords");
        $rating      = $db->f("rating_id");
        $category    = $db->f("category_id");
        $subcategory = $db->f("subcategory_id");
        $added       = $db->f("added");
        $public      = $db->f("public_f");

        if ($public == "on" || $public == "Y") {
           $public_selected = "CHECKED";
        }

        load_ddlb("category", $category, &$category_select, FALSE);
        load_ddlb("subcategory", $subcategory, &$subcategory_select, FALSE);  
        load_ddlb("rating", $rating, &$rating_select, FALSE);

        if (!empty($returnto)) {
           $cancel_button = sprintf("<input type=\"image\" name=\"bk_cancel\" title=\"Cancel Maintain\" src=\"%scancel.%s\" border=0 width=24 height=24>", $bookmarker->image_url_prefix, $bookmarker->image_ext);
        }

        $phpgw->template->set_var(array(FORM_ACTION        => $phpgw->link("","id=$id&returnto=" . urlencode($returnto)),
                                        MAIL_THIS_LINK_URL => $phpgw->link("maillink.php","id=".$id),
                                        ID                 => $id,
                                        URL                => $url,
                                        NAME               => htmlspecialchars(stripslashes($name)),
                                        LDESC              => htmlspecialchars(stripslashes($ldesc)),
                                        KEYWORDS           => htmlspecialchars(stripslashes($keywords)),
                                        CATEGORY           => $category_select,
                                        SUBCATEGORY        => $subcategory_select,
                                        RATING             => $rating_select,
                                        ADDED              => $phpgw->common->show_date($added),
                                        PUBLIC_SELECTED    => $public_selected,
                                        CANCEL_BUTTON      => $cancel_button
                                ));


       $phpgw->template->parse(BODY, "body");
    } else {
       $error_msg .= "<br>Bookmark $id not found.";
    }
  }
}

  $phpgw->common->navbar();
  include(LIBDIR . "bkend.inc");
?>
