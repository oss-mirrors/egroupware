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

  $phpgw_flags["currentapp"] = "forum";
  include("../../header.inc.php");

?>

<p>
<table border="0" width=100%>
<tr>
<?  echo "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] . "\" align=\"left\"><b>" . lang_forums("Forums") . " " . lang_forums("Admin") . "</b></td>" . "</tr>"; ?>

<tr>
 <td>
  <font size=-1>
<?
echo "<a href=\"" . $phpgw->link("category.php") . "\">" . lang_forums("New Category") ."</a>";
echo " | ";
echo "<a href=\"" . $phpgw->link("forum.php") . "\">" . lang_forums("New Forum") ."</a>";
echo " | ";
echo "<a href=\"" . $phpgw->link("../") . "\">" . lang_forums("Return to Forums") ."</a>";

?>
  </font>
  <br><br>
  <center>
  <table border="0" width=80% bgcolor="<? echo $phpgw_info["theme"]["table_bg"]?>">
   <tr>
    <td colspan=3 bgcolor="<?echo $phpgw_info["theme"]["th_bg"]?>">
     <center><?echo lang_forums("Current Categories and Sub Forums")?></center>
    </td>
   </tr>
   <tr>
    <td>
<?
 $q1 = $phpgw->db->query("select * from f_categories");
 while($phpgw->db->next_record($q1)) {
  $cat_id = $phpgw->db->f("id");
  $cat_name = $phpgw->db->f("name");
  $cat_descr = $phpgw->db->f("descr");

  echo "<tr>\n";
  echo " <td valign=top align=left width=20%>$cat_name</td>\n";
  echo " <td valign=top align=left width=70%>$cat_descr</td>\n";
  echo " <td width=150>" . lang_forums("Edit") . "</td>\n";
  echo "</tr>\n";
  echo "<tr>\n";
  echo " <td colspan=3 align=right valign=top>\n";
  echo "  <table border=0 width=95%>\n";
  
/*  $q2 = $phpgw->db->query("select * from f_forums where cat_id=$cat_id");
   while($phpgw->db->next_record($q2)) {
   echo "  <tr>\n";
   echo "   <td width=20%>" . $phpgw->db->f("name") . "</td>\n";
   echo "   <td width=70%>" . $phpgw->db->f("descr") . "</td>\n";
   echo "   <td width=150>" . lang_forums("Edit") . "</td>\n";
   echo "  </tr>\n";
*/

   $q2 = mysql_db_query($phpgw->db->Database,"select * from f_forums where cat_id=$cat_id");
   while($row = mysql_fetch_array($q2)) {
   echo "  <tr>\n";
   echo "   <td width=20%>" . $row["name"] . "</td>\n";
   echo "   <td width=70%>" . $row["descr"] . "</td>\n";
   echo "   <td width=150>" . lang_forums("Edit") . "</td>\n";
   echo "  </tr>\n";


  }
  echo "  </table><br>\n";
  echo " </td>\n";
  echo "</tr>\n";
 }

?>
    </td>
   </tr>
  </table>
  <br>


  <br><br>
  </center>
 </td>
</tr>
</table>
<?









echo "</center>";
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>

