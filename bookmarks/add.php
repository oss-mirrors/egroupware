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

  $currentapp = "bookmarks";

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
      <form method="post" action="<?php echo $phpgw->link("add.php"); ?>">
       <table border=0>
       <tr>
        <td>Title:</td>
        <td><input name="title"></td>
       </tr>
       <tr>
        <td>URL:</td>
        <td><input name="URL"></td>
       </tr>
       <tr>
        <td>Description:</td>
        <td><textarea name="des" cols="40" rows="5" wrap=virtual></textarea></td>
       </tr>
       <tr>
        <td>Category</td>
        <td><select name="cat_number">
        <?php
          $phpgw->db->query("select * from bookmarks_cats where owner='" . $phpgw->session->loginid
			     . "' and type='sub' order by parent_name");

          while ($phpgw->db->next_record()) {
            echo "<option value=\"" . $phpgw->db->f("con") . "\"";
//            if ($phpgw->db->f("con") == $cat)
//               echo " selected";
            echo ">" . $phpgw->db->f("parent_name") . " :: "
	       . $phpgw->db->f("name") . "</option>\n";
          }
       ?>
        </select></td>
       </tr>
       <tr>
        <td>access</td>
        <td><select name="access">
        <option value="private" selected>Private</option>
        <option value="public">Public</option>
       </select></td>
       </tr>
       <tr>
        <td colspan=2><input type="submit" name="submit" value="Submit"></td>
       </tr>
      </table>
      </form>
     <?
  } else {
     $phpgw->db->query("insert into bookmarks (owner,category,url,title,des,access"
		    . ",lastupdate,totalviews) values ('" . $phpgw->session->loginid
		    . "','$cat_number','"
		    . addslashes($URL) . "','"
		    . addslashes($title) . "','"
		    . addslashes($des) . "','$access','" . time() . "',0)");

     Header("Location: " . $phpgw->link("index.php", "cd=14"));
  }
