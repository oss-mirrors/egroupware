<?php
	/**************************************************************************\
	* eGroupWare - phpldapadmin                                                *
	* http://www.eGroupWare.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$setup_info['phpldapadmin']['name']      = 'phpldapadmin';
	$setup_info['phpldapadmin']['version']   = '0.9.1';
	$setup_info['phpldapadmin']['app_order'] = 101;
	$setup_info['phpldapadmin']['tables']    = array();
	$setup_info['phpldapadmin']['enable']    = 1;

	$setup_info['phpldapadmin']['author'] = array(
		'name' => 'phpldapadmin project',
		'email' => 'phpldapadmin-devel@lists.sourceforge.net'
	);
 	$setup_info['phpldapadmin']['maintainer'] = array(
		'name'  => 'Ralf Becker',
		'email' => 'ralfbecker@outdoor-training.de'
	);
	$setup_info['phpldapadmin']['license']  = 'GPL';
	$setup_info['phpldapadmin']['description'] =
		'A comprehensiv LDAP administration tool.';
	$setup_info['phpldapadmin']['note'] =
		'For more info visit <a href="http://phpldapadmin.sourceforge.net/">Homepage</a> of the phpldapadmin project.';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['phpldapadmin']['hooks'] = array(
	);

	/* Dependencies for this app to work */
	$setup_info['phpldapadmin']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.16')
	);
?>
