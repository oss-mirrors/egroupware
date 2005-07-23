<?php
  /**************************************************************************\
  * eGroupWare - Preferences                                                 *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$yes_and_no = array(
		'True'  => lang('Yes'),
		'False' => lang('No')
	);

	///rem by Josip
	///$acc = CreateObject('phpgwapi.accounts');
	///$group_list = $acc->get_list('groups');
	///while (list($key,$entry) = each($group_list))
	///{
	///  $_groups[$entry['account_id']] = $entry['account_lid'];
	///}
	///create_select_box('Default group','groupdefault',$_groups);

	///$account_list = $acc->get_list('accounts');
	///while (list($key,$entry) = each($account_list))
	///{
	///  $_accounts[$entry['account_id']] = $entry['account_lid'];
	///}
	///create_select_box('Default assign to','assigntodefault',$_accounts);

	// Choose the correct priority to display
	$priority_comment[1]  = ' - ' . lang('Lowest'); 
	$priority_comment[5]  = ' - ' . lang('Medium'); 
	$priority_comment[10] = ' - ' . lang('Highest'); 
	for ($i=1; $i<=10; $i++)
	{
		$priority[$i] = $i . $priority_comment[$i];
	}

	$GLOBALS['settings'] = array(
		'mainscreen_show_new_updated' => array(
			'type'   => 'select',
			'label'  => 'show new/updated tickets on main screen',
			'name'   => 'mainscreen_show_new_updated',
			'values' => $yes_and_no,
			'xmlrpc' => True,
			'admin'  => False
		),
		'prioritydefault' => array(
			'type'   => 'select',
			'label'  => 'Default Priority',
			'name'   => 'prioritydefault',
			'values' => $priority,
			'xmlrpc' => True,
			'admin'  => False
		),
		'refreshinterval' => array(
			'type'    => 'input',
			'label'   => 'Refresh every (seconds)',
			'name'    => 'refreshinterval',
			'xmlrpc' => True,
			'admin'  => False
		)
	);
