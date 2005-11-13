<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$setup_info['backup']['name']		= 'backup';
	$setup_info['backup']['title']		= 'Backup';
	$setup_info['backup']['version']	= '1.2';
	$setup_info['backup']['app_order']	= 41;
	$setup_info['backup']['enable'] = 1;

	$setup_info['backup']['author'] = 
	$setup_info['backup']['maintainer'] = array(
		'name' => 'Joao Martins',
		'joao@wipmail.com.br'
	);
	$setup_info['backup']['license']  = 'GPL';
	$setup_info['backup']['description'] =
		'An online configurable backup app to backup the eGW Database.';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['backup']['hooks'][] = 'admin';

	/* Dependencies for this app to work */
	$setup_info['backup']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('1.0.0','1.0.1','1.2')
	);

	$setup_info['backup']['depends'][] = array(
		'appname'  => 'admin',
		'versions' => Array('1.0.0','1.0.1','1.2')
	);
?>
