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
?>
