<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	$GLOBALS['acl_manager']['workflow']['admin_workflow'] = array(
		'name' => 'Grant access to the administration of workflow elements',
		'rights' => array(
			'administer processes'   => 1
		)
	);	

	$GLOBALS['acl_manager']['workflow']['monitor_workflow'] = array(
		'name' => 'Grant access to the monitoring of workflow elements',
		'rights' => array(
			'monitoring'    => 1,
		)
	);	

