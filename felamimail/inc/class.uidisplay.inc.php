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

	class uidisplay
	{

		var $public_functions = array
		(
			'display'	=> 'True',
			'showHeader'	=> 'True',
			'getAttachment'	=> 'True'
		);

		function uidisplay()
		{
			$this->t 		= $GLOBALS['phpgw']->template;
			$this->bofelamimail	= CreateObject('felamimail.bofelamimail');
			$this->bofelamimail->openConnection();
			
			$this->mailbox		= $this->bofelamimail->sessionData['mailbox'];
			$this->uid		= $GLOBALS['HTTP_GET_VARS']['uid'];

			$this->rowColor[0] = $GLOBALS['phpgw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['phpgw_info']["theme"]["bg02"];

			if($GLOBALS['HTTP_GET_VARS']['showHeader'] == "false")
			{
				$this->bofelamimail->sessionData['showHeader'] = 'False';
				$this->bofelamimail->saveSessionData();
			}
		}
		
		function createLinks($_data)
		{
			
		}
		
		function display()
		{
			$headers	= $this->bofelamimail->getMessageHeader($this->uid);
			$rawheaders	= $this->bofelamimail->getMessageRawHeader($this->uid);
			$bodyParts	= $this->bofelamimail->getMessageBody($this->uid);
			$attachments	= $this->bofelamimail->getMessageAttachments($this->uid);

			// add line breaks to $rawheaders
			$newRawHeaders = explode("\n",$rawheaders);
			// reset $rawheaders
			$rawheaders 	= "";
			// create it new, with good line breaks
			reset($newRawHeaders);
			while(list($key,$value) = @each($newRawHeaders))
			{
				$rawheaders .= wordwrap($value,90,"\n     ");
			}
			
			$this->bofelamimail->closeConnection();
			
			if(!isset($GLOBALS['HTTP_GET_VARS']['printable']))
			{
				$this->display_app_header();
			}
			
			$this->t->set_file(array("displayMsg" => "view_message.tpl"));
			$this->t->set_block('displayMsg','message_main');
			$this->t->set_block('displayMsg','message_header');
			$this->t->set_block('displayMsg','message_raw_header');
			$this->t->set_block('displayMsg','message_navbar');
			$this->t->set_block('displayMsg','message_navbar_print');
			$this->t->set_block('displayMsg','message_cc');
			$this->t->set_block('displayMsg','message_attachement_row');
			
			$this->translate();
			
			if(!isset($GLOBALS['HTTP_GET_VARS']['printable']))
			{

				// navbar
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen'
				);
				$this->t->set_var("link_message_list",$GLOBALS['phpgw']->link('/felamimail/index.php',$linkData));
	
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uicompose.compose'
				);
				$this->t->set_var("link_compose",$GLOBALS['phpgw']->link('/index.php',$linkData));
				$this->t->set_var('folder_name',$this->bofelamimail->sessionData['mailbox']);

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uicompose.reply',
					'reply_id'	=> $this->uid
				);
				$this->t->set_var("link_reply",$GLOBALS['phpgw']->link('/index.php',$linkData));

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uicompose.replyAll',
					'reply_id'	=> $this->uid
				);
				$this->t->set_var("link_reply_all",$GLOBALS['phpgw']->link('/index.php',$linkData));

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uicompose.forward',
					'reply_id'	=> $this->uid
				);
				$this->t->set_var("link_forward",$GLOBALS['phpgw']->link('/index.php',$linkData));	

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.deleteMessage',
					'message'	=> $this->uid
				);
				$this->t->set_var("link_delete",$GLOBALS['phpgw']->link('/index.php',$linkData));

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uidisplay.showHeader',
					'uid'		=> $this->uid
				);
				$this->t->set_var("link_header",$GLOBALS['phpgw']->link('/index.php',$linkData));

				$linkData = array
				(
					'menuaction'	=> 'felamimail.uidisplay.display',
					'printable'	=> 1,
					'uid'		=> $this->uid
				);
				$this->t->set_var("link_printable",$GLOBALS['phpgw']->link('/index.php',$linkData));
	
				$langArray = array
				(
					'lang_messagelist'      => lang('Message List'),
					'lang_compose'          => lang('Compose'),
					'lang_delete'           => lang('Delete'),
					'lang_forward'          => lang('Forward'),
					'lang_reply'            => lang('Reply'),
					'lang_reply_all'        => lang('Reply All'),
					'lang_back_to_folder'   => lang('back to folder'),
					'app_image_path'        => PHPGW_IMAGES
				);
				$this->t->set_var($langArray);
				$this->t->parse('navbar','message_navbar',True);
			}
			else
			{	
				$langArray = array
				(
					'lang_print_this_page'  => lang('print this page'),
					'lang_close_this_page'  => lang('close this page'),
					'lang_printable'        => '',
					'lang_reply'            => lang('Reply'),
					'lang_reply_all'        => lang('Reply All'),
					'lang_back_to_folder'   => lang('back to folder'),
					'app_image_path'        => PHPGW_IMAGES
				);
				$this->t->set_var($langArray);
				$this->t->parse('navbar','message_navbar_print',True);
			}
			
			
			// rawheader
			if($this->bofelamimail->sessionData['showHeader'] == 'True')
			{
				$this->t->set_var("raw_header_data",htmlentities($rawheaders));
				$this->t->parse("rawheader",'message_raw_header',True);
				$this->t->set_var("view_header",lang('hide header'));
			}
			else
			{
				$this->t->set_var("rawheader",'');
				$this->t->set_var("view_header",lang('show header'));
			}
			

			// header
			$this->t->set_var("from_data",htmlentities($this->bofelamimail->decode_header($headers->fromaddress)));
			$this->t->set_var("to_data",htmlentities($this->bofelamimail->decode_header($headers->toaddress)));
			if($headers->ccaddress)
			{
				$this->t->set_var("cc_data",htmlentities($this->bofelamimail->decode_header($headers->ccaddress)));
				$this->t->parse('cc_data_part','message_cc',True);
			}
			else
			{
				$this->t->set_var("cc_data_part",'');
			}
			$this->t->set_var("date_data",htmlentities($GLOBALS['phpgw']->common->show_date($headers->udate)));
			$this->t->set_var("subject_data",htmlentities($this->bofelamimail->decode_header($headers->subject)));
			$this->t->parse("header","message_header",True);

			// body
			for($i=0; $i<count($bodyParts); $i++ )
			{
				if(!empty($body)) $body .= "<hr>";
			
				// add line breaks to $bodyParts
				$newBody	= explode("\n",$bodyParts[$i]);
				$bodyAppend	= '';
				// create it new, with good line breaks
				reset($newBody);
				while(list($key,$value) = @each($newBody))
				{
					$bodyAppend .= wordwrap($value,90);
				}
				
				$body .= htmlentities($bodyAppend);
			}
			$this->t->set_var("body",$body);
			$this->t->set_var("signature",$sessionData['signature']);

			// attachments
			if (is_array($attachments) && count($attachments) > 0)
			{
				$this->t->set_var('row_color',$this->rowColor[0]);
				$this->t->set_var('name',lang('name'));
				$this->t->set_var('type',lang('type'));
				$this->t->set_var('size',lang('size'));
				#$this->t->parse('attachment_rows','attachment_row_bold',True);
				while (list($key,$value) = each($attachments))
				{
					$this->t->set_var('row_color',$this->rowColor[($key+1)%2]);
					$this->t->set_var('filename',htmlentities($this->bofelamimail->decode_header($value['name'])));
					$this->t->set_var('mimetype',$value['type']);
					$this->t->set_var('size',$value['size']);
					$this->t->set_var('attachment_number',$key);
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uidisplay.getAttachment',
						'uid'		=> $this->uid,
						'part'		=> $value['pid']
					);
					$this->t->set_var("link_view",$GLOBALS['phpgw']->link('/index.php',$linkData));

					$linkData = array
					(
						'menuaction'	=> 'felamimail.uidisplay.getAttachment',
						'mode'		=> 'save',
						'uid'		=> $this->uid,
						'part'		=> $value['pid']
					);
					$this->t->set_var("link_save",$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					$this->t->parse('attachment_rows','message_attachement_row',True);
				}
			}
			else
			{
				$this->t->set_var('attachment_rows','');
			}

			#$this->t->pparse("out","message_attachment_rows");

			// print it out
			$this->t->pparse("out","message_main");

			global $calendar_id;
			list(,$app,,,,$calendar_id) = explode('"',strstr($rawheaders,'X-phpGW-Type:'));
			if(!isset($GLOBALS['HTTP_GET_VARS']['printable']) && !empty($app))
			{
				echo '<table align="center" width="100%"><tr><td align="center">';
				$GLOBALS['phpgw']->hooks->single('email',$app);
				echo '</td></tr></table>';
			}
		}

		function display_app_header()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}
		
		function getAttachment()
		{
			
			$part	= $GLOBALS['HTTP_GET_VARS']['part'];
			
			$attachment 	= $this->bofelamimail->getAttachment($this->uid,$part);
			
			$this->bofelamimail->closeConnection();
			
			if($GLOBALS['HTTP_GET_VARS']['mode'] == "save")
			{
				header ("Content-Type: application/octet-stream");
			}
			else
			{
				header ("Content-Type: ".$attachment['type']);
			}
			header("Content-Disposition: filename=\"".$attachment['filename']."\"");
			echo $attachment['attachment'];
			$GLOBALS['phpgw']->common->phpgw_exit();
			exit;
			                                
		}
		
		function showHeader()
		{
			if($this->bofelamimail->sessionData['showHeader'] == 'True')
			{
				$this->bofelamimail->sessionData['showHeader'] = 'False';
			}
			else
			{
				$this->bofelamimail->sessionData['showHeader'] = 'True';
			}
			$this->bofelamimail->saveSessionData();
			
			$this->display();
		}
		
		function translate()
		{
			$this->t->set_var("lang_message_list",lang('Message List'));
			$this->t->set_var("lang_to",lang('to'));
			$this->t->set_var("lang_cc",lang('cc'));
			$this->t->set_var("lang_bcc",lang('bcc'));
			$this->t->set_var("lang_from",lang('from'));
			$this->t->set_var("lang_reply_to",lang('reply to'));
			$this->t->set_var("lang_subject",lang('subject'));
			$this->t->set_var("lang_addressbook",lang('addressbook'));
			$this->t->set_var("lang_search",lang('search'));
			$this->t->set_var("lang_send",lang('send'));
			$this->t->set_var("lang_back_to_folder",lang('back to folder'));
			$this->t->set_var("lang_attachments",lang('attachments'));
			$this->t->set_var("lang_add",lang('add'));
			$this->t->set_var("lang_remove",lang('remove'));
			$this->t->set_var("lang_priority",lang('priority'));
			$this->t->set_var("lang_normal",lang('normal'));
			$this->t->set_var("lang_high",lang('high'));
			$this->t->set_var("lang_low",lang('low'));
			$this->t->set_var("lang_signature",lang('signature'));
			$this->t->set_var("lang_compose",lang('compose'));
			$this->t->set_var("lang_date",lang('date'));
			$this->t->set_var("lang_view",lang('view'));
			$this->t->set_var("lang_save",lang('save'));
			$this->t->set_var("lang_printable",lang('print it'));
			
			$this->t->set_var("th_bg",$GLOBALS['phpgw_info']["theme"]["th_bg"]);
			$this->t->set_var("bg01",$GLOBALS['phpgw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['phpgw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['phpgw_info']["theme"]["bg03"]);
		}
}

?>
