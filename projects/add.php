<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille [aeb@hansenet.de]                               *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -------------------------------------------------------                  *
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

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "projects_add" => "form.tpl"));  	
  $t->set_block("projects_add", "add", "addhandle");                                                                                                                                 
  $t->set_block("projects_add", "edit", "edithandle");                                                                                                                               
  $t->set_block("projects_add", "edit_act", "acthandle");

  $db2 = $phpgw->db;

 if ($submit) {

    $phpgw->db->query("select count(*) from p_projects where num='$num'");                                                                                               
    $phpgw->db->next_record();                                                                                                                                                       
                                                                                                                                                                                      
    if ($phpgw->db->f(0) != 0) {                                                                                                                                                       
    unset($submit);
    $phpgw->common->phpgw_header();
    echo parse_navbar();
    echo "<br><br><center><b>" . lang("That Project ID has been used already !"). "</b></center>";
    $phpgw->common->phpgw_footer();
    $phpgw->common->phpgw_exit();
    }

    if (checkdate($month,$day,$year)) {                                                                                                                                              
    $date = mktime(2,0,0,$month,$day,$year);                                                                                                                                      
      } 
    else {                                                                                                                                                                         
    if ($month && $day && $year) {                                                                                                                                                
    $phpgw->common->phpgw_header();
    echo parse_navbar();
    echo "<br><br><center><b>" . lang("You have entered an invailed date"). "<br>" . "$month - $day - $year" . "</b></center>";                                                                                                                                          
    $phpgw->common->phpgw_footer();                                                                                                                                               
    $phpgw->common->phpgw_exit();
     }                                                                                                                                                                             
    }                                                                                                                                                                                
    if (checkdate($end_month,$end_day,$end_year)) {                                                                                                                                  
       $end_date = mktime(2,0,0,$end_month,$end_day,$end_year);                                                                                                                      
       } 
      else {                                                                                                                                                                         
       if ($end_month && $end_day && $end_year) {                                                                                                                                    
       $phpgw->common->phpgw_header();                                                                                                                                               
       echo parse_navbar();                                                                                                                                                          
       echo "<br><br><center><b>" . lang("You have entered an invailed date"). "<br>" . "$end_month - $end_day - $end_year" . "</b></center>";                                                   
       $phpgw->common->phpgw_footer();                                                                                                                                               
       $phpgw->common->phpgw_exit();
       }                                                                                                                                                                             
      }                                                                                                                                                                                

     if ($access != "public" && $access != "private" && $access != "") {                                                                                                             
     $access = $phpgw->accounts->array_to_string($access,$n_groups);                                                                                                                 
      }                                                                                                                                                                              
                                                                                                                                                                                     
    $owner = $phpgw_info["user"]["account_id"];                                                                                                                                   
                                                                                                                                                                                     
    if ($choose)                                                                                                                                                                      
      $num = create_projectid($year);                                                                                                                                                
    else                                                                                                                                                                              
      $num = addslashes($num);                                                                                                                                                       
                                                                                                                                                                                     
                                                                                                                                                                                     
   $phpgw->db->query("insert into p_projects (owner,access,entry_date,date,end_date,"                                                                                                
                   . "coordinator,customer,status,descr,title,budget,num) "                                                                                                          
                   . "values ('$owner','$access','" . time() ."','$date','$end_date',"                                                                                               
                   . "'$coordinator','$customer','$status','" . addslashes($descr) . "',"                                                                                            
                   . "'" . addslashes($title) . "','$budget','$num')");                                                                                                              
                                                                                                                                                                                     
        $db2->query("SELECT max(id) AS max FROM p_projects");                                                                                                                        
        if($db2->next_record()) {                                                                                                                                                    
        $p_id = $db2->f("max");                                                                                                                                                      
        if (count($ba_activities) != 0) {                                                                                                                                            
        while($activ=each($ba_activities)) {                                                                                                                                         
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"                                                                                              
                       . "billable) values ('$p_id','$activ[1]','N')");                                                                                                              
         }                                                                                                                                                                           
        }                                                                                                                                                                            
        if (count($bill_activities) != 0) {                                                                                                                                          
        while($activ=each($bill_activities)) {                                                                                                                                       
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"                                                                                              
                       . "billable) values ('$p_id','$activ[1]','Y')");                                                                                                              
           }                                                                                                                                                                          
          }                                                                                                                                                                           
         }
    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/",                                                                                         
           "cd=14&sort=$sort&order=$order&query=$query&start="                                                                                                                        
         . "$start&filter=$filter"));
        }
                                                                                                                                                                                      
   $t->set_var("actionurl",$phpgw->link("add.php"));
   $t->set_var("addressbook_link",$phpgw->link("addressbook.php","query="));
   $t->set_var("lang_action",lang("Add project"));

   if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {                                                                                                                       
   $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];                                                                                                                        
   $t->set_var("error","");                                                                                                                                                                     
   }                                                                                                                                                                                            
   else {                                                                                                                                                                                       
   $t->set_var("error",lang("Please select your currency in preferences!"));                                                                                                                    
   }
	
   $common_hidden_vars = "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
        	       . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        	       . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
        	       . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
        	       . "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
        	       . "<input type=\"hidden\" name=\"id\" value=\"$id\">";
        		
   $t->set_var("common_hidden_vars",$common_hidden_vars);
   $t->set_var("lang_num",lang("Project ID"));

