<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* ------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects', 
					'enable_nextmatchs_class' => True);
	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('activities_list_t' => 'listactivities.tpl',
						'activities_list' => 'listactivities.tpl'));
	$t->set_block('activities_list_t','activities_list','list');

	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
	}
	else
	{
		$t->set_var('error',lang('Please set your preferences for this application'));
	}

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n";

	$t->set_var('lang_action',lang('Activities list'));
	$t->set_var('actionurl',$phpgw->link('/projects/addactivity.php'));
	$t->set_var('lang_projects',lang('Project list'));
	$t->set_var('projectsurl',$phpgw->link('/projects/index.php'));
	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('lang_search',lang('Search'));
	$t->set_var('searchurl',$phpgw->link('/projects/activities.php'));

	if (! $start)
	{
		$start = 0;
	}

	if ($order)
	{
		$ordermethod = " order by $order $sort";
	}
	else
	{
		$ordermethod = " order by num asc";
	}

	if (! $filter)
	{
		$filter = "none";
	}

	if ($query)
	{
		$querymethod = " where (descr like '%$query%' or num like '%$query%' or minperae like '%$query%' or billperae like '%$query%')";
	}

	$db2 = $phpgw->db;

	$sql = "select * from phpgw_p_activities $querymethod";
	$db2->query($sql,__LINE__,__FILE__);
	$total_records = $db2->num_rows();

	$t->set_var('lang_showing',$phpgw->nextmatchs->show_hits($total_records,$start));

// ---------------- nextmatch variable template-declarations ------------------------------

	$left = $phpgw->nextmatchs->left('activities.php',$start,$total_records);
	$right = $phpgw->nextmatchs->right('activities.php',$start,$total_records);
	$t->set_var('left',$left);
	$t->set_var('right',$right);

// ------------------------- end nextmatch template ---------------------------------------

// ----------------- list header variable template-declarations ---------------------------
  
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('currency',$currency);
	$t->set_var('sort_num',$phpgw->nextmatchs->show_sort_order($sort,'num',$order,'activities.php',lang('Activity ID')));
	$t->set_var('sort_descr',$phpgw->nextmatchs->show_sort_order($sort,'descr',$order,'activities.php',lang('Description')));
	$t->set_var('sort_billperae',$phpgw->nextmatchs->show_sort_order($sort,'billperae',$order,'activities.php',lang('Bill per workunit')));
	$t->set_var('sort_minperae',$phpgw->nextmatchs->show_sort_order($sort,'minperae',$order,'activities.php',lang('Minutes per workunit')));
	$t->set_var('lang_edit',lang('Edit'));
	$t->set_var('lang_delete',lang('Delete'));

// ---------------------------- end header declaration -------------------------------------

	$phpgw->db->limit_query($sql . $ordermethod,$start,__LINE__,__FILE__);
	while ($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$num = $phpgw->strip_html($phpgw->db->f('num'));                                                                                                                                    
		if (! $num)  $num  = '&nbsp;';

		$descr = $phpgw->strip_html($phpgw->db->f('descr'));                                                                                                                                    
		if (! $descr)  $descr  = '&nbsp;';

		$billperae = $phpgw->db->f('billperae');
		$minperae = $phpgw->db->f('minperae');
		$t->set_var('tr_color',$tr_color);

// ------------------- template declaration for list records -------------------------
      
		$t->set_var(array('num' => $num,
						'descr' => $descr,
					'billperae' => $billperae,
					'minperae' => $minperae));

		$t->set_var('edit',$phpgw->link('/projects/editactivity.php','id=' . $phpgw->db->f('id') . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));
		$t->set_var('delete',$phpgw->link('/projects/deleteactivity.php','id=' . $phpgw->db->f('id') . "&sort=$sort&order=$order&query=$query&start=$start&filter=$filter"));

		$t->parse('list','activities_list',True);

// ------------------------------- end record declaration --------------------------------
	}

// ------------------------- template declaration for Add Form ---------------------------

	$t->set_var('lang_add',lang('Add'));
	$t->parse('out','activities_list_t',True);
	$t->p('out');

// -------------------------------- end Add form declaration ------------------------------

	$phpgw->common->phpgw_footer();
?>
