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

	$test[] = '1.0.0';
	function chatty_upgrade1_0_0()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable('chatty_msgs',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'session_id' => array('type' => 'int','precision' => '4','nullable' => False),
				'heure' => array('type' => 'timestamp','nullable' => False),
				'texte' => array('type' => 'longtext'),
				'statut' => array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','session_id'),
			'uc' => array('id')
		));

		$GLOBALS['setup_info']['chatty']['currentver'] = '1.0.001';
		return $GLOBALS['setup_info']['chatty']['currentver'];
	}


	$test[] = '1.0.001';
	function chatty_upgrade1_0_001()
	{
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('chatty_msgs',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'session_id' => array('type' => 'int','precision' => '4','nullable' => False),
				'heure' => array('type' => 'timestamp','nullable' => False),
				'texte' => array('type' => 'longtext'),
				'statut' => array('type' => 'int','precision' => '4','nullable' => False),
				'userid' => array('type' => 'varchar','precision' => '255','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','session_id','userid'),
			'uc' => array('id')
		));

		$GLOBALS['setup_info']['chatty']['currentver'] = '1.0.002';
		return $GLOBALS['setup_info']['chatty']['currentver'];
	}


	$test[] = '1.0.002';
	function chatty_upgrade1_0_002()
	{
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('chatty_msgs',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'session_id' => array('type' => 'int','precision' => '4','nullable' => False),
				'heure' => array('type' => 'timestamp','nullable' => False),
				'texte' => array('type' => 'longtext'),
				'statut' => array('type' => 'int','precision' => '4','nullable' => False),
				'userid' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'sender' => array('type' => 'varchar','precision' => '255','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','session_id','userid','sender'),
			'uc' => array('id')
		));

		$GLOBALS['setup_info']['chatty']['currentver'] = '1.0.003';
		return $GLOBALS['setup_info']['chatty']['currentver'];
	}


	$test[] = '1.0.003';
	function chatty_upgrade1_0_003()
	{
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('chatty_msgs',array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'session_id' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'heure' => array('type' => 'timestamp','nullable' => False),
				'texte' => array('type' => 'longtext'),
				'statut' => array('type' => 'int','precision' => '4','nullable' => False),
				'userid' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'sender' => array('type' => 'varchar','precision' => '255','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','userid','sender'),
			'uc' => array('id')
		));

		$GLOBALS['setup_info']['chatty']['currentver'] = '1.0.004';
		return $GLOBALS['setup_info']['chatty']['currentver'];
	}
?>
