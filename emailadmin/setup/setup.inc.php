<?php
	/**************************************************************************\
	* phpGroupWare - QMailLDAP                                                 *
	* http://www.phpgroupware.org                                              *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@phpgw.de                                               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$setup_info['qmailldap']['name']      = 'qmailldap';
	$setup_info['qmailldap']['title']     = 'QMailLDAP';
	$setup_info['qmailldap']['version']   = '0.0.3';
	$setup_info['qmailldap']['app_order'] = 99;
	$setup_info['qmailldap']['tables']    = array('phpgw_qmailldap');
	$setup_info['qmailldap']['enable']    = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['qmailldap']['hooks'] = array
	(
		'preferences',
		'manual',
		'edit_user',
		'add_def_pref',
		'about'
	);

	/* Dependacies for this app to work */
	$setup_info['qmailldap']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13','0.9.14','0.9.15')
	);
?>
