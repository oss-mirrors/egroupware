<?php

  if ($phpgw_info["flags"]["admin_header"]) {
     $tpl = $phpgw->template;
     $tpl->set_file(array("admin_header" => "admin_header.tpl"));
     
     $tpl->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
     $tpl->set_var("info",lang("Voting booth administration"));
     $tpl->set_var("link_questions",'<a href="' . $phpgw->link("admin_addanswer.php") . '">' . lang("Add answers") . '</a>');
     $tpl->set_var("link_answers",'<a href="' . $phpgw->link("admin_addquestion.php") . '">' . lang("Add answers") . '</a>');
     
     $tpl->pparse("out","admin_header");


/*
     echo '<table border="0" width="100%"><tr bgcolor="' . $phpgw_info["theme"]["th_bg"] . '"><td><b>' . lang("Voting booth administration")
        . '</b></td></tr><tr><td align="left"><a href="' . $phpgw->link("admin_addanswer.php")
        . '">Add answers</a></td><td align="left"><a href="' . $phpgw->link("admin_addquestion.php")
        . '">Add questions</a></td></tr></table><p>';
*/
  }

