<?php
	/**************************************************************************\
	* phpGroupWare - Comics                                                    *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['comic']['name']      = 'comic';
	$setup_info['comic']['title']     = 'Comics';
	$setup_info['comic']['version']   = '0.0.1';
	$setup_info['comic']['app_order'] = 21;
	$setup_info['comic']['enable']    = 1;

	/* The tables this app creates */
	$setup_info['comic']['tables']    = array(
		'phpgw_comic',
		'phpgw_comic_admin',
		'phpgw_comic_data'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['comic']['hooks'][] = 'admin';
	$setup_info['comic']['hooks'][] = 'home';
	$setup_info['comic']['hooks'][] = 'preferences';

	/* Dependencies for this app to work */
	$setup_info['comic']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13', '0.9.14', '0.9.15')
	);
?>
