<?php
	/***************************************************************************\
	* phpGroupWare - FeLaMiMail                                                 *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class bofelamimail
	{
		var $public_functions = array
		(
			'updateImapStatus'	=> True,
			'flagMessages'		=> True
		);

		var $mbox;		// the mailbox identifier any function should use

		function bofelamimail($_foldername)
		{
			$this->foldername	= $_foldername;
			$this->accountid	= $GLOBALS['phpgw_info']['user']['account_id'];
			
			$this->bopreferences	= CreateObject('felamimail.bopreferences');
			$this->sofelamimail	= CreateObject('felamimail.sofelamimail');
			
			$this->mailPreferences	= $this->bopreferences->getPreferences();
			$this->imapBaseDir	= '';
			
			$mailboxString = sprintf("{%s:%s}%s",
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['imapPort'],
					imap_utf7_encode($this->foldername));

			$this->mbox = imap_open ($mailboxString, 
					$this->mailPreferences['username'], $this->mailPreferences['key']);
		}
		
		function closeConnection()
		{
			imap_close($this->mbox);
		}

		function compressFolder()
		{
			
			$deleteOptions 	= $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["deleteOptions"];
			$trashFolder	= $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["trashFolder"];
			
			if($this->foldername == $trashFolder && $deleteOptions == "move_to_trash")
			{
				// delete all messages in the trash folder
				$mailboxString = sprintf("{%s:%s}%s",
						$this->mailPreferences['imapServerAddress'],
						$this->mailPreferences['imapPort'],
						imap_utf7_encode($this->foldername));
				$status = imap_status ($this->mbox, $mailboxString, SA_ALL);
				$numberOfMessages = $status->messages;
				$msgList = "1:$numberOfMessages";
				imap_delete($this->mbox, $msgList);
				imap_expunge($this->mbox);
			}
			elseif($deleteOptions == "mark_as_deleted")
			{
				// delete all messages in the current folder which have the deleted flag set 
				imap_expunge($this->mbox);
			}
		}

		function decode_header2($_charset, $_string)
		{
			$_string = str_replace('_', ' ', $_string);
			$string = quoted_printable_decode($_string);
			return $string;
		}

		function decode_header($string)
		{
			/* Decode from qp or base64 form */
			if (preg_match("/\=\?(.*?)\?b\?/i", $string))
			{
				$string = ereg_replace("'", "\'", $string);
				$string = preg_replace("/\=\?(.*?)\?b\?(.*?)\?\=/ieU","base64_decode('\\2')",$string);
				return $string;
			}
			if (preg_match("/\=\?(.*?)\?q\?/i", $string))
			{
				$string = preg_replace("/\=\?(.*?)\?q\?(.*?)\?\=/ie","\$this->decode_header2('\\1','\\2')",$string);
				return $string;
			}
			return $string;
		}
		
		function deleteMessages($_messageUID)
		{
			$caching = CreateObject('felamimail.bocaching',
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['username'],
					$this->foldername);

			reset($_messageUID);
			while(list($key, $value) = each($_messageUID))
			{
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}
			
			$deleteOptions 	= $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["deleteOptions"];
			$trashFolder	= $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["trashFolder"];
			
			if($this->foldername == $trashFolder && $deleteOptions == "move_to_trash")
			{
				$deleteOptions = "remove_immediately";
			}
			
			switch($deleteOptions)
			{
				case "move_to_trash":
					if(!empty($trashFolder))
					{
						if (imap_mail_move ($this->mbox, $msglist, imap_utf7_encode($trashFolder), CP_UID))
						{
							imap_expunge($this->mbox);
							reset($_messageUID);
							while(list($key, $value) = each($_messageUID))
							{
								$caching->removeFromCache($value);
							}
						}
						else
						{
							print imap_last_error()."<br>";
						}
					}
					break;
					
				case "mark_as_deleted":
					imap_delete($this->mbox, $msglist, FT_UID);
					break;
					
				case "remove_immediately":
					imap_delete($this->mbox, $msglist, FT_UID);
					imap_expunge ($this->mbox);
					reset($_messageUID);
					while(list($key, $value) = each($_messageUID))
					{
						$caching->removeFromCache($value);
					}
					break;
			}
		}
		
		function flagMessages($_flag, $_messageUID)
		{
			reset($_messageUID);
			while(list($key, $value) = each($_messageUID))
			{
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}
			
			switch($_flag)
			{
				case "flagged":
					$result = imap_setflag_full ($this->mbox, $msglist, "\\Flagged", ST_UID);
					break;
				case "read":
					$result = imap_setflag_full ($this->mbox, $msglist, "\\Seen", ST_UID);
					break;
				case "unflagged":
					$result = imap_clearflag_full ($this->mbox, $msglist, "\\Flagged", ST_UID);
					break;
				case "unread":
					$result = imap_clearflag_full ($this->mbox, $msglist, "\\Seen", ST_UID);
					break;
			}
			
			
			#print "Result: $result<br>";
		}
		
		function getFolderList($_subscribedOnly)
		{
			$mailboxString = sprintf("{%s:%s}%s",
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['imapPort'],
					imap_utf7_encode($this->imapBaseDir));
		
			if($_subscribedOnly == 'true')
			{
				$list = imap_getsubscribed($this->mbox,$mailboxString,"*");
			}
			else
			{
				$list = imap_getmailboxes($this->mbox,$mailboxString,"*");
			}
			if(is_array($list))
			{
				reset($list);
				while (list($key, $val) = each($list))
				{
					$folders[] = preg_replace("/{.*}/","",$val->name);
					
				}
				sort($folders,SORT_STRING);
				reset($folders);
				return $folders;
			}
			else
			{
				return false;
			}
		}
		
		function getHeaders($_startMessage, $_numberOfMessages, $_sort)
		{

			$caching = CreateObject('felamimail.bocaching',
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['username'],
					$this->foldername);
			$transformdate = CreateObject('felamimail.transformdate');

			$mailboxString = sprintf("{%s:%s}%s",
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['imapPort'],
					imap_utf7_encode($this->foldername));
			$status = imap_status ($this->mbox, $mailboxString, SA_ALL);
			$cachedStatus = $caching->getImapStatus();

			// no data chached already?
			// get all message informations from the imap server for this folder
			if ($cachedStatus['uidnext'] == 0)
			{
				#print "nix gecached!!<br>";
				#print "current UIDnext :".$cachedStatus['uidnext']."<br>";
				#print "new UIDnext :".$status->uidnext."<br>";
				for($i=1; $i<=$status->messages; $i++)
				{
					@set_time_limit();
					$messageData['uid'] = imap_uid($this->mbox, $i);
					$header = imap_headerinfo($this->mbox, $i);
					
					if (isset($header->date))
					{
						$header->date = ereg_replace('  ', ' ', $header->date);
						$tmpdate = explode(' ', trim($header->date));
					}
					else
					{
						$tmpdate = $date = array("","","","","","");
					}
					$messageData['date']		= date("Y-m-d H:i:s",$transformdate->getTimeStamp($tmpdate));
					
					$messageData['subject']		= $header->subject;
					$messageData['to_name']		= $header->to[0]->personal;
					$messageData['to_address']	= $header->to[0]->mailbox."@".$header->to[0]->host;
					$messageData['sender_name']	= $header->from[0]->personal;
					$messageData['sender_address']	= $header->from[0]->mailbox."@".$header->from[0]->host;
					$messageData['size']		= $header->Size;
					
					$caching->addToCache($messageData);
					
					unset($messageData);
				}
				$caching->updateImapStatus($status);
			}
			// update cache, but only add new emails
			elseif($status->uidnext != $cachedStatus['uidnext'])
			{
				#print "found new messages<br>";
				#print "new uidnext: ".$status->uidnext." old uidnext: ".$cachedStatus['uidnext']."<br>";
				$uidRange = $cachedStatus['uidnext'].":".$status->uidnext;
				#print "$uidRange<br>";
				$newHeaders = imap_fetch_overview($this->mbox,$uidRange,FT_UID);
				for($i=0; $i<count($newHeaders); $i++)
				{
					$messageData['uid'] = $newHeaders[$i]->uid;
					$header = imap_headerinfo($this->mbox, $newHeaders[$i]->msgno);
				
					if (isset($header->date)) 
					{	
						$header->date = ereg_replace('  ', ' ', $header->date);
						$tmpdate = explode(' ', trim($header->date));
					}
					else
					{
						$tmpdate = $date = array("","","","","","");	
					}
					$messageData['date'] 		= date("Y-m-d H:i:s",$transformdate->getTimeStamp($tmpdate));
				
					$messageData['subject'] 	= $header->subject;
					$messageData['to_name']		= $header->to[0]->personal;
					$messageData['to_address']	= $header->to[0]->mailbox."@".$header->to[0]->host;
					$messageData['sender_name'] 	= $header->from[0]->personal;
					$messageData['sender_address'] 	= $header->from[0]->mailbox."@".$header->from[0]->host;
					$messageData['size'] 		= $header->Size;
					
					$caching->addToCache($messageData);
					
					unset($messageData);
				}
				$caching->updateImapStatus($status);
			}

			// now let's do some clean up
			// if we have more messages in the cache then in the imap box, some external 
			// imap client deleted some messages. It's better to erase the messages from the cache.
			$displayHeaders = $caching->getHeaders();
			if (count($displayHeaders) > $status->messages)
			{
				$messagesToRemove = count($displayHeaders) - $status->messages;
				reset($displayHeaders);
				for($i=0; $i<count($displayHeaders); $i++)
				{
					$header = imap_fetch_overview($this->mbox,$displayHeaders[$i]['uid'],FT_UID);
					if (count($header[0]) == 0)
					{
						$caching->removeFromCache($displayHeaders[$i]['uid']);
						$removedMessages++;
					}
					if ($removedMessages == $messagesToRemove) break;
				}
			}

			$displayHeaders = $caching->getHeaders($_startMessage, $_numberOfMessages, $_sort);
			
			$count=0;
			for ($i=0;$i<count($displayHeaders);$i++)
			{
				$header = imap_fetch_overview($this->mbox,$displayHeaders[$i]['uid'],FT_UID);

				$rawHeader = imap_fetchheader($this->mbox,$displayHeaders[$i]['uid'],FT_UID);
				$headers = $this->sofelamimail->fetchheader($rawHeader);
				
				$retValue['header'][$count]['subject'] = $this->decode_header($header[0]->subject);
				$from = imap_rfc822_parse_adrlist($headers['from'],"unknown domain");
				$retValue['header'][$count]['sender_name'] = $this->decode_header($from[0]->personal);
				$retValue['header'][$count]['sender_address'] = $from[0]->mailbox."@".$from[0]->host;
				$to = imap_rfc822_parse_adrlist($headers['to'],"unknown domain");
				$retValue['header'][$count]['to_name'] = $this->decode_header($to[0]->personal);
				$retValue['header'][$count]['to_address'] = $to[0]->mailbox."@".$to[0]->host;
				$retValue['header'][$count]['size'] = $header[0]->size;
				if (isset($header[0]->date)) 
				{	
					$header[0]->date = ereg_replace('  ', ' ', $header[0]->date);
					$tmpdate = explode(' ', trim($header[0]->date));
				}
				else
				{
					$tmpdate = $date = array("","","","","","");	
				}

				$timestamp = $transformdate->getTimeStamp($tmpdate);
				$timestamp7DaysAgo = 
					mktime(date("H"), date("i"), date("s"), date("m"), date("d")-7, date("Y"));
				$timestampNow = 
					mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
				// date from the future
				if($timestamp > $timestampNow)
				{
					$retValue['header'][$count]['date'] = date("Y-m-d",$timestamp);
				}
				// email from today, show only time
				elseif (date("Y-m-d") == date("Y-m-d",$timestamp))
				{
					$retValue['header'][$count]['date'] = date("H:i:s",$timestamp);
				}
				// email from the last 7 days, show only weekday
				elseif($timestamp7DaysAgo < $timestamp)
				{
					$retValue['header'][$count]['date'] = lang(date("l",$timestamp));
					#$retValue['header'][$count]['date'] = date("Y-m-d H:i:s",$timestamp7DaysAgo)." - ".date("Y-m-d",$timestamp);
				}
				else
				{
					$retValue['header'][$count]['date'] = date("Y-m-d",$timestamp);
				}
				$retValue['header'][$count]['id'] = $header[0]->msgno;
				$retValue['header'][$count]['uid'] = $displayHeaders[$i]['uid'];
				$retValue['header'][$count]['recent'] = $header[0]->recent;
				$retValue['header'][$count]['flagged'] = $header[0]->flagged;
				$retValue['header'][$count]['answered'] = $header[0]->answered;
				$retValue['header'][$count]['deleted'] = $header[0]->deleted;
				$retValue['header'][$count]['seen'] = $header[0]->seen;
				$retValue['header'][$count]['draft'] = $header[0]->draft;

				$count++;
			}

			if(is_array($retValue['header']))
			{
				$retValue['info']['total']	= $status->messages;
				$retValue['info']['first']	= $_startMessage;
				$retValue['info']['last']	= $_startMessage + $count - 1 ;
				return $retValue;
			}
			else
			{
				return 0;
			}
		}

		function moveMessages($_foldername, $_messageUID)
		{
			$caching = CreateObject('felamimail.bocaching',
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['username'],
					$this->foldername);
			$deleteOptions  = $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["deleteOptions"];

			reset($_messageUID);
			while(list($key, $value) = each($_messageUID))
			{
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}
			#print $msglist."<br>";
			
			#print "destination folder: ".imap_utf7_encode($_foldername)."<br>";
			
			if (imap_mail_move ($this->mbox, $msglist, imap_utf7_encode($_foldername), CP_UID))
			{
				#print "allet ok<br>";
				if($deleteOptions != "mark_as_deleted")
				{
					imap_expunge($this->mbox);
					reset($_messageUID);
					while(list($key, $value) = each($_messageUID))
					{
						$caching->removeFromCache($value);
					}
				}
			}
			else
			{
				print imap_last_error()."<br>";
			}
			
		}

	}

?>