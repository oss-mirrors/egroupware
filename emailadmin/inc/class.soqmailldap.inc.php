<?php
	/***************************************************************************\
	* phpGroupWare - QMailLDAP                                                  *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@phpgw.de]                           *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class soqmailldap
	{
		function soqmailldap()
		{
			$this->db			= $GLOBALS['phpgw']->db;
			$this->ldap			= $GLOBALS['phpgw']->common->ldapConnect();

			$config = CreateObject('phpgwapi.config','qmailldap');
			$config->read_repository();

			if ($config->config_data)
            {
                $items = $config->config_data;

				$this->mail_address		= $config['mail'];
				$this->routing_address	= $config['routing'];
			}
		}

		function deleteServer($_serverid)
		{
			$query = "delete from phpgw_qmailldap where id='$_serverid'";
			$this->db->query($query);
		}

		function getLDAPStorageData($_serverid)
		{
			$query = "select * from phpgw_qmailldap where id='$_serverid'";
			$this->db->query($query);
			
			if ($this->db->next_record())
			{
				$storageData['qmail_servername'] 	= $this->db->f('qmail_servername');
				$storageData['description'] 		= $this->db->f('description');
				$storageData['ldap_basedn'] 		= $this->db->f('ldap_basedn');
				
				return $storageData;
			}
			else
			{
				return False;
			}
		}
		
		function getLDAPData($_serverid)
		{
			$storageData = $this->getLDAPStorageData($_serverid);

			$filter = "cn=".$storageData['qmail_servername'];

			$sri = @ldap_read($this->ldap,$storageData['ldap_basedn'],$filter);
			if ($sri)
			{
				$allValues = ldap_get_entries($ldap, $sri);
				
				unset($allValues[0]['rcpthosts']['count']);
				unset($allValues[0]['locals']['count']);
				unset($allValues[0]['smtproutes']['count']);

				$data = array
				(
					'rcpthosts'		=> $allValues[0]['rcpthosts'],
					'locals'		=> $allValues[0]['locals'],
					'smtproutes'	=> $allValues[0]['smtproutes'],
					'ldapbasedn'	=> $allValues[0]['ldapbasedn'][0]
				);
				
				#$data['smtproutes'] = array
				#(
				#	'0'	=> 't-online.de:smtprelay.t-online.de:25',
				#	'1'	=> 't-dialin.net:smtprelay.t-online.de:25'
				#);
				
				if (isset($allValues[0]['ldaplocaldelivery'][0]))
				{
					$data['ldaplocaldelivery'] = $allValues[0]['ldaplocaldelivery'][0];
				}
				else
				{
					//set to default
					$data['ldaplocaldelivery'] = 1;
				}

				if (isset($allValues[0]['ldapdefaultdotmode'][0]))
				{
					$data['ldapdefaultdotmode'] = $allValues[0]['ldapdefaultdotmode'][0];
				}
				else
				{
					//set to default
					$data['ldapdefaultdotmode'] = 'ldaponly';
				}

				return $data;
			}
			else
			{
				return False;
			}
		}

		function getServerList()
		{
			$query = "select id,qmail_servername,description from phpgw_qmailldap";
			$this->db->query($query);

			$i=0;
			while ($this->db->next_record())
			{
				$serverList[$i]['id']				= $this->db->f('id');
				$serverList[$i]['qmail_servername']	= $this->db->f('qmail_servername');
				$serverList[$i]['description']		= $this->db->f('description');
				$i++;
			}

			if ($i>0)
			{
				return $serverList;
			}
			else
			{
				return False;
			}
		}

		function getUserData($_accountID)
		{
			$filter = "(&(uidnumber=$_accountID))";

			$mail_address		= $this->mail_address;
			$routing_address	= $this->routing_address;

			$sri = @ldap_search($this->ldap,$GLOBALS['phpgw_info']['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues = ldap_get_entries($ldap, $sri);
				if ($allValues['count'] > 0)
				{
					#print "found something<br>";
					$userData['mailLocalAddress']		= $allValues[0][$mail_address][0];
					$userData['accountStatus']			= $allValues[0]['accountstatus'][0];
					$userData['mailForwardingAddress']	= $allValues[0][$routing_address][0];
					$userData['qmailDotMode']			= $allValues[0]['qmaildotmode'][0];
					$userData['deliveryProgramPath']	= $allValues[0]['deliveryprogrampath'][0];
					$userData['mailAlternateAddress']	= $allValues[0]['mailalternateaddress'];

					if ($userData['mailAlternateAddress']['count'] == 0)
					{
						$userData['mailAlternateAddress']='';
					}
					else
					{
						unset($userData['mailAlternateAddress']['count']);
					}
					return $userData;
				}
			}

			// if we did not return before, return false
			return False;
		}
		
		function saveUserData($_accountID, $_accountData)
		{
			$filter = "uidnumber=$_accountID";

			$sri = @ldap_search($this->ldap,$GLOBALS['phpgw_info']['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues 	= ldap_get_entries($ldap, $sri);
				$accountDN 	= $allValues[0]['dn'];
				$uid	   	= $allValues[0]['uid'][0];
				$homedirectory	= (isset($allValues[0]['homedirectory'][0])?$allValues[0]['homedirectory'][0]:'/home/' . $uid);
			}
			else
			{
				return False;
			}

			$mail_address		= $this->mail_address;
			$routing_address	= $this->routing_address;

			$newData = array
			(
				$mail_address	=> $_accountData['mailLocalAddress'],
				'objectclass'	=> 'qmailUser'
			);
			@ldap_mod_add($this->ldap, $accountDN, $newData);

			if(empty($homedirectory))
			{
				$homedirectory = '/home/' . $uid;
			}

			$newData = array 
			(
				$mail_address			=> (isset($_accountData['mailLocalAddress'])?$_accountData['mailLocalAddress']:$uid . '@localhost'),
				'homedirectory'			=> $homedirectory,
				'mailMessageStore'		=> $homedirectory . '/Maildir/',
				'qmailDotMode'			=> (isset($_accountData['qmailDotMode'])?$_accountData['qmailDotMode']:'ldaponly')
			);

			if ($_accountData['accountStatus'])
			{
				$newData['accountStatus'] = $_accountData['accountStatus'];
			}

			if ($_accountData['mailAlternateAddress'])
			{
				$newData['mailAlternateAddress'] = $_accountData['mailAlternateAddress'];
			}

			if ($_accountData['mailForwardingAddress'])
			{
				$newData[$routing_address] = $_accountData['mailForwardingAddress'];
			}

			if ($_accountData['deliveryProgramPath'])
			{
				$newData['deliveryProgramPath'] = $_accountData['deliveryProgramPath'];
			}

			ldap_mod_replace ($this->ldap, $accountDN, $newData);

			#print ldap_error($ldap);
		}

		function update($_action, $_data)
		{
			switch ($_action)
			{
				case 'add_server':
					$query = sprintf("insert into phpgw_qmailldap (description, ldap_basedn, qmail_servername) values ('%s','%s','%s')",
							$_data['description'],
							$_data['ldap_basedn'],
							$_data['qmail_servername']);
					$this->db->query($query);
					break;
				case 'update_server':
					$query = sprintf("update phpgw_qmailldap set description='%s',ldap_basedn='%s',qmail_servername='%s' where id='%s'",
						$_data['description'],
						$_data['ldap_basedn'],
						$_data['qmail_servername'],
						$_data['id']);
					$this->db->query($query);
					break;
			}
		}

		function writeConfigData($_data, $_serverid)
		{
			$storageData = $this->getLDAPStorageData($_serverid);
			
			#print "write Data for ".$storageData['qmail_servername']."<br>";
			
			// check if the DN exists, if not create it
			$filter = "objectclass=*";
			@ldap_read($this->ldap,$storageData['ldap_basedn'], $filter);
			if (ldap_errno($ds) == 32)
			{
				$ldapData['objectclass'][0]	= 'qmailcontrol';
				$ldapData['cn']				= $storageData['qmail_servername'];
				ldap_add($ds,$storageData['ldap_basedn'],$ldapData);
			}

			$ldapData['rcpthosts']	= $_data['rcpthosts'];
			$ldapData['locals']		= $_data['locals'];
			$ldapData['smtproutes']	= $_data['smtproutes'];

			ldap_modify($this->ldap,$storageData['ldap_basedn'],$ldapData);
		}
	}
?>
