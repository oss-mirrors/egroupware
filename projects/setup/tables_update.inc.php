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
		$tablenames = $GLOBALS['phpgw_setup']->db->table_names();
		while(list($key,$val) = @each($tablenames))
		{
			$all_tables[] = $val['table_name'];
		}
		if(in_array($table,$all_tables))
		{
			if ($GLOBALS['DEBUG']) { echo '<br>' . $table . ' exists.'; }
			return True;
		}
		else
		{
			if ($GLOBALS['DEBUG']) { echo '<br>' . $table . ' does not exist.'; }
			return False;
		}
	}

	function projects_table_column($table,$column)
	{
		$GLOBALS['phpgw_setup']->db->HaltOnError = False;

		$GLOBALS['phpgw_setup']->db->query("SELECT COUNT($column) FROM $table");
		$GLOBALS['phpgw_setup']->db->next_record();
		if (!$GLOBALS['phpgw_setup']->db->f(0))
		{
			if ($GLOBALS['DEBUG']) { echo '<br>' . $table . ' has no column named ' . $column; }
			return False;
		}
		if ($GLOBALS['DEBUG']) { echo '<br>' . $table . ' has a column named ' . $column; }
		return True;
	}

	if ($GLOBALS['setup_info']['projects']['currentver'] == '')
	{
		$GLOBALS['setup_info']['projects']['currentver'] == '0.0.0';
	}

	$test[] = '0.0';
	function projects_upgrade0_0()
	{
		$GLOBALS['setup_info']['projects']['currentver'] == '0.0.0';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.2';
	function projects_upgrade0_8_2()
	{
		$GLOBALS['setup_info']['projects']['currentver'] == '0.0.0';
		return $GLOBALS['setup_info']['projects']['currentver'];
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
        $GLOBALS['phpgw_setup']->oProc->DropTable('p_projectaddress');

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.3.001';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.3.001';
	function projects_upgrade0_8_3_001()
	{
        $GLOBALS['phpgw_setup']->oProc->AlterColumn('p_projects','access',array('type' => 'varchar','precision' => 25,'nullable' => True));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.3.002';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.3.002';
	function projects_upgrade0_8_3_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('p_invoicepos','invoice_id',array('type' => 'int','precision' => 4,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('p_deliverypos','delivery_id',array('type' => 'int','precision' => 4,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.3.003';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.3.003';
	function projects_upgrade0_8_3_003()
	{
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

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_projects','phpgw_p_projects');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_p_projects','date','start_date');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','start_date',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','title',array('type' => 'varchar','precision' => 255,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_p_projects',$newtabledefinition,'access');
		$GLOBALS['phpgw_setup']->oProc->query("CREATE INDEX phpgw_p_projects_key ON phpgw_p_projects(id,num)");

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_activities','phpgw_p_activities');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_activities','descr',array('type' => 'varchar','precision' => 255,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_activities','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->query("CREATE INDEX phpgw_p_activities_key ON phpgw_p_activities(id,num)");

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_projectactivities','phpgw_p_projectactivities');
		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_hours','phpgw_p_hours');
		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_projectmembers','phpgw_p_projectmembers');

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_invoice','phpgw_p_invoice');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_invoice','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->query("CREATE INDEX phpgw_p_invoice_key ON phpgw_p_invoice(id,num)");

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_invoicepos','phpgw_p_invoicepos');

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_delivery','phpgw_p_delivery');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_delivery','num',array('type' => 'varchar','precision' => 20,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->query("CREATE INDEX phpgw_p_delivery_key ON phpgw_p_delivery(id,num)");

		$GLOBALS['phpgw_setup']->oProc->RenameTable('p_deliverypos','phpgw_p_deliverypos');

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4';
	function projects_upgrade0_8_4()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_p_hours','date','start_date');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_hours','start_date',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.001';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.001';
	function projects_upgrade0_8_4_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_hours','hours_descr',array('type' => 'varchar','precision' => 255,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.002';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.002';
	function projects_upgrade0_8_4_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','access',array('type' => 'varchar','precision' => 7,'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','category',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','status',array('type' => 'varchar','precision' => 9,'default' => 'active','nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.003';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.003';
	function projects_upgrade0_8_4_003()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projectmembers','type',array('type' => 'char','precision' => 2,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.004';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.004';
	function projects_upgrade0_8_4_004()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_activities','remarkreq',array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projectactivities','billable',array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.005';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.005';
	function projects_upgrade0_8_4_005()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.4.006';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.4.006';
	function projects_upgrade0_8_4_006()
	{
		$GLOBALS['phpgw_setup']->oProc->query("CREATE UNIQUE INDEX project_num ON phpgw_p_projects(num)");
		$GLOBALS['phpgw_setup']->oProc->query("CREATE UNIQUE INDEX invoice_num ON phpgw_p_invoice(num)");
		$GLOBALS['phpgw_setup']->oProc->query("CREATE UNIQUE INDEX delivery_num ON phpgw_p_delivery(num)");

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.001';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.001';
	function projects_upgrade0_8_5_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_hours','status',array('type' => 'varchar','precision' => 6,'default' => 'done','nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.002';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.002';
	function projects_upgrade0_8_5_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_hours','dstatus',array('type' => 'char','precision' => 1,'default' => 'o','nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.003';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.003';
	function projects_upgrade0_8_5_003()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','parent',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.004';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.004';
	function projects_upgrade0_8_5_004()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_activities','category',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.005';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.5.005';
	function projects_upgrade0_8_5_005()
	{
        $GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','num',array('type' => 'varchar','precision' => 25,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.5.006';
		return $GLOBALS['setup_info']['projects']['currentver'];
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

	$test[] = '0.8.5.008';
	function projects_upgrade0_8_5_008()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.001';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6';
	function projects_upgrade0_8_6()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.001';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.001';
	function projects_upgrade0_8_6_001()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.002';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.002';
	function projects_upgrade0_8_6_002()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.003';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.003';
	function projects_upgrade0_8_6_003()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.004';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.004';
	function projects_upgrade0_8_6_004()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.005';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.005';
	function projects_upgrade0_8_6_005()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.006';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.6.006';
	function projects_upgrade0_8_6_006()
	{
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.007';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.001';
	function projects_upgrade0_8_7_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','time_planned',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','date_created',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','processor',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.002';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.002';
	function projects_upgrade0_8_7_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','investment_nr',array('type' => 'varchar','precision' => 50,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.003';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.003';
	function projects_upgrade0_8_7_003()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','pcosts',array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.004';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.004';
	function projects_upgrade0_8_7_004()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_p_pcosts', array(
				'fd' => array(
					'c_id' => array('type' => 'auto','nullable' => False),
					'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
					'month' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
					'pcosts' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False)
				),
				'pk' => array('c_id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);
		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.005';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.005';
	function projects_upgrade0_8_7_005()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','main',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','level',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_p_projects','num',array('type' => 'varchar','precision' => 255,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.006';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.006';
	function projects_upgrade0_8_7_006()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_p_mstones', array(
				'fd' => array(
					's_id' => array('type' => 'auto','nullable' => False),
					'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
					'title' => array('type' => 'varchar','precision' => 255,'nullable' => False),
					'edate' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False)
				),
				'pk' => array('s_id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.007';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}

	$test[] = '0.8.7.007';
	function projects_upgrade0_8_7_007()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_p_projects','previous',array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False));

		$GLOBALS['setup_info']['projects']['currentver'] = '0.8.7.008';
		return $GLOBALS['setup_info']['projects']['currentver'];
	}
?>
