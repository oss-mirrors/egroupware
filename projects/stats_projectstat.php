<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
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

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  
  if (! $id)
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/stats_index.php"
	  . "sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";


     $phpgw->db->query("select * from p_projects where id='$id'");
     $phpgw->db->next_record();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "project_stat" => "stats_projectstat.tpl"));
     $t->set_block("project_stat","stat_list","list");
     
     // ====================================================================
     // create two seperate blocks, addblock will be cut off from template
     // editblock contains the buttons and forms for edit
     // ====================================================================
     
     $t->set_var("actionurl",$phpgw->link("stats_projectstat.php"));
     $t->set_var("lang_action",lang("projectstatistic"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_num",lang("num"));
     $t->set_var("num", stripslashes($phpgw->db->f("num")));
     $t->set_var("lang_title",lang("title"));
     $t->set_var("title", stripslashes($phpgw->db->f("title")));

     $t->set_var("lang_status",lang("status"));
     $t->set_var("status",lang($phpgw->db->f("status")));
     $t->set_var("lang_budget",lang("budget"));
     $t->set_var("budget",stripslashes($phpgw->db->f("budget")));

     $t->set_var("lang_start_date",lang("start_date"));
     $start_date_formatorder ="<select name=month>\n"
               . "<option value=\"\"$n_month[0]> </option>\n"
               . "<option value=\"1\"$n_month[1]>" . lang("January") . "</option>\n" 
               . "<option value=\"2\"$n_month[2]>" . lang("February") . "</option>\n"
               . "<option value=\"3\"$n_month[3]>" . lang("March") . "</option>\n"
               . "<option value=\"4\"$n_month[4]>" . lang("April") . "</option>\n"
               . "<option value=\"5\"$n_month[5]>" . lang("May") . "</option>\n"
               . "<option value=\"6\"$n_month[6]>" . lang("June") . "</option>\n" 
               . "<option value=\"7\"$n_month[7]>" . lang("July") . "</option>\n"
               . "<option value=\"8\"$n_month[8]>" . lang("August") . "</option>\n"
               . "<option value=\"9\"$n_month[9]>" . lang("September") . "</option>\n"
               . "<option value=\"10\"$n_month[10]>" . lang("October") . "</option>\n"
               . "<option value=\"11\"$n_month[11]>" . lang("November") . "</option>\n"
               . "<option value=\"12\"$n_month[12]>" . lang("December") . "</option>\n"
               . "</select>";
     $start_date_formatorder  .= "<input maxlength=2 name=day value=\"$n_day\" size=2>\n";
     $start_date_formatorder .= "<input maxlength=4 name=year value=\"$n_year\" size=4>";
     $t->set_var("start_date_formatorder",$start_date_formatorder);

     $t->set_var("lang_end_date",lang("end_date"));
     $end_date_formatorder ="<select name=end_month>\n"
               . "<option value=\"\"$e_month[0]> </option>\n"
               . "<option value=\"1\"$e_month[1]>" . lang("January") . "</option>\n" 
               . "<option value=\"2\"$e_month[2]>" . lang("February") . "</option>\n"
               . "<option value=\"3\"$e_month[3]>" . lang("March") . "</option>\n"
               . "<option value=\"4\"$e_month[4]>" . lang("April") . "</option>\n"
               . "<option value=\"5\"$e_month[5]>" . lang("May") . "</option>\n"
               . "<option value=\"6\"$e_month[6]>" . lang("June") . "</option>\n" 
               . "<option value=\"7\"$e_month[7]>" . lang("July") . "</option>\n"
               . "<option value=\"8\"$e_month[8]>" . lang("August") . "</option>\n"
               . "<option value=\"9\"$e_month[9]>" . lang("September") . "</option>\n"
               . "<option value=\"10\"$e_month[10]>" . lang("October") . "</option>\n"
               . "<option value=\"11\"$e_month[11]>" . lang("November") . "</option>\n"
               . "<option value=\"12\"$e_month[12]>" . lang("December") . "</option>\n"
               . "</select>";
     $end_date_formatorder  .= "<input maxlength=2 name=end_day value=\"$e_day\" size=2>\n";
     $end_date_formatorder .= "<input maxlength=4 name=end_year value=\"$e_year\" size=4>";
     $t->set_var("end_date_formatorder",$end_date_formatorder);

     $t->set_var("lang_coordinator",lang("coordinator"));
     
     $db2 = $phpgw->db;
     
     $db2->query("SELECT account_id,account_firstname,account_lastname,account_lid FROM accounts where "
                     . "account_status != 'L' ORDER BY account_lid,account_lastname,account_firstname asc");
          while ($db2->next_record()) {
            if($db2->f("account_id")==$phpgw->db->f("coordinator")){    
            $coordinator = htmlentities($db2->f("account_lid") ." [ ".$db2->f("account_firstname") . " "                                                                                        
               . $db2->f("account_lastname"). " ]");

//            $coordinator  = $phpgw->common->display_fullname($db2->f("account_id"),
//                                $db2->f("account_firstname"),
//                                $db2->f("account_lastname"));
                      }
            }
     $t->set_var("coordinator",$coordinator);

// customer 
    
    $t->set_var("lang_customer",lang("customer"));
    
    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
    $db2->query("SELECT ab_id,ab_firstname,ab_lastname,ab_company_id,company_name from "
                     . "addressbook,customers where customers.company_id=addressbook.ab_company_id and "
                     . "addressbook.ab_company_id='" .$phpgw->db->f("customer")."'");
    if ($db2->next_record())
        $customerout = $db2->f("company_name")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]";
    }
    else {
    $db2->query("select ab_id,ab_firstname,ab_lastname,ab_company from addressbook "
                     . "where ab_id='" .$phpgw->db->f("customer")."'");
    if ($db2->next_record())
       $customerout = $db2->f("ab_company")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]";
       }    
    
    $t->set_var("customer",$customerout);
    
    if($billed)                     
       $t->set_var("billed","checked");                                 
       $t->set_var("billedonly",lang("billedonly"));
    
    $t->set_var("lang_calcb",lang("calc"));

