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

  include("session.inc");
  if ($permissions[bookmarks] != "Y")
     badsession();

  navigation_bar();

  if (! $submit) {
     $phpgw->db->query("select * from bookmarks_cats where owner='$loginid' "
		 . "and con='$con'");
     $phpgw->db->next_record();

     $cat_type	 = $phpgw->db->f("type");
     $cat_name	 = $phpgw->db->f("name");
     $cat_parent = $phpgw->db->f("parent");
     ?>
      <p>
      <form action="edit_category.php3" method="post">
       <table border=0>
       <input type="hidden" name="sessionid" value="<? echo $sessionid; ?>">
       <input type="hidden" name="con" value="<? echo $con; ?>">
       <input type="hidden" name="cat_type" value="<? echo $cat_type; ?>">

     <?php
       if ($cat_type != "main") {
       ?>
       <tr>
         <td>Category parent</td>
         <td><select name="cat_parent">
       <?php
         $phpgw->db->query("select * from bookmarks_cats where owner='$loginid' "
		     . "and type='main'");

         while ($phpgw->db->next_record()) {
           echo "<option value=\"" . $phpgw->db->f("con") . "\"";
           if ($phpgw->db->f("con") == $cat_parent)
              echo " selected";
           echo ">" . $phpgw->db->f("name") . "</option>\n";
         }
       ?>
       </select></td>
       </tr>
       <?php } ?>
       <tr>
         <td>Category name:</td>
         <td><input name="cat_name" value="<? echo $cat_name; ?>"></td>
       </tr>
       <tr>
         <td colspan2><input type="submit" name="submit" value="Change"></td>
       </tr>
       </table>
      </form>

     <?php
  } else {
     if ($cat_type != "main") {
        $phpgw->db->query("select name from bookmarks_cats where con='"
		       . "$cat_parent'");
        $phpgw->db->next_record();

        $phpgw->db->query("update bookmarks_cats set name='"
		       . addslashes($cat_name) . "', parent_name='"
		       . addslashes($phpgw->db->f("name"))
		       . "', parent='$cat_parent' where con='$con'");
     } else {
        $phpgw->db->query("update bookmarks_cats set name='"
		       . addslashes($cat_name) . "' where con='$con'");
        $phpgw->db->query("update bookmarks_cats set parent_name='"
		       . addslashes($cat_name) . "' where owner='$loginid' "
		       . "and type='sub' and parent='$con'");

     }

     echo "<center>Category has been updated</center>";
  }

?>
