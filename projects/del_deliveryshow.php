<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectdelivery                                  *
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
  
  $phpgw_info["flags"] = array("currentapp" => "projects",
                               "noheader" => True, 
                               "nonavbar" => True);         
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "delivery_list_t" => "del_deliveryform.tpl",
                      "deliverypos_list"   => "del_deliveryform.tpl"));
  $t->set_block("delivery_list_t", "deliverypos_list", "list");


  if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                                                 
      $phpgw->db->query("SELECT p_projects.address,p_delivery.project_id,ab_company_id,company_name,ab_firstname,ab_lastname,ab_street,ab_zip, "                                                           
                 . "ab_city FROM p_projects,addressbook,customers,p_delivery WHERE "                                                                                                                       
                 . "p_delivery.id=$delivery_id AND p_delivery.project_id=p_projects.id AND "                                                                                            
                 . "p_projects.address=customers.company_id AND customers.company_id=addressbook.ab_company_id");                                                                    
      if ($phpgw->db->next_record()) {                                                                                                                                               
      $t->set_var("ad_company",$phpgw->db->f("company_name"));                                                                                                                       
      $t->set_var("ad_firstname",$phpgw->db->f("ab_firstname"));                                                                                                                     
      $t->set_var("ad_lastname",$phpgw->db->f("ab_lastname"));                                                                                                                       
      $t->set_var("ad_street",$phpgw->db->f("ab_street"));                                                                                                                           
      $t->set_var("ad_zip",$phpgw->db->f("ab_zip"));                                                                                                                                 
      $t->set_var("ad_city",$phpgw->db->f("ab_city"));                                                                                                                               
          }                                                                                                                                                                          
      else {                                                                                                                                                                         
      $t->set_var("ad_company","");                                                                                                                                                  
      $t->set_var("ad_firstname","");                                                                                                                                                
      $t->set_var("ad_lastname","");                                                                                                                                                 
      $t->set_var("ad_street","");                                                                                                                                                   
      $t->set_var("ad_zip","");                                                                                                                                                      
      $t->set_var("ad_city","");                                                                                                                                                     
         }                                                                                                                                                                           
        }
      else {                                                                                                                                                                         
         $phpgw->db->query("SELECT p_projects.address,p_delivery.project_id,ab_id,ab_company,ab_firstname,ab_lastname,ab_street,ab_zip, "                                             
                 . "ab_city FROM p_projects,addressbook,p_delivery WHERE "                                                                                                            
                 . "p_delivery.id=$delivery_id AND p_delivery.project_id=p_projects.id AND "                                                                                            
                 . "p_projects.address=addressbook.ab_id");                                                                                                                          
      if ($phpgw->db->next_record()) {                                                                                                                                               
      $t->set_var("ad_company",$phpgw->db->f("ab_company"));                                                                                                                         
      $t->set_var("ad_firstname",$phpgw->db->f("ab_firstname"));                                                                                                                     
      $t->set_var("ad_lastname",$phpgw->db->f("ab_lastname"));                                                                                                                       
      $t->set_var("ad_street",$phpgw->db->f("ab_street"));                                                                                                                           
      $t->set_var("ad_zip",$phpgw->db->f("ab_zip"));                                                                                                                                 
      $t->set_var("ad_city",$phpgw->db->f("ab_city"));                                                                                                                               
            }                                                                                                                                                                        
                                                                                                                                                                                     
   else {                                                                                                                                                                            
      $t->set_var("ad_company","");                                                                                                                                                  
      $t->set_var("ad_firstname","");                                                                                                                                                
      $t->set_var("ad_lastname","");                                                                                                                                                 
      $t->set_var("ad_street","");                                                                                                                                                   
      $t->set_var("ad_zip","");                                                                                                                                                      
      $t->set_var("ad_city","");                                                                                                                                                     
       }                                                                                                                                                                             
     }   


   $t->set_var("site_title",$phpgw_info["site_title"]);   
   $charset = $phpgw->translation->translate("charset");                                                                                                                                         
   $t->set_var("charset",$charset);   
   $t->set_var("to","");        
   $t->set_var(lang_delivery,lang("Delivery ID"));      
   $t->set_var(lang_project,lang("Project"));      
   $t->set_var(lang_pos,lang("Position"));       
   $t->set_var(lang_workunits,lang("Workunits"));     
   $t->set_var(lang_date,lang("Date"));      
   $t->set_var(lang_descr,lang("Description"));
  
   if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
   $phpgw->db->query("SELECT p_delivery.customer,p_delivery.num,p_delivery.project_id,p_delivery.date, "
                 . "ab_company_id,company_name,ab_firstname,ab_lastname,ab_street,ab_zip, "
                 . "ab_city,p_projects.title FROM addressbook,customers,p_delivery,p_projects WHERE "
                 . "p_delivery_id=$delivery_id AND p_delivery.project_id=p_projects.id AND "
                 . "p_delivery.customer=ab_company_id AND customers.company_id=addressbook.ab_company_id");
   if ($phpgw->db->next_record()) {  
   $t->set_var("company",$phpgw->db->f("company_name"));
   $t->set_var("firstname",$phpgw->db->f("ab_firstname"));                                                                                                                                       
   $t->set_var("lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                         
   $t->set_var("street",$phpgw->db->f("ab_street"));                                                                                                                                             
   $t->set_var("zip",$phpgw->db->f("ab_zip"));                                                                                                                                                   
   $t->set_var("city",$phpgw->db->f("ab_city"));
   $t->set_var("delivery_day",date("j",$phpgw->db->f("date")));                                                                                                                                       
   $t->set_var("delivery_month",date("n",$phpgw->db->f("date")));                                                                                                                                     
   $t->set_var("delivery_year",date("Y",$phpgw->db->f("date")));                                                                                                                                      
   $t->set_var("delivery_num",$phpgw->db->f("num"));                                                                                                                                                  
   $t->set_var("title",$phpgw->db->f("title"));                                                                                                                                                      
      }
    }
   else {    
   $phpgw->db->query("SELECT p_delivery.customer,p_delivery.num,p_delivery.project_id,p_delivery.date, "
                 . "ab_id,ab_company,ab_firstname,ab_lastname,ab_street,ab_zip, "
                 . "ab_city,p_projects.title FROM addressbook,p_delivery,p_projects WHERE "
                 . "p_delivery.id=$delivery_id AND p_delivery.customer=ab_id AND p_delivery.project_id=p_projects.id");
   if ($phpgw->db->next_record()) {
   $t->set_var("company",$phpgw->db->f("ab_company"));                                                                                                                                           
   $t->set_var("firstname",$phpgw->db->f("ab_firstname"));                                                                                                                                       
   $t->set_var("lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                         
   $t->set_var("street",$phpgw->db->f("ab_street"));                                                                                                                                             
   $t->set_var("zip",$phpgw->db->f("ab_zip"));                                                                                                                                                   
   $t->set_var("city",$phpgw->db->f("ab_city"));
   $t->set_var("delivery_day",date("j",$phpgw->db->f("date")));                                                                                                                                  
   $t->set_var("delivery_month",date("n",$phpgw->db->f("date")));                                                                                                                                
   $t->set_var("delivery_year",date("Y",$phpgw->db->f("date")));                                                                                                                                 
   $t->set_var("delivery_num",$phpgw->db->f("num"));                                                                                                                                             
   $t->set_var("title",$phpgw->db->f("title"));                                                                                                                                                 
     }
   }
                                                                                                                                                                                                 
   $pos = 0;

  $phpgw->db->query("SELECT ceiling(p_hours.minutes/p_hours.minperae) as aes,p_hours.remark, "                                                                                                        
                . "p_activities.descr,p_hours.date FROM p_hours,p_activities,p_deliverypos "                                                                                          
                . "WHERE p_deliverypos.hours_id=p_hours.id AND p_deliverypos.delivery_id=$delivery_id "                                                                               
                . "AND p_hours.activity_id=p_activities.id");
   while ($phpgw->db->next_record()) {
	$pos++;
	$t->set_var("pos",$pos);
	if ($phpgw->db->f("date") == 0) {
		$t->set_var("day","");
		$t->set_var("month","");
		$t->set_var("year","");
	}
	else {
		$t->set_var("day",date("j",$phpgw->db->f("date")));
		$t->set_var("month",date("n",$phpgw->db->f("date")));
		$t->set_var("year",date("Y",$phpgw->db->f("date")));
	}
	$t->set_var("aes",$phpgw->db->f("aes"));
	$t->set_var("act_descr",$phpgw->db->f("descr"));
	$t->set_var("billperae",$phpgw->db->f("billperae"));
	if($phpgw->db->f("remark")) {
		$t->set_var("act_remark",$phpgw->db->f("remark"));
	        } else {                    
                $t->set_var("act_remark","");
                }
                $t->parse("list", "deliverypos_list", true);
      }
   

   $t->parse("out", "delivery_list_t", true);
   $t->p("out");
  
       // -------------- end Add form declaration ------------------------

?>