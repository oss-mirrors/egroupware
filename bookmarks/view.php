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

  include("../inc/config.inc.php");
  include($phpgw_info["server"]["api_dir"] . "/header.inc.php");

  if (! $phpgw_info["user"]["permissions"]["bookmarks"])
     badsession();

  function format_cat_name($phpgw->db,$owner,$category)
  {
    global $phpgw;
    if ($owner != $phpgw->session->loginid)
       return "n/a";
    else {
       $phpgw->db->query("select name,parent_name from bookmarks_cats where owner="
	 	   . "'" . $phpgw->session->loginid . "' and con='$category'");
       $phpgw->db->next_record();
       return $phpgw->db->f("parent_name") . " :: " . $phpgw->db->f("name");
    }
  }

  $phpgw->db->query("select * from bookmarks where (owner='"
	      . $phpgw->session->loginid . "' or access='public') and con='$con'");
  $phpgw->db->next_record();

     ?>
       <table border=0>
       <tr>
        <td>Title:</td>
        <td><? echo $phpgw->db->f("title"); ?></td>
       </tr>
       <tr>
        <td>URL:</td>
        <td><a href="<?php echo $phpgw->link("redirect.php3",
                    "location=" . rawurlencode($phpgw->db->f("url"))
		  . "&con=" . $phpgw->db->f("con")) . "\" target=\"_new\">"
		  . $phpgw->db->f("url"); ?></a></td>
       </tr>
       <tr>
        <td>Description:</td>
        <td>
         <form>
          <textarea name="des" cols="40" rows="5" wrap=virtual><? 
           echo $phpgw->db->f("des");
          ?></textarea>
         </form>
        </td>
       </tr>
       <tr>
        <td>Category</td>
        <td><?php 
	      echo format_cat_name($phpgw->db,$phpgw->db->f("owner"),
				   $phpgw->db->f("category")); 
            ?></td>
       </tr>
       <tr>
        <td>access</td>
        <td><? echo $phpgw->db->f("access]"); ?></td>
       </tr>
       <tr>
        <td>Last view</td>
        <td><? 
         if ($phpgw->db->f("lastview") != 0)
            echo $phpgw->preferences->show_date($phpgw->db->f("lastview")); 
         else
            echo "never";
         ?></td>
       </tr>
       <tr>
        <td>Total views</td>
        <td><? echo $phpgw->db->f("totalviews"); ?></td>
       </tr>
  

      </table>
<?php
  include($phpgw_info["server"]["my_include_dir"] . "/footer.inc.php");
