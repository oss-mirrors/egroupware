<?php
  /**************************************************************************\
  * phpGroupWare - email/addressbook                                         *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader' => True,
		'nonavbar' => True,
		'currentapp' => 'email',
		'enable_nextmatchs_class' => True
	);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('email'));
	$t->set_file(array(
		'addressbook_list_t' => 'addressbook.tpl',
		'addressbook_list' => 'addressbook.tpl'
	));
	$t->set_block('addressbook_list_t','addressbook_list','list');

	$d = CreateObject('phpgwapi.contacts');
	$c = CreateObject('phpgwapi.categories');
	$c->app_name = 'addressbook';

	$charset = $GLOBALS['phpgw']->translation->translate('charset');
	$t->set_var('charset',$charset);
	$t->set_var('title',$GLOBALS['phpgw_info']['site_title']);
	$t->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['bg_color']);
	$t->set_var('lang_addressbook_action',lang('Address book'));
	$t->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);

	$t->set_var('lang_search',lang('Search'));
	$t->set_var('search_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php'));
	$t->set_var('lang_select_cats',lang('Select category'));

	if (! $start) { $start = 0; }

	if (!$filter) { $filter = 'none'; }

	if (!$cat_id)
	{
		if ($filter == 'none') { $qfilter  = 'tid=n'; }
		elseif ($filter == 'private') { $qfilter  = 'tid=n,owner='.$GLOBALS['phpgw_info']['user']['account_id']; }
		else { $qfilter = 'tid=n,owner='.$filter; }
	}
	else
	{
		if ($filter == 'none') { $qfilter  = 'tid=n,cat_id='.$cat_id; }
		elseif ($filter == 'private') { $qfilter  = 'tid=n,owner='.$GLOBALS['phpgw_info']['user']['account_id'].',cat_id='.$cat_id; }
		else { $qfilter = 'tid=n,owner='.$filter.'cat_id='.$cat_id; }
	}

	if($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] && $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] > 0)
	{
		$offset = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
	}
	else
	{
		$offset = 15;
	}

	$account_id = $GLOBALS['phpgw_info']['user']['account_id'];

	$cols = array (
		'n_given'    => 'n_given',
		'n_family'   => 'n_family',
		'email'      => 'email',
		'email_home' => 'email_home'
	);

	$entries = $d->read($start,$offset,$cols,$query,$qfilter,$sort,$order,$account_id);

	//------------------------------------------- nextmatch --------------------------------------------
	$left = $GLOBALS['phpgw']->nextmatchs->left('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
	$right = $GLOBALS['phpgw']->nextmatchs->right('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
	$t->set_var('left',$left);
	$t->set_var('right',$right);

	if ($d->total_records > $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])
	{
		$t->set_var('lang_showing',lang('showing x - x of x',($start + 1),($start + $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']),$d->total_records));
	}
	else
	{
		$t->set_var('lang_showing',lang('showing x',$d->total_records));
	}
	// --------------------------------------- end nextmatch ------------------------------------------

	// ------------------- list header variable template-declaration -----------------------
	$t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$t->set_var('sort_firstname',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'n_given',$order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',lang('Firstname')));
	$t->set_var('sort_lastname',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'n_family',$order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',lang('Lastname')));
	$t->set_var('lang_email',lang('Select work email address'));
	$t->set_var('lang_hemail',lang('Select home email address'));
	$t->set_var('cats_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',"sort=$sort&order=$order&filter=$filter&start=$start&query=$query&cat_id=$cat_id"));
	$t->set_var('cats_list',$c->formated_list('select','all',$cat_id,'True'));
	$t->set_var('lang_select',lang('Select'));

	// --------------------------- end header declaration ----------------------------------
	for ($i=0;$i<count($entries);$i++)
	{
		$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
		$t->set_var(tr_color,$tr_color);
		$firstname = $entries[$i]['n_given'];
		if (!$firstname) { $firstname = '&nbsp;'; }
		$lastname = $entries[$i]['n_family'];
		if (!$lastname) { $lastname = '&nbsp;'; }
		$id     = $entries[$i]['id'];
		$email  = $entries[$i]['email'];
		$hemail = $entries[$i]['email_home'];
		// --------------------- template declaration for list records --------------------------
		$t->set_var(array(
			'firstname' => $firstname,
			'lastname'  => $lastname
		));

		$t->set_var('id',$id);
		$t->set_var('email',$email);
		$t->set_var('hemail',$hemail);

		$t->parse('list','addressbook_list',True);
	}
	// --------------------------- end record declaration ---------------------------

	$t->set_var('lang_done',lang('Done'));
	$t->parse('out','addressbook_list_t',True);
	$t->p('out');

	$GLOBALS['phpgw']->common->phpgw_exit();
?>
