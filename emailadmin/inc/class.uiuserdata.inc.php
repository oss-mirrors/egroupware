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
			$this->boqmailldap = CreateObject('qmailldap.boqmailldap');
		}

		function editUserData($_useCache='0')
		{
			$accountID = get_var('account_id',array('GET'));			
			$this->translate();

			$GLOBALS['phpgw']->template->set_file(array('editUserData' => 'edituserdata.tpl'));
			$GLOBALS['phpgw']->template->set_block('editUserData','form','form');
			$GLOBALS['phpgw']->template->set_block('editUserData','link_row','link_row');

			$GLOBALS['phpgw']->template->set_var('lang_email_config',lang('edit email settings'));
			$GLOBALS['phpgw']->template->set_var('lang_emailAddress',lang('email address'));
			$GLOBALS['phpgw']->template->set_var('lang_emailaccount_active',lang('email account active'));
			$GLOBALS['phpgw']->template->set_var('lang_mailAlternateAddress',lang('alternate email address'));
			$GLOBALS['phpgw']->template->set_var('lang_mailRoutingAddress',lang('forward emails to'));
			$GLOBALS['phpgw']->template->set_var('lang_forward_also_to',lang('forward also to'));
			$GLOBALS['phpgw']->template->set_var('lang_button',lang('save'));
			$GLOBALS['phpgw']->template->set_var('lang_deliver_extern',lang('deliver extern'));
			$GLOBALS['phpgw']->template->set_var('lang_deliver_extern',lang('deliver extern'));
			$GLOBALS['phpgw']->template->set_var('lang_edit_email_settings',lang('edit email settings'));
			$GLOBALS['phpgw']->template->set_var('lang_ready',lang("Done"));
			$GLOBALS['phpgw']->template->set_var('link_back',$GLOBALS['phpgw']->link('/admin/accounts.php'));
			
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiuserdata.saveUserData',
				'account_id'	=> $accountID
			);
			$GLOBALS['phpgw']->template->set_var("form_action", $GLOBALS['phpgw']->link('/index.php',$linkData));
			
			// only when we show a existing user
			if($userData = $this->boqmailldap->getUserData($accountID, $_useCache))
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

				$GLOBALS['phpgw']->template->set_var('mailLocalAddress',$userData['mailLocalAddress']);
				$GLOBALS['phpgw']->template->set_var('mailAlternateAddress','');
				$GLOBALS['phpgw']->template->set_var('options_mailAlternateAddress',$options_mailAlternateAddress);
				$GLOBALS['phpgw']->template->set_var('mailRoutingAddress',$userData['mailRoutingAddress']);
				$GLOBALS['phpgw']->template->set_var('selected_'.$userData['qmailDotMode'],'selected');
				$GLOBALS['phpgw']->template->set_var('deliveryProgramPath',$userData['deliveryProgramPath']);
				
				$GLOBALS['phpgw']->template->set_var('uid',rawurlencode($_accountData['dn']));
				if ($userData["accountStatus"] == 'active')
				{
					$GLOBALS['phpgw']->template->set_var('account_checked','checked');
				}
				if ($_accountData['deliverExtern'] == 'active')
				{
					$GLOBALS['phpgw']->template->set_var('deliver_checked','checked');
				}
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('mailLocalAddress','');
				$GLOBALS['phpgw']->template->set_var('mailAlternateAddress','');
				$GLOBALS['phpgw']->template->set_var('mailRoutingAddress','');
				$GLOBALS['phpgw']->template->set_var('options_mailAlternateAddress',lang('no alternate email address'));
				$GLOBALS['phpgw']->template->set_var('account_checked','');
			}
		
			// create the menu on the left, if needed		
			$menuClass = CreateObject('admin.uimenuclass');
			$GLOBALS['phpgw']->template->set_var('rows',$menuClass->createHTMLCode('edit_user'));

			$GLOBALS['phpgw']->template->pparse("out","form");

		}
		
		function saveUserData()
		{
			$HTTP_POST_VARS	= get_var('HTTP_POST_VARS',array('POST'));
			$HTTP_GET_VARS	= get_var('HTTP_GET_VARS',array('GET'));
			
			if($HTTP_POST_VARS['accountStatus'] == 'on')
			{
				$accountStatus = 'active';
			}

			$formData = array
			(
				'mailLocalAddress'				=> $HTTP_POST_VARS['mailLocalAddress'],
				'mailRoutingAddress'			=> $HTTP_POST_VARS['mailRoutingAddress'],
				'add_mailAlternateAddress'		=> $HTTP_POST_VARS['mailAlternateAddressInput'],
				'remove_mailAlternateAddress'	=> $HTTP_POST_VARS['mailAlternateAddress'],
				'qmailDotMode'					=> $HTTP_POST_VARS['qmailDotMode'],
				'deliveryProgramPath'			=> $HTTP_POST_VARS['deliveryProgramPath'],
				'accountStatus'					=> $accountStatus
			);

			if($HTTP_POST_VARS['add_mailAlternateAddress'])
			{
				$bo_action='add_mailAlternateAddress';
			}
			if($HTTP_POST_VARS['remove_mailAlternateAddress'])
			{
				$bo_action='remove_mailAlternateAddress';
			}
			if($HTTP_POST_VARS['save'])
			{
				$bo_action='save';
			}
			$this->boqmailldap->saveUserData($HTTP_GET_VARS['account_id'], $formData, $bo_action);

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
			$GLOBALS['phpgw']->template->set_var('lang_add',lang('add'));
			$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
			$GLOBALS['phpgw']->template->set_var('lang_remove',lang('remove'));
			$GLOBALS['phpgw']->template->set_var('lang_remove',lang('remove'));
			$GLOBALS['phpgw']->template->set_var('lang_advanced_options',lang('advanced options'));
			$GLOBALS['phpgw']->template->set_var('lang_qmaildotmode',lang('qmaildotmode'));
			$GLOBALS['phpgw']->template->set_var('lang_default',lang('default'));
			$GLOBALS['phpgw']->template->set_var('lang_deliveryProgramPath',lang('deliveryProgramPath'));
		}
	}
?>
