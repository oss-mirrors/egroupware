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

	class defaultsmtp
	{
		var $profileData;
	
		// the constructor
		function defaultsmtp($_profileData)
		{
			$this->profileData = $_profileData;
		}
		
		// add a account
		function addAccount($_hookValues)
		{
			return true;
		}
		
		// delete a account
		function deleteAccount($_hookValues)
		{
			return true;
		}
		
		function getAccountEmailAddress($_accountName)
		{
			return array(
				array(
					'name'		=> $GLOBALS['phpgw_info']['user']['fullname'], 
					'address'	=> $_accountName.'@'.$this->profileData['defaultDomain'], 
					'type'		=> 'default'
				)
			);
		}

		// update a account
		function updateAccount($_hookValues)
		{
			return true;
		}
	}
?>
