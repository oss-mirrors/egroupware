<?php
  /**************************************************************************\
  * phpGroupWare - Polls                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp"   => "polls", "enable_nextmatchs_class" => True,
                               "admin_header" => True);
  include("../header.inc.php");

  $phpgw->template->set_file(array("form" => "admin_list.tpl",
                                   "row"  => "admin_list_row.tpl"
                                  ));

  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("sort_title",$phpgw->nextmatchs->show_sort_order($sort,"poll_title",$order,"admin.php",lang("title")));
  $phpgw->template->set_var("lang_edit",lang("edit"));
  $phpgw->template->set_var("lang_delete",lang("delete"));
  $phpgw->template->set_var("lang_view",lang("view"));

  $phpgw->db->query("select * from phpgw_polls_desc",__LINE__,__FILE__);
  while ($phpgw->db->next_record()) {
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     $phpgw->template->set_var("tr_color",$tr_color);

     $phpgw->template->set_var("row_title",$phpgw->db->f("poll_title"));
     $phpgw->template->set_var("row_edit",'<a href="' . $phpgw->link("admin_editquestion.php") . '">' . lang("Edit") . '</a>');
     $phpgw->template->set_var("row_delete",'<a href="' . $phpgw->link("admin_deletequestion.php") . '">' . lang("Delete") . '</a>');
     $phpgw->template->set_var("row_view",'<a href="' . $phpgw->link("admin_viewquestion.php") . '">' . lang("View") . '</a>');

     $phpgw->template->parse("rows","row",True);
  }

  $phpgw->template->set_var("add_action",$phpgw->link("admin_add.php"));
  $phpgw->template->set_var("lang_add",lang("add"));

  $phpgw->template->pparse("out","form");
?>