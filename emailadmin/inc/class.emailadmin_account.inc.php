<?php
/**
 * EGroupware EMailAdmin: Mail accounts
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
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
 * To store the accounts 3 tables are used
 * - egw_ea_accounts all data except credentials and identities (incl. signature)
 * - egw_ea_credentials username/password for various accounts and types (imap, smtp, admin)
 * - egw_ea_identities identities of given account and user incl. standard identity of account
 */
class emailadmin_account
{
	const APP = 'emailadmin';
	const TABLE = 'egw_ea_accounts';
	const VALID_TABLE = 'egw_ea_valid';
	const IDENTITIES_TABLE = 'egw_ea_identities';

	/**
	 * No SSL
	 */
	const SSL_NONE = 0;
	/**
	 * STARTTLS on regular tcp connection/port
	 */
	const SSL_STARTTLS = 1;
	/**
	 * require TLS
	 */
	const SSL_TLS = 2;
	/**
	 * SSL
	 */
	const SSL_SSL = 3;
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
	protected $params;

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
		if (!isset($this->imapServer) || $this->imapServer->isAdminConnection != $_adminConnection)
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
			$this->oldImapServer->host = $this->params['acc_imap_host'];
			$this->oldImapServer->encryption = $this->params['acc_imap_ssl'] & ~self::SSL_VERIFY;
			$this->oldImapServer->port 	= $this->params['acc_imap_port'];;
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
			$class = $this->params['acc_smpt_type'];
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
	 * Give read access to protected parameters in $this->params
	 *
	 * @param type $name
	 * @return mixed
	 */
	public function __get($name)
	{
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
		$memberships = $GLOBALS['egw']->accounts->memberhips();
		$memberships[] = $GLOBALS['egw_info']['user']['account_id'];

		$join = 'JOIN '.self::IDENTITIES_TABLE.' ON '.self::IDENTITIES_TABLE.'.ident_id='.self::TABLE.'.ident_id ';
		$join .= 'LEFT JOIN '.self::VALID_TABLE.' ON '.self::VALID_TABLE.'.acc_id='.self::TABLE.'.acc_id ';

		$where = array(self::TABLE.'.acc_id='.(int)$acc_id);
		if ($only_current_user)
		{
			$where[] = '(account_id IS NULL OR '.self::$db->expression(self::VALID_TABLE, array('acccount_id' => $memberships)).')';
		}
		if (!($data = self::$db->select(self::TABLE, 'DISTINCT *', $where, __LINE__, __FILE__, false, '', self::APP, 0, $join)->fetch()))
		{
			throw new egw_exception_not_found;
		}
		$data += emailadmin_credentials::read($acc_id);

		return new emailadmin_account($data);
	}

	/**
	 * Init our static properties
	 */
	static public function init_static()
	{
		self::$db = $GLOBALS['egw']->db;
	}
}
emailadmin_account::init_static();