<?php
	/**************************************************************************\
	* phpGroupWare - Manual                                                    *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['manual']['name']      = 'manual';
	$setup_info['manual']['version']   = '0.9.13.002';
	$setup_info['manual']['app_order'] = 5;
	$setup_info['manual']['enable']    = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['manual']['hooks'][] = 'help';

	/* Dependencies for this app to work */
	$setup_info['manual']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.15')
	);
?>
