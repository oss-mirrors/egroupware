<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  if (($submit) or ($template)) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "projects";
  include("../header.inc.php");
  
  
  $db2 = $phpgw->db;
  
  
  if (!$id)
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/"
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
     $t->set_file(array( "projects_edit" => "form.tpl"));
     
     // ====================================================================
     // create two seperate blocks, addblock will be cut off from template
     // editblock contains the buttons and forms for edit
     // ====================================================================
     $t->set_block("projects_edit", "add", "addhandle");
     $t->set_block("projects_edit", "edit", "edithandle");
     
     $t->set_var("addressbook_link",$phpgw->link("addressbook.php","query="));
     $t->set_var("actionurl",$phpgw->link("edit.php"));
     $t->set_var("deleteurl",$phpgw->link("delete.php"));
     $t->set_var("lang_action",lang("project list - edit"));
     $t->set_var("common_hidden_vars",$common_hidden_vars);
     $t->set_var("lang_num",lang("num"));
     $t->set_var("num", stripslashes($phpgw->db->f("num")));
     $t->set_var("lang_title",lang("title"));
     $t->set_var("title", stripslashes($phpgw->db->f("title")));
     $t->set_var("descrval", stripslashes($phpgw->db->f("descr")));
     $t->set_var("lang_status",lang("status"));
     if ($phpgw->db->f("status")=="active"):
         $stat_sel[0]=" selected";
     elseif ($phpgw->db->f("status")=="nonactive"):
         $stat_sel[1]=" selected";
     elseif ($phpgw->db->f("status")=="archiv"):
         $stat_sel[2]=" selected";
     elseif ($phpgw->db->f("status")=="template"):
         $stat_sel[3]=" selected"; 
     endif;

     $status_list = "<option value=\"active\"".$stat_sel[0].">" . lang("active") . "</option>\n"
                  . "<option value=\"nonactive\"".$stat_sel[1].">" . lang("nonactive") . "</option>\n"
                  . "<option value=\"archiv\"".$stat_sel[2].">" . lang("archiv") . "</option>\n"
                  . "<option value=\"template\"".$stat_sel[3].">" . lang("template") . "</option>\n";       
     $t->set_var("status_list",$status_list);
     if($phpgw->db->f("status")=="template") {
        $t->set_var("lang_cptemplateb",lang("copy template"));
     } else {
        $t->set_var("lang_cptemplateb","");
     }
     $t->set_var("lang_budget",lang("budget"));
     $t->set_var("budget",stripslashes($phpgw->db->f("budget")));

     $t->set_var("lang_date",lang("date"));
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

     $t->set_var("lang_end_date",lang("end_date"));
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

     $t->set_var("lang_coordinator",lang("coordinator"));
     
     $db2->query("SELECT account_id,account_firstname,account_lastname FROM accounts where "
                     . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
     while ($db2->next_record()) {
        $coordinator_list .= "<option value=\"" . $db2->f("account_id") . "\"";
        if($db2->f("account_id")==$phpgw->db->f("coordinator"))
            $coordinator_list .= " selected";
        $coordinator_list .= ">"        
                    . $phpgw->common->display_fullname($db2->f("account_id"),
                      $db2->f("account_firstname"),
                      $db2->f("account_lastname")) . "</option>";
     }
     $t->set_var("coordinator_list",$coordinator_list);  

// customer 
    $t->set_var("lang_customer",lang("customer"));
    $t->set_var("customer_con",$phpgw->db->f("customer"));

    if ($phpgw_info["apps"]["timetrack"]["enabled"]) {
    $db2->query("SELECT ab_id,ab_firstname,ab_lastname,ab_company_id,company_name FROM "
                     . "addressbook,customers where "
                     . "ab_company_id='" .$phpgw->db->f("customer")."'");
    if ($db2->next_record()) {
        $t->set_var("customer_name",$db2->f("company_name")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]");
    } else {
        $t->set_var("customer_name","");
    }
    }
    else {
    $db2->query("select ab_id,ab_lastname,ab_firstname,ab_company from addressbook where "
                        . "ab_id='" .$phpgw->db->f("customer")."'");
	if ($db2->next_record()) {
        $t->set_var("customer_name",$db2->f("ab_company")." [ ".$db2->f("ab_firstname")." ".$db2->f("ab_lastname")." ]");
	}
	else {
	$t->set_var("customer_name","");		
        }
      }
// activites bookable
     $t->set_var("lang_bookable_activities",lang("bookable activities"));
     $db2->query("SELECT p_activities.id as id,p_activities.descr,"
		     . "p_projectactivities.project_id FROM p_activities "
		     . "LEFT JOIN p_projectactivities ON "
                     . "(p_activities.id=p_projectactivities.activity_id) and  "
                     . "((project_id='$id') or (project_id IS NULL)) "
                     . " WHERE billable IS NULL OR billable='N' ORDER BY descr asc");
     while ($db2->next_record()) {
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("project_id"))
            $ba_activities_list .= " selected";
        $ba_activities_list .= ">"        
                    . $db2->f("descr")
                    . "</option>";
     }
     $t->set_var("lang_descr",lang("description"));
     $t->set_var("ba_activities_list",$ba_activities_list);  

