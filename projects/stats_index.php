<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "projects", 
                               "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "project_choose" => "stats_choose.tpl"));

  $t->set_var(link_project,"<a href=\"". $phpgw->link("stats_projectlist.php")."\">".lang("projectstatistics")."</a>");
  $t->set_var(link_user,"<a href=\"". $phpgw->link("stats_userlist.php")."\">".lang("userstatistics")."</a>");

  $t->parse("out", "project_choose", true);
  $t->p("out");

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
