<?php
/**
 * eGgroupWare setup - test or create the ldap connection and hierarchy
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package setup
 * @copyright (c) 2007-10 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * setup command: test or create the ldap connection and hierarchy
 */
class setup_cmd_ldap extends setup_cmd
{
	/**
	 * Allow to run this command via setup-cli
	 */
	const SETUP_CLI_CALLABLE = true;

	/**
	 * Instance of ldap object
	 *
	 * @var ldap
	 */
	private $test_ldap;

	/**
	 * Constructor
	 *
	 * @param string/array $domain domain-name to customize the defaults or array with all parameters
	 * @param string $ldap_host=null
	 * @param string $ldap_suffix=null base of the whole ldap install, default "dc=local"
	 * @param string $ldap_admin=null root-dn needed to create new entries in the suffix
	 * @param string $ldap_admin_pw=null
	 * @param string $ldap_base=null base of the instance, default "o=$domain,$suffix"
	 * @param string $ldap_root_dn=null root-dn used for the instance, default "cn=admin,$base"
	 * @param string $ldap_root_pw=null
	 * @param string $ldap_context=null ou for accounts, default "ou=accounts,$base"
	 * @param string $ldap_search_filter=null search-filter for accounts, default "(uid=%user)"
	 * @param string $ldap_group_context=null ou for groups, default "ou=groups,$base"
	 * @param string $sub_command='create_ldap' 'create_ldap', 'test_ldap', 'test_ldap_root'
	 */
	function __construct($domain,$ldap_host=null,$ldap_suffix=null,$ldap_admin=null,$ldap_admin_pw=null,
		$ldap_base=null,$ldap_root_dn=null,$ldap_root_pw=null,$ldap_context=null,$ldap_search_filter=null,
		$ldap_group_context=null,$sub_command='create_ldap')
	{
		if (!is_array($domain))
		{
			$domain = array(
				'domain'        => $domain,
				'ldap_host'     => $ldap_host,
				'ldap_suffix'   => $ldap_suffix,
				'ldap_admin'    => $ldap_admin,
				'ldap_admin_pw' => $ldap_admin_pw,
				'ldap_base'     => $ldap_base,
				'ldap_root_dn'  => $ldap_root_dn,
				'ldap_root_pw'  => $ldap_root_pw,
				'ldap_context'  => $ldap_context,
				'ldap_search_filter' => $ldap_search_filter,
				'ldap_group_context' => $ldap_group_context,
				'sub_command'   => $sub_command
			);
		}
		//echo __CLASS__.'::__construct()'; _debug_array($domain);
		admin_cmd::__construct($domain);
	}

	/**
	 * run the command: test or create the ldap connection and hierarchy
	 *
	 * @param boolean $check_only=false only run the checks (and throw the exceptions), but not the command itself
	 * @return string success message
	 * @throws Exception(lang('Wrong credentials to access the header.inc.php file!'),2);
	 * @throws Exception('header.inc.php not found!');
	 */
	protected function exec($check_only=false)
	{
		if (!empty($this->domain) && !preg_match('/^([a-z0-9_-]+\.)*[a-z0-9]+/i',$this->domain))
		{
			throw new egw_exception_wrong_userinput(lang("'%1' is no valid domain name!",$this->domain));
		}
		if ($this->remote_id && $check_only) return true;	// further checks can only done locally

		$this->_merge_defaults();
		//_debug_array($this->as_array());

		switch($this->sub_command)
		{
			case 'test_ldap_root':
				$msg = $this->connect($this->ldap_admin,$this->ldap_admin_pw);
				break;
			case 'test_ldap':
				$msg = $this->connect();
				break;
			case 'delete_ldap':
				$msg = $this->delete_base();
				break;
			case 'users_ldap':
				$msg = $this->users();
				break;
			case 'create_ldap':
			default:
				$msg = $this->create();
				break;
		}
		return $msg;
	}

