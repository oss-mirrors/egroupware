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
		var $start;
		var $search;
		var $filter;
		var $cat_id;

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

		}
		
		function getLocals()
		{
		}
		
		function getRcptHosts()
		{
		}
		
		function getLDAPData($_serverid)
		{
			$data = $this->soqmailldap->getLDAPData($_serverid);
			return $data;
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
		
		function save($_postVars, $_getVars)
		{
			switch ($_postVars["bo_action"])
			{
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
					break;
			}
		}

	}
?>
