<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
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
  
    $phpgw_info['flags'] = array('currentapp' => 'projects',
				'enable_nextmatchs_class' => True);
    include('../header.inc.php');

    $db2 = $phpgw->db;

    if (! $account_id) {
	Header('Location: ' . $phpgw->link('/projects/stats_userlist.php',"sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    }

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
		. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
		. "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

    $phpgw->db->query("select * from phpgw_accounts where account_id = '$account_id'");
    $phpgw->db->next_record();

    $t = new Template(PHPGW_APP_TPL);
    $t->set_file(array('projects_stat' => 'stats_userstat.tpl'));
    $t->set_block('projects_stat','stat_list','list');

    $t->set_var('actionurl',$phpgw->link('/projects/stats_userstat.php','account_id=' . $phpgw->db->f('account_id')));
    $t->set_var('lang_action',lang('User statistic'));
    $t->set_var('hidden_vars',$hidden_vars);
    $t->set_var('lang_lid',lang('Username'));
    $t->set_var('lid',$phpgw->strip_html($phpgw->db->f('account_lid')));
    $t->set_var('lang_firstname',lang('Firstname'));
    $t->set_var('firstname',$phpgw->strip_html($phpgw->db->f('account_firstname')));
    $t->set_var('lang_lastname',lang('Lastname'));                                                                                                                
    $t->set_var('lastname',$phpgw->strip_html($phpgw->db->f('account_lastname')));
    $t->set_var('th_bg',$phpgw_info['theme']['th_bg']);

    $t->set_var('lang_start_date',lang('Start date'));
    $t->set_var('lang_end_date',lang('Date due'));

    $sm = CreateObject('phpgwapi.sbox');

    if (!$submit) {
        $emonth = date('m',time());
        $eday = date('d',time());
        $eyear = date('Y',time());
        $edate = mktime(2,0,0,$emonth,$eday,$eyear);
    }

    if (!$sdate) {
        $smonth = 0;
        $sday = 0;
        $syear = 0;
        }
    else {
        $smonth = date('m',$sdate);
        $sday = date('d',$sdate);
        $syear = date('Y',$sdate);
        }

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

    $t->set_var('start_date_select',$phpgw->common->dateformatorder($sm->getYears('syear',$syear),$sm->getMonthText('smonth',$smonth),$sm->getDays('sday',$sday)));
    $t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));

    if($billed) { $t->set_var('billed','checked'); }
    $t->set_var('billedonly',lang('Billed only'));


// -------------- calculate statistics --------------------------

    if($billed) { $filter .= " AND p_hours.status='billed' "; }                                                                                                           

    if (checkdate($smonth,$sday,$syear)) {                                                                                                               
        $sdate = mktime(2,0,0,$smonth,$sday,$syear);                                                                                                     
        $filter .= " AND phpgw_p_hours.start_date>='$sdate' ";                                                                                                          
    }                                                                                                                                                       
      
    if (checkdate($emonth,$eday,$eyear)) {
        $edate = mktime(2,0,0,$emonth,$eday,$eyear);
        $filter .= " AND phpgw_p_hours.end_date<='$edate' ";
    }

    $phpgw->db->query("SELECT title,phpgw_p_projects.id FROM phpgw_p_hours,phpgw_p_projects WHERE project_id=phpgw_p_projects.id "                                                  
                ."AND phpgw_p_hours.employee='$account_id' $filter GROUP BY project_id");

    $t->set_var("hd_project",lang("Project"));
    $t->set_var("hd_activity",lang("Activity"));
    $t->set_var("hd_hours",lang("Hours"));

    while ($phpgw->db->next_record()) {
        $summin = 0;
        $t->set_var("e_project",$phpgw->db->f("title"));
        $t->set_var("e_activity","");
        $t->set_var("e_hours","");
        $t->parse("list","stat_list",true);

    $db2->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE "
                        . "employee='$account_id' AND project_id='".$phpgw->db->f("id")."' AND "
                        . "phpgw_p_hours.activity_id=phpgw_p_activities.id $filter GROUP BY phpgw_p_hours.activity_id");

    while ($db2->next_record()) {
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var(tr_color,$tr_color);
        $t->set_var("e_project","");
        $t->set_var("e_activity",$db2->f("descr"));
        $summin += $db2->f("min");
        $hrs = floor($db2->f("min")/60).":"
                . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));
        $t->set_var("e_hours",$hrs);
        $t->parse("list","stat_list",true);
    }

    $t->set_var("e_project","");
    $t->set_var("e_activity","");
    $hrs = floor($summin/60).":"
                . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
    $t->set_var("e_hours",$hrs);
    $t->parse("list","stat_list",true);
    }

    $db2->query("SELECT SUM(minutes) as min,descr FROM phpgw_p_hours,phpgw_p_activities WHERE "
		. "employee='$account_id' AND "
                . "phpgw_p_hours.activity_id=phpgw_p_activities.id $filter GROUP BY phpgw_p_hours.activity_id");$t->set_var("lang_calcb",lang("Calculate"));


    $summin=0;
    $t->set_var("e_project",lang("Overall"));
    $t->set_var("e_activity","");
    $t->set_var("e_hours","");
    $t->parse("list","stat_list",true);

    while ($db2->next_record()) {
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var(tr_color,$tr_color);
        $t->set_var("e_project","");
        $t->set_var("e_activity",$db2->f("descr"));
        $summin += $db2->f("min");
        $hrs = floor($db2->f("min")/60).":"
        . sprintf ("%02d",(int)($db2->f("min")-floor($db2->f("min")/60)*60));
	$t->set_var("e_hours",$hrs);
	$t->parse("list","stat_list",true);
    }

    $t->set_var("e_project",lang("sum"));
    $t->set_var("e_activity","");
    $hrs = floor($summin/60).":"
        . sprintf ("%02d",(int)($summin-floor($summin/60)*60));
    $t->set_var("e_hours",$hrs);

    $t->parse("list","stat_list",true);
    $t->pparse("out","projects_stat");
    $phpgw->common->phpgw_footer();
?>