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
  if($action) {
    $phpgw_info["flags"]["noheader"] = True;
    $phpgw_info["flags"]["nonavbar"] = True;
  }
  include("../../header.inc.php");

  $actiontype = "addcat";
  $buttontext = lang("Add Forum");
  $extrahidden = "";

  if($act == "edit") {
   if(!$phpgw->db->query("select * from f_categories where id=$cat_id")) {
    print "Error in reading database<br>\n";
     exit;
   } else {
    $phpgw->db->next_record();
    $catname = $phpgw->db->f("name");
    $catdescr = $phpgw->db->f("descr");
    $cat_id = $phpgw->db->f("id"); 

    $extrahidden = "<input type=\"hidden\" name=\"cat_id\" value=\"$cat_id\">"; 
    $buttontext = lang("Update Category");
    $actiontype = "updcat";
   }
  } 

  
  if($action) {
   if($action == "addcat") {
    if(!$phpgw->db->query("insert into f_categories (name,descr) values ('$catname','$catdescr')")) {
     print "Error in adding forum to database<br>\n";
     exit;
    } else {
     Header("Location: " . $phpgw->link("./"));
     exit;
    }
   } elseif ($action == "updcat" && $cat_id) {
    if(!$phpgw->db->query("update f_categories set name='$catname',descr='$catdescr' where id = $cat_id")) {
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
<?  echo "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] . "\" align=\"left\"><b>" . lang("Forums") . " " . lang("Admin") . "</b></td>" . "</tr>"; ?>

<tr>
 <td>
  <font size=-1>
<?
echo "<a href=\"" . $phpgw->link("category.php") . "\">" . lang("New Category") ."</a>";
echo " | ";
echo "<a href=\"" . $phpgw->link("forum.php") . "\">" . lang("New Forum") ."</a>";   
echo " | ";
echo "<a href=\"" . $phpgw->link("./") . "\">" . lang("Return to Admin") ."</a>";
echo " | ";
echo "<a href=\"" . $phpgw->link("../") . "\">" . lang("Return to Forums") ."</a>";
  
?>
  </font>
  <br><br>
  <center>
  <table border="0" width=80% bgcolor="<? echo $phpgw_info["theme"]["table_bg"]?>">
   <tr>
    <td colspan=2 bgcolor="<?echo $phpgw_info["theme"]["th_bg"]?>">
     <center><?echo lang("Create New Category")?></center>
    </td>
   </tr>
   <tr>
    <form method="POST" action="<?php echo $phpgw->link("category.php"); ?>">
    <?php echo $extrahidden; ?> 
    <input type="hidden" name="action" value="<?echo $actiontype?>">
    <td><? echo lang("Category Name") ?>:</td>
    <td><input type="text" name="catname" size=40 maxlength=49 value="<? echo $catname ?>"></td>
   </tr>  
   <tr>
    <td><? echo lang("Category Description") ?>:</td>
    <td><textarea rows="3" cols="40" name="catdescr" virtual-wrap maxlength=240><? echo $catdescr ?></textarea></td>
   </tr>
   <tr><td colspan=2 align=right><input type="submit" value="<? echo $buttontext ?>"></td></tr>

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
