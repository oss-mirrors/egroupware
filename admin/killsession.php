<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($confirm) {
     $phpgw_flags = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_flags["currentapp"] = "admin";
  include("../header.inc.php");
  if ($ksessionid == $phpgw->session->id) {
     Header("Location: " . $phpgw->link("currentusers.php"));
     exit;
  }

  if ($confirm) {
     $phpgw->db->query("delete from sessions where sessionid='$ksession'");
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/admin/currentusers.php",
	    "cd=19"));
  } else {
     ?>
     <center>
      <table border=0 with=65%>
       <tr colspan=2>
        <td align=center>
         <?php echo lang_admin("Are you sure you want to kill this session ?"); ?>
        <td>
       </tr>
       <tr>
         <td>
           <a href="<?php echo $phpgw->link("currentusers.php") . "\">" . lang_common("No"); ?></a>
         </td>
         <td>
           <a href="<?php echo $phpgw->link("killsession.php","ksession=$ksession&confirm=true")
		 . "\">" . lang_common("Yes"); ?></a>
         </td>
       </tr>
      </table>
     </center>
     <?
  }

