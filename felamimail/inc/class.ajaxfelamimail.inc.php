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

	/* $Id$ */

	class ajaxfelamimail {
		// which profile to use(currently only 0 is supported)
		var $imapServerID=0;
		
		// the object storing the data about the incoming imap server
		var $icServer;
		
		var $charset;
		
		function ajaxfelamimail() {
			$this->charset		=  $GLOBALS['egw']->translation->charset();
			$this->bofelamimail	=& CreateObject('felamimail.bofelamimail',$this->charset);
			$this->uiwidgets	=& CreateObject('felamimail.uiwidgets');
			$this->bofelamimail->openConnection();

			$this->sessionDataAjax	= $GLOBALS['egw']->session->appsession('ajax_session_data');
			$this->sessionData	= $GLOBALS['egw']->session->appsession('session_data');

			if(!isset($this->sessionDataAjax['folderName'])) {
				$this->sessionDataAjax['folderName'] = 'INBOX';
			}

			$this->bofelamimail->openConnection($this->sessionDataAjax['folderName']);

			$this->icServer = $this->bofelamimail->mailPreferences->getIncomingServer($this->imapServerID);
		}
		
		function addACL($_accountName, $_aclData) {
			if(!empty($_accountName)) {
				$acl = implode('',(array)$_aclData['acl']);
				$data = $this->bofelamimail->addACL($this->sessionDataAjax['folderName'], $_accountName, $acl);
			}
		}
		
		function addFolder($_parentFolder, $_newSubFolder) {
			$folderData = $this->bofelamimail->getFolderStatus('INBOX');
			$folderName = ($_parentFolder == '--topfolder--'?$_newSubFolder:$_parentFolder.$folderData['delimiter'].$_newSubFolder);
			$response =& new xajaxResponse();
			if($this->bofelamimail->imap_createmailbox($folderName, true)) {
				$response->addScript("tree.insertNewItem('$_parentFolder','$folderName','$_newSubFolder',onNodeSelect,'folderClosed.gif',0,0,'CHILD,CHECKED,SELECT,CALL');");
				$response->addScript("tree.setCheck('$folderName','0');");
			}
			$response->addAssign("newSubFolder", "value", '');
			return $response->getXML();
		}
		
		function changeSorting($_sortBy) {
			$this->sessionData['startMessage']	= 1;

			switch($_sortBy) {
				case 'date':
					$this->sessionData['sort'] = ($this->sessionData['sort'] == 0?1:0);
					break;
				case 'from':
					$this->sessionData['sort'] = ($this->sessionData['sort'] == 3?2:3);
					break;
				case 'size':
					$this->sessionData['sort'] = ($this->sessionData['sort'] == 6?7:6);
					break;
				case 'subject':
					$this->sessionData['sort'] = ($this->sessionData['sort'] == 5?4:5);
					break;
			}

			$this->saveSessionData();

			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function compressFolder() {
			$this->bofelamimail->restoreSessionData();
			$this->bofelamimail->compressFolder($this->sessionData['mailbox']);

			$bofilter =& CreateObject('felamimail.bofilter');
			$caching =& CreateObject('felamimail.bocaching',
				$this->icServer->host,
				$this->icServer->username,
				$this->sessionData['mailbox']);
			
			$messageCounter = $caching->getMessageCounter($bofilter->getFilter($this->sessionData['activeFilter']));

			// $lastPage is the first message ID of the last page
			if($messageCounter > $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) {
				$lastPage = $messageCounter - ($messageCounter % $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) + 1;
				if($lastPage > $messageCounter)
					$lastPage -= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
				if($this->sessionData['startMessage'] > $lastPage)
					$this->sessionData['startMessage'] = $lastPage;
			} else {
				$this->sessionData['startMessage'] = 1;
			}

			$this->saveSessionData();

			return $this->generateMessageList($this->sessionData['mailbox']);
		}

		function createACLTable($_acl) {
			if(!is_object($GLOBALS['egw']->html)) {
				$GLOBALS['egw']->html =& CreateObject('phpgwapi.html');
			}

			$aclList = array('l','r','s','w','i','p','c','d','a');
			$aclShortCuts = array('custom', 'readable', 'post', 'append', 'write', 'all');
		
			ksort($_acl);
		
			foreach($_acl as $accountName => $accountAcl) {
				$row .= '<tr class="row_on">';
				
				$row .= "<td><input type=\"checkbox\" name=\"accountName[]\" id=\"accountName\" value=\"$accountName\"></td>";
				
				$row .= "<td>$accountName</td>";
				
				foreach($aclList as $acl) {
					$row .= "<td><input type=\"checkbox\" name=\"acl[$accountName][$acl]\" id=\"acl_$accountName_$acl\"". 
						(strpos($accountAcl,$acl) !== false ? 'checked' : '') .
						" onclick=\"xajax_doXMLHTTP('felamimail.ajaxfelamimail.updateSingleACL','$accountName','$acl',this.checked)\"</td>";
				}

				$selectFrom = $GLOBALS['egw']->html->select('identity', $defaultIdentity, $aclShortCuts, false, "style='width: 100px;'");

				$row .= "<td>$selectFrom</td>";
				
				$row .= "</tr>";
			}
			
			return "<table border=\"0\" style=\"width: 100%;\"><tr class=\"th\"><th>&nbsp;</th><th style=\"width:100px;\">Name</th><th>L</th><th>R</th><th>S</th><th>W</th><th>I</th><th>P</th><th>C</th><th>D</th><th>A</th><th>&nbsp;</th></tr>$row</table>";
		}
		
		function deleteACL($_aclData) {
			if(is_array($_aclData)) {
				foreach($_aclData['accountName'] as $accountName) {
					$data = $this->bofelamimail->addACL($this->sessionDataAjax['folderName'], $accountName, '');
				}
				
				$folderACL = $this->bofelamimail->getIMAPACL($this->sessionDataAjax['folderName']);
				
				$response =& new xajaxResponse();
				$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));
				return $response->getXML();
			}
		}

		function deleteAttachment($_composeID, $_attachmentID) {
			$bocompose	=& CreateObject('felamimail.bocompose', $_composeID);
			$bocompose->removeAttachment($_attachmentID);
		}

		function deleteFolder($_folderName) {
			if($_folderName == 'INBOX' || $_folderName == '--topfolder--')
				return false;

			if($this->bofelamimail->deleteFolder($_folderName)) {
				$response =& new xajaxResponse();
				$response->addScript("tree.deleteItem('$_folderName',1);");
				return $response->getXML();
			}
		}
		
		function deleteMessages($_messageList) {
			$this->bofelamimail->deleteMessages($_messageList['msg']);

			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function emptyTrash() {
			if(!empty($GLOBALS['egw_info']['user']['preferences']['felamimail']['trashFolder'])) {
				$this->bofelamimail->compressFolder($GLOBALS['egw_info']['user']['preferences']['felamimail']['trashFolder']);
			}

			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function extendedSearch($_filterID) {
			// start displaying at message 1
			$this->sessionData['startMessage']      = 1;
			$this->sessionData['activeFilter']	= (int)$_filterID;
			$this->saveSessionData();
			
			// generate the new messageview                
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function flagMessages($_flag, $_messageList) {
			$this->bofelamimail->flagMessages($_flag, $_messageList['msg']);

			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function generateMessageList($_folderName) {
			$this->bofelamimail->restoreSessionData();
			
			$isSentFolder = $this->bofelamimail->isSentFolder($_folderName);

			$maxMessages = $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			$headers = $this->bofelamimail->getHeaders($this->sessionData['startMessage'], $maxMessages, $this->sessionData['sort']);

			$headerTable = $this->uiwidgets->messageTable(
				$headers, 
				$isSentFolder, 
				$GLOBALS['egw_info']['user']['preferences']['felamimail']['message_newwindow'],
				$GLOBALS['egw_info']['user']['preferences']['felamimail']['rowOrderStyle']
			);
			
			$response =& new xajaxResponse();
			$firstMessage = (int)$headers['info']['first'];
			$lastMessage  = (int)$headers['info']['last'];
			$totalMessage = (int)$headers['info']['total'];

			if($totalMessage == 0) {
				$response->addAssign("messageCounter", "innerHTML", lang('no messages found...'));
			} else {
				$response->addAssign("messageCounter", "innerHTML", lang('Viewing messages')." <b>$firstMessage</b> - <b>$lastMessage</b> ($totalMessage ".lang("total").')');
			}
			
			if($isSentFolder) {
				$response->addAssign("from_or_to", "innerHTML", lang('to'));
			} else {
				$response->addAssign("from_or_to", "innerHTML", lang('from'));
			}
			
			$response->addAssign("divMessageList", "innerHTML", $headerTable);

			$folderStatus = $this->bofelamimail->getFolderStatus($_folderName);
			if($folderStatus['unseen'] > 0) {
				$response->addScript("tree.setItemText('$_folderName', '<b>". $folderStatus['shortName'] ." (". $folderStatus['unseen'] .")</b>');");
			} else {
				$response->addScript("tree.setItemText('$_folderName', '". $folderStatus['shortName'] ."');");
			}

			$response->addScript("tree.selectItem('".$_folderName. "',false);");

			return $response->getXML();
		}
		
		function getFolderInfo($_folderName) {
			if($_folderName != '--topfolder--' && $folderStatus = $this->bofelamimail->getFolderStatus($_folderName)) {
				$response =& new xajaxResponse();

				if($this->sessionDataAjax['oldFolderName'] == '--topfolder--') {
					$this->sessionDataAjax['oldFolderName'] = '';
					$response->addScript("document.getElementById('newMailboxName').disabled = false;");
					$response->addScript("document.getElementById('mailboxRenameButton').disabled = false;");
				}
				// only folders with LATT_NOSELECT not set, can have subfolders
				// seem to work only for uwimap
				#if($folderStatus['attributes'] & LATT_NOSELECT) {
					$response->addScript("document.getElementById('newSubFolder').disabled = false;");
				#} else {
				#	$response->addScript("document.getElementById('newSubFolder').disabled = true;");
				#}
				
				$this->sessionDataAjax['folderName'] = $_folderName;
				$this->saveSessionData();
				
				$folderACL = $this->bofelamimail->getIMAPACL($_folderName);
				
				$response->addAssign("newMailboxName", "value", $folderStatus['shortName']);
				$response->addAssign("folderName", "innerHTML", $_folderName);
				$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));

				return $response->getXML();
			}
			else
			{
				$this->sessionDataAjax['oldFolderName'] = $_folderName;
				$this->saveSessionData();

				$response =& new xajaxResponse();
				$response->addAssign("newMailboxName", "value", '');
				$response->addAssign("folderName", "innerHTML", '');
				$response->addScript("document.getElementById('newMailboxName').disabled = true;");
				$response->addScript("document.getElementById('mailboxRenameButton').disabled = true;");
				$response->addAssign("aclTable", "innerHTML", '');
				return $response->getXML();
			}
		}
		
		function gotoStart() {
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function jumpEnd() {
			$bofilter =& CreateObject('felamimail.bofilter');
			$caching =& CreateObject('felamimail.bocaching',
				$this->icServer->host,
				$this->icServer->username,
				$this->sessionData['mailbox']);

			$messageCounter = $caching->getMessageCounter($bofilter->getFilter($this->sessionData['activeFilter']));

			$lastPage = $messageCounter - ($messageCounter % $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) + 1;
			if($lastPage > $messageCounter)
				$lastPage -= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];

			$this->sessionData['startMessage'] = $lastPage;

			$this->saveSessionData();

			return $this->generateMessageList($this->sessionData['mailbox']);
		}

		function jumpStart() {
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function moveMessages($_folder, $_selectedMessages) {
			$this->bofelamimail->moveMessages($_folder, $_selectedMessages['msg']);

			return $this->generateMessageList($this->sessionData['mailbox']);
		}

		function quickSearch($_searchString) {
			// save the filter
			$bofilter		=& CreateObject('felamimail.bofilter');

			$filter['filterName']	= lang('Quicksearch');
			$filter['from']		= $_searchString;
			$filter['subject']	= $_searchString;

			$bofilter->saveFilter($filter,0);
			
			// start displaying at message 1
			$this->sessionData['startMessage']      = 1;
			if($_searchString != '') {
				$this->sessionData['activeFilter']	= 0;
			} else {
				$this->sessionData['activeFilter']	= -1;
			}

			$this->saveSessionData();
			
			// generate the new messageview                
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function refreshMessageList() {
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function refreshFolderList() {
			$folders = $this->bofelamimail->getFolderObjects();
			
			$response =& new xajaxResponse();
			
			foreach($folders as $folderName => $folderData) {
				$folderStatus = $this->bofelamimail->getFolderStatus($folderName);
				if($folderStatus['unseen'] > 0) {
					$response->addScript("tree.setItemText('$folderName', '<b>". $folderStatus['shortName'] ." (". $folderStatus['unseen'] .")</b>');");
				} else {
					$response->addScript("tree.setItemText('$folderName', '". $folderStatus['shortName'] ."');");
				}
			}
			
			return $response->getXML();
			
		}
		
		function reloadAttachments($_composeID) {
			$bocompose	=& CreateObject('felamimail.bocompose', $_composeID);
			$tableRows	=  array();
			$table		=  '';
			$imgClearLeft	=  $GLOBALS['egw']->common->image('felamimail','clear_left');

			foreach((array)$bocompose->sessionData['attachments'] as $id => $attachment) {
				$tempArray = array (
					'1' => $attachment['name'],
					'2' => $attachment['type'], '.2' => "style='text-align:center;'",
					'3' => $attachment['size'],
					'4' => "<img src='$imgClearLeft' onclick=\"fm_compose_deleteAttachmentRow(this,'$_composeID','$id')\">"
				);
				$tableRows[] = $tempArray;
			}
			
			if(count($tableRows) > 0) {
				if(!is_object($GLOBALS['egw']->html)) {
					$GLOBALS['egw']->html =& CreateObject('phpgwapi.html');
				}
				$table = $GLOBALS['egw']->html->table($tableRows, "style='width:100%'");
			}

			$response =& new xajaxResponse();
			$response->addAssign('divAttachments', 'innerHTML', $table);
			return $response->getXML();
		}

		function renameFolder($_oldName, $_newParent, $_newName) {
			#$mailPreferences  = $this->bopreferences->getPreferences();
			#if(!$icServer = $mailPreferences->getIncomingServer(0))
			#{
			#	return false;
			#}
			
			if($_newParent != '--topfolder--') {
				$parentFolder = $this->bofelamimail->getFolderStatus($_newParent);
			
				$newName = $_newParent. $parentFolder['delimiter']. $_newName;
			} else {
				$newName = $_newName;
			}

			if($this->bofelamimail->imap_renamemailbox($_oldName, $newName))
			{
				$response =& new xajaxResponse();
				$response->addScript("tree.deleteItem('$_oldName',0);");
				$response->addScript("tree.insertNewItem('$_newParent','$newName','$_newName',onNodeSelect,0,0,0,'CHILD,CHECKED,SELECT,CALL');");
				return $response->getXML();
			}
		}
		
		function saveSessionData() {
			$GLOBALS['egw']->session->appsession('ajax_session_data','',$this->sessionDataAjax);
			$GLOBALS['egw']->session->appsession('session_data','',$this->sessionData);
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
		
		function skipForward() {
			$icServer = $this->bofelamimail->mailPreferences->getIncomingServer(0);
			$bofilter =& CreateObject('felamimail.bofilter');
			$caching =& CreateObject('felamimail.bocaching',
				$this->icServer->host,
				$this->icServer->username,
				$this->sessionData['mailbox']);

			$messageCounter = $caching->getMessageCounter($bofilter->getFilter($this->sessionData['activeFilter']));
			// $lastPage is the first message ID of the last page
			if($messageCounter > $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) {
				$lastPage = $messageCounter - ($messageCounter % $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) + 1;
				if($lastPage > $messageCounter) {
					$lastPage -= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
				}
				$this->sessionData['startMessage'] += $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
				if($this->sessionData['startMessage'] > $lastPage) {
					$this->sessionData['startMessage'] = $lastPage;
				}
			} else {
				$this->sessionData['startMessage'] = 1;
			}

			$this->saveSessionData();
			
			$response = $this->generateMessageList($this->sessionData['mailbox']);
			
			return $response;
		}
		
		function skipPrevious() {
			$this->sessionData['startMessage']	-= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			if($this->sessionData['startMessage'] < 1) {
				$this->sessionData['startMessage'] = 1;
			}
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function updateACLView() {
			$folderACL = $this->bofelamimail->getIMAPACL($this->sessionDataAjax['folderName']);
			
			$response =& new xajaxResponse();
			$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));
			return $response->getXML();
		}
		
		function updateFolderStatus($_folderName, $_status) {
			$this->bofelamimail->subscribe($_folderName,($_status == '1' ? 'subscribe' : 'unsubscribe'));
			$response =& new xajaxResponse();
			return $response->getXML();
		}
		
		function updateMessageView($_folderName) {
			$this->sessionData['mailbox'] 		= $_folderName;
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			$messageList = $this->generateMessageList($_folderName);
			
			$this->bofelamimail->closeConnection();
			
			return $messageList;
		}
		
		function updateSingleACL($_accountName, $_aclType, $_aclStatus) {
			$data = $this->bofelamimail->updateSingleACL($this->sessionDataAjax['folderName'], $_accountName, $_aclType, $_aclStatus);			
			#$response =& new xajaxResponse();
			#$response->addAssign("newMailboxName", "value", $_accountName.' '.$_aclType.' '.$_aclStatus.' '.$data);
			#return $response->getXML();
		}
		
		function xajaxFolderInfo($_formValues) {
			$response =& new xajaxResponse();
												$response->addAssign("field1", "value", $_formValues['num1']);
												$response->addAssign("field2", "value", $_formValues['num2']);
												$response->addAssign("field3", "value", $_formValues['num1'] * $_formValues['num2']);

												return $response->getXML();
		}
	}
?>