	/**
	 * Connect to ldap server
	 *
	 * @param string $dn=null default $this->ldap_root_dn
	 * @param string $pw=null default $this->ldap_root_pw
	 * @throws egw_exception_wrong_userinput Can not connect to ldap ...
	 */
	private function connect($dn=null,$pw=null)
	{
		if (is_null($dn)) $dn = $this->ldap_root_dn;
		if (is_null($pw)) $pw = $this->ldap_root_pw;

		if (!$pw)	// ldap::ldapConnect use the current eGW's pw otherwise
		{
			throw new egw_exception_wrong_userinput(lang('You need to specify a password!'));
		}
		$this->test_ldap = new ldap();

		$error_rep = error_reporting();
		//error_reporting($error_rep & ~E_WARNING);	// switch warnings of, in case they are on
		ob_start();
		$ds = $this->test_ldap->ldapConnect($this->ldap_host,$dn,$pw);
		ob_end_clean();
		error_reporting($error_rep);

		if (!$ds)
		{
			throw new egw_exception_wrong_userinput(lang('Can not connect to LDAP server on host %1 using DN %2!',
				$this->ldap_host,$dn).($this->test_ldap->ds ? ' ('.ldap_error($this->test_ldap->ds).')' : ''));
		}
		return lang('Successful connected to LDAP server on %1 using DN %2.',$this->ldap_host,$dn);
	}
	
	/**
	 * Count active (not expired) users
	 *
	 * @return int number of active users
	 * @throws egw_exception_wrong_userinput
	 */
	private function users()
	{
		$this->connect();
	
		$sr = ldap_list($this->test_ldap->ds,$this->ldap_context,'ObjectClass=posixAccount',array('dn','shadowExpire'));
		if (!($entries = ldap_get_entries($this->test_ldap->ds, $sr)))
		{
			throw new egw_exception('Error listing "dn=%1"!',$this->ldap_context);
		}
		$num = 0;
		foreach($entries as $n => $entry)
		{
			if ($n === 'count') continue;
			if (isset($entry['shadowexpire']) && $entry['shadowexpire'][0]*24*3600 < time()) continue;
			++$num;
		}
		return $num;
	}
		
	/**
	 * Check and if does not yet exist create the new database and user
	 *
	 * @return string with success message
	 * @throws egw_exception_wrong_userinput
	 */
	private function create()
	{
		$this->connect($this->ldap_admin,$this->ldap_admin_pw);

		foreach(array(
			$this->ldap_base => array(),
			$this->ldap_context => array(),
			$this->ldap_group_context => array(),
			$this->ldap_root_dn => array('userPassword' => '{crypt}'.crypt($this->ldap_root_pw)),
		) as $dn => $extra)
		{
			if (!$this->_create_node($dn,$extra,$check_only) && $dn == $this->ldap_root_dn)
			{
				// ldap_root already existed, lets check the pw is correct
				$this->connect();
			}
		}
		return lang('Successful connected to LDAP server on %1 and created/checked required structur %2.',
			$this->ldap_host,$this->ldap_base);
	}

	/**
	 * Delete whole LDAP tree of an instance dn=$this->ldap_base using $this->ldap_admin/_pw
	 *
	 * @return string with success message
	 * @throws egw_exception if dn not found, not listable or delete fails
	 */
	private function delete_base()
	{
		$this->connect($this->ldap_admin,$this->ldap_admin_pw);
		
		// if base not set, use context minus one hierarchy, eg. ou=accounts,(o=domain,dc=local)
		if (empty($this->ldap_base) && $this->ldap_context)
		{
			list(,$this->ldap_base) = explode(',',$this->ldap_context,2);
		}
		// some precausion to not delete whole ldap tree!
		if (count(explode(',',$this->ldap_base)) < 2)
		{
			throw new egw_exception_assertion_failed(lang('Refusing to delete dn "%1"!',$this->ldap_base));
		}
		// check if base does exist
		if (!@ldap_read($this->test_ldap->ds,$this->ldap_base,'objectClass=*'))
		{
			throw new egw_exception_wrong_userinput(lang('Base dn "%1" NOT found!',$this->ldap_base));
		}
		return lang('LDAP dn="%1" with %2 entries deleted.',
			$this->ldap_base,$this->rdelete($this->ldap_base));
	}
	
