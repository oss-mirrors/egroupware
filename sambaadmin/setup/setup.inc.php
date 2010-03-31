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
	$setup_info['sambaadmin']['version']	= '1.4';
	$setup_info['sambaadmin']['app_order']	= 99;

	$setup_info['sambaadmin']['author']	= 'Lars Kneschke';

	$setup_info['sambaadmin']['license']	= 'GPL';
	$setup_info['sambaadmin']['description']= 'Manage LDAP based Samba servers';

	$setup_info['sambaadmin']['maintainer'] = array(
		'name'	=> 'eGroupware coreteam',
		'email'	=> 'egroupware-developers@lists.sf.net',
	);

	$setup_info['sambaadmin']['tables']    = array();
	$setup_info['sambaadmin']['enable']    = 1;
	$setup_info['sambaadmin']['index']     = 'sambaadmin.uisambaadmin.listWorkstations';

	/* The hooks this app includes, needed for hooks registration */
	#$setup_info['sambaadmin']['hooks'][]	= 'preferences';
	$setup_info['sambaadmin']['hooks'][]	= 'edit_user';
	$setup_info['sambaadmin']['hooks'][]	= 'admin';
	$setup_info['sambaadmin']['hooks'][]	= 'changepassword';

	$setup_info['sambaadmin']['hooks']['addaccount']	= 'sambaadmin.bosambaadmin.updateAccount';
	$setup_info['sambaadmin']['hooks']['editaccount']	= 'sambaadmin.bosambaadmin.updateAccount';
	$setup_info['sambaadmin']['hooks']['addgroup']		= 'sambaadmin.bosambaadmin.updateGroup';
	$setup_info['sambaadmin']['hooks']['editgroup']		= 'sambaadmin.bosambaadmin.updateGroup';

	/* Dependacies for this app to work */
	$setup_info['sambaadmin']['depends'][]	= array(
		'appname'  => 'phpgwapi',
		'versions' => Array('1.4','1.5','1.6','1.7')
	);
