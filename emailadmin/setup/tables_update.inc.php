<?php
	/**************************************************************************\
	* EGroupWare - EMailadmin                                                  *
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

	$test[] = '0.0.3';
	function emailadmin_upgrade0_0_3()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','smtpType', array('type' => 'int', 'precision' => 4));		

		return $setup_info['emailadmin']['currentver'] = '0.0.4';
	}

	$test[] = '0.0.4';
	function emailadmin_upgrade0_0_4()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','defaultDomain', array('type' => 'varchar', 'precision' => 100));		

		return $setup_info['emailadmin']['currentver'] = '0.0.5';
	}

	$test[] = '0.0.5';
	function emailadmin_upgrade0_0_5()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','organisationName', array('type' => 'varchar', 'precision' => 100));		
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','userDefinedAccounts', array('type' => 'varchar', 'precision' => 3));		

		return $setup_info['emailadmin']['currentver'] = '0.0.6';
	}
	


	$test[] = '0.0.6';
	function emailadmin_upgrade0_0_6()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','oldimapcclient',array(
			'type' => 'varchar',
			'precision' => '3'
		));

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.007';
	}


	$test[] = '0.0.007';
	function emailadmin_upgrade0_0_007()
	{
		$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_emailadmin','oldimapcclient','imapoldcclient');

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.008';
	}
	

	$test[] = '0.0.008';
	function emailadmin_upgrade0_0_008()
	{
		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.0';
	}

	$test[] = '1.0.0';
	function emailadmin_upgrade1_0_0()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','editforwardingaddress',array(
			'type' => 'varchar',
			'precision' => '3'
		));

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.1';
	}

	$test[] = '1.0.1';
	function emailadmin_upgrade1_0_1()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_order', array('type' => 'int', 'precision' => 4));		

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.2';
	}

	$test[] = '1.0.2';
	function emailadmin_upgrade1_0_2()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_appname', array('type' => 'varchar','precision' => '80'));
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_group', array('type' => 'varchar','precision' => '80'));

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.3';
	}

	$test[] = '1.0.3';
	function emailadmin_upgrade1_0_3()
	{
		$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_emailadmin','egw_emailadmin');

		return $GLOBALS['setup_info']['emailadmin']['currentver'] = '1.2';
	}
	
	//next version should be 1.2.001
?>