/*        $db2->query("SELECT max(num) AS max FROM p_projects");
        if($db2->next_record()) {
           $t->set_var("num",(int)($db2->f("max"))+1);
        } else {
           $t->set_var("num","1");
        }   */     

     $t->set_var("num",$num);        
     $choose = "<input type=\"checkbox\" name=\"choose\" value=\"True\">";
     $t->set_var("lang_choose",lang("Auto generate Project ID ?"));                                                                                                                   
     $t->set_var("choose",$choose);


        $t->set_var("lang_title",lang("Title"));
        $t->set_var("title",$title);
        $t->set_var("lang_descr",lang("Description"));
	$t->set_var("descrval",$descr);

        $t->set_var("lang_status",lang("Status"));
	$status_list = "<option value=\"active\" selected>" . lang("Active") . "</option>\n"
           		. "<option value=\"nonactive\">" . lang("Nonactive") . "</option>\n"
           		. "<option value=\"archiv\">" . lang("Archiv") . "</option>\n";

	
        $t->set_var("status_list",$status_list);
        $t->set_var("lang_budget",lang("Budget"));
        $t->set_var("budget",$budget);

	$cur_month=date("n",time());
        $cur_day=date("j",time());
        $cur_year=date("Y",time());
        $t->set_var("lang_date",lang("Date"));
        $n_month[$cur_month]=" selected ";
        $date_formatorder ="<select name=month>\n"
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
  	$date_formatorder  .= "<input maxlength=2 name=\"day\" value=\"$cur_day\" size=2>\n";
  	$date_formatorder .= "<input maxlength=4 name=\"year\" value=\"$cur_year\" size=4>\n";
        $t->set_var("date_formatorder",$date_formatorder);

        $t->set_var("lang_end_date",lang("Date due"));
        $end_date_formatorder = "<select name=\"end_month\">\n"
              . "<option value=\"\" SELECTED> </option>\n"
              . "<option value=\"1\">" . lang("january") . "</option>\n"
              . "<option value=\"2\">" . lang("February"). "</option>\n"
              . "<option value=\"3\">" . lang("March")   . "</option>\n"
              . "<option value=\"4\">" . lang("April")   . "</option>\n"
              . "<option value=\"5\">" . lang("May")     . "</option>\n"
              . "<option value=\"6\">" . lang("June")    . "</option>\n"
              . "<option value=\"7\">" . lang("July")    . "</option>\n"
              . "<option value=\"8\">" . lang("August")  . "</option>\n"
              . "<option value=\"9\">" . lang("September") . "</option>\n"
              . "<option value=\"10\">" . lang("October")  . "</option>\n"
              . "<option value=\"11\">" . lang("November") . "</option>\n"
              . "<option value=\"12\">" . lang("December") . "</option>\n"
              . "</select>\n";
  	$end_date_formatorder  .= "<input maxlength=2 name=\"end_day\" size=2>\n";
  	$end_date_formatorder .= "<input maxlength=4 name=\"end_year\" size=4>\n";
        $t->set_var("end_date_formatorder",$end_date_formatorder);

        $t->set_var("lang_coordinator",lang("Coordinator"));
   
        $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM accounts where "
                        . "account_status != 'L' ORDER BY account_lastname,account_firstname,account_id asc");
        while ($phpgw->db->next_record()) {
           $coordinator_list .= "<option value=\"" . $phpgw->db->f("account_id") . "\"";

        if($phpgw->db->f("account_id")==$phpgw_info["user"]["account_id"])                                                                                                                             
            $coordinator_list .= " selected";                                                                                                                                               
        $coordinator_list .= ">"                                                                                                                                                            
                    . $phpgw->common->display_fullname($phpgw->db->f("account_id"),                                                                                                               
                      $phpgw->db->f("account_firstname"),                                                                                                                                         
                      $phpgw->db->f("account_lastname")) . "</option>";
                }
        $t->set_var("coordinator_list",$coordinator_list);

        $t->set_var("lang_select",lang("Select per button !"));
        $t->set_var("lang_customer",lang("Customer"));
        $t->set_var("customer_con","");
        $t->set_var("customer_name","");

