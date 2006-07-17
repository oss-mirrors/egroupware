<?php
  /**************************************************************************\
  * eGroupWare - TTS                                                         *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	require_once (EGW_INCLUDE_ROOT.'/tts/inc/acl_funcs.inc.php');
	require_once (EGW_INCLUDE_ROOT.'/tts/inc/prio.inc.php');

	$d1 = strtolower(substr($GLOBALS['phpgw_info']['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo 'Failed attempt to break in via an old Security Hole!<br>'."\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	if ($GLOBALS['phpgw_info']['user']['apps']['tts']
		&& $GLOBALS['phpgw_info']['user']['preferences']['tts']['mainscreen_show_new_updated'])
	{
		$GLOBALS['phpgw']->translation->add_app('tts');

		$db2 = clone($GLOBALS['phpgw']->db);
		$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

		// this will be an user option
/* Outcommented for now; this would only list my own tickets (as owner or assignee).
 * We'll use the list where all tickets I'm allowed to see will be shown.
 * A preference for this can be added later, OvE
 *
		$filtermethod="where ticket_status='O' and (ticket_assignedto='".$GLOBALS['phpgw_info']['user']['account_id']."' "
				. "or ticket_owner='".$GLOBALS['phpgw_info']['user']['account_id']."') ";
 *
 * The filtermethod below shows the same as above, plus all tickets that have not yet been assigned.
 * ACL will be checked futher on, OvE
 */
		$filtermethod="where ticket_status='O' and ((ticket_assignedto='".$GLOBALS['phpgw_info']['user']['account_id']."' OR ticket_assignedto=0)"
				. "or (ticket_owner='".$GLOBALS['phpgw_info']['user']['account_id']."')) ";
		$sortmethod = "ORDER BY ticket_priority ASC, CASE WHEN ticket_due IS NOT NULL THEN ticket_due ELSE '2100-01-01' END ASC, ticket_id ASC";

		$GLOBALS['phpgw']->db->query('select ticket_id, ticket_category, ticket_priority,'.
			' ticket_assignedto, ticket_owner, ticket_group, ticket_subject, ticket_due ' .
			' from phpgw_tts_tickets ' . $filtermethod . ' ' . $sortmethod,__LINE__,__FILE__);

		$tmp_app_tpl = $GLOBALS['phpgw']->common->get_tpl_dir('tts');
		$p = CreateObject('phpgwapi.Template',$tmp_app_tpl);
		$p->set_file('index','hook_home.tpl');

		$p->set_block('index', 'tts_list', 'tts_list');
		$p->set_block('index', 'tts_row', 'tts_row');
		$p->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
		$p->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');
		$p->set_var(
			Array(
				'tts_head_id'			=> lang('Ticket #'),
				'tts_head_subject'		=> lang('Subject'),
				'tts_head_duedate'		=> lang('Due Date'),
				'tts_head_openedby'		=> lang('Opened by')
			)
		);
		while ($GLOBALS['phpgw']->db->next_record())
		{

			if (!check_read_right($GLOBALS['phpgw']->db->f('ticket_owner')
			  , $GLOBALS['phpgw']->db->f('ticket_assignedto')
			  , $GLOBALS['phpgw']->db->f('ticket_group'))) {
				continue;
			}

			$p->set_var('tts_col_status','');

			/* We now try to find a good bg-color:	    -- MSc
			 * If the due date is in the past, color it red
			 * If the due date is in the future, color it according to Prio */
			$priority = $GLOBALS['phpgw']->db->f('ticket_priority');
			$tdu = $GLOBALS['phpgw']->db->f('ticket_due');
			if ($tdu && $tdu > 0 && $tdu < time()) {  # it's DUE!
			    $tr_color = $GLOBALS['phpgw_info']['theme']['due'];
			} else {
			    # as we are using prios from 1..5, let's multiply prio by 2
			    $tr_color = $GLOBALS['phpgw_info']['theme']['bg'.sprintf('%02s',(5-$priority)*2)];
			}

			$db2->query("select count(*) from phpgw_tts_views where view_id='" . $GLOBALS['phpgw']->db->f('ticket_id')
				. "' and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
			$db2->next_record();
			if($db2->f(0))
			{
				$t_read=1;
			}
			else
			{
				$t_read=0;
			}
			$p->set_var('tts_row_color', $tr_color );
			$p->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php','ticket_id=' . $GLOBALS['phpgw']->db->f('ticket_id')));

			$p->set_var('tts_t_id',$GLOBALS['phpgw']->db->f('ticket_id') );

			if (!$t_read==1)
			{
				$p->fp('tts_ticket_id', 'tts_ticket_id_unread', false );
			}
			else
			{
				$p->fp('tts_ticket_id', 'tts_ticket_id_read', false );
			}

			$p->set_var('tts_t_user', $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner')));

			$p->set_var('tts_t_duedate', substr($GLOBALS['phpgw']->db->f('ticket_due'), 0, 16));

			// cope with old, wrongly saved entries, stripslashes would remove single backslashes too
			$subject = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'), $GLOBALS['phpgw']->db->f('ticket_subject'));
			
			// (erics, 22.05.2006 - commented out clipping of subject line.
			//if (strlen($subject) > 25) {
			//    $subject = substr($subject,0,23) . '...';
			//}

			$p->set_var('tts_t_subject', $subject);

			$p->fp('rows','tts_row',true);
		}

		$extra_data = '<td>'."\n".$p->fp('out','tts_list').'</td>'."\n";

		$portalbox = CreateObject('phpgwapi.listbox',
			array(
				'title'     => lang('Trouble Ticket System'),
				'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'width'     => '100%',
				'outerborderwidth' => '0',
				'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler')
			)
		);

		$app_id = $GLOBALS['phpgw']->applications->name2id('tts');
		$GLOBALS['portal_order'][] = $app_id;
		$var = array(
			'up'       => array('url' => '/set_box.php', 'app' => $app_id),
			'down'     => array('url' => '/set_box.php', 'app' => $app_id),
			'close'    => array('url' => '/set_box.php', 'app' => $app_id),
			'question' => array('url' => '/set_box.php', 'app' => $app_id),
			'edit'     => array('url' => '/set_box.php', 'app' => $app_id)
		);

		while(list($key,$value) = each($var))
		{
			$portalbox->set_controls($key,$value);
		}
		$portalbox->data = array();
		echo "\n".'<!-- Begin TTS New/Updated -->'."\n".$portalbox->draw($extra_data)."\n".'<!-- End TTS New/Updated -->'."\n";
	}
?>
