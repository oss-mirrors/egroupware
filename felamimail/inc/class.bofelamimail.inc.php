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

		// define some constants
		// message types
		var $type = array("text", "multipart", "message", "application", "audio", "image", "video", "other");
		
		// message encodings
		var $encoding = array("7bit", "8bit", "binary", "base64", "quoted-printable", "other");

		function bofelamimail()
		{
			$this->restoreSessionData();
			
			// set some defaults
			if(count($this->sessionData) == 0)
			{
				// this should be under user preferences
				// sessionData empty
				// no filter active
				$this->sessionData['activeFilter']	= "-1";
				// default mailbox INBOX
				$this->sessionData['mailbox']		= "INBOX";
				// default start message
				$this->sessionData['startMessage']	= 1;
				// default sorting
				if(!empty($GLOBALS['phpgw_info']['user']['preferences']['felamimail']['sortOrder']))
				{
					$this->sessionData['sort']	= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['sortOrder'];
				}
				else
				{
					$this->sessionData['sort']	= 6;
				}
				$this->saveSessionData();
			}
			
			$this->foldername	= $this->sessionData['mailbox'];
			$this->accountid	= $GLOBALS['phpgw_info']['user']['account_id'];
			
			$this->bopreferences	= CreateObject('felamimail.bopreferences');
			$this->sofelamimail	= CreateObject('felamimail.sofelamimail');
			
			$this->mailPreferences	= $this->bopreferences->getPreferences();
			$this->imapBaseDir	= '';
			
		}
		
		function appendMessage($_folder, $_header, $_body)
		{
			imap_append($this->mbox, $_folder, $_header.$_body);
		}
		
		function closeConnection()
		{
			imap_close($this->mbox);
		}

		function compressFolder()
		{
			$prefs	= $this->bopreferences->getPreferences();

			$deleteOptions	= $prefs['deleteOptions'];
			$trashFolder	= $prefs['trash_folder'];
			
			if($this->sessionData['mailbox'] == $trashFolder && $deleteOptions == "move_to_trash")
			{
				// delete all messages in the trash folder
				$mailboxString = sprintf("{%s:%s}%s",
						$this->mailPreferences['imapServerAddress'],
						$this->mailPreferences['imapPort'],
						imap_utf7_encode($this->sessionData['mailbox']));
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
					$this->sessionData['mailbox']);

			reset($_messageUID);
			while(list($key, $value) = each($_messageUID))
			{
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}

			$prefs	= $this->bopreferences->getPreferences();

			$deleteOptions	= $prefs['deleteOptions'];
			$trashFolder	= $prefs['trash_folder'];

			if($this->sessionData['mailbox'] == $trashFolder && $deleteOptions == "move_to_trash")
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
				case "answered":
					$result = imap_setflag_full ($this->mbox, $msglist, "\\Answered", ST_UID);
					break;
				case "unflagged":
					$result = imap_clearflag_full ($this->mbox, $msglist, "\\Flagged", ST_UID);
					break;
				case "unread":
					$result = imap_clearflag_full ($this->mbox, $msglist, "\\Seen", ST_UID);
					$result = imap_clearflag_full ($this->mbox, $msglist, "\\Answered", ST_UID);
					break;
			}
			
			
			#print "Result: $result<br>";
		}
		
		// this function is based on a on "Building A PHP-Based Mail Client"
		// http://www.devshed.com
		// fetch a specific attachment from a message
		function getAttachment($_uid, $_partID)
		{
			// parse message structure
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			$this->structure = array();
			$this->parse2($structure);
			$sections = $this->structure;
			
			// look for specified part
			while(list($key,$value) = each($sections))
			{
				#print $value["pid"]." ".$_partID."<br>";
				if($value["pid"] == $_partID)
				{
					$type = $value["type"];
					$encoding = $value["encoding"];
					$filename = $value["name"];
				}
			}
			
			$attachment = imap_fetchbody($this->mbox, $_uid, $_partID, FT_UID);
			
			switch ($encoding) 
			{
				case ENCBASE64:
					// use imap_base64 to decode
					$attachment = imap_base64($attachment);
					break;
				case ENCQUOTEDPRINTABLE:
					// use imap_qprint to decode
					$attachment = imap_qprint($attachment);
					break;
				case ENCOTHER:
					// not sure if this needs decoding at all
					break;
				default:
					// it is either not encoded or we don't know about it
			}
			
			return array(
				'type'	=> $type,
				'encoding'	=> $encoding,
				'filename'	=> $filename,
				'attachment'	=> $attachment
				);
		}

		// this function is based on a on "Building A PHP-Based Mail Client"
		// http://www.devshed.com
		// iterate through object returned by parse()
		// create a new array holding information only on message attachments
		function get_attachments($arr)
		{
			reset($arr);
			while(list($key,$value) = @each($arr))
			{
				if(strtolower($value["disposition"])		== "attachment" ||
					strtolower($value["disposition"])	== "inline" ||
					($value["type"] != "text/plain" && substr($value["type"],0,9) != "multipart"))
				{
					$ret[] = $value;
				}
			}
			
			return $ret;
		}
		
		function getFolderStatus($_folderName)
		{
			// now we have the keys as values
			$subscribedFolders = array_flip($this->getFolderList(true));
			#print_r($subscribedFolders);
			#print $subscribedFolders[$_folderName]." - $_folderName<br>";
			if(isset($subscribedFolders[$_folderName]))
			{
				$retValue['subscribed']	= true;
			}
			else
			{
				$retValue['subscribed'] = false;
			}
			
			return $retValue;
		}
		
		function getFolderList($_subscribedOnly=false)
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
					#$folders[] = $val->name;
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

