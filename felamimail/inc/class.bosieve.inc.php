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
	* Free Software Foundation; version 2 of the License.                       *
	\***************************************************************************/
	/* $Id: class.uisieve.inc.php,v 1.24 2005/11/30 08:29:45 ralfbecker Exp $ */

	include_once('Net/Sieve.php');

	class bosieve extends Net_Sieve
	{
		/**
		* @var object $icServer object containing the information about the imapserver
		*/
		var $icServer;
	
		/**
		* @var object $icServer object containing the information about the imapserver
		*/
		var $scriptName='felamimail';
	
		function bosieve($_icServer) {
			parent::Net_Sieve();
			
			$this->displayCharset	= $GLOBALS['egw']->translation->charset();

			#$this->bopreferences    =& CreateObject('felamimail.bopreferences');
			#$this->mailPreferences  = $this->bopreferences->getPreferences();
			
			#$this->restoreSessionData();

			if(is_a($_icServer,'defaultimap') && $_icServer->enableSieve) {
				$sieveHost		= $_icServer->sieveHost;
				$sievePort		= $_icServer->sievePort;
				$username		= $_icServer->username;
				$password		= $_icServer->password;
				
				$this->icServer = $_icServer;
			} else {
				die('Sieve not activated');
			}

			if(PEAR::isError($error = $this->connect($sieveHost , $sievePort) ) ){
				echo "  there was an error trying to connect to the server. The error is: " . $error->getMessage() . "<br>" ;
				exit();
			}

			if(PEAR::isError($error = $this->login($username, $password  , null , '', false ) ) ){
				echo "  there was an error trying to connect to the server. The error is: " . $error->getMessage()  . "<br>";
				exit();
			}
		}
		
		function getRules($_scriptName) {
			return $this->rules;
		}

		function getVacation($_scriptName) {
			return $this->vacation;
		}
		
		function setRules($_scriptName, $_rules) {
			$script         =& CreateObject('felamimail.Script',$_scriptName);

			if($script->retrieveRules($this)) {
				$script->rules = $_rules;
				$script->updateScript($this);
				
				return true;
			} 

			return false;
		}

		function setVacation($_scriptName, $_vacation) {
			$script         =& CreateObject('felamimail.Script',$_scriptName);

			if($script->retrieveRules($this)) {
				$script->vacation = $_vacation;
				$script->updateScript($this);
				
				return true;
			} 

			return false;
		}

		function retrieveRules($_scriptName) {
			$script         =& CreateObject('felamimail.Script',$_scriptName);
			
			if($script->retrieveRules($this)) {
				$this->rules = $script->rules;
				$this->vacation = $script->vacation;
				
				return true;
			} 
			
			return false;
		}
		
		function updateScript($_scriptName, $_rules) {
			$script		=& CreateObject('felamimail.Script',$_scriptName);
			$script->rules	= $_rules;
			$result 	= $script->updateScript($this);

			return $result;
		}
	}
?>
