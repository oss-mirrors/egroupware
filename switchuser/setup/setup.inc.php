<?php
    /**************************************************************************\
    * eGroupWare - switchuser Application                                      *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License.              *
    \**************************************************************************/

	/* Basic information about this app */
	$setup_info['switchuser']['name']      = 'switchuser';
	$setup_info['switchuser']['title']     = 'Switch User';
	$setup_info['switchuser']['version']   = '0.0.1.002';
	$setup_info['switchuser']['app_order'] = 62;
	$setup_info['switchuser']['enable']    = 1;
	
	$setup_info['switchuser']['author']    = 'Pim Snel';
	$setup_info['switchuser']['license']   = 'GPL';
	$setup_info['switchuser']['description'] =
	'With this application you can login as other user without knowing or changing any passwords. Only administrators can use this application';
	$setup_info['switchuser']['note'] = '';
	$setup_info['switchuser']['maintainer'] = 'Pim Snel';
	$setup_info['switchuser']['maintainer_email'] = 'pim@lingewoud.nl';
	
	/* The tables this app creates */
	$setup_info['switchuser']['tables']    = '';

	/* The hooks this app includes, needed for hooks registration */
	#$setup_info['switchuser']['hooks'] = array();

	/* Dependacies for this app to work */
	$setup_info['switchuser']['depends'][] = array(
			 'appname' => 'phpgwapi',
			 'versions' => array('0.9.14','1.0.0','1.0.1')
		);
?>