// calculate statistics                                                                                                                                         
  
   $filter="";                                                                                                                                                   
   if($billed)                                                                                                                                                   
   $filter .= " AND p_hours.status='billed' ";                                                                                                                 
   if (checkdate($s_month,$s_day,$s_year)) {                                                                                                                     
     $s_date = mktime(2,0,0,$s_month,$s_day,$s_year);                                                                                                           
     $filter .= " AND date>='$s_date' ";                                                                                                                        
  }                                                                                                                                                             
  if (checkdate($end_month,$end_day,$end_year)) {                                                                                                               
     $end_date = mktime(2,0,0,$end_month,$end_day,$end_year);                                                                                                   
     $filter .= " AND date<='$end_date' ";                                                                                                                      
  }                                                                                                                                                             
  $phpgw->db->query("SELECT employee,account_firstname,account_lastname FROM p_hours"                                                                           
                .",accounts WHERE project_id=$id AND p_hours.employee=account_id $filter GROUP BY employee");                                                  
                                                                                                                                                                
  $t->set_var("hd_account",lang("account"));                                                                                                                    
  $t->set_var("hd_activity",lang("activity"));                                                                                                                 
  $t->set_var("hd_hours",lang("hours"));                                                                                                                        
  while ($phpgw->db->next_record()) {                                                                                                                           
    $summin = 0;                                                                                                                                                
    $t->set_var("e_account",$phpgw->db->f("account_firstname")." ".$phpgw->db->f("account_lastname"));                                                          
    $t->set_var("e_activity","");                                                                                                                               
    $t->set_var("e_hours","");                                                                                                                                  
    $t->parse("list","stat_list",true);                                                                                                                         
    $db2->query("SELECT SUM(minutes) as min,descr FROM p_hours,p_activities WHERE "                                                                   
                        ." project_id=$id AND employee='".$phpgw->db->f("employee")."' AND "                                                                    
                        ." p_hours.activity_id=p_activities.id $filter GROUP BY p_hours.activity_id");                                                          
    while ($db2->next_record()) {                                                                                                                     
      $t->set_var("e_account","");                                                                                                                              
      $t->set_var("e_activity",$db2->f("descr"));                                                                                                     
      $summin += $db2->f("min");                                                                                                                      
      $hrs = floor($db2->f("min")/60).":"                                                                                                             
                . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));                                                       
      $t->set_var("e_hours",$hrs);                                                                                                                              
      $t->parse("list","stat_list",true);                                                                                                                       
    }                                                                                                                                                           
    $t->set_var("e_account","");                                                                                                                                
    $t->set_var("e_activity","");                                                                                                                               
    $hrs = floor($summin/60).":"                                                                                                                                
                . sprintf ("%02d",(int)($summin-floor($summin/60)*60));                                                                                         
    $t->set_var("e_hours",$hrs);                                                                                                                                
    $t->parse("list","stat_list",true);                                                                                                                         
  }                                                                                                                                                             
  $db2->query("SELECT SUM(minutes) as min,descr FROM p_hours,p_activities WHERE "                                                                     
                        ." project_id=$id AND "                                                                                                                 
                        ." p_hours.activity_id=p_activities.id $filter GROUP BY p_hours.activity_id");    
   $summin=0;                                                                                                                                                    
   $t->set_var("e_account",lang("overall"));                                                                                                                     
   $t->set_var("e_activity","");                                                                                                                                 
   $t->set_var("e_hours","");                                                                                                                                    
   $t->parse("list","stat_list",true);                                                                                                                           
   while ($db2->next_record()) {                                                                                                                       
    $t->set_var("e_account","");                                                                                                                                
    $t->set_var("e_activity",$db2->f("descr"));                                                                                                       
    $summin += $db2->f("min");                                                                                                                        
    $hrs = floor($db2->f("min")/60).":"                                                                                                               
        . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));                                                               
    $t->set_var("e_hours",$hrs);                                                                                                                                
    $t->parse("list","stat_list",true);                                                                                                                         
  }                                                                                                                                                             
  $t->set_var("e_account",lang("sum"));                                                                                                                         
  $t->set_var("e_activity","");                                                                                                                                 
  $hrs = floor($summin/60).":"                                                                                                                                  
        . sprintf ("%02d",(int)($summin-floor($summin/60)*60));                                                                                                 
  $t->set_var("e_hours",$hrs);                                                                                                                                  
  $t->parse("list","stat_list",true);                                                                                                                           
                                                                                                                                                                
  $t->pparse("out","project_stat");                                                                                                                            
  
include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");                                                                                                                                                                
?>