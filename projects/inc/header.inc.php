<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              * 
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
       
      $lid = $phpgw_info["user"]["userid"];                                                                                                                                                                                                                                                                                                                            
      
      $t = new Template($phpgw_info["server"]["app_tpl"]);
      $t->set_file(array("projects_header" => "header.tpl"));
      $phpgw->db->query("select group_id from groups where group_name = 'projectAdmin'");
      $admin_info = lang("projects");
      
      if ($phpgw->db->next_record()) {
         $group_id = $phpgw->db->f("group_id");
	 $phpgw->db->query("select account_lid from accounts where account_groups like '%,$group_id%' and account_lid='$lid'");
         $phpgw->db->next_record();
	 if($phpgw->db->f("account_lid") == $lid)
            $admin_info = lang("projects")."&nbsp;&nbsp;&nbsp;".lang("admin");
	 }
      $t->set_var("admin_info", "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] 
                        . "\" align=\"left\"><b>" . $admin_info . "</b>");

     
     

     $t->set_var("link_billing","<a href=\"" . $phpgw->link("../projectbilling/") . "\">" . lang("projectbilling") ."</a>");
     $t->set_var("link_hours","<a href=\"" . $phpgw->link("../projecthours/") . "\">" . lang("projecthours") ."</a>");
     $t->set_var("link_statistics","<a href=\"" . $phpgw->link("../projectstatistics/") . "\">" . lang("projectstatistics") ."</a>");
     $t->set_var("link_delivery","<a href=\"" . $phpgw->link("../projectdelivery/") . "\">" . lang("projectdelivery") ."</a>");                                                                                 

     $t->pparse("out","projects_header");
 
?>