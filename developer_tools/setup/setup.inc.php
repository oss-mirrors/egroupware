<?php
	/**************************************************************************\
	* phpGroupWare - Developer tools                                           *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['developer_tools']['name']      = 'developer_tools';
	$setup_info['developer_tools']['title']     = 'TranslationTools';
	$setup_info['developer_tools']['version']   = '0.8.1';
	$setup_info['developer_tools']['app_order'] = 8;
	$setup_info['developer_tools']['enable']    = 1;

	$setup_info['developer_tools']['author'] = 'Miles Lott';
	$setup_info['developer_tools']['description'] =
		'The TranslationTools allow to create and extend translations-files for phpGroupWare. 
		They can search the sources for new / added phrases and show you the ones missing in your language.';
	$setup_info['developer_tools']['note'] =
		'Reworked and imporved version of the former language-management of Miloschi\'s the developer_tools.';
	$setup_info['developer_tools']['license']  = 'GPL';
	$setup_info['developer_tools']['maintainer'] = 'Ralf Becker';
	$setup_info['developer_tools']['maintainer_email']    = 'RalfBecker@outdoor-training.de';

	/* The tables this app creates */
	$setup_info['developer_tools']['tables']    = array();

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['developer_tools']['hooks']     = array();

	/* Dependencies for this app to work */
	$setup_info['developer_tools']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.11','0.9.12','0.9.13', '0.9.14')
	);
?>
