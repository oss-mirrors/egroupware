<?php
	/***************************************************************************\
	* phpGroupWare - QMailLDAP                                                  *
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
			'addServer'		=> True,
			'deleteServer'	=> True,
			'editServer'	=> True,
			'editSettings'	=> True,
			'addSmtpRoute'	=> True,
			'save'			=> True
		);

		function uiqmailldap()
		{
			$this->cats			= CreateObject('phpgwapi.categories');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
			$this->boqmailldap	= CreateObject('qmailldap.boqmailldap');
		}

		function addServer()
		{
			$GLOBALS['phpgw']->template->set_file(array('body' => 'ldapsettings.tpl'));
			$GLOBALS['phpgw']->template->set_block('body','main');
			$GLOBALS['phpgw']->template->set_block('body','menu_row');
			$GLOBALS['phpgw']->template->set_block('body','menu_row_bold');
			$GLOBALS['phpgw']->template->set_block('body','activation_row');

			$this->translate();

			$linkData = array
			(
				'menuaction' => 'qmailldap.uiqmailldap.listServers'
			);
			$GLOBALS['phpgw']->template->set_var('done_link',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$GLOBALS['phpgw']->template->set_var('qmail_servername',$values['qmail_servername']);
			$GLOBALS['phpgw']->template->set_var('description',$values['description']);
			$GLOBALS['phpgw']->template->set_var('ldap_basedn',$values['ldap_basedn']);
			$GLOBALS['phpgw']->template->set_var('ldap_basedn',$values['ldap_basedn']);


			$linkData = array
			(
				'menuaction' => 'qmailldap.uiqmailldap.save'
			);
			$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$GLOBALS['phpgw']->xslttpl->set_var('phpgw',array
			(
				'body_data' => $GLOBALS['phpgw']->template->parse('out','main')
			));
		}

		function addSmtpRoute()
		{
			$this->translate();
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
			
			$GLOBALS['phpgw']->template->set_file(array('body' => $menu[$_pagenumber]['template']));
			$GLOBALS['phpgw']->template->set_block('body','menu_row');
			$GLOBALS['phpgw']->template->set_block('body','menu_row_bold');
			$GLOBALS['phpgw']->template->set_block('body','activation_row');

			reset($menu);
			$i=0;
			while (list($key,$value) = each($menu))
			{
				$GLOBALS['phpgw']->template->set_var('menu_description',$value['name']);
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
					'pagenumber'	=> $key,
					'serverid'	=> $_serverid
				);
				$GLOBALS['phpgw']->template->set_var('menu_link',$GLOBALS['phpgw']->link('/index.php',$linkData));

				if ($_pagenumber == $key)
				{
					$GLOBALS['phpgw']->template->parse('menu_rows','menu_row_bold',True);
				}
				else
				{
					$GLOBALS['phpgw']->template->parse('menu_rows','menu_row',True);
				}
				$i++;
			}
			
			if ($_ldapData['needActivation'] == 1)
			{
				$linkData = array
				(
					'menuaction'	=> 'qmailldap.uiqmailldap.save',
					'pagenumber'	=> $_pagenumber,
					'serverid'		=> $_serverid,
					'bo_action'		=> 'write_to_ldap'
				);
				$GLOBALS['phpgw']->template->set_var('activation_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
				$GLOBALS['phpgw']->template->parse('activation_rows','activation_row');
			}

			$linkData = array
			(
				'menuaction' => 'qmailldap.uiqmailldap.listServers',
			);
			$GLOBALS['phpgw']->template->set_var('done_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
		}
		
		function deleteServer()
		{
			$this->boqmailldap->deleteServer($GLOBALS['HTTP_GET_VARS']['serverid']);
			$this->listServers();
		}

		function editServer($_serverid='', $_pagenumber='')
		{
			$serverid		= get_var('serverid',array('GET'));
			$pagenumber		= get_var('pagenumber',array('GET'));
			//$values			= get_var('values',array('GET'));;

			if(!empty($_serverid))
			{
				$serverid=$_serverid;
			}
			if(!empty($_pagenumber))
			{
				$pagenumber=$_pagenumber;
			}

			$ldapData = $this->boqmailldap->getLDAPData($serverid);
			
			$this->createMenu($serverid, $pagenumber, $ldapData);

			$GLOBALS['phpgw']->template->set_block('body','main');

			$this->translate();
			
			$linkData = array
			(
				'menuaction'	=> 'qmailldap.uiqmailldap.save',
				'pagenumber'	=> $pagenumber,
				'serverid'		=> $serverid
			);
			$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			switch($pagenumber)
			{
				case '0':
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
						$GLOBALS['phpgw']->template->set_var('rcpt_selectbox',$selectBox);
					}
					else
					{
						$GLOBALS['phpgw']->template->set_var('rcpt_selectbox',
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
						$GLOBALS['phpgw']->template->set_var('locals_selectbox',$selectBox);
					}
					else
					{
						$GLOBALS['phpgw']->template->set_var('locals_selectbox',
							"<b>".lang("We don't deliver any email local!")."</b>");
					}
					break;
				case '15':
					$GLOBALS['phpgw']->template->set_block('body','smtproute_row');
					if (count($ldapData['smtproutes']) > 0)
					{
						for ($i=0;$i < count($ldapData['smtproutes']); $i++)
						{
							$smtproute = explode(":",$ldapData['smtproutes'][$i]);
							$GLOBALS['phpgw']->template->set_var('domain_name',$smtproute[0]);
							$GLOBALS['phpgw']->template->set_var('remote_server',$smtproute[1]);
							$GLOBALS['phpgw']->template->set_var('remote_port',$smtproute[2]);
							$linkData = array
							(
								'menuaction'	=> 'qmailldap.uiqmailldap.save',
								'bo_action'		=> 'remove_smtproute',
								'smtproute_id'	=> $i,
								'pagenumber'	=> 15,
								'serverid'		=> $serverid
							);
							$GLOBALS['phpgw']->template->set_var('delete_route_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
							$GLOBALS['phpgw']->template->parse('smtproute_rows','smtproute_row',True);
						}
					}
					
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.addSmtpRoute',
						'pagenumber'	=> 15,
						'serverid'	=> $serverid
					);
					$GLOBALS['phpgw']->template->set_var('add_route_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
					break;
				case '20':
					$GLOBALS['phpgw']->template->set_var('ldaplocaldelivery_'.$ldapData['ldaplocaldelivery'],'selected');
					$GLOBALS['phpgw']->template->set_var('ldapdefaultdotmode_'.$ldapData['ldapdefaultdotmode'],'selected');
					$GLOBALS['phpgw']->template->set_var('ldapbasedn',$ldapData['ldapbasedn']);
					break;
				case '99':
					if ($storageData = $this->boqmailldap->getLDAPStorageData($serverid))
					{
						$GLOBALS['phpgw']->template->set_var('qmail_servername',$storageData['qmail_servername']);
						$GLOBALS['phpgw']->template->set_var('description',$storageData['description']);
						$GLOBALS['phpgw']->template->set_var('ldap_basedn',$storageData['ldap_basedn']);
					}
					break;
			}

			$GLOBALS['phpgw']->xslttpl->set_var('phpgw',array
			(
				'body_data' => $GLOBALS['phpgw']->template->parse('out','main')
			));
		}
		
		function editSettings($_serverid='')
		{
			$serverid		= get_var('serverid',array('GET'));
			$HTTP_GET_VARS	= get_var('HTTP_GET_VARS',array('GET'));;

			if(!empty($_serverid)) $serverid=$_serverid;

			$ldapData = $this->boqmailldap->getLDAPData($serverid);

			$GLOBALS['phpgw']->template->set_file(array('body' => 'ldapsettings.tpl'));
			$GLOBALS['phpgw']->template->set_block('body','main');
			$GLOBALS['phpgw']->template->set_block('body','menu_row');
			$GLOBALS['phpgw']->template->set_block('body','menu_row_bold');
			$GLOBALS['phpgw']->template->set_block('body','activation_row');

			$this->translate();

			if ($storageData = $this->boqmailldap->getLDAPStorageData($serverid))
			{
				$GLOBALS['phpgw']->template->set_var('qmail_servername',$storageData['qmail_servername']);
				$GLOBALS['phpgw']->template->set_var('description',$storageData['description']);
				$GLOBALS['phpgw']->template->set_var('ldap_basedn',$storageData['ldap_basedn']);
			}

			$linkData = array
			(
				'menuaction' => 'qmailldap.uiqmailldap.listServers'
			);
			$GLOBALS['phpgw']->template->set_var('done_link',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'qmailldap.uiqmailldap.save',
				'pagenumber'    => $pagenumber,
				'serverid'      => $serverid
			);

			$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php',$linkData));
			$GLOBALS['phpgw']->xslttpl->set_var('phpgw',array
			(
				'body_data' => $GLOBALS['phpgw']->template->parse('out','main')
			));
		}
		
		function listServers()
		{
			$GLOBALS['phpgw']->template->set_file(array('body' => 'listservers.tpl'));
			$GLOBALS['phpgw']->template->set_block('body','main','main');
			$GLOBALS['phpgw']->template->set_block('body','row','row');

			$this->translate();
			$serverList = $this->boqmailldap->getServerList();

			if (is_array($serverList))
			{
				for ($i=0; $i < count($serverList); $i++)
				{
					$GLOBALS['phpgw']->template->set_var('server_name',$serverList[$i]['qmail_servername']);
					$GLOBALS['phpgw']->template->set_var('server_description',$serverList[$i]['description']);
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.editServer',
						'nocache'	=> '1',
						'pagenumber'	=> '0',
						'serverid'	=> $serverList[$i]['id']
					);
					$GLOBALS['phpgw']->template->set_var('edit_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.editSettings',
						'nocache'	=> '1',
						'serverid'	=> $serverList[$i]['id']
					);
					$GLOBALS['phpgw']->template->set_var('settings_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
					$linkData = array
					(
						'menuaction'	=> 'qmailldap.uiqmailldap.deleteServer',
						'serverid'	=> $serverList[$i]['id']
					);
					$GLOBALS['phpgw']->template->set_var('delete_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
					$GLOBALS['phpgw']->template->parse('rows','row');
				}
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('rows','');
			}

			$linkData = array
			(
				'menuaction' => 'qmailldap.uiqmailldap.addServer'
			);
			$GLOBALS['phpgw']->template->set_var('add_link',$GLOBALS['phpgw']->link('/index.php',$linkData));
			$GLOBALS['phpgw']->xslttpl->set_var('phpgw',array
			(
				'body_data' => $GLOBALS['phpgw']->template->parse('out','main')
			));
		}

		function save()
		{
			$values = get_var('values',array('POST','GET'));

			$this->boqmailldap->save($values);
			if ($values['bo_action'] == 'save_ldap')
			{
				$this->listServers();
			}
			else
			{
				$this->editServer($values['serverid'],$values['pagenumber']);
			}
		}

		function translate()
		{
			$GLOBALS['phpgw']->template->set_var('lang_server_list',lang('server list'));
			$GLOBALS['phpgw']->template->set_var('lang_server_name',lang('server name'));
			$GLOBALS['phpgw']->template->set_var('lang_server_description',lang('description'));
			$GLOBALS['phpgw']->template->set_var('lang_activate',lang('Activate'));
			$GLOBALS['phpgw']->template->set_var('lang_edit',lang('edit'));
			$GLOBALS['phpgw']->template->set_var('lang_save',lang('save'));
			$GLOBALS['phpgw']->template->set_var('lang_delete',lang('delete'));
			$GLOBALS['phpgw']->template->set_var('lang_disabled',lang('disabled'));
			$GLOBALS['phpgw']->template->set_var('lang_enabled',lang('enabled'));
			$GLOBALS['phpgw']->template->set_var('lang_add',lang('add'));
			$GLOBALS['phpgw']->template->set_var('lang_done',lang('Done'));
			$GLOBALS['phpgw']->template->set_var('lang_back',lang('back'));
			$GLOBALS['phpgw']->template->set_var('lang_remove',lang('remove'));
			$GLOBALS['phpgw']->template->set_var('lang_add_to_local',lang('add also to local domains'));
			$GLOBALS['phpgw']->template->set_var('lang_ldap_server',lang('LDAP server'));
			$GLOBALS['phpgw']->template->set_var('lang_qmail_base',lang('qmail dn'));
			$GLOBALS['phpgw']->template->set_var('lang_ldap_server_admin',lang('admin dn'));
			$GLOBALS['phpgw']->template->set_var('lang_ldap_server_password',lang('admin password'));
			$GLOBALS['phpgw']->template->set_var('lang_add_server',lang('add server'));
			$GLOBALS['phpgw']->template->set_var('lang_domain_name',lang('domainname'));
			$GLOBALS['phpgw']->template->set_var('lang_remote_server',lang('remote server'));
			$GLOBALS['phpgw']->template->set_var('lang_remote_port',lang('remote port'));
			
			$GLOBALS['phpgw']->template->set_var('desc_ldaplocaldelivery',lang('To lookup the local passwd file if the LDAP lookup finds no match. This affects qmail-lspawn and auth_* if the LDAP lookup returns nothing.'));
			$GLOBALS['phpgw']->template->set_var('desc_ldapdefaultdotmode',lang('The default interpretation of .qmail files.<br><b>Note:</b> Works only for deliveries based on LDAP lookups. Local mails use dotonly like in normal qmail.'));
			$GLOBALS['phpgw']->template->set_var('desc_ldapbasedn',lang('The base DN from where the search in the LDAP tree begins.'));
		}
	}
?>
