<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.eGroupWare.org                                                *
  * Created by eTemplates DB-Tools written by ralfbecker@outdoor-training.de *
  * --------------------------------------------                             *
  * This program is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU General Public License as published by the    *
  * Free Software Foundation; either version 2 of the License, or (at your   *
  * option) any later version.                                               *
  \**************************************************************************/

  /* $Id$ */

	$test[] = '0.5.2';
	function jinn_upgrade0_5_2()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_site_db_name',array(
			'type' => 'varchar',
			'precision' => '100',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_site_db_host',array(
			'type' => 'varchar',
			'precision' => '50',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_site_db_user',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_site_db_password',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_site_db_type',array(
			'type' => 'varchar',
			'precision' => '10',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_upload_path',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','dev_upload_url',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.6.001';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}

	$test[] = '0.6.001';
	function jinn_upgrade0_6_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','help_information',array(
			'type' => 'text'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','dev_upload_url',array(
			'type' => 'varchar',
			'precision' => '255'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','dev_upload_path',array(
			'type' => 'varchar',
			'precision' => '255'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.6.002';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.6.002';
	function jinn_upgrade0_6_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','max_records',array(
			'type' => 'int',
			'precision' => '4'
		));


		$GLOBALS['setup_info']['jinn']['currentver'] = '0.6.003';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}
?>
