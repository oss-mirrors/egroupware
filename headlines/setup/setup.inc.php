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
	$setup_info['headlines']['version']   = '0.8.1';
	$setup_info['headlines']['app_order'] = 19;

	/* The tables this app creates */
	$setup_info['headlines']['tables']    = array(
		'news_site',
		'news_headlines',
		'users_headlines'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['headlines']['hooks'][] = 'preferences';
	$setup_info['headlines']['hooks'][] = 'admin';

	/* Dependencies for this app to work */
	$setup_info['headlines']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.11','0.9.12','0.9.13')
	);
?>
