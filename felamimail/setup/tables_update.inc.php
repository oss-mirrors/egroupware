<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$test[] = '0.8.2';
	function felamimail_upgrade0_8_2()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_felamimail_cache','to_name',array('type' => 'varchar', 'precision' => 120));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_felamimail_cache','to_address',array('type' => 'varchar', 'precision' => 120));
		
		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.8.3';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

	$test[] = '0.8.3';
	function felamimail_upgrade0_8_3()
	{

		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_felamimail_cache','attachments',array('type' => 'varchar', 'precision' => 120));
		
		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.8.4';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

?>