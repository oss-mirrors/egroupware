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
		function so()
		{
			$this->db		= $GLOBALS['phpgw']->db;
		}
		
		function addProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			$fields = '';
			$values = '';
			
			foreach($_smtpSettings as $key => $value)
			{
				if($fields != '')
					$fields .= ',';
				if($values != '')
					$values .= ',';
				$fields .= "$key";
				$values .= "'$value'";
			}
			
			foreach($_globalSettings as $key => $value)
			{
				if($key == 'profileID')
					continue;
				if($fields != '')
					$fields .= ',';
				if($values != '')
					$values .= ',';
				$fields .= "$key";
				$values .= "'$value'";
			}
			
			foreach($_imapSettings as $key => $value)
			{
				if($fields != '')
					$fields .= ',';
				if($values != '')
					$values .= ',';
				$fields .= "$key";
				$values .= "'$value'";
			}
			
			$query = "insert into phpgw_emailadmin ($fields) values ($values)";
			
			$this->db->query($query,__LINE__,__FILE__);
		}

		function deleteProfile($_profileID)
		{
			$query = "delete from phpgw_emailadmin where profileID='$_profileID'";
			$this->db->query($query,__LINE__ , __FILE__);
		}

		function getProfile($_profileID, $_fieldNames)
		{
			$query = '';
			foreach($_fieldNames as $key => $value)
			{
				if(!empty($query))
					$query .= ', ';
				$query .= $value;
			}
			
			$query = "select $query from phpgw_emailadmin where profileID='$_profileID'";
			
			$this->db->query($query, __LINE__, __FILE__);
			
			if($this->db->next_record())
			{
				foreach($_fieldNames as $key => $value)
				{
					$profileData[$value] = $this->db->f($value);
				}

				return $profileData;
			}
			
			return false;
		}
		
		function getProfileList($_profileID='')
		{
			if(is_int(intval($_profileID)) && $_profileID != '')
			{
				$query = "select profileID,smtpServer,smtpType,imapServer,imapType,description from phpgw_emailadmin where profileID='".intval($_profileID)."'";
			}
			else
			{
				$query = "select profileID,smtpServer,smtpType,imapServer,imapType,description from phpgw_emailadmin";
			}
			
			$this->db->query($query);
			
			$i=0;
			while ($this->db->next_record())
			{
				$serverList[$i]['profileID'] 		= $this->db->f('profileID');
				$serverList[$i]['smtpServer']		= $this->db->f('smtpServer');
				$serverList[$i]['smtpType']		= $this->db->f('smtpType');
				$serverList[$i]['imapServer']		= $this->db->f('imapServer');
				$serverList[$i]['imapType']		= $this->db->f('imapType');
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
					$userData["mailLocalAddress"]		= $allValues[0]["mail"][0];
					$userData["mailAlternateAddress"]	= $allValues[0]["mailalternateaddress"];
					$userData["accountStatus"]		= $allValues[0]["accountstatus"][0];
					$userData["mailRoutingAddress"]		= $allValues[0]["mailforwardingaddress"];
					$userData["qmailDotMode"]		= $allValues[0]["qmaildotmode"][0];
					$userData["deliveryProgramPath"]	= $allValues[0]["deliveryprogrampath"][0];
					$userData["deliveryMode"]		= $allValues[0]["deliverymode"][0];

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
				'mail'			=> $_accountData["mailLocalAddress"],
				'mailAlternateAddress'	=> $_accountData["mailAlternateAddress"],
				'mailRoutingAddress'	=> $_accountData["mailRoutingAddress"],
				'homedirectory'		=> $homedirectory,
				'mailMessageStore'	=> $homedirectory."/Maildir/",
				'gidnumber'		=> '1000',
				'qmailDotMode'		=> $_accountData["qmailDotMode"],
				'deliveryProgramPath'	=> $_accountData["deliveryProgramPath"],
				'accountStatus'		=> $_accountData["accountStatus"]
			);
			
			$objectClasses[]	= 'qmailUser'; 
			
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
				$delete['mailAlternateAddress'] = array();
				@ldap_mod_del($ldap, $accountDN, $delete);
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
				$delete['mailForwardingAddress'] = array();
				@ldap_mod_del($ldap, $accountDN, $delete);
			}
				
			ldap_mod_replace ($ldap, $accountDN, $newData);
			#print ldap_error($ldap);
			
		}

		function updateProfile($_globalSettings, $_smtpSettings, $_imapSettings)
		{
			$query = '';
			
			foreach($_smtpSettings as $key => $value)
			{
				if($query != '')
					$query .= ', ';
				$query .= "$key='$value'";
			}
			
			foreach($_globalSettings as $key => $value)
			{
				if($key == 'profileID')
					continue;
				if($query != '')
					$query .= ', ';
				$query .= "$key='$value'";
			}
			
			foreach($_imapSettings as $key => $value)
			{
				if($query != '')
					$query .= ', ';
				$query .= "$key='$value'";
			}
			
			$query = "update phpgw_emailadmin set $query where profileID='".$_globalSettings['profileID']."'";
			
			$this->db->query($query,__LINE__,__FILE__);
		}
	}
?>
