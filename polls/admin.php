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

  if (! $show) {
     $phpgw->common->phpgw_exit();
  }

  if ($order) {
     $ordermethod = " order by $order $sort";
  }

  if ($show == "questions") {
     $phpgw->template->set_file(array("form" => "admin_list_questions.tpl",
                                      "row"  => "admin_list_questions_row.tpl"
                                     ));
  } else {
     $phpgw->template->set_file(array("form" => "admin_list_answers.tpl",
                                      "row"  => "admin_list_answers_row.tpl"
                                     ));  
  }

  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("sort_title",$phpgw->nextmatchs->show_sort_order($sort,"poll_title",$order,"admin.php",lang("Title"),"&show=$show"));
  if ($show == "answers") {
     $phpgw->template->set_var("sort_answer",$phpgw->nextmatchs->show_sort_order($sort,"option_text",$order,"admin.php",lang("Answer"),"&show=$show"));
  }

  $phpgw->template->set_var("lang_edit",lang("edit"));
  $phpgw->template->set_var("lang_delete",lang("delete"));
  $phpgw->template->set_var("lang_view",lang("view"));

  if ($show == "questions") {
     $phpgw->db->query("select * from phpgw_polls_desc $ordermethod",__LINE__,__FILE__);
  } else {
     $phpgw->db->query("select phpgw_polls_data.*, phpgw_polls_desc.poll_title from phpgw_polls_data,"
                     . "phpgw_polls_desc where phpgw_polls_desc.poll_id = phpgw_polls_data.poll_id $ordermethod",__LINE__,__FILE__);
  }
  while ($phpgw->db->next_record()) {
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     $phpgw->template->set_var("tr_color",$tr_color);

     if ($show == "questions") {
        $phpgw->template->set_var("row_title",$phpgw->db->f("poll_title"));
        $phpgw->template->set_var("row_edit",'<a href="' . $phpgw->link("admin_editquestion.php","poll_id=" . $phpgw->db->f("poll_id")) . '">' . lang("Edit") . '</a>');
        $phpgw->template->set_var("row_delete",'<a href="' . $phpgw->link("admin_deletequestion.php","poll_id=" . $phpgw->db->f("poll_id")) . '">' . lang("Delete") . '</a>');
        $phpgw->template->set_var("row_view",'<a href="' . $phpgw->link("admin_viewquestion.php","poll_id=" . $phpgw->db->f("poll_id")) . '">' . lang("View") . '</a>');
     } else {
        $phpgw->template->set_var("row_answer",$phpgw->db->f("option_text"));
        $phpgw->template->set_var("row_title",$phpgw->db->f("poll_title"));
        $phpgw->template->set_var("row_edit",'<a href="' . $phpgw->link("admin_editanswer.php","vote_id=" . $phpgw->db->f("vote_id")) . '">' . lang("Edit") . '</a>');
        $phpgw->template->set_var("row_delete",'<a href="' . $phpgw->link("admin_deleteanswer.php","vote_id=" . $phpgw->db->f("vote_id")) . '">' . lang("Delete") . '</a>');
        $phpgw->template->set_var("row_view",'<a href="' . $phpgw->link("admin_viewanswer.php","vote_id=" . $phpgw->db->f("vote_id")) . '">' . lang("View") . '</a>');     
     }

     $phpgw->template->parse("rows","row",True);
  }

  $phpgw->template->set_var("add_action",$phpgw->link("admin_add" . substr($show,0,(strlen($show)-1)) . ".php"));
  $phpgw->template->set_var("lang_add",lang("add"));

  $phpgw->template->pparse("out","form");
?>