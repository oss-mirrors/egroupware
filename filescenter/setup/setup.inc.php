<?php
	/***************************************************************************\
	* eGroupWare - Contacts Center                                              *
	* http://www.egroupware.org                                                 *
	* Written by:                                                               *
	*  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
	*  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>                *
	*  sponsored by Think.e - http://www.think-e.com.br                         *
	* ------------------------------------------------------------------------- *
	*  This program is free software; you can redistribute it and/or modify it  *
	*  under the terms of the GNU General Public License as published by the    *
	*  Free Software Foundation; either version 2 of the License, or (at your   *
	*  option) any later version.                                               *
	\***************************************************************************/

	/* Basic information about this app */
	$setup_info['filescenter']['name']      = 'filescenter';
	$setup_info['filescenter']['title']     = 'FilesCenter';
	$setup_info['filescenter']['version']   = '0.1.9.001th';
	$setup_info['filescenter']['app_order'] = 5;
	$setup_info['filescenter']['enable']    = 1;

	$setup_info['filescenter']['author'] = 'Thyamad Projects';
	$setup_info['filescenter']['author_img'] = 'thyamad';
	$setup_info['filescenter']['author_url'] = 'http://www.thyamad.com';

	$setup_info['filescenter']['note']   = 'This tool is based in egroupware FileManager application, with some functionalities more, that include: single file sharing, compression/decompression, version control, custom fields, mime type customization, and integration with other applications.';
	$setup_info['filescenter']['license']  = 'GPL';
	$setup_info['filescenter']['description'] =	'FilesCenter is the new eGroupWare File Management Tool.';

	$setup_info['filescenter']['maintainer'] = 'Vinicius Cubas Brand';
	$setup_info['filescenter']['maintainer_email'] = 'viniciuscb@users.sourceforge.net';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['filescenter']['hooks'] = array
	(
		'admin',
		'add_def_pref',
#		'admin',
#		'deleteaccount',
		'settings',
		'sidebox_menu',
#		'personalizer',
		'preferences'
#		'verify_settings'
	);

	
	
	/* Dependencies for this app to work */
	$setup_info['filescenter']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('0.9.14','0.9.15','1.0.0','1.0.1','1.0.2','1.0.3')
	);
?>
