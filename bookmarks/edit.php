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

  include ("session.inc");
  if ($permissions[bookmarks] != "Y")
     badsession();

  if (! $submit) {
     navigation_bar();
     $phpgw->db->query("select * from bookmarks where owner='$loginid' and con='"
		 . "$con'");
     $phpgw->db->next_record();

     $cat    = $phpgw->db->f("category");
     $access = $phpgw->db->f("access");
     ?>
      <form method="post" action="edit.php3">
       <input type="hidden" name="sessionid" value="<? echo $sessionid; ?>">
       <input type="hidden" name="con" value="<? echo $con; ?>">

       <table border=0>
       <tr>
        <td>Title:</td>
        <td><input name="title" value="<? echo $phpgw->db->f("title"); ?>"></td>
       </tr>
       <tr>
        <td>URL:</td>
        <td><input name="URL" value="<? echo $phpgw->db->f("url"); ?>"></td>
       </tr>
       <tr>
        <td>Description:</td>
        <td><textarea name="des" cols="40" rows="5" wrap=virtual><? 
            echo $phpgw->db->f("des"); 
          ?></textarea></td>
       </tr>
       <tr>
        <td>Category</td>
        <td><select name="cat_number">
        <?php
          $phpgw->db->query("select * from bookmarks_cats where owner='$loginid' "
		      . "and type='sub' order by parent_name");

          while ($phpgw->db->next_record()) {
            echo "<option value=\"" . $phpgw->db->f("con") . "\"";
            if ($phpgw->db->f("con") == $cat)
               echo " selected";
            echo ">" . $phpgw->db->f("parent_name") . " :: "
	       . $phpgw->db->f("name") . "</option>\n";
          }
       ?>
        </select></td>
       </tr>
       <tr>
        <td>access</td>
        <td><select name="access">
        <option value="private"<?
	    if ($access == "private") echo "selected"; ?>>Private</option>
        <option value="public"<? 
	    if ($access == "public")  echo "selected"; ?>>Public</option>
       </select></td>
       </tr>
       <tr>
        <td><input type="submit" name="submit" value="Submit"></td>
        <td><a href="delete.php3?sessionid=<? echo "$sessionid&con=$con"; ?>">Delete</a></td>
       </tr>
      </table>
      </form>
     <?
     include("bookmarks_footer.inc");
  } else {
     $phpgw->db->query("update bookmarks set category='$cat_number',"
		 . "url='" . addslashes($URL) . "',"
		 . "title='" . addslashes($title) . "',"
		 . "des='" . addslashes($des)
		 . "', access='$access', lastupdate='" . time()
		 . "' where con='$con' and owner='$loginid'");

     //echo "<center>Entry has been updated</center>";
     Header("Location: /secure/bookmarks/?sessionid=$sessionid&cd=14");
  }
?>
