<?php
	/**************************************************************************\
	* EGroupWare - EMailAdmin                                                  *
	* http://www.egroupware.org                                                *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@egroupware.org                                         *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$setup_info['emailadmin']['name']      = 'emailadmin';
	$setup_info['emailadmin']['title']     = 'EMailAdmin';
	$setup_info['emailadmin']['version']   = '0.0.6';
	$setup_info['emailadmin']['app_order'] = 10;
	$setup_info['emailadmin']['enable']    = 1;

	$setup_info['emailadmin']['author'] = 'Lars Kneschke';
	$setup_info['emailadmin']['license']  = 'GPL';
	$setup_info['emailadmin']['description'] =
		'A central Mailserver management application for EGroupWare.';
	$setup_info['emailadmin']['note'] =
		'';
	$setup_info['emailadmin']['maintainer'] = array(
		'name'  => 'Lars Kneschke',
		'email' => 'lkneschke@linux-at-work.de'
	);



	$setup_info['emailadmin']['tables'][]	= 'phpgw_emailadmin';
	
	/* The hooks this app includes, needed for hooks registration */
	$setup_info['emailadmin']['hooks'][] = 'preferences';
	$setup_info['emailadmin']['hooks'][] = 'manual';
	$setup_info['emailadmin']['hooks'][] = 'edit_user';
	$setup_info['emailadmin']['hooks'][] = 'editaccount';
	$setup_info['emailadmin']['hooks'][] = 'addaccount';
	$setup_info['emailadmin']['hooks'][] = 'deleteaccount';
	$setup_info['emailadmin']['hooks'][] = 'admin';

	/* Dependacies for this app to work */
	$setup_info['emailadmin']['depends'][] = array(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.14','0.9.16')
	);
?>
