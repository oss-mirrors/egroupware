<?php
	/***************************************************************************\
	* phpGroupWare - Notes                                                      *
	* http://www.phpgroupware.org                                               *
	* Written by : Bettina Gille [ceb@phpgroupware.org]                         *
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
			'editUser'	=> True,
			'save'		=> True
		);

		function uiuserdata()
		{
			global $phpgw, $phpgw_info;

			$this->cats			= CreateObject('phpgwapi.categories');
			$this->nextmatchs		= CreateObject('phpgwapi.nextmatchs');
			#$this->account			= $phpgw_info['user']['account_id'];
			$this->t			= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			#$this->grants			= $phpgw->acl->get_grants('notes');
			#$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;
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

		function editUser()
		{
			global $phpgw, $phpgw_info, $HTTP_GET_VARS;
			
			$this->display_app_header();

			$template_dir = $phpgw->common->get_tpl_dir('qmailldap');
			$t = CreateObject('phpgwapi.Template',$template_dir);
			
			$t->set_file(array("editUserData" => "edituserdata.tpl"));
			$t->set_block('editUserData','form','form');
			$t->set_block('editUserData','link_row','link_row');
			$t->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);
			$t->set_var("tr_color1",$phpgw_info["theme"]["row_on"]);
			$t->set_var("tr_color2",$phpgw_info["theme"]["row_off"]);
			
			$t->set_var("lang_email_config",lang("edit email settings"));
			$t->set_var("lang_emailAddress",lang("email address"));
			$t->set_var("lang_emailaccount_active",lang("email account active"));
			$t->set_var("lang_alternateEmailAddress",lang("alternate email address"));
			$t->set_var("lang_forward_also_to",lang("forward also to"));
			$t->set_var("lang_button",lang("save"));
			$t->set_var("lang_deliver_extern",lang("deliver extern"));
			$t->set_var("lang_deliver_extern",lang("deliver extern"));
			$t->set_var("lang_edit_email_settings",lang("edit email settings"));
			$t->set_var("lang_ready",lang("Done"));
			$t->set_var("link_back",$phpgw->link('/admin/accounts.php'));
			
			$t->set_var("form_action",
				$phpgw->link('/qmailldap/modifyemailsettings.php','account_id='.$_accountData["uidnumber"]));
				
			// only when we show a existing user
			if(is_array($_accountData))
			{
				$t->set_var("emailAddress",$_accountData["emailAddress"]);
				$t->set_var("alternateEmailAddress",$_accountData["alternateEmailAddress"]);
				$t->set_var("forwardTo",$_accountData["forwardTo"]);
				$t->set_var("uid",rawurlencode($_accountData["dn"]));
				if ($_accountData["accountStatus"] == "active")
					$t->set_var("account_checked","checked");
				if ($_accountData["deliverExtern"] == "active")
					$t->set_var("deliver_checked","checked");
			}
			else
			{
				$t->set_var("account_checked","checked");
			}
		
#			$phpgw->common->hook('edit_account');

			$t->pparse("out","form");

#			return $t->get("out");
			
			
			return;
			
			if(!empty($_serverid)) $serverid=$_serverid;
			if(!empty($_pagenumber)) $pagenumber=$_pagenumber;
			
			$ldapData = $this->boqmailldap->getLDAPData($serverid);

			$this->display_app_header();
			
			$this->t->set_file(array("body" => $menu[$pagenumber]['template']));
			$this->t->set_block('body','main');
			$this->t->set_block('body','menu_row');
			$this->t->set_block('body','menu_row_bold');
			$this->t->set_block('body','activation_row');
			
			$this->translate();
			
			reset($menu);
			$i=0;
			while (list($key,$value) = each($menu))
			{
				$this->t->set_var('menu_description',$value['name']);
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
					'pagenumber'	=> $key,
					'serverid'	=> $serverid
				);
				$this->t->set_var('menu_link',$phpgw->link('/index.php',$linkData));
				$this->t->set_var('menu_row_color',$this->rowColor[$i%2]);
				if ($pagenumber == $key)
				{
					$this->t->parse('menu_rows','menu_row_bold',True);
				}
				else
				{
					$this->t->parse('menu_rows','menu_row',True);
				}
				$i++;
			}
			
			if ($ldapData['needActivation'] == 1)
			{
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.save',
					'pagenumber'	=> $pagenumber,
					'serverid'	=> $serverid,
					'bo_action'	=> 'write_to_ldap'
				);
				$this->t->set_var('activation_link',$phpgw->link('/index.php',$linkData));
				$this->t->parse('activation_rows','activation_row');
			}
			
			$this->t->set_var('done_row_color',$this->rowColor[($i)%2]);
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.listServers',
			);
			$this->t->set_var('done_link',$phpgw->link('/index.php',$linkData));
			$this->t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$phpgw_info["theme"]["bg01"]);
			$this->t->set_var('bg_02',$phpgw_info["theme"]["bg02"]);
			
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.save',
				'pagenumber'	=> $pagenumber,
				'serverid'	=> $serverid
			);
			$this->t->set_var('form_action',$phpgw->link('/index.php',$linkData));
			
			switch($pagenumber)
			{
				case "0":
					if (count($ldapData['rcpthosts']) > 0)
					{
						$selectBox  = "<select size=\"10\" name=\"rcpthosts\">\n";
						for ($i=0;$i < count($ldapData['rcpthosts']); $i++)
						{
							$selectBox .= "<option value=\"$i\">".
									$ldapData['rcpthosts'][$i].
									"</option>\n";
						}
						$selectBox .= "</select>\n";
						$this->t->set_var('rcpt_selectbox',$selectBox);
					}
					else
					{
						$this->t->set_var('rcpt_selectbox',
							"<b>".lang("We don't accept any email!")."</b>");
					}


					if (count($ldapData['locals']) > 0)
					{
						$selectBox  = "<select size=\"10\" name=\"locals\">\n";
						for ($i=0;$i < count($ldapData['locals']); $i++)
						{
							$selectBox .= "<option value=\"$i\">".
									$ldapData['locals'][$i].
									"</option>\n";
						}
						$selectBox .= "</select>\n";
						$this->t->set_var('locals_selectbox',$selectBox);
					}
					else
					{
						$this->t->set_var('locals_selectbox',
							"<b>".lang("We don't deliver any email local!")."</b>");
					}


					break;
				
				case "20":
					$this->t->set_var("ldaplocaldelivery_".$ldapData['ldaplocaldelivery'],'selected');
					$this->t->set_var("ldapdefaultdotmode_".$ldapData['ldapdefaultdotmode'],'selected');
					$this->t->set_var("ldapbasedn",$ldapData['ldapbasedn']);
				
					break;
					
				case "99":
					if ($storageData = $this->boqmailldap->getLDAPStorageData($serverid))
					{
						$this->t->set_var('qmail_servername',$storageData['qmail_servername']);
						$this->t->set_var('description',$storageData['description']);
						$this->t->set_var('ldap_basedn',$storageData['ldap_basedn']);
					}
					break;
			}
			
			$this->t->parse("out","main");
			print $this->t->get('out','main');
			
			$phpgw->common->phpgw_footer();
		}
		
		function save()
		{
			global $HTTP_POST_VARS, $HTTP_GET_VARS;

			$this->boqmailldap->save($HTTP_POST_VARS, $HTTP_GET_VARS);
			$this->editServer($HTTP_GET_VARS["serverid"],$HTTP_GET_VARS["pagenumber"]);
		}
		
		function translate()
		{
			global $phpgw_info;			

			$this->t->set_var('th_bg',$phpgw_info['theme']['th_bg']);

			$this->t->set_var('lang_add',lang('add'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_remove',lang('remove'));
		}
	}
?>
