<?php
/*
Copyright Intermesh 2003
Author: Merijn Schering <mschering@intermesh.nl>
Version: 1.0 Release date: 08 July 2003

This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation; either version 2 of the License, or (at your
option) any later version.
*/

/* Modified by Vinicius Cubas Brand for egw */


#require("../Group-Office.php");

	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'disable_Template_class' => True,
		'currentapp' => 'filescenter',
		'noheader'   => True,
		'disable_Template_class' => True
	);
	include('../header.inc.php');


	//load files management class
	$vfs = CreateObject('phpgwapi.vfs');

	//load filetypes management class
	#require($GO_CONFIG->class_path."filetypes.class.inc");
	$filetypes = new vfs_mimetypes($vfs);
	$mime = isset($_REQUEST['mime']) ? $_REQUEST['mime'] : '';
	if(!$filetype = $filetypes->get_type(array(
		'extension' => $_REQUEST['extension']), true))
	{
		$filetype = $filetypes->add_type(
			array('extension' => $_REQUEST['extension'],'mime' => $mime),true);
	}

	header("Cache-Control: max-age=2592000\n");
	header("Content-type: image/gif\n");
	header("Content-Disposition: filename=".$filetype['extension'].".gif\n");
	header("Content-Transfer-Encoding: binary\n");
	echo $filetype['image'];
?>
