<?php
  /**************************************************************************\
  * phpGroupWare - projects/projecthours                                     *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * ------------------------------------------------                         *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */
  
    $phpgw_info["flags"]["currentapp"] = "projects";
    include("../header.inc.php");

    if (!$id) { Header('Location: ' . $phpgw->link('/projects/hours_index.php',"sort=$sort&order=$order&query=$query&start=$start&filter=$filter")); }

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('hours_add' => 'hours_formhours.tpl'));
    $t->set_block('hours_add','add','addhandle');
    $t->set_block('hours_add','edit','edithandle');

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

    if (! $activity) { $error[$errorcount++] = lang('Please choose an activity for the project first !'); }

    $phpgw->db->query("SELECT minperae,billperae,remarkreq FROM phpgw_p_activities WHERE id = '" . $activity . "'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) == 0) { $error[$errorcount++] = lang('You have selected an invalid activity !'); }
    else { 
    $billperae = $phpgw->db->f("billperae");
    $minperae = $phpgw->db->f("minperae");

    if (($phpgw->db->f("remarkreq")=="Y") and (!$remark)) { $error[$errorcount++] = lang('Please enter a remark !'); }
    }

    if (! $error) {
    $remark = addslashes($remark);
    $ae_minutes = $hours*60+$minutes;
//    $ae_minutes = ceil($ae_minutes / $phpgw->db->f("minperae"));

    $phpgw->db->query("insert into phpgw_p_hours (project_id,activity_id,entry_date,start_date,end_date,"
               . "remark,minutes,status,minperae,billperae,employee) values "
               . " ('$id','$activity','" . time() ."','$sdate','$date','$remark',"
               . "'$ae_minutes','$status','$minperae','$billperae','$employee')");

      } 
    }

    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Hours has been added !')); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',""); }

    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var('error','');
    $t->set_var('currency',$currency);
    }
    else {
    $t->set_var('error',lang('Please select your currency in preferences !'));
    }

    $t->set_var('actionurl',$phpgw->link('/projects/hours_addhour.php'));
    $t->set_var('lang_action',lang('Add project hours'));
	
    $hidden_vars = "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
        		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
        		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
        		. "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
        		. "<input type=\"hidden\" name=\"id\" value=\"$id\">";
        		
    $t->set_var('hidden_vars',$hidden_vars);

    $phpgw->db->query("SELECT num,title FROM phpgw_p_projects WHERE id = '" . $id . "'");
    if ($phpgw->db->next_record()) {
    $t->set_var('num',$phpgw->strip_html($phpgw->db->f("num")));
    $title  = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                                
    if (! $title)  $title  = "&nbsp;";
    $t->set_var('title',$title);
    $t->set_var('lang_num',lang('Project ID'));
    $t->set_var('lang_title',lang('Title'));
    }

    $t->set_var('lang_activity',lang('Activity'));

    $phpgw->db->query("SELECT activity_id,descr FROM phpgw_p_projectactivities,phpgw_p_activities"
                        . " WHERE project_id = '".$id."' AND phpgw_p_projectactivities.activity_id="
                        . "phpgw_p_activities.id");
        while ($phpgw->db->next_record()) {
           $activity_list .= "<option value=\"" . $phpgw->db->f("activity_id") . "\">"
	            . $phpgw->strip_html($phpgw->db->f("descr")) . "</option>";
        }
        
    $t->set_var('activity_list',$activity_list);

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
    $t->set_var('lang_start_date',lang('Start_date'));

    if (!$edate) {
        $emonth = 0;
        $eday = 0;
        $eyear = 0;
        }
    else {
        $emonth = date('m',$edate);
        $emonth = date('m',$edate);
        $eyear = date('Y',$edate);
        }

    $t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));
    $t->set_var('lang_end_date',lang('Date due'));

    $t->set_var('lang_remark',lang('Remark'));
    $t->set_var('remark',$remark);

    $t->set_var('lang_time',lang("Time"));
    $t->set_var('hours',$hours);
    $t->set_var('minutes',$minutes);

    $t->set_var('lang_status',lang('Status'));
    
    $status_list = "<option value=\"done\" selected>" . lang('Done') . "</option>\n"
           		. "<option value=\"open\">" . lang('Open') . "</option>\n";

    $t->set_var("status_list",$status_list);

    $t->set_var('lang_employee',lang('Employee'));
    
    $phpgw->db->query("SELECT account_id,account_firstname,account_lastname FROM phpgw_accounts where "
                        . "account_status != 'L' ORDER BY account_lastname,account_firstname asc");
        while ($phpgw->db->next_record()) {
           $employee_list .= "<option value=\"" . $phpgw->db->f("account_id") . "\"";
        if($phpgw->db->f("account_id")==$phpgw_info["user"]["account_id"])                                                                                                                   
            $employee_list .= " selected";                                                                                                                                                
        $employee_list .= ">"                                                                                                                                                             
                    . $phpgw->common->display_fullname($phpgw->db->f("account_id"),                                                                                                          
                      $phpgw->db->f("account_firstname"),                                                                                                                                    
                      $phpgw->db->f("account_lastname")) . "</option>";


/*                    . $selected_users[$phpgw->db->f("account_id")] . ">"
	            . $phpgw->common->display_fullname($phpgw->db->f("account_id"),
                      $phpgw->db->f("account_firstname"),
                      $phpgw->db->f("account_lastname")) . "</option>"; */
        }
        
    $t->set_var('employee_list',$employee_list);

    $t->set_var('lang_minperae',lang('Minutes per workunit'));
    $t->set_var('minperae',$minperae);
    $t->set_var('lang_billperae',lang("Bill per workunit"));
    $t->set_var('billperae',$billperae);

    $t->set_var('lang_done',lang('Done'));    
    $t->set_var('doneurl',$phpgw->link('/projects/hours_index.php',"sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));

    $t->set_var('lang_add',lang('Add'));
    $t->set_var('lang_reset',lang('Clear Form'));
        
    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','hours_add');
    $t->pparse('addhandle','add');

    $phpgw->common->phpgw_footer();
?>