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
		$title = lang('Stocks');
		
		$portalbox = CreateObject('phpgwapi.listbox',
			Array
			(
				'title'						=> $title,
				'width'						=> '100%',
				'outerborderwidth'			=> '0',
				'header_background_image'	=> $GLOBALS['phpgw']->common->image('phpgwapi/templates/default','bg_filler')
			)
		);

		$app_id = $GLOBALS['phpgw']->applications->name2id('stocks');
		$GLOBALS['portal_order'][] = $app_id;
		$var = Array
		(
			'up'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'down'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'close'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'question'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id),
			'edit'	=> Array('url'	=> '/set_box.php', 'app'	=> $app_id)
		);

		while(list($key,$value) = each($var))
		{
			$portalbox->set_controls($key,$value);
		}

		$portalbox->data = Array();
		$stocks = CreateObject('stocks.ui');

		$GLOBALS['phpgw']->template->set_var('phpgw_body',$portalbox->draw($stocks->return_quotes()),True);

		/*echo "\n" . '<!-- BEGIN Stock Quotes info -->' . "\n" . $portalbox->draw('<td>' . "\n" . $stocks->return_quotes() . "\n" . '</td>') . "\n"
					. '<!-- END Stock Quotes info -->' . "\n";*/
	}
	flush();
?>
