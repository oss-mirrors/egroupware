<?php
	/**************************************************************************\
	* eGroupWare - PHPBrain                                                    *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['workflow']['name']      = 'workflow';
	$setup_info['workflow']['title']     = 'Workflow management';
	$setup_info['workflow']['version']   = '1.0.0';
	$setup_info['workflow']['app_order'] = 10;
	$setup_info['workflow']['enable']    = 1;

	$setup_info['workflow']['author'] = 'Ported from tikiwiki';
	$setup_info['workflow']['note']   = 'Workflow engine';
	$setup_info['workflow']['license']  = 'GPL';
	$setup_info['workflow']['description'] = 'Workflow management';
	$setup_info['workflow']['maintainer'] = 'Alejandro Pedraza';
	$setup_info['workflow']['maintainer_email'] = 'alpeb AT users.sourceforge DOT net';
	$setup_info['workflow']['tables'] = array('egw_wf_activities', 'egw_wf_activity_roles', 'egw_wf_instance_activities', 'egw_wf_instance_comments', 'egw_wf_instances', 'egw_wf_processes', 'egw_wf_roles', 'egw_wf_transitions', 'egw_wf_user_roles', 'egw_wf_workitems');

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['workflow']['hooks'][] = 'about';
	$setup_info['workflow']['hooks'][] = 'admin';
	$setup_info['workflow']['hooks'][] = 'add_def_pref';
	$setup_info['workflow']['hooks'][] = 'config';
	$setup_info['workflow']['hooks'][] = 'manual';
	$setup_info['workflow']['hooks'][] = 'preferences';
	$setup_info['workflow']['hooks'][] = 'settings';
	$setup_info['workflow']['hooks'][] = 'sidebox_menu';

	/* Dependencies for this app to work */
	$setup_info['workflow']['depends'][] = array(
		'appname' => 'phpgwapi',
		'versions' => Array('0.9.13', '0.9.14', '0.9.15', '1.0.0', '1.0.1')
	);
?>
