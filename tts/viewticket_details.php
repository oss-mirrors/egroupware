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

	$GLOBALS['phpgw_info']['flags'] = array(
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True,
		'currentapp'              => 'tts'
	);
	$submit = $HTTP_POST_VARS['submit'];

	if ($submit)
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
		$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	}
	include('../header.inc.php');

	$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');
	$GLOBALS['phpgw']->historylog->types = array(
		'R' => 'Re-opened',
		'X' => 'Closed',
		'O' => 'Opened',
		'A' => 'Re-assigned',
		'P' => 'Priority changed',
		'T' => 'Category changed',
		'S' => 'Subject changed',
		'B' => 'Billing rate',
		'H' => 'Billing hours'
	);

	if (! $submit)
	{
		// Have they viewed this ticket before ?
		$GLOBALS['phpgw']->db->query("select count(*) from phpgw_tts_views where view_id='$ticket_id' "
				. "and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		if (! $GLOBALS['phpgw']->db->f(0))
		{
			$GLOBALS['phpgw']->db->query("insert into phpgw_tts_views values ('$ticket_id','"
				. $GLOBALS['phpgw_info']['user']['account_id'] . "','" . time() . "')",__LINE__,__FILE__);
		}

		// select the ticket that you selected
		$GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$ticket['billable_hours'] = $GLOBALS['phpgw']->db->f('ticket_billable_hours');
		$ticket['billable_rate']  = $GLOBALS['phpgw']->db->f('ticket_billable_rate');
		$ticket['assignedto']     = $GLOBALS['phpgw']->db->f('ticket_assignedto');
		$ticket['category']       = $GLOBALS['phpgw']->db->f('ticket_category');
		$ticket['details']        = $GLOBALS['phpgw']->db->f('ticket_details');
		$ticket['subject']        = $GLOBALS['phpgw']->db->f('ticket_subject');
		$ticket['priority']       = $GLOBALS['phpgw']->db->f('ticket_priority');
		$ticket['owner']          = $GLOBALS['phpgw']->db->f('ticket_owner');

		$GLOBALS['phpgw']->template->set_file('viewticket','viewticket_details.tpl');
		$GLOBALS['phpgw']->template->set_block('viewticket','options_select');
		$GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row');
		$GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row_empty');
		$GLOBALS['phpgw']->template->set_block('viewticket','row_history');
		$GLOBALS['phpgw']->template->set_block('viewticket','row_history_empty');
		$GLOBALS['phpgw']->template->set_block('viewticket','form');

		$messages = $GLOBALS['phpgw']->session->appsession('messages','tts');
		if ($messages)
		{
			$GLOBALS['phpgw']->template->set_var('messages',$messages);
			$GLOBALS['phpgw']->session->appsession('messages','tts','');
		}

		if ($GLOBALS['phpgw']->db->f('ticket_status') == 'C')
		{
			$GLOBALS['phpgw']->template->set_var('t_status','FIX ME! time closed ' . __LINE__); // $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed')));
		}
		else
		{
			$GLOBALS['phpgw']->template->set_var('t_status', lang('In progress'));
		}

		// Choose the correct priority to display
		$priority_selected[$ticket['priority']] = ' selected';
		$priority_comment[1]=' - '.lang('Lowest'); 
		$priority_comment[5]=' - '.lang('Medium'); 
		$priority_comment[10]=' - '.lang('Highest'); 

		for ($i=1; $i<=10; $i++)
		{
			$GLOBALS['phpgw']->template->set_var('optionname', $i.$priority_comment[$i]);
			$GLOBALS['phpgw']->template->set_var('optionvalue', $i);
			$GLOBALS['phpgw']->template->set_var('optionselected', $priority_selected[$i]);
			$GLOBALS['phpgw']->template->parse('options_priority','options_select',true);
		}

		// assigned to
		$accounts = CreateObject('phpgwapi.accounts');
		$account_list = $accounts->get_list('accounts');
		$GLOBALS['phpgw']->template->set_var('optionname',lang('None'));
		$GLOBALS['phpgw']->template->set_var('optionvalue','0');
		$GLOBALS['phpgw']->template->set_var('optionselected','');
		$GLOBALS['phpgw']->template->parse('options_assignedto','options_select',true);
		while (list($key,$entry) = each($account_list))
		{
			$tag = '';
			if ($entry['account_id'] == $ticket['assignedto'])
			{
				$tag = 'selected';
			}
			$GLOBALS['phpgw']->template->set_var('optionname', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
			$GLOBALS['phpgw']->template->set_var('optionselected', $tag);
			$GLOBALS['phpgw']->template->parse('options_assignedto','options_select',True);
		}

		// Figure out when it was opened and last closed
		$history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('X','O'),'','',$ticket_id);
		while (is_array($history_array) && list(,$value) = each($history_array))
		{
			if ($value['status'] == 'O')
			{
				$ticket['opened'] = $GLOBALS['phpgw']->common->show_date($value['datetime']);
			}

			if ($value['status'] == 'X')
			{
				$ticket['closed'] = $GLOBALS['phpgw']->common->show_date($value['datetime']);
			}
		}

		// group
/*
		$groups = $accounts;
		$group_list = $groups->get_list('groups');
		while (list($key,$entry) = each($group_list))
		{
			$tag = '';
			if ($entry['account_lid'] == $ticket['groups'])
			{
				$tag = 'selected';
			}
			$GLOBALS['phpgw']->template->set_var('optionname', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
			$GLOBALS['phpgw']->template->set_var('optionselected', $tag);
			$GLOBALS['phpgw']->template->parse('options_group','options_select',true);
		}
*/
		$GLOBALS['phpgw']->template->set_var('options_category',$GLOBALS['phpgw']->categories->formated_list('select','',$ticket['category'],True));

		$ticket_status[$ticket['status']] = ' selected';
		$s = '<option value="O"' . $ticket_status['O'] . '>' . lang('Open') . '</option>';
		$s .= '<option value="X"' . $ticket_status['X'] . '>' . lang('Closed') . '</option>';

		$GLOBALS['phpgw']->template->set_var('options_status',$s);
		$GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));

		/**************************************************************\
		* Display additional notes                                     *
		\**************************************************************/
		$history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'','',$ticket_id);
		while (is_array($history_array) && list(,$value) = each($history_array))
		{
			$GLOBALS['phpgw']->template->set_var('lang_date',lang('Date'));
			$GLOBALS['phpgw']->template->set_var('lang_user',lang('User'));

			$GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
			$GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

			$GLOBALS['phpgw']->template->set_var('value_note',nl2br(stripslashes($value['new_value'])));
			$GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row',True);
		}

		if (! count($history_array))
		{
			$GLOBALS['phpgw']->template->set_var('lang_no_additional_notes',lang('No additional notes'));
			$GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row_empty',True);
		}

		/**************************************************************\
		* Display record history                                       *
		\**************************************************************/
		$GLOBALS['phpgw']->template->set_var('lang_user',lang('User'));
		$GLOBALS['phpgw']->template->set_var('lang_date',lang('Date'));
		$GLOBALS['phpgw']->template->set_var('lang_action',lang('Action'));
		$GLOBALS['phpgw']->template->set_var('lang_new_value',lang('New Value'));

		$history_array = $GLOBALS['phpgw']->historylog->return_array(array('C','O'),array(),'','',$ticket_id);
		while (is_array($history_array) && list(,$value) = each($history_array))
		{
			$GLOBALS['phpgw']->nextmatchs->template_alternate_row_color($GLOBALS['phpgw']->template);

			$GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
			$GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

			switch ($value['status'])
			{
				case 'R': $type = lang('Re-opened'); break;
				case 'X': $type = lang('Closed');    break;
				case 'O': $type = lang('Opened');    break;
				case 'A': $type = lang('Re-assigned'); break;
				case 'P': $type = lang('Priority changed'); break;
				case 'T': $type = lang('Category changed'); break;
				case 'S': $type = lang('Subject changed'); break;
				case 'H': $type = lang('Billable hours changed'); break;
				case 'B': $type = lang('Billable rate changed'); break;
				default: break;
			}

			$GLOBALS['phpgw']->template->set_var('value_action',($type?$type:'&nbsp;'));
			unset($type);

			if ($value['status'] == 'A')
			{
				if (! $value['new_value'])
				{
					$GLOBALS['phpgw']->template->set_var('value_new_value',lang('None'));
				}
				else
				{
					$GLOBALS['phpgw']->template->set_var('value_new_value',$GLOBALS['phpgw']->accounts->id2name($value['new_value']));
				}
			}
			else if ($value['status'] == 'T')
			{
				$GLOBALS['phpgw']->template->set_var('value_new_value',$GLOBALS['phpgw']->categories->id2name($value['new_value']));
			}
			else if ($value['status'] != 'O' && $value['new_value'])
			{
				$GLOBALS['phpgw']->template->set_var('value_new_value',$value['new_value']);
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('value_new_value','&nbsp;');
			}

			$GLOBALS['phpgw']->template->fp('rows_history','row_history',True);
		}

		if (! count($history_array))
		{
			$GLOBALS['phpgw']->template->set_var('lang_no_history',lang('No history for this record'));
			$GLOBALS['phpgw']->template->fp('rows_history','row_history_empty',True);
		}

		$GLOBALS['phpgw']->template->set_var('lang_update',lang('Update'));

