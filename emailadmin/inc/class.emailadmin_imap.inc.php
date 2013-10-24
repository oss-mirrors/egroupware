<?php
/**
 * EGroupware EMailAdmin: IMAP support using Horde_Imap_Client
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
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
	 * ImapServerId
	 *
	 * @var int
	 */
	var $ImapServerId;

	/**
	 * the password to be used for admin connections
	 *
	 * @var string
	 */
	var $adminPassword;

	/**
	 * the username to be used for admin connections
	 *
	 * @var string
	 */
	var $adminUsername;

	/**
	 * enable encryption
	 *
	 * @var int 0 = no, 2 = TLS, 3 = SSL
	 */
	public $encryption;

	/**
	 * Validate ssl certificate
	 *
	 * Currently not supported by Horde_Imap_Client, probably because no default certificate store in PHP!
	 *
	 * @var bool
	 */
	public $validatecert;

	/**
	 * the hostname/ip address of the imap server
	 *
	 * @var string
	 */
	var $host;

	/**
	 * the password for the user
	 *
	 * @var string
	 */
	var $password;

	/**
	 * the port of the imap server
	 *
	 * @var integer
	 */
	var $port = 143;

	/**
	 * the username
	 *
	 * @var string
	 */
	var $username;

	/**
	 * the domainname to be used for vmailmgr logins
	 *
	 * @var string
	 */
	var $domainName = false;

	/**
	 * is the mbstring extension available
	 *
	 * @var unknown_type
	 */
	var $mbAvailable;

	/**
	 * Mailboxes which get automatic created for new accounts (INBOX == '')
	 *
	 * @var array
	 */
	var $imapLoginType;
	var $defaultDomain;


	/**
	 * disable internal conversion from/to ut7
	 * get's used by Net_IMAP
	 *
	 * @var array
	 */
	var $_useUTF_7 = false;

	/**
	 * a debug switch
	 */
	var $debug = false;

	/**
	 * Sieve available
	 *
	 * @var boolean
	 */
	var $enableSieve = false;

	/**
	 * Hostname / IP of sieve host
	 *
	 * @var string
	 */
	var $sieveHost;

	/**
	 * Port of Sieve service
	 *
	 * @var int
	 */
	var $sievePort = 4190;

	/**
	 * True if connection is an admin connection
	 * @var boolean
	 */
	public $isAdminConnection = false;

	/**
	 * the construtor
	 *
	 * @return void
	 */
	function __construct()
	{
		if (function_exists('mb_convert_encoding'))
		{
			$this->mbAvailable = true;
		}

	}

	/**
	 * opens a connection to a imap server
	 *
	 * @param bool $_adminConnection create admin connection if true
	 * @param int $_timeout=null timeout in secs, if none given fmail pref or default of 20 is used
	 * @return boolean|PEAR_Error true on success, PEAR_Error of false on failure
	 */
	function openConnection($_adminConnection=false, $_timeout=null)
	{
		// if no explicit $_timeout given with the openConnection call, check mail preferences, defaulting to 20
		if (is_null($_timeout)) $_timeout = self::getTimeOut();

		if($_adminConnection)
		{
			$username	= $this->adminUsername;
			$password	= $this->adminPassword;
			$this->isAdminConnection = true;
		}
		else
		{
			$username	= $this->loginName;
			$password	= $this->password;
			$this->isAdminConnection = false;
		}

		parent::__construct(array(
			'username' => $username,
			'password' => $password,
			'hostspec' => $this->host,
			'port' => $this->port,
			'secure' => $this->encryption ? ($this->encryption == 2 ? 'tls' : ($this->encryption == 1?'tls':'ssl')) : null,
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
			if ($k!='user' && $k==$mailbox) return true; //_debug_array(array($k => $client->status($k)));
		}
		return false;
	}

	/**
	 * getStatus
	 *
	 * @param string $mailbox
	 * @return array with counters
	 */
	function getStatus($mailbox)
	{
		$mailboxes = $this->listMailboxes($mailbox);

		$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
		//_debug_array($mboxes->count());
		foreach ($mboxes->getIterator() as $k =>$box)
		{
			if ($k!='user' && $k==$mailbox)
			{
				$status = $this->status($k);
				foreach ($status as $k => $v)
				{
					$_status[strtoupper($k)]=$v;
				}
				return $_status;
			}
		}
		return false;
	}

	function getMailboxes($mailbox)
	{
		$mailboxes = $this->listMailboxes($mailbox,Horde_Imap_Client::MBOX_ALL, array('flat' => true));
	}

	function listSubscribedMailboxes($mailbox)
	{
		$mailboxes = $this->listMailboxes($mailbox,Horde_Imap_Client::MBOX_SUBSCRIBED, array('flat' => true));
//_debug_array($mailboxes);
	}

	/**
	 * examineMailbox
	 *
	 * @param string $_folderName
	 * @return array of counters for mailbox
	 */
	function examineMailbox($_folderName)
	{
		$mailboxes = $this->listMailboxes($mailbox);

		$mboxes = new Horde_Imap_Client_Mailbox_List($mailboxes);
	//_debug_array($mboxes->count());
		foreach ($mboxes->getIterator() as $k =>$box)
		{
			if ($k!='user' && $k==$mailbox)
			{
				$status = $this->status($k);
				foreach ($status as $k => $v)
				{
					$_status[strtoupper($k)]=$v;
				}
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
				foreach ($v as $sc => $v)
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

		$cap = $this->capability();
		foreach ($cap as $c => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $sc => $v)
				{
					$cap[$c.'='.$v] = true;
				}
			}
		}
		//_debug_array($cap);
		if (isset($cap[$_capability]) && $cap[$_capability])
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
		switch ($type)
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
		foreach ($namespaces as $ns => $nsp)
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
			if (isset($types[$data['type']]))
			{
				$result[$types[$data['type']]] = array(
					'name' => $data['name'],
					'delimiter' => $data['delimiter'],
				);
			}
		}

		return $result;
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
		$storageQuota = $this->getStorageQuota($mailboxName);
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
