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
			#$this->db2		= $this->db;
			#$this->grants	= $phpgw->acl->get_grants('notes');
			#$this->owner	= $phpgw_info['user']['account_id'];
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
				print "found data<br>";
				
				#return $data;
			}
			else
			{
				$data = array
				(
					'rcpthosts'	=> array
							   (
							   	'0' => 'vater-www.shacknet.nu',
							   	'1' => 'phpgw.de',
							   	'2' => 'linux-at-work.de'
							   ),
					'locals'	=> array
							   (
							   	'0' => 'vater-www.shacknet.nu',
							   	'1' => 'phpgw.de',
							   	'2' => 'linux-at-work.de'
							   )
				);
				
				return $data;
				#return false;
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

		function update($_action, $_data)
		{
			switch ($_action)
			{
				case "save_ldap":
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
	}
?>
