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
		global $phpgw;
		global $phpgw_info;
		$members = array();
		
		// $phpgw->preferences->read_repository();
		// $phpgw_info['user']['preferences']['tts']['mailnotification']

		$phpgw->config->read_repository();

		
		if ($phpgw->config->config_data['mailnotification']) {
		
			$phpgw->send = CreateObject('phpgwapi.send');
	
			$phpgw->db->query("select t_id,t_category,t_detail,t_priority,t_user,t_assignedto,"
				. "t_timestamp_opened, t_timestamp_closed, t_subject from phpgw_tts_tickets where t_id='$ticket_id'");
			$phpgw->db->next_record();
    
			$group = $phpgw->db->f("t_category");
			
			// build subject
			$subject = "[TTS #".$ticket_id." ".$group."] ".(!$phpgw->db->f("t_timestamp_closed")?"Updated":"Closed").": ".$phpgw->db->f("t_subject");

			// build body
			$body  = "";
			$body .= "TTS #".$ticket_id."\n\n";
			$body .= "Subject: ".$phpgw->db->f("t_subject")."\n\n";
			$body .= "Assigned To: ".$phpgw->db->f("t_assignedto")."\n\n";
			$body .= "Priority: ".$phpgw->db->f("t_priority")."\n\n";
			$body .= "Group: ".$group."\n\n";
			$body .= "Opened By: ".$phpgw->db->f("t_user")."\n";
			$body .= "Date Opened: ".$phpgw->common->show_date($phpgw->db->f("t_timestamp_opened"))."\n\n";
			if($phpgw->db->f("t_timestamp_closed"))
			{
				$body .= "Date Closed: ".$phpgw->common->show_date($phpgw->db->f("t_timestamp_closed"))."\n\n";
			}
			$body .= stripslashes(strip_tags($phpgw->db->f("t_detail")))."\n\n.";
			
			if ($phpgw->config->config_data['groupnotification']) 
			{
			// select group recipients
			$group_id = $phpgw->accounts->name2id($group);
			$members  = $phpgw->accounts->members($group_id);
			}

			if ($phpgw->config->config_data['ownernotification'])
			{
			// add owner to recipients
			 $members[] = array("account_id" => $phpgw->accounts->name2id($phpgw->db->f("t_user")), "account_name" => $phpgw->db->f("t_user"));
			}

			if ($phpgw->config->config_data['assignednotification'])
			{
			// add assigned to recipients
			 $members[] = array("account_id" => $phpgw->accounts->name2id($phpgw->db->f("t_assignedto")), "account_name" => $phpgw->db->f("t_assignedto"));
			}

			$toarray = Array();
			$i=0;
			for ($i=0;$i<count($members);$i++)
			{
				if ($members[$i]['account_id'])
				{
					$pref = CreateObject('phpgwapi.preferences',$members[$i]['account_id']);
					$prefs = $pref->read_repository();
					$prefs = $phpgw->common->create_emailpreferences($prefs,$members[$i]['account_id']);
					$toarray[] = $prefs["email"]["address"];
					// echo '<br>'.$toarray[$i];
					unset($pref);
				}
			}
			if(count($toarray) > 1)
			{
				$to = implode(",",$toarray);
			}
			else
			{
				$to = $toarray[0];
			}
    
			$rc = $phpgw->send->msg("email", $to, $subject, stripslashes($body), "", $cc, $bcc);
			if (!$rc)
			{
				echo  "Your message could <B>not</B> be sent!<BR>\n"
					. "The mail server returned:<BR>"
					. "err_code: '".$phpgw->send->err["code"]."';<BR>"
					. "err_msg: '".htmlspecialchars($phpgw->send->err[msg])."';<BR>\n"
					. "err_desc: '".$phpgw->err[desc]."'.<P>\n"
					. "To go back to the msg list, click <A HRef=\"".$phpgw->link("/tts/index.php","cd=13")."\">here</a>";
				$phpgw->common->phpgw_exit();
			}
		}
	}
?>
