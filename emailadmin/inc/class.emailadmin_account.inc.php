<?php
/**
 * EGroupware EMailAdmin: Mail accounts
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Stylite AG <info@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Mail accounts supports 3 types of accounts:
 *
 * a) personal mail accounts either created by admin or user themselfs
 * b) accounts for multiple users or groups created by admin
 * c) configuration to administrate a mail-server
 *
 * To store the accounts 4 tables are used
 * - egw_ea_accounts all data except credentials and identities (incl. signature)
 * - egw_ea_valid for which users an account is valid 1:N relation to accounts table
 * - egw_ea_credentials username/password for various accounts and types (imap, smtp, admin)
 * - egw_ea_identities identities of given account and user incl. standard identity of account
 *
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
 * @property-read string $acc_smtp_host smtp hostname
 * @property-read int $acc_smtp_ssl 0=none, 1=starttls, 2=tls, 3=ssl, &8=validate certificate
 * @property-read int $acc_smtp_port smtp port
 * @property-read string $acc_smtp_username if smtp auth required
 * @property-read string $acc_smtp_password
 * @property-read string $acc_smtp_type smtp class to use, default emailadmin_smtp
 * @property-read string $acc_imap_type imap class to use, default emailadmin_imap
 * @property-read string $acc_imap_logintype how to construct login-name standard, vmailmgr, admin, uidNumber
 * @property-read string $acc_domain domain name
 * @property-read boolean $acc_imap_administration enable administration
 * @property-read string $acc_admin_username
 * @property-read string $acc_admin_password
 * @property-read boolean $acc_further_identities are non-admin users allowed to create further identities
 * @property-read boolean $acc_user_editable are non-admin users allowed to edit this account, if it is for them
 * @property-read int $acc_modified timestamp of last modification
 * @property-read int $acc_modifier account_id of last modifier
 * @property-read int $ident_id standard identity
 * @property-read string $ident_realname real name
 * @property-read string $ident_email email address
 * @property-read string $ident_org organisation
 * @property-read string $ident_signature signature text (html)
 * @property-read array $params parameters passed to constructor (all above as array)
 */
class emailadmin_account
{
	const APP = 'emailadmin';
	/**
	 * Table with mail-accounts
	 */
	const TABLE = 'egw_ea_accounts';
	/**
	 * Table holding 1:N relation for which EGroupware accounts a mail-account is valid
	 */
	const VALID_TABLE = 'egw_ea_valid';
	/**
	 * Join with egw_ea_valid
	 */
	const VALID_JOIN = 'JOIN egw_ea_valid ON egw_ea_valid.acc_id=egw_ea_accounts.acc_id ';
	/**
	 * Table with identities and signatures
	 */
	const IDENTITIES_TABLE = 'egw_ea_identities';
	/**
	 * Join with standard identity of main-account
	 */
	const IDENTITY_JOIN = 'JOIN egw_ea_identities ON egw_ea_identities.ident_id=egw_ea_accounts.ident_id';
	/**
	 * Order for search: first group-profiles, then general profiles, then personal profiles
	 */
	const DEFAULT_ORDER = 'account_id ASC,acc_name ASC';

	/**
	 * No SSL
	 */
	const SSL_NONE = 0;
	/**
	 * STARTTLS on regular tcp connection/port
	 */
	const SSL_STARTTLS = 1;
	/**
	 * SSL (inferior to TLS!)
	 */
	const SSL_SSL = 3;
	/**
	 * require TLS version 1+, no SSL version 2 or 3
	 */
	const SSL_TLS = 2;
	/**
	 * if set, verify certifcate (currently not implemented in Horde_Imap_Client!)
	 */
	const SSL_VERIFY = 8;

	/**
	 * Reference to global db object
	 *
	 * @var egw_db
	 */
	static protected $db;

	/**
	 * Parameters passed to contructor
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Instance of imap server
	 *
	 * @var emailadmin_imap
	 */
	protected $imapServer;

	/**
	 * Instance of old imap server
	 *
	 * @var emailadmin_oldimap
	 */
	protected $oldImapServer;

