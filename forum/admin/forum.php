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

  if($action) $phpgw_flags = array("noheader" => True, "nonavbar" => True);

  $phpgw_flags["currentapp"] = "forum";
  include("../../header.inc.php");

  $actiontype = "addforum";
  
  if($action) {
   if($action == "addforum") {
    if(!$phpgw->db->query("insert into f_forums (name,descr,cat_id) values ('$forname','$fordescr',$goestocat)")) {
     print "Error in adding forum to database<br>\n";
     exit;
    } else {
     Header("Location: " . $phpgw->link("./"));
     exit;
    }
   }
  }



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
echo "<a href=\"" . $phpgw->link("./") . "\">" . lang_forums("Return to Admin") ."</a>";  
echo " | ";
echo "<a href=\"" . $phpgw->link("../") . "\">" . lang_forums("Return to Forums") ."</a>";
  
?>
  </font>
  <br><br>
  <center>
  <table border="0" width=80% bgcolor="<? echo $phpgw_info["theme"]["table_bg"]?>">
   <tr>
    <td colspan=2 bgcolor="<?echo $phpgw_info["theme"]["th_bg"]?>">
     <center><?echo lang_forums("Create New Forum")?></center>
    </td>
   </tr>
   <tr>
    <form method="POST" action="./forum.php">
    <?  echo $phpgw->session->hidden_var(); ?>
    <input type="hidden" name="action" value="<?echo $actiontype?>">

    <td><? echo lang_forums("Belongs to Category") ?>:</td>
    <td>
     <select name="goestocat">
<?

    $q = $phpgw->db->query("select * from f_categories");
    while($phpgw->db->next_record($q)) {
     $cat_id = $phpgw->db->f("id");
     $cat_name = $phpgw->db->f("name");
     echo "<option value=\"$cat_id\">$cat_name</option>\n";
    }
?>
    </select>
   </td>
   <tr>
    <td><? echo lang_forums("Forum Name") ?>:</td>
    <td><input type="text" name="forname" size=40 maxlength=49></td>
   </tr>  
   <tr>
    <td><? echo lang_forums("Forum Description") ?>:</td>
    <td><textarea rows="3" cols="40" name="fordescr" virtual-wrap maxlength=240></textarea></td>
   </tr>
   <tr>
    <td colspan=2 align=right>

     <input type="submit" value="<?echo lang_forums("Add Forum")?>">
    </td>
   </tr>

  </table>
  </center>
  <br>
 </td>
</tr>

   </tr>
  </table>
  </center>
  <br>




 </td>
</tr>
</table>


<?

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>

