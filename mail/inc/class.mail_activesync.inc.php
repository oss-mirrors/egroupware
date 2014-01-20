<?php
/**
 * EGroupware - Mail - interface class for activesync implementation
 *
 * @link http://www.egroupware.org
 * @package mail
 * @author Stylite AG [info@stylite.de]
 * @author Ralf Becker <rb@stylite.de>
 * @author Philip Herbert <philip@knauber.de>
 * @copyright (c) 2014 by Stylite AG <info-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * mail eSync plugin
 *
 * Plugin creates a device specific file to map alphanumeric folder names to nummeric id's.
 */
class mail_activesync implements activesync_plugin_write, activesync_plugin_sendmail, activesync_plugin_meeting_response, activesync_plugin_search_mailbox
{
	/**
	 * var BackendEGW
	 */
	private $backend;

	/**
	 * Instance of mail_bo
	 *
	 * @var mail_bo
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

	private $folders;

	private $messages;

	static $profileID;

	/**
	 * Integer waitOnFailureDefault how long (in seconds) to wait on connection failure
	 *
	 * @var int
	 */
	protected $waitOnFailureDefault = 120;

	/**
	 * Integer waitOnFailureLimit how long (in seconds) to wait on connection failure until a 500 is raised
	 *
	 * @var int
	 */
	protected $waitOnFailureLimit = 7200;
	/**
	 * debugLevel - enables more debug
	 *
	 * @var int
	 */
	private $debugLevel = 0;

