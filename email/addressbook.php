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

	$GLOBALS['phpgw']->template->set_file(array(
		'addressbook_list_t' => 'addressbook.tpl',
		'addressbook_list' => 'addressbook.tpl'
	));
	$GLOBALS['phpgw']->template->set_block('addressbook_list_t','addressbook_list','list');

	$d = CreateObject('phpgwapi.contacts');
	$c = CreateObject('phpgwapi.categories');
	$c->app_name = 'addressbook';
	
	$include_personal = True;
	
	$charset = $GLOBALS['phpgw']->translation->translate('charset');
	$GLOBALS['phpgw']->template->set_var('charset',$charset);
	$GLOBALS['phpgw']->template->set_var('title',$GLOBALS['phpgw_info']['site_title']);
	$GLOBALS['phpgw']->template->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['bg_color']);
	$GLOBALS['phpgw']->template->set_var('lang_addressbook_action',lang('Address book'));
	$GLOBALS['phpgw']->template->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);

	$GLOBALS['phpgw']->template->set_var('lang_search',lang('Search'));
	$GLOBALS['phpgw']->template->set_var('search_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php'));
	$GLOBALS['phpgw']->template->set_var('lang_select_cats',lang('Select category'));

	if (! $start)
	{
		$start = 0;
	}

	if (!$filter)
	{
		$filter = 'none';
	}

	if (!$cat_id)
	{
		if ($filter == 'none')
		{
			$qfilter  = 'tid=n';
		}
		elseif ($filter == 'private')
		{
			$qfilter  = 'tid=n,owner='.$GLOBALS['phpgw_info']['user']['account_id'];
		}
		else
		{
			$qfilter = 'tid=n,owner='.$filter;
		}
	}
	else
	{
		if ($filter == 'none')
		{
			$qfilter  = 'tid=n,cat_id='.$cat_id;
		}
		elseif ($filter == 'private')
		{
			$qfilter  = 'tid=n,owner='.$GLOBALS['phpgw_info']['user']['account_id'].',cat_id='.$cat_id; 
		}
		else
		{
			$qfilter = 'tid=n,owner='.$filter.'cat_id='.$cat_id;
		}
	}

	if (($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']) &&
	($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] > 0))
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
		'org_name'   => 'org_name',
		'email'      => 'email',
		'email_home' => 'email_home'
	);

	$entries = $d->read($start,$offset,$cols,$query,$qfilter,$sort,$order);

	//------------------------------------------- nextmatch --------------------------------------------
	$left = $GLOBALS['phpgw']->nextmatchs->left('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
	$right = $GLOBALS['phpgw']->nextmatchs->right('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',$start,$d->total_records,"&order=$order&filter=$filter&sort=$sort&query=$query");
	$GLOBALS['phpgw']->template->set_var('left',$left);
	$GLOBALS['phpgw']->template->set_var('right',$right);

	if ($d->total_records > $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])
	{
		$GLOBALS['phpgw']->template->set_var('lang_showing',lang('showing %1 - %2 of %3',($start + 1),($start + $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']),$d->total_records));
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('lang_showing',lang('showing %1',$d->total_records));
	}
	// --------------------------------------- end nextmatch ------------------------------------------

	// ------------------- list header variable template-declaration -----------------------
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('sort_firstname',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'n_given',$order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',lang('Firstname')));
	$GLOBALS['phpgw']->template->set_var('sort_lastname',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'n_family',$order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',lang('Lastname')));
	$GLOBALS['phpgw']->template->set_var('sort_company',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'org_name',$order,'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',lang('Company')));
	$GLOBALS['phpgw']->template->set_var('lang_email',lang('Select work email address'));
	$GLOBALS['phpgw']->template->set_var('lang_hemail',lang('Select home email address'));
	$GLOBALS['phpgw']->template->set_var('cats_list',$c->formated_list('select','all',$cat_id,'True'));
	$GLOBALS['phpgw']->template->set_var('lang_select',lang('Select'));
	
	//$GLOBALS['phpgw']->template->set_var('cats_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',"sort=$sort&order=$order&filter=$filter&start=$start&query=$query&cat_id=$cat_id"));
	// thanks to  dave.hall@mbox.com.au for fixing drop down list filtering by categories
	$GLOBALS['phpgw']->template->set_var('cats_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/addressbook.php',"sort=$sort&order=$order&filter=$filter&start=$start&query=$query"));

	// --------------------------- end header declaration ----------------------------------
	for ($i=0;$i<count($entries);$i++)
	{
		$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
		$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);
		$firstname = $entries[$i]['n_given'];
		if (!$firstname)
		{
			$firstname = '&nbsp;';
		}
		$lastname = $entries[$i]['n_family'];
		if (!$lastname)
		{
			$lastname = '&nbsp;';
		}
		// thanks to  dave.hall@mbox.com.au for adding company
		$company = $entries[$i]['org_name'];
		if (!$company)
		{
			$company = '&nbsp;';
		}
		
		$personal_firstname = '';
		$personal_lastname = '';
		$personal_part = '';
		if ((isset($firstname)) &&
			($firstname != '') &&
			($firstname != '&nbsp;'))
		{
			$personal_firstname = $firstname.' ';
		}
		if ((isset($lastname)) &&
			($lastname != '') &&
			($lastname != '&nbsp;'))
		{
			$personal_lastname = $lastname;
		}
		$personal_part = $personal_firstname.$personal_lastname;
		
		if (($personal_part == '') ||
			($include_personal == False))
		{
			$id     = $entries[$i]['id'];
			$email  = $entries[$i]['email'];
			$hemail = $entries[$i]['email_home'];
		}
		else
		{
			$id = $entries[$i]['id'];
			if ((isset($entries[$i]['email'])) &&
				(trim($entries[$i]['email']) != ''))
			{
				$email  = '&quot;'.$personal_part.'&quot; &lt;'.$entries[$i]['email'].'&gt;';
			}
			else
			{
				$email  = $entries[$i]['email'];
			}
			if ((isset($entries[$i]['email_home'])) &&
			(trim($entries[$i]['email_home']) != ''))
			{
				$hemail = '&quot;'.$personal_part.'&quot; &lt;'.$entries[$i]['email_home'].'&gt;';
			}
			else
			{
				$hemail = $entries[$i]['email_home'];
			}
		}
		
		// --------------------- template declaration for list records --------------------------
		$GLOBALS['phpgw']->template->set_var(array(
			'firstname' => $firstname,
			'lastname'  => $lastname,
			'company'	=> $company
		));

		$GLOBALS['phpgw']->template->set_var('id',$id);
		$GLOBALS['phpgw']->template->set_var('email',$email);
		$GLOBALS['phpgw']->template->set_var('hemail',$hemail);

		$GLOBALS['phpgw']->template->parse('list','addressbook_list',True);
	}
	// --------------------------- end record declaration ---------------------------

	$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
	$GLOBALS['phpgw']->template->parse('out','addressbook_list_t',True);
	$GLOBALS['phpgw']->template->p('out');

	$GLOBALS['phpgw']->common->phpgw_exit();
?>
