<?php

  /***************************************************************************\
  * eGroupWare - File Manager 2                                               *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>                *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  * Description: Tables description for vfs (sql implementation v.2)          *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	# viniciuscb: I guess this should be better in phpgwapi tables description

	$phpgw_baseline = array(
		'phpgw_vfs2_mimetypes' => array(
			'fd' => array(
				'mime_id' => array('type' => 'auto','nullable' => False),
				'extension' => array('type' => 'varchar', 'precision' => 10, 'nullable' => false),
				'mime' => array('type' => 'varchar', 'precision' => 50, 'nullable' => false),
				'mime_magic' => array('type' => 'varchar', 'precision' => 255, 'nullable' => true),
				'friendly' => array('type' => 'varchar', 'precision' => 50, 'nullable' => false),
				'image' => array('type' => 'blob'),
				'proper_id' => array('type' => 'varchar', 'precision' => 4, 'nullable' => true)
			),
			'pk' => array('mime_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_vfs2_files' => array(
			'fd' => array(
				'file_id' => array('type' => 'auto','nullable' => False),
				'mime_id' => array('type' => 'int','precision' => 4),
				'owner_id' => array('type' => 'int','precision' => 4,'nullable' => False),
				'createdby_id' => array('type' => 'int','precision' => 4),
				'created' => array('type' => 'timestamp','default' => '1970-01-01 00:00:00', 'nullable' => False),
				'size' => array('type' => 'int','precision' => 8),
				'deleteable' => array('type' => 'char','precision' => 1,'default' => 'Y'),
				'comment' => array('type' => 'varchar','precision' => 255),
				'app' => array('type' => 'varchar','precision' => 25),
				'directory' => array('type' => 'varchar','precision' => 255),
				'name' => array('type' => 'varchar','precision' => 128,'nullable' => False),
				'link_directory' => array('type' => 'varchar','precision' => 255),
				'link_name' => array('type' => 'varchar','precision' => 128),
				'version' => array('type' => 'varchar','precision' => 30,'nullable' => False,'default' => '0.0.0.0'),
				'content' => array('type' => 'longtext'),
				'is_backup' => array('type' => 'varchar', 'precision' => 1, 'nullable' => False, 'default' => 'N'),
				'shared' => array('type' => 'varchar', 'precision' => 1, 'nullable' => False,'default' => 'N'),
				'proper_id' => array('type' => 'varchar', 'precision' => 45)
			),
			'pk' => array('file_id'),
			'fk' => array('mime_id' => array ('phpgw_vfs2_mimetypes' => 'mime_id')),
			'ix' => array(array('directory','name')),
			'uc' => array()
		),

		'phpgw_vfs2_customfields' => array(
			'fd' => array(
				'customfield_id' => array('type' => 'auto','nullable' => False),
				'customfield_name' => array('type' => 'varchar','precision' => 60,'nullable' => False),
				'customfield_description' => array('type' => 'varchar','precision' => 255,'nullable'=> True),
				'customfield_type' => array('type' => 'varchar','precision' => 20, 'nullable' => false),
				'customfield_precision' => array('type' => 'int', 'precision' => 4, 'nullable' => true),
				'customfield_active' => array('type' => 'varchar','precision' => 1,'nullable' => False,'default' => 'N')
			),
			'pk' => array('customfield_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_vfs2_quota' => array(
			'fd' => array(
				'account_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'quota' => array('type' => 'int','precision' => 4,'nullable' => false)
			),
			'pk' => array('account_id'),
			'fk' => array('account_id' => array('phpgw_accounts' => 'account_id')),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_vfs2_shares' => array(
			'fd' => array(
				'account_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'file_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'acl_rights' => array('type' => 'int','precision' => 4,'nullable' => false)
			),
			'pk' => array('account_id','file_id'),
			'fk' => array('account_id' => array('phpgw_accounts' => 'account_id'), 'file_id' => array('phpgw_vfs2_files' => 'file_id')),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_vfs2_versioning' => array(
			'fd' => array(
				'version_id' => array('type' => 'auto', 'nullable' => false),
				'file_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'operation' => array('type' => 'int','precision' => 4, 'nullable' => False),
				'modifiedby_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'modified' => array('type' => 'timestamp', 'nullable' => False ),
				'version' => array('type' => 'varchar', 'precision' => 30, 'nullable' => False ),
				'comment' => array('type' => 'varchar', 'precision' => 255, 'nullable' => True),
				'backup_file_id' => array('type' => 'int','precision' => 4, 'nullable' => True),
				'backup_content' => array('type' => 'longtext', 'nullable' => True),
				'src' => array('type' => 'varchar', 'precision' => 255, 'nullable' => True),
				'dest' => array('type' => 'varchar', 'precision' => 255, 'nullable' => True)
			),
			'pk' => array('version_id'),
			'fk' => array('file_id' => array('phpgw_vfs2_files' => 'file_id')),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_vfs2_customfields_data' => array(
			'fd' => array(
				'file_id' => array('type' => 'int','precision' => 4,'nullable' => false),
				'customfield_id' => array('type' => 'int', 'precision' => 4, 'nullable' => false),
				'data' => array('type' => 'longtext', 'nullable' => True)
			),
			'pk' => array('file_id','customfield_id'),
			'fk' => array('file_id' => array('phpgw_vfs2_files' => 'file_id'),'customfield_id' => array('phpgw_vfs2_customfields' => 'customfield_id')),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_vfs2_prefixes' => array(
			'fd' => array(
				'prefix_id' => array('type' => 'auto','nullable' => false),
				'prefix' => array('type' => 'varchar', 'precision' => 8, 'nullable' => false),
				'owner_id' => array('type' => 'int', 'precision' => 4, 'nullable' => false),
				'prefix_description' => array('type' => 'varchar', 'precision' => 30, 'nullable' => True),
				'prefix_type' => array('type' => 'varchar', 'precision' => 1, 'nullable' => false, 'default' => 'p')
			),
			'pk' => array('prefix_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()

		)
	);

?>
