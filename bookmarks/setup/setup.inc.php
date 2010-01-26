<?php
	/**************************************************************************\
	* eGroupWare - Bookmarks                                                   *
	* http://www.egroupware.org                                                *
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
	$setup_info['bookmarks']['version']   = '1.7.001';
	$setup_info['bookmarks']['app_order'] = '12';
	$setup_info['bookmarks']['enable']    = 1;

	$setup_info['bookmarks']['author'] = 'Joseph Engo';
	$setup_info['bookmarks']['license']  = 'GPL';
	$setup_info['bookmarks']['description'] =
		'Manage your bookmarks with eGW.  Has Netscape plugin.';
	$setup_info['bookmarks']['maintainer'] = array(
		'name' => 'eGroupWare Developers',
		'email' => 'egroupware-developers@lists.sourceforge.net'
	);

	/* The tables this app creates */
	$setup_info['bookmarks']['tables'][] = 'egw_bookmarks';
	$setup_info['bookmarks']['tables'][] = 'egw_bookmarks_extra';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['bookmarks']['hooks']['preferences'] = 'bookmarks_hooks::all_hooks';
	$setup_info['bookmarks']['hooks']['settings'] = 'bookmarks_hooks::settings';
	$setup_info['bookmarks']['hooks']['admin'] = 'bookmarks_hooks::all_hooks';
	$setup_info['bookmarks']['hooks']['sidebox_menu'] = 'bookmarks_hooks::all_hooks';
	$setup_info['bookmarks']['hooks']['search_link'] = 'bookmarks_hooks::search_link';


	/* Dependencies for this app to work */
	$setup_info['bookmarks']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('1.2','1.3','1.4','1.5','1.6','1.7')
	);

