<?php
	/**************************************************************************\
	* eGroupWare - mydms                                                       *
	* http://www.egroupware.org                                                *
	* This application is ported from Mydms                                    *
        *        by Lian Liming <dawnlinux@realss.com>                             *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_baseline = array(
		'phpgw_mydms_ACLs' => array(
			'fd' => array(
				'id' => array('type' => 'auto', 'nullable' => False),
				'target' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
				'targetType' => array('type' => 'int', 'precision' => '2', 'nullable' => False ,'default' => '0'),
				'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '-1'),
				'groupID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '-1'),
				'mode' => array('type' => 'int', 'precision' => '2', 'nullable' => False, 'default' => '0')
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_mydms_DocumentContent' => array(
			'fd' => array(
				'id' => array('type' => 'auto', 'nullable' => False),
				'document' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
				'version'  => array('type' => 'int', 'precision' => '2', 'default' => 'NULL'),
				'comment'  => array('type' => 'text'),
				'date'     => array('type' => 'int', 'precision' => '8', 'default' => 'NULL'),
				'createdBy' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
				'dir'      => array('type' => 'varchar', 'precision' => '10','default' => ' ','nullable' => False),
				'orgFileName' => array('type' => 'varchar', 'precision' => '150', 'default' => ' ', 'nullable' => False),
				'fileType' => array('type' => 'varchar', 'precision' => '10', 'default' => ' ', 'nullable' => False),
				'mimeType' => array('type' => 'varchar', 'precision' => '70', 'default' => ' ', 'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_mydms_Documents' => array(
			'fd' => array(
				'id' => array('type' => 'auto', 'nullable' => False),
				'name' => array('type' => 'varchar', 'precision' => '150', 'default' => 'NULL'),
				'comment' => array('type' => 'text'),
				'date' => array('type' => 'int', 'precision' => '8', 'default' => 'NULL'),
				'expires' => array('type' => 'int', 'precision' => '8', 'default' => 'NULL'),
				'owner' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
				'folder' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
				'inheritAccess' => array('type' => 'bool', 'nullable' => False, 'default' => '1'),
				'defaultAccess' => array('type' => 'int', 'precision' => '2', 'nullable' => False, 'default' => '0'),
				'locked' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '-1'),
				'keywords' => array('type' => 'text', 'nullable' => False),
				'sequence' => array('type' => 'float', 'precision' => '8', 'nullable' => False, 'default' => '0')
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
	),

	'phpgw_mydms_Folders' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'name' => array('type' => 'varchar', 'precision' => '70', 'default' => 'NULL'),
			'parent' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
			'comment' => array('type' => 'text'),
			'owner' => array('type' => 'int', 'precision' => '4', 'default' => 'NULL'),
			'inheritAccess' => array('type' => 'bool', 'nullable' => False, 'default' => '1'),
			'defaultAccess' => array('type' => 'int', 'precision' => '2', 'nullable' => False, 'default' => '0'),
			'sequence' => array('type' => 'float', 'precision' => '8', 'nullable' => False, 'default' => '0')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),


	'phpgw_mydms_GroupMembers' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'groupID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
		
	'phpgw_mydms_Groups' => array(
		'fd' => array(
	        'id' => array('type' => 'auto', 'nullable' => False),
			'name' => array('type' => 'varchar', 'precision' => '50', 'default' => 'NULL'),
			'comment' => array('type' => 'text', 'nullable' => 'NULL')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_Notify' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'target' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'targetType' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '-1'),
			'groupID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '-1')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_DocumentLinks' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'document' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'target' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'public' => array('type' => 'bool', 'nullable' => False, 'default' => '0')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_Sessions' => array(
		'fd' => array(
			'id' => array('type' => 'char','precision' => '50', 'nullable' => False, 'default' => ''),
			'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'lastAccess' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'theme' => array('type' => 'varchar', 'precision' => '30', 'nullable' => False, 'default' => ''),
			'language' => array('type' => 'varchar', 'precision' => '30', 'nullable' => False, 'default' => '')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_UserImages' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'userID' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'image' => array('type' => 'blob', 'nullable' => False),	
			'mimeType' => array('type' => 'varchar', 'precision' => '10', 'nullable' => False, 'default' => '')		  
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_Users' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'login' => array('type' => 'varchar', 'precision' => '50', 'default' => 'NULL'),
			'pwd' => array('type' => 'varchar', 'precision' => '50', 'default' => 'NULL'),
			'fullName' => array('type' => 'varchar', 'precision' => '100', 'default' => 'NULL'),
			'email' => array('type' => 'varchar', 'precision' => '70', 'default' => 'NULL'),
			'comment' => array('type' => 'text', 'nullable' => False),
			'isAdmin' => array('type' => 'int', 'precision' => '2', 'nullable' => False, 'default' => '0')		 
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_KeywordCategories' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'name' => array('type' => 'varchar', 'precision' => '255', 'nullable' => False, 'default' => ''),
			'owner' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0')
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),

	'phpgw_mydms_Keywords' => array(
		'fd' => array(
			'id' => array('type' => 'auto', 'nullable' => False),
			'category' => array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'),
			'keywords' => array('type' => 'text', 'nullable' => False)
		),
		'pk' => array('id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	)
        );
?>
