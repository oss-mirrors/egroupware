<?php
	/**************************************************************************\
	* phpGroupWare - Addressbook hook_home                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw_info']['flags']['nodisplay'] = True;
		exit;
	}
	unset($d1);

	if ($GLOBALS['phpgw_info']['user']['apps']['addressbook']
		&& $GLOBALS['phpgw_info']['user']['preferences']['addressbook']['mainscreen_showbirthdays'])
	{
		$c = CreateObject('phpgwapi.contacts');
		$qfields = array
		(
			'n_given'  => 'n_given',
			'n_family' => 'n_family',
			'bday'     => 'bday'
		);
		$now = time() - ((60 * 60) * intval($GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset']));
		$today = $GLOBALS['phpgw']->common->show_date($now,'n/d/');

		$bdays = $c->read(0,15,$qfields,$today,'tid=n','','',$GLOBALS['phpgw_info']['user']['account_id']);

		$app_id = $GLOBALS['phpgw']->applications->name2id('addressbook');
		$GLOBALS['portal_order'][] = $app_id;

		$GLOBALS['phpgw']->portalbox->set_params(array('app_id'	=> $app_id,
														'title'	=> lang('addressbook')));
		while(list($key,$val) = @each($bdays))
		{
			if (substr($val['bday'],0,strlen($today)) == $today)
			{
				$GLOBALS['phpgw']->portalbox->data[] = array
				(
					'text'					=> lang("Today is %1's birthday!", $val['n_given'] . ' ' . $val['n_family']),
					'link'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.view&ab_id=' .  $val['id']),
					'lang_link_statustext'	=> lang('show contact')
				);
			}
		}

		$tomorrow = $GLOBALS['phpgw']->common->show_date($now + 86400,'n/d/');

		$bdays = $c->read(0,15,$qfields,$tomorrow,'tid=n','','',$GLOBALS['phpgw_info']['user']['account_id']);

		while(list($key,$val) = @each($bdays))
		{
			if (substr($val['bday'],0,strlen($tomorrow)) == $tomorrow)
			{
				$GLOBALS['phpgw']->portalbox->data[] = array
				(
					'text'					=> lang("Tomorrow is %1's birthday.", $val['n_given'] . ' ' . $val['n_family']),
					'link'					=> $GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.view&ab_id='.$val['id']),
					'lang_link_statustext'	=> lang('show contact')
				);
			}
		}
		$GLOBALS['phpgw']->portalbox->draw();
	}
?>
