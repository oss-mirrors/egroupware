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
class mail_zpush implements activesync_plugin_write, activesync_plugin_sendmail, activesync_plugin_meeting_response, activesync_plugin_search_mailbox
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
	 * Provides the ability to change the line ending
	 * @var string
	 */
	public static $LE = "\n";

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
		if ($GLOBALS['egw_setup']) return;

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

		// verify we are on an existing profile, if not running in setup (settings can not be static according to interface!)
		if (!isset($GLOBALS['egw_setup']))
		{
			try {
				emailadmin_account::read(self::$profileID);
			}
			catch(Exception $e) {
				unset($e);
				self::$profileID = emailadmin_account::get_default_acc_id();
			}
		}
		if ($this->debugLevel>0) error_log(__METHOD__.'::'.__LINE__.' ProfileSelected:'.self::$profileID);
		//$this->debugLevel=0;
	}

	/**
	 * Populates $settings for the preferences
	 *
	 * @param array|string $hook_data
	 * @return array
	 */
	function egw_settings($hook_data)
	{
		//error_log(__METHOD__.__LINE__.array2string($hook_data));
		$identities = array();
		if (!isset($hook_data['setup']) && in_array($hook_data['type'], array('user', 'group')))
		{
			$identities = iterator_to_array(emailadmin_account::search((int)$hook_data['account_id']));
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
				$this->_connect(0,true);
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
	 * @param boolean $verify_mode mode used for verify_settings; we want the exception but not the header stuff
	 * @todo support different accounts
	 */
	private function _connect($account=0, $verify_mode=false)
	{
		static $waitOnFailure = null;
		if (is_null($account)) $account = 0;
		if ($this->mail && $this->account != $account) $this->_disconnect();

		$hereandnow = egw_time::to('now','ts');
		$this->_wasteID = false;
		$this->_sentID = false;

		$connectionFailed = false;

		if ($verify_mode==false && (is_null($waitOnFailure)||empty($waitOnFailure[self::$profileID])||empty($waitOnFailure[self::$profileID][$this->backend->_devid])))
		{
			$waitOnFailure = egw_cache::getCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']), null, array(), 60*60*2);
		}
		if (isset($waitOnFailure[self::$profileID]) && !empty($waitOnFailure[self::$profileID]) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]) && isset($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']) && isset($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']) && !empty($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']))
		{
			if ($waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']+$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']<$hereandnow)
			{
				if ($this->debugLevel>0) error_log(__METHOD__.__LINE__.'# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid']." Refuse to open connection for Profile:".self::$profileID.' Device '.$this->backend->_devid.' should still wait '.array2string($waitOnFailure[self::$profileID][$this->backend->_devid]));
				//header("HTTP/1.1 503 Service Unavailable");
				$hL = $waitOnFailure[self::$profileID][$this->backend->_devid]['lastattempt']+$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']-$hereandnow;
				header("Retry-After: 30");
				// let z-push know we want to terminate
				ZLog::Write(LOGLEVEL_ERROR, "($account) still waiting for Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid." Should wait for:".$waitaslongasthis.'(s)'.' WaitInfoStored2Cache:'.array2string($waitOnFailure));
				throw new HTTPReturnCodeException('Service Unavailable', 503);
				/*$ethrown = new egw_exception_not_found(__METHOD__.__LINE__."($account) still waiting for Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid." Should wait for:".$waitaslongasthis.'(s)'.' WaitInfoStored2Cache:'.array2string($waitOnFailure));
				_egw_log_exception($ethrown);
				exit;*/
			}
		}
		if (!$this->mail)
		{
			$this->account = $account;
			// todo: tell mail which account to use
			//error_log(__METHOD__.__LINE__.' create object with ProfileID:'.array2string(self::$profileID));
			try
			{
				$this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);
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
			// in verify_moode, we want the exeption, but not the exit
			if ($verify_mode)
			{
				throw new egw_exception_not_found(__METHOD__.__LINE__."($account) can not open connection on Profile #".self::$profileID."!".$this->mail->getErrorMessage().' for Instance='.$GLOBALS['egw_info']['user']['domain']);
			}
			else
			{
				//error_log(__METHOD__.__LINE__."($account) could not open connection!".$errorMessage);
				//error_log(date('Y-m-d H:i:s').' '.__METHOD__.__LINE__."($account) can not open connection!".$this->mail->getErrorMessage()."\n",3,'/var/lib/egroupware/esync-imap.log');
				//error_log('# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', URL='.
				//	($_SERVER['HTTPS']?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n\n",3,'/var/lib/egroupware/esync-imap.log');
				if ($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong'] > $this->waitOnFailureLimit )
				{
					$waitOnFailure[self::$profileID][$this->backend->_devid] = array('howlong'=>$this->waitOnFailureDefault,'lastattempt'=>$hereandnow);
					egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
					header("HTTP/1.1 500 Internal Server Error");
					throw new egw_exception_not_found(__METHOD__.__LINE__."($account) can not open connection on Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid);
				}
				else
				{
					//error_log(__METHOD__.__LINE__.'# Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid']." Can not open connection for Profile:".self::$profileID.' Device:'.$this->backend->_devid.' should wait '.array2string($waitOnFailure[self::$profileID][$this->backend->_devid]));
					$waitaslongasthis = $waitOnFailure[self::$profileID][$this->backend->_devid]['howlong'];
					$waitOnFailure[self::$profileID][$this->backend->_devid] = array('howlong'=>(empty($waitOnFailure[self::$profileID][$this->backend->_devid]['howlong'])?$this->waitOnFailureDefault:$waitOnFailure[self::$profileID][$this->backend->_devid]['howlong']) * 2,'lastattempt'=>$hereandnow);
					egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
					header("HTTP/1.1 503 Service Unavailable");
					header("Retry-After: ".$waitaslongasthis);
					$ethrown = new egw_exception_not_found(__METHOD__.__LINE__."($account) can not open connection on Profile #".self::$profileID."!".$errorMessage.' for Instance='.$GLOBALS['egw_info']['user']['domain'].', User='.$GLOBALS['egw_info']['user']['account_lid'].', Device:'.$this->backend->_devid." Should wait for:".$waitaslongasthis.'(s)'.' WaitInfoStored2Cache:'.array2string($waitOnFailure));
					_egw_log_exception($ethrown);
					exit;
				}
			}
			//die('Mail not or mis-configured!');
		}
		else
		{
			if (!empty($waitOnFailure[self::$profileID][$this->backend->_devid]))
			{
				$waitOnFailure[self::$profileID][$this->backend->_devid] = array();
				egw_cache::setCache(egw_cache::INSTANCE,'email','ActiveSyncWaitOnFailure'.trim($GLOBALS['egw_info']['user']['account_id']),$waitOnFailure,$expiration=60*60*2);
			}
		}
		$this->_wasteID = $this->mail->getTrashFolder(false);
		//error_log(__METHOD__.__LINE__.' TrashFolder:'.$this->_wasteID);
		$this->_sentID = $this->mail->getSentFolder(false);
		$this->mail->getOutboxFolder(true);
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
		debugLog(__METHOD__.__LINE__);
		/*foreach($available_accounts as $account)*/ $account = 0;
		{
			$this->_connect($account);
			if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false,$_alwaysGetDefaultFolders=true);
			debugLog(__METHOD__.__LINE__.array2string($this->folders));

			foreach ($this->folders as $folder => $folderObj) {
				debugLog(__METHOD__.__LINE__.' folder='.$folder);
				$folderlist[] = $f = array(
					'id'     => $this->createID($account,$folder),
					'mod'    => $folderObj->shortDisplayName,
					'parent' => $this->getParentID($account,$folder),
				);
				if ($this->debugLevel>0) debugLog(__METHOD__."() returning ".array2string($f));
			}
		}
		debugLog(__METHOD__."() returning ".array2string($folderlist));

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
     * @param array $smartdata = IMAP-SendMail: SyncSendMail (
     *        (S) clientid => SendMail-30722448149304
     *        (S) saveinsent => empty
     *        (S) replacemime => null
     *        (S) accountid => null
     *        (S) source => SyncSendMailSource (
     *                                (S) folderid => 101000000000
     *                                (S) itemid => 33776
     *                                (S) longid => null
     *                                (S) instanceid => null
     *                                unsetVars(Array) size: 0
     *                                flags => false
     *                                content => null
     *                        )
     *        (S) mime => Date: Tue, 23 Jun 2015 14:13:23 +0200
     *Subject: AW: Blauer himmel
     *....
     *        (S) replyflag => true
     *        (S) forwardflag => null
     *        unsetVars(Array) size: 0
     *        flags => false
     *        content => null
     *)
	 *
     * @return boolean true on success, false on error
     *
     * @see eg. BackendIMAP::SendMail()
     * @todo implement either here or in mail backend
     * 	(maybe sending here and storing to sent folder in plugin, as sending is supposed to always work in EGroupware)
     */
	public function SendMail($smartdata)
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
		$smartdata_task = ($smartdata->replyflag?'reply':($smartdata->forwardflag?'forward':'new'));

   		debugLog(__METHOD__.__LINE__ . (isset($smartdata->mime) ? $smartdata->mime : ""). "task: ".(isset($smartdata_task) ? $smartdata_task : "")." itemid: ".(isset($smartdata->source->itemid) ? $smartdata->source->itemid : "")." folder: ".(isset($smartdata->source->folderid) ? $smartdata->source->folderid : ""));
		if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__."): Smartdata = ".array2string($smartdata));
		//error_log("IMAP-Sendmail: Smartdata = ".array2string($smartdata));

		// initialize our mail_bo
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);
		$activeMailProfiles = $this->mail->getAccountIdentities(self::$profileID);
		// use the standardIdentity
		$activeMailProfile = mail_bo::getStandardIdentityForProfile($activeMailProfiles,self::$profileID);

		if ($this->debugLevel>2) debugLog(__METHOD__."(".__LINE__.")".' ProfileID:'.self::$profileID.' ActiveMailProfile:'.array2string($activeMailProfile));

		// initialize the new egw_mailer object for sending
		$mailObject = new egw_mailer(self::$profileID);
		$this->mail->parseRawMessageIntoMailObject($mailObject,$smartdata->mime);
		// Horde SMTP Class uses utf-8 by default. as we set charset always to utf-8
		$mailObject->Sender  = $activeMailProfile['ident_email'];
		$mailObject->From 	= $activeMailProfile['ident_email'];
		$mailObject->FromName = $mailObject->EncodeHeader(mail_bo::generateIdentityString($activeMailProfile,false));
		$mailObject->AddCustomHeader('X-Mailer: mail-Activesync');


		// prepare addressee list; moved the adding of addresses to the mailobject down
		// to

		foreach(emailadmin_imapbase::parseAddressList($mailObject->getHeader("To")) as $addressObject) {
			if (!$addressObject->valid) continue;
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") Header Sentmail To: ".array2string($addressObject) );
			//$mailObject->AddAddress($addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : ''),$addressObject->personal);
			$toMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		// CC
		foreach(emailadmin_imapbase::parseAddressList($mailObject->getHeader("Cc")) as $addressObject) {
			if (!$addressObject->valid) continue;
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") Header Sentmail CC: ".array2string($addressObject) );
			//$mailObject->AddCC($addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : ''),$addressObject->personal);
			$ccMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		// BCC
		foreach(emailadmin_imapbase::parseAddressList($mailObject->getHeader("Bcc")) as $addressObject) {
			if (!$addressObject->valid) continue;
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") Header Sentmail BCC: ".array2string($addressObject) );
			//$mailObject->AddBCC($addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : ''),$addressObject->personal);
			$bccMailAddr[] = imap_rfc822_write_address($addressObject->mailbox, $addressObject->host, $addressObject->personal);
		}
		$mailObject->clearAllRecipients();

		$use_orgbody = false;

		$k = 'Content-Type';
		$ContentType =$mailObject->getHeader('Content-Type');
		//error_log(__METHOD__.__LINE__." Header Sentmail original Header (filtered): " . $k.  " = ".trim($ContentType));
		// if the message is a multipart message, then we should use the sent body
		if (preg_match("/multipart/i", $ContentType)) {
			$use_orgbody = true;
		}

		// save the original content-type header for the body part when forwarding
		if ($smartdata_task == 'forward' && $smartdata->source->itemid && !$use_orgbody) {
			//continue; // ignore
		}
		// horde/egw_ mailer does everything as utf-8, the following should not be needed
		//$org_charset = $ContentType;
		//$ContentType = preg_replace("/charset=([A-Za-z0-9-\"']+)/", "charset=\"utf-8\"", $ContentType);
		// if the message is a multipart message, then we should use the sent body
		if (($smartdata_task == 'new' || $smartdata_task == 'reply' || $smartdata_task == 'forward') &&
			((isset($smartdata->replacemime) && $smartdata->replacemime == true) ||
			$k == "Content-Type" && preg_match("/multipart/i", $ContentType))) {
			$use_orgbody = true;
		}
		$Body =  $AltBody = "";
		// get body of the transmitted message
		// if this is a simple message, no structure at all
		if (preg_match("/text/i", $ContentType))
		{
			$simpleBodyType = (preg_match("/html/i", $ContentType)?'text/html':'text/plain');
			$bodyObj = $mailObject->findBody(preg_match("/html/i", $ContentType) ? 'html' : 'plain');
			$body = preg_replace("/(<|&lt;)*(([\w\.,-.,_.,0-9.]+)@([\w\.,-.,_.,0-9.]+))(>|&gt;)*/i","[$2]", $bodyObj ?$bodyObj->getContents() : null);
			if  ($simpleBodyType == "text/plain")
			{
				$Body = $body;
				$AltBody = "<pre>".nl2br($body)."</pre>";
				if ($this->debugLevel>1) debugLog(__METHOD__."(".__LINE__.") fetched Body as :". $simpleBodyType.'=> Created AltBody');
			}
			else
			{
				$AltBody = $body;
				$Body =  trim(translation::convertHTMLToText($body));
				if ($this->debugLevel>1) debugLog(__METHOD__."(".__LINE__.") fetched Body as :". $simpleBodyType.'=> Created Body');
			}
		}
		else
		{
			// if this is a structured message
			// prefer plain over html
			$Body = preg_replace("/(<|&lt;)*(([\w\.,-.,_.,0-9.]+)@([\w\.,-.,_.,0-9.]+))(>|&gt;)*/i","[$2]",
				($text_body = $mailObject->findBody('plain')) ? $text_body->getContents() : null);
			$AltBody = preg_replace("/(<|&lt;)*(([\w\.,-.,_.,0-9.]+)@([\w\.,-.,_.,0-9.]+))(>|&gt;)*/i","[$2]",
				($html_body = $mailObject->findBody('html')) ? $html_body->getContents() : null);
		}
		if ($this->debugLevel>1 && $Body) debugLog(__METHOD__."(".__LINE__.") fetched Body as with MessageContentType:". $ContentType.'=>'.$Body);
		if ($this->debugLevel>1 && $AltBody) debugLog(__METHOD__."(".__LINE__.") fetched AltBody as with MessageContentType:". $ContentType.'=>'.$AltBody);
		//error_log(__METHOD__.__LINE__.array2string($mailObject));
		// if this is a multipart message with a boundary, we must use the original body
		//if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' mailObject after Inital Parse:'.array2string($mailObject));
        if ($use_orgbody) {
    	    if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") use_orgbody = true ContentType:".$ContentType);
 			// if it is a ClientSideMeetingRequest, we report it as send at all times
			if (stripos($ContentType,'text/calendar') !== false )
			{
				$body = ($text_body = $mailObject->findBody('calendar')) ? $text_body->getContents() : null;
				$Body = $body;
				$AltBody = "<pre>".nl2br($body)."</pre>";
				if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") we have a Client Side Meeting Request");
				// try figuring out the METHOD -> [ContentType] => text/calendar; name=meeting.ics; method=REQUEST
				$tA = explode(' ',$ContentType);
				foreach ((array)$tA as $k => $p)
				{
					if (stripos($p,"method=")!==false) $cSMRMethod= trim(str_replace('METHOD=','',strtoupper($p)));
				}
				$ClientSideMeetingRequest = true;
			}
        }
		// now handle the addressee list
		$toCount = 0;
		//error_log(__METHOD__.__LINE__.array2string($toMailAddr));
		foreach((array)$toMailAddr as $address) {
			foreach(emailadmin_imapbase::parseAddressList((get_magic_quotes_gpc()?stripslashes($address):$address)) as $addressObject) {
				$emailAddress = $addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress,(isset($cSMRMethod)?$cSMRMethod:'REQUEST'))) continue;
				$mailObject->AddAddress($emailAddress, $addressObject->personal);
				$toCount++;
			}
		}
		$ccCount = 0;
		foreach((array)$ccMailAddr as $address) {
			foreach(emailadmin_imapbase::parseAddressList((get_magic_quotes_gpc()?stripslashes($address):$address)) as $addressObject) {
				$emailAddress = $addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress)) continue;
				$mailObject->AddCC($emailAddress, $addressObject->personal);
				$ccCount++;
			}
		}
		$bccCount = 0;
		foreach((array)$bccMailAddr as $address) {
			foreach(emailadmin_imapbase::parseAddressList((get_magic_quotes_gpc()?stripslashes($address):$address)) as $addressObject) {
				$emailAddress = $addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : '');
				if ($ClientSideMeetingRequest === true && $allowSendingInvitations == 'sendifnocalnotif' && calendar_boupdate::email_update_requested($emailAddress)) continue;
				$mailObject->AddBCC($emailAddress, $addressObject->personal);
				$bccCount++;
			}
		}
		if ($toCount+$ccCount+$bccCount == 0) return 0; // noone to send mail to
		if ($ClientSideMeetingRequest === true && $allowSendingInvitations===false) return true;
		// as we use our mailer (horde mailer) it is detecting / setting the mimetype by itself while creating the mail
