<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         *
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
  
    if (!$id) { Header('Location: ' . $phpgw->link('/projects/index.php' . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter")); }

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array( "projects_edit" => "form.tpl"));
    $t->set_block("projects_edit", "add", "addhandle");
    $t->set_block("projects_edit", "edit", "edithandle");

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
			. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
			. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
			. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
			. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
			. "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

    if ($phpgw_info["server"]["db_type"]=="pgsql") { $join = " JOIN "; }
    else { $join = " LEFT JOIN "; }

    $db2 = $phpgw->db;

    if ($submit) {

    $errorcount = 0;    
    $phpgw->db->query("select count(*) from phpgw_p_projects where num='$num' and id != '$id'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) != 0) { $error[$errorcount++] = lang('That Project ID has been used already !'); }

    if (!$num) { $error[$errorcount++] = lang('Please enter an ID for that Project !'); }

    if (checkdate($smonth,$sday,$syear)) { $sdate = mktime(2,0,0,$smonth,$sday,$syear); }
    else {
        if ($smonth && $sday && $syear) { $error[$errorcount++] = lang('You have entered an invalid date !') . " : " . "$smonth - $sday - $syear"; }
    }
    if (checkdate($emonth,$eday,$eyear)) { $edate = mktime(2,0,0,$emonth,$eday,$eyear); }
      else {
        if ($emonth && $eday && $eyear) { $error[$errorcount++] = lang("You have entered an invalid date") . " : " . "$emonth - $eday - $eyear"; }
    }

    if ((!$ba_activities) && (!$bill_activities)) { $error[$errorcount++] = lang('Please choose activities for that project first !'); }

    if (! $error) {
    $owner = $phpgw_info["user"]["account_id"];
    $num = addslashes($num);
    $descr = addslashes($descr);
    $title = addslashes($title);

    $phpgw->db->query("update phpgw_p_projects set entry_date='" . time() . "', start_date='"
		    . "$sdate', end_date='$edate', coordinator='$coordinator', "
		    . "customer='$abid', status='$status', descr='$descr', title='$title', "
		    . "budget='$budget', num='$num' where id='$id'");

    $phpgw->db->query("delete from phpgw_p_projectactivities where project_id='$id' and billable='N'");

    if (count($ba_activities) != 0) {
	while($activ=each($ba_activities)) {
	    $phpgw->db->query("insert into phpgw_p_projectactivities (project_id, activity_id, billable) values ('$id','$activ[1]','N')");
	}
    }

    $phpgw->db->query("delete from phpgw_p_projectactivities where project_id='$id' and billable='Y'");

    if (count($bill_activities) != 0) {
        while($activ=each($bill_activities)) {
	    $phpgw->db->query("delete from phpgw_p_projectactivities where project_id='$id' and activity_id='$activ[1]' and billable='N'");
	    $phpgw->db->query("insert into phpgw_p_projectactivities (project_id, activity_id, billable) values ('$id','$activ[1]','Y')");
	  }
	}
      }
    }

    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang("Project $num $title has been updated !")); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',""); }


     $phpgw->db->query("select * from phpgw_p_projects where id='$id'");
     $phpgw->db->next_record();

     
    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var('error','');
    $t->set_var('currency',$currency);
    }
    else { $t->set_var('error',lang('Please select your currency in preferences !')); }

    $t->set_var("addressbook_link",$phpgw->link("/projects/addressbook.php","query="));
    $t->set_var("actionurl",$phpgw->link("/projects/edit.php","id=$id"));
    $t->set_var("deleteurl",$phpgw->link("/projects/delete.php","id=$id"));
    $t->set_var("lang_action",lang("Edit project"));
    $t->set_var("hidden_vars",$hidden_vars);
    $t->set_var("lang_num",lang("Project ID"));
    $t->set_var("num",$phpgw->strip_html($phpgw->db->f("num")));
    $t->set_var("lang_choose","");                                                                                                                    
    $t->set_var("choose","");
    $t->set_var("lang_title",lang("Title"));
    $title  = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                               
    if (! $title)  $title  = "&nbsp;";                                                                                                                                                  
    $t->set_var("title",$title);
    $descrval  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                               
    if (! $descrval)  $descrval  = "&nbsp;";                                                                                                                                                  
    $t->set_var("descrval",$descrval);

    $t->set_var("lang_status",lang("Status"));
    if ($phpgw->db->f("status")=="active"):
         $stat_sel[0]=" selected";
     elseif ($phpgw->db->f("status")=="nonactive"):
         $stat_sel[1]=" selected";
     elseif ($phpgw->db->f("status")=="archiv"):
         $stat_sel[2]=" selected";
     endif;

     $status_list = "<option value=\"active\"".$stat_sel[0].">" . lang('Active') . "</option>\n"
                  . "<option value=\"nonactive\"".$stat_sel[1].">" . lang('Nonactive') . "</option>\n"
                  . "<option value=\"archiv\"".$stat_sel[2].">" . lang('Archiv') . "</option>\n";
     $t->set_var("status_list",$status_list);

    $t->set_var('lang_budget',lang('Budget'));
    $t->set_var('budget',$phpgw->db->f("budget"));
    $t->set_var('lang_start_date',lang('Start date'));
    $t->set_var('lang_end_date',lang('Date due'));

    $sdate = $phpgw->db->f("start_date");
    $edate = $phpgw->db->f("end_date");

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

     $t->set_var('lang_coordinator',lang('Coordinator'));
     
/*     $db2->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts where "
                     . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
     while ($db2->next_record()) {
        $coordinator_list .= "<option value=\"" . $db2->f("account_id") . "\"";
        if($db2->f("account_id")==$phpgw->db->f("coordinator"))
            $coordinator_list .= " selected";
        $coordinator_list .= ">"        
                    . $phpgw->common->display_fullname($db2->f("account_id"),
                      $db2->f("account_firstname"),
                      $db2->f("account_lastname")) . "</option>";
     }  */

    $employees = $phpgw->accounts->get_list('accounts', $start, $sort, $order, $query);
        while (list($null,$account) = each($employees)) {
            $coordinator_list .= "<option value=\"" . $account['account_id'] . "\"";
            if($account['account_id']==$phpgw->db->f("coordinator"))
            $coordinator_list .= " selected";
            $coordinator_list .= ">"
                    . $phpgw->common->display_fullname($account['account_id'],
                      $account['account_firstname'],
                      $account['account_lastname']) . "</option>";
    }

    $t->set_var('coordinator_list',$coordinator_list);  

// customer 
    $t->set_var('lang_select',lang('Select per button !'));
    $t->set_var('lang_customer',lang('Customer'));

    $d = CreateObject('phpgwapi.contacts');
    $abid = $phpgw->db->f("customer");

//    if ($abid) {
    $cols = array('n_given' => 'n_given',
                 'n_family' => 'n_family',
                 'org_name' => 'org_name');

    $customer = $d->read_single_entry($abid,$cols);
    
    $t->set_var('name',$customer[0]['org_name'] . " [ " . $customer[0]['n_given'] . " " . $customer[0]['n_family'] . " ]");
    $t->set_var('abid',$abid);
/*    }
    else {
    $t->set_var('name',$name);
    $t->set_var('abid',$abid);
    } */

// activites bookable
    $t->set_var("lang_bookable_activities",lang("Bookable activities"));

    $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_projectactivities.project_id,phpgw_p_projectactivities.billable "
		. "FROM phpgw_p_activities "
		. "$join phpgw_p_projectactivities ON (phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) AND "
		. "((project_id='$id') OR (project_id IS NULL)) WHERE billable IS NULL OR billable='N' OR billable='Y' ORDER BY descr asc");
    while ($db2->next_record()) {
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("billable")=="N")
            $ba_activities_list .= " selected";
        $ba_activities_list .= ">"        
                    . $phpgw->strip_html($db2->f("descr"))
                    . "</option>";
    }
    
    $t->set_var('lang_descr',lang('Description'));
    $t->set_var('ba_activities_list',$ba_activities_list);  

