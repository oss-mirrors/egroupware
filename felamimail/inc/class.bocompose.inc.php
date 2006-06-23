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
	* Free Software Foundation; version 2 of the License.                       *
	\***************************************************************************/
	/* $Id$ */

	class bocompose
	{
		var $public_functions = array
		(
			'addAtachment'	=> True,
			'action'	=> True
		);
		
		var $attachments;	// Array of attachments
		var $preferences;	// the prefenrences(emailserver, username, ...)

		function bocompose($_composeID = '', $_charSet = 'iso-8859-1')
		{
			$this->displayCharset	= strtolower($_charSet);
			$this->bopreferences	=& CreateObject('felamimail.bopreferences');
			$this->bofelamimail	=& CreateObject('felamimail.bofelamimail',$_charSet);
			$this->preferences	= $this->bopreferences->getPreferences();
			$this->botranslation	=& CreateObject('phpgwapi.translation');

			if (!empty($_composeID))
			{
				$this->composeID = $_composeID;
				$this->restoreSessionData();
			}
		}
		
		/**
		 * adds uploaded files or files in eGW's temp directory as attachments
		 *
		 * It also stores the given data in the session
		 *
		 * @param array $_formData fields of the compose form (to,cc,bcc,reply_to,subject,body,priority,signature), plus data of the file (name,file,size,type)
		 */
		function addAttachment($_formData)
		{
			// to gard against exploits the file must be either uploaded or be in the temp_dir
			if ($_formData['size'] != 0 && (is_uploaded_file($_formData['file']) || 
				realpath(dirname($_formData['file'])) == realpath($GLOBALS['egw_info']['server']['temp_dir'])))
			{
				// ensure existance of eGW temp dir
				// note: this is different from apache temp dir, 
				// and different from any other temp file location set in php.ini
				if (!file_exists($GLOBALS['egw_info']['server']['temp_dir']))
				{
					@mkdir($GLOBALS['egw_info']['server']['temp_dir'],0700);
				}
				
				// if we were NOT able to create this temp directory, then make an ERROR report
				if (!file_exists($GLOBALS['egw_info']['server']['temp_dir']))
				{
					$alert_msg .= 'Error:'.'<br>'
						.'Server is unable to access phpgw tmp directory'.'<br>'
						.$GLOBALS['egw_info']['server']['temp_dir'].'<br>'
						.'Please check your configuration'.'<br>'
						.'<br>';
				}
				
				// sometimes PHP is very clue-less about MIME types, and gives NO file_type
				// rfc default for unknown MIME type is:
				$mime_type_default = 'application/octet-stream';
				// so if PHP did not pass any file_type info, then substitute the rfc default value
				if (trim($_formData['type']) == '')
				{
					$_formData['type'] = $mime_type_default;
				}
				
				$tmpFileName = $GLOBALS['egw_info']['server']['temp_dir'].
					SEP.
					$GLOBALS['egw_info']['user']['account_id'].
					$this->composeID.
					basename($_formData['file']);
				
				if (is_uploaded_file($_formData['file']))
				{
					move_uploaded_file($_formData['file'],$tmpFileName);	// requirement for safe_mode!
				}
				else
				{
					rename($_formData['file'],$tmpFileName);
				}
				$attachmentID = $this->getRandomString();

				$this->sessionData['attachments'][$attachmentID]=array
				(
					'name'	=> $_formData['name'],
					'type'	=> $_formData['type'],
					'file'	=> $tmpFileName,
					'size'	=> $_formData['size']
				);
			}

			$this->saveSessionData();
			#print"<pre>";print_r($this->sessionData);print"</pre>";exit;
		}
		
		function addMessageAttachment($_uid, $_partID, $_folder, $_name, $_size)
		{
			$this->sessionData['attachments'][]=array
			(
				'uid'		=> $_uid,
				'partID'	=> $_partID,
				'name'		=> $_name,
				'type'		=> 'message/rfc822',
				'size'		=> $_size,
				'folder'	=> $_folder
			);
			
			$this->saveSessionData();
			#print"<pre>";print_r($this->sessionData);print"</pre>";
		}
		
		function getAttachmentList()
		{
		}
		
		// create a hopefully unique id, to keep track of different compose windows
		// if you do this, you are creating a new email
		function getComposeID()
		{
			$this->composeID = $this->getRandomString();

			$this->setDefaults();
			
			return $this->composeID;
		}
		
		function getErrorInfo()
		{
			if(isset($this->errorInfo)) {
				$errorInfo = $this->errorInfo;
				unset($this->errorInfo);
				return $errorInfo;
			}
			return false;
		}
		
		function getForwardData($_icServer, $_folder, $_uid, $_partID) {
			$bofelamimail    =& CreateObject('felamimail.bofelamimail',$this->displayCharset);
			$bofelamimail->openConnection();

			// get message headers for specified message
			$headers	= $bofelamimail->getMessageHeader($_folder, $_uid, $_partID);
			// check for Re: in subject header
			$this->sessionData['subject'] 	= "[FWD] " . $bofelamimail->decode_header($headers->Subject);
			if($headers->Size)
				$size				= $headers->Size;
			else
				$size				= lang('unknown');

			$this->addMessageAttachment($_uid, $_partID, 
				$_folder, 
				$bofelamimail->decode_header($headers->Subject), 
				$size);
			
			$bofelamimail->closeConnection();
			
			$this->saveSessionData();
		}

		function getRandomString() {
			mt_srand((float) microtime() * 1000000);
			return md5(mt_rand (100000, 999999));
		}
		
		// $_mode can be:
		// single: for a reply to one address
		// all: for a reply to all
		function getReplyData($_mode, $_icServer, $_folder, $_uid, $_partID) {
			$bofelamimail    =& CreateObject('felamimail.bofelamimail',$this->displayCharset);
			$bofelamimail->openConnection();
			
			$userEMailAddresses = $this->preferences->getUserEMailAddresses();

			// get message headers for specified message
			$headers	= $bofelamimail->getMessageHeader($_folder, $_uid, $_partID);

			$this->sessionData['uid'] = $_uid;

			// check for Reply-To: header and use if available
			if($headers->reply_toaddress) {
				$oldTo		= $bofelamimail->decode_header(trim($headers->reply_toaddress));
				$oldToAddress	= $headers->reply_to[0]->mailbox.'@'.$headers->reply_to[0]->host;
			} else {
				$oldTo = $bofelamimail->decode_header(trim($headers->fromaddress));
				$oldToAddress	= $headers->from[0]->mailbox.'@'.$headers->from[0]->host;
			}
			if($_mode != 'all' || ($_mode == 'all' && !$userEMailAddresses[$oldToAddress]) ) {
				$this->sessionData['to'] = $oldTo;
			}
			
			if($_mode == 'all') {
				// reply to any address which is cc, but not to my self
				$oldCC = $bofelamimail->decode_header(trim($headers->ccaddress));
				$addressParts = imap_rfc822_parse_adrlist($oldCC, '');
				if (count($addressParts)>0) {
					while(list($key,$val) = each($addressParts)) {
						if($userEMailAddresses[$val->mailbox.'@'.$val->host]) {
							continue;
						}

						if($val->mailbox == 'undisclosed-recipients' || (empty($val->mailbox) && empty($val->host)) ) {
							continue;
						}

						if(!empty($this->sessionData['cc'])) $this->sessionData['cc'] .= ",";

						if(!empty($val->personal) && !empty($val->mailbox) && !empty($val->host)) {
							$this->sessionData['cc'] .= sprintf('"%s" <%s@%s>',
											$val->personal,
											$val->mailbox,
											$val->host);
						} elseif(!empty($val->mailbox) && !empty($val->host)) {
							$this->sessionData['cc'] .= sprintf("%s@%s",
											$val->mailbox,
											$val->host);
						} else {
							$this->sessionData['cc'] .= $val->mailbox;
						}
					}
				}
				
				
				// reply to any address which is to, but not to my self
				$oldTo = $bofelamimail->decode_header(trim($headers->toaddress));
				$addressParts = imap_rfc822_parse_adrlist($oldTo, '');

				if (count($addressParts)>0) {
					while(list($key,$val) = each($addressParts)) {
						if($userEMailAddresses[$val->mailbox.'@'.$val->host]) {
							continue;
						}
						
						if($val->mailbox == 'undisclosed-recipients' || (empty($val->mailbox) && empty($val->host)) ) {
							continue;
						}

						if(!empty($this->sessionData['to'])) $this->sessionData['to'] .= ", ";
						if(!empty($val->personal) && !empty($val->mailbox) && !empty($val->host)) {
							$this->sessionData['to'] .= sprintf('"%s" <%s@%s>',
											$val->personal,
											$val->mailbox,
											$val->host);
						} elseif(!empty($val->mailbox) && !empty($val->host)) {
							$this->sessionData['to'] .= sprintf("%s@%s",
											$val->mailbox,
											$val->host);
						} else {
							$this->sessionData['to'] .= $val->mailbox;
						}
					}
				}
			}
			
			
			// check for Re: in subject header
			if(strtolower(substr(trim($bofelamimail->decode_header($headers->Subject)), 0, 3)) == "re:") {
				$this->sessionData['subject'] = $bofelamimail->decode_header($headers->Subject);
			} else {
				$this->sessionData['subject'] = "Re: " . $bofelamimail->decode_header($headers->Subject);
			}

			$this->sessionData['body']	= $bofelamimail->decode_header($headers->fromaddress) . " ".lang("wrote").": \n>";
			
			// get the body
			$bodyParts = $bofelamimail->getMessageBody($_uid, 'only_if_no_text', $_partID);
			#_debug_array($bodyParts);
			for($i=0; $i<count($bodyParts); $i++)
			{
				if(!empty($this->sessionData['body'])) $$this->sessionData['body'] .= "\n\n";
				// add line breaks to $bodyParts
				$newBody	= $this->botranslation->convert($bodyParts[$i]['body'], $bodyParts[$i]['charSet']);
				#print "<pre>".$newBody."</pre><hr>";
				$newBody        = explode("\n",$newBody);
				#_debug_array($newBody);
				// create it new, with good line breaks
				reset($newBody);
				while(list($key,$value) = @each($newBody))
				{
					$value .= "\n";
					$bodyAppend = $this->bofelamimail->wordwrap($value,75,"\n");
					$bodyAppend = str_replace("\n", "\n>", $bodyAppend);
					$this->sessionData['body'] .= $bodyAppend;
				}
			}
																
					
			$bofelamimail->closeConnection();
			
			$this->saveSessionData();
		}
		
		function getSessionData()
		{
			return $this->sessionData;
		}

		// get the user name, will will use for the FROM field
		function getUserName()
		{
			$retData = sprintf("%s <%s>",$this->preferences['realname'],$this->preferences['emailAddress']);
			return $retData;
		}
		
		function removeAttachment($_attachmentID)
		{
			unlink($this->sessionData['attachments'][$_attachmentID]['file']);
			unset($this->sessionData['attachments'][$_attachmentID]);

			$this->saveSessionData();
		}
		
		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['egw']->session->appsession('compose_session_data_'.$this->composeID);
			#print "bocompose after restore<pre>";print_r($this->sessionData);print"</pre>";
		}
		
		function saveSessionData()
		{
			$GLOBALS['egw']->session->appsession('compose_session_data_'.$this->composeID,'',$this->sessionData);
		}

		function send($_formData)
		{
			$bofelamimail	=& CreateObject('felamimail.bofelamimail',$this->displayCharset);
			$mail 		=& CreateObject('phpgwapi.phpmailer');
			
			$this->sessionData['identity']	= $_formData['identity'];
			$this->sessionData['to']	= $_formData['to'];
			$this->sessionData['cc']	= $_formData['cc'];
			$this->sessionData['bcc']	= $_formData['bcc'];
			$this->sessionData['folder']	= $_formData['folder'];
			$this->sessionData['reply_to']	= trim($_formData['reply_to']);
			$this->sessionData['subject']	= trim($_formData['subject']);
			$this->sessionData['body']	= $_formData['body'];
			$this->sessionData['priority']	= $_formData['priority'];
			$this->sessionData['signature']	= $_formData['signature'];
			$this->sessionData['disposition'] = $_formData['disposition'];


			$userLang = $GLOBALS['egw_info']['user']['preferences']['common']['lang'];
			$langFile = EGW_SERVER_ROOT."/phpgwapi/setup/phpmailer.lang-$userLang.php";
			if(file_exists($langFile))
			{
				$mail->SetLanguage($userLang, EGW_SERVER_ROOT."/phpgwapi/setup/");
			}
			else
			{
				$mail->SetLanguage("en", EGW_SERVER_ROOT."/phpgwapi/setup/");
			}
			$mail->PluginDir = EGW_SERVER_ROOT."/phpgwapi/inc/";
			
			#print $this->sessionData['uid']."<bR>";
			#print $this->sessionData['folder']."<bR>";
			
			#_debug_array($_formData);
			#exit;
			
			#include(EGW_APP_ROOT . "/config/config.php");
				
			$identity = $this->preferences->getIdentity((int)$this->sessionData['identity']);
			$ogServer = $this->preferences->getOutgoingServer(0);

			$mail->IsSMTP();
			$mail->From 	= $identity->emailAddress;
			$mail->FromName = $bofelamimail->encodeHeader($identity->realName,'q');
			$mail->Host 	= $ogServer->host;
			$mail->Port	= $ogServer->port;
			$mail->Priority = $this->sessionData['priority'];
			$mail->Encoding = 'quoted-printable';
			$mail->CharSet	= $this->displayCharset;
			$mail->AddCustomHeader("X-Mailer: FeLaMiMail version 0.9.5");
			if($this->sessionData['disposition']) {
				$mail->AddCustomHeader("Disposition-Notification-To: $mail->From");
			}
			if(!empty($identity->organization))
				$mail->AddCustomHeader("Organization: ".$bofelamimail->encodeHeader($identity->organization,'q'));

			foreach((array)$this->sessionData['to'] as $address)
			{
				$address_array	= imap_rfc822_parse_adrlist($address,'');
				if(count($address_array)>0)
				{
					for($i=0;$i<count($address_array);$i++)
					{
						$emailAddress = $address_array[$i]->mailbox."@".$address_array[$i]->host;
						$emailName = $bofelamimail->encodeHeader($address_array[$i]->personal,'q');
						$mail->AddAddress($emailAddress,$emailName);
					}
				}
			}

			foreach((array)$this->sessionData['cc'] as $address)
			{
				$address_array	= imap_rfc822_parse_adrlist($address,'');
				if(count($address_array)>0)
				{
					for($i=0;$i<count($address_array);$i++)
					{
						$emailAddress = $address_array[$i]->mailbox."@".$address_array[$i]->host;
						$emailName = $bofelamimail->encodeHeader($address_array[$i]->personal,'q');
						$mail->AddCC($emailAddress,$emailName);
					}
				}
			}
			
			foreach((array)$this->sessionData['bcc'] as $address)
			{
				$address_array	= imap_rfc822_parse_adrlist($address,'');
				if(count($address_array)>0)
				{
					for($i=0;$i<count($address_array);$i++)
					{
						$emailAddress = $address_array[$i]->mailbox."@".$address_array[$i]->host;
						$emailName = $bofelamimail->encodeHeader($address_array[$i]->personal,'q');
						$mail->AddBCC($emailAddress,$emailName);
					}
				}
			}
			
			if (!empty($this->sessionData['reply_to']))
			{
				$address_array	= imap_rfc822_parse_adrlist($this->sessionData['reply_to'],'');
				if(count($address_array)>0)
				{
					$emailAddress = $address_array[0]->mailbox."@".$address_array[0]->host;
					$emailName = $bofelamimail->encodeHeader($address_array[0]->personal,'q');
					$mail->AddReplyTo($emailAddress,$emailName);
				}
			}
			
			$mail->WordWrap = 76;
			$mail->Subject = $bofelamimail->encodeHeader($this->sessionData['subject'],'q');
			$mail->IsHTML(false);
			$mail->Body    = $this->sessionData['body'];
			if (!empty($this->sessionData['signature']))
			{
				$mail->Body	.= "\r\n-- \r\n";
				$mail->Body	.= $this->sessionData['signature'];
			}

			// add the attachments
			if (is_array($this->sessionData['attachments']))
			{
				while(list($key,$value) = each($this->sessionData['attachments']))
				{
					switch($value['type'])
					{
						case 'message/rfc822':
							$bofelamimail->openConnection($value['folder']);
			
							$rawBody	= $bofelamimail->getMessageRawBody($value['uid'],$value['partID']);
			
							$bofelamimail->closeConnection();
					
							$mail->AddStringAttachment($rawBody,$value['name'],'7bit','message/rfc822');
			
							break;
							
						default:
							$mail->AddAttachment
							(
								$value['file'],
								$value['name'],
								'base64',
								$value['type']
							);
					}
				}
			}
			#$mail->AltBody = $this->sessionData['body'];

			// SMTP Auth??
			if($ogServer->smtpAuth)
			{
				$mail->SMTPAuth	= true;
				$mail->Username	= $ogServer->username;
				$mail->Password	= $ogServer->password;
			}
			
			// set a higher timeout for big messages
			@set_time_limit(120);
			#$mail->SMTPDebug = 10;
			if(count((array)$this->sessionData['to']) > 0 || count((array)$this->sessionData['cc']) > 0 || count((array)$this->sessionData['bcc']) > 0) {
				if(!$mail->Send()) {
					$this->errorInfo = $mail->ErrorInfo;
					return false;
				}
			}

			$folder = (array)$this->sessionData['folder'];
			if($GLOBALS['egw_info']['user']['preferences']['felamimail']['sentFolder'] != 'none') {
				$folder[] = $GLOBALS['egw_info']['user']['preferences']['felamimail']['sentFolder'];
			}
			$folder = array_unique($folder);

			if (count($folder) > 0) {
				$bofelamimail =& CreateObject('felamimail.bofelamimail');
				foreach($folder as $folderName) {
					#if($folderName == $GLOBALS['egw_info']['user']['preferences']['felamimail']['sentFolder']) {
						$flags = '\\Seen';
					#} else {
					#	$flags = '';
					#}
					$bofelamimail->openConnection($folderName);
					$bofelamimail->appendMessage($folderName,
								$mail->getMessageHeader(),
								$mail->getMessageBody(),
								$flags);
				}
				$bofelamimail->closeConnection();
			}

			if(isset($this->sessionData['uid']))
			{
				// mark message as answered
				$bofelamimail =& CreateObject('felamimail.bofelamimail',$this->sessionData['folder']);
				$bofelamimail->openConnection();
				$bofelamimail->flagMessages("answered",array('0' => $this->sessionData['uid']));
				$bofelamimail->closeConnection();
			}

			if(is_array($this->sessionData['attachments']))
			{
				reset($this->sessionData['attachments']);
				while(list($key,$value) = @each($this->sessionData['attachments']))
				{
					#print "$key: ".$value['file']."<br>";
					if (!empty($value['file']))	// happens when forwarding mails
					{
						unlink($value['file']);
					}
				}
			}

			$this->sessionData = '';
			$this->saveSessionData();

			return true;
		}
		
		function setDefaults()
		{
			$this->sessionData['signature']	= $GLOBALS['egw']->preferences->parse_notify($GLOBALS['egw_info']['user']['preferences']['felamimail']['signature']);
			
			$this->saveSessionData();
		}
		
		function stripSlashes($_string) 
		{
			if (get_magic_quotes_gpc()) 
			{
				return stripslashes($_string);
			}
			else
			{
				return $_string;
			}
		}
															

}
