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
	$setup_info['projects']['title']     = 'Projects';
	$setup_info['projects']['version']   = '0.8.5.007';
	$setup_info['projects']['app_order'] = 13;
	$setup_info['projects']['enable']    = 1;

	$setup_info['projects']['tables'] = array(
		'phpgw_p_projects',
		'phpgw_p_activities',
		'phpgw_p_projectactivities',
		'phpgw_p_hours',
		'phpgw_p_projectmembers',
		'phpgw_p_invoice',
		'phpgw_p_invoicepos',
		'phpgw_p_delivery',
		'phpgw_p_deliverypos'
	);

/* The hooks this app includes, needed for hooks registration */

	$setup_info['projects']['hooks'] = array
	(
		'preferences',
		'admin',
		'about',
		'manual',
		'add_def_pref'
	);

/* Dependacies for this app to work */

	$setup_info['projects']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13','0.9.14')
	);
?>
