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

	$test[] = '0.8.4';
	function felamimail_upgrade0_8_4()
	{
		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.0';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

	$test[] = '0.9.0';
	function felamimail_upgrade0_9_0()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_felamimail_folderstatus', 'accountname', array('type' => 'varchar', 'precision' => 200, 'nullable' => false));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_felamimail_cache', 'accountname', array('type' => 'varchar', 'precision' => 200, 'nullable' => false));

		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.1';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

	$test[] = '0.9.1';
	function felamimail_upgrade0_9_1()
	{
		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.2';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

	$test[] = '0.9.2';
	function felamimail_upgrade0_9_2()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_felamimail_displayfilter',
			Array(
				'fd' => array(
					'accountid' 	=> array('type' => 'int', 'precision' => 4, 'nullable' => false),
					'filter' 	=> array('type' => 'text')
				),
				'pk' => array('accountid'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)

		);

		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.3';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}

	$test[] = '0.9.3';
	function felamimail_upgrade0_9_3()
	{
		$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_felamimail_cache');
		$GLOBALS['phpgw_setup']->oProc->query('delete from phpgw_felamimail_folderstatus',__LINE__,__FILE__);
		$GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_felamimail_cache',
			Array(
				'fd' => array(
					'accountid' 	=> array('type' => 'int', 'precision' => 4, 'nullable' => false),
					'hostname' 	=> array('type' => 'varchar', 'precision' => 60, 'nullable' => false),
					'accountname' 	=> array('type' => 'varchar', 'precision' => 200, 'nullable' => false),
					'foldername' 	=> array('type' => 'varchar', 'precision' => 200, 'nullable' => false),
					'uid' 		=> array('type' => 'int', 'precision' => 4, 'nullable' => false),
					'subject'	=> array('type' => 'text'),
					'striped_subject'=> array('type' => 'text'),
					'sender_name'	=> array('type' => 'varchar', 'precision' => 120),
					'sender_address'=> array('type' => 'varchar', 'precision' => 120),
					'to_name'	=> array('type' => 'varchar', 'precision' => 120),
					'to_address'	=> array('type' => 'varchar', 'precision' => 120),
					'date'		=> array('type' => 'varchar', 'precision' => 120),
					'size'		=> array('type' => 'int', 'precision' => 4),
					'attachments'	=> array('type' => 'varchar', 'precision' =>120)
				),
				'pk' => array('accountid','hostname','accountname','foldername','uid'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);

		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.4';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}



	$test[] = '0.9.4';
	function felamimail_upgrade0_9_4()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_felamimail_cache','accountname',array(
			'type' => 'varchar',
			'precision' => '25',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_felamimail_cache','date',array(
			'type' => 'int',
			'precision' => '8'
		));

		$GLOBALS['setup_info']['felamimail']['currentver'] = '0.9.5';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}


	$test[] = '0.9.5';
	function felamimail_upgrade0_9_5()
	{
		$GLOBALS['setup_info']['felamimail']['currentver'] = '1.0.0';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}


	$test[] = '1.0.0';
	function felamimail_upgrade1_0_0()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','accountid','fmail_accountid');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','hostname','fmail_hostname');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','accountname','fmail_accountname');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','foldername','fmail_foldername');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','uid','fmail_uid');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','subject','fmail_subject');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','striped_subject','fmail_striped_subject');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','sender_name','fmail_sender_name');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','sender_address','fmail_sender_address');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','to_name','fmail_to_name');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','to_address','fmail_to_address');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','date','fmail_date');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','size','fmail_size');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_cache','attachments','fmail_attachments');
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('phpgw_felamimail_cache',array(
			'fd' => array(
				'fmail_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_hostname' => array('type' => 'varchar','precision' => '60','nullable' => False),
				'fmail_accountname' => array('type' => 'varchar','precision' => '25','nullable' => False),
				'fmail_foldername' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_uid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_subject' => array('type' => 'text'),
				'fmail_striped_subject' => array('type' => 'text'),
				'fmail_sender_name' => array('type' => 'varchar','precision' => '120'),
				'fmail_sender_address' => array('type' => 'varchar','precision' => '120'),
				'fmail_to_name' => array('type' => 'varchar','precision' => '120'),
				'fmail_to_address' => array('type' => 'varchar','precision' => '120'),
				'fmail_date' => array('type' => 'int','precision' => '8'),
				'fmail_size' => array('type' => 'int','precision' => '4'),
				'fmail_attachments' => array('type' => 'varchar','precision' => '120')
			),
			'pk' => array('fmail_accountid','fmail_hostname','fmail_accountname','fmail_foldername','fmail_uid'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		$GLOBALS['setup_info']['felamimail']['currentver'] = '1.0.0.001';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}


	$test[] = '1.0.0.001';
	function felamimail_upgrade1_0_0_001()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','accountid','fmail_accountid');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','hostname','fmail_hostname');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','accountname','fmail_accountname');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','foldername','fmail_foldername');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','messages','fmail_messages');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','recent','fmail_recent');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','unseen','fmail_unseen');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','uidnext','fmail_uidnext');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_folderstatus','uidvalidity','fmail_uidvalidity');
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('phpgw_felamimail_folderstatus',array(
			'fd' => array(
				'fmail_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_hostname' => array('type' => 'varchar','precision' => '60','nullable' => False),
				'fmail_accountname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_foldername' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_messages' => array('type' => 'int','precision' => '4'),
				'fmail_recent' => array('type' => 'int','precision' => '4'),
				'fmail_unseen' => array('type' => 'int','precision' => '4'),
				'fmail_uidnext' => array('type' => 'int','precision' => '4'),
				'fmail_uidvalidity' => array('type' => 'int','precision' => '4')
			),
			'pk' => array('fmail_accountid','fmail_hostname','fmail_accountname','fmail_foldername'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		$GLOBALS['setup_info']['felamimail']['currentver'] = '1.0.0.002';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}


	$test[] = '1.0.0.002';
	function felamimail_upgrade1_0_0_002()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_displayfilter','accountid','fmail_filter_accountid');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_felamimail_displayfilter','filter','fmail_filter_data');
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('phpgw_felamimail_displayfilter',array(
			'fd' => array(
				'fmail_filter_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_filter_data' => array('type' => 'text')
			),
			'pk' => array('fmail_filter_accountid'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		$GLOBALS['setup_info']['felamimail']['currentver'] = '1.0.0.003';
		return $GLOBALS['setup_info']['felamimail']['currentver'];
	}
?>
