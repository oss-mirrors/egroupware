<?php
	/****************************************************************************\
	* phpGroupWare - Forums                                                      *
	* http://www.phpgroupware.org                                                *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                              *
	* -------------------------------------------                                *
	*  This program is free software; you	can redistribute it and/or modify it  *
	*  under the terms of	the GNU	General	Public License as published by the *
	*  Free Software Foundation; either version 2	of the License, or (at your  *
	*  option) any later version.                                                *
	\****************************************************************************/

	/* $Id$ */

	$setup_info['forum']['name'] = 'forum';
	$setup_info['forum']['title'] = 'forum';
	$setup_info['forum']['version'] = '0.9.13.002';
	$setup_info['forum']['app_order'] = 7;
	$setup_info['forum']['enable'] = 1;

	/* the table info */
	$setup_info['forum']['tables'] = array(
		'phpgw_forum_body',
		'phpgw_forum_categories',
		'phpgw_forum_forums',
		'phpgw_forum_threads'
	);

	/* the hooks */
	$setup_info['forum']['hooks'][] = 'admin';

	/* the dependencies */
	$setup_info['forum']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array(
			'0.9.10',
			'0.9.11',
			'0.9.12',
			'0.9.13'
		)
	);
?>
