<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectbilling                                   *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              * 
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */
  
  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  
  
if ($submit) {
     $phpgw->common->preferences_delete("byapp",$phpgw_info["user"]["account_id"],"projects");
     $phpgw->common->preferences_add($phpgw_info["user"]["account_id"],"tax","projects");
     
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/preferences/"));
    }
  
     if ($totalerrors) {                                                                                                                                                               
     echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";                                                                                                        
      }     

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "bill_prefs" => "preferences.tpl"));
     
     
     $t->set_var("actionurl",$phpgw->link("preferences.php"));
     

     $t->set_var("lang_action",lang("Project preferences"));
     $t->set_var("lang_select_tax",lang("Select tax for work hours"));
     
     $tax = $phpgw_info["user"]["preferences"]["projects"]["tax"];
     $t->set_var("tax",$tax);
     
     $t->set_var("lang_editsubmitb",lang("Edit"));
    
     $t->pparse("out","bill_prefs");
    
     include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
    ?>

