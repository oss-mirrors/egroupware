<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$setup_info['backup']['name']		= 'backup';
	$setup_info['backup']['title']		= 'Backup';
	$setup_info['backup']['version']	= '0.0.1.001';
	$setup_info['backup']['app_order']	= 72;
	$setup_info['backup']['enable'] = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['backup']['hooks'][] = 'admin';

	/* Dependencies for this app to work */
	$setup_info['backup']['depends'][] = array
	(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13', '0.9.14')
	);

	$setup_info['backup']['depends'][] = array
	(
		'appname'  => 'admin',
		'versions' => Array('0.9.13','0.9.14','0.9.15')
	);
?>
