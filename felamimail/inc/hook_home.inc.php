<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	if($GLOBALS['phpgw_info']['user']['preferences']['felamimail']['mainscreen_showmail'])
	{

	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo 'Failed attempt to break in via an old Security Hole!<br>'."\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $GLOBALS['phpgw']->common->get_inc_dir('felamimail');

#		// ----  Create the base email Msg Class    -----
		$GLOBALS['phpgw']->translation->add_app('felamimail');
		$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");

			$title = '<font color="#FFFFFF">'.lang('felamimail').'</font>';
		
			$portalbox = CreateObject('phpgwapi.listbox',
				Array(
					'title'	=> $title,
					'primary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'secondary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'tertiary'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
					'width'	=> '100%',
					'outerborderwidth'	=> '0',
					'header_background_image'	=> $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler.gif')
				)
			);

			$app_id = $GLOBALS['phpgw']->applications->name2id('calendar');
			$GLOBALS['portal_order'][] = $app_id;
			$var = Array(
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

		
			if($data)
			{
				$portalbox->data = $data;
			}

			// output the portalbox and below it (1) the folders listbox (if applicable) and (2) Compose New mail link
			echo "\r\n".'<!-- start Mailbox info -->'."\r\n"
				.$portalbox->draw($extra_data)
				.'<!-- ends Mailox info -->'."\r\n";
	}
?>
