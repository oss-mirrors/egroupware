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

  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, 
                                  "nonavbar" => True);
  }
  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");

  
  $db2 = $phpgw->db;
  
  
  if (! $submit) {
     ?>
      
   <?PHP
  	$t = new Template($phpgw_info["server"]["app_tpl"]);
  	
  	$t->set_var("actionurl",$phpgw->link("add.php"));
  	$t->set_file(array( "projects_add" => "form.tpl"));
  	
  	// ====================================================================
     	// create two seperate blocks, editblock will be cut off from template
     	// addblock contains the buttons needed
     	// ====================================================================
     	$t->set_block("projects_add", "add", "addhandle");
     	$t->set_block("projects_add", "edit", "edithandle");
     	$t->set_block("projects_add", "edit_act", "acthandle");
  	
        $t->set_var("addressbook_link",$phpgw->link("addressbook.php","query="));
  	$t->set_var("lang_action",lang("project list - add"));
	
	$common_hidden_vars = "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
        		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
        		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
        		. "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
        		. "<input type=\"hidden\" name=\"id\" value=\"$id\">";
        		
        $t->set_var("common_hidden_vars",$common_hidden_vars);
        $t->set_var("lang_num",lang("num"));
        $db2->query("SELECT max(num+1) AS max FROM p_projects");
        if($db2->next_record()) {
           $t->set_var("num",(int)($db2->f("max")));
        } else {
           $t->set_var("num","1");
        }
        $t->set_var("lang_title",lang("title"));
        $t->set_var("title","");
        $t->set_var("lang_descr",lang("description"));
	$t->set_var("descrval","");

        $t->set_var("lang_status",lang("status"));
	$status_list = "<option value=\"active\" selected>" . lang("active") . "</option>\n"
           		. "<option value=\"nonactive\">" . lang("nonactive") . "</option>\n"
           		. "<option value=\"archiv\">" . lang("archiv") . "</option>\n"
           		. "<option value=\"template\">" . lang("template") . "</option>\n";

	
        $t->set_var("status_list",$status_list);
        $t->set_var("lang_budget",lang("budget"));
        $t->set_var("budget","");

	$cur_month=date("n",time());
        $cur_day=date("j",time());
        $cur_year=date("Y",time());
        $t->set_var("lang_date",lang("date"));
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

        $t->set_var("lang_end_date",lang("end_date"));
	$end_date_formatorder = "<select name=\"end_month\">\n"
              . "<option value=\"\" SELECTED> </option>\n"
              . "<option value=\"1\">" . lang("january") . "</option>\n"
              . "<option value=\"2\">" . lang("February"). "</option>\n"
              . "<option value=\"3\">" . lang("March")   . "</option>\n"
              . "<option value=\"4\">" . lang("April")   . "</option>\n"
              . "<option value=\"5\">" . lang("May")             . "</option>\n"
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

        $t->set_var("lang_coordinator",lang("coordinator"));
        $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM accounts where "
                        . "account_status != 'L' ORDER BY account_lastname,account_firstname,account_id asc");
        while ($phpgw->db->next_record()) {
           $coordinator_list .= "<option value=\"" . $phpgw->db->f("account_id") . "\""
                    . $selected_users[$phpgw->db->f("account_id")] . ">"
	            . $phpgw->common->display_fullname($phpgw->db->f("account_lid"),
                      $phpgw->db->f("account_firstname"),
                      $phpgw->db->f("account_lastname")) . "</option>";
        }
        $t->set_var("coordinator_list",$coordinator_list);

        $t->set_var("lang_customer",lang("customer"));
        $t->set_var("customer_con","");
        $t->set_var("customer_name","");

        $t->set_var("lang_bookable_activities","");
        $t->set_var("ba_activities_list","");  
        $t->set_var("lang_billable_activities","");
        $t->set_var("bill_activities_list","");
      
        $t->set_var("lang_access_type",lang("Access type"));
        $t->set_var("access_list",lang("Access type"));
	
        $access_list = "<option value=\"private\">" . lang("Private") . "</option>\n"
        	      ."<option value=\"public\" selected>" .lang("Global public"). "</option>\n"
        	      ."<option value=\"group\">" .lang("Group public"). "</option>\n"; 

	$t->set_var("access_list",$access_list);
	
	$t->set_var("lang_which_groups",lang("which groups"));

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
        
?>

   <?php
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

    $access = $phpgw->accounts->array_to_string($access,$n_groups);

    if ($phpgw_info["user"]["permissions"]["manager"] && $otheruser) {
       $owner = $otheruser;
    } else {
       $owner = $phpgw_info["user"]["account_id"];
    }

    $phpgw->db->query("insert into p_projects (owner,access,entry_date,date,end_date," 
                . "coordinator,customer,status,descr,title,budget,num) "
                . "values ('$owner','$access','" . time() ."','$date','$end_date',"
                . "'$coordinator','$customer','$status','" . addslashes($descr) . "',"
                . "'" . addslashes($title) . "','" . addslashes($budget) . "',"
		. "'" . $num . "')");
    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/",
           "cd=14&sort=$sort&order=$order&query=$query&start="
         . "$start&filter=$filter"));
  }
