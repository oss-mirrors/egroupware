<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

    $phpgw_info["flags"]["currentapp"] = "projects";
    include("../header.inc.php");

    $t = CreateObject('phpgwapi.Template',$phpgw_info["server"]["app_tpl"]);
    $t->set_file(array( "projects_add" => "form.tpl"));
    $t->set_block("projects_add", "add", "addhandle");
    $t->set_block("projects_add", "edit", "edithandle");
    $t->set_block("projects_add", "edit_act", "acthandle");

    if ($phpgw_info["server"]["db_type"]=="pgsql") { $join = " JOIN "; }
    else { $join = " LEFT JOIN "; }

    $db2 = $phpgw->db;

    if ($submit) {

    if ($choose) { $num = create_projectid($year); }
    else { $num = addslashes($num); }

    $errorcount = 0;
    $phpgw->db->query("select count(*) from phpgw_p_projects where num='$num'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) != 0) { $error[$errorcount++] = lang('That Project ID has been used already !'); }

    if (!$num) { $error[$errorcount++] = lang('Please enter an ID for that Project !'); }


    if (checkdate($smonth,$sday,$syear)) { $sdate = mktime(2,0,0,$smonth,$sday,$syear); } 
    else {
	if ($smonth && $sday && $syear) { $error[$errorcount++] = lang('You have entered an invalid date !'). "<br>" . "$smonth - $sday - $syear" . "</b></center>"; }
    }

    if (checkdate($emonth,$eday,$eyear)) { $edate = mktime(2,0,0,$emonth,$eday,$eyear); } 
      else {
	if ($emonth && $eday && $eyear) { $error[$errorcount++] = lang("You have entered an invailed date"). "<br>" . "$emonth - $eday - $eyear" . "</b></center>"; }                                                   
    }                                                                                                                                                                                

    if (! $error) {
    $owner = $phpgw_info["user"]["account_id"];
    $descr = addslashes($descr);
    $title = addslashes($title);

    $phpgw->db->query("insert into phpgw_p_projects (owner,entry_date,start_date,end_date,coordinator,customer,status,descr,title,budget,num) "
                   . "values ('$owner','" . time() ."','$sdate','$edate',"
                   . "'$coordinator','$abid','$status','$descr','$title','$budget','$num')");

    $db2->query("SELECT max(id) AS max FROM phpgw_p_projects");
	if($db2->next_record()) {
	$p_id = $db2->f("max");
	if (count($ba_activities) != 0) {
	while($activ=each($ba_activities)) {
	$phpgw->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','$activ[1]','N')");
	    }
	}

    if (count($bill_activities) != 0) {
	while($activ=each($bill_activities)) {
	$phpgw->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','$activ[1]','Y')");
	    }
	  }
        }
      }
    }                                                                                                                                                                                  
    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang("Project $num - $title has been added !")); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',""); }

    $t->set_var("actionurl",$phpgw->link("add.php"));
    $t->set_var("addressbook_link",$phpgw->link("addressbook.php","query="));
    $t->set_var("lang_action",lang("Add project"));

    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var("error","");
    }
    else { $t->set_var("error",lang("Please select your currency in preferences!")); }
    
    $hidden_vars = "<input type=\"hidden\" name=\"id\" value=\"$id\">";
    $t->set_var('hidden_vars',$hidden_vars);
    
    $t->set_var("lang_num",lang("Project ID"));
    
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
    $t->set_var('lang_start_date',lang('Start date'));
    $t->set_var('lang_end_date',lang('Date due'));
    $t->set_var("budget",$budget);

    $sm = CreateObject('phpgwapi.sbox');

    if (!$sdate) {
        $smonth = date('m',time());
        $sday = date('d',time());
        $syear = date('Y',time());
	}
    else {
        $smonth = date('m',$sdate);
        $sday = date('d',$sdate);
        $syear = date('Y',$sdate);
	}

    $t->set_var('start_date_select',$phpgw->common->dateformatorder($sm->getYears('syear',$syear),$sm->getMonthText('smonth',$smonth),$sm->getDays('sday',$sday)));

    if (!$edate) {
        $emonth = 0;
        $eday = 0;
        $eyear = 0;
	}
    else {
        $emonth = date('m',$edate);
        $eday = date('d',$edate);
        $eyear = date('Y',$edate);
	}

    $t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));
    
    $t->set_var("lang_coordinator",lang("Coordinator"));
    $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts where "
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
    $t->set_var('abid',$abid);
    $t->set_var('name',$name);

// activities bookable     
    $t->set_var("lang_bookable_activities",lang("Bookable activities"));       
    $db2->query("SELECT phpgw_p_activities.id as id,descr FROM phpgw_p_activities ORDER BY descr asc");
        while ($db2->next_record()) {
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        $ba_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr"))
                    . "</option>";
        }

    $t->set_var("ba_activities_list",$ba_activities_list);  

// activities billable 
    $t->set_var("lang_billable_activities",lang("Billable activities"));
    $db2->query("SELECT phpgw_p_activities.id as id,descr,billperae FROM phpgw_p_activities ORDER BY descr asc");
     while ($db2->next_record()) {
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        $bill_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr")) . " " . $currency . " "
                    . $db2->f("billperae") . " " . lang("per workunit") . "</option>";
    }

    $t->set_var("bill_activities_list",$bill_activities_list);
    
    $t->set_var("lang_add",lang("Add"));
    $t->set_var("lang_reset",lang("Clear Form"));

    $t->set_var("edithandle","");
    $t->set_var("addhandle","");
    $t->pparse("out","projects_add");
    $t->pparse("addhandle","add");

    $phpgw->common->phpgw_footer();

?>