// activities billable
     $t->set_var("lang_billable_activities",lang("Billable activities"));
     $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_activities.billperae, "
		     . "phpgw_p_projectactivities.project_id,phpgw_p_projectactivities.billable"
		     . " FROM phpgw_p_activities $join phpgw_p_projectactivities ON "
                     . "(phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) AND "
                     . "((project_id='$id') OR (project_id IS NULL)) WHERE billable IS NULL OR billable='Y' OR billable='N' ORDER BY descr asc");

     while ($db2->next_record()) {
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("billable")=="Y")
            $bill_activities_list .= " selected";
        $bill_activities_list .= ">"        
                    . $phpgw->strip_html($db2->f("descr")) . " " . $currency . " " . $db2->f("billperae")
                    . " " . lang("per workunit") . " " . "</option>";
     }
     $t->set_var('bill_activities_list',$bill_activities_list);  

/*    $t->set_var("lang_access_type",lang("Access type"));   
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
    $access_list .= ">" . lang("Group public") . "</option>\n";

    $_access = $phpgw->db->f("access");     

    $t->set_var("access_list",$access_list);
    $t->set_var("lang_which_groups",lang("Which groups"));
    
    $user_groups = $phpgw->common->sql_search();

	       for ($i=0;$i<count($user_groups);$i++) {
                  $group_list .= "<option value=\"" . $user_groups[$i][0] . "\"";
		  if (ereg(",".$user_groups[$i][0].",",$phpgw->db->f("access")))
                  if (ereg(",".$user_groups[$i][0].",",$_access)) 
		     $group_list .= " selected";
		  $group_list .= ">" . $user_groups[$i][1] . "</option>\n";
	       }
    
    $t->set_var("group_list",$group_list); */

    $t->set_var('lang_edit',lang('Edit'));
    $t->set_var('lang_delete',lang('Delete'));
    
    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','projects_edit');
    $t->pparse('edithandle','edit');

    $phpgw->common->phpgw_footer();
?>
