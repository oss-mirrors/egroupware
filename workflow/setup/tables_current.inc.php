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
				'wf_activity_id'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_name'				=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'wf_normalized_name'	=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'wf_p_id'				=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_type'				=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
				'wf_is_autorouted'		=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'wf_flow_num'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_is_interactive'		=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'wf_last_modif'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_description'		=> array('type' => 'text', 'nullable' => True)
			),
			'pk' => array('wf_activity_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_activity_roles' => array(
			'fd' => array(
				'wf_activity_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_role_id'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False)
			),
			'pk' => array('wf_activity_id', 'wf_role_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instance_activities' => array(
			'fd' => array(
				'wf_instance_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_activity_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'wf_status'			=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
			),
			'pk' => array('wf_instance_id', 'wf_activity_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instance_comments' => array(
			'fd' => array(
				'wf_c_id' 			=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_instance_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'wf_activity_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_hash'			=> array('type' => 'varchar', 'precision' => '32', 'nullable' => True),
				'wf_title'			=> array('type' => 'varchar', 'precision' => '250', 'nullable' => True),
				'wf_comment'		=> array('type' => 'text', 'nullable' => True),
				'wf_activity'		=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'wf_timestamp'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
			),
			'pk' => array('wf_c_Id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_instances' => array(
			'fd' => array(
				'wf_instance_Id'	=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_p_id'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_owner'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'wf_next_activity'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_next_user'		=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
				'wf_ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_status'			=> array('type' => 'varchar', 'precision' => '25', 'nullable' => True),
				'wf_properties'		=> array('type' => 'blob', 'nullable' => True),
			),
			'pk' => array('wf_instance_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_processes' => array(
			'fd' => array(
				'wf_p_id'				=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_name'				=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'wf_is_valid'			=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'wf_is_active'			=> array('type' => 'char', 'precision' => '1', 'nullable' => True),
				'wf_version'			=> array('type' => 'varchar', 'precision' => '12', 'nullable' => True),
				'wf_description'		=> array('type' => 'text', 'nullable' => True),
				'wf_last_modif'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_normalized_name'	=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
			),
			'pk' => array('wf_p_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_roles' => array(
			'fd' => array(
				'wf_role_id'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_p_id'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_last_modif'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_name'			=> array('type' => 'varchar', 'precision' => '80', 'nullable' => True),
				'wf_description'	=> array('type' => 'text', 'nullable' => True),
			),
			'pk' => array('wf_role_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_transitions' => array(
			'fd' => array(
				'wf_p_id'			=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_act_from_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_act_to_id'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
			),
			'pk' => array('wf_act_from_id', 'wf_act_to_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_user_roles' => array(
			'fd' => array(
				'wf_role_id'	=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_p_id'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_user'		=> array('type' => 'varchar', 'precision' => '200', 'nullable' => False),
			),
			'pk' => array('wf_role_id', 'wf_user'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_wf_workitems' => array(
			'fd' => array(
				'wf_item_id'		=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_instance_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_order_id'		=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_activity_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_properties'		=> array('type' => 'blob', 'nullable' => True),
				'wf_started'		=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_ended'			=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				'wf_user'			=> array('type' => 'varchar', 'precision' => '200', 'nullable' => True),
			),
			'pk' => array('wf_item_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