/*
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' retrieved Body:'.$body);
		$body = str_replace("\r",((preg_match("^text/html^i", $ContentType))?'<br>':""),$body); // what is this for?
		if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' retrieved Body (modified):'.$body);
*/
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
		$beforePlain = $beforeHtml = "";
		$beforeHtml = ($disableRuler ?'&nbsp;<br>':'&nbsp;<br><hr style="border:dotted 1px silver; width:90%; border:dotted 1px silver;">');
		$beforePlain = ($disableRuler ?"\r\n\r\n":"\r\n\r\n-- \r\n");
		$sigText = emailadmin_imapbase::merge($signature,array($GLOBALS['egw']->accounts->id2name($GLOBALS['egw_info']['user']['account_id'],'person_id')));
		if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Signature to use:'.$sigText);
		$sigTextHtml = $beforeHtml.$sigText;
		$sigTextPlain = $beforePlain.translation::convertHTMLToText($sigText);
		$isreply = $isforward = false;
		// reply ---------------------------------------------------------------------------
		if ($smartdata_task == 'reply' && isset($smartdata->source->itemid) &&
			isset($smartdata->source->folderid) && $smartdata->source->itemid && $smartdata->source->folderid &&
			(!isset($smartdata->replacemime) ||
			(isset($smartdata->replacemime) && $smartdata->replacemime == false)))
		{
			// now get on, and fetch the original mail
			$uid = $smartdata->source->itemid;
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") IMAP Smartreply is called with FolderID:".$smartdata->source->folderid.' and ItemID:'.$smartdata->source->itemid);
			$this->splitID($smartdata->source->folderid, $account, $folder);

			$this->mail->reopen($folder);
			$bodyStruct = $this->mail->getMessageBody($uid, 'html_only');
			$bodyBUFFHtml = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$bodyBUFFHtml);
		    if ($bodyBUFFHtml != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
				// may be html
				if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:html (fetched with html_only):'.$bodyBUFFHtml);
				$AltBody = $AltBody."</br>".$bodyBUFFHtml.$sigTextHtml;
				$isreply = true;
			}
			// plain text Message part
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:plain, fetch text:');
			// if the new part of the message is html, we must preserve it, and handle that the original mail is text/plain
			$bodyStruct = $this->mail->getMessageBody($uid,'never_display');//'never_display');
			$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
			if ($bodyBUFF != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/plain')) {
				if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:plain (fetched with never_display):'.$bodyBUFF);
				$Body = $Body."\r\n".$bodyBUFF.$sigTextPlain;
				$isreply = true;
			}
			if (!empty($bodyBUFF) && empty($bodyBUFFHtml) && !empty($AltBody))
			{
				$isreply = true;
				$AltBody = $AltBody."</br><pre>".nl2br($bodyBUFF).'</pre>'.$sigTextHtml;
				if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." no html Body found use modified plaintext body for txt/html: ".$AltBody);
			}
		}

		// how to forward and other prefs
		$preferencesArray =& $GLOBALS['egw_info']['user']['preferences']['mail'];

		// forward -------------------------------------------------------------------------
		if ($smartdata_task == 'forward' && isset($smartdata->source->itemid) &&
			isset($smartdata->source->folderid) && $smartdata->source->itemid && $smartdata->source->folderid &&
			(!isset($smartdata->replacemime) ||
			(isset($smartdata->replacemime) && $smartdata->replacemime == false)))
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
			$uid = $smartdata->source->itemid;
			if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.")IMAP Smartfordward is called with FolderID:".$smartdata->source->folderid.' and ItemID:'.$smartdata->source->itemid);
			$this->splitID($smartdata->source->folderid, $account, $folder);

			$this->mail->reopen($folder);
            // receive entire mail (header + body)
			// get message headers for specified message
			$headers	= $this->mail->getMessageEnvelope($uid, $_partID, true, $folder);
			// build a new mime message, forward entire old mail as file
			if ($preferencesArray['message_forwarding'] == 'asmail')
			{
				$rawHeader='';
				$rawHeader      = $this->mail->getMessageRawHeader($smartdata->source->itemid, $_partID,$folder);
				$rawBody        = $this->mail->getMessageRawBody($smartdata->source->itemid, $_partID,$folder);
				$mailObject->AddStringAttachment($rawHeader.$rawBody, $headers['SUBJECT'].'.eml', 'message/rfc822');
				$AltBody = $AltBody."</br>".lang("See Attachments for Content of the Orignial Mail").$sigTextHtml;
				$Body = $Body."\r\n".lang("See Attachments for Content of the Orignial Mail").$sigTextPlain;
				$isforward = true;
			}
			else
			{
				// now get on, and fetch the original mail
				$uid = $smartdata->source->itemid;
				if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") IMAP Smartreply is called with FolderID:".$smartdata->source->folderid.' and ItemID:'.$smartdata->source->itemid);
				$this->splitID($smartdata->source->folderid, $account, $folder);

				$this->mail->reopen($folder);
				$bodyStruct = $this->mail->getMessageBody($uid, 'html_only');
				$bodyBUFFHtml = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$bodyBUFFHtml);
				if ($bodyBUFFHtml != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
					// may be html
					if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:html (fetched with html_only):'.$bodyBUFFHtml);
					$AltBody = $AltBody."</br>".$bodyBUFFHtml.$sigTextHtml;
					$isforward = true;
				}
				// plain text Message part
				if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:plain, fetch text:');
				// if the new part of the message is html, we must preserve it, and handle that the original mail is text/plain
				$bodyStruct = $this->mail->getMessageBody($uid,'never_display');//'never_display');
				$bodyBUFF = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
				if ($bodyBUFF != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/plain')) {
					if ($this->debugLevel>0) debugLog(__METHOD__."(".__LINE__.") MIME Body".' Type:plain (fetched with never_display):'.$bodyBUFF);
					$Body = $Body."\r\n".$bodyBUFF.$sigTextPlain;
					$isforward = true;
				}
				if (!empty($bodyBUFF) && empty($bodyBUFFHtml) && !empty($AltBody))
				{
					$AltBody = $AltBody."</br><pre>".nl2br($bodyBUFF).'</pre>'.$sigTextHtml;
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__." no html Body found use modified plaintext body for txt/html: ".$AltBody);
					$isforward = true;
				}
				// get all the attachments and add them too.
				// start handle Attachments
				//												$_uid, $_partID=null, Horde_Mime_Part $_structure=null, $fetchEmbeddedImages=true, $fetchTextCalendar=false, $resolveTNEF=true, $_folderName=''
				$attachments = $this->mail->getMessageAttachments($uid, null,          null,								true,						false,				 true			, $folder);
				$attachmentNames = false;
				if (is_array($attachments) && count($attachments)>0)
				{
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' gather Attachments for BodyCreation of/for MessageID:'.$uid.' found:'.count($attachments));
					foreach((array)$attachments as $key => $attachment)
					{
						if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attachment));
						$attachmentNames .= $attachment['name']."\n";
						$attachmentData = '';
						$attachmentData	= $this->mail->getAttachment($uid, $attachment['partID'],0,false,false,$folder);
						/*$x =*/ $mailObject->AddStringAttachment($attachmentData['attachment'], $mailObject->EncodeHeader($attachment['name']), $attachment['mimeType']);
						//debugLog(__METHOD__.__LINE__.' added part with number:'.$x);
					}
				}
			}
		} // end forward
		// add signature, in case its not already added in forward or reply
		if (!$isreply && !$isforward)
		{
				$Body = $Body.$sigTextPlain;
				$AltBody = $AltBody.$sigTextHtml;
		}
		// now set the body
		if ($AltBody && ($html_body = $mailObject->findBody('html')))
		{
			if ($this->debugLevel>1) debugLog(__METHOD__.__LINE__.' -> '.$AltBody);
			$html_body->setContents($AltBody,array('encoding'=>Horde_Mime_Part::DEFAULT_ENCODING));
		}
		if ($Body && ($text_body = $mailObject->findBody('plain')))
		{
			if ($this->debugLevel>1) debugLog(__METHOD__.__LINE__.' -> '.$Body);
			$text_body->setContents($Body,array('encoding'=>Horde_Mime_Part::DEFAULT_ENCODING));
		}
		//advanced debugging
		// Horde SMTP Class uses utf-8 by default.
        //debugLog("IMAP-SendMail: parsed message: ". print_r($message,1));
		if ($this->debugLevel>2) debugLog(__METHOD__."(".__LINE__."): MailObject:".array2string($mailObject));

		// set a higher timeout for big messages
		@set_time_limit(120);

		// send
		$send = true;
		try {
			$mailObject->Send();
		}
		catch(phpmailerException $e) {
			debugLog(__METHOD__."(".__LINE__.") The email could not be sent. Last-SMTP-error: ". $e->getMessage());
			$send = false;
		}

		if (( $smartdata_task == 'reply' || $smartdata_task == 'forward') && $send == true)
		{
			$uid = $smartdata->source->itemid;
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' tASK:'.$smartdata_task." FolderID:".$smartdata->source->folderid.' and ItemID:'.$smartdata->source->itemid);
			$this->splitID($smartdata->source->folderid, $account, $folder);
			//error_log(__METHOD__.__LINE__.' Folder:'.$folder.' Uid:'.$uid);
			$this->mail->reopen($folder);
			// if the draft folder is a starting part of the messages folder, the draft message will be deleted after the send
			// unless your templatefolder is a subfolder of your draftfolder, and the message is in there
			if ($this->mail->isDraftFolder($folder) && !$this->mail->isTemplateFolder($folder))
			{
				$this->mail->deleteMessages(array($uid),$folder);
			} else {
				$this->mail->flagMessages("answered", array($uid),$folder);
				if ($smartdata_task== "forward")
				{
					$this->mail->flagMessages("forwarded", array($uid),$folder);
				}
			}
		}

		$asf = ($send ? true:false); // initalize accordingly
		if (/*($smartdata->saveinsent==1 || !isset($smartdata->saveinsent)) && */  $send==true && $this->mail->mailPreferences['sendOptions'] != 'send_only')
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
				debugLog(__METHOD__."(".__LINE__.") IMAP-SendMail: No Sent mailbox set");
				// we dont try guessing
				$asf = true;
			}
			if (count($folderArray) > 0) {
				foreach((array)$bccMailAddr as $address) {
					foreach(emailadmin_imapbase::parseAddressList((get_magic_quotes_gpc()?stripslashes($address):$address)) as $addressObject) {
						$emailAddress = $addressObject->mailbox. ($addressObject->host ? '@'.$addressObject->host : '');
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
							$this->mail->appendMessage($folderName,$mailObject->getRaw(), null,
									$flags);
						}
						catch (egw_exception_wrong_userinput $e)
						{
							//$asf = false;
							debugLog(__METHOD__.__LINE__.'->'.lang("Import of message %1 failed. Could not save message to folder %2 due to: %3",$mailObject->getHeader('Subject'),$folderName,$e->getMessage()));
						}
					}
					else
					{
						//$asf = false;
						debugLog(__METHOD__.__LINE__.'->'.lang("Import of message %1 failed. Destination Folder %2 does not exist.",$mailObject->getHeader('Subject'),$folderName));
					}
			        debugLog(__METHOD__."(".__LINE__."): Outgoing mail saved in configured 'Sent' folder '".$folderName."': ". (($asf)?"success":"failed"));
				}
				//$this->mail->closeConnection();
			}
		}

		$this->debugLevel=0;

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
	 * For meeting requests (iCal attachments with method='request') we call calendar plugin with iCal to get SyncMeetingRequest object,
	 * and do NOT return the attachment itself!
	 *
	 * @param string $folderid
	 * @param string $id
	 * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
	 *  object with attributes foldertype, truncation, rtftruncation, conflict, filtertype, bodypref, deletesasmoves, filtertype, contentclass, mimesupport, conversationmode
	 *  bodypref object with attributes: ]truncationsize, allornone, preview
	 * @return $messageobject|boolean false on error
	 */
	public function GetMessage($folderid, $id, $contentparameters)
	{
		//$this->debugLevel=4;
		debugLog(__METHOD__.__LINE__.' FolderID:'.$folderid.' ID:'.$id.' ContentParams='.array2string($contentparameters));
		$truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
		$mimesupport = $contentparameters->GetMimeSupport();
		debugLog(__METHOD__."() truncsize=$truncsize, mimeSupport=".array2string($mimesupport));
		$bodypreference = $contentparameters->GetBodyPreference(); /* fmbiete's contribution r1528, ZP-320 */

		//$this->debugLevel=4;
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);
		debugLog(__METHOD__.__LINE__.' FolderID:'.$folderid.' ID:'.$id.' TruncSize:'.$truncsize.' Bodypreference: '.array2string($bodypreference));
		$account = $_folderName = $xid = null;
		$this->splitID($folderid,$account,$_folderName,$xid);
		$this->mail->reopen($_folderName);
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
				$bodyStruct = $this->mail->getMessageBody($id, 'only_if_no_text', '', null, true,$_folderName);
				$raw_body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);
				//$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				if (stripos($raw_body,'<style')!==false) $body = preg_replace("/<style.*?<\/style>/is", "", $raw_body); // in case there is only a html part
				// remove all other html
				$body = strip_tags($raw_body);
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
				//Select body type preference
				$bpReturnType = 1;//SYNC_BODYPREFERENCE_PLAIN;
				if ($bodypreference !== false) {
					$bpReturnType = Utils::GetBodyPreferenceBestMatch($bodypreference); // changed by mku ZP-330
				}
				debugLog(__METHOD__.__LINE__." getBodyPreferenceBestMatch: ".array2string($bpReturnType));
				// set the protocoll class
				$output->asbody = new SyncBaseBody();
				// fetch the body (try to gather data only once)
				$css ='';
				$bodyStruct = $this->mail->getMessageBody($id, 'html_only', '', null, true,$_folderName);
				if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__.' html_only Struct:'.array2string($bodyStruct));
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct,true);//$this->ui->getdisplayableBody($bodyStruct,false);
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' html_only:'.$body);
			    if ($body != "" && (is_array($bodyStruct) && $bodyStruct[0]['mimeType']=='text/html')) {
					// may be html
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:html (fetched with html_only)');
					$css = $this->mail->getStyles($bodyStruct);
					$output->nativebodytype=2;
				} else {
					// plain text Message
					if ($this->debugLevel>0) debugLog("MIME Body".' Type:plain, fetch text (HTML, if no text available)');
					$output->nativebodytype=1;
					$bodyStruct = $this->mail->getMessageBody($id,'never_display', '', null, true,$_folderName); //'only_if_no_text');
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' plain text Struct:'.array2string($bodyStruct));
					$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
					if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.' never display html(plain text only):'.$body);
				}
				// whatever format decode (using the correct encoding)
				if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__."MIME Body".' Type:'.($output->nativebodytype==2?' html ':' plain ').$body);
				//$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				// prepare plaintextbody
				if ($output->nativebodytype == 2)
				{
					$bodyStructplain = $this->mail->getMessageBody($id,'never_display', '', null, true,$_folderName); //'only_if_no_text');
					if($bodyStructplain[0]['error']==1)
					{
						$plainBody = translation::convertHTMLToText($body); // always display with preserved HTML
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
				if ($this->debugLevel>3 && $output->nativebodytype==1) debugLog(__METHOD__.__LINE__.' Plain Text:'.$plainBody);
				//$body = str_replace("\n","\r\n", str_replace("\r","",$body)); // do we need that?
				if ($bpReturnType==4)//$mimesupport==2 || $mimesupport ==1 && stristr($headers['CONTENT-TYPE'],'signed') !== false)
				{
					debugLog(__METHOD__.__LINE__." bodypreference 4 requested");
					$output->asbody->type = 4;
					$Body = $this->mail->getMessageRawBody($id, '', $_folderName);
					if ($this->debugLevel>2) debugLog(__METHOD__.__LINE__." Setting Mailobjectcontent to output:".$Body);
					$output->asbody->data = $Body;
				}
				else if ($bpReturnType==2)
				{
					if ($this->debugLevel>0) debugLog("HTML Body with requested pref 2");
					// Send HTML if requested and native type was html
					$output->asbody->type = 2;
					$htmlbody = '<html>'.
						'<head>'.
						'<meta name="Generator" content="Z-Push">'.
						'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
						$css.
						'</head>'.
						'<body>';
					if ($output->nativebodytype==2)
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

					if(isset($truncsize) && strlen($html) > $truncsize)
					{
						$htmlbody = utf8_truncate($htmlbody,$truncsize);
						$output->asbody->truncated = 1;
					}
					$output->asbody->data = $htmlbody;
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
					$output->asbody->type = 1;
					if(isset($truncsize) &&
			    		strlen($plainBody) > $truncsize)
					{
						$plainBody = utf8_truncate($plainBody, $truncsize);
						$output->asbody->truncated = 1;
					}
					$output->asbody->data = $plainBody;
				}
				// In case we have nothing for the body, send at least a blank...
				// dw2412 but only in case the body is not rtf!
				if ($output->asbody->type != 3 && (!isset($output->asbody->data) || strlen($output->asbody->data) == 0))
				{
					$output->asbody->data = " ";
				}
				// determine estimated datasize for all the above cases ...
				$output->asbody->estimatedDataSize = strlen($output->asbody->data);
			}
			// end AS12 Stuff
			debugLog(__METHOD__.__LINE__.' gather Header info:'.$headers['SUBJECT'].' from:'.$headers['DATE']);
			$output->read = $stat["flags"];
			$output->subject = $this->messages[$id]['subject'];
			$output->importance = $this->messages[$id]['priority'] > 3 ? 0 :
				($this->messages[$id]['priority'] < 3 ? 2 : 1) ;
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
			//$output->poommailflag = new SyncMailFlags();

			if ($this->messages[$id]['flagged'] == 1)
			{
				$output->flag = new SyncMailFlags();
				$output->flag->flagstatus = 2;
				$output->flag->flagtype = "Flag for Follow up";
			}

			$output->internetcpid = 65001;
			$output->contentclass="urn:content-classes:message";
			// end AS12 Stuff

			// start handle Attachments (include text/calendar multipart alternative)
			$attachments = $this->mail->getMessageAttachments($id, $_partID='', $_structure=null, $fetchEmbeddedImages=true, $fetchTextCalendar=true, true, $_folderName);
			if (is_array($attachments) && count($attachments)>0)
			{
				debugLog(__METHOD__.__LINE__.' gather Attachments for MessageID:'.$id.' found:'.count($attachments));
				//error_log(__METHOD__.__LINE__.array2string($attachments));
				foreach ($attachments as $key => $attach)
				{
					if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' Key:'.$key.'->'.array2string($attach));

					// pass meeting requests to calendar plugin
					if (strtolower($attach['mimeType']) == 'text/calendar' && strtolower($attach['method']) == 'request' &&
						isset($GLOBALS['egw_info']['user']['apps']['calendar']) &&
						($attachment = $this->mail->getAttachment($id, $attach['partID'],0,false,false,$_folderName)) &&
						($output->meetingrequest = calendar_zpush::meetingRequest($attachment['attachment'])))
					{
						$output->messageclass = "IPM.Schedule.Meeting.Request";
						continue;	// do NOT add attachment as attachment
					}
					if (Request::GetProtocolVersion() >= 12.0) {
						$attachment = new SyncBaseAttachment();
						if (!isset($output->asattachments) || !is_array($output->asattachments))
							$output->asattachments = array();
						$attachment->estimatedDataSize = $attach['size'];
						$attachment->method = 1;
						$attachment->filereference = $folderid . ":" . $id . ":" . $attach['partID'];
					} else {
						$attachment = new SyncAttachment();
						if (!isset($output->attachments) || !is_array($output->attachments))
							$output->attachments = array();
						$attachment->attsize = $attach['size'];
						$attachment->attmethod = 1;
						$attachment->attname = $folderid . ":" . $id . ":" . $attach['partID'];//$key;
					}

					$attachment->displayname = $attach['name'];
					//error_log(__METHOD__.__LINE__.'->'.$folderid . ":" . $id . ":" . $attach['partID']);

					$attachment->attoid = "";//isset($part->headers['content-id']) ? trim($part->headers['content-id']) : "";
					if (!empty($attach['cid']) && $attach['cid'] <> 'NIL' )
					{
						if ($bpReturnType != 4 && $attach['disposition'] == 'inline')
						{
							$attachment->isinline = true;
						}
						if (Request::GetProtocolVersion() >= 12.0) {
							$attachment->method=1;
							$attachment->contentid= str_replace(array("<",">"), "",$attach['cid']);
						} else {
							$attachment->attmethod=6;
							$attachment->attoid = str_replace(array("<",">"), "",$attach['cid']);
						}
						//	debugLog("'".$part->headers['content-id']."'  ".$attachment->contentid);
						$attachment->contenttype = trim($attach['mimeType']);
						//	debugLog("'".$part->headers['content-type']."'  ".$attachment->contentid);
					}
					if (Request::GetProtocolVersion() >= 12.0) {
						array_push($output->asattachments, $attachment);
					} else {
						array_push($output->attachments, $attachment);
					}
				}
			}
			//$this->debugLevel=0;
			// end handle Attachments
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($output));
//$this->debugLevel=0;
			return $output;
		}
