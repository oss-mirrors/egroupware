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

  /**************************************************************************\
  * This file should be generated for you by setup. It should not need to be *
  * edited by hand.                                                          *
  \**************************************************************************/

  /* $Id$ */

  /* table array for  */
	$phpgw_baseline = array(
		'phpgw_jinn_acl' => array(
			'fd' => array(
				'site_id' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'site_object_id' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'uid' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'rights' => array('type' => 'int', 'precision' => 4,'nullable' => True)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_jinn_conf' => array(
			'fd' => array(
				'name' => array('type' => 'varchar', 'precision' => 15,'nullable' => False),
				'value' => array('type' => 'varchar', 'precision' => 15,'nullable' => False)
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_jinn_site_objects' => array(
			'fd' => array(
				'object_id' => array('type' => 'auto','nullable' => False),
				'parent_site_id' => array('type' => 'int', 'precision' => 4,'nullable' => True),
				'name' => array('type' => 'varchar', 'precision' => 50,'nullable' => False),
				'table_name' => array('type' => 'varchar', 'precision' => 30,'nullable' => True),
				'preview_url' => array('type' => 'varchar', 'precision' => 250,'nullable' => False),
				'upload_path' => array('type' => 'varchar', 'precision' => 250,'nullable' => False),
				'image_width' => array('type' => 'varchar', 'precision' => 5,'nullable' => False),
				'thumb_width' => array('type' => 'varchar', 'precision' => 5,'nullable' => False),
				'image_type' => array('type' => 'char', 'precision' => 3,'nullable' => False),
				'image_dir_url' => array('type' => 'varchar', 'precision' => 50,'nullable' => False),
				'relations' => array('type' => 'text','nullable' => True)
			),
			'pk' => array('object_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_jinn_sites' => array(
			'fd' => array(
				'site_id' => array('type' => 'auto','nullable' => False),
				'site_name' => array('type' => 'varchar', 'precision' => 15,'nullable' => False),
				'site_title' => array('type' => 'varchar', 'precision' => 50,'nullable' => False),
				'site_description' => array('type' => 'varchar', 'precision' => 255,'nullable' => False),
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
		)
	);
?>
