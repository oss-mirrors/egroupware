<?php
	/**************************************************************************\
	* eGroupWare - syncml setup                                                *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; version 2 of the License.                     *
	\**************************************************************************/

	// $Id$

	/* Basic information about this app */
	$setup_info['syncml']['name']      = 'syncml';
	$setup_info['syncml']['title']     = 'SyncML';
	$setup_info['syncml']['version']   = '0.9.6';
	$setup_info['syncml']['enable']    = 3;
	$setup_info['syncml']['app_order'] = 99;

	$setup_info['syncml']['author'] = 'Lars Kneschke';
	$setup_info['syncml']['note']   = 'SyncML interface for eGroupWare';
	$setup_info['syncml']['license']  = 'GPL';
	$setup_info['syncml']['description'] =
		'This module allows you to syncronize your SyncML enabled device.';

	$setup_info['syncml']['maintainer'] = 'Lars Kneschke';
	$setup_info['syncml']['maintainer_email'] = 'l.kneschke@metaways.de';
 

	/* The tables this app creates */
	$setup_info['syncml']['tables'][]  = 'egw_contentmap';
	$setup_info['syncml']['tables'][]  = 'egw_syncmldevinfo';
	$setup_info['syncml']['tables'][]  = 'egw_syncmlsummary';
	$setup_info['syncml']['tables'][]  = 'egw_syncmldeviceowner';

	/* Dependencies for this app to work */
	$setup_info['syncml']['depends'][] = array(
		 'appname'  => 'phpgwapi',
		 'versions' => Array('1.3')
	);
	// installation checks for SyncML
	$setup_info['syncml']['check_install'] = array(
		'' => array(
			'func' => 'pear_check',
			'from' => 'SyncML',
		),
		'Log' => array(
			'func' => 'pear_check',
			'from' => 'SyncML',
		),
	);