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

    $phpgw_info["flags"] = array("currentapp" => "projects", 
                               "enable_nextmatchs_class" => True);
    include("../header.inc.php");

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('hours_list_t' => 'hours_listhours.tpl'));
    $t->set_block('hours_list_t','hours_list','list');

    if ($phpgw_info["server"]["db_type"]=="pgsql") { $join = " JOIN "; } 
    else { $join = " LEFT JOIN "; }

    if (! $start) { $start = 0; }
    if ($order) { $ordermethod = "order by $order $sort"; } 
    else { $ordermethod = "order by phpgw_p_hours.start_date asc"; }

    $filtermethod = "employee='" . $phpgw_info["user"]["account_id"] . "' ";
    if($status) { $filtermethod .= " AND phpgw_p_hours.status='$status' "; }

    $querymethod = " (status like '%$query%' OR remark like '%$query%' OR start_date like '%$query%' OR end_date like '%$query%' OR minutes like '%$query%') ";

    if (! $filter) {
    if ($query) {
	$phpgw->db->query("SELECT project_id from phpgw_p_hours WHERE $filtermethod AND $querymethod");
	    if ($phpgw->db->next_record()) { 
	    $filter = $phpgw->db->f("project_id");
	    $project_select[$filter] = " selected";
	    }
    }
    else { $filter = "999"; }
    }

    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
        $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $limit = 15; }

    if ($query) {
	$phpgw->db->query("select count(*) from phpgw_p_hours WHERE project_id='$filter' AND $filtermethod AND $querymethod");
	$phpgw->db->next_record();
	if ($phpgw->db->f(0) == 1) { $t->set_var('lang_showing',lang('your search returned 1 match')); }
	else { $t->set_var('lang_showing',lang("your search returned x matchs",$phpgw->db->f(0))); } 
    }
    else {
    $phpgw->db->query("select count(*) from phpgw_p_hours WHERE project_id='$filter' AND $filtermethod");
    $phpgw->db->next_record();                                                                      
    if ($phpgw->db->f(0) > $limit) { $t->set_var('lang_showing',lang("showing x - x of x",($start + 1),($start + $limit),$phpgw->db->f(0))); }
    else { $t->set_var('lang_showing',lang("showing x",$phpgw->db->f(0))); }
    }
// ------------ nextmatch variable template-declarations ----------------------------

    $left = $phpgw->nextmatchs->left('/projects/hours_listhours.php',$start,$phpgw->db->f(0));
    $right = $phpgw->nextmatchs->right('/projects/hours_listhours.php',$start,$phpgw->db->f(0));
    $t->set_var('left',$left);
    $t->set_var('right',$right);

// ----------------------- end nextmatch template -------------------------------------

