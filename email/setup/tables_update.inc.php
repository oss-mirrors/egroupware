<?php
	/**************************************************************************\
	* Anglemail - setup files for phpGroupWare - DB Table Update			*
	* http://www.anglemail.org							*
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/*
	$test[] = '0.9.13.002';
	function email_upgrade0_9_13_002()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_anglemail' => array(
				'fd' => array(
					'account_id' => array('type' => 'varchar', 'precision' => 20, 'nullable' => false),
					'data_key' => array('type' => 'varchar', 'precision' => 255, 'nullable' => False, 'default' => ''),
					'content' => array('type' => 'text', 'nullable' => False, 'default' => ''),
				),
				'pk' => array('account_id', 'data_key'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);
		
		//$GLOBALS['setup_info']['email']['currentver'] = '0.9.13.110805';
		$GLOBALS['setup_info']['email']['currentver'] = '0.9.13.003';
		return $GLOBALS['setup_info']['email']['currentver'];
	}
	*/
	
	
	$test[] = '0.9.13.002';
	function email_upgrade0_9_13_002()
	{
		global $setup_info, $phpgw_setup;

		$phpgw_setup->oProc->CreateTable(
			'phpgw_anglemail', array(
				'fd' => array(
					'account_id' => array('type' => 'varchar', 'precision' => 20, 'nullable' => false),
					'data_key' => array('type' => 'varchar', 'precision' => 255, 'nullable' => False, 'default' => ''),
					'content' => array('type' => 'text', 'nullable' => False, 'default' => ''),
				),
				'pk' => array('account_id', 'data_key'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);

		//$setup_info['email']['currentver'] = '0.9.13.110805';
		$setup_info['email']['currentver'] = '0.9.13.003';
		return $setup_info['email']['currentver'];
	}
	

?>