#			printf ("this->bofelamimail->getHeaders start: %s<br>",date("H:i:s",mktime()));

			$caching = CreateObject('felamimail.bocaching',
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['username'],
					$this->sessionData['mailbox']);
			$bofilter = CreateObject('felamimail.bofilter');
			$transformdate = CreateObject('felamimail.transformdate');

			$mailboxString = sprintf("{%s:%s}%s",
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['imapPort'],
					imap_utf7_encode($this->sessionData['mailbox']));
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
					// parse structure to see if attachments exist
					// display icon if so
					$structure = imap_fetchstructure($this->mbox, $i);
					$sections = $this->parse($structure);
					$attachments = $this->get_attachments($sections);
					
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
					
					$messageData['attachments']     = "false";
					if (is_array($attachments))
					{
						$messageData['attachments']	= "true";
					}
					
					// maybe it's already in the database
					// lets remove it, sometimes the database gets out of sync
					$caching->removeFromCache($messageData['uid']);
					
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
					// parse structure to see if attachments exist
					// display icon if so
					$structure = imap_fetchstructure($this->mbox, $newHeaders[$i]->msgno);
					$sections = $this->parse($structure);
					$attachments = $this->get_attachments($sections);
				
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

					$messageData['attachments']     = "false";
					if (is_array($attachments))
					{
						$messageData['attachments']	= "true";
					}
					
					// maybe it's already in the database
					// lets remove it, sometimes the database gets out of sync
					$caching->removeFromCache($messageData['uid']);
					
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

			// now lets gets the important messages
			$filterList = $bofilter->getFilterList();
			$activeFilter = $this->sessionData['activeFilter'];
			$filter = $filterList[$activeFilter];
			$displayHeaders = $caching->getHeaders($_startMessage, $_numberOfMessages, $_sort, $filter);

			$count=0;
			for ($i=0;$i<count($displayHeaders);$i++)
			{
				$header = imap_fetch_overview($this->mbox,$displayHeaders[$i]['uid'],FT_UID);

				#$rawHeader = imap_fetchheader($this->mbox,$displayHeaders[$i]['uid'],FT_UID);
				#$headers = $this->sofelamimail->fetchheader($rawHeader);
				
				$retValue['header'][$count]['subject'] = $this->decode_header($header[0]->subject);
				$retValue['header'][$count]['sender_name'] 	= $this->decode_header($displayHeaders[$i]['sender_name']);
				$retValue['header'][$count]['sender_address'] 	= $this->decode_header($displayHeaders[$i]['sender_address']);
				$retValue['header'][$count]['to_name'] 		= $this->decode_header($displayHeaders[$i]['to_name']);
				$retValue['header'][$count]['to_address'] 	= $this->decode_header($displayHeaders[$i]['to_address']);
				$retValue['header'][$count]['attachments']	= $displayHeaders[$i]['attachments'];
				$retValue['header'][$count]['size'] 		= $header[0]->size;
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

#			printf ("this->bofelamimail->getHeaders done: %s<br>",date("H:i:s",mktime()));

			if(is_array($retValue['header']))
			{
				$retValue['info']['total']	= $caching->getMessageCounter($filter);
				$retValue['info']['first']	= $_startMessage;
				$retValue['info']['last']	= $_startMessage + $count - 1 ;
				return $retValue;
			}
			else
			{
				return 0;
			}
		}

		function getMessageAttachments($_uid)
		{
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			if(sizeof($structure->parts) > 0 && is_array($structure->parts))
			{
				$this->structure = array();
				$this->parse2($structure);
				$sections = $this->structure;
				#$sections = $this->parse($structure);
				return $this->get_attachments($sections);
			}
		}
		
		function getMessageBody($_uid)
		{
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			if(sizeof($structure->parts) > 0 && is_array($structure->parts))
			{
				#print "<pre>";print_r($structure);print"</pre>";
				$this->structure = array();
				$this->parse2($structure);
				$sections = $this->structure;
				#print "<hr><pre>";print_r($this->structure);print"</pre>";
			}
			
			if(is_array($sections))
			{
				reset($sections);
				while(list($key,$value) = each($sections))
				#for($x=0; $x<sizeof($sections); $x++)
				{
					unset($newPart);
					if(($value["type"] == "text/plain" || 
						$value["type"] == "message/rfc822") && 
						strtolower($value["disposition"]) != "attachment")
					{
						$newPart = stripslashes(trim(imap_fetchbody($this->mbox, $_uid, $value["pid"], FT_UID)));
					}
					
					if(isset($newPart)) 
					{
					switch ($value['encoding']) 
					{
						case ENCBASE64:
							// use imap_base64 to decode
							$newPart = imap_base64($newPart);
							break;
						case ENCQUOTEDPRINTABLE:
							// use imap_qprint to decode
							$newPart = imap_qprint($newPart);
							break;
						case ENCOTHER:
							// not sure if this needs decoding at all
							break;
						default:
							// it is either not encoded or we don't know about it
					}
						$bodyPart[] = $newPart;
					}
				}
			}
			else
			{
				$newPart = stripslashes(trim(imap_body($this->mbox, $_uid, FT_UID)));
				switch ($structure->encoding) 
				{
					case ENCBASE64:
						// use imap_base64 to decode
						$newPart = imap_base64($newPart);
						break;
					case ENCQUOTEDPRINTABLE:
						// use imap_qprint to decode
						$newPart = imap_qprint($newPart);
						break;
					case ENCOTHER:
						// not sure if this needs decoding at all
						break;
					default:
						// it is either not encoded or we don't know about it
				}
				$bodyPart[] = $newPart;
			}
			
			return $bodyPart;
		}

		function getMessageHeader($_uid)
		{
			$msgno = imap_msgno($this->mbox, $_uid);
			return imap_header($this->mbox, $msgno);
		}

		function getMessageRawHeader($_uid)
		{
			return imap_fetchheader($this->mbox, $_uid, FT_UID);
		}

		function getMessageStructure($_uid)
		{
			return imap_fetchstructure($this->mbox, $_uid, FT_UID);
		}

		function moveMessages($_foldername, $_messageUID)
		{
			$caching = CreateObject('felamimail.bocaching',
					$this->mailPreferences['imapServerAddress'],
					$this->mailPreferences['username'],
					$this->sessionData['mailbox']);
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

		function openConnection($_folderName='',$_options=0)
		{
			switch($this->mailPreferences['imap_server_type'])
			{
				case "imap":
					$mailboxString = sprintf("{%s:%s}%s",
							$this->mailPreferences['imapServerAddress'],
							$this->mailPreferences['imapPort'],
							imap_utf7_encode($this->sessionData['mailbox']));
					break;
					
				case "imaps-encr-only":
					$mailboxString = sprintf("{%s:%s/ssl/novalidate-cert}%s",
						$this->mailPreferences['imapServerAddress'],
						$this->mailPreferences['imapPort'],
						imap_utf7_encode($this->sessionData['mailbox']));
					break;
					
				case "imaps-encr-auth":
					$mailboxString = sprintf("{%s:%s/ssl}%s",
						$this->mailPreferences['imapServerAddress'],
						$this->mailPreferences['imapPort'],
						imap_utf7_encode($this->sessionData['mailbox']));
					break;
			}

			if(!$this->mbox = @imap_open ($mailboxString, 
					$this->mailPreferences['username'], $this->mailPreferences['key'], $_options))
			{
				return imap_last_error();
			}
			else
			{
				return True;
			}
			
		}		

		// this function is based on a on "Building A PHP-Based Mail Client"
		// http://www.devshed.com
		function parse($structure)
		{
			// create an array to hold message sections
			$ret = array();
			
			// split structure into parts
			$parts = $structure->parts;
			                                                                                        
			for($x=0; $x<sizeof($parts); $x++)
			{
				$ret[$x]["pid"] = ($x+1);
				
				$part = $parts[$x];
				
				// default to text
				if ($part->type == "") { $part->type = 0; }
				
				$ret[$x]["type"] = $this->type[$part->type] . "/" . strtolower($part->subtype);
				
				// default to 7bit
				if ($part->encoding == "") { $part->encoding = 0; }
				$ret[$x]["encoding"] = $this->encoding[$part->encoding];
				$ret[$x]["Encoding"] = $part->encoding;
				
				$ret[$x]["size"] = strtolower($part->bytes);
				
				$ret[$x]["disposition"] = strtolower($part->disposition);
				
				if (strtolower($part->disposition) == "attachment")
				{
				
					$params = $part->dparameters;
					foreach ($params as $p)
					{
						if($p->attribute == "FILENAME")
						{
							$ret[$x]["name"] = $p->value;
							break;
						}
					}
				}
			}
			
			return $ret;
		}
		

		// this function is based on
		// http://www.bitsense.com/PHPNotes/IMAP/imap_fetchstructure.asp/
		function parse2($this_part,$part_no="")
		{
				if ($this_part->ifdisposition && strtolower($this_part->disposition) == "attachment") 
				{
					// See if it has a disposition
					// The only thing I know of that this
					// would be used for would be an attachment
					// Lets check anyway
					if (strtolower($this_part->disposition) == "attachment" ||
						strtolower($this_part->disposition) == "inline" ) 
					{
						$this->structure[$part_no]['encoding']	= $this_part->encoding;
						$this->structure[$part_no]['size']	= $this_part->bytes;
						$this->structure[$part_no]['disposition']	= $this_part->disposition;
						$this->structure[$part_no]['pid']	= $part_no;
						$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
						// If it is an attachment, then we let people download it
						// First see if they sent a filename
						$att_name = lang("unknown");
						if($this_part->ifparameters)
						{
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if (strtolower($param->attribute) == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
						}
						if($this_part->ifdparameters)
						{
							for ($lcv = 0; $lcv < count($this_part->dparameters); $lcv++) 
							{
								$param = $this_part->dparameters[$lcv];
								if (strtolower($param->attribute) == "filename") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
						}
						// You could give a link to download the attachment here....
						switch ($this_part->type) 
						{
							case TYPETEXT:
								$mime_type = "text";
								break;
							case TYPEMULTIPART:
								$mime_type = "multipart";
								break;
							case TYPEMESSAGE:
								$mime_type = "message";
								break;
							case TYPEAPPLICATION:
								$mime_type = "application";
								break;
							case TYPEAUDIO:
								$mime_type = "audio";
								break;
							case TYPEIMAGE:
								$mime_type = "image";
								break;
							case TYPEVIDEO:
								$mime_type = "video";
								break;
							case TYPEMODEL:
								$mime_type = "model";
								break;
							default:
								$mime_type = "unknown";
								// hmmm....
						}
						$this->structure[$part_no]["type"] = $mime_type."/". strtolower($this_part->subtype);
					} 
					else 
					{
						// disposition can also be used for images in HTML (Inline)
					}
				}
				else
				{
					// Not an attachment, lets see what this part is...
					#print "Type: ".$this_part->type."<br>";
					switch ($this_part->type) 
					{
						case TYPETEXT:
							$mime_type = "text";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$this->structure[$part_no]["name"]	= lang("unknown");
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if (strtolower($param->attribute) == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						
						case TYPEMULTIPART:
							$mime_type = "multipart";
							#print "found $mime_type<br>";
							// Hey, why not use this function to deal with all the parts
							// of this multipart part :)
							for ($i = 0; $i < count($this_part->parts); $i++) 
							{
								if ($part_no != "") 
								{
									$part_no = $part_no.".";
								}
								$this->structure[$part_no.($i + 1)]['encoding']	= $this_part->encoding;
								$this->structure[$part_no.($i + 1)]['size']	= $this_part->bytes;
								$this->structure[$part_no.($i + 1)]['pid']	= $part_no.($i + 1);
								$this->structure[$part_no.($i + 1)]["type"]	= $mime_type."/". strtolower($this_part->subtype);
								for ($i = 0; $i < count($this_part->parts); $i++) 
								{
									$this->parse2($this_part->parts[$i], $part_no.($i + 1));
								}
							}
							break;
						case TYPEMESSAGE:
							$mime_type = "message";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$att_name = "unknown";
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if ($param->attribute == "NAME" ||
									$param->attribute == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						case TYPEAPPLICATION:
							$mime_type = "application";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$att_name = "unknown";
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if ($param->attribute == "NAME" ||
									$param->attribute == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						case TYPEAUDIO:
							$mime_type = "audio";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$att_name = "unknown";
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if ($param->attribute == "NAME" ||
									$param->attribute == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						case TYPEIMAGE:
							$mime_type = "image";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$att_name = "unknown";
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if ($param->attribute == "NAME" ||
									$param->attribute == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						case TYPEVIDEO:
							$mime_type = "video";
							$this->structure[$part_no]['encoding']	= $this_part->encoding;
							$this->structure[$part_no]['size']	= $this_part->bytes;
							$this->structure[$part_no]['pid']	= $part_no;
							$this->structure[$part_no]["type"]	= $mime_type."/". strtolower($this_part->subtype);
							$att_name = "unknown";
							for ($lcv = 0; $lcv < count($this_part->parameters); $lcv++) 
							{
								$param = $this_part->parameters[$lcv];
								if ($param->attribute == "NAME" ||
									$param->attribute == "name") 
								{
									$this->structure[$part_no]["name"] = $param->value;
									break;
								}
							}
							break;
						case TYPEMODEL:
							$mime_type = "model";
							break;
						default:
							$mime_type = "unknown";
							// hmmm....
					}
					$full_mime_type = $mime_type."/".$this_part->subtype;
					
					// Decide what you what to do with this part
					// If you want to show it, figure out the encoding and echo away
					switch ($this_part->encoding) 
					{
						case ENCBASE64:
							// use imap_base64 to decode
							break;
						case ENCQUOTEDPRINTABLE:
							// use imap_qprint to decode
							break;
						case ENCOTHER:
							// not sure if this needs decoding at all
							break;
						default:
							// it is either not encoded or we don't know about it
					}
				}
		}
		
		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['phpgw']->session->appsession('session_data');
		}
		
		function saveFilter($_formData)
		{
			if(!empty($_formData['from']))
				$data['from']	= $_formData['from'];
			if(!empty($_formData['to']))
				$data['to']	= $_formData['to'];
			if(!empty($_formData['subject']))
				$data['subject']= $_formData['subject'];
			if($_formData['filterActive'] == "true")
			{
				$data['filterActive']= "true";
			}

			$this->sessionData['filter'] = $data;
			$this->saveSessionData();
		}
		
		function saveSessionData()
		{
			$GLOBALS['phpgw']->session->appsession('session_data','',$this->sessionData);
		}
		
		function subscribe($_folderName, $_status)
		{
			#$this->mailPreferences['imapServerAddress']
			#$this->mailPreferences['imapPort'],
			
			$folderName = imap_utf7_encode($_folderName);
			$folderName = "{".$this->mailPreferences['imapServerAddress'].":".$this->mailPreferences['imapPort']."}".$folderName;
			
			if($_status == 'unsubscribe')
			{
				return imap_unsubscribe($this->mbox,$folderName);
			}
			else
			{
				return imap_subscribe($this->mbox,$folderName);
			}
		}
		
		function toggleFilter()
		{
			if($this->sessionData['filter']['filterActive'] == 'true')
			{
				$this->sessionData['filter']['filterActive'] = 'false';
			}
			else
			{
				$this->sessionData['filter']['filterActive'] = 'true';
			}
			$this->saveSessionData();
		}
		
		function validate_email($_emailAddress)
		{
			if($val != "")
			{
				$pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
				if(preg_match($pattern, $val))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
?>