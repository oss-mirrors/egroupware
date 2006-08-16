<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	// $Id: tables_current.inc.php 22031 2006-07-08 01:02:37Z ralfbecker $
	// $Source$

	$phpgw_baseline = array(
		'egw_contentmap' => array(
			'fd' => array(
				'map_id' => array('type' => 'varchar','precision' => '128','nullable' => False),
				'map_guid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'map_locuid' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'map_timestamp' => array('type' => 'timestamp','nullable' => False),
				'map_expired' => array('type' => 'bool','nullable' => False)
			),
			'pk' => array('map_id','map_guid','map_locuid'),
			'fk' => array(),
			'ix' => array('map_expired',array('map_id','map_locuid')),
			'uc' => array()
		),
		'egw_syncmldevinfo' => array(
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
		),
		'egw_syncmlsummary' => array(
			'fd' => array(
				'dev_id' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'sync_path' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'sync_serverts' => array('type' => 'varchar','precision' => '20','nullable' => False),
				'sync_clientts' => array('type' => 'varchar','precision' => '20','nullable' => False)
			),
			'pk' => array(array('dev_id','sync_path')),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_syncmldeviceowner' => array(
			'fd' => array(
				'owner_locname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'owner_devid' => array('type' => 'int','precision' => '4','nullable' => False),
				'owner_deviceid' => array('type' => 'varchar','precision' => '100','nullable' => False)
			),
			'pk' => array('owner_devid'),
			'fk' => array(),
			'ix' => array('owner_locname','owner_deviceid'),
			'uc' => array(array('owner_locname','owner_devid','owner_deviceid'))
		)
	);
?>