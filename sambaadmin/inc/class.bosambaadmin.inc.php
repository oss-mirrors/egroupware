<?php
	/***************************************************************************\
	* phpGroupWare - QMailLDAP                                                  *
	* http://www.phpgroupware.org                                               *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class bosambaadmin
	{
		var $sessionData;
		var $LDAPData;

		function bosambaadmin()
		{
			#global $phpgw;

			$this->sosambaadmin = CreateObject('sambaadmin.sosambaadmin');
			
			$this->restoreSessionData();

		}

		function checkLDAPSetup()
		{
			return $this->sosambaadmin->checkLDAPSetup();
		}
		
		function changePassword($_accountID, $_newPassword)
		{
			return $this->sosambaadmin->changePassword($_accountID, $_newPassword);
		}
		
		function deleteWorkstation($_workstations)
		{
			return $this->sosambaadmin->deleteWorkstation($_workstations);
		}
		
		function getUserData($_accountID, $_usecache)
		{
			if ($_usecache)
			{
				$userData = $this->userSessionData[$_accountID];
			}
			else
			{
				$userData = $this->sosambaadmin->getUserData($_accountID);
				$this->userSessionData[$_accountID] = $userData;
				$this->saveSessionData();
			}
			return $userData;
		}

		function getWorkstationData($_uidnumber)
		{
			return $this->sosambaadmin->getWorkstationData($_uidnumber);
		}
		
		function getWorkstationList($_start, $_sort, $_order)
		{
			return $this->sosambaadmin->getWorkstationList($_start, $_sort, $_order);
		}

		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['phpgw']->session->appsession('session_data');
			$this->userSessionData = $GLOBALS['phpgw']->session->appsession('user_session_data');
		}
		
		function saveSessionData()
		{
			$GLOBALS['phpgw']->session->appsession('session_data','',$this->sessionData);
			$GLOBALS['phpgw']->session->appsession('user_session_data','',$this->userSessionData);
		}
		
		function saveUserData($_accountID, $_formData)
		{
			return $this->sosambaadmin->saveUserData($_accountID, $_formData);
		}

		function updateAccount()
		{
			#_debug_array($GLOBALS['hook_values']);
			if($accountID = (int)$GLOBALS['hook_values']['account_id'])
			{
				$accountData = array();
				if($GLOBALS['hook_values']['new_passwd'])
				{
					$accountData['password']	= $GLOBALS['hook_values']['new_passwd'];
				}
				return $this->sosambaadmin->saveUserData($accountID, $accountData);
			}
			return false;
		}

		function updateGroup()
		{
			if($accountID = (int)$GLOBALS['hook_values']['account_id'])
			{
				return $this->sosambaadmin->updateGroup($accountID);
			}
			return false;
		}
				
		function updateWorkstation($_newData)
		{
			if(!$this->verifyData($_newData))
				return false;
				
			return $this->sosambaadmin->updateWorkstation($_newData);
		}
		
		function verifyData($_newData)
		{
			return true;
		}
	}
?>
