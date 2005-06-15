<?php
    /**************************************************************************\
    * eGroupWare - Skeleton Application                                        *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['browser']['name']      = 'browser';
	$setup_info['browser']['title']     = 'Browser';
	$setup_info['browser']['version']   = '0.0.1.002';
	$setup_info['browser']['app_order'] = 62;
	$setup_info['browser']['enable']    = 1;
	
	/* some info's for about.php and apps.phpgroupware.org */
	$setup_info['browser']['author']    = 'Pim Snel';
	$setup_info['browser']['license']   = 'GPL';
	$setup_info['browser']['description'] =
	'intergrated browser to surf the web within eGroupWare';
	$setup_info['browser']['note'] =
	'This app is written to demo the strength of the new template set idots2.';
	$setup_info['browser']['maintainer'] = 'Pim Snel';
	$setup_info['browser']['maintainer_email'] = 'mipmip at users.sourceforge.net';
		
	/* The tables this app creates */
	$setup_info['browser']['tables']    = array();

	/* The hooks this app includes, needed for hooks registration */
		$setup_info['browser']['hooks'][] = 'settings';
		
		$setup_info['browser']['hooks']['toolbar'] = 'browser.toolbar.toolbar';
		$setup_info['browser']['hooks']['menu'] = 'browser.browsermenu.browsermenu';


?>
