<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	function projects_table_exists($table)
	{
		global $phpgw_setup,$all_tables,$DEBUG;

		$tablenames = $phpgw_setup->db->table_names();
		while(list($key,$val) = @each($tablenames))
		{
			$all_tables[] = $val['table_name'];
		}
		if($phpgw_setup->isinarray($table,$all_tables))
		{
			if ($DEBUG) { echo '<br>' . $table . ' exists.'; }
			return True;
		}
		else
		{
			if ($DEBUG) { echo '<br>' . $table . ' does not exist.'; }
			return False;
		}
	}

	function projects_table_column($table,$column)
	{
		global $phpgw_setup,$DEBUG;

		$phpgw_setup->db->HaltOnError = False;

		$phpgw_setup->db->query("SELECT COUNT($column) FROM $table");
		$phpgw_setup->db->next_record();
		if (!$phpgw_setup->db->f(0))
		{
			if ($DEBUG) { echo '<br>' . $table . ' has no column named ' . $column; }
			return False;
		}
		if ($DEBUG) { echo '<br>' . $table . ' has a column named ' . $column; }
		return True;
	}

	if ($setup_info['projects']['currentver'] == '')
	{
		$setup_info['projects']['currentver'] == '0.0.0';
	}

	$test[] = '0.0';
	function projects_upgrade0_0()
	{
		global $setup_info;

		$setup_info['projects']['currentver'] == '0.0.0';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.2';
	function projects_upgrade0_8_2()
	{
		global $setup_info;

		$setup_info['projects']['currentver'] == '0.0.0';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.0.0';
	function projects_upgrade0_0_0()
	{
		if (projects_table_exists('phpgw_p_projects'))
		{
			if (!projects_table_column('phpgw_p_hours','start_date'))
			{
				return '0.8.4';
			}
			elseif (!projects_table_column('phpgw_p_hours','hours_descr'))
			{
				return '0.8.4.001';
			}
			elseif (!projects_table_column('phpgw_p_projects','category'))
			{
				return '0.8.4.002';
			}
			elseif (!projects_table_column('phpgw_p_projectmembers','type'))
			{
				return '0.8.4.003';
			}
			else
			{
				return '0.8.4.004';
			}
		}
		else
		{
			if (projects_table_exists('p_projectaddress'))
			{
				return '0.8.3';
			}
			else
			{
				return '0.8.3.001';
			}
		}
		return False;
	}

	$test[] = '0.8.3';
	function projects_upgrade0_8_3()
	{
		global $setup_info, $phpgw_setup;

        $phpgw_setup->oProc->DropTable('p_projectaddress');

		$setup_info['projects']['currentver'] = '0.8.3.001';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.3.001';
	function projects_upgrade0_8_3_001()
	{
		global $setup_info,$phpgw_setup;

        $phpgw_setup->oProc->AlterColumn('p_projects','access',array('type' => 'varchar','precision' => 25,'nullable' => True));

		$setup_info['projects']['currentver'] = '0.8.3.002';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.3.002';
	function projects_upgrade0_8_3_002()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AlterColumn('p_invoicepos','invoice_id',array('type' => 'int','precision' => 4,'nullable' => False));
		$phpgw_setup->oProc->AlterColumn('p_deliverypos','delivery_id',array('type' => 'int','precision' => 4,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.3.003';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.3.003';
	function projects_upgrade0_8_3_003()
	{
		global $setup_info,$phpgw_setup;

		$newtabledefinition = array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'num' => array('type' => 'varchar','precision' => 20,'nullable' => False),
				'owner' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'entry_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'start_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'end_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'coordinator' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'customer' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'status' => array('type' => 'varchar','precision' => 9,'default' => 'active','nullable' => False),
				'descr' => array('type' => 'text','nullable' => True),
				'title' => array('type' => 'varchar','precision' => 255,'nullable' => False),
				'budget' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','num'),
			'uc' => array()
		);

		$phpgw_setup->oProc->RenameTable('p_projects','phpgw_p_projects');
		$phpgw_setup->oProc->RenameColumn('phpgw_p_projects','date','start_date');
		$phpgw_setup->oProc->AlterColumn('phpgw_p_projects','start_date',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$phpgw_setup->oProc->AlterColumn('phpgw_p_projects','title',array('type' => 'varchar','precision' => 255,'nullable' => False));
		$phpgw_setup->oProc->AlterColumn('phpgw_p_projects','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$phpgw_setup->oProc->DropColumn('phpgw_p_projects',$newtabledefinition,'access');
		$phpgw_setup->oProc->query("CREATE INDEX phpgw_p_projects_key ON phpgw_p_projects(id,num)");

		$phpgw_setup->oProc->RenameTable('p_activities','phpgw_p_activities');
		$phpgw_setup->oProc->AlterColumn('phpgw_p_activities','descr',array('type' => 'varchar','precision' => 255,'nullable' => False));
		$phpgw_setup->oProc->AlterColumn('phpgw_p_activities','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$phpgw_setup->oProc->query("CREATE INDEX phpgw_p_activities_key ON phpgw_p_activities(id,num)");

		$phpgw_setup->oProc->RenameTable('p_projectactivities','phpgw_p_projectactivities');
		$phpgw_setup->oProc->RenameTable('p_hours','phpgw_p_hours');
		$phpgw_setup->oProc->RenameTable('p_projectmembers','phpgw_p_projectmembers');

		$phpgw_setup->oProc->RenameTable('p_invoice','phpgw_p_invoice');
		$phpgw_setup->oProc->AlterColumn('phpgw_p_invoice','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$phpgw_setup->oProc->query("CREATE INDEX phpgw_p_invoice_key ON phpgw_p_invoice(id,num)");

		$phpgw_setup->oProc->RenameTable('p_invoicepos','phpgw_p_invoicepos');

		$phpgw_setup->oProc->RenameTable('p_delivery','phpgw_p_delivery');
		$phpgw_setup->oProc->AlterColumn('phpgw_p_delivery','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$phpgw_setup->oProc->query("CREATE INDEX phpgw_p_delivery_key ON phpgw_p_delivery(id,num)");

		$phpgw_setup->oProc->RenameTable('p_deliverypos','phpgw_p_deliverypos');

		$setup_info['projects']['currentver'] = '0.8.4';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4';
	function projects_upgrade0_8_4()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->RenameColumn('phpgw_p_hours','date','start_date');
		$phpgw_setup->oProc->AlterColumn('phpgw_p_hours','start_date',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.4.001';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.001';
	function projects_upgrade0_8_4_001()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_hours','hours_descr',array('type' => 'varchar','precision' => 255,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.4.002';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.002';
	function projects_upgrade0_8_4_002()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_projects','access',array('type' => 'varchar','precision' => 7,'nullable' => True));
		$phpgw_setup->oProc->AddColumn('phpgw_p_projects','category',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$phpgw_setup->oProc->AlterColumn('phpgw_p_projects','status',array('type' => 'varchar','precision' => 9,'default' => 'active','nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.4.003';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.003';
	function projects_upgrade0_8_4_003()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_projectmembers','type',array('type' => 'char','precision' => 2,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.4.004';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.004';
	function projects_upgrade0_8_4_004()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AlterColumn('phpgw_p_activities','remarkreq',array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False));
		$phpgw_setup->oProc->AlterColumn('phpgw_p_projectactivities','billable',array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.4.005';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.005';
	function projects_upgrade0_8_4_005()
	{
		global $setup_info;

		$setup_info['projects']['currentver'] = '0.8.4.006';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.4.006';
	function projects_upgrade0_8_4_006()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->query("CREATE UNIQUE INDEX project_num ON phpgw_p_projects(num)");
		$phpgw_setup->oProc->query("CREATE UNIQUE INDEX invoice_num ON phpgw_p_invoice(num)");
		$phpgw_setup->oProc->query("CREATE UNIQUE INDEX delivery_num ON phpgw_p_delivery(num)");

		$setup_info['projects']['currentver'] = '0.8.5.001';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.001';
	function projects_upgrade0_8_5_001()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AlterColumn('phpgw_p_hours','status',array('type' => 'varchar','precision' => 6,'default' => 'done','nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.5.002';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.002';
	function projects_upgrade0_8_5_002()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_hours','dstatus',array('type' => 'char','precision' => 1,'default' => 'o','nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.5.003';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.003';
	function projects_upgrade0_8_5_003()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_projects','parent',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.5.004';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.004';
	function projects_upgrade0_8_5_004()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AddColumn('phpgw_p_activities','category',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.5.005';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.005';
	function projects_upgrade0_8_5_005()
	{
		global $setup_info,$phpgw_setup;

        $phpgw_setup->oProc->AlterColumn('phpgw_p_projects','num',array('type' => 'varchar','precision' => 25,'nullable' => False));

		$setup_info['projects']['currentver'] = '0.8.5.006';
		return $setup_info['projects']['currentver'];
	}

	$test[] = '0.8.5.006';
	function projects_upgrade0_8_5_006()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_hours','pro_parent',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('projects','add_def_pref','hook_add_def_pref.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('projects','manual','hook_manual.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('projects','about','hook_about.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('projects','deleteaccount','hook_deleteaccount.inc.php')");
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.007';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.007';
	function projects_upgrade0_8_5_007()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_activities','minperae',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_hours','minperae',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.008';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}
?>
