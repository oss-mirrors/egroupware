<?php
	/**************************************************************************\
	* phpGroupWare - Stock Quotes                                              *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = PHPGW_APP_INC;
	define('PHPGW_APP_INC',$phpgw->common->get_inc_dir('stocks'));

	if ($phpgw_info['user']['apps']['stocks'] && $phpgw_info['user']['preferences']['stocks']['enabled'])
	{
		echo "\n" . '<!-- Stock Quotes -->' . "\n";
		include(PHPGW_APP_INC . '/stocks/inc/functions.inc.php');
		echo '<tr><td align="center">' . return_quotes($quotes) . '</td></tr>';
		echo "\n" . '<!-- Stock Quotes -->' . "\n";
	}

	define('PHPGW_APP_INC',$tmp_app_inc);
?>