	/**
	 * Instance of smtp server
	 *
	 * @var emailadmin_smtp
	 */
	protected $smtpServer;

	/**
	 * Constructor
	 *
	 * @param array $params
	 */
	protected function __construct(array $params)
	{
		$this->params = $params;

		unset($this->imapServer);
		unset($this->oldImapServer);
		unset($this->smtpServer);
	}

	/**
	 * Get new Horde_Imap_Client imap server object
	 *
	 * @param $_adminConnection=false true: connect with admin credentials
	 * @param int $_timeout=null timeout in secs, if none given fmail pref or default of 20 is used
	 * @return emailadmin_imap
	 */
	public function imapServer($_adminConnection=false, $_timeout=null)
	{
		if (!isset($this->imapServer) )
		{
			$class = emailadmin_bo::getIcClass($this->params['acc_imap_type']);
			$this->imapServer = new $class($this->params, $_adminConnection, $_timeout);
			$this->imapServer->ImapServerId = $this->params['acc_id'];
		}
		return $this->imapServer;
	}

	/**
	 * Get old Net_IMAP imap server object
	 *
	 * @return emailadmin_oldimap
	 */
	public function oldImapServer()
	{
		if (!isset($this->imapServer))
		{
			$class = emailadmin_bo::getIcClass($this->params['acc_imap_type'], true);
			$this->oldImapServer = new $class();
			$this->oldImapServer->ImapServerId = $this->params['acc_id'];
			$this->oldImapServer->host = $this->params['acc_imap_host'];
			$this->oldImapServer->encryption = $this->params['acc_imap_ssl'] & ~self::SSL_VERIFY;
			$this->oldImapServer->port 	= $this->params['acc_imap_port'];
			$this->oldImapServer->validatecert	= (boolean)($this->params['acc_imap_ssl'] & self::SSL_VERIFY);
			$this->oldImapServer->username 	= $this->params['acc_imap_username'];
			$this->oldImapServer->loginName 	= $this->params['acc_imap_password'];
			$this->oldImapServer->password	= $this->params['acc_imap_password'];
			$this->oldImapServer->enableSieve = (boolean)$this->params['acc_sieve_enabled'];
			$this->oldImapServer->loginType = $this->params['acc_imap_logintype'];
			$this->oldImapServer->domainName = $this->params['acc_domain'];
		}
		return $this->oldImapServer;
	}

	/**
	 * Get smtp server object
	 *
	 * @return emailadmin_smtp
	 */
	public function smtpServer()
	{
		if (!isset($this->smtpServer))
		{
			$class = $this->params['acc_smtp_type'];
			if ($class=='defaultsmtp') $class='emailadmin_smtp';
			$this->smtpServer = new $class($this->params);
			$this->smtpServer->editForwardingAddress = false;
			$this->smtpServer->host = $this->params['acc_smtp_host'];
			$this->smtpServer->port = $this->params['acc_smtp_port'];
			switch($this->params['acc_smtp_ssl'])
			{
				case self::SSL_SSL:
					$this->smtpServer->host = 'ssl://'.$this->smtpServer->host;
					break;
				case self::SSL_TLS:
					$this->smtpServer->host = 'tls://'.$this->smtpServer->host;
					break;
				case self::SSL_STARTTLS:
					throw new egw_exception_wrong_parameter('STARTTLS currently not supported for SMTP');
			}
			$this->smtpServer->smtpAuth = !empty($this->params['acc_smtp_username']);
			$this->smtpServer->username = $this->params['acc_smtp_username'];
			$this->smtpServer->password = $this->params['acc_smtp_username'];
			$this->smtpServer->defaultDomain = $this->params['acc_domain'];
		}
		return $this->smtpServer;
	}

	/**
	 * Get identities object
	 *
	 * @return identities connected to a server object
	 */
	public function identities()
	{
	}

