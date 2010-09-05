<?php
  /**
   * @file EGroupware - IcalSrv setup file
   * @package IcalSrv
   * @version 0.0.2
   * @author erics
   * @author jvl
   */
  /* homepage @url http://www.egroupware.org                                  *
   * ---------------------------------------- ---                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   **************************************************************************/

	/* Id$ */
	/* Basic information about this app */
	$setup_info['icalsrv']['name']      = 'icalsrv';
	$setup_info['icalsrv']['title']     = 'Ical Server';
	$setup_info['icalsrv']['version']   = '1.4';
	$setup_info['icalsrv']['app_order'] = 8;
	$setup_info['icalsrv']['enable']    = 2;
	$setup_info['icalsrv']['author'] = 'Jan van Lieshout (JVL)';
	$setup_info['icalsrv']['maintainer'] = array(
		'name'  => 'Jan van Lieshout',
		'email' => 'prito@users.sourceforge.net'
	);
	$setup_info['icalsrv']['license']  = 'GPL';
	$setup_info['icalsrv']['description'] = 'A Service that provides access to  eGroupware Calendar- and Infolog data'
		. ' via the socalled "iCalendar-over-HTTP" protocol.';
	$setup_info['icalsrv']['note'] = 'The developement of the iCal Service was sponsored by:<ul>
<li> <a href="http://www.wizwise.com" target="_blank">WizWise Technology</a></li>
</ul>';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['icalsrv']['hooks'][] = 'preferences';
	$setup_info['icalsrv']['hooks'][] = 'admin';
	$setup_info['icalsrv']['hooks'][] = 'sidebox_menu';

	/* Dependencies for this app to work */
	$setup_info['icalsrv']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('1.4','1.5','1.6','1.7','1.8','1.9')
	);
