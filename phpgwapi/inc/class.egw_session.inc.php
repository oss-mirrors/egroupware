<?php
/**
 * eGroupWare API: eGW session handling
 *
 * This class is based on the old phpgwapi/inc/class.sessions(_php4).inc.php:
 * (c) 1998-2000 NetUSE AG Boris Erdmann, Kristian Koehntopp
 * (c) 2003 FreeSoftware Foundation
 * Not sure how much the current code still has to do with it.
 *
 * Former authers were:
 * - NetUSE AG Boris Erdmann, Kristian Koehntopp
 * - Dan Kuykendall <seek3r@phpgroupware.org>
 * - Joseph Engo <jengo@phpgroupware.org>
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage session
 * @author Ralf Becker <ralfbecker@outdoor-training.de> since 2003 on
 * @version $Id$
 */

/**
 * eGW session handling
 *
 * Create, verifies or destroys an eGroupWare session
 *
 * There are separate session-handler classes: egw_session_(files|memcache),
 * which implement custom session handler or certain extra functionality, like eg. listing sessions,
 * not available in php's session extension.
 */
class egw_session //extends sessions
{
	/**
	 * key of eGW's session-data in $_SESSION
	 */
	const EGW_SESSION_VAR = 'egw_session';

	/**
	 * key of eGW's application session-data in $_SESSION
	 */
	const EGW_APPSESSION_VAR = 'egw_app_session';

	/**
	 * Name of cookie or get-parameter with session-id
	 */
	const EGW_SESSION_NAME = 'sessionid';

	/**
	* current user login (account_lid@domain)
	*
	* @var string
	*/
	var $login;

	/**
	* current user password
	*
	* @var string
	*/
	var $passwd;

	/**
	* current user db/ldap account id
	*
	* @var int
	*/
	var $account_id;

	/**
	* current user account login id (without the eGW-domain/-instance part
	*
	* @var string
	*/
	var $account_lid;

	/**
	* domain for current user
	*
	* @var string
	*/
	var $account_domain;

	/**
	* type flag, A - anonymous session, N - None, normal session
	*
	* @var string
	*/
	var $session_flags;

	/**
	* current user session id
	*
	* @var string
	*/
	var $sessionid;

	/**
	* an other session specific id (md5 from a random string),
	* used together with the sessionid for xmlrpc basic auth and the encryption of session-data (if that's enabled)
	*
	* @var string
	*/
	var $kp3;

	/**
	* encryption key for the encrption of the session-data, if enabled
	*
	* @var string
	*/
	var $key;

	/**
	* mcrypt's iv
	*
	* @var string
	*/
	var $iv;

	/**
	* name of XML-RPC/SOAP method called
	*
	* @var string
	*/
	var $xmlrpc_method_called;

	/**
	* Array with the name of the system domains
	*
	* @var array
	*/
	private $egw_domains;

	/**
	 * Write debug messages about session verification and creation to the error_log
	 *
	 * @var boolean
	 */
	private static $errorlog_debug = false;

	/**
	 * $_SESSION at the time the constructor was called
	 *
	 * @var array
	 */
	var $save_session_vars_start;

