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

	$GLOBALS['phpgw_info']['flags'] = array('noheader' => True, 
		'nonavbar' => True,
		'enable_nextmatchs_class' => True);

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'stocks';
	include('../header.inc.php');

	$action = $HTTP_GET_VARS['action'] ? $HTTP_GET_VARS['action'] : $HTTP_POST_VARS['action'];
	$name   = $HTTP_POST_VARS['name'];
	$symbol = $HTTP_POST_VARS['symbol'];
	$mainscreen = $HTTP_GET_VARS['mainscreen'];
	$sym    = $HTTP_GET_VARS['sym'];
	$value  = $HTTP_GET_VARS['value'];

	if ($action == 'add')
	{
		$GLOBALS['phpgw']->preferences->read_repository();
		$GLOBALS['phpgw']->preferences->change('stocks',urlencode(strtoupper($symbol)),urlencode($name));
		$GLOBALS['phpgw']->preferences->save_repository(True);

		// For some odd reason, if I forward it back to stocks/preferences.php after an add
		// I get no data errors, so for now forward it to the main preferences section.

		Header('Location: ' . $GLOBALS['phpgw']->link('/stocks/preferences.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	elseif ($action == 'delete')
	{
	// This needs to be fixed
		$GLOBALS['phpgw']->preferences->read_repository();
		$GLOBALS['phpgw']->preferences->delete('stocks',urlencode(strtoupper($value)));
		$GLOBALS['phpgw']->preferences->save_repository(True);
		Header('Location: ' . $GLOBALS['phpgw']->link('/stocks/preferences.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	if ($mainscreen)
	{
		$GLOBALS['phpgw']->preferences->read_repository();
		if ($mainscreen == 'enable')
		{
			$GLOBALS['phpgw']->preferences->delete('stocks','disabled','True');
			$GLOBALS['phpgw']->preferences->add('stocks','enabled','True');
		}

		if ($mainscreen == 'disable')
		{
			$GLOBALS['phpgw']->preferences->delete('stocks','enabled','True');
			$GLOBALS['phpgw']->preferences->add('stocks','disabled','True');
		}

		$GLOBALS['phpgw']->preferences->save_repository(True);
		Header('Location: ' . $GLOBALS['phpgw']->link('/stocks/preferences.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->common->phpgw_header();
	echo parse_navbar();

	// If they don't have any stocks in there, give them something to look at
	$GLOBALS['phpgw']->preferences->read_repository();
	if (count($GLOBALS['phpgw_info']['user']['preferences']['stocks']) == 1)
	{
		$GLOBALS['phpgw']->preferences->change('stocks','LNUX','VA%20Linux');
		$GLOBALS['phpgw']->preferences->change('stocks','RHAT','RedHat');
		$GLOBALS['phpgw']->preferences->save_repository(True);
		$GLOBALS['phpgw_info']['user']['preferences']['stocks']['LNUX'] = 'VA%20Linux';
		$GLOBALS['phpgw_info']['user']['preferences']['stocks']['RHAT'] = 'RedHat';
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'stock_prefs' => 'preferences.tpl',
		'stock_prefs_t' => 'preferences.tpl'
	));
	$GLOBALS['phpgw']->template->set_block('stock_prefs_t','stock_prefs','prefs');

	$hidden_vars = '<input type="hidden" name="symbol" value="' . $symbol . '">' . "\n"
		. '<input type="hidden" name="name" value="' . $name . '">' . "\n";

	$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/stocks/preferences.php'));
	$GLOBALS['phpgw']->template->set_var('lang_action',lang('Stock Quote preferences'));
	$GLOBALS['phpgw']->template->set_var('h_lang_edit',lang('Edit'));
	$GLOBALS['phpgw']->template->set_var('hidden_vars',$hidden_vars);
	$GLOBALS['phpgw']->template->set_var('h_lang_delete',lang('Delete'));
	$GLOBALS['phpgw']->template->set_var('lang_company',lang('Company name'));
	$GLOBALS['phpgw']->template->set_var('lang_symbol',lang('Symbol'));
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']["theme"][th_bg]);

	while ($stock = @each($GLOBALS['phpgw_info']['user']['preferences']['stocks']))
	{
		if (($stock[0] != 'enabled') && ($stock[0] != 'disabled'))
		{
			$dsymbol = urldecode($stock[0]);
			$dname = urldecode($stock[1]);

			$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
			$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);

			$GLOBALS['phpgw']->template->set_var(array(
				'dsymbol' => $dsymbol,
				'dname' => $dname
			));

			$GLOBALS['phpgw']->template->set_var('edit',$GLOBALS['phpgw']->link('/stocks/preferences_edit.php','sym=' . urlencode($dsymbol)));
			$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
			$GLOBALS['phpgw']->template->set_var('delete',$GLOBALS['phpgw']->link('/stocks/preferences.php','action=delete&value=' . urlencode($dsymbol)));
			$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));

			$GLOBALS['phpgw']->template->parse('prefs','stock_prefs',True);
		}
	}

	if ($GLOBALS['phpgw_info']['user']['preferences']['stocks']['enabled'])
	{
		$GLOBALS['phpgw']->template->set_var('lang_display',lang('Display stocks on main screen is enabled'));
		$newstatus = 'disable';
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('lang_display',lang('Display stocks on main screen is disabled'));
		$newstatus = 'enable';
	}

	$GLOBALS['phpgw']->template->set_var('newstatus',$GLOBALS['phpgw']->link('/stocks/preferences.php','mainscreen=' . $newstatus));
	$GLOBALS['phpgw']->template->set_var('lang_newstatus',lang($newstatus));

	$GLOBALS['phpgw']->template->set_var('add_action',$GLOBALS['phpgw']->link('/stocks/preferences.php','action=add&name=' . $name . '&symbol=' . $symbol));
	$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
	$GLOBALS['phpgw']->template->set_var('tr_color1',$GLOBALS['phpgw_info']['theme']['row_on']);
	$GLOBALS['phpgw']->template->set_var('tr_color2',$GLOBALS['phpgw_info']['theme']['row_off']);
	$GLOBALS['phpgw']->template->set_var('lang_add_stock',lang('Add new stock'));
	$GLOBALS['phpgw']->template->set_var('lang_add',lang('Add'));

	$GLOBALS['phpgw']->template->parse('out','stock_prefs_t',True);
	$GLOBALS['phpgw']->template->p('out');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
