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
     $phpgw->preferences->change("projects","tax");
     $phpgw->preferences->change("projects","address");
     $phpgw->preferences->commit();     
     
    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/preferences/"));
     }
  
     if ($totalerrors) {                                                                                                                                                               
     echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";                                                                                                        
      }     

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "prefs" => "preferences.tpl"));
     
     
     $t->set_var("actionurl",$phpgw->link("preferences.php"));
     $t->set_var("addresses_link",$phpgw->link("addresses.php","query="));
    
     $t->set_var("lang_action",lang("Project preferences"));
     $t->set_var("lang_select_tax",lang("Select tax for work hours"));
     $t->set_var("lang_select",lang("Select per button !"));
     
     $tax = $phpgw_info["user"]["preferences"]["projects"]["tax"];
     $t->set_var("tax",$tax);
     
     $t->set_var("lang_address",lang("Select your address"));                                                                                                                                         
     
     if (isset($phpgw_info["user"]["preferences"]["projects"]["address"])) {
    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                                                         
    $phpgw->db->query("SELECT ab_id,ab_firstname,ab_lastname,ab_company_id,company_name FROM "                                                                                                       
                     . "addressbook,customers where "                                                                                                                                          
                     . "ab_company_id='" .$phpgw_info["user"]["preferences"]["projects"]["address"]."' and "
                     . "customers.company_id=addressbook.ab_company_id");                                                                                                                       
    if ($phpgw->db->next_record()) {                                                                                                                                                                 
        $t->set_var("address_name",$phpgw->db->f("company_name")." [ ".$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")." ]");                                                                     
      } else {                                                                                                                                                                                   
        $t->set_var("address_name","");                                                                                                                                                        
        }                                                                                                                                                                                          
       }                                                                                                                                                                                          
    else {                                                                                                                                                                                     
    $phpgw->db->query("select ab_id,ab_lastname,ab_firstname,ab_company from addressbook where "                                                                                                     
                        . "ab_id='" .$phpgw_info["user"]["preferences"]["projects"]["address"]."'");                                                                                                                            
        if ($phpgw->db->next_record()) {                                                                                                                                                             
        if (!$phpgw->db->f("ab_company")) {
        $t->set_var("address_name",$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname"));        
            }
        else {
        $t->set_var("address_name",$phpgw->db->f("ab_company")." [ ".$phpgw->db->f("ab_firstname")." ".$phpgw->db->f("ab_lastname")." ]");                                                                       
          }                                                                                                                                                                                      
         }
        else {                                                                                                                                                                                 
        $t->set_var("address_name","");                                                                                                                                                        
        }                                                                                                                                                                                      
      }
     }
     else {
    $t->set_var("address_con","");
    $t->set_var("address_name","");
      }


     $t->set_var("lang_editsubmitb",lang("Edit"));
    
     $t->pparse("out","prefs");
    
     include($phpgw_info["server"]["api_inc"] . "/footer.inc.php");
    ?>

