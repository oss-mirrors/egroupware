<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/* Basic information about this app */
	$setup_info['filescenter']['name']      = 'filescenter';
	$setup_info['filescenter']['title']     = 'FilesCenter';
	$setup_info['filescenter']['version']   = '0.1.6';
	$setup_info['filescenter']['app_order'] = 5;
	$setup_info['filescenter']['enable']    = 1;

	$setup_info['filescenter']['author'] = 'Vinicius Cubas Brand';
	$setup_info['filescenter']['note']   = 'FilesCenter is the eGroupWare File Management Tool.';
	$setup_info['filescenter']['license']  = 'GPL';
	$setup_info['filescenter']['description'] =
		'Extended functionalities to be implemented.';

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

	
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_mimetypes';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_quota';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_files';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_shares';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_versioning';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_customfields';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_customfields_data';
	$setup_info['filescenter']['tables'][] = 'phpgw_vfs2_prefixes';


	
	/* Dependencies for this app to work */
	$setup_info['filescenter']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('0.9.14','0.9.15','1.0.0','1.0.1')
	);
?>
