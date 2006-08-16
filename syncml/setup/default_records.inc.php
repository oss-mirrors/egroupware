<?php
/**************************************************************************\
* eGroupWare - Setup                                                       *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id: default_records.inc.php 20209 2006-01-06 05:06:46Z ralfbecker $ */

//$oProc->m_odb->Halt_On_Error = 'yes';

 
/*

foreach(array(
	'sessions_checkip' => 'True',
	'image_type'       => '1',
	'asyncservice'     => 'fallback',
) as $name => $value)
{
	$oProc->insert($GLOBALS['egw_setup']->config_table,array(
		'config_value' => $value,
	),array(
		'config_app' => 'phpgwapi',
		'config_name' => $name,
	),__LINE__,__FILE__);
}

$oProc->query("INSERT INTO egw_interserv(server_name,server_host,server_url,trust_level,trust_rel,server_mode) VALUES ('eGW demo',NULL,'http://www.egroupware.org/egroupware/xmlrpc.php',99,0,'xmlrpc')");

// insert the VFS basedir /home
$oProc->query ("INSERT INTO egw_vfs (vfs_owner_id, vfs_createdby_id, vfs_modifiedby_id, vfs_created, vfs_modified, vfs_size, vfs_mime_type, vfs_deleteable, vfs_comment, vfs_app, vfs_directory, vfs_name, vfs_link_directory, vfs_link_name) VALUES (0,0,0,'1970-01-01',NULL,NULL,'Directory','Y',NULL,NULL,'/','', NULL, NULL)");
$oProc->query ("INSERT INTO egw_vfs (vfs_owner_id, vfs_createdby_id, vfs_modifiedby_id, vfs_created, vfs_modified, vfs_size, vfs_mime_type, vfs_deleteable, vfs_comment, vfs_app, vfs_directory, vfs_name, vfs_link_directory, vfs_link_name) VALUES (0,0,0,'1970-01-01',NULL,NULL,'Directory','Y',NULL,NULL,'/','home', NULL, NULL)");

if ($GLOBALS['DEBUG']) {
	echo " DONE!</b>";
}
*/
/*************************************************************************/
