<?php
	/***************************************************************************\
	* phpGroupWare - Notes                                                      *
	* http://www.phpgroupware.org                                               *
	* Written by : Andy Holman (LoCdOg)                                         *
	*              Bettina Gille [ceb@phpgroupware.org]                         *
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
			'read_notes'		=> True,
			'read_single_note'	=> True,
			'save_note'		=> True,
			'delete_note'		=> True,
			'read_preferences'	=> True,
			'save_preferences'	=> True
		);

		function boqmailldap()
		{
			global $phpgw;

			$this->soqmailldap = CreateObject('qmailldap.soqmailldap');

		}
		
		function getServerList()
		{
			$data = array
			(
				'0'	=> array
				(
					'servername'	=> 'gateway.intranet.local',
					'description'	=> 'Standard Server',
					'id'		=> '0'
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
