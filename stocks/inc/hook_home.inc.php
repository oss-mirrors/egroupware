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
		echo 'Failed attempt to break in via an old Security Hole!<br>'."\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	if ($GLOBALS['phpgw_info']['user']['apps']['stocks'] && $GLOBALS['phpgw_info']['user']['preferences']['stocks']['mainscreen']['enabled'])
	{
		$app_id	= $GLOBALS['phpgw']->applications->name2id('stocks');
		$GLOBALS['portal_order'][] = $app_id;

		$portalbox = CreateObject('phpgwapi.listbox',array
		(
			'app_name'	=> 'stocks',
			'app_id'	=> $app_id,
			'title'		=> lang('Stocks')
		));

		$stocks = CreateObject('stocks.ui');
		$portalbox->xdraw($stocks->return_quotes());
	}
?>
