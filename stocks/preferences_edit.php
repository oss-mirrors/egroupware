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

	$GLOBALS['phpgw_info']['flags'] = array(
		'noheader' => True,
		'nonavbar' => True,
		'enable_nextmatchs_class' => True
	);

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'stocks';
	include('../header.inc.php');

	$edit   = $HTTP_POST_VARS['edit'];
	$sym    = $HTTP_GET_VARS['sym'] ? $HTTP_GET_VARS['sym'] : $HTTP_POST_VARS['sym'];
	$name   = $HTTP_POST_VARS['name'];
	$symbol = $HTTP_POST_VARS['symbol'];

	if ($edit)
	{
		$GLOBALS['phpgw']->preferences->read_repository();
		$GLOBALS['phpgw']->preferences->delete('stocks',$sym);
		$GLOBALS['phpgw']->preferences->change('stocks',urlencode($symbol),urlencode($name));
		$GLOBALS['phpgw']->preferences->save_repository(True);
		Header('Location: ' . $GLOBALS['phpgw']->link('/stocks/preferences.php'));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->common->phpgw_header();
	echo parse_navbar();

	$GLOBALS['phpgw']->template->set_file(array('edit' => 'preferences_edit.tpl'));
	$GLOBALS['phpgw']->template->set_var('actionurl',$GLOBALS['phpgw']->link('/stocks/preferences_edit.php'));
	$GLOBALS['phpgw']->template->set_var('lang_action',lang('Stock Quote preferences'));

	$common_hidden_vars = '<input type="hidden" name="sym" value="' . $sym . '">' . "\n";
	$GLOBALS['phpgw']->template->set_var('common_hidden_vars',$common_hidden_vars);
	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('h_lang_edit',lang('Edit stock'));
	$GLOBALS['phpgw']->template->set_var('lang_symbol',lang('Symbol'));
	$GLOBALS['phpgw']->template->set_var('lang_company',lang('Company name'));

	@reset($GLOBALS['phpgw_info']['user']['preferences']['stocks']);
	while ($stock = @each($GLOBALS['phpgw_info']['user']['preferences']['stocks']))
	{
		if (rawurldecode($stock[0]) == $sym)
		{
			$GLOBALS['phpgw']->template->set_var('tr_color1',$GLOBALS['phpgw_info']['theme']['row_on']);
			$GLOBALS['phpgw']->template->set_var('tr_color2',$GLOBALS['phpgw_info']['theme']['row_off']);
			$GLOBALS['phpgw']->template->set_var('symbol',rawurldecode($stock[0]));
			$GLOBALS['phpgw']->template->set_var('name',rawurldecode($stock[1]));
		}
	}

	$GLOBALS['phpgw']->template->set_var('lang_edit',lang('Edit'));
	$GLOBALS['phpgw']->template->pparse('out','edit');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
