<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$d1 = strtolower(substr($GLOBALS['phpgw_info']['server']['app_inc'],0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_tpl = $GLOBALS['phpgw']->common->get_tpl_dir('tts');

	if ($GLOBALS['phpgw_info']["user"]["apps"]["tts"]
		&& $GLOBALS['phpgw_info']["user"]["preferences"]["tts"]["mainscreen_show_new_updated"])
	{
		echo "\n<!-- Begin TTS New/Updated -->\n";

		// this will be an user option
		$filtermethod="where ticket_status='O' and ticket_assignedto='".$GLOBALS['phpgw_info']['user']['account_id']."'";
		$sortmethod="order by ticket_priority desc";

		$GLOBALS['phpgw']->db->query("select ticket_id,ticket_category,ticket_priority,ticket_assignedto,"
			. "ticket_owner,ticket_subject from phpgw_tts_tickets $filtermethod $sortmethod",__LINE__,__FILE__);

		$p = CreateObject('phpgwapi.Template',$tmp_app_tpl);
		// echo PHPGW_APP_TPL;
		$p->set_file('index','hook_home.tpl');

		$p->set_block('index', 'tts_list', 'tts_list');
		$p->set_block('index', 'tts_row', 'tts_row');
		$p->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
		$p->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');

		while ($GLOBALS['phpgw']->db->next_record())
		{

			$p->set_var('tts_col_status',"");
			$priority=$GLOBALS['phpgw']->db->f('ticket_priority');
			switch ($priority)
			{
				case 1:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg01"]; break;
				case 2:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg02"]; break;
				case 3:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg03"]; break;
				case 4:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg04"]; break;
				case 5:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg05"]; break;
				case 6:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg06"]; break;
				case 7:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg07"]; break;
				case 8:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg08"]; break;
				case 9:  $tr_color = $GLOBALS['phpgw_info']["theme"]["bg09"]; break;
				case 10: $tr_color = $GLOBALS['phpgw_info']["theme"]["bg10"]; break;
				default: $tr_color = $GLOBALS['phpgw_info']["theme"]["bg_color"];
			}

			$t_watchers=explode(":",$GLOBALS['phpgw']->db->f("t_watchers"));
			if (in_array( $GLOBALS['phpgw_info']["user"]["userid"], $t_watchers))
			{
				$t_read=1;
			}
			else
			{
				$t_read=0;
			}
			$p->set_var('tts_row_color', $tr_color );
			$p->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link("/tts/viewticket_details.php","ticketid=" . $GLOBALS['phpgw']->db->f('ticket_id')));

			$p->set_var('tts_t_id',$GLOBALS['phpgw']->db->f('ticket_id') );

			if (!$t_read==1)
			{
				$p->fp('tts_ticket_id', 'tts_ticket_id_unread', false );
			}
			else
			{
				$p->fp('tts_ticket_id', 'tts_ticket_id_read', false );
			}

			$p->set_var('tts_t_user', $GLOBALS['phpgw']->db->f('ticket_user'));
			$p->set_var('tts_t_timestampopened', $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f("t_timestamp_opened")));
			$p->set_var('tts_t_subject', $GLOBALS['phpgw']->db->f('ticket_subject'));

			$p->fp('rows','tts_row',true);
		}
		
		$portalbox = CreateObject('phpgwapi.listbox',
			array(
				'title'     => '<font color="#FFFFFF">' . lang('Trouble Ticket System') . '</font>',
				'primary'   => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'secondary' => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'tertiary'  => $GLOBALS['phpgw_info']['theme']['navbar_bg'],
				'width'     => '100%',
				'outerborderwidth' => '0',
				'header_background_image' => $GLOBALS['phpgw']->common->image('phpgwapi/templates/phpgw_website','bg_filler.gif')
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
		$portalbox->draw($p->fp('out','tts_list'));

echo 'TEST -&gt;&gt;' . $p->fp('out','tts_list') . '&lt;&lt;-';
//		$p->pfp('out','tts_list');
		echo "\n<!-- End TTS New/Updated -->\n";
	}
?>
