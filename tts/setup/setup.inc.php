<?php
	/**************************************************************************\
	* phpGroupWare - Addressbook                                               *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['tts']['name']      = 'tts';
	$setup_info['tts']['title']     = 'Trouble Ticket System';
	$setup_info['tts']['version']   = '0.8.1.003';
	$setup_info['tts']['app_order'] = 99;
	$setup_info['tts']['enable']    = 1;

	/* The tables this app creates */
	$setup_info['tts']['tables']    = array(
		'phpgw_tts_tickets',
		'phpgw_tts_views'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['tts']['hooks'][]   = 'admin';
	$setup_info['tts']['hooks'][]   = 'home';
	$setup_info['tts']['hooks'][]   = 'manual';
	$setup_info['tts']['hooks'][]   = 'preferences';
	$setup_info['tts']['hooks'][]   = 'settings';

	/* Dependencies for this app to work */
	$setup_info['tts']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13')
	);
?>
