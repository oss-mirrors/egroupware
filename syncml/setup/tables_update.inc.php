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
		// We drop and create the table new, as the info's in it are queried 
		// automatic from the device if not present in the table.
		// This way we dont have to fight with DB's who can create an 
		// autoincrement-index for an existing table.
		$GLOBALS['egw_setup']->oProc->DropTable('egw_syncmldevinfo');

		$GLOBALS['egw_setup']->oProc->CreateTable('egw_syncmldevinfo',array(
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
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.4';
	}

	$test[] = '0.9.4';
	function syncml_upgrade0_9_4()
	{
		$GLOBALS['egw_setup']->oProc->CreateTable('egw_syncmldeviceowner',array(
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
		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.6';
	}

	$test[] = '0.9.6';
	function syncml_upgrade0_9_6()
	{
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_syncmldeviceowner',array(
			'fd' => array(
				'owner_locname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'owner_devid' => array('type' => 'int','precision' => '4','nullable' => False),
				'owner_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array('owner_deviceid'),
			'uc' => array(array('owner_locname','owner_devid','owner_deviceid'))
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.007';
	}


	$test[] = '0.9.007';
	function syncml_upgrade0_9_007()
	{
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
				'dev_datastore' => array('type' => 'text','nullable' => False),
				'dev_id' => array('type' => 'auto','nullable' => False),
				'dev_fwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_hwversion' => array('type' => 'varchar','precision' => '100'),
				'dev_utc' => array('type' => 'bool','nullable' => False)
			),
			'pk' => array('dev_id'),
			'fk' => array(),
			'ix' => array(array('dev_model','dev_manufacturer')),
			'uc' => array()
		));

		return $GLOBALS['setup_info']['syncml']['currentver'] = '0.9.008';
	}
?>
