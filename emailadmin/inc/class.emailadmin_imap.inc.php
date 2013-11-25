<?php
/**
 * EGroupware EMailAdmin: IMAP support using Horde_Imap_Client
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Stylite AG <info@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once EGW_INCLUDE_ROOT.'/emailadmin/inc/class.defaultimap.inc.php';

/**
 * This class holds all information about the imap connection.
 * This is the base class for all other imap classes.
 *
 * Also proxies Sieve calls to emailadmin_sieve (eg. it behaves like the former felamimail bosieve),
 * to allow IMAP plugins to also manage Sieve connection.
 *
 * @property-read integer $ImapServerId acc_id of mail account (alias for acc_id)
 * @property-read boolean $enableSieve sieve enabled (alias for acc_sieve_enabled)
 * @property-read int $acc_id id
 * @property-read string $acc_name description / display name
 * @property-read string $acc_imap_host imap hostname
 * @property-read int $acc_imap_ssl 0=none, 1=starttls, 2=tls, 3=ssl, &8=validate certificate
 * @property-read int $acc_imap_port imap port, default 143 or for ssl 993
 * @property-read string $acc_imap_username
 * @property-read string $acc_imap_password
 * @property-read boolean $acc_sieve_enabled sieve enabled
 * @property-read string $acc_sieve_hostsieve host, default imap_host
 * @property-read int $acc_sieve_ssl 0=none, 1=starttls, 2=tls, 3=ssl, &8=validate certificate
 * @property-read int $acc_sieve_port sieve port, default 4190, old non-ssl port 2000 or ssl 5190
 * @property-read string $acc_folder_sent sent folder
 * @property-read string $acc_folder_trash trash folder
 * @property-read string $acc_folder_draft draft folder
 * @property-read string $acc_folder_template template folder
 * @property-read string $acc_imap_type imap class to use, default emailadmin_imap
 * @property-read string $acc_imap_logintype how to construct login-name standard, vmailmgr, admin, uidNumber
 * @property-read string $acc_domain domain name
 * @property-read boolean $acc_imap_administration enable administration
 * @property-read string $acc_admin_username
 * @property-read string $acc_admin_password
 * @property-read boolean $acc_further_identities are non-admin users allowed to create further identities
 * @property-read boolean $acc_user_editable are non-admin users allowed to edit this account, if it is for them
 * @property-read array $params parameters passed to constructor (all above as array)
 */
class emailadmin_imap extends Horde_Imap_Client_Socket implements defaultimap
{
	/**
	 * Label shown in EMailAdmin
	 */
	const DESCRIPTION = 'standard IMAP server';

	/**
	 * Capabilities of this class (pipe-separated): default, sieve, admin, logintypeemail
	 */
	const CAPABILITIES = 'default|sieve';

	/**
	 * does the server with the serverID support keywords
	 * this information is filled/provided by examineMailbox
	 *
	 * @var array of boolean for each known serverID
	 */
	static $supports_keywords;

	/**
	 * is the mbstring extension available
	 *
	 * @var boolean
	 */
	protected $mbAvailable;

	/**
	 * Login type: 'uid', 'vmailmgr', 'uidNumber', 'email'
	 *
	 * @var string
	 */
	protected $imapLoginType;

	/**
	 * a debug switch
	 */
	public $debug = false;

	/**
	 * Sieve available
	 *
	 * @var boolean
	 */
	protected $enableSieve = false;

	/**
	 * True if connection is an admin connection
	 *
	 * @var boolean
	 */
	protected $isAdminConnection = false;

	/**
	 * Domain name
	 *
	 * @var string
	 */
	protected $domainName;

