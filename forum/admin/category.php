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

  $actiontype = "addcat";
  
  if($action) {
   if($action == "addcat") {
    if(!$phpgw->db->query("insert into f_categories (name,descr) values ('$catname','$catdescr')")) {
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
     <center><?echo lang_forums("Create New Category")?></center>
    </td>
   </tr>
   <tr>
    <form method="POST" action="./category.php">
    <?  echo $phpgw->session->hidden_var(); ?>
    <input type="hidden" name="action" value="<?echo $actiontype?>">
    <td><? echo lang_forums("Category Name") ?>:</td>
    <td><input type="text" name="catname" size=40 maxlength=49></td>
   </tr>  
   <tr>
    <td><? echo lang_forums("Category Description") ?>:</td>
    <td><textarea rows="3" cols="40" name="catdescr" virtual-wrap maxlength=240></textarea></td>
   </tr>
   <tr><td colspan=2 align=right><input type="submit" value="<?echo lang_forums("Add Category")?>"></td></tr>

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

