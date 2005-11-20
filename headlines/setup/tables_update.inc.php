<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$test[] = '0.8.1';
	function headlines_upgrade0_8_1()
	{
		$GLOBALS['egw_setup']->oProc->RenameTable('news_site','phpgw_headlines_sites');
		$GLOBALS['egw_setup']->oProc->RenameTable('news_headlines','phpgw_headlines_cached');
		$GLOBALS['egw_setup']->oProc->DropTable('users_headlines');

		return $GLOBALS['setup_info']['headlines']['currentver'] = '0.8.1.001';
	}

	$test[] = '0.8.1.001';
	function headlines_upgrade0_8_1_001()
	{
		return $GLOBALS['setup_info']['headlines']['currentver'] = '1.0.0';
	}

	$test[] = '1.0.0';
	function headlines_upgrade1_0_0()
	{
		$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_headlines_sites','egw_headlines_sites');
		// timestamps have to be 8byte
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_headlines_sites','lastread',array('type' => 'int', 'precision' => 8));
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_headlines_sites','cachetime',array('type' => 'int', 'precision' => 8));
		
		$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_headlines_cached','egw_headlines_cached');

		return $GLOBALS['setup_info']['headlines']['currentver'] = '1.2';
	}
