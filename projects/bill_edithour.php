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
  
  if (($submit) or ($template)) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  
  
  $db2 = $phpgw->db;
  
  
  if (!$id)
     Header("Location: " . $phpgw->link('/projects/bill_index.php',"sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));


  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

  if (!$submit) {

     $isadmin = isprojectadmin();     
  
     $phpgw->db->query("select * from p_hours where id='$id'");
     $phpgw->db->next_record();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "projects_edit" => "hours_formhours.tpl"));
     $t->set_block("projects_edit", "add", "addhandle");
     $t->set_block("projects_edit", "edit", "edithandle");
     
     $t->set_var("actionurl",$phpgw->link("/projects/bill_edithour.php"));
     $t->set_var("lang_action",lang("Edit project hours"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     
     $db2->query("SELECT num,title FROM p_projects WHERE id = '".$phpgw->db->f("project_id")."'");
     if ($db2->next_record()) {
     $t->set_var("num",$phpgw->strip_html($db2->f("num")));
     $title = $phpgw->strip_html($db2->f("title"));                                                                                                                                         
     if (! $title)  $title  = "&nbsp;";
     $t->set_var("title",$title);
     }
     $t->set_var("lang_num",lang("Project ID"));
     $t->set_var("lang_title",lang("title"));

     $t->set_var("lang_activity",lang("Activity"));
     $db2->query("SELECT activity_id,descr FROM p_projectactivities,p_activities"
                     . " WHERE project_id = '".$phpgw->db->f("project_id")."' AND p_projectactivities.activity_id="
                     . "p_activities.id");
     while ($db2->next_record()) {
        $activity_list .= "<option value=\"" . $phpgw->db->f("activity_id") . "\"";
        if($db2->f("activitiy_id")==$phpgw->db->f("activity_id"))
            $activity_list .= " selected";
        $activity_list .= ">"
          . $phpgw->strip_html($db2->f("descr")) . "</option>";
     }
     $t->set_var("activity_list",$activity_list);


     $t->set_var("lang_status",lang("Status"));
     if ($phpgw->db->f("status")=="open"): 
         $stat_sel[0]=" selected";
     elseif ($phpgw->db->f("status")=="done"):
         $stat_sel[1]=" selected";
     elseif ($phpgw->db->f("status")=="billed"):
         $stat_sel[2]=" selected";
     endif;

     $status_list = "<option value=\"open\"".$stat_sel[0].">" . lang("Open") . "</option>\n"
                  . "<option value=\"done\"".$stat_sel[1].">" . lang("Done") . "</option>\n"
                  . "<option value=\"billed\"".$stat_sel[2].">" . lang("Billed") . "</option>\n";
     $t->set_var("status_list",$status_list);

     $t->set_var("lang_date",lang("Date"));
     if ($phpgw->db->f("date") != 0) {
        $n_month[$phpgw->common->show_date($phpgw->db->f("date"),"n")] = " selected";
	$n_day			 = $phpgw->common->show_date($phpgw->db->f("date"),"d");
	$n_year			 = $phpgw->common->show_date($phpgw->db->f("date"),"Y");
     } else {
        $n_month[0]		 = " selected";
	$n_day			 = "";
	$n_year			 = "";
     }
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
     $date_formatorder  .= "<input maxlength=2 name=day value=\"$n_day\" size=2>\n";
     $date_formatorder .= "<input maxlength=4 name=year value=\"$n_year\" size=4>";
     $t->set_var("date_formatorder",$date_formatorder);

     $t->set_var("lang_end_date",lang("Date due"));
     if ($phpgw->db->f("end_date") != 0) {
        $e_month[$phpgw->common->show_date($phpgw->db->f("end_date"),"n")] = " selected";
	$e_day			 = $phpgw->common->show_date($phpgw->db->f("end_date"),"d");
	$e_year			 = $phpgw->common->show_date($phpgw->db->f("end_date"),"Y");
     } else {
        $e_month[0]		 = " selected";
	$e_day			 = "";
	$e_year			 = "";
     }
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

     $t->set_var("lang_remark",lang("Remark"));
     
     $remark  = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                                    
     if (! $remark)  $remark  = "&nbsp;"; 
     $t->set_var("remark",$remark);     
     
     $t->set_var("lang_time",lang("Time"));
     $t->set_var("hours",floor($phpgw->db->f("minutes")/60));
     $t->set_var("minutes",($phpgw->db->f("minutes"))-((floor($phpgw->db->f("minutes")/60)*60)));

     $t->set_var("lang_employee",lang("Employee"));
     $db2->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts where "
                     . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
     while ($db2->next_record()) {
        $employee_list .= "<option value=\"" . $db2->f("account_id") . "\"";
        if($db2->f("account_id")==$phpgw->db->f("employee"))
            $employee_list .= " selected";
        $employee_list .= ">"        
                    . $phpgw->common->display_fullname($db2->f("account_id"),
                      $db2->f("account_firstname"),
                      $db2->f("account_lastname")) . "</option>";
     }
     $t->set_var("employee_list",$employee_list);  

     $t->set_var("lang_minperae",lang("Minutes per workunit"));
     $t->set_var("minperae",$phpgw->db->f("minperae"));
     $t->set_var("lang_billperae",lang("Bill per workunit"));
     $t->set_var("billperae",$phpgw->db->f("billperae"));


    $t->set_var("lang_editsubmitb",lang("Edit"));
    $t->set_var("lang_atvititiesb",lang("Activities"));
    
    $t->set_var("edithandle","");
    $t->set_var("addhandle","");
    $t->pparse("out","projects_edit");
    $t->pparse("edithandle","edit");

  } else {
    if (checkdate($month,$day,$year)) {
       $date = mktime(2,0,0,$month,$day,$year);
    } else {
       if ($month && $day && $year) {
          navigation_bar();
          echo "<p><center>" . lang("You have entered an invailed date"). "</center>";
          echo "<br>$month - $day - $year";
          exit;
       }
    }
    if (checkdate($end_month,$end_day,$end_year)) {
       $end_date = mktime(2,0,0,$end_month,$end_day,$end_year);
    } else {
       if ($end_month && $end_day && $end_year) {
          navigation_bar();
          echo "<p><center>" . lang("You have entered an invailed date"). "</center>";
          echo "<br>$end_month - $end_day - $end_year";
          exit;
       }
    }
    $ae_minutes=$hours*60+$minutes;
    $remark = addslashes($remark);
    $phpgw->db->query("update p_hours set activity_id='$activity',entry_date='" . time()
		. "',date='$date',end_date='$end_date',remark='$remark',"
		. "minutes='$ae_minutes',status='$status',minperae='$minperae',"
		. "billperae='$billperae',employee='$employee' where id='$id'");

    Header("Location: " . $phpgw->link('/projects/bill_index.php'));
//	   "cd=15&sort=$sort&order=$order&query=$query&start="
//	 . "$start&filter=$filter"));
  }
$phpgw->common->phpgw_footer();
?>