	/**
	 * Parameters passed to constructor from emailadmin_account
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Construtor
	 *
	 * @param array
	 * @param bool $_adminConnection create admin connection if true
	 * @param int $_timeout=null timeout in secs, if none given fmail pref or default of 20 is used
	 * @return void
	 */
	function __construct(array $params, $_adminConnection=false, $_timeout=null)
	{
		if (function_exists('mb_convert_encoding'))
		{
			$this->mbAvailable = true;
		}
		$this->params = $params;
		$this->isAdminConnection = $_adminConnection;
		$this->enableSieve = (boolean)$this->params['acc_sieve_enabled'];
		$this->loginType = $this->params['acc_imap_logintype'];
		$this->domainName = $this->params['acc_domain'];

		if (is_null($_timeout)) $_timeout = self::getTimeOut ();

		switch($this->params['acc_imap_ssl'] & ~emailadmin_account::SSL_VERIFY)
		{
			case emailadmin_account::SSL_STARTTLS:
				$secure = 'tls';	// Horde uses 'tls' for STARTTLS, not ssl connection with tls version >= 1 and no sslv2/3
				break;
			case emailadmin_account::SSL_SSL:
				$secure = 'ssl';
				break;
			case emailadmin_account::SSL_TLS:
				$secure = 'tlsv1';	// since Horde_Imap_Client-1.16.0 requiring Horde_Socket_Client-1.1.0
				break;
		}
		// Horde use locale for translation of error messages
		common::setlocale(LC_MESSAGES);

		parent::__construct(array(
			'username' => $this->params[$_adminConnection ? 'acc_imap_admin_username' : 'acc_imap_username'],
			'password' => $this->params[$_adminConnection ? 'acc_imap_admin_password' : 'acc_imap_password'],
			'hostspec' => $this->params['acc_imap_host'],
			'port' => $this->params['acc_imap_port'],
			'secure' => $secure,
			'timeout' => $_timeout,
			//'debug_literal' => true,
			//'debug' => '/tmp/imap.log',
			'cache' => array(
				'backend' => new Horde_Imap_Client_Cache_Backend_Cache(array(
					'cacheob' => new emailadmin_horde_cache(),
				)),
			),
		));
	}

	/**
	 * Allow read access to former public attributes
	 *
	 * @param type $name
	 */
	public function __get($name)
	{
		switch($name)
		{
			case 'ImapServerId':
				return $this->params['acc_id'];

			case 'enableSieve':
				return (boolean)$this->params['acc_sieve_enabled'];

			default:
				// allow readonly access to all class attributes
				if (isset($this->$name))
				{
					return $this->name;
				}
				if (array_key_exists($name,$this->params))
				{
					return $this->params[$name];
				}
				throw new egw_exception_wrong_parameter("Tried to access unknown attribute '$name'!");
		}
	}

	/**
	 * opens a connection to a imap server
	 *
	 * @param bool $_adminConnection create admin connection if true
	 * @param int $_timeout=null timeout in secs, if none given fmail pref or default of 20 is used
	 * @deprecated allready called by constructor automatic, parameters must be passed to constructor!
	 * @return boolean|PEAR_Error true on success, PEAR_Error of false on failure
	 */
	function openConnection($_adminConnection=false, $_timeout=null)
	{
		unset($_timeout);	// not used
		if ($_adminConnection !== $this->params['adminConnection'])
		{
			throw new egw_exception_wrong_parameter('need to set parameters on calling emailadmin_account->imapServer()!');
		}
	}

	/**
	 * getTimeOut
	 *
	 * @param string _use decide if the use is for IMAP or SIEVE, by now only the default differs
	 * @return int - timeout (either set or default 20/10)
	 */
	static function getTimeOut($_use='IMAP')
	{
		$timeout = $GLOBALS['egw_info']['user']['preferences']['mail']['connectionTimeout'];
		if (empty($timeout) || !($timeout > 0)) $timeout = $_use == 'SIEVE' ? 10 : 20; // this is the default value
		return $timeout;
	}

	/**
	 * Return description for EMailAdmin
	 *
	 * @return string
	 */
	public static function description()
	{
		return static::DESCRIPTION;
	}

