<?php
	/***************************************************************************\
	* eGroupWare - FeLaMiMail                                                   *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/

	/* $Id$ */

	/**
	* the core logic of FeLaMiMail
	*
	* This class contains all logic of FeLaMiMail.
	* @package FeLaMiMail
	* @author Lars Kneschke
	* @version 1.35
	* @copyright Lars Kneschke 2002,2003,2004
	* @license http://opensource.org/licenses/gpl-license.php GPL
	*/
	class bofelamimail
	{
		var $public_functions = array
		(
			'flagMessages'		=> True,
		);

		var $mbox;		// the mailbox identifier any function should use

		// define some constants
		// message types
		var $type = array("text", "multipart", "message", "application", "audio", "image", "video", "other");
		
		// message encodings
		var $encoding = array("7bit", "8bit", "binary", "base64", "quoted-printable", "other");
		
		// set to true, if php is compiled with multi byte string support
		var $mbAvailable = FALSE;

		// what type of mimeTypes do we want from the body(text/html, text/plain)
		var $htmlOptions;

		var $sessionData;

		function bofelamimail($_displayCharset='iso-8859-1')
		{
			$this->restoreSessionData();

			// FIXME: this->foldername seems to be unused
			//$this->foldername	= $this->sessionData['mailbox'];
			$this->accountid	= $GLOBALS['egw_info']['user']['account_id'];
			
			$this->bopreferences	=& CreateObject('felamimail.bopreferences');
			$this->sofelamimail	=& CreateObject('felamimail.sofelamimail');
			$this->botranslation	=& CreateObject('phpgwapi.translation');
			
			$this->mailPreferences	= $this->bopreferences->getPreferences();
			$this->icServer = $this->mailPreferences->getIncomingServer(0);
			$this->ogServer = $this->mailPreferences->getOutgoingServer(0);

			#_debug_array($this->mailPreferences);
			$this->imapBaseDir	= '';

			$this->displayCharset	= $_displayCharset;
			
			// set some defaults
			//if(count($this->sessionData) == 0) // regis (for me PHP4.1.3it's not working)
			if(empty($this->sessionData))
			{
				// this should be under user preferences
				// sessionData empty
				// no filter active
				$this->sessionData['activeFilter']	= "-1";
				// default mailbox INBOX
				$this->sessionData['mailbox']		= "INBOX";
				// default start message
				$this->sessionData['startMessage']	= 1;
				// default mailbox for preferences pages
				$this->sessionData['preferences']['mailbox']	= "INBOX";
				// default sorting
				$this->sessionData['sort']	= $GLOBALS['egw_info']['user']['preferences']['felamimail']['sortOrder'];
				$this->saveSessionData();
			}
			
			if (function_exists('mb_convert_encoding')) $this->mbAvailable = TRUE;

			$this->htmlOptions	= $this->mailPreferences->preferences['htmlOptions'];
		}
		
		function addACL($_folderName, $_accountName, $_acl)
		{
			imap_setacl($this->mbox, $_folderName, $_accountName, $_acl);
			
			return TRUE;
		}
		
		/**
		* hook to add account
		*
		* this function is a wrapper function for emailadmin
		*
		* @param _hookValues contains the hook values as array
		* @returns nothing
		*/
		function addAccount($_hookValues) {
			$icServer = $this->mailPreferences->getIncomingServer(0);
			if(is_a($icServer,'defaultimap')) {
				$icServer->addAccount($_hookValues);
			}

			$ogServer = $this->mailPreferences->getOutgoingServer(0);
			if(is_a($ogServer,'defaultsmtp')) {
				$ogServer->addAccount($_hookValues);
			}
		}
		
		function adminMenu()
		{
 			if ($GLOBALS['egw_info']['server']['account_repository'] == "ldap")
			{
									$data = Array
							(
					'description'   => 'email settings',
					'url'           => '/index.php',
					'extradata'     => 'menuaction=emailadmin.uiuserdata.editUserData'
				);
			
				//Do not modify below this line
				global $menuData;
			
				$menuData[] = $data;
			}
		}
		
		function appendMessage($_folderName, $_header, $_body, $_flags)
		{
			$imapServer =& $this->mailPreferences->getIncomingServer(0);
			#print "<pre>$_header.$_body</pre>";
			$mailboxString = $imapServer->getMailboxString($_folderName);
			$header = str_replace("\n","\r\n",$_header);
			$body   = str_replace("\n","\r\n",$_body);
			$result = @imap_append($this->mbox, $mailboxString, "$header"."$body", $_flags);
			#print imap_last_error();exit;
			return $result;
		}
		
		function closeConnection()
		{
			if(is_resource($this->mbox)) {
				imap_close($this->mbox);
			}
		}
		
		function compressFolder($_folderName = false)
		{
			$folderName	= ($_folderName ? $_folderName : $this->sessionData['mailbox']);
			$deleteOptions	= $GLOBALS['egw_info']['user']['preferences']['felamimail']['deleteOptions'];
			$trashFolder	= $GLOBALS['egw_info']['user']['preferences']['felamimail']['trashFolder'];
			
			$this->reopen($folderName);

			if($folderName == $trashFolder && $deleteOptions == "move_to_trash") {
				$imapServer =& $this->mailPreferences->getIncomingServer(0);
				$mailboxString = $imapServer->getMailboxString($folderName);
				$status = imap_status ($this->mbox, $mailboxString, SA_ALL);
				$numberOfMessages = $status->messages;
				$msgList = "1:$numberOfMessages";
				imap_delete($this->mbox, $msgList);
				imap_expunge($this->mbox);

				$caching =& CreateObject('felamimail.bocaching',
					$imapServer->host,
					$imapServer->username,
					$folderName);
				$caching->clearCache($folderName);
			} else {
				// delete all messages in the current folder which have the deleted flag set 
				imap_expunge($this->mbox);
				$this->updateCache($folderName);				
			}
		}
		
		function decodeFolderName($_folderName)
		{
			if($this->mbAvailable)
			{
				return mb_convert_encoding( $_folderName, $this->displayCharset, "UTF7-IMAP");
			}
			
			// if not
			return @imap_utf7_decode($_folderName);
		}

		function decodeMimePart($_mimeMessage, $_encoding) {
			#// MS-Outlookbug workaround (don't break links)
			#$mimeMessage = preg_replace("!((http(s?)://)|((www|ftp)\.))(([^\n\t\r]+)([=](\r)?\n))+!i", 
			#		"$1$7", 
			#		$_mimeMessage);

			// decode the file ...
			switch ($_encoding) 
			{
				case ENCBASE64:
					// use imap_base64 to decode
					return imap_base64($_mimeMessage);
					break;
				case ENCQUOTEDPRINTABLE:
					// use imap_qprint to decode
					return quoted_printable_decode($_mimeMessage);
					break;
				case ENCOTHER:
					// not sure if this needs decoding at all
					#break;
				default:
					// it is either not encoded or we don't know about it
					return $_mimeMessage;
					break;
			}
		}

		function decode_header($_string)
		{
			$newString = '';

			$string = preg_replace('/\?=\s+=\?/', '?= =?', $_string);

			$elements=imap_mime_header_decode($string);

			foreach((array)$elements as $element) {
				if ($element->charset == 'default')
					$element->charset = 'iso-8859-1';
				$tempString = $this->botranslation->convert($element->text,$element->charset);
				$newString .= $tempString;
			}
			return $newString;
		}
		
		function deleteAccount($_hookValues)
		{
			$icServer = $this->mailPreferences->getIncomingServer(0);
			if(is_a($icServer,'defaultimap')) {
				$icServer->deleteAccount($_hookValues);
			}

			$ogServer = $this->mailPreferences->getOutgoingServer(0);
			if(is_a($ogServer,'defaultsmtp')) {
				$ogServer->deleteAccount($_hookValues);
			}
		}
		
		function deleteFolder($_folderName) {
			if(is_a($this->icServer, 'defaultimap')) {
				$folderName = $this->icServer->getMailboxString($_folderName);
				@imap_unsubscribe($this->mbox, $folderName);
				$result = imap_deletemailbox($this->mbox, $folderName);
				return $result;
			}
			
			return false;
		}

		function deleteMessages($_messageUID) {
			$msglist = '';
			
			$imapServer =& $this->mailPreferences->getIncomingServer(0);
			$caching =& CreateObject('felamimail.bocaching',
					$imapServer->host,
					$imapServer->username,
					$this->sessionData['mailbox']);

			foreach($_messageUID as $key => $value) {
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}

			$deleteOptions  = $this->mailPreferences->preferences['deleteOptions'];
                        $trashFolder    = $this->mailPreferences->preferences['trashFolder'];

			if($this->sessionData['mailbox'] == $trashFolder && $deleteOptions == "move_to_trash") {
				$deleteOptions = "remove_immediately";
			}
			$this->reopen($this->sessionData['mailbox']);
			
			switch($deleteOptions) {
				case "move_to_trash":
					if(!empty($trashFolder)) {
						if (imap_mail_move ($this->mbox, $msglist, $this->encodeFolderName($trashFolder), CP_UID)) {
							imap_expunge($this->mbox);
							reset($_messageUID);
							while(list($key, $value) = each($_messageUID)) {
								$caching->removeFromCache($value);
							}
						} else {
							//print imap_last_error()."<br>";
							error_log(imap_last_error());
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
		
		function encodeFolderName($_folderName)
		{
			if($this->mbAvailable)
			{
				return mb_convert_encoding( $_folderName, "UTF7-IMAP", $this->displayCharset );
			}
			
			// if not
			return imap_utf7_encode($_folderName);
		}

		function encodeHeader($_string, $_encoding='q')
		{
			switch($_encoding)
			{
				case "q":
					if(!preg_match("/[\x80-\xFF]/",$_string))
					{
						// nothing to quote, only 7 bit ascii
						return $_string;
					}
					
					$string = imap_8bit($_string);
					$stringParts = explode("=\r\n",$string);
					while(list($key,$value) = each($stringParts))
					{
						if(!empty($retString)) $retString .= " ";
						$value = str_replace(" ","_",$value);
						// imap_8bit does not convert "?"
						// it does not need, but it should
						$value = str_replace("?","=3F",$value);
						$retString .= "=?".strtoupper($this->displayCharset). "?Q?". $value. "?=";
					}
					#exit;
					return $retString;
					break;
				default:
					return $_string;
			}
		}

		function flagMessages($_flag, $_messageUID)
		{
			if(!is_array($_messageUID))
				return false;
			
			foreach($_messageUID as $value)
			{
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}

			$this->reopen($this->sessionData['mailbox']);
			
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
			
			#error_log(imap_last_error());
			#print "Result: $result<br>";
		}
		
		// this function is based on a on "Building A PHP-Based Mail Client"
		// http://www.devshed.com
		// fetch a specific attachment from a message
		function getAttachment($_uid, $_partID)
		{
			// parse message structure
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			$sections = array();
			$this->parseMessage($sections, $structure, $_partID);
			
			#_debug_array($sections);
			
			$type 		= $sections[$_partID]["mimeType"];
			$encoding 	= $sections[$_partID]["encoding"];
			$filename 	= $this->decode_header($sections[$_partID]["name"]);
			
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
				'type'		=> $type, 
				'encoding'	=> $encoding, 
				'filename'	=> $filename, 
				'attachment'	=> $attachment
				);
		}
		
		// this function is based on a on "Building A PHP-Based Mail Client"
		// http://www.devshed.com
		// fetch a specific attachment from a message
		function getAttachmentByCID($_uid, $_cid)
		{
			$partID = false;
			
			$attachments = $this->getMessageAttachments($_uid);
			foreach($attachments as $attachment) {
				if($attachment['id'] == "<$_cid>") {
					$partID = $attachment['partID'];
					break;
				}
			}

			if(!$partID) {
				return false;
			}

			// parse message structure
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			$sections = array();
			$this->parseMessage($sections, $structure, $partID);
			
			#_debug_array($sections);
			
			$type 		= $sections[$partID]["mimeType"];
			$encoding 	= $sections[$partID]["encoding"];
			$filename 	= $this->decode_header($sections[$partID]["name"]);
			
			$attachment = imap_fetchbody($this->mbox, $_uid, $partID, FT_UID);
			
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
				'type'		=> $type, 
				'encoding'	=> $encoding, 
				'filename'	=> $filename, 
				'attachment'	=> $attachment
				);
		}
		
		function getEMailProfile()
		{
			$config =& CreateObject('phpgwapi.config','felamimail');
			$config->read_repository();
			$felamimailConfig = $config->config_data;
			
			#_debug_array($felamimailConfig);
			
			if(!isset($felamimailConfig['profileID']))
			{
				return -1;
			}
			else
			{
				return intval($felamimailConfig['profileID']);
			}
		}

		function getFolderStatus($_folderName)
		{
			$retValue = array();
			$retValue['subscribed'] = false;
			if(!$icServer = $this->mailPreferences->getIncomingServer(0))
			{
				return false;
			}
			// now we have the keys as values
			$mailboxString = $icServer->getMailboxString($_folderName);

			if(imap_getsubscribed($this->mbox, $mailboxString, $mailboxString))
			{
				$retValue['subscribed'] = true;
			}

			if(!$folderInfo = imap_getmailboxes($this->mbox, $mailboxString, $mailboxString)) {
				// folder does not exist
				error_log("folder ($mailboxString) does not exist");
				return false;
			}

			$retValue['delimiter']	= (!empty($folderInfo[0]->delimiter) ? $folderInfo[0]->delimiter : $icServer->mailboxDelimiter);
			$retValue['attributes']	= $folderInfo[0]->attributes;
			$shortNameParts = explode($retValue['delimiter'], $_folderName);
			$retValue['shortName']	= array_pop($shortNameParts);
			
			$folderStatus = imap_status($this->mbox,$mailboxString,SA_ALL);
			if($folderStatus)
			{
				// merge a array and object to a array
				$retValue = array_merge($retValue,(array)$folderStatus);
			}

			return $retValue;
		}
		
		/**
		* get IMAP folder objects
		*
		* returns an array of IMAP folder objects. Put INBOX folder in first
		* position. Preserves the folder seperator for later use. The returned
		* array is indexed using the foldername.
		*
		* THIS CODE REALLY NEEDS TO BE CLEANED UP SOMEDAY!!! (Lars)
		*
		* @param _subscribedOnly boolean get subscribed or all folders
		* @param _getCounters    boolean get get messages counters
		*
		* @returns array with folder objects. eg.: INBOX => {inbox object}
		*/		
		function getFolderObjects($_subscribedOnly=false, $_getCounters=false) 
		{
			#print LATT_NOINFERIORS."<bR>";
			#print LATT_NOSELECT."<br>";
			#$_subscribedOnly=false;
			#$list = array();
			$isUWIMAP = false;
			
			if (!is_resource($this->mbox)) { 
				return $false;
			} 
			
			if(!$icServer = $this->mailPreferences->getIncomingServer(0)) {
				return $false;
			}

			$inboxData = new stdClass;
			$inboxData->name = $icServer->getMailboxString('INBOX');
			$inboxData->delimiter = $icServer->getDelimiter();
			$inboxData->subscribed = true;
			#$inboxData->attributes = 64;
			$folders = array('INBOX' => $inboxData);
			#_debug_array($folders);

			#print basename(__FILE__) .' '. __LINE__ ."<br>";
			$nameSpace = $icServer->getNameSpace(IMAP_NAMESPACE_ALL);
			#krsort($nameSpace);
			#_debug_array($nameSpace);
			#_debug_array($icServer->mailboxPrefix);
			
			// uw imap does not return the attribute of a folder, when requesting subscribed folders only
			$mailboxString = $icServer->getMailboxString();
			if(isset($nameSpace['#mh/'])) {
				// detected uw-imap! the most worst imap server ever!!!!
				// we support only the personal namespace on uwimap
				$foldersNameSpace['personal']['subscribed'] = imap_getsubscribed($this->mbox, $mailboxString, $icServer->mailboxPrefix.'/*');

				foreach($foldersNameSpace['personal']['subscribed'] as $id => $folderInfo) {
					$shortName = preg_replace("/{.*}/",'',$folderInfo->name);
					$mailBoxInfo = imap_getmailboxes($this->mbox,$mailboxString, $shortName);
					if(is_a($mailBoxInfo[0], 'stdClass')) {
						$foldersNameSpace['personal']['subscribed'][$id] = $mailBoxInfo[0];
					}
				}
				
				if(!$_subscribedOnly) {
					$foldersNameSpace['personal']['all'] = imap_getmailboxes($this->mbox, $mailboxString, $icServer->mailboxPrefix.'/*');
				}
				$foldersNameSpace['personal']['prefix'] = '';
				$foldersNameSpace['personal']['delimiter'] = '/';
				
				$isUWIMAP = TRUE;
				
			} else { foreach($nameSpace as $singleNameSpace) {
				// some imap servers support multiple prefixes for the some namespace type. only use the first.
				if(isset($foldersNameSpace[$singleNameSpace['type']])) continue;
				#_debug_array($singleNameSpace);
				
				$foldersNameSpace[$singleNameSpace['type']]['subscribed'] = imap_getsubscribed($this->mbox, $mailboxString, $singleNameSpace['name'].'*');
				if(!$_subscribedOnly) {
					$foldersNameSpace[$singleNameSpace['type']]['all'] = imap_getmailboxes($this->mbox, $mailboxString, $singleNameSpace['name'].'*');
				}
				$foldersNameSpace[$singleNameSpace['type']]['prefix'] = $singleNameSpace['name'];
				$foldersNameSpace[$singleNameSpace['type']]['delimiter'] = $singleNameSpace['delimiter'];
			}}

			$personalInboxFolders	= array();
			$personalOtherFolders	= array();
			$otherUsersFolders	= array();
			$sharedFolders		= array();
			$foundFolders		= array($inboxData->name => true);

			foreach($foldersNameSpace as $nameSpaceType => $nameSpaceFolders) {
				if((is_array($nameSpaceFolders['subscribed']) && $_subscribedOnly) ||
					(is_array($nameSpaceFolders['all']) && !$_subscribedOnly) ) {
					#print "111: $nameSpaceType<br>";
					#_debug_array($nameSpaceFolders);
					#print "foundFolders<br>";
					#_debug_array($foundFolders);
#######################################
					// create a array containing all subscribed folders for this namespace
					if(!$_subscribedOnly) {
						foreach((array)$nameSpaceFolders['subscribed'] as $folderInfo) {
							$subscribedFolders[$folderInfo->name] = TRUE;
						}
					}
					$folderList = ($_subscribedOnly ? $nameSpaceFolders['subscribed'] : $nameSpaceFolders['all']);
					#_debug_array($folderList);
					foreach($folderList as $key => $val) {
						// we got this folder already
						if($foundFolders[$val->name]) continue;
						
						// is this folder subscribed
						$val->subscribed = ($_subscribedOnly ? TRUE : (bool)$subscribedFolders[$val->name]);

						if(empty($val->delimiter)) {
							$val->delimiter = $icServer->getDelimiter();
						}
					
						if($isUWIMAP && !empty($icServer->mailboxPrefix)) {
							$pregMailboxPrefix = preg_quote($icServer->mailboxPrefix,'/');
							$pregMailboxPrefixFull = preg_quote($icServer->mailboxPrefix.$val->delimiter,'/');
							$pregMailboxDelimiter = preg_quote($val->delimiter,'/');

							$search = array("/$pregMailboxPrefixFull/", "/$pregMailboxPrefix/");
							$replace = array('', '');
							$pregMailboxDelimiter = preg_quote($val->delimiter,'/');
							$folderNameIMAP = $this->decodeFolderName(preg_replace($search, $replace, $val->name));
						} else {
							$folderNameIMAP = $this->decodeFolderName($val->name);
						}
						$folderNameIMAP = preg_replace("/{.*}/",'',$folderNameIMAP);
						
						#print "FOLDERNAMEIMAP: $folderNameIMAP<br>";

						if(empty($folderNameIMAP)) 
							continue;

						$folderNameIMAP = preg_replace("/". $pregMailboxDelimiter. "$/", '', $folderNameIMAP);
						$val->name = preg_replace("/". $pregMailboxDelimiter. "$/", '', $val->name);
					
						if($_getCounters == true) {
							$val->counter = imap_status($this->mbox,$val->name,SA_ALL);
						}
						
						switch($nameSpaceType) {
							case 'personal':
								$inboxPos = strpos($folderNameIMAP,'INBOX'); 
								if ($inboxPos !== false AND $inboxPos == 0) { 
									$personalInboxFolders["$folderNameIMAP"] = $val;
								} else { 
									$personalOtherFolders["$folderNameIMAP"] = $val;
								}
								break;
								
							case 'others':
								$otherUsersFolders["$folderNameIMAP"] = $val;
								break;
								
							case 'shared':
								$sharedFolders["$folderNameIMAP"] = $val;
								break;
						}
						$foundFolders[$val->name] = TRUE;
					}
########################################
				}
			}
			#print "<hr>";
			#_debug_array($folders);
			#_debug_array($personalInboxFolders);
			#_debug_array($personalOtherFolders);
			ksort($personalInboxFolders,SORT_STRING);
			ksort($personalOtherFolders,SORT_STRING);
			ksort($otherUsersFolders,SORT_STRING);
			ksort($sharedFolders,SORT_STRING);
			$__folders = array(
				'personal'	=> $folders + $personalInboxFolders + $personalOtherFolders ,
				'other'		=> $otherUsersFolders ,
				'shared'	=> $sharedFolders ,
			);
			#_debug_array($__folders);                                                            
			$folders = $folders + $personalInboxFolders + $personalOtherFolders + $otherUsersFolders + $sharedFolders;
			return $folders;




#################################
# old code
################################
			$inboxMailboxString = $icServer->getMailboxString('INBOX');

			#print "<br><br><br><br><br>INBOX: $mailboxString<br> $inboxMailboxString<br>";

			// we always fetch the subscribed first, to be able to detect subscribed state
			// and we can not use the result from imap_getsubscribed as this function does not return correct values
			// for the attribbute value when using UW IMAP
			$subList = (array)imap_getsubscribed($this->mbox,$mailboxString, '*');
			#_debug_array($subList);
			foreach($subList as $folderInfo)
			{
				$subscribedFolders[$folderInfo->name] = true;
				$tmpList = imap_getmailboxes($this->mbox,$folderInfo->name,$folderInfo->name);
				if(is_object($tmpList[0]))
					$list[] = $tmpList[0];
			}
			
			#if(!$_subscribedOnly) {
			#	$list = imap_getmailboxes($this->mbox,$mailboxString, "*");
			#}

			// make sure that we always return the INBOX
			// on some IMAP Servers you can NOT subscribe to the INBOX
			// to avoid problems, we always fetch the INBOX separatly
			$inboxList = imap_getmailboxes($this->mbox,$inboxMailboxString,'%');
			foreach($inboxList as $folderInfo)
			{
				if($folderInfo->name == $inboxMailboxString)
				{
					$list[] = $folderInfo;
					$subscribedFolders[$inboxMailboxString] = true;
					break;
				}
			}

			if(is_array($list))
			{	
				#reset($list); 
				$inboxFolders = array();
				$otherFolders = array();
				#while (list($key, $val) = each($list))
				foreach($list as $key => $val) {
					#_debug_array($val);
					if($subscribedFolders[$val->name])
						$val->subscribed = TRUE;
					else
						$val->subscribed = FALSE;

					if(empty($val->delimiter))
						$val->delimiter = $icServer->mailboxDelimiter;
					
					if(!empty($icServer->mailboxPrefix)) {
						$pregMailboxPrefix = preg_quote($icServer->mailboxPrefix,'/');
						$pregMailboxPrefixFull = preg_quote($icServer->mailboxPrefix.$val->delimiter,'/');
						$pregMailboxDelimiter = preg_quote($val->delimiter,'/');

						$search = array("/$pregMailboxPrefixFull/", "/$pregMailboxPrefix/");
						$replace = array('', '');
						$pregMailboxDelimiter = preg_quote($val->delimiter,'/');
						$folderNameIMAP = $this->decodeFolderName(preg_replace($search, $replace, $val->name));
					} else {
						$folderNameIMAP = $this->decodeFolderName($val->name);
					}
					$folderNameIMAP = preg_replace("/{.*}/",'',$folderNameIMAP);

					if(empty($folderNameIMAP)) 
						continue;

					$folderNameIMAP = preg_replace("/". $pregMailboxDelimiter. "$/", '', $folderNameIMAP);
					$val->name = preg_replace("/". $pregMailboxDelimiter. "$/", '', $val->name);
					
					if($_getCounters == true) {
						$val->counter = imap_status($this->mbox,$val->name,SA_ALL);
					}
					
					$inboxPos = strpos($folderNameIMAP,'INBOX'); 
					if ($inboxPos !== false AND $inboxPos == 0) { 
						$inboxFolders["$folderNameIMAP"] = $val;
					} else { 
						$otherFolders["$folderNameIMAP"] = $val;
					} 
				}
				ksort($inboxFolders,SORT_STRING); 
				ksort($otherFolders,SORT_STRING); 
				$folders = $inboxFolders + $otherFolders;

				#_debug_array($folders);
				return $folders; 
			}
			else
			{
					if($_subscribedOnly == 'true' &&
						is_array($inboxName = imap_list($this->mbox,$mailboxString,'INBOX'))) {
						$inboxData = imap_getmailboxes($this->mbox,$mailboxString,'INBOX');
						$folders['INBOX'] = $inboxData[0];
					
						return $folders;
					}
			}
		}
		
		function getMimePartCharset($_mimePartObject) {
			$charSet = 'ISO-8859-1';

			if(is_array($_mimePartObject->parameters)) {
				foreach($_mimePartObject->parameters as $parameters) {
					if(strtolower($parameters->attribute) == 'charset') {
						$charSet = $parameters->value;
					}
				}
			}
			
			return $charSet;
		}
		
		function getMultipartAlternative($_uid, $_parentPartID, $_structure, $_htmlMode) {
				// a multipart/alternative has exactly 2 parts (text and html  OR  text and something else)
				$i=1;
				$partText;
				$partHTML;
				$parentPartID = ($_parentPartID != '') ? $_parentPartID.'.' : $_parentPartID;

				foreach($_structure->parts as $mimePart) {
					if($mimePart->type == TYPETEXT && $mimePart->subtype == 'PLAIN' && $mimePart->bytes > 0) {
						$partText = array(
							'partID'	=> $parentPartID.$i ,
							'charset'	=> $this->getMimePartCharset($mimePart) ,
							'encoding'	=> $mimePart->encoding ,
						);
					} elseif ($mimePart->type == TYPETEXT && $mimePart->subtype == 'HTML' && $mimePart->bytes > 0) {
						$partHTML = array(
							'partID'	=> $parentPartID.$i ,
							'charset'	=> $this->getMimePartCharset($mimePart) ,
							'encoding'	=> $mimePart->encoding ,
						);
					} elseif ($mimePart->type == TYPEMULTIPART && $mimePart->subtype == 'RELATED' && is_object($mimePart->parts[0])) {
						$mimePart = $mimePart->parts[0];
						$parentPartID = $parentPartID.$i;
						if($mimePart->type == TYPETEXT && $mimePart->subtype == 'PLAIN' && $mimePart->bytes > 0) {
							$partText = array(
								'partID'	=> $parentPartID.'.1',
								'charset'	=> $this->getMimePartCharset($mimePart) ,
								'encoding'	=> $mimePart->encoding ,
							);
						} elseif ($mimePart->type == TYPETEXT && $mimePart->subtype == 'HTML' && $mimePart->bytes > 0) {
							$partHTML = array(
								'partID'	=> $parentPartID.'.1',
								'charset'	=> $this->getMimePartCharset($mimePart) ,
								'encoding'	=> $mimePart->encoding ,
							);
						}					
					}
					$i++;
				}

				switch($_htmlMode) {
					case 'always_display':
						if(is_array($partHTML)) {
							$partContent = $this->decodeMimePart(
								imap_fetchbody($this->mbox, $_uid, $partHTML['partID'], FT_UID) ,
								$partHTML['encoding']
							);
							$bodyPart[] = array(
								'body'		=> $partContent ,
								'mimeType'	=> 'text/html' ,
								'charSet'	=> $partHTML['charset']
							);
						}
						break;
					case 'only_if_no_text':
						if(is_array($partHTML) && !is_array($partText)) {
							$partContent = $this->decodeMimePart(
								imap_fetchbody($this->mbox, $_uid, $partHTML['partID'], FT_UID) ,
								$partHTML['encoding']
							);
							$bodyPart[] = array(
								'body'		=> $partContent ,
								'mimeType'	=> 'text/html' ,
								'charSet'	=> $partHTML['charset']
							);
						} elseif (is_array($partText)) {
							$partContent = $this->decodeMimePart(
								imap_fetchbody($this->mbox, $_uid, $partText['partID'], FT_UID) ,
								$partText['encoding']
							);

							$bodyPart[] = array(
								'body'		=> $partContent ,
								'mimeType'	=> 'text/plain' ,
								'charSet'	=> $partText['charset']
							);
						}
						break;
						
					default:
						if (is_array($partText)) {
							$partContent = $this->decodeMimePart(
								imap_fetchbody($this->mbox, $_uid, $partText['partID'], FT_UID) ,
								$partText['encoding']
							);

							$bodyPart[] = array(
								'body'		=> $partContent ,
								'mimeType'	=> 'text/plain' ,
								'charSet'	=> $partText['charset']
							);
						}
						break;
				}
				
				return $bodyPart;
				
		}
		
		#function microtime_float()
		#{
		#	list($usec, $sec) = explode(" ", microtime());
		#	return ((float)$usec + (float)$sec);
		#}
		
		function updateCache($_folderName)
		{
			if(!$icServer = $this->mailPreferences->getIncomingServer(0))
			{
				return false;
			}

			$caching =& CreateObject('felamimail.bocaching',
					$icServer->host,
					$icServer->username,
					$_folderName);

			$mailboxString = $icServer->getMailboxString($_folderName);
			$status = imap_status ($this->mbox, $mailboxString, SA_ALL);

			$this->reopen($_folderName);

			$cachedStatus = $caching->getImapStatus();
			// no data cached yet?
			// get all message informations from the imap server for this folder
			if ($cachedStatus['uidnext'] == 0 || $cachedStatus['uidnext'] > $status->uidnext)
			{
				//drop all cached info for this folder
				$caching->clearCache();
				#print "nix gecached!!<br>";
				#print "current UIDnext :".$cachedStatus['uidnext']."<br>";
				# "new UIDnext :".$status->uidnext."<br>";
				// (regis) seems to be necessary to reopen...
				for($i=1; $i<=$status->messages; $i++)
				{
					@set_time_limit();// FIXME: beware no effect if in PHP safe_mode
					$messageData['uid'] = imap_uid($this->mbox, $i);
					$header = imap_headerinfo($this->mbox, $i);

					// parse structure to see if attachments exist
					// display icon if so
					$structure = imap_fetchstructure($this->mbox, $i);
					$sections = array();
					$this->parseMessage($sections, $structure);
					
					$messageData['date']		= $header->udate;
					$messageData['subject']		= $this->decode_header($header->subject);
					if($header->to[0]->mailbox != 'undisclosed-recipients') {
						$messageData['to_name']		= ($header->to[0]->personal ? $this->decode_header($header->to[0]->personal) : '');
						$messageData['to_address']	= $header->to[0]->mailbox.(!empty($header->to[0]->host) ? '@'.$header->to[0]->host : '');
					} else {
						$messageData['to_name']		= '';
						$messageData['to_address']	= '';
					}
					$messageData['sender_name']	= ($header->from[0]->personal ? $this->decode_header($header->from[0]->personal) : '');
					$messageData['sender_address']	= $header->from[0]->mailbox."@".$header->from[0]->host;
					$messageData['size']		= $header->Size;

					$messageData['attachments']     = "false";

					foreach($sections as $key => $value)
					{
						if($value['type'] == 'attachment')
						{
							$messageData['attachments']	= "true";
							break;
						}
					}
				
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
				#return $uidRange;
				$newHeaders = imap_fetch_overview($this->mbox,$uidRange,FT_UID);
				$countNewHeaders = count($newHeaders);
				for($i=0; $i<$countNewHeaders; $i++)
				{
					$messageData['uid'] = $newHeaders[$i]->uid;
					$header = imap_headerinfo($this->mbox, $newHeaders[$i]->msgno);
					// parse structure to see if attachments exist
					// display icon if so
					$structure = imap_fetchstructure($this->mbox, $newHeaders[$i]->msgno);
					$sections = array();
					$this->parseMessage($sections, $structure);
				
					$messageData['date'] 		= $header->udate;
					$messageData['subject'] 	= $this->decode_header($header->subject);
					if($header->to[0]->mailbox != 'undisclosed-recipients') {
						$messageData['to_name']		= ($header->to[0]->personal ? $this->decode_header($header->to[0]->personal) : '');
						$messageData['to_address']	= $header->to[0]->mailbox.(!empty($header->to[0]->host) ? '@'.$header->to[0]->host : '');
					} else {
						$messageData['to_name']		= '';
						$messageData['to_address']	= '';
					}
					$messageData['sender_name'] 	= ($header->from[0]->personal ? $this->decode_header($header->from[0]->personal) : '');
					$messageData['sender_address'] 	= $header->from[0]->mailbox."@".$header->from[0]->host;
					$messageData['size'] 		= $header->Size;

					$messageData['attachments']     = "false";
					foreach($sections as $key => $value)
					{
						if($value['type'] == 'attachment')
						{
							$messageData['attachments']	= "true";
							break;
						}
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
			#$displayHeaders = $caching->getHeaders();
			#printf ("this->bofelamimail->getHeaders start: %s Zeile: %d<br>",$this->microtime_float()-$start, __LINE__);
			$dbMessageCounter = $caching->getMessageCounter();
			#printf ("this->bofelamimail->getHeaders start: %s Zeile: %d<br>",$this->microtime_float()-$start, __LINE__);
			#print count($displayHeaders) .' - '.$messageCounter."<br>";
			if ($dbMessageCounter > $status->messages)
			{
				$displayHeaders = $caching->getHeaders();
				$messagesToRemove = count($displayHeaders) - $status->messages;
				foreach((array)$displayHeaders as $displayHeader)
				{
					$header = imap_fetch_overview($this->mbox,$displayHeader['uid'],FT_UID);
					if (count($header[0]) == 0)
					{
						$caching->removeFromCache($displayHeader['uid']);
						$removedMessages++;
					}
					if ($removedMessages == $messagesToRemove) break;
				}
			}

		}
					
		function getHeaders($_startMessage, $_numberOfMessages, $_sort)
		{
			$this->timeCounter = microtime(true);
			if(!$icServer = $this->mailPreferences->getIncomingServer(0))
			{
				return false;
			}
			
			$caching =& CreateObject('felamimail.bocaching',
					$icServer->host,
					$icServer->username,
					$this->sessionData['mailbox']);
			$bofilter =& CreateObject('felamimail.bofilter');
			$transformdate =& CreateObject('felamimail.transformdate');
			//print __LINE__ . ': ' . (microtime(true) - $this->timeCounter) . '<br>';

			$this->updateCache($this->sessionData['mailbox']);
			//print __LINE__ . ': ' . (microtime(true) - $this->timeCounter) . '<br>';

			$filter = $bofilter->getFilter($this->sessionData['activeFilter']);

			if($this->sessionData['mailbox'] == $GLOBALS['egw_info']['user']['preferences']['felamimail']['sentFolder']) {
				$filter['to'] = $filter['from'];
				unset($filter['from']);
			}
			                                                                                        
			if(!$displayHeaders = $caching->getHeaders($_startMessage, $_numberOfMessages, $_sort, $filter)) {
				return false;
			}

			//print __LINE__ . ': ' . (microtime(true) - $this->timeCounter) . '<br>';
			$count=0;

			foreach((array)$displayHeaders as $uid => $headerObject) {
				$sequences[] = $uid;
				$sortOrder[$uid] = $count++;
			}
			$sequence = implode(',', $sequences);

			$count=0;
			//print __LINE__ . ': ' . (microtime(true) - $this->timeCounter) . '<br>';
			$headers = imap_fetch_overview($this->mbox, $sequence, FT_UID);
			//print __LINE__ . ': ' . (microtime(true) - $this->timeCounter) . '<br>';

			foreach((array)$headers as $uid => $headerObject) {
				//_debug_array($headerObject);

				$uid = $headerObject->uid;

				$retValue['header'][$sortOrder[$uid]]['subject'] 	= $displayHeaders[$uid]['subject'];
				$retValue['header'][$sortOrder[$uid]]['sender_name'] 	= $displayHeaders[$uid]['sender_name'];
				$retValue['header'][$sortOrder[$uid]]['sender_address'] = $this->decode_header($displayHeaders[$uid]['sender_address']);
				$retValue['header'][$sortOrder[$uid]]['to_name'] 	= $displayHeaders[$uid]['to_name'];
				$retValue['header'][$sortOrder[$uid]]['to_address'] 	= $this->decode_header($displayHeaders[$uid]['to_address']);
				$retValue['header'][$sortOrder[$uid]]['attachments']	= $displayHeaders[$uid]['attachments'];
				$retValue['header'][$sortOrder[$uid]]['size'] 		= $this->decode_header($headerObject->size);
				
				$timestamp = $displayHeaders[$uid]['date'];
				$timestamp7DaysAgo = 
					mktime(date("H"), date("i"), date("s"), date("m"), date("d")-7, date("Y"));
				$timestampNow = 
					mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

				// date from the future
				if($timestamp > $timestampNow+86400) {
					$retValue['header'][$sortOrder[$uid]]['date'] = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],$timestamp);
				} elseif (date("Y-m-d") == date("Y-m-d",$timestamp)) {
					// email from today, show only time
					$retValue['header'][$sortOrder[$uid]]['date'] = date("H:i:s",$timestamp);
				} elseif($timestamp7DaysAgo < $timestamp) {
					// email from the last 7 days, show only weekday
					$retValue['header'][$sortOrder[$uid]]['date'] = lang(date("l",$timestamp));
					#$retValue['header'][$sortOrder[$uid]]['date'] = date("Y-m-d H:i:s",$timestamp7DaysAgo)." - ".date("Y-m-d",$timestamp);
					$retValue['header'][$sortOrder[$uid]]['date'] = date("H:i:s",$timestamp)."(".lang(date("D",$timestamp)).")";
				} else {
					$retValue['header'][$sortOrder[$uid]]['date'] = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],$timestamp);
				}
				$retValue['header'][$sortOrder[$uid]]['id'] =  $headerObject->msgno;
				$retValue['header'][$sortOrder[$uid]]['uid'] = $displayHeaders[$uid]['uid'];
				$retValue['header'][$sortOrder[$uid]]['recent'] =  $headerObject->recent;
				$retValue['header'][$sortOrder[$uid]]['flagged'] =  $headerObject->flagged;
				$retValue['header'][$sortOrder[$uid]]['answered'] =  $headerObject->answered;
				$retValue['header'][$sortOrder[$uid]]['deleted'] =  $headerObject->deleted;
				$retValue['header'][$sortOrder[$uid]]['seen'] =  $headerObject->seen;
				$retValue['header'][$sortOrder[$uid]]['draft'] =  $headerObject->draft;

				#$retValue['header'][$sortOrder[$uid]]['recent'] = $this->sessionData['mailbox'];
				
				$count++;
			}

			if(is_array($retValue['header'])) {
				ksort($retValue['header']);
				#_debug_array($retValue['header']);
				$retValue['info']['total']	= $caching->getMessageCounter($filter);
				$retValue['info']['first']	= $_startMessage;
				$retValue['info']['last']	= $_startMessage + $count - 1 ;
				return $retValue;
			} else {
				return 0;
			}
		}
		
		function getIMAPACL($_folderName)
		{
			if(!$icServer = $this->mailPreferences->getIncomingServer(0))
			{
				return false;
			}
			
			$acl = array();
			$mailboxString = $icServer->getMailboxString($_folderName);
			if(function_exists('imap_getacl'))
			{
				$acl = imap_getacl ($this->mbox, $_folderName);
			}
			
			return $acl;
		}
		
		function getMailPreferences() {
			return $this->mailPreferences;
		}
		
		function getMessageAttachments($_uid, $_partID='') {
			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			$sections = array();
			$this->parseMessage($sections, $structure, $_partID);
			#if(isset($sections['attachment']) && is_array($sections['attachment']))
			#{
			#	#_debug_array($structure['attachment']);
			#	return $sections['attachment'];
			#}

			$arrayData = array();
			if(count($sections) > 0) {
				foreach($sections as $key => $value) {
					if($value['type'] == 'attachment' && $sections[substr($key,0,-2)]['mimeType'] != "multipart/alternative") {
						$arrayData[] = $value;
					}
				}
				if(count($arrayData) > 0) {
					return $arrayData;
				}
			}
			
			return false;

		}
		
		function getMessageBody($_uid, $_htmlOptions='', $_partID='', $_structure='') {
			#print "UID: $_uid HTML: $_htmlOptions PART: $_partID<br>";
			#print $this->htmlOptions."<br>";
			#require_once('Mail/mimeDecode.php');
			#$messageBody = imap_fetchbody($this->mbox, $_uid, '', FT_UID);
			#print "<pre>".$messageBody."</pre>"; print "<hR>";
			#$decoder = new Mail_mimeDecode($messageBody);
			#$structure = $decoder->decode($params);

			if($_htmlOptions != '')
				$this->htmlOptions = $_htmlOptions; 

			if(is_object($_structure)) {
				$structure = $_structure;
			} else {
				$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
				if($_partID != '') {
					$imapPartIDs = explode('.',$_partID);
					foreach($imapPartIDs as $id) {
						if($structure->type == TYPEMESSAGE && $structure->subtype == 'RFC822') {
							$structure = $structure->parts[0]->parts[$id-1];
						} else {
							$structure = $structure->parts[$id-1];
						}
					}
				}
			}
			#_debug_array($structure);
			switch($structure->type) {
				case TYPEMESSAGE:
					switch($structure->subtype) {
						case 'RFC822':
							$i=1;
							foreach($structure->parts as $part) {
								if($part->type == TYPEMULTIPART && 
								($part->subtype == 'RELATED' || $part->subtype == 'MIXED' || $part->subtype == 'ALTERNATIVE' || $part->subtype == 'REPORT') ) {
									$bodyParts = $this->getMessageBody($_uid, $this->htmlOptions, $_partID, $part);
								} else {
									$bodyParts = $this->getMessageBody($_uid, $this->htmlOptions, $_partID.'.'.$i, $part);
								}
								$i++;
							}
							return $bodyParts;
						
							break;
						case 'DELIVERY-STATUS':
							// only text
							if($_partID == '') $_partID=1;
							$mimePartBody = imap_fetchbody($this->mbox, $_uid, $_partID, FT_UID);
							$bodyPart = array(
								array(
									'body'		=> $this->decodeMimePart($mimePartBody, $structure->encoding),
									'mimeType'	=> 'text/plain',
									'charSet'	=> $this->getMimePartCharset($structure),
								)
							);
							
							return $bodyPart;
						
							break;
					}
					
					break;
					
				case TYPEMULTIPART:
					switch($structure->subtype) {
						case 'ALTERNATIVE':
							return $this->getMultipartAlternative($_uid, $_partID, $structure, $this->htmlOptions);
							
							break;

						default:
							$i = 1;
							$parentPartID = ($_partID != '') ? $_partID.'.' : '';
							$bodyParts = array();
							foreach($structure->parts as $part) {
								#if($part->type == TYPETEXT || $part->type == TYPEMULTIPART || $part->type == TYPEMESSAGE) {
								if($part->type == TYPETEXT || $part->type == TYPEMULTIPART) {
									$bodyParts = array_merge($bodyParts, $this->getMessageBody($_uid, $this->htmlOptions, $parentPartID.$i, $part));
								}
								$i++;
							}
							return $bodyParts;

							break;
					}
					
					break;
					
				case TYPETEXT:
					$bodyPart = array();
					#_debug_array($structure);
					if (($structure->subtype == 'HTML' || $structure->subtype == 'PLAIN') && $structure->disposition != 'ATTACHMENT') {
						if($_partID == '') { 
							$_partID=1;
						}
						$partID = $_partID;
						$mimePartBody = imap_fetchbody($this->mbox, $_uid, $partID, FT_UID);

						$bodyPart = array(
							array(
								'body'		=> $this->decodeMimePart($mimePartBody, $structure->encoding),
								'mimeType'	=> $structure->subtype == 'HTML' ? 'text/html' : 'text/plain',
								'charSet'	=> $this->getMimePartCharset($structure),
							)
						);
					}
	
					return $bodyPart;
					
					break;
					
				default:
					$bodyPart = array(
						array(
							'body'		=> lang('The mimeparser can not parse this message.'),
							'mimeType'	=> 'text/plain',
							'charSet'	=> 'iso-8859-1',
						)
					);
					
					return $bodyPart;
					
					break;
			}
		}

		function getMessageBody_olf($_uid, $_htmlOptions = '', $_partID)
		{
			if($_htmlOptions != '')
				$this->htmlOptions = $_htmlOptions; 

			$structure = imap_fetchstructure($this->mbox, $_uid, FT_UID);
			$sections = array();
			$this->parseMessage($sections, $structure, $_partID);
			#_debug_array($sections);
			
			foreach($sections as $key => $value)
			{
				#print 'parent is: '.$sections[substr($key,0,-2)]['mimeType'].'<br>';
				if($value['type'] == 'body' || $sections[substr($key,0,-2)]['mimeType'] == "multipart/alternative")
				{
					#_debug_array($value);
					// no mime message, only body available
					if($key == 0)
					{
						$newPart	= trim(imap_body($this->mbox, $_uid, FT_UID));
						$encoding	= $structure->encoding;
						
						// find mimetype
						if(strtolower($structure->subtype) == 'html')
						{
							$mimeType = 'text/html';
						}
						else
						{
							$mimeType = 'text/plain';
						}
						
						// find charset
						if($structure->ifparameters)
						{
							foreach($structure->parameters as $value)
							{
								$parameter[strtolower($value->attribute)] = 
									strtolower($value->value);
							}
							$charSet = $parameter['charset'];
						}
					}
					else
					{
						// select which part(text or html) to display from multipart/alternative
						#_debug_array($sections);
						#print $sections['']['mimeType']."<br>";
						if($sections[substr($key,0,-2)]['mimeType'] == "multipart/alternative")
						{
							switch($this->htmlOptions)
							{
								// prefer html part
								// don't display text part
								case 'always_display':
									if($value['mimeType'] == 'text/plain')
										continue 2;
									break;
									
								case 'only_if_no_text':
								default:
									if($value['mimeType'] == 'text/html')
										continue 2;
									break;
							}
						}
						// don't diplay html emails at all
						if($value['mimeType'] == 'text/html' && 
						$this->htmlOptions != 'always_display' &&
						$this->htmlOptions != 'only_if_no_text')
						{
							continue;
						}
						$newPart = imap_fetchbody($this->mbox, $_uid, $value["partID"], FT_UID);
						#if($newPart == '')
						#{
						#	#print "nothing<br>";
						#	// FIX ME
						#	// do this only if the parent sub type is multipart/mixed
						#	// and parent/parent is message/rfc
						#	$newPart = imap_fetchbody($this->mbox, $_uid, substr($value["partID"],0,-2), FT_UID);
						#	#$newPart = imap_fetchbody($this->mbox, $_uid, '2.2', FT_UID);
						#}
						$encoding	= $value['encoding'];
						$mimeType	= $value['mimeType'];
						$charSet	= $value['charset'];
					}
					
					// MS-Outlookbug workaround (don't break links)
					$newPart = preg_replace("!((http(s?)://)|((www|ftp)\.))(([^\n\t\r]+)([=](\r)?\n))+!i", 
							"$1$7", 
							$newPart);
					
					// decode the file ...
					switch ($encoding) 
					{
						case ENCBASE64:
							// use imap_base64 to decode
							$newPart = imap_base64($newPart);
							break;
						case ENCQUOTEDPRINTABLE:
							// use imap_qprint to decode
							#$newPart = imap_qprint($newPart);
							$newPart = quoted_printable_decode($newPart);
							break;
						case ENCOTHER:
							// not sure if this needs decoding at all
							break;
						default:
							// it is either not encoded or we don't know about it
							break;
					}
					
					$bodyPart[] = array('body'	=> $newPart,
									'mimeType'	=> $mimeType,
									'charSet'	=> $charSet);
				}
			}
			
			return $bodyPart;
			
		}


		function getMessageHeader($_folder, $_uid, $_partID = '') {
			$this->reopen($_folder);
			$msgno = imap_msgno($this->mbox, $_uid);
			if($_partID == '') {
				$retValue = imap_header($this->mbox, $msgno);
			} else {
				// do it the hard way
				// we need to fetch the headers of another part(message/rfcxxxx)
				$headersPart = imap_fetchbody($this->mbox, $_uid, $_partID.".0", FT_UID);
				$retValue = imap_rfc822_parse_headers($headersPart);
			}

			return $retValue;
		}

		function getMessageRawBody($_uid, $_partID = '')
		{
			if($_partID != '') {
				$body = imap_fetchbody($this->mbox, $_uid, $_partID, FT_UID);
			} else {
				$header = imap_fetchheader($this->mbox, $_uid, FT_UID);
				$body = $header.imap_body($this->mbox, $_uid, FT_UID);
			}
			
			return $body;
		}

		function getMessageRawHeader($_uid, $_partID = '') {
			if(!$_partID == '') {
				return imap_fetchbody($this->mbox, $_uid, $_partID.'.0', FT_UID);
			} else {
				return imap_fetchheader($this->mbox, $_uid, FT_UID);
			}
		}

		function getMessageStructure($_uid) {
			return imap_fetchstructure($this->mbox, $_uid, FT_UID);
		}
		
		// return the qouta of the users INBOX
		function getQuotaRoot() {
			return $this->icServer->getQuota('INBOX');
		}
		
		function imap_createmailbox($_folderName, $_subscribe = False)
		{
			if(!$icServer = $this->mailPreferences->getIncomingServer(0))
			{
				return false;
			}
			
			$mailboxString = $icServer->getMailboxString($_folderName);
			$result = @imap_createmailbox($this->mbox,$mailboxString);

			if($_subscribe) {
				return @imap_subscribe($this->mbox,$mailboxString);
			}
			
			return $result;
		}
		
/*		function imap_deletemailbox($_folderName)
		{
			$mailboxString = ExecMethod('emailadmin.bo.getMailboxString',$_folderName,3,$this->profileID);
			
			@imap_unsubscribe ($this->mbox, $mailboxString);

			$result = imap_deletemailbox($this->mbox, $mailboxString);
			
			#print imap_last_error();
			
			return $result;
		}
*/		
		function imapGetQuota($_username)
		{
			$quota_value = @imap_get_quota($this->mbox, "user.".$_username);

			if(is_array($quota_value) && count($quota_value) > 0)
			{
				return array('limit' => $quota_value['limit']/1024);
			}
			else
			{
				return false;
			}
		}		
		
		function imap_get_quotaroot($_folderName)
		{
			return @imap_get_quotaroot($this->mbox, $_folderName);
		}
		
		function imap_renamemailbox($_oldMailboxName, $_newMailboxName)
		{
			if(strcasecmp("inbox",$_oldMailboxName) == 0 || strcasecmp("inbox",$_newMailboxName) == 0)
			{
				return False;
			}

			if(!$icServer = $this->mailPreferences->getIncomingServer(0)) {
				return false;
			}

			$oldMailboxName = $icServer->getMailboxString($_oldMailboxName);
			$newMailboxName = $icServer->getMailboxString($_newMailboxName);
			$this->reopen('INBOX');
			
			$oldFolderStatus = $this->getFolderStatus($_oldMailboxName);
			// unsubscribe from old foldername
			// this is needed for UW IMAP
			$this->subscribe($_oldMailboxName, 'unsubscribe');
			
			$result =  @imap_renamemailbox($this->mbox,$oldMailboxName, $newMailboxName);
			
			if($oldFolderStatus['subscribed']) {
				$this->subscribe($_newMailboxName, 'subscribe');
			}
			#error_log(imap_last_error());
			
			return $result;
		}
		
		function imapSetQuota($_username, $_quotaLimit)
		{
			if(is_numeric($_quotaLimit) && $_quotaLimit >= 0)
			{
				// enable quota
				$quota_value = @imap_set_quota($this->mbox, "user.".$_username, $_quotaLimit*1024);
			}
			else
			{
				// disable quota
				$quota_value = @imap_set_quota($this->mbox, "user.".$_username, -1);
			}
		}
		
		function isSentFolder($_folderName)
		{
			if($this->mailPreferences->preferences['sentFolder'] == $_folderName)
				return TRUE;
			else
				return FALSE;
		}
		
		function isDraftFolder($_folderName)
		{
			if($this->mailPreferences->preferences['draftFolder'] == $_folderName)
				return TRUE;
			else
				return FALSE;
		}
		
		function moveMessages($_foldername, $_messageUID)
		{
			$msglist = '';
			
			$caching =& CreateObject('felamimail.bocaching',
					$this->icServer->host,
					$this->icServer->username,
					$this->sessionData['mailbox']);
			$deleteOptions  = $GLOBALS['egw_info']["user"]["preferences"]["felamimail"]["deleteOptions"];

			foreach($_messageUID as $key => $value) {
				if(!empty($msglist)) $msglist .= ",";
				$msglist .= $value;
			}
			#print $msglist."<br>";
			
			#print "destination folder($_folderName): ".$this->encodeFolderName($_foldername)."<br>";

			$this->reopen($this->sessionData['mailbox']);
			
			if (imap_mail_move ($this->mbox, $msglist, $this->encodeFolderName($_foldername), CP_UID))
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
				error_log(__FILE__ .' '.__LINE__.': '.imap_last_error());
			}
			
		}

		function openConnection($_folderName='', $_options=0, $_adminConnection=false)
		{
			if(!$icServer = $this->mailPreferences->getIncomingServer(0)) {
				return lang('No active IMAP server found!!');
			}

			$mbox = $icServer->openConnection($_options, $_adminConnection);

			if (is_a($mbox, 'PEAR_Error')) {
				return $mbox;
			}
			
			$this->mbox = $mbox;
			
			return true;
		}		

		function parseMessage(&$_sections, $_structure, $_wantedPartID = '', $_currentPartID='')
		{
			#print "w: $_wantedPartID, c: $_currentPartID<br>";
			#if($_currentPartID == '')
			#{
			#	 _debug_array($_structure);
			#	print "<hr><hr>";
			#}
			#_debug_array($_sections);
			#if ($_currentPartID == '') _debug_array($_structure);
			switch ($_structure->type)
			{
				case TYPETEXT:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID))
					{
						break;
					}
					$mime_type = "text";
					$data['encoding']	= $_structure->encoding;
					$data['size']		= $_structure->bytes;
					$data['partID']	= $_currentPartID;
					$data["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$data["name"]		= lang("unknown");
					#for ($lcv = 0; $lcv < count($_structure->parameters); $lcv++)
					foreach ((array)$_structure->parameters as $param)
					{
						#$param = $_structure->parameters[$lcv];
						switch(strtolower($param->attribute))
						{
							case 'name':
								$data["name"] = $param->value;
								break;
							case 'charset':
								$data["charset"] = $param->value;
								break;
						}
						
					}
					
					// set this to zero, when we have a plaintext message
					// if partID[0] is set, we have no attachments
					if($_currentPartID == '') $_currentPartID = '0';
					
					if (strtolower($_structure->disposition) == "attachment" ||
						$data["name"] != lang("unknown"))
					{
						// treat it as attachment
						// must be a attachment
						$_sections[$_currentPartID]		= $data;
						$_sections[$_currentPartID]['type']	= 'attachment';
					}
					else
					{
						#print "found a body part $_currentPartID<br>";
						// must be a body part
						$_sections["$_currentPartID"]		= $data;
						$_sections["$_currentPartID"]['name']	= lang('body part')." $_currentPartID";
						$_sections[$_currentPartID]['type']	= 'body';
					}
					#print "<hr>";
					#_debug_array($retData);
					#print "<hr>";
					break;
					
				case TYPEMULTIPART:
					#print "found multipart<br>";
					$mimeType = 'multipart';
					// lets cycle trough all parts
					$_sections[$_currentPartID]['mimeType']	= $mimeType."/". strtolower($_structure->subtype);

					if($_currentPartID != '') $_currentPartID .= '.';

					#print $_sections[$_currentPartID]['mimeType']."<br>";
					for($i = 0; $i < count($_structure->parts); $i++)
					{
						$structureData = array();
						$this->parseMessage($_sections, $_structure->parts[$i], $_wantedPartID, $_currentPartID.($i+1));
					}
					break;
				
				case TYPEMESSAGE:
					#print "found message $_currentPartID<br>";
					#_debug_array($_structure);
					#print "<hr>";
					// handle it as attachment
					#print "$_wantedPartID : $_currentPartID<br>";
					if(($_wantedPartID < $_currentPartID) ||
						empty($_wantedPartID))
					{
						#print "add as attachment<br>";
						$mime_type = "message";
						$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
						$_sections[$_currentPartID]['size']	= $_structure->bytes;
						$_sections[$_currentPartID]['partID']	= $_currentPartID;
						$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
						$_sections[$_currentPartID]["name"]	= lang("unknown");
						$_sections[$_currentPartID]['type']	= 'attachment';
						if(!empty($_structure->description))
						{
							$_sections[$_currentPartID]["name"] = lang($_structure->description);
						}

						// has the structure dparameters ??
						if($_structure->ifdparameters)
						{
							foreach($_structure->dparameters as $key => $value)
							{
								switch(strtolower($value->attribute))
								{
									case 'filename':
										$_sections[$_currentPartID]["name"] = $value->value;
										break;
								}
							}
						}

						// has the structure parameters ??
						if($_structure->ifparameters)
						{
							foreach($_structure->parameters as $key => $value)
							{
								switch(strtolower($value->attribute))
								{
									case 'name':
										$_sections[$_currentPartID]["name"] = $value->value;
										break;
								}
							}
						}
	
						
					}
					// recurse in it
					else
					{
						#_debug_array($_structure);
						for($i = 0; $i < count($_structure->parts); $i++)
						{
						#	print "<b>dive into Message</b><bR>";
							if($_structure->parts[$i]->type != TYPEMULTIPART)
								$_currentPartID = $_currentPartID.'.'.($i+1);
							$this->parseMessage($_sections, $_structure->parts[$i], $_wantedPartID, $_currentPartID);
						#	$this->parseMessage($_sections, $_structure->parts[0], $_wantedPartID, $_currentPartID);
						#	print "<b>done diving</b><br>";
						}
					}
					break;
					
				case TYPEAPPLICATION:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID))
					{
						break;
					}
					$mime_type = "application";
					$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
					$_sections[$_currentPartID]['size']	= $_structure->bytes;
					$_sections[$_currentPartID]['partID']	= $_currentPartID;
					$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$_sections[$_currentPartID]["name"]	= lang("unknown");
					$_sections[$_currentPartID]['type']	= 'attachment';
					foreach((array)$_structure->dparameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'filename':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					foreach((array)$_structure->parameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'name':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					break;
					
				case TYPEAUDIO:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID))
					{
						break;
					}
					$mime_type = "audio";
					$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
					$_sections[$_currentPartID]['size']	= $_structure->bytes;
					$_sections[$_currentPartID]['partID']	= $_currentPartID;
					$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$_sections[$_currentPartID]["name"]	= lang("unknown");
					$_sections[$_currentPartID]['type']	= 'attachment';
					foreach((array)$_structure->dparameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'filename':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					foreach((array)$_structure->parameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'name':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					break;
					
				case TYPEIMAGE:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID)) {
						break;
					}
					#print "found image $_currentPartID<br>";
					$mime_type = "image";
					$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
					$_sections[$_currentPartID]['size']	= $_structure->bytes;
					$_sections[$_currentPartID]['partID']	= $_currentPartID;
					$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$_sections[$_currentPartID]["name"]	= lang("unknown");
					$_sections[$_currentPartID]['type']	= 'attachment';
					if($_structure->id) {
						$_sections[$_currentPartID]['id'] = $_structure->id;
					}
					foreach((array)$_structure->dparameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'filename':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					foreach((array)$_structure->parameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'name':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					break;
					
				case TYPEVIDEO:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID))
					{
						break;
					}
					$mime_type = "video";
					$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
					$_sections[$_currentPartID]['size']	= $_structure->bytes;
					$_sections[$_currentPartID]['partID']	= $_currentPartID;
					$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$_sections[$_currentPartID]["name"]	= lang("unknown");
					$_sections[$_currentPartID]['type']	= 'attachment';
					foreach((array)$_structure->dparameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'filename':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					foreach((array)$_structure->parameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'name':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					break;
					
				case TYPEMODEL:
					if(!preg_match("/^$_wantedPartID/i",$_currentPartID))
					{
						break;
					}
					$mime_type = "model";
					$_sections[$_currentPartID]['encoding']	= $_structure->encoding;
					$_sections[$_currentPartID]['size']	= $_structure->bytes;
					$_sections[$_currentPartID]['partID']	= $_currentPartID;
					$_sections[$_currentPartID]["mimeType"]	= $mime_type."/". strtolower($_structure->subtype);
					$_sections[$_currentPartID]["name"]	= lang("unknown");
					$_sections[$_currentPartID]['type']	= 'attachment';
					foreach((array)$_structure->dparameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'filename':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					foreach((array)$_structure->parameters as $param) {
						switch(strtolower($param->attribute)) {
							case 'name':
								$_sections[$_currentPartID]["name"] = $param->value;
								break;
						}
					}
					break;
					
				default:
					break;
			}

			#if ($_currentPartID == '') _debug_array($_sections);
			
			#print "$_wantedPartID, $_currentPartID<br>";
			#if($_currentPartID >= $_wantedPartID)
			#{
			#	print "will add<br>";
			#	return $retData;
			#}
			
		}
		
		function reopen($_foldername)
		{
			// (regis) seems to be necessary/usefull to reopen in the good folder
			//echo "<hr>reopening imap mailbox in:".$_foldername;
			if(!$icServer = $this->mailPreferences->getIncomingServer(0)) {
				return false;
			}
			$mailboxString = $icServer->getMailboxString($_foldername);
			imap_reopen ($this->mbox, $mailboxString);
		}
		
		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['egw']->session->appsession('session_data');
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
			$GLOBALS['egw']->session->appsession('session_data','',$this->sessionData);
		}
		
		function setEMailProfile($_profileID)
		{
			$config =& CreateObject('phpgwapi.config','felamimail');
			$config->read_repository();
			$config->value('profileID',$_profileID);
			$config->save_repository();
		}
		
		function subscribe($_folderName, $_status)
		{
			#$this->mailPreferences['imapServerAddress']
			#$this->mailPreferences['imapPort'],
			if(!$icServer = $this->mailPreferences->getIncomingServer(0)) {
				return false;
			}
			
			$folderName = $icServer->getMailboxString($_folderName);
			
			if($_status == 'unsubscribe')
			{
				return imap_unsubscribe($this->mbox,$folderName);
			}
			else
			{
				return imap_subscribe($this->mbox,$folderName);
			}
		}
		
		function toggleFilter() {
			if($this->sessionData['filter']['filterActive'] == 'true') {
				$this->sessionData['filter']['filterActive'] = 'false';
			} else {
				$this->sessionData['filter']['filterActive'] = 'true';
			}
			$this->saveSessionData();
		}

		function updateAccount($_hookValues) {
			$icServer = $this->mailPreferences->getIncomingServer(0);
			if(is_a($icServer,'defaultimap')) {
				$icServer->updateAccount($_hookValues);
			}

			$ogServer = $this->mailPreferences->getOutgoingServer(0);
			if(is_a($ogServer,'defaultsmtp')) {
				$ogServer->updateAccount($_hookValues);
			}
		}
		
		function updateSingleACL($_folderName, $_accountName, $_aclType, $_aclStatus)
		{
			$folderACL = $this->getIMAPACL($_folderName);
			$userACL = $folderACL[$_accountName];
			
			if($_aclStatus == 'true')
			{
				if(strpos($userACL, $_aclType) === false)
				{
					$userACL .= $_aclType;
					imap_setacl ($this->mbox, $_folderName, $_accountName, $userACL);
				}
			}
			elseif($_aclStatus == 'false')
			{
				if(strpos($userACL, $_aclType) !== false)
				{
					$userACL = str_replace($_aclType,'',$userACL);
					imap_setacl ($this->mbox, $_folderName, $_accountName, $userACL);
				}
			}
			
			return $userACL;
		}
		
		/* inspired by http://de2.php.net/wordwrap
			 desolate19 at hotmail dot com */
		function wordwrap($str, $cols, $cut)
		{
/*			
			// todo
			// think about multibyte charsets
			// think about links in html mode
			$len		= strlen($str);
			$tag		= 0;
			$lineLenght	= 0;
			
			for ($i = 0; $i < $len; $i++) 
			{
				$lineLenght++;
				$chr = substr($str,$i,1);
				if(ctype_cntrl($chr))
				{
					if(ord($chr) == 10)
						$lineLenght     = 0;
				}
				if ($chr == '<') {
					$tag++;
				} elseif ($chr == '>') {
					$tag--;
				} elseif ((!$tag) && (ctype_space($chr))) {
					$wordlen = 0;
				} elseif (!$tag) {
					$wordlen++;
				}

				if ((!$tag) && (!$wordlen) && $lineLenght > $cols) {
				//if ((!$tag) && ($wordlen) && (!($wordlen % $cols))) {
					#print "add cut<br>";
					$chr .= $cut;
					$lineLenght     = 0;
				}
				$result .= $chr;
			}
			return $result;
*/
			$lines = explode('\n', $str);
			$newStr = '';
			foreach($lines as $line)
			{
				// replace tabs by 8 space chars, or any tab only counts one char
				$line = str_replace("\t","        ",$line);
				$newStr .= wordwrap($line, $cols, $cut);
			}
			return $newStr;
		}
		
	}
?>
