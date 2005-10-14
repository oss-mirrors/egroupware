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

	$GLOBALS['egw']->db->query('SELECT con,display FROM phpgw_headlines_sites ORDER BY display asc',__LINE__,__FILE__);
	while($GLOBALS['egw']->db->next_record())
	{
		$_headlines[$GLOBALS['egw']->db->f('con')] = $GLOBALS['egw']->db->f('display');
	}

	$GLOBALS['settings'] = array(
		'headlines' => array(
			'type'   => 'select',
			'label'  => 'Select Headline News sites',
			'name'   => 'headlines',
			'values' => $_headlines,
			'size'   => (count($_headlines)>10 ? 10 : count($_headlines)),
			'xmlrpc' => True,
			'admin'  => False
		)
	);
?>
