<?php
	/**************************************************************************\
	* phpGroupWare - PHP Webhosting                                            *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$setup_info['filemanager']['name']    = 'filemanager';
	$setup_info['filemanager']['title']   = 'Filemanager';
	$setup_info['filemanager']['version'] = '0.9.13.005';
	$setup_info['filemanager']['app_order'] = 10;
	$setup_info['filemanager']['enable']  = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['filemanager']['hooks'] = array
	(
		'add_def_pref',
		'admin',
		'deleteaccount',
		'settings',
		'preferences'
	);

	/* Dependencies for this app to work */
	$setup_info['filemanager']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => array('0.9.14','0.9.16')
	);
?>
