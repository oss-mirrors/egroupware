<?php
/**************************************************************************\
* phpGroupWare - TTS		                                           *
* http://www.phpgroupware.org                                              *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

  /* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'tts', 
		'noheader'                => True, 
		'nonavbar'                => True, 
		'noappheader'             => True,
		'noappfooter'             => True,
		'enable_contacts_class'   => True,
		'enable_nextmatchs_class' => True
	);

	include('../header.inc.php');

	$account_selected = array();
	$entry_selected = array();
	$priority_selected = array();
	
	$phpgw->preferences->read_repository();

	if ($submit)
	{
		$totalerrors = 0;
		if (! $totalerrors)
		{

 			if ($mainscreen_show_new_updated)
			{
				$phpgw->preferences->delete('tts','mainscreen_show_new_updated');
				$phpgw->preferences->add('tts','mainscreen_show_new_updated');
			}
			else
			{
				$phpgw->preferences->delete('tts','mainscreen_show_new_updated');
			}

 			if ($groupdefault)
			{
				$phpgw->preferences->delete('tts','groupdefault');
				$phpgw->preferences->add('tts','groupdefault',$groupdefault);
			}
			else
			{
				$phpgw->preferences->delete('tts','groupdefault');
			}

 			if ($assigntodefault)
			{
				$phpgw->preferences->delete('tts','assigntodefault');
				$phpgw->preferences->add('tts','assigntodefault',$assigntodefault);
			}
			else
			{
				$phpgw->preferences->delete('tts','assigntodefault');
			}

 			if ($prioritydefault)
			{
				$phpgw->preferences->delete('tts','prioritydefault');
				$phpgw->preferences->add('tts','prioritydefault',$prioritydefault);
			}
			else
			{
				$phpgw->preferences->delete('tts','prioritydefault');
			}

 			if ($refreshinterval)
			{
				$phpgw->preferences->delete('tts','refreshinterval');
				$phpgw->preferences->add('tts','refreshinterval',$refreshinterval);
			}
			else
			{
				$phpgw->preferences->delete('tts','refreshinterval');
			}

			$phpgw->preferences->save_repository(True);
			Header('Location: ' . $phpgw->link('/preferences/index.php'));
		}
	}

	$phpgw->common->phpgw_header();
	echo parse_navbar();

	if ($totalerrors)
	{
		echo '<p><center>' . $phpgw->common->error_list($errors) . '</center>';
	}

	$t = new Template(PHPGW_APP_TPL);
	$t->set_file(array(
		'preferences' => 'preferences.tpl'
	));
	$t->set_block('preferences', 'tts_select_options','tts_select_options');

	$t->set_var(action_url,$phpgw->link('/tts/preferences.php'));

	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var(tr_color,$tr_color);
	$t->set_var(lang_show_new_updated,lang('show new/updated tickets on main screen'));

	if ($phpgw_info['user']['preferences']['tts']['mainscreen_show_new_updated'])
	{
		$t->set_var(show_new_updated,' checked');
	}
	else
	{
		$t->set_var(show_new_updated,'');
	}

	if ($phpgw_info['user']['preferences']['tts']['groupdefault']) { $entry_selected[$phpgw_info['user']['preferences']['tts']['groupdefault']]=" selected"; };
	if ($phpgw_info['user']['preferences']['tts']['assigntodefault']) { $account_selected[$phpgw_info['user']['preferences']['tts']['assigntodefault']]=" selected"; };
	if ($phpgw_info['user']['preferences']['tts']['prioritydefault']) { $priority_selected[$phpgw_info['user']['preferences']['tts']['prioritydefault']]=" selected"; };

	if ($phpgw_info['user']['preferences']['tts']['refreshinterval']) {
		$t->set_var(refreshinterval,$phpgw_info['user']['preferences']['tts']['refreshinterval']);
	} else {
		$t->set_var(refreshinterval,"");
	}

	$groups = CreateObject('phpgwapi.accounts');
	$group_list = $groups->get_list('groups');
	while (list($key,$entry) = each($group_list))
	{
		    $t->set_var('tts_optionvalue', $entry['account_lid']);
    		    $t->set_var('tts_optionname', $entry['account_lid']);
		    $t->set_var('tts_optionselected', $entry_selected[$entry['account_lid']]);
		    $t->parse('tts_groupoptions','tts_select_options',true);
	}

        $t->set_var('tts_lang_assignto', lang("assign to"));
	$accounts = CreateObject('phpgwapi.accounts',$group_id);
	$account_list = $accounts->get_list('accounts');
	$t->set_var('tts_account_lid', "none" );
	$t->set_var('tts_account_name', lang("none"));
	$t->parse('tts_assignoptions','tts_select_options',false);
	while (list($key,$entry) = each($account_list))
	{
		if ($entry['account_lid'])
		{
			$t->set_var('tts_optionvalue', $entry['account_lid']);
    			$t->set_var('tts_optionname', $entry['account_lid']);
			$t->set_var('tts_optionselected', $account_selected[$entry['account_lid']]);
		}
		$t->parse('tts_assigntooptions','tts_select_options',true);
	}

	// Choose the correct priority to display
	$prority_selected[$phpgw->db->f("t_priority")] = " selected";
	$priority_comment[1]=" - ".lang("Lowest"); 
	$priority_comment[5]=" - ".lang("Medium"); 
	$priority_comment[10]=" - ".lang("Highest"); 

    	for ($i=1; $i<=10; $i++) {
	    $t->set_var('tts_optionname', $i.$priority_comment[$i]);
	    $t->set_var('tts_optionvalue', $i);
	    $t->set_var('tts_optionselected', $priority_selected[$i]);
	    $t->parse('tts_priorityoptions','tts_select_options',true);
	}

	$t->set_var(lang_refreshinterval,lang('Refresh every (seconds)'));
	$t->set_var(lang_ttsprefs,lang('tts preferences'));
	$t->set_var(lang_defaultgroup,lang('Default group'));
	$t->set_var(lang_defaultassignto,lang('Default assign to'));
	$t->set_var(lang_defaultpriority,lang('Default Priority'));
	$t->set_var(lang_submit,lang('submit'));
	$t->set_var(tts_select_options,'');

	$t->pparse('out','preferences');
	$phpgw->common->phpgw_footer();
?>
