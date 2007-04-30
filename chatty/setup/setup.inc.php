<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier     oliviert@maphilo.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* Basic information about this app */
	$setup_info['chatty']['name']      = 'chatty';
	$setup_info['chatty']['title']     = 'Chatty';
	$setup_info['chatty']['version']   = '1.0.004';
	$setup_info['chatty']['app_order'] = '6';
	$setup_info['chatty']['enable']    = 2;

	$setup_info['chatty']['author'] = 'Olivier TITECA-BEAUPORT';
	$setup_info['chatty']['license']  = 'GPL';
	$setup_info['chatty']['description'] =
		'Chat system for eGroupware.';
	$setup_info['chatty']['maintainer'] = array(
		'name' => 'EGFU Developers',
		'email' => 'oliviert@maphilo.com'
	);

	$setup_info['chatty']['hooks']['after_navbar'] = 'hook_after_navbar.inc.php';
	/* Dependencies for this app to work */
	$setup_info['chatty']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('1.2','1.3','1.4','1.5')
	);

	$setup_info['chatty']['tables'] = array('chatty_sessions','chatty_msgs');



