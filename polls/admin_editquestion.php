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

  $phpgw->template->set_file(array("form"   => "admin_form.tpl",
                                   "row"    => "admin_form_row_2.tpl"
                                  ));

  if ($submit) {
     $phpgw->db->query("update phpgw_polls_desc set poll_title='" . addslashes($question)
                     . "' where poll_id='$poll_id'",__LINE__,__FILE__);
     $phpgw->template->set_var("message",lang("Question has been updated"));
  } else {
     $phpgw->template->set_var("message","");
  }

  $phpgw->db->query("select * from phpgw_polls_desc where poll_id='$poll_id'");
  $phpgw->db->next_record();

  $phpgw->template->set_var("header_message",lang("Edit poll question"));
  $phpgw->template->set_var("td_message","&nbsp;");
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("form_action",$phpgw->link("admin_editquestion.php","poll_id=$poll_id"));
  $phpgw->template->set_var("form_button_1",'<input type="submit" name="submit" value="' . lang("Edit") . '">');
  $phpgw->template->set_var("form_button_2",'</form><form method="POST" action="' . $phpgw->link("admin.php","show=questions") . '"><input type="submit" name="submit" value="' . lang("Cancel") . '">');

  add_template_row($phpgw->template,lang("Poll question"),'<input name="question" value="' . $phpgw->db->f("poll_title") . '">');

  $phpgw->template->pparse("out","form");
?>