<?php
/**************************************************************************\
 * phpGroupWare - Setup                                                     *
 * http://www.phpgroupware.org                                              *
 * --------------------------------------------                             *
 *  This program is free software; you can redistribute it and/or modify it *
 *  under the terms of the GNU General Public License as published by the   *
 *  Free Software Foundation; either version 2 of the License, or (at your  *
 *  option) any later version.                                              *
 \**************************************************************************/

/* $Id$ */

$test[] = '0.3.0';
function jinn_upgrade0_3_0()
{
	$GLOBALS['setup_info']['jinn']['currentver'] = '0.3.1';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}


$test[] = '0.3.1';
function jinn_upgrade0_3_1()
{
	$db2 = $GLOBALS['phpgw_setup']->db;

	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_jinn_site_objects','image_dir_url',array('type' => 'varchar', 'precision' => 100));
	$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','preview_images_in_form',array('type' => 'int', 'precision' => 2,'nullable' => False, 'default' => '0'));
	$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','plugins',array('type' => 'text', 'nullable' => True));

	$GLOBALS['setup_info']['jinn']['currentver'] = '0.3.2';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}

$test[] = '0.3.2';
function jinn_upgrade0_3_2()
{
	$GLOBALS['setup_info']['jinn']['currentver'] = '0.4.0';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}

$test[] = '0.4.0';
function jinn_upgrade0_4_0()
{
	$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_jinn_sites','site_name',array('type' => 'varchar', 'precision' => 100));

	$GLOBALS['setup_info']['jinn']['currentver'] = '0.4.1';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}

$test[] = '0.4.1';
function jinn_upgrade0_4_1()
{
	$GLOBALS['setup_info']['jinn']['currentver'] = '0.4.2';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}

$test[] = '0.4.2';
function jinn_upgrade0_4_2()
{

	$newtbldef_site =  array(
			'fd' => array(
				'site_id' => array('type' => 'auto','nullable' => False),
				'site_name' => array('type' => 'varchar', 'precision' => 100,'nullable' => True),
				'site_db_name' => array('type' => 'varchar', 'precision' => 15,'nullable' => False),
				'site_db_host' => array('type' => 'varchar', 'precision' => 15,'nullable' => False),
				'site_db_user' => array('type' => 'varchar', 'precision' => 10,'nullable' => False),
				'site_db_password' => array('type' => 'varchar', 'precision' => 10,'nullable' => False),
				'site_db_type' => array('type' => 'varchar', 'precision' => 10,'nullable' => False)
				),
			'pk' => array('site_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
			);

	$newtbldef_obj =  array(
			'fd' => array(
				'object_id' => array('type' => 'auto','nullable' => False),
				'parent_site_id' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'name' => array('type' => 'varchar', 'precision' => 50,'nullable' => False),
				'table_name' => array('type' => 'varchar', 'precision' => 30,'nullable' => True),
				'upload_path' => array('type' => 'varchar', 'precision' => 250,'nullable' => False),
				'multiple_images' => array('type' => 'int', 'precision' => 2,'nullable' => False,'default' => '0'),
				'multiple_attachments' => array('type' => 'int', 'precision' => 2,'nullable' => False,'default' => '0'),
				'image_width' => array('type' => 'varchar', 'precision' => 5,'nullable' => False),
				'thumb_width' => array('type' => 'varchar', 'precision' => 5,'nullable' => False),
				'image_type' => array('type' => 'char', 'precision' => 3,'nullable' => False),
				'image_dir_url' => array('type' => 'varchar', 'precision' => 100,'nullable' => True),
				'relations' => array('type' => 'text','nullable' => True),
				'preview_images_in_form' => array('type' => 'int', 'precision' => 2,'nullable' => False,'default' => '0'),
				'plugins' => array('type' => 'text','nullable' => True),
				'extra_field_info' => array('type' => 'text','nullable' => True)
				),
			'pk' => array('object_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
				);

			$GLOBALS['phpgw_setup']->oProc->DropTable('phpgw_jinn_conf');
			$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','multiple_images',array('type' => 'int', 'precision' => 2,'nullable' => False, 'default' => '0'));
			$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','multiple_attachments',array('type' => 'int', 'precision' => 2,'nullable' => False, 'default' => '0'));		
			$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_jinn_site_objects','extra_field_info',array('type' => 'text', 'precision' => 2,'nullable' => True));				
			$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_site_objects',$newtbldef_obj,'preview_url');
			$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_sites',$newtbldef_site,'site_title');
			$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_jinn_sites',$newtbldef_site,'site_description');
			$GLOBALS['setup_info']['jinn']['currentver'] = '0.4.3';

			return $GLOBALS['setup_info']['jinn']['currentver'];	
}

$test[] = '0.4.3';
function jinn_upgrade0_4_3()
{

	$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_contentmanager_acl','phpgw_jinn_acl');
	$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_contentmanager_site_objects','phpgw_jinn_site_objects');
	$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_contentmanager_sites','phpgw_jinn_sites');
	$GLOBALS['setup_info']['jinn']['currentver'] = '0.4.4';
	return $GLOBALS['setup_info']['jinn']['currentver'];	
}


?>
