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

	$GLOBALS['phpgw_info']['flags'] = array('currentapp' => 'tts');
	$submit = $HTTP_POST_VARS['submit'];

	if ($submit)
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
		$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	}
	include('../header.inc.php');

	if (!$submit)
	{
		// select the ticket that you selected
		$GLOBALS['phpgw']->db->query("select t_id,t_category,t_detail,t_priority,t_user,t_assignedto,"
			. "t_timestamp_opened, t_timestamp_closed, t_subject, t_watchers from ticket where t_id='$ticketid'");
		$GLOBALS['phpgw']->db->next_record();

		$lstAssignedto = $GLOBALS['phpgw']->db->f("t_assignedto");
		$lstCategory   = $GLOBALS['phpgw']->db->f("t_category");

		// mark as read.
		$temp_watchers=explode(':',$GLOBALS['phpgw']->db->f('t_watchers'));
		if (!(in_array( $GLOBALS['phpgw_info']['user']['userid'], $temp_watchers)))
		{
			$temp_watchers[]=$GLOBALS['phpgw_info']['user']['userid'];
			$t_watchers=implode(":",$temp_watchers);
			// var_dump($t_watchers);
			$GLOBALS['phpgw']->db->query("UPDATE ticket set t_watchers='".$t_watchers."' where t_id=$ticketid");
		} 

		// Print the table
		$GLOBALS['phpgw']->template->set_file(array(
			'viewticket' => 'viewticket_details.tpl'
		));

		$GLOBALS['phpgw']->template->set_block('viewticket', 'tts_select_options','tts_select_options');

		$GLOBALS['phpgw']->template->set_unknowns('remove');

		if ($GLOBALS['phpgw']->db->f('t_timestamp_closed') > 0)
		{
			$GLOBALS['phpgw']->template->set_var('tts_t_status', $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed')));
		}
		else
		{
			$GLOBALS['phpgw']->template->set_var('tts_t_status', lang('in progress'));
		}

		// Choose the correct priority to display
		$priority_selected[$GLOBALS['phpgw']->db->f('t_priority')] = ' selected';
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

		// assigned to
		$accounts = CreateObject('phpgwapi.accounts');
		$account_list = $accounts->get_list('accounts');
		$GLOBALS['phpgw']->template->set_var('tts_optionname', lang('none'));
		$GLOBALS['phpgw']->template->set_var('tts_optionvalue', 'none' );
		$GLOBALS['phpgw']->template->set_var('tts_optionselected', '');
		$GLOBALS['phpgw']->template->parse('tts_assignedto_options','tts_select_options',true);
		while (list($key,$entry) = each($account_list))
		{
			$tag="";
			if ($entry['account_lid'] == "$lstAssignedto")
			{
				$tag = "selected";
			}
			$GLOBALS['phpgw']->template->set_var('tts_optionname', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionselected', $tag);
			$GLOBALS['phpgw']->template->parse('tts_assignedto_options','tts_select_options',true);
		}

		// group
		$groups = $accounts;
		$group_list = $groups->get_list('groups');
		while (list($key,$entry) = each($group_list))
		{
			$tag="";
			if ($entry['account_lid'] == "$lstCategory")
			{
				$tag = "selected";
			}
			$GLOBALS['phpgw']->template->set_var('tts_optionname', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionvalue', $entry['account_lid']);
			$GLOBALS['phpgw']->template->set_var('tts_optionselected', $tag);
			$GLOBALS['phpgw']->template->parse('tts_group_options','tts_select_options',true);
		}

		$details_string = stripslashes($GLOBALS['phpgw']->db->f('t_detail'));

		$GLOBALS['phpgw']->template->set_var('tts_viewticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php'));
		$GLOBALS['phpgw']->template->set_var('tts_t_id', $GLOBALS['phpgw']->db->f('t_id'));
		$GLOBALS['phpgw']->template->set_var('tts_t_user', $GLOBALS['phpgw']->db->f('t_user'));
		$GLOBALS['phpgw']->template->set_var('tts_th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);
		$GLOBALS['phpgw']->template->set_var('tts_lang_viewjobdetails', lang('View Job Detail'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_assignedfrom', lang('Assigned from'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_opendate', lang('Open Date'));
		$GLOBALS['phpgw']->template->set_var('tts_t_opendate', $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_opened')));
		$GLOBALS['phpgw']->template->set_var('tts_t_status', $GLOBALS['phpgw']->db->f('t_timestamp_closed')?$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed')):lang('In progress'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_closedate', lang('Close Date'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_priority', lang('Priority'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_group', lang('Group'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_assignedto', lang('Assigned to'));
		$GLOBALS['phpgw']->template->set_var('tts_hidden_detailstring', $GLOBALS['phpgw']->strip_html($details_string));
		$GLOBALS['phpgw']->template->set_var('tts_lang_subject', lang('Subject'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_details', lang('Details'));
		$GLOBALS['phpgw']->template->set_var('tts_t_subject', stripslashes($GLOBALS['phpgw']->db->f('t_subject')));
		$GLOBALS['phpgw']->template->set_var('tts_detailstring', stripslashes($details_string));
		$GLOBALS['phpgw']->template->set_var('tts_lang_additionalnotes', lang('Additional notes'));
		$GLOBALS['phpgw']->template->set_var('tts_lang_ok', lang('OK'));

		// change buttons from update/close to close/reopen if ticket is already closed
		if ($GLOBALS['phpgw']->db->f(7) > 0)
		{
			$GLOBALS['phpgw']->template->set_var('tts_leftradio', lang('Closed'));
			$GLOBALS['phpgw']->template->set_var('tts_rightradio', lang('ReOpen'));
			$GLOBALS['phpgw']->template->set_var('tts_leftradiovalue', 'letclosed');
			$GLOBALS['phpgw']->template->set_var('tts_rightradiovalue', 'reopen');
		}
		else
		{
			$GLOBALS['phpgw']->template->set_var('tts_leftradio', lang('Update'));
			$GLOBALS['phpgw']->template->set_var('tts_rightradio', lang('Close'));
			$GLOBALS['phpgw']->template->set_var('tts_leftradiovalue', 'update');
			$GLOBALS['phpgw']->template->set_var('tts_rightradiovalue', 'close');
		}

		$GLOBALS['phpgw']->template->set_var('tts_select_options','');

		$GLOBALS['phpgw']->template->pfp('out','viewticket');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	else
	{
		// DB Content is fresher than http posted value.
		$GLOBALS['phpgw']->db->query("select t_detail, t_assignedto, t_category, t_priority from ticket where t_id='".$t_id."'");
		$GLOBALS['phpgw']->db->next_record();
		$txtDetail = $GLOBALS['phpgw']->db->f('t_detail');
		$oldassigned = $GLOBALS['phpgw']->db->f('t_assignedto');
		$oldpriority = $GLOBALS['phpgw']->db->f('t_priority');
		$oldcategory = $GLOBALS['phpgw']->db->f('t_category');

		if ($optUpdateclose == 'letclosed')
		{
			# let ticket be closed
			# don't do any changes, ppl will have to reopen tickets to
			# submit additional infos
		}
		else
		{
			if ($optUpdateclose == 'reopen')
			{
				# reopen the ticket
				$GLOBALS['phpgw']->db->query("UPDATE ticket set t_timestamp_closed='0' WHERE t_id=$t_id");
				$txtReopen = '<b>'.lang('Ticket reopened').'</b><br>';
			}

			if ( $optUpdateclose == 'close' )
			{
				$txtClose = '<br /><b>'.lang('Ticket closed').'</b>';
				$GLOBALS['phpgw']->db->query("UPDATE ticket set t_timestamp_closed='" . time() . "' WHERE t_id=$t_id");
			}

			if ($oldassigned != $lstAssignedto)
			{
				$txtAssignTo = "<br /><b>".lang("Ticket assigned to x",$lstAssignedto)."</b>";
			}

			if ($oldpriority != $optPriority)
			{
				$txtPriority = '<br /><b>'.lang('Priority changed to x',$optPriority).'</b>';
			}

			if ($oldcategory != $lstCategory)
			{
				$txtCategory = '<br /><b>'.lang('Category changed to x',$lstCategory).'</b>';
			}

			$txtAdditional = $txtReopen.$txtAdditional.$txtAssignTo.$txtCategory.$txtPriority.$txtClose;

			if (! empty($txtAdditional))
			{
				$UserInfo = "<BR><i>\n" . $GLOBALS['phpgw_info']['user']['userid'] . ' - '
					. $GLOBALS['phpgw']->common->show_date(time()) . "</i><BR>\n";

				$txtDetail .= $UserInfo;
				$txtDetail .= nl2br($txtAdditional);
				$txtDetail .= '<hr>';

				# update the database if ticket content changed
				$GLOBALS['phpgw']->db->query("UPDATE ticket set t_category='$lstCategory',t_detail='".addslashes($txtDetail)."',t_priority='$optPriority',t_user='$lstAssignedfrom',t_assignedto='$lstAssignedto',t_watchers='".$GLOBALS['phpgw_info']["user"]["userid"]."' WHERE t_id=$t_id");

				if ($GLOBALS['phpgw_info']['server']['tts_mailticket'])
				{
					mail_ticket($t_id);
				}
			}
		}
		Header('Location: ' . $GLOBALS['phpgw']->link('/tts/index.php'));
	}
?>
