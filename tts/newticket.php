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

	// $Id$
	// $Source$

	$submit = $HTTP_POST_VARS['submit'];
	$cancel = $HTTP_POST_VARS['cancel'];
	if($submit || $cancel)
	{
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => True,
			'nonavbar' => True
		);
	}

	$GLOBALS['phpgw_info']['flags']['currentapp']          = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_send_class']   = True;
	$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;

	include('../header.inc.php');

	$GLOBALS['phpgw']->config->read_repository();

	if($cancel)
	{
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/index.php'));
	}

	if($submit)
	{
		$GLOBALS['phpgw']->db->query("insert into phpgw_tts_tickets (ticket_group,ticket_priority,ticket_owner,"
			. "ticket_assignedto,ticket_subject,ticket_category,ticket_billable_hours,"
			. "ticket_billable_rate,ticket_status,ticket_details) values ('"
                        . $ticket['group'] . "','"
			. $ticket['priority'] . "','"
			. $GLOBALS['phpgw_info']['user']['account_id'] . "','"
			. $ticket['assignedto'] . "','"
			. $ticket['subject'] . "','"
			. $ticket['category'] . "','"
			. $ticket['billable_hours'] . "','"
			. $ticket['billable_rate'] . "','O','"
			. addslashes($ticket['details']) . "')",__LINE__,__FILE__);

		$ticket_id = $GLOBALS['phpgw']->db->get_last_insert_id('phpgw_tts_tickets','ticket_id');

		$historylog = createobject('phpgwapi.historylog','tts');
		$historylog->add('O',$ticket_id,' ','');

		if($GLOBALS['phpgw']->config->config_data['mailnotification'])
		{
			mail_ticket($ticket_id);
		}

		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/viewticket_details.php','&ticket_id=' . $ticket_id));
	}

	$account_selected  = array();
	$entry_selected    = array();
	$priority_selected = array();
	$priority_comment  = array();

	if(!$submit)
	{
		$GLOBALS['phpgw']->preferences->read_repository();
		if($GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault'])
		{
			$entry_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault']] = ' selected';
		}

		if($GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault'])
		{
			$account_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault']] = ' selected';
		}

		if($GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault'])
		{
			$priority_selected[$GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault']] = ' selected';
		}

		$GLOBALS['phpgw']->template->set_file(array(
			'newticket'   => 'newticket.tpl'
		));
		$GLOBALS['phpgw']->template->set_block('newticket','options_select');
		$GLOBALS['phpgw']->template->set_block('newticket','form');

		$GLOBALS['phpgw']->template->set_var('lang_create_new_ticket',lang('Create new ticket'));
		$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/newticket.php'));

		$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
		$GLOBALS['phpgw']->template->set_var('lang_group', lang('Group'));
		$GLOBALS['phpgw']->template->set_var('lang_subject', lang('Subject') );
		$GLOBALS['phpgw']->template->set_var('lang_nosubject', lang('No subject'));
		$GLOBALS['phpgw']->template->set_var('lang_details', lang('Details'));
		$GLOBALS['phpgw']->template->set_var('lang_priority', lang('Priority'));
		$GLOBALS['phpgw']->template->set_var('lang_lowest', lang('Lowest'));
		$GLOBALS['phpgw']->template->set_var('lang_medium', lang('Medium'));
		$GLOBALS['phpgw']->template->set_var('lang_highest', lang('Highest'));
		$GLOBALS['phpgw']->template->set_var('lang_addticket', lang('Add Ticket'));
		$GLOBALS['phpgw']->template->set_var('lang_clearform', lang('Clear Form'));
		$GLOBALS['phpgw']->template->set_var('lang_assignedto',lang('Assign to'));
		$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Submit'));
		$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

		$GLOBALS['phpgw']->template->set_var('lang_billable_hours',lang('Billable hours'));
		$GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable hours rate'));

		$GLOBALS['phpgw']->template->set_var('row_off', $GLOBALS['phpgw_info']['theme']['row_off']);
		$GLOBALS['phpgw']->template->set_var('row_on', $GLOBALS['phpgw_info']['theme']['row_on']);
		$GLOBALS['phpgw']->template->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);

		$GLOBALS['phpgw']->template->set_var('value_details',$ticket['details']);
		$GLOBALS['phpgw']->template->set_var('value_subject',$ticket['details']);
		$GLOBALS['phpgw']->template->set_var('value_billable_hours',($ticket['billable_hours']?$ticket['billable_hours']:'0.00'));
		$GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',($ticket['billable_rate']?$ticket['billable_rate']:'0.00'));

		unset($s);
		$groups = CreateObject('phpgwapi.accounts');
		$group_list = array();
		$group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

		while(list($key,$entry) = each($group_list))
		{
			$GLOBALS['phpgw']->template->set_var('optionname', $entry['account_name']);
			$GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
			$GLOBALS['phpgw']->template->set_var('optionselected', $tag);
			$GLOBALS['phpgw']->template->parse('options_group','options_select',true);
		}

		$s = '<select name="ticket[category]">' . $GLOBALS['phpgw']->categories->formated_list('select','',$group,True) . '</select>';
		$GLOBALS['phpgw']->template->set_var('value_category',$s);

		unset($s);
		$accounts = $groups;
		$accounts->account_id = $group_id;
		$account_list = $accounts->get_list('accounts');
		while(list($key,$entry) = each($account_list))
		{
			$s .= '<option value="' . $entry['account_id'] . '" ' . $account_selected[$entry['account_lid']]
				. '>' . $entry['account_lid'] . '</option>';
		}
		$GLOBALS['phpgw']->template->set_var('value_assignedto','<select name="ticket[assignedto]">' . $s . '</select>');

		//$GLOBALS['phpgw']->template->set_var('tts_account_lid','0');
		//$GLOBALS['phpgw']->template->set_var('tts_account_name',lang('None'));

		// Choose the correct priority to display
		$prority_selected[$ticket['priority']] = ' selected';
		$priority_comment[1]  = ' - '.lang('Lowest');
		$priority_comment[5]  = ' - '.lang('Medium');
		$priority_comment[10] = ' - '.lang('Highest');
		for($i=1; $i<=10; $i++)
		{
			$priority_select .= '<option value="' . $i . '">' . $i . $priority_comment[$i] . '</option>';
		}
		$GLOBALS['phpgw']->template->set_var('value_priority','<select name="ticket[priority]">' . $priority_select . '</select>');

		$GLOBALS['phpgw']->template->set_var('tts_select_options','');
		$GLOBALS['phpgw']->template->set_var('tts_new_lstcategory','');
		$GLOBALS['phpgw']->template->set_var('tts_new_lstassignto','');

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	else
	{
	}
?>
