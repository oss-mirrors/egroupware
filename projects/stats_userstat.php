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
  
  
  $db2 = $phpgw->db;
  
  
  if (! $account_id)
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

     $phpgw->db->query("select * from phpgw_accounts where account_id = '$account_id'");
     $phpgw->db->next_record();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "projects_stat" => "stats_userstat.tpl"));
     $t->set_block("projects_stat","stat_list","list");

     // ====================================================================
     // create two seperate blocks, addblock will be cut off from template
     // editblock contains the buttons and forms for edit
     // ====================================================================
     
     $t->set_var("actionurl",$phpgw->link("stats_userstat.php","account_id=" . $phpgw->db->f("account_id")));
     $t->set_var("lang_action",lang("User statistic"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_lid",lang("Username"));
     $t->set_var("lid",$phpgw->strip_html($phpgw->db->f("account_lid")));
     $t->set_var("lang_firstname",lang("Firstname"));
     $t->set_var("firstname",$phpgw->strip_html($phpgw->db->f("account_firstname")));
     $t->set_var("lang_lastname",lang("Lastname"));                                                                                                                
     $t->set_var("lastname",$phpgw->strip_html($phpgw->db->f("account_lastname")));

     $t->set_var("lang_start_date",lang("Start date"));
     $n_month[$s_month]=" selected ";
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

     $t->set_var("lang_end_date",lang("End date"));
     $n_month[$s_month]=" selected ";
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

     if($billed)                                                                                                                                                   
       $t->set_var("billed","checked");                                                                                                                            
       $t->set_var("billedonly",lang("Billed only"));                                                                                                                 
                                                  
                                                                                                                     
    // calculate statistics                                                                                                                                   
      
      $filter="";                                                                                                                                             
      if($billed)                                                                                                                                             
      $filter .= " AND p_hours.status='billed' ";                                                                                                           
      if (checkdate($s_month,$s_day,$s_year)) {                                                                                                               
      $s_date = mktime(2,0,0,$s_month,$s_day,$s_year);                                                                                                     
      $filter .= " AND p_hours.date>='$s_date' ";                                                                                                          
      }                                                                                                                                                       
      if (checkdate($end_month,$end_day,$end_year)) {                                                                                                         
      $end_date = mktime(2,0,0,$end_month,$end_day,$end_year);                                                                                             
      $filter .= " AND p_hours.date<='$end_date' ";                                                                                                        
      }                                                                                                                                                       
     $phpgw->db->query("SELECT title,p_projects.id FROM p_hours,p_projects WHERE project_id=p_projects.id "                                                  
                ."AND p_hours.employee='$account_id' $filter GROUP BY project_id");                                                                      
                                                                                                                                                          
     $t->set_var("hd_project",lang("Project"));                                                                                                              
     $t->set_var("hd_activity",lang("Activity"));                                                                                                           
     $t->set_var("hd_hours",lang("Hours"));                                                                                                                  
     while ($phpgw->db->next_record()) {                                                                                                                     
     $summin = 0;                                                                                                                                          
     $t->set_var("e_project",$phpgw->db->f("title"));                                                                                                      
     $t->set_var("e_activity","");                                                                                                                         
     $t->set_var("e_hours","");                                                                                                                            
     $t->parse("list","stat_list",true);                                                                                                                   
     
     $db2->query("SELECT SUM(minutes) as min,descr FROM p_hours,p_activities WHERE "                                                             
                        ." employee='$account_id' AND project_id='".$phpgw->db->f("id")."' AND "                                                         
                        ." p_hours.activity_id=p_activities.id $filter GROUP BY p_hours.activity_id");                                                    
     while ($db2->next_record()) {                                                                                                               
      $t->set_var("e_project","");                                                                                                                        
      $t->set_var("e_activity",$db2->f("descr"));                                                                                               
      $summin += $db2->f("min");                                                                                                                
      $hrs = floor($db2->f("min")/60).":"                                                                                                       
                . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));                                                 
      $t->set_var("e_hours",$hrs);                                                                                                                        
      $t->parse("list","stat_list",true);                                                                                                                 
    }                                                                                                                                                     
    $t->set_var("e_project","");                                                                                                                          
    $t->set_var("e_activity","");                                                                                                                         
    $hrs = floor($summin/60).":"                                                                                                                          
                . sprintf ("%02d",(int)($summin-floor($summin/60)*60));                                                                                   
    $t->set_var("e_hours",$hrs);                                                                                                                          
    $t->parse("list","stat_list",true);                                                                                                                   
    }                                                                                                                                                       
    $db2->query("SELECT SUM(minutes) as min,descr FROM p_hours,p_activities WHERE "                                                               
                        ." employee='$account_id' AND "                                                                                                  
                        ." p_hours.activity_id=p_activities.id $filter GROUP BY p_hours.activity_id");$t->set_var("lang_calcb",lang("Calculate"));


     $summin=0;                                                                                                                                              
     $t->set_var("e_project",lang("Overall"));                                                                                                               
     $t->set_var("e_activity","");                                                                                                                           
     $t->set_var("e_hours","");                                                                                                                              
     $t->parse("list","stat_list",true);                                                                                                                     
     while ($db2->next_record()) {                                                                                                                 
     $t->set_var("e_project","");                                                                                                                          
     $t->set_var("e_activity",$db2->f("descr"));                                                                                                 
     $summin += $db2->f("min");                                                                                                                  
     $hrs = floor($db2->f("min")/60).":"                                                                                                         
        . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));                                                         
    $t->set_var("e_hours",$hrs);                                                                                                                          
    $t->parse("list","stat_list",true);                                                                                                                   
    }                                                                                                                                                       
    $t->set_var("e_project",lang("sum"));                                                                                                                   
    $t->set_var("e_activity","");                                                                                                                           
    $hrs = floor($summin/60).":"                                                                                                                            
        . sprintf ("%02d",(int)($summin-floor($summin/60)*60));                                                                                           
    $t->set_var("e_hours",$hrs);                                                                                                                            
    $t->parse("list","stat_list",true);                                                                                                                     
                                                                                                                                                          
    $t->pparse("out","projects_stat");                                                                                                                      
  
$phpgw->common->phpgw_footer();
?>
