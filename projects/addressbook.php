<?php
	/**************************************************************************\
	* phpGroupWare - Projects addressbook                                      *
	* http://www.phpgroupware.org                                              *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('noheader' => True,
								'nonavbar' => True,
								'currentapp' => 'projects',
					'enable_nextmatchs_class' => True);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('addressbook_list_t' => 'addressbook.tpl',
						'addressbook_list' => 'addressbook.tpl'));
	$t->set_block('addressbook_list_t','addressbook_list','list');

	$d = CreateObject('phpgwapi.contacts');
	$c = CreateObject('phpgwapi.categories');
	$c->app_name = 'addressbook';

	$t->set_var('title',$phpgw_info['site_title']);
	$t->set_var('bg_color',$phpgw_info['theme']['bg_color']);
	$t->set_var('lang_addressbook_action',lang('Address book'));
	$charset = $phpgw->translation->translate('charset');
	$t->set_var('charset',$charset);
	$t->set_var('font',$phpgw_info['theme']['font']);
	$t->set_var('lang_search',lang('Search'));
	$t->set_var('search_action',$phpgw->link('/projects/addressbook.php'));
	$t->set_var('lang_category',lang('Category'));
	$t->set_var('lang_all',lang('All'));

	if (! $start) { $start = 0; }

	if (!$filter) { $filter = 'none'; }

	if (!$cat_id)
	{
		if ($filter == 'none') { $qfilter = 'tid=n'; }
        elseif ($filter == 'private') { $qfilter  = 'tid=n,owner=' . $phpgw_info['user']['account_id']; }
        else { $qfilter = 'tid=n,owner=' . $filter; }
	}
	else
	{
		if ($filter == 'none') { $qfilter  = 'tid=n,cat_id=' . $cat_id; }
        elseif ($filter == 'private') { $qfilter = 'tid=n,owner=' . $phpgw_info['user']['account_id'] . ',cat_id=' . $cat_id; }
        else { $qfilter = 'tid=n,owner='.$filter . 'cat_id=' . $cat_id; }
	}

	$account_id = $phpgw_info['user']['account_id'];

	$cols = array('n_given' => 'n_given',
				'n_family' => 'n_family',
				'org_name' => 'org_name');

	$entries = $d->read($start,True,$cols,$query,$qfilter,$sort,$order,$account_id);

//--------------------------------- nextmatch --------------------------------------------

	$left = $phpgw->nextmatchs->left('/projects/addressbook.php',$start,$d->total_records,'&order=' . $order . '&filter=' . $filter . '&sort='
									. $sort . '&query=' . $query);
	$right = $phpgw->nextmatchs->right('/projects/addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
	$t->set_var('left',$left);
	$t->set_var('right',$right);

    $t->set_var('lang_showing',$phpgw->nextmatchs->show_hits($d->total_records,$start));

// ------------------------------ end nextmatch ------------------------------------------

// -------------- list header variable template-declaration ------------------------

	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('sort_company',$phpgw->nextmatchs->show_sort_order($sort,'org_name',$order,'/projects/addressbook.php',lang('Company')));
	$t->set_var('sort_firstname',$phpgw->nextmatchs->show_sort_order($sort,'n_given',$order,'/projects/addressbook.php',lang('Firstname')));
	$t->set_var('sort_lastname',$phpgw->nextmatchs->show_sort_order($sort,'n_family',$order,'/projects/addressbook.php',lang('Lastname')));
	$t->set_var('lang_select',lang('Select'));
	$t->set_var('cats_action',$phpgw->link('/projects/addressbook.php',"sort=$sort&order=$order&filter=$filter&start=$start&query=$query&cat_id=$cat_id"));
	$t->set_var('cats_list',$c->formated_list('select','all',$cat_id,'True'));

// ------------------------- end header declaration --------------------------------

	for ($i=0;$i<count($entries);$i++)
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$t->set_var('tr_color',$tr_color);
		$firstname = $entries[$i]['n_given'];
		if (!$firstname) { $firstname = '&nbsp;'; }
		$lastname = $entries[$i]['n_family'];
		if (!$lastname) { $lastname = '&nbsp;'; }
		$company = $entries[$i]['org_name'];
		if (!$company) { $company = '&nbsp;'; }
		$id = $entries[$i]['id'];

// ---------------- template declaration for list records -------------------------- 

		$t->set_var(array('company' => $company,
						'firstname' => $firstname,
						'lastname' => $lastname));

		$t->set_var('id',$id);

		$t->parse('list','addressbook_list',True);
	}

	$t->set_var('lang_done',lang('Done'));
	$t->parse('out','addressbook_list_t',True);
	$t->p('out');

	$phpgw->common->phpgw_exit();
?>
