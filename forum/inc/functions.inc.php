<?php
  /**************************************************************************\
  * phpGroupWare - Forums                                                    *
  * http://www.phpgroupware.org                                              *
  * Written by Jani Hirvinen <jpkh@shadownet.com>                            *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

// Global functions for phpgw forums



//
// showthread shows thread in threaded mode :)
//  params are: $thread = id from master message, father of all messages in this thread
//          $current = maybe NULL or message number where we are at the moment,
//         used only in reply (read.php) section to show our current
//         message with little different color ($phpgw_info["theme"]["bg05"])
//
function showthread ($cat) {
    global $phpgw, $phpgw_info, $tr_color;

    while($phpgw->db->next_record()) {
      $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

      if($phpgw->db->f("id") == $current) $tr_color = $phpgw_info["theme"]["bg05"];
      echo "<tr bgcolor=\"$tr_color\">";

      $move = "";
      for($tmp = 1;$tmp <= $phpgw->db->f("depth"); $tmp++)
          $move .= "&nbsp;&nbsp;";

      $pos = $phpgw->db->f("pos");
      $cat = $phpgw->db->f("cat_id");
      $for = $phpgw->db->f("for_id");
      echo "<td>" . $move . "<a href=" . $phpgw->link("read.php","cat=$cat&for=$for&pos=$pos&col=1&msg=" . $phpgw->db->f("id")) .">"
       . $phpgw->db->f("subject") . "</a></td>\n";

      echo "<td align=left valign=top>" . $phpgw->db->f("author") ."</td>\n";
      echo "<td align=left valign=top>" . $phpgw->db->f("postdate") ."</td>\n";

      if($debug) echo "<td>" . $phpgw->db->f("id")." " . $phpgw->db->f("parent") ." "
                    . $phpgw->db->f("depth") ." " . $phpgw->db->f("pos") ."</td>";

    }
}


function show_topics($cat,$for) {

    global $phpgw, $phpgw_info, $tr_color;

    while($phpgw->db->next_record())
    {
      $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
      echo "<tr bgcolor=\"$tr_color\">";
      echo "<td><a href=" . $phpgw->link("read.php","cat=$cat&for=$for&msg=$msg" . $phpgw->db->f("id")) .">" . $phpgw->db->f("subject") . "</a></td>\n";
      $lastreply = $phpgw->db->f("postdate");
      echo "<td align=left valign=top>" . $phpgw->db->f("author") . "</td>\n";
      $msgid = $phpgw->db->f("id");
      $mainid = $phpgw->db->f("main");

      echo "<td align=left valign=top>" . $phpgw->db->f("n_replies") . "</td>\n";
      echo "<td align=left valign=top>$lastreply</td>\n";
    }

  echo "</tr>\n";

}

