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

  $phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class" => True);
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

 echo '<td bgcolor="' . $phpgw_info["theme"]["th_bg"] . '" align="left"><font size=+1><a href=' . $phpgw->link("/forum/index.php") .'>' . lang("Forums") ;
 echo '</a> : <a href=' . $phpgw->link("/forum/forums.php","cat=" . $cat) . '>' . $category . '</a> : ' . $forums . '</font></td></tr>';


 echo "<tr>";
 echo '<td align="left" width="50%" valign="top">';

 include("./inc/bar.inc.php");

 echo "<center>";
 echo ' <table border="0" width="80%">';

   // Collapsed view
   // Something wrong with this section when it goes here the query for $msgid fails
if(!$col) {
  echo "<tr bgcolor=" . $phpgw_info["theme"]["th_bg"] . " align=left>";
  echo "<th width=40%>" .lang("Topic") ."</th>";
  echo "<th>".lang("Author") ."</th>";
  echo "<th>".lang("Replies")."</th>";
  echo "<th>".lang("Latest Reply")."</th>";
  echo "</tr>";

  $phpgw->db->query("select * from f_threads where cat_id=$cat and for_id=$for and parent = -1  order by postdate DESC");
  show_topics($cat,$for);


   // Threaded view  ...... I hate these darn threads, and this gotta redo soon
} else {
    echo "<tr bgcolor=" . $phpgw_info["theme"]["th_bg"] . " align=left>";
  echo "<th width=40%>" .lang("Topic") ."</th>";
  echo "<th>".lang("Author") ."</th>";
  echo "<th>".lang("Date")."</th>";
    echo "</tr>";
    echo "<tr>";

    $phpgw->db->query("select * from f_threads where cat_id = $cat and for_id = $for order by thread DESC, postdate, depth");
    showthread($cat);

   }
 echo "</table>";
 if(!$phpgw->db->num_rows()) echo "<b>" . lang("No messages available!") . "</b>";

 echo "</center>";
   ?>
  </td>
</table>


<?php
  $phpgw->common->phpgw_footer();
?>


