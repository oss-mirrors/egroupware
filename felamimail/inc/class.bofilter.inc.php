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

	class bofilter
	{
		var $public_functions = array
		(
			'updateImapStatus'	=> True,
			'flagMessages'		=> True
		);

		function bofilter()
		{
			$this->accountid	= $GLOBALS['phpgw_info']['user']['account_id'];
			
			$this->bopreferences	= CreateObject('felamimail.bopreferences');
			$this->sofelamimail	= CreateObject('felamimail.sofelamimail');
			
			$this->mailPreferences	= $this->bopreferences->getPreferences();
			
			$this->restoreSessionData();
		}
		
		function deleteFilter($_filterID)
		{
			unset($this->sessionData['filter'][$_filterID]);
			$this->saveSessionData();
		}

		function getFilterList()
		{
			return $this->sessionData['filter'];
		}
		
		function restoreSessionData()
		{
			$this->sessionData = $GLOBALS['phpgw']->session->appsession('session_data');
		}
		
		function saveFilter($_formData, $_filterID='')
		{
			if(!empty($_formData['filterName']))
				$data['filterName']	= $_formData['filterName'];
			if(!empty($_formData['from']))
				$data['from']	= $_formData['from'];
			if(!empty($_formData['to']))
				$data['to']	= $_formData['to'];
			if(!empty($_formData['subject']))
				$data['subject']= $_formData['subject'];
			if($_formData['filterActive'] == "true")
			{
				$data['filterActive']= "true";
			}

			if($_filterID == '')
			{
				$this->sessionData['filter'][] = $data;
			}
			else
			{
				$this->sessionData['filter'][$_filterID] = $data;
			}
			$this->saveSessionData();
		}
		function saveSessionData()
		{
			$GLOBALS['phpgw']->session->appsession('session_data','',$this->sessionData);
		}
		
		function toggleFilter()
		{
			if($this->sessionData['filter']['filterActive'] == 'true')
			{
				$this->sessionData['filter']['filterActive'] = 'false';
			}
			else
			{
				$this->sessionData['filter']['filterActive'] = 'true';
			}
			$this->saveSessionData();
		}
	}

?>