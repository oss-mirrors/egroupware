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

  function empty_list($colspan)
  {
    global $phpgw_info;
    return "<tr bgcolor=" . $phpgw_info["theme"]["row_on"] . "><td colspan=$colspan align="
	 . "center>This category is empty</td></tr>";
  }

  if ($level == 1) {
     $phpgw->db->query("select * from bookmarks_cats where owner='" . $phpgw->session->loginid
			. "' and con='$con'");
     $phpgw->db->next_record();

     $main_cat_name = $phpgw->db->f("name");

     echo "<a href=\"" . $phpgw->link("index.php") . "\">Back</a>\n"
        . "<center><table border=0 width=50%><tr bgcolor=$theme[th_bg]>"
	. "<td colspan=2>"
	. "Sub category list for $main_cat_name</td></tr>\n";

     $phpgw->db->query("select * from bookmarks_cats where owner='" . $phpgw->session->loginid
		 . "' and parent_name='$main_cat_name'");
     while ($phpgw->db->next_record()) {
       $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

       echo "<tr bgcolor=$tr_color><td><a href=\"" . $phpgw->link("category_list.php",
	    "con=" . $phpgw->db->f("con") . "&level=2&from=$con") . "\">"
	  . $phpgw->db->f("name") . "</a></td><td width=5%>"
	  . "<a href=\"" . $phpgw->link("edit_category.php3","con=" . $phpgw->db->f("con"))
	  . "\">Edit</a></td></tr>\n";
     }
     echo "</table>\n";

  } else {
     $phpgw->db->query("select * from bookmarks_cats where owner='" . $phpgw->session->loginid
		 . "' and con='$con'");
     $phpgw->db->next_record();
     $cat_name = $phpgw->db->f("name");

     $phpgw->db->query("select name from bookmarks_cats where owner='"
		    . $phpgw->session->loginid . "' and name='" . $phpgw->db->f("parent_name")
			. "'");
     $phpgw->db->next_record();

     echo "<a href=\"" . $phpgw->link("category_list.php","level=1&con=$from") . "\">Back</a>\n"
        . "<center><table border=0 width=50%><tr bgcolor=$theme[th_bg]>"
	. "<td colspan=3>List for " . $phpgw->db->f("name") . "::$cat_name</td>"
	. "</tr>\n";

     $phpgw->db->query("select count(*) from bookmarks where owner='"
			. $phpgw->session->loginid. "' and category='$con'");
     $phpgw->db->next_record();
     $total = $phpgw->db->f(0);

     $phpgw->db->query("select * from bookmarks where owner='" . $phpgw->session->loginid
			. "' and category='$con'");
     while ($phpgw->db->next_record()) {
       $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

       echo "<tr bgcolor=$tr_color><td><a href=\"" . $phpgw->link("redirect.php",
	    "location=" . rawurlencode($phpgw->db->f("url"))
	  . "&con=" . $phpgw->db->f("con")) . "\" target=\"_new\">"
	  . $phpgw->db->f("title") . "</a><td width=5%><a href=\""
	  . $phpgw->link("edit.php","con=" . $phpgw->db->f("con"))
	  . "\">Edit</a></td><td width=5%><a href=\"" . $phpgw->link("view.php",
	    "con=" . $phpgw->db->f("con")) . "\">View</a></td></tr>\n";
     }
//     if ($total == 0)
//        echo empty_list(3);

     echo "</center></table>\n";
  }

  include($phpgw_info["server"]["my_include_dir"] . "/footer.inc.php");

