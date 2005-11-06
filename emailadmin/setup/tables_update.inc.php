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
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','smtpType', array('type' => 'int', 'precision' => 4));		

		$setup_info['emailadmin']['currentver'] = '0.0.4';
		return $setup_info['emailadmin']['currentver'];
	}

	$test[] = '0.0.4';
	function emailadmin_upgrade0_0_4()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','defaultDomain', array('type' => 'varchar', 'precision' => 100));		

		$setup_info['emailadmin']['currentver'] = '0.0.5';
		return $setup_info['emailadmin']['currentver'];
	}

	$test[] = '0.0.5';
	function emailadmin_upgrade0_0_5()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','organisationName', array('type' => 'varchar', 'precision' => 100));		
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','userDefinedAccounts', array('type' => 'varchar', 'precision' => 3));		

		$setup_info['emailadmin']['currentver'] = '0.0.6';
		return $setup_info['emailadmin']['currentver'];
	}
	


	$test[] = '0.0.6';
	function emailadmin_upgrade0_0_6()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_emailadmin','oldimapcclient',array(
			'type' => 'varchar',
			'precision' => '3'
		));


		$GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.007';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}


	$test[] = '0.0.007';
	function emailadmin_upgrade0_0_007()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_emailadmin','oldimapcclient','imapoldcclient');


		$GLOBALS['setup_info']['emailadmin']['currentver'] = '0.0.008';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}
	

	$test[] = '0.0.008';
	function emailadmin_upgrade0_0_008()
	{
		$GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.0';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}

	$test[] = '1.0.0';
	function emailadmin_upgrade1_0_0()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','editforwardingaddress',array(
			'type' => 'varchar',
			'precision' => '3'
		));

		$GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.1';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}

	$test[] = '1.0.1';
	function emailadmin_upgrade1_0_1()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_order', array('type' => 'int', 'precision' => 4));		

		$GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.2';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}

	$test[] = '1.0.2';
	function emailadmin_upgrade1_0_2()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_appname', array('type' => 'varchar','precision' => '80'));
		$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_emailadmin','ea_group', array('type' => 'varchar','precision' => '80'));

		$GLOBALS['setup_info']['emailadmin']['currentver'] = '1.0.3';
		return $GLOBALS['setup_info']['emailadmin']['currentver'];
	}

?>
