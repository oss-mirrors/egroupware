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
  $phpgw_info["flags"]["enable_categories_class"] = True;
  if ($submit || $method == "delete") {
     $phpgw_info["flags"]["nonavbar"] = True;
     $phpgw_info["flags"]["noheader"] = True;
     $phpgw_info["flags"]["nofooter"] = True;
  }
  include("../header.inc.php");

  if ($submit || $method == "delete") {
     if ($type == "subcategory") {
        $cat_parent = -1;
     }

     if ($method == "edit") {
        $phpgw->categories->edit($bm_id,$cat_parent,$value);
        $message = urlencode(lang("x has been updated",$type));
     }
     if ($method == "add") {
        $phpgw->categories->add($value,$cat_parent);
        $message = urlencode(lang("x has be added",$type));
     }
     if ($method == "delete") {
        $phpgw->categories->delete($bm_id);
        $message = urlencode(lang("x has been deleted",$type));
     }
     
     Header("Location: " . $phpgw->link($phpgw_info["webserver_url"] . "/bookmarks/categories.php","type=$type&message=$message"));
     $phpgw->common->phpgw_exit();
  }

  if ($method == "edit") {
     $ta = $phpgw->categories->return_array("single",$bm_id);
     $name = $ta[0]["name"];
  }

  $phpgw->template->set_file(array("common" => "common.tpl",
                                   "body"   => "categories_form.tpl",
                            ));
  app_header(&$phpgw->template);

  $phpgw->template->set_var("form_action",$phpgw->link("category_maintain.php","type=$type&method=$method"));
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("row_on",$phpgw_info["theme"]["row_on"]);
  $phpgw->template->set_var("lang_name",lang("Name"));

  if ($method == "add") {
     $phpgw->template->set_var("header_message",lang("Add new x",$type));
  }
  if ($method == "edit") {
     $phpgw->template->set_var("header_message",lang("Edit new x",$type));
  }

  $phpgw->template->set_var("name_value",$name);
  $phpgw->template->set_var("submit_value",lang("Add"));

  $phpgw->common->phpgw_footer();
?>