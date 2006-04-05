<?php
	/* @file EGroupware - IcalSrv setup file 
	* @package IcalSrv 
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* @version 0.0.1
	* @author erics
	**************************************************************************/

    /* $Id$ */
	/* Basic information about this app */
	$setup_info['icalsrv']['name']      = 'icalsrv';
	$setup_info['icalsrv']['title']     = 'Ical Server';
	$setup_info['icalsrv']['version']   = '0.9.30';
	$setup_info['icalsrv']['app_order'] = 3;
	$setup_info['icalsrv']['enable']    = 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['icalsrv']['hooks'][] = 'preferences';
	$setup_info['icalsrv']['hooks'][] = 'admin';
	$setup_info['icalsrv']['hooks'][] = 'sidebox_menu';

	/* Dependencies for this app to work */
	$setup_info['icalsrv']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('1.2')
		);
#  sorry Egwical is not yet an application (and probably never will...)
#  It should go into phpgwapi. However: IcalSRV DOES DEPEND ON EgwIcal!!!
#	$setup_info['icalsrv']['depends'][] = array(
#		'appname' => 'egwical',
#		'versions' => Array('0.9.30')
#		);

?>
