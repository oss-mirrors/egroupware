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
//	        $current = maybe NULL or message number where we are at the moment,
//			   used only in reply (read.php) section to show our current
//			   message with little different color ($phpgw_info["theme"]["bg05"])
//
function showthread ($thread,$current) {
    Global $phpgw, $phpgw_info, $tr_color;

    $SQL = "select * from f_threads where thread = $thread order by pos";
    $dbQ = mysql_db_query($phpgw->db->Database,$SQL);
    while($row = mysql_fetch_array($dbQ)) {
      $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

      if($row["id"] == $current) $tr_color = $phpgw_info["theme"]["bg05"];
      echo "<tr bgcolor=\"$tr_color\">";

      $move = "";
      for($tmp = 1;$tmp <= $row["depth"];$tmp++)
          $move .= "&nbsp;&nbsp;";
 
      $pos = $row["pos"];
      $cat = $row["cat_id"];
      $for = $row["for_id"];
      echo "<td>" . $move . "<a href=" . $phpgw->link("read.php","cat=$cat&for=$for&pos=$pos&col=1&msg=" . $row["id"]) .">"
       . $row["subject"] . "</a></td>\n";

      echo "<td align=left valign=top>" . $row["author"] ."</td>\n";
      echo "<td align=left valign=top>" . $row["postdate"] ."</td>\n";
  
      if($debug) echo "<td>" . $row["id"]." " . $row["parent"] ." " .$row["depth"] ." " . $row["pos"] ."</td>";

    }
}




