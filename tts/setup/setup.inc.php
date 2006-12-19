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
$setup_info['tts']['version']   = '1.3.001';
$setup_info['tts']['app_order'] = 10;
$setup_info['tts']['enable']    = 1;

$setup_info['tts']['author']     = 'Drago Bokal, Joao, Oscar van Eijk et al.';
$setup_info['tts']['license']    = 'GPL';
$setup_info['tts']['maintainer'] = array('Oscar van Eijk','oscar@oveas.com');

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
	 'versions' => Array('1.3','1.4')
);

