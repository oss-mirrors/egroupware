<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectdelivery                                  *
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
    $t->set_file(array('projects_list_t' => 'del_listdelivery.tpl'));
    $t->set_block('projects_list_t','projects_list','list');

    $d = CreateObject('phpgwapi.contacts');
    $t->set_var('lang_action',lang('Delivery list'));
    $t->set_var('lang_search',lang('Search'));  
    $t->set_var('searchurl',$phpgw->link("/projects/del_deliverylist.php"));

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
			. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
			. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
			. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
			. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

    $t->set_var('hidden_vars',$hidden_vars);   

    if (! $start) { $start = 0; }
    if ($order) { $ordermethod = " order by $order $sort"; }
    else { $ordermethod = " order by date asc"; }

    if (! $filter) { $filter = "none"; }

    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
                $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $limit = 15; }

    if ($query) {
    $querymethod = " (phpgw_p_delivery.num like '%$query%' OR phpgw_p_delivery.date like '%$query%') ";
	if ($project_id) { $phpgw->db->query("SELECT count(*) from phpgw_p_delivery WHERE $querymethod AND project_id=$project_id"); }
	else { $phpgw->db->query("SELECT count(*) from phpgw_p_delivery WHERE $querymethod"); }
	$phpgw->db->next_record();
	if ($phpgw->db->f(0) == 1) { $t->set_var('lang_showing',lang('your search returned 1 match')); }
	else { $t->set_var('lang_showing',lang("your search returned x matchs",$phpgw->db->f(0))); }
    } 
    else {
        if ($project_id) { $phpgw->db->query("SELECT count(*) from phpgw_p_delivery WHERE project_id=$project_id"); }
	else { $phpgw->db->query("SELECT count(*) from phpgw_p_delivery"); }
	$phpgw->db->next_record();
	if ($phpgw->db->f(0) > $limit) { $t->set_var('lang_showing',lang("showing x - x of x",($start + 1),($start + $limit),$phpgw->db->f(0))); }
	else { $t->set_var('lang_showing',lang("showing x",$phpgw->db->f(0))); }
    }

// -------------------- nextmatch variable template-declarations -----------------------------

    $left = $phpgw->nextmatchs->left('del_deliverylist.php',$start,$phpgw->db->f(0));
    $right = $phpgw->nextmatchs->right('del_deliverylist.php',$start,$phpgw->db->f(0));
    $t->set_var('left',$left);
    $t->set_var('right',$right);

// ------------------------ end nextmatch template -------------------------------------------

// ---------------- list header variable template-declarations -------------------------------

    $t->set_var('th_bg',$phpgw_info["theme"][th_bg]);
    $t->set_var('sort_num',$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"del_deliverylist.php",lang("Delivery ID")));
    $t->set_var('sort_customer',$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"del_deliverylist.php",lang("Customer")));
    $t->set_var('sort_title',$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"del_deliverylist.php",lang("Title")));
    $t->set_var('sort_date',$phpgw->nextmatchs->show_sort_order($sort,"date",$order,"del_deliverylist.php",lang("Date")));
    $t->set_var('h_lang_delivery',lang("Delivery"));

  // -------------- end header declaration -----------------

    if ($project_id) {
	$sql = "SELECT phpgw_p_delivery.id as id,phpgw_p_delivery.num,title,phpgw_p_delivery.date,"
	    . "phpgw_p_delivery.project_id as pid,phpgw_p_delivery.customer "
	    . "FROM phpgw_p_delivery,phpgw_p_projects WHERE phpgw_p_delivery.project_id='$project_id' "
	    . "AND phpgw_p_delivery.project_id=phpgw_p_projects.id";

	$phpgw->db->query($sql . "$ordermethod limit $limit",__LINE__,__FILE__);

	if ($query) { $phpgw->db->query($sql . " AND $querymethod $ordermethod limit $limit",__LINE__,__FILE__); }
    }
    else {
	$sql = "SELECT phpgw_p_delivery.id as id,phpgw_p_delivery.num,title,phpgw_p_delivery.date,"
			    . "phpgw_p_delivery.project_id as pid,phpgw_p_delivery.customer "
			    . "FROM phpgw_p_delivery,phpgw_p_projects WHERE "
			    . "phpgw_p_delivery.project_id=phpgw_p_projects.id";

        $phpgw->db->query($sql . "$ordermethod limit $limit",__LINE__,__FILE__);

        if ($query) { $phpgw->db->query($sql . " AND $querymethod $ordermethod limit $limit",__LINE__,__FILE__); }
    }

    while ($phpgw->db->next_record()) {
    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

    $title = $phpgw->strip_html($phpgw->db->f("title"));                                                                                                                               
    if (! $title)  $title  = "&nbsp;";
    $t->set_var(tr_color,$tr_color);

    $date = $phpgw->db->f("date");
    if ($date == 0)
             $dateout = "&nbsp;";
    else {
	$month = $phpgw->common->show_date(time(),"n");
	$day   = $phpgw->common->show_date(time(),"d");
	$year  = $phpgw->common->show_date(time(),"Y");

	$date = $date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        $dateout =  $phpgw->common->show_date($date,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
    }

    $ab_customer = $phpgw->db->f("customer");
    $cols = array('n_given' => 'n_given',
                 'n_family' => 'n_family',
                 'org_name' => 'org_name');
    $customer = $d->read_single_entry($ab_customer,$cols);
    $customerout = $customer[0]['org_name'] . " [ " . $customer[0]['n_given'] . " " . $customer[0]['n_family'] . " ]";

// ------------------ template declaration for list records ----------------------------------

    $t->set_var(array("num" => $phpgw->strip_html($phpgw->db->f("num")),
                      "customer" => $customerout,
    		      "title" => $title,
      		      "date" => $dateout));

    $t->set_var('delivery',$phpgw->link("/projects/delivery_update.php","delivery_id=" . $phpgw->db->f("id")
                                        . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter&project_id="
                                        . $phpgw->db->f("pid") . "&delivery_num=" . $phpgw->db->f("num")));
    $t->set_var('lang_delivery',lang('Delivery'));

    $t->parse("list", "projects_list", true);

// ------------------------ end record declaration --------------------------------------------
  }
    $t->parse('out','projects_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>
