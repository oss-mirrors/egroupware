<?php
	/***************************************************************************\
	* phpGroupWare - QMailLDAP                                                  *
	* http://www.phpgroupware.org                                               *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class uiuserdata
	{

		var $public_functions = array
		(
			'editUserData'	=> True,
			'saveUserData'	=> True
		);

		function uiuserdata()
		{
			global $phpgw, $phpgw_info;

			$this->t			= CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('qmailldap'));
			$this->boqmailldap		= CreateObject('qmailldap.boqmailldap');
			
			$this->rowColor[0] = $phpgw_info["theme"]["bg01"];
			$this->rowColor[1] = $phpgw_info["theme"]["bg02"];
			                 
		}
	
		function display_app_header()
		{
			global $phpgw, $phpgw_info;
			
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			
		}

		function editUserData()
		{
			global $phpgw, $phpgw_info, $HTTP_GET_VARS;
			
			$accountID = $HTTP_GET_VARS['account_id'];			

			$this->display_app_header();

			$this->translate();

			$this->t->set_file(array("editUserData" => "edituserdata.tpl"));
			$this->t->set_block('editUserData','form','form');
			$this->t->set_block('editUserData','link_row','link_row');
			$this->t->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var("tr_color1",$phpgw_info["theme"]["row_on"]);
			$this->t->set_var("tr_color2",$phpgw_info["theme"]["row_off"]);
			
			$this->t->set_var("lang_email_config",lang("edit email settings"));
			$this->t->set_var("lang_emailAddress",lang("email address"));
			$this->t->set_var("lang_emailaccount_active",lang("email account active"));
			$this->t->set_var("lang_alternateEmailAddress",lang("alternate email address"));
			$this->t->set_var("lang_forward_also_to",lang("forward also to"));
			$this->t->set_var("lang_button",lang("save"));
			$this->t->set_var("lang_deliver_extern",lang("deliver extern"));
			$this->t->set_var("lang_deliver_extern",lang("deliver extern"));
			$this->t->set_var("lang_edit_email_settings",lang("edit email settings"));
			$this->t->set_var("lang_ready",lang("Done"));
			$this->t->set_var("link_back",$phpgw->link('/admin/accounts.php'));
			
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiuserdata.saveUserData',
				'account_id'	=> $accountID
			);
			$this->t->set_var("form_action", $phpgw->link('/index.php',$linkData));
				
			// only when we show a existing user
			if($userData = $this->boqmailldap->getUserData($accountID))
			{
				$this->t->set_var("emailAddress",$userData["emailAddress"]);
				$this->t->set_var("alternateEmailAddress",$userData["alternateEmailAddress"]);
				$this->t->set_var("forwardTo",$_accountData["forwardTo"]);
				$this->t->set_var("uid",rawurlencode($_accountData["dn"]));
				if ($userData["accountStatus"] == "active")
					$this->t->set_var("account_checked","checked");
				if ($_accountData["deliverExtern"] == "active")
					$this->t->set_var("deliver_checked","checked");
			}
			else
			{
				#$this->t->set_var("emailAddress",'');
				$this->t->set_var("alternateEmailAddress",'');
				$this->t->set_var("account_checked",'');
			}
		
			// create the menu on the left, if needed		
			$menuClass = CreateObject('admin.uimenuclass');
			$this->t->set_var('rows',$menuClass->createHTMLCode('edit_account'));

			$this->t->pparse("out","form");

		}
		
		function saveUserData()
		{
			global $HTTP_POST_VARS, $HTTP_GET_VARS;
			
			if($HTTP_POST_VARS["accountStatus"] == "on")
			{
				$accountStatus = "active";
			}

			$userData = array
			(
				'emailAddress'	=> $HTTP_POST_VARS["emailAddress"],
				'accountStatus'	=> $accountStatus
			);
			$this->boqmailldap->saveUserData($HTTP_GET_VARS['account_id'], $userData);
			$this->editUserData();
		}
		
		function translate()
		{
			global $phpgw_info;			

			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);

			$this->t->set_var('lang_add',lang('add'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_remove',lang('remove'));
			$this->t->set_var('lang_remove',lang('remove'));
		}
	}
?>
