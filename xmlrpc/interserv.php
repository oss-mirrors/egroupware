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

	$phpgw_info['flags'] = array(
		'currentapp' => 'login',
		'disable_Template_class' => True
	);

	include('../header.inc.php');

	$is = CreateObject('phpgwapi.interserver',intval($server_id));

//	_debug_array($is->server);
	if($login)
	{
		$is->send(
			'system.login', array(
				'server_name' => $HTTP_HOST,
				'username'    => $is->server['username'],
				'password'    => $is->server['password']
			),
			$is->server['server_url']
		);
		/* _debug_array($is->result); */
		list($x,$xsessionid,$y,$xkp3) = $is->result;
		if($x && !$y)
		{
			$xkp3 = $xsessionid;
			$xsessionid = $x;
		}
	}
	elseif($logout)
	{
		$is->send(
			'system.logout', array(
				'sessionid' => $xsessionid,
				'kp3'       => $xkp3
			),
			$is->server['server_url']
		);
	}
	elseif($methods)
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		$is->send(
			'system.listMethods',
			array(''),
			$is->server['server_url']
		);
	}
	elseif($apps)
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		$is->send(
			'system.listApps',
			array(''),
			$is->server['server_url']
		);
	}
	elseif($users)
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		$is->send(
			'system.listUsers',
			array(''),
			$is->server['server_url']
		);
	}

	echo '<table><tr><td>';
	echo '<form action="' . $phpgw->link('/xmlrpc/interserv.php') . '">' . "\n";
	echo $is->formatted_list($server_id) . "\n";
	echo '<input type="submit" name="login" value="Login">' . "\n";
	echo '<input type="submit" name="logout" value="Logout">' . "\n";
	echo '<input type="submit" name="methods" value="List Methods">' . "\n";
	echo '<input type="submit" name="apps" value="List Apps">' . "\n";
	echo '<input type="submit" name="users" value="List Users">' . "\n";
	echo '<input type="hidden" name="xsessionid" value="' . $xsessionid . '">' . "\n";
	echo '<input type="hidden" name="xkp3" value="' . $xkp3 . '">' . "\n";
	echo 'listapps and listusers are disabled by default in xml_functions.php' . "\n";
	echo '</form>' . "\n";
	echo '</td></tr></table><td>';
?>
