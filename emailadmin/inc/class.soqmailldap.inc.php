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

	class soqmailldap
	{
		function soqmailldap()
		{
			global $phpgw, $phpgw_info;

			$this->db		= $phpgw->db;
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
				return false;
			}
		}
		
		function getLDAPData($_serverid)
		{
			global $phpgw;
		
			$storageData = $this->getLDAPStorageData($_serverid);
			
			$ldap = $phpgw->common->ldapConnect();
			$filter = "cn=".$storageData['qmail_servername'];
			
			$sri = @ldap_read($ldap,$storageData['ldap_basedn'],$filter);
			if ($sri)
			{
				$allValues = ldap_get_entries($ldap, $sri);
				
				unset($allValues[0]['rcpthosts']['count']);
				unset($allValues[0]['locals']['count']);
				unset($allValues[0]['smtproutes']['count']);
				
				$data = array
				(
					'rcpthosts'	=> $allValues[0]['rcpthosts'],
					'locals'	=> $allValues[0]['locals'],
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
				return false;
			}
			
		}
		
		function getServerList()
		{
			$query = "select id,qmail_servername,description from phpgw_qmailldap";
			$this->db->query($query);
			
			$i=0;
			while ($this->db->next_record())
			{
				$serverList[$i]['id'] 			= $this->db->f('id');
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
				return false;
			}
		}

		function getUserData($_accountID)
		{
			global $phpgw, $phpgw_info;

			$ldap = $phpgw->common->ldapConnect();
			$filter = "(&(uidnumber=$_accountID))";
			
			$sri = @ldap_search($ldap,$phpgw_info['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues = ldap_get_entries($ldap, $sri);
				if ($allValues['count'] > 0)
				{
					#print "found something<br>";
					$userData["mailLocalAddress"]		= $allValues[0]["maillocaladdress"][0];
					$userData["mailAlternateAddress"]	= $allValues[0]["mailalternateaddress"];
					$userData["accountStatus"]		= $allValues[0]["accountstatus"][0];
					$userData["mailRoutingAddress"]		= $allValues[0]["mailroutingaddress"][0];
					$userData["qmailDotMode"]		= $allValues[0]["qmaildotmode"][0];
					$userData["deliveryProgramPath"]	= $allValues[0]["deliveryprogrampath"][0];
					if ($userData["mailAlternateAddress"]["count"] == 0)
					{
						$userData["mailAlternateAddress"]='';
					}
					else
					{
						unset($userData["mailAlternateAddress"]["count"]);
					}
					return $userData;
				}
			}
			
			// if we did not return before, return false
			return false;
		}
		
		function saveUserData($_accountID, $_accountData)
		{
			global $phpgw, $phpgw_info;
			
			$ldap = $phpgw->common->ldapConnect();
			$filter = "uidnumber=$_accountID";
			
			$sri = @ldap_search($ldap,$phpgw_info['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues 	= ldap_get_entries($ldap, $sri);
				$accountDN 	= $allValues[0]['dn'];
				$uid	   	= $allValues[0]['uid'][0];
				$homedirectory	= $allValues[0]['homedirectory'][0];
			}
			else
			{
				return false;
			}
			
			if(empty($homedirectory))
			{
				$homedirectory = "/home/".$uid;
			}
			
			$newData = array 
			(
				'mailLocalAddress'	=> $_accountData["mailLocalAddress"],
				'mailAlternateAddress'	=> $_accountData["mailAlternateAddress"],
				'mailRoutingAddress'	=> $_accountData["mailRoutingAddress"],
				'homedirectory'		=> $homedirectory,
				'mailMessageStore'	=> $homedirectory."/Maildir/",
				'gidnumber'		=> '1000',
				'qmailDotMode'		=> $_accountData["qmailDotMode"],
				'deliveryProgramPath'	=> $_accountData["deliveryProgramPath"],
				'accountStatus'		=> $_accountData["accountStatus"]
			);
			ldap_mod_replace ($ldap, $accountDN, $newData);
			
			$newData = array
			(
				'objectclass'	=> "qmailUser"
			);
			@ldap_mod_add($ldap, $accountDN, $newData);
			#print ldap_error($ldap);
		}

		function update($_action, $_data)
		{
			switch ($_action)
			{
				case "add_server":
					$query = sprintf("insert into phpgw_qmailldap (description, ldap_basedn, qmail_servername)
							values ('%s','%s','%s')",
							$_data['description'],
							$_data['ldap_basedn'],
							$_data["qmail_servername"]);
					$this->db->query($query);
					break;
					
				case "update_server":
					$query = sprintf("update phpgw_qmailldap set 
							  description='%s',
							  ldap_basedn='%s',
							  qmail_servername='%s' where id='%s'",
						$_data['description'],
						$_data['ldap_basedn'],
						$_data["qmail_servername"],
						$_data["id"]);
					$this->db->query($query);
					break;
			}
		}

		function writeConfigData($_data, $_serverid)
		{
			global $phpgw;
		
			$storageData = $this->getLDAPStorageData($_serverid);
			
			#print "write Data for ".$storageData['qmail_servername']."<br>";
			
			$ds = $phpgw->common->ldapConnect();
			
			// check if the DN exists, if not create it
			$filter = "objectclass=*";
			@ldap_read($ds,$storageData['ldap_basedn'], $filter);
			if (ldap_errno($ds) == 32)
			{
				$ldapData["objectclass"][0] 	= "qmailcontrol";
				$ldapData["cn"]         	= $storageData['qmail_servername'];
				ldap_add($ds,$storageData['ldap_basedn'],$ldapData);
			}
			
			$ldapData['rcpthosts']		= $_data['rcpthosts'];
			$ldapData['locals']		= $_data['locals'];
			$ldapData['smtproutes']		= $_data['smtproutes'];
			
			ldap_modify($ds,$storageData['ldap_basedn'],$ldapData);
		}
	}
?>
