<?php
	/**************************************************************************\
	* phpGroupWare - Forum                                                      *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['forum']['name']      = 'forum';
	$setup_info['forum']['title']     = 'forum';
	$setup_info['forum']['version']   = '0.8.1';
	$setup_info['forum']['app_order'] = 4;
	$setup_info['forum']['enable']    = 1;

	/* The tables this app creates */
	$setup_info['forum']['tables']    = array(
		'f_body',
		'f_categories',
		'f_forums',
		'f_threads'
	);

	$setup_info['forum']['hooks'][]   = array('admin');
	/* Dependencies for this app to work */
	$setup_info['forum']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13')
	);
?>