// activities bookable     
       $t->set_var("lang_bookable_activities",lang("Bookable activities"));       
        $db2->query("SELECT p_activities.id as id,p_activities.descr "                                                                                                                      
                     . "FROM p_activities "                                                                                                               
                     . "ORDER BY descr asc");                                                                                                    
        while ($db2->next_record()) {                                                                                                                                                       
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";                                                                                                                
        $ba_activities_list .= ">"                                                                                                                                                       
                    . $db2->f("descr")                                                                                                                                                   
                    . "</option>";                                                                                                                                                       
        }
        
       $t->set_var("ba_activities_list",$ba_activities_list);  

// activities billable        
        $t->set_var("lang_billable_activities",lang("Billable activities"));
     $db2->query("SELECT p_activities.id as id,p_activities.descr,p_activities.billperae "                                                                                                                      
                     . " FROM p_activities "                                                                                                                   
                     . " ORDER BY descr asc");                                                                                                  
     while ($db2->next_record()) {                                                                                                                                                       
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";                                                                                                              
        $bill_activities_list .= ">"                                                                                                                                                     
                    . $db2->f("descr") . " " . $currency . " "                                                                                                                                      
                    . $db2->f("billperae") . " " . lang("per workunit") . "</option>";                                                                                                                                
     }        

       $t->set_var("bill_activities_list",$bill_activities_list);
      
        $t->set_var("lang_access_type",lang("Access type"));
        $t->set_var("access_list",lang("Access type"));
	
        $access_list = "<option value=\"private\">" . lang("Private") . "</option>\n"
        	      ."<option value=\"public\" selected>" .lang("Global public"). "</option>\n"
        	      ."<option value=\"group\">" .lang("Group public"). "</option>\n"; 

	$t->set_var("access_list",$access_list);
	
	$t->set_var("lang_which_groups",lang("Which groups"));

        $user_groups = $phpgw->accounts->read_group_names($phpgw_info["user"]["userid"]);
        for ($i=0;$i<count($user_groups);$i++) {
            $group_list .= "<option value=\"" . $user_groups[$i][0] . "\">" . $user_groups[$i][1]
            			. "</option>\n";
        }
        
        $t->set_var("group_list",$group_list);
        $t->set_var("lang_addsubmitb",lang("Add"));
        $t->set_var("lang_addresetb",lang("Clear Form"));
        
        $t->set_var("edithandle","");
    	$t->set_var("addhandle","");
    	$t->set_var("acthandle","");
    	$t->pparse("out","projects_add");
    	$t->pparse("addhandle","add");
        
    $phpgw->common->phpgw_footer();

?>