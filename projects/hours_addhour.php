<?php
  /**************************************************************************\
  * phpGroupWare - projects/projecthours                                     *
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
  
  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }
  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");

  if (! $submit) {
       $isadmin = isprojectadmin();
      
        $t = new Template($phpgw_info["server"]["app_tpl"]);
  	$t->set_var("actionurl",$phpgw->link("hours_addhour.php"));
  	$t->set_file(array( "projects_add" => "hours_formhours.tpl"));
  	
  	// ====================================================================
     	// create two seperate blocks, editblock will be cut off from template
     	// addblock contains the buttons needed
     	// ====================================================================
     	$t->set_block("projects_add", "add", "addhandle");
     	$t->set_block("projects_add", "edit", "edithandle");
     	$t->set_block("projects_add", "edit_act", "acthandle");
  	
  	$t->set_var("lang_action",lang("add project hours"));
	
	$common_hidden_vars = "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
        		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
        		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
        		. "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
        		. "<input type=\"hidden\" name=\"id\" value=\"$id\">";
        		
        $t->set_var("common_hidden_vars",$common_hidden_vars);
        $phpgw->db->query("SELECT num,title FROM p_projects "
                        . " WHERE id = '".$id."'");
        if ($phpgw->db->next_record()) {
           $t->set_var("num",$phpgw->db->f("num"));
	   $t->set_var("title",$phpgw->db->f("title"));
        }
        $t->set_var("lang_num",lang("Project ID"));
        $t->set_var("lang_title",lang("Title"));

        $t->set_var("lang_activity",lang("Activity"));
        $phpgw->db->query("SELECT activity_id,descr FROM p_projectactivities,p_activities"
                        . " WHERE project_id = '".$id."' AND p_projectactivities.activity_id="
                        . "p_activities.id");
        while ($phpgw->db->next_record()) {
           $activity_list .= "<option value=\"" . $phpgw->db->f("activity_id") . "\">"
	            . $phpgw->db->f("descr") . "</option>";
        }
        $t->set_var("activity_list",$activity_list);

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
  	$date_formatorder .= "<input maxlength=4 name=\"year\" value=\"$cur_year\" size=4> (e.g. 2000)\n";
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
  	$end_date_formatorder .= "<input maxlength=4 name=\"end_year\" size=4> (e.g. 2000)\n";
        $t->set_var("end_date_formatorder",$end_date_formatorder);

        $t->set_var("lang_remark",lang("Remark"));
        $t->set_var("remark","");

        $t->set_var("lang_time",lang("Time"));
        $t->set_var("hours","");
        $t->set_var("minutes","");

        $t->set_var("lang_status",lang("Status"));
	$status_list = "<option value=\"done\" selected>" . lang("Done") . "</option>\n"
           		. "<option value=\"open\">" . lang("Open") . "</option>\n"
           		. "<option value=\"billed\">" . lang("Billed") . "</option>\n";	
        $t->set_var("status_list",$status_list);

        $t->set_var("lang_employee",lang("Employee"));
        $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM accounts where "
                        . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
        while ($phpgw->db->next_record()) {
           $employee_list .= "<option value=\"" . $phpgw->db->f("account_id") . "\""
                    . $selected_users[$phpgw->db->f("account_id")] . ">"
	            . $phpgw->common->display_fullname($phpgw->db->f("account_id"),
                      $phpgw->db->f("account_firstname"),
                      $phpgw->db->f("account_lastname")) . "</option>";
        }
        $t->set_var("employee_list",$employee_list);

        $t->set_var("lang_addsubmitb",lang("Add"));
        $t->set_var("lang_addresetb",lang("Clear Form"));
        
        $t->set_var("edithandle","");
    	$t->set_var("addhandle","");
    	$t->set_var("acthandle","");
    	$t->pparse("out","projects_add");
    	$t->pparse("addhandle","add");
        

  } else {

    if (checkdate($month,$day,$year)) {
       $date = mktime(2,0,0,$month,$day,$year);
    } else {
       if ($month && $day && $year) {
          $phpgw->common->phpgw_header();
          $phpgw->common->navbar();
          echo "<p><center>" . lang("You have entered an invalid date"). "</center>";
          echo "<br>$month - $day - $year";
          exit;
       }
    }
    if (checkdate($end_month,$end_day,$end_year)) {
       $end_date = mktime(2,0,0,$end_month,$end_day,$end_year);
    } else {
       if ($end_month && $end_day && $end_year) {
          $phpgw->common->phpgw_header();
          $phpgw->common->navbar();
          echo "<p><center>" . lang("You have entered an invalid date"). "</center>";
          echo "<br>$end_month - $end_day - $end_year";
          exit;
       }
    }
    $ae_minutes = $hours*60+$minutes;
    $phpgw->db->query("SELECT minperae,billperae,remarkreq FROM p_activities"
                        . " WHERE id = '".$activity."'");
    if (!$phpgw->db->next_record()) {
        $phpgw->common->phpgw_header();
        $phpgw->common->navbar();
        echo "<p><center>" . lang("You have selected an invalid activity"). "</center>";
        exit;
    }
    if (($phpgw->db->f("remarkreq")=="Y") and (!$remark)){
        $phpgw->common->phpgw_header();
        $phpgw->common->navbar();
        echo "<p><center>" . lang("You have to enter a remark"). "</center>";
        exit;
    }
    $billperae = $phpgw->db->f("billperae");
    $minperae = $phpgw->db->f("minperae");
//    $ae_minutes = ceil($ae_minutes / $phpgw->db->f("minperae"));

    $phpgw->db->query("insert into p_hours (project_id,activity_id,entry_date,date,end_date,"
               . "remark,minutes,status,minperae,billperae,employee) values "
               . " ('$id','$activity','" . time() ."','$date','$end_date','".addslashes($remark)."',"
               . "'$ae_minutes','$status','$minperae','$billperae','$employee')");
    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/hours_index.php",
           "cd=14&sort=$sort&order=$order&query=$query&start="
         . "$start&filter=$filter"));
  }
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>