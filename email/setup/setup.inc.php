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
	$setup_info['email']['title']     = 'Email';
	$setup_info['email']['version']   = '0.9.13.002';
	$setup_info['email']['app_order'] = '2';
	$setup_info['email']['enable']    = 1;
	$setup_info['email']['tables']    = '';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['email']['hooks'][] = 'admin';
	$setup_info['email']['hooks'][] = 'preferences';
	$setup_info['email']['hooks'][] = 'email_add_def_pref';
	$setup_info['email']['hooks'][] = 'home';
	$setup_info['email']['hooks'][] = 'manual';
	$setup_info['email']['hooks'][] = 'notifywindow';
	$setup_info['email']['hooks'][] = 'notifywindow_simple';

	/* Dependacies for this app to work */
	$setup_info['email']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13')
	);
?>
