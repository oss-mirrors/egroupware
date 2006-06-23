<?php
	/***************************************************************************\
	* eGroupWare - FeLaMiMail                                                   *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/

	/* $Id: class.ajaxfelamimail.inc.php 21848 2006-06-15 21:50:59Z ralfbecker $ */

	class ajax_contacts {
		// which profile to use(currently only 0 is supported)
		var $imapServerID=0;
		
		// the object storing the data about the incoming imap server
		var $icServer;
		
		var $charset;
		
		function ajax_contacts() {
			$GLOBALS['egw']->session->commit_session();
			$this->charset	= $GLOBALS['egw']->translation->charset();
		}
		
		function searchAddress($_searchString) {
			if (!is_object($GLOBALS['egw']->contacts))
			{
				$GLOBALS['egw']->contacts =& CreateObject('phpgwapi.contacts');
			}
			if (method_exists($GLOBALS['egw']->contacts,'search'))	// 1.3+
			{
				$contacts = $GLOBALS['egw']->contacts->search(array(
					'n_fn'       => $_searchString,
					'email'      => $_searchString,
					'email_home' => $_searchString,
				),array('n_fn','email','email_home'),'n_fn','','%',false,'OR',array(0,20));
			}
			else	// < 1.3
			{
				$contacts = $GLOBALS['egw']->contacts->read(0,20,array(
					'fn' => 1,
					'email' => 1,
					'email_home' => 1,
				),$_searchString,'tid=n','','fn');
			}
			$response =& new xajaxResponse();

			if(is_array($contacts)) {
				$innerHTML	= '';
				$jsArray	= array();
				$i		= 0;
				
				foreach($contacts as $contact) {
					foreach(array($contact['email'],$contact['email_home']) as $email)
					{
						if(!empty($email) && !isset($jsArray[$email])) 
						{
							$i++;
							$str = $GLOBALS['egw']->translation->convert(trim($contact['n_fn'] ? $contact['n_fn'] : $contact['fn']).' <'.trim($email).'>',$this->charset,'utf-8');
							$innerHTML .= '<div class="inactiveResultRow" onclick="selectSuggestion($i)">'.
								htmlentities($str,ENT_QUOTES,'utf-8').'</div>';
							$jsArray[$email] = addslashes($str);
						}
						if ($i > 10) break;	// we check for # of results here, as we might have empty email addresses
					}
				}

				if($jsArray) {
					$response->addAssign('resultBox', 'innerHTML', $innerHTML);
					$response->addScript('results = new Array("'.implode('","',$jsArray).'");');
					$response->addScript('displayResultBox();');
				}
				//$response->addScript("getResults();");
				//$response->addScript("selectSuggestion(-1);");
			} else {
				$response->addAssign('resultBox', 'className', 'resultBoxHidden');
			}
			return $response->getXML();
		}
	}
