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

  if ($submit) {
     $phpgw_flags = array("noheader" => True, "nonavbar" => True);
  }

  include("../inc/config.inc.php");
  include($phpgw_info["server"]["api_dir"] . "/header.inc.php");

  if (! $phpgw_info["user"]["permissions"]["bookmarks"])
     badsession();

  //include_lang("bookmarks");

  if (! $submit) {
     ?>
      <p>
      <form action="add_category.php" method="post">
       <table border=0>
       <?php echo $phpgw->session->hidden_var(); ?>
       <tr>
         <td>Main category</td>
         <td><input type="radio" name="cat_type" value="main"></td>
       </tr>
       <tr>
         <td>Sub category</td>
         <td><input type="radio" name="cat_type" value="sub"></td>
       </tr>
       <tr>
         <td>Category parent</td>
         <td><select name="cat_parent">
       <?php
         $phpgw->db->query("select * from bookmarks_cats where owner='"
			    . $phpgw->session->loginid . "' and type='main'");
         while ($phpgw->db->next_record())
           echo "<option value=\"" . $phpgw->db->f("con") . "\">"
	      . $phpgw->db->f("name") . "</option>\n";

       ?>
       </select></td>
       </tr>
       <tr>
         <td>Category name:</td>
         <td><input name="cat_name"></td>
       </tr>
       <tr>
         <td colspan2><input type="submit" name="submit" value="add"></td>
       </tr>
       </table>
      </form>

     <?php
  } else {
     if ($cat_type == "sub") {
        $phpgw->db->query("select name from bookmarks_cats where con='$cat_parent'"
		       . " and owner='" . $phpgw->session->loginid . "'");
        $phpgw->db->next_record();
        $pn = $phpgw->db->f("name");
     }

     if (! $cat_parent)
        $cat_parent = 1;

     $phpgw->db->query("insert into bookmarks_cats (owner,parent,parent_name,type,"
		    . "name) values ('" . $phpgw->session->loginid . "','$cat_parent','$pn','"
		    . "$cat_type','" . addslashes($cat_name) . "') ");

     Header("Location: " . $phpgw->link("add_category.php"));
  }


