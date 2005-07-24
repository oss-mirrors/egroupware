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

	class uipreferences
	{

		var $public_functions = array
		(
			'addACL'	=> 'True',
			'listFolder'	=> 'True',
			'showHeader'	=> 'True',
			'getAttachment'	=> 'True'
		);

		function uipreferences()
		{
			$this->t = $GLOBALS['egw']->template;
			#$this->t->egroupware_hack = False;
			$this->bofelamimail	=& CreateObject('felamimail.bofelamimail',$GLOBALS['egw']->translation->charset());
			$this->uiwidgets	=& CreateObject('felamimail.uiwidgets');
			$this->bofelamimail->openConnection('',OP_HALFOPEN);
			
			
			$this->rowColor[0] = $GLOBALS['egw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['egw_info']["theme"]["bg02"];

		}
		
		function addACL()
		{
			$this->display_app_header(FALSE);

			$this->t->set_file(array("body" => "preferences_manage_folder.tpl"));
			$this->t->set_block('body','main');
			$this->t->set_block('body','add_acl');

			$this->translate();

			$this->t->pparse("out","add_acl");			

		}
		
		// $_displayNavbar false == don't display navbar
		function display_app_header($_displayNavbar)
		{
			if(!@is_object($GLOBALS['egw']->js))
			{
				$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['egw']->js->validate_file('tabs','tabs');
			$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXCommon');
			$GLOBALS['egw']->js->validate_file('dhtmlxtree','js/dhtmlXTree');
			$GLOBALS['egw']->js->validate_file('jscode','listFolder','felamimail');
			$GLOBALS['egw']->js->validate_file('jscode','baseFunctions','felamimail');
			$GLOBALS['egw']->js->set_onload('javascript:initAll();');
			
			$GLOBALS['egw_info']['flags']['include_xajax'] = True;
			$GLOBALS['egw']->common->egw_header();
			if($_displayNavbar == TRUE)
				echo parse_navbar();
		}
		
		function listFolder()
		{
			// rename a mailbox
			if(isset($_POST['newMailboxName']))
			{
				$oldMailboxName = $this->bofelamimail->sessionData['preferences']['mailbox'];
				$newMailboxName = $_POST['newMailboxName'];
				
				if($position = strrpos($oldMailboxName,'.'))
				{
					$newMailboxName		= substr($oldMailboxName,0,$position+1).$newMailboxName;
				}
			
				
				if($this->bofelamimail->imap_renamemailbox($oldMailboxName, $newMailboxName))
				{
					$this->bofelamimail->sessionData['preferences']['mailbox']
						= $newMailboxName;
					$this->bofelamimail->saveSessionData();
				}
			}
			
			// delete a Folder
			if(isset($_POST['deleteFolder']) && $this->bofelamimail->sessionData['preferences']['mailbox'] != 'INBOX')
			{
				if($this->bofelamimail->imap_deletemailbox($this->bofelamimail->sessionData['preferences']['mailbox']))
				{
					$this->bofelamimail->sessionData['preferences']['mailbox']
						= "INBOX";
					$this->bofelamimail->saveSessionData();
				}
			}

			// create a new Mailbox
			if(isset($_POST['newSubFolder']))
			{
				$oldMailboxName = $this->bofelamimail->sessionData['preferences']['mailbox'].'.';
				$oldMailboxName	= ($oldMailboxName == '--topfolderselected--.') ? '' : $oldMailboxName;
				$newMailboxName = $oldMailboxName.$_POST['newSubFolder'];

				$this->bofelamimail->imap_createmailbox($newMailboxName,True);
			}
			
			$folderList	= $this->bofelamimail->getFolderObjects();
			// check user input BEGIN
			// the name of the new current folder
			if(get_var('mailboxName',array('POST')) && $folderList[get_var('mailboxName',array('POST'))] ||
			get_var('mailboxName',array('POST')) == '--topfolderselected--')
			{
				$this->bofelamimail->sessionData['preferences']['mailbox']
					= get_var('mailboxName',array('POST'));
				$this->bofelamimail->saveSessionData();
			}

			$this->selectedFolder	= $this->bofelamimail->sessionData['preferences']['mailbox'];
			
			// (un)subscribe to a folder??
			if(isset($_POST['folderStatus']))
			{
				$this->bofelamimail->subscribe($this->selectedFolder,$_POST['folderStatus']);
			}
			

			$this->selectedFolder	= $this->bofelamimail->sessionData['preferences']['mailbox'];

			// check user input END
			
			
			if($this->selectedFolder != '--topfolderselected--')
				$folderStatus	= $this->bofelamimail->getFolderStatus($this->selectedFolder);
			$mailPrefs	= $this->bofelamimail->getMailPreferences();
			
			$this->display_app_header(TRUE);

			$this->t->set_file(array("body" => "preferences_manage_folder.tpl"));
			$this->t->set_block('body','main');
			#$this->t->set_block('body','select_row');
			$this->t->set_block('body','folder_settings');
			$this->t->set_block('body','mainFolder_settings');
			#$this->t->set_block('body','folder_acl');

			$this->translate();
			
			#print "<pre>";print_r($folderList);print "</pre>";
			// set the default values for the sort links (sort by subject)
			$linkData = array
			(
				'menuaction'    => 'felamimail.uipreferences.listFolder'
			);
			$this->t->set_var('form_action',$GLOBALS['egw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'felamimail.uipreferences.addACL'
			);
			$this->t->set_var('url_addACL',$GLOBALS['egw']->link('/index.php',$linkData));
			
			// create the link to show folder settings
			#$linkData = array
			#(
			#	'menuaction'    => 'felamimail.uipreferences.listFolder',
			#	'display'	=> 'settings'
			#);
			#$this->t->set_var('settings_url',$GLOBALS['egw']->link('/index.php',$linkData));
			
			// create the link to show folder acl
			#$linkData = array
			#(
			#	'menuaction'    => 'felamimail.uipreferences.listFolder',
			#	'display'	=> 'acl'
			#);
			#$this->t->set_var('acl_url',$GLOBALS['egw']->link('/index.php',$linkData));
			
			// folder select box
			$folderTree = $this->uiwidgets->createHTMLFolder
			(
				$folderList, 
				$this->selectedFolder, 
				lang('IMAP Server'), 
				$mailPrefs['username'].'@'.$mailPrefs['imapServerAddress'],
				'divFolderTree',
				TRUE
			);
			$this->t->set_var('folder_tree',$folderTree);
			
			switch($_GET['display'])
			{
				#case 'acl':
				#	$uiBaseClass =& CreateObject('felamimail.uibaseclass');
				#	#$uiBaseClass->accounts_popup('calendar');
				#	$this->t->parse('settings_view','folder_acl',True);
				#	break;
					
				case 'settings':
				default:
					// selected folder data
					if($folderStatus['subscribed'])
					{
						$this->t->set_var('subscribed_checked','checked');
						$this->t->set_var('unsubscribed_checked','');
					}
					else
					{
						$this->t->set_var('subscribed_checked','');
						$this->t->set_var('unsubscribed_checked','checked');
					}
			
					if(is_array($quota))
					{
						$this->t->set_var('storage_usage',$quota['STORAGE']['usage']);
						$this->t->set_var('storage_limit',$quota['STORAGE']['limit']);
						$this->t->set_var('message_usage',$quota['MESSAGE']['usage']);
						$this->t->set_var('message_limit',$quota['MESSAGE']['limit']);
					}
					else
					{
						$this->t->set_var('storage_usage',lang('unknown'));
						$this->t->set_var('storage_limit',lang('unknown'));
						$this->t->set_var('message_usage',lang('unknown'));
						$this->t->set_var('message_limit',lang('unknown'));
					}
			
					if($this->selectedFolder != '--topfolderselected--')
					{
						$this->t->parse('settings_view','folder_settings',True);
					}
					else
					{
						$this->t->parse('settings_view','mainFolder_settings',True);
					}
					
					break;
			}
			
			$mailBoxTreeName 	= '';
			$mailBoxName		= $this->selectedFolder;
			if($position = strrpos($this->selectedFolder,'.'))
			{
				$mailBoxTreeName 	= substr($this->selectedFolder,0,$position+1);
				$mailBoxName		= substr($this->selectedFolder,$position+1);
			}
			
			$this->t->set_var('mailboxTreeName',$mailBoxTreeName);
			$this->t->set_var('mailboxNameShort',$mailBoxName);
			$this->t->set_var('mailboxName',$mailBoxName);			
			$this->t->set_var('folderName',$this->selectedFolder);
			$this->t->set_var('imap_server',$mailPrefs['imapServerAddress']);
			
			$this->t->pparse("out","main");			
			$this->bofelamimail->closeConnection();
		}
		
		function translate()
		{
			$this->t->set_var("lang_folder_name",lang('folder name'));
			$this->t->set_var("lang_folder_list",lang('folderlist'));
			$this->t->set_var("lang_select",lang('select'));
			$this->t->set_var("lang_folder_status",lang('folder status'));
			$this->t->set_var("lang_subscribed",lang('subscribed'));
			$this->t->set_var("lang_unsubscribed",lang('unsubscribed'));
			$this->t->set_var("lang_subscribe",lang('subscribe'));
			$this->t->set_var("lang_unsubscribe",lang('unsubscribe'));
			$this->t->set_var("lang_update",lang('update'));
			$this->t->set_var("lang_rename_folder",lang('rename folder'));
			$this->t->set_var("lang_create_subfolder",lang('create subfolder'));
			$this->t->set_var("lang_delete_folder",lang('delete folder'));
			$this->t->set_var("lang_confirm_delete",addslashes(lang("Do you really want to delete the '%1' folder?",$this->bofelamimail->sessionData['preferences']['mailbox'])));
			$this->t->set_var("lang_delete",lang('delete'));
			$this->t->set_var("lang_imap_server",lang('IMAP Server'));
			$this->t->set_var("lang_folder_settings",lang('folder settings'));
			$this->t->set_var("lang_folder_acl",lang('folder acl'));
			$this->t->set_var("lang_anyone",lang('anyone'));
			$this->t->set_var("lang_reading",lang('reading'));
			$this->t->set_var("lang_writing",lang('writing'));
			$this->t->set_var("lang_posting",lang('posting'));
			$this->t->set_var("lang_none",lang('none'));
			$this->t->set_var("lang_rename",lang('rename'));
			$this->t->set_var("lang_create",lang('create'));
			$this->t->set_var('lang_open_all',lang("open all"));
			$this->t->set_var('lang_close_all',lang("close all"));
			$this->t->set_var('lang_add',lang("add"));
			$this->t->set_var('lang_delete_selected',lang("delete selected"));
			$this->t->set_var('lang_cancel',lang("close"));
			$this->t->set_var('lang_ACL',lang("ACL"));
			$this->t->set_var('lang_Overview',lang("Overview"));
			
			$this->t->set_var("th_bg",$GLOBALS['egw_info']["theme"]["th_bg"]);
			$this->t->set_var("bg01",$GLOBALS['egw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['egw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['egw_info']["theme"]["bg03"]);
		}
}

?>
