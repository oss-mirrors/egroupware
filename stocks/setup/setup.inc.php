<?php
    /**************************************************************************\
    * phpGroupWare - Stock Quotes                                              *
    * http://www.phpgroupware.org                                              *
    * --------------------------------------------                             *
    * This program is free software; you can redistribute it and/or modify it  *
    * under the terms of the GNU General Public License as published by the    *
    * Free Software Foundation; either version 2 of the License, or (at your   *
    * option) any later version.                                               *
    /**************************************************************************\
    /* $Id$ */

	$setup_info['stocks']['name']      = 'stocks';
	$setup_info['stocks']['version']   = '0.8.3.002';
	$setup_info['stocks']['app_order'] = 24;
	$setup_info['stocks']['enable']    = 1;

	$setup_info['stocks']['tables'] = array('phpgw_stocks');

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['stocks']['hooks'] = array
	(
		'preferences',
		'manual',
		'home',
		'add_def_pref',
		'about'
	);

	/* Dependencies for this app to work */
	$setup_info['stocks']['depends'][] = array
	(
		 'appname' => 'preferences',
		 'versions' => Array('0.9.13','0.9.14','0.9.15')
	);

	$setup_info['stocks']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13', '0.9.14','0.9.15')
	);
?>