	/**
	 * Constructor just loads up some defaults from cookies
	 *
	 * @param array $domain_names=null domain-names used in this install
	 */
	function __construct(array $domain_names=null)
	{
		$this->save_session_vars_start = $_SESSION;

		$this->sessionid = $_REQUEST[self::EGW_SESSION_NAME];
		$this->kp3       = $_REQUEST['kp3'];

		$this->egw_domains = $domain_names;

		// verfiy and if necessary create and save our config settings
		//
		$save_rep = false;
		if (!isset($GLOBALS['egw_info']['server']['max_access_log_age']))
		{
			$GLOBALS['egw_info']['server']['max_access_log_age'] = 90;	// default 90 days
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['block_time']))
		{
			$GLOBALS['egw_info']['server']['block_time'] = 5;	// default 5min
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['num_unsuccessful_id']))
		{
			$GLOBALS['egw_info']['server']['num_unsuccessful_id']  = 3;	// default 3 trys per id
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['num_unsuccessful_ip']))
		{
			$GLOBALS['egw_info']['server']['num_unsuccessful_ip']  = $GLOBALS['egw_info']['server']['num_unsuccessful_id'];	// default same as for id
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['install_id']))
		{
			$GLOBALS['egw_info']['server']['install_id']  = md5($GLOBALS['egw']->common->randomstring(15));
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['sessions_timeout']))
		{
			$GLOBALS['egw_info']['server']['sessions_timeout'] = 14400;
			$save_rep = true;
		}
		if (!isset($GLOBALS['egw_info']['server']['max_history']))
		{
			$GLOBALS['egw_info']['server']['max_history'] = 20;
			$save_rep = true;
		}
		if ($save_rep)
		{
			$config = new config('phpgwapi');
			$config->read_repository();
			$config->value('max_access_log_age',$GLOBALS['egw_info']['server']['max_access_log_age']);
			$config->value('block_time',$GLOBALS['egw_info']['server']['block_time']);
			$config->value('num_unsuccessful_id',$GLOBALS['egw_info']['server']['num_unsuccessful_id']);
			$config->value('num_unsuccessful_ip',$GLOBALS['egw_info']['server']['num_unsuccessful_ip']);
			$config->value('install_id',$GLOBALS['egw_info']['server']['install_id']);
			$config->value('sessions_timeout',$GLOBALS['egw_info']['server']['sessions_timeout']);
			$config->value('sessions_app_timeout',$GLOBALS['egw_info']['server']['sessions_app_timeout']);
			$config->save_repository();
		}
		self::set_cookiedomain();
      	ini_set('session.gc_maxlifetime', $GLOBALS['egw_info']['server']['sessions_timeout']);
	}

	function __wakeup()
	{
       	ini_set('session.gc_maxlifetime', $GLOBALS['egw_info']['server']['sessions_timeout']);
	}

	/**
	 * Create a new eGW session
	 *
	 * @param string $login user login
	 * @param string $passwd user password
	 * @param string $passwd_type type of password being used, ie plaintext, md5, sha1
	 * @param boolean $no_session_needed=false dont create a real session, eg. for GroupDAV clients using only basic auth, no cookie support
	 * @param boolean $auth_check=true if false, the user is loged in without checking his password (eg. for single sign on), default = true
	 * @return string session id
	 */
	function create($login,$passwd = '',$passwd_type = '',$no_session=false,$auth_check=true)
	{
		if (is_array($login))
		{
			$this->login       = $login['login'];
			$this->passwd      = $login['passwd'];
			$this->passwd_type = $login['passwd_type'];
			$login             = $this->login;
		}
		else
		{
			$this->login       = $login;
			$this->passwd      = $passwd;
			$this->passwd_type = $passwd_type;
		}
		if (self::$errorlog_debug) error_log(__METHOD__."($this->login,$this->passwd,$this->passwd_type,$no_session,$auth_check)");

		self::split_login_domain($login,$this->account_lid,$this->account_domain);
		// add domain to the login, if not already there
		if (substr($this->login,-strlen($this->account_domain)-1) != '@'.$this->account_domain)
		{
			$this->login .= '@'.$this->account_domain;
		}
		$now = time();

		// This is to ensure that we authenticate to the correct domain (might not be default)
		// if no domain is given we use the default domain, so we dont need to re-create everything
		if (!$GLOBALS['egw_info']['user']['domain'] && $this->account_domain == $GLOBALS['egw_info']['server']['default_domain'])
		{
			$GLOBALS['egw_info']['user']['domain'] = $this->account_domain;
		}
		elseif($this->account_domain != $GLOBALS['egw_info']['user']['domain'])
		{
			throw new Exception("Wrong domain! '$this->account_domain' != '{$GLOBALS['egw_info']['user']['domain']}'");
/*			$GLOBALS['egw']->ADOdb = null;
			$GLOBALS['egw_info']['user']['domain'] = $this->account_domain;
			// reset the db and all other (non-header!) egw_info/server data
			$GLOBALS['egw_info']['server'] = array(
				'sessions_type'  => $GLOBALS['egw_info']['server']['sessions_type'],
				'default_domain' => $GLOBALS['egw_info']['server']['default_domain'],
			);
			$GLOBALS['egw_info']['server']['db_host'] = $GLOBALS['egw_domain'][$this->account_domain]['db_host'];
			$GLOBALS['egw_info']['server']['db_port'] = $GLOBALS['egw_domain'][$this->account_domain]['db_port'];
			$GLOBALS['egw_info']['server']['db_name'] = $GLOBALS['egw_domain'][$this->account_domain]['db_name'];
			$GLOBALS['egw_info']['server']['db_user'] = $GLOBALS['egw_domain'][$this->account_domain]['db_user'];
			$GLOBALS['egw_info']['server']['db_pass'] = $GLOBALS['egw_domain'][$this->account_domain]['db_pass'];
			$GLOBALS['egw_info']['server']['db_type'] = $GLOBALS['egw_domain'][$this->account_domain]['db_type'];
			$GLOBALS['egw']->setup('',false);*/
		}

		//echo "<p>session::create(login='$login'): lid='$this->account_lid', domain='$this->account_domain'</p>\n";
		$user_ip = self::getuser_ip();

		$this->account_id = $GLOBALS['egw']->accounts->name2id($this->account_lid,'account_lid','u');

		if (($blocked = $this->login_blocked($login,$user_ip)) ||	// too many unsuccessful attempts
			$GLOBALS['egw_info']['server']['global_denied_users'][$this->account_lid] ||
			$auth_check && !$GLOBALS['egw']->auth->authenticate($this->account_lid, $this->passwd, $this->passwd_type) ||
			$this->account_id && $GLOBALS['egw']->accounts->get_type($this->account_id) == 'g')
		{
			$this->reason = $blocked ? 'blocked, too many attempts' : 'bad login or password';
			$this->cd_reason = $blocked ? 99 : 5;

			$this->log_access($this->reason,$login,$user_ip,0);	// log unsuccessfull login
			return false;
		}

		if (!$this->account_id && $GLOBALS['egw_info']['server']['auto_create_acct'])
		{
			if ($GLOBALS['egw_info']['server']['auto_create_acct'] == 'lowercase')
			{
				$this->account_lid = strtolower($this->account_lid);
			}
			$this->account_id = $GLOBALS['egw']->accounts->auto_add($this->account_lid, $passwd);
		}

		$GLOBALS['egw_info']['user']['account_id'] = $this->account_id;
		$GLOBALS['egw']->accounts->accounts($this->account_id);
		session_start();
		// set a new session-id, if not syncml (already done in Horde code and can NOT be changed)
		if ($GLOBALS['egw_info']['flags']['currentapp'] != 'syncml')
		{
			session_regenerate_id(true);
		}
		$this->sessionid = $no_session ? 'no-session' : session_id();
		$this->kp3       = md5($GLOBALS['egw']->common->randomstring(15));

		unset($GLOBALS['egw_info']['server']['default_domain']); // we kill this for security reasons

		// init the crypto object
		$this->key = md5($this->kp3 . $this->sessionid . $GLOBALS['egw_info']['server']['encryptkey']);
		$this->iv  = $GLOBALS['egw_info']['server']['mcrypt_iv'];
		$GLOBALS['egw']->crypto->init(array($this->key,$this->iv));

		$this->read_repositories(false);
		if ($GLOBALS['egw']->accounts->is_expired($this->user))
		{
			if(is_object($GLOBALS['egw']->log))
			{
				$GLOBALS['egw']->log->message(array(
					'text' => 'W-LoginFailure, account loginid %1 is expired',
					'p1'   => $this->account_lid,
					'line' => __LINE__,
					'file' => __FILE__
				));
				$GLOBALS['egw']->log->commit();
			}
			$this->reason = 'account is expired';
			$this->cd_reason = 98;

			return false;
		}

		$GLOBALS['egw_info']['user']  = $this->user;
		$GLOBALS['egw_info']['hooks'] = $this->hooks;

		$this->appsession('password','phpgwapi',base64_encode($this->passwd));
		if ($GLOBALS['egw']->acl->check('anonymous',1,'phpgwapi'))
		{
			$this->session_flags = 'A';
		}
		else
		{
			$this->session_flags = 'N';
		}

		if (($hook_result = $GLOBALS['egw']->hooks->process(array(
			'location'       => 'session_creation',
			'sessionid'      => $this->sessionid,
			'session_flags'  => $this->session_flags,
			'account_id'     => $this->account_id,
			'account_lid'    => $this->account_lid,
			'passwd'         => $this->passwd,
			'account_domain' => $this->account_domain,
			'user_ip'        => $user_ip,
		),'',true)))	// true = run hooks from all apps, not just the ones the current user has perms to run
		{
			foreach($hook_result as $app => $reason)
			{
				if ($reason)	// called hook requests to deny the session
				{
					$this->reason = $this->cd_reason = $reason;
					$this->log_access($this->reason,$login,$user_ip,0);		// log unsuccessfull login
					return false;
				}
			}
		}
		$GLOBALS['egw']->db->transaction_begin();
		$this->register_session($this->login,$user_ip,$now,$this->session_flags);
		if ($this->session_flags != 'A')		// dont log anonymous sessions
		{
			$this->log_access($this->sessionid,$login,$user_ip,$this->account_id);
		}
		self::appsession('account_previous_login','phpgwapi',$GLOBALS['egw']->auth->previous_login);
		$GLOBALS['egw']->accounts->update_lastlogin($this->account_id,$user_ip);
		$GLOBALS['egw']->db->transaction_commit();

		if ($GLOBALS['egw_info']['server']['usecookies'] && !$no_session)
		{
			self::egw_setcookie(self::EGW_SESSION_NAME,$this->sessionid);
			self::egw_setcookie('kp3',$this->kp3);
			self::egw_setcookie('domain',$this->account_domain);
		}
		if ($GLOBALS['egw_info']['server']['usecookies'] && !$no_session || isset($_COOKIE['last_loginid']))
		{
			self::egw_setcookie('last_loginid', $this->account_lid ,$now+1209600); /* For 2 weeks */
			self::egw_setcookie('last_domain',$this->account_domain,$now+1209600);
		}
		//if (!$this->sessionid) echo "<p>session::create(login='$login') = '$this->sessionid': lid='$this->account_lid', domain='$this->account_domain'</p>\n";
		if (self::$errorlog_debug) error_log(__METHOD__."($this->login,$this->passwd,$this->passwd_type,$no_session,$auth_check) successfull sessionid=$this->sessionid");

		return $this->sessionid;
	}

	/**
	 * Store eGW specific session-vars
	 *
	 * @param string $login
	 * @param string $user_ip
	 * @param int $now
	 * @param string $session_flags
	 */
	private function register_session($login,$user_ip,$now,$session_flags)
	{
		// restore session vars set before session was started
		if ($this->save_session_vars_start && is_array($this->save_session_vars_start))
		{
			foreach($this->save_session_vars_start as $name => &$value)
			{
				//error_log(__METHOD__."() added $name=".array2string($value));
				$_SESSION[$name] =& $value;
			}
			unset($this->save_session_vars_start);
		}
		$_SESSION[self::EGW_SESSION_VAR] = array(
			'session_id'     => $this->sessionid,
			'session_lid'    => $login,
			'session_ip'     => $user_ip,
			'session_logintime' => $now,
			'session_dla'    => $now,
			'session_action' => $_SERVER['PHP_SELF'],
			'session_flags'  => $session_flags,
			// we need the install-id to differ between serveral installs shareing one tmp-dir
			'session_install_id' => $GLOBALS['egw_info']['server']['install_id']
		);
	}

	/**
	 * name of access-log table
	 */
	const ACCESS_LOG_TABLE = 'egw_access_log';

	/**
    * Write or update (for logout) the access_log
	*
	* @param string $sessionid id of session or 0 for unsuccessful logins
	* @param string $login account_lid (evtl. with domain) or '' for settion the logout-time
	* @param string $user_ip ip to log
	* @param int $account_id numerical account_id
	*/
	private function log_access($sessionid,$login='',$user_ip='',$account_id='')
	{
		$now = time();

		if ($login)
		{
			$GLOBALS['egw']->db->insert(self::ACCESS_LOG_TABLE,array(
				'sessionid' => $sessionid,
				'loginid'   => $login,
				'ip'        => $user_ip,
				'li'        => $now,
				'lo'        => 0,
				'account_id'=> $account_id,
			),false,__LINE__,__FILE__);
		}
		else
		{
			$GLOBALS['egw']->db->update(self::ACCESS_LOG_TABLE,array('lo' => $now),array('sessionid' => $sessionid),__LINE__,__FILE__);
		}
		if ($GLOBALS['egw_info']['server']['max_access_log_age'])
		{
			$max_age = $now - $GLOBALS['egw_info']['server']['max_access_log_age'] * 24 * 60 * 60;

			$GLOBALS['egw']->db->delete(self::ACCESS_LOG_TABLE,"li < $max_age",__LINE__,__FILE__);
		}
	}

	/**
	 * Protect against brute force attacks, block login if too many unsuccessful login attmepts
     *
	 * @param string $login account_lid (evtl. with domain)
	 * @param string $ip ip of the user
	 * @returns bool login blocked?
	 */
	private function login_blocked($login,$ip)
	{
		$blocked = false;
		$block_time = time() - $GLOBALS['egw_info']['server']['block_time'] * 60;

		if (($false_ip = $GLOBALS['egw']->db->select(self::ACCESS_LOG_TABLE,'COUNT(*)',array(
			'account_id = 0',
			'ip'         => $ip,
			"li > $block_time",
		),__LINE__,__FILE__)->fetchSingle()) > $GLOBALS['egw_info']['server']['num_unsuccessful_ip'])
		{
			//echo "<p>login_blocked: ip='$ip' ".$this->db->f(0)." trys (".$GLOBALS['egw_info']['server']['num_unsuccessful_ip']." max.) since ".date('Y/m/d H:i',$block_time)."</p>\n";
			$blocked = true;
		}
		if (($false_id = $GLOBALS['egw']->db->select(self::ACCESS_LOG_TABLE,'COUNT(*)',array(
			'account_id = 0',
			'(loginid = '.$GLOBALS['egw']->db->quote($login).' OR loginid LIKE '.$GLOBALS['egw']->db->quote($login.'@%').')',
			"li > $block_time",
		),__LINE__,__FILE__)->fetchSingle()) > $GLOBALS['egw_info']['server']['num_unsuccessful_id'])
		{
			//echo "<p>login_blocked: login='$login' ".$this->db->f(0)." trys (".$GLOBALS['egw_info']['server']['num_unsuccessful_id']." max.) since ".date('Y/m/d H:i',$block_time)."</p>\n";
			$blocked = true;
		}
		if ($blocked && $GLOBALS['egw_info']['server']['admin_mails'] &&
			$GLOBALS['egw_info']['server']['login_blocked_mail_time'] < time()-5*60)	// max. one mail every 5mins
		{
			// notify admin(s) via email
			$from    = 'eGroupWare@'.$GLOBALS['egw_info']['server']['mail_suffix'];
			$subject = lang("eGroupWare: login blocked for user '%1', IP %2",$login,$ip);
			$body    = lang("Too many unsucessful attempts to login: %1 for the user '%2', %3 for the IP %4",$false_id,$login,$false_ip,$ip);

			$subject = $GLOBALS['egw']->send->encode_subject($subject);
			$admin_mails = explode(',',$GLOBALS['egw_info']['server']['admin_mails']);
			foreach($admin_mails as $to)
			{
				$GLOBALS['egw']->send->msg('email',$to,$subject,$body,'','','',$from,$from);
			}
			// save time of mail, to not send to many mails
			$config = new config('phpgwapi');
			$config->read_repository();
			$config->value('login_blocked_mail_time',time());
			$config->save_repository();
		}
		return $blocked;
	}

	/**
	 * Check to see if a session is still current and valid
	 *
	 * @param string $sessionid session id to be verfied
	 * @param string $kp3 ?? to be verified
	 * @return bool is the session valid?
	 */
	function verify($sessionid='',$kp3='')
	{
		if (self::$errorlog_debug) error_log(__METHOD__."('$sessionid','$kp3') ".function_backtrace());

		$fill_egw_info_and_repositories = !$GLOBALS['egw_info']['flags']['restored_from_session'];

		if(!$sessionid)
		{
			$sessionid = $_REQUEST[self::EGW_SESSION_NAME];
			$kp3       = $_REQUEST['kp3'];
		}

		$this->sessionid = $sessionid;
		$this->kp3       = $kp3;

		if (!$this->sessionid) return false;

		session_name(self::EGW_SESSION_NAME);
		session_id($this->sessionid);
		session_start();
		$session =& $_SESSION[self::EGW_SESSION_VAR];

		if ($session['session_dla'] <= time() - $GLOBALS['egw_info']['server']['sessions_timeout'])
		{
			if (self::$errorlog_debug) error_log(__METHOD__."('$sessionid') session timed out");
			$this->destroy($sessionid,$kp3);
			return false;
		}

		$this->session_flags = $session['session_flags'];

		$this->split_login_domain($session['session_lid'],$this->account_lid,$this->account_domain);

		// This is to ensure that we authenticate to the correct domain (might not be default)
		if($GLOBALS['egw_info']['user']['domain'] && $this->account_domain != $GLOBALS['egw_info']['user']['domain'])
		{
			throw new Exception("Wrong domain! '$this->account_domain' != '{$GLOBALS['egw_info']['user']['domain']}'");
/*			if (self::$errorlog_debug) error_log(__METHOD__."('$sessionid','$kp3') account_domain='$this->account_domain' != '{$GLOBALS['egw_info']['user']['domain']}'=egw_info[user][domain]");
			$GLOBALS['egw']->ADOdb = null;
			$GLOBALS['egw_info']['user']['domain'] = $this->account_domain;
			// reset the db
			$GLOBALS['egw_info']['server']['db_host'] = $GLOBALS['egw_domain'][$this->account_domain]['db_host'];
			$GLOBALS['egw_info']['server']['db_port'] = $GLOBALS['egw_domain'][$this->account_domain]['db_port'];
			$GLOBALS['egw_info']['server']['db_name'] = $GLOBALS['egw_domain'][$this->account_domain]['db_name'];
			$GLOBALS['egw_info']['server']['db_user'] = $GLOBALS['egw_domain'][$this->account_domain]['db_user'];
			$GLOBALS['egw_info']['server']['db_pass'] = $GLOBALS['egw_domain'][$this->account_domain]['db_pass'];
			$GLOBALS['egw_info']['server']['db_type'] = $GLOBALS['egw_domain'][$this->account_domain]['db_type'];
			$GLOBALS['egw']->setup('',false);
*/
		}
		$GLOBALS['egw_info']['user']['kp3'] = $this->kp3;

		// allow xajax / notifications to not update the dla, so sessions can time out again
		if (!isset($GLOBALS['egw_info']['flags']['no_dla_update']) || !$GLOBALS['egw_info']['flags']['no_dla_update'])
		{
			$this->update_dla();
		}
		$this->account_id = $GLOBALS['egw']->accounts->name2id($this->account_lid,'account_lid','u');
		if (!$this->account_id)
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) !accounts::name2id('$this->account_lid')");
			return false;
		}

		$GLOBALS['egw_info']['user']['account_id'] = $this->account_id;

		// init the crypto object before appsession call below
		$this->key = md5($this->kp3 . $this->sessionid . @$GLOBALS['egw_info']['server']['encryptkey']);
		$this->iv  = $GLOBALS['egw_info']['server']['mcrypt_iv'];
		$GLOBALS['egw']->crypto->init(array($this->key,$this->iv));

		if ($fill_egw_info_and_repositories)
		{
			$this->read_repositories($GLOBALS['egw_info']['server']['cache_phpgw_info']);
		}

		if ($this->user['expires'] != -1 && $this->user['expires'] < time())
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) accounts is expired");
			if(is_object($GLOBALS['egw']->log))
			{
				$GLOBALS['egw']->log->message(array(
					'text' => 'W-VerifySession, account loginid %1 is expired',
					'p1'   => $this->account_lid,
					'line' => __LINE__,
					'file' => __FILE__
				));
				$GLOBALS['egw']->log->commit();
			}
			return false;
		}
		if ($fill_egw_info_and_repositories)
		{
			$GLOBALS['egw_info']['user']  = $this->user;
			$GLOBALS['egw_info']['hooks'] = $this->hooks;

			$GLOBALS['egw_info']['user']['session_ip'] = $session['session_ip'];
			$GLOBALS['egw_info']['user']['passwd']     = base64_decode($this->appsession('password','phpgwapi'));
		}
		if ($this->account_domain != $GLOBALS['egw_info']['user']['domain'])
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) wrong domain");
			if(is_object($GLOBALS['egw']->log))
			{
				$GLOBALS['egw']->log->message(array(
					'text' => 'W-VerifySession, the domains %1 and %2 don\'t match',
					'p1'   => $userid_array[1],
					'p2'   => $GLOBALS['egw_info']['user']['domain'],
					'line' => __LINE__,
					'file' => __FILE__
				));
				$GLOBALS['egw']->log->commit();
			}
			return false;
		}

		if ($GLOBALS['egw_info']['server']['sessions_checkip'])
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) wrong IP");
			if (strtoupper(substr(PHP_OS,0,3)) != 'WIN' && (!$GLOBALS['egw_info']['user']['session_ip'] ||
				$GLOBALS['egw_info']['user']['session_ip'] != $this->getuser_ip()))
			{
				if(is_object($GLOBALS['egw']->log))
				{
					// This needs some better wording
					$GLOBALS['egw']->log->message(array(
						'text' => 'W-VerifySession, IP %1 doesn\'t match IP %2 in session table',
						'p1'   => $this->getuser_ip(),
						'p2'   => $GLOBALS['egw_info']['user']['session_ip'],
						'line' => __LINE__,
						'file' => __FILE__
					));
					$GLOBALS['egw']->log->commit();
				}
				return false;
			}
		}

		if ($fill_egw_info_and_repositories)
		{
			$GLOBALS['egw']->acl->acl($this->account_id);
			accounts::getInstance()->setAccountId($this->account_id);
			$GLOBALS['egw']->preferences->preferences($this->account_id);
			$GLOBALS['egw']->applications->applications($this->account_id);
		}
		if (!$this->account_lid)
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) !account_lid");
			if(is_object($GLOBALS['egw']->log))
			{
				// This needs some better wording
				$GLOBALS['egw']->log->message(array(
					'text' => 'W-VerifySession, account_id is empty',
					'line' => __LINE__,
					'file' => __FILE__
				));
				$GLOBALS['egw']->log->commit();
			}
			//echo 'DEBUG: Sessions: account_id is empty!<br>'."\n";
			return false;
		}
		// if user is anonymous and enters a not allowed application its session will be destroyed inmediatly.
		$_current_app=$GLOBALS['egw_info']['flags']['currentapp'];
		if($this->session_flags=='A' && !$GLOBALS['egw_info']['user']['apps'][$_current_app])
		{
			if (self::$errorlog_debug) error_log("*** session::verify($sessionid) anon user entering not allowed app");
			$this->destroy($sessionid,$kp3);

			/* Overwrite Cookie with empty user. For 2 weeks */
			self::egw_setcookie(self::EGW_SESSION_NAME,'');
			self::egw_setcookie('kp3','');
			self::egw_setcookie('domain','');
			self::egw_setcookie('last_domain','');
			self::egw_setcookie('last_loginid', '');

			return false;
		}
		if (self::$errorlog_debug) error_log("--> session::verify($sessionid) SUCCESS");

		return true;
	}

	/**
	 * Terminate a session
	 *
	 * @param string $sessionid the id of the session to be terminated
	 * @param string $kp3
	 * @return boolean true on success, false on error
	 */
	function destroy($sessionid, $kp3)
	{
		if (!$sessionid && $kp3)
		{
			return false;
		}
		$this->log_access($this->sessionid);	// log logout-time

		if (self::$errorlog_debug) error_log(__METHOD__."($sessionid,$kp3) parent::destroy()=$ret");

		$GLOBALS['egw']->hooks->process(array(
			'location'  => 'session_destroyed',
			'sessionid' => $sessionid,
		),'',true);	// true = run hooks from all apps, not just the ones the current user has perms to run

		// Only do the following, if where working with the current user
		if (!$GLOBALS['egw_info']['user']['sessionid'] || $sessionid == $GLOBALS['egw_info']['user']['sessionid'])
		{
			session_unset();
			//echo '<p>'.__METHOD__.": session_destroy() returned ".(session_destroy() ? 'true' : 'false')."</p>\n";
			@session_destroy();
			if ($GLOBALS['egw_info']['server']['usecookies'])
			{
				self::egw_setcookie(session_name());
			}
		}
		else
		{
			$sessions = self::session_list(0,'','',true);

			if (isset($sessions[$sessionid]) && session_module_name() == 'files')
			{
				//echo '<p>'.__METHOD__."($session_id): unlink('".$sessions[$sessionid]['php_session_file']."')</p>\n";
				@unlink($sessions[$sessionid]['php_session_file']);
			}
		}
		return true;
	}

	/**
	 * Generate a url which supports url or cookies based sessions
	 *
	 * Please note, the values of the query get url encoded!
	 *
	 * @param string $url a url relative to the egroupware install root, it can contain a query too
	 * @param array/string $extravars query string arguements as string or array (prefered)
	 * @return string generated url
	 */
	public static function link($url, $extravars = '')
	{
		//echo '<p>'.__METHOD__."(url='$url',extravars='".array2string($extravars)."')";

		if ($url[0] != '/')
		{
			$app = $GLOBALS['egw_info']['flags']['currentapp'];
			if ($app != 'login' && $app != 'logout')
			{
				$url = $app.'/'.$url;
			}
		}

		// append the url to the webserver url, but avoid more then one slash between the parts of the url
		if ($url[0] != '/' || $GLOBALS['egw_info']['server']['webserver_url'] != '/')
		{
			if($url[0] != '/' && substr($GLOBALS['egw_info']['server']['webserver_url'],-1) != '/')
			{
				$url = $GLOBALS['egw_info']['server']['webserver_url'] .'/'. $url;
			}
			else
			{
				$url = $GLOBALS['egw_info']['server']['webserver_url'] . $url;
			}
		}

		if(isset($GLOBALS['egw_info']['server']['enforce_ssl']) && $GLOBALS['egw_info']['server']['enforce_ssl'])
		{
			if(substr($url ,0,4) != 'http')
			{
				$url = 'https://'.$GLOBALS['egw_info']['server']['hostname'].$url;
			}
			else
			{
				$url = str_replace ( 'http:', 'https:', $url);
			}
		}
		$vars = array();
		// add session params if not using cookies
		if (!$GLOBALS['egw_info']['server']['usecookies'])
		{
			$vars[self::EGW_SESSION_NAME] = $GLOBALS['egw']->session->sessionid;
			$vars['kp3'] = $GLOBALS['egw']->session->kp3;
			$vars['domain'] = $GLOBALS['egw']->session->account_domain;
		}

		// check if the url already contains a query and ensure that vars is an array and all strings are in extravars
		list($url,$othervars) = explode('?',$url);
		if ($extravars && is_array($extravars))
		{
			$vars += $extravars;
			$extravars = $othervars;
		}
		else
		{
			if ($othervars) $extravars .= '&'.$othervars;
		}

		// parse extravars string into the vars array
		if ($extravars)
		{
			foreach(explode('&',$extravars) as $expr)
			{
				list($var,$val) = explode('=', $expr,2);
				if (substr($var,-2) == '[]')
				{
					$vars[substr($var,0,-2)][] = $val;
				}
				else
				{
					$vars[$var] = $val;
				}
			}
		}

		// if there are vars, we add them urlencoded to the url
		if (count($vars))
		{
			$query = array();
			foreach($vars as $key => $value)
			{
				if (is_array($value))
				{
					foreach($value as $val)
					{
						$query[] = $key.'[]='.urlencode($val);
					}
				}
				else
				{
					$query[] = $key.'='.urlencode($value);
				}
			}
			$url .= '?' . implode('&',$query);
		}
		//echo " = '$url'</p>\n";
		return $url;
	}

	/**
	 * Stores or retrieve applications data in/form the eGW session
	 *
	 * @param string $location free lable to store the data
	 * @param string $appname='' default current application (egw_info[flags][currentapp])
	 * @param mixed $data='##NOTHING##' if given, data to store, if not specified
	 * @todo enable session encryption again, but with the whole session data at once, not every single value, which would also solve mcrypts padding problem
	 * @return mixed session data or false if no data stored for $appname/$location
	 */
	public static function &appsession($location = 'default', $appname = '', $data = '##NOTHING##')
	{
		if (!$appname)
		{
			$appname = $GLOBALS['egw_info']['flags']['currentapp'];
		}
		//error_log(__METHOD__."($location,$appname,$data)");

		// allow to store eg. '' as the value.
		if ($data === '##NOTHING##')
		{
			// do not decrypt and return if no data (decrypt returning garbage)
			if(isset($_SESSION[self::EGW_APPSESSION_VAR][$appname]) && array_key_exists($location,$_SESSION[self::EGW_APPSESSION_VAR][$appname]))
			{
				return /*$GLOBALS['egw']->crypto->decrypt(*/$_SESSION[self::EGW_APPSESSION_VAR][$appname][$location];//);
			}
			return false;
		}
		return $_SESSION[self::EGW_APPSESSION_VAR][$appname][$location] =& $data; //$GLOBALS['egw']->crypto->encrypt($data);
	}

	/**
	 * Get the ip address of current users
	 *
	 * @return string ip address
	 */
	public static function getuser_ip()
	{
		return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * domain for cookies
	 *
	 * @var string
	 */
	private static $cookie_domain = '';

	/**
	 * path for cookies
	 *
	 * @var string
	 */
	private static $cookie_path = '/';

	/**
	 * Set a cookie with eGW's cookie-domain and -path settings
	 *
	 * @param string $cookiename name of cookie to be set
	 * @param string $cookievalue='' value to be used, if unset cookie is cleared (optional)
	 * @param int $cookietime=0 when cookie should expire, 0 for session only (optional)
	 * @param string $cookiepath=null optional path (eg. '/') if the eGW install-dir should not be used
	 */
	public static function egw_setcookie($cookiename,$cookievalue='',$cookietime=0,$cookiepath=null)
	{
		if (!isset(self::$cookie_domain) || !isset(self::$cookie_path))
		{
			self::set_cookiedomain();
		}
		if (self::$errorlog_debug) error_log(__METHOD__."($cookiename,$cookievalue,$cookietime,$cookiepath,self::$cookie_domain)");

		setcookie($cookiename,$cookievalue,$cookietime,is_null($cookiepath) ? self::$cookie_path : $cookiepath,self::$cookie_domain);
	}

	/**
	 * Set the domain and path used for cookies
	 */
	private static function set_cookiedomain()
	{
		if ($GLOBALS['egw_info']['server']['cookiedomain'])
		{
			// Admin set domain, eg. .domain.com to allow egw.domain.com and www.domain.com
			self::$cookie_domain = $GLOBALS['egw_info']['server']['cookiedomain'];
		}
		else
		{
			// Use HTTP_X_FORWARDED_HOST if set, which is the case behind a none-transparent proxy
			self::$cookie_domain = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?  $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
		}
		// remove port from HTTP_HOST
		if (preg_match("/^(.*):(.*)$/",self::$cookie_domain,$arr))
		{
			self::$cookie_domain = $arr[1];
		}
		if (count(explode('.',self::$cookie_domain)) <= 1)
		{
			// setcookie dont likes domains without dots, leaving it empty, gets setcookie to fill the domain in
			self::$cookie_domain = '';
		}
		if (!$GLOBALS['egw_info']['server']['cookiepath'] ||
			!(self::$cookie_path = parse_url($GLOBALS['egw_info']['server']['webserver_url'],PHP_URL_PATH)))
		{
			 self::$cookie_path = '/';
		}
		//echo "<p>cookie_path='self::$cookie_path', cookie_domain='self::$cookie_domain'</p>\n";

		session_set_cookie_params(0,$path,$domain);
	}

	/**
	 * Search the instance matching the request
	 *
	 * @param string $login on login $_POST['login']
	 * @param string $domain_requested usually $_REQUEST['domain']
	 * @param string &$default_domain usually $default_domain get's set eg. by sitemgr
	 * @param string $server_name usually $_SERVER['SERVER_NAME']
	 * @param array $domains=null defaults to $GLOBALS['egw_domain'] from the header
	 * @return string $GLOBALS['egw_info']['user']['domain'] set with the domain/instance to use
	 */
	public static function search_instance($login,$domain_requested,&$default_domain,$server_name,array $domains=null)
	{
		if (is_null($domains)) $domains = $GLOBALS['egw_domain'];

		if (!isset($default_domain) || !isset($domains[$default_domain]))	// allow to overwrite the default domain
		{
			if(isset($domains[$server_name]))
			{
				$default_domain = $server_name;
			}
			else
			{
				$domain_part = explode('.',$server_name);
				array_shift($domain_part);
				$domain_part = implode('.',$domain_part);
				if(isset($domains[$domain_part]))
				{
					$default_domain = $domain_part;
				}
				else
				{
					reset($domains);
					list($default_domain) = each($domains);
				}
				unset($domain_part);
			}
		}
		if (isset($login))	// on login
		{
			if (strpos($login,'@') === false || count($domains) == 1)
			{
				$login .= '@' . (isset($_POST['logindomain']) ? $_POST['logindomain'] : $default_domain);
			}
			$parts = explode('@',$login);
			$domain = array_pop($parts);
			$GLOBALS['login'] = $login;
		}
		else	// on "normal" pageview
		{
			$domain = $domain_requested;
		}
		if (!isset($domains[$domain]))
		{
			$domain = $default_domain;
		}
		return $domain;
	}

	/**
	 * Update session_action and session_dla (session last used time),
	 */
	private function update_dla()
	{
		if (isset($_GET['menuaction']))
		{
			$action = $_GET['menuaction'];
		}
		else
		{
			$action = $_SERVER['PHP_SELF'];
		}

		// This way XML-RPC users aren't always listed as
		// xmlrpc.php
		if ($this->xmlrpc_method_called)
		{
			$action = $this->xmlrpc_method_called;
		}

		$_SESSION[self::EGW_SESSION_VAR]['session_dla'] = time();
		$_SESSION[self::EGW_SESSION_VAR]['session_action'] = $action;
	}

	/**
	 * Read the diverse repositories / init classes with data from the just loged in user
	 *
	 */
	public function read_repositories()
	{
		$GLOBALS['egw']->acl->acl($this->account_id);
		accounts::getInstance()->setAccountId($this->account_id);
		$GLOBALS['egw']->preferences->preferences($this->account_id);
		$GLOBALS['egw']->applications->applications($this->account_id);

		$this->user                = $GLOBALS['egw']->accounts->read_repository();
		$this->user['acl']         = $GLOBALS['egw']->acl->read_repository();
		$this->user['preferences'] = $GLOBALS['egw']->preferences->read_repository();
		if (is_object($GLOBALS['egw']->datetime))
		{
			$GLOBALS['egw']->datetime->datetime();		// to set tz_offset from the now read prefs
		}
		$this->user['apps']        = $GLOBALS['egw']->applications->read_repository();
		$this->user['domain']      = $this->account_domain;
		$this->user['sessionid']   = $this->sessionid;
		$this->user['kp3']         = $this->kp3;
		$this->user['session_ip']  = $this->getuser_ip();
		$this->user['session_lid'] = $this->account_lid.'@'.$this->account_domain;
		$this->user['account_id']  = $this->account_id;
		$this->user['account_lid'] = $this->account_lid;
		$this->user['userid']      = $this->account_lid;
		$this->user['passwd']      = @$this->passwd;

		$this->hooks = $GLOBALS['egw']->hooks->read();
	}

	/**
	 * Splits a login-name into account_lid and eGW-domain/-instance
	 *
	 * @param string $login login-name (ie. user@default)
	 * @param string &$account_lid returned account_lid (ie. user)
	 * @param string &$domain returned domain (ie. domain)
	 */
	private function split_login_domain($login,&$account_lid,&$domain)
	{
		$parts = explode('@',$login);

		//conference - for strings like vinicius@thyamad.com@default ,
		//allows that user have a login that is his e-mail. (viniciuscb)
		if (count($parts) > 1)
		{
			$probable_domain = array_pop($parts);
			//Last part of login string, when separated by @, is a domain name
			if (in_array($probable_domain,$this->egw_domains))
			{
				$got_login = true;
				$domain = $probable_domain;
				$account_lid = implode('@',$parts);
			}
		}

		if (!$got_login)
		{
			$domain = $GLOBALS['egw_info']['server']['default_domain'];
			$account_lid = $login;
		}
	}

	/*
	 * Funtions to access the used session-handler, specified in header.inc.php: $GLOBALS['egw_info']['server']['session_handler']
	 */

	/**
	 * Name of session-handler-class
	 *
	 * @var string
	 */
	private static $session_handler = 'egw_session_files';

	/**
	 * Initialise the used session handler
	 */
	public static function init_handler()
	{
		if (isset($GLOBALS['egw_info']['server']['session_handler']) && class_exists($GLOBALS['egw_info']['server']['session_handler']))
		{
			self::$session_handler = $GLOBALS['egw_info']['server']['session_handler'];
		}
		//error_log(__METHOD__.'() session_handler='.self::$session_handler.', egw_info[server][session_handler]='.$GLOBALS['egw_info']['server']['session_handler']);

		if (method_exists(self::$session_handler,'init_session_handler'))
		{
			call_user_func(array(self::$session_handler,'init_session_handler'));
		}
		ini_set('session.use_cookies',0);	// disable the automatic use of cookies, as it uses the path / by default
		session_name(self::EGW_SESSION_NAME);
	}

	/**
	 * Get a session list (of the current instance)
	 *
	 * @param int $start
	 * @param string $sort='session_dla' session_lid, session_id, session_started, session_logintime, session_action, or (default) session_dla
	 * @param string $order='ASC' ASC or DESC
	 * @return array with sessions (values for keys as in $sort) or array() if not supported by session-handler
	 */
	public static function session_list($start,$sort='ASC',$order='session_dla')
	{
		if (method_exists(self::$session_handler,'session_list'))
		{
			return call_user_func(array(self::$session_handler,'session_list'),$start,$sort,$order);
		}
		return array();
	}

	/**
	 * Query number of sessions (not more then once every N secs)
	 *
	 * @return int|boolean integer number of sessions or false if not supported by session-handler
	 */
	public static function session_count()
	{
		if (method_exists(self::$session_handler,'session_count'))
		{
			return call_user_func(array(self::$session_handler,'session_count'));
		}
		return false;
	}

	/*
	 * depricated functions, to be removed after 1.6
	 */

	/**
	 * commit the sessiondata to storage
	 *
	 * @deprecated call session_write_close() direct, as we only support php sessions
	 */
	function commit_session()
	{
		session_write_close();
	}

	/**
	 * Delete all data from the session cache for a user
	 *
	 * @param int $accountid user account id, defaults to current user (optional)
	 * @deprecated not longer used / necessary
	 */
	function delete_cache($accountid='')
	{

	}
}
