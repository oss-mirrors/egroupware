<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier     oliviert@maphilo.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	$phpgw_baseline = array(
		'chatty_sessions' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'session' => array('type' => 'blob','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id'),
			'uc' => array('id')
		)
	);
