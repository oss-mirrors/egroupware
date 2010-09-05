<?php
	/**************************************************************************\
	* eGroupWare - Mydms                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* Basic information about this app */
	$setup_info['mydms']['name']      = 'mydms';
	$setup_info['mydms']['title']     = 'mydms';
	$setup_info['mydms']['version']   = '1.4';
	$setup_info['mydms']['app_order'] = 5;
	$setup_info['mydms']['enable']    = 1;

	$setup_info['mydms']['author'] = 'Real SoftService';
	$setup_info['mydms']['note']   = 'DMS(Document Management System for Egroupware';
	$setup_info['mydms']['license']  = 'GPL';
	$setup_info['mydms']['description'] =
		'This module is ported from project mydms';

	$setup_info['mydms']['maintainer'] = 'Lars Kneschke';
	$setup_info['mydms']['maintainer_email'] = 'l.kneschke@metaways.de';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['mydms']['hooks'][] = 'sidebox_menu';
	$setup_info['mydms']['hooks'][] = 'settings'; //tim
	$setup_info['mydms']['hooks']['search_link'] = 'mydms.bomydms.search_link'; //tim

    /* The tables this app creates */
	$setup_info['mydms']['tables']    = array(
		'phpgw_mydms_ACLs',
		'phpgw_mydms_DocumentContent',
		'phpgw_mydms_Documents',
		'phpgw_mydms_Folders',
		'phpgw_mydms_GroupMembers',
		'phpgw_mydms_Groups',
		'phpgw_mydms_Notify',
		'phpgw_mydms_DocumentLinks',
		'phpgw_mydms_Sessions',
		'phpgw_mydms_UserImages',
		'phpgw_mydms_Users',
		'phpgw_mydms_KeywordCategories',
		'phpgw_mydms_Keywords'
	);
	$setup_info['mydms']['only_db'] = array('mysql');

	/* Dependencies for this app to work */
	$setup_info['mydms']['depends'][] = array(
		 'appname'  => 'phpgwapi',
		 'versions' => Array('1.4','1.5','1.6','1.7','1.8','1.9')
	);
