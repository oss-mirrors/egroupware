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

	class uicompose
	{

		var $public_functions = array
		(
			'compose'	=> 'True',
			'reply'		=> 'True',
			'forward'	=> 'True',
			'action'	=> 'True'
		);

		function uicompose()
		{
			if (!isset($GLOBALS['HTTP_POST_VARS']['composeid']) && !isset($GLOBALS['HTTP_GET_VARS']['composeid']))
			{
				// create new compose session
				$this->bocompose   = CreateObject('felamimail.bocompose');
				$this->composeID = $this->bocompose->getComposeID();
			}
			else
			{
				// reuse existing compose session
				if (isset($GLOBALS['HTTP_POST_VARS']['composeid']))
					$this->composeID = $GLOBALS['HTTP_POST_VARS']['composeid'];
				else
					$this->composeID = $GLOBALS['HTTP_GET_VARS']['composeid'];
				$this->bocompose   = CreateObject('felamimail.bocompose',$this->composeID);
			}			
			
			$this->t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);

			$this->t->set_unknowns('remove');
			
			$this->rowColor[0] = $GLOBALS['phpgw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['phpgw_info']["theme"]["bg02"];


		}
		
		function action()
		{
			$formData['to'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['to']);
			$formData['cc'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['cc']);
			$formData['bcc'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['bcc']);
			$formData['reply_to'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['reply_to']);
			$formData['subject'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['subject']);
			$formData['body'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['body']);
			$formData['priority'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['priority']);
			$formData['signature'] 	= $this->bocompose->stripSlashes($GLOBALS['HTTP_POST_VARS']['signature']);

			if (isset($GLOBALS['HTTP_POST_VARS']['send'])) 
			{
				$action="send";
			}
			elseif (isset($GLOBALS['HTTP_POST_VARS']['addfile'])) 
			{
				$action="addfile";
			}
			elseif (isset($GLOBALS['HTTP_POST_VARS']['removefile']))
			{
				$action="removefile";
			}
			
			switch ($action)
			{
				case "addfile":
					$formData['name']	= $GLOBALS['HTTP_POST_FILES']['attachfile']['name'];
					$formData['type']	= $GLOBALS['HTTP_POST_FILES']['attachfile']['type'];
					$formData['file']	= $GLOBALS['HTTP_POST_FILES']['attachfile']['tmp_name'];
					$formData['size']	= $GLOBALS['HTTP_POST_FILES']['attachfile']['size'];
					$this->bocompose->addAttachment($formData);
					$this->compose();
					break;

				case "removefile":
					$formData['removeAttachments']	= $GLOBALS['HTTP_POST_VARS']['attachment'];
					$this->bocompose->removeAttachment($formData);
					$this->compose();
					break;
					
				case "send":
					$this->bocompose->send($formData);
					$linkData = array
					(
						'mailbox'	=> $GLOBALS['HTTP_GET_VARS']['mailbox'],
						'startMessage'	=> '1'
					);
					$link = $GLOBALS['phpgw']->link('/felamimail/index.php',$linkData);
					$GLOBALS['phpgw']->redirect($link);
					$GLOBALS['phpgw']->common->phpgw_exit();
					break;
			}
		}
		
		function compose()
		{
			// read the data from session
			// all values are empty for a new compose window
			$sessionData = $this->bocompose->getSessionData();
			
			// is the to address set already?
			if (!empty($GLOBALS['HTTP_GET_VARS']['send_to']))
			{
				$sessionData['to'] = urldecode($GLOBALS['HTTP_GET_VARS']['send_to']);
			}
			
			$this->display_app_header();
			
			$this->t->set_file(array("composeForm" => "composeForm.tpl"));
			$this->t->set_block('composeForm','header','header');
			$this->t->set_block('composeForm','body_input');
			$this->t->set_block('composeForm','attachment','attachment');
			$this->t->set_block('composeForm','attachment_row','attachment_row');
			$this->t->set_block('composeForm','attachment_row_bold');
			
			$this->translate();
			
			$this->t->set_var("link_addressbook",$GLOBALS['phpgw']->link('/felamimail/addressbook.php'));

			$linkData = array
			(
				'mailbox'	=> urlencode($GLOBALS['HTTP_GET_VARS']['mailbox']),
				'startMessage'	=> $GLOBALS['HTTP_GET_VARS']['startMessage'],
				'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
				'sort'		=> $GLOBALS['HTTP_GET_VARS']['sort']
			);
			$this->t->set_var("link_message_list",$GLOBALS['phpgw']->link('/felamimail/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uicompose.action',
				'composeid'	=> $this->composeID,
				'mailbox'	=> urlencode($GLOBALS['HTTP_GET_VARS']['mailbox']),
				'startMessage'	=> '1'
			);
			$this->t->set_var("link_action",$GLOBALS['phpgw']->link('/index.php',$linkData));
			$this->t->set_var('folder_name',$GLOBALS['HTTP_GET_VARS']['mailbox']);

			// header
			$this->t->set_var("to",$sessionData['to']);
			$this->t->set_var("cc",$sessionData['cc']);
			$this->t->set_var("bcc",$sessionData['bcc']);
			$this->t->set_var("reply_to",$sessionData['reply_to']);
			$this->t->set_var("subject",$sessionData['subject']);
			$this->t->pparse("out","header");

			// body
			$this->t->set_var("body",$sessionData['body']);
			$this->t->set_var("signature",$sessionData['signature']);
			$this->t->pparse("out","body_input");

			// attachments
			if (is_array($sessionData['attachments']) && count($sessionData['attachments']) > 0)
			{
				$this->t->set_var('row_color',$this->rowColor[0]);
				$this->t->set_var('name',lang('name'));
				$this->t->set_var('type',lang('type'));
				$this->t->set_var('size',lang('size'));
				$this->t->parse('attachment_rows','attachment_row_bold',True);
				while (list($key,$value) = each($sessionData['attachments']))
				{
					#print "$key : $value<br>";
					$this->t->set_var('row_color',$this->rowColor[($key+1)%2]);
					$this->t->set_var('name',$value['name']);
					$this->t->set_var('type',$value['type']);
					$this->t->set_var('size',$value['size']);
					$this->t->set_var('attachment_number',$key);
					$this->t->parse('attachment_rows','attachment_row',True);
				}
			}
			else
			{
				$this->t->set_var('attachment_rows','');
			}
			
			$this->t->pparse("out","attachment");
		}

		function display_app_header()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}
		
		function forward()
		{
			$replyID = $GLOBALS['HTTP_GET_VARS']['reply_id'];
			$folder  = urldecode($GLOBALS['HTTP_GET_VARS']['mailbox']);
			if (!empty($replyID) && !empty($folder))
			{
				// this fill the session data with the values from the original email
				$this->bocompose->getForwardData($folder, $replyID);
			}
			$this->compose();
		}

		function reply()
		{
			$replyID = $GLOBALS['HTTP_GET_VARS']['reply_id'];
			$folder  = urldecode($GLOBALS['HTTP_GET_VARS']['mailbox']);
			if (!empty($replyID) && !empty($folder))
			{
				// this fill the session data with the values from the original email
				$this->bocompose->getReplyData($folder, $replyID);
			}
			$this->compose();
		}
		
		function translate()
		{
			$this->t->set_var("lang_message_list",lang('Message List'));
			$this->t->set_var("lang_to",lang('to'));
			$this->t->set_var("lang_cc",lang('cc'));
			$this->t->set_var("lang_bcc",lang('bcc'));
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
			
			$this->t->set_var("th_bg",$GLOBALS['phpgw_info']["theme"]["th_bg"]);
			$this->t->set_var("bg01",$GLOBALS['phpgw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['phpgw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['phpgw_info']["theme"]["bg03"]);
		}
}