	/**
	 * Recursive delete a dn
	 * 
	 * @param string $dn
	 * @return int integer number of deleted entries
	 * @throws egw_exception if dn not listable or delete fails
	 */
	private function rdelete($dn)
	{
		if (!($sr = ldap_list($this->test_ldap->ds,$dn,'ObjectClass=*',array(''))) ||
			!($entries = ldap_get_entries($this->test_ldap->ds, $sr)))
		{
			throw new egw_exception(lang('Error listing "dn=%1"!',$dn));
		}
		foreach($entries as $n => $entry)
		{
			if ($n === 'count') continue;
			$this->rdelete($entry['dn']);
		}
		if (!ldap_delete($this->test_ldap->ds,$dn))
		{
			throw new egw_exception(lang('Error deleting "dn=%1"!',$dn));
		}
		return 1 + $entries['count'];
	}

	/**
	 * array with objectclasses for the objects we can create
	 *
	 * @var array of name => objectClass pairs (or array with multiple)
	 */
	static $requiredObjectclasses = array(
		'o' => 'organization',
		'ou' => 'organizationalUnit',
		'cn' => array('organizationalRole','simpleSecurityObject'),
		'dc' => array('organization','dcObject'),
	);

	/**
	 * Create a new node in the ldap tree
	 *
	 * @param string $dn dn to create, eg. "cn=admin,dc=local"
	 * @param array $extra=array() extra attributes to set
	 * @return boolean true if the node was create, false if it was already there
	 * @throws egw_exception_wrong_userinput
	 */
	private function _create_node($dn,$extra=array())
	{
		//echo "<p>_create_node($dn,".print_r($extra,true).")</p>\n";
		// check if the node already exists and return if it does
		if (@ldap_read($this->test_ldap->ds,$dn,'objectClass=*'))
		{
			return false;
		}
		list($node,$base) = explode(',',$dn,2);

		if (!@ldap_read($this->test_ldap->ds,$base,'objectClass=*'))
		{
			$this->_create_node($base);		// create the base if it's not already there
		}
		// now we need to create the node itself
		list($name,$value) = explode('=',$node);

		if (!isset(self::$requiredObjectclasses[$name]))
		{
			throw new egw_exception_wrong_userinput(lang('Can not create DN %1!',$dn).' '.
				lang('Supported node types:').implode(', ',array_keys(self::$requiredObjectclasses)));
		}
		if ($name == 'dc') $extra['o'] = $value;	// required by organisation

		if (!@ldap_add($this->test_ldap->ds,$dn,$attr = array(
			$name => $value,
			'objectClass' => self::$requiredObjectclasses[$name],
		)+$extra))
		{
			throw new egw_exception_wrong_userinput(lang('Can not create DN %1!',$dn).
				' ('.ldap_error($this->test_ldap->ds).', attributes='.print_r($attr,true).')');
		}
		return true;
	}

	/**
	 * Return default database settings for a given domain
	 *
	 * @return array
	 */
	static function defaults()
	{
		return array(
			'ldap_host'     => 'localhost',
			'ldap_suffix'   => 'dc=local',
			'ldap_admin'    => 'cn=admin,$suffix',
			'ldap_admin_pw' => '',
			'ldap_base'     => 'o=$domain,$suffix',
			'ldap_root_dn'  => 'cn=admin,$base',
			'ldap_root_pw'  => self::randomstring(),
			'ldap_context'  => 'ou=accounts,$base',
			'ldap_search_filter' => '(uid=%user)',
			'ldap_group_context' => 'ou=groups,$base',
		);
	}

	/**
	 * Merges the default into the current properties, if they are empty or contain placeholders
	 */
	private function _merge_defaults()
	{
		foreach(self::defaults() as $name => $default)
		{
			if ($this->sub_command == 'delete_ldap' && in_array($name,array('ldap_base','ldap_context')))
			{
				continue;	// no default on what to delete!
			}
			if (!$this->$name)
			{
				//echo "<p>setting $name='{$this->$name}' to it's default='$default'</p>\n";
				$this->set_defaults[$name] = $this->$name = $default;
			}
			if (strpos($this->$name,'$') !== false)
			{
				$this->set_defaults[$name] = $this->$name = str_replace(array(
					'$domain',
					'$suffix',
					'$base',
					'$admin_pw',
				),array(
					$this->domain,
					$this->ldap_suffix,
					$this->ldap_base,
					$this->ldap_admin_pw,
				),$this->$name);
			}
		}
	}
}
