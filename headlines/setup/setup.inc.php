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

	$setup_info['headlines']['author'] = 'Mark Peters';
	$setup_info['headlines']['license']  = 'GPL';
	$setup_info['headlines']['description'] =
		'Read news site headlines.';
	$setup_info['headlines']['maintainer'] = 'Mark Peters<br>Joseph Engo';
	$setup_info['headlines']['maintainer_email'] = 'skeeter@phpgroupware.org<br>jengo@phpgroupware.org';

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
		'versions' => Array('0.9.13', '0.9.14')
	);
?>
