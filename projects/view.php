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
  
    if (!$id) {
    Header("Location: " . $phpgw->link('/projects/index.php'
	  . "sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    }

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('view' => 'view.tpl'));

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
                        . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
                        . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
                        . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
                        . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
                        . "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";


    if ($phpgw_info["server"]["db_type"]=="pgsql") { $join = " JOIN "; }
    else { $join = " LEFT JOIN "; }

    $db2 = $phpgw->db;

    $phpgw->db->query("select * from phpgw_p_projects where id='$id'");
    $phpgw->db->next_record();
     
    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var("error","");
    $t->set_var('currency',$currency);
    }
    else { $t->set_var("error",lang("Please select your currency in preferences!")); }

    $t->set_var('done_action',$phpgw->link("/projects/index.php","sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    $t->set_var('lang_done',lang('Done'));
    $t->set_var("lang_action",lang("View project"));
    $t->set_var("hidden_vars",$hidden_vars);
    $t->set_var("lang_num",lang("Project ID"));
    $t->set_var("num",$phpgw->strip_html($phpgw->db->f("num")));
    $t->set_var("lang_title",lang("Title"));
    $title  = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                               
    if (! $title)  $title  = "&nbsp;";                                                                                                                                                  
    $t->set_var("title",$title);
    $descrval  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                               
    if (! $descrval)  $descrval  = "&nbsp;";                                                                                                                                                  
    $t->set_var("descrval",$descrval);

    $t->set_var("lang_status",lang('Status'));
    $t->set_var('status',$phpgw->db->f("status"));

    $t->set_var("lang_budget",lang("Budget"));
    $t->set_var("budget",$phpgw->db->f("budget"));
    $t->set_var('lang_start_date',lang('Start date'));
    $t->set_var('lang_end_date',lang('Date due'));

    $sdate = $phpgw->db->f("start_date");
    $edate = $phpgw->db->f("end_date");

    if ($sdate != 0) {
    $smonth = $phpgw->common->show_date(time(),"n");
    $sday   = $phpgw->common->show_date(time(),"d");
    $syear  = $phpgw->common->show_date(time(),"Y");
    $sdate = $sdate + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
    $sdateout =  $phpgw->common->show_date($sdate,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }
    else { $sdateout = "&nbsp;"; }
    $t->set_var('sdate',$sdateout);

    if ($edate != 0) {
    $emonth = $phpgw->common->show_date(time(),"n");
    $eday   = $phpgw->common->show_date(time(),"d");
    $eyear  = $phpgw->common->show_date(time(),"Y");
    $edate = $edate + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
    $edateout =  $phpgw->common->show_date($edate,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }
    else { $edateout = "&nbsp;"; }
    $t->set_var('edate',$edateout);

    $t->set_var("lang_coordinator",lang("Coordinator"));
     
    $db2->query("SELECT account_lid,account_firstname,account_lastname FROM phpgw_accounts where "
                     . "account_id='" . $phpgw->db->f("coordinator") . "'");
    $db2->next_record();
    $t->set_var('coordinator',$db2->f("account_lid"). " [ " . $db2->f("account_firstname") . " " . $db2->f("account_lastname") . " ]");

// customer 
    $t->set_var("lang_select",lang("Select per button !"));
    $t->set_var("lang_customer",lang("Customer"));

    $d = CreateObject('phpgwapi.contacts');
    $abid = $phpgw->db->f("customer");
    $cols = array('n_given' => 'n_given',
                 'n_family' => 'n_family',
                 'org_name' => 'org_name');

    $customer = $d->read_single_entry($abid,$cols);
    
    $t->set_var('name',$customer[0]['org_name'] . " [ " . $customer[0]['n_given'] . " " . $customer[0]['n_family'] . " ]");

// activites bookable
    $t->set_var("lang_bookable_activities",lang("Bookable activities"));

    $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_projectactivities.project_id FROM phpgw_p_activities "
		     . "$join phpgw_p_projectactivities ON (phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) and  "
                     . "((project_id='$id') or (project_id IS NULL)) WHERE billable IS NULL OR billable='N' ORDER BY descr asc");
	while ($db2->next_record()) { $ba_activities_list .= $phpgw->strip_html($db2->f("descr")) . "<br>"; }
    
    $t->set_var('lang_descr',lang('Description'));
    $t->set_var('ba_activities_list',$ba_activities_list);  

// activities billable
     $t->set_var("lang_billable_activities",lang("Billable activities"));
     $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_activities.billperae, "
		     . "phpgw_p_projectactivities.project_id,phpgw_p_projectactivities.billable"
		     . " FROM phpgw_p_activities $join phpgw_p_projectactivities ON "
                     . "(phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) and  "
                     . "((project_id='$id') or (project_id IS NULL)) WHERE billable IS NULL OR billable='Y' ORDER BY descr asc");

	while ($db2->next_record()) {
	    $bill_activities_list .= $phpgw->strip_html($db2->f("descr")) . " " . $currency . " " . $db2->f("billperae")
					. " " . lang('per workunit') . " " . "<br>";
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

    $t->pparse('out','view');

    $phpgw->common->phpgw_footer();
?>