//$this->debugLevel=0;
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
		if (!class_exists('calendar_zpush'))
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
				($attachment = $this->mail->getAttachment($requestid, $attach['partID'],0,false)))
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
		debugLog("getAttachmentData: $fid (attname: '$attname')");
		//error_log(__METHOD__.__LINE__." Fid: $fid (attname: '$attname')");
		list($folderid, $id, $part) = explode(":", $attname);

		$this->splitID($folderid, $account, $folder);

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);

		$this->mail->reopen($folder);
		$attachment = $this->mail->getAttachment($id,$part,0,false,true,$folder);
        $SIOattachment = new SyncItemOperationsAttachment();
        $SIOattachment->data = $attachment['attachment'];
        if (isset($attachment['type']) )
            $SIOattachment->contenttype = $attachment['type'];

		unset($attachment);

        return $SIOattachment;
	}

	/**
	 * ItemOperationsGetAttachmentData
	 * Should return attachment data for the specified attachment. The passed attachment identifier is
	 * the exact string that is returned in the 'AttName' property of an SyncAttachment. So, you should
	 * encode any information you need to find the attachment in that 'attname' property.
	 *
     * @param string $fid - id
     * @param string $attname - should contain (folder)id
	 * @return SyncItemOperationsAttachment-object
	 */
	function ItemOperationsGetAttachmentData($fid,$attname) {
		debugLog(__METHOD__.": $fid (attname: '$attname')");
		//error_log(__METHOD__.__LINE__." Fid: $fid (attname: '$attname')");
		list($folderid, $id, $part) = explode(":", $attname);

		$this->splitID($folderid, $account, $folder);

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);

		$this->mail->reopen($folder);
		$attachment = $this->mail->getAttachment($id,$part,0,false,true,$folder);
        $SIOattachment = new SyncItemOperationsAttachment();
        $SIOattachment->data = $attachment['attachment'];
        if (isset($attachment['type']) )
            $SIOattachment->contenttype = $attachment['type'];

		unset($attachment);

        return $SIOattachment;
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
	 * Called when a message has been changed on the mobile.
	 * Added support for FollowUp flag
	 *
	 * @param string              $folderid            id of the folder
	 * @param string              $id                  id of the message
	 * @param SyncXXX             $message             the SyncObject containing a message
	 * @param ContentParameters   $contentParameters
	 *
	 * @access public
	 * @return array                        same return value as StatMessage()
	 * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
	 */
	function ChangeMessage($folderid, $id, $message, $contentParameters)
	{
		debugLog(__METHOD__.__LINE__." $folderid, $id,".array2string($message).",".array2string($contentParameters));
		//unset($folderid, $id, $message, $contentParameters);
		$account = $folder = null;
		$this->splitID($folderid, $account, $folder);
		if (isset($message->flag)) {
			if (isset($message->flag->flagstatus) && $message->flag->flagstatus == 2) {
				$rv = $this->mail->flagMessages((($message->flag->flagstatus == 2) ? "flagged" : "unflagged"), $id,$folder);
				debugLog(__METHOD__." -> set ".array2string($id).' in Folder '.$folder." as " . (($message->flag->flagstatus == 2) ? "flagged" : "unflagged") . "-->". $rv);
			} else {
				$rv = $this->mail->flagMessages("unflagged", $id,$folder);
				debugLog(__METHOD__." -> set ".array2string($id).' in Folder '.$folder." as " . "unflagged" . "-->". $rv);
			}
		}
		return $this->StatMessage($folderid, $id);
	}

	/**
	 * This function is called when the user moves an item on the PDA. You should do whatever is needed
	 * to move the message on disk. After this call, StatMessage() and GetMessageList() should show the items
	 * to have a new parent. This means that it will disappear from GetMessageList() will not return the item
	 * at all on the source folder, and the destination folder will show the new message
	 *
	 * @param string              $folderid            id of the source folder
	 * @param string              $id                  id of the message
	 * @param string              $newfolderid         id of the destination folder
	 * @param ContentParameters   $contentParameters
	 *
	 * @return boolean                      status of the operation
	 * @throws StatusException              could throw specific SYNC_MOVEITEMSSTATUS_* exceptions
	 */
	public function MoveMessage($folderid, $id, $newfolderid, $contentParameters)
	{
		unset($contentParameters);	// not used, but required by function signature
		debugLog("IMAP-MoveMessage: (sfid: '$folderid'  id: '$id'  dfid: '$newfolderid' )");
		$account = $srcFolder = $destFolder = null;
		$this->splitID($folderid, $account, $srcFolder);
		$this->splitID($newfolderid, $account, $destFolder);
		debugLog("IMAP-MoveMessage: (SourceFolder: '$srcFolder'  id: '$id'  DestFolder: '$destFolder' )");
		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);
		$this->mail->reopen($destFolder);
		$status = $this->mail->getFolderStatus($destFolder);
		$uidNext = $status['uidnext'];
		$this->mail->reopen($srcFolder);

		// move message
		$rv = $this->mail->moveMessages($destFolder,(array)$id,true,$srcFolder,true);
		debugLog(__METHOD__.__LINE__.": New Status of $destFolder :".array2string($status).", ReturnValOf moveMessage".array2string($rv)); // this may be true, so try using the nextUID value by examine
		// return the new id "as string"
		return ($rv===true ? $uidNext : $rv[$id]) . "";
	}

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetMessageList($folderid, $cutoffdate=NULL)
	{
		static $cutdate=null;
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
			$account = $_folderName = $id = null;
			$this->splitID($folderid,$account,$_folderName,$id);
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
	 * @param object $_searchquery holds information specifying the query with GetDataArray it holds
	 * 		[searchname] => MAILBOX
	 * 		[searchfolderid] => 101000000000
	 * 		[searchfreetext] => somesearchtexgt
	 * 		[searchdatereceivedgreater] => 1
	 * 		[searchvaluegreater] => 2015-07-06T22:00:00.000Z
	 * 		[searchdatereceivedless] => 1
	 * 		[searchvalueless] => 2015-07-14T15:11:00.000Z
	 * 		[searchrebuildresults] => 1
	 * 		[searchrange] => 0-99
	 * 		[bodypref] => Array([1] => BodyPreference Object([unsetdata:protected] => Array([truncationsize] => [allornone] => [preview] => )[SO_internalid:StateObject:private] => [data:protected] =>
	 * 			 Array([truncationsize] => 2147483647)[changed:protected] => 1))
	 * 				[mimesupport] => 2)
	 * @return array(["range"] = $_searchquery->GetSearchRange(), ['searchtotal'] = count of results,
	 *			array("class" => "Email",
	 *					"longid" => folderid.':'.uid',
	 *					"folderid"	=> folderid,
	 *					), ....
	 *		)
	 */
	public function getSearchResultsMailbox($_searchquery)
	{
		//$this->debugLevel=1;
		$searchquery=$_searchquery->GetDataArray();
		if (!is_array($searchquery)) return array();
		if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.array2string($searchquery));

		if (isset($searchquery['searchrebuildresults'])) {
			$rebuildresults = $searchquery['searchrebuildresults'];
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

		if (isset($searchquery['searchrange'])) {
			$range = explode("-",$_searchquery->GetSearchRange());
			$start =$range[0] + 1;
			$limit = $range[1] - $range[0] + 1;
		} else {
			$range = false;
		}
		if ($this->debugLevel>0) debugLog( 'Range ['.print_r($range, true).']' );

		//foreach($searchquery['query'] as $k => $value) {
		//	$query = $value;
		//}
		if (isset($searchquery['searchfolderid']))
		{
			$folderid = $searchquery['searchfolderid'];
		}
/*
		// other types may be possible - we support quicksearch first (freeText in subject and from (or TO in Sent Folder))
		if (is_null(emailadmin_imapbase::$supportsORinQuery) || !isset(emailadmin_imapbase::$supportsORinQuery[self::$profileID]))
		{
			emailadmin_imapbase::$supportsORinQuery = egw_cache::getCache(egw_cache::INSTANCE,'email','supportsORinQuery'.trim($GLOBALS['egw_info']['user']['account_id']),$callback=null,$callback_params=array(),$expiration=60*60*10);
			if (!isset(emailadmin_imapbase::$supportsORinQuery[self::$profileID])) emailadmin_imapbase::$supportsORinQuery[self::$profileID]=true;
		}
*/
		if (isset($searchquery['searchfreetext']))
		{
			$searchText = $searchquery['searchfreetext'];
		}
		if (!$folderid)
		{
			$_folderName = ($this->mail->sessionData['mailbox']?$this->mail->sessionData['mailbox']:'INBOX');
			$folderid = $this->createID($account=0,$_folderName);
		}
//$_filter = array('status'=>array('UNDELETED'),'type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		debugLog(__METHOD__.__LINE__.' ProfileID:'.self::$profileID.' FolderID:'.$folderid.' Foldername:'.$_folderName);
		$this->_connect($account);
		// this should not be needed ???
		emailadmin_imapbase::$supportsORinQuery[self::$profileID]=true; // trigger quicksearch (if possible)
		$_filter = array('type'=> (emailadmin_imapbase::$supportsORinQuery[self::$profileID]?'quick':'subject'),
						 'string'=> $searchText,
						 'status'=>'any',
						);

		//$_filter[] = array('type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		if ($this->debugLevel>1) debugLog (__METHOD__.' for Folder:'.$_folderName.' Filter:'.array2string($_filter));
		$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=($range?$start:1), $_numberOfMessages=($limit?$limit:9999999), $_sort=0, $_reverse=false, $_filter, $_id=NULL);
		//debugLog(__METHOD__.__LINE__.array2string($rv_messages));
		$list=array();

		$cnt = count($rv_messages['header']);
		//$list['status'] = 1;
		$list['searchtotal'] = $cnt;
		$list["range"] = $_searchquery->GetSearchRange();
		foreach((array)$rv_messages['header'] as $i => $vars)
		{
			$list[] = array(
				"class" => "Email",
				"longid" => $folderid.':'.$vars['uid'],
				"folderid"	=> $folderid,
			);
		}
		//error_log(__METHOD__.__LINE__.array2string($list));
		//debugLog(__METHOD__.__LINE__.array2string($list));
		return $list;
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
		static $last_id = null;
		static $folderObj = null;
		if (isset($last_id) && $last_id === $id) return $folderObj;

		try {
			$account = $folder = null;
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
		$account = $folder = null;
		$this->splitID($folderid, $account, $folder);
		if (is_numeric($account)) $type = 'mail';
		if ($type != 'mail') return false;

		if (!isset($this->mail)) $this->mail = mail_bo::getInstance(false,self::$profileID,true,false,true);

		$changes = array();
        debugLog("AlterPingChanges on $folderid ($folder) stat: ". $syncstate);
        $this->mail->reopen($folder);
//        $oldStat = $this->mail->getFolderStatus($folder);
//error_log(__METHOD__.__LINE__.' withCache:'.array2string($oldStat));
        $status = $this->mail->getFolderStatus($folder,$ignoreStatusCache=true);
//error_log(__METHOD__.__LINE__.' noCache:'.array2string($status));
        if (!$status) {
            debugLog("AlterPingChanges: could not stat folder $folder ");
            return false;
        } else {
            $syncstate = "M:". $status['messages'] ."-R:". $status['recent'] ."-U:". $status['unseen']."-NUID:".$status['uidnext']."-UIDV:".$status['uidvalidity'];
        }
		debugLog(__METHOD__.' called with ('.$folderid.', ....) returning '.array2string($syncstate));
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
		$this->_connect($this->account);
		$id = $this->createID($account=0, $this->_wasteID);
		debugLog(__METHOD__.__LINE__."() account=$this->account returned $id for folder $this->_wasteID");
		return $id;
	}

    /**
     * Called when the user has requested to delete (really delete) a message. Usually
     * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the mobile
     * as it will be seen as a 'new' item. This means that if this method is not implemented, it's possible to
     * delete messages on the PDA, but as soon as a sync is done, the item will be resynched to the mobile
     *
     * @param string              $folderid             id of the folder
     * @param string              $id                   id of the message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function DeleteMessage($folderid, $id, $contentParameters)
	{
		unset($contentParameters);	// not used, but required by function signature
		debugLog("IMAP-DeleteMessage: (fid: '$folderid'  id: '$id' )");
		/*
		$this->imap_reopenFolder($folderid);
		$s1 = @imap_delete ($this->_mbox, $id, FT_UID);
		$s11 = @imap_setflag_full($this->_mbox, $id, "\\Deleted", FT_UID);
		$s2 = @imap_expunge($this->_mbox);
		*/
		// we may have to split folderid
		$account = $folder = null;
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
     * Changes the 'read' flag of a message on disk. The $flags
     * parameter can only be '1' (read) or '0' (unread). After a call to
     * SetReadFlag(), GetMessageList() should return the message with the
     * new 'flags' but should not modify the 'mod' parameter. If you do
     * change 'mod', simply setting the message to 'read' on the mobile will trigger
     * a full resync of the item from the server.
     *
     * @param string              $folderid            id of the folder
     * @param string              $id                  id of the message
     * @param int                 $flags               read flag of the message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters)
	{
		unset($contentParameters);	// not used, but required by function signature
		// debugLog("IMAP-SetReadFlag: (fid: '$folderid'  id: '$id'  flags: '$flags' )");
		$account = $folder = null;
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
		$account = $folder = null;
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
	 * @param int $id =0
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
			$this->folderHashes = json_decode($hashes,true);
			// fallback in case hashes have been serialized instead of being json-encoded
			if (json_last_error()!=JSON_ERROR_NONE)
			{
				//error_log(__METHOD__.__LINE__." error decoding with json");
				$this->folderHashes = unserialize($hashes);
			}
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
		// make sure $this->folderHashes is an array otherwise json_encode may fail on decode for string,integer,float or boolean
		return file_put_contents($this->hashFile(), json_encode((is_array($this->folderHashes)?$this->folderHashes:array($this->folderHashes))));
	}

	/**
	 * Get name of hashfile in state dir
	 *
	 * @throws egw_exception_assertion_failed
	 */
	private function hashFile()
	{
		if (!($dev_id=Request::GetDeviceID()))
		{
			throw new egw_exception_assertion_failed(__METHOD__."() called without this->_devid set!");
		}
		if (!file_exists(STATE_DIR.$dev_id))
		{
			mkdir(STATE_DIR.$dev_id);
		}
		return STATE_DIR.$dev_id.'/'.$dev_id.'.hashes';
	}

}
