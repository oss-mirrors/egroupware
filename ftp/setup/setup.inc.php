<?php
	/**************************************************************************\
	* phpGroupWare - FTP                                                       *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['ftp']['name']      = 'ftp';
	$setup_info['ftp']['title']     = 'FTP';
	$setup_info['ftp']['version']   = '0.8.1';
	$setup_info['ftp']['app_order'] = 25;
	$setup_info['ftp']['enable']    = 1;

	/* Dependencies for this app to work */
	$setup_info['ftp']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13', '0.9.14','0.9.15')
	);
?>
