<?php
  /**************************************************************************\
  * phpGroupWare - projects/projecthours                                     *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [aeb@hansenet.de]                              *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -------------------------------------------------------                  *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "projects", 
                               "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $t = new Template($phpgw_info["server"]["app_tpl"]);
  $t->set_file(array( "projecthours_list_t" => "hours_listhours.tpl"));
  $t->set_block("projecthours_list_t", "projecthours_list", "list");

  $common_hidden_vars =
   "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
 . "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
 . "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
 . "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
 . "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
 . "<input type=\"hidden\" name=\"project_id\" value=\"$project_id\">\n";

  $t->set_var(common_hidden_vars,$common_hidden_vars); 
  $t->set_var("lang_action",lang("List project hours"));   

  if (! $start)
     $start = 0;
  if ($order)
     $ordermethod = "order by $order $sort";
  else
     $ordermethod = "order by date asc";

  $filtermethod = "employee='" . $phpgw_info["user"]["account_id"] . "' ";

  if (!$filter)
     $filter = "none";
 
  if ($project_id) {
     if ($filter=="none")
        $filter = "project_id=$project_id";
     }

    if($filter != "private") {
      if($filter<>"none")     
         $filtermethod .= " AND $filter ";
         }
  
   if($status)
     $filtermethod .= " AND status='$status' ";

  if ($query) {
     $phpgw->db->query("select count(*) from p_hours where $filtermethod");
     $phpgw->db->next_record();
     if ($phpgw->db->f(0) == 1)
        $t->set_var(total_matchs,lang("your search returned 1 match"));
     else
        $t->set_var(total_matchs,lang("your search returned x matchs",$phpgw->db->f(0)));
       } 
    else {
     $phpgw->db->query("select count(*) from p_hours where $filtermethod");
     $phpgw->db->next_record();                                                                      
     if ($phpgw->db->f(0) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"])
     $total_matchs = "<br>" . lang("showing x - x of x",($start + 1),
                           ($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),
                           $phpgw->db->f(0));
     else
     $total_matchs = "<br>" . lang("showing x",$phpgw->db->f(0));
     $t->set_var(total_matchs,$total_matchs);                                                                                                               
      }

// ------------ nextmatch variable template-declarations ----------------------------

     $next_matchs = $phpgw->nextmatchs->show_tpl("hours_listhours.php",$start,$phpgw->db->f(0),
                   "&order=$order&filter=$filter&sort="
                 . "$sort&query=$query","85%",$phpgw_info["theme"][th_bg]);
     $t->set_var(next_matchs,$next_matchs);

// ----------------------- end nextmatch template -------------------------------------

  // ===========================================
  // list header variable template-declarations
  // ===========================================

  $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
  $t->set_var(sort_activity,$phpgw->nextmatchs->show_sort_order($sort,"p_activities.descr",$order,"hours_listhours.php",lang("Activity"),"&project_id=$project_id&status=$status"));
  $t->set_var(sort_remark,$phpgw->nextmatchs->show_sort_order($sort,"p_hours.remark",$order,"hours_listhours.php",lang("Remark"),"&project_id=$project_id&status=$status"));
  $t->set_var(sort_status,$phpgw->nextmatchs->show_sort_order($sort,"status",$order,"hours_listhours.php",lang("Status"),"&project_id=$project_id&status=$status"));
  $t->set_var(sort_date,$phpgw->nextmatchs->show_sort_order($sort,"date",$order,"hours_listhours.php",lang("Date"),"&project_id=$project_id&status=$status"));
  $t->set_var(sort_end_date,$phpgw->nextmatchs->show_sort_order($sort,"end_date",$order,"hours_listhours.php",lang("Date due"),"&project_id=$project_id&status=$status"));
  $t->set_var(sort_minutes,$phpgw->nextmatchs->show_sort_order($sort,"minutes",$order,"hours_listhours.php",lang("Time"),"&project_id=$project_id&status=$status"));
  $t->set_var(h_lang_edithour,lang("Edit hours"));
  $t->set_var(h_lang_deletehour,lang("Delete hours"));             

  // -------------- end header declaration -----------------

    $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
//  $limit = $phpgw->db->limit($start);

  if ($query) {
     $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark as remark,p_activities.descr as descr,status,"
                 . "date,end_date,minutes FROM p_activities,p_hours WHERE $filtermethod AND "
                 . "p_hours.activity_id=p_activities.id AND "
                 . "descr like '%$query%' OR remark like '%$query%' $ordermethod limit $limit");
  } else {
     $phpgw->db->query("SELECT p_hours.id as id,p_hours.remark,p_activities.descr,status,date,"
                 . "end_date,minutes FROM p_activities,p_hours WHERE $filtermethod AND "
                 . "p_hours.activity_id=p_activities.id "
                 . " $ordermethod limit $limit");
  }

  while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    
     $activity  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                             
     if (! $activity)  $activity  = "&nbsp;";                                                                                                                                                

    $remark  = $phpgw->strip_html($phpgw->db->f("remark"));                                                                                                                             
    if (! $remark)  $remark  = "&nbsp;";                                                                                                                                                

    $status = lang($phpgw->db->f("status"));
    $t->set_var(tr_color,$tr_color);

    if ($phpgw->db->f("date") == 0)
             $end_dateout = "&nbsp;";
    else {
      $month = $phpgw->common->show_date(time(),"n");
      $day   = $phpgw->common->show_date(time(),"d");
      $year  = $phpgw->common->show_date(time(),"Y");

      $date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
      $dateout =  $phpgw->common->show_date($phpgw->db->f("date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }

    if ($phpgw->db->f("end_date") == 0)
             $end_dateout = "&nbsp;";
    else {
      $month = $phpgw->common->show_date(time(),"n");
      $day   = $phpgw->common->show_date(time(),"d");
      $year  = $phpgw->common->show_date(time(),"Y");

      $end_date = (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("end_date"))
        	$end_dateout =  "<font color=\"CC0000\">";

        $end_dateout =  $phpgw->common->show_date($phpgw->db->f("end_date"),$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) >= $phpgw->db->f("end_date"))
                $end_dateout .= "</font>";
    }
    $minutes = floor($phpgw->db->f("minutes")/60).":"
		. sprintf ("%02d",(int)($phpgw->db->f("minutes")-floor($phpgw->db->f("minutes")/60)*60));

    if($phpgw->db->f("status")=="open")
	$deleteurl = "<a href=\"". $phpgw->link("hours_deletehour.php","id=" . $phpgw->db->f("id") 
                                         . "&sort=$sort&order=$order&"
                                         . "query=$query&start=$start&filter="
                                         . "$filter&status=$status")
                                 . "\">". lang("Delete hours") . "</a>";
    else 
	$deleteurl = "";
    if(($phpgw->db->f("status")=="open") or ($phpgw->db->f("status")=="done") or ($isadmin==1))
        $editurl = "<a href=\"". $phpgw->link("hours_edithour.php","id=" . $phpgw->db->f("id")
	                                . "&sort=$sort&order=$order&"
					. "query=$query&start=$start&filter="
					. "$filter&status=$status")
			 	. "\">". lang("Edit hours") . "</a>";		
    else
       $editurl = "";
    
// ---------------- template declaration for list records ------------------------------

    $t->set_var(array("activity" =>$activity,
                      "remark" => $remark,
                      "status" => $status,
    		      "date" => $dateout,
      		      "end_date" => $end_dateout,
      		      "minutes" => $minutes,
      		      "edithour" => $editurl, 
                      "deletehour" => $deleteurl));
    $t->parse("list", "projecthours_list", true);

// --------------------------- end record declaration -----------------------------------
  }

    $t->parse("out", "projecthours_list_t", true);
    $t->p("out");

$phpgw->common->phpgw_footer();
?>