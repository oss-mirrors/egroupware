<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['skel']['name']      = 'skel';
	$setup_info['skel']['title']     = 'Skeleton';
	$setup_info['skel']['version']   = '0.0.1.001';
	$setup_info['skel']['app_order'] = 8;
	$setup_info['skel']['enable']    = 1;
	
	/* The tables this app creates */
	$setup_info['skel']['tables']    = Array('phpgw_skel');

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['skel']['hooks'] = Array(
		'preferences',
		'manual',
		'add_def_prefs'
	);

	/* Dependacies for this app to work */
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'phpgwapi',
			 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13', '0.9.14')
		);
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'email',
			 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13', '0.9.14')
		);
?>
