<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectbilling                                   *
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
  $t->set_file(array( "invoice_list_t" => "bill_invoiceform.tpl",
                      "invoicepos_list" => "bill_invoiceform.tpl"));
  $t->set_block("invoice_list_t", "invoicepos_list", "list");

//  $taxpercent = 0.16;
//  $eurtodm = 1.95583;

  if (isset($phpgw_info["user"]["preferences"]["projects"]["tax"]) && (isset($phpgw_info["user"]["preferences"]["common"]["currency"]) && (isset($phpgw_info["user"]["preferences"]["projects"]["address"])))) {                                                                                
    $tax = $phpgw_info["user"]["preferences"]["projects"]["tax"];                                                                                 
    $tax = ((float)$tax);
    $taxpercent = ($tax/100);
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    
     if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                                                         
     $phpgw->db->query("SELECT ab_firstname,ab_lastname,ab_street,ab_zip,ab_city,ab_state,ab_company_id,company_name FROM "                                                                     
                     . "addressbook,customers where "                                                                                                                                          
                     . "ab_company_id='" .$phpgw_info["user"]["preferences"]["projects"]["address"]."' and customers.company_id= "
                     . "addressbook.ab_company_id");                                                                                      
      if ($phpgw->db->next_record()) {                                                                                                                                                         
      $t->set_var("ad_company",$phpgw->db->f("company_name"));                                                                                                                                 
      $t->set_var("ad_firstname",$phpgw->db->f("ab_firstname"));                                                                                                                               
      $t->set_var("ad_lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                 
      $t->set_var("ad_street",$phpgw->db->f("ab_street"));                                                                                                                                     
      $t->set_var("ad_zip",$phpgw->db->f("ab_zip"));                                                                                                                                           
      $t->set_var("ad_city",$phpgw->db->f("ab_city"));                                                                                                                                         
      $t->set_var("ad_state",$phpgw->db->f("ab_state"));                                                                                                                                       
          }                                                                                                                                                                                    
      else {                                                                                                                                                                                   
      $t->set_var("ad_company","");                                                                                                                                                            
      $t->set_var("ad_firstname","");                                                                                                                                                          
      $t->set_var("ad_lastname","");                                                                                                                                                           
      $t->set_var("ad_street","");                                                                                                                                                             
      $t->set_var("ad_zip","");                                                                                                                                                                
      $t->set_var("ad_city","");                                                                                                                                                               
      $t->set_var("ad_state","");                                                                                                                                                              
          }                                                                                                                                                                                    
        }                                                                                                                                                                                      
      else {                                                                                                                                                                                   
    $phpgw->db->query("select ab_id,ab_lastname,ab_firstname,ab_street,ab_zip,ab_city,ab_state,ab_company from addressbook where "                                                             
                        . "ab_id='" .$phpgw_info["user"]["preferences"]["projects"]["address"]."'");                                                                                           
      if ($phpgw->db->next_record()) {                                                                                                                                                         
      $t->set_var("ad_company",$phpgw->db->f("ab_company"));                                                                                                                                   
      $t->set_var("ad_firstname",$phpgw->db->f("ab_firstname"));                                                                                                                               
      $t->set_var("ad_lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                 
      $t->set_var("ad_street",$phpgw->db->f("ab_street"));                                                                                                                                     
      $t->set_var("ad_zip",$phpgw->db->f("ab_zip"));                                                                                                                                           
      $t->set_var("ad_city",$phpgw->db->f("ab_city"));                                                                                                                                         
      $t->set_var("ad_state",$phpgw->db->f("ab_state"));                                                                                                                                       
            }                                                                                                                                                                                  
                                                                                                                                                                                               
   else {                                                                                                                                                                                      
      $t->set_var("ad_company","");                                                                                                                                                            
      $t->set_var("ad_firstname","");                                                                                                                                                          
      $t->set_var("ad_lastname","");                                                                                                                                                           
      $t->set_var("ad_street","");                                                                                                                                                             
      $t->set_var("ad_zip","");                                                                                                                                                                
      $t->set_var("ad_city","");                                                                                                                                                               
      $t->set_var("ad_state","");                                                                                                                                                              
                                                                                                                                                                                               
      }                                                                                                                                                                                        
     }     
    $t->set_var("error","");     
     }
   else {                                                                                                                                                       
    $t->set_var("error",lang("Please select currency,tax and your address in preferences!"));
    $taxpercent = ((int)0); 
    }

   $charset = $phpgw->translation->translate("charset");                                                                                                                                         
   $t->set_var("charset",$charset);   
   $t->set_var("site_title",$phpgw_info["site_title"]);
   $t->set_var(lang_invoice,lang("Invoice ID"));                                                                                                                                                   
   $t->set_var(lang_project,lang("Project"));                                                                                                                                                   
   $t->set_var(lang_pos,lang("Position"));                                                                                                                                                           
   $t->set_var(lang_workunits,lang("Workunits"));                                                                                                                                               
   $t->set_var(lang_date,lang("Date"));                                                                                                                                                         
   $t->set_var(lang_descr,lang("Description"));                                                                                                                                                 
   $t->set_var("currency",$currency);                                                                                                                                                         
   $t->set_var(lang_mwst,lang("tax"));
   $t->set_var(lang_netto,lang("net"));                                                                                                                                                         
   
  
   if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
   $phpgw->db->query("SELECT p_projects.title,p_invoice.customer,p_invoice.num,p_invoice.project_id,p_invoice.date,p_invoice.sum as sum_netto, "
                 . "round(sum*$taxpercent,2) as sum_tax,round(sum*(1+$taxpercent),2) as sum_sum,"                                                                                                    
                 . "ab_company_id,company_name,ab_firstname,ab_lastname,ab_street,ab_zip,ab_state, "
                 . "ab_city FROM addressbook,customers,p_invoice,p_projects WHERE "
                 . "p_invoice.id=$invoice_id AND p_invoice.project_id=p_projects.id AND "
                 . "p_invoice.customer=ab_company_id AND customers.company_id=addressbook.ab_company_id");
   if ($phpgw->db->next_record()) {  
   $t->set_var("company",$phpgw->db->f("company_name"));
   $t->set_var("firstname",$phpgw->db->f("ab_firstname"));                                                                                                                                       
   $t->set_var("lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                         
   $t->set_var("street",$phpgw->db->f("ab_street"));                                                                                                                                             
   $t->set_var("zip",$phpgw->db->f("ab_zip"));                                                                                                                                                   
   $t->set_var("city",$phpgw->db->f("ab_city"));
   $t->set_var("state",$phpgw->db->f("ab_state"));
   $t->set_var("invoice_day",date("j",$phpgw->db->f("date")));                                                                                                                                       
   $t->set_var("invoice_month",date("n",$phpgw->db->f("date")));                                                                                                                                     
   $t->set_var("invoice_year",date("Y",$phpgw->db->f("date")));                                                                                                                                      
   $t->set_var("invoice_num",$phpgw->strip_html($phpgw->db->f("num")));   
   $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                               
    if (! $title)  $title  = "&nbsp;";
   $t->set_var("title",$title);                                                                                                                                                      
   $t->set_var("sum_netto",$phpgw->db->f("sum_netto"));                                                                                                                                              
   $t->set_var("tax_percent",$taxpercent);                                                                                                                                                           
   $t->set_var("sum_tax",$phpgw->db->f("sum_tax"));                                                                                                                                                  
   $t->set_var("sum_sum",$phpgw->db->f("sum_sum"));                                                                                                                                                  
      }
    }
   else {    
   $phpgw->db->query("SELECT p_invoice.customer,p_invoice.num,p_invoice.project_id,p_invoice.date,p_invoice.sum as sum_netto, "
                 . "round(sum*$taxpercent,2) as sum_tax,round(sum*(1+$taxpercent),2) as sum_sum,"
                 . "ab_id,ab_company,ab_firstname,ab_lastname,ab_street,ab_zip,ab_state, "
                 . "ab_city,p_projects.title FROM addressbook,p_invoice,p_projects WHERE "
                 . "p_invoice.id=$invoice_id AND p_invoice.customer=ab_id AND p_invoice.project_id=p_projects.id");
   if ($phpgw->db->next_record()) {
   $t->set_var("company",$phpgw->db->f("ab_company"));                                                                                                                                           
   $t->set_var("firstname",$phpgw->db->f("ab_firstname"));                                                                                                                                       
   $t->set_var("lastname",$phpgw->db->f("ab_lastname"));                                                                                                                                         
   $t->set_var("street",$phpgw->db->f("ab_street"));                                                                                                                                             
   $t->set_var("zip",$phpgw->db->f("ab_zip"));                                                                                                                                                   
   $t->set_var("city",$phpgw->db->f("ab_city"));
   $t->set_var("state",$phpgw->db->f("ab_state"));
   $t->set_var("invoice_day",date("j",$phpgw->db->f("date")));                                                                                                                                  
   $t->set_var("invoice_month",date("n",$phpgw->db->f("date")));                                                                                                                                
   $t->set_var("invoice_year",date("Y",$phpgw->db->f("date"))); 
   $t->set_var("invoice_num",$phpgw->strip_html($phpgw->db->f("num")));                                                                                                                    
   $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                                    
    if (! $title)  $title  = "&nbsp;";                                                                                                                                                     
   $t->set_var("title",$title); 
   $t->set_var("sum_netto",$phpgw->db->f("sum_netto"));    
   $t->set_var("tax_percent",$taxpercent);                                                                                                                                                      
   $t->set_var("sum_tax",$phpgw->db->f("sum_tax"));                                                                                                                                             
   $t->set_var("sum_sum",$phpgw->db->f("sum_sum"));                                                                                                                                             
     }
   }
   $sum_netto = $phpgw->db->f("sum_netto");
                                                                                                                                                                                                 
   $pos = 0;
   $sum = 0;
   $phpgw->db->query("SELECT ceiling(p_hours.minutes/p_hours.minperae) as aes,"
		. "p_hours.remark,p_hours.billperae,p_hours.billperae*"
		. "(ceiling(p_hours.minutes/p_hours.minperae)) as sumpos,"
		. "p_activities.descr,p_hours.date FROM p_hours,p_activities,p_invoicepos "
		. "WHERE p_invoicepos.hours_id=p_hours.id AND p_invoicepos.invoice_id=$invoice_id "
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
        $act_descr = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                               
        if (! $act_descr)  $act_descr  = "&nbsp;";
	$t->set_var("act_descr",$act_descr);
	$t->set_var("billperae",$phpgw->db->f("billperae"));
	$t->set_var("sumperpos",$phpgw->db->f("sumpos"));
	$sum += $phpgw->db->f("sumpos");
	$remark = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                               
        if (! $remark)  $remark  = "&nbsp;";
        $t->set_var("remark",$remark);
        $t->parse("list", "invoicepos_list", true);
      }
   
   if($sum==$sum_netto)
   $t->set_var("error_hint","");
   else
   $t->set_var("error_hint",lang("error in calculation sum doesn't match")); 

   $t->parse("out", "invoice_list_t", true);
   $t->p("out");
  
       // -------------- end Add form declaration ------------------------

?>