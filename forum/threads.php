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

  $phpgw_info["flags"]["currentapp"] = "forum";
  include("../header.inc.php");

?>

<p>
<table border="0" width="100%">
 <tr>
<? 
 $phpgw->db->query("select * from f_categories where id = $cat");
 $phpgw->db->next_record();
 $category = $phpgw->db->f("name");

 $phpgw->db->query("select * from f_forums where id = $for");
 $phpgw->db->next_record();
 $forums = $phpgw->db->f("name");

 $catfor = "cat=" . $cat . "&for=" . $for;

 echo '<td bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="left"><font size=+1><a href=' . $phpgw->link("index.php") .'>' . langs("Forums") ;
 echo '</a> : <a href=' . $phpgw->link("forums.php","cat=" . $cat) . '>' . $category . '</a> : ' . $forums . '</font></td></tr>';


 echo "<tr>";
 echo '<td align="left" width="50%" valign="top">';

 include("./inc/bar.inc.php");

 echo "<center>";
 echo ' <table border="0" width="80%">';

   // Collapsed view
   if(!$col) {
    echo "<tr bgcolor=" . $phpgw_info["theme"]["th_bg"] . " align=left>";
	echo "<th width=40%>" .langs("Topic") ."</th>";
	echo "<th>".langs("Author") ."</th>";
	echo "<th>".langs("Replies")."</th>";
	echo "<th>".langs("Latest Reply")."</th>";
    echo "</tr>";
    $phpgw->db->query("select * from f_threads where cat_id = $cat and for_id = $for and parent = -1");
    while($phpgw->db->next_record()) {
     $replycount = 0;
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     echo "<tr bgcolor=\"$tr_color\">";
     echo "<td><a href=" . $phpgw->link("read.php","cat=$cat&for=$for&msg=$msg" . $phpgw->db->f("id")) .">" 
       . $phpgw->db->f("subject") . "</a></td>\n";

     $lastreply = $phpgw->db->f("postdate");
     echo "<td align=left valign=top>" . $phpgw->db->f("author") . "</td>\n";
      $msgid = $phpgw->db->f("id");
      $mainid = $phpgw->db->f("main");

      $dbQ = mysql_db_query($phpgw->db->Database,"select count(*) from f_threads where thread = " . $msgid);
      $row = mysql_fetch_array($dbQ);
      $replycount = $row[0] - 1;

      $dbQ = mysql_db_query($phpgw->db->Database,"select postdate from f_threads where parent = " . $msgid . " order by postdate ");
      $row = mysql_fetch_array($dbQ);	
      if($row[0]) $lastreply = $row[0];
 
     echo "<td align=left valign=top>$replycount</td>\n";
     echo "<td align=left valign=top>$lastreply</td>\n";

     }

     echo "</tr>\n";


   // Threaded view  ...... I hate these darn threads, and this gotta redo soon
   } else {
    echo "<tr bgcolor=" . $phpgw_info["theme"]["th_bg"] . " align=left>";
	echo "<th width=40%>" .langs("Topic") ."</th>";
	echo "<th>".langs("Author") ."</th>";
	echo "<th>".langs("Date")."</th>";
    echo "</tr>";
    echo "<tr>";


    $new = $phpgw->db->query("select * from f_threads where cat_id = $cat and for_id = $for and parent = -1 order by thread");
    while($phpgw->db->next_record($new)) {
         showthread($phpgw->db->f("thread"),NULL);
    }



   }
 echo "</table>";
 if(!$phpgw->db->num_rows()) echo "<b>" . langs("No messages available!") . "</b>";

 echo "</center>";
   ?>
  </td>
</table>


<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>


