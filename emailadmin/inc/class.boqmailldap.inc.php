<?php
	/***************************************************************************\
	* phpGroupWare - QMailLDAP                                                  *
	* http://www.phpgroupware.org                                               *
	* http://www.phpgw.de                                                       *
	* Written by : Lars Kneschke                                                *
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
			#global $phpgw;

			$this->soqmailldap = CreateObject('qmailldap.soqmailldap');
			
			$this->restoreSessionData();

		}
		
		function getLDAPData($_serverid, $_nocache=0)
		{
			global $phpgw, $HTTP_GET_VARS;
			
			if ($HTTP_GET_VARS['nocache'] == '1' || $_nocache == '1')
			{
				#print "option1<br>";
				$LDAPData = $this->soqmailldap->getLDAPData($_serverid);
				$this->sessionData[$_serverid]['data'] = $LDAPData;
				
				$this->saveSessionData();

				#while(list($key, $value) = each($this->sessionData[$_serverid]['data']['rcpthosts']))
				#{
				#	print "... $key: $value<br>";
				#}
				
				return $this->sessionData['$_serverid']['data'];
			}
			else
			{
				#print "option2<br>";
				#while(list($key, $value) = each($this->sessionData[$_serverid]['data']['rcpthosts']))
				#{
				#	print ".... $key: $value<br>";
				#}
				return $this->sessionData[$_serverid]['data'];
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
		
		function restoreSessionData()
		{
			global $phpgw;
		
			$this->sessionData = $phpgw->session->appsession('session_data');
			
			#while(list($key, $value) = each($this->sessionData))
			#{
			#	print "++ $key: $value<br>";
			#}
			#print "restored Session<br>";
		}
		
		function save($_postVars, $_getVars)
		{
			$serverid = $_getVars['serverid'];
			
			switch ($_postVars["bo_action"])
			{
				case "add_locals":
					$count = count($this->sessionData[$serverid]['data']['locals']);
					
					$this->sessionData[$serverid]['data']['locals'][$count] = 
						$_postVars["new_local"];
					
					$this->saveSessionData();
					
					break;
					
				case "add_rcpthosts":
					$count = count($this->sessionData[$serverid]['data']['rcpthosts']);
					
					$this->sessionData[$serverid]['data']['rcpthosts'][$count] = 
						$_postVars["new_rcpthost"];
						
					if ($_postVars["add_to_local"] == "on")
					{
						$count = count($this->sessionData[$serverid]['data']['locals']);
						
						$this->sessionData[$serverid]['data']['locals'][$count] = 
							$_postVars["new_rcpthost"];
					}
					
					$this->saveSessionData();
					
					break;
					
				case "remove_locals":
					$i=0;
					
					while(list($key, $value) = each($this->sessionData[$serverid]['data']['locals']))
					{
						#print ".. $key: $value<br>";
						if ($key != $_postVars["locals"])
						{
							$newLocals[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->sessionData[$serverid]['data']['locals'] = $newLocals;
					
					$this->saveSessionData();
					
					break;
					
				case "remove_rcpthosts":
					$i=0;
					
					while(list($key, $value) = each($this->sessionData[$serverid]['data']['rcpthosts']))
					{
						#print ".. $key: $value<br>";
						if ($key != $_postVars["rcpthosts"])
						{
							$newRcpthosts[$i]=$value;
							#print "!! $i: $value<br>";
							$i++;
						}
					}
					$this->sessionData[$serverid]['data']['rcpthosts'] = $newRcpthosts;
					
					$this->saveSessionData();
					
					break;
					
				case "save_ldap":
					#print "hallo".$_getVars["serverid"]." ".$_postVars["servername"]."<br>";
					$data = array
					(
						"qmail_servername"	=> $_postVars["qmail_servername"],
						"description"		=> $_postVars["description"],
						"ldap_basedn"		=> $_postVars["ldap_basedn"],
						"id"			=> $_getVars["serverid"]
					);
					$this->soqmailldap->update("save_ldap",$data);
					$this->getLDAPData($_getVars["serverid"], '1');
					
					break;
			}
		}
		
		function saveSessionData()
		{
			global $phpgw;
			
			$phpgw->session->appsession('session_data','',$this->sessionData);
		}

	}
?>
