<?php
	/**************************************************************************\
	* phpGroupWare - PHPSysInfo                                                *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$setup_info['phpsysinfo']['name']      = 'phpsysinfo';
	$setup_info['phpsysinfo']['version']   = '1.7';
	$setup_info['phpsysinfo']['app_order'] = 99;
	$setup_info['phpsysinfo']['enable']    = 2;
	$setup_info['phpsysinfo']['tables']    = '';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['phpsysinfo']['hooks'][]   = 'admin';

	/* Dependencies for this app to work */
	$setup_info['phpsysinfo']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13', '0.9.14','0.9.15')
	);
?>
