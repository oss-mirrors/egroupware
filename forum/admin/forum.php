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

  if($action) $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);

  $phpgw_info["flags"]["currentapp"] = "forum";
  include("../../header.inc.php");

  $actiontype = "addforum";
  $buttontext = langs("Add Forum");
  $extrahidden = "";

  if($act == "edit") {
   if(!$phpgw->db->query("select * from f_forums where id=$for_id")) {
    print "Error in reading database<br>\n";
     exit;
   } else {
    $phpgw->db->next_record(); 
    $forname = $phpgw->db->f("name");
    $fordescr = $phpgw->db->f("descr");
    $cat_id = $phpgw->db->f("cat_id");
    if(!$phpgw->db->query("select * from f_categories where id=$cat_id")) {
     print "Error in readindg database<br>\n";
     exit;
    } else $phpgw->db->next_record();
    $catname = $phpgw->db->f("name");
    $extraselect = "<option value=\"" . $cat_id . "\">" . $catname ."</option>";
    $extrahidden = "<input type=\"hidden\" name=\"for_id\" value=\"$for_id\">";
    $buttontext = langs("Update Forum");
    $actiontype = "updforum";
   }
  }

  
  if($action) {
   if($action == "addforum") {
    if(!$phpgw->db->query("insert into f_forums (name,descr,cat_id) values ('$forname','$fordescr',$goestocat)")) {
     print "Error in adding forum to database<br>\n";
     exit;
    } else {
     Header("Location: " . $phpgw->link("./"));
     exit;
    }
   } elseif ($action == "updforum" && $for_id) {
    if(!$phpgw->db->query("update f_forums set name='$forname',descr='$fordescr',cat_id=$goestocat where id=$for_id ")) {
     print "Error in updating forum on database<br>\n";
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
<?  echo "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] . "\" align=\"left\"><b>" . langs("Forums") . " " . langs("Admin") . "</b></td>" . "</tr>"; ?>

<tr>
 <td>
  <font size=-1>
<?
echo "<a href=\"" . $phpgw->link("category.php") . "\">" . langs("New Category") ."</a>";
echo " | ";
echo "<a href=\"" . $phpgw->link("forum.php") . "\">" . langs("New Forum") ."</a>";   
echo " | ";
echo "<a href=\"" . $phpgw->link("./") . "\">" . langs("Return to Admin") ."</a>";  
echo " | ";
echo "<a href=\"" . $phpgw->link("../") . "\">" . langs("Return to Forums") ."</a>";
  
?>
  </font>
  <br><br>
  <center>
  <table border="0" width=80% bgcolor="<? echo $phpgw_info["theme"]["table_bg"]?>">
   <tr>
    <td colspan=2 bgcolor="<?echo $phpgw_info["theme"]["th_bg"]?>">
     <center><?echo langs("Create New Forum")?></center>
    </td>
   </tr>
   <tr>
    <form method="POST" action="./forum.php">
    <? echo $phpgw->session->hidden_var(); ?>
    <? echo $extrahidden ?> 
    <input type="hidden" name="action" value="<?echo $actiontype?>">

    <td><? echo langs("Belongs to Category") ?>:</td>
    <td>
     <select name="goestocat">
<?
    if($extraselect) echo $extraselect;
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
    <td><? echo langs("Forum Name") ?>:</td>
    <td><input type="text" name="forname" size=40 maxlength=49 value="<? echo $forname ?>"></td>
   </tr>  
   <tr>
    <td><? echo langs("Forum Description") ?>:</td>
    <td><textarea rows="3" cols="40" name="fordescr" virtual-wrap maxlength=240><? echo $fordescr ?></textarea></td>
   </tr>
   <tr>
    <td colspan=2 align=right>

     <input type="submit" value="<?echo $buttontext?>">
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

