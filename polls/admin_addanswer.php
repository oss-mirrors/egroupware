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

  $phpgw_info["flags"] = array("currentapp" => "polls", "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $phpgw->template->set_file(array("form"   => "admin_form.tpl",
                                   "row"    => "admin_form_row_2.tpl"
                                  ));
  if ($submit) {
     $phpgw->db->query("insert into phpgw_polls_data (poll_id,option_text) values ('$poll_id','" . addslashes($answer) . "')",__LINE__,__FILE__);
     $phpgw->template->set_var("message",lang("Answer has been added to poll."));
  } else {
     $phpgw->template->set_var("message","");
  }

  $phpgw->template->set_var("header_message",lang("Add answer to poll"));
  $phpgw->template->set_var("td_message","&nbsp;");
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("form_action",$phpgw->link("admin_addanswer.php"));
  $phpgw->template->set_var("form_button",'<input type="submit" name="submit" value="' . lang("Add") . '">');

  $poll_select = '<select name="poll_id">';
  $phpgw->db->query("select * from phpgw_polls_desc",__LINE__,__FILE__);
  while ($phpgw->db->next_record()) {
     $poll_select .= '<option value="' . $phpgw->db->f("poll_id") . '"';
     if ($poll_id == $phpgw->db->f("poll_id")) {
        $poll_select .= " selected";
     }
     $poll_select .= '>' . $phpgw->db->f("poll_title") . '</option>';
  }
  $poll_select .= "</select>";

  add_template_row($phpgw->template,lang("Which poll"),$poll_select);
  add_template_row($phpgw->template,lang("Answer"),'<input name="answer">');

  $phpgw->template->pparse("out","form");
?>