	/**
	 * Constructor
	 *
	 * @param BackendEGW $backend
	 */
	public function __construct(BackendEGW $backend)
	{
		//$this->debugLevel=2;
		$this->backend = $backend;
		if (!isset($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID']))
		{
			if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' Noprefs set: using 0 as default');
			// globals preferences add appname varname value
			$GLOBALS['egw']->preferences->add('activesync','mail-ActiveSyncProfileID',0,'user');
			// save prefs
			$GLOBALS['egw']->preferences->save_repository(true);
		}
		if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' ActiveProfileID:'.array2string(self::$profileID));

		if (is_null(self::$profileID))
		{
			if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' self::ProfileID isNUll:'.array2string(self::$profileID));
			self::$profileID =& egw_cache::getSession('mail','activeSyncProfileID');
			if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' ActiveProfileID (after reading Cache):'.array2string(self::$profileID));
		}
		if (isset($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID']))
		{
			if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' Pref for ProfileID:'.array2string($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID']));
			if ($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID'] == 'G')
			{
				self::$profileID = 'G'; // this should trigger the fetch of the first negative profile (or if no negative profile is available the firstb there is)
			}
			else
			{
				self::$profileID = (int)$GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID'];
			}
		}
		if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' Profile Selected (after reading Prefs):'.array2string(self::$profileID));
		$params =null;
		if (isset($GLOBALS['egw_setup'])) $params['setup']=true;
		$identities = $this->getAvailableProfiles($params);
		//error_log(__METHOD__.__LINE__.array2string($identities));
		if (array_key_exists(self::$profileID,$identities))
		{
			// everything seems to be in order self::$profileID REMAINS UNCHANGED
		}
		else
		{
			foreach (array_keys((array)$identities) as $k => $ident) if ($ident <0) self::$profileID = $ident;
			if ($this->debugLevel>1) error_log(__METHOD__.__LINE__.' Profile Selected (after trying to fetch DefaultProfile):'.array2string(self::$profileID));
			if (!array_key_exists(self::$profileID,$identities))
			{
				// everything failed, try first profile found
				$keys = array_keys((array)$identities);
				if (count($keys)>0) self::$profileID = array_shift($keys);
				else self::$profileID = 0;
			}
		}
		if ($this->debugLevel>0) error_log(__METHOD__.'::'.__LINE__.' ProfileSelected:'.self::$profileID.' -> '.$identities[self::$profileID]);
		//$this->debugLevel=0;
	}

	/**
	 * fetches available Profiles
	 *
	 * @return array
	 */
	function getAvailableProfiles($params = null)
	{
		$allIdentities = mail_bo::getAllIdentities();
		$identities = array();
		if (!isset($params['setup']))
		{
			if (!$this->mail) $this->mail = mail_bo::getInstance(true,(self::$profileID=='G'?emailadmin_bo::getDefaultAccID():self::$profileID));
			foreach($allIdentities as $key => $singleIdentity) {
				if (isset($identities[$singleIdentity['acc_id']])) continue; // only use the first
				$iS = mail_bo::generateIdentityString($singleIdentity);
				if (mail_bo::$mailConfig['how2displayIdentities']=='' || count($allIdentities) ==1)
				{
					$id_prepend ='';
				}
				else
				{
					$id_prepend = '('.$singleIdentity['ident_id'].') ';
				}
				if (array_search($id_prepend.$iS,$identities)===false)
				{
					$identities[$singleIdentity['acc_id']] = $id_prepend.$iS;
				}
			}
		}
		return $identities;
	}

	/**
	 * Populates $settings for the preferences
	 *
	 * @param array|string $hook_data
	 * @return array
	 */
	function settings($hook_data)
	{
		$identities = array();
		if (!isset($hook_data['setup']))
		{
			$identities = $this->getAvailableProfiles($hook_data);
		}
		$identities += array(
			'G' => lang('Primary Profile'),
		);

		$settings['mail-ActiveSyncProfileID'] = array(
			'type'   => 'select',
			'label'  => 'eMail Account to sync',
			'name'   => 'mail-ActiveSyncProfileID',
			'help'   => 'eMail Account to sync ',
			'values' => $identities,
			'default'=> 'G',
			'xmlrpc' => True,
			'admin'  => False,
		);
		$settings['mail-allowSendingInvitations'] = array(
			'type'   => 'select',
			'label'  => 'allow sending of calendar invitations using this profile?',
			'name'   => 'mail-allowSendingInvitations',
			'help'   => 'control the sending of calendar invitations while using this profile',
			'values' => array(
				'sendifnocalnotif'=>'only send if there is no notification in calendar',
				'send'=>'yes, always send',
				'nosend'=>'no, do not send',
			),
			'xmlrpc' => True,
			'default' => 'sendifnocalnotif',
			'admin'  => False,
		);
		return $settings;
	}

	/**
	 * Verify preferences
	 *
	 * @param array|string $hook_data
	 * @return array with error-messages from all plugins
	 */
	function verify_settings($hook_data)
	{
		$errors = array();

		// check if an eSync eMail profile is set (might not be set as default or forced!)
		if (isset($hook_data['prefs']['mail-ActiveSyncProfileID']) || $hook_data['type'] == 'user')
		{
			// eSync and eMail translations are not (yet) loaded
			translation::add_app('activesync');
			translation::add_app('mail');

			// inject preference to verify and call constructor
			$GLOBALS['egw_info']['user']['preferences']['activesync']['mail-ActiveSyncProfileID'] =
				$hook_data['prefs']['mail-ActiveSyncProfileID'];
			$this->__construct($this->backend);

			try {
				$this->_connect();
				$this->_disconnect();

				if (!$this->_wasteID) $errors[] = lang('No valid %1 folder configured!', '<b>'.lang('trash').'</b>');
				if (!$this->_sentID) $errors[] = lang('No valid %1 folder configured!', '<b>'.lang('send').'</b>');
			}
			catch(Exception $e) {
				$errors[] = lang('Can not open IMAP connection').': '.$e->getMessage();
			}
			if ($errors)
			{
				$errors[] = '<b>'.lang('eSync will FAIL without a working eMail configuration!').'</b>';
			}
		}
		//error_log(__METHOD__.'('.array2string($hook_data).') returning '.array2string($errors));
		return $errors;
	}

	/**
	 * Open IMAP connection
	 *
	 * @param int $account integer id of account to use
	 * @todo support different accounts
	 */
	private function _connect($account=0)
	{
		static $waitOnFailure;
		if ($this->mail && $this->account != $account) $this->_disconnect();

		$hereandnow = egw_time::to('now','ts');
		$this->_wasteID = false;
		$this->_sentID = false;

		$connectionFailed = false;

		if (is_null($waitOnFailure)||empty($waitOnFailure[self::$profileID])||empty($waitOnFailure[self::$profileID][$this->backend->_devid])) $waitOnFailure = egw_cache::getCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$callback=null,$callback_params=array(),$expiration=60*60*2);
		if (isset($waitOnFailure[self::$profileID]) && !empty($waitOnFailure[self::$profileID]) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]) && isset($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']) && isset($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']))
		{
			if ($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']+$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']<$hereandnow)
			{
				if ($this->debugLevel>0); error_log(__METHOD__.__LINE__.'# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid']." Refuse to open connection for Profile:".self::$profileID.' Device '.$this->backend->_devid.' should still wait '.array2string($waitOnFailure[self::$profileID][$this->backend->_devid]));
				header("HTTP/1.1 503 Service Unavailable");
				$hL = $waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']+$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']-$hereandnow;
				header("Retry-After: ".$hL);
				exit;
			}
		}
		if (!$this->mail)
		{
			$this->account = $account;
			// todo: tell mail which account to use
			//error_log(__METHOD__.__LINE__.' create object with ProfileID:'.array2string(self::$profileID));
			try
			{
				$this->mail = mail_bo::getInstance(false,self::$profileID);
				if (self::$profileID == 0 && isset($this->mail->icServer->ImapServerId) && !empty($this->mail->icServer->ImapServerId)) self::$profileID = $this->mail->icServer->ImapServerId;
				$this->mail->openConnection(self::$profileID,false);
				$connectionFailed = false;
			}
			catch (Exception $e)
			{
				$connectionFailed = true;
				$errorMessage = $e->getMessage();
			}
		}
		else
		{
			//error_log(__METHOD__.__LINE__." connect with profileID: ".self::$profileID);
			if (self::$profileID == 0 && isset($this->mail->icServer->ImapServerId) && !empty($this->mail->icServer->ImapServerId)) self::$profileID = $this->mail->icServer->ImapServerId;
			try
			{
				$this->mail->openConnection(self::$profileID,false);
				$connectionFailed = false;
			}
			catch (Exception $e)
			{
				$connectionFailed = true;
				$errorMessage = $e->getMessage();
			}
		}
		if (empty($waitOnFailure[self::$profileID][$this->backend->_devid])) $waitOnFailure[self::$profileID][$this->backend->_devid] = array('howlong'=>$this->waitOnFailureDefault,'lastattempt'=>$hereandnow);
		if ($connectionFailed)
		{
			//error_log(__METHOD__.__LINE__."($account) could not open connection!".$errorMessage);
			//error_log(date('Y-m-d H:i:s').' '.__METHOD__.__LINE__."($account) can not open connection!".$this->mail->getErrorMessage()."\n",3,'/var/lib/egroupware/esync-imap.log');
			//error_log('# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', URL='.
			//	($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n\n",3,'/var/lib/egroupware/esync-imap.log');
			if ($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong'] > $this->waitOnFailureLimit )
			{
				header("HTTP/1.1 500 Internal Server Error");
				$waitOnFailure[self::$profileID][$this->backend->_devid] = array('howlong'=>$this->waitOnFailureDefault,'lastattempt'=>$hereandnow);
				egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
				throw new egw_exception_not_found(__METHOD__.__LINE__."($account) can not open connection on Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid);
			}
			else
			{
				//error_log(__METHOD__.__LINE__.'# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid']." Can not open connection for Profile:".self::$profileID.' Device:'.$this->backend->_devid.' should wait '.array2string($waitOnFailure[self::$profileID][$this->backend->_devid]));
				header("HTTP/1.1 503 Service Unavailable");
				header("Retry-After: ".$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']);
				$waitOnFailure[self::$profileID][$this->backend->_devid] = array('howlong'=>(empty($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong'])?$this->waitOnFailureDefault:$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']) * 2,'lastattempt'=>$hereandnow);
				egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
				$ethrown = new egw_exception_not_found(__METHOD__.__LINE__."($account) can not open connection on Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid);
				_egw_log_exception($ethrown);
				exit;
			}
			//die('Mail not or mis-configured!');
		}
		else
		{
			$waitOnFailure[self::$profileID][$this->backend->_devid] = array();
			egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
		}
		$this->_wasteID = $this->mail->getTrashFolder(false);
		//error_log(__METHOD__.__LINE__.' TrashFolder:'.$this->_wasteID);
		$this->_sentID = $this->mail->getSentFolder(false);
		//error_log(__METHOD__.__LINE__.' SentFolder:'.$this->_sentID);
		//error_log(__METHOD__.__LINE__.' Connection Status for ProfileID:'.self::$profileID.'->'.$this->mail->icServer->_connected);
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

	/**
	 *  GetFolderList
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetFolderList()
	{
		$folderlist = array();
		//debugLog(__METHOD__.__LINE__);
		/*foreach($available_accounts as $account)*/ $account = 0;
		{
			$this->_connect($account);
			if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false,$_alwaysGetDefaultFolders=true);
			//debugLog(__METHOD__.__LINE__.array2string($this->folders));

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

    /**
     * Sends a message which is passed as rfc822. You basically can do two things
     * 1) Send the message to an SMTP server as-is
     * 2) Parse the message yourself, and send it some other way
     * It is up to you whether you want to put the message in the sent items folder. If you
     * want it in 'sent items', then the next sync on the 'sent items' folder should return
     * the new message as any other new message in a folder.
     *
     * @param string $rfc822 mail
     * @param array $smartdata=array() values for keys:
     * 	'task': 'forward', 'new', 'reply'
     *  'itemid': id of message if it's an reply or forward
     *  'folderid': folder
     *  'replacemime': false = send as is, false = decode and recode for whatever reason ???
	 *	'saveinsentitems': 1 or absent?
     * @param boolean|double $protocolversion=false
     * @return boolean true on success, false on error
     *
     * @see eg. BackendIMAP::SendMail()
     * @todo implement either here or in mail backend
     * 	(maybe sending here and storing to sent folder in plugin, as sending is supposed to always work in EGroupware)
     */
	public function SendMail($rfc822, $smartdata=array(), $protocolversion = false)
	{
		//$this->debugLevel=3;
		$ClientSideMeetingRequest = false;
		$allowSendingInvitations = 'sendifnocalnotif';
		if (isset($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-allowSendingInvitations']) &&
			$GLOBALS['egw_info']['user']['preferences']['activesync']['mail-allowSendingInvitations']=='nosend')
		{
			$allowSendingInvitations = false;
		}
		elseif (isset($GLOBALS['egw_info']['user']['preferences']['activesync']['mail-allowSendingInvitations']) &&
			$GLOBALS['egw_info']['user']['preferences']['activesync']['mail-allowSendingInvitations']!='nosend')
		{
			$allowSendingInvitations = $GLOBALS['egw_info']['user']['preferences']['activesync']['mail-allowSendingInvitations'];
		}

		if ($protocolversion < 14.0)
    		debugLog("IMAP-SendMail: " . (isset($rfc822) ? $rfc822 : ""). "task: ".(isset($smartdata['task']) ? $smartdata['task'] : "")." itemid: ".(isset($smartdata['itemid']) ? $smartdata['itemid'] : "")." folder: ".(isset($smartdata['folderid']) ? $smartdata['folderid'] : ""));
		if ($this->debugLevel>0) debugLog("IMAP-Sendmail: Smartdata = ".array2string($smartdata));
		//error_log("IMAP-Sendmail: Smartdata = ".array2string($smartdata));
		// if we cannot decode the mail in question, fail
		if (class_exists('Mail_mimeDecode',false)==false && (@include_once 'Mail/mimeDecode.php') === false)
		{
			debugLog("IMAP-SendMail: Could not find Mail_mimeDecode.");
			return false;
		}
		// initialize our mail_bo
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID);
		$activeMailProfiles = $this->mail->getAccountIdentities(self::$profileID);
		$activeMailProfile = array_shift($activeMailProfiles);
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' ProfileID:'.self::$profileID.' ActiveMailProfile:'.array2string($activeMailProfile));

		// initialize the new egw_mailer object for sending
		$mailObject = new egw_mailer();
		$mailObject->CharSet = 'utf-8'; // set charset always to utf-8
		// default, should this be forced?
		$mailObject->IsSMTP();
		$mailObject->Sender  = $activeMailProfile['ident_email'];
		$mailObject->From 	= $activeMailProfile['ident_email'];
		$mailObject->FromName = $mailObject->EncodeHeader(mail_bo::generateIdentityString($activeMailProfile,false));
		$mailObject->AddCustomHeader('X-Mailer: mail-Activesync');

		$mimeParams = array('decode_headers' => true,
							'decode_bodies' => false,
							'include_bodies' => true,
							'input' => $rfc822,
							'crlf' => "\r\n",
							'charset' => 'utf-8');
		$mobj = new Mail_mimeDecode($mimeParams['input'], $mimeParams['crlf']);
		$message = $mobj->decode($mimeParams, $mimeParams['crlf']);
		//error_log(__METHOD__.__LINE__.array2string($message));
		$mailObject->Priority = $message->headers['priority'];
		$mailObject->Encoding = 'quoted-printable'; // we use this by default

		if (isset($message->headers['date'])) $mailObject->RFCDateToSet = $message->headers['date'];
		if (isset($message->headers['return-path'])) $mailObject->Sender = $message->headers['return-path'];
		$mailObject->Subject = $message->headers['subject'];
		$mailObject->MessageID = $message->headers['message-id'];
		/* the client send garbage sometimes (blackberry->domain\username)
		// from
		$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($message->headers['from']):$message->headers['from']),'');
		foreach((array)$address_array as $addressObject) {
			if ($addressObject->host == '.SYNTAX-ERROR.') continue;
			if ($this->debugLevel>0) debugLog("Header Sentmail From: ".array2string($addressObject).' vs. '.array2string($message->headers['from']));
			$mailObject->From = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
			$mailObject->FromName = $addressObject->personal;
		}
		*/
		// prepare addressee list; moved the adding of addresses to the mailobject down
		// to
		$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($message->headers["to"]):$message->headers["to"]),'');
		foreach((array)$address_array as $addressObject) {
			if ($addressObject->host == '.SYNTAX-ERROR.') continue;
			if ($this->debugLevel>0) debugLog("Header Sentmail To: ".array2string($addressObject) );
			//$mailObject->AddAddress($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
			$toMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		// CC
		$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($message->headers["cc"]):$message->headers["cc"]),'');
		foreach((array)$address_array as $addressObject) {
			if ($addressObject->host == '.SYNTAX-ERROR.') continue;
			if ($this->debugLevel>0) debugLog("Header Sentmail CC: ".array2string($addressObject) );
			//$mailObject->AddCC($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
			$ccMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		// BCC
		$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($message->headers["bcc"]):$message->headers["bcc"]),'');
		foreach((array)$address_array as $addressObject) {
			if ($addressObject->host == '.SYNTAX-ERROR.') continue;
			if ($this->debugLevel>0) debugLog("Header Sentmail BCC: ".array2string($addressObject) );
			//$mailObject->AddBCC($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
			$bccMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		/*
		//	AddReplyTo
		$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($message->headers['reply-to']):$message->headers['reply-to']),'');
		foreach((array)$address_array as $addressObject) {
			if ($addressObject->host == '.SYNTAX-ERROR.') continue;
			if ($this->debugLevel>0) debugLog("Header Sentmail REPLY-TO: ".array2string($addressObject) );
			$mailObject->AddReplyTo($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
		}
		*/
		$addedfullname = false;
		// save some headers when forwarding mails (content type & transfer-encoding)
		$headers = "";

		$use_orgbody = false;

		// clean up the transmitted headers
		// remove default headers because we are using our own mailer
		//$returnPathSet = false;
		//$body_base64 = false;
		$org_charset = "";
		foreach($message->headers as $k => $v) {
			if ($k == "subject" ||
				$k == "to" || $k == "cc" || $k == "bcc" || $k == "sender" || $k == "reply-to" || $k == 'from' || $k == 'return_path' ||
				$k == "message-id" || $k == 'date')
                continue; // already set

				if ($this->debugLevel>0) debugLog("Header Sentmail original Header (filtered): " . $k.  " = ".trim($v));
				if ($k == "content-type") {
					// if the message is a multipart message, then we should use the sent body
					if (preg_match("/multipart/i", $v)) {
						$use_orgbody = true;
						$org_boundary = $message->ctype_parameters["boundary"];
					}

					// save the original content-type header for the body part when forwarding
					if ($smartdata['task'] == 'forward' && $smartdata['itemid'] && !$use_orgbody) {
						continue; // ignore
					}

					$org_charset = $v;
					$v = preg_replace("/charset=([A-Za-z0-9-\"']+)/", "charset=\"utf-8\"", $v);
				}

            if ($k == "content-transfer-encoding") {
/* we do not use this, as we determine that by ourself by forcing Encoding=base64 on smartreply/forward
				// if the content was base64 encoded, encode the body again when sending
				if (trim($v) == "base64") $body_base64 = true;

				// save the original encoding header for the body part when forwarding
				if ($smartdata['task'] == 'forward' && $smartdata['itemid']) {
					continue; // ignore
				}
*/
			}

			// if the message is a multipart message, then we should use the sent body
			if (($smartdata['task'] == 'new' || $smartdata['task'] == 'reply' || $smartdata['task'] == 'forward') &&
				((isset($smartdata['replacemime']) && $smartdata['replacemime'] == true) ||
				$k == "content-type" && preg_match("/multipart/i", $v))) {
				$use_orgbody = true;
			}

			// all other headers stay, we probably dont use them, but we may add them with AddHeader/AddCustomHeader
			//if ($headers) $headers .= "\n";
			//$headers .= ucfirst($k) . ": ". trim($v);
        }
		// if this is a simple message, no structure at all
		if ($message->ctype_primary=='text' && $message->body)
		{
			$mailObject->IsHTML($message->ctype_secondary=='html'?true:false);
			// we decode the body ourself
			$message->body = $this->mail->decodeMimePart($message->body,($message->headers['content-transfer-encoding']?$message->headers['content-transfer-encoding']:'base64'));
			$mailObject->Body = $body = $message->body;
			$simpleBodyType = ($message->ctype_secondary=='html'?'text/html':'text/plain');
			if ($this->debugLevel>0) debugLog("IMAP-Sendmail: fetched simple body as ".($message->ctype_secondary=='html'?'html':'text'));
		}
		//error_log(__METHOD__.__LINE__.array2string($mailObject));
		// if this is a multipart message with a boundary, we must use the original body
		$this->mail->createBodyFromStructure($mailObject, $message,NULL,$decode=true);
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' mailObject after Inital Parse:'.array2string($mailObject));
        if ($use_orgbody) {
    	    if ($this->debugLevel>0) debugLog("IMAP-Sendmail: use_orgbody = true");
            $repl_body = $body = $mailObject->Body;
			// if it is a ClientSideMeetingRequest, we report it as send at all times
			if ($mailObject->AltExtendedContentType && stripos($mailObject->AltExtendedContentType,'text/calendar') !== false )
			{
				if ($this->debugLevel>0) debugLog("IMAP-Sendmail: we have a Client Side Meeting Request");
				// try figuring out the METHOD -> [AltExtendedContentType] => text/calendar; name=meeting.ics; method=REQUEST
				$tA = explode(' ',$mailObject->AltExtendedContentType);
				foreach ((array)$tA as $k => $p) if (stripos($p,"method=")!==false) $cSMRMethod= trim(str_replace('METHOD=','',strtoupper($p)));
				$ClientSideMeetingRequest = true;
			}
        }
        else {
    	    if ($this->debugLevel>0) debugLog("IMAP-Sendmail: use_orgbody = false");
			$body = $mailObject->Body;
		}
		// now handle the addressee list
		$toCount = 0;
		//error_log(__METHOD__.__LINE__.array2string($toMailAddr));
		foreach((array)$toMailAddr as $address) {
			$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($address):$address),'');
			foreach((array)$address_array as $addressObject) {
				$emailAddress = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress,(isset($cSMRMethod)?$cSMRMethod:'REQUEST'))) continue;
				$mailObject->AddAddress($emailAddress, $addressObject->personal);
				$toCount++;
			}
		}
		$ccCount = 0;
		foreach((array)$ccMailAddr as $address) {
			$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($address):$address),'');
			foreach((array)$address_array as $addressObject) {
				$emailAddress = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress)) continue;
				$mailObject->AddCC($emailAddress, $addressObject->personal);
				$ccCount++;
			}
		}
		$bccCount = 0;
		foreach((array)$bccMailAddr as $address) {
			$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($address):$address),'');
			foreach((array)$address_array as $addressObject) {
				$emailAddress = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress)) continue;
				$mailObject->AddBCC($emailAddress, $addressObject->personal);
				$bccCount++;
			}
		}
		if ($toCount+$ccCount+$bccCount == 0) return 0; // noone to send mail to
		if ($ClientSideMeetingRequest === true && $allowSendingInvitations===false) return true;
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' MailAttachments:'.array2string($mailObject->GetAttachments()));
		// as we use our mailer (phpmailer) it is detecting / setting the mimetype by itself while creating the mail
		if (isset($smartdata['replacemime']) && $smartdata['replacemime'] == true &&
			isset($message->ctype_primary)) {
			//if ($headers) $headers .= "\n";
			//$headers .= "Content-Type: ". $message->ctype_primary . "/" . $message->ctype_secondary .
			//	(isset($message->ctype_parameters['boundary']) ? ";\n\tboundary=".$message->ctype_parameters['boundary'] : "");
		}
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' retrieved Body:'.$body);
		$body = str_replace("\r",($mailObject->ContentType=='text/html'?'<br>':""),$body); // what is this for?
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' retrieved Body (modified):'.$body);
		// reply ---------------------------------------------------------------------------
		if ($smartdata['task'] == 'reply' && isset($smartdata['itemid']) &&
			isset($smartdata['folderid']) && $smartdata['itemid'] && $smartdata['folderid'] &&
			(!isset($smartdata['replacemime']) ||
			(isset($smartdata['replacemime']) && $smartdata['replacemime'] == false)))
		{
			//remember Content type of org messageFolder
			$orgMessageContentType = $mailObject->ContentType;
			// now get on, and fetch the original mail
			$uid = $smartdata['itemid'];
			if ($this->debugLevel>0) debugLog("IMAP Smartreply is called with FolderID:".$smartdata['folderid'].' and ItemID:'.$smartdata['itemid']);
			$this->splitID($smartdata['folderid'], $account, $folder);

			$this->mail->reopen($folder);
			// not needed, as the original header is always transmitted
			/*
			$headers	= $this->mail->getMessageEnvelope($uid, $_partID, true, $folder);
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." Headers of Message with UID:$uid ->".array2string($headers));
			$body .= $this->mail->createHeaderInfoSection($headers,lang("original message"));
			*/
			$bodyStruct = $this->mail->getMessageBody($uid, 'html_only');

			$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$bodyBUFF);
		    if ($bodyBUFF != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
				// may be html
				if ($this->debugLevel>0) debugLog("MIME Body".' Type:html (fetched with html_only):'.$bodyBUFF);
				$mailObject->IsHTML(true);
			} else {
				// plain text Message
				if ($this->debugLevel>0) debugLog("MIME Body".' Type:plain, fetch text:');
				// if the new part of the message is html, we must preserve it, and handle that the original mail is text/plain
				if ($orgMessageContentType!='text/html') $mailObject->IsHTML(false);
				$bodyStruct = $this->mail->getMessageBody($uid,'never_display');//'never_display');
				$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);

				if ($this->debugLevel>0) debugLog("MIME Body ContentType ".$mailObject->ContentType);
				$bodyBUFF = ($mailObject->ContentType=='text/html'?'<pre>':'').$bodyBUFF.($mailObject->ContentType=='text/html'?'</pre>':'');
			}

			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Body -> '.$bodyBUFF);
			if (isset($simpleBodyType) && $simpleBodyType == 'text/plain' && $mailObject->ContentType == 'text/html') $body=nl2br($body); // this is (should be) the same as $orgMessageContentType == 'text/plain' && $mailObject->ContentType == 'text/html'
			// receive only body
			$body .= $bodyBUFF;
			$mailObject->Encoding = 'base64';
		}

		// how to forward and other prefs
		$preferencesArray =& $GLOBALS['egw_info']['user']['preferences']['mail'];

		// forward -------------------------------------------------------------------------
		if ($smartdata['task'] == 'forward' && isset($smartdata['itemid']) &&
			isset($smartdata['folderid']) && $smartdata['itemid'] && $smartdata['folderid'] &&
			(!isset($smartdata['replacemime']) ||
			(isset($smartdata['replacemime']) && $smartdata['replacemime'] == false)))
		{
			//force the default for the forwarding -> asmail
			if (is_array($preferencesArray)) {
				if (!array_key_exists('message_forwarding',$preferencesArray)
					|| !isset($preferencesArray['message_forwarding'])
					|| empty($preferencesArray['message_forwarding'])) $preferencesArray['message_forwarding'] = 'asmail';
			} else {
				$preferencesArray['message_forwarding'] = 'asmail';
			}
			// construct the uid of the message out of the itemid - seems to be the uid, no construction needed
			$uid = $smartdata['itemid'];
			if ($this->debugLevel>0) debugLog("IMAP Smartfordward is called with FolderID:".$smartdata['folderid'].' and ItemID:'.$smartdata['itemid']);
			$this->splitID($smartdata['folderid'], $account, $folder);

			$this->mail->reopen($folder);
            // receive entire mail (header + body)
			// get message headers for specified message
			$headers	= $this->mail->getMessageEnvelope($uid, $_partID, true, $folder);

            // build a new mime message, forward entire old mail as file
            if ($preferencesArray['message_forwarding'] == 'asmail')
			{
				$rawHeader='';
				$rawHeader      = $this->mail->getMessageRawHeader($smartdata['itemid'], $_partID);
				$rawBody        = $this->mail->getMessageRawBody($smartdata['itemid'], $_partID);
				$mailObject->AddStringAttachment($rawHeader.$rawBody, $mailObject->EncodeHeader($headers['SUBJECT']), '7bit', 'message/rfc822');
            }
            else
			{
/* ToDo - as it may double text
				// This is for forwarding and using the original body as Client may only include parts of the original mail
				if (!$use_orgbody)
					$nbody = $body;
				else
					$nbody = $repl_body;
*/
				//$body .= $this->mail->createHeaderInfoSection($headers,lang("original message"));
				$bodyStruct = $this->mail->getMessageBody($uid, 'html_only');
				$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$body);
				if ($bodyBUFF != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
					// may be html
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:html (fetched with html_only)');
					$mailObject->IsHTML(true);
				} else {
					// plain text Message
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:plain, fetch text:');
					// as we glue together the send mail part, and the smartforward part, we stick to the ContentType of the to be sent-Mail
					$mailObject->IsHTML($mailObject->ContentType=='text/html');
					$bodyStruct = $this->mail->getMessageBody($uid,'never_display');//'never_display');
					$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);

					if ($this->debugLevel>0) debugLog("MIME Body ContentType ".$mailObject->ContentType);
					$bodyBUFF = ($mailObject->ContentType=='text/html'?'<pre>':'').$bodyBUFF.($mailObject->ContentType=='text/html'?'</pre>':'');
				}
				if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Body -> '.$bodyBUFF);
				// receive only body
				$body .= $bodyBUFF;
				// get all the attachments and add them too.
				// start handle Attachments
				$attachments = $this->mail->getMessageAttachments($uid);
				$attachmentNames = false;
				if (is_array($attachments) && count($attachments)>0)
				{
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' gather Attachments for BodyCreation of/for MessageID:'.$uid.' found:'.count($attachments));
					foreach((array)$attachments as $key => $attachment)
					{
						if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attachment));
						$attachmentNames .= $attachment['name']."\n";
						switch($attachment['type'])
						{
							case 'MESSAGE/RFC822':
								$rawHeader = $rawBody = '';
								$rawHeader = $this->mail->getMessageRawHeader($uid, $attachment['partID']);
								$rawBody = $this->mail->getMessageRawBody($uid, $attachment['partID']);
								$mailObject->AddStringAttachment($rawHeader.$rawBody, $mailObject->EncodeHeader($attachment['name']), '7bit', 'message/rfc822');
								break;
							default:
								$attachmentData = '';
								$attachmentData	= $this->mail->getAttachment($uid, $attachment['partID']);
								$mailObject->AddStringAttachment($attachmentData['attachment'], $mailObject->EncodeHeader($attachment['name']), 'base64', $attachment['mimeType']);
								break;
						}
					}
				}
            }
			if (isset($simpleBodyType) && $simpleBodyType == 'text/plain' && $mailObject->ContentType == 'text/html') $body=nl2br($body);
			$mailObject->Encoding = 'base64';
		} // end forward

		// add signature!! -----------------------------------------------------------------
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' ActiveMailProfile:'.array2string($activeMailProfile));
		try
		{
			$acc = emailadmin_account::read($this->mail->icServer->ImapServerId);
			//error_log(__METHOD__.__LINE__.array2string($acc));
			$_signature = emailadmin_account::read_identity($acc['ident_id'],true);
		}
		catch (Exception $e)
		{
			$_signature=array();
		}
		$signature = $_signature['ident_signature'];
		if ((isset($preferencesArray['disableRulerForSignatureSeparation']) &&
			$preferencesArray['disableRulerForSignatureSeparation']) ||
			empty($signature) || trim(translation::convertHTMLToText($signature)) =='')
		{
			$disableRuler = true;
		}
		$before = "";
		if ($disableRuler==false)
		{
			if($mailObject->ContentType=='text/html') {
				$before = ($disableRuler ?'&nbsp;<br>':'&nbsp;<br><hr style="border:dotted 1px silver; width:90%; border:dotted 1px silver;">');
			} else {
				$before = ($disableRuler ?"\r\n\r\n":"\r\n\r\n-- \r\n");
			}
		}
		$sigText = $this->mail->merge($signature,array($GLOBALS['egw']->accounts->id2name($GLOBALS['egw_info']['user']['account_id'],'person_id')));
		if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Signature to use:'.$sigText);
		$body .= $before.($mailObject->ContentType=='text/html'?$sigText:translation::convertHTMLToText($sigText));
		//debugLog(__METHOD__.__LINE__.' -> '.$body);
		// remove carriage-returns from body, set the body of the mailObject
		if (trim($body) =='' && trim($mailObject->Body)==''/* && $attachmentNames*/) $body .= ($attachmentNames?$attachmentNames:lang('no text body supplied, check attachments for message text')); // to avoid failure on empty messages with attachments
		//debugLog(__METHOD__.__LINE__.' -> '.$body);
		$mailObject->Body = $body ;//= str_replace("\r\n", "\n", $body); // if there is a <pre> this needs \r\n so DO NOT replace them
		if ($mailObject->ContentType=='text/html') $mailObject->AltBody = translation::convertHTMLToText($body);

        //advanced debugging
		if (strtolower($mailObject->CharSet) != 'utf-8')
		{
			debugLog(__METHOD__.__LINE__.' POSSIBLE PROBLEM: CharSet was changed during processing of the Body from:'.$mailObject->CharSet.'. Force back to UTF-8 now.');
			$mailObject->CharSet = 'utf-8';
		}
        //debugLog("IMAP-SendMail: parsed message: ". print_r($message,1));
		#_debug_array($ogServer);
		$mailObject->Host 	= $this->mail->ogServer->host;
		$mailObject->Port	= $this->mail->ogServer->port;
		// SMTP Auth??
		if($this->mail->ogServer->smtpAuth) {
			$mailObject->SMTPAuth	= true;
			// check if username contains a ; -> then a sender is specified (and probably needed)
			list($username,$senderadress) = explode(';', $this->mail->ogServer->username,2);
			if (isset($senderadress) && !empty($senderadress)) $mailObject->Sender = $senderadress;
			$mailObject->Username = $username;
			$mailObject->Password	= $this->mail->ogServer->password;
		}
		if ($this->debugLevel>2) debugLog("IMAP-SendMail: MailObject:".array2string($mailObject));
		if ($this->debugLevel>0 && $this->debugLevel<=2)
		{
			debugLog("IMAP-SendMail: MailObject (short):".array2string(array('host'=>$mailObject->Host,
				'port'=>$mailObject->Port,
				'username'=>$mailObject->Username,
				'subject'=>$mailObject->Subject,
				'CharSet'=>$mailObject->CharSet,
				'Priority'=>$mailObject->Priority,
				'Encoding'=>$mailObject->Encoding,
				'ContentType'=>$mailObject->ContentType,
			)));
		}
   	    if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' MailAttachments:'.array2string($mailObject->GetAttachments()));

		// set a higher timeout for big messages
		@set_time_limit(120);

		// send
		$send = true;
		try {
			$mailObject->Send();
		}
		catch(phpmailerException $e) {
			debugLog("The email could not be sent. Last-SMTP-error: ". $e->getMessage());
			debugLog("IMAP-SendMail: MailObject (short):".array2string(array('host'=>$mailObject->Host,
				'port'=>$mailObject->Port,
				'username'=>$mailObject->Username,
				'subject'=>$mailObject->Subject,
				'CharSet'=>$mailObject->CharSet,
				'Priority'=>$mailObject->Priority,
				'Encoding'=>$mailObject->Encoding,
				'ContentType'=>$mailObject->ContentType,
			)));
			$send = false;
		}

		if (( $smartdata['task'] == 'reply' || $smartdata['task'] == 'forward') && $send == true)
		{
			$uid = $smartdata['itemid'];
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' tASK:'.$smartdata['task']." FolderID:".$smartdata['folderid'].' and ItemID:'.$smartdata['itemid']);
			$this->splitID($smartdata['folderid'], $account, $folder);
			//error_log(__METHOD__.__LINE__.' Folder:'.$folder.' Uid:'.$uid);
			$this->mail->reopen($folder);
			// if the draft folder is a starting part of the messages folder, the draft message will be deleted after the send
			// unless your templatefolder is a subfolder of your draftfolder, and the message is in there
			if ($this->mail->isDraftFolder($folder) && !$this->mail->isTemplateFolder($folder))
			{
				$this->mail->deleteMessages(array($uid),$folder);
			} else {
				$this->mail->flagMessages("answered", array($uid),$folder);
				if ($smartdata['task']== "forward")
				{
					$this->mail->flagMessages("forwarded", array($uid),$folder);
				}
			}
		}

		$asf = ($send ? true:false); // initalize accordingly
		if (($smartdata['saveinsentitems']==1 || !isset($smartdata['saveinsentitems'])) && $send==true && $this->mail->mailPreferences['sendOptions'] != 'send_only')
		{
			$asf = false;
			$sentFolder = $this->mail->getSentFolder();
			if ($this->_sentID) {
				$folderArray[] = $this->_sentID;
			}
			else if(isset($sentFolder) && $sentFolder != 'none')
			{
				$folderArray[] = $sentFolder;
			}
			// No Sent folder set, try defaults
			else
			{
				debugLog("IMAP-SendMail: No Sent mailbox set");
				// we dont try guessing
				$asf = true;
			}
			if (count($folderArray) > 0) {
				foreach((array)$bccMailAddr as $address) {
					$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($address):$address),'');
					foreach((array)$address_array as $addressObject) {
						$emailAddress = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
						$mailAddr[] = array($emailAddress, $addressObject->personal);
					}
				}
				$BCCmail='';
				if (count($mailAddr)>0) $BCCmail = $mailObject->AddrAppend("Bcc",$mailAddr);
				foreach($folderArray as $folderName) {
					if($this->mail->isSentFolder($folderName)) {
						$flags = '\\Seen';
					} elseif($this->mail->isDraftFolder($folderName)) {
						$flags = '\\Draft';
					} else {
						$flags = '';
					}
					$asf = true;
					//debugLog(__METHOD__.__LINE__.'->'.array2string($this->mail->icServer));
					$this->mail->openConnection(self::$profileID,false);
					if ($this->mail->folderExists($folderName)) {
						try
						{
							$this->mail->appendMessage($folderName,
									$BCCmail.$mailObject->getMessageHeader(),
									$mailObject->getMessageBody(),
									$flags);
						}
						catch (egw_exception_wrong_userinput $e)
						{
							//$asf = false;
							debugLog(__METHOD__.__LINE__.'->'.lang("Import of message %1 failed. Could not save message to folder %2 due to: %3",$mailObject->Subject,$folderName,$e->getMessage()));
						}
					}
					else
					{
						//$asf = false;
						debugLog(__METHOD__.__LINE__.'->'.lang("Import of message %1 failed. Destination Folder %2 does not exist.",$mailObject->Subject,$folderName));
					}
			        debugLog("IMAP-SendMail: Outgoing mail saved in configured 'Sent' folder '".$folderName."': ". (($asf)?"success":"failed"));
				}
				//$this->mail->closeConnection();
			}
		}
        	// unset mimedecoder - free memory
		unset($message);
        unset($mobj);

		//$this->debugLevel=0;

		if ($send && $asf)
		{
			return true;
		}
		else
		{
			debugLog(__METHOD__." returning ".($ClientSideMeetingRequest ? true : 120)." (MailSubmissionFailed)".($ClientSideMeetingRequest ?" is ClientSideMeetingRequest (we ignore the failure)":""));
			return ($ClientSideMeetingRequest ? true : 120);   //MAIL Submission failed, see MS-ASCMD
		}

	}

	/**
	 *
	 * For meeting requests (iCal attachments with method='request') we call calendar plugin with iCal to get SyncMeetingRequest object,
	 * and do NOT return the attachment itself!
	 *
	 * @see activesync_plugin_read::GetMessage()
	 */
	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $optionbodypreference=false, $mimesupport = 0)
	{
		//$this->debugLevel=4;
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID);
		debugLog(__METHOD__.__LINE__.' FolderID:'.$folderid.' ID:'.$id.' TruncSize:'.$truncsize.' Bodypreference: '.array2string($bodypreference));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		$stat = $this->StatMessage($folderid, $id);
		if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($stat));
		// StatMessage should reopen the folder in question, so we dont need folderids in the following statements.
		if ($stat)
		{
			debugLog(__METHOD__.__LINE__." Message $id with stat ".array2string($stat));
			// initialize the object
			$output = new SyncMail();
			$headers = $this->mail->getMessageHeader($id,'',true,true,$_folderName);
			if (empty($headers))
			{
				error_log(__METHOD__.__LINE__.' Retrieval of Headers Failed! for .'.$this->account.'/'.$GLOBALS['egw_info']['user']['account_lid'].' ServerID:'.self::$profileID.'FolderID:'.$folderid.'/'.$_folderName.' ID:'.$id.' TruncSize:'.$truncsize.' Bodypreference: '.array2string($bodypreference).' Stat was:'.array2string($stat));
				return $output;//empty object
			}
			//$rawHeaders = $this->mail->getMessageRawHeader($id);
			// simple style
			// start AS12 Stuff (bodypreference === false) case = old behaviour
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__. ' for message with ID:'.$id.' with headers:'.array2string($headers));
			if ($bodypreference === false) {
				$bodyStruct = $this->mail->getMessageBody($id, 'only_if_no_text', '', null, true);
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);
				//$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				if (stripos($body,'<style')!==false) $body = preg_replace("/<style.*?<\/style>/is", "", $body); // in case there is only a html part
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
				$css ='';
				$bodyStruct = $this->mail->getMessageBody($id, 'html_only', '', null, true);
				if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' html_only Struct:'.array2string($bodyStruct));
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);//$this->ui->getdisplayableBody($bodyStruct,false);
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$body);
			    if ($body != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
					// may be html
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:html (fetched with html_only)');
					$css = $this->mail->getStyles($bodyStruct);
					$output->airsyncbasenativebodytype=2;
				} else {
					// plain text Message
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:plain, fetch text (HTML, if no text available)');
					$output->airsyncbasenativebodytype=1;
					$bodyStruct = $this->mail->getMessageBody($id,'never_display', '', null, true); //'only_if_no_text');
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' plain text Struct:'.array2string($bodyStruct));
					$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' never display html(plain text only):'.$body);
				}
				// whatever format decode (using the correct encoding)
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__."MIME Body".' Type:'.($output->airsyncbasenativebodytype==2?' html ':' plain ').$body);
				//$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				// prepare plaintextbody
				if ($output->airsyncbasenativebodytype == 2)
				{
					$bodyStructplain = $this->mail->getMessageBody($id,'never_display', '', null, true); //'only_if_no_text');
					if($bodyStructplain[0]['error']==1)
					{
						$plainBody = translation::convertHTMLToText($body,true); // always display with preserved HTML
					}
					else
					{
						$plainBody = $this->mail->getdisplayableBody($this->mail,$bodyStructplain);//$this->ui->getdisplayableBody($bodyStruct,false);
					}
				}
				//if ($this->debugLevel>0) debugLog("MIME Body".$body);
				$plainBody = preg_replace("/<style.*?<\/style>/is", "", (strlen($plainBody)?$plainBody:$body));
				// remove all other html
				$plainBody = preg_replace("/<br.*>/is","\r\n",$plainBody);
				$plainBody = strip_tags($plainBody);
				if ($this->debugLevel>3 && $output->airsyncbasenativebodytype==1) debugLog(__METHOD__.__LINE__.' Plain Text:'.$plainBody);
				//$body = str_replace("\n","\r\n", str_replace("\r","",$body)); // do we need that?
				if (isset($bodypreference[4]) && ($mimesupport==2 || ($mimesupport ==1 && stristr($headers['CONTENT-TYPE'],'signed') !== false)))
				{
					debugLog(__METHOD__.__LINE__." bodypreference 4 requested");
					$output->airsyncbasebody->type = 4;
					//$rawBody = $this->mail->getMessageRawBody($id);
					$mailObject = new egw_mailer();
					// this try catch block is probably of no use anymore, as it was intended to catch exceptions thrown by parseRawMessageIntoMailObject
					try
					{
						// we create a complete new rfc822 message here to pass a clean one to the client.
						// this strips a lot of information, but ...
						$Header = $Body = '';
						if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." Creation of Mailobject.");
						//if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__." Using data from ".$rawHeaders.$rawBody);
						//$this->mail->parseRawMessageIntoMailObject($mailObject,$rawHeaders.$rawBody,$Header,$Body);
						//debugLog(__METHOD__.__LINE__.array2string($headers));
						// now force UTF-8
						$mailObject->IsSMTP(); // needed to ensure the to part of the Header is Created too, when CreatingHeader
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
							//debugLog(__METHOD__.__LINE__.'Address to add (FROM):'.array2string($addressObject));
							if ($addressObject->host == '.SYNTAX-ERROR.') continue;
							$mailObject->From = $addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : '');
							$mailObject->FromName = $addressObject->personal;
