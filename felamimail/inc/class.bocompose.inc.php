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

	class bocompose
	{
		var $public_functions = array
		(
			'addAtachment'	=> True,
			'action'	=> True
		);
		
		var $attachments;	// Array of attachments
		var $preferences;	// the prefenrences(emailserver, username, ...)

		function bocompose($_composeID = '')
		{
			$this->bopreferences	= CreateObject('felamimail.bopreferences');
			$this->preferences = $this->bopreferences->getPreferences();
			
			if (!empty($_composeID))
			{
				$this->composeID = $_composeID;
				$this->restoreSessionData();
			}
		}
		
		function addAttachment($_formData)
		{
			$this->sessionData['to']	= $_formData['to'];
			$this->sessionData['cc']	= $_formData['cc'];
			$this->sessionData['bcc']	= $_formData['bcc'];
			$this->sessionData['reply_to']	= $_formData['reply_to'];
			$this->sessionData['subject']	= $_formData['subject'];
			$this->sessionData['body']	= $_formData['body'];
			$this->sessionData['priority']	= $_formData['priority'];
			$this->sessionData['signature'] = $_formData['signature'];
			
			#while(list($key,$value) = each($GLOBALS['phpgw_info']['user']))
			#{
			#	print "$key: $value<br>";
			#}
			
			if ($_formData['size'] != 0)
			{
				// ensure existance of PHPGROUPWARE temp dir
				// note: this is different from apache temp dir, 
				// and different from any other temp file location set in php.ini
				if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
				{
					@mkdir($GLOBALS['phpgw_info']['server']['temp_dir'],0700);
				}
				
				// if we were NOT able to create this temp directory, then make an ERROR report
				if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
				{
					$alert_msg .= 'Error:'.'<br>'
						.'Server is unable to access phpgw tmp directory'.'<br>'
						.$phpgw_info['server']['temp_dir'].'<br>'
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
				
				$tmpFileName = $GLOBALS['phpgw_info']['server']['temp_dir'].
					SEP.
					$GLOBALS['phpgw_info']['user']['account_id'].
					$this->composeID.
					basename($_formData['file']);
				
				copy($_formData['file'],$tmpFileName);
				
				$this->sessionData['attachments'][]=array
				(
					'name'	=> $_formData['name'],
					'type'	=> $_formData['type'],
					'file'	=> $tmpFileName,
					'size'	=> $_formData['size']
				);
			}
			
			$this->saveSessionData();
		}
		
		function getAttachmentList()
		{
		}
		
		// create a hopefully unique id, to keep track of different compose windows
		// if you do this, you are creating a new email
		function getComposeID()
		{
			mt_srand((float) microtime() * 1000000);
			$this->composeID = mt_rand (100000, 999999);
			
			$this->setDefaults();
			
			return $this->composeID;
		}
		
		function getSessionData()
		{
			return $this->sessionData;
		}
		
		function removeAttachment($_formData)
		{
			$this->sessionData['to']	= $_formData['to'];
			$this->sessionData['cc']	= $_formData['cc'];
			$this->sessionData['bcc']	= $_formData['bcc'];
			$this->sessionData['reply_to']	= $_formData['reply_to'];
			$this->sessionData['subject']	= $_formData['subject'];
			$this->sessionData['body']	= $_formData['body'];
			$this->sessionData['priority']	= $_formData['priority'];
			$this->sessionData['signature']	= $_formData['signature'];

			while(list($key,$value) = each($_formData['removeAttachments']))
			{
				#print "$key: $value<br>";
				unlink($this->sessionData['attachments'][$key]['file']);
				unset($this->sessionData['attachments'][$key]);
			}
			reset($this->sessionData['attachments']);
			
			// if it's empty, clear it totaly
			if (count($this->sessionData['attachments']) == 0) 
			{
				$this->sessionData['attachments'] = '';
			}
			
			$this->saveSessionData();
		}
		
		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['phpgw']->session->appsession('compose_session_data_'.$this->composeID);
		}
		
		function saveSessionData()
		{
			$GLOBALS['phpgw']->session->appsession('compose_session_data_'.$this->composeID,'',$this->sessionData);
		}

		function send($_formData)
		{
			$this->sessionData['to']	= $_formData['to'];
			$this->sessionData['cc']	= $_formData['cc'];
			$this->sessionData['bcc']	= $_formData['bcc'];
			$this->sessionData['reply_to']	= $_formData['reply_to'];
			$this->sessionData['subject']	= $_formData['subject'];
			$this->sessionData['body']	= $_formData['body'];
			$this->sessionData['priority']	= $_formData['priority'];
			$this->sessionData['signature']	= $_formData['signature'];

			$mail = CreateObject('felamimail.phpmailer');
			
			include(PHPGW_APP_ROOT . "/config/config.php");
				
			$mail->IsSMTP();
			$mail->From 	= $this->preferences['emailAddress'];
			$mail->FromName = $this->preferences['realname'];
			$mail->Host 	= $this->preferences['smtpServerAddress'];
			$mail->Priority = $this->sessionData['priority'];
			$mail->Encoding = '8bit';

			if (!empty($this->sessionData['to']))
			{
				$address = split(";",$this->sessionData['to']);
				while (list($key,$value) = each($address))
				{
					$mail->AddAddress($value);
				}
			}
			
			if (!empty($this->sessionData['cc']))
			{
				$address = split(";",$this->sessionData['cc']);
				while (list($key,$value) = each($address))
				{
					$mail->AddCC($value);
				}
			}
			
			if (!empty($this->sessionData['bcc']))
			{
				$address = split(";",$this->sessionData['bcc']);
				while (list($key,$value) = each($address))
				{
					$mail->AddBCC($value);
				}
			}
			
			if (!empty($this->sessionData['reply_to']))
			{
				$address = split(";",$this->sessionData['reply_to']);
				while (list($key,$value) = each($address))
				{
					$mail->AddReplyTo($value);
				}
			}
			
			$mail->WordWrap = 76;
			$mail->Subject = $this->sessionData['subject'];
			$mail->IsHTML(false);
			$mail->Body    = $this->sessionData['body'];
			if (!empty($this->sessionData['signature']))
			{
				$mail->Body	.= "\r\n--\r\n";
				$mail->Body	.= $this->sessionData['signature'];
			}
			if (is_array($this->sessionData['attachments']))
			{
				while(list($key,$value) = each($this->sessionData['attachments']))
				{
					$mail->AddAttachment
					(
						$value['file'],
						$value['name'],
						'base64',
						$value['type']
					);
				}
			}
			#$mail->AltBody = $this->sessionData['body'];
			
			if(!$mail->Send())
			{
				echo "Message could not be sent. <p>";
				echo "Mailer Error: " . $mail->ErrorInfo;
				exit;
			}

			if ($this->preferences['move_to_sent'] == "true")
			{
				$username 		= $this->preferences['username'];
				$key 			= $this->preferences['key'];
				$imapServerAddress 	= $this->preferences['imapServerAddress'];
				$imapPort 		= $this->preferences['imapPort'];
				$sent_folder		= $this->preferences['sent_folder'];
			
				include(PHPGW_APP_ROOT . "/inc/imap_general.php");
				include(PHPGW_APP_ROOT . "/inc/imap_mailbox.php");
				include(PHPGW_APP_ROOT . "/inc/smtp.php");
			
				$imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 1);
				$sent_folder = trim($sent_folder);
				if (sqimap_mailbox_exists ($imap_stream, $sent_folder)) 
				{
					sqimap_append ($imap_stream, $sent_folder, 
						strlen($mail->create_header())+strlen($mail->create_body()));
					fputs ($imap_stream, $mail->create_header());
					fputs ($imap_stream, $mail->create_body());
					sqimap_append_done ($imap_stream);
				}
				sqimap_logout($imap_stream);
			}
			while(list($key,$value) = @each($this->sessionData['attachments']))
			{
				#print "$key: $value<br>";
				unlink($value['file']);
			}
			
			$this->sessionData = '';
			$this->saveSessionData();
		}
		
		function setDefaults()
		{
			$this->sessionData['signature']	= $this->preferences['signature'];
			
			$this->saveSessionData();
		}
		
		function stripSlashes($_string) 
		{
			if (get_magic_quotes_gpc()) 
			{
				$string = stripslashes($_string);
			}
			return $string;
		}
                              

}