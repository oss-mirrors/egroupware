<?php
	/***************************************************************************\
	* phpGroupWare - Notes                                                      *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@phpgroupware.org]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class uiqmailldap
	{
		#var $grants;
		#var $cat_id;
		#var $start;
		#var $search;
		#var $filter;

		var $public_functions = array
		(
			'listServers'	=> True,
			'addServer'	=> True,
			'deleteServer'	=> True,
			'editServer'	=> True,
			'editSettings'	=> True,
			'addSmtpRoute'	=> True,
			'save'		=> True
		);

		function uiqmailldap()
		{
			global $phpgw, $phpgw_info;

			$this->cats			= CreateObject('phpgwapi.categories');
			$this->nextmatchs		= CreateObject('phpgwapi.nextmatchs');
			#$this->account			= $phpgw_info['user']['account_id'];
			$this->t			= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			#$this->grants			= $phpgw->acl->get_grants('notes');
			#$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;
			$this->boqmailldap		= CreateObject('qmailldap.boqmailldap');
			
			$this->rowColor[0] = $phpgw_info["theme"]["row_on"];
			$this->rowColor[1] = $phpgw_info["theme"]["row_off"];

			$this->dataRowColor[0] = $phpgw_info["theme"]["bg01"];
			$this->dataRowColor[1] = $phpgw_info["theme"]["bg02"];
			                 
		}
		
		function addServer()
		{
			$this->display_app_header();
			
			$this->t->set_file(array("body" => 'ldapsettings.tpl'));
			$this->t->set_block('body','main');
			$this->t->set_block('body','menu_row');
			$this->t->set_block('body','menu_row_bold');
			$this->t->set_block('body','activation_row');
			
			$this->translate();

			$this->t->set_var('done_row_color',$this->rowColor[($i)%2]);
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.listServers'
			);
			$this->t->set_var('done_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$GLOBALS['phpgw_info']["theme"]["bg01"]);
			$this->t->set_var('bg_02',$GLOBALS['phpgw_info']["theme"]["bg02"]);

			$linkData = array
			(
				'menuaction'    => 'qmailldap.uiqmailldap.save'
			);
			$this->t->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',$linkData));
			                                                                                                                                                                        
			$this->t->parse("out","main");
			print $this->t->get('out','main');
			
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
	
		function addSmtpRoute()
		{
			$this->display_app_header();
			
			$this->translate();
			
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
		
		function createMenu($_serverid, $_pagenumber, $_ldapData)
		{
			$menu = array
			(
				'0'	=> array
					   (
					   	'name'		=> lang('domain names'),
					   	'template'	=> 'domainnames.tpl'
					   ),
				'10'	=> array
					   (
					   	'name'		=> lang('virtual domains'),
					   	'template'	=> 'defaultpage.tpl'
					   ),
				'15'	=> array
					   (
					   	'name'		=> lang('smtp routing'),
					   	'template'	=> 'smtprouting.tpl'
					   ),
				'20'	=> array
					   (
					   	'name'		=> lang('options'),
					   	'template'	=> 'options.tpl'
					   )
			);
			
			$this->t->set_file(array("body" => $menu[$_pagenumber]['template']));
			$this->t->set_block('body','menu_row');
			$this->t->set_block('body','menu_row_bold');
			$this->t->set_block('body','activation_row');

			reset($menu);
			$i=0;
			while (list($key,$value) = each($menu))
			{
				$this->t->set_var('menu_description',$value['name']);
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
					'pagenumber'	=> $key,
					'serverid'	=> $_serverid
				);
				$this->t->set_var('menu_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
				$this->t->set_var('menu_row_color',$this->rowColor[$i%2]);
				if ($_pagenumber == $key)
				{
					$this->t->parse('menu_rows','menu_row_bold',True);
				}
				else
				{
					$this->t->parse('menu_rows','menu_row',True);
				}
				$i++;
			}
			
			if ($_ldapData['needActivation'] == 1)
			{
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.save',
					'pagenumber'	=> $_pagenumber,
					'serverid'	=> $_serverid,
					'bo_action'	=> 'write_to_ldap'
				);
				$this->t->set_var('activation_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
				$this->t->parse('activation_rows','activation_row');
			}
			
			$this->t->set_var('done_row_color',$this->rowColor[($i)%2]);
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.listServers',
			);
			$this->t->set_var('done_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
		}
		
		function deleteServer()
		{
			$this->boqmailldap->deleteServer($GLOBALS['HTTP_GET_VARS']['serverid']);
			$this->listServers();
		}
		
		function display_app_header()
		{
			global $phpgw, $phpgw_info;
			
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			
		}

		function editServer($_serverid='', $_pagenumber='')
		{
			global $phpgw, $phpgw_info, $serverid, $pagenumber, $HTTP_GET_VARS;
			
			if(!empty($_serverid)) $serverid=$_serverid;
			if(!empty($_pagenumber)) $pagenumber=$_pagenumber;
			
			$ldapData = $this->boqmailldap->getLDAPData($serverid);

			$this->display_app_header();
			
			$this->createMenu($serverid, $pagenumber, $ldapData);

			$this->t->set_block('body','main');
			
			$this->translate();
			
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
					
				case "15":
					$this->t->set_block('body','smtproute_row');
					
					if (count($ldapData['smtproutes']) > 0)
					{
						for ($i=0;$i < count($ldapData['smtproutes']); $i++)
						{
							$smtproute = explode(":",$ldapData['smtproutes'][$i]);
							$this->t->set_var('domain_name',$smtproute[0]);
							$this->t->set_var('remote_server',$smtproute[1]);
							$this->t->set_var('remote_port',$smtproute[2]);
							$this->t->set_var('row_color',$this->dataRowColor[($i)%2]);
							$linkData = array
							(
								'menuaction'	=> 'qmailldap.uiqmailldap.save',
								'bo_action'	=> 'remove_smtproute',
								'smtproute_id'	=> $i,
								'pagenumber'	=> 15,
								'serverid'	=> $serverid
							);
							$this->t->set_var('delete_route_link',$phpgw->link('/index.php',$linkData));
							$this->t->parse('smtproute_rows','smtproute_row',True);
						}
					}
					
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.addSmtpRoute',
						'pagenumber'	=> 15,
						'serverid'	=> $serverid
					);
					$this->t->set_var('last_row_color',$this->dataRowColor[($i)%2]);
					$this->t->set_var('add_route_link',$phpgw->link('/index.php',$linkData));
					
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
		
		function editSettings($_serverid='')
		{
			global $phpgw, $phpgw_info, $serverid, $HTTP_GET_VARS;
			
			if(!empty($_serverid)) $serverid=$_serverid;
			
			$ldapData = $this->boqmailldap->getLDAPData($serverid);

			$this->display_app_header();
			
			$this->t->set_file(array("body" => 'ldapsettings.tpl'));
			$this->t->set_block('body','main');
			$this->t->set_block('body','menu_row');
			$this->t->set_block('body','menu_row_bold');
			$this->t->set_block('body','activation_row');
			
			$this->translate();

			if ($storageData = $this->boqmailldap->getLDAPStorageData($serverid))
			{
				$this->t->set_var('qmail_servername',$storageData['qmail_servername']);
				$this->t->set_var('description',$storageData['description']);
				$this->t->set_var('ldap_basedn',$storageData['ldap_basedn']);
			}

			$this->t->set_var('done_row_color',$this->rowColor[($i)%2]);
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.listServers'
			);
			$this->t->set_var('done_link',$phpgw->link('/index.php',$linkData));
			$this->t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$phpgw_info["theme"]["bg01"]);
			$this->t->set_var('bg_02',$phpgw_info["theme"]["bg02"]);
			
			$linkData = array
			(
				'menuaction'    => 'qmailldap.uiqmailldap.save',
				'pagenumber'    => $pagenumber,
				'serverid'      => $serverid
			);
			$this->t->set_var('form_action',$phpgw->link('/index.php',$linkData));
			                                                                                                                                                                        
			$this->t->parse("out","main");
			print $this->t->get('out','main');
			
			$phpgw->common->phpgw_footer();
		}
		
		function listServers()
		{
			global $phpgw, $phpgw_info;
			
			$this->display_app_header();
			
			$this->t->set_file(array("body" => "listservers.tpl"));
			$this->t->set_block('body','main','main');
			$this->t->set_block('body','row','row');
			
			$this->translate();

			$serverList = $this->boqmailldap->getServerList();
			
			if ($serverList)
			{
				for ($i=0; $i < count($serverList); $i++)
				{
					$this->t->set_var('server_name',$serverList[$i]['qmail_servername']);
					$this->t->set_var('server_description',$serverList[$i]['description']);
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
						'nocache'	=> '1',
						'pagenumber'	=> '0',
						'serverid'	=> $serverList[$i]['id']
					);
					$this->t->set_var('edit_link',$phpgw->link('/index.php',$linkData));
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.editSettings',
						'nocache'	=> '1',
						'serverid'	=> $serverList[$i]['id']
					);
					$this->t->set_var('settings_link',$phpgw->link('/index.php',$linkData));
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.deleteServer',
						'serverid'	=> $serverList[$i]['id']
					);
					$this->t->set_var('delete_link',$phpgw->link('/index.php',$linkData));
					$this->t->set_var('row_color',$this->rowColor[$i%2]);
					$this->t->parse('rows','row',True);
				}
			}
			
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.addServer'
			);
			$this->t->set_var('add_link',$phpgw->link('/index.php',$linkData));
			
			$this->t->parse("out","main");
			
			print $this->t->get('out','main');
			
			$phpgw->common->phpgw_footer();
		}


		function save()
		{
			global $HTTP_POST_VARS, $HTTP_GET_VARS;

			$this->boqmailldap->save($HTTP_POST_VARS, $HTTP_GET_VARS);
			if ($HTTP_POST_VARS['bo_action'] == 'save_ldap' || $HTTP_GET_VARS['bo_action'] == 'save_ldap')
			{
				$this->listServers();
			}
			else
			{
				$this->editServer($HTTP_GET_VARS["serverid"],$HTTP_GET_VARS["pagenumber"]);
			}
		}
		
		function translate()
		{
			global $phpgw_info;			

			$this->t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$phpgw_info["theme"]["bg01"]);
			$this->t->set_var('bg_02',$phpgw_info["theme"]["bg02"]);

			$this->t->set_var('lang_server_list',lang('server list'));
			$this->t->set_var('lang_server_name',lang('server name'));
			$this->t->set_var('lang_server_description',lang('description'));
			$this->t->set_var('lang_activate',lang('Activate'));
			$this->t->set_var('lang_edit',lang('edit'));
			$this->t->set_var('lang_save',lang('save'));
			$this->t->set_var('lang_delete',lang('delete'));
			$this->t->set_var('lang_disabled',lang('disabled'));
			$this->t->set_var('lang_enabled',lang('enabled'));
			$this->t->set_var('lang_add',lang('add'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_back',lang('back'));
			$this->t->set_var('lang_remove',lang('remove'));
			$this->t->set_var('lang_add_to_local',lang('add also to local domains'));
			$this->t->set_var('lang_ldap_server',lang('LDAP server'));
			$this->t->set_var('lang_ldap_basedn',lang('LDAP basedn'));
			$this->t->set_var('lang_ldap_server_admin',lang('admin dn'));
			$this->t->set_var('lang_ldap_server_password',lang('admin password'));
			$this->t->set_var('lang_add_server',lang('add server'));
			$this->t->set_var('lang_domain_name',lang('domainname'));
			$this->t->set_var('lang_remote_server',lang('remote server'));
			$this->t->set_var('lang_remote_port',lang('remote port'));
			
			$this->t->set_var('desc_ldaplocaldelivery',lang('To lookup the local passwd file if the LDAP lookup finds no match. This affects qmail-lspawn and auth_* if the LDAP lookup returns nothing.'));
			$this->t->set_var('desc_ldapdefaultdotmode',lang('The default interpretation of .qmail files.<br><b>Note:</b> Works only for deliveries based on LDAP lookups. Local mails use dotonly like in normal qmail.'));
			$this->t->set_var('desc_ldapbasedn',lang('The base DN from where the search in the LDAP tree begins.'));
		}
	}
?>
