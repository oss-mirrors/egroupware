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
			'delete_note'		=> True,
			'read_preferences'	=> True,
			'save_preferences'	=> True
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
		
		function getServerList()
		{
			$data = array
			(
				'0'	=> array
				(
					'servername'		=> 'gateway.intranet.local',
					'description'		=> 'Standard Server',
					'default_ldap_server'	=> '1',
					'qmail_base_dn'		=> 'ou=qmailldap, ou=future_project, dc=intranet, dc=local',
					'id'			=> '0'
				),
				'1'	=> array
				(
					'servername'	=> 'gateway.intranet.local',
					'description'	=> 'Standard Server1',
					'id'		=> '1'
				)
			);
			
			return $data;
		}

	}
?>
