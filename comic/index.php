<?php

/*************************************************************************\
* Comics (phpGroupWare app)                                               *
* http://www.phpgroupware.org                                             *
* This file is written by: Sam Wynn <neotexan@wynnsite.com>               *
*                          Rick Bakker <r.bakker@linvision.com>           *
* --------------------------------------------                            *
* This program is free software; you can redistribute it and/or modify it *
* under the terms of the GNU General Public License as published by the   *
* Free Software Foundation; either version 2 of the License, or (at your  *
* option) any later version.                                              *
\*************************************************************************/

/* $Id$ */

$phpgw_info['flags'] = array(
	'currentapp' => 'comic',
	'noheader'   => True,
	'nonavbar'   => True
);
include('../header.inc.php');

$obj = CreateObject('comic.uicomic');
$obj->show_daily_comic();

?>
