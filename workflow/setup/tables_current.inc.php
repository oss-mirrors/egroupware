<?php
  /**************************************************************************\
  * E-GroupWare - Setup                                                      *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	$phpgw_baseline = array(
		'egw_wf_activities' => array(
			'fd' => array(
				'activityId'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'name'				=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'normalized_name'	=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'pId'				=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'type'				=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
				'isAutoRouted'		=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'flowNum'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'isInteractive'		=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'lastModif'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'description'		=> array('type' => 'text', 'nullable' => True)
			),
			'pk' => array('activityId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_activity_roles' => array(
			'fd' => array(
				'activityId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'roleId'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False)
			),
			'pk' => array('activityId', 'roleId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instance_activities' => array(
			'fd' => array(
				'instanceId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'activityId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'status'		=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
			),
			'pk' => array('instanceId', 'activityId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instance_comments' => array(
			'fd' => array(
				'cId' 			=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'instanceId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'activityId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'hash'			=> array('type' => 'varchar', 'precision' => '32', 'nullable' => True),
				'title'			=> array('type' => 'varchar', 'precision' => '250', 'nullable' => True),
				'comment'		=> array('type' => 'text', 'nullable' => True),
				'activity'		=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'timestamp'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
			),
			'pk' => array('cId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instances' => array(
			'fd' => array(
				'instanceId'	=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'pId'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'owner'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'nextActivity'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'nextUser'		=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'status'		=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
				'properties'	=> array('type' => 'blob', 'nullable' => True),
			),
			'pk' => array('instanceId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_processes' => array(
			'fd' => array(
				'pId'				=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'name'				=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'isValid'			=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'isActive'			=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'version'			=> array('type' => 'varchar', 'precision' => '12', 'nullable' => True),
				'description'		=> array('type' => 'text', 'nullable' => True),
				'lastModif'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'normalized_name'	=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
			),
			'pk' => array('pId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_roles' => array(
			'fd' => array(
				'roleId'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'pId'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'lastModif'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'name'			=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'description'	=> array('type' => 'text', 'nullable' => True),
			),
			'pk' => array('roleId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_transitions' => array(
			'fd' => array(
				'pId'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'actFromId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'actToId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
			),
			'pk' => array('actFromId', 'actToId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_user_roles' => array(
			'fd' => array(
				'roleId'	=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'pId'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'user'		=> array('type' => 'varchar', 'precision' => '200', 'nullable' => False),
			),
			'pk' => array('roleId', 'user'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_workitems' => array(
			'fd' => array(
				'itemId'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'instanceId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'orderId'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'activityId'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'properties'	=> array('type' => 'blob', 'nullable' => True),
				'started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
			),
			'pk' => array('itemId'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