	/**
	 * adds a account on the imap server
	 *
	 * @param array $_hookValues
	 * @return bool true on success, false on failure
	 */
	function addAccount($_hookValues)
	{
		unset($_hookValues);	// not used
		return true;
	}

	/**
	 * updates a account on the imap server
	 *
	 * @param array $_hookValues
	 * @return bool true on success, false on failure
	 */
	function updateAccount($_hookValues)
	{
		unset($_hookValues);	// not used
		return true;
	}

	/**
	 * deletes a account on the imap server
	 *
	 * @param array $_hookValues
	 * @return bool true on success, false on failure
	 */
	function deleteAccount($_hookValues)
	{
		unset($_hookValues);	// not used
		return true;
	}

	function disconnect()
	{

	}

	/**
	 * converts a foldername from current system charset to UTF7
	 *
	 * @param string $_folderName
	 * @return string the encoded foldername
	 */
	function encodeFolderName($_folderName)
	{
		if($this->mbAvailable) {
			return mb_convert_encoding($_folderName, "UTF7-IMAP", translation::charset());
		}

		// if not
		// we can encode only from ISO 8859-1
		return imap_utf7_encode($_folderName);
	}

	/**
	 * mailboxExists
	 *
	 * @param string $mailbox
	 * @return boolean
	 */
	function mailboxExist($mailbox)
	{
		$mailboxes = $this->listMailboxes($mailbox);

		$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//_debug_array($mboxes->count());
		foreach ($mboxes->getIterator() as $k =>$box)
		{
			if ($k!='user' && $k != '' && $k==$mailbox) return true; //_debug_array(array($k => $client->status($k)));
		}
		return false;
	}

	/**
	 * getSpecialUseFolders
	 *
	 * @return current mailbox, or if none check on INBOX, and return upon existance
	 */
	function getCurrentMailbox()
	{
		$mailbox = $this->currentMailbox();
		if (!empty($mailbox)) return $mailbox['mailbox'];
		if (empty($mailbox) && $this->mailboxExist('INBOX')) return 'INBOX';
		return null;
	}

	/**
	 * getSpecialUseFolders
	 *
	 * @return array with special use folders
	 */
	function getSpecialUseFolders()
	{
		$mailboxes = $this->getMailboxes('',0,true);
		$suF = array();
		foreach ($mailboxes as $k =>$box)
		{
			if ($box['MAILBOX']!='user' && $box['MAILBOX'] != '')
			{
				//error_log(__METHOD__.__LINE__.$k.'->'.array2string($box));
				if (isset($box['ATTRIBUTES'])&&!empty($box['ATTRIBUTES'])&&
					stripos(strtolower(array2string($box['ATTRIBUTES'])),'\noselect')=== false&&
					stripos(strtolower(array2string($box['ATTRIBUTES'])),'\nonexistent')=== false)
				{
					$suF[$box['MAILBOX']] = $box;
				}
			}
		}
		return $suF;
	}

