<?php
/***************************************************************************
* copyright            : (C) 2001-2003 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it 
* under the terms of the GNU General Public License as published by the 
* Free Software Foundation; either version 2 of the License, or 
* (at your option) any later version.
***************************************************************************/

	if (!empty($_COOKIE['domain'])) {
		$dom = sprintf("%u", crc32(stripslashes($_COOKIE['domain'])));
	} else if (!empty($_GET['domain'])) {
		$dom = sprintf("%u", crc32(stripslashes($_GET['domain'])));
	} else {
		$d = opendir('.');
		readdir($d); readdir($d);
		while (($dom = readdir($d)) && !is_numeric($dom));
		closedir($d);

		if (!$dom) {
			/* should not happen */
			exit("Fatal Error");
		}
	}

	$prefix = strpos($_SERVER["REQUEST_URI"], 'index.php') ? dirname($_SERVER["REQUEST_URI"]) : $_SERVER["REQUEST_URI"]; 

	$path = $prefix . "/" . $dom . "/index.php?" . $_SERVER["QUERY_STRING"];
	header("Location: ".$path);
?>