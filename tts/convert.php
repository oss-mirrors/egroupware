<?php
	/**************************************************************************\
	* eGroupWare - Trouble Ticket System                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/*
	 * This script is basically created to be used only once. It converts
	 * tickets from TTS to the new Tracker application, introduced
	 * in eGroupWare v1.3.
	 * When all tickets have been converted, they will remain in the database.
	 * There is no option to roll-back a conversion. If this needs to be
	 * done, a manual update should be made on the database:
	 *  UPDATE phpgw_tts_tickets SET ticket_converted = 'N' [WHERE...]
	 * Remember to remove the converted tickets from Tracker as well....
	 */

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	$GLOBALS['phpgw_info']['flags']['noheader'] = True;
	include('../header.inc.php');

	$_confirmation = '';
	if($_POST['submit'])
	{
		$_converted = TTSCONV_perform_conversion ($_POST);
		// if ($_converted < 0) then there's an error... ToDo
		$_confirmation = "<strong>$_converted</strong> " . lang('tickets have been converted');
		
	}
	TTSCONV_ticket_overview($_confirmation);

		
function TTSCONV_ticket_overview ($_confirmation = '') {
/*
 * Display the amount of tickets that can be converted and give the user
 * the option to strart the conversions
 */

	$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].' - '.lang("convert tickets to tracker");
	$GLOBALS['phpgw']->common->phpgw_header();

	$GLOBALS['phpgw']->template->set_file('convert','convert.tpl');

	$GLOBALS['phpgw']->db->query(
			  'SELECT COUNT(*) AS cnt '
			. 'FROM   phpgw_tts_tickets '
			. "WHERE  ticket_status <> 'X' " ,__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$open_tickets = $GLOBALS['phpgw']->db->f('cnt');

	$GLOBALS['phpgw']->db->query(
			  'SELECT COUNT(*) AS cnt '
			. 'FROM   phpgw_tts_tickets '
			. "WHERE  ticket_status <> 'X' "
			. "AND    ticket_converted = 'Y' ",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$open_conv_tickets = $GLOBALS['phpgw']->db->f('cnt');

	$GLOBALS['phpgw']->db->query(
			  'SELECT COUNT(*) AS cnt '
			. 'FROM   phpgw_tts_tickets '
			. "WHERE  ticket_status = 'X' ",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$closed_tickets = $GLOBALS['phpgw']->db->f('cnt');

	$GLOBALS['phpgw']->db->query(
			  'SELECT COUNT(*) AS cnt '
			. 'FROM   phpgw_tts_tickets '
			. "WHERE  ticket_status = 'X' "
			. "AND    ticket_converted = 'Y' " ,__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$closed_conv_tickets = $GLOBALS['phpgw']->db->f('cnt');

	$GLOBALS['phpgw']->template->set_block('convert','form');

	$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/convert.php'));

	$GLOBALS['phpgw']->template->set_var('confirmation', $_confirmation);
	$GLOBALS['phpgw']->template->set_var('lang_total', lang('ticket count'));
	$GLOBALS['phpgw']->template->set_var('lang_converted', lang('converted count'));
	$GLOBALS['phpgw']->template->set_var('lang_open_cnt', lang('open'));
	$GLOBALS['phpgw']->template->set_var('lang_close_cnt', lang('closed'));
	$GLOBALS['phpgw']->template->set_var('lang_conv_open', lang('convert all open tickets')
					. ' ('.($open_tickets - $open_conv_tickets).')');
	$GLOBALS['phpgw']->template->set_var('lang_conv_closed', lang('convert all closed tickets')
					. ' ('.($closed_tickets - $closed_conv_tickets).')');

	$GLOBALS['phpgw']->template->set_var('open_cnt', $open_tickets);
	$GLOBALS['phpgw']->template->set_var('open_cnv_cnt', $open_conv_tickets);
	$GLOBALS['phpgw']->template->set_var('close_cnt', $closed_tickets);
	$GLOBALS['phpgw']->template->set_var('close_cnv_cnt', $closed_conv_tickets);

	// Disable the chekboxes if nothing needs to be convered
	if ($open_tickets == $open_conv_tickets)
	{
		$GLOBALS['phpgw']->template->set_var('convopen_disa', 'disabled');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('convopen_disa', '');
	}
	if ($closed_tickets == $closed_conv_tickets)
	{
		$GLOBALS['phpgw']->template->set_var('convclose_disa', 'disabled');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('convclose_disa', '');
	}
	

	if (($open_tickets == $open_conv_tickets) && ($closed_tickets == $closed_conv_tickets))
	{
		// If all tickets were already converted, there's nothing left to do,
		// so just print a message.
		$GLOBALS['phpgw']->template->set_var('btn_or_msg', lang('all tickets are already converted to Tracker'));
	}
	else
	{
		$_btn = '<input type="submit" name="submit" value="'.lang('Save').'">&nbsp;'
			.'<input type="submit" name="cancel" value="'.lang('Cancel').'">';
		$GLOBALS['phpgw']->template->set_var('btn_or_msg', $_btn);

		// Select Global- and Tracker- categories
		$tr_catlist = clone($GLOBALS['phpgw']->db);
		$GLOBALS['phpgw']->db->query('SELECT cat_id, cat_name FROM egw_categories '
				. "WHERE cat_appname = 'tracker' and cat_parent = 0",__LINE__,__FILE__);
		if ($GLOBALS['phpgw']->db->num_rows() == 0)
		{
			// Overwrite (and thus, abuse :-S) the Confirmation
			$GLOBALS['phpgw']->template->set_var('confirmation', "<font color='red' size='+1'><b>".lang('No Trackers found, aborting').'</b></font>');
		}
		else
		{
			while ($GLOBALS['phpgw']->db->next_record()) {
				$tr_catlist->query('SELECT cat_id, cat_name FROM egw_categories '
						. "WHERE cat_appname = 'tracker' and cat_parent = "
						. $GLOBALS['phpgw']->db->f('cat_id') . ' ',__LINE__,__FILE__);
				if ($tr_catlist->num_rows() > 0)
				{
					$Tracker_groups[$GLOBALS['phpgw']->db->f('cat_id')] = $GLOBALS['phpgw']->db->f('cat_name');
					while ($tr_catlist->next_record())
					{
						$Tracker_cats[$GLOBALS['phpgw']->db->f('cat_id')][$tr_catlist->f('cat_id')] = $tr_catlist->f('cat_name');
					}
				}
			}

			$GLOBALS['phpgw']->db->query('SELECT DISTINCT(ticket_category) FROM phpgw_tts_tickets',__LINE__,__FILE__);
			$GLOBALS['phpgw']->template->set_var('lang_catconv', lang('select which categories should be used in Tracker for the TTS categories'));
			$_selects = '';
			$_rowclasses = array('row_on', 'row_off');
			$_switch = 1;
			while ($GLOBALS['phpgw']->db->next_record())
			{
				$_selects .= '<tr class="'.$_rowclasses[$_switch].'"><td>'
					. $GLOBALS['phpgw']->categories->id2name($GLOBALS['phpgw']->db->f('ticket_category'))
					. '</td><td align="center"> =&gt; </td>';
				$_selects .= '<td><select name="cat_conv['.$GLOBALS['phpgw']->db->f('ticket_category').']">';
				foreach ($Tracker_groups as $_groupID => $_group)
				{
					$_selects .= '<optgroup label="'.$_group.'"><br />';
					foreach ($Tracker_cats[$_groupID] as $_catID => $_cat)
					{
						$_selects .= '<option value="'.$_groupID.':'.$_catID.'">'.$_cat.'</option><br />';
					}
					$_selects .= '</optgroup><br />';
				}
				$_selects .= '</select></td></tr>';
				$_switch = 1 - $_switch;
			}
			$GLOBALS['phpgw']->template->set_var('convert_categories', $_selects);
		}
	}

	$GLOBALS['phpgw']->template->pfp('out','form');
	$GLOBALS['phpgw']->common->phpgw_footer();
}

function TTSCONV_perform_conversion ($_data)
{
	$StatesResolutions  = TTSCONV_states_resolution();
	$CategoryConversion = $_data['cat_conv'];


	$ticketlist = clone($GLOBALS['phpgw']->db);

	$_converted = 0;
	if (!$_data['conv_open'] && !$_data['conv_closed'])
	{
		return $_converted;				// Nothing to do
	} elseif (!$_data['conv_open']) {			// Not open...
		$_select = "WHERE  ticket_status = 'X' ";	// ... so only closed
	} elseif (!$_data['conv_closed']) {			// Not closed...
		$_select = "WHERE  ticket_status <> 'X' ";	// ... so only open
	} else {
		$_select = ''; 					// Select all
	}

	$ticketlist->query(
			  'SELECT * '
			. 'FROM   phpgw_tts_tickets '
			. $_select ,__LINE__,__FILE__);
			
	if (($selected_tickets = $ticketlist->num_rows()) == 0)
	{
		// Hmmm.... should be an error actually; is that useful? ToDo
		return $_converted;
	}

	while ($ticketlist->next_record())
	{
		$_fields = array(
			 'tr_summary'        => substr ($ticketlist->f('ticket_subject'),0,80)
			,'tr_status'         => $StatesResolutions[$ticketlist->f('ticket_state')][0]
			,'tr_created'        => mktime (1, 0, 1, 1, 1, 1970)
			,'tr_description'    => $ticketlist->f('ticket_details')
			,'tr_private'        => 0  // Default to not private (not used in TTS)
			,'tr_budget'         => (ticket_billable_hours*ticket_billable_rate)
			,'tr_creator'        => intval($ticketlist->f('ticket_owner'))
			,'tr_priority'       => (9-(2*intval($ticketlist->f('ticket_priority'))))
			,'tr_resolution'     => $StatesResolutions[$ticketlist->f('ticket_state')][1]
		);

		if (!is_null($ticketlist->f('ticket_assignedto')))
		{
			$_fields['tr_assigned'] = intval($ticketlist->f('ticket_assignedto'));
		}

		if (!is_null($ticketlist->f('ticket_category')))
		{
			if (isset($CategoryConversion[$ticketlist->f('ticket_category')]))
			{
				list ($_fields['tr_tracker'], $_fields['cat_id']) = explode (':', $CategoryConversion[$ticketlist->f('ticket_category')]);
			}
			else
			{
				$_fields['cat_id'] = $ticketlist->f('ticket_category');
				$_fields['tr_tracker'] = $_fields['cat_id'];
			}
		}

		$GLOBALS['phpgw']->db->insert('egw_tracker'
			, $_fields
			, false
			, __LINE__
			, __FILE__
			, 'tracker');


		$tracker_id = $GLOBALS['phpgw']->db->get_last_insert_id('egw_tracker','tr_id');

		TTSCONV_ticket_history ($ticketlist->f('ticket_id'), $tracker_id);

		$GLOBALS['phpgw']->db->query('UPDATE phpgw_tts_tickets '
				. "SET ticket_converted = 'Y' "
				. ", tracker_id = $tracker_id "
				. 'WHERE ticket_id = ' . $ticketlist->f('ticket_id'),__LINE__,__FILE__);

		$_converted++;
	}
	return $_converted;
}

function TTSCONV_ticket_history ($tts_id, $tracker_id)
{
	$ticketdata = clone($GLOBALS['phpgw']->db);

	$ticketdata->query(
			  'SELECT * '
			. 'FROM   egw_history_log '
			. "WHERE  history_appname = 'tts' "
			. "AND    history_record_id = $tts_id");
			
	if ($ticketdata->num_rows() == 0)
	{
		return;
	}

	while ($ticketdata->next_record())
	{
		if ($ticketdata->f('history_status') == 'O') {
			$GLOBALS['phpgw']->db->update('egw_tracker'
				, array('tr_created'   => TTSCONV_timeval2timestamp($ticketdata->f('history_timestamp')))
				, array('tr_id' => $tracker_id)
				, __LINE__
				, __FILE__
				, 'tracker');
		} elseif ($ticketdata->f('history_status') == 'X') {
			$GLOBALS['phpgw']->db->update('egw_tracker'
				, array( 'tr_closed'   => TTSCONV_timeval2timestamp($ticketdata->f('history_timestamp'))
					,'tr_modified' => TTSCONV_timeval2timestamp($ticketdata->f('history_timestamp'))
					,'tr_modifier' => $ticketdata->f('history_owner'))
				, array('tr_id' => $tracker_id)
				, __LINE__
				, __FILE__
				, 'tracker');
		} elseif ($ticketdata->f('history_status') == 'C') {
			$_fields = array (
 				  'tr_id'         => $tracker_id
				, 'reply_creator' => $ticketdata->f('history_owner')
				, 'reply_created' => TTSCONV_timeval2timestamp($ticketdata->f('history_timestamp'))
				, 'reply_message' => $ticketdata->f('history_new_value')
			);
			$GLOBALS['phpgw']->db->insert('egw_tracker_replies'
				, $_fields
				, false
				, __LINE__
				, __FILE__
				, 'tracker');
//		} else
			// All other changes are ignored, since they're not
			// compatible with Tracker.
		}
	}
}

function TTSCONV_timeval2timestamp ($val)
{
	list ($dt, $tm) = explode (' ', $val);
	list ($y, $m, $d) = explode ('-', $dt);
	list ($H, $M, $S) = explode (':', $tm);
	return (mktime ($H, $M, $S, $m, $d, $y));
}

function TTSCONV_states_resolution ()
/*
 * Create an array that will be used in an attempt to translate States and
 * Resolutions from TTS to Tracker.
 */
{
	$StatesResolutions = array ();
	$GLOBALS['phpgw']->db->query('SELECT * FROM phpgw_tts_states ',__LINE__,__FILE__);
	while ($GLOBALS['phpgw']->db->next_record())
	{
		$_idx = $GLOBALS['phpgw']->db->f('state_id');
		$StatesResolutions[$_idx] = array ();

		if ($GLOBALS['phpgw']->db->f('state_open') == 'X')
		{
			$StatesResolutions[$_idx][0] = -101; //State is closed
		}
		else
		{
			$StatesResolutions[$_idx][0] = -100; //State is opened
		}

		// Check for the default state names in TTS and try
		// to translate them.
		if ($GLOBALS['phpgw']->db->f('state_name') == 'ACCEPTED')
		{
			$StatesResolutions[$_idx][1] = 'a';
		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'REOPENED')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'RESOLVED')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'VERIFIED')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'CLOSED')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'TOVALIDATE')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
//		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'NEEDSWORK')
//		{
//			$StatesResolutions[$_idx][1] = '';
//		}
		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'INVALID')
		{
			$StatesResolutions[$_idx][1] = 'i';
		}
		elseif ($GLOBALS['phpgw']->db->f('state_name') == 'DUPLICATE')
		{
			$StatesResolutions[$_idx][1] = 'd';
		}
		else
		{
			$StatesResolutions[$_idx][1] = ''; // Default: None
		}
	}
	return ($StatesResolutions);
}
?>