	/**
	 * Give read access to protected parameters in $this->params
	 *
	 * @param type $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->$name))
		{
			return $this->$name;
		}
		return $this->params[$name];
	}

	/**
	 * Read/return account object for given $acc_id
	 *
	 * @param int $acc_id
	 * @param boolean $only_current_user=true true: only return accounts valid for current user
	 * @return email_account
	 * @throws egw_exception_not_found if account was not found (or not valid for current user)
	 */
	public static function read($acc_id, $only_current_user=true)
	{
		$memberships = $GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id'], true);
		$memberships[] = $GLOBALS['egw_info']['user']['account_id'];
		$memberships[] = 0;	// marks accounts valid for everyone

		$where = array(self::TABLE.'.acc_id='.(int)$acc_id);
		if ($only_current_user)
		{
			$where[] = self::$db->expression(self::VALID_TABLE, array('account_id' => $memberships));
		}
		if (!($data = self::$db->select(self::TABLE, 'DISTINCT '.self::TABLE.'.*,'.self::IDENTITIES_TABLE.'.*',
			$where, __LINE__, __FILE__, false, '', self::APP, 0, self::IDENTITY_JOIN.' '.self::VALID_JOIN)->fetch()))
		{
			throw new egw_exception_not_found;
		}
		$data += emailadmin_credentials::read($acc_id);
		//error_log(__METHOD__."($acc_id, $only_current_user) returning ".array2string($data));

		return new emailadmin_account($data);
	}

	/**
	 * Create new account object from given data AND store it to database
	 *
	 * @param array $data
	 * @return emailadmin_account
	 * @throws egw_exception_db
	 */
	public static function create(array $data)
	{
		return new emailadmin_account(self::write($data));
	}

	/**
	 * Save account data to db
	 *
	 * @param array $data
	 * @return array
	 * @throws egw_exception_db
	 */
	public function update(array $data=array())
	{
		$this->__construct(self::write(array_merge($this->params, (array)$data)));

		return $this;
	}

	/**
	 * Save account data to db
	 *
	 * @param array $data
	 * @return array $data plus added values for keys acc_id, ident_id from insert
	 * @throws egw_exception_wrong_parameter if called static without data-array
	 * @throws egw_exception_db
	 */
	public static function write(array $data)
	{
		error_log(__METHOD__."(".array2string($data).")");
		$data['acc_modifier'] = $GLOBALS['egw_info']['user']['account_id'];
		$data['acc_modified'] = time();

		// store account data
		$where = $data['acc_id'] ? array('acc_id' => $data['acc_id']) : false;
		self::$db->insert(self::TABLE, $data, $where, __LINE__, __FILE__, self::APP);
		if (!$data['acc_id'])
		{
			$data['acc_id'] = self::$db->get_last_insert_id(self::TABLE, 'acc_id');
		}
		// store identity
		$iwhere = $data['ident_id'] ? array('ident_id' => $data['ident_id']) : false;
		self::$db->insert(self::IDENTITIES_TABLE, $data, $iwhere, __LINE__, __FILE__, self::APP);
		if (!$data['ident_id'])
		{
			$data['ident_id'] = self::$db->get_last_insert_id(self::IDENTITIES_TABLE, 'ident_id');
			self::$db->update(self::TABLE, array(
				'ident_id' => $data['ident_id'],
			), array(
				'acc_id' => $data['acc_id'],
			), __LINE__, __FILE__, self::APP);
		}
		// make account valid for given owner
		if (!isset($data['account_id']))
		{
			$data['account_id'] = $GLOBALS['egw_info']['user']['account_id'];
		}
		$old_account_ids = array();
		if ($where)
		{
			foreach(self::$db->select(self::VALID_TABLE, 'account_id', $where,
				__LINE__, __FILE__, false, '', self::APP) as $row)
			{
				$old_account_ids[] = (int)$row['account_id'];
			}
			if (($ids_to_remove = array_diff((array)$data['account_id'], $old_account_ids)))
			{
				self::$db->delete(self::VALID_TABLE, $where+array(
					'account_id' => $ids_to_remove,
				), __LINE__, __FILE__, self::APP);
			}
		}
		foreach((array)$data['account_id'] as $account_id)
		{
			if (!in_array($account_id, $old_account_ids))
			{
				self::$db->insert(self::VALID_TABLE, array(
					'acc_id' => $data['acc_id'],
					'account_id' => $account_id,
				), false, __LINE__, __FILE__, self::APP);
			}
			// add imap credentials
			$cred_type = $data['acc_imap_username'] == $data['acc_smtp_username'] &&
				$data['acc_imap_password'] == $data['acc_smtp_password'] ? 3 : 1;
			emailadmin_credentials::write($data['acc_id'], $data['acc_imap_username'], $data['acc_imap_password'],
				$cred_type, $account_id, $data['acc_imap_cred_id']);
			// add smtp credentials if necessary and different from imap
			if ($data['acc_smtp_username'] && $cred_type != 3)
			{
				emailadmin_credentials::write($data['acc_id'], $data['acc_smtp_username'], $data['acc_smtp_password'],
					2, $account_id, $data['acc_smtp_cred_id'] != $data['acc_imap_cred_id'] ?
						$data['acc_smtp_cred_id'] : null);
			}
		}
		return $data;
	}

