<?php
	/***************************************************************************\
	* phpGroupWare - FeLaMiMail                                                 *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class bopreferences
	{
		var $public_functions = array
		(
			'getPreferences'	=> True,
			'none'	=> True
		);
		
		function bopreferences()
		{
			#$this->bocompose	= CreateObject('felamimail.bocompose');
		}
		
		function getPreferences()
		{
/*			while(list($key,$value) = each($GLOBALS['phpgw_info']['user']['preferences']) )
			{
				print ". $key: $value<br>";
				if (is_array($value))
				{
					while(list($key1,$value1) = each($value) )
					{
						print ".. &nbsp;$mbsp;-$key1: $value1<br>";
					}
				}
			}
*/
			if ($GLOBALS['phpgw_info']['user']['preferences']['email']['use_custom_settings'] == 'True')
			{
				$data['imapServerAddress']	= $GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server'];

				$data['key']			= $GLOBALS['phpgw_info']['user']['preferences']['email']['passwd'];
				$data['username']		= $GLOBALS['phpgw_info']['user']['preferences']['email']['userid'];
				$data['emailAddress']		= $GLOBALS['phpgw_info']['user']['preferences']['email']['address'];
				
				$data['imap_server_type']	= strtolower($GLOBALS['phpgw_info']['user']['preferences']['email']['imap_server_type']);
			}
			else
			{
				$data['imapServerAddress']	= $GLOBALS['phpgw_info']['server']['mail_server'];

				$data['key']			= $GLOBALS['phpgw_info']['user']['passwd'];
				$data['username']		= $GLOBALS['phpgw_info']['user']['userid'];
				$data['emailAddress']		= $GLOBALS['phpgw_info']['user']['userid']."@".$GLOBALS['phpgw_info']['server']['mail_suffix'];

				$data['imap_server_type']	= strtolower($GLOBALS['phpgw_info']["server"]["address"]);
			}
			
			// global settings
			$data['realname']		= $GLOBALS['phpgw_info']['user']['fullname'];
			$data['defaultDomainname']	= $GLOBALS['phpgw_info']["server"]["mail_suffix"];

			$data['smtpServerAddress']	= $GLOBALS['phpgw_info']["server"]["smtp_server"];
			$data['smtpPort']		= $GLOBALS['phpgw_info']["server"]["smtp_port"];

			$data['imapPort']		= 143;
			
			// preferences
			$data['deleteOptions']		= $GLOBALS['phpgw_info']["user"]["preferences"]["felamimail"]["deleteOptions"];
			$data['trash_folder']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['trashFolder'];
			$data['sent_folder']		= $GLOBALS['phpgw_info']['user']['preferences']['felamimail']['sent_folder'];
			if (!empty($data['trash_folder'])) 
				$data['move_to_trash'] 	= "true";
			if (!empty($data['sent_folder']))  
				$data['move_to_sent'] 	= "true";
			$data['signature']		= $GLOBALS['phpgw_info']['user']['preferences']['email']['email_sig'];

			return $data;
		}
}