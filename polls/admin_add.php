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

  $phpgw->template->set_file(array("form"  => "admin_form.tpl",
                                   "row"   => "admin_form_row.tpl",
                                   "row_2" => "admin_form_row_2.tpl"
                                  ));

  $phpgw->template->set_var("form_action",$phpgw->link("admin_add.php","step=$step"));
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
  $phpgw->template->set_var("td_message",$phpgw_info["theme"]["th_bg"]);

  $phpgw->template->pparse("out","form");
?>