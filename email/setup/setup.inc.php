<?php
	/**************************************************************************\
	* phpGroupWare - Email                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$setup_info['email']['name']      = 'email';
	$setup_info['email']['version']   = '0.9.13.002';
	$setup_info['email']['app_order'] = '2';
	$setup_info['email']['enable']    = 1;
	$setup_info['email']['tables']    = '';

	$setup_info['email']['author'] =
	$setup_info['email']['maintainer'] = array(
		'name'  => 'Angelo Tony Puglisi',
		'email' => 'devel@aminvestments.com'
	);
	$setup_info['email']['license']  = 'GPL';
	$setup_info['email']['description'] =
		'Email reader with multiple accounts and mailbox filtering.';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['email']['hooks'] = array
	(
		'admin',
		'home',
		'help',
		'notifywindow',
		'notifywindow_simple',
		'add_def_pref',
		'preferences'
	);

	/* Dependacies for this app to work */
	$setup_info['email']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.15')
	);

	$setup_info['email']['depends'][] = array
	(
		 'appname' => 'addressbook',
		 'versions' => Array('0.9.13')
	);
?>
