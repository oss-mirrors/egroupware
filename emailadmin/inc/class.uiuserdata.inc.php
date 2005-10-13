<?php
	/***************************************************************************\
	* eGroupWare                                                                *
	* http://www.egroupware.org                                                 *
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
			$this->t			= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$this->boemailadmin		= CreateObject('emailadmin.bo');
		}
	
		function display_app_header()
		{
			$GLOBALS['egw']->common->egw_header();
			echo parse_navbar();
		}

		function editUserData($_useCache='0')
		{
			$accountID = $_GET['account_id'];			
			$GLOBALS['account_id'] = $accountID;

			$this->display_app_header();

			$this->translate();

			$this->t->set_file(array("editUserData" => "edituserdata.tpl"));
			$this->t->set_block('editUserData','form','form');
			$this->t->set_block('editUserData','link_row','link_row');
			$this->t->set_var("th_bg",$GLOBALS['egw_info']["theme"]["th_bg"]);
			$this->t->set_var("tr_color1",$GLOBALS['egw_info']["theme"]["row_on"]);
			$this->t->set_var("tr_color2",$GLOBALS['egw_info']["theme"]["row_off"]);

			$this->t->set_var("lang_email_config",lang("edit email settings"));
			$this->t->set_var("lang_emailAddress",lang("email address"));
			$this->t->set_var("lang_emailaccount_active",lang("email account active"));
			$this->t->set_var("lang_mailAlternateAddress",lang("alternate email address"));
			$this->t->set_var("lang_mailRoutingAddress",lang("forward email's to"));
			$this->t->set_var("lang_forward_also_to",lang("forward also to"));
			$this->t->set_var("lang_button",lang("save"));
			$this->t->set_var("lang_deliver_extern",lang("deliver extern"));
			$this->t->set_var("lang_deliver_extern",lang("deliver extern"));
			$this->t->set_var("lang_edit_email_settings",lang("edit email settings"));
			$this->t->set_var("lang_ready",lang("Done"));
			$this->t->set_var("link_back",$GLOBALS['egw']->link('/admin/accounts.php'));

			$linkData = array
			(
				'menuaction'	=> 'emailadmin.uiuserdata.saveUserData',
				'account_id'	=> $accountID
			);
			$this->t->set_var("form_action", $GLOBALS['egw']->link('/index.php',$linkData));

			// only when we show a existing user
			if($userData = $this->boemailadmin->getUserData($accountID, $_useCache))
			{
				if ($userData['mailAlternateAddress'] != '')
				{
					$options_mailAlternateAddress = "<select size=\"6\" name=\"mailAlternateAddress\">\n";
					for ($i=0;$i < count($userData['mailAlternateAddress']); $i++)
					{
						$options_mailAlternateAddress .= "<option value=\"$i\">".
							$userData['mailAlternateAddress'][$i].
							"</option>\n";
					}
					$options_mailAlternateAddress .= "</select>\n";
				}
				else
				{
					$options_mailAlternateAddress = lang('no alternate email address');
				}
			
				if ($userData['mailRoutingAddress'] != '')
				{
					$options_mailRoutingAddress = "<select size=\"6\" name=\"mailRoutingAddress\">\n";
					for ($i=0;$i < count($userData['mailRoutingAddress']); $i++)
					{
						$options_mailRoutingAddress .= "<option value=\"$i\">".
							$userData['mailRoutingAddress'][$i].
							"</option>\n";
					}
					$options_mailRoutingAddress .= "</select>\n";
				}
				else
				{
					$options_mailRoutingAddress = lang('no forwarding email address');
				}
				
				$this->t->set_var("quotaLimit",$userData["quotaLimit"]);
			
				$this->t->set_var("mailLocalAddress",$userData["mailLocalAddress"]);
				$this->t->set_var("mailAlternateAddress",'');
				$this->t->set_var("mailRoutingAddress",'');
				$this->t->set_var("options_mailAlternateAddress",$options_mailAlternateAddress);
				$this->t->set_var("options_mailRoutingAddress",$options_mailRoutingAddress);
				$this->t->set_var("selected_".$userData["qmailDotMode"],'selected');
				$this->t->set_var("deliveryProgramPath",$userData["deliveryProgramPath"]);
				
				$this->t->set_var("uid",rawurlencode($_accountData["dn"]));
				if ($userData["accountStatus"] == "active")
					$this->t->set_var("account_checked","checked");
				if ($userData["deliveryMode"] == "forwardOnly")
					$this->t->set_var("forwardOnly_checked","checked");
				if ($_accountData["deliverExtern"] == "active")
					$this->t->set_var("deliver_checked","checked");
			}
			else
			{
				$this->t->set_var("mailLocalAddress",'');
				$this->t->set_var("mailAlternateAddress",'');
				$this->t->set_var("mailRoutingAddress",'');
				$this->t->set_var("options_mailAlternateAddress",lang('no alternate email address'));
				$this->t->set_var("options_mailRoutingAddress",lang('no forwarding email address'));
				$this->t->set_var("account_checked",'');
				$this->t->set_var("forwardOnly_checked",'');
			}
		
			// create the menu on the left, if needed		
			$menuClass = CreateObject('admin.uimenuclass');
			$this->t->set_var('rows',$menuClass->createHTMLCode('edit_user'));

			$this->t->pparse("out","form");

		}
		
		function saveUserData()
		{
			if($_POST["accountStatus"] == "on")
			{
				$accountStatus = "active";
			}
			if($_POST["forwardOnly"] == "on")
			{
				$deliveryMode = "forwardOnly";
			}

			$formData = array
			(
				'mailLocalAddress'		=> $_POST["mailLocalAddress"],
				'mailRoutingAddress'		=> $_POST["mailRoutingAddress"],
				'add_mailAlternateAddress'	=> $_POST["mailAlternateAddressInput"],
				'remove_mailAlternateAddress'	=> $_POST["mailAlternateAddress"],
				'quotaLimit'			=> $_POST["quotaLimit"],
				'add_mailRoutingAddress'	=> $_POST["mailRoutingAddressInput"],
				'remove_mailRoutingAddress'	=> $_POST["mailRoutingAddress"],
				
				'qmailDotMode'			=> $_POST["qmailDotMode"],
				'deliveryProgramPath'		=> $_POST["deliveryProgramPath"],
				'accountStatus'			=> $accountStatus,
				'deliveryMode'			=> $deliveryMode
			);
			
			if($_POST["add_mailAlternateAddress"]) $bo_action='add_mailAlternateAddress';
			if($_POST["remove_mailAlternateAddress"]) $bo_action='remove_mailAlternateAddress';
			if($_POST["add_mailRoutingAddress"]) $bo_action='add_mailRoutingAddress';
			if($_POST["remove_mailRoutingAddress"]) $bo_action='remove_mailRoutingAddress';
			if($_POST["save"]) $bo_action='save';
			
			$this->boemailadmin->saveUserData($_GET['account_id'], $formData, $bo_action);

			if ($bo_action == 'save')
			{
				// read date fresh from ldap storage
				$this->editUserData();
			}
			else
			{
				// use cached data
				$this->editUserData('1');
			}
		}
		
		function translate()
		{
			$this->t->set_var('th_bg',$GLOBALS['egw_info']['theme']['th_bg']);

			$this->t->set_var('lang_add',lang('add'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_remove',lang('remove'));
			$this->t->set_var('lang_remove',lang('remove'));
			$this->t->set_var('lang_advanced_options',lang('advanced options'));
			$this->t->set_var('lang_qmaildotmode',lang('qmaildotmode'));
			$this->t->set_var('lang_default',lang('default'));
			$this->t->set_var('lang_quota_settings',lang('quota settings'));
			$this->t->set_var('lang_qoutainmbyte',lang('qouta size in MByte'));
			$this->t->set_var('lang_inmbyte',lang('in MByte'));
			$this->t->set_var('lang_0forunlimited',lang('leave empty for no quota'));
			$this->t->set_var('lang_forward_only',lang('forward only'));
		}
	}
?>
