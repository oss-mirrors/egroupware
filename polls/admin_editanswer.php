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
     $phpgw->db->query("update phpgw_polls_data set poll_id='$poll_id',option_text='"
                     . addslashes($answer) . "' where vote_id='$vote_id'",__LINE__,__FILE__);
     $phpgw->template->set_var("message",lang("Answer has been updated"));
  } else {
     $phpgw->template->set_var("message","");
  }

  $phpgw->db->query("select * from phpgw_polls_data where vote_id='$vote_id'");
  $phpgw->db->next_record();
  $answer_value = $phpgw->db->f("option_text");
  $poll_id = $phpgw->db->f("poll_id");

  $phpgw->template->set_var("header_message",lang("Edit answer"));
  $phpgw->template->set_var("td_message","&nbsp;");
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("form_action",$phpgw->link("admin_editanswer.php","vote_id=$vote_id"));
  $phpgw->template->set_var("form_button_1",'<input type="submit" name="submit" value="' . lang("Edit") . '">');
  $phpgw->template->set_var("form_button_2",'</form><form method="POST" action="' . $phpgw->link("admin.php","show=answers") . '"><input type="submit" name="submit" value="' . lang("Cancel") . '">');

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
  add_template_row($phpgw->template,lang("Answer"),'<input name="answer" value="' . $answer_value . '">');

  $phpgw->template->pparse("out","form");
?>
