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
  
  if (($submit) or ($template)) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  
  $db2 = $phpgw->db;
  
  if (! $id)
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/stat_index.php"
	  . "sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

  if (!(($submit) or ($template))) {

     $phpgw->db->query("select * from p_projects where (coordinator='" . $phpgw_info["user"]["account_id"]
		 . "' or owner='".$phpgw_info["user"]["account_id"]."') and id='$id'");
     $phpgw->db->next_record();

     $t = new Template($phpgw_info["server"]["app_tpl"]);
     $t->set_file(array( "project_stat" => "stat_projectstat.tpl"));
     
     // ====================================================================
     // create two seperate blocks, addblock will be cut off from template
     // editblock contains the buttons and forms for edit
     // ====================================================================
     
     $t->set_var("addressbook_link",$phpgw->link("addressbook.php","query="));
     $t->set_var("actionurl",$phpgw->link("stats_projectstat.php"));
     $t->set_var("lang_action",lang("Project statistic"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_num",lang("Project ID"));
     $t->set_var("num", stripslashes($phpgw->db->f("num")));
     $t->set_var("lang_title",lang("Title"));
     $t->set_var("title", stripslashes($phpgw->db->f("title")));

     $t->set_var("lang_status",lang("Status"));
     $t->set_var("status",lang($phpgw->db->f("status")));
     $t->set_var("lang_budget",lang("Budget"));
     $t->set_var("budget",stripslashes($phpgw->db->f("budget")));

     $t->set_var("lang_start_date",lang("Start date"));
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

     $t->set_var("lang_coordinator",lang("Coordinator"));
     $db2->query("SELECT account_id,account_firstname,account_lastname FROM accounts where "
                     . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
     while ($db2->next_record()) {
        if($db2->f("account_id")==$phpgw->db->f("coordinator"))
            $coordinator  = $phpgw->common->display_fullname($db2->f("account_id"),
                                $db2->f("account_firstname"),
                                $db2->f("account_lastname"));
     }
     $t->set_var("coordinator",$coordinator);

// customer 
    
    $t->set_var("lang_customer",lang("Customer"));
    
    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {                                                                                                          
    $db2->query("SELECT ab_id,ab_firstname,ab_lastname,ab_company_id,company_name from "                                                                   
                     . "addressbook,customers where customers.company_id=addressbook.ab_company_id and "                                                                                                      
                     . "addressbook.ab_company_id='" .$phpgw->db->f("customer")."'");                                                                                               
    if ($db2->next_record())                                                                                                                          
        $customerout = $db2->f("company_name")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]";                     
    }                                                                                                                                                           
    else {
    $db2->query("SELECT ab_id,ab_firstname,ab_lastname ab_company FROM addressbook where "
                     . "ab_id='" .$phpgw->db->f("customer")."'");
    if ($db2->next_record())
        $customerout = $db2->f("ab_company")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]";
    }    
    
    $t->set_var("customer",$customerout);
    
    $t->set_var("lang_calcb",lang("Calculate"));

    $t->pparse("out","project_stat");

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
  }
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
