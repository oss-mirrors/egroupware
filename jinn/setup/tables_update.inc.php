<?php
   /**************************************************************************\
   * eGroupWare - Setup                                                       *
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


   $test[] = '0.6.003';
   function jinn_upgrade0_6_003()
   {
	  $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','website_url',array(
		 'type' => 'varchar',
		 'precision' => '250',
		 'nullable' => False
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.004';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.004';
   function jinn_upgrade0_6_004()
   {
	  $GLOBALS['phpgw_setup']->oProc->CreateTable('egw_jinn_mail_list',array(
		 'fd' => array(
			'id' => array('type' => 'auto'),
			'name' => array('type' => 'varchar','precision' => '40'),
			'email_table' => array('type' => 'varchar','precision' => '40'),
			'email_field' => array('type' => 'varchar','precision' => '40')
		 ),
		 'pk' => array('id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ));

	  $GLOBALS['phpgw_setup']->oProc->CreateTable('egw_jinn_mail_data',array(
		 'fd' => array(
			'id' => array('type' => 'auto','nullable' => False),
			'subject' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'body_text' => array('type' => 'text','nullable' => False),
			'body_html' => array('type' => 'text','nullable' => False),
			'attachments' => array('type' => 'text','nullable' => False),
			'reply_address' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'reply_name' => array('type' => 'varchar','precision' => '255','nullable' => False),
			'email_type' => array('type' => 'varchar','precision' => '10','nullable' => False)
		 ),
		 'pk' => array('id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ));


	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.005';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }



   $test[] = '0.6.005';
   function jinn_upgrade0_6_005()
   {
	  $GLOBALS['phpgw_setup']->oProc->RenameColumn('egw_jinn_mail_list','email_table','email_object_id');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.006';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.006';
   function jinn_upgrade0_6_006()
   {
	  $GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_mail_data','list_id',array(
		 'type' => 'varchar',
		 'precision' => '255'
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.007';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.007';
   function jinn_upgrade0_6_007()
   {
	  $GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_sites',array(
		 'fd' => array(
			'site_id' => array('type' => 'auto','nullable' => False),
			'site_name' => array('type' => 'varchar','precision' => '100'),
			'site_db_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
			'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'dev_site_db_name' => array('type' => 'varchar','precision' => '100','nullable' => False),
			'dev_site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'dev_site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'dev_site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'dev_site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
			'dev_upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'dev_upload_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'website_url' => array('type' => 'varchar','precision' => '250','nullable' => False)
		 ),
		 'pk' => array('site_id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ),'upload_url');
	  $GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_sites',array(
		 'fd' => array(
			'site_id' => array('type' => 'auto','nullable' => False),
			'site_name' => array('type' => 'varchar','precision' => '100'),
			'site_db_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
			'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'dev_site_db_name' => array('type' => 'varchar','precision' => '100','nullable' => False),
			'dev_site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'dev_site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'dev_site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
			'dev_site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
			'dev_upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'website_url' => array('type' => 'varchar','precision' => '250','nullable' => False)
		 ),
		 'pk' => array('site_id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ),'dev_upload_url');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.008';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.008';
   function jinn_upgrade0_6_008()
   {
	  $GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_site_objects',array(
		 'fd' => array(
			'object_id' => array('type' => 'auto','nullable' => False),
			'parent_site_id' => array('type' => 'int','precision' => '4'),
			'name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'table_name' => array('type' => 'varchar','precision' => '30'),
			'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'relations' => array('type' => 'text'),
			'plugins' => array('type' => 'text'),
			'help_information' => array('type' => 'text'),
			'dev_upload_url' => array('type' => 'varchar','precision' => '255'),
			'dev_upload_path' => array('type' => 'varchar','precision' => '255'),
			'max_records' => array('type' => 'int','precision' => '4')
		 ),
		 'pk' => array('object_id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ),'upload_url');
	  $GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_site_objects',array(
		 'fd' => array(
			'object_id' => array('type' => 'auto','nullable' => False),
			'parent_site_id' => array('type' => 'int','precision' => '4'),
			'name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'table_name' => array('type' => 'varchar','precision' => '30'),
			'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
			'relations' => array('type' => 'text'),
			'plugins' => array('type' => 'text'),
			'help_information' => array('type' => 'text'),
			'dev_upload_path' => array('type' => 'varchar','precision' => '255'),
			'max_records' => array('type' => 'int','precision' => '4')
		 ),
		 'pk' => array('object_id'),
		 'fk' => array(),
		 'ix' => array(),
		 'uc' => array()
	  ),'dev_upload_url');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.009';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.009';
   function jinn_upgrade0_6_009()
   {
	  $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_sites','last_edit_date',array(
		 'type' => 'timestamp'
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.010';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.010';
   function jinn_upgrade0_6_010()
   {
	  $GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','last_edit_date',array(
		 'type' => 'timestamp'
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.011';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.011';
   function jinn_upgrade0_6_011()
   {
	  $GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_jinn_sites','last_edit_date',array(
		 'type' => 'int',
		 'precision' => '4'
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.012';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.6.012';
   function jinn_upgrade0_6_012()
   {
	  $GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_jinn_site_objects','last_edit_date',array(
		 'type' => 'int',
		 'precision' => '4'
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.6.013';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }


   $test[] = '0.6.013';
   function jinn_upgrade0_6_013()
   {
	  $GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_jinn_sites','last_edit_date','serialnumber');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.7.000';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.7.000';
   function jinn_upgrade0_7_000()
   {
	  $GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_jinn_site_objects','last_edit_date','serialnumber');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.7.001';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.7.001';
   function jinn_upgrade0_7_001()
   {
	  $GLOBALS['phpgw_setup']->oProc->CreateTable('phpgw_jinn_adv_field_conf',array(
		 'fd' => array(
			'parent_object' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'field_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'field_type' => array('type' => 'varchar','precision' => '20','nullable' => False),
			'field_alt_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
			'field_help_info' => array('type' => 'text','nullable' => False),
			'field_read_protection' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0')
		 ),
		 'pk' => array('parent_object','field_name'),
		 'fk' => array(),
		 'ix' => array('parent_object','field_name'),
		 'uc' => array()
	  ));

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.7.002';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.7.002';
   function jinn_upgrade0_7_002()
   {
	  $GLOBALS['phpgw_setup']->oProc->DropTable('egw_jinn_mail_data');
	  $GLOBALS['phpgw_setup']->oProc->DropTable('egw_jinn_mail_list');

	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.7.003';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.7.003';
   function jinn_upgrade0_7_003()
   {
	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.000';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.8.000';
   function jinn_upgrade0_8_000()
   {
	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.001';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.8.001';
   function jinn_upgrade0_8_001()
   {
	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.002';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   $test[] = '0.8.002';
   function jinn_upgrade0_8_002()
   {
	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.003';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

   
   
   $test[] = '0.8.003';
   function jinn_upgrade0_8_003()
   {
	  $GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_jinn_adv_field_conf');
	  $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.004';
	  return $GLOBALS['setup_info']['jinn']['currentver'];
   }

	$test[] = '0.8.004';
	function jinn_upgrade0_8_004()
	{
	   $GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_jinn_acl','egw_jinn_acl');
	   $GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_jinn_site_objects','egw_jinn_objects');
	   $GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_jinn_sites','egw_jinn_sites');
	   
	   $GLOBALS['phpgw_setup']->oProc->CreateTable('egw_jinn_obj_fields',array(
			'fd' => array(
				'field_id' => array('type' => 'int','precision' => '4'),
				'field_parent_object' => array('type' => 'int','precision' => '4'),
				'field_name' => array('type' => 'varchar','precision' => '50'),
				'field_type' => array('type' => 'varchar','precision' => '20'),
				'field_alt_name' => array('type' => 'varchar','precision' => '50'),
				'field_help_info' => array('type' => 'text'),
				'field_plugins' => array('type' => 'text')
			),
			'pk' => array('field_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array('field_id')
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.005';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.005';
	function jinn_upgrade0_8_005()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_id',array(
			'type' => 'auto'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.006';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.006';
	function jinn_upgrade0_8_006()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','hide_from_menu',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.007';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}
	
	$test[] = '0.8.007';
	function jinn_upgrade0_8_007()
	{
	   $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.100';
	   return $GLOBALS['setup_info']['jinn']['currentver'];
	}

 

	$test[] = '0.8.100';
	function jinn_upgrade0_8_100()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','hide_from_menu',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.101';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.101';
	function jinn_upgrade0_8_101()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','field_mandatory',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.102';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.102';
	function jinn_upgrade0_8_102()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','field_order',array(
			'type' => 'int',
			'precision' => '4',
			'default' => '100'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.103';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.103';
	function jinn_upgrade0_8_103()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','hide_from_menu',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.104';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.104';
	function jinn_upgrade0_8_104()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','hide_from_menu',array(
			'type' => 'char',
			'precision' => '1'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.105';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.105';
	function jinn_upgrade0_8_105()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_sites','upload_url',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_sites','dev_upload_url',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.106';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.106';
	function jinn_upgrade0_8_106()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','upload_url',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','dev_upload_url',array(
			'type' => 'varchar',
			'precision' => '250',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.107';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	 }

	 $test[] = '0.8.107';
	 function jinn_upgrade0_8_107()
	 {
		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.200';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	 }

	 $test[] = '0.8.200';
	 function jinn_upgrade0_8_200()
	 {
		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.205';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	 }

	$test[] = '0.8.205';
	function jinn_upgrade0_8_205()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','extra_where_sql_filter',array(
			'type' => 'text'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.206';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.206';
	function jinn_upgrade0_8_206()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','field_show_default',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.207';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.207';
	function jinn_upgrade0_8_207()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_order','field_position');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_position',array(
			'type' => 'varchar',
			'precision' => '10'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.208';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.208';
	function jinn_upgrade0_8_208()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','events_config',array(
			'type' => 'text'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.210';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.210';
	function jinn_upgrade0_8_210()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','unique_id',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.211';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.211';
	function jinn_upgrade0_8_211()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','unique_id',array(
			'type' => 'varchar',
			'precision' => '13'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.212';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.212';
	function jinn_upgrade0_8_212()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_sites','object_scan_prefix',array(
			'type' => 'varchar',
			'precision' => '100'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.213';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.213';
	function jinn_upgrade0_8_213()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_name',array(
			'type' => 'text'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.214';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.214';
	function jinn_upgrade0_8_214()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_sites','jinn_version',array(
			'type' => 'varchar',
			'precision' => '30'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.215';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.215';
	function jinn_upgrade0_8_215()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','field_form_visible',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.216';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.216';
	function jinn_upgrade0_8_216()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable('egw_jinn_report',array(
			'fd' => array(
				'report_id' => array('type' => 'auto','nullable' => False),
				'report_naam' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'report_object_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'report_header' => array('type' => 'text'),
				'report_body' => array('type' => 'text'),
				'report_footer' => array('type' => 'text'),
				'report_html' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'report_html_title' => array('type' => 'varchar','precision' => '25','nullable' => False)
			),
			'pk' => array('report_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.217';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.217';
	function jinn_upgrade0_8_217()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_form_visible',array(
			'type' => 'int',
			'precision' => '4',
			'default' => '1'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.218';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.218';
	function jinn_upgrade0_8_218()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_report','report_object_id',array(
			'type' => 'varchar',
			'precision' => '20',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.219';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.219';
	function jinn_upgrade0_8_219()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','field_enabled',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.220';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}
	
	$test[] = '0.8.220';
	function jinn_upgrade0_8_220()
	{
	   $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.900';
	   return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.900';
	function jinn_upgrade0_8_900()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','layoutmethod',array(
			'type' => 'char',
			'precision' => '1'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.901';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.901';
	function jinn_upgrade0_8_901()
	{
		$GLOBALS['phpgw_setup']->oProc->DropColumn('egw_jinn_obj_fields',array(
			'fd' => array(
				'field_id' => array('type' => 'auto'),
				'field_parent_object' => array('type' => 'int','precision' => '4'),
				'field_name' => array('type' => 'text'),
				'field_type' => array('type' => 'varchar','precision' => '20'),
				'field_alt_name' => array('type' => 'varchar','precision' => '50'),
				'field_help_info' => array('type' => 'text'),
				'field_plugins' => array('type' => 'text'),
				'field_mandatory' => array('type' => 'int','precision' => '4'),
				'field_show_default' => array('type' => 'int','precision' => '4'),
				'field_form_visible' => array('type' => 'int','precision' => '4','default' => '1'),
				'field_enabled' => array('type' => 'int','precision' => '4')
			),
			'pk' => array('field_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array('field_id')
		),'field_position');
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','canvas_field_x',array(
			'type' => 'int',
			'precision' => '4'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','canvas_field_y',array(
			'type' => 'int',
			'precision' => '4'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','canvas_label_x',array(
			'type' => 'int',
			'precision' => '4'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_obj_fields','canvas_label_y',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.902';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.8.902';
	function jinn_upgrade0_8_902()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','formwidth',array(
			'type' => 'int',
			'precision' => '4'
		));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_objects','formheight',array(
			'type' => 'int',
			'precision' => '4'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.8.903';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}

	$test[] = '0.8.903';
	function jinn_upgrade0_8_903()
	{
	   $GLOBALS['phpgw_setup']->oProc->AddColumn('egw_jinn_sites','host_profile',array(
		  'type' => 'varchar',
		  'precision' => '30'
	   ));

	   $GLOBALS['setup_info']['jinn']['currentver'] = '0.8.904';
	   return $GLOBALS['setup_info']['jinn']['currentver'];
	}

	$test[] = '0.8.904';
	function jinn_upgrade0_8_904()
	{

	   $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.000';
	   return $GLOBALS['setup_info']['jinn']['currentver'];
	}



	$test[] = '0.9.000';
	function jinn_upgrade0_9_000()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','site_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','site_object_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','uid',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','rights',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.001';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.001';
	function jinn_upgrade0_9_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','site_name',array(
			'type' => 'varchar',
			'precision' => '100',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','serialnumber',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','object_scan_prefix',array(
			'type' => 'varchar',
			'precision' => '100',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','jinn_version',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','host_profile',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.002';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.002';
	function jinn_upgrade0_9_002()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','parent_site_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','table_name',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','relations',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','plugins',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','help_information',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','dev_upload_path',array(
			'type' => 'varchar',
			'precision' => '255',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','max_records',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','serialnumber',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','hide_from_menu',array(
			'type' => 'char',
			'precision' => '1',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','extra_where_sql_filter',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','events_config',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','unique_id',array(
			'type' => 'varchar',
			'precision' => '13',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','layoutmethod',array(
			'type' => 'char',
			'precision' => '1',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','formwidth',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','formheight',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.003';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.003';
	function jinn_upgrade0_9_003()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_id',array(
			'type' => 'auto',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_parent_object',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_name',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_type',array(
			'type' => 'varchar',
			'precision' => '20',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_alt_name',array(
			'type' => 'varchar',
			'precision' => '50',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_help_info',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_plugins',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_mandatory',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_show_default',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_form_visible',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_enabled',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_field_x',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_field_y',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_label_x',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_label_y',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.004';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.004';
	function jinn_upgrade0_9_004()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_report','report_header',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_report','report_body',array(
			'type' => 'text',
			'nullable' => False
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_report','report_footer',array(
			'type' => 'text',
			'nullable' => False
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.005';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.005';
	function jinn_upgrade0_9_005()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_parent_object',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_mandatory',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_show_default',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_enabled',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_field_x',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_field_y',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_label_x',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','canvas_label_y',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.006';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.006';
	function jinn_upgrade0_9_006()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','parent_site_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','max_records',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','serialnumber',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','formwidth',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_objects','formheight',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.007';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.007';
	function jinn_upgrade0_9_007()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_sites','serialnumber',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.008';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.008';
	function jinn_upgrade0_9_008()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','site_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','site_object_id',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','uid',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_acl','rights',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '0'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.009';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.009';
	function jinn_upgrade0_9_009()
	{
		$GLOBALS['phpgw_setup']->oProc->CreateTable('egw_jinn_relation',array(
			'fd' => array(
				'id' => array('type' => 'varchar','precision' => '20','nullable' => False),
				'object_id' => array('type' => 'int','precision' => '4'),
				'type_num' => array('type' => 'int','precision' => '4'),
				'type_text' => array('type' => 'varchar','precision' => '20'),
				'local_field' => array('type' => 'varchar','precision' => '100'),
				'foreign_table' => array('type' => 'varchar','precision' => '100'),
				'foreign_key' => array('type' => 'varchar','precision' => '100'),
				'foreign_show_fields' => array('type' => 'text'),
				'foreign_object_uniqid' => array('type' => 'varchar','precision' => '100'),
				'connect_table' => array('type' => 'varchar','precision' => '100'),
				'connect_key_local' => array('type' => 'varchar','precision' => '100'),
				'connect_key_foreign' => array('type' => 'varchar','precision' => '100')
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id'),
			'uc' => array('id')
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.010';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.010';
	function jinn_upgrade0_9_010()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('egw_jinn_relation','object_id',array(
			'type' => 'varchar',
			'precision' => '20'
		));

		$GLOBALS['setup_info']['jinn']['currentver'] = '0.9.011';
		return $GLOBALS['setup_info']['jinn']['currentver'];
	}


	$test[] = '0.9.011';
	function jinn_upgrade0_9_011()
	{
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_type','element_type');
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_alt_name','element_label');
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_mandatory','form_listing_order');
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_show_default','list_visibility');
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_obj_fields','field_form_visible','form_visibility');

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.012';
	}


	$test[] = '0.9.012';
	function jinn_upgrade0_9_012()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_obj_fields','data_source',array(
			'type' => 'varchar',
			'precision' => '100',
			'nullable' => False
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.013';
	}


	$test[] = '0.9.013';
	function jinn_upgrade0_9_013()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_obj_fields','label_visibility',array(
			'type' => 'char',
			'precision' => '1',
			'nullable' => False
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.014';
	}


	$test[] = '0.9.014';
	function jinn_upgrade0_9_014()
	{
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','list_visibility',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','form_visibility',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_enabled',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '1'
		));
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','label_visibility',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '1'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.015';
	}


	$test[] = '0.9.015';
	function jinn_upgrade0_9_015()
	{
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','form_listing_order',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False,
			'default' => '999'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_obj_fields','single_col',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '0'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.016';
	}


	$test[] = '0.9.016';
	function jinn_upgrade0_9_016()
	{
		$GLOBALS['egw_setup']->oProc->RefreshTable('egw_jinn_obj_fields',array(
			'fd' => array(
				'field_id' => array('type' => 'auto','nullable' => False),
				'field_parent_object' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'field_name' => array('type' => 'text','nullable' => False),
				'element_type' => array('type' => 'varchar','precision' => '20','nullable' => False),
				'element_label' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'field_help_info' => array('type' => 'text','nullable' => False),
				'field_plugins' => array('type' => 'text','nullable' => False),
				'form_listing_order' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '999'),
				'list_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'form_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'field_enabled' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'canvas_field_x' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_field_y' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_label_x' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_label_y' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'data_source' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'label_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'single_col' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0')
			),
			'pk' => array('field_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.017';
	}


	$test[] = '0.9.017';
	function jinn_upgrade0_9_017()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_obj_fields','fe_readonly',array(
			'type' => 'int',
			'precision' => '1',
			'nullable' => False,
			'default' => '0'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.018';
	}


	$test[] = '0.9.018';
	function jinn_upgrade0_9_018()
	{
		$GLOBALS['egw_setup']->oProc->DropColumn('egw_jinn_objects',array(
			'fd' => array(
				'object_id' => array('type' => 'auto','nullable' => False),
				'parent_site_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'name' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'table_name' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'relations' => array('type' => 'text','nullable' => False),
				'plugins' => array('type' => 'text','nullable' => False),
				'help_information' => array('type' => 'text','nullable' => False),
				'dev_upload_path' => array('type' => 'varchar','precision' => '255','nullable' => False),
				'max_records' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'hide_from_menu' => array('type' => 'char','precision' => '1','nullable' => False),
				'upload_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'dev_upload_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'extra_where_sql_filter' => array('type' => 'text','nullable' => False),
				'events_config' => array('type' => 'text','nullable' => False),
				'unique_id' => array('type' => 'varchar','precision' => '13','nullable' => False),
				'layoutmethod' => array('type' => 'char','precision' => '1','nullable' => False),
				'formwidth' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'formheight' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0')
			),
			'pk' => array('object_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),'serialnumber');
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_objects','object_id',array(
			'type' => 'varchar',
			'precision' => '13',
			'nullable' => False
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.019';
	}


	$test[] = '0.9.019';
	function jinn_upgrade0_9_019()
	{
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_obj_fields','field_parent_object',array(
			'type' => 'varchar',
			'precision' => '13',
			'nullable' => False,
			'default' => '0'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.020';
	}


	$test[] = '0.9.020';
	function jinn_upgrade0_9_020()
	{
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_acl','site_object_id',array(
			'type' => 'varchar',
			'precision' => '13',
			'nullable' => False,
			'default' => '0'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.021';
	}


	$test[] = '0.9.021';
	function jinn_upgrade0_9_021()
	{
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_sites','serialnumber','site_version');

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.022';
	}


	$test[] = '0.9.022';
	function jinn_upgrade0_9_022()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_sites','uniqid',array(
			'type' => 'varchar',
			'precision' => '30',
			'nullable' => False
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.023';
	}


	$test[] = '0.9.023';
	function jinn_upgrade0_9_023()
	{
		$GLOBALS['egw_setup']->oProc->DropColumn('egw_jinn_sites',array(
			'fd' => array(
				'site_id' => array('type' => 'auto','nullable' => False),
				'site_name' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'site_db_name' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'dev_site_db_name' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'dev_site_db_host' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'dev_site_db_user' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'dev_site_db_password' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'dev_site_db_type' => array('type' => 'varchar','precision' => '10','nullable' => False),
				'dev_upload_path' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'website_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'upload_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'dev_upload_url' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'object_scan_prefix' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'jinn_version' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'host_profile' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'uniqid' => array('type' => 'varchar','precision' => '30','nullable' => False)
			),
			'pk' => array('site_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),'site_version');

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.024';
	}


	$test[] = '0.9.024';
	function jinn_upgrade0_9_024()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_sites','site_version',array(
			'type' => 'int',
			'precision' => '4'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.025';
	}


	$test[] = '0.9.025';
	function jinn_upgrade0_9_025()
	{
		$GLOBALS['egw_setup']->oProc->AlterColumn('egw_jinn_sites','site_version',array(
			'type' => 'int',
			'precision' => '4',
			'nullable' => False
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.026';
	}


	$test[] = '0.9.026';
	function jinn_upgrade0_9_026()
	{
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_list_del',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_list_multi',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_edit_rec',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_view_rec',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_copy_rec',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_reports',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_simple_search',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_filters',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_export',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));
		$GLOBALS['egw_setup']->oProc->AddColumn('egw_jinn_objects','disable_import',array(
			'type' => 'bool',
			'nullable' => False,
			'default' => '0'
		));

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.027';
	}


	$test[] = '0.9.027';
	function jinn_upgrade0_9_027()
	{
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_objects','disable_list_del','disable_del');
		$GLOBALS['egw_setup']->oProc->RenameColumn('egw_jinn_objects','disable_list_multi','disable_multi');

		return $GLOBALS['setup_info']['jinn']['currentver'] = '0.9.028';
	}
?>
