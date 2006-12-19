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

	require_once (EGW_INCLUDE_ROOT.'/tts/inc/acl_funcs.inc.php');
	require_once (EGW_INCLUDE_ROOT.'/tts/inc/prio.inc.php');


	$yes_and_no = array(
		'True'  => lang('Yes'),
		'False' => lang('No')
	);

	$_groups = array();
	foreach($GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']) as $entry)
	{
		$_groups[$entry['account_id']] = $entry['account_name'];
	}

	$_accounts = array();
	foreach($GLOBALS['egw']->accounts->get_list('accounts') as $entry)
	{
		if (check_assign_right($entry['account_id'], 1, 1)) {
			$_accounts[$entry['account_id']] = $GLOBALS['egw']->common->grab_owner_name($entry['account_id']);
		}
	}

	// Choose the correct priority to display
	$priority_comment[4] = ' - '.lang('Wish');
	$priority_comment[3] = ' - '.lang('Low');
	$priority_comment[2] = ' - '.lang('High');
	$priority_comment[1] = ' - '.lang('Critical');
	$priority_comment[0] = ' - '.lang('Emergency');
	for ($i=4; $i>=0; $i--)
	{
	    $priority[$i] = $i . $priority_comment[$i];
	}

	$show_entries = array(
		0 => lang('No'),
//		1 => lang('Yes'),
		2 => lang('Yes').' - '.lang('small view'),
	);

	$GLOBALS['settings'] = array(
		'show_converted_tickets' => array(
			'type'   => 'select',
			'label'  => 'show tickets converted to Tracker in overviews',
			'name'   => 'show_converted_tickets',
			'values' => $yes_and_no,
			'xmlrpc' => True,
			'admin'  => False
		),
		'mainscreen_show_new_updated' => array(
			'type'   => 'select',
			'label'  => 'show new/updated tickets on main screen',
			'name'   => 'mainscreen_show_new_updated',
			'values' => $show_entries,
//			'values' => $yes_and_no,
			'xmlrpc' => True,
			'admin'  => False
		),
		'groupdefault' => array(
			'type'   => 'select',
			'label'  => 'Default group',
			'name'   => 'groupdefault',
			'values' => $_groups,
			'xmlrpc' => True,
			'admin'  => False
		),
		'assigntodefault' => array(
			'type'   => 'select',
			'label'  => 'Default assign to',
			'name'   => 'assigntodefault',
			'values' => $_accounts,
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
