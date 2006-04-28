<?php
/**************************************************************************\
* eGoupWare - TTS							   *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

/* Basic information about this app */
$setup_info['tts']['name']      = 'tts';
$setup_info['tts']['title']     = 'Trouble Ticket System';
$setup_info['tts']['version']   = '1.2.009';
$setup_info['tts']['app_order'] = 10;
$setup_info['tts']['enable']    = 1;

$setup_info['tts']['author']     = 'Oscar van Eijk, Martin Schuster et al.';
$setup_info['tts']['license']    = 'GPL';
$setup_info['tts']['maintainer'] = array('Martin Schuster','Martin.Schuster@centerpoint.eu.com');

/* The tables this app creates */
$setup_info['tts']['tables']    = array('phpgw_tts_tickets','phpgw_tts_views','phpgw_tts_states','phpgw_tts_transitions');

/* The hooks this app includes, needed for hooks registration */
$setup_info['tts']['hooks'][]   = 'admin';
$setup_info['tts']['hooks'][]   = 'home';
$setup_info['tts']['hooks'][]   = 'preferences';
$setup_info['tts']['hooks'][]   = 'settings';
$setup_info['tts']['hooks'][]   = 'sidebox_menu';

/* Dependencies for this app to work */
$setup_info['tts']['depends'][] = array(
	 'appname' => 'phpgwapi',
	 'versions' => Array('1.0.0','1.0.1','1.2','1.3')
);

