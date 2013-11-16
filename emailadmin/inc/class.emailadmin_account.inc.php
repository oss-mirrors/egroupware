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
class emailadmin_account implements ArrayAccess
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
	const DEFAULT_ORDER = 'egw_ea_valid.account_id ASC,ident_org ASC,ident_realname ASC,acc_name ASC';

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
	 * SQL to query valid account_id's as comma-separated as subquery
	 *
	 * False if not supported by dbms.
	 *
	 * @var string
	 */
	protected static $valid_account_id_sql;

	/**
	 * Instanciated account object by acc_id, read acts as singelton
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Cache for emailadmin_account::read() to minimize database access
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Cache for emailadmin_account::search() to minimize database access
	 */
	protected static $search_cache = array();

	/**
	 * Constructor
	 *
	 * @param array $params
	 * @param boolean $load_smtp_auth_session=true true: load/set username/password for smtp auth
	 */
	protected function __construct(array $params, $load_smtp_auth_session=true)
	{
		// read credentials from database
		$params += emailadmin_credentials::read($params['acc_id']);

		if (!empty($params['acc_imap_logintype']) && !isset($params['acc_imap_username']) &&
			$GLOBALS['egw_info']['user']['account_id'])
		{
			// get usename/password from current user, let it overwrite credentials for all/no session
			$params = emailadmin_credentials::from_session(
				($load_smtp_auth_session ? array() : array('acc_smtp_auth_session' => false)) + $params
			) + $params;
		}
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
	 * Get identities of given or current account (for current user!)
	 *
	 * Standard identity is always first (as it has account_id=0 and we order account_id ASC).
	 *
	 * @param int|array|emailadmin_account $account=null default this account
	 * @return Iterator ident_id => identity_name of identity
	 */
	public function identities($account=null, $replace_placeholders=true)
	{
		if (!$account) $account = $this;
		$acc_id = is_scalar($account) ? $account : $account['acc_id'];

		$rs = self::$db->select(self::IDENTITIES_TABLE, 'ident_id,ident_realname,ident_org,ident_email', array(
			'acc_id' => $acc_id,
			'account_id' => self::memberships(),
		), __LINE__, __FILE__, false, 'ORDER BY account_id,ident_realname,ident_org,ident_email', self::APP);

		return new egw_db_callback_iterator($rs, __CLASS__.'::identity_name', array($replace_placeholders),
			function($row) { return $row['ident_id'];});
	}

	/**
	 * Replace placeholders like {{n_fn}} in an identity
	 *
	 * For full list of placeholders see addressbook_merge.
	 *
	 * @param array|emailadmin_account $identity=null
	 * @param boolean $replace_placeholders=false should placeholders like {{n_fn}} be replaced
	 * @return array with modified fields
	 */
	public /*static*/ function replace_placeholders($identity=null)
	{
		static $fields = array('ident_realname','ident_org','ident_email','ident_signature');

		if (!$identity && isset($this)) $identity = $this;
		if (!is_array($identity) && !is_a($identity, 'emailadmin_account'))
		{
			throw new egw_exception_wrong_parameter(__METHOD__."() requires an identity or account as first parameter!");
		}
		$to_replace = array();
		foreach($fields as $name)
		{
			if (strpos($identity[$name], '{{') !== false || strpos($identity[$name], '$$') !== false)
			{
				$to_replace[$name] = $identity[$name];
			}
		}
		if ($to_replace)
		{
			static $merge=null;
			if (!isset($merge)) $merge = new addressbook_merge();
			foreach($to_replace as $name => &$value)
			{
				$err = null;
				$value = $merge->merge_string($value,
					(array)accounts::id2name($GLOBALS['egw_info']['user']['account_id'], 'person_id'),
					$err, $name == 'ident_signature' ? 'text/html' : 'text/plain');
			}
		}
		//error_log(__METHOD__."(".array2string($identity).") returning ".array2string($to_replace));
		return $to_replace;
	}

	/**
	 * Read an identity
	 *
	 * @param int $ident_id
	 * @param boolean $replace_placeholders=false should placeholders like {{n_fn}} be replaced
	 * @return array
	 * @throws egw_exception_not_found
	 */
	public static function read_identity($ident_id, $replace_placeholders=false)
	{
		if (!($data = self::$db->select(self::IDENTITIES_TABLE, '*', array(
			'ident_id' => $ident_id,
			'account_id' => self::memberships(),
		), __LINE__, __FILE__, false, '', self::APP)->fetch()))
		{
			throw new egw_exception_not_found();
		}
		if ($replace_placeholders)
		{
			$data = array_merge($data, self::replace_placeholders($data));
		}
		return $data;
	}

	/**
	 * Store an identity in database
	 *
	 * Can be called static, if identity is given as parameter
	 *
	 * @param array|emailadmin_account $identity=null default standard identity of current account
	 * @return int ident_id of new/updated identity
	 */
	public /*static*/ function save_identity($identity=null)
	{
		if (!$identity && isset($this)) $identity = $this;
		if (!is_array($identity) && !is_a($identity, 'emailadmin_account'))
		{
			throw new egw_exception_wrong_parameter(__METHOD__."() requires an identity or account as first parameter!");
		}
		if (!($identity['acc_id'] > 0))
		{
			throw new egw_exception_wrong_parameter(__METHOD__."() no account / acc_id specified in identity!");
		}
		$data = array(
			'acc_id' => $identity['acc_id'],
			'ident_realname' => $identity['ident_realname'],
			'ident_org' => $identity['ident_org'],
			'ident_email' => $identity['ident_email'],
			'ident_signature' => $identity['ident_signature'],
			'account_id' => $identity['account_id'],
		);
		if ($identity['ident_id'] > 0)
		{
			self::$db->update(self::IDENTITIES_TABLE, $data, array(
				'ident_id' => $identity['ident_id'],
			), __LINE__, __FILE__, self::APP);

			return $identity['ident_id'];
		}
		self::$db->insert(self::IDENTITIES_TABLE, $data, false, __LINE__, __FILE__, self::APP);

		return self::$db->get_last_insert_id(self::IDENTITIES_TABLE, 'ident_id');
	}

	/**
	 * Delete given identity
	 *
	 * @param int $ident_id
	 * @return int number off affected rows
	 * @throws egw_exception_wrong_parameter if identity is standard identity of existing account
	 */
	public static function delete_identity($ident_id)
	{
		if (($acc_id = self::$db->select(self::TABLE, 'acc_id', array('ident_id' => $ident_id),
			__LINE__, __FILE__, 0, '', self::APP, 1)->fetchColumn()))
		{
			throw new egw_exception_wrong_parameter("Can not delete identity #$ident_id used as standard identity in account #$acc_id!");
		}
		self::$db->delete(self::IDENTITIES_TABLE, array('ident_id' => $ident_id), __LINE__, __FILE__, self::APP);

		return self::$db->affected_rows();
	}

	/**
	 * Give read access to protected parameters in $this->params
	 *
	 * @param type $name
	 * @return mixed
	 */
	public function __get($name)
	{
		switch($name)
		{
			case 'acc_imap_administration':	// no longer stored in database
				return !empty($this->params['acc_imap_admin_username']);

			case 'params':
				return $this->params;
		}
		return $this->params[$name];
	}

	/**
	 * Give read access to protected parameters in $this->params
	 *
	 * @param type $name
	 * @return mixed
	 */
	public function __isset($name)
	{
		switch($name)
		{
			case 'acc_imap_administration':	// no longer stored in database
				return true;

			case 'params':
				return isset($this->params);
		}
		return isset($this->params[$name]);
	}

	/**
	 * ArrayAccess to emailadmin_account
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * ArrayAccess to emailadmin_account
	 *
	 * @param string $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * ArrayAccess requires it but we dont want to give public write access
	 *
	 * Protected access has to use protected attributes!
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @throws egw_exception_wrong_parameter
	 */
	public function offsetSet($offset, $value)
	{
		throw new egw_exception_wrong_parameter(__METHOD__."($offset, $value) No write access through ArrayAccess interface of emailadmin_account!");
	}

	/**
	 * ArrayAccess requires it but we dont want to give public write access
	 *
	 * Protected access has to use protected attributes!
	 *
	 * @param string $offset
	 * @throws egw_exception_wrong_parameter
	 */
	public function offsetUnset($offset)
	{
		throw new egw_exception_wrong_parameter(__METHOD__."($offset) No write access through ArrayAccess interface of emailadmin_account!");
	}

	/**
	 * Check which rights current user has on mail-account
	 *
	 * @param int $rights EGW_ACL_(READ|EDIT|DELETE)
	 * @param array|emailadmin_account $account=null default use this
	 * @return boolean
	 */
	public /*static*/ function check_access($rights, $account=null)
	{
		if (!isset($account)) $account = $this;

		if (!is_array($account) && !is_a($account, 'emailadmin_account'))
		{
			throw new egw_exception_wrong_parameter('$account must be either an array or an emailadmin_account object!');
		}

		$access = false;
		// emailadmin has all rights
		if (isset($GLOBALS['egw_info']['user']['apps']['emailadmin']))
		{
			$access = true;
			$reason = 'user is EMailAdmin';
		}
		else
		{
			// check if account is for current user, if not deny access
			$memberships = self::memberships();
			$memberships[] = '';	// edit uses '' for everyone

			if (array_intersect((array)$account['account_id'], $memberships))
			{
				switch($rights)
				{
					case EGW_ACL_READ:
						$access = true;
						break;

					case EGW_ACL_EDIT:
					case EGW_ACL_DELETE:
						// users have only edit/delete rights on accounts marked as user-editable AND belonging to them personally
						if (!$account['acc_user_editable'])
						{
							$access = false;
							$reason = 'account not user editable';
						}
						elseif (!in_array($GLOBALS['egw_info']['user']['account_id'], (array)$account['account_id']))
						{
							$access = false;
							$reason = 'no edit/delete for public (not personal) account';
						}
						else
						{
							$access = true;
							$reason = 'user editable personal account';
						}
						break;
				}
			}
			else
			{
				$reason = 'account not valid for current user'.array2string($account['account_id']);
			}
		}
		//error_log(__METHOD__."($rights, $account[acc_id]: $account[acc_name]) returning ".array2string($access).' '.$reason);
		return $access;
	}

	/**
	 * Read/return account object for given $acc_id
	 *
	 * @param int $acc_id
	 * @param boolean $only_current_user=true true: only return accounts valid for current user
	 * @param boolean $load_smtp_auth_session=true true: load/set username/password for smtp auth
	 * @return email_account
	 * @throws egw_exception_not_found if account was not found (or not valid for current user)
	 */
	public static function read($acc_id, $only_current_user=true, $load_smtp_auth_session=true)
	{
		error_log(__METHOD__."($acc_id, $only_current_user, $load_smtp_auth_session)");
		// some caching, but only for regular usage/users
		if ($only_current_user && $load_smtp_auth_session)
		{
			// act as singleton: if we already have an instance, return it
			if (isset(self::$instances[$acc_id]))
			{
				error_log(__METHOD__."($acc_id) returned existing instance");
				return self::$instances[$acc_id];
			}
			// not yet an instance, create one
			if (isset(self::$cache[$acc_id]))
			{
				error_log(__METHOD__."($acc_id) created instance from cached data");
				return self::$instances[$acc_id] = new emailadmin_account(self::$cache[$acc_id]);
			}
			$data =& self::$cache[$acc_id];
		}
		$where = array(self::TABLE.'.acc_id='.(int)$acc_id);
		if ($only_current_user)
		{
			$where[] = self::$db->expression(self::VALID_TABLE, self::VALID_TABLE.'.', array('account_id' => self::memberships()));
		}
		$cols = self::TABLE.'.*,'.self::IDENTITIES_TABLE.'.*';
		if (self::$valid_account_id_sql)
		{
			$cols .= ','.self::$valid_account_id_sql.' AS account_id';
		}
		if (!($data = self::$db->select(self::TABLE, $cols, $where, __LINE__, __FILE__,
			false, 'GROUP BY '.self::TABLE.'.acc_id', self::APP, 0, self::IDENTITY_JOIN.' '.self::VALID_JOIN)->fetch()))
		{
			throw new egw_exception_not_found(lang('Account not found!').' (acc_id='.array2string($acc_id).')');
		}
		if (!self::$valid_account_id_sql)
		{
			$data['account_id'] = array();
			foreach(self::$db->select(self::VALID_TABLE, 'account_id', array('acc_id' => $acc_id),
				__LINE__, __FILE__, false, '', self::APP) as $row)
			{
				$data['account_id'][] = $row['account_id'];
			}
		}
		$data = self::db2data($data);
		//error_log(__METHOD__."($acc_id, $only_current_user) returning ".array2string($data));

		if ($only_current_user && $load_smtp_auth_session)
		{
			error_log(__METHOD__."($acc_id) creating instance and caching data read from db");
			$ret =& self::$instances[$acc_id];
		}
		return $ret = new emailadmin_account($data, $load_smtp_auth_session);
	}

	/**
	 * Transform data returned from database (currently only fixing bool values)
	 *
	 * @param array $data
	 * @return array
	 */
	protected static function db2data(array $data)
	{
		foreach(array('acc_sieve_enabled','acc_further_identities','acc_user_editable','acc_smtp_auth_session') as $name)
		{
			if (isset($data[$name]))
			{
				$data[$name] = self::$db->from_bool($data[$name]);
			}
		}
		if (isset($data['account_id']) && !is_array($data['account_id']))
		{
			$data['account_id'] = explode(',', $data['account_id']);
		}
		return $data;
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
		//error_log(__METHOD__."(".array2string($data).")");
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
		if (!($data['ident_id'] > 0))
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
				$old_account_ids[] = $row['account_id'];
			}
			if (($ids_to_remove = array_diff($old_account_ids, (array)$data['account_id'])))
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
		}
		// check if we have an account_id for which to store credentials
		foreach(array('acc_imap_account_id', 'acc_smtp_account_id') as $name)
		{
			if (!isset($data[$name]))
			{
				$data[$name] = count($data['account_id']) == 1 ? $data['account_id'][0] : 0;
			}
			// account of credentials is not direct in accounts valid for mail account
			elseif (!in_array($data[$name], $data['account_id']))
			{
				// check further with memberships and 0=all
				// if still not in, update with new account_id
				if (!array_intersect(self::memberships(), $data['account_id']))
				{
					$data[$name] = count($data['account_id']) == 1 ? $data['account_id'][0] : 0;
				}
			}
		}
		// add imap credentials
		$cred_type = $data['acc_imap_username'] == $data['acc_smtp_username'] &&
			$data['acc_imap_password'] == $data['acc_smtp_password'] &&
			$data['acc_imap_account_id'] == $data['acc_smtp_account_id'] ? 3 : 1;
		emailadmin_credentials::write($data['acc_id'], $data['acc_imap_username'], $data['acc_imap_password'],
			$cred_type, $data['acc_imap_account_id'], $data['acc_imap_cred_id']);
		// add smtp credentials if necessary and different from imap
		if ($data['acc_smtp_username'] && $cred_type != 3)
		{
			emailadmin_credentials::write($data['acc_id'], $data['acc_smtp_username'], $data['acc_smtp_password'],
				2, $data['acc_smtp_account_id'], $data['acc_smtp_cred_id'] != $data['acc_imap_cred_id'] ?
					$data['acc_smtp_cred_id'] : null);
		}
		// store or delete admin credentials
		if ($data['acc_imap_admin_username'] && $data['acc_imap_admin_password'])
		{
			emailadmin_credentials::write($data['acc_id'], $data['acc_imap_admin_username'],
				$data['acc_imap_admin_password'], emailadmin_credentials::ADMIN, 0,
				$data['acc_imap_admin_cred_id']);
		}
		else
		{
			emailadmin_credentials::delete($data['acc_id'], 0, emailadmin_credentials::ADMIN);
		}
		self::cache_invalidate($data['acc_id']);

		return $data;
	}

	/**
	 * Delete accounts or account related data belonging to given mail or user account
	 *
	 * @param int|array $acc_id mail account
	 * @param int $account_id=null user or group
	 * @return int number of deleted mail accounts or null if only user-data was deleted and no full mail accounts
	 */
	public static function delete($acc_id, $account_id=null)
	{
		if (is_array($acc_id) || $acc_id > 0)
		{
			self::$db->delete(self::VALID_TABLE, array('acc_id' => $acc_id), __LINE__, __FILE__, self::APP);
			self::$db->delete(self::IDENTITIES_TABLE, array('acc_id' => $acc_id), __LINE__, __FILE__, self::APP);
			emailadmin_credentials::delete($acc_id);
			self::$db->delete(self::TABLE, array('acc_id' => $acc_id), __LINE__, __FILE__, self::APP);

			// invalidate caches
			foreach((array)$acc_id as $acc_id)
			{
				self::cache_invalidate($acc_id);
			}
			return self::$db->affected_rows();
		}
		if (!$account_id)
		{
			throw new egw_exception_wrong_parameter(__METHOD__."() no acc_id AND no account_id parameter!");
		}
		// delete all credentials belonging to given account(s)
		emailadmin_credentials::delete(0, $account_id);
		// delete all pointers to mail accounts belonging to given user accounts
		self::$db->delete(self::VALID_TABLE, array('account_id' => $account_id), __LINE__, __FILE__, self::APP);
		// delete all identities belonging to given user accounts
		self::$db->delete(self::IDENTITIES_TABLE, array('account_id' => $account_id), __LINE__, __FILE__, self::APP);
		// find profiles not belonging to anyone else and delete them
		$acc_ids = array();
		foreach(self::$db->select(self::TABLE, self::TABLE.'.acc_id', 'account_id IS NULL', __LINE__, __FILE__,
			false, 'GROUP BY '.self::TABLE.'.acc_id', self::APP, 0, 'LEFT '.self::VALID_JOIN) as $row)
		{
			$acc_ids[] = $row['acc_id'];
		}
		if ($acc_ids)
		{
			return self::delete($acc_ids);
		}
		return null;
	}

	/**
	 * Return array with acc_id => acc_name or account-object pairs
	 *
	 * @param boolean $only_current_user=true return only accounts for current user
	 * @param boolean|string $just_name=true true: return self::identity_name, false: return emailadmin_account objects,
	 *	string with attribute-name: return that attribute, eg. acc_imap_host or 'params' to return all attributes as array
	 * @param string $order_by='acc_name ASC'
	 * @param int|boolean $offset=false offset or false to return all
	 * @param int $num_rows=0 number of rows to return, 0=default from prefs (if $offset !== false)
	 * @param boolean $replace_placeholders=true should placeholders like {{n_fn}} be replaced
	 * @return Iterator with acc_id => acc_name or emailadmin_account objects
	 */
	public static function search($only_current_user=true, $just_name=true, $order_by=null, $offset=false, $num_rows=0, $replace_placeholders=true)
	{
		error_log(__METHOD__."($only_current_user, $just_name, '$order_by', $offset, $num_rows)");
		$where = array();
		if ($only_current_user)
		{
			$where[] = self::$db->expression(self::VALID_TABLE, self::VALID_TABLE.'.', array('account_id' => self::memberships()));
		}
		if (empty($order_by) || !preg_match('/^[a-z_]+ (ASC|DESC)$/i', $order_by))
		{
			$order_by = self::DEFAULT_ORDER;
		}
		$cache_key = json_encode($where).$order_by;

		if (!$only_current_user || !isset(self::$search_cache[$cache_key]))
		{
			$cols = self::TABLE.'.*,'.self::IDENTITIES_TABLE.'.*';
			if (self::$valid_account_id_sql)
			{
				$cols .= ','.self::$valid_account_id_sql.' AS account_id';
			}
			$rs = self::$db->select(self::TABLE, $cols,	$where, __LINE__, __FILE__,
				$offset, 'GROUP BY '.self::TABLE.'.acc_id ORDER BY '.$order_by,
				self::APP, $num_rows, self::IDENTITY_JOIN.' '.self::VALID_JOIN);

			$ids = array();
			foreach($rs as $row)
			{
				$row = self::db2data($row);

				if ($only_current_user)
				{
					error_log(__METHOD__."(TRUE, $just_name) caching data for acc_id=$row[acc_id]");
					self::$search_cache[$cache_key][$row['acc_id']] =& self::$cache[$row['acc_id']];
					self::$cache[$row['acc_id']] = $row;
				}
				$ids[] = $row['acc_id'];
			}
			// fetch valid_id, if not yet fetched
			if (!self::$valid_account_id_sql && $ids)
			{
				foreach(self::$db->select(self::VALID_TABLE, 'account_id', array('acc_id' => $ids),
					__LINE__, __FILE__, false, '', self::APP) as $row)
				{
					self::$cache[$row['acc_id']]['account_id'][] = $row['account_id'];
				}
			}
		}
		return new egw_db_callback_iterator(new ArrayIterator(self::$search_cache[$cache_key]),
			// process each row
			function($row) use ($just_name, $replace_placeholders)
			{
				if (is_string($just_name))
				{
					return $just_name == 'params' ? $row : $row[$just_name];
				}
				elseif ($just_name)
				{
					return emailadmin_account::identity_name($row, $replace_placeholders);
				}
				return new emailadmin_account($row);
			}, array(),
			// return acc_id as key
			function($row)
			{
				return $row['acc_id'];
			});
	}

	/**
	 * build an identity name
	 *
	 * @param array|emailadmin_account $account object or values for keys 'ident_(realname|org|email)', 'acc_(id|name|imap_username)'
	 * @param boolean $replace_placeholders=true should placeholders like {{n_fn}} be replaced
	 * @return string with htmlencoded angle brackets
	 */
	public static function identity_name($account, $replace_placeholders=true)
	{
		if ($replace_placeholders)
		{
			$data = array(
				'ident_realname' => $account['ident_realname'],
				'ident_org' => $account['ident_org'],
				'ident_email' => $account['ident_email'],
				'acc_name' => $account['acc_name'],
				'acc_imap_username' => $account['acc_imap_username'],
				'acc_imap_logintype' => $account['acc_imap_logintype'],
				'acc_id' => $account['acc_id'],
			);
			unset($account);
			//$start = microtime(true);
			$account = array_merge($data, self::replace_placeholders($data));
			//error_log(__METHOD__."() account=".array2string($account).' took '.number_format(microtime(true)-$start,3));
		}
		if (strlen(trim($account['ident_realname'].$account['ident_org'])))
		{
			$name = $account['ident_realname'].' '.$account['ident_org'];
		}
		else
		{
			$name = $account['acc_name'];
		}
		if ($account['ident_email'])
		{
			$name .= ' &lt;'.$account['ident_email'].'&gt;';
		}
		else
		{
			if (is_array($account) && !isset($account['acc_imap_username']) && $account['acc_id'])
			{
				$account += emailadmin_credentials::read($account['acc_id']);

				if (empty($account['acc_imap_username']) && $account['acc_imap_logintype'])
				{
					$account += emailadmin_credentials::from_session($account);
				}
			}
			if (!empty($account['acc_imap_username']))
			{
				$name .= ' &lt;'.$account['acc_imap_username'].'&gt;';
			}
		}
		//error_log(__METHOD__."(".array2string($account).", $replace_placeholders) returning ".array2string($name));
		return $name;
	}

	/**
	 * Check if account is for multiple users
	 *
	 * account_id == 0 == everyone, is multiple too!
	 *
	 * @param array|emailadmin_account $account value for key account_id (can be an array too!)
	 * @return boolean
	 */
	public static function is_multiple($account)
	{
		$is_multiple = !is_array($account['account_id']) ? !$account['account_id'] :
			(count($account['account_id']) > 1 || !$account['account_id'][0]);
		//error_log(__METHOD__."(account_id=".array2string($account['account_id']).") returning ".array2string($is_multiple));
		return $is_multiple;
	}

	/**
	 * Magic method to convert account to a string: identity_name
	 *
	 * @return string
	 */
	public function __toString()
	{
		return self::identity_name($this);
	}

	/**
	 * Get memberships of current or given user incl. our 0=Everyone
	 *
	 * @param type $user
	 * @return array
	 */
	protected static function memberships($user=null)
	{
		if (!$user) $user = $GLOBALS['egw_info']['user']['account_id'];

		$memberships = $GLOBALS['egw']->accounts->memberships($user, true);
		$memberships[] = $user;
		$memberships[] = 0;	// marks accounts valid for everyone

		return $memberships;
	}

	/**
	 * Invalidate various caches
	 *
	 * @param int $acc_id
	 */
	protected static function cache_invalidate($acc_id)
	{
		error_log(__METHOD__."($acc_id) invalidating cache");
		unset(self::$cache[$acc_id]);
		unset(self::$instances[$acc_id]);
		self::$search_cache = array();
	}

	/**
	 * Init our static properties
	 */
	static public function init_static()
	{
		self::$db = $GLOBALS['egw']->db;

		self::$valid_account_id_sql = self::$db->group_concat('account_id');
		if (self::$valid_account_id_sql)
		{
			self::$valid_account_id_sql = '(SELECT '.self::$valid_account_id_sql.
				' FROM '.self::VALID_TABLE.
				' WHERE '.self::VALID_TABLE.'.acc_id='.self::TABLE.'.acc_id'.
				' GROUP BY '.self::VALID_TABLE.'.acc_id)';
		}
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

	foreach(emailadmin_account::search(true, false) as $acc_id => $account)
	{
		echo "<p>$acc_id: <a href='{$_SERVER['PHP_SELF']}?acc_id=$acc_id'>$account</a></p>\n";
	}
	if (isset($_GET['acc_id']) && (int)$_GET['acc_id'] > 0)
	{
		$account = emailadmin_account::read((int)$_GET['acc_id']);
		_debug_array($account);
	}
}*/

// need to be after test-code!
emailadmin_account::init_static();