//		$phpgw->template->set_var('additonal_details_rows',$s);

		$GLOBALS['phpgw']->template->set_var('viewticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php'));
		$GLOBALS['phpgw']->template->set_var('ticket_id', $ticket_id);

		$GLOBALS['phpgw']->template->set_var('lang_assignedfrom', lang('Assigned from'));
		$GLOBALS['phpgw']->template->set_var('value_owner',$GLOBALS['phpgw']->accounts->id2name($ticket['owner']));

		$GLOBALS['phpgw']->template->set_var('row_off', $GLOBALS['phpgw_info']['theme']['row_off']);
		$GLOBALS['phpgw']->template->set_var('row_on', $GLOBALS['phpgw_info']['theme']['row_on']);
		$GLOBALS['phpgw']->template->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);

		$GLOBALS['phpgw']->template->set_var('lang_viewjobdetails', lang('View Job Detail'));

		$GLOBALS['phpgw']->template->set_var('lang_opendate', lang('Open Date'));
		$GLOBALS['phpgw']->template->set_var('value_opendate',$ticket['opened']);

		$GLOBALS['phpgw']->template->set_var('lang_priority', lang('Priority'));
		$GLOBALS['phpgw']->template->set_var('value_priority',$ticket['priority']);

		$GLOBALS['phpgw']->template->set_var('lang_group', lang('Group'));
		$GLOBALS['phpgw']->template->set_var('value_group',$GLOBALS['phpgw']->accounts->id2name($phpgw->db->f('ticket_group')));

		$GLOBALS['phpgw']->template->set_var('lang_billable_hours',lang('Billable hours'));
		$GLOBALS['phpgw']->template->set_var('value_billable_hours',$ticket['billable_hours']);

		$GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable rate'));
		$GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',$ticket['billable_rate']);

		$GLOBALS['phpgw']->template->set_var('lang_billable_hours_total',lang('Total billable'));
		$GLOBALS['phpgw']->template->set_var('value_billable_hours_total',sprintf('%01.2f',($ticket['billable_hours'] * $ticket['billable_rate'])));

		$GLOBALS['phpgw']->template->set_var('lang_assignedto',lang('Assigned to'));
		if ($ticket['assignedto'])
		{
			$assignedto = $GLOBALS['phpgw']->accounts->id2name($ticket['assignedto']);
		}
		else
		{
			$assignedto = lang('None');
		}
		$GLOBALS['phpgw']->template->set_var('value_assignedto',$assignedto);

		$GLOBALS['phpgw']->template->set_var('lang_subject', lang('Subject'));

		$GLOBALS['phpgw']->template->set_var('lang_details', lang('Details'));
		$GLOBALS['phpgw']->template->set_var('value_details', nl2br(stripslashes($ticket['details'])));

		$GLOBALS['phpgw']->template->set_var('value_subject', stripslashes($ticket['subject']));

		$GLOBALS['phpgw']->template->set_var('lang_additional_notes',lang('Additional notes'));
		$GLOBALS['phpgw']->template->set_var('lang_ok', lang('OK'));

		$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
		$GLOBALS['phpgw']->template->set_var('value_category',$GLOBALS['phpgw']->categories->id2name($ticket['category']));

		$GLOBALS['phpgw']->template->set_var('options_select','');

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	else
	{
		// DB Content is fresher than http posted value.
		$GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$oldassigned = $GLOBALS['phpgw']->db->f('ticket_assignedto');
		$oldpriority = $GLOBALS['phpgw']->db->f('ticket_priority');
		$oldcategory = $GLOBALS['phpgw']->db->f('ticket_category');
		$old_status  = $GLOBALS['phpgw']->db->f('ticket_status');

		$GLOBALS['phpgw']->db->transaction_begin();

		/*
		**	phpgw_tts_append.append_type - Defs
		**	R - Reopen ticket
		** X - Ticket closed
		** O - Ticket opened
		** C - Comment appended
		** A - Ticket assignment
		** P - Priority change
		** T - Category change
		** S - Subject change
		** B - Billing rate
		** H - Billing hours
		*/

		if ($old_status != $ticket['status'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->historylog->add($ticket['status'],$ticket_id,'');

			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_status='"
				. $ticket['status'] . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
		}

		if ($oldassigned != $ticket['assignedto'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_assignedto='" . $ticket['assignedto']
				. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->historylog->add('A',$ticket_id,$ticket['assignedto']);
		}

		if ($oldpriority != $ticket['priority'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_priority='" . $ticket['priority']
				. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->historylog->add('P',$ticket_id,$ticket['priority']);
		}

		if ($oldcategory != $ticket['category'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_category='" . $ticket['category']
				. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->historylog->add('T',$ticket_id,$ticket['category']);
		}

		if ($old_billable_hours != $ticket['billable_hours'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_hours='" . $ticket['billable_hours']
				. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->historylog->add('H',$ticket_id,$ticket['billable_hours']);
		}

		if ($old_billable_rate != $ticket['billable_rate'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_rate='" . $ticket['billable_rate']
				. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
			$GLOBALS['phpgw']->historylog->add('B',$ticket_id,$ticket['billable_rate']);
		}

		if ($ticket['note'])
		{
			$fields_updated = True;
			$GLOBALS['phpgw']->historylog->add('C',$ticket_id,$ticket['note']);

			// Do this before we go into mail_ticket()
			$GLOBALS['phpgw']->db->transaction_commit();

			if ($GLOBALS['phpgw_info']['server']['tts_mailticket'])
			{
				mail_ticket($ticket_id);
			}
		}
		else
		{
			// Only do our commit once
			$GLOBALS['phpgw']->db->transaction_commit();
		}

		if ($fields_updated)
		{
			$GLOBALS['phpgw']->session->appsession('messages','tts',lang('Ticket has been updated'));
		}

		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/tts/viewticket_details.php','ticket_id=' . $ticket_id));
	}
?>
