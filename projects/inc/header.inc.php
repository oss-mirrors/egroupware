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
/* $Id$ */                                                                                                                                                                                          
       
   $lid = $phpgw_info["user"]["userid"];

/*       $db2 = $phpgw->db;                                                                                                                                                                 
                                                                                                                                                                                          
       $db2->query("select app_name from applications where (app_enabled = 2 ) or (app_enabled = 1 and app_name='projects')");                                                          
       while ($db2->next_record()) {                                                                                                                                                      
         $invisible_apps[$db2->f("app_name")] = 2;                                                                                                                                          
         $app_status[$db2->f("app_name")]   = $db2->f("app_status");                                                                                                                      
       }                                                                                                                                                                                  
                                                                                                                                                                                          
       if (gettype($lid) == "integer") {                                                                                                                                                  
          $db2->query("select account_permissions from accounts where account_id='$lid'");                                                                                
       } else {                                                                                                                                                                           
          $db2->query("select account_permissions from accounts where account_lid='$lid'");                                                                             
       }                                                                                                                                                                                  
       $db2->next_record();                                                                                                                                                               
                                                                                                                                                                                          
       $pl = explode(":",$db2->f("account_permissions"));                                                                                                                                 
                                                                                                                                                                                          
       for ($i=0; $i<count($pl); $i++) {                                                                                                                                                  
          if ($invisible_apps[$pl[$i]]) {                                                                                                                                                   
             $invisible_apps[$pl[$i]] = True;                                                                                                                                                  
          }                                                                                                                                                                               
       }
       
          if ($phpgw_info["user"]["userid"] != $lid) {                                                                                                           
          $db2->query("select account_groups from accounts where account_lid='$lid'");                                                      
          $db2->next_record();                                                                                                                                
          $gl = explode(",",$db2->f("account_groups"));                                                                                                       
          } else {                                                                                                                                               
          $gl = $phpgw_info["user"]["groups"];                                                                                                                
          }                                                                                                                                                      
                                                                                                                                                              
          for ($i=1; $i<(count($gl)-1); $i++) {                                                                                                                  
          $ga = explode(":",$gl[$i]);                                                                                                                         
          $groups[$ga[0]] = $ga[1];                                                                                                                           
           }
       
          while ($gl && $group = each($gl)) {                                                                                                                                
          $db2->query("select group_apps from groups where group_id=".$group[0]);                                                                                       
          $db2->next_record();                                                                                                                                                            
                                                                                                                                                                                          
          $gp = explode(":",$db2->f("group_apps"));                                                                                                                                       
          for ($i=1,$j=0;$i<count($gp)-1;$i++,$j++) {                                                                                                                                     
             $invisible_apps[$gp[$i]] = True;                                                                                                                                                  
          }                                                                                                                                                                               
        }                                                                                                                                                                                  
                                                                                                                                                                                          
       while ($sa = each($invisible_apps)) {                                                                                                                                                
          if ($sa[1] == 2) {                                                                                                                                                              
             $return_apps[$sa[0]] = True;                                                                                                                                                 
          }                                                                                                                                                                               
        }  */       

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
                        . "\" align=\"left\"><b>" . $admin_info);

     
     
//     if($invisible_apps["projectbilling"]==True)
     $t->set_var("link_billing","<a href=\"" . $phpgw->link("bill_index.php") . "\">" . lang("Project billing") ."</a>");
//     else                                                                                                                                                                                          
//     $t->set_var("link_billing","");     

//     if($invisible_apps["projecthours"]==True)
     $t->set_var("link_hours","<a href=\"" . $phpgw->link("hours_index.php") . "\">" . lang("Project hours") ."</a>");
//     else                                                                                                                                                                                          
//     $t->set_var("link_hours","");

//     if($invisible_apps["projectstatistics"]==True)
     $t->set_var("link_statistics","<a href=\"" . $phpgw->link("stats_projectlist.php") . "\">" . lang("Project statistics") ."</a>");
//     else                                                                                                                                                                                          
//     $t->set_var("link_statistics","");

//     if($invisible_apps["projectdelivery"]==True)
     $t->set_var("link_delivery","<a href=\"" . $phpgw->link("del_index.php") . "\">" . lang("Project delivery") ."</a>");                                                                                 
//     else                                                                                                                                                                                          
//     $t->set_var("link_delivery","");
     
//    if ($phpgw_info["apps"]["projects"]["enabled"]) {
     $t->set_var("link_return_projects","<a href=\"" . $phpgw->link("index.php") . "\">" . lang("Return to projects") ."</a>");     
//         }
//    else {     
//       $t->set_var("link_hours",""); 
//       $t->set_var("link_return_projects","");
//         }
    $t->pparse("out","projects_header");
 
?>