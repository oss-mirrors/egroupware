<?php
	/**************************************************************************\
	* eGroupWare - syncml setup                                                *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; version 2 of the License.                     *
	\**************************************************************************/

	// $Id: setup.inc.php 22031 2006-07-08 01:02:37Z ralfbecker $

	/* Basic information about this app */
	$setup_info['syncml']['name']      = 'syncml';
	$setup_info['syncml']['title']     = 'SyncML';
	$setup_info['syncml']['version']   = '0.9.6';
	$setup_info['syncml']['enable']    = 3;
	$setup_info['syncml']['app_order'] = 99;

	$setup_info['phpgwapi']['author'] = 'Lars Kneschke';
	$setup_info['phpgwapi']['note']   = 'SyncML interface for eGroupWare';
	$setup_info['phpgwapi']['license']  = 'GPL';
	$setup_info['phpgwapi']['description'] =
		'This module allows you to syncronize your SyncML enabled device.';

	$setup_info['phpgwapi']['maintainer'] = 'Lars Kneschke';
	$setup_info['phpgwapi']['maintainer_email'] = 'l.kneschke@metaways.de';
 

	/* The tables this app creates */
	$setup_info['syncml']['tables'][]  = 'egw_contentmap';
	$setup_info['syncml']['tables'][]  = 'egw_syncmldevinfo';
	$setup_info['syncml']['tables'][]  = 'egw_syncmlsummary';
	$setup_info['syncml']['tables'][]  = 'egw_syncmldeviceowner';

	/* Dependencies for this app to work */
	$setup_info['phpgwapi']['depends'][] = array(
		 'appname'  => 'phpgwapi',
		 'versions' => Array('1.3')
	);
?>