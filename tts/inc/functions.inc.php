<?php
  /* $Id$ */

	//open and print each line of a file
	function rfile($textFile)
	{
		$myFile = fopen("$textFile", "r");
		if(!($myFile))
		{
			print("<P><B>Error: </B>");
			print("<i>'$textFile'</i> could not be read\n");
			$phpgw->common->phpgw_exit();
		}
		if($myFile)
		{
			while(!feof($myFile)) {
				$myLine = fgets($myFile, 255);
				print("$myLine <BR>\n");
			}
			fclose($myFile);
		}
	}

	function mail_ticket($ticket_id)
	{
		$members = array();
		
		// $GLOBALS['phpgw']->preferences->read_repository();
		// $GLOBALS['phpgw_info']['user']['preferences']['tts']['mailnotification']

		$GLOBALS['phpgw']->config->read_repository();

		
		if ($GLOBALS['phpgw']->config->config_data['mailnotification'])
		{
			$db2 = $GLOBALS['phpgw']->db;
		
			$GLOBALS['phpgw']->send = CreateObject('phpgwapi.send');
	
			$GLOBALS['phpgw']->db->query('SELECT * FROM phpgw_tts_tickets WHERE ticket_id='.$ticket_id,__LINE__,__FILE__);
			$GLOBALS['phpgw']->db->next_record();
			
			$db2->query('SELECT * FROM phpgw_tts_views WHERE view_id='.$ticket_id,__LINE__,__FILE);
			$db2->next_record();

			$group = $GLOBALS['phpgw']->db->f('ticket_category');

			$stat = $GLOBALS['phpgw']->db->f('ticket_status');
			$status = array(
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

			// build subject
			$subject = '['.lang('Ticket').' #'.$ticket_id.' '.$group.'] '.lang($status[$stat]).': '.$GLOBALS['phpgw']->db->f('ticket_subject');

			// build body
			$body  = '';
			$body .= lang('Ticket').' #'.$ticket_id."\n";
			$body .= lang('Subject').': '.$GLOBALS['phpgw']->db->f('ticket_subject')."\n";
			$body .= lang('Assigned To').': '.$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_assignedto'))."\n";
			$body .= lang('Priority').': '.$GLOBALS['phpgw']->db->f('ticket_priority')."\n";
			$body .= lang('Group').': '.$group_name."\n";
			$body .= lang('Opened By').': '.$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner'))."\n\n";
			$body .= lang('Latest Note Added').":\n";
			if($GLOBALS['phpgw']->db->f('t_timestamp_closed'))
			{
				$body .= lang('Date Closed').': '.$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed'))."\n\n";
			}
			$body .= stripslashes(strip_tags($GLOBALS['phpgw']->db->f('t_details')))."\n\n.";
			
			if ($GLOBALS['phpgw']->config->config_data['groupnotification']) 
			{
				// select group recipients
				$group_id = $GLOBALS['phpgw']->accounts->name2id($group);
				$members  = $GLOBALS['phpgw']->accounts->members($group_id);
			}

			if ($GLOBALS['phpgw']->config->config_data['ownernotification'])
			{
				// add owner to recipients
//				$members[] = array('account_id' => $GLOBALS['phpgw']->accounts->name2id($GLOBALS['phpgw']->db->f('t_user')), 'account_name' => $GLOBALS['phpgw']->db->f('t_user'));
				$members[] = array('account_id' => $GLOBALS['phpgw']->db->f('ticket_owner'), 'account_name' => $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner')));
			}

			if ($GLOBALS['phpgw']->config->config_data['assignednotification'])
			{
				// add assigned to recipients
//				$members[] = array('account_id' => $GLOBALS['phpgw']->accounts->name2id($GLOBALS['phpgw']->db->f('t_assignedto')), 'account_name' => $GLOBALS['phpgw']->db->f('t_assignedto'));
				$members[] = array('account_id' => $GLOBALS['phpgw']->db->f('ticket_assignedto'), 'account_name' => $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_assignedto')));
			}

			$toarray = Array();
			$i=0;
			for ($i=0;$i<count($members);$i++)
			{
				if ($members[$i]['account_id'])
				{
					$prefs = $GLOBALS['phpgw']->preferences->create_email_preferences($members[$i]['account_id']);
//					$pref = CreateObject('phpgwapi.preferences',$members[$i]['account_id']);
//					$prefs = $pref->read_repository();
//					$prefs = $phpgw->common->create_emailpreferences($prefs,$members[$i]['account_id']);
					$toarray[$prefs['email']['address']] = $prefs['email']['address'];
					// echo '<br>'.$toarray[$i];
//					unset($pref);
				}
			}
			if(count($toarray) > 1)
			{
				$to = implode(',',$toarray);
			}
			else
			{
				$to = current($toarray);
			}
    
			$rc = $GLOBALS['phpgw']->send->msg('email', $to, $subject, stripslashes($body), '', $cc, $bcc);
			if (!$rc)
			{
				echo  lang('Your message could <B>not</B> be sent!<BR>')."\n"
					. lang('The mail server returned').':<BR>'
					. "err_code: '".$GLOBALS['phpgw']->send->err['code']."';<BR>"
					. "err_msg: '".htmlspecialchars($GLOBALS['phpgw']->send->err['msg'])."';<BR>\n"
					. "err_desc: '".$GLOBALS['phpgw']->err['desc']."'.<P>\n"
					. lang('To go back to the tts index, click <a href= x >here</a>',$GLOBALS['phpgw']->link('/tts/index.php','cd=13'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}
	}
?>
