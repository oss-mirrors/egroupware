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

  include("../inc/config.inc.php");
  include($phpgw_info["server"]["api_dir"] . "/header.inc.php");

  if (! $phpgw_info["user"]["permissions"]["bookmarks"])
     badsession();

  //include_lang("bookmarks");

  $phpgw->db->query("select * from bookmarks_cats where owner='" . $phpgw->session->loginid
		 . "' and type='main'");

  echo "<center><table border=0 width=50%>\n"
     . "<tr bgcolor=$theme[th_bg]>Bookmarks<br><td colspan=2>Main categorys"
     . "</td></tr>";
  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

    echo "<tr bgcolor=$tr_color><td><a href=\"" . $phpgw->link("category_list.php",
         "con=" . $phpgw->db->f("con") . "&level=1") . "\">"
       . $phpgw->db->f("name") . "</a></td><td width=6%>"
       . "<a href=\"" . $phpgw->link("edit_category.php", "con=" . $phpgw->db->f("con"))
       . "\">Edit</a></td></tr>\n";
  }
  echo "</table></center>\n";

  $phpgw->db->query("select count(*) from bookmarks where access='public' and "
	      . "owner != '" . $phpgw->session->loginid . "'");
  $phpgw->db->next_record();

  if ($phpgw->db->f(0)) {
     echo "<p><center><table border=0 width=50%>\n"
        . "<tr bgcolor=$theme[th_bg]><td colspan=2>Public bookmarks</td></tr>";
     $phpgw->db->query("select * from bookmarks where access='public' AND "
		 . "owner != '" . $phpgw->session->loginid . "'");
     while ($phpgw->db->next_record()) {
       $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

       echo "<tr bgcolor=$tr_color><td><a href=\"" . $phpgw->link("redirect.php",
	    "location=" . rawurlencode($phpgw->db->f("url"))
	  . "&con=" . $phpgw->db->f("con")) . "\">" . $phpgw->db->f("title") . "</a>"
	  . "</td><td width=5%><a href=\"" . $phpgw->link("view.php", "con="
	  . $phpgw->db->f("con")) . "\">View</a></td></tr>\n";
     }
     echo "</table>\n";
  }

  include($phpgw_info["server"]["my_include_dir"] . "/footer.inc.php");
