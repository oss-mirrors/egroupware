<?php
	/**************************************************************************\
	* eGroupWare - Preferences                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$default_view = array(
		'threads'   => lang('Threaded'),
		'collapsed' => lang('collapsed')
	);
	$GLOBALS['settings'] = array(
		'default_view' => array(
			'type'   => 'select',
			'label'  => 'Default view',
			'name'   => 'default_view',
			'values' => $default_view,
			'xmlrpc' => True,
			'admin'  => False
		)
	);
