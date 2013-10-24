<?php
/**
 * EGroupware EMailAdmin: Mail account credentials
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Mail account credentials are stored in egw_ea_credentials for given
 * acocunt-id, users and types (imap, smtp and optional admin connection).
 *
 * Passwords in credentials are encrypted with either user password from session
 * or a secret from header.inc.php table.
 */
class emailadmin_credentials
{
	const APP = 'emailadmin';
	const TABLE = 'egw_ea_credentials';

	/**
	 * Credentials for type IMAP
	 */
	const IMAP = 1;
	/**
	 * Credentials for type SMTP
	 */
	const SMTP = 2;
	/**
	 * Credentials for admin connection
	 */
	const ADMIN = 8;
	/**
	 * All credentials IMAP|SMTP|ADMIN
	 */
	const ALL = 11;

	/**
	 * Password in cleartext
	 */
	const CLEARTEXT = 0;
	/**
	 * Password encrypted with user password
	 */
	const USER = 1;
	/**
	 * Password encrypted with system secret
	 */
	const SYSTEM = 2;

	/**
	 * Translate type to prefix
	 *
	 * @var array
	 */
	protected static $type2prefix = array(
		self::IMAP => 'acc_imap_',
		self::SMTP => 'acc_smtp_',
		self::ADMIN => 'acc_imap_admin_',
	);

	/**
	 * Reference to global db object
	 *
	 * @var egw_db
	 */
	static protected $db;

	/**
	 * Mcrypt instance initialised with system specific key
	 *
	 * @var ressource
	 */
	static protected $system_mcrypt;

	/**
	 * Mcrypt instance initialised with user password from session
	 *
	 * @var ressource
	 */
	static protected $user_mcrypt;

	/**
	 * Read credentials for a given mail account
	 *
	 * @param int $acc_id
	 * @param int $type=null default return all credentials
	 * @return array with values for (imap|smtp|admin)_(username|password|cred_id)
	 */
	public static function read($acc_id, $type=null)
	{
		if (is_null($type)) $type = self::ALL;

		$results = array();
		foreach(self::$db->select(self::TABLE, '*', array(
			'acc_id' => $acc_id,
			'cred_type & '.(int)$type,
		), __LINE__, __FILE__, false, '', self::APP) as $row)
		{
			$password = self::decrypt($row);

			foreach(self::$type2prefix as $pattern => $prefix)
			{
				if ($row['cred_type'] & $pattern)
				{
					$result[$prefix.'username'] = $row['cred_username'];
					$result[$prefix.'password'] = $password;
					$result[$prefix.'cred_id'] = $row['cred_id'];
				}
			}
		}
		return $results;
	}

	/**
	 * Write and encrypt credentials
	 *
	 * @param int $acc_id id of account
	 * @param string $username
	 * @param string $password cleartext password to write
	 * @param int $type self::IMAP, self::SMTP or self::ADMIN
	 * @param int $account_id if of user-account for whom credentials are
	 * @param int $cred_id=null id of existing credentials to update
	 * @return int cred_id
	 */
	public static function write($acc_id, $username, $password, $type, $account_id=0, $cred_id=null)
	{
		$data = array(
			'cred_username' => $username,
			'cred_password' => self::encrypt($password, $account_id, $pw_enc=0),
			'cred_type' => $type,
			'cred_pw_enc' => $pw_enc,
		);
		$where = array(
			'acc_id' => $acc_id,
			'account_id' => $account_id,
		);
		return !self::$db->update(self::TABLE, $data, $where, __LINE__, __FILE__) ? false :
			($cred_id > 0 ? $cred_id : self::$db->get_last_insert_id(self::TABLE, 'cred_id'));
	}

	/**
	 * Encrypt password for storing in database
	 *
	 * @param type $password cleartext password
	 * @param type $account_id user-account password is for
	 * @param type &$pw_enc on return encryption used
	 */
	protected static function encrypt($password, $account_id, &$pw_enc)
	{
		if ($account_id > 0 && $account_id == $GLOBALS['egw_info']['user']['account_id'] &&
			($mcrypt = self::init_crypt(true)))
		{
			$pw_enc = self::USER;
			return base64_encode(mcrypt_generic($mcrypt, $password));
		}
		elseif (($mcrypt = self::init_crypt(false)))
		{
			$pw_enc = self::SYSTEM;
			return base64_encode(mcrypt_generic($mcrypt, $password));
		}
		$pw_enc = self::CLEARTEXT;
		return base64_encode($password);
	}

	/**
	 * Decrypt password from database
	 *
	 * @param array $row database row
	 * @param string password in cleartext
	 */
	protected static function decrypt(array $row)
	{
		switch ($row['cred_pw_enc'])
		{
			case self::CLEARTEXT:
				return base64_decode($row['cred_password']);

			case self::USER:
			case self::SYSTEM:
				if (!($mcrypt = self::init_crypt($row['cred_pw_enc'] == self::USER)))
				{
					throw egw_exception_wrong_parameter("Password encryption type $row[cred_pw_enc] NOT available!");
				}
				return mdecrypt_generic(self::$mcrypt, base64_decode($row['cred_password']));
		}
		throw egw_exception_wrong_parameter("Unknow password encryption type $row[cred_pw_enc]!");
	}

	/**
	 * Check if session encryption is configured, possible and initialise it
	 *
	 * @param boolean $user=false use user-password from session or global mcyrpt_iv
	 * @param string $algo='tripledes'
	 * @param string $mode='ecb'
	 * @return ressource|boolean mcrypt ressource to use or false if not available
	 */
	static public function init_crypt($user=false, $algo='tripledes',$mode='ecb')
	{
		if ($user)
		{
			$mcrypt &= self::$user_mcrypt;
		}
		else
		{
			$mcrypt &= self::$system_mcrypt;
		}
		if (!isset($mcrypt))
		{
			if ($user)
			{
				$key = $GLOBALS['egw']->session->passwd;
				if (empty($key)) return false;
			}
			else
			{
				$key = $GLOBALS['egw_info']['server']['db_pass'].EGW_SERVER_ROOT;
			}
			if (!check_load_extension('mcrypt'))
			{
				error_log(__METHOD__."() required PHP extension mcrypt not loaded and can not be loaded, passwords can be NOT encrypted!");
				return $mcrypt = false;
			}
			if (!(self::$mcrypt = mcrypt_module_open($algo, '', $mode, '')))
			{
				error_log(__METHOD__."() could not mcrypt_module_open(algo='$algo','',mode='$mode',''), passwords can be NOT encrypted!");
				return $mcrypt = false;
			}
			$iv_size = mcrypt_enc_get_iv_size(self::$mcrypt);
			$iv = !isset($GLOBALS['egw_info']['server']['mcrypt_iv']) || strlen($GLOBALS['egw_info']['server']['mcrypt_iv']) < $iv_size ?
				mcrypt_create_iv ($iv_size, MCRYPT_RAND) : substr($GLOBALS['egw_info']['server']['mcrypt_iv'],0,$iv_size);

			$key_size = mcrypt_enc_get_key_size(self::$mcrypt);
			if (bytes($key) > $key_size) $key = cut_bytes($key,0,$key_size-1);

			if (mcrypt_generic_init(self::$mcrypt,$key, $iv) < 0)
			{
				error_log(__METHOD__."() could not initialise mcrypt, passwords can be NOT encrypted!");
				return $mcrypt = false;
			}
		}
		return is_resource($mcrypt);
	}

	/**
	 * Init our static properties
	 */
	static public function init_static()
	{
		self::$db = $GLOBALS['egw']->db;
	}
}
emailadmin_credentials::init_static();