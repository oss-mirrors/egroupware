<?php
  /**************************************************************************\
  * phpGroupWare - bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $currentapp = "bookmarks";
  $phpgw_flags = array("noheader" => True, "nonavbar" => True);

  include("../inc/config.inc.php");
  include($phpgw_info["server"]["api_dir"] . "/header.inc.php");

  if (! $phpgw_info["user"]["permissions"]["bookmarks"])
     badsession();

  $phpgw->db->query("delete from bookmarks where owner='" . $phpgw->session->loginid
		 . "' and con='$con'");
  echo "<p><center>Entry deleted</center>\n";