// activities billable
     $t->set_var("lang_billable_activities",lang("billable activities"));
     $db2->query("SELECT p_activities.id as id,p_activities.descr,"
		     . "p_projectactivities.project_id,p_projectactivities.billable"
		     . " FROM p_activities LEFT JOIN p_projectactivities ON "
                     . "(p_activities.id=p_projectactivities.activity_id) and  "
                     . "((project_id='$id') or (project_id IS NULL)) "
//                     . " WHERE billable IS NULL OR billable='Y' ORDER BY descr asc");
                     . " ORDER BY descr asc");
     while ($db2->next_record()) {
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("billable")=="Y")
            $bill_activities_list .= " selected";
        $bill_activities_list .= ">"        
                    . $db2->f("descr")
                    . " " . lang("billperae") . " "
                    . $db2->f("billperae") . "</option>";
     }
     $t->set_var("bill_activities_list",$bill_activities_list);  
//

    $t->set_var("lang_access_type",lang("Access type"));   
    $access_list = "<option value=\"private\"";
      		if ($phpgw->db->f("access") == "private")
              		$access_list .= " selected";
    $access_list .= ">" . lang("Private") . "</option>\n";
           
    $access_list .= "<option value=\"public\"";
       		if ($phpgw->db->f("access") == "public")
              		$access_list .= " selected";
    $access_list .= ">" . lang("Global public") . "</option>\n";

    $access_list .= "<option value=\"group\"";
       		if ($phpgw->db->f("access") != "public" && $phpgw->db->f("access") != "private")
	               $access_list .= " selected";
    $access_list .= ">" . lang("Group Public") . "</option>\n";

    $_access = $phpgw->db->f("access");     

    $t->set_var("access_list",$access_list);
    $t->set_var("lang_which_groups",lang("Which groups"));
    
    $user_groups = $phpgw->accounts->read_group_names();

	       for ($i=0;$i<count($user_groups);$i++) {
                  $group_list .= "<option value=\"" . $user_groups[$i][0] . "\"";
		  if (ereg(",".$user_groups[$i][0].",",$phpgw->db->f("access")))
                  if (ereg(",".$user_groups[$i][0].",",$_access)) 
		     $group_list .= " selected";
		  $group_list .= ">" . $user_groups[$i][1] . "</option>\n";
	       }
    
    $t->set_var("group_list",$group_list);
    $t->set_var("lang_editsubmitb",lang("Edit"));
    $t->set_var("lang_editdeleteb",lang("Delete"));
    $t->set_var("lang_atvititiesb",lang("Activities"));
    
    $t->set_var("edithandle","");
    $t->set_var("addhandle","");
    $t->pparse("out","projects_edit");
    $t->pparse("edithandle","edit");
   ?>

   <?
  } else {
    // Create function to take care of this
    if ($access == "group") {
     if (count($n_groups) != 0) {
        $access = ",";
        $access .= implode(",",$n_groups);
        $access .= ",";
     } else
        $access = "private";            // Change to spit out an error
    }

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
    if($submit) {
      $phpgw->db->query("update p_projects set entry_date='" . time() . "', date='" 
                   . "$date',end_date='$end_date',coordinator='$coordinator',"
                   . "customer='$customer',status='$status',descr='"
                   . addslashes($descr) . "',title='".addslashes($title)."',"
                   . "budget='".addslashes($budget)."',access='$access' where id='$id'");


      $phpgw->db->query("delete from p_projectactivities where project_id='$id' and billable='N'");
       if (count($ba_activities) != 0) {
        while($activ=each($ba_activities)) {
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"
                       . "billable) values ('$id','$activ[1]','N')");
        }
      }
      $phpgw->db->query("delete from p_projectactivities where project_id='$id' and billable='Y'");
       if (count($bill_activities) != 0) {
        while($activ=each($bill_activities)) {
           $phpgw->db->query("delete from p_projectactivities where project_id='$id' and activity_id='$activ[1]' and billable='N'");
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"
                       . "billable) values ('$id','$activ[1]','Y')");
        }
      }

    } 
    if($template) {
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
                  . "'" . addslashes($num) . "')");

      $phpgw->db->query("select LAST_INSERT_ID()");
      $phpgw->db->next_record();
      $id = $phpgw->db->f("id");
      if (count($ba_activities) != 0) {
        while($activ=each($ba_activities)) {
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"
                       . "billable) values ('$id','$activ[1]','N')");
        }
      }
      if (count($bill_activities) != 0) {
        while($activ=each($bill_activities)) {
           $phpgw->db->query("delete from p_projectactivities where project_id='$id' and activity_id='$activ[1]' and billable='N'");
           $phpgw->db->query("insert into p_projectactivities (project_id,activity_id,"
                       . "billable) values ('$id','$activ[1]','Y')");
        }
      }
      
    }

    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/projects/",
	   "cd=15&sort=$sort&order=$order&query=$query&start="
	 . "$start&filter=$filter"));
  }
?>
