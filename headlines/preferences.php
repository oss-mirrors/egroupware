<?php

  /**************************************************************************\
  * phpGroupWare - headlines                                                 *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);

  $phpgw_info["flags"]["currentapp"] = "preferences";
  include("../header.inc.php");

  if (! $submit) {
     $phpgw->common->phpgw_header();
     $phpgw->common->navbar();
  ?>
    <form method="POST" action="<?php echo $phpgw->link(); ?>">
      <table>
      <tr><td>
<?php
      echo lang("select headline news sites").":</td></tr><tr>";
      echo "<td><select name=\"headlines[]\" multiple size=5>\n";
//      $phpgw->db->query("select * from users_headlines where owner='"
//  	               .$phpgw_info["user"]["userid"] . "'");
//      while ($phpgw->db->next_record()){
//     	$users_headlines[$phpgw->db->f("site")] = " selected";
//      }
      while ($preference = @each($phpgw_info["user"]["preferences"]["headlines"])){
     	$users_headlines[$preference[1]] = " selected";
      }
  
      $phpgw->db->query("SELECT con,display FROM news_site ORDER BY display asc");
      while ($phpgw->db->next_record()) {
     	echo "<option value=\"" . $phpgw->db->f("con") . "\""
	         . $users_headlines[$phpgw->db->f("con")] . ">"
    	     . $phpgw->db->f("display") . "</option>";

      }
      echo "</select></td>\n";
?>
    </tr><tr><td><input type="submit" name="submit" value="<?php echo lang("submit"); ?>"></td></tr></table>
    </form>
<?php
  } else {
//    include($phpgw_info["server"]["server_root"] . "/headlines/inc/functions.inc.php");
//    headlines_update($phpgw_info["user"]["userid"],$headlines);
   if (count($headlines)) {
      $phpgw->common->preferences_delete("byapp",$phpgw_info["user"]["account_id"],"headlines");
      while ($value = each($headlines)) {
         $phpgw->common->preferences_add($phpgw_info["user"]["account_id"],$value[1],"headlines","True");
      }
   }


    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"]."/preferences/index.php"));
  }

  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
