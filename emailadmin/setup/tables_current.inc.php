<?php
	/**************************************************************************\
	* EGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@phpgw.de                                               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
 	\**************************************************************************/

	/* $Id$ */

	$phpgw_baseline = array(
		'phpgw_emailadmin' => array(
			'fd' => array(
				'id' => array('type' => 'auto', 'nullable' => false),
				'mail_servername' => array('type' => 'varchar', 'precision' => 50),
				'mail_dn' => array('type' => 'varchar', 'precision' => 200),
				'use_default_ldap_server' => array('type' => 'int', 'precision' => 2),
				'ldap_servername' => array('type' => 'varchar', 'precision' => 50),
				'ldap_basedn' => array('type' => 'varchar', 'precision' => 200),
				'ldap_admindn' => array('type' => 'varchar', 'precision' => 200),
				'ldap_adminpw' => array('type' => 'varchar', 'precision' => 30),
				'description' => array('type' => 'varchar', 'precision' => 200)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
