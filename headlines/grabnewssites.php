<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "admin", "enable_network_class" => True, "noheader" => True, "nonavbar" => True);
  include("../header.inc.php");
  include($phpgw_info["server"]["server_root"]."/headlines/inc/functions.inc.php");

  $headlines = new headlines;
  $headlines->getList();

  header("Location: ".$phpgw->link("admin.php"));
?>

