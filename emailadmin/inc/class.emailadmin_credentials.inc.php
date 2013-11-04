<?php
/**
 * EGroupware EMailAdmin: Mail account credentials
 *
 * @link http://www.stylite.de
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Stylite AG <info@stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Mail account credentials are stored in egw_ea_credentials for given
 * acocunt-id, users and types (imap, smtp and optional admin connection).
 *
 * Passwords in credentials are encrypted with either user password from session
 * or the database password.
 */
class emailadmin_credentials
{
	const APP = 'emailadmin';
	const TABLE = 'egw_ea_credentials';
	const USER_EDITABLE_JOIN = 'JOIN egw_ea_accounts ON egw_ea_accounts.acc_id=egw_ea_credentials.acc_id AND acc_user_editable=1';

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
			'account_id' => array(0, $GLOBALS['egw_info']['user']['account_id']),
			'cred_type & '.(int)$type,
		), __LINE__, __FILE__, false, 'ORDER BY account_id ASC', self::APP) as $row)
		{
			$password = self::decrypt($row);

			foreach(self::$type2prefix as $pattern => $prefix)
			{
				if ($row['cred_type'] & $pattern)
				{
					$results[$prefix.'username'] = $row['cred_username'];
					$results[$prefix.'password'] = $password;
					$results[$prefix.'cred_id'] = $row['cred_id'];
				}
			}
		}
		return $results;
	}


	/**
	 * Generate username according to acc_imap_logintype and fetch password from session
	 *
	 * @param array $data values for acc_imap_logintype and acc_domain
	 * @return array with values for keys 'acc_(imap|smtp)_(username|password|cred_id)'
	 */
	public static function from_session(array $data)
	{
		switch($data['acc_imap_logintype'])
		{
			case 'standard':
				$username = $GLOBALS['egw_info']['user']['account_lid'];
				break;

			case 'vmailmgr':
				$username = $GLOBALS['egw_info']['user']['account_lid'].'@'.$data['acc_domain'];
				break;

			case 'email':
				$username = $GLOBALS['egw_info']['user']['account_email'];
				break;

			case 'uidNumber':
				$username = 'u'.$GLOBALS['egw_info']['user']['account_id'].'@'.$data['acc_domain'];
				break;

			case 'admin':
				// data should have been stored in credentials table
				throw new egw_exception_assertion_failed('data[acc_imap_logintype]=admin and no stored username/password!');

			default:
				throw new egw_exception_wrong_parameter("Unknown data[acc_imap_logintype]=".array2string($data['acc_imap_logintype']).'!');
		}
		$password = base64_decode(egw_cache::getSession('phpgwapi', 'password'));

		return array(
			'acc_imap_username' => $username,
			'acc_imap_password' => $password,
			'acc_imap_cred_id'  => $data['acc_imap_logintype'],	// to NOT store it
			'acc_smtp_username' => $username,
			'acc_smtp_password' => $password,
			'acc_smtp_cred_id'  => $data['acc_imap_logintype'],	// to NOT store it
		);
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
	 * @param ressource $mcrypt=null mcrypt ressource for user, default calling self::init_crypt(true)
	 * @return int cred_id
	 */
	public static function write($acc_id, $username, $password, $type, $account_id=0, $cred_id=null, $mcrypt=null)
	{
		//error_log(__METHOD__."(acc_id=$acc_id, '$username', \$password, type=$type, account_id=$account_id, cred_id=$cred_id)");
		if (!empty($cred_id) && !is_numeric($cred_id))
		{
			return;	// do NOT store credentials from session of current user!
		}
		$data = array(
			'acc_id' => $acc_id,
			'account_id' => $account_id,
			'cred_username' => $username,
			'cred_password' => self::encrypt($password, $account_id, $pw_enc, $mcrypt),
			'cred_type' => $type,
			'cred_pw_enc' => $pw_enc,
		);
		//error_log(__METHOD__."($acc_id, '$username', '$password', $type, $account_id, $cred_id, $mcrypt) storing ".array2string($data).' '.function_backtrace());
		if ($cred_id > 0)
		{
			self::$db->update(self::TABLE, $data, array('cred_id' => $cred_id), __LINE__, __FILE__, self::APP);
		}
		else
		{
			self::$db->insert(self::TABLE, $data, false, __LINE__, __FILE__, self::APP);
			$cred_id = self::$db->get_last_insert_id(self::TABLE, 'cred_id');
		}
		return $cred_id;
	}

	/**
	 * Encrypt password for storing in database
	 *
	 * @param string $password cleartext password
	 * @param int $account_id user-account password is for
	 * @param int &$pw_enc on return encryption used
	 * @param ressource $mcrypt=null mcrypt ressource for user, default calling self::init_crypt(true)
	 * @return string encrypted password
	 */
	protected static function encrypt($password, $account_id, &$pw_enc, $mcrypt=null)
	{
		if ($account_id > 0 && $account_id == $GLOBALS['egw_info']['user']['account_id'] &&
			($mcrypt || ($mcrypt = self::init_crypt(true))))
		{
			$pw_enc = self::USER;
			$password = mcrypt_generic($mcrypt, $password);
		}
		elseif (($mcrypt = self::init_crypt(false)))
		{
			$pw_enc = self::SYSTEM;
			$password = mcrypt_generic($mcrypt, $password);
		}
		else
		{
			$pw_enc = self::CLEARTEXT;
		}
		//error_log(__METHOD__."(, $account_id, , $mcrypt) pw_enc=$pw_enc returning ".array2string(base64_encode($password)));
		return base64_encode($password);
	}

	/**
	 * Decrypt password from database
	 *
	 * @param array $row database row
	 * @param ressource $mcrypt=null mcrypt ressource for user, default calling self::init_crypt(true)
	 */
	protected static function decrypt(array $row, $mcrypt=null)
	{
		switch ($row['cred_pw_enc'])
		{
			case self::CLEARTEXT:
				return base64_decode($row['cred_password']);

			case self::USER:
			case self::SYSTEM:
				if (($row['cred_pw_enc'] != self::USER || !$mcrypt) &&
					!($mcrypt = self::init_crypt($row['cred_pw_enc'] == self::USER)))
				{
					throw egw_exception_wrong_parameter("Password encryption type $row[cred_pw_enc] NOT available!");
				}
				return (!empty($row['cred_password'])?trim(mdecrypt_generic($mcrypt, base64_decode($row['cred_password']))):'');
		}
		throw egw_exception_wrong_parameter("Unknow password encryption type $row[cred_pw_enc]!");
	}

	/**
	 * Hook called when user changes his password, to re-encode his credentials with his new password
	 *
	 * It also changes all user credentials encoded with system password!
	 *
	 * It only changes credentials from user-editable accounts, as user probably
	 * does NOT know password set by admin!
	 *
	 * @param array $data values for keys 'old_passwd', 'new_passwd', 'account_id'
	 */
	static public function changepassword(array $data)
	{
		if (empty($data['old_passwd'])) return;

		foreach(self::$db->select(self::TABLE, self::TABLE.'.*', array(
			'account_id' => $data['account_id']
		),__LINE__, __FILE__, false, '', 'emailadmin', 0, self::USER_EDITABLE_JOIN) as $row)
		{
			if (!isset($old_mcrypt))
			{
				$old_mcrypt = self::init_crypt($data['old_passwd']);
				$new_mcrypt = self::init_crypt($data['new_passwd']);
				if (!$old_mcrypt && !$new_mcrypt) return;
			}
			$password = self::decrypt($row, $old_mcrypt);

			self::write($row['acc_id'], $row['cred_username'], $password, $row['cred_type'],
				$row['account_id'], $row['cred_id'], $new_mcrypt);
		}
	}

	/**
	 * Check if session encryption is configured, possible and initialise it
	 *
	 * @param boolean|string $user=false true: use user-password from session,
	 *	false: database password or string with password to use
	 * @param string $algo='tripledes'
	 * @param string $mode='ecb'
	 * @return ressource|boolean mcrypt ressource to use or false if not available
	 */
	static public function init_crypt($user=false, $algo='tripledes',$mode='ecb')
	{
		if (is_string($user))
		{
			// do NOT use/set/change static object
		}
		elseif ($user)
		{
			$mcrypt =& self::$user_mcrypt;
		}
		else
		{
			$mcrypt =& self::$system_mcrypt;
		}
		if (!isset($mcrypt))
		{
			if (is_string($user))
			{
				$key = $user;
			}
			elseif ($user)
			{
				$key = egw_cache::getSession('phpgwapi', 'password');
				if (empty($key)) return false;
				$key = base64_decode($key);
			}
			else
			{
				$key = self::$db->Password;
			}
			if (!check_load_extension('mcrypt'))
			{
				error_log(__METHOD__."() required PHP extension mcrypt not loaded and can not be loaded, passwords can be NOT encrypted!");
				$mcrypt = false;
			}
			elseif (!($mcrypt = mcrypt_module_open($algo, '', $mode, '')))
			{
				error_log(__METHOD__."() could not mcrypt_module_open(algo='$algo','',mode='$mode',''), passwords can be NOT encrypted!");
				$mcrypt = false;
			}
			else
			{
				$iv_size = mcrypt_enc_get_iv_size($mcrypt);
				$iv = !isset($GLOBALS['egw_info']['server']['mcrypt_iv']) || strlen($GLOBALS['egw_info']['server']['mcrypt_iv']) < $iv_size ?
					mcrypt_create_iv ($iv_size, MCRYPT_RAND) : substr($GLOBALS['egw_info']['server']['mcrypt_iv'],0,$iv_size);

				$key_size = mcrypt_enc_get_key_size($mcrypt);
				if (bytes($key) > $key_size) $key = cut_bytes($key,0,$key_size-1);

				if (mcrypt_generic_init($mcrypt, $key, $iv) < 0)
				{
					error_log(__METHOD__."() could not initialise mcrypt, passwords can be NOT encrypted!");
					$mcrypt = false;
				}
			}
		}
		//error_log(__METHOD__."(".array2string($user).") key=".array2string($key)." returning ".array2string($mcrypt));
		return $mcrypt;
	}

	/**
	 * Init our static properties
	 */
	static public function init_static()
	{
		self::$db = isset($GLOBALS['egw_setup']) ? $GLOBALS['egw_setup']->db : $GLOBALS['egw']->db;
	}
}
emailadmin_credentials::init_static();
