<?php
/**
 * EGroupware: ActiveSync access: FMail plugin
 *
 * @link http://www.egroupware.org
 * @package felamimail
 * @subpackage activesync
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Philip Herbert <philip@knauber.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once (EGW_INCLUDE_ROOT.'/felamimail/inc/class.bofelamimail.inc.php');
//require_once (EGW_INCLUDE_ROOT.'/felamimail/inc/class.uidisplay.inc.php');
//require_once (EGW_INCLUDE_ROOT.'/phpgwapi/inc/class.egw_mailer.inc.php');

/**
 * FMail activesync plugin
 *
 * Plugin creates a device specific file to map alphanumeric folder names to nummeric id's.
 */
class felamimail_activesync implements activesync_plugin_read
{
	/**
	 * var BackendEGW
	 */
	private $backend;

	/**
	 * Instance of bofelamimail
	 *
	 * @var bofelamimail
	 */
	private $mail;

	/**
	 * Instance of uidisplay
	 * needed to use various bodyprocessing functions
	 *
	 * @var uidisplay
	 */
	//private $ui; // may not be needed after all

	/**
	 * Integer id of trash folder
	 *
	 * @var mixed
	 */
	private $_wasteID = false;

	/**
	 * Integer id of sent folder
	 *
	 * @var mixed
	 */
	private $_sentID = false;

	/**
	 * Integer id of current mail account / connection
	 *
	 * @var int
	 */
	private $account;

	/**
	 * debugLevel - enables more debug
	 *
	 * @var int
	 */
	private $debugLevel = 1;

	/**
	 * Constructor
	 *
	 * @param BackendEGW $backend
	 */
	public function __construct(BackendEGW $backend)
	{
		$this->backend = $backend;
	}

	/**
	 * Open IMAP connection
	 *
	 * @param int $account integer id of account to use
	 * @todo support different accounts
	 */
	private function _connect($account=0)
	{
		if ($this->mail && $this->account != $account) $this->_disconnect();

		$this->_wasteID = false;
		$this->_sentID = false;
		if (!$this->mail)
		{
			$this->account = $account;
			// todo: tell fmail which account to use
			$this->mail = new bofelamimail ("UTF-8",false);
			//$this->ui	= new uidisplay();

			if (!$this->mail->openConnection(0,false))
			{
				throw new egw_exception_not_found(__METHOD__."($account) can not open connection!");
			}
		}
	}

	/**
	 * Close IMAP connection
	 */
	private function _disconnect()
	{
		debugLog(__METHOD__);
		if ($this->mail) $this->mail->closeConnection();

		unset($this->mail);
		unset($this->account);
		unset($this->folders);
	}

