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

  if ($phpgw_info["flags"]["admin_header"]) {
     $tpl = $phpgw->template;
     $tpl->set_file(array("admin_header" => "admin_header.tpl"));
     
     $tpl->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
     $tpl->set_var("info",lang("Voting booth administration"));
     $tpl->set_var("link_list_questions",'<a href="' . $phpgw->link("admin.php") . '">' . lang("Show questions") . '</a>');
     $tpl->set_var("link_questions",'<a href="' . $phpgw->link("admin_addanswer.php") . '">' . lang("Add answers") . '</a>');
     $tpl->set_var("link_answers",'<a href="' . $phpgw->link("admin_addquestion.php") . '">' . lang("Add questions") . '</a>');
     
     $tpl->pparse("out","admin_header");
  }
?>