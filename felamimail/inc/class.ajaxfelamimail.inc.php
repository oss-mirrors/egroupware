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

	class ajaxfelamimail
	{
		function ajaxfelamimail()
		{
			$this->bofelamimail	=& CreateObject('felamimail.bofelamimail',$GLOBALS['egw']->translation->charset());
			$this->uiwidgets	=& CreateObject('felamimail.uiwidgets');
			$this->bofelamimail->openConnection('',OP_HALFOPEN);

			$this->sessionDataAjax	= $GLOBALS['egw']->session->appsession('ajax_session_data');
			$this->sessionData	= $GLOBALS['egw']->session->appsession('session_data');
			
			if(!isset($this->sessionDataAjax['folderName']))
				$this->sessionDataAjax['folderName'] = 'INBOX';
		}
		
		function addACL($_accountName, $_aclData)
		{
			if(!empty($_accountName))
			{
				$acl = implode('',(array)$_aclData['acl']);
				$data = $this->bofelamimail->addACL($this->sessionDataAjax['folderName'], $_accountName, $acl);
				#$response =& new xajaxResponse();
				#$response->addScript("window.close();");
				#$response->addAssign("accountName", "value", $this->sessionDataAjax['folderName'].'-'.$_accountName.'-'.$acl);
				#return $response->getXML();

			}
		}
		
		function addFolder($_parentFolder, $_newSubFolder)
		{
			if($this->bofelamimail->imap_createmailbox($_parentFolder.'.'.$_newSubFolder))
			{
				$response =& new xajaxResponse();
				$response->addScript("tree.insertNewItem('$_parentFolder','$_parentFolder.$_newSubFolder','$_newSubFolder',onNodeSelect,0,0,0,'CHILD,CHECKED,SELECT,CALL');");
				$response->addAssign("newSubFolder", "value", '');
				return $response->getXML();
			}
		}
		
		function createACLTable($_acl)
		{
			$aclList = array('l','r','s','w','i','p','c','d','a');
		
			ksort($_acl);
		
			foreach($_acl as $accountName => $accountAcl)
			{
				$row .= '<tr class="row_on">';
				
				$row .= "<td><input type=\"checkbox\" name=\"accountName[]\" id=\"accountName\" value=\"$accountName\"></td>";
				
				$row .= "<td>$accountName</td>";
				
				foreach($aclList as $acl)
				{
					$row .= "<td><input type=\"checkbox\" name=\"acl[$accountName][$acl]\" id=\"acl_$accountName_$acl\"". 
						(strpos($accountAcl,$acl) !== false ? 'checked' : '') .
						" onclick=\"xajax_doXMLHTTP('felamimail.ajaxfelamimail.updateSingleACL','$accountName','$acl',this.checked)\"</td>";
				}
				
				$row .= "</tr>";
			}
			
			return "<table border=\"0\"><tr class=\"th\"><th>&nbsp;</th><th style=\"width:100px;\">Name</th><th>L</th><th>R</th><th>S</th><th>W</th><th>I</th><th>P</th><th>C</th><th>D</th><th>A</th></tr>$row</table>";
		}
		
		function deleteACL($_aclData)
		{
			if(is_array($_aclData))
			{
				foreach($_aclData['accountName'] as $accountName)
				{
					$data = $this->bofelamimail->addACL($this->sessionDataAjax['folderName'], $accountName, '');
				}
				
				$folderACL = $this->bofelamimail->getIMAPACL($this->sessionDataAjax['folderName']);
				
				$response =& new xajaxResponse();
				$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));
				return $response->getXML();
			}
		}

		function deleteFolder($_folderName)
		{
			if($this->bofelamimail->imap_deletemailbox($_folderName))
			{
				$response =& new xajaxResponse();
				$response->addScript("tree.deleteItem('$_folderName',1);");
				#$response->addAssign("newSubFolder", "value", '');
				return $response->getXML();
			}
		}
		
		function deleteMessages($_messageList)
		{
			$this->bofelamimail->deleteMessages($_messageList['msg']);

			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function extendedSearch($_filterID)
		{
			// start displaying at message 1
			$this->sessionData['startMessage']      = 1;
			$this->sessionData['activeFilter']	= (int)$_filterID;
			$this->saveSessionData();
			
			// generate the new messageview                
			return $this->generateMessageList($this->sessionData['mailbox']);
		}

		function generateMessageList($_folderName)
		{
			$this->bofelamimail->restoreSessionData();
			
			$maxMessages = $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			$headers = $this->bofelamimail->getHeaders($this->sessionData['startMessage'], $maxMessages, $this->sort);
			
			$headerTable = $this->uiwidgets->messageTable($headers, $this->bofelamimail->isSentFolder($_folderName), TRUE);
			
			$response =& new xajaxResponse();
			$firstMessage = (int)$headers['info']['first'];
			$lastMessage  = (int)$headers['info']['last'];
			$totalMessage = (int)$headers['info']['total'];
			if($totalMessage == 0)
				$response->addAssign("messageCounter", "innerHTML", lang('no messages found...'));
			else
				$response->addAssign("messageCounter", "innerHTML", lang('Viewing messages')." <b>$firstMessage</b> - <b>$lastMessage</b> ($totalMessage ".lang("total").')');
			$response->addAssign("divMessageList", "innerHTML", $headerTable);

			$response->addScript("tree.selectItem('".$_folderName."',false);");

			return $response->getXML();
		}
		
		function getFolderInfo($_folderName)
		{
			if($folderStatus = $this->bofelamimail->getFolderStatus($_folderName))
			{
				$this->sessionDataAjax['folderName'] = $_folderName;
				$this->saveSessionData();
				
				$folderACL = $this->bofelamimail->getIMAPACL($_folderName);
				
				$response =& new xajaxResponse();
				$response->addAssign("newMailboxName", "value", $folderStatus['shortName']);
				$response->addAssign("folderName", "innerHTML", $_folderName);
				$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));
				#if($folderStatus['subscribed'] == TRUE)
				#{
				#	$response->addScript("document.getElementById('subscribed').checked = true;");
				#}
				#else
				#{
				#	$response->addScript("document.getElementById('subscribed').checked = false;");
				#}
				return $response->getXML();
			}
		}
		
		function gotoStart()
		{
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function jumpEnd()
		{
			$bofilter =& CreateObject('felamimail.bofilter');
			$caching =& CreateObject('felamimail.bocaching',
																				$this->bofelamimail->mailPreferences['imapServerAddress'],
																				$this->bofelamimail->mailPreferences['username'],
																				$this->sessionData['mailbox']);
			$messageCounter = $caching->getMessageCounter($bofilter->getFilter($this->sessionData['activeFilter']));

			$lastPage = $messageCounter - ($messageCounter % $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) + 1;

			$this->sessionData['startMessage']	+= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			if($this->sessionData['startMessage'] > $lastPage)
				$this->sessionData['startMessage'] = $lastPage;
			
			$this->saveSessionData();

			return $this->generateMessageList($this->sessionData['mailbox']);
		}

		function jumpStart()
		{
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function moveMessages($_folder, $_selectedMessages)
		{
			$this->bofelamimail->moveMessages($_folder, $_selectedMessages['msg']);

			return $this->generateMessageList($this->sessionData['mailbox']);
			
		}

		function quickSearch($_searchString)
		{
			// save the filter
			$bofilter		=& CreateObject('felamimail.bofilter');

			$filter['filterName']	= lang('Quicksearch');
			$filter['from']		= $_searchString;
			$filter['subject']	= $_searchString;

			$bofilter->saveFilter($filter,0);
			
			// start displaying at message 1
			$this->sessionData['startMessage']      = 1;
			if($_searchString != '')
				$this->sessionData['activeFilter']	= 0;
			else
				$this->sessionData['activeFilter']	= -1;

			#$response =& new xajaxResponse();
			#$response->addScript("document.getElementById('quickSearch').select();");
			#return $response->getXML();
			$this->saveSessionData();
			
			// generate the new messageview                
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function refreshMessageList()
		{
			return $this->generateMessageList($_folderName);
		}

		function renameFolder($_oldName, $_newParent, $_newName)
		{
			$newName = $_newParent.'.'.$_newName;
			if($this->bofelamimail->imap_renamemailbox($_oldName, $newName))
			{
				$response =& new xajaxResponse();
				$response->addScript("tree.deleteItem('$_oldName',0);");
				$response->addScript("tree.insertNewItem('$_newParent','$newName','$_newName',onNodeSelect,0,0,0,'CHILD,CHECKED,SELECT,CALL');");
				return $response->getXML();
			}
		}
		
		function saveSessionData()
		{
			$GLOBALS['egw']->session->appsession('ajax_session_data','',$this->sessionDataAjax);
			$GLOBALS['egw']->session->appsession('session_data','',$this->sessionData);
		}
		
		function skipForward()
		{
			$bofilter =& CreateObject('felamimail.bofilter');
			$caching =& CreateObject('felamimail.bocaching',
																				$this->bofelamimail->mailPreferences['imapServerAddress'],
																				$this->bofelamimail->mailPreferences['username'],
																				$this->sessionData['mailbox']);
			$messageCounter = $caching->getMessageCounter($bofilter->getFilter($this->sessionData['activeFilter']));

			$lastPage = $messageCounter - ($messageCounter % $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"]) + 1;

			$this->sessionData['startMessage']	+= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			if($this->sessionData['startMessage'] > $lastPage)
				$this->sessionData['startMessage'] = $lastPage;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function skipPrevious()
		{
			$this->sessionData['startMessage']	-= $GLOBALS['egw_info']["user"]["preferences"]["common"]["maxmatchs"];
			if($this->sessionData['startMessage'] < 1)
				$this->sessionData['startMessage'] = 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($this->sessionData['mailbox']);
		}
		
		function updateACLView()
		{
			$folderACL = $this->bofelamimail->getIMAPACL($this->sessionDataAjax['folderName']);
			
			$response =& new xajaxResponse();
			$response->addAssign("aclTable", "innerHTML", $this->createACLTable($folderACL));
			return $response->getXML();
		}
		
		function updateFolderStatus($_folderName, $_status)
		{
			$this->bofelamimail->subscribe($_folderName,($_status == '1'?'subscribe':'unsubscribe'));

			#$response =& new xajaxResponse();
			#$response->addAssign("folderName", "innerHTML", $_status);
			#return $response->getXML();
			
			#return($this->getFolderInfo($folderName));
		}
		
		function updateMessageView($_folderName)
		{
			$this->sessionData['mailbox'] 		= $_folderName;
			$this->sessionData['startMessage']	= 1;
			$this->saveSessionData();
			
			return $this->generateMessageList($_folderName);
		}
		
		function updateSingleACL($_accountName, $_aclType, $_aclStatus)
		{
			$data = $this->bofelamimail->updateSingleACL($this->sessionDataAjax['folderName'], $_accountName, $_aclType, $_aclStatus);			
			#$response =& new xajaxResponse();
			#$response->addAssign("newMailboxName", "value", $_accountName.' '.$_aclType.' '.$_aclStatus.' '.$data);
			#return $response->getXML();
		}
		
		function xajaxFolderInfo($_formValues)
		{
			$response =& new xajaxResponse();
												$response->addAssign("field1", "value", $_formValues['num1']);
												$response->addAssign("field2", "value", $_formValues['num2']);
												$response->addAssign("field3", "value", $_formValues['num1'] * $_formValues['num2']);

												return $response->getXML();
		}
	}
?>