	/**
	 * getStatus
	 *
	 * @param string $mailbox
	 * @return array with counters
	 */
	function getStatus($mailbox)
	{
		$mailboxes = $this->listMailboxes($mailbox,Horde_Imap_Client::MBOX_ALL,array(
				'attributes'=>true,
				'children'=>true, //child info
				'delimiter'=>true,
				'special_use'=>true,
			));

		$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//error_log(__METHOD__.__LINE__.array2string($mboxes->count()));
		foreach ($mboxes->getIterator() as $k =>$box)
		{
			if ($k!='user' && $k != '' && $k==$mailbox)
			{
				if (stripos(array2string($box['attributes']),'\noselect')=== false)
				{
					$status = $this->status($k);
					foreach ($status as $key => $v)
					{
						$_status[strtoupper($key)]=$v;
					}
					$_status['HIERACHY_DELIMITER'] = $_status['delimiter'] = $box['delimiter'];//$this->getDelimiter('user');
					$_status['ATTRIBUTES'] = $box['attributes'];
					//error_log(__METHOD__.__LINE__.$k.'->'.array2string($_status));
					return $_status;
				}
				else
				{
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Returns an array containing the names of the selected mailboxes
	 *
	 * @param   string  $reference          base mailbox to start the search (default is current mailbox)
	 * @param   string  $restriction_search false or 0 means return all mailboxes
	 *                                      true or 1 return only the mailbox that contains that exact name
	 *                                      2 return all mailboxes in that hierarchy level
	 * @param   string  $returnAttributes   true means return an assoc array containing mailbox names and mailbox attributes
	 *                                      false - the default - means return an array of mailboxes with only selected attributes like delimiter
	 *
	 * @return  mixed   array of mailboxes
	 */
	function getMailboxes($reference = ''  , $restriction_search = 0, $returnAttributes = false)
	{
		if ( is_bool($restriction_search) ){
			$restriction_search = (int) $restriction_search;
		}

		if ( is_int( $restriction_search ) ){
			switch ( $restriction_search ) {
			case 0:
				$mailbox = "*";
				break;
			case 1:
				$mailbox = $reference;
				$reference = '%';
				break;
			case 2:
				$mailbox = "%";
				break;
			}
		}else{
			if ( is_string( $restriction_search ) ){
				$mailbox = $restriction_search;
			}
		}
		//error_log(__METHOD__.__LINE__.$mailbox);
		$options = array(
				'attributes'=>true,
				'children'=>true, //child info
				'delimiter'=>true,
				'special_use'=>true,
				'sort'=>true,
			);
		if ($returnAttributes==false)
		{
			unset($options['attributes']);
			unset($options['children']);
			unset($options['special_use']);
		}
		$mailboxes = $this->listMailboxes($mailbox,Horde_Imap_Client::MBOX_ALL, $options);
		//$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//_debug_array($mboxes->count());
		foreach ((array)$mailboxes as $k =>$box)
		{
			//error_log(__METHOD__.__LINE__.' Box:'.$k.'->'.array2string($box));
			$ret[]=array('MAILBOX'=>$k,'ATTRIBUTES'=>$box['attributes'],'delimiter'=>$box['delimiter']);
		}
		return $ret;
	}

	/**
	 * Returns an array containing the names of the selected mailboxes
	 *
	 * @param   string  $reference          base mailbox to start the search (default is current mailbox)
	 * @param   string  $restriction_search false or 0 means return all mailboxes
	 *                                      true or 1 return only the mailbox that contains that exact name
	 *                                      2 return all mailboxes in that hierarchy level
	 *
	 * @return  mixed   array of mailboxes
	 */
	function listSubscribedMailboxes($reference = ''  , $restriction_search = 0)
	{
		if ( is_bool($restriction_search) ){
			$restriction_search = (int) $restriction_search;
		}

		if ( is_int( $restriction_search ) ){
			switch ( $restriction_search ) {
			case 0:
				$mailbox = "*";
				break;
			case 1:
				$mailbox = $reference;
				$reference = '%';
				break;
			case 2:
				$mailbox = "%";
				break;
			}
		}else{
			if ( is_string( $restriction_search ) ){
				$mailbox = $restriction_search;
			}
		}
		//error_log(__METHOD__.__LINE__.$mailbox);
		$options = array(
				'sort'=>true,
			);
		$mailboxes = $this->listMailboxes($mailbox,Horde_Imap_Client::MBOX_SUBSCRIBED, $options);
		//$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//_debug_array($mboxes->count());
		foreach ((array)$mailboxes as $k =>$box)
		{
			//error_log(__METHOD__.__LINE__.' Box:'.$k.'->'.array2string($box));
			$ret[]=$k;
		}
		return $ret;
	}

	/**
	 * examineMailbox
	 *
	 * @param string $mailbox
	 * @return array of counters for mailbox
	 */
	function examineMailbox($mailbox)
	{
		if ($mailbox=='') return false;
		$mailboxes = $this->listMailboxes($mailbox);

		$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//_debug_array($mboxes->count());
		foreach ($mboxes->getIterator() as $k =>$box)
		{
			//error_log(__METHOD__.__LINE__.array2string($box));
			if ($k!='user' && $k != '' && $k==$mailbox)
			{
				$status = $this->status($k, Horde_Imap_Client::STATUS_ALL | Horde_Imap_Client::STATUS_FLAGS | Horde_Imap_Client::STATUS_PERMFLAGS);
				//error_log(__METHOD__.__LINE__.array2string($status));
				foreach ($status as $key => $v)
				{
					$_status[strtoupper($key)]=$v;
				}
				if (!isset(self::$supports_keywords[$this->ImapServerId])) self::$supports_keywords[$this->ImapServerId]=(stripos(array2string($_status['FLAGS']),'$label')?true:false);
				return $_status;
			}
		}
		return false;
	}

	/**
	 * returns the supported capabilities of the imap server
	 * return false if the imap server does not support capabilities
	 *
	 * @deprecated use capability()
	 * @return array the supported capabilites
	 */
	function getCapabilities()
	{
		$cap = $this->capability();
		foreach ($cap as $c => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $v)
				{
					$cap[$c.'='.$v] = true;
				}
			}
		}
		return $cap;
	}

	/**
	 * Query a single capability
	 *
	 * @deprecated use queryCapability($capability)
	 * @param string $capability
	 * @return boolean
	 */
	function hasCapability($capability)
	{
		//return $this->queryCapability($capability);
		//error_log(__METHOD__.__LINE__.' '.$capability.'->'.array2string(self::$supports_keywords));
		if ($capability=='SUPPORTS_KEYWORDS')
		{
			return self::$supports_keywords[$this->ImapServerId];
		}
		$cap = $this->capability();
		foreach ($cap as $c => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $v)
				{
					$cap[$c.'='.$v] = true;
				}
			}
		}
		//error_log(__METHOD__.__LINE__.$capability.'->'.array2string($cap));
		if (isset($cap[$capability]) && $cap[$capability])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * return the delimiter used by the current imap server
	 * @param mixed _type (1=personal, 2=user/other, 3=shared)
	 * @return string the delimimiter
	 */
	function getDelimiter($_type=1)
	{
		switch ($_type)
		{
			case 'user':
			case 'other':
			case 2:
				$type=2;
				break;
			case 'shared':
			case '':
			case 3:
				$type=3;
				break;
			case 'personal':
			case 1:
			default:
				$type=1;
		}
		$namespaces = $this->getNamespaces();
		foreach ($namespaces as $nsp)
		{
			if ($nsp['type']==$type) $this->mailboxDelimiter = $nsp['delimiter'];
		}
		return $this->mailboxDelimiter;
	}

	/**
	 * get the effective Username for the Mailbox, as it is depending on the loginType
	 * @param string $_username
	 * @return string the effective username to be used to access the Mailbox
	 */
	function getMailBoxUserName($_username)
	{
		switch ($this->loginType)
		{
			case 'email':
				$_username = $_username;
				$accountID = $GLOBALS['egw']->accounts->name2id($_username);
				$accountemail = $GLOBALS['egw']->accounts->id2name($accountID,'account_email');
				//$accountemail = $GLOBALS['egw']->accounts->read($GLOBALS['egw']->accounts->name2id($_username,'account_email'));
				if (!empty($accountemail))
				{
					list($lusername,$domain) = explode('@',$accountemail,2);
					if (strtolower($domain) == strtolower($this->domainName) && !empty($lusername))
					{
						$_username = $lusername;
					}
				}
				break;

			case 'uidNumber':
				$_username = 'u'.$GLOBALS['egw']->accounts->name2id($_username);
				break;
		}
		return strtolower($_username);
	}

	/**
	 * Create mailbox string from given mailbox-name and user-name
	 *
	 * @param string $_folderName=''
	 * @return string utf-7 encoded (done in getMailboxName)
	 */
	function getUserMailboxString($_username, $_folderName='')
	{
		$nameSpaces = $this->getNameSpaceArray();

		if(!isset($nameSpaces['others'])) {
			return false;
		}

		$_username = $this->getMailBoxUserName($_username);
		if($this->loginType == 'vmailmgr' || $this->loginType == 'email' || $this->loginType == 'uidNumber') {
			$_username .= '@'. $this->domainName;
		}

		$mailboxString = $nameSpaces['others'][0]['name'] . $_username . (!empty($_folderName) ? $nameSpaces['others'][0]['delimiter'] . $_folderName : '');

		return $mailboxString;
	}

	/**
	 * get list of namespaces
	 *
	 * @return array with keys 'personal', 'shared' and 'others' and value array with values for keys 'name' and 'delimiter'
	 */
	function getNameSpaceArray()
	{
		static $types = array(
			Horde_Imap_Client::NS_PERSONAL => 'personal',
			Horde_Imap_Client::NS_OTHER    => 'others',
			Horde_Imap_Client::NS_SHARED   => 'shared'
		);

		foreach($this->getNamespaces() as $data)
		{
			//error_log(__METHOD__.__LINE__.array2string($data));
			if (isset($types[$data['type']]))
			{
				$result[$types[$data['type']]] = array(
					'name' => $data['name'],
					'prefix' => $data['name'],
					'prefix_present' => !empty($data['name']),
					'delimiter' => $data['delimiter'],
				);
			}
		}
		//error_log(__METHOD__.__LINE__.array2string($result));
		return $result;
	}

	/**
	 * return the quota for the current user
	 *
	 * @param string $mailboxName
	 * @return mixed the quota for the current user -> array with all available Quota Information, or false
	 */
	function getStorageQuotaRoot($mailboxName)
	{
		$storageQuota = $this->getQuotaRoot($mailboxName);
		foreach ($storageQuota as $qInfo)
		{
			if ($qInfo['storage'])
			{
				return array('USED'=>$qInfo['storage']['usage'],'QMAX'=>$qInfo['storage']['limit']);
			}
		}
		return false;
	}

	/**
	 * return the quota for another user
	 * used by admin connections only
	 *
	 * @param string $_username
	 * @param string $_what - what to retrieve either QMAX, USED or ALL is supported
	 * @returnmixed the quota for specified user (by what) or array with all available Quota Information, or false
	 */
	function getQuotaByUser($_username, $_what='QMAX')
	{
		$mailboxName = $this->getUserMailboxString($_username);
		$storageQuota = $this->getQuotaRoot($mailboxName);
		//error_log(__METHOD__.' Username:'.$_username.' Mailbox:'.$mailboxName.' Quota('.$_what.'):'.array2string($storageQuota));
		if ( PEAR::isError($storageQuota)) error_log(__METHOD__.$storageQuota->message);
		if(is_array($storageQuota) && (isset($storageQuota[$_what])||($_what=='ALL' && (isset($storageQuota['QMAX'])||isset($storageQuota['USED']))))) {
			//error_log(__METHOD__.' '.array2string($storageQuota).' '.$_what.' => '.(int)$storageQuota[$_what]);
			return ($_what=='ALL'?$storageQuota:(int)$storageQuota[$_what]);
		}

		return false;
	}

	/**
	 * returns information about a user
	 *
	 * Only a stub, as admin connection requires, which is only supported for Cyrus
	 *
	 * @param string $_username
	 * @return array userdata
	 */
	function getUserData($_username)
	{
		unset($_username);	// not used
		return array();
	}

	/**
	 * set userdata
	 *
	 * @param string $_username username of the user
	 * @param int $_quota quota in bytes
	 * @return bool true on success, false on failure
	 */
	function setUserData($_username, $_quota)
	{
		unset($_username, $_quota);	// not used
		return true;
	}

	/**
	 * check if imap server supports given capability
	 *
	 * @param string $_capability the capability to check for
	 * @return bool true if capability is supported, false if not
	 */
	function supportsCapability($_capability)
	{
		return $this->hasCapability($_capability);
	}

	/**
	 * Instance of emailadmin_sieve
	 *
	 * @var emailadmin_sieve
	 */
	private $sieve;

	public $scriptName;
	public $error;

	//public $error;

	/**
	 * Proxy former felamimail bosieve methods to internal emailadmin_sieve instance
	 *
	 * @param string $name
	 * @param array $params
	 */
	public function __call($name,array $params=null)
	{
		if ($this->debug) error_log(__METHOD__.'->'.$name.' with params:'.array2string($params));
		switch($name)
		{
			case 'installScript':
			case 'getScript':
			case 'setActive':
			case 'setEmailNotification':
			case 'getEmailNotification':
			case 'setRules':
			case 'getRules':
			case 'retrieveRules':
			case 'getVacation':
			case 'setVacation':
				if (is_null($this->sieve))
				{
					$this->sieve = new emailadmin_sieve($this);
					$this->scriptName =& $this->sieve->scriptName;
					$this->error =& $this->sieve->error;
				}
				$ret = call_user_func_array(array($this->sieve,$name),$params);
				//error_log(__CLASS__.'->'.$name.'('.array2string($params).') returns '.array2string($ret));
				return $ret;
		}
		throw new egw_exception_wrong_parameter("No method '$name' implemented!");
	}

	public function setVacationUser($_euser, $_scriptName, $_vacation)
	{
		if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.' User:'.array2string($_euser).' Scriptname:'.array2string($_scriptName).' VacationMessage:'.array2string($_vacation));
		if (is_null($this->sieve))
		{
			$this->sieve = new emailadmin_sieve();
			$this->scriptName =& $this->sieve->scriptName;
			$this->error =& $this->sieve->error;
			$this->sieve->icServer = $this;
		}
		return $this->sieve->setVacationUser($_euser, $_scriptName, $_vacation);
	}

