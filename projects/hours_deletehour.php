<?php
  /**************************************************************************\
  * phpGroupWare - projcts/projecthours                                      *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  if ($confirm) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");


  if (! $id) {
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/hours_index.php",
	    "&sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));
  }

  if ($confirm) {
     $phpgw->db->query("delete from p_hours where id='$id'");
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/hours_index.php",
	    "cd=16&sort=$sort&order=$order&query=$query&start="
	  . "$start&filter=$filter"));
  } else {
	$common_hidden_vars =
 	  "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 	. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 	. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 	. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 	. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";
     
     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "projects_delete" => "delete.tpl"));
     
     $t->set_var("deleteheader",lang("Are you sure you want to delete this entry"));
     
     $nolinkf = $phpgw->link("index.php","sort=$sort&order=$order&"
     				. "query=$query&start=$start&filter=$filter");
     $nolink = "<a href=\"$nolinkf\">" . lang("No") ."</a>";
     $t->set_var("nolink",$nolink);
    
     $yeslinkf = $phpgw->link("delete.php","id=$id&confirm=True&sort="
				. "$sort&order=$order&query=$query&start=$start"
				. "&filter=$filter");

     $yeslinkf = "<FORM method=\"POST\" name=yesbutton action=\"".$phpgw->link("hours_deletehour.php")."\">"
                 . $common_hidden_vars
                 . "<input type=hidden name=id value=$id>"
		 . "<input type=hidden name=confirm value=True>"
                 . "<input type=submit name=yesbutton value=Yes>"
                 . "</FORM><SCRIPT>document.yesbutton.yesbutton.focus()</SCRIPT>";

     $yeslink = "<a href=\"$yeslinkf\">" . lang("Yes") ."</a>";
     $yeslink = $yeslinkf;

     $t->set_var("yeslink",$yeslink);
     
     $t->pparse("out", "projects_delete");
 ?>
     
   <?php
  }
