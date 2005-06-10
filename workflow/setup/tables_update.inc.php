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

	$test[] = '1.0.1';
	function workflow_upgrade1_0_1()
	{	
		# add an instance_supplements table
		$GLOBALS['phpgw_setup']->oProc->createTable('egw_wf_instance_supplements', 
			array(
				'fd' => array(
					'wf_supplement_id' 	=> array('type' => 'auto', 'precision' => '4', 'nullable' => False),
					'wf_supplement_type'	=> array('type' => 'varchar', 'precision' => '50', 'nullable' => True),
					'wf_supplement_name'	=> array('type' => 'varchar', 'precision' => '100', 'nullable' => True),
					'wf_supplement_value'	=> array('type' => 'text', 'nullable' => True),
					'wf_workitem_id'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
					'wf_supplement_blob'	=> array('type' => 'blob', 'nullable' => True)
				),
				'pk' => array('wf_supplement_id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);
		
		#Add in activities table is_reassign_box, is_report, default_user and default group
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_activities' ,'wf_is_reassign_box',array('type' => 'char', 'precision' => 1, 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_activities' ,'wf_is_report',array('type' => 'char', 'precision' => 1, 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_activities' ,'wf_default_user', array('type' => 'varchar', 'precision' => '200', 'nullable' => True, 'default' => '*'));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_activities' ,'wf_default_group', array('type' => 'varchar', 'precision' => '200', 'nullable' => True, 'default' => '*'));

		#Add in instance_activities table the group field
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_instance_activities' ,'wf_group',array('type' => 'varchar', 'precision' => 200, 'nullable' => True, 'default' => '*'));
		
		#Add in instance table the name, and the priority, we keep the properties for the moment
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_instances' ,'wf_priority',array('type' => 'int', 'precision' => 4, 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_instances' ,'wf_name',array('type' => 'varchar', 'precision' => 120, 'nullable' => True));
		
		#Add in workitems table note and action
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_workitems' ,'wf_note',array('type' => 'text', 'precision' => 50, 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_workitems' ,'wf_action',array('type' => 'text', 'precision' => 50, 'nullable' => True));
		
		#Add in user_roles table the account type
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_wf_user_roles' ,'wf_account_type',array('type' => 'char', 'precision' => 1, 'nullable' => True, 'default' => 'u'));
			#modifying the sequence as well
			#we need a RefreshTable
		$GLOBALS['phpgw_setup']->oProc->RefreshTable('egw_wf_user_roles' ,array(
		 	'fd' => array(
				'wf_role_id'            => array('type' => 'auto', 'precision' => '4', 'nullable' => False),
				'wf_p_id'               => array('type' => 'int', 'precision' => '4', 'nullable' => False),
				'wf_user'               => array('type' => 'varchar', 'precision' => '200', 'nullable' => False),
				'wf_account_type'       => array('type' => 'char', 'precision' => '1', 'nullable' => True, 'default' => 'u'),
			 ),
			 'pk' => array('wf_role_id', 'wf_user', 'wf_account_type'),
			 'fk' => array(),
			 'ix' => array(),
			 'uc' => array()
		));
		
		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '1.1.00.000';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}

	$test[] = '1.1.00.000';
	function workflow_upgrade1_1_00_000()
	{	
		# add a process_config table
		$GLOBALS['phpgw_setup']->oProc->createTable('egw_wf_process_config', 
			array(
				'fd' => array(
					'wf_p_id'               => array('type' => 'int', 'precision' => '4', 'nullable' => False),
					'wf_config_name' 	=> array('type' => 'varchar', 'precision' => '255', 'nullable' => False),
					'wf_config_value'	=> array('type' => 'text', 'nullable' => True),
					'wf_config_value_int'	=> array('type' => 'int', 'precision' => '4', 'nullable' => True),
				),
				'pk' => array('wf_p_id','wf_config_name'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);
		
		//change de default value for priority
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_wf_instances','wf_priority',array('type' => 'int', 'precision' => '4', 'nullable' => True, 'default'=> 0));

		#updating the current version
		$GLOBALS['setup_info']['workflow']['currentver'] = '1.1.01.000';
		return $GLOBALS['setup_info']['workflow']['currentver'];
	}
?>
