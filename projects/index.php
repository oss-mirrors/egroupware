<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
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
    $t->set_file(array('projects_list' => 'list.tpl',
		     'projects_list_t' => 'list.tpl'));
    $t->set_block('projects_list_t','projects_list','list');

    $d = CreateObject('phpgwapi.contacts');

    $common_hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
			. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
			. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
			. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
			. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

    $t->set_var('lang_action',lang('Projects'));
    $t->set_var('addurl',$phpgw->link("add.php"));
    $t->set_var('lang_activities',lang('Activities list'));
    $t->set_var('activitiesurl',$phpgw->link("activities.php"));
    $t->set_var('searchurl',$phpgw->link("index.php"));
    $t->set_var('common_hidden_vars',$common_hidden_vars);   

    if (! $start) { $start = 0; }

    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
                $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $limit = 15; }

    $projects = read_projects($start,$limit,$query,$filter,$sort,$order);

//---------------------- nextmatch variable template-declarations ---------------------------

    $left = $phpgw->nextmatchs->left('index.php',$start,$total_records);
    $right = $phpgw->nextmatchs->right('index.php',$start,$total_records);
    $t->set_var('left',$left);
    $t->set_var('right',$right);

    if ($total_records > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
	$lang_showing=lang("showing x - x of x",($start + 1),($start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]),$total_records);
    }
    else { $lang_showing=lang("showing x",$total_records); }
    $t->set_var('lang_showing',$lang_showing);

// ------------------------------ end nextmatch template ------------------------------------

// ------------------list header variable template-declarations -------------------------------

    $t->set_var('th_bg',$phpgw_info["theme"][th_bg]);
    $t->set_var('sort_number',$phpgw->nextmatchs->show_sort_order($sort,'num',$order,'index.php',lang('Project ID')));
    $t->set_var('sort_customer',$phpgw->nextmatchs->show_sort_order($sort,'customer',$order,'index.php',lang('Customer')));
    $t->set_var('sort_status',$phpgw->nextmatchs->show_sort_order($sort,'status',$order,'index.php',lang('Status')));
    $t->set_var('sort_title',$phpgw->nextmatchs->show_sort_order($sort,'title',$order,'index.php',lang('Title')));
    $t->set_var('sort_end_date',$phpgw->nextmatchs->show_sort_order($sort,'end_date',$order,'index.php',lang('Date due')));
    $t->set_var('sort_coordinator',$phpgw->nextmatchs->show_sort_order($sort,'coordinator',$order,'index.php',lang('Coordinator')));
    $t->set_var('lang_edit',lang('Edit'));
    $t->set_var('lang_delete',lang('Delete'));
    $t->set_var('lang_search',lang('Search'));             

  // -------------- end header declaration -----------------

    for ($i=0;$i<count($projects);$i++) {    

    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $title = $phpgw->strip_html($projects[$i]['title']);                                                                                                                                   
    if (! $title)  $title  = "&nbsp;";

    $number = $phpgw->strip_html($projects[$i]['number']);
    $status = lang($projects[$i]['status']);
    $t->set_var('tr_color',$tr_color);

    $end_date = $projects[$i]['end_date'];
    if ($end_date == 0) { $end_dateout = "&nbsp;"; }
    else {
	$month = $phpgw->common->show_date(time(),"n");
	$day   = $phpgw->common->show_date(time(),"d");
	$year  = $phpgw->common->show_date(time(),"Y");

	$end_date = $end_date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
	if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout =  "<font color=\"CC0000\">"; }

	$end_dateout =  $phpgw->common->show_date($end_date,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout .= "</font>"; }
    }

    $ab_customer = $projects[$i]['customer'];
    $cols = array('n_given' => 'n_given',
		 'n_family' => 'n_family',
		 'org_name' => 'org_name');
    $customer = $d->read_single_entry($ab_customer,$cols);    
    $customerout = $customer[0]['org_name'] . " [ " . $customer[0]['n_given'] . " " . $customer[0]['n_family'] . " ]";

    $coordinatorout = $projects[$i]['lid'] . " [ " . $projects[$i]['firstname'] . " " . $projects[$i]['lastname'] . " ]";
      
    $edit = $phpgw->common->check_owner($projects[$i]['coordinator'],'edit.php',lang('Edit'),'id=' . $projects[$i]['id']
                                         . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter");
    $delete = $phpgw->common->check_owner($projects[$i]['coordinator'],'delete.php',lang('Delete'),'id=' . $projects[$i]['id']
                                         . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter");
    
// ------------------ template declaration for list records -----------------------------------
      
    $t->set_var(array('number' => $number,
		    'customer' => $customerout,
                      'status' => $status,
		       'title' => $title,
		    'end_date' => $end_dateout,
		 'coordinator' => $coordinatorout,
			'edit' => $edit,
		      'delete' => $delete));
       
    $t->parse('list','projects_list',True);

// ------------------------- end record declaration -------------------------------------------
  
    }

// ------------------ template declaration for Add Form ---------------------------------------

    $t->set_var('lang_add',lang('Add'));
    $t->parse('out','projects_list_t',True);
    $t->p('out');
       
// ---------------------- end Add form declaration --------------------------------------------

    $phpgw->common->phpgw_footer();
?>
