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

	class smtpBaseClass
	{
		var $profileData;
	
		function smtpBaseClass($_profileData)
		{
			$this->profileData = $_profileData;
		}
		
		function addAccount($_username, $_password)
		{
			return true;
		}
		
	}
?>
