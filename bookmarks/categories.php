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
  include("../header.inc.php");
  
  $phpgw->template->set_file(array("common"    => "common.tpl",
                                   "body"      => "categories_list.tpl",
                                   "row"       => "categories_list_row.tpl"
                            ));
  app_header(&$phpgw->template);

  $phpgw->template->set_var("message",$message);
  $phpgw->template->set_var("sort_name",lang("Name"));
  $phpgw->template->set_var("lang_edit",lang("Edit"));
  $phpgw->template->set_var("lang_delete",lang("Delete"));
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);

  if ($type == "category") {
     $cats = $phpgw->categories->return_array("mains");
  }
  if ($type == "subcategory") {
     $cats = $phpgw->categories->return_array("submains");
  }

  while ($cat = each($cats)) {
     $phpgw->nextmatchs->template_alternate_row_color(&$phpgw->template);
     $phpgw->template->set_var("cat_name",$cat[1]["name"]);
     $phpgw->template->set_var("cat_edit",'<a href="' . $phpgw->link("category_maintain.php","bm_id=" . $cat[1]["id"] . "&type=$type&method=edit")
                                        . '">' . lang("Edit") . '</a>');
     $phpgw->template->set_var("cat_delete",'<a href="' . $phpgw->link("category_maintain.php","bm_id=" . $cat[1]["id"] . "&type=$type&method=delete")
                                        . '">' . lang("Delete") . '</a>');
     $phpgw->template->parse("rows","row",True);
  }
  $phpgw->template->set_var("add_link",'<a href="' . $phpgw->link("category_maintain.php","type=$type&method=add") . '">'
                                     . lang("Add") . '</a>');

  $phpgw->common->phpgw_footer();
?>