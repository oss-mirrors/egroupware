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

	$phpgw_info['flags'] = array('noheader' => True, 
                               'nonavbar' => True,
                               'enable_nextmatchs_class' => True);

	$phpgw_info['flags']['currentapp'] = 'stocks';
	include('../header.inc.php');

	if ($action == 'add')
	{
    	$phpgw->preferences->read_repository();
		$phpgw->preferences->change('stocks',urlencode($symbol),urlencode($name));
		$phpgw->preferences->save_repository(True);

// For some odd reason, if I forward it back to stocks/preferences.php after an add
// I get no data errors, so for now forward it to the main preferences section.

		Header('Location: ' . $phpgw->link('/stocks/preferences.php'));
		$phpgw->common->phpgw_exit();
	}
	else if ($action == 'delete')
	{

// This needs to be fixed

    	$phpgw->preferences->read_repository();	
		$phpgw->preferences->delete('stocks',$value);
     	$phpgw->preferences->save_repository(True);
     	Header('Location: ' . $phpgw->link('/stocks/preferences.php'));
     	$phpgw->common->phpgw_exit();
	}

	if ($mainscreen)
	{
    	$phpgw->preferences->read_repository();
		if ($mainscreen == 'enable')
		{
			$phpgw->preferences->delete('stocks','disabled','True');
			$phpgw->preferences->add('stocks','enabled','True');
		}

		if ($mainscreen == 'disable')
		{
			$phpgw->preferences->delete('stocks','enabled','True');
			$phpgw->preferences->add('stocks','disabled','True');
		}

		$phpgw->preferences->save_repository(True);
		Header('Location: ' . $phpgw->link('/stocks/preferences.php'));
		$phpgw->common->phpgw_exit();
	}

	$phpgw->common->phpgw_header();
	echo parse_navbar();

// If they don't have any stocks in there, give them something to look at

    $phpgw->preferences->read_repository();
	if (count($phpgw_info['user']['preferences']['stocks']) == 1)
	{
		$phpgw->preferences->change('stocks','LNUX','VA%20Linux');
		$phpgw->preferences->change('stocks','RHAT','RedHat');
		$phpgw->preferences->save_repository(True);
		$phpgw_info['user']['preferences']['stocks']['LNUX'] = 'VA%20Linux';
		$phpgw_info['user']['preferences']['stocks']['RHAT'] = 'RedHat';
	}

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('stock_prefs' => 'preferences.tpl',
					'stock_prefs_t' => 'preferences.tpl'));
	$t->set_block('stock_prefs_t','stock_prefs','prefs');

	$hidden_vars = '<input type="hidden" name="symbol" value="' . $symbol . '">' . "\n"
				. '<input type="hidden" name="name" value="' . $name . '">' . "\n";

	$t->set_var('actionurl',$phpgw->link('/stocks/preferences.php'));
	$t->set_var('lang_action',lang('Stock Quote preferences'));	
    $t->set_var('h_lang_edit',lang('Edit'));
    $t->set_var('hidden_vars',$hidden_vars);
    $t->set_var('h_lang_delete',lang('Delete'));
    $t->set_var('lang_company',lang('Company name'));
    $t->set_var('lang_symbol',lang('Symbol'));
    $t->set_var('th_bg',$phpgw_info["theme"][th_bg]);

	while ($stock = each($phpgw_info['user']['preferences']['stocks']))
	{
		if (($stock[0] != 'enabled') && ($stock[0] != 'disabled'))
		{
            $dsymbol = rawurldecode($stock[0]);
            $dname = rawurldecode($stock[1]);

			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$t->set_var('tr_color',$tr_color);

			$t->set_var(array('dsymbol' => $dsymbol,                                                                                                                                                   
								'dname' => $dname));

			$t->set_var('edit',$phpgw->link('/stocks/preferences_edit.php','sym=' . $dsymbol));
			$t->set_var('lang_edit',lang('Edit'));
			$t->set_var('delete',$phpgw->link('/stocks/preferences.php','action=delete&value=' . $dsymbol));
			$t->set_var('lang_delete',lang('Delete'));

			$t->parse('prefs','stock_prefs',True);
		}
	}

	if ($phpgw_info['user']['preferences']['stocks']['enabled'])
	{
		$t->set_var('lang_display',lang('Display stocks on main screen is enabled'));
		$newstatus = 'disable';
	}
	else
	{
		$t->set_var('lang_display',lang('Display stocks on main screen is disabled'));
		$newstatus = 'enable';
	}
	
	$t->set_var('newstatus',$phpgw->link('/stocks/preferences.php','mainscreen=' . $newstatus));
	$t->set_var('lang_newstatus',lang($newstatus));

	$t->set_var('add_action',$phpgw->link('/stocks/preferences.php','action=add&name=' . $name . '&symbol=' . $symbol));
	$tr_color = '';
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);

	$t->set_var('lang_add_stock',lang('Add new stock'));
	$t->set_var('lang_add',lang('Add'));

    $t->parse('out','stock_prefs_t',True);
    $t->p('out');
	$phpgw->common->phpgw_footer();
?>
