<?php
  /**************************************************************************\
  * phpGroupWare - Trouble Ticket System                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$submit = $HTTP_POST_VARS['submit'];
	if ($submit)
	{
		$GLOBALS['phpgw_info']['flags'] = array('noheader' => True, 'nonavbar' => True);
	}

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_send_class']   = True;
	$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	
	include('../header.inc.php');

	$account_selected = array();
	$entry_selected = array();
	$priority_selected = array();
	$priority_comment = array();

	if (! $submit)
	{
		$GLOBALS['phpgw']->preferences->read_repository();
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

		$GLOBALS['phpgw']->template->set_file(array(
			'newticket'   => 'newticket.tpl'
		));

		$GLOBALS['phpgw']->template->set_block('newticket', 'tts_new_lstassignto','tts_new_lstassignto');
		$GLOBALS['phpgw']->template->set_block('newticket', 'tts_new_lstcategory','tts_new_lstcategory');
		$GLOBALS['phpgw']->template->set_block('newticket', 'tts_select_options','tts_select_options');
	
		$GLOBALS['phpgw']->template->set_unknowns('remove');
		$GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php'));
		$GLOBALS['phpgw']->template->set_var('tts_bgcolor',$theme['th_bg'] );
		$GLOBALS['phpgw']->template->set_var('tts_textcolor', $theme['th_text'] );
		$GLOBALS['phpgw']->template->set_var('tts_lang_addnewticket', lang('Add new ticket'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_group', lang('Group'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_subject', lang('Subject') );
		$GLOBALS['phpgw']->template->set_var('tts_lang_nosubject', lang('No subject'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_details', lang('Detail'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_priority', lang('Priority'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_lowest', lang('Lowest'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_medium', lang('Medium'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_highest', lang('Highest'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_addticket', lang('Add Ticket'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_clearform', lang('Clear Form'));

		$groups = CreateObject('phpgwapi.accounts');
		$group_list = $groups->get_list('groups');
		while (list($key,$entry) = each($group_list))
		{
			$GLOBALS['phpgw']->template->set_var('tts_account_lid', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_account_name', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_categoryselected', $entry_selected[$entry['account_lid']]);
			$GLOBALS['phpgw']->template->parse('tts_new_lstcategories','tts_new_lstcategory',true);
		}

		$GLOBALS['phpgw']->template->set_var('tts_lang_assignto', lang('assign to'));
		$accounts = $groups;
		$accounts->account_id = $group_id;
		$account_list = $accounts->get_list('accounts');

		$GLOBALS['phpgw']->template->set_var('tts_account_lid', 'none' );
		$GLOBALS['phpgw']->template->set_var('tts_account_name', lang('none'));
		$GLOBALS['phpgw']->template->parse('tts_new_lstassigntos','tts_new_lstassignto',false);
		
		while (list($key,$entry) = each($account_list))
		{
			if ($entry['account_lid'])
			{
				$GLOBALS['phpgw']->template->set_var('tts_account_lid', $entry['account_lid']);
				$GLOBALS['phpgw']->template->set_var('tts_account_name', $entry['account_lid']);
				$GLOBALS['phpgw']->template->set_var('tts_assignedtoselected', $account_selected[$entry['account_lid']]);
			}
			$GLOBALS['phpgw']->template->parse('tts_new_lstassigntos','tts_new_lstassignto',true);
		}

		// Choose the correct priority to display
		// $prority_selected[$GLOBALS['phpgw']->db->f("t_priority")] = " selected";
		$priority_comment[1]=' - '.lang('Lowest');
		$priority_comment[5]=' - '.lang('Medium');
		$priority_comment[10]=' - '.lang('Highest');
		for ($i=1; $i<=10; $i++)
		{
			$GLOBALS['phpgw']->template->set_var('tts_optionname', $i.$priority_comment[$i]);
			$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $i);
			$GLOBALS['phpgw']->template->set_var('tts_optionselected', $priority_selected[$i]);
			$GLOBALS['phpgw']->template->parse('tts_priority_options','tts_select_options',true);
		}

		$GLOBALS['phpgw']->template->set_var('tts_select_options','');
		$GLOBALS['phpgw']->template->set_var('tts_new_lstcategory','');
		$GLOBALS['phpgw']->template->set_var('tts_new_lstassignto','');
	
		$GLOBALS['phpgw']->template->pparse('out', 'newticket');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	else
	{
		//$current_date = date("ymdHi");		//set timestamp
		$txtDetail .= $GLOBALS['phpgw_info']['user']['userid'] . ' - ' . $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f(6)) . "<BR>\n";
		$txtDetail .= $txtAdditional . '<br><hr>';
		$txtDetail = addslashes(nl2br($txtDetail));

		$GLOBALS['phpgw']->db->query("INSERT INTO phpgw_tts_tickets (t_category,t_detail,t_priority,t_user,t_assignedto, "
			. " t_timestamp_opened,t_timestamp_closed,t_subject) VALUES ('$lstCategory','$txtDetail',"
			. "'$optPriority','" . $GLOBALS['phpgw_info']["user"]["userid"] . "','$assignto','"
			. time() . "',0,'$subject');");
		$GLOBALS['phpgw']->db->query("SELECT t_id FROM phpgw_tts_tickets WHERE t_subject='$subject' AND t_user='".$GLOBALS['phpgw_info']['user']['userid']."'");
		$GLOBALS['phpgw']->db->next_record();
		if($GLOBALS['phpgw_info']['server']['tts_mailticket'])
		{
			mail_ticket($GLOBALS['phpgw']->db->f('t_id'));
		}

		Header('Location: ' . $GLOBALS['phpgw']->link('/tts/index.php'));
	}
?>
