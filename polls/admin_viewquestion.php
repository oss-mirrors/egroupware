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

  $phpgw->db->query("select poll_title from phpgw_polls_desc where poll_id='$poll_id'");
  $phpgw->db->next_record();
  $poll_title = $phpgw->db->f("poll_title");

  $phpgw->template->set_var("message","");
  $phpgw->template->set_var("header_message",lang("View poll"));
  $phpgw->template->set_var("td_message","&nbsp;");
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("form_action",$phpgw->link("/polls/admin_editquestion.php"));
  $phpgw->template->set_var("form_button_1",'<input type="submit" name="submit" value="' . lang("Edit") . '">');
  $phpgw->template->set_var("form_button_2",'</form><form method="POST" action="' . $phpgw->link("/polls/admin_deletequestion.php","poll_id=$poll_id") . '"><input type="submit" name="submit" value="' . lang("Delete") . '">');

  add_template_row($phpgw->template,lang("Poll question"),$phpgw->strip_html($poll_title));

  $phpgw->db->query("select * from phpgw_polls_data where poll_id='$poll_id'");
  while ($phpgw->db->next_record()) {
     if (! $title_shown) {
        $title = lang("Answers");
        $title_shown = True;
     }
     add_template_row($phpgw->template,$title,$phpgw->strip_html($phpgw->db->f("option_text")));
     $title = "&nbsp;";
  }

  $phpgw->template->pparse("out","form");
  $phpgw->common->phpgw_footer();
?>
