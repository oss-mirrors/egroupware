<?php
/**
 * eGroupWare Tracker - Handle incoming mails
 *
 * This class handles incoming mails in the async services.
 * It is an addition for the eGW Tracker app by Ralf Becker
 *
 * @link http://www.egroupware.org
 * @author Oscar van Eijk <oscar.van.eijk-AT-oveas.com>
 * @package tracker
 * @copyright (c) 2008 by Oscar van Eijk <oscar.van.eijk-AT-oveas.com>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

class tracker_mailhandler extends tracker_bo
{
	/**
	 * UID of the mailsender, 0 if not recognized
	 *
	 * @var int
	 */
	var $mailSender;

	/**
	 * Subject line of the incoming mail
	 *
	 * @var string
	 */
	var $mailSubject;

	/**
	 * Text from the mailbody (1st part)
	 *
	 * @var string
	 */
	var $mailBody;

	/**
	 * Identification of the mailbox
	 *
	 * @var string
	 */
	var $mailBox;

	/**
	 * List with all messages retrieved from the server
	 *
	 * @var array
	 */
	var $msgList = array();

	/**
	 * Mailbox stream
	 *
	 * @var int
	 */
	var $mbox;

	/**
	 * smtpObject for autoreplies and worwarding
	 *
	 * @var send object
	 */
	var $smtpMail;

	/**
	 * Ticket ID or 0 if not recognize
	 *
	 * @var int
	 */
	var $ticketId;

	/**
	 * User ID currently executing. Used in case we execute in fallback
	 *
	 * @var int
	 */
	var $originalUser;

	/**
	 * Supported mailservertypes, extracted from parent::mailservertypes
	 *
	 * @var array
	 */
	var $serverTypes = array();

	/**
	 * How much should be logged to the apache error-log
	 *
	 * 0 = Nothing
	 * 1 = only errors
	 * 2 = more debug info
	 * 3 = complete debug info
	 */
	const LOG_LEVEL = 0;

	/**
	 * Constructor
	 * @param array mailhandlingConfig - optional mailhandling config, to overrule the config loaded by parent
	 */
	function __construct($mailhandlingConfig=null)
	{
		parent::__construct();
		if (!is_null($mailhandlingConfig)) $this->mailhandling = $mailhandlingConfig;
		// In case we run in fallback, make sure the original user gets restored
		$this->originalUser = $this->user;
		foreach($this->mailservertypes as $ind => $typ)
		{
			$this->serverTypes[] = $typ[0];
		}
		if (($this->mailBox = self::get_mailbox(0)) === false)
		{
			return false;
		}
	}

	/**
	 * Destructor, close the stream if not done before.
	 */
	function __destruct()
	{
		if($this->mbox)
		{
			@imap_close($this->mbox);
		}
	}

	/**
	 * Compare 2 given mailbox settings (for a given set of properties)
	 * @param defaultImap Object $reference reference
	 * @param defaultImap Object $profile compare to reference
	 * @return mixed false/array with the differences found; empty array when no differences for the predefined set of keys are found; false if either one is not of type defaultImap
	 */
	static function compareMailboxSettings($reference, $profile)
	{
		$diff = array();
		if (!($reference instanceof defaultimap)) return false;
		if (!($profile instanceof defaultimap)) return false;
		if ($reference->ImapServerId != $profile->ImapServerId) $diff['ImapServerId']=array('reference'=>$reference->ImapServerId,'profile'=>$profile->ImapServerId);
		if ($reference->encryption != $profile->encryption) $diff['encryption']=array('reference'=>$reference->encryption,'profile'=>$profile->encryption);
		if ($reference->host != $profile->host) $diff['host']=array('reference'=>$reference->host,'profile'=>$profile->host);
		if ($reference->port != $profile->port) $diff['port']=array('reference'=>$reference->port,'profile'=>$profile->port);
		if ($reference->validatecert != $profile->validatecert) $diff['validatecert']=array('reference'=>$reference->validatecert,'profile'=>$profile->validatecert);
		if ($reference->username != $profile->username) $diff['username']=array('reference'=>$reference->username,'profile'=>$profile->username);
		if ($reference->loginName != $profile->loginName) $diff['loginName']=array('reference'=>$reference->loginName,'profile'=>$profile->loginName);
		if ($reference->password != $profile->password) $diff['password']=array('reference'=>$reference->password,'profile'=>$profile->password);
		return $diff;
	}

	/**
	 * Compose the mailbox identification
	 *
	 * @return string mailbox identification as '{server[:port]/type}folder'
	 */
	function get_mailbox($queue = 0)
	{
		if (empty($this->mailhandling[$queue]['server']))
		{
			return false; // Or should we default to 'localhost'?
		}
		if ($this->mailhandling[$queue]['servertype']<=2)
		{
			$icServer = new emailadmin_oldimap();
			$icServer->ImapServerId	= 'tracker_'.trim($queue);
			$icServer->encryption	= ($this->mailhandling[$queue]['servertype']==2?3:($this->mailhandling[$queue]['servertype']==1?2:0));
			$icServer->host		= $this->mailhandling[$queue]['server'];
			$icServer->port 	= $this->mailhandling[$queue]['serverport'];
			$icServer->validatecert	= $this->mailhandling[$queue]['servertype']==2?true:false;
			if ($icServer->validatecert)
			{
				$vCO=egw_cache::getCache(egw_cache::INSTANCE,'email','emailValidateCertOverrule_'.trim($icServer->ImapServerId));
				if ($vCO) $icServer->validatecert=false;
			}
			$icServer->username 	= $this->mailhandling[$queue]['username'];
			$icServer->loginName 	= $this->mailhandling[$queue]['username'];
			$icServer->password	= $this->mailhandling[$queue]['password'];
			$icServer->enableSieve	= false;
			return $icServer;
		}
		$mBox = '{'.$this->mailhandling[$queue]['server'];	// Set the servername

		if(!empty($this->mailhandling[$queue]['serverport']))
		{
			// If set, add the portnumber
			$mBox .= (':'.$this->mailhandling[$queue]['serverport']);
		}
		// Add the Servertype
		$mBox .= ('/'.$this->serverTypes[($this->mailhandling[$queue]['servertype'])]);
		$mBox .= '/norsh'; // do not use rsh or ssh to establish connection
		// Close the server ID
		$mBox .= '}';

		// Add the default incoming folder or the one specified
		if(empty($this->mailhandling[$queue]['folder']))
		{
			$mBox .= 'INBOX';
		}
		else
		{
			$mBox .= $this->mailhandling[$queue]['folder'];
		}
		return $mBox;
	}

	/**
	 * Get all mails from the server. Invoked by the async timer
	 *
	 * @param int Which tracker queue to check mail for
	 * @param boolean TestConnection=false
	 * @return boolean true=run finished, false=an error occured
	 */
	function check_mail($queue = 0, $TestConnection=false) {
		// Config for all passes null
		if(!$queue) {
			$queue = 0;
		} else {
			// Mailbox for all is pre-loaded, for others we have to change it
			$this->mailBox = self::get_mailbox($queue);
		}
		if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__." for $queue");
		if ($this->mailBox === false)
		{
			if ($TestConnection) throw new egw_exception_wrong_userinput(lang("incomplete server profile for mailhandling provided; Disabling mailhandling for Queue %1", $queue));
			error_log(__METHOD__.','.__LINE__.lang("incomplete server profile for mailhandling provided; Disabling mailhandling for Queue %1", $queue));
			$this->mailhandling[$queue]['interval']=0;
			$this->save_config();
			return false;
		}
		if ($this->mailBox instanceof defaultimap)
		{
			if (/*$this->mailhandling[$queue]['auto_reply'] ||*/ $this->mailhandling[$queue]['autoreplies'] || $this->mailhandling[$queue]['unrecognized_mails'])
			{
				if(is_object($this->smtpMail))
				{
					unset($this->smtpMail);
				}
				$this->smtpMail = new send('notification');
				if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.array2string($this->smtpMail));
			}
			$rFP=egw_cache::getCache(egw_cache::INSTANCE,'email','rememberFailedProfile_'.trim($this->mailBox->ImapServerId));
			if ($rFP && !empty($rFP))
			{
				$d = self::compareMailboxSettings($this->mailBox,$rFP);
				if ($d===false || empty($d))
				{
					if ($TestConnection==false)
					{
						error_log(__METHOD__.','.__LINE__." ".lang("eGroupWare Tracker Mailhandling: could not connect previously, and profile did not change"));
						error_log(__METHOD__.','.__LINE__." ".lang("refused to open mailbox: %1",array2string($this->mailBox)));
						$previousInterval = $this->mailhandling[$queue]['interval'];
						$this->mailhandling[$queue]['interval']=$this->mailhandling[$queue]['interval']*2;
						$this->save_config();
						egw_cache::setCache(egw_cache::INSTANCE,'email','rememberFailedProfile_'.trim($this->mailBox->ImapServerId),array(),$expiration=60*10);
						if ($GLOBALS['egw_info']['server']['admin_mails'])
						{
							// notify admin(s) via email
							$from    = 'eGroupWareTrackerMailHandling@'.$GLOBALS['egw_info']['server']['mail_suffix'];
							$subject = lang("eGroupWare Tracker Mailhandling: could not connect previously, and profile did not change");
							$body    = lang("refused to open mailbox therefore changed Interval from %1 to %2",$previousInterval,$this->mailhandling[$queue]['interval']);
							$body    .= "\n";
							$body    .= lang("Mailbox settings used: %1",array2string($this->mailBox));

							$admin_mails = explode(',',$GLOBALS['egw_info']['server']['admin_mails']);
							foreach($admin_mails as $to)
							{
								try {
										$GLOBALS['egw']->send->msg('email',$to,$subject,$body,'','','',$from,$from);
								}
								catch(Exception $e) {
									// ignore exception, but log it, to block the account and give a correct error-message to user
									error_log(__METHOD__."('$to') ".$e->getMessage());
								}
							}
						}
						return false;
					}
				}
			}
			$mailClass = 'felamimail_bo';
			$mailobject	= $mailClass::getInstance(false,$this->mailBox->ImapServerId,false,$this->mailBox);
			if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.'#'.array2string($this->mailBox));
			
			$connectionFailed = false;
			// connect
			$tretval = $mailobject->openConnection($this->mailBox->ImapServerId);
			// may fail for validatecert=true
			if ( (PEAR::isError($tretval) || $tretval===false) && $mailobject->icServer->validatecert)
			{
				$mailobject->icServer->validatecert=false;
				if (is_object($mailobject->icServer->_connectionErrorObject)) $mailobject->icServer->_connectionErrorObject->message = ',';
				$mailobject->icServer->_connected = false;
				// try again
				$tretval = $mailobject->openConnection($this->mailBox->ImapServerId);
				if (!(PEAR::isError($tretval) || $tretval===false))
				{
					egw_cache::setCache(egw_cache::INSTANCE,'email','emailValidateCertOverrule_'.trim($this->mailBox->ImapServerId),true,$expiration=60*60*10);
				}
				else
				{
					// connection failed - remember that
					$connectionFailed = true;
				}
			}
			else
			{
				// connection failed - remember that
				if (PEAR::isError($tretval) || $tretval===false) $connectionFailed = true;
			}
			if ($TestConnection===true)
			{
				if (self::LOG_LEVEL>0) error_log(__METHOD__.','.__LINE__." failed to open mailbox:".array2string($mailobject->icServer));
				if ($connectionFailed) throw new egw_exception_wrong_userinput(lang("failed to open mailbox: %1 -> disabled for automatic mailprocessing!",(PEAR::isError($tretval)?$tretval->message:lang('could not connect'))));
				return true;//everythig all right
			}
			if ($connectionFailed)
			{
				egw_cache::setCache(egw_cache::INSTANCE,'email','rememberFailedProfile_'.trim($this->mailBox->ImapServerId),$this->mailBox,$expiration=60*60*5);
				if (self::LOG_LEVEL>0) error_log(__METHOD__.','.__LINE__." failed to open mailbox:".array2string($this->mailBox));
				return false;
			}
			else
			{
				egw_cache::setCache(egw_cache::INSTANCE,'email','rememberFailedProfile_'.trim($this->mailBox->ImapServerId),array(),$expiration=60*10);
			}
			// load lang stuff for mailheaderInfoSection creation
			translation::add_app('felamimail');
			// retrieve list
			if (self::LOG_LEVEL>0 && (PEAR::isError($tretval) || $tretval===false)) error_log(__METHOD__.__LINE__.'#'.array2string($tretval).$mailobject->errorMessage);
			$_folderName = (!empty($this->mailhandling[$queue]['folder'])?$this->mailhandling[$queue]['folder']:'INBOX');
			$mailobject->reopen($_folderName);
			if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__." Processing mailbox {$_folderName} with ServerID:".$mailobject->icServer->ImapServerId." for queue $queue\n".array2string($mailobject->icServer));
			$_filter=array('status'=>array('UNSEEN','UNDELETED'));
			if (!empty($this->mailhandling[$queue]['address']))
			{
				$_filter['type']='TO';
				$_filter['string']=trim($this->mailhandling[$queue]['address']);
			}
			$sortResult = $mailobject->getSortedList($_folderName, $_sort=0, $_reverse=1, $_filter,$byUid=true,false);
			if (self::LOG_LEVEL>1 && $sortResult) error_log(__METHOD__.__LINE__.'#'.array2string($sortResult));
			$deletedCounter = 0;
			$mailobject->icServer->selectMailbox($_folderName);
			foreach ((array)$sortResult as $i => $uid)
			{
				if (empty($uid)) continue;
				if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__.'# fetching Data for:'.array2string(array('uid'=>$uid,'folder'=>$_folderName)).' Mode:'.$this->htmledit.' SaveAsOption:'.$GLOBALS['egw_info']['user']['preferences']['felamimail']['saveAsOptions']);
				if ($uid)
				{
					$this->user = $this->originalUser;
					$htmlEditOrg = $this->htmledit; // preserve that, as an existing ticket may be of a different mode
					if (self::process_message2($mailobject, $uid, $_folderName, $queue) && $this->mailhandling[$queue]['delete_from_server'])
					{
						$mailobject->deleteMessages($uid, $_folderName, 'move_to_trash');
						$deletedCounter++;
					}
					$this->htmledit = $htmlEditOrg;
				}
			}
			// Expunge deleted mails, if any
			if ($deletedCounter) // NOTE THERE MAY BE DELETED MESSAGES AFTER THE PROCESSING
			{
				$mailobject->icServer->selectMailbox($_folderName);
				$rv = $mailobject->icServer->expunge();
				if (self::LOG_LEVEL && PEAR::isError($rv)) error_log(__METHOD__." failed to expunge Message(s) from Folder: ".$_folderName.' due to:'.$rv->message);
			}

			// Close the connection
			//$mailobject->closeConnection(); // not sure we should do that, as this seems to kill more then our connection

			$this->user = $this->originalUser;
			return true;
		}
		if (self::LOG_LEVEL>1) error_log(__METHOD__." Processing mailbox {$this->mailBox} for queue $queue\n");
		if (!($this->mbox = @imap_open($this->mailBox,
									$this->mailhandling[$queue]['username'],
									$this->mailhandling[$queue]['password'])))
		{
			$show_failed = true;
			// try novalidate cert, in case of ssl connection
			if ($this->mailhandling[$queue]['servertype']==2)
			{
				$this->mailBox = str_replace('/ssl','/ssl/novalidate-cert',$this->mailBox);
				if (($this->mbox = imap_open($this->mailBox,$this->mailhandling[$queue]['username'],$this->mailhandling[$queue]['password']))) $show_failed=false;
			}
			if ($show_failed)
			{
				error_log(__METHOD__.__LINE__." failed to open mailbox:".print_r($this->mailBox,true));
				return false;
			}
		}

		// There seems to be a bug in imap_seach() (#48619) that causes a SegFault if all msg match
		// This was introduced in v5.2.10 and fixed in v5.2.11, so use a workaround in 5.2.10
		//
		if (empty($this->mailhandling[$queue]['address']) || (version_compare(PHP_VERSION, '5.2.10') === 0))
		{
			// Use sort here to ensure the format returned equals search
			$this->msgList = imap_sort ($this->mbox, SORTARRIVAL, 1);
		}
		else
		{
			$this->msgList = imap_search ($this->mbox, 'TO "' . $this->mailhandling[$queue]['address'] . '"');
		}

		if ($this->msgList)
		{
			$_cnt = count ($this->msgList);
			for ($_idx = 0; $_idx < $_cnt; $_idx++)
			{
				//error_log(__METHOD__.':About to process Message with ID:'.$_idx.' -> '.array2string($this->msgList[$_idx]));
				if ($this->msgList[$_idx])
				if (self::process_message($this->msgList[$_idx], $queue) && $this->mailhandling[$queue]['delete_from_server'])
				{
					@imap_delete($this->mbox, $this->msgList[$_idx]);
				}
			}
		}
		// Expunge delete mails, if any
		@imap_expunge($this->mbox);

		// Close the stream
		@imap_close($this->mbox);

		// Restore original user (for fallback)
		$this->user = $this->originalUser;

		return true;
	}

	/**
	 * determines the mime type of a eMail in accordance to the imap_fetchstructure
	 * found at http://www.linuxscope.net/articles/mailAttachmentsPHP.html
	 * by Kevin Steffer
	 */
	function get_mime_type(&$structure)
	{
		$primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
		if($structure->subtype) {
			return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
		}
		return "TEXT/PLAIN";
	}

	function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false)
	{
		//error_log(__METHOD__." getting body for ID: $msg_number, $mime_type, $part_number");
		if(!$structure) {
			//error_log(__METHOD__." fetching structure, as no structure passed.");
			$structure = imap_fetchstructure($stream, $msg_number);
		}
		if($structure)
		{
			if($mime_type == $this->get_mime_type($structure))
			{
				if(!$part_number)
				{
					$part_number = "1";
				}
				//error_log(__METHOD__." mime type matched. Part $part_number.");
				$struct = imap_bodystruct ($stream, $msg_number, "$part_number");
				$body = imap_fetchbody($stream, $msg_number, $part_number);
				return array('struct'=> $struct,
							 'body'=>$body,
							);
			}

			if($structure->type == 1) /* multipart */
			{
				while(list($index, $sub_structure) = each($structure->parts))
				{
					if($part_number)
					{
						$prefix = $part_number . '.';
					}
					$data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure,$prefix.($index + 1));
					if($data && !empty($data['body']))
					{
						return $data;
					}
				} // END OF WHILE
			} // END OF MULTIPART
		} // END OF STRUTURE
		return false;
	} // END OF FUNCTION

	/**
	 * Retrieve and decode a bodypart
	 *
	 * @param int Message ID from the server
	 * @param string The body part, defaults to "1"
	 * @return string The decoded bodypart
	 */
	function get_mailbody ($mid, $section=false, $structure = false)
	{
		$nonDisplayAbleCharacters = array('[\016]','[\017]',
			'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
			'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');

		//error_log(__METHOD__." Fetching body for ID $mid, Section $section with Structure: ".print_r($structure,true));
		$charset = $GLOBALS['egw']->translation->charset(); // set some default charset, for translation to use
		$mailbodyasAttachment = false;
		if(function_exists(mb_decode_mimeheader)) {
			mb_internal_encoding($charset);
		}
		if ($section === false)
		{
			$part_number = 1;
		}
		else
		{
			$part_number = $section;
			$mailbodyasAttachment = true;
		}
		if ($structure === false) $structure = imap_fetchstructure($this->mbox, $mid);
		if($structure) {
			$mimeType = 'TEXT/PLAIN';
			$rv = $this->get_part($this->mbox, $mid, $mimeType, $structure,($section ? $part_number:false));
			$struct = $rv['struct'];
			$body = $rv['body'];
			if (empty($body))
			{
				$mimeType = 'TEXT/HTML';
				$rv = $this->get_part($this->mbox, $mid, $mimeType, $structure,($section ? $part_number:false));
				$struct = $rv['struct'];
				$body = $rv['body'];
			}
			//error_log(__METHOD__. "->get_part returned: ".print_r($rv,true));
			/*
			error_log($this->get_part($this->mbox, $mid, 'TEXT/HTML', $structure,2));
			error_log($this->get_part($this->mbox, $mid, 'TEXT/HTML', $structure,"2.1"));
			error_log($this->get_part($this->mbox, $mid, 'TEXT/HTML', $structure,3));
			error_log($this->get_part($this->mbox, $mid, 'TEXT/HTML', $structure,"3.1"));
			*/
			if (self::LOG_LEVEL) error_log(__METHOD__.'Structure:'.print_r($structure,true));
			if (self::LOG_LEVEL>1) error_log(__METHOD__.'Struct:'.print_r($struct,true));
			if (self::LOG_LEVEL>2) error_log(__METHOD__.'Body:'.print_r($body,true));
			if (isset($struct->ifparameters) && $struct->ifparameters == 1)
			{
				//error_log(__METHOD__.__LINE__.print_r($param,true));
				while(list($index, $param) = each($struct->parameters))
				{
					if (strtoupper($param->attribute) == 'CHARSET') $charset = $param->value;
				}
			}
			switch ($struct->encoding)
			{
				case 0: // 7 BIT
					//dont try to decode, as we do use convert anyway later on
					//$body = imap_utf7_decode($body);
					break;
				case 1: // 8 BIT
					if ($struct->subtype == 'PLAIN' && strtolower($charset) != 'iso-8859-1') {
						// only decode if we are at utf-8, not sure that we should decode at all, since we use convert anyway
						//$body = utf8_decode ($body);
					}
					break;
				case 2: // Binary
					$body = imap_binary($body);
					break;
				case 3: //BASE64
					$body = imap_base64($body);
					break;
				case 4: // QUOTED Printable
					$body = quoted_printable_decode($body);
					break;
				case 5: // other
				default:
					break;
			}
			$GLOBALS['egw']->translation->convert($body,$charset);
			if ($mimeType=='TEXT/PLAIN')
			{
				$newBody    = @htmlentities($body,ENT_QUOTES, strtoupper($charset));
				// if empty and charset is utf8 try sanitizing the string in question
				if (empty($newBody) && strtolower($charset)=='utf-8') $newBody = @htmlentities(iconv('utf-8', 'utf-8', $body),ENT_QUOTES, strtoupper($charset));
				// if the conversion to htmlentities fails somehow, try without specifying the charset, which defaults to iso-
				if (empty($newBody)) $newBody    = htmlentities($body,ENT_QUOTES);
				$body = $newBody;
			}
			$body = preg_replace($nonDisplayAbleCharacters,'',$body);

			// handle Attachments
			$contentParts = count($structure->parts);
			$additionalAttachments = array();
			$attachments = array();
			if($structure->type == 1 && $contentParts >=2) /* multipart */
			{
				$att = array();
				$partNumber = array();
				for ($i=2;$i<=$contentParts;$i++)
				{
					//error_log(__METHOD__. " --> part ".$i);
					$att[$i-2] = imap_bodystruct($this->mbox,$mid,$i);
					$partNumber[$i-2] = array('number' => $i,
											'substruct' => $structure->parts[$i-1],
										);
				}
				for ($k=0; $k<sizeof($att);$k++)
				{
					//error_log(__METHOD__. " processing part->".$k." Message Part:".print_r($partNumber[$k],true));
					if ($att[$k]->ifdisposition == 1 && strtoupper($att[$k]->disposition) == 'ATTACHMENT')
					{
						//$num = count($attachments) - 1;
						$num = $k;
						if ($num < 0) $num = 0;
						$attachments[$num]['type'] = $this->get_mime_type($att[$k]);
						//error_log(__METHOD__. " part:".print_r($att[$k],true));
						// type2 = Message; get mail as attachment, with its attachments too
						if ($att[$k]->type == 2)
						{
							//error_log(__METHOD__. " part $k ->".($section ? $part_number.".".$partNumber[$k]['number']:$partNumber[$k]['number'])." is MESSAGE:".print_r($partNumber[$k]['substruct']->parts[0],true));
							$rv = $this->get_mailbody($mid,($section ? $part_number.".".$partNumber[$k]['number']:$partNumber[$k]['number']) , $partNumber[$k]['substruct']->parts[0]);
							$attachments[$num]['attachment'] = $rv['body'];
							$attachments[$num]['type'] = $this->get_mime_type($rv['struct']);
							if ($att[$k]->ifparameters)
							{
								//error_log(__METHOD__. " parameters exist:");
								while(list($index, $param) = each($att[$k]->parameters))
								{
									//error_log(__METHOD__.__LINE__.print_r($param,true));
									if (strtoupper($param->attribute) == 'NAME') $attachments[$num]['name'] = $param->value;
								}
							}
							if ($att[$k]->ifdparameters)
							{
								//error_log(__METHOD__. " dparameters exist:");
								while(list($index, $param) = each($att[$k]->dparameters))
								{
									//error_log(__METHOD__.__LINE__.print_r($param,true));
									if (strtoupper($param->attribute) == 'FILENAME') $attachments[$num]['filename'] = $param->value;
								}
							}
							$att[$k] = $rv['struct'];
							if (!empty($rv['attachments'])) for ($a=0; $a<sizeof($rv['attachments']);$a++) $additionalAttachments[] = $rv['attachments'][$a];
							if (empty($attachments[$num]['attachment']) && empty($rv['attachments']))
							{
								unset($attachments[$num]);
								continue;  // no content -> skip
							}
						}
						else
						{
							$attachments[$num]['attachment'] = imap_fetchbody($this->mbox,$mid,$k+2);
							if (empty($attachments[$num]['attachment']))
							{
								unset($attachments[$num]);
								continue; // no content -> skip
							}
							if ($att[$k]->ifparameters)
							{
								//error_log(__METHOD__. " parameters exist:");
								while(list($index, $param) = each($att[$k]->parameters))
								{
									//error_log(__METHOD__.__LINE__.print_r($param,true));
									if (strtoupper($param->attribute) == 'CHARSET') $attachments[$num]['charset'] = $param->value;
									if (strtoupper($param->attribute) == 'NAME') $attachments[$num]['name'] = $param->value;
								}
							}
							if ($att[$k]->ifdparameters)
							{
								//error_log(__METHOD__. " dparameters exist:");
								while(list($index, $param) = each($att[$k]->dparameters))
								{
									//error_log(__METHOD__.__LINE__.print_r($param,true));
									if (strtoupper($param->attribute) == 'FILENAME') $attachments[$num]['filename'] = $param->value;
								}
							}
						}
						$this->decode_header($attachments[$num]['filename']);
						$this->decode_header($attachments[$num]['name']);
						if (empty($attachments[$num]['name'])) $attachments[$num]['name'] = $attachments[$num]['filename'];
						if (empty($attachments[$num]['name']))
						{
							$attachments[$num]['name'] = 'noname_'.$num;
							$st = '';
							if (strpos($attachments[$num]['type'],'/')!==false) list($t,$st) = explode('/',$attachments[$num]['type'],2);
							if (!empty($st)) $attachments[$num]['name'] = $attachments[$num]['name'].'.'.$st;
						}
						$attachments[$num]['tmp_name'] = tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
						$tmpfile = fopen($attachments[$num]['tmp_name'],'w');
						fwrite($tmpfile,((substr(strtolower($attachments[$num]['type']),0,4) == "text") ? $attachments[$num]['attachment']: imap_base64($attachments[$num]['attachment'])));
						fclose($tmpfile);
						unset($attachments[$num]['attachment']);
						//error_log(__METHOD__.print_r($attachments[$num],true));
					}
				}
			}
		}
		//if (!empty($attachments)) error_log(__METHOD__." Attachments with this mail:".print_r($attachments,true));
		if (!empty($additionalAttachments))
		{
			//error_log(__METHOD__." Attachments retrieved with attachments:".print_r($additionalAttachments,true));
			for ($a=0; $a<sizeof($additionalAttachments);$a++) $attachments[] = $additionalAttachments[$a];
		}
		return array('body' => $GLOBALS['egw']->translation->convertHTMLToText(nl2br(html::purify($body))),
					 'struct' => $struct,
					 'attachments' =>  $attachments
					);
	}

	/**
	 * Check if this is an automated message (bounce, autoreply...)
	 * @TODO This is currently a very basic implementation, the intention is to implement more checks,
	 * eg, filter failing addresses and remove them from CC.
	 *
	 * @param int $mid Message ID
	 * @param array $msgHeader IMap header
	 * @return boolean
	 */
	function is_automail($mid, $msgHeader)
	{
		// This array can be filled with checks that should be made.
		// 'bounces' and 'autoreplies' (level 1) are the keys coded below, the level 2 arrays
		// must match $msgHeader properties.
		//
		$autoMails = array(
			 'bounces' => array(
				 'subject' => array(
				)
				,'fromaddress' => array(
					 'mailer-daemon'
				)
			)
			,'autoreplies' => array(
				 'subject' => array(
					 'out of the office'
					,'autoreply'
					)
				,'fromaddress' => array(
				)
			)
		);

		// Check for bounced messages
		foreach ($autoMails['bounces'] as $_k => $_v) {
			if (count($_v) == 0) {
				continue;
			}
			$_re = '/(' . implode('|', $_v) . ')/i';
			if (preg_match($_re, $msgHeader->$_k)) {
				switch ($this->mailhandling[0]['bounces']) {
					case 'delete' :		// Delete, whatever the overall delete setting is
						@imap_delete($this->mbox, $mid);
						break;
					case 'forward' :	// Return the status of the forward attempt
						$returnVal = self::forward_message($mid, $msgHeader);
						if ($returnVal) $status = $this->flagMessageAsSeen($mid, $msgHeader);
					default :			// default: 'ignore'
						break;
				}
				return true;
			}
		}

		// Check for autoreplies
		foreach ($autoMails['autoreplies'] as $_k => $_v) {
			if (count($_v) == 0) {
				continue;
			}
			$_re = '/(' . implode('|', $_v) . ')/i';
			if (preg_match($_re, $msgHeader->$_k)) {
				switch ($this->mailhandling[0]['autoreplies']) {
					case 'delete' :		// Delete, whatever the overall delete setting is
						@imap_delete($this->mbox, $mid);
						break;
					case 'forward' :	// Return the status of the forward attempt
						$returnVal = self::forward_message($mid, $msgHeader);
						if ($returnVal) $status = $this->flagMessageAsSeen($mid, $msgHeader);
						break;
					case 'process' :	// Process normally...
						return false;	// ...so act as if it's no automail
						break;
					default :			// default: 'ignore'
						break;
				}
				return true;
			}
		}
	}

	/**
	 * Check if this is an automated message (bounce, autoreply...)
	 * @TODO This is currently a very basic implementation, the intention is to implement more checks,
	 * eg, filter failing addresses and remove them from CC.
	 *
	 * @param object mailobject holding the server, and its connection
	 * @param int message ID from the server
	 * @param string subject the messages subject
	 * @param array msgHeaders full headers retrieved for message
	 * @param int queue the queue we are in
	 * @return boolean status
	 */
	function is_automail2($mailobject, $uid, $subject, $msgHeaders, $queue=0)
	{
		// This array can be filled with checks that should be made.
		// 'bounces' and 'autoreplies' (level 1) are the keys coded below, the level 2 arrays
		// must match $msgHeader properties.
		//
		$autoMails = array(
			 'bounces' => array(
				 'subject' => array(
				)
				,'from' => array(
					 'mailer-daemon'
				)
			)
			,'autoreplies' => array(
				 'subject' => array(
					 'out of the office',
					 'out of office',
					 'autoreply'
					)
				,'auto-submitted' => array(
					'auto-replied'
				)
			)
		);

		// Check for bounced messages
		foreach ($autoMails['bounces'] as $_k => $_v) {
			if (count($_v) == 0) {
				continue;
			}
			$_re = '/(' . implode('|', $_v) . ')/i';
			if (preg_match($_re, $msgHeader[strtoupper($_k)])) {
				switch ($this->mailhandling[0]['bounces']) {
					case 'delete' :		// Delete, whatever the overall delete setting is
						$returnVal = $mailobject->deleteMessages($uid, $_folderName, 'move_to_trash');
						break;
					case 'forward' :	// Return the status of the forward attempt
						$returnVal = $this->forward_message2($mailobject, $uid, $mailcontent['subject'], lang("automatic mails (bounces) are configured to be forwarded"), $queue);
						if ($returnVal)
						{
							$rv = $mailobject->icServer->setFlags($uid, '\\Seen', 'add', true);
							$rv = $mailobject->icServer->setFlags($uid, '$Forwarded', 'add', true);
						}
					default :			// default: 'ignore'
						break;
				}
				return true;
			}
		}

		// Check for autoreplies
		foreach ($autoMails['autoreplies'] as $_k => $_v) {
			if (count($_v) == 0) {
				continue;
			}
			$_re = '/(' . implode('|', $_v) . ')/i';
			if (preg_match($_re, $msgHeader[strtoupper($_k)])) {
				switch ($this->mailhandling[0]['autoreplies']) {
					case 'delete' :		// Delete, whatever the overall delete setting is
						$returnVal = $mailobject->deleteMessages($uid, $_folderName, 'move_to_trash');
						break;
					case 'forward' :	// Return the status of the forward attempt
						$returnVal = $this->forward_message2($mailobject, $uid, $mailcontent['subject'], lang("automatic mails (replies) are configured to be forwarded"), $queue);
						if ($returnVal)
						{
							$rv = $mailobject->icServer->setFlags($uid, '\\Seen', 'add', true);
							$rv = $mailobject->icServer->setFlags($uid, '$Forwarded', 'add', true);
						}
						break;
					case 'process' :	// Process normally...
						return false;	// ...so act as if it's no automail
						break;
					default :			// default: 'ignore'
						break;
				}
				return true;
			}
		}
	}

	/**
	 * Decode a mail header
	 *
	 * @param string Pointer to the (possibly) encoded header that will be changes
	 */
	function decode_header (&$header)
	{
		$header = translation::decodeMailHeader($header);
	}

	/**
	 * Process a messages from the mailbox
	 *
	 * @param int Message ID from the server
	 * @param int queue tracking queue_id
	 * @return boolean true=message successfully processed, false=message couldn't or shouldn't be processed
	 */
	function process_message ($mid, $queue)
	{
		$senderIdentified = false;
		$this->mailBody = null; // Clear previous message
		$msgHeader = imap_headerinfo($this->mbox, $mid);
		if (self::LOG_LEVEL>2)  error_log(__METHOD__.':Header retrieved:'.array2string($msgHeader));
		// Workaround for PHP bug#48619
		//
		if (!empty($this->mailhandling[$queue]['address']) && (version_compare(PHP_VERSION, '5.2.10') === 0))
		{
			if (strstr($msgHeader->toaddress, $this->mailhandling[0]['address']) === false)
			{
				return false;
			}
		}
		/*
		 # Recent - R if recent and seen (Read), N if recent and unseen (New), ' ' if not recent
		 # Unseen - U if not recent AND unseen, ' ' if seen  OR unseen and recent.
		 # Flagged - F if marked as important/urgent, else ' '
		 # Answered - A if Answered, else ' '
		 # Deleted - D if marked for deletion, else ' '
		 # Draft - X if marked as draft, else ' '
		 */

		if ($msgHeader->Deleted == 'D')
		{
			return false; // Already deleted
		}
		/*
		if ($msgHeader->Recent == 'R' ||		// Recent and seen or
				($msgHeader->Recent == ' ' &&	// not recent but
				$msgHeader->Unseen == ' '))		// seen
		*/
		// should do the same, but is more robust as recent is a flag with some sideeffects
		// message should be marked/flagged as seen after processing
		// (don't forget to flag the message if forwarded; as forwarded is not supported with all IMAP use Seen instead)
		if ((($msgHeader->Recent == 'R' || $msgHeader->Recent == ' ') && $msgHeader->Unseen == ' ') ||
			($msgHeader->Answered == 'A' && $msgHeader->Unseen == ' ') || // is answered and seen
			$msgHeader->Draft == 'X') // is Draft
		{
			if (self::LOG_LEVEL>1) error_log(__FILE__.','.__METHOD__.':'."\n".' Subject:'.print_r($msgHeader->subject,true).
				"\n Date:".print_r($msgHeader->Date,true).
	            "\n Recent:".print_r($msgHeader->Recent,true).
	            "\n Unseen:".print_r($msgHeader->Unseen,true).
	            "\n Flagged:".print_r($msgHeader->Flagged,true).
	            "\n Answered:".print_r($msgHeader->Answered,true).
	            "\n Deleted:".print_r($msgHeader->Deleted,true)."\n Stopped processing Mail. Not recent, new, or already answered, or deleted");
			return false;
		}

		if ($this->is_automail($mid, $msgHeader)) {
			if (self::LOG_LEVEL>1) error_log(__METHOD__.' Automails will not be processed.');
			return false;
		}

		if (self::LOG_LEVEL>1) error_log(__FILE__.','.__METHOD__.' Mailheader/Subject:'.print_r($msgHeader,true));
		// Try several headers to identify the sender
		$try_addr = array(
			0 => $msgHeader->from[0],
			1 => $msgHeader->sender[0],
			2 => $msgHeader->return_path[0],
			3 => $msgHeader->reply_to[0],
			// Users mentioned addresses where not recognized. That was not
			// reproducable by me, so these headers are a trial-and-error apprach :-S
			4 => $msgHeader->fromaddress,
			5 => $msgHeader->senderaddress,
			6 => $msgHeader->return_pathaddress,
			7 => $msgHeader->reply_toaddress,
		);

		foreach ($try_addr as $id => $sender)
		{
			if (($extracted = self::extract_mailaddress (
					(is_object($sender)
						? $sender->mailbox.'@'.$sender->host
						: $sender))) !== false)
			{
				if ($id == 3)
				{
					// Save the reply-to address in case the mailaddress should be
					// added to the CC field.
					$replytoAddress = $extracted;
				}
				if (($senderIdentified = self::search_user($extracted)) === true)
				{
					// Save the reply-to address if we found match for use with the
					// auto-reply
					$replytoAddress = $extracted;
				}
			}
			if ($senderIdentified === true)
			{
				break;
			}
		}

		// Handle unrecognized mails
		if (!$senderIdentified)
		{
			switch ($this->mailhandling[$queue]['unrecognized_mails'])
			{
				case 'ignore' :		// Do nothing
					return false;
					break;
				case 'delete' :		// Delete, whatever the overall delete setting is
					@imap_delete($this->mbox, $mid);
					return false;	// Prevent from a second delete attempt
					break;
				case 'forward' :	// Return the status of the forward attempt
					$returnVal = self::forward_message($mid, $msgHeader, $queue);
					if ($returnVal) $status = $this->flagMessageAsSeen($mid, $msgHeader);
					return $returnVal;
					break;
				case 'default' :	// Save as default user; handled below
				default :			// Duh ??
					break;
			}
		}

		$this->mailSubject = $msgHeader->subject;
		$this->decode_header ($this->mailSubject);
		$this->ticketId = $this->get_ticketId($this->mailSubject);

		if ($this->ticketId == 0) // Create new ticket?
		{
			if (empty($this->mailhandling[$queue]['default_tracker']))
			{
				return false; // Not allowed
			}
			if (!$senderIdentified) // Unknown user
			{
				if (empty($this->mailhandling[$queue]['unrec_mail']))
				{
					return false; // Not allowed for unknown users
				}
				$this->mailSender = $this->mailhandling[$queue]['unrec_mail']; // Ok, set default user
			}
		}

		// By the time we get here, we know this ticket will be updated or created
		$rv = $this->get_mailbody ($mid);
		//error_log(__METHOD__.__LINE__.print_r($rv,true));
		$parsed_header=imap_rfc822_parse_headers(imap_fetchheader($this->mbox, $mid));
		$buff=array();
		$mailHeaderInfo = '';
		$header2desc=$header2comment=false;
		foreach(array('from','to','cc','bcc') as $k)
		{
			if (isset($parsed_header->{$k}))
			{
				foreach($parsed_header->{$k} as $i)
				{
					if ($i->mailbox)
					{
						$buff[$k][] = imap_rfc822_write_address($i->mailbox,$i->host,$i->personal);
					}
				}
			}
		}
		$this->mailBody = $rv['body'];
		if (isset($this->mailhandling[$queue]['mailheaderhandling']) && $this->mailhandling[$queue]['mailheaderhandling']>0)
		{
			if ($this->mailhandling[$queue]['mailheaderhandling']==1) $header2desc=true;
			if ($this->mailhandling[$queue]['mailheaderhandling']==2) $header2comment=true;
			if ($this->mailhandling[$queue]['mailheaderhandling']==3) $header2desc=$header2comment=true;
			$mailHeaderInfo = felamimail_bo::createHeaderInfoSection(array('FROM'=>implode(',',$buff['from']),
				'TO'=>(isset($buff['to']) && !empty($buff['to'])?implode(',',$buff['to']):null),
				'CC'=>(isset($buff['cc']) && !empty($buff['cc'])?implode(',',$buff['cc']):null),
				'BCC'=>(isset($buff['bcc']) && !empty($buff['bcc'])?implode(',',$buff['bcc']):null),
				'SUBJECT'=>$this->mailSubject,
				'DATE'=>felamimail_bo::_strtotime($msgHeader->Date)),'',false/*$this->htmledit*/);
		}
		// as we read the mail here, we should mark it as seen \Seen, \Answered, \Flagged, \Deleted  and \Draft are supported
		$status = $this->flagMessageAsSeen($mid, $msgHeader);

		if ($this->ticketId == 0)
		{
			$this->init();
			// this should take care, that new tickets are created by either the identified sender or the configured user
			// as by default the creator was the user running the async job, thus not recognizing the configuration, we assume the
			// running user having sufficient rights (see else)
			if (self::LOG_LEVEL>1)
			{
				error_log(__METHOD__.__LINE__.'->'.$this->check_rights(TRACKER_ITEM_CREATOR|TRACKER_ITEM_NEW|TRACKER_ADMIN|TRACKER_TECHNICIAN|TRACKER_USER,$this->mailhandling[$queue]['default_tracker'],null,$this->mailSender,'add'));
				error_log(__METHOD__.__LINE__.'->'.$this->mailSender);
				error_log(__METHOD__.__LINE__.'->'.$this->mailhandling[$queue]['default_tracker']);
			}
			if ($this->check_rights(TRACKER_ITEM_CREATOR|TRACKER_ITEM_NEW|TRACKER_ADMIN|TRACKER_TECHNICIAN|TRACKER_USER,$this->mailhandling[$queue]['default_tracker'],null,$this->mailSender,'add'))
			{
				$this->data['tr_creator'] = $this->user = $this->mailSender;
			}
			else
			{
				$this->user = $this->mailSender;
			}
			$this->data['tr_created'] = felamimail_bo::_strtotime($msgHeader->Date,'ts',true);
			$this->data['tr_summary'] = $this->mailSubject;
			$this->data['tr_tracker'] = $this->mailhandling[$queue]['default_tracker'];
			$this->data['cat_id'] = $this->mailhandling[$queue]['default_cat'];
			$this->data['tr_version'] = $this->mailhandling[$queue]['default_version'];
			$this->data['tr_priority'] = 5;
			$this->data['tr_description'] = ($mailHeaderInfo&&$header2desc?$mailHeaderInfo:'').$this->mailBody;
			//if ($this->htmledit) $this->data['tr_description'] = $this->data['tr_description'];
			if (!$senderIdentified && $this->mailhandling[$queue]['auto_cc'])
			{
				$this->data['tr_cc'] = $replytoAddress;
			}
			//error_log(__METHOD__.__LINE__.array2string($this->data));
		}
		else
		{
			$this->read($this->ticketId);
			if (!$senderIdentified)
			{
				switch ($this->mailhandling[$queue]['unrec_reply'])
				{
					case 0 :
						$this->user = $this->data['tr_creator'];
						break;
					case 1 :
						$this->user = 0;
						break;
					default :
						$this->user = 0;
						break;
				}
			}
			else
			{
				$this->user = $this->mailSender;
			}
			if ($this->mailhandling[$queue]['auto_cc'] && stristr($this->data['tr_cc'], $replytoAddress) === FALSE)
			{
				$this->data['tr_cc'] .= (empty($this->data['tr_cc'])?'':',').$replytoAddress;
			}
			$this->data['reply_message'] = ($mailHeaderInfo&&$header2comment?$mailHeaderInfo:'').$this->mailBody;
			$this->data['reply_created'] = felamimail_bo::_strtotime($msgHeader->Date,'ts',true);
		}
		$this->data['tr_status'] = parent::STATUS_OPEN; // If the ticket isn't new, (re)open it anyway
		// Save Current edition mode preventing mixed types
		if ($this->data['tr_edit_mode'] == 'html' && !$this->htmledit)
		{
			$this->data['tr_edit_mode'] = 'html';
		}
		elseif ($this->data['tr_edit_mode'] == 'ascii' && $this->htmledit)
		{
			$this->data['tr_edit_mode'] = 'ascii';
		}
		else
		{
			$this->htmledit ? $this->data['tr_edit_mode'] = 'html' : $this->data['tr_edit_mode'] = 'ascii';
		}
		if (self::LOG_LEVEL>1) error_log(__METHOD__.' Replytoaddress:'.array2string($replytoAddress));
		// Save the ticket and let tracker_bo->save() handle the autorepl, if required
		$saverv = $this->save(null,
			(($this->mailhandling[$queue]['auto_reply'] == 2		// Always reply or
			|| ($this->mailhandling[$queue]['auto_reply'] == 1	// only new tickets
				&& $this->ticketId == 0)					// and this is a new one
				) && (										// AND
					$senderIdentified		 				// we know this user
				|| (!$senderIdentified						// or we don't and
				&& $this->mailhandling[$queue]['reply_unknown'] == 1 // don't care
			))) == true
				? array(
					'reply_text' => $this->mailhandling[$queue]['reply_text'],
					// UserID or mail address
					'reply_to' => ($this->user ? $this->user : $replytoAddress),
				)
				: null
		);

		if (($saverv==0) && is_array($rv['attachments']))
		{
			foreach ($rv['attachments'] as $attachment)
			{
				if(is_readable($attachment['tmp_name']))
				{
					egw_link::attach_file('tracker',$this->data['tr_id'],$attachment);
				}
			}
		}

		return !$saverv;
	}

	/**
	 * Process a messages from the mailbox (felamimail/NET_IMAP Object)
	 *
	 * @param int mailobject that holds connection to the server
	 * @param int Message ID from the server
	 * @param string _folderName the folder where the messages should reside in
	 * @param int queue tracking queue_id
	 * @return boolean true=message successfully processed, false=message couldn't or shouldn't be processed
	 */
	function process_message2 ($mailobject, $uid, $_folderName, $queue)
	{
		$senderIdentified = true;
		$s = $mailobject->icServer->getSummary($uid, true);// we need that, to be able to manipulate message flags as Seen, etc.
		$subject = $mailobject->decode_subject($s[0]['SUBJECT']);// we use the needed headers for determining beforehand, if we have a new ticket, or a comment
		// FLAGS - control in case filter wont work
		$flags = felamimail_bo::prepareFlagsArray($s[0]);
		if ($flags['deleted'] || $flags['seen'])
		{
			return false; // Already seen or deleted (in case our filter did not work as intended)
		}
		// should do the same as checking only recent, but is more robust as recent is a flag with some sideeffects
		// message should be marked/flagged as seen after processing
		// (don't forget to flag the message if forwarded; as forwarded is not supported with all IMAP use Seen instead)
		if (($flags['recent'] && $flags['seen']) ||
			($flags['answered'] && $flags['seen']) || // is answered and seen
			$flags['draft']) // is Draft
		{
			if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__.':'."UID:$uid in Folder $_folderName with".' Subject:'.$subject.
				"\n Date:".$s[0]['DATE'].
	            "\n Flags:".print_r($flags,true).
				"\n Stopped processing Mail ($uid). Not recent, new, or already answered, or draft");
			return false;
		}
		$subject = felamimail_bo::adaptSubjectForImport($subject);	
		$tId = $this->get_ticketId($subject);
		if ($tId)
		{
			$t = $this->read($tId);
			$this->htmledit = $t['tr_edit_mode']=='html';
		}
		$addHeaderInfoSection = false;
		if (isset($this->mailhandling[$queue]['mailheaderhandling']) && $this->mailhandling[$queue]['mailheaderhandling']>0)
		{
			//$tId == 0 will be new ticket, else will indicate comment
			if ($this->mailhandling[$queue]['mailheaderhandling']==1) $addHeaderInfoSection=($tId == 0 ? true : false);
			if ($this->mailhandling[$queue]['mailheaderhandling']==2) $addHeaderInfoSection=($tId == 0 ? false: true);
			if ($this->mailhandling[$queue]['mailheaderhandling']==3) $addHeaderInfoSection=true;
		}
		if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__."# $uid with title:".$subject.($tId==0?' for new ticket':' for ticket:'.$tId).'. FetchMailHeader:'.$addHeaderInfoSectiont.' mailheaderhandling:'.$this->mailhandling[$queue]['mailheaderhandling']);
		$mailcontent = $mailobject::get_mailcontent($mailobject,$uid,$partid='',$_folderName,$this->htmledit,$addHeaderInfoSection,(!($GLOBALS['egw_info']['user']['preferences']['felamimail']['saveAsOptions']==='text_only')));

		// on we go, as everything seems to be in order. flagging the message
		$rv = $mailobject->icServer->setFlags($uid, '\\Seen', 'add', true);
		if ( PEAR::isError($rv)) error_log(__METHOD__.__LINE__." failed to flag Message $uid as Seen in Folder: ".$_folderName.' due to:'.$rv->message);

		// this one adds the mail itself (as message/rfc822 (.eml) file) to the infolog as additional attachment
		// this is done to have a simple archive functionality
		if ($mailcontent && $GLOBALS['egw_info']['user']['preferences']['felamimail']['saveAsOptions']==='add_raw')
		{
			$message = $mailobject->getMessageRawBody($uid, $partid);
			$headers = $mailobject->getMessageHeader($uid, $partid,true);
			$subject = felamimail_bo::adaptSubjectForImport($headers['SUBJECT']);
			$attachment_file =tempnam($GLOBALS['egw_info']['server']['temp_dir'],$GLOBALS['egw_info']['flags']['currentapp']."_");
			$tmpfile = fopen($attachment_file,'w');
			fwrite($tmpfile,$message);
			fclose($tmpfile);
			$size = filesize($attachment_file);
			$mailcontent['attachments'][] = array(
					'name' => trim($subject).'.eml',
					'mimeType' => 'message/rfc822',
					'tmp_name' => $attachment_file,
					'size' => $size,
				);
		}
		if (self::LOG_LEVEL>1 && $mailcontent)
		{
			error_log(__METHOD__.__LINE__.'#'.array2string($mailcontent));
			if (!empty($mailcontent['attachments'])) error_log(__METHOD__.__LINE__.'#'.array2string($mailcontent['attachments']));
		}
		if (!$mailcontent)
		{
			error_log(__METHOD__.__LINE__." Could not retrieve Content for message $uid in $_folderName for Server with ID:".$mailobject->icServer->ImapServerId." for Queue: $queue");
			return false;
		}
		// prepare the data to be saved
		// (use bo function connected to the ui interface mail import, so after preparing we need to adjust stuff)
		$mailcontent['subject'] = felamimail_bo::adaptSubjectForImport($mailcontent['subject']);
		$this->data = $this->prepare_import_mail(
			$mailcontent['mailaddress'],
			$mailcontent['subject'],
			$mailcontent['message'],
			$mailcontent['attachments'],
			strtotime($mailcontent['headers']['DATE']),
			$queue
		);
		if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.array2string($this->data));
		if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.' Mailaddress:'.array2string($mailcontent['mailaddress']));
		if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__.':'.$this->mailhandling[$queue]['unrecognized_mails'].':'.($this->data['tr_id']?$this->data['reply_creator']:$this->data['tr_creator']).' vs. '.array2string($this->user).' Ticket:'.$this->data['tr_id'].' Message:'.$this->data['msg']);

		// handle auto - mails
		if ($this->is_automail2($mailobject, $uid, $mailcontent['subject'], $mailcontent['headers'], $queue)) {
			if (self::LOG_LEVEL>1) error_log(__METHOD__.' Automails will not be processed.');
			return false;
		}
		if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.array2string($this->data['msg']).':'.$this->data['tr_creator'].'=='.$this->data['reply_creator'].'=='. $this->user);
		// Handle unrecognized mails: we get a warning from prepare_import_mail, when mail is not recognized
		// ToDo: Introduce a key, to be able to tell the error-condition
		if (!empty($this->data['msg']) && (($this->data['tr_creator'] == $this->user) || ($this->data['tr_id'] && $this->data['reply_creator'] == $this->user)))
		{
			if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__.array2string($this->data['msg']).':'.$this->data['tr_creator'].'=='. $this->user);
			if ($this->data['tr_id'] && $this->data['reply_creator'] == $this->user) unset($this->data['reply_creator']);
			$senderIdentified = false;
			$replytoAddress = $mailcontent['mailaddress'];
			if (self::LOG_LEVEL>1) error_log(__METHOD__.__LINE__.' ReplyToAddress:'.$replytoAddress);
			switch ($this->mailhandling[$queue]['unrecognized_mails'])
			{
				case 'ignore' :		// Do nothing
					return false;
					break;
				case 'delete' :		// Delete, whatever the overall delete setting is
					$mailobject->deleteMessages($uid, $_folderName, 'move_to_trash');
					return false;	// Prevent from a second delete attempt
					break;
				case 'forward' :	// Return the status of the forward attempt
					$returnVal = $this->forward_message2($mailobject, $uid, $mailcontent['subject'], $this->data['msg'], $queue);
					if ($returnVal)
					{
						$rv = $mailobject->icServer->setFlags($uid, '\\Seen', 'add', true);
						$rv = $mailobject->icServer->setFlags($uid, '$Forwarded', 'add', true);
					}
					return $returnVal;
					break;
				case 'default' :	// Save as default user; handled below
				default :			// Duh ??
					break;
			}
		}
		else
		{
			$replytoAddress = ($this->data['tr_id']?$this->data['reply_creator']:$this->data['tr_creator']);
		}

		// do not fetch the possible ticketID (again), use what is returned by prepare_import_mail
		$this->ticketId = $this->data['tr_id'];


		if ($this->ticketId == 0) // Create new ticket?
		{
			if (empty($this->mailhandling[$queue]['default_tracker']))
			{
				return false; // Not allowed
			}
			if (!$senderIdentified) // Unknown user
			{
				if (empty($this->mailhandling[$queue]['unrec_mail']))
				{
					return false; // Not allowed for unknown users
				}
				$this->mailSender = $this->mailhandling[$queue]['unrec_mail']; // Ok, set default user
			}
		}
		else
		{
			$this->mailSender = (!$senderIdentified?$this->mailhandling[$queue]['unrec_mail']:$this->data['reply_creator']);
		}

		if ($this->ticketId == 0)
		{

			$this->data['tr_tracker'] = $this->mailhandling[$queue]['default_tracker'];
			$this->data['cat_id'] = $this->mailhandling[$queue]['default_cat'];
			$this->data['tr_version'] = $this->mailhandling[$queue]['default_version'];
			$this->data['tr_priority'] = 5;
			if (!$senderIdentified && isset($this->mailSender))  $this->data['tr_creator'] = $this->user = $this->mailSender;
			//error_log(__METHOD__.__LINE__.array2string($this->data));
		}
		else
		{
			if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.array2string($this->data['reply_message']));
			if (!$senderIdentified)
			{
				if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.':'.$this->data['tr_creator'].':'.$this->mailhandling[$queue]['unrec_mail'].':'.$this->user.':'.$this->mailSender.'#');
				switch ($this->mailhandling[$queue]['unrec_reply'])
				{
					case 0 :
						$this->user = (!empty($this->data['tr_creator'])?$this->data['tr_creator']:(!empty($this->mailhandling[$queue]['unrec_mail'])?$this->mailhandling[$queue]['unrec_mail']:$this->user));
						break;
					case 1 :
						$this->user = 0;
						break;
					default :
						$this->user = (!empty($this->mailhandling[$queue]['unrec_mail'])?$this->mailhandling[$queue]['unrec_mail']:0);
						break;
				}
			}
			else
			{
				$this->user = $this->mailSender;
			}
		}
		if ($this->ticketId == 0 && (!isset($this->mailhandling[$queue]['auto_cc']) || $this->mailhandling[$queue]['auto_cc']==false))
		{
			unset($this->data['tr_cc']);
		}
		$this->data['tr_status'] = parent::STATUS_OPEN; // If the ticket isn't new, (re)open it anyway

		if ($this->data['popup']) unset($this->data['popup']);
		// Save Current edition mode preventing mixed types
		if ($this->data['tr_edit_mode'] == 'html' && !$this->htmledit)
		{
			$this->data['tr_edit_mode'] = 'html';
		}
		elseif ($this->data['tr_edit_mode'] == 'ascii' && $this->htmledit)
		{
			$this->data['tr_edit_mode'] = 'ascii';
		}
		else
		{
			$this->htmledit ? $this->data['tr_edit_mode'] = 'html' : $this->data['tr_edit_mode'] = 'ascii';
		}
		if (self::LOG_LEVEL>1 && $replytoAddress) error_log(__METHOD__.__LINE__.' Replytoaddress:'.array2string($replytoAddress).' Text:'.$this->mailhandling[$queue]['reply_text']);
		// Save the ticket and let tracker_bo->save() handle the autorepl, if required
		$saverv = $this->save(null,
			(($this->mailhandling[$queue]['auto_reply'] == 2		// Always reply or
			|| ($this->mailhandling[$queue]['auto_reply'] == 1	// only new tickets
				&& $this->ticketId == 0)					// and this is a new one
				) && (										// AND
					$senderIdentified		 				// we know this user
				|| (!$senderIdentified						// or we don't and
				&& $this->mailhandling[$queue]['reply_unknown'] == 1 // don't care
			))) == true
				? array(
					'reply_text' => $this->mailhandling[$queue]['reply_text'],
					// UserID or mail address
					'reply_to' => ($replytoAddress ? $replytoAddress : $this->user),
				)
				: null
		);
		// attachments must be saved/linked after saving the ticket
		if (($saverv==0) && is_array($mailcontent['attachments']))
		{
			foreach ($mailcontent['attachments'] as $attachment)
			{
				//error_log(__METHOD__.__LINE__.'#'.$attachment['tmp_name'].'#'.$this->data['tr_id']);
				if(is_readable($attachment['tmp_name']))
				{
					//error_log(__METHOD__.__LINE__.'# trying to link '.$attachment['tmp_name'].'# to:'.$this->data['tr_id']);
					egw_link::attach_file('tracker',$this->data['tr_id'],$attachment);
				}
			}
		}

		return !$saverv;
	}

	/**
	 * flag message after processing
	 *
	 */
	function flagMessageAsSeen($mid, $messageHeader)
	{
		return imap_setflag_full($this->mbox, $mid, "\\Seen".($messageHeader->Flagged == 'F' ? "\\Flagged" : ""));
	}

	/**
	 * Get an email address in plain format, no matter how the address was specified
	 *
	 * @param string $addr a string (probably) containing an email address
	 */
	function extract_mailaddress($addr='')
	{
		if (empty($addr))
		{
			return false;
		}
		//preg_match_all("/[a-zA-Z0-9_\-\.]+?@([a-zA-Z0-9_\-]+?\.)+?[a-zA-Z]{2,}/", $addr, $address);
		preg_match_all("/([A-Za-z0-9][A-Za-z0-9._-]*)?[A-Za-z0-9]@([A-Za-z0-9ÄÖÜäöüß](|[A-Za-z0-9ÄÖÜäöüß_-]*[A-Za-z0-9ÄÖÜäöüß])\.)+[A-Za-z]{2,6}/", $addr, $address);
		return ($address[0][0]);
	}

	/**
	 * Retrieve the user ID based on the mail address that was extracted from the mailheaders
	 *
	 * @param string $mail_addr, the mail address.
	 */
	function search_user($mail_addr='')
	{
		$this->mailSender = null; // Make sure previous msg data is cleared
		if (self::LOG_LEVEL>1) error_log(__METHOD__.'Try to resolve Useraccount by mail:'.print_r($mail_addr,true));
		$account_ID = $GLOBALS['egw']->accounts->name2id($mail_addr,'account_email');
		if (!empty($account_ID)) $this->mailSender = $account_ID;
		if (self::LOG_LEVEL>1 && $this->mailSender) error_log(__METHOD__.'Found User:'.print_r($this->mailSender,true));
		return (!empty($account_ID) ? true : false);
	}

	/**
	 * Forward a mail that was not recognized
	 *
	 * @param int message ID from the server
	 * @return boolean status
	 */
	function forward_message($mid=0, &$headers=null, $queue=0)
	{

		if ($mid == 0 || $headers == null) // no data
		{
			return false;
		}

		// Sending mail is not implemented using notifations, since it's pretty straight forward here
		$to   = $this->mailhandling[$queue]['forward_to'];
		$subj = $headers->subject;
		$body = imap_body($this->mbox, $mid, FK_INTERNAL);
		$hdrs = 'From: ' . $headers->fromaddress . "\r\n" .
				'Reply-To: ' . $headers->reply_toaddress . "\r\n";

		return (mail($to, $subj, $body, $hdrs));

	}

	/**
	 * Forward a mail that was not recognized
	 *
	 * @param object mailobject holding the server, and its connection
	 * @param int message ID from the server
	 * @param string subject the messages subject
	 * @param array _message full retrieved message
	 * @param int queue the queue we are in
	 * @return boolean status
	 */
	function forward_message2($mailobject, $uid, $subject, $_message, $queue=0)
	{
		$this->smtpMail->ClearAddresses();
		$this->smtpMail->ClearAttachments();
		$this->smtpMail->AddAddress($this->mailhandling[$queue]['forward_to'], $this->mailhandling[$queue]['forward_to']);
		$this->smtpMail->AddCustomHeader('X-EGroupware-type: tracker-forward');
		$this->smtpMail->AddCustomHeader('X-EGroupware-Tracker: '.$queue);
		$this->smtpMail->AddCustomHeader('X-EGroupware-Install: '.$GLOBALS['egw_info']['server']['install_id'].'@'.$GLOBALS['egw_info']['server']['default_domain']);
		//$this->mail->AddCustomHeader('X-EGroupware-URL: notification-mail');
		//$this->mail->AddCustomHeader('X-EGroupware-Tracker: notification-mail');
		$account_email = $GLOBALS['egw']->accounts->id2name($this->sender,'account_email');
		$account_lid = $GLOBALS['egw']->accounts->id2name($this->sender,'account_lid');
		$notificationSender = (!empty($this->notification[$queue]['sender'])?$this->notification[$queue]['sender']:$this->notification[0]['sender']);
		$this->smtpMail->From = (!empty($notificationSender)?$notificationSender:$account_email);
		$this->smtpMail->FromName = (!empty($notificationSender)?$notificationSender:$account_lid);
		$this->smtpMail->Subject = lang('[FWD]').' '.$subject;
		$this->smtpMail->IsHTML(false);
		$this->smtpMail->Body = lang("This message was forwarded to you from EGroupware-Tracker Mailhandling: %1. \r\nSee attachment (original mail) for further details\r\n %2",$queue,$_message);

		$rawBody        = $mailobject->getMessageRawBody($uid);
		$this->smtpMail->AddStringAttachment($rawBody, $this->smtpMail->EncodeHeader($subject), '7bit', 'message/rfc822');
		if(!$error=$this->smtpMail->Send())
		{
			error_log(__METHOD__.__LINE__." Failed forwarding message via email.$error".print_r($this->smtpMail->ErrorInfo,true));
			return false;
		}
		if (self::LOG_LEVEL>2) error_log(__METHOD__.__LINE__.array2string($this->smtpMail));
		return true;
	}

	/**
	 * Check if exist and if not start or stop an async job to check incoming mails
	 *
	 * @param int $queue ID of the queue to check email for
	 * @param int $interval=1 >0=start, 0=stop
	 */
	static function set_async_job($queue=0, $interval=0)
	{
		$async = new asyncservice();
		$job_id = 'tracker-check-mail' . ($queue ? '-'.$queue : '');

		// Make sure an existing timer is cancelled
		$async->cancel_timer($job_id);

		if ($interval > 0)
		{
			if ($interval == 60)
			{
				$async->set_timer(array('hour' => '*'),$job_id,'tracker.tracker_mailhandler.check_mail',(int)$queue);
			}
			else
			{
				$async->set_timer(array('min' => "*/$interval"),$job_id,'tracker.tracker_mailhandler.check_mail',$queue);
			}
		}
	}
}