	private $folders;
	private $messages;

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetFolderList()
	{
		$folderlist = array();
		debugLog(__METHOD__.__LINE__);
		/*foreach($available_accounts as $account)*/ $account = 0;
		{
			$this->_connect($account);
			if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

			foreach ($this->folders as $folder => $folderObj) {
				$folderlist[] = $f = array(
					'id'     => $this->createID($account,$folder),
					'mod'    => $folderObj->shortDisplayName,
					'parent' => $this->getParentID($account,$folder),
				);
				if ($this->debugLevel>0) debugLog(__METHOD__."() returning ".array2string($f));
			}
		}
		//debugLog(__METHOD__."() returning ".array2string($folderlist));

		return $folderlist;
	}

	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $mimesupport = 0)
	{
		debugLog (__METHOD__.__LINE__);
		$stat = $this->StatMessage($folderid, $id);
		if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($stat));
		// StatMessage should reopen the folder in question, so we dont need folderids in the following statements.
		if ($stat)
		{
			debugLog(__METHOD__.__LINE__." Message $id with stat ");
			// initialize the object
			$output = new SyncMail();
			$headers = $this->mail->getMessageHeader($id,'',true);
			//$rawHeaders = $this->mail->getMessageRawHeader($id);
			// simple style
			// start AS12 Stuff (bodypreference === false) case = old behaviour
			debugLog(__METHOD__.__LINE__.' Bodypreference: '.array2string($bodypreference));
			if ($bodypreference === false) {
				$bodyStruct = $this->mail->getMessageBody($id, 'only_if_no_text');
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);
				$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				$body = preg_replace("/<style.*?<\/style>/is", "", $body); // in case there is only a html part
				// remove all other html
				$body = strip_tags($body);
				if(strlen($body) > $truncsize) {
					$body = utf8_truncate($body, $truncsize);
					$output->bodytruncated = 1;
				}
				else
				{
					$output->bodytruncated = 0;
				}
				$output->bodysize = strlen($body);
				$output->body = $body;
			}
			else // style with bodypreferences
			{
				if (isset($bodypreference[1]) && !isset($bodypreference[1]["TruncationSize"]))
					$bodypreference[1]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[2]) && !isset($bodypreference[2]["TruncationSize"]))
					$bodypreference[2]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[3]) && !isset($bodypreference[3]["TruncationSize"]))
					$bodypreference[3]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[4]) && !isset($bodypreference[4]["TruncationSize"]))
					$bodypreference[4]["TruncationSize"] = 1024*1024;
				// set the protocoll class
				$output->airsyncbasebody = new SyncAirSyncBaseBody();
				if ($this->debugLevel>0) debugLog("airsyncbasebody!");
				// fetch the body (try to gather data only once)
				$bodyStruct = $this->mail->getMessageBody($id, 'always_display');
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);//$this->ui->getdisplayableBody($bodyStruct,false);
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' Always Display:'.$body);
			    if ($body != "" || (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
					// may be html
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:html (fetched with always_display)');
				    $output->airsyncbasenativebodytype=2;
				} else {
					// plain text Message
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:plain, fetch text (HTML, if no text available)');
				    $output->airsyncbasenativebodytype=1;
			        $bodyStruct = $this->mail->getMessageBody($id,'only_if_no_text');//'never_display');
				    $body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
				}
				// whatever format decode (using the correct encoding)
				if ($this->debugLevel>3) debugLog("MIME Body".' Type:'.($output->airsyncbasenativebodytype==2?' html ':' plain ').$body);
				$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				// prepare plaintextbody
				if ($output->airsyncbasenativebodytype == 2) $plainBody = $this->mail->convertHTMLToText($body,true); // always display with preserved HTML
				//if ($this->debugLevel>0) debugLog("MIME Body".$body);
				$plainBody = preg_replace("/<style.*?<\/style>/is", "", $plainBody);
				// remove all other html
				$plainBody = preg_replace("/<br.*>/is","<br>",$plainBody);
				$plainBody = preg_replace("/<br >/is","<br>",$plainBody);
				$plainBody = preg_replace("/<br\/>/is","<br>",$plainBody);
				$plainBody = str_replace("<br>","\r\n",$plainBody);
				$plainBody = strip_tags($plainBody);
				if ($this->debugLevel>3 && $output->airsyncbasenativebodytype==1) debugLog(__METHOD__.__LINE__.' Plain Text:'.$plainBody);
				//$body = str_replace("\n","\r\n", str_replace("\r","",$body)); // do we need that?
				if (isset($bodypreference[4]))
				{
					$output->airsyncbasebody->type = 4;
					//$rawBody = $this->mail->getMessageRawBody($id);
					$mailObject = new egw_mailer();
					// this try catch block is probably of no use anymore, as it was intended to catch exceptions thrown by parseRawMessageIntoMailObject
					try
					{
						// we create a complete new rfc822 message here to paa a clean one to the client.
						// this strips a lot of information, but ...
						$Header = $Body = '';
						if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." Creation of Mailobject.");
						//if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__." Using data from ".$rawHeaders.$rawBody);
						//$this->mail->parseRawMessageIntoMailObject($mailObject,$rawHeaders.$rawBody,$Header,$Body);
						//debugLog(__METHOD__.__LINE__.array2string($headers));
						// now force UTF-8
						$mailObject->CharSet = 'utf-8';
						$mailObject->Priority = $headers['PRIORITY'];
						$mailObject->Encoding = 'quoted-printable'; // we use this by default

						$mailObject->RFCDateToSet = $headers['DATE'];
						$mailObject->Sender = $headers['RETURN-PATH'];
						$mailObject->Subject = $headers['SUBJECT'];
						$mailObject->MessageID = $headers['MESSAGE-ID'];
						// from
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['FROM']):$headers['FROM']),'');
						foreach((array)$address_array as $addressObject) {
							$mailObject->From = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
							$mailObject->FromName = $addressObject->personal;
						}
						// to
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['TO']):$headers['TO']),'');
						foreach((array)$address_array as $addressObject) {
							$mailObject->AddAddress($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
						}
						// CC
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['CC']):$headers['CC']),'');
						foreach((array)$address_array as $addressObject) {
							$mailObject->AddAddress($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
						}
						//	AddReplyTo
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['REPLY-TO']):$headers['REPLY-TO']),'');
						foreach((array)$address_array as $addressObject) {
							$mailObject->AddReplyTo($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
						}
						$Header = $Body = ''; // we do not use Header and Body we use the MailObject
						if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." Creation of Mailobject succeeded.");
					}
					catch (egw_exception_assertion_failed $e)
					{
						debugLog(__METHOD__.__LINE__." Creation of Mail failed.".$e->getMessage());
						$Header = $Body = '';
					}

					if ($this->debugLevel>0) debugLog("MIME Body"); // body is retrieved up
					if ($output->airsyncbasenativebodytype==2) { //html
						if ($this->debugLevel>0) debugLog("HTML Body");
						$mailObject->IsHTML(true);
						$html = '<html>'.
	    					    '<head>'.
						        '<meta name="Generator" content="Z-Push">'.
						        '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
							    '</head>'.
							    '<body>'.
						        str_replace("\n","<BR>",str_replace("\r","", str_replace("\r\n","<BR>",$body))).
							    '</body>'.
								'</html>';
						$mailObject->Body = str_replace("\n","\r\n", str_replace("\r","",$html));
						$mailObject->AltBody = $plainBody;
					}
					if ($output->airsyncbasenativebodytype==1) { //plain
						if ($this->debugLevel>0) debugLog("Plain Body");
						$mailObject->IsHTML(false);
						$mailObject->Body = $plainBody;
						$mailObject->AltBody = '';
					}
					// we still need the attachments to be added ( if there are any )
					// start handle Attachments
					$attachments = $this->mail->getMessageAttachments($id);
					if (is_array($attachments))
					{
						debugLog(__METHOD__.__LINE__.' gather Attachments for BodyCreation of/for MessageID:'.$id.' found:'.count($attachments));
						foreach((array)$attachments as $key => $attachment) 
						{
							if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attachment));
							switch($attachment['type']) 
							{
								case 'MESSAGE/RFC822':
									$rawHeader = $rawBody = '';
									if (isset($attachment['partID'])) 
									{
										$rawHeader = $this->mail->getMessageRawHeader($attachment['uid'], $attachment['partID']);
									}
									$rawBody = $this->mail->getMessageRawBody($attachment['uid'], $attachment['partID']);
									$mailObject->AddStringAttachment($rawHeader.$rawBody, $mailObject->EncodeHeader($attachment['name']), '7bit', 'message/rfc822');
									break;
								default:
									$attachmentData = '';
									$attachmentData	= $this->mail->getAttachment($attachment['uid'], $attachment['partID']);
									$mailObject->AddStringAttachment($attachmentData['attachment'], $mailObject->EncodeHeader($attachment['name']), 'base64', $attachment['type']);
									break;
							}
						}
					}

					$mailObject->SetMessageType();
					$Header = $mailObject->CreateHeader();
					$Body = trim($mailObject->CreateBody()); // philip thinks this is needed, so lets try if it does any good/harm
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' MailObject:'.array2string($mailObject));
					if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__." Setting Mailobjectcontent to output:".$Header.$mailObject->LE.$mailObject->LE.$Body);
					$output->airsyncbasebody->data = $Header.$mailObject->LE.$mailObject->LE.$Body;
				}
				else if (isset($bodypreference[2]))
				{
					if ($this->debugLevel>0) debugLog("HTML Body");
					// Send HTML if requested and native type was html
					$output->airsyncbasebody->type = 2;
					if ($output->airsyncbasenativebodytype==2) 
					{
						// as we fetch html, and body is HTML, we may not need to handle this
					}
					else
					{
						// html requested but got only plaintext, so fake html
						$body = '<html>'.
							'<head>'.
							'<meta name="Generator" content="Z-Push">'.
							'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
							'</head>'.
							'<body>'.
							str_replace("\n","<BR>",str_replace("\r","<BR>", str_replace("\r\n","<BR>",$plainBody))).
							'</body>'.
							'</html>';
					}
					if(isset($bodypreference[2]["TruncationSize"]) && strlen($html) > $bodypreference[2]["TruncationSize"]) 
					{
						$body = utf8_truncate($body,$bodypreference[2]["TruncationSize"]);
						$output->airsyncbasebody->truncated = 1;
					}
					$output->airsyncbasebody->data = $body;
				}
				else
				{
					// Send Plaintext as Fallback or if original body is plainttext
					if ($this->debugLevel>0) debugLog("Plaintext Body");
					/* we use plainBody (set above) instead
					$bodyStruct = $this->mail->getMessageBody($id,'only_if_no_text'); //'never_display');
					$plain = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
					$plain = html_entity_decode($plain,ENT_QUOTES,$this->mail->detect_encoding($plain));
					$plain = strip_tags($plain);
					//$plain = str_replace("\n","\r\n",str_replace("\r","",$plain));
					*/
					$output->airsyncbasebody->type = 1;
					if(isset($bodypreference[1]["TruncationSize"]) &&
			    		strlen($plainBody) > $bodypreference[1]["TruncationSize"])
					{
						$plainBody = utf8_truncate($plainBody, $bodypreference[1]["TruncationSize"]);
						$output->airsyncbasebody->truncated = 1;
					}
					$output->airsyncbasebody->data = $plainBody;
				}
				// In case we have nothing for the body, send at least a blank...
				// dw2412 but only in case the body is not rtf!
				if ($output->airsyncbasebody->type != 3 && (!isset($output->airsyncbasebody->data) || strlen($output->airsyncbasebody->data) == 0))
				{
					$output->airsyncbasebody->data = " ";
				}
				// determine estimated datasize for all the above cases ...
				$output->airsyncbasebody->estimateddatasize = strlen($output->airsyncbasebody->data);
			}
			// end AS12 Stuff
			debugLog(__METHOD__.__LINE__.' gather Header info:'.$headers['SUBJECT'].' from:'.$headers['DATE']);
			$output->read = $stat["flags"];
			$output->subject = $this->messages[$id]['subject'];
			$output->importance = ($this->messages[$id]['priority'] ?  $this->messages[$id]['priority']:1) ;
			$output->datereceived = $this->mail->_strtotime($headers['DATE'],'ts',true);
			$output->displayto = ($headers['TO'] ? $headers['TO']:null); //$stat['FETCHED_HEADER']['to_name']
			$output->to = $this->messages[$id]['to_address']; //$stat['FETCHED_HEADER']['to_name']
			$output->from = $this->messages[$id]['sender_address']; //$stat['FETCHED_HEADER']['sender_name']
			$output->cc = ($headers['CC'] ? $headers['CC']:null);
			$output->reply_to = ($headers['REPLY_TO']?$headers['REPLY_TO']:null);
			$output->messageclass = "IPM.Note";
			if (stripos($this->messages[$id]['mimetype'],'multipart')!== false &&
				stripos($this->messages[$id]['mimetype'],'signed')!== false)
			{
				$output->messageclass = "IPM.Note.SMIME.MultipartSigned";
			}
			// start AS12 Stuff
			$output->poommailflag = new SyncPoommailFlag();
			$output->poommailflag->flagstatus = 0;
			$output->internetcpid = 65001;
			$output->contentclass="urn:content-classes:message";
			// end AS12 Stuff

			// start handle Attachments
			$attachments = $this->mail->getMessageAttachments($id);
			if (is_array($attachments))
			{
				debugLog(__METHOD__.__LINE__.' gather Attachments for MessageID:'.$id.' found:'.count($attachments));
				foreach ($attachments as $key => $attach)
				{
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attach));
					if(isset($output->_mapping['POOMMAIL:Attachments'])) {
						$attachment = new SyncAttachment();
					} else if(isset($output->_mapping['AirSyncBase:Attachments'])) {
						$attachment = new SyncAirSyncBaseAttachment();
					}
					$attachment->attsize = $attach['size'];
					$attachment->displayname = $attach['name'];
					$attachment->attname = $folderid . ":" . $id . ":" . $key;
					$attachment->attmethod = 1;
					$attachment->attoid = "";//isset($part->headers['content-id']) ? trim($part->headers['content-id']) : "";
					if (!empty($attach['cid']) && $attach['cid'] <> 'NIL' ) 
					{
						$attachment->isinline=true;
						$attachment->attmethod=6;
						$attachment->contentid= $attach['cid'];
						//	debugLog("'".$part->headers['content-id']."'  ".$attachment->contentid);
						$attachment->contenttype = trim($attach['mimeType']);
						//	debugLog("'".$part->headers['content-type']."'  ".$attachment->contentid);
					} else {
						$attachment->attmethod=1;
					}

					if (isset($output->_mapping['POOMMAIL:Attachments'])) {
						array_push($output->attachments, $attachment);
					} else if(isset($output->_mapping['AirSyncBase:Attachments'])) {
						array_push($output->airsyncbaseattachments, $attachment);
					}
				}
			}

			// end handle Attachments
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($output));
			return $output;
		}
		return false;
	}

	/**
	 * StatMessage should return message stats, analogous to the folder stats (StatFolder). Entries are:
	 * 'id'	 => Server unique identifier for the message. Again, try to keep this short (under 20 chars)
	 * 'flags'	 => simply '0' for unread, '1' for read
	 * 'mod'	=> modification signature. As soon as this signature changes, the item is assumed to be completely
	 *			 changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
	 *			 time for this field, which will change as soon as the contents have changed.
	 *
	 * @param string $folderid
	 * @param int|array $id event id or array or cal_id:recur_date for virtual exception
	 * @return array
	 */
	public function StatMessage($folderid, $id)
	{
        $messages = $this->fetchMessages($folderid, NULL, (array)$id);
        $stat = array_shift($messages);
        debugLog (__METHOD__."('$folderid','$id') returning ".array2string($stat));
        return $stat;
	}

	/**
	 * This function is called when a message has been changed on the PDA. You should parse the new
	 * message here and save the changes to disk. The return value must be whatever would be returned
	 * from StatMessage() after the message has been saved. This means that both the 'flags' and the 'mod'
	 * properties of the StatMessage() item may change via ChangeMessage().
	 * Note that this function will never be called on E-mail items as you can't change e-mail items, you
	 * can only set them as 'read'.
	 */
	function ChangeMessage($folderid, $id, $message)
	{
		return false;
	}

	/**
	 * This function is called when the user moves an item on the PDA. You should do whatever is needed
	 * to move the message on disk. After this call, StatMessage() and GetMessageList() should show the items
	 * to have a new parent. This means that it will disappear from GetMessageList() will not return the item
	 * at all on the source folder, and the destination folder will show the new message
	 *
	 */
	function MoveMessage($folderid, $id, $newfolderid) {
		debugLog("IMAP-MoveMessage: (sfid: '$folderid'  id: '$id'  dfid: '$newfolderid' )");
		$this->splitID($folderid, $account, $srcFolder);
		$this->splitID($newfolderid, $account, $destFolder);
		debugLog("IMAP-MoveMessage: (SourceFolder: '$srcFolder'  id: '$id'  DestFolder: '$destFolder' )");
		if (!isset($this->mail)) $this->mail = new bofelamimail ("UTF-8",false);
		$this->mail->reopen($destFolder);
		$status = $this->mail->getFolderStatus($destFolder);
		$uidNext = $status['uidnext'];
		$this->mail->reopen($srcFolder);

		// move message
		$rv = $this->mail->moveMessages($destFolder,(array)$id,true,$srcFolder,true);
		debugLog(__METHOD__.__LINE__.array2string($rv)); // this may be true, so try using the nextUID value by examine
		// return the new id "as string""
		return ($rv===true ? $uidNext : $rv[$id]) . "";
	}

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetMessageList($folderid, $cutoffdate=NULL)
	{
		static $cutdate;
		if (!empty($cutoffdate) && $cutoffdate >0 && (empty($cutdate) || $cutoffdate != $cutdate))  $cutdate = $cutoffdate;
		debugLog (__METHOD__.' for Folder:'.$folderid.' SINCE:'.$cutdate);

		return $this->fetchMessages($folderid, $cutdate);
	}

	private function fetchMessages($folderid, $cutoffdate=NULL, $_id=NULL)
	{
		debugLog(__METHOD__.__LINE__);
		$this->_connect($this->account);
		$messagelist = array();
		if (!empty($cutoffdate)) $_filter = array('type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		if ($this->debugLevel>0) debugLog (__METHOD__.' for Folder:'.$_folderName.' Filter:'.array2string($_filter).' Ids:'.array2string($_id));
		$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=1, $_numberOfMessages=9999999, $_sort=0, $_reverse=false, $_filter, $_id);
		//debugLog(__METHOD__.__LINE__.array2string($rv_messages));
		foreach ((array)$rv_messages['header'] as $k => $vars)
		{
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' ID to process:'.$vars['uid'].' Subject:'.$vars['subject']);
			$this->messages[$vars['uid']] = $vars;
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' MailID:'.$k.'->'.array2string($vars));
			if (!empty($vars['deleted'])) continue; // cut of deleted messages
			if ($cutoffdate && $vars['date'] < $cutoffdate) continue; // message is out of range for cutoffdate, ignore it
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' ID to report:'.$vars['uid'].' Subject:'.$vars['subject']);
			$mess["mod"] = $vars['date'];
			$mess["id"] = $vars['uid'];
			// 'seen' aka 'read' is the only flag we want to know about
			$mess["flags"] = 0;
			// outlook supports additional flags, set them to 0
			$mess["olflags"] = 0;
			if($vars["seen"]) $mess["flags"] = 1;
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($mess));
			$messagelist[$vars['uid']] = $mess;
			unset($mess);
		}
		return $messagelist;
	}



	/**
	 * Get ID of parent Folder or '0' for folders in root
	 *
	 * @param int $account
	 * @param string $folder
	 * @return string
	 */
	private function getParentID($account,$folder)
	{
		$this->_connect($account);
		if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

		$fmailFolder = $this->folders[$folder];
		if (!isset($fmailFolder)) return false;

		$parent = explode($fmailFolder->delimiter,$folder);
		array_pop($parent);
		$parent = implode($fmailFolder->delimiter,$parent);

		$id = $parent ? $this->createID($account, $parent) : '0';
		if ($this->debugLevel>1) debugLog(__METHOD__."('$folder') --> parent=$parent --> $id");
		return $id;
	}

	/**
	 * Get Information about a folder
	 *
	 * @param string $id
	 * @return SyncFolder|boolean false on error
	 */
	public function GetFolder($id)
	{
		static $last_id;
		static $folderObj;
		if (isset($last_id) && $last_id === $id) return $folderObj;

		try {
			$this->splitID($id, $account, $folder);
		}
		catch(Exception $e) {
			return $folderObj=false;
		}
		$this->_connect($account);
		if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

		$fmailFolder = $this->folders[$folder];
		if (!isset($fmailFolder)) return $folderObj=false;

		$folderObj = new SyncFolder();
		$folderObj->serverid = $id;
		$folderObj->parentid = $this->getParentID($account,$folder);
		$folderObj->displayname = $fmailFolder->shortDisplayName;

		// get folder-type
		foreach($this->folders as $inbox => $fmailFolder) break;
		if ($folder == $inbox)
		{
			$folderObj->type = SYNC_FOLDER_TYPE_INBOX;
		}
		elseif($this->mail->isDraftFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_DRAFTS;
		}
		elseif($this->mail->isTrashFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_WASTEBASKET;
			$this->_wasteID = $folder;
		}
		elseif($this->mail->isSentFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_SENTMAIL;
			$this->_sentID = $folder;
		}
		else
		{
			$folderObj->type = SYNC_FOLDER_TYPE_USER_MAIL;
		}
		if ($this->debugLevel>1) debugLog(__METHOD__."($id) --> $folder --> type=$folderObj->type, parentID=$folderObj->parentid, displayname=$folderObj->displayname");
		return $folderObj;
	}

	/**
	 * Return folder stats. This means you must return an associative array with the
	 * following properties:
	 *
	 * "id" => The server ID that will be used to identify the folder. It must be unique, and not too long
	 *		 How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
	 * "parent" => The server ID of the parent of the folder. Same restrictions as 'id' apply.
	 * "mod" => This is the modification signature. It is any arbitrary string which is constant as long as
	 *		  the folder has not changed. In practice this means that 'mod' can be equal to the folder name
	 *		  as this is the only thing that ever changes in folders. (the type is normally constant)
	 *
	 * @return array with values for keys 'id', 'mod' and 'parent'
	 */
	public function StatFolder($id)
	{
		$folder = $this->GetFolder($id);

		$stat = array(
			'id'     => $id,
			'mod'    => $folder->displayname,
			'parent' => $folder->parentid,
		);

		return $stat;
	}


	/**
	 * Return a changes array
	 *
	 * if changes occurr default diff engine computes the actual changes
	 *
	 * @param string $folderid
	 * @param string &$syncstate on call old syncstate, on return new syncstate
	 * @return array|boolean false if $folderid not found, array() if no changes or array(array("type" => "fakeChange"))
	 */
	function AlterPingChanges($folderid, &$syncstate)
	{
		debugLog(__METHOD__.' called with '.$folderid);
		$this->splitID($folderid, $account, $folder);
		if (is_numeric($account)) $type = 'felamimail';
		if ($type != 'felamimail') return false;

		if (!isset($this->mail)) $this->mail = new bofelamimail ("UTF-8",false);

		$changes = array();
        debugLog("AlterPingChanges on $folderid ($folder) stat: ". $syncstate);
        $this->mail->reopen($folder);

        $status = $this->mail->getFolderStatus($folder);
        if (!$status) {
            debugLog("AlterPingChanges: could not stat folder $folder ");
            return false;
        } else {
            $newstate = "M:". $status['messages'] ."-R:". $status['recent'] ."-U:". $status['unseen']."-NUID:".$status['uidnext']."-UIDV:".$status['uidvalidity'];

            // message number is different - change occured
            if ($syncstate != $newstate) {
                $syncstate = $newstate;
                debugLog("AlterPingChanges: Change FOUND!");
                // build a dummy change
                $changes = array(array("type" => "fakeChange"));
            }
        }
		//error_log(__METHOD__."('$folderid','$syncstate_was') syncstate='$syncstate' returning ".array2string($changes));
		return $changes;
	}

	/**
	 * Should return a wastebasket folder if there is one. This is used when deleting
	 * items; if this function returns a valid folder ID, then all deletes are handled
	 * as moves and are sent to your backend as a move. If it returns FALSE, then deletes
	 * are always handled as real deletes and will be sent to your importer as a DELETE
	 */
	function GetWasteBasket() {
		return $this->_wasteID;
	}

	/**
	 * This function is called when the user has requested to delete (really delete) a message. Usually
	 * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
	 * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the PDA
	 * as it will be seen as a 'new' item. This means that if you don't implement this function, you will
	 * be able to delete messages on the PDA, but as soon as you sync, you'll get the item back
	 */
	function DeleteMessage($folderid, $id)
	{
		debugLog("IMAP-DeleteMessage: (fid: '$folderid'  id: '$id' )");
		/*
		$this->imap_reopenFolder($folderid);
		$s1 = @imap_delete ($this->_mbox, $id, FT_UID);
		$s11 = @imap_setflag_full($this->_mbox, $id, "\\Deleted", FT_UID);
		$s2 = @imap_expunge($this->_mbox);
		*/
		// we may have to split folderid
		$this->splitID($folderid, $account, $folder);
		debugLog(__METHOD__.__LINE__.' '.$folderid.'->'.$folder);
		$_messageUID = (array)$id;

		if (!isset($this->mail)) $this->mail = new bofelamimail ("UTF-8",false);
		$rv = $this->mail->deleteMessages($_messageUID, $folder);
		// this may be a bit rude, it may be sufficient that GetMessageList does not list messages flagged as deleted
		if ($this->mail->mailPreferences->preferences['deleteOptions'] == 'mark_as_deleted')
		{
			// ignore mark as deleted -> Expunge!
			//$this->mail->icServer->expunge(); // do not expunge as GetMessageList does not List messages flagged as deleted
		}
		debugLog("IMAP-DeleteMessage: $rv");

		return $rv;
	}

	/**
	 * This should change the 'read' flag of a message on disk. The $flags
	 * parameter can only be '1' (read) or '0' (unread). After a call to
	 * SetReadFlag(), GetMessageList() should return the message with the
	 * new 'flags' but should not modify the 'mod' parameter. If you do
	 * change 'mod', simply setting the message to 'read' on the PDA will trigger
	 * a full resync of the item from the server
	 */
	function SetReadFlag($folderid, $id, $flags)
	{
		debugLog("IMAP-SetReadFlag: (fid: '$folderid'  id: '$id'  flags: '$flags' )");
		$_messageUID = (array)$id;
		if (!isset($this->mail)) $this->mail = new bofelamimail ("UTF-8",false);
		$rv = $this->mail->flagMessages((($flags) ? "read" : "unread"), $_messageUID,$_folderid);
		debugLog("IMAP-SetReadFlag -> set as " . (($flags) ? "read" : "unread") . "-->". $rv);

		return $rv;
	}


	/**
	 * Create a max. 32 hex letter ID, current 20 chars are used
	 *
	 * @param int $account mail account id
	 * @param string $folder
	 * @param int $id=0
	 * @return string
	 * @throws egw_exception_wrong_parameter
	 */
	private function createID($account,$folder,$id=0)
	{
		if (!is_numeric($folder))
		{
			// convert string $folder in numeric id
			$folder = $this->folder2hash($account,$f=$folder);
		}

		$str = $this->backend->createID($account, $folder, $id);

		if ($this->debugLevel>1) debugLog(__METHOD__."($account,'$f',$id) type=$account, folder=$folder --> '$str'");

		return $str;
	}

	/**
	 * Split an ID string into $app, $folder and $id
	 *
	 * @param string $str
	 * @param int &$account mail account id
	 * @param string &$folder
	 * @param int &$id=null
	 * @throws egw_exception_wrong_parameter
	 */
	private function splitID($str,&$account,&$folder,&$id=null)
	{
		$this->backend->splitID($str, $account, $folder, $id=null);

		// convert numeric folder-id back to folder name
		$folder = $this->hash2folder($account,$f=$folder);

		if ($this->debugLevel>1) debugLog(__METHOD__."('$str','$account','$folder',$id)");
	}

	/**
	 * Methods to convert (hierarchical) folder names to nummerical id's
	 *
	 * This is currently done by storing a serialized array in the device specific
	 * state directory.
	 */

	/**
	 * Convert folder string to nummeric hash
	 *
	 * @param int $account
	 * @param string $folder
	 * @return int
	 */
	private function folder2hash($account,$folder)
	{
		if(!isset($this->folderHashes)) $this->readFolderHashes();

		if (($index = array_search($folder, (array)$this->folderHashes[$account])) === false)
		{
			// new hash
			$this->folderHashes[$account][] = $folder;
			$index = array_search($folder, (array)$this->folderHashes[$account]);

			// maybe later storing in on class destruction only
			$this->storeFolderHashes();
		}
		return $index;
	}

	/**
	 * Convert numeric hash to folder string
	 *
	 * @param int $account
	 * @param int $index
	 * @return string NULL if not used so far
	 */
	private function hash2folder($account,$index)
	{
		if(!isset($this->folderHashes)) $this->readFolderHashes();

		return $this->folderHashes[$account][$index];
	}

	private $folderHashes;

	/**
	 * Read hashfile from state dir
	 */
	private function readFolderHashes()
	{
		if (file_exists($file = $this->hashFile()) &&
			($hashes = file_get_contents($file)))
		{
			$this->folderHashes = unserialize($hashes);
		}
		else
		{
			$this->folderHashes = array();
		}
	}

	/**
	 * Store hashfile in state dir
	 *
	 * return int|boolean false on error
	 */
	private function storeFolderHashes()
	{
		return file_put_contents($this->hashFile(), serialize($this->folderHashes));
	}

	/**
	 * Get name of hashfile in state dir
	 *
	 * @throws egw_exception_assertion_failed
	 */
	private function hashFile()
	{
		if (!isset($this->backend->_devid))
		{
			throw new egw_exception_assertion_failed(__METHOD__."() called without this->_devid set!");
		}
		return STATE_DIR.'/'.strtolower($this->backend->_devid).'/'.$this->backend->_devid.'.hashes';
	}
}
