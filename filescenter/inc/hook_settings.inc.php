<?php
	/**************************************************************************\
	* eGroupWare - FilesCenter Preferences                                     *
	* http://www.egroupware.org                                                *
	* Modified by Pim Snel <pim@egroupware.org>, later by                      *
	*             Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>      *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option)                                                                 *
	\**************************************************************************/

	//ExecMethod('filemanager.bofilemanager.check_set_default_prefs');

	/*create_section('TESTING');

	create_check_box('Use new experimental Filemanager?','experimental_new_code','The future filemanager, now for TESTING PURPOSES ONLY, please send bugreports');

	*/
	create_section('Display attributes');

	create_subsection('File Listing');

	$file_attributes = Array(
		'name' => 'File Name',
		'mime_type' => 'MIME Type',
		'size' => 'Size',
		'created' => 'Created',
		'modified' => 'Modified',
		'owner' => 'Owner',
		'createdby_id' => 'Created by',
		'modifiedby_id' => 'Modified by',
		'app' => 'Application',
		'comment' => 'Comment',
		'version' => 'Version',
		'proper_id' => 'File ID'
	);

	$custom =& CreateObject('phpgwapi.vfs_customfields');

	$customfields = $custom->get_customfields('customfield_name');

	foreach($customfields as $key => $val)
	{
		$file_attributes[$key] = $val['customfield_description'];
	}

	while (list ($key, $value) = each ($file_attributes))
	{
		create_check_box($value,$key);
	}

	create_subsection('Upload Screen');

	unset($file_attributes);
	$file_attributes = Array(
		'upl_prefix' => 'Prefix',
		'upl_type' => 'Type'
	);

	while (list ($key, $value) = each ($file_attributes))
	{
		create_check_box($value,$key);
	}
	
	create_section('Other settings');
	create_input_box('Maximum number of backups','vfs_backups','The maximum number of backups that will be stored in the file version system. Oldest backups are deleted first. Use -1 to store unlimited backups.','5',4,4);


