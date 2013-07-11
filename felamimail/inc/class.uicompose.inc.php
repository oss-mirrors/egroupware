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
	/* $Id$ */

	class uicompose
	{
		var $public_functions = array
		(
			'action'		=> True,
			'compose'		=> True,
			'composeFromDraft'	=> True,
			'getAttachment'		=> True,
			'fileSelector'		=> True,
			'forward'		=> True,
			'composeAsNew'         => True,
			'composeAsForward'=> True,
			'reply'			=> True,
			'replyAll'		=> True,
			'selectFolder'		=> True,
		);

		var $destinations = array(
			'to' 		=> 'to',
			'cc'		=> 'cc',
			'bcc'		=> 'bcc',
			'replyto'	=> 'replyto',
			'folder'	=> 'folder'
		);
		/**
		 * Instance of bofelamimail
		 *
		 * @var bofelamimail
		 */
		var $bofelamimail;
		/**
		 * Instance of bocompose
		 *
		 * @var bocompose
		 */
		var $bocompose;
		/**
		 * Active preferences, reference to $this->bofelamimail->mailPreferences
		 *
		 * @var array
		 */
		var $mailPreferences;
		/**
		 * Instance of Template class
		 *
		 * @var Template
		 */
		var $t;

		function uicompose()
		{
			$this->displayCharset   = $GLOBALS['egw']->translation->charset();
			if (!isset($_POST['composeid']) && !isset($_GET['composeid']))
			{
				// create new compose session
				$this->bocompose   = CreateObject('felamimail.bocompose','',$this->displayCharset);
				$this->composeID = $this->bocompose->getComposeID();
			}
			else
			{
				// reuse existing compose session
				if (isset($_POST['composeid']))
					$this->composeID = $_POST['composeid'];
				else
					$this->composeID = $_GET['composeid'];
				$this->bocompose   = CreateObject('felamimail.bocompose',$this->composeID,$this->displayCharset);
			}
			$this->t 		= CreateObject('phpgwapi.Template',EGW_APP_TPL);

			$this->bofelamimail	=& $this->bocompose->bofelamimail;
			$this->mailPreferences  =& $this->bofelamimail->mailPreferences;
			$this->t->set_unknowns('remove');

			$this->rowColor[0] = $GLOBALS['egw_info']["theme"]["bg01"];
			$this->rowColor[1] = $GLOBALS['egw_info']["theme"]["bg02"];
		}

		function unhtmlentities ($string)
		{
			$trans_tbl = get_html_translation_table (HTML_ENTITIES);
			$trans_tbl = array_flip ($trans_tbl);
			return strtr ($string, $trans_tbl);
		}

		function action()
		{
			$formData['identity']	= (int)$_POST['identity'];

			foreach((array)$_POST['destination'] as $key => $destination) {
				if(!empty($_POST['address'][$key])) {
					if($destination == 'folder') {
						$formData[$destination][] = $GLOBALS['egw']->translation->convert($_POST['address'][$key], $this->charset, 'UTF7-IMAP');
					} else {
						$formData[$destination][] = $_POST['address'][$key];
					}
				}
			}

			$formData['subject'] 	= $this->bocompose->stripSlashes($_POST['subject']);
			$formData['body'] 	= $this->bocompose->stripSlashes($_POST['body']);
			// if the body is empty, maybe someone pasted something with scripts, into the message body
			if(empty($formData['body']))
			{
				// this is to be found with the egw_unset_vars array for the _POST['body'] array
				$name='_POST';
				$key='body';
				#error_log($GLOBALS['egw_unset_vars'][$name.'['.$key.']']);
				if (isset($GLOBALS['egw_unset_vars'][$name.'['.$key.']']))
				{
					$formData['body'] = bocompose::_getCleanHTML( $GLOBALS['egw_unset_vars'][$name.'['.$key.']']);
				}
			}
			$formData['priority'] 	= $this->bocompose->stripSlashes($_POST['priority']);
			$formData['signatureID'] = (int)$_POST['signatureID'];
			$formData['stationeryID'] = $_POST['stationeryID'];
			$formData['mimeType']	= $this->bocompose->stripSlashes($_POST['mimeType']);
			if ($formData['mimeType'] == 'html' && html::htmlarea_availible()===false)
			{
				$formData['mimeType'] = 'plain';
				$formData['body'] = $this->bocompose->convertHTMLToText($formData['body']);
			}
			$formData['disposition'] = (bool)$_POST['disposition'];
			$formData['to_infolog'] = $_POST['to_infolog'];
			$formData['to_tracker'] = $_POST['to_tracker'];
			//$formData['mailbox']	= $_GET['mailbox'];
			if((bool)$_POST['printit'] == true) {
				$formData['printit'] = 1;
				$formData['isDraft'] = 1;
				// pint the composed message. therefore save it as draft and reopen it as plain printwindow
				$formData['subject'] = "[".lang('printview').":]".$formData['subject'];
				$messageUid = $this->bocompose->saveAsDraft($formData,$destinationFolder);
				if (!$messageUid) {
					print "<script type=\"text/javascript\">alert('".lang("Error: Could not save Message as Draft")."');</script>";
					return;
				}
				$uidisplay   = CreateObject('felamimail.uidisplay');
				$uidisplay->printMessage($messageUid, $formData['printit'],$destinationFolder);
				//egw::link('/index.php',array('menuaction' => 'felamimail.uidisplay.printMessage','uid'=>$messageUid));
				return;
			}
			if((bool)$_POST['saveAsDraft'] == true) {
				$formData['isDraft'] = 1;
				// save as draft
				$folder = $this->bofelamimail->getDraftFolder();
				$this->bofelamimail->reopen($folder);
				$status = $this->bofelamimail->getFolderStatus($folder);
				//error_log(__METHOD__.__LINE__.array2string(array('Folder'=>$folder,'Status'=>$status)));
				$uidNext = $status['uidnext']; // we may need that, if the server does not return messageUIDs of saved/appended messages
				$messageUid = $this->bocompose->saveAsDraft($formData,$folder); // folder may change
				if (!$messageUid) {
					print "<script type=\"text/javascript\">alert('".lang("Error: Could not save Message as Draft")." ".lang("Trying to recover from session data")."');</script>";
					//try to reopen the mail from session data
					$this->compose('to',true);
					return;
				}
				// saving as draft, does not mean closing the message
				unset($_POST['composeid']);
				unset($_GET['composeid']);
				$uicompose   = CreateObject('felamimail.uicompose');
				$messageUid = ($messageUid===true ? $uidNext : $messageUid);
				if (!$uicompose->bofelamimail->icServer->_connected) $uicompose->bofelamimail->openConnection($uicompose->bofelamimail->profileID);
				if ($uicompose->bofelamimail->getMessageHeader($messageUid))
				{
					//error_log(__METHOD__.__LINE__.' (re)open drafted message with new UID: '.$messageUid.' in folder:'.$folder);
					$uicompose->bocompose->getDraftData($uicompose->bofelamimail->icServer, $folder, $messageUid);
					$uicompose->compose('to',true);
					return;
				}
			} else {
				$cachedComposeID = egw_cache::getCache(egw_cache::SESSION,'email','composeIdCache'.trim($GLOBALS['egw_info']['user']['account_id']),$callback=null,$callback_params=array(),$expiration=60);
				egw_cache::setCache(egw_cache::SESSION,'email','composeIdCache'.trim($GLOBALS['egw_info']['user']['account_id']),$this->composeID,$expiration=60);
				//error_log(__METHOD__.__LINE__.' '.$formData['subject'].' '.$cachedComposeID.'<->'.$this->composeID);
				if (!empty($cachedComposeID) && $cachedComposeID == $this->composeID)
				{
					//already send
					print "<script type=\"text/javascript\">window.close();</script>";
					return;
				}
				if(!$this->bocompose->send($formData)) {
					// reset the cached composeID, as something failed
					egw_cache::setCache(egw_cache::SESSION,'email','composeIdCache'.trim($GLOBALS['egw_info']['user']['account_id']),null,$expiration=60);
//					print "<script type=\"text/javascript\">alert('".lang("Error: Could not send Message.")." ".lang("Trying to recover from session data")."');</script>";
					$this->compose();
					return;
				}
			}

			#common::egw_exit();
			print "<script type=\"text/javascript\">window.close();</script>";
		}

		function composeAsForward($_focusElement='to')
		{
			if (isset($_GET['forwardmails']))
			{
				unset($_GET['forwardmails']);
				$replyID = $_GET['reply_id'];
				$replyIds = explode(',',$replyID);
				$icServer = $this->bofelamimail->profileID;
				$folder = (isset($_GET['folder'])?base64_decode($_GET['folder']):base64_decode($_GET['mailbox']));
				//_debug_array(array('reply_id'=>$replyIds,'folder'=>$folder));
				if (!empty($folder) && !empty($replyID) ) {
					// this fill the session data with the values from the original email
					$buff = $this->bocompose->preferencesArray['message_forwarding'];
					$this->bocompose->preferencesArray['message_forwarding'] = 'asmail';
					foreach($replyIds as $key => $id)
					{
						$this->bocompose->getForwardData($icServer, $folder, $id,NULL);
					}
					$this->bocompose->preferencesArray['message_forwarding'] = $buff;
				}
			}
			$this->compose($_focusElement);
		}

		/**
		 * function compose
		 * 	this function is used to fill the compose dialog with the content provided by session data
		 *
		 * @var _focusElement		varchar subject, to, body supported
		 * @var suppressSigOnTop	boolean
		 * @var isReply				boolean
		 */
		function compose($_focusElement='to',$suppressSigOnTop=false, $isReply=false)
		{
			//error_log(__METHOD__.__LINE__.array2string($_REQUEST));
			if (isset($_GET['reply_id'])) $replyID = $_GET['reply_id'];
			// read the data from session
			// all values are empty for a new compose window
			$insertSigOnTop = false;
			$sessionData = $this->bocompose->getSessionData();
			$alwaysAttachVCardAtCompose = false; // we use this to eliminate double attachments, if users VCard is already present/attached
			if ( isset($GLOBALS['egw_info']['apps']['stylite']) && (isset($this->bocompose->preferencesArray['attachVCardAtCompose']) &&
				$this->bocompose->preferencesArray['attachVCardAtCompose']))
			{
				$alwaysAttachVCardAtCompose = true;
				if (!is_array($_REQUEST['preset']['file']) && !empty($_REQUEST['preset']['file']))
				{
					$f = $_REQUEST['preset']['file'];
					$_REQUEST['preset']['file'] = array($f);
				}
				$_REQUEST['preset']['file'][] = "vfs://default/apps/addressbook/".$GLOBALS['egw']->accounts->id2name($GLOBALS['egw_info']['user']['account_id'],'person_id')."/.entry";
			}
			// an app passed the request for fetching and mailing an entry
			if (isset($_REQUEST['app']) && isset($_REQUEST['method']) && isset($_REQUEST['id']))
			{
				$app = $_REQUEST['app'];
				$mt = $_REQUEST['method'];
				$id = $_REQUEST['id'];
				// passed method MUST be registered
				$method = egw_link::get_registry($app,$mt);
				//error_log(__METHOD__.__LINE__.array2string($method));
				if ($method)
				{
					$res = ExecMethod($method,array($id,'html'));
					//_debug_array($res);
					if (!empty($res))
					{
						$insertSigOnTop = 'below';
						if (isset($res['attachments']) && is_array($res['attachments']))
						{
							foreach($res['attachments'] as $f)
							{
								$_REQUEST['preset']['file'][] = $f;
							}
						}
						$sessionData['subject'] = lang($app).' #'.$res['id'].': ';
						foreach(array('subject','body','mimetype') as $name) {
							$sName = $name;
							if ($name=='mimetype')
							{
								$sName = 'mimeType';
								$sessionData[$sName] = $res[$name];
							}
							else
							{
								if ($res[$name]) $sessionData[$sName] .= (strlen($sessionData[$sName])>0 ? ' ':'') .$res[$name];
							}
						}
					}
				}
			}
			// handle preset info/values
			if (is_array($_REQUEST['preset']))
			{
				//_debug_array($_REQUEST);
				if ($_REQUEST['preset']['mailto']) {
					// handle mailto strings such as
					// mailto:larry,dan?cc=mike&bcc=sue&subject=test&body=type+your&body=message+here
					// the above string may be htmlentyty encoded, then multiple body tags are supported
					// first, strip the mailto: string out of the mailto URL
					$tmp_send_to = (stripos($_REQUEST['preset']['mailto'],'mailto')===false?$_REQUEST['preset']['mailto']:trim(substr(html_entity_decode($_REQUEST['preset']['mailto']),7)));
					// check if there is more than the to address
					$mailtoArray = explode('?',$tmp_send_to,2);
					if ($mailtoArray[1]) {
						// check if there are more than one requests
						$addRequests = explode('&',$mailtoArray[1]);
						foreach ($addRequests as $key => $reqval) {
							// the additional requests should have a =, to separate key from value.
							$keyValuePair = explode('=',$reqval,2);
							$sessionData[$keyValuePair[0]] .= (strlen($sessionData[$keyValuePair[0]])>0 ? ' ':'') . $keyValuePair[1];
						}
					}
					$sessionData['to']=$mailtoArray[0];
					// if the mailto string is not htmlentity decoded the arguments are passed as simple requests
					foreach(array('cc','bcc','subject','body') as $name) {
						if ($_REQUEST[$name]) $sessionData[$name] .= (strlen($sessionData[$name])>0 ? ( $name == 'cc' || $name == 'bcc' ? ',' : ' ') : '') . $_REQUEST[$name];
					}
				}

				if ($_REQUEST['preset']['mailtocontactbyid']) {
					if ($GLOBALS['egw_info']['user']['apps']['addressbook']) {
						$addressbookprefs =& $GLOBALS['egw_info']['user']['preferences']['addressbook'];
						if (method_exists($GLOBALS['egw']->contacts,'search')) {

							$addressArray = split(',',$_REQUEST['preset']['mailtocontactbyid']);
							foreach ((array)$addressArray as $id => $addressID)
							{
								$addressID = (int) $addressID;
								if (!($addressID>0))
								{
									unset($addressArray[$id]);
								}
							}
							if (count($addressArray))
							{
								$_searchCond = array('contact_id'=>$addressArray);
								//error_log(__METHOD__.__LINE__.$_searchString);
								if ($GLOBALS['egw_info']['user']['preferences']['addressbook']['hide_accounts']) $showAccounts=false;
								$filter = ($showAccounts?array():array('account_id' => null));
								$filter['cols_to_search']=array('n_fn','email','email_home');
								$contacts = $GLOBALS['egw']->contacts->search($_searchCond,array('n_fn','email','email_home'),'n_fn','','%',false,'OR',array(0,100),$filter);
								// additionally search the accounts, if the contact storage is not the account storage
								if ($showAccounts &&
									$GLOBALS['egw_info']['server']['account_repository'] == 'ldap' &&
									$GLOBALS['egw_info']['server']['contact_repository'] == 'sql')
								{
									$accounts = $GLOBALS['egw']->contacts->search($_searchCond,array('n_fn','email','email_home'),'n_fn','','%',false,'OR',array(0,100),array('owner' => 0));

									if ($contacts && $accounts)
									{
										$contacts = array_merge($contacts,$accounts);
										usort($contacts,create_function('$a,$b','return strcasecmp($a["n_fn"],$b["n_fn"]);'));
									}
									elseif($accounts)
									{
										$contacts =& $accounts;
									}
									unset($accounts);
								}
							}
							if(is_array($contacts)) {
								$mailtoArray = array();
								$primary = $addressbookprefs['distributionListPreferredMail'];
								if ($primary != 'email' && $primary != 'email_home') $primary = 'email';
								$secondary = ($primary == 'email'?'email_home':'email');
								//error_log(__METHOD__.__LINE__.array2string($contacts));
								foreach($contacts as $contact) {
									$innerCounter=0;
									foreach(array($contact[$primary],$contact[$secondary]) as $email) {
										// use pref distributionListPreferredMail for the primary address
										// avoid wrong addresses, if an rfc822 encoded address is in addressbook
										$email = preg_replace("/(^.*<)([a-zA-Z0-9_\-]+@[a-zA-Z0-9_\-\.]+)(.*)/",'$2',$email);
										$contact['n_fn'] = str_replace(array(',','@'),' ',$contact['n_fn']);
										$completeMailString = addslashes(trim($contact['n_fn'] ? $contact['n_fn'] : $contact['fn']) .' <'. trim($email) .'>');
										if($innerCounter==0 && !empty($email) && in_array($completeMailString ,$mailtoArray) === false) {
											$i++;
											$innerCounter++;
											$str = $GLOBALS['egw']->translation->convert(trim($contact['n_fn'] ? $contact['n_fn'] : $contact['fn']) .' <'. trim($email) .'>', $this->charset, 'utf-8');
											$mailtoArray[$i] = $completeMailString;
										}
									}
								}
							}
							//error_log(__METHOD__.__LINE__.array2string($mailtoArray));
							$sessionData['to']=$mailtoArray;
						}
					}
				}

				if (isset($_REQUEST['preset']['file']))
				{
					$names = (array)$_REQUEST['preset']['name'];
					$types = (array)$_REQUEST['preset']['type'];
					//if (!empty($types) && in_array('text/calendar; method=request',$types))
					$files = (array)$_REQUEST['preset']['file'];
					foreach($files as $k => $path)
					{
						if (!empty($types[$k]) && stripos($types[$k],'text/calendar')!==false)
						{
							$insertSigOnTop = 'below';
						}
						//error_log(__METHOD__.__LINE__.$path.'->'.array2string(parse_url($path,PHP_URL_SCHEME == 'vfs')));
						if (parse_url($path,PHP_URL_SCHEME == 'vfs'))
						{
							$type = egw_vfs::mime_content_type($path);
							// special handling for attaching vCard of iCal --> use their link-title as name
							if (substr($path,-7) != '/.entry' ||
								!(list($app,$id) = array_slice(explode('/',$path),-3)) ||
								!($name = egw_link::title($app, $id)))
							{
								$name = urldecode(egw_vfs::basename($path));
							}
							else
							{
								$name .= '.'.mime_magic::mime2ext($type);
							}
							$formData = array(
								'name' => $name,
								'type' => $type,
								'file' => urldecode($path),
								'size' => filesize(urldecode($path)),
							);
							if ($formData['type'] == egw_vfs::DIR_MIME_TYPE) continue;	// ignore directories
						}
						elseif(is_readable($path))
						{
							$formData = array(
								'name' => isset($names[$k]) ? $names[$k] : basename($path),
								'type' => isset($types[$k]) ? $types[$k] : (function_exists('mime_content_type') ? mime_content_type($path) : mime_magic::filename2mime($path)),
								'file' => $path,
								'size' => filesize($path),
							);
						}
						else
						{
							continue;
						}
						$this->bocompose->addAttachment($formData,($alwaysAttachVCardAtCompose?true:false));
					}
					$remember = array();
					if (isset($_REQUEST['preset']['mailto']) || (isset($_REQUEST['app']) && isset($_REQUEST['method']) && isset($_REQUEST['id'])))
					{
						foreach(array_keys($sessionData) as $k)
						{
							if (in_array($k,array('to','cc','bcc','subject','body','mimeType'))) $remember[$k] = $sessionData[$k];
						}
					}
					$sessionData = $this->bocompose->getSessionData();
					if(!empty($remember)) $sessionData = array_merge($sessionData,$remember);
				}
				foreach(array('to','cc','bcc','subject','body') as $name)
				{
					if ($_REQUEST['preset'][$name]) $sessionData[$name] = $_REQUEST['preset'][$name];
				}
			}
			//_debug_array($sessionData);
			// is the to address set already?
			if (!empty($_REQUEST['send_to']))
			{
				$sessionData['to'] = base64_decode($_REQUEST['send_to']);
				// first check if there is a questionmark or ampersand
				if (strpos($sessionData['to'],'?')!== false) list($sessionData['to'],$rest) = explode('?',$sessionData['to'],2);
				$sessionData['to'] = html_entity_decode($sessionData['to']);
				if (($at_pos = strpos($sessionData['to'],'@')) !== false)
				{
					if (($amp_pos = strpos(substr($sessionData['to'],$at_pos),'&')) !== false)
					{
						//list($email,$addoptions) = explode('&',$value,2);
						$email = substr($sessionData['to'],0,$amp_pos+$at_pos);
						$rest = substr($sessionData['to'], $amp_pos+$at_pos+1);
						//error_log(__METHOD__.__LINE__.$email.' '.$rest);
						$sessionData['to'] = $email;
					}
				}
				if (strpos($sessionData['to'],'%40')!== false) $sessionData['to'] = html::purify(str_replace('%40','@',$sessionData['to']));
				$rarr = array(html::purify($rest));
				if (isset($rest)&&!empty($rest) && strpos($rest,'&')!== false) $rarr = explode('&',$rest);
				//error_log(__METHOD__.__LINE__.$sessionData['to'].'->'.array2string($rarr));
				$karr = array();
				foreach ($rarr as $ri => $rval)
				{
					//must contain =
					if (strpos($rval,'=')!== false)
					{
						$k = $v = '';
						list($k,$v) = explode('=',$rval,2);
						$karr[$k] = $v;
					}
				}
				//error_log(__METHOD__.__LINE__.$sessionData['to'].'->'.array2string($karr));
				foreach(array('cc','bcc','subject','body') as $name)
				{
					if ($karr[$name]) $sessionData[$name] = $karr[$name];
				}
				if (!empty($_REQUEST['subject'])) $sessionData['subject'] = html::purify(trim(html_entity_decode($_REQUEST['subject'])));
			}

			//is the MimeType set/requested
			if (!empty($_REQUEST['mimeType']))
			{
				$sessionData['mimeType'] = $_REQUEST['mimeType'];
			}
			else
			{
				// try to enforce a mimeType on reply ( if type is not of the wanted type )
				if ($isReply)
				{
					if (!empty($this->bocompose->preferencesArray['replyOptions']) && $this->bocompose->preferencesArray['replyOptions']=="text" &&
						$sessionData['mimeType'] == 'html')
					{
						$sessionData['mimeType']  = 'plain';
						$sessionData['body'] = $this->bocompose->convertHTMLToText(str_replace(array("\n\r","\n"),' ',$sessionData['body']));
					}
					if (!empty($this->bocompose->preferencesArray['replyOptions']) && $this->bocompose->preferencesArray['replyOptions']=="html" &&
						$sessionData['mimeType'] != 'html')
					{
						$sessionData['mimeType']  = 'html';
						$sessionData['body'] = "<pre>".$sessionData['body']."</pre>";
						// take care this assumption is made on the creation of the reply header in bocompose::getReplyData
						if (strpos($sessionData['body'],"<pre> \r\n \r\n---")===0) $sessionData['body'] = substr_replace($sessionData['body']," <br>\r\n<pre>---",0,strlen("<pre> \r\n \r\n---")-1);
					}
				}
			}
			if ($sessionData['mimeType'] == 'html' && html::htmlarea_availible()===false)
			{
				$sessionData['mimeType'] = 'plain';
				$sessionData['body'] = $this->bocompose->convertHTMLToText($sessionData['body']);
			}
			// removal of possible script elements in HTML; getCleanHTML is the choice here, if not only for consistence
			// we use the preg of common_functions (slightly altered) to meet eGroupwares behavior on posted variables
			if ($sessionData['mimeType'] == 'html')
			{
				// this is now moved to egw_htmLawed (triggered by default config) which is called with ckeditor anyway
				//felamimail_bo::getCleanHTML($sessionData['body'],true);
			}

			// is a certain signature requested?
			// only the following values are supported (and make sense)
			// no => means -2
			// system => means -1
			// default => fetches the default, which is standard behavior
			if (!empty($_REQUEST['signature']) && (strtolower($_REQUEST['signature']) == 'no' || strtolower($_REQUEST['signature']) == 'system'))
			{
				$presetSig = (strtolower($_REQUEST['signature']) == 'no' ? -2 : -1);
			}
			if (($suppressSigOnTop || $sessionData['isDraft']) && !empty($sessionData['signatureID'])) $presetSig = (int)$sessionData['signatureID'];
			if (($suppressSigOnTop || $sessionData['isDraft']) && !empty($sessionData['stationeryID'])) $presetStationery = $sessionData['stationeryID'];
			$presetId = NULL;
			if (($suppressSigOnTop || $sessionData['isDraft']) && !empty($sessionData['identity'])) $presetId = (int)$sessionData['identity'];
			$this->display_app_header();

			$this->t->set_file(array("composeForm" => "composeForm.tpl"));
			$this->t->set_block('composeForm','header','header');
			$this->t->set_block('composeForm','body_input');
			$this->t->set_block('composeForm','attachment','attachment');
			$this->t->set_block('composeForm','attachment_row','attachment_row');
			$this->t->set_block('composeForm','attachment_row_bold');
			$this->t->set_block('composeForm','destination_row');
			$this->t->set_block('composeForm','simple_text');

			$this->translate();
			// store the selected Signature
			$this->t->set_var("mySigID",($presetSig ? $presetSig : $sessionData['signatureID']));

			if ($GLOBALS['egw_info']['user']['apps']['addressbook']) {
				$this->t->set_var("link_addressbook",egw::link('/index.php',array(
					'menuaction' => 'addressbook.addressbook_ui.emailpopup',
				)));
			} else {
				$this->t->set_var("link_addressbook",'');
			}
			$this->t->set_var("focusElement",$_focusElement);

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uicompose.selectFolder',
			);
			$this->t->set_var('folder_select_url',egw::link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uicompose.fileSelector',
				'composeid'	=> $this->composeID
			);
			$this->t->set_var('file_selector_url',egw::link('/index.php',$linkData));

			$this->t->set_var('vfs_selector_url',egw::link('/index.php',array(
				'menuaction' => 'filemanager.filemanager_select.select',
				'mode' => 'open-multiple',
				'method' => 'felamimail.uicompose.vfsSelector',
				'id' => $this->composeID,
				'label' => lang('Attach'),
			)));
			if ($GLOBALS['egw_info']['user']['apps']['filemanager'])
			{
				$this->t->set_var('vfs_attach_button','
				<button class="menuButton" type="button" onclick="fm_compose_displayVfsSelector();" title="'.htmlspecialchars(lang('filemanager')).'">
					<img src="'.common::image('filemanager','navbar').'" height="18">
				</button>');
			}
			else
			{
				$this->t->set_var('vfs_attach_button','');
			}
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uicompose.action',
				'composeid'	=> $this->composeID
			);
			$this->t->set_var("link_action",egw::link('/index.php',$linkData));
			$this->t->set_var('folder_name',$this->bofelamimail->sessionData['mailbox']);
			$this->t->set_var('compose_id',$this->composeID);
			// the editorobject is needed all the time (since we use CKEDITOR
			//$editorObject = html::initCKEditor('400px','simple');
			$this->t->set_var('ckeditorConfig', egw_ckeditor_config::get_ckeditor_config('simple-withimage'));//$editorObject->jsEncode($editorObject->config));
			$this->t->set_var('refreshTimeOut', 3*60*1000); // 3 minutes till a compose messages will be saved as draft;

			// check for some error messages from last posting attempt
			$errorInfo = $this->bocompose->getErrorInfo();
			if (isset($_REQUEST['preset']['msg'])) $errorInfo = html::purify($_REQUEST['preset']['msg']);
			if($errorInfo)
			{
				$this->t->set_var('errorInfo',"<font color=\"red\"><b>$errorInfo</b></font>");
			}
			else
			{
				$this->t->set_var('errorInfo','&nbsp;');
			}

			// header
			$allIdentities = $this->mailPreferences->getIdentity();
			unset($allIdentities[0]);
			//_debug_array($allIdentities);
			if (is_null(felamimail_bo::$felamimailConfig)) felamimail_bo::$felamimailConfig = config::read('felamimail');
			// not set? -> use default, means full display of all available data
			if (!isset(felamimail_bo::$felamimailConfig['how2displayIdentities'])) felamimail_bo::$felamimailConfig['how2displayIdentities'] ='';
			$globalIds = 0;
			$defaultIds = array();
			foreach($allIdentities as $key => $singleIdentity) {
				if ($singleIdentity->id<0){ $globalIds++; }/*else{ unset($allIdentities[$key]);}*/
				// there could be up to 2 default IDS. the activeProfile and another on marking the desired Identity to choose
				if(!empty($singleIdentity->default) && $singleIdentity->default==1) $defaultIds[$singleIdentity->id] = $singleIdentity->id;
			}
			//error_log(__METHOD__.__LINE__.' Identities regarded/marked as default:'.array2string($defaultIds). ' MailProfileActive:'.$this->bofelamimail->profileID);
			// if there are 2 defaultIDs, its most likely, that the user choose to set
			// the one not being the activeServerProfile to be his default Identity
			if (count($defaultIds)>1) unset($defaultIds[$this->bofelamimail->profileID]);
			$defaultIdentity = 0;
			$identities = array();
			foreach($allIdentities as $key => $singleIdentity) {
				//$identities[$singleIdentity->id] = $singleIdentity->realName.' <'.$singleIdentity->emailAddress.'>';
				$iS = felamimail_bo::generateIdentityString($singleIdentity);
				if (felamimail_bo::$felamimailConfig['how2displayIdentities']=='' || count($allIdentities) ==1 || count($allIdentities) ==$globalIds)
				{
					$id_prepend ='';
				}
				else
				{
					$id_prepend = '('.$singleIdentity->id.') ';
				}
				//if ($singleIdentity->default) error_log(__METHOD__.__LINE__.':'.$presetId.'->'.$key.'('.$singleIdentity->id.')'.'#'.$iS.'#');
				if (array_search($id_prepend.$iS,$identities)===false) $identities[$singleIdentity->id] = $id_prepend.$iS;
				if(in_array($singleIdentity->id,$defaultIds) && $defaultIdentity==0)
				{
					//_debug_array($singleIdentity);
					$defaultIdentity = $singleIdentity->id;
					//$defaultIdentity = $key;
					$sessionData['signatureID'] = (!empty($singleIdentity->signature) ? $singleIdentity->signature : $sessionData['signatureID']);
				}
			}
			$selectFrom = html::select('identity', (!empty($presetId) ? $presetId : $defaultIdentity), $identities, true, "style='width:100%;' onchange='changeIdentity(this);'");
			$this->t->set_var('select_from', $selectFrom);
			//error_log(__METHOD__.__LINE__.' DefaultIdentity:'.array2string($identities[($presetId ? $presetId : $defaultIdentity)]));
			// navbar(, kind of)
			$this->t->set_var('img_clear_left', common::image('felamimail','clear_left'));
			$this->t->set_var('img_fileopen', common::image('phpgwapi','fileopen'));
			$this->t->set_var('img_mail_send', common::image('felamimail','mail_send'));
			$this->t->set_var('img_attach_file', common::image('felamimail','attach'));
			$this->t->set_var('ajax-loader', common::image('felamimail','ajax-loader'));
			$this->t->set_var('img_fileexport', common::image('felamimail','fileexport'));
			// prepare print url/button
			$this->t->set_var('img_print_it', common::image('felamimail','print'));
			$this->t->set_var('lang_print_it', lang('print it'));
			$this->t->set_var('print_it', $printURL);
			// from, to, cc, replyto
			$destinationRows = 0;
			foreach(array('to','cc','bcc','replyto','folder') as $destination) {
				foreach((array)$sessionData[$destination] as $key => $value) {
					if ($value=="NIL@NIL") continue;
					if ($destination=='replyto' && str_replace('"','',$value) == str_replace('"','',$identities[($presetId ? $presetId : $defaultIdentity)])) continue;
					//error_log(__METHOD__.__LINE__.array2string(array('key'=>$key,'value'=>$value)));
					$selectDestination = html::select('destination[]', $destination, $this->destinations, false, "style='width: 100%;' onchange='fm_compose_changeInputType(this)'");
					$this->t->set_var('select_destination', $selectDestination);
					$value = htmlspecialchars_decode($value,ENT_COMPAT);
					$value = str_replace("\"\"",'"',$value);
					$address_array = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($value):$value), '');
					foreach((array)$address_array as $addressObject) {
						if ($addressObject->host == '.SYNTAX-ERROR.') continue;
						$address = imap_rfc822_write_address($addressObject->mailbox,$addressObject->host,$addressObject->personal);
						$address = felamimail_bo::htmlentities($address, $this->displayCharset);
						$this->t->set_var('address', ($destination=='folder'?$value:$address));
						$this->t->parse('destinationRows','destination_row',True);
						$destinationRows++;
					}
				}
			}
			while($destinationRows < 3) {
				// and always add one empty row
				$selectDestination = html::select('destination[]', 'to', $this->destinations, false, "style='width: 100%;' onchange='fm_compose_changeInputType(this)'");
				$this->t->set_var('select_destination', $selectDestination);
				$this->t->set_var('address', '');
				$this->t->parse('destinationRows','destination_row',True);
				$destinationRows++;
			}
			// and always add one empty row
			$selectDestination = html::select('destination[]', 'to', $this->destinations, false, "style='width: 100%;' onchange='fm_compose_changeInputType(this)'");
			$this->t->set_var('select_destination', $selectDestination);
			$this->t->set_var('address', '');
			$this->t->parse('destinationRows','destination_row',True);

			// handle subject
			$subject = felamimail_bo::htmlentities($sessionData['subject'],$this->displayCharset);
			$this->t->set_var("subject",$subject);

			if ($GLOBALS['egw_info']['user']['apps']['addressbook']) {
				$this->t->set_var('addressbookButton','<button class="menuButton" type="button" onclick="addybook();" title="'.lang('addressbook').'">
                    <img src="'.common::image('phpgwapi/templates/phpgw_website','users').'">
                </button>');
			} else {
				$this->t->set_var('addressbookButton','');
			}
			if ($GLOBALS['egw_info']['user']['apps']['infolog']) {
				$this->t->set_var('infologImage',html::image('felamimail','to_infolog',lang('Save as infolog'),'width="17px" height="17px" valign="middle"' ));
				$this->t->set_var('lang_save_as_infolog',lang('Save as infolog'));
				$this->t->set_var('infolog_checkbox','<input class="input_text" type="checkbox" id="to_infolog" name="to_infolog" />');
			} else {
				$this->t->set_var('infologImage','');
				$this->t->set_var('lang_save_as_infolog','');
				$this->t->set_var('infolog_checkbox','');
			}
			if ($GLOBALS['egw_info']['user']['apps']['tracker'])
			{
				$this->t->set_var('trackerImage',html::image('felamimail','to_tracker',lang('Save as tracker'),'width="17px" height="17px" valign="middle"' ));
				$this->t->set_var('lang_save_as_infolog',($GLOBALS['egw_info']['user']['apps']['infolog']?lang('Save:'):lang('Save as tracker')));
				$this->t->set_var('tracker_checkbox','<input class="input_text" type="checkbox" id="to_tracker" name="to_tracker" />');
			} else {
				$this->t->set_var('trackerImage','');
				$this->t->set_var('tracker_checkbox','');
			}
			$this->t->set_var('lang_no_recipient',lang('No recipient address given!'));
			$this->t->set_var('lang_no_subject',lang('No subject given!'));
			$this->t->set_var('lang_infolog_tracker_not_both',lang("You can either choose to save as infolog OR tracker, not both."));
			$this->t->pparse("out","header");

			// prepare signatures, the selected sig may be used on top of the body
			require_once(EGW_INCLUDE_ROOT.'/felamimail/inc/class.felamimail_bosignatures.inc.php');
			$boSignatures = new felamimail_bosignatures();
			$signatures = $boSignatures->getListOfSignatures();

			if (empty($sessionData['signatureID'])) {
				if ($signatureData = $boSignatures->getDefaultSignature()) {
					if (is_array($signatureData)) {
						$sessionData['signatureID'] = $signatureData['signatureid'];
					} else {
						$sessionData['signatureID'] =$signatureData;
					}
				}
			}

			$selectSignatures = array(
				'-2' => lang('no signature')
			);
			foreach($signatures as $signature) {
				$selectSignatures[$signature['fm_signatureid']] = lang('Signature').': '.$signature['fm_description'];
			}
			$disableRuler = false;
			$signature = $boSignatures->getSignature(($presetSig ? $presetSig : $sessionData['signatureID']));
			if ((isset($this->bocompose->preferencesArray['disableRulerForSignatureSeparation']) &&
				$this->bocompose->preferencesArray['disableRulerForSignatureSeparation']) ||
				empty($signature->fm_signature) || trim($this->bocompose->convertHTMLToText($signature->fm_signature,true,true)) =='')
			{
				$disableRuler = true;
			}
			$font_span ='';
			if($sessionData['mimeType'] == 'html' /*&& trim($sessionData['body'])==''*/) {
				// User preferences for style
				$font = $GLOBALS['egw_info']['user']['preferences']['common']['rte_font'];
				$font_size = egw_ckeditor_config::font_size_from_prefs();
				$font_span = '<span '.($font||$font_size?'style="':'').($font?'font-family:'.$font.'; ':'').($font_size?'font-size:'.$font_size.'; ':'').'">'.'&nbsp;'.'</span>';
				if (empty($font) && empty($font_size)) $font_span = '';
			}
			//remove possible html header stuff
			if (stripos($sessionData['body'],'<html><head></head><body>')!==false) $sessionData['body'] = str_ireplace(array('<html><head></head><body>','</body></html>'),array('',''),$sessionData['body']);
			//error_log(__METHOD__.__LINE__.array2string($this->bocompose->preferencesArray));
			$blockElements = array('address','blockquote','center','del','dir','div','dl','fieldset','form','h1','h2','h3','h4','h5','h6','hr','ins','isindex','menu','noframes','noscript','ol','p','pre','table','ul');
			if (isset($this->bocompose->preferencesArray['insertSignatureAtTopOfMessage']) &&
				$this->bocompose->preferencesArray['insertSignatureAtTopOfMessage'] &&
				!(isset($_POST['mySigID']) && !empty($_POST['mySigID']) ) && !$suppressSigOnTop
			)
			{
				$insertSigOnTop = ($insertSigOnTop?$insertSigOnTop:true);
				$sigText = felamimail_bo::merge($signature->fm_signature,array($GLOBALS['egw']->accounts->id2name($GLOBALS['egw_info']['user']['account_id'],'person_id')));
				if ($sessionData['mimeType'] == 'html')
				{
					$sigTextStartsWithBlockElement = ($disableRuler?false:true);
					foreach($blockElements as $e)
					{
						if ($sigTextStartsWithBlockElement) break;
						if (stripos(trim($sigText),'<'.$e)===0) $sigTextStartsWithBlockElement = true;
					}
				}
				if($sessionData['mimeType'] == 'html') {
					$before = (!empty($font_span) && !($insertSigOnTop === 'below')?$font_span:'&nbsp;').($disableRuler?''/*($sigTextStartsWithBlockElement?'':'<p style="margin:0px;"/>')*/:'<hr style="border:1px dotted silver; width:90%;">');
					$inbetween = '&nbsp;<br>';
				} else {
					$before = ($disableRuler ?"\r\n\r\n":"\r\n\r\n-- \r\n");
					$inbetween = "\r\n";
				}
				if ($sessionData['mimeType'] == 'html')
				{
					$sigText = ($sigTextStartsWithBlockElement?'':"<div>")."<!-- HTMLSIGBEGIN -->".$sigText."<!-- HTMLSIGEND -->".($sigTextStartsWithBlockElement?'':"</div>");
				}

				if ($insertSigOnTop === 'below')
				{
					$sessionData['body'] = $font_span.$sessionData['body'].$before.($sessionData['mimeType'] == 'html'?$sigText:$this->bocompose->convertHTMLToText($sigText,true,true));
				}
				else
				{
					$sessionData['body'] = $before.($sessionData['mimeType'] == 'html'?$sigText:$this->bocompose->convertHTMLToText($sigText,true,true)).$inbetween.$sessionData['body'];
				}
			}
			else
			{
				$sessionData['body'] = ($font_span?$font_span:'&nbsp;').$sessionData['body'];
			}
			//error_log(__METHOD__.__LINE__.$sessionData['body']);
			// prepare body
			// in a way, this tests if we are having real utf-8 (the displayCharset) by now; we should if charsets reported (or detected) are correct
			if (strtoupper($this->displayCharset) == 'UTF-8')
			{
				$test = @json_encode($sessionData['body']);
				//error_log(__METHOD__.__LINE__.' ->'.strlen($singleBodyPart['body']).' Error:'.json_last_error().'<- BodyPart:#'.$test.'#');
				//if (json_last_error() != JSON_ERROR_NONE && strlen($singleBodyPart['body'])>0)
				if ($test=="null" && strlen($sessionData['body'])>0)
				{
					// this should not be needed, unless something fails with charset detection/ wrong charset passed
					error_log(__METHOD__.__LINE__.' Charset problem detected; Charset Detected:'.felamimail_bo::detect_encoding($sessionData['body']));
					$sessionData['body'] = utf8_encode($sessionData['body']);
				}
			}
			//error_log(__METHOD__.__LINE__.$sessionData['body']);
			if($sessionData['mimeType'] == 'html') {
				$mode = 'simple-withimage';
				//$mode ='advanced';// most helpful for debuging
				#if (isset($GLOBALS['egw_info']['server']['enabled_spellcheck'])) $mode = 'egw_simple_spellcheck';
				$style="border:0px; width:100%; height:500px;";
				// dont run purify, as we already did that (getCleanHTML).
				$this->t->set_var('tinymce', html::fckEditorQuick('body', $mode, $sessionData['body'],'500px','100%',false,'0px',($_focusElement=='body'?true:false),($_focusElement!='body'?'parent.setToFocus("'.$_focusElement.'");':'')));
				$this->t->set_var('mimeType', 'html');
				$ishtml=1;
			} else {
				$border="1px solid gray; margin:1px";
				// initalize the CKEditor Object to enable switching back and force
				$editor = html::fckEditorQuick('body', 'ascii', $sessionData['body'],'500px','99%',false,$border);
				$this->t->set_var('tinymce', $editor); //html::fckEditorQuick('body', 'ascii', $sessionData['body'],'400px','99%'));
				$this->t->set_var('mimeType', 'text');
				$ishtml=0;
			}


			$bostationery = new felamimail_bostationery();
			$selectStationeries = array(
				'0' => lang('no stationery')
			);
			$showStationaries = false;
			$validStationaries = $bostationery->get_valid_templates();
			if (is_array($validStationaries) && count($validStationaries)>0)
			{
				$showStationaries = true;
				$selectStationeries += $validStationaries;
			}
			// if ID of signature Select Box is set, we allow for changing the sig onChange of the signatueSelect
			$selectBoxSignature  = html::select('signatureID', ($presetSig ? $presetSig : $sessionData['signatureID']), $selectSignatures, true, ($insertSigOnTop?"id='signatureID'":"")." style='width: 35%;' onchange='fm_compose_changeInputType(this)'");
			$selectBoxStationery = html::select('stationeryID', ($presetStationery ? $presetStationery : 0), $selectStationeries, true, "style='width: 35%;'");
			$this->t->set_var("select_signature", $selectBoxSignature);
			$this->t->set_var("select_stationery", ($showStationaries ? $selectBoxStationery:''));
			$this->t->set_var("lang_editormode",lang("Editor type"));
			if (html::htmlarea_availible()===false)
			{
				$this->t->set_var("toggle_editormode",'');
			}
			else
			{
				// IE seems to need onclick to realize there is a change
				$this->t->set_var("toggle_editormode", lang("Editor type").":&nbsp;<span><input name=\"_is_html\" value=\"".$ishtml."\" type=\"hidden\" /><input name=\"_editorselect\" onchange=\"fm_toggle_editor(this)\" onclick=\"fm_toggle_editor(this)\" ".($ishtml ? "checked=\"checked\"" : "")." id=\"_html\" value=\"html\" type=\"radio\"><label for=\"_html\">HTML</label><input name=\"_editorselect\" onchange=\"fm_toggle_editor(this)\" onclick=\"fm_toggle_editor(this)\" ".($ishtml ? "" : "checked=\"checked\"")." id=\"_plain\" value=\"plain\" type=\"radio\"><label for=\"_plain\">Plain text</label></span>");
			}
			$this->t->pparse("out","body_input");

			// attachments
			if (is_array($sessionData['attachments']) && count($sessionData['attachments']) > 0)
			{
				$imgClearLeft	=  common::image('felamimail','clear_left');
				$tableRows = array();
				foreach((array)$sessionData['attachments'] as $id => $attachment) {
					$tempArray = array (
						'1' => $attachment['name'], '.1' => 'width="40%"',
						'2' => mime_magic::mime2label($attachment['type']),
						'3' => egw_vfs::hsize($attachment['size']), '.3' => "style='text-align:right;'",
						'4' => '&nbsp;', '.4' => 'width="10%"',
						'5' => "<img src='$imgClearLeft' onclick=\"fm_compose_deleteAttachmentRow(this,'".$this->composeID."','$id')\">",
					);
					$tableRows[] = $tempArray;
				}

				if(count($tableRows) > 0) {
					$table = html::table($tableRows, "style='width:100%'");
				}
				$this->t->set_var('attachment_rows',$table);
			}
			else
			{
				$this->t->set_var('attachment_rows','');
			}

			$this->t->pparse("out","attachment");
		}

		function composeFromDraft() {
			$icServer = (int)$_GET['icServer'];
			$folder = (isset($_GET['folder'])?base64_decode($_GET['folder']):base64_decode($_GET['mailbox']));
			$replyID = $_GET['uid'];

			if (!empty($folder) && !empty($replyID) ) {
				// this fill the session data with the values from the original email
				$this->bocompose->getDraftData($icServer, $folder, $replyID);
			}
			$this->compose('body',$suppressSigOnTop=true);
		}

		function display_app_header()
		{
			egw_framework::validate_file('jscode','composeMessage','felamimail');
			egw_framework::validate_file('ckeditor','ckeditor','phpgwapi');

			$GLOBALS['egw']->js->set_onload('javascript:initAll();');
			$GLOBALS['egw']->js->set_onbeforeunload("if (do_onunload && justClickedSend==false) if (draftsMayExist) {a = checkunload(browserSupportsOnUnloadConfirm?'".addslashes(lang("Please choose:"))."'+'\\n'+'".addslashes(lang("1) keep drafted message (press OK)"))."'+'\\n'+'".addslashes(lang("2) discard the message completely (press Cancel)"))."':'".addslashes(lang("if you leave this page without saving to draft, the message will be discarded completely"))."');".' if (!browserSupportsOnUnloadConfirm) return a;}');
			//$GLOBALS['egw']->js->set_onbeforeunload("if (do_onunload) if (draftsMayExist) return '".addslashes(lang("Please choose:"))."'+'\\n'+'".addslashes(lang("1) keep drafted message (press OK)"))."'+'\\n'+'".addslashes(lang("2) discard the message completely (press Cancel)"))."';");
			$GLOBALS['egw']->js->set_onunload("if (do_onunload && justClickedSend==false) checkunload();");
			//$GLOBALS['egw']->js->set_onunload("if (do_onunload) egw_appWindow('felamimail').xajax_doXMLHTTPsync('felamimail.ajaxfelamimail.removeLastDraftedVersion','".$this->composeID."');");

			$GLOBALS['egw_info']['flags']['include_xajax'] = True;

			common::egw_header();
		}

		/**
		 * Callback for filemanagers select file dialog
		 *
		 * @param string $composeid
		 * @param string|array $files path of file(s) in vfs (no egw_vfs::PREFIX, just the path)
		 * @return string javascript output by the file select dialog, usually to close it
		 */
		function vfsSelector($composeid,$files)
		{
			$this->bocompose   = CreateObject('felamimail.bocompose',$this->composeID=$composeid,$this->displayCharset);

			foreach((array) $files as $path)
			{
				$formData = array(
					'name' => egw_vfs::basename($path),
					'type' => egw_vfs::mime_content_type($path),
					'file' => egw_vfs::PREFIX.$path,
					'size' => filesize(egw_vfs::PREFIX.$path),
				);
				$this->bocompose->addAttachment($formData);
			}
			return 'window.close();';
		}

		function fileSelector()
		{
			if(is_array($_FILES["addFileName"])) {
				#phpinfo();
				//_debug_array($_FILES);
				$success=false;
				if (is_array($_FILES["addFileName"]['name']))
				{
					// multiple uploads supported by newer firefox (>3.6) and chrome (>4) versions,
					// upload array information is by key within the attribute (name, type, size, temp_name)
					foreach($_FILES["addFileName"]['name'] as $key => $filename)
					{
						if($_FILES['addFileName']['error'][$key] == $UPLOAD_ERR_OK) {
							$formData['name']	= $_FILES['addFileName']['name'][$key];
							$formData['type']	= $_FILES['addFileName']['type'][$key];
							$formData['file']	= $_FILES['addFileName']['tmp_name'][$key];
							$formData['size']	= $_FILES['addFileName']['size'][$key];
							$this->bocompose->addAttachment($formData);
							$success = true;
						}
					}
				}
				else // should not happen as upload form name is defined as addFileName[]
				{
					if($_FILES['addFileName']['error'] == $UPLOAD_ERR_OK) {
						$formData['name']   = $_FILES['addFileName']['name'];
						$formData['type']   = $_FILES['addFileName']['type'];
						$formData['file']   = $_FILES['addFileName']['tmp_name'];
						$formData['size']   = $_FILES['addFileName']['size'];
						$this->bocompose->addAttachment($formData);
						$success = true;
					}
				}
				if ($success == true)
				{
					print "<script type='text/javascript'>window.close();</script>";
				}
				else
				{
					print "<script type='text/javascript'>document.getElementById('fileSelectorDIV1').style.display = 'inline';document.getElementById('fileSelectorDIV2').style.display = 'none';</script>";
				}
			}

			// this call loads js and css for the treeobject
			html::tree(false,false,false,null,'foldertree','','',false,'/',null,false);
			egw_framework::validate_file('jscode','composeMessage','felamimail');
			common::egw_header();

			#$uiwidgets		=& CreateObject('felamimail.uiwidgets');

			$this->t->set_file(array("composeForm" => "composeForm.tpl"));
			$this->t->set_block('composeForm','fileSelector','fileSelector');

			$this->translate();

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uicompose.fileSelector',
				'composeid'	=> $this->composeID
			);
			$this->t->set_var('file_selector_url', egw::link('/index.php',$linkData));

			$maxUploadSize = ini_get('upload_max_filesize');
			$this->t->set_var('max_uploadsize', $maxUploadSize);

			$this->t->set_var('ajax-loader', common::image('felamimail','ajax-loader'));

			$this->t->pparse("out","fileSelector");
		}

		function forward() {
			$icServer = (int)$_GET['icServer'];
			$folder = base64_decode($_GET['folder']);
			$replyID = $_GET['reply_id'];
			$partID  = $_GET['part_id'];
			$mode = false;
			if (isset($_GET['mode']) && ($_GET['mode']=='forwardasattach'||$_GET['mode']=='forwardinline')) $mode  = ($_GET['mode']=='forwardinline'?'inline':'asattach');
			if (!empty($replyID))
			{
				// this fill the session data with the values from the original email
				$this->bocompose->getForwardData($icServer, $folder, $replyID, $partID, $mode);
			}
			$handleAsReply = ($mode?$mode=='inline':$this->bocompose->preferencesArray['message_forwarding'] == 'inline');
			$this->compose('to',false, $handleAsReply);
		}

		function getAttachment()
		{
			$bocompose  = CreateObject('felamimail.bocompose', $_GET['_composeID']);
			$attachment =  $bocompose->sessionData['attachments'][$_GET['attID']] ;
			if (!empty($attachment['folder']))
			{
				$is_winmail = $_GET['is_winmail'] ? $_GET['is_winmail'] : 0;
				$this->mailbox  = $attachment['folder'];
				$this->bofelamimail->reopen($this->mailbox);
				#$attachment 	= $this->bofelamimail->getAttachment($this->uid,$part);
				$attachmentData = $this->bofelamimail->getAttachment($attachment['uid'],$attachment['partID'],$is_winmail);
				$this->bofelamimail->closeConnection();
			}

			if (parse_url($attachment['file'],PHP_URL_SCHEME) == 'vfs')
			{
				egw_vfs::load_wrapper('vfs');
			}
			//error_log(print_r($attachmentData,true));
			header ("Content-Type: ".$attachment['type']."; name=\"". $this->bofelamimail->decode_header($attachment['name']) ."\"");
			header ("Content-Disposition: inline; filename=\"". $this->bofelamimail->decode_header($attachment['name']) ."\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");
			if (!empty($attachment['file']))
			{
				$fp = fopen($attachment['file'], 'rb');
				fpassthru($fp);
				fclose($fp);
			}
			else
			{
				echo $attachmentData['attachment'];
			}
			common::egw_exit();
			exit;

		}


		function selectFolder()
		{
			// this call loads js and css for the treeobject
			html::tree(false,false,false,null,'foldertree','','',false,'/',null,false);

			egw_framework::validate_file('jscode','composeMessage','felamimail');
			common::egw_header();

			$bofelamimail		= $this->bofelamimail;
			$uiwidgets		= CreateObject('felamimail.uiwidgets');
			$connectionStatus	= $bofelamimail->openConnection($bofelamimail->profileID);

			$folderObjects = $bofelamimail->getFolderObjects(true,false);
			$folderTree = $uiwidgets->createHTMLFolder
			(
				$folderObjects,
				'INBOX',
				0,
				lang('IMAP Server'),
				$mailPreferences['username'].'@'.$mailPreferences['imapServerAddress'],
				'divFolderTree',
				false//,
				//true
			);
			print '<div id="divFolderTree" style="overflow:auto; width:320px; height:450px; margin-bottom:0px; padding-left:0px; padding-top:0px; z-index:100; border:1px solid Silver;"></div>';
			print $folderTree;
		}

		function composeAsNew() {
			$icServer = (int)$_GET['icServer'];
			$folder = (isset($_GET['folder'])?base64_decode($_GET['folder']):base64_decode($_GET['mailbox']));
			$replyID = $_GET['reply_id'];
			$partID  = $_GET['part_id'];
			if (!empty($folder) && !empty($replyID) ) {
				// this fill the session data with the values from the original email
				$this->bocompose->getDraftData($icServer, $folder, $replyID, $partID);
			}
			$this->compose('body',true);
		}

		function reply() {
			$icServer = (int)$_GET['icServer'];
			$folder = base64_decode($_GET['folder']);
			$replyID = $_GET['reply_id'];
			$partID	 = $_GET['part_id'];
			if (!empty($folder) && !empty($replyID) ) {
				// this fill the session data with the values from the original email
				$this->bocompose->getReplyData('single', $icServer, $folder, $replyID, $partID);
			}
			$this->compose('body',false,true);
		}

		function replyAll() {
			$icServer = (int)$_GET['icServer'];
			$folder = base64_decode($_GET['folder']);
			$replyID = $_GET['reply_id'];
			$partID	 = $_GET['part_id'];
			if (!empty($folder) && !empty($replyID) ) {
				// this fill the session data with the values from the original email
				$this->bocompose->getReplyData('all', $icServer, $folder, $replyID, $partID);
			}
			$this->compose('body',false,true);
		}

		function translate() {
			$this->t->set_var("lang_message_list",lang('Message List'));
			$this->t->set_var("lang_to",lang('to'));
			$this->t->set_var("lang_cc",lang('cc'));
			$this->t->set_var("lang_bcc",lang('bcc'));
			$this->t->set_var("lang_identity",lang('identity'));
			$this->t->set_var("lang_reply_to",lang('reply to'));
			$this->t->set_var("lang_subject",lang('subject'));
			$this->t->set_var("lang_addressbook",lang('addressbook'));
			$this->t->set_var("lang_search",lang('search'));
			$this->t->set_var("lang_send",lang('send'));
			$this->t->set_var('lang_save_as_draft',lang('save as draft'));
			$this->t->set_var("lang_back_to_folder",lang('back to folder'));
			$this->t->set_var("lang_attachments",lang('attachments'));
			$this->t->set_var("lang_add",lang('add'));
			$this->t->set_var("lang_remove",lang('remove'));
			$this->t->set_var("lang_priority",lang('priority'));
			$this->t->set_var("lang_normal",lang('normal'));
			$this->t->set_var("lang_high",lang('high'));
			$this->t->set_var("lang_low",lang('low'));
			$this->t->set_var("lang_signature",lang('signature'));
			$this->t->set_var("lang_stationery",lang('stationery'));
			$this->t->set_var("lang_select_folder",lang('select folder'));
			$this->t->set_var('lang_max_uploadsize',lang('max uploadsize'));
			$this->t->set_var('lang_adding_file_please_wait',lang('Adding file to message. Please wait!'));
			$this->t->set_var('lang_receive_notification',lang('Receive notification'));
			$this->t->set_var('lang_no_address_set',lang('can not send message. no recipient defined!'));

			$this->t->set_var("th_bg",$GLOBALS['egw_info']["theme"]["th_bg"]);
			$this->t->set_var("bg01",$GLOBALS['egw_info']["theme"]["bg01"]);
			$this->t->set_var("bg02",$GLOBALS['egw_info']["theme"]["bg02"]);
			$this->t->set_var("bg03",$GLOBALS['egw_info']["theme"]["bg03"]);
		}

}

?>
