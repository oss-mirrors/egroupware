<?php
	/***************************************************************************\
	* EGroupWare - EMailAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	include_once(PHPGW_SERVER_ROOT."/emailadmin/inc/class.smtpBaseClass.inc.php");

	class postfixldap extends smtpBaseClass
	{
		function addAccount($_username, $_password)
		{
			$boQmailLDAP = CreateObject('emailadmin.bo');
			$data["mailLocalAddress"]	= $_username."@".$this->profileData['defaultDomain'];
			$data["accountStatus"]		= 'active';
			$boQmailLDAP->saveUserData($_username, $data, 'save');
		}
	}
?>
