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

	class uipreferences
	{

		var $public_functions = array
		(
			'listFolder'	=> 'True',
			'showHeader'	=> 'True',
			'getAttachment'	=> 'True'
		);

		function uipreferences()
		{
			$this->t 		= $GLOBALS['phpgw']->template;
			$this->bofelamimail	= CreateObject('felamimail.bofelamimail');
			$this->bofelamimail->openConnection('',OP_HALFOPEN);
			// the name of the current folder
			if(isset($GLOBALS['HTTP_POST_VARS']['foldername']))
			{
				$this->selectedFolder	= $GLOBALS['HTTP_POST_VARS']['foldername'];
			}
			else
			{
				$this->selectedFolder	= "INBOX";
			}
			
			// (un)subscribe to a folder??
			if(isset($GLOBALS['HTTP_POST_VARS']['folderStatus']))
			{
				$this->bofelamimail->subscribe($this->selectedFolder,$GLOBALS['HTTP_POST_VARS']['folderStatus']);
			}
			
			$this->rowColor[0] = $GLOBALS['phpgw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['phpgw_info']["theme"]["bg02"];

		}
		
		function display_app_header()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}
		
		function listFolder()
		{
			$folderList	= $this->bofelamimail->getFolderList();
			$folderStatus	= $this->bofelamimail->getFolderStatus($this->selectedFolder);
			
			$this->display_app_header();

			$this->t->set_file(array("body" => "preferences_manage_folder.tpl"));
			$this->t->set_block('body','main');
			$this->t->set_block('body','select_row');

			$this->translate();
			
			#print "<pre>";print_r($folderList);print "</pre>";
			// set the default values for the sort links (sort by subject)
			$linkData = array
			(
				'menuaction'    => 'felamimail.uipreferences.listFolder'
			);
			$this->t->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			// folder select box
			while(list($key,$value) = @each($folderList))
			{
				$currentFolderStatus = $this->bofelamimail->getFolderStatus($value);
				$this->t->set_var('folder_name',$value);
				if($this->selectedFolder == $value)
				{
					$this->t->set_var('selected','selected');
				}
				else
				{
					$this->t->set_var('selected','');
				}
				if($currentFolderStatus['subscribed'])
				{
					$this->t->set_var('subscribed','S');
				}
				else
				{
					$this->t->set_var('subscribed','U');
				}
				$this->t->parse('select_rows','select_row',True);
			}
			
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
			$this->t->set_var('folderName',$this->selectedFolder);
			$this->t->pparse("out","main");			
			$this->bofelamimail->closeConnection();
		}
		
		function translate()
		{
			$this->t->set_var("lang_folder_name",lang('folder name'));
			$this->t->set_var("lang_select",lang('select'));
			$this->t->set_var("lang_folder_status",lang('folder status'));
			$this->t->set_var("lang_subscribed",lang('subscribed'));
			$this->t->set_var("lang_unsubscribed",lang('unsubscribed'));
			$this->t->set_var("lang_subscribe",lang('subscribe'));
			$this->t->set_var("lang_unsubscribe",lang('unsubscribe'));
			$this->t->set_var("lang_update",lang('update'));
			
			$this->t->set_var("th_bg",$GLOBALS['phpgw_info']["theme"]["th_bg"]);
			$this->t->set_var("bg01",$GLOBALS['phpgw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['phpgw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['phpgw_info']["theme"]["bg03"]);
		}
}

?>
