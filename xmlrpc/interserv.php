<?php
	/**************************************************************************\
	* phpGroupWare - Interserver XML-RPC/SOAP Test app                         *
	* http://www.phpgroupware.org                                              *
	* This file written by Miles Lott <milosch@phpgroupware.org                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'login',
		'disable_Template_class' => True
	);

	include('../header.inc.php');

	$server_id  = $HTTP_POST_VARS['server_id'];
	$xsessionid = $HTTP_POST_VARS['xsessionid'];
	$xkp3       = $HTTP_POST_VARS['xkp3'];

	$is = CreateObject('phpgwapi.interserver',intval($server_id));

	/* _debug_array($is->server); */
	if($HTTP_POST_VARS['login'])
	{
		/* You may need to adjust $HTTP_HOST manually here */
		$is->send(
			'system.login', array(
				'server_name' => $HTTP_HOST,
				'username'    => $is->server['username'],
				'password'    => $is->server['password']
			),
			$is->server['server_url']
		);
		/* _debug_array($is->result); */
		$xsessionid = $is->result['sessionid'];
		$xkp3       = $is->result['kp3'];
	}
	elseif($HTTP_POST_VARS['logout'])
	{
		$is->send(
			'system.logout', array(
				'sessionid' => $xsessionid,
				'kp3'       => $xkp3
			),
			$is->server['server_url']
		);
	}
	elseif($HTTP_POST_VARS['methods'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.listMethods','',$is->server['server_url']);
	}
	elseif($HTTP_POST_VARS['apps'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.listApps','',$is->server['server_url']);
	}
	elseif($HTTP_POST_VARS['users'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.listUsers','',$is->server['server_url']);
	}
	elseif($HTTP_POST_VARS['addressbook'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		/* NOTE: Currently acl in the addressbook so class blocks this from working.
			You will need to replace the first line in read_entry() there to if(1).
		*/
		$is->send(
			'addressbook.boaddressbook.read_entry',array(
				'id' => 24,
				'fields' => array(
					'n_given'  => 'n_given',
					'n_family' => 'n_family'
				)
			),
			$is->server['server_url']
		);
	}

	echo '<table><tr><td>';
	echo '<form method="POST" action="' . $GLOBALS['phpgw']->link('/xmlrpc/interserv.php') . '">' . "\n";
	echo $is->formatted_list($server_id) . "\n";
	echo '<input type="submit" name="login" value="Login">' . "\n";
	echo '<input type="submit" name="logout" value="Logout">' . "\n";
	echo '<input type="submit" name="addressbook" value="Addressbook test">' . "\n";
	echo '<input type="submit" name="methods" value="List Methods">' . "\n";
	echo '<input type="submit" name="apps" value="List Apps">' . "\n";
	echo '<input type="submit" name="users" value="List Users">' . "\n";
	echo '<input type="hidden" name="xsessionid" value="' . $xsessionid . '">' . "\n";
	echo '<input type="hidden" name="xkp3" value="' . $xkp3 . '">' . "\n";
	echo 'listapps and listusers are disabled by default in xml_functions.php' . "\n";
	echo '</form>' . "\n";
	echo '</td></tr></table><td>';
?>
