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

	class boqmailldap
	{
		var $sessionData;
		var $LDAPData;

		var $public_functions = array
		(
			'getServerList'		=> True,
			'getLocals'		=> True,
			'getRcptHosts'		=> True,
			'getLDAPStorageData'	=> True,
			'abcdefgh'		=> True
		);

		function boqmailldap()
		{
			$this->soqmailldap = CreateObject('qmailldap.soqmailldap');
			$this->restoreSessionData();
		}

		function deleteServer($_serverid)
		{
			$this->soqmailldap->deleteServer($_serverid);
		}
		
		function getLDAPData($_serverid, $_nocache=0)
		{
			if ($GLOBALS['HTTP_GET_VARS']['nocache'] == '1' || $_nocache == '1')
			{
				#print "option1<br>";
				$LDAPData = $this->soqmailldap->getLDAPData($_serverid);
				$this->sessionData[$_serverid] = $LDAPData;
				$this->sessionData[$_serverid]['needActivation'] = 0;

				$this->saveSessionData();

				#while(list($key, $value) = each($this->sessionData[$_serverid]['rcpthosts']))
				#{
				#	print "... $key: $value<br>";
				#}

				return $this->sessionData[$_serverid];
			}
			else
			{
				#print "option2<br>";
				#while(list($key, $value) = each($this->sessionData[$_serverid]['rcpthosts']))
				#{
				#	print ".... $key: $value<br>";
				#}
				return $this->sessionData[$_serverid];
			}
		}

		function getLDAPStorageData($_serverid)
		{
			$storageData = $this->soqmailldap->getLDAPStorageData($_serverid);
			return $storageData;
		}

		function getServerList()
		{
			$serverList = $this->soqmailldap->getServerList();
			return $serverList;
		}

		function getUserData($_accountID, $_usecache)
		{
			if ($_usecache)
			{
				$userData = $this->userSessionData[$_accountID];
			}
			else
			{
				$userData = $this->soqmailldap->getUserData($_accountID);
				$this->userSessionData[$_accountID] = $userData;
				$this->saveSessionData();
			}
			return $userData;
		}

		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['phpgw']->session->appsession('session_data');
			$this->userSessionData = $GLOBALS['phpgw']->session->appsession('user_session_data');

			#while(list($key, $value) = each($this->sessionData))
			#{
			#	print "++ $key: $value<br>";
			#}
			#print "restored Session<br>";
		}
		
		function save($_postVars, $_getVars)
		{
			$serverid = $_getVars['serverid'];

			if (isset($_postVars["bo_action"]))
			{
				$bo_action = $_postVars["bo_action"];
			}
			elseif (isset($_getVars["bo_action"]))
			{
				$bo_action = $_getVars["bo_action"];
			}
			else
			{
				return false;
			}

			#print "bo_action: $bo_action<br>";

			switch ($bo_action)
			{
				case 'add_locals':
					$count = count($this->sessionData[$serverid]['locals']);

					$this->sessionData[$serverid]['locals'][$count] = 
						$_postVars['new_local'];

					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'add_rcpthosts':
					$count = count($this->sessionData[$serverid]['rcpthosts']);
					$this->sessionData[$serverid]['rcpthosts'][$count] = $_postVars['new_rcpthost'];
					if ($_postVars['add_to_local'] == 'on')
					{
						$count = count($this->sessionData[$serverid]['locals']);
						
						$this->sessionData[$serverid]['locals'][$count] = 
							$_postVars["new_rcpthost"];
					}
					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'add_smtproute':
					$count = count($this->sessionData[$serverid]['smtproutes']);
					$this->sessionData[$serverid]['smtproutes'][$count] = sprintf("%s:%s:%s",
																			$_postVars['domain_name'],
																			$_postVars['remote_server'],
																			$_postVars['remote_port']);
					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'remove_locals':
					$i=0;
					while(list($key, $value) = each($this->sessionData[$serverid]['locals']))
					{
						#print ".. $key: $value<br>";
						if ($key != $_postVars['locals'])
						{
							$newLocals[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->sessionData[$serverid]['locals'] = $newLocals;
					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'remove_rcpthosts':
					$i=0;
					while(list($key, $value) = each($this->sessionData[$serverid]['rcpthosts']))
					{
						#print ".. $key: $value<br>";
						if ($key != $_postVars['rcpthosts'])
						{
							$newRcpthosts[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->sessionData[$serverid]['rcpthosts'] = $newRcpthosts;
					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'remove_smtproute':
					$i=0;
					while(list($key, $value) = each($this->sessionData[$serverid]['smtproutes']))
					{
						#print ".. $key: $value : ".$_getVars["smtproute_id"]."<br>";
						if ($key != $_getVars['smtproute_id'])
						{
							$newSmtproutes[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->sessionData[$serverid]['smtproutes'] = $newSmtproutes;
					$this->sessionData[$serverid]['needActivation'] = 1;
					$this->saveSessionData();
					break;
				case 'save_ldap':
					#print "hallo".$_getVars["serverid"]." ".$_postVars["servername"]."<br>";
					$data = array
					(
						'qmail_servername'	=> $_postVars['qmail_servername'],
						'description'		=> $_postVars['description'],
						'ldap_basedn'		=> $_postVars['ldap_basedn'],
						'id'				=> $_getVars['serverid']
					);
					if (!isset($_getVars['serverid']))
					{
						$this->soqmailldap->update('add_server',$data);
					}
					else
					{
						$this->soqmailldap->update('update_server',$data);
					}
					$this->getLDAPData($_getVars['serverid'], '1');
					break;
				case 'write_to_ldap':
					$this->soqmailldap->writeConfigData($this->sessionData[$serverid], $serverid);
					$this->sessionData[$serverid]['needActivation'] = 0;
					$this->saveSessionData();
					break;
			}
		}

		function saveSessionData()
		{
			$GLOBALS['phpgw']->session->appsession('session_data','',$this->sessionData);
			$GLOBALS['phpgw']->session->appsession('user_session_data','',$this->userSessionData);
		}

		function saveUserData($_accountID, $_formData, $_boAction)
		{
			$this->userSessionData[$_accountID]['mailLocalAddress'] 	= $_formData['mailLocalAddress'];
			$this->userSessionData[$_accountID]['accountStatus'] 		= $_formData['accountStatus'];
			$this->userSessionData[$_accountID]['mailRoutingAddress'] 	= $_formData['mailRoutingAddress'];
			$this->userSessionData[$_accountID]['qmailDotMode'] 		= $_formData['qmailDotMode'];
			$this->userSessionData[$_accountID]['deliveryProgramPath'] 	= $_formData['deliveryProgramPath'];

			switch ($_boAction)
			{
				case 'add_mailAlternateAddress':
					if (is_array($this->userSessionData[$_accountID]['mailAlternateAddress']))
					{
						$count = count($this->userSessionData[$_accountID]['mailAlternateAddress']);
					}
					else
					{
						$count = 0;
						$this->userSessionData[$_accountID]['mailAlternateAddress'] = array();
					}
					$this->userSessionData[$_accountID]['mailAlternateAddress'][$count] = 
						$_formData['add_mailAlternateAddress'];
						
					$this->saveSessionData();
					break;
				case 'remove_mailAlternateAddress':
					$i=0;
					while(list($key, $value) = each($this->userSessionData[$_accountID]['mailAlternateAddress']))
					{
						#print ".. $key: $value<br>";
						if ($key != $_formData['remove_mailAlternateAddress'])
						{
							$newMailAlternateAddress[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->userSessionData[$_accountID]['mailAlternateAddress'] = $newMailAlternateAddress;
					$this->saveSessionData();
					break;
				case 'save':
					$this->soqmailldap->saveUserData($_accountID, $this->userSessionData[$_accountID]);
					break;
			}
		}
	}
?>
