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
  // This is mainly used for stats on the sites visted in your bookmarks

  $phpgw_flags = array("noheader" => True, "nonavbar" => True);

  include("../inc/config.inc.php");
  include($phpgw_info["server"]["api_dir"] . "/header.inc.php");

  if (! $phpgw_info["user"]["permissions"]["bookmarks"])
     badsession();

  //include_lang("bookmarks");
  // If they are not the owner, it will not update the counter.
  $phpgw->db->query("update bookmarks set totalviews=totalviews+1,lastview='"
	         . time() . "' where owner='" . $phpgw->session->loginid . "' and con='$con'");
  Header("Location: " . rawurldecode($location) );
?>
