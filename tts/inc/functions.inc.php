<?php
  /* $Id$ */

	function try_lang($phrase,$param=False,$upcase=False)
	{
		$trans = $GLOBALS['phpgw']->translation->translate($phrase,
			is_array($param) ? $param : array($param),False);

		return $upcase ? strtoupper($trans) : $trans;
	}

   /**
	* Produce an option list from the database table to be used in HTML template.
	*/
	function listid_field($table,$field,$idf,$selected,$conditions=False)
	{
		$db=$GLOBALS['phpgw']->db;
		$sql  = 'SELECT '.$db->db_addslashes($field).','.$db->db_addslashes($idf).
			' FROM '.$db->db_addslashes($table). ($conditions?' WHERE '.$conditions:'');
		$db->query($sql,__FILE__,__LINE__);

		while ($db->next_record())
		{
			$val=$db->f($idf,True);
			$select .= '<option value="' . intval($val). ($val==$selected?'" SELECTED>':'" >');
			$select .= htmlspecialchars(try_lang($db->f($field,True),False,True)).($val==$selected?('  '.lang('selected')):''). "</option>\n";
		}
		return $select;
	}

  /**
	* Obtain a value of a field for a given row from the database table.
	*/
	function id2field($table,$field,$idf,$id)
	{
		$db=$GLOBALS['phpgw']->db;
		$sql  = 'SELECT '.$db->db_addslashes($field).' FROM '.$db->db_addslashes($table). " WHERE ".$db->db_addslashes($idf)."=" . intval($id);
		$db->query($sql,__FILE__,__LINE__);
		if($db->next_record())
		{
			return try_lang($db->f($field,True),False,True);
		}
		else
		{
			return '';
		}
	}


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

		if ($GLOBALS['phpgw']->config->config_data['mailnotification']) {
		
			$GLOBALS['phpgw']->send = CreateObject('phpgwapi.send');
	
			$GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'");
			$GLOBALS['phpgw']->db->next_record();
    
			$group_id = $GLOBALS['phpgw']->db->f('ticket_group');
			$group_name = $GLOBALS['phpgw']->accounts->id2name($group_id);
			$t_subject = $GLOBALS['phpgw']->db->f('ticket_subject');
			$t_assigned = $GLOBALS['phpgw']->db->f('ticket_assignedto');
			$t_assigned_name = $GLOBALS['phpgw']->accounts->id2name($t_assigned);
			$t_owner_name = $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner'));
			
			// build subject
			$subject = '['.lang('Ticket').' #'.$ticket_id.' '.$group_name.'] '.lang(($GLOBALS['phpgw']->db->f('ticket_status')!='X')?'Updated':'Closed').': '.$GLOBALS['phpgw']->db->f('ticket_subject');

			// build body
			$body  = '';
			$body .= lang('Ticket').' #'.$ticket_id."\n";
			$body .= lang('Subject').': '.$t_subject."\n";
			$body .= lang('Assigned To').': '.$t_assigned_name."\n";
			$body .= lang('Priority').': '.$GLOBALS['phpgw']->db->f('ticket_priority')."\n";
			$body .= lang('Group').': '.$group_name."\n";
			$body .= lang('Opened By').': '.$t_owner_name."\n\n";
			$body .= lang('Latest Note Added').":\n";
			/**************************************************************\
			* Display latest note                                         *
			\**************************************************************/

			$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

			$history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'','',$ticket_id);
			while (is_array($history_array) && list(,$value) = each($history_array))
			{
				$latest_note=$GLOBALS['phpgw']->common->show_date($value['datetime'])." - ".$value['owner'];
                                $latest_note.=" - ".stripslashes($value['new_value'])."\n";
//				$GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
//				$body.= "$GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
//				$GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);
				
//				$GLOBALS['phpgw']->template->set_var('value_note',nl2br(stripslashes($value['new_value'])));
//				$GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row',True);
			}
			
			if (! count($history_array))
			{
				$latest_note=lang('No notes for this ticket')."\n";
			}
			
			$body .= $latest_note;

			$body .= "\n\n".lang('Original Ticket Details').":\n".$GLOBALS['phpgw']->db->f('ticket_details')."\n\n";


//			if($GLOBALS['phpgw']->db->f('t_timestamp_closed'))
//			{
//				$body .= 'Date Closed: '.$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed'))."\n\n";
//			}
			$body .= stripslashes(strip_tags($GLOBALS['phpgw']->db->f('ticket_detail')))."\n\n.";
			
			
			$GLOBALS['phpgw']->config->config_data['groupnotification']=True;
			// do we need to email all the users in the group assigned to this ticket?
			if ($GLOBALS['phpgw']->config->config_data['groupnotification']) 
			{
				// select group recipients
				$members  = $GLOBALS['phpgw']->accounts->member($group_id);
			}


			// do we need to email the owner of this ticket?
			if ($GLOBALS['phpgw']->config->config_data['ownernotification'])
			{
				// add owner to recipients
//AW -temporary			$members[] = array('account_id' => $GLOBALS['phpgw']->accounts->name2id($GLOBALS['phpgw']->db->f('ticket_owner')), 'account_name' => $GLOBALS['phpgw']->db->f('ticket_owner'));
			}

			// do we need to email the user who is assigned to this ticket?
			if ($GLOBALS['phpgw']->config->config_data['assignednotification'])
			{
				// add assigned to recipients
				$members[] = array('account_id' => $t_assigned, 'account_name' => $t_assigned_name);
			}


			$toarray = Array();
			$i=0;
			for ($i=0;$i<count($members);$i++)
			{
				if ($members[$i]['account_name'])
				{
					$prefs = $GLOBALS['phpgw']->preferences->create_email_preferences($members[$i]['account_id']);
//					$pref = CreateObject('phpgwapi.preferences',$members[$i]['account_id']);
//					$prefs = $pref->read_repository();
//					$prefs = $phpgw->common->create_emailpreferences($prefs,$members[$i]['account_id']);
					$toarray[$prefs['email']['address']] = $prefs['email']['address'];
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
					. lang('the mail server returned').':<BR>'
					. "err_code: '".$GLOBALS['phpgw']->send->err['code']."';<BR>"
					. "err_msg: '".htmlspecialchars($GLOBALS['phpgw']->send->err['msg'])."';<BR>\n"
					. "err_desc: '".$GLOBALS['phpgw']->err['desc']."'.<P>\n"
					. lang('To go back to the tts index, click <a href= %1 >here</a>',$GLOBALS['phpgw']->link('/tts/index.php','cd=13'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}
	}
?>
