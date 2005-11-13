<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@egroupware.org]                     *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class so
	{
		var $db;
		var $table = 'egw_emailadmin';

		function so()
		{
			$this->db = clone($GLOBALS['egw']->db);
			$this->db->set_app('emailadmin');
		}
		
		function updateProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			$profileID = (int) $_globalSettings['profileID'];
			unset($_globalSettings['profileID']);

			$where = $profileID ? array('profileID' => $profileID) : false;

			$this->db->insert($this->table,$_smtpSettings+$_globalSettings+$_imapSettings,$where,__LINE__,__FILE__);

			return $profileID ? $profileID : $this->db->get_last_insert_id($this->table,'profileID');
		}

		function addProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			unset($_globalSettings['profileID']);	// just in case

			return $this->updateProfile($_globalSettings, $_smtpSettings, $_imapSettings);
		}

		function deleteProfile($_profileID)
		{
			$this->db->delete($this->table,array('profileID' => $_profileID),__LINE__ , __FILE__);
		}

		function getProfile($_profileID, $_fieldNames)
		{
			$this->db->select($this->table,$_fieldNames,array('profileID' => $_profileID), __LINE__, __FILE__);
			
			return $this->db->row(true);
		}
		
		function getProfileList($_profileID='')
		{
			$where = false;
			if ((int) $_profileID) $where = array('profileID' => $_profileID);
			
			$this->db->select($this->table,'profileID,smtpServer,smtpType,imapServer,imapType,description,ea_appname,ea_group',
				$where, __LINE__, __FILE__,false,(int) $_profileID ? '' : 'ORDER BY ea_order');

			$serverList = false;
			while (($row = $this->db->row(true)))
			{
				$serverList[] = $row;
			}
			return $serverList;
		}

		function getUserData($_accountID)
		{
			$ldap = $GLOBALS['egw']->common->ldapConnect();
			
			if (($sri = @ldap_search($ldap,$GLOBALS['egw_info']['server']['ldap_context'],"(uidnumber=$_accountID)")))
			{
				$allValues = ldap_get_entries($ldap, $sri);
				if ($allValues['count'] > 0)
				{
					#print "found something<br>";
					$userData["mailLocalAddress"]		= $allValues[0]["mail"][0];
					$userData["mailAlternateAddress"]	= $allValues[0]["mailalternateaddress"];
					$userData["accountStatus"]			= $allValues[0]["accountstatus"][0];
					$userData["mailRoutingAddress"]		= $allValues[0]["mailforwardingaddress"];
					$userData["qmailDotMode"]			= $allValues[0]["qmaildotmode"][0];
					$userData["deliveryProgramPath"]	= $allValues[0]["deliveryprogrampath"][0];
					$userData["deliveryMode"]			= $allValues[0]["deliverymode"][0];

					unset($userData["mailAlternateAddress"]["count"]);
					unset($userData["mailRoutingAddress"]["count"]);					

					return $userData;
				}
			}
			
			// if we did not return before, return false
			return false;
		}
		
		function saveUserData($_accountID, $_accountData)
		{
			$ldap = $GLOBALS['egw']->common->ldapConnect();
			// need to be fixed
			if(is_numeric($_accountID))
			{
				$filter = "uidnumber=$_accountID";
			}
			else
			{
				$filter = "uid=$_accountID";
			}

			$sri = @ldap_search($ldap,$GLOBALS['egw_info']['server']['ldap_context'],$filter);
			if ($sri)
			{
				$allValues 	= ldap_get_entries($ldap, $sri);
				$accountDN 	= $allValues[0]['dn'];
				$uid	   	= $allValues[0]['uid'][0];
				$homedirectory	= $allValues[0]['homedirectory'][0];
				$objectClasses	= $allValues[0]['objectclass'];
				
				unset($objectClasses['count']);
			}
			else
			{
				return false;
			}
			
			if(empty($homedirectory))
			{
				$homedirectory = "/home/".$uid;
			}
			
			// the old code for qmail ldap
			$newData = array 
			(
				'mail'					=> $_accountData["mailLocalAddress"],
				'mailAlternateAddress'	=> $_accountData["mailAlternateAddress"],
				'mailRoutingAddress'	=> $_accountData["mailRoutingAddress"],
				'homedirectory'			=> $homedirectory,
				'mailMessageStore'		=> $homedirectory."/Maildir/",
				'gidnumber'				=> '1000',
				'qmailDotMode'			=> $_accountData["qmailDotMode"],
				'deliveryProgramPath'	=> $_accountData["deliveryProgramPath"]
			);
			
			if(!in_array('qmailUser',$objectClasses) &&
				!in_array('qmailuser',$objectClasses))
			{
				$objectClasses[]	= 'qmailuser'; 
			}
			
			// the new code for postfix+cyrus+ldap
			$newData = array 
			(
				'mail'			=> $_accountData["mailLocalAddress"],
				'accountStatus'		=> $_accountData["accountStatus"],
				'objectclass'		=> $objectClasses
			);

			if(is_array($_accountData["mailAlternateAddress"]))
			{	
				$newData['mailAlternateAddress'] = $_accountData["mailAlternateAddress"];
			}
			else
			{
				$newData['mailAlternateAddress'] = array();
			}

			if($_accountData["accountStatus"] == 'active')
			{	
				$newData['accountStatus'] = 'active';
			}
			else
			{
				$newData['accountStatus'] = 'disabled';
			}

			if(!empty($_accountData["deliveryMode"]))
			{	
				$newData['deliveryMode'] = $_accountData["deliveryMode"];
			}
			else
			{
				$newData['deliveryMode'] = array();
			}


			if(is_array($_accountData["mailRoutingAddress"]))
			{	
				$newData['mailForwardingAddress'] = $_accountData["mailRoutingAddress"];
			}
			else
			{
				$newData['mailForwardingAddress'] = array();
			}
			
			#print "DN: $accountDN<br>";
			ldap_mod_replace ($ldap, $accountDN, $newData);
			#print ldap_error($ldap);
			
			// also update the account_email field in egw_accounts
			// when using sql account storage
			if($GLOBALS['egw_info']['server']['account_repository'] == 'sql')
			{
				$this->db->update('egw_accounts',array(
						'account_email'	=> $_accountData["mailLocalAddress"]
					),
					array(
						'account_id'	=> $_accountID
					),__LINE__,__FILE__
				);
			}
			return true;
		}
		
		function setOrder($_order)
		{
			foreach($_order as $order => $profileID)
			{
				$this->db->update($this->table,array(
					'ea_order'  => $order,
				),array(
					'profileID' => $profileID,
				),__LINE__, __FILE__);
			}
		}
	}
?>
