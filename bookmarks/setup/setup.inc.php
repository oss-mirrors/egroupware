<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['bookmarks']['name']      = 'bookmarks';
	$setup_info['bookmarks']['title']     = 'Bookmarks';
	$setup_info['bookmarks']['version']   = '0.9.1';
	$setup_info['bookmarks']['app_order'] = '20';
	$setup_info['bookmarks']['enable']    = 1;

	$setup_info['bookmarks']['author'] = 'Joseph Engo';
	$setup_info['bookmarks']['license']  = 'GPL';
	$setup_info['bookmarks']['description'] =
		'Manage your bookmarks with phpGW.  Has Netscape plugin.';
	$setup_info['bookmarks']['maintainer'] = 'Michael Totschnig';
	$setup_info['bookmarks']['maintainer_email'] = 'michael@totschnig.org';

	/* The tables this app creates */
	$setup_info['bookmarks']['tables']	= Array(
		'phpgw_bookmarks'
	);

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['bookmarks']['hooks'][] = 'admin';
	$setup_info['bookmarks']['hooks'][] = 'preferences';

	/* Dependencies for this app to work */
	$setup_info['bookmarks']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13','0.9.14','0.9.15')
	);
?>
