<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* http://www.phpgroupware.org                                              *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_nextmatchs_class' => True,
								'noappheader' => True);
  
	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('admin_list_t' => 'list_admin.tpl',
						'admin_list' => 'list_admin.tpl'));
	$t->set_block('admin_list_t','admin_list','list');

	$t->set_var('lang_action',lang('Project administration'));
	$t->set_var('addurl',$phpgw->link('/projects/add_admin.php'));
	$t->set_var('lang_edit',lang('Edit'));
	$t->set_var('lang_search',lang('Search'));
	$t->set_var('actionurl',$phpgw->link('/projects/admin.php'));
	$t->set_var('lang_done',lang('Done'));
	$t->set_var('doneurl',$phpgw->link('/admin/index.php'));

	if (!$sort) { $sort = "ASC";  }
	if ($order) { $ordermethod = "order by $order $sort"; }
	else { $ordermethod = "order by account_lid asc"; }

	if (! $start) { $start = 0; }

	if ($phpgw_info['user']['preferences']['common']['maxmatchs'] && $phpgw_info['user']['preferences']['common']['maxmatchs'] > 0)
	{                                                                                                                                                                              
		$limit = $phpgw_info['user']['preferences']['common']['maxmatchs'];
	}
	else { $limit = 15; }

	if ($query)
	{
		$querymethod = " AND account_lid like '%$query%' OR account_lastname like '%$query%' OR account_firstname like '%$query%'";
	}

	$db2 = $phpgw->db;

	$sql = "SELECT phpgw_p_projectmembers.account_id,type,account_lid,account_firstname,account_lastname from phpgw_p_projectmembers, "
		. "phpgw_accounts WHERE project_id='0' AND phpgw_p_projectmembers.account_id=phpgw_accounts.account_id $querymethod $ordermethod";

	$db2->query($sql,__LINE__,__FILE__);
	$total_records = $db2->num_rows();

	$phpgw->db->query($sql . " " . $phpgw->db->limit($start),__LINE__,__FILE__);
	while ($phpgw->db->next_record())
	{
		$admins[] = array('id' => $phpgw->db->f('account_id'),
						'lid' => $phpgw->db->f('account_lid'),
					'firstname' => $phpgw->db->f('account_firstname'),
					'lastname' => $phpgw->db->f('account_lastname'),
						'type' => $phpgw->db->f('type'));
	}

//--------------------------------- nextmatch --------------------------------------------
 
	$left = $phpgw->nextmatchs->left('/projects/admin.php',$start,$total_records);
	$right = $phpgw->nextmatchs->right('/projects/admin.php',$start,$total_records);
	$t->set_var('left',$left);
	$t->set_var('right',$right);

	if ($total_records > $limit)
	{
		$t->set_var('lang_showing',lang('showing x - x of x',($start + 1),($start + $limit),$total_records));
	}
	else
	{
		$t->set_var('lang_showing',lang('showing x',$total_records));
	}
 
// ------------------------------ end nextmatch ------------------------------------------
 
//------------------- list header variable template-declarations -------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('sort_lid',$phpgw->nextmatchs->show_sort_order($sort,'account_lid',$order,'/projects/admin.php',lang('Username / Group')));
	$t->set_var('sort_lastname',$phpgw->nextmatchs->show_sort_order($sort,'account_lastname',$order,'/projects/admin.php',lang('Lastname')));
	$t->set_var('sort_firstname',$phpgw->nextmatchs->show_sort_order($sort,'account_firstname',$order,'/projects/admin.php',lang('Firstname')));

// -------------------------- end header declaration --------------------------------------

	for ($i=0;$i<count($admins);$i++)
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$t->set_var('tr_color',$tr_color);
		$lid = $admins[$i]['lid'];

		if ($admins[$i]['type']=='aa')
		{
			$firstname = $admins[$i]['firstname'];
			if (!$firstname) { $firstname = '&nbsp;'; }
			$lastname = $admins[$i]['lastname'];
			if (!$lastname) { $lastname = '&nbsp;'; }
		}
		else
		{
			$firstname = '&nbsp;';
			$lastname = '&nbsp;';
		}

		$t->set_var(array('lid' => $lid,
					'firstname' => $firstname,
					'lastname' => $lastname));

		$t->parse('list','admin_list',True);
	}

	$t->parse('out','admin_list_t',True);
	$t->p('out');

	$phpgw->common->phpgw_footer();
?>
