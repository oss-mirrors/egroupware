<?php
	/**************************************************************************\
	* phpGroupWare - Headlines                                                 *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['headlines']['name']      = 'headlines';
	$setup_info['headlines']['title']     = 'Headlines';
	$setup_info['headlines']['version']   = '0.8.1.001';
	$setup_info['headlines']['app_order'] = 19;
	$setup_info['headlines']['enable']    = 1;

	/* The tables this app creates */
	$setup_info['headlines']['tables']    = array(
		'phpgw_headlines_sites',
		'phpgw_headlines_cached'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['headlines']['hooks'][]   = 'admin';
	$setup_info['headlines']['hooks'][]   = 'manual';
	$setup_info['headlines']['hooks'][]   = 'settings';
	$setup_info['headlines']['hooks'][]   = 'preferences';

	/* Dependencies for this app to work */
	$setup_info['headlines']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13', '0.9.14','0.9.15')
	);
?>