	/**
	 * Return array with acc_id => acc_name or account-object pairs
	 *
	 * @param boolean $only_current_user=true return only accounts for current user
	 * @param boolean $just_name=true return just acc_name or emailadmin_account objects
	 * @param string $order_by='acc_name ASC'
	 * @param int|boolean $offset=false offset or false to return all
	 * @param int $num_rows=0 number of rows to return, 0=default from prefs (if $offset !== false)
	 * @return array with acc_id => acc_name or emailadmin_account objects
	 */
	public static function search($only_current_user=true, $just_name=true, $order_by=null,$offset=false, $num_rows=0)
	{
		$where = array();
		if ($only_current_user)
		{
			$memberships = $GLOBALS['egw']->accounts->memberships($GLOBALS['egw_info']['user']['account_id'], true);
			$memberships[] = $GLOBALS['egw_info']['user']['account_id'];
			$memberships[] = 0;	// marks accounts valid for everyone
			$where[] = self::$db->expression(self::VALID_TABLE, array('account_id' => $memberships));
		}
		$cols = $just_name ? '.acc_id,acc_name' : '.*,'.self::IDENTITIES_TABLE.'.*';

		if (empty($order_by) || !preg_match('/^[a-z_]+ (ASC|DESC)$/i', $order_by))
		{
			$order_by = self::DEFAULT_ORDER;
		}
		$results = array();
		foreach(self::$db->select(self::TABLE, 'DISTINCT '.self::TABLE.$cols,
			$where, __LINE__, __FILE__, $offset, 'ORDER BY '.$order_by, self::APP, $num_rows,
			self::IDENTITY_JOIN.' '.self::VALID_JOIN) as $row)
		{
			if (!$just_name) $row += emailadmin_credentials::read($row['acc_id']);
			$results[$row['acc_id']] = $just_name ? $row['acc_name'] : new emailadmin_account($row);
		}
		return $results;
	}

	/**
	 * Init our static properties
	 */
	static public function init_static()
	{
		self::$db = $GLOBALS['egw']->db;
	}
}

// some testcode, if this file is called via it's URL (you need to uncomment!)
/*if (isset($_SERVER['SCRIPT_FILENAME']) && $_SERVER['SCRIPT_FILENAME'] == __FILE__)
{
	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'currentapp' => 'home',
			'nonavbar' => true,
		),
	);
	include_once '../../header.inc.php';

	emailadmin_account::init_static();

	foreach(emailadmin_account::search() as $acc_id => $acc_name)
	{
		echo "<p>$acc_id: <a href='{$_SERVER['PHP_SELF']}?acc_id=$acc_id'>$acc_name</a></p>\n";
	}
	if (isset($_GET['acc_id']) && (int)$_GET['acc_id'] > 0)
	{
		$account = emailadmin_account::read((int)$_GET['acc_id']);
		_debug_array($account);
	}
}*/

// need to be after test-code!
emailadmin_account::init_static();
