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

  if ($submit) {
     $phpgw->db->query("insert into phpgw_polls_desc (poll_title,poll_timestamp) values ('" . addslashes($title) . "','" . time() . "')",__LINE__,__FILE__);
  } else {
     $phpgw->template->set_var("message","");
  }

  $phpgw->template->set_file(array("form"   => "admin_form.tpl",
                                   "row"    => "admin_form_row_2.tpl"
                                  ));

  $phpgw->template->set_var("form_action",$phpgw->link("admin_addquestion.php"));
  $phpgw->template->set_var("form_button",'<input type="submit" name="submit" value="' . lang("Add") . '">');

  $phpgw->template->set_var("tr_color",$phpgw->nextmatchs->alternate_row_color());
  $phpgw->template->set_var("label",lang("Question"));
  $phpgw->template->set_var("value",'<input name="title">');
  $phpgw->template->parse("rows","row",True);

  $phpgw->template->pparse("out","form");

?>
