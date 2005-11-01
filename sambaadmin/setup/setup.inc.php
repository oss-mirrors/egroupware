<?php
	/**************************************************************************\
	* eGroupWare - SambaAdmin                                                  *
	* http://www.egroupware.org                                                *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@linux-at-work.de                                       *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; version 2 of the License.                     *
	\**************************************************************************/

	/* $Id$ */

	$setup_info['sambaadmin']['name']	= 'sambaadmin';
	$setup_info['sambaadmin']['title']	= 'SambaAdmin';
	$setup_info['sambaadmin']['version']	= '0.0.1';
	$setup_info['sambaadmin']['app_order']	= 99;

	$setup_info['sambaadmin']['author']	= array(
		'name'	=> 'Lars Kneschke',
		'email'	=> 'lkneschke@users.sourceforge.net',
	);

	$setup_info['sambaadmin']['license']	= 'GPL';
	$setup_info['sambaadmin']['description']= 'Manage LDAP based Samba servers';

	$setup_info['sambaadmin']['maintainer'] = array(
		'name'	=> 'Lars Kneschke',
		'email'	=> 'lkneschke@linux-at-work.de',
	);


	$setup_info['sambaadmin']['tables']    = array();
	$setup_info['sambaadmin']['enable']    = 1;

	/* The hooks this app includes, needed for hooks registration */
	#$setup_info['sambaadmin']['hooks'][]	= 'preferences';
	$setup_info['sambaadmin']['hooks'][]	= 'edit_user';
	$setup_info['sambaadmin']['hooks'][]	= 'admin';
	$setup_info['sambaadmin']['hooks'][]	= 'changepassword';
	
	$setup_info['sambaadmin']['hooks']['addaccount']	= 'sambaadmin.bosambaadmin.updateAccount';
	$setup_info['sambaadmin']['hooks']['editaccount']	= 'sambaadmin.bosambaadmin.updateAccount';
	$setup_info['sambaadmin']['hooks']['editgroup']		= 'sambaadmin.bosambaadmin.updateGroup';

	/* Dependacies for this app to work */
	$setup_info['sambaadmin']['depends'][]	= array(
		'appname'  => 'phpgwapi',
		'versions' => Array('1.0.0','1.0.1')
	);
?>
