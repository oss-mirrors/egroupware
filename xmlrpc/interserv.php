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
			'system.auth', array(
				'server_name' => $HTTP_HOST,
				'username'    => $is->server['username'],
				'password'    => $is->server['password']
			),
			$is->server['server_url']
		);
		_debug_array($is->result);
		list($x,$xsessionid,$y,$xkp3) = $is->result;
		if($x && !$y)
		{
			$xkp3 = $xsessionid;
			$xsessionid = $x;
		}
	}
	elseif($verify)
	{
		$is->send(
			'system.auth_verify', array(
				'server_name' => $HTTP_HOST,
				'sessionid'   => $xsessionid,
				'kp3'         => $xkp3
			),
			$is->server['server_url']
		);
	}

	echo '<table><tr><td>';
	echo '<form action="' . $phpgw->link('/xmlrpc/interserv.php') . '">' . "\n";
	echo $is->formatted_list($server_id) . "\n";
	echo '<input type="submit" name="login" value="Login">' . "\n";
	echo '<input type="submit" name="verify" value="Verify">' . "\n";
	echo '<input type="hidden" name="xsessionid" value="' . $xsessionid . '">' . "\n";
	echo '<input type="hidden" name="xkp3" value="' . $xkp3 . '">' . "\n";
	echo '</form>' . "\n";
	echo '</td></tr></table><td>';
?>
