<?php
  /*************************************************************************\
  * phpGroupWare Setup - Projects                                           *
  * http://www.phpgroupware.org                                             *
  * --------------------------------------------                            *
  * This program is free software; you can redistribute it and/or modify it *
  * under the terms of the GNU General Public License as published by the   *
  * Free Software Foundation; either version 2 of the License, or (at your  *
  * option) any later version.                                              *
  \*************************************************************************/
  /* $Id$ */

	$setup_info['projects']['name']      = 'projects';
	$setup_info['projects']['version']   = '0.8.7.009';
	$setup_info['projects']['app_order'] = 13;
	$setup_info['projects']['enable']    = 1;

	$setup_info['projects']['author'] = array
	(
		'name'	=> 'Bettina Gille',
		'email'	=> 'ceb@phpgroupware.org'
	);

	$setup_info['projects']['license']  = 'GPL';
	$setup_info['projects']['description'] = 'Advanced project management';

	$setup_info['projects']['maintainer'] = $setup_info['projects']['author'];

	$setup_info['projects']['tables'] = array
	(
		'phpgw_p_projects',
		'phpgw_p_activities',
		'phpgw_p_projectactivities',
		'phpgw_p_hours',
		'phpgw_p_projectmembers',
		'phpgw_p_invoice',
		'phpgw_p_invoicepos',
		'phpgw_p_delivery',
		'phpgw_p_deliverypos',
		'phpgw_p_pcosts',
		'phpgw_p_mstones'
	);

/* The hooks this app includes, needed for hooks registration */

	$setup_info['projects']['hooks'] = array
	(
		'sidebox_menu' => 'projects.uiprojects.hook_sidebox_menu',
		'preferences',
		'admin',
		'manual',
		'add_def_pref',
		'deleteaccount',
		'home'
	);

/* Dependencies for this app to work */

	$setup_info['projects']['depends'][] = array
	(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.14','0.9.15','0.9.16','0.9.17')
	);

	$setup_info['projects']['depends'][] = array
	(
		 'appname' => 'admin',
		 'versions' => Array('0.9.13','0.9.14')
	);

	$setup_info['projects']['depends'][] = array
	(
		 'appname' => 'preferences',
		 'versions' => Array('0.9.13','0.9.14')
	);

	$setup_info['projects']['depends'][] = array
	(
		 'appname' => 'addressbook',
		 'versions' => Array('0.9.13','0.9.14')
	);
?>
