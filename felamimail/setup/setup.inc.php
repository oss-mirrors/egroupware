<?php
	/**************************************************************************\
	* phpGroupWare - FeLaMiMail                                                *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$setup_info['felamimail']['name']      = 'felamimail';
	$setup_info['felamimail']['title']     = 'FeLaMiMail';
	$setup_info['felamimail']['version']	= '0.8.3';
	$setup_info['felamimail']['app_order'] = 2;
	$setup_info['felamimail']['tables']    = array(
		'phpgw_felamimail_cache',
		'phpgw_felamimail_folderstatus'
	);
	$setup_info['felamimail']['enable']    = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['felamimail']['hooks'][] = 'about';
	$setup_info['felamimail']['hooks'][] = 'admin';
	$setup_info['felamimail']['hooks'][] = 'home';
	$setup_info['felamimail']['hooks'][] = 'preferences';

	/* Dependacies for this app to work */
	$setup_info['felamimail']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13','0.9.14','0.9.15')
	);
?>
