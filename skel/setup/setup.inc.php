<?php
    /**************************************************************************\
    * eGroupWare - Skeleton Application                                        *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
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
	$setup_info['skel']['app_order'] = 62;
	$setup_info['skel']['enable']    = 1;
	
	/* some info's for about.php and apps.egroupware.org */
	$setup_info['skel']['author']    = 'Your Name';
	$setup_info['skel']['license']   = 'GPL';
	$setup_info['skel']['description'] =
		'the description here is needed';
	$setup_info['skel']['note'] =
		'Some more text shown below the description in italics.';
	$setup_info['skel']['maintainer'] = 'eGroupWare developers';
	$setup_info['skel']['maintainer_email'] = 'shrykedude at users.sourceforge.net';
	
	/* The tables this app creates */
	$setup_info['skel']['tables']    = array('phpgw_skel');

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['skel']['hooks'] = array(
		'preferences',
		'manual',
		'add_def_prefs'
	);

	/* Dependacies for this app to work */
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'phpgwapi',
			 'versions' => array('0.9.14','1.0.0','1.0.1')
		);
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'email',
			 'versions' => array('0.9.13', '0.9.14','1.0.0')
		);
?>