	/**
	 * set the asyncjob for a timed vacation
	 *
	 * @param array $_vacation the vacation to set/unset
	 * @param string $_scriptName ; optional scriptName
	 * @param boolean $_reschedule ; do nothing but reschedule the job by 3 minutes
	 * @return  void
	 */
	function setAsyncJob ($_vacation, $_scriptName=null, $_reschedule=false)
	{
		// setting up an async job to enable/disable the vacation message
		$async = new asyncservice();
		$user = (isset($_vacation['account_id'])&&!empty($_vacation['account_id'])?$_vacation['account_id']:$GLOBALS['egw_info']['user']['account_id']);
		$async_id = (isset($_vacation['id'])&&!empty($_vacation['id'])?$_vacation['id']:"felamimail-vacation-$user");
		$async->delete($async_id); // ="felamimail-vacation-$user");
		$_scriptName = (!empty($_scriptName)?$_scriptName:(isset($_vacation['scriptName'])&&!empty($_vacation['scriptName'])?$_vacation['scriptName']:'felamimail'));
		$end_date = $_vacation['end_date'] + 24*3600;   // end-date is inclusive, so we have to add 24h
		if ($_vacation['status'] == 'by_date' && time() < $end_date && $_reschedule===false)
		{
			$time = time() < $_vacation['start_date'] ? $_vacation['start_date'] : $end_date;
			$async->set_timer($time,$async_id,'felamimail.bosieve.async_vacation',$_vacation+array('scriptName'=>$_scriptName),$user);
		}
		if ($_reschedule===true)
		{
			$time = time() + 60*3;
			unset($_vacation['next']);
			unset($_vacation['times']);
			if ($_reschedule) $async->set_timer($time,$async_id,'felamimail.bosieve.async_vacation',$_vacation+array('scriptName'=>$_scriptName),$user);
		}
 	}
}
