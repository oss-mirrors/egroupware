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

    $phpgw_info['flags'] = array('currentapp' => 'projects',
		    'enable_categories_class' => True);
    include('../header.inc.php');

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('projects_add' => 'form.tpl'));
    $t->set_block('projects_add','add','addhandle');
    $t->set_block('projects_add','edit','edithandle');

    if ($new_cat) { $cat_id = $new_cat; }

    if ($phpgw_info['server']['db_type']=="pgsql") { $join = " JOIN "; }
    else { $join = " LEFT JOIN "; }

    $db2 = $phpgw->db;

    if ($submit) {

    if ($choose) { $num = create_projectid($year); }
    else { $num = addslashes($num); }

    $errorcount = 0;

    if (!$num) { $error[$errorcount++] = lang('Please enter an ID for that Project !'); }

    $phpgw->db->query("select count(*) from phpgw_p_projects where num='$num'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) != 0) { $error[$errorcount++] = lang('That Project ID has been used already !'); }

    if (checkdate($smonth,$sday,$syear)) { $sdate = mktime(2,0,0,$smonth,$sday,$syear); } 
    else {
	if ($smonth && $sday && $syear) { $error[$errorcount++] = lang('You have entered an invalid start date !') . " : " . "$smonth - $sday - $syear"; }
    }

    if (checkdate($emonth,$eday,$eyear)) { $edate = mktime(2,0,0,$emonth,$eday,$eyear); } 
      else {
	if ($emonth && $eday && $eyear) { $error[$errorcount++] = lang('You have entered an invalid end date !') . " : " . "$emonth - $eday - $eyear"; }                                                   
    }                                                                                                                                                                                

    if ((!$ba_activities) && (!$bill_activities)) { $error[$errorcount++] = lang('Please choose activityies for that project first !'); }

    if (! $error) {
    
    if ($access) { $access = 'private'; }
    else { $access = 'public'; }

    $owner = $phpgw_info['user']['account_id'];
    $descr = addslashes($descr);
    $title = addslashes($title);

    $phpgw->db->query("insert into phpgw_p_projects (owner,access,category,entry_date,start_date,end_date,coordinator,customer,status,"
		    . "descr,title,budget,num) values ('$owner','$access','$cat_id','" . time() ."','$sdate','$edate',"
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
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Project x x has been added !',$num,$title)); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

    $t->set_var('actionurl',$phpgw->link('/projects/add.php'));
    $t->set_var('addressbook_link',$phpgw->link('/projects/addressbook.php','query='));
    $t->set_var('lang_action',lang('Add project'));

    if (isset($phpgw_info['user']['preferences']['common']['currency'])) {
	$currency = $phpgw_info['user']['preferences']['common']['currency'];
	$t->set_var('error','');
	$t->set_var('currency',$currency);
    }
    else { $t->set_var('error',lang('Please select your currency in preferences !')); }
    
    $hidden_vars = "<input type=\"hidden\" name=\"id\" value=\"$id\">"
		 . "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
                 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
                 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
                 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
                 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
                 . "<input type=\"hidden\" name=\"cat_id\" value=\"$cat_id\">\n";

    $t->set_var('hidden_vars',$hidden_vars);
    $t->set_var('lang_num',lang('Project ID'));
    $t->set_var('num',$num);

    if (! $submit) {
	$choose = "<input type=\"checkbox\" name=\"choose\" value=\"True\">";
	$t->set_var('lang_choose',lang('Generate Project ID ?'));
	$t->set_var('choose',$choose);
    }
    else {
	$t->set_var('lang_choose','');
	$t->set_var('choose',''); 
    }

    $t->set_var('lang_title',lang('Title'));
    $t->set_var('title',$title);
    $t->set_var('lang_descr',lang('Description'));
    $t->set_var('descrval',$descr);
    $t->set_var('lang_category',lang('Category'));
    $t->set_var('lang_select_cat',lang('Select category'));
    $t->set_var('category_list',$phpgw->categories->formated_list('select','all',$cat_id,'True'));

    $t->set_var('lang_status',lang('Status'));
    $status_list = "<option value=\"active\" selected>" . lang('Active') . "</option>\n"
		. "<option value=\"nonactive\">" . lang('Nonactive') . "</option>\n"
		. "<option value=\"archive\">" . lang('Archive') . "</option>\n";

    $t->set_var('status_list',$status_list);
    $t->set_var('lang_budget',lang('Budget'));
    $t->set_var('lang_start_date',lang('Start date'));
    $t->set_var('lang_end_date',lang('Date due'));
    $t->set_var('budget',$budget);

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
/*    $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts where "
                        . "account_status != 'L' ORDER BY account_lastname,account_firstname,account_id asc");
	while ($phpgw->db->next_record()) {
	    $coordinator_list .= "<option value=\"" . $phpgw->db->f("account_id") . "\"";
	    
	    if($phpgw->db->f("account_id")==$phpgw_info["user"]["account_id"]) { $coordinator_list .= " selected"; }
		    $coordinator_list .= ">"
		    . $phpgw->common->display_fullname($phpgw->db->f("account_id"),
		    $phpgw->db->f("account_firstname"),
		    $phpgw->db->f("account_lastname")) . "</option>";
	} */

    $employees = $phpgw->accounts->get_list('accounts', $start, $sort, $order, $query);
        while (list($null,$account) = each($employees)) {
            $coordinator_list .= "<option value=\"" . $account['account_id'] . "\"";
            if($account['account_id']==$phpgw_info["user"]["account_id"])
            $coordinator_list .= " selected";
            $coordinator_list .= ">"
	    . $account['account_firstname'] . " " . $account['account_lastname'] . " [ " . $account['account_lid'] . " ]" . "</option>";
    }

    $t->set_var('coordinator_list',$coordinator_list);

    $t->set_var('lang_select',lang('Select per button !'));
    $t->set_var('lang_customer',lang('Customer'));
    $t->set_var('abid',$abid);
    $t->set_var('name',$name);
    $t->set_var('lang_bookable_activities',lang('Bookable activities'));
    $t->set_var('lang_billable_activities',lang('Billable activities'));
    $t->set_var('lang_access',lang('Private'));
    if ($access) { $t->set_var('access', '<input type="checkbox" name="access" value="True" checked>'); }
    else { $t->set_var('access', '<input type="checkbox" name="access" value="True"'); }

    if (!$submit) {
// activities bookable     

    $db2->query("SELECT phpgw_p_activities.id as id,descr FROM phpgw_p_activities ORDER BY descr asc");
        while ($db2->next_record()) {
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        $ba_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr"))
                    . "</option>";
        }

// activities billable 

    $db2->query("SELECT phpgw_p_activities.id as id,descr,billperae FROM phpgw_p_activities ORDER BY descr asc");
     while ($db2->next_record()) {
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        $bill_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr")) . " " . $currency . " "
                    . $db2->f("billperae") . " " . lang('per workunit') . "</option>";
     }
    }
    else {
// activites bookable
    $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_projectactivities.project_id,phpgw_p_projectactivities.billable "
                . "FROM phpgw_p_activities "
                . "$join phpgw_p_projectactivities ON (phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) AND "
                . "((project_id='$p_id') OR (project_id IS NULL)) WHERE billable IS NULL OR billable='N' OR billable='Y' ORDER BY descr asc");
    while ($db2->next_record()) {
        $ba_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("billable")=="N")
            $ba_activities_list .= " selected";
        $ba_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr"))
                    . "</option>";
    }

// activities billable 

     $t->set_var("lang_billable_activities",lang("Billable activities"));
     $db2->query("SELECT phpgw_p_activities.id as id,phpgw_p_activities.descr,phpgw_p_activities.billperae, "
                     . "phpgw_p_projectactivities.project_id,phpgw_p_projectactivities.billable"
                     . " FROM phpgw_p_activities $join phpgw_p_projectactivities ON "
                     . "(phpgw_p_activities.id=phpgw_p_projectactivities.activity_id) AND "
                     . "((project_id='$p_id') OR (project_id IS NULL)) WHERE billable IS NULL OR billable='Y' OR billable='N' ORDER BY descr asc");

     while ($db2->next_record()) {
        $bill_activities_list .= "<option value=\"" . $db2->f("id") . "\"";
        if($db2->f("billable")=="Y")
            $bill_activities_list .= " selected";
        $bill_activities_list .= ">"
                    . $phpgw->strip_html($db2->f("descr")) . " " . $currency . " " . $db2->f("billperae")
                    . " " . lang('per workunit') . " " . "</option>";
     }
    }

    $t->set_var('ba_activities_list',$ba_activities_list);    
    $t->set_var('bill_activities_list',$bill_activities_list);
    
    $t->set_var('lang_add',lang('Add'));
    $t->set_var('lang_reset',lang('Clear Form'));
    $t->set_var('lang_done',lang('Done'));
    $t->set_var('done_url',$phpgw->link('/projects/index.php',"sort=$sort&order=$order&query=$query&start=$start&filter=$filter&cat_id=$cat_id"));

    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','projects_add');
    $t->pparse('addhandle','add');

    $phpgw->common->phpgw_footer();
?>