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

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
		. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
		. "<input type=\"hidden\" name=\"project_id\" value=\"$project_id\">\n";

    $t->set_var(hidden_vars,$hidden_vars); 
    $t->set_var('lang_action',lang('List project hours'));   
    $t->set_var('lang_search',lang('Search'));
    $t->set_var('searchurl',$phpgw->link("/projects/hours_listhours.php"));

    if ($phpgw_info["server"]["db_type"]=="pgsql") { $join = " JOIN "; }
    else { $join = " LEFT JOIN "; }

    if (! $start) { $start = 0; }
    if ($order) { $ordermethod = "order by $order $sort"; }
    else { $ordermethod = "order by phpgw_p_hours.end_date asc"; }

    $filtermethod = "employee='" . $phpgw_info["user"]["account_id"] . "' ";
    
    if (!$filter) { $filter = "none"; }
  
    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
        $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $limit = 15; }

    if ($project_id) {
	if ($filter=="none")
	$filter = "project_id=$project_id";
    }

    if($filter != "private") {
	if($filter<>"none")     
	$filtermethod .= " AND $filter ";
    } 
    
    if($status) { $filtermethod .= " AND phpgw_p_hours.status='$status' "; }

    if ($query) {
	$phpgw->db->query("select count(*) from phpgw_p_hours WHERE $filtermethod");
	$phpgw->db->next_record();
	if ($phpgw->db->f(0) == 1) { $t->set_var('lang_showing',lang('your search returned 1 match')); }
	else { $t->set_var('lang_showing',lang("your search returned x matchs",$phpgw->db->f(0))); } 
    }
    else {
    $phpgw->db->query("select count(*) from phpgw_p_hours WHERE $filtermethod");
    $phpgw->db->next_record();                                                                      
    if ($phpgw->db->f(0) > $limit) { $t->set_var('lang_showing',lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0))); }
    else { $t->set_var('lang_showing',lang("showing x",$phpgw->db->f(0))); }
    }
// ------------ nextmatch variable template-declarations ----------------------------

    $left = $phpgw->nextmatchs->left('hours_listhours.php',$start,$phpgw->db->f(0));
    $right = $phpgw->nextmatchs->right('hours_listhours.php',$start,$phpgw->db->f(0));
    $t->set_var('left',$left);
    $t->set_var('right',$right);

// ----------------------- end nextmatch template -------------------------------------

// ---------------- list header variable template-declarations ------------------------

  $t->set_var('th_bg',$phpgw_info["theme"][th_bg]);
  $t->set_var('sort_project',$phpgw->nextmatchs->show_sort_order($sort,'phpgw_p_projects.num',$order,'hours_listhours.php',lang('Project ID'),"&project_id=$project_id&status=$status"));
  $t->set_var('sort_activity',$phpgw->nextmatchs->show_sort_order($sort,'phpgw_p_activities.descr',$order,'hours_listhours.php',lang('Activity'),"&project_id=$project_id&status=$status"));
  $t->set_var('sort_remark',$phpgw->nextmatchs->show_sort_order($sort,'phpgw_p_hours.remark',$order,'hours_listhours.php',lang('Remark'),"&project_id=$project_id&status=$status"));
  $t->set_var('sort_status',$phpgw->nextmatchs->show_sort_order($sort,'status',$order,"hours_listhours.php",lang("Status"),"&project_id=$project_id&status=$status"));
  $t->set_var('sort_end_date',$phpgw->nextmatchs->show_sort_order($sort,'end_date',$order,'hours_listhours.php',lang('Date due'),"&project_id=$project_id&status=$status"));
  $t->set_var('sort_minutes',$phpgw->nextmatchs->show_sort_order($sort,'minutes',$order,'hours_listhours.php',lang('Time'),"&project_id=$project_id&status=$status"));
  $t->set_var('h_lang_edit',lang('Edit'));
  $t->set_var('h_lang_view',lang('View'));             

  // -------------- end header declaration -----------------

    if ($query) {
    $phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.remark,phpgw_p_activities.descr,phpgw_p_hours.status,"
		    . "phpgw_p_hours.end_date,phpgw_p_hours.minutes,phpgw_p_projects.num FROM phpgw_p_hours $join phpgw_p_projects ON phpgw_p_projects.id=phpgw_p_hours.project_id "
		    . "$join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id WHERE $filtermethod AND "
		    . "(descr like '%$query%' OR remark like '%$query%') $ordermethod limit $limit");
    } 
    else {
    $phpgw->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.remark,phpgw_p_activities.descr,phpgw_p_hours.status,"
		    . "phpgw_p_hours.end_date,phpgw_p_hours.minutes,phpgw_p_projects.num FROM phpgw_p_hours $join phpgw_p_projects ON phpgw_p_projects.id=phpgw_p_hours.project_id "
		    . "$join phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id WHERE $filtermethod $ordermethod limit $limit");
    }

    while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    
    $project = $phpgw->db->f("num");

    $activity  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                             
    if (! $activity)  $activity  = "&nbsp;";                                                                                                                                                

    $remark  = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                             
    if (! $remark)  $remark  = "&nbsp;";                                                                                                                                                

    $status = lang($phpgw->db->f("status"));
    $t->set_var(tr_color,$tr_color);

    $end_date = $phpgw->db->f("end_date");
    if ($end_date == 0)
             $end_dateout = "&nbsp;";
    else {
	$month = $phpgw->common->show_date(time(),"n");
	$day   = $phpgw->common->show_date(time(),"d");
	$year  = $phpgw->common->show_date(time(),"Y");

	$end__date = $end_date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
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
                      'status' => $status,
      		      'end_date' => $end_dateout,
      		      'minutes' => $minutes,
		      'project' => $project));

    if ($status != "billed") {
    $t->set_var('edit',$phpgw->link('/projects/hours_edithour.php','id=' . $phpgw->db->f("id") 
				    . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    $t->set_var('lang_edit',lang('Edit'));
    }
    else { 
    $t->set_var('edit','');
    $t->set_var('lang_edit','');
    }
    $t->set_var('view',$phpgw->link('/projects/viewhours.php',"id=$id&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
    $t->set_var('lang_view',lang('View'));

    $t->parse('list','hours_list',True);

// --------------------------- end record declaration -----------------------------------
    }

    $t->parse('out','hours_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>