// ---------------- list header variable template-declarations ------------------------

    $t->set_var('th_bg',$phpgw_info["theme"][th_bg]);
    $t->set_var('sort_activity',$phpgw->nextmatchs->show_sort_order($sort,'phpgw_p_activities.descr',$order,'/projects/hours_listhours.php',lang('Activity')));
    $t->set_var('sort_remark',$phpgw->nextmatchs->show_sort_order($sort,'phpgw_p_hours.remark',$order,'/projects/hours_listhours.php',lang('Remark')));
    $t->set_var('sort_status',$phpgw->nextmatchs->show_sort_order($sort,'status',$order,'/projects/hours_listhours.php',lang("Status")));
    $t->set_var('sort_start_date',$phpgw->nextmatchs->show_sort_order($sort,'start_date',$order,'/projects/hours_listhours.php',lang('Start date')));
    $t->set_var('sort_end_date',$phpgw->nextmatchs->show_sort_order($sort,'end_date',$order,'/projects/hours_listhours.php',lang('Date due')));
    $t->set_var('sort_minutes',$phpgw->nextmatchs->show_sort_order($sort,'minutes',$order,'/projects/hours_listhours.php',lang('Time')));
    $t->set_var('h_lang_edit',lang('Edit'));
    $t->set_var('h_lang_view',lang('View'));
    $t->set_var('lang_action',lang('Job list'));
    $t->set_var('lang_search',lang('Search'));
    $t->set_var('search_action',$phpgw->link('/projects/hours_listhours.php'));
    $t->set_var('project_action',$phpgw->link('/projects/hours_listhours.php'));
    $t->set_var('lang_submit',lang('Submit'));
    $project_select[$filter] = " selected";
    $t->set_var('project_list',select_project_list($filter));
    $t->set_var('lang_select_project',lang('Select project'));
    $t->set_var('add_action',$phpgw->link('/projects/hours_addhour.php',"project_id=$filter"));

  // -------------- end header declaration -----------------

    if ($query) {
    $phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.remark,phpgw_p_activities.descr,phpgw_p_hours.status,"
		    . "phpgw_p_hours.start_date,phpgw_p_hours.end_date,phpgw_p_hours.minutes FROM phpgw_p_hours "
		    . "$join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id WHERE phpgw_p_hours.project_id='$filter' AND $filtermethod AND "
		    . "$querymethod $ordermethod limit $limit");
    } 
    else {
    $phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.remark,phpgw_p_activities.descr,phpgw_p_hours.status,"
		    . "phpgw_p_hours.start_date,phpgw_p_hours.end_date,phpgw_p_hours.minutes FROM phpgw_p_hours "
		    . "$join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id WHERE phpgw_p_hours.project_id='$filter' AND $filtermethod $ordermethod limit $limit");
    }

    while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    
    $activity  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                             
    if (! $activity)  $activity  = "&nbsp;";                                                                                                                                                

    $remark  = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                             
    if (! $remark)  $remark  = "&nbsp;";                                                                                                                                                

    $status = $phpgw->db->f("status");
    $statusout = lang($status);
    $t->set_var(tr_color,$tr_color);

    $start_date = $phpgw->db->f("start_date");
    if ($start_date == 0)
             $start_dateout = "&nbsp;";
    else {
	$month = $phpgw->common->show_date(time(),"n");
	$day   = $phpgw->common->show_date(time(),"d");
	$year  = $phpgw->common->show_date(time(),"Y");

	$start_date = $start_date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
	$start_dateout =  $phpgw->common->show_date($start_date,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }

    $end_date = $phpgw->db->f("end_date");
    if ($end_date == 0)
             $end_dateout = "&nbsp;";
    else {
	$month = $phpgw->common->show_date(time(),"n");
	$day   = $phpgw->common->show_date(time(),"d");
	$year  = $phpgw->common->show_date(time(),"Y");

	$end_date = $end_date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
	$end_dateout =  $phpgw->common->show_date($end_date,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) == $end_date) { $end_dateout = "<b>" . $end_dateout . "</b>"; }
        if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout = "<font color=\"CC0000\"><b>" . $end_dateout . "</b></font>"; }
    }
    
    $minutes = floor($phpgw->db->f("minutes")/60).":"
		. sprintf ("%02d",(int)($phpgw->db->f("minutes")-floor($phpgw->db->f("minutes")/60)*60));

    $id = $phpgw->db->f("id");

// ---------------- template declaration for list records ------------------------------

    $t->set_var(array('activity' =>$activity,
                      'remark' => $remark,
                      'status' => $statusout,
      		      'start_date' => $start_dateout,
			'end_date' => $end_dateout,
      		      'minutes' => $minutes));

    if ($status != "billed") {
    $t->set_var('edit',$phpgw->link('/projects/hours_edithour.php',"id=$id&filter=$filter&order=$order&query=$query&start=$start&sort=$sort"));
    $t->set_var('lang_edit',lang('Edit'));
    }
    else { 
    $t->set_var('edit','');
    $t->set_var('lang_edit','&nbsp;');
    }
    $t->set_var('view',$phpgw->link('/projects/viewhours.php',"id=$id&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    $t->set_var('lang_view',lang('View'));

    $t->parse('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------
    }

    $t->set_var('lang_add',lang('Add'));

    $t->parse('out','hours_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>