//error_log(__METHOD__.__LINE__.'Address to add (FROM):'.array2string($addressObject));
						}
						// to
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['TO']):$headers['TO']),'');
						foreach((array)$address_array as $addressObject) {
							//debugLog(__METHOD__.__LINE__.'Address to add (TO):'.array2string($addressObject));
							if ($addressObject->host == '.SYNTAX-ERROR.') continue;
							$mailObject->AddAddress($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
						}
						// CC
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['CC']):$headers['CC']),'');
						foreach((array)$address_array as $addressObject) {
							//debugLog(__METHOD__.__LINE__.'Address to add (CC):'.array2string($addressObject));
							if ($addressObject->host == '.SYNTAX-ERROR.') continue;
							$mailObject->AddCC($addressObject->mailbox. (!empty($addressObject->host) ? '@'.$addressObject->host : ''),$addressObject->personal);
						}
						//	AddReplyTo
						$address_array  = imap_rfc822_parse_adrlist((get_magic_quotes_gpc()?stripslashes($headers['REPLY-TO']):$headers['REPLY-TO']),'');
						foreach((array)$address_array as $addressObject) {
							//debugLog(__METHOD__.__LINE__.'Address to add (ReplyTO):'.array2string($addressObject));
							if ($addressObject->host == '.SYNTAX-ERROR.') continue;
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

					if ($this->debugLevel>0) debugLog("MIME Body -> ".$body); // body is retrieved up
					if ($output->airsyncbasenativebodytype==2) { //html
						if ($this->debugLevel>0) debugLog("HTML Body with requested pref 4");
						$mailObject->IsHTML(true);
						$html = '<html>'.
	    					    '<head>'.
						        '<meta name="Generator" content="Z-Push">'.
						        '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
								$css.
							    '</head>'.
							    '<body>'.
						        str_replace("\n","<BR>",str_replace("\r","", str_replace("\r\n","<BR>",$body))).
							    '</body>'.
								'</html>';
						$mailObject->Body = str_replace("\n","\r\n", str_replace("\r","",$html));
						if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__." MIME Body (constructed)-> ".$mailObject->Body);
						$mailObject->AltBody = empty($plainBody)?strip_tags($body):$plainBody;
					}
					if ($output->airsyncbasenativebodytype==1) { //plain
						if ($this->debugLevel>0) debugLog("Plain Body with requested pref 4");
						$mailObject->IsHTML(false);
						$mailObject->Body = $plainBody;
						$mailObject->AltBody = '';
					}
					// we still need the attachments to be added ( if there are any )
					// start handle Attachments
					$attachments = $this->mail->getMessageAttachments($id);
					if (is_array($attachments) && count($attachments)>0)
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
										$rawHeader = $this->mail->getMessageRawHeader($id, $attachment['partID']);
									}
									$rawBody = $this->mail->getMessageRawBody($id, $attachment['partID']);
									$mailObject->AddStringAttachment($rawHeader.$rawBody, $mailObject->EncodeHeader($attachment['name']), '7bit', 'message/rfc822');
									break;
								default:
									$attachmentData = '';
									$attachmentData	= $this->mail->getAttachment($id, $attachment['partID']);
									$mailObject->AddStringAttachment($attachmentData['attachment'], $mailObject->EncodeHeader($attachment['name']), 'base64', $attachment['mimeType']);
									break;
							}
						}
					}

					$mailObject->SetMessageType();
					$Header = $mailObject->CreateHeader();
					//debugLog(__METHOD__.__LINE__.' MailObject-Header:'.array2string($Header));
					$Body = trim($mailObject->CreateBody()); // philip thinks this is needed, so lets try if it does any good/harm
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' MailObject:'.array2string($mailObject));
					if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__." Setting Mailobjectcontent to output:".$Header.$mailObject->LE.$mailObject->LE.$Body);
					$output->airsyncbasebody->data = $Header.$mailObject->LE.$mailObject->LE.$Body;
				}
				else if (isset($bodypreference[2]))
				{
					if ($this->debugLevel>0) debugLog("HTML Body with requested pref 2");
					// Send HTML if requested and native type was html
					$output->airsyncbasebody->type = 2;
					$htmlbody = '<html>'.
						'<head>'.
						'<meta name="Generator" content="Z-Push">'.
						'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
						$css.
						'</head>'.
						'<body>';
					if ($output->airsyncbasenativebodytype==2)
					{
						// as we fetch html, and body is HTML, we may not need to handle this
						$htmlbody .= $body;
					}
					else
					{
						// html requested but got only plaintext, so fake html
						$htmlbody .= str_replace("\n","<BR>",str_replace("\r","<BR>", str_replace("\r\n","<BR>",$plainBody)));
					}
					$htmlbody .= '</body>'.
							'</html>';

					if(isset($bodypreference[2]["TruncationSize"]) && strlen($html) > $bodypreference[2]["TruncationSize"])
					{
						$htmlbody = utf8_truncate($htmlbody,$bodypreference[2]["TruncationSize"]);
						$output->airsyncbasebody->truncated = 1;
					}
					$output->airsyncbasebody->data = $htmlbody;
				}
				else
				{
					// Send Plaintext as Fallback or if original body is plainttext
					if ($this->debugLevel>0) debugLog("Plaintext Body:".$plainBody);
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
			// $output->to = $this->messages[$id]['to_address']; //$stat['FETCHED_HEADER']['to_name']
			// $output->from = $this->messages[$id]['sender_address']; //$stat['FETCHED_HEADER']['sender_name']
//error_log(__METHOD__.__LINE__.' To:'.$headers['TO']);
			$output->to = $headers['TO'];
//error_log(__METHOD__.__LINE__.' From:'.$headers['FROM']);
			$output->from = $headers['FROM'];
			$output->cc = ($headers['CC'] ? $headers['CC']:null);
			$output->reply_to = ($headers['REPLY_TO']?$headers['REPLY_TO']:null);
			$output->messageclass = "IPM.Note";
			if (stripos($this->messages[$id]['mimetype'],'multipart')!== false &&
				stripos($this->messages[$id]['mimetype'],'signed')!== false)
			{
				$output->messageclass = "IPM.Note.SMIME.MultipartSigned";
			}
			// start AS12 Stuff
			//$output->poommailflag = new SyncPoommailFlag();

			if ($this->messages[$id]['flagged'] == 1)
			{
				$output->poommailflag = new SyncPoommailFlag();
				$output->poommailflag->flagstatus = 2;
				$output->poommailflag->flagtype = "Flag for Follow up";
			}

			$output->internetcpid = 65001;
			$output->contentclass="urn:content-classes:message";
			// end AS12 Stuff

			// start handle Attachments (include text/calendar multiplar alternative)
			$attachments = $this->mail->getMessageAttachments($id, $_partID='', $_structure=null, $fetchEmbeddedImages=true, $fetchTextCalendar=true);
			if (is_array($attachments) && count($attachments)>0)
			{
				debugLog(__METHOD__.__LINE__.' gather Attachments for MessageID:'.$id.' found:'.count($attachments));
				foreach ($attachments as $key => $attach)
				{
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attach));

					// pass meeting requests to calendar plugin
					if (strtolower($attach['mimeType']) == 'text/calendar' && strtolower($attach['method']) == 'request' &&
						isset($GLOBALS['egw_info']['user']['apps']['calendar']) &&
						($attachment = $this->mail->getAttachment($id, $attach['partID'])) &&
						($output->meetingrequest = calendar_activesync::meetingRequest($attachment['attachment'])))
					{
						$output->messageclass = "IPM.Schedule.Meeting.Request";
						continue;	// do NOT add attachment as attachment
					}
					if(isset($output->_mapping['POOMMAIL:Attachments'])) {
						$attachment = new SyncAttachment();
					} else if(isset($output->_mapping['AirSyncBase:Attachments'])) {
						$attachment = new SyncAirSyncBaseAttachment();
					}
					$attachment->attsize = $attach['size'];
					$attachment->displayname = $attach['name'];
					$attachment->attname = $folderid . ":" . $id . ":" . $attach['partID'];//$key;
					//error_log(__METHOD__.__LINE__.'->'.$folderid . ":" . $id . ":" . $attach['partID']);
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
			//$this->debugLevel=0;
			// end handle Attachments
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($output));
			return $output;
		}
		return false;
	}

	/**
	 * Process response to meeting request
	 *
	 * mail plugin only extracts the iCal attachment and let's calendar plugin deal with adding it
	 *
	 * @see BackendDiff::MeetingResponse()
	 * @param string $folderid folder of meeting request mail
	 * @param int|string $requestid uid of mail with meeting request
	 * @param int $response 1=accepted, 2=tentative, 3=decline
	 * @return int|boolean id of calendar item, false on error
	 */
	function MeetingResponse($folderid, $requestid, $response)
	{
		if (!class_exists('calendar_activesync'))
		{
			debugLog(__METHOD__."(...) no EGroupware calendar installed!");
			return null;
		}
		if (!($stat = $this->StatMessage($folderid, $requestid)))
		{
			debugLog(__METHOD__."($requestid, '$folderid', $response) returning FALSE (can NOT stat message)");
			return false;
		}
		$ret = false;
		foreach($this->mail->getMessageAttachments($requestid, $_partID='', $_structure=null, $fetchEmbeddedImages=true, $fetchTextCalendar=true) as $key => $attach)
		{
			if (strtolower($attach['mimeType']) == 'text/calendar' && strtolower($attach['method']) == 'request' &&
				($attachment = $this->mail->getAttachment($requestid, $attach['partID'])))
			{
				debugLog(__METHOD__."($requestid, '$folderid', $response) iCal found, calling now backend->MeetingResponse('$attachment[attachment]')");

				// calling backend again with iCal attachment, to let calendar add the event
				if (($ret = $this->backend->MeetingResponse($attachment['attachment'],
					$this->backend->createID('calendar',$GLOBALS['egw_info']['user']['account_id']),
					$response, $calendarid)))
				{
					$ret = $calendarid;
				}
				break;
			}
		}
		debugLog(__METHOD__."($requestid, '$folderid', $response) returning ".array2string($ret));
		return $ret;
	}

	/**
	 * GetAttachmentData
	 * Should return attachment data for the specified attachment. The passed attachment identifier is
	 * the exact string that is returned in the 'AttName' property of an SyncAttachment. So, you should
	 * encode any information you need to find the attachment in that 'attname' property.
	 *
     * @param string $fid - id
     * @param string $attname - should contain (folder)id
	 * @return true, prints the content of the attachment
	 */
	function GetAttachmentData($fid,$attname) {
		debugLog("getAttachmentData: (attname: '$attname')");

		list($folderid, $id, $part) = explode(":", $attname);

		$this->splitID($folderid, $account, $folder);

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID);

		$this->mail->reopen($folder);
		$attachment = $this->mail->getAttachment($id,$part);
		print $attachment['attachment'];
		unset($attachment);
		return true;
	}

	/**
	 * ItemOperationsGetAttachmentData
	 * Should return attachment data for the specified attachment. The passed attachment identifier is
	 * the exact string that is returned in the 'AttName' property of an SyncAttachment. So, you should
	 * encode any information you need to find the attachment in that 'attname' property.
	 *
     * @param string $fid - id
     * @param string $attname - should contain (folder)id
	 * @return SyncAirSyncBaseFileAttachment-object
	 */
	function ItemOperationsGetAttachmentData($fid,$attname) {
		debugLog("ItemOperationsGetAttachmentData: (attname: '$attname')");

		list($folderid, $id, $part) = explode(":", $attname);

		$this->splitID($folderid, $account, $folder);

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false, self::$profileID);

		$this->mail->reopen($folder);
		$att = $this->mail->getAttachment($id,$part);
		$attachment = new SyncAirSyncBaseFileAttachment();
		/*
		debugLog(__METHOD__.__LINE__.array2string($att));
		if ($arr['filename']=='error.txt' && stripos($arr['attachment'], 'mail_bo::getAttachment failed') !== false)
		{
			return $attachment;
		}
		*/
		if (is_array($att)) {
			$attachment->_data = $att['attachment'];
			$attachment->contenttype = trim($att['type']);
		}
		unset($att);
		return $attachment;
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
		//debugLog (__METHOD__."('$folderid','$id') returning ".array2string($stat));
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
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID);
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
		debugLog (__METHOD__.' for Folder:'.$folderid.' SINCE:'.$cutdate.'/'.date("d-M-Y", $cutdate));
		if (empty($cutdate))
		{
			$cutdate = egw_time::to('now','ts')-(3600*24*28*3);
			debugLog(__METHOD__.' Client set no truncationdate. Using 12 weeks.'.date("d-M-Y", $cutdate));
		}
		return $this->fetchMessages($folderid, $cutdate);
	}

	private function fetchMessages($folderid, $cutoffdate=NULL, $_id=NULL)
	{
		if ($this->debugLevel>1) $gstarttime = microtime (true);
		//debugLog(__METHOD__.__LINE__);
		$rv_messages = array();
		// if the message is still available within the class, we use it instead of fetching it again
		if (is_array($_id) && count($_id)==1 && is_array($this->messages) && isset($this->messages[$_id[0]]) && is_array($this->messages[$_id[0]]))
		{
			//debugLog(__METHOD__.__LINE__." the message ".$_id[0]." is still available within the class, we use it instead of fetching it again");
			$rv_messages = array('header'=>array($this->messages[$_id[0]]));
		}
		if (empty($rv_messages))
		{
			if ($this->debugLevel>1) $starttime = microtime (true);
			$this->_connect($this->account);
			if ($this->debugLevel>1)
			{
				$endtime = microtime(true) - $starttime;
				debugLog(__METHOD__. " connect took : ".$endtime.' for account:'.$this->account);
			}
			$messagelist = array();
			// if not connected, any further action must fail
			if (!empty($cutoffdate)) $_filter = array('status'=>array('UNDELETED'),'type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
			if ($this->debugLevel>1) $starttime = microtime (true);
			$rv = $this->splitID($folderid,$account,$_folderName,$id);
			if ($this->debugLevel>1)
			{
				$endtime = microtime(true) - $starttime;
				debugLog(__METHOD__. " splitID took : ".$endtime.' for FolderID:'.$folderid);
			}
			if ($this->debugLevel>1) debugLog(__METHOD__.' for Folder:'.$_folderName.' Filter:'.array2string($_filter).' Ids:'.array2string($_id).'/'.$id);
			if ($this->debugLevel>1) $starttime = microtime (true);
			$_numberOfMessages = (empty($cutoffdate)?250:99999);
			$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=1, $_numberOfMessages, $_sort=0, $_reverse=false, $_filter, $_id);
			if ($this->debugLevel>1)
			{
				$endtime = microtime(true) - $starttime;
				debugLog(__METHOD__. " getHeaders call took : ".$endtime.' for FolderID:'.$_folderName);
			}
		}
		if ($_id == NULL && $this->debugLevel>1)  debugLog(__METHOD__." found :". count($rv_messages['header']));
		//debugLog(__METHOD__.__LINE__.' Result:'.array2string($rv_messages));
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
			if($vars["flagged"]) $mess["olflags"] = 2;
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($mess));
			$messagelist[$vars['uid']] = $mess;
			unset($mess);
		}

		if ($this->debugLevel>1)
		{
			$endtime = microtime(true) - $gstarttime;
			debugLog(__METHOD__. " total time used : ".$endtime.' for Folder:'.$_folderName.' Filter:'.array2string($_filter).' Ids:'.array2string($_id).'/'.$id);
		}
		return $messagelist;
	}

	/**
	 * Search mailbox for a given pattern
	 *
	 * @param string $searchquery
	 * @return array with just rows (no values for keys rows, status or global_search_status!)
	 */
	public function getSearchResultsMailbox($searchquery)
	{
		if (!is_array($searchquery)) return array();
		if ($this->debugLevel>0); debugLog(__METHOD__.__LINE__.array2string($searchquery));
		// 19.10.2011 16:28:59 [24502] mail_activesync::getSearchResultsMailbox1408
		//Array(
		//	[query] => Array(
		//		[0] => Array([op] => Search:And
		//			[value] => Array(
		//				[FolderType] => Email
		//				[FolderId] => 101000000000
		//				[Search:FreeText] => ttt
		//				[subquery] => Array(
		//					[0] => Array([op] => Search:GreaterThan
		//						[value] => Array(
		//							[POOMMAIL:DateReceived] => 1318975200))
		//					[1] => Array([op] => Search:LessThan
		//						[value] => Array(
		//							[POOMMAIL:DateReceived] => 1319034600))))))
		//	[rebuildresults] => 1
		//	[deeptraversal] =>
		//	[range] => 0-999)
		if (isset($searchquery['rebuildresults'])) {
			$rebuildresults = $searchquery['rebuildresults'];
		} else {
			$rebuildresults = false;
		}
		if ($this->debugLevel>0) debugLog( 'RebuildResults ['.$rebuildresults.']' );

		if (isset($searchquery['deeptraversal'])) {
			$deeptraversal = $searchquery['deeptraversal'];
		} else {
			$deeptraversal = false;
		}
		if ($this->debugLevel>0) debugLog( 'DeepTraversal ['.$deeptraversal.']' );

		if (isset($searchquery['range'])) {
			$range = explode("-",$searchquery['range']);
			$limit = $range[1] - $range[0] + 1;
		} else {
			$range = false;
		}
		if ($this->debugLevel>0) debugLog( 'Range ['.print_r($range, true).']' );

		//foreach($searchquery['query'] as $k => $value) {
		//	$query = $value;
		//}
		if (isset($searchquery['query'][0]['value']['FolderId'])) $folderid = $searchquery['query'][0]['value']['FolderId'];
		// other types may be possible - we support quicksearch first (freeText in subject and from (or TO in Sent Folder))
		if (isset($searchquery['query'][0]['value']['Search:FreeText']))
		{
			$type = 'quick';
			$searchText = $searchquery['query'][0]['value']['Search:FreeText'];
		}
		if (!$folderid)
		{
			$_folderName = ($this->mail->sessionData['mailbox']?$this->mail->sessionData['mailbox']:'INBOX');
			$folderid = $this->createID($account=0,$_folderName);
		}
//if ($searchquery['query'][0]['value'][subquery][0][op]=='Search:GreaterThan');
//if (isset($searchquery['query'][0]['value'][subquery][0][value][POOMMAIL:DateReceived]));
//if ($searchquery['query'][0]['value'][subquery][1][op]=='Search:LessThan');
//if (isset($searchquery['query'][0]['value'][subquery][1][value][POOMMAIL:DateReceived]));
//$_filter = array('status'=>array('UNDELETED'),'type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		$_filter = array('type'=> 'quick',
						 'string'=> $searchText,
						 'status'=>'any',
						);

		//$_filter[] = array('type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		if ($this->debugLevel>1) debugLog (__METHOD__.' for Folder:'.$_folderName.' Filter:'.array2string($_filter));
		$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=1, $_numberOfMessages=($limit?$limit:9999999), $_sort=0, $_reverse=false, $_filter, $_id=NULL);
		//debugLog(__METHOD__.__LINE__.array2string($rv_messages));
		$list=array();
		foreach((array)$rv_messages['header'] as $i => $vars)
		{
			$list[] = array(
				"uniqueid" => $folderid.':'.$vars['uid'],
				"item"	=> $vars['uid'],
				//"parent" => ???,
				"searchfolderid" => $folderid,
			);
		}
		debugLog(__METHOD__.__LINE__.array2string($list));
		return $list;//array();
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

		$mailFolder = $this->folders[$folder];
		if (!isset($mailFolder)) return false;
		$delimiter = (isset($mailFolder->delimiter)?$mailFolder->delimiter:$this->mail->getHierarchyDelimiter());
		$parent = explode($delimiter,$folder);
		array_pop($parent);
		$parent = implode($delimiter,$parent);

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
			debugLog(__METHOD__.__LINE__.' failed for '.$e->getMessage());
			return $folderObj=false;
		}
		$this->_connect($account);
		if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

		$mailFolder = $this->folders[$folder];
		if (!isset($mailFolder)) return $folderObj=false;

		$folderObj = new SyncFolder();
		$folderObj->serverid = $id;
		$folderObj->parentid = $this->getParentID($account,$folder);
		$folderObj->displayname = $mailFolder->shortDisplayName;
		if ($this->debugLevel>1) debugLog(__METHOD__.__LINE__." ID: $id, Account:$account, Folder:$folder");
		// get folder-type
		foreach($this->folders as $inbox => $mailFolder) break;
		if ($folder == $inbox)
		{
			$folderObj->type = SYNC_FOLDER_TYPE_INBOX;
		}
		elseif($this->mail->isDraftFolder($folder, false))
		{
			//debugLog(__METHOD__.' isDraft');
			$folderObj->type = SYNC_FOLDER_TYPE_DRAFTS;
			$folderObj->parentid = 0; // required by devices
		}
		elseif($this->mail->isTrashFolder($folder, false))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_WASTEBASKET;
			$this->_wasteID = $folder;
			//error_log(__METHOD__.__LINE__.' TrashFolder:'.$this->_wasteID);
			$folderObj->parentid = 0; // required by devices
		}
		elseif($this->mail->isSentFolder($folder, false))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_SENTMAIL;
			$folderObj->parentid = 0; // required by devices
			$this->_sentID = $folder;
			//error_log(__METHOD__.__LINE__.' SentFolder:'.$this->_sentID);
		}
		elseif($this->mail->isOutbox($folder, false))
		{
			//debugLog(__METHOD__.' isOutbox');
			$folderObj->type = SYNC_FOLDER_TYPE_OUTBOX;
			$folderObj->parentid = 0; // required by devices
		}
		else
		{
			//debugLog(__METHOD__.' isOther Folder'.$folder);
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
		if (is_numeric($account)) $type = 'mail';
		if ($type != 'mail') return false;

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID);

		$changes = array();
        debugLog("AlterPingChanges on $folderid ($folder) stat: ". $syncstate);
        $this->mail->reopen($folder);

        $status = $this->mail->getFolderStatus($folder,$ignoreStatusCache=true);
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
	function GetWasteBasket()
	{
		debugLog(__METHOD__.__LINE__.' called.');
		$this->_connect($this->account);
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

		$this->_connect($this->account);
		$this->mail->reopen($folder);
		try
		{
			$rv = $this->mail->deleteMessages($_messageUID, $folder);
		}
		catch (egw_exception $e)
		{
			$error = $e->getMessage();
			debugLog(__METHOD__.__LINE__." $_messageUID, $folder ->".$error);
			// if the server thinks the message does not exist report deletion as success
			if (stripos($error,'[NONEXISTENT]')!==false) return true;
			return false;
		}

		// this may be a bit rude, it may be sufficient that GetMessageList does not list messages flagged as deleted
		if ($this->mail->mailPreferences['deleteOptions'] == 'mark_as_deleted')
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
		// debugLog("IMAP-SetReadFlag: (fid: '$folderid'  id: '$id'  flags: '$flags' )");
		$this->splitID($folderid, $account, $folder);

		$_messageUID = (array)$id;
		$this->_connect($this->account);
		$rv = $this->mail->flagMessages((($flags) ? "read" : "unread"), $_messageUID,$folder);
		debugLog("IMAP-SetReadFlag -> set ".array2string($_messageUID).' in Folder '.$folder." as " . (($flags) ? "read" : "unread") . "-->". $rv);

		return $rv;
	}

	/**
	 *  Creates or modifies a folder
	 *
	 * @param string $id of the parent folder
	 * @param string $oldid => if empty -> new folder created, else folder is to be renamed
	 * @param string $displayname => new folder name (to be created, or to be renamed to)
	 * @param string $type folder type, ignored in IMAP
	 *
	 * @return array|boolean stat array or false on error
	 */
	public function ChangeFolder($id, $oldid, $displayname, $type)
	{
		debugLog(__METHOD__."('$id', '$oldid', '$displayname', $type) NOT supported!");
		return false;
	}

	/**
	 * Deletes (really delete) a Folder
	 *
	 * @param string $parentid of the folder to delete
	 * @param string $id of the folder to delete
	 *
	 * @return
	 * @TODO check what is to be returned
	 */
	public function DeleteFolder($parentid, $id)
	{
		debugLog(__METHOD__."('$parentid', '$id') NOT supported!");
		return false;
	}

	/**
	 * modify olflags (outlook style) flag of a message
	 *
	 * @param $folderid
	 * @param $id
	 * @param $flags
	 *
	 *
	 * @DESC The $flags parameter must contains the poommailflag Object
	 */
	function ChangeMessageFlag($folderid, $id, $flags)
	{
		$_messageUID = (array)$id;
		$this->_connect($this->account);
		$this->splitID($folderid, $account, $folder);
		$rv = $this->mail->flagMessages((($flags->flagstatus == 2) ? "flagged" : "unflagged"), $_messageUID,$folder);
		debugLog("IMAP-SetFlaggedFlag -> set ".array2string($_messageUID).' in Folder '.$folder." as " . (($flags->flagstatus == 2) ? "flagged" : "unflagged") . "-->". $rv);

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
		$this->backend->splitID($str, $account, $folder, $id);

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
