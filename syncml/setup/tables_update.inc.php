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

	// $Id: tables_update.inc.php 22049 2006-07-08 22:18:36Z ralfbecker $

	$test[] = '0.9.0';
	function syncml_upgrade0_9_0()
	{
		$GLOBALS['egw_setup']->oProc->DropColumn('egw_syncmldevinfo',array(
			'fd' => array(
				'dev_dtdversion' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_numberofchanges' => array('type' => 'bool','nullable' => False),
				'dev_largeobjs' => array('type' => 'bool','nullable' => False),
				'dev_swversion' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_oem' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_model' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_manufacturer' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_devicetype' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_datastore' => array('type' => 'text','nullable' => False)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),'dev_id');
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_syncmldevinfo',array(
			'fd' => array(
				'dev_dtdversion' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_numberofchanges' => array('type' => 'bool','nullable' => False),
				'dev_largeobjs' => array('type' => 'bool','nullable' => False),
				'dev_swversion' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_oem' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_model' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_manufacturer' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_devicetype' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_datastore' => array('type' => 'text','nullable' => False)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.1';
	}


	$test[] = '0.9.1';
	function syncml_upgrade0_9_1()
	{
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_syncmldevinfo','dev_id',array(
			'type' => 'auto',
			'nullable' => False
		));*/
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_syncmldevinfo',array(
			'fd' => array(
				'dev_dtdversion' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_numberofchanges' => array('type' => 'bool','nullable' => False),
				'dev_largeobjs' => array('type' => 'bool','nullable' => False),
				'dev_swversion' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_oem' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_model' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_manufacturer' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_devicetype' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_datastore' => array('type' => 'text','nullable' => False),
				'dev_id' => array('type' => 'auto','nullable' => False)
			),
			'pk' => array('dev_id'),
			'fk' => array(),
			'ix' => array('dev_id'),
			'uc' => array('dev_id')
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.2';
	}


	$test[] = '0.9.2';
	function syncml_upgrade0_9_2()
	{
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_syncmldevinfo','dev_swversion',array(
			'type' => 'varchar',
			'precision' => '100'
		));*/
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_syncmldevinfo','dev_oem',array(
			'type' => 'varchar',
			'precision' => '100'
		));*/
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_syncmldevinfo','dev_fwversion',array(
			'type' => 'varchar',
			'precision' => '100'
		));*/
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_syncmldevinfo','dev_hwversion',array(
			'type' => 'varchar',
			'precision' => '100'
		));*/
		/* done by RefreshTable() anyway
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_syncmldevinfo','dev_utc',array(
			'type' => 'bool',
			'nullable' => False
		));*/
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_syncmldevinfo',array(
			'fd' => array(
				'dev_dtdversion' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_numberofchanges' => array('type' => 'bool','nullable' => False),
				'dev_largeobjs' => array('type' => 'bool','nullable' => False),
				'dev_swversion' => array('type' => 'varchar','precision' => '100'),
				'dev_oem' => array('type' => 'varchar','precision' => '100'),
				'dev_model' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_manufacturer' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_devicetype' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_datastore' => array('type' => 'text','nullable' => False),
				'dev_id' => array('type' => 'auto','nullable' => False),
				'dev_fwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_hwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_utc' => array('type' => 'bool','nullable' => False)
			),
			'pk' => array('dev_id'),
			'fk' => array(),
			'ix' => array('dev_id'),
			'uc' => array('dev_id',array('dev_model','dev_manufacturer','dev_swversion'))
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.3';
	}


	$test[] = '0.9.3';
	function syncml_upgrade0_9_3()
	{
		$GLOBALS['egw_setup']->oProc->DropColumn('egw_syncmldevinfo',array(
			'fd' => array(
				'dev_dtdversion' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_numberofchanges' => array('type' => 'bool','nullable' => False),
				'dev_largeobjs' => array('type' => 'bool','nullable' => False),
				'dev_swversion' => array('type' => 'varchar','precision' => '100'),
				'dev_oem' => array('type' => 'varchar','precision' => '100'),
				'dev_model' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_manufacturer' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_devicetype' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_datastore' => array('type' => 'text','nullable' => False),
				'dev_id' => array('type' => 'auto','nullable' => False),
				'dev_fwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_hwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_utc' => array('type' => 'bool','nullable' => False)
			),
			'pk' => array('dev_id'),
			'fk' => array(),
			'ix' => array('dev_id'),
			'uc' => array('dev_id',array('dev_model','dev_manufacturer','dev_swversion'))
		),'dev_deviceid');

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.4';
	}


	$test[] = '0.9.4';
	function syncml_upgrade0_9_4()
	{
		$GLOBALS['egw_setup']->oProc->CreateTable('egw_syncmldeviceowner',array(
			'fd' => array(
				'owner_accountid' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'owner_devid' => array('type' => 'int','precision' => '4','nullable' => False),
				'owner_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array('owner_accountid','owner_deviceid'),
			'uc' => array(array('owner_accountid','owner_devid','owner_deviceid'))
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.5';
	}


	$test[] = '0.9.5';
	function syncml_upgrade0_9_5()
	{
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_syncmldeviceowner','owner_accountid','owner_locname');
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_syncmldeviceowner',array(
			'fd' => array(
				'owner_locname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'owner_devid' => array('type' => 'int','precision' => '4','nullable' => False),
				'owner_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False)
			),
			'pk' => array('owner_devid'),
			'fk' => array(),
			'ix' => array('owner_locname','owner_deviceid'),
			'uc' => array(array('owner_locname','owner_devid','owner_deviceid'))
		));
		
		$GLOBALS['egw_setup']->oProc->query('delete from egw_syncmldevinfo', __LINE__, __FILE__);
		
		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.6';
	}
?>