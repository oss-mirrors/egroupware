<?php
	/**************************************************************************\
	* phpGroupWare - Notes                                                     *
	* http://www.phpgroupware.org                                              *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */
	
	// this is to get css inclusion working
	$_GET['menuaction']     = 'sambaadmin.uisambaadmin.listWorkstations';
	                
	$phpgw_info['flags'] = array
	(
		'currentapp' => 'sambaadmin',
		'noheader'   => True,
		'nonavbar'   => True
	);
	include('../header.inc.php');
	
	ExecMethod('sambaadmin.uisambaadmin.listWorkstations');
?>
