<?php
	/**************************************************************************\
	* phpGroupWare - Developer tools                                           *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['developer_tools']['name']      = 'developer_tools';
	$setup_info['developer_tools']['title']     = 'Developer Tools';
	$setup_info['developer_tools']['version']   = '0.8.1';
	$setup_info['developer_tools']['app_order'] = 8;
	$setup_info['developer_tools']['enable']    = 1;

	$setup_info['developer_tools']['author'] = 'Miles Lott';
	$setup_info['developer_tools']['note'] = '';
	$setup_info['developer_tools']['license']  = 'GPL';
	$setup_info['developer_tools']['description'] =
		'Contains the language management system.';
	$setup_info['developer_tools']['maintainer'] = 'Miles Lott';
	$setup_info['developer_tools']['maintainer_email']    = 'milosch@phpgroupware.org';

	/* The tables this app creates */
	$setup_info['developer_tools']['tables']    = array(
		'phpgw_devtools_diary',
		'phpgw_devtools_sf_cache',
		'phpgw_devtools_changelogs'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['developer_tools']['hooks'][] = 'admin';
	$setup_info['developer_tools']['hooks'][] = 'preferences';

	/* Dependencies for this app to work */
	$setup_info['developer_tools']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13', '0.9.14','0.9.15')
	);
?>
