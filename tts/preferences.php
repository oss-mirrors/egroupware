<?php
  /**************************************************************************\
  * phpGroupWare - TTS                                                       *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *	This program is free software; you can redistribute it and/or modify it  *
  *	under the terms of the GNU General Public License as published by the    *
  *	Free Software Foundation; either version 2 of the License, or (at your   *
  *	option) any later version.                                               *
  \**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'tts', 
		'noheader'    => True, 
		'nonavbar'    => True, 
		'noappheader' => True,
		'noappfooter' => True,
		'enable_contacts_class'   => True,
		'enable_nextmatchs_class' => True
	);

	include('../header.inc.php');

	$account_selected = array();
	$entry_selected = array();
	$priority_selected = array();

	$GLOBALS['phpgw']->preferences->read_repository();

	if ($HTTP_POST_VARS['submit'])
	{
		$totalerrors = 0;
		if (! $totalerrors)
		{
			if ($HTTP_POST_VARS['mainscreen_show_new_updated'])
			{
				$GLOBALS['phpgw']->preferences->delete('tts','mainscreen_show_new_updated');
				$GLOBALS['phpgw']->preferences->add('tts','mainscreen_show_new_updated',True);
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('tts','mainscreen_show_new_updated');
			}

			if ($HTTP_POST_VARS['groupdefault'])
			{
				$GLOBALS['phpgw']->preferences->delete('tts','groupdefault');
				$GLOBALS['phpgw']->preferences->add('tts','groupdefault',$HTTP_POST_VARS['groupdefault']);
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('tts','groupdefault');
			}

			if ($HTTP_POST_VARS['assigntodefault'])
			{
				$GLOBALS['phpgw']->preferences->delete('tts','assigntodefault');
				$GLOBALS['phpgw']->preferences->add('tts','assigntodefault',$HTTP_POST_VARS['assigntodefault']);
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('tts','assigntodefault');
			}

			if ($HTTP_POST_VARS['prioritydefault'])
			{
				$GLOBALS['phpgw']->preferences->delete('tts','prioritydefault');
				$GLOBALS['phpgw']->preferences->add('tts','prioritydefault',$HTTP_POST_VARS['prioritydefault']);
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('tts','prioritydefault');
			}

			if ($HTTP_POST_VARS['refreshinterval'])
			{
				$GLOBALS['phpgw']->preferences->delete('tts','refreshinterval');
				$GLOBALS['phpgw']->preferences->add('tts','refreshinterval',$HTTP_POST_VARS['refreshinterval']);
			}
			else
			{
				$GLOBALS['phpgw']->preferences->delete('tts','refreshinterval');
			}

			$GLOBALS['phpgw']->preferences->save_repository(True);
			Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
		}
	}

	$GLOBALS['phpgw']->common->phpgw_header();
	echo parse_navbar();

	if ($totalerrors)
	{
		echo '<p><center>' . $GLOBALS['phpgw']->common->error_list($errors) . '</center>';
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'preferences' => 'preferences.tpl'
	));
	$GLOBALS['phpgw']->template->set_block('preferences', 'tts_select_options','tts_select_options');

	$GLOBALS['phpgw']->template->set_var(action_url,$GLOBALS['phpgw']->link('/tts/preferences.php'));

	$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
	$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);
	$GLOBALS['phpgw']->template->set_var('lang_show_new_updated',lang('show new/updated tickets on main screen'));

	if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['mainscreen_show_new_updated'])
	{
		$GLOBALS['phpgw']->template->set_var('show_new_updated',' checked');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('show_new_updated','');
	}

	if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault'])
	{
		$entry_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault']]=' selected';
	}
	if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault'])
	{
		$account_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault']]=' selected';
	}
	if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault'])
	{
		$priority_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault']]=' selected';
	}

	if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
	{
		$GLOBALS['phpgw']->template->set_var('refreshinterval',$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval']);
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('refreshinterval','');
	}

	$groups = CreateObject('phpgwapi.accounts');
	$group_list = $groups->get_list('groups');
	while (list($key,$entry) = each($group_list))
	{
		$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $entry['account_lid']);
		$GLOBALS['phpgw']->template->set_var('tts_optionname', $entry['account_lid']);
		$GLOBALS['phpgw']->template->set_var('tts_optionselected', $entry_selected[$entry['account_lid']]);
		$GLOBALS['phpgw']->template->parse('tts_groupoptions','tts_select_options',true);
	}

	$GLOBALS['phpgw']->template->set_var('tts_lang_assignto', lang('assign to'));
	$accounts = CreateObject('phpgwapi.accounts',$group_id);
	$account_list = $accounts->get_list('accounts');
	$GLOBALS['phpgw']->template->set_var('tts_account_lid', 'none' );
	$GLOBALS['phpgw']->template->set_var('tts_account_name', lang('none'));
	$GLOBALS['phpgw']->template->parse('tts_assignoptions','tts_select_options',false);
	while (list($key,$entry) = each($account_list))
	{
		if ($entry['account_lid'])
		{
			$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionname', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionselected', $account_selected[$entry['account_lid']]);
		}
		$GLOBALS['phpgw']->template->parse('tts_assigntooptions','tts_select_options',true);
	}

	// Choose the correct priority to display
	$prority_selected[$GLOBALS['phpgw']->db->f('t_priority')] = ' selected';
	$priority_comment[1]  = ' - ' . lang('Lowest'); 
	$priority_comment[5]  = ' - ' . lang('Medium'); 
	$priority_comment[10] = ' - ' . lang('Highest'); 

	for ($i=1; $i<=10; $i++)
	{
		$GLOBALS['phpgw']->template->set_var('tts_optionname', $i.$priority_comment[$i]);
		$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $i);
		$GLOBALS['phpgw']->template->set_var('tts_optionselected', $priority_selected[$i]);
		$GLOBALS['phpgw']->template->parse('tts_priorityoptions','tts_select_options',true);
	}

	$GLOBALS['phpgw']->template->set_var('lang_refreshinterval',lang('Refresh every (seconds)'));
	$GLOBALS['phpgw']->template->set_var('lang_ttsprefs',lang('tts preferences'));
	$GLOBALS['phpgw']->template->set_var('lang_defaultgroup',lang('Default group'));
	$GLOBALS['phpgw']->template->set_var('lang_defaultassignto',lang('Default assign to'));
	$GLOBALS['phpgw']->template->set_var('lang_defaultpriority',lang('Default Priority'));
	$GLOBALS['phpgw']->template->set_var('lang_submit',lang('submit'));
	$GLOBALS['phpgw']->template->set_var('tts_select_options','');

	$GLOBALS['phpgw']->template->pparse('out','preferences');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
