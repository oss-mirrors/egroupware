<?php
  /**************************************************************************\
  * phpGroupWare - projects/projecthours                                     *
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
  
    $db2 = $phpgw->db;
  
    if (!$id) {
     Header("Location: " . $phpgw->link('/projects/hours_index.php'
	  . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    }

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
		. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
		. "<input type=\"hidden\" name=\"id\" value=\"$id\">\n"
		. "<input type=\"hidden\" name=\"delivery_id\" value=\"$delivery_id\">\n"
		. "<input type=\"hidden\" name=\"invoice_id\" value=\"$invoice_id\">\n";



    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('hours_edit' => 'hours_formhours.tpl'));
    $t->set_block('hours_edit','add','addhandle');
    $t->set_block('hours_edit','edit','edithandle');

    if ($submit) {

    $errorcount = 0;
    if (checkdate($smonth,$sday,$syear)) { $sdate = mktime(2,0,0,$smonth,$sday,$syear); }
    else {
       if ($smonth && $sday && $syear) { $error[$errorcount++] = lang("You have entered an invalid date ! :") . " " . "$smonth - $sday - $syear"; }
    }
    if (checkdate($emonth,$eday,$eyear)) { $edate = mktime(2,0,0,$emonth,$eday,$eyear); }
    else {
       if ($emonth && $eday && $eyear) { $error[$errorcount++] = lang("You have entered an invailed end date ! :") . " " . "$emonth - $eday - $eyear"; }
    }

    $phpgw->db->query("SELECT minperae,billperae,remarkreq FROM phpgw_p_activities WHERE id = '".$activity."'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) == 0) { $error[$errorcount++] = lang('You have selected an invalid activity !'); }
    if (($phpgw->db->f("remarkreq")=="Y") and (!$remark)) { $error[$errorcount++] = lang('You have to enter a remark !'); }

    if (! $error) {
    $billperae = $phpgw->db->f("billperae");
    $minperae = $phpgw->db->f("minperae");
    $ae_minutes=$hours*60+$minutes;
    $remark = addslashes($remark);

    $phpgw->db->query("update phpgw_p_hours set activity_id='$activity',entry_date='" . time() . "',start_date='$sdate',end_date='$edate',remark='$remark',"
                . "minutes='$ae_minutes',status='$status',minperae='$minperae',billperae='$billperae',employee='$employee' where id='$id'");
      }
    }

    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Hours has been updated !')); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var('error','');
    $t->set_var('currency',$currency);
    }
    else {
    $t->set_var('error',lang('Please select your currency in preferences !'));
    }

    $phpgw->db->query("select * from phpgw_p_hours where id='$id'");
    $phpgw->db->next_record();

    $t->set_var('actionurl',$phpgw->link("/projects/hours_edithour.php"));
    $t->set_var('deleteurl',$phpgw->link("/projects/delete_hours.php","id=$id"));
    $t->set_var('lang_action',lang('Edit project hours'));
    $t->set_var('hidden_vars',$hidden_vars);
     
    $db2->query("SELECT num,title FROM phpgw_p_projects WHERE id = '".$phpgw->db->f("project_id")."'");
     if ($db2->next_record()) {
	$t->set_var('num',$phpgw->strip_html($db2->f("num")));
        $title  = $phpgw->strip_html($db2->f("title"));                                                                                                                                
        if (! $title)  $title  = "&nbsp;";
        $t->set_var('title',$title);
     }

    $t->set_var('lang_num',lang('Project ID'));
    $t->set_var('lang_title',lang('Title'));

    $t->set_var('lang_activity',lang('Activity'));
    
    $db2->query("SELECT activity_id,descr FROM phpgw_p_projectactivities,phpgw_p_activities"
                     . " WHERE project_id = '".$phpgw->db->f("project_id")."' AND phpgw_p_projectactivities.activity_id="
                     . "phpgw_p_activities.id");
	while ($db2->next_record()) {
        $activity_list .= "<option value=\"" . $phpgw->db->f("activity_id") . "\"";
        if($db2->f("activitiy_id")==$phpgw->db->f("activity_id"))
            $activity_list .= " selected";
        $activity_list .= ">"
          . $phpgw->strip_html($db2->f("descr")) . "</option>";
	}
    
    $t->set_var('activity_list',$activity_list);


    $t->set_var('lang_status',lang('Status'));
    if ($phpgw->db->f("status")=="open"): 
         $stat_sel[0]=" selected";
    elseif ($phpgw->db->f("status")=="done"):
         $stat_sel[1]=" selected";
    endif;

    $status_list = "<option value=\"open\"".$stat_sel[0].">" . lang("Open") . "</option>\n"
                  . "<option value=\"done\"".$stat_sel[1].">" . lang("Done") . "</option>\n";

    $t->set_var("status_list",$status_list);

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

    $t->set_var('lang_remark',lang("Remark"));
    $remark  = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                             
    if (! $remark)  $remark  = "&nbsp;";                                                                                                                                                
    $t->set_var("remark",$remark);

    $t->set_var('lang_time',lang('Time'));
    $t->set_var('hours',floor($phpgw->db->f("minutes")/60));
    $t->set_var('minutes',($phpgw->db->f("minutes"))-((floor($phpgw->db->f("minutes")/60)*60)));

    $t->set_var('lang_employee',lang('Employee'));
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
    $t->set_var('employee_list',$employee_list);  

    $t->set_var('lang_minperae',lang('Minutes per workunit'));
    $t->set_var('minperae',$phpgw->db->f('minperae'));
    $t->set_var('lang_billperae',lang("Bill per workunit"));
    $t->set_var('billperae',$phpgw->db->f('billperae'));

    $t->set_var('lang_done',lang('Done'));
    $t->set_var('doneurl',$phpgw->link($HTTPREFERRER . '&project_id=' . $phpgw->db->f("id") . "&delivery_id=$delivery_id&invoice_id=$invoice_id&sort=$sort&order=$order&"
                                        . "query=$query&start=$start&filter=$filter&status=$status"));

    $t->set_var('lang_edit',lang('Edit'));
    $t->set_var('lang_delete',lang('Delete'));
    
    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','hours_edit');
    $t->pparse('edithandle','edit');

    $phpgw->common->phpgw_footer();
?>
