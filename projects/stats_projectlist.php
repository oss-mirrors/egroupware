<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         *
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  *-------------------------------------------------                         *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

    $phpgw_info['flags'] = array('currentapp' => 'projects', 
		    'enable_nextmatchs_class' => True);

    include('../header.inc.php');

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('project_list_t' => 'stats_projectlist.tpl',
			 'project_list' => 'stats_projectlist.tpl'));
    $t->set_block('project_list_t','project_list','list');

    $projects = CreateObject('projects.projects');
    $grants = $phpgw->acl->get_grants('projects');

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
			. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
			. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
			. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
			. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";

    $t->set_var(lang_action,lang('Project statistics'));  
    $t->set_var(lang_userlist,lang('User statistics'));
    $t->set_var(userlisturl,$phpgw->link('/projects/stats_userlist.php'));  
    $t->set_var('hidden_vars',$hidden_vars);   
    $t->set_var('searchurl',$phpgw->link('/projects/stats_projectlist.php'));  

    if (! $start) { $start = 0; }

    if($phpgw_info["user"]["preferences"]["common"]["maxmatchs"] && $phpgw_info["user"]["preferences"]["common"]["maxmatchs"] > 0) {
                $limit = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
    }
    else { $limit = 15; }

    $pro = $projects->read_projects($start,$limit,$query,$filter,$sort,$order);

//---------------------- nextmatch variable template-declarations ---------------------------

    $left = $phpgw->nextmatchs->left('index.php',$start,$projects->total_records);
    $right = $phpgw->nextmatchs->right('index.php',$start,$projects->total_records);
    $t->set_var('left',$left);
    $t->set_var('right',$right);

    if ($projects->total_records > $limit) {
        $t->set_var('lang_showing',lang("showing x - x of x",($start + 1),($start + $limit),$projects->total_records));
    }
    else { $t->set_var('lang_showing',lang("showing x",$projects->total_records)); }

// ------------------------------ end nextmatch template ------------------------------------

// ------------------list header variable template-declarations ------------------------------- 

    $t->set_var(th_bg,$phpgw_info["theme"][th_bg]);
    $t->set_var(sort_num,$phpgw->nextmatchs->show_sort_order($sort,"num",$order,"stats_projectlist.php",lang("Project ID")));
    $t->set_var(sort_customer,$phpgw->nextmatchs->show_sort_order($sort,"customer",$order,"stats_projectlist.php",lang("Customer")));
    $t->set_var(sort_status,$phpgw->nextmatchs->show_sort_order($sort,"status",$order,"stats_projectlist.php",lang("Status")));
    $t->set_var(sort_title,$phpgw->nextmatchs->show_sort_order($sort,"title",$order,"stats_projectlist.php",lang("Title")));
    $t->set_var(sort_end_date,$phpgw->nextmatchs->show_sort_order($sort,"end_date",$order,"stats_projectlist.php",lang("Date due")));
    $t->set_var(sort_coordinator,$phpgw->nextmatchs->show_sort_order($sort,"coordinator",$order,"stats_projectlist.php",lang("Coordinator")));
    $t->set_var(h_lang_stat,lang("Statistic"));
    $t->set_var('lang_search',lang('Search'));

  // -------------- end header declaration -----------------

    $d = CreateObject('phpgwapi.contacts');

    for ($i=0;$i<count($pro);$i++) {    

    $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
    $title = $phpgw->strip_html($pro[$i]['title']);
        if (! $title)  $title  = '&nbsp;';

    $number = $phpgw->strip_html($pro[$i]['number']);
    $status = lang($pro[$i]['status']);
    $t->set_var(tr_color,$tr_color);

    $end_date = $pro[$i]['end_date'];
    if ($end_date == 0) { $end_dateout = '&nbsp;'; }
    else {
        $month = $phpgw->common->show_date(time(),'n');
        $day   = $phpgw->common->show_date(time(),'d');
        $year  = $phpgw->common->show_date(time(),'Y');

        $end_date = $end_date + (60*60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"];
        $end_dateout =  $phpgw->common->show_date($end_date,$phpgw_info["user"]["preferences"]["common"]["dateformat"]);
        if (mktime(2,0,0,$month,$day,$year) == $end_date) { $end_dateout = '<b>' . $end_dateout . '</b>'; }
        if (mktime(2,0,0,$month,$day,$year) >= $end_date) { $end_dateout = '<font color="CC0000"><b>' . $end_dateout . '</b></font>'; }
    }

    $ab_customer = $pro[$i]['customer'];
    if (!$ab_customer) { $customerout = '&nbsp;'; }
    else {
	$cols = array('n_given' => 'n_given',
    	             'n_family' => 'n_family',
        	     'org_name' => 'org_name');
	$customer = $d->read_single_entry($ab_customer,$cols);
        if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
	else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
    }
    $coordinatorout = $pro[$i]['lid'] . ' [ ' . $pro[$i]['firstname'] . ' ' . $pro[$i]['lastname'] . ' ]';

    $id = $pro[$i]['id'];

// ----------------- template declaration for list records -------------------------------

    $t->set_var(array('number' => $number,
                      'customer' => $customerout,
                      'status' => $status,
    		      'title' => $title,
      		      'end_date' => $end_dateout,
      		      'coordinator' => $coordinatorout));

    $t->set_var('stat',$phpgw->link('/projects/stats_projectstat.php',"id=$id"));
    $t->set_var('lang_stat',lang('Statistic'));

    $t->parse('list','project_list',True);

// --------------------------- end record declaration ------------------------------------
  
}
    $t->parse('out','project_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>