<?php
  /**************************************************************************\
  * phpGroupWare - projects/projectstatistics                                *
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

    $phpgw_info["flags"] = array('currentapp' => 'projects', 
                               'enable_nextmatchs_class' => True);
    include('../header.inc.php');

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('user_list_t' => 'stats_userlist.tpl'));
    $t->set_block('user_list_t','user_list','list');

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
		. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
		. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
		. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
		. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n";


    $t->set_var('lang_action',lang('User statistics'));
    $t->set_var('hidden_vars',$hidden_vars);   
    $t->set_var('lang_search',lang('Search'));
    $t->set_var('searchurl',$phpgw->link('/projects/stats_userlist.php'));

    if (! $start) { $start = 0; }
    if ($order) { $ordermethod = " order by $order $sort"; }
    else { $ordermethod = " order by account_lid asc"; }

    if($phpgw_info['user']['preferences']['common']['maxmatchs'] && $phpgw_info['user']['preferences']['common']['maxmatchs'] > 0) {
                $limit = $phpgw_info['user']['preferences']['common']['maxmatchs'];
    }
    else { $limit = 15; }

    if ($query) {
	$filtermethod = " AND (account_firstname like '%$query%' OR account_lastname like '%$query%' OR account_lid like '%$query%') ";
    }

    $db2 = $phpgw->db;

    $sql = "SELECT account_id,account_lid,account_firstname,account_lastname FROM phpgw_accounts WHERE account_type='u'"
	  . "$filtermethod $ordermethod";

    $db2->query($sql,__LINE__,__FILE__);
    $total_records = $db2->num_rows();
    $phpgw->db->query($sql . ' ' . $phpgw->db->limit($start,$limit),__LINE__,__FILE__);
    
    while ($phpgw->db->next_record()) {

	$accounts[] = Array('id' => $phpgw->db->f('account_id'),
			'firstname' => $phpgw->db->f('account_firstname'),
			'lastname' => $phpgw->db->f('account_lastname'),
			'lid' => $phpgw->db->f('account_lid'));

    }

// ------------- nextmatch variable template-declarations -------------------------------

    $left = $phpgw->nextmatchs->left('/projects/stats_userlist.php',$start,$total_records);
    $right = $phpgw->nextmatchs->right('/projects/stats_userlist.php',$start,$total_records);
    $t->set_var('left',$left);
    $t->set_var('right',$right);

    if ($total_records > $limit) {
	$t->set_var('lang_showing',lang("showing x - x of x",($start + 1),($start + $limit),$total_records));
    }
    else {
	$t->set_var('lang_showing',lang("showing x",$total_records));
    }

// ------------------------ end nextmatch template --------------------------------------

// --------------- list header variable template-declarations ---------------------------

    $t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
    $t->set_var('sort_lid',$phpgw->nextmatchs->show_sort_order($sort,'account_lid',$order,'/projects/stats_userlist.php',lang('Username')));
    $t->set_var('sort_firstname',$phpgw->nextmatchs->show_sort_order($sort,'account_firstname',$order,'/projects/stats_userlist.php',lang('Firstname')));
    $t->set_var('sort_lastname',$phpgw->nextmatchs->show_sort_order($sort,'account_lastname',$order,'/projects/stats_userlist.php',lang('Lastname')));
    $t->set_var('lang_stat',lang('Statistic'));

// ------------------------- end header declaration -------------------------------------

    for ($i=0;$i<count($accounts);$i++) {

	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);

// --------------------- template declaration for list records ---------------------------

	$firstname = $accounts[$i]['firstname'];
	if (!$firstname) { $firstname = '&nbsp;'; }
	$lastname = $accounts[$i]['lastname'];
	if (!$lastname) { $lastname = '&nbsp;'; }

	$t->set_var(array('lid' => $accounts[$i]['lid'],
                      'firstname' => $firstname,
                      'lastname' => $lastname));
	$t->set_var('stat',$phpgw->link('/projects/stats_userstat.php','account_id=' . $accounts[$i]['id']
                                         . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));

	$t->parse('list','user_list',True);
    }

// ------------------------------- end record declaration ---------------------------------

    $t->parse('out','user_list_t',True);
    $t->p('out');

    $phpgw->common->phpgw_footer();
?>