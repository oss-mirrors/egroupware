<?php
/**
 * EGroupware API - accounts
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> complete rewrite in 6/2006 and earlier modifications
 *
 * Implements the (now depricated) interfaces on the former accounts class written by
 * Joseph Engo <jengo@phpgroupware.org> and Bettina Gille <ceb@phpgroupware.org>
 * Copyright (C) 2000 - 2002 Joseph Engo, Copyright (C) 2003 Joseph Engo, Bettina Gille
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage accounts
 * @access public
 * @version $Id$
 */

/**
 * API - accounts
 *
 * This class uses a backend class (at them moment SQL or LDAP) and implements some
 * caching on to top of the backend functions:
 *
 * a) instance-wide account-data cache queried by account_id including also members(hips)
 *    implemented by self::cache_read($account_id) and self::cache_invalidate($account_ids)
 *
 * b) session based cache for search, split_accounts and name2id
 *    implemented by self::setup_cache() and self::cache_invalidate()
 *
 * The backend only implements the read, save, delete, name2id and the {set_}members{hips} methods.
 * The account class implements all other (eg. name2id, id2name) functions on top of these.
 *
 * read and search return timestamps (account_(created|modified|lastlogin) in server-time!
 */
class accounts
{
	var $xmlrpc_methods = array(
		array(
			'name'        => 'search',
			'description' => 'Returns a list of accounts and/or groups'
		),
		array(
			'name'        => 'name2id',
			'description' => 'Cross reference account_lid with account_id'
		),
		array(
			'name'        => 'id2name',
			'description' => 'Cross reference account_id with account_lid'
		),
		array(
			'name'        => 'get_list',
			'description' => 'Depricated: use search. Returns a list of accounts and/or groups'
		),
	);
	/**
	 * Enables the session-cache, currently switched on independent of the backend
	 *
	 * @var boolean
	 */
	static $use_session_cache = true;

	/**
	 * Cache, stored in sesssion
	 *
	 * @var array
	 */
	static $cache;

	/**
	 * Depricated: Account this class was instanciated for
	 *
	 * @deprecated dont use this in new code, always explcitly specify the account to use
	 * @var int account_id
	 */
	var $account_id;
	/**
	 * Depricated: Account data of $this->account_id
	 *
	 * @deprecated dont use this in new code, store the data in your own code
	 * @var array
	 */
	var $data;

	/**
	 * Keys for which both versions with 'account_' prefix and without (depricated!) can be used, if requested.
	 * Migrate your code to always use the 'account_' prefix!!!
	 *
	 * @var array
	 */
	var $depricated_names = array('firstname','lastname','fullname','email','type',
		'status','expires','lastlogin','lastloginfrom','lastpasswd_change');

	/**
	 * List of all config vars accounts depend on and therefore should be passed in when calling contructor with array syntax
	 *
	 * @var array
	 */
	static public $config_vars = array(
		'account_repository', 'auth_type',	// auth_type if fallback if account_repository is not set
		'install_id',	// instance-specific caching
		'auto_create_expire', 'default_group_lid',	// auto-creation of accounts
		'ldap_host','ldap_root_dn','ldap_root_pw','ldap_context','ldap_group_context','ldap_search_filter',	// ldap backend
		'ads_domain', 'ads_host', 'ads_admin_user', 'ads_admin_passwd', 'ads_connection', 'ads_context',	// ads backend
	);

	/**
	 * Querytypes for the account-search
	 *
	 * @var array
	 */
	var $query_types = array(
		'all' => 'all fields',
		'firstname' => 'firstname',
		'lastname' => 'lastname',
		'lid' => 'LoginID',
		'email' => 'email',
		'start' => 'start with',
		'exact' => 'exact',
	);

	/**
	 * Backend to use
	 *
	 * @var accounts_sql|accounts_ldap
	 */
	var $backend;

	/**
	 * total number of found entries
	 *
	 * @var int
	 */
	var $total;

	/**
	 * Current configuration
	 *
	 * @var array
	 */
	var $config;

	/**
	 * hold an instance of the accounts class
	 *
	 * @var accounts the instance of the accounts class
	 */
	private static $_instance = NULL;

	/**
	 * Singleton
	 *
	 * @return accounts
	 */
	public static function getInstance()
	{
		if (self::$_instance === NULL)
		{
			self::$_instance = new accounts;
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @param string|array $backend =null string with backend 'sql'|'ldap', or whole config array, default read from global egw_info
	 */
	public function __construct($backend=null)
	{
		if (is_numeric($backend))	// depricated use with account_id
		{
			if ((int)$backend) $this->account_id = (int) $backend;
			$backend = null;
		}
		if (is_array($backend))
		{
			$this->config = $backend;
			$backend = null;
			self::$_instance = $this;	// also set instance returned by singleton
			self::$cache = array();		// and empty our internal (session) cache
		}
		else
		{
			$this->config =& $GLOBALS['egw_info']['server'];

			if (!isset(self::$_instance)) self::$_instance = $this;
		}
		if (is_null($backend))
		{
			if (empty($this->config['account_repository']))
			{
				if (!empty($this->config['auth_type']))
				{
					$this->config['account_repository'] = $this->config['auth_type'];
				}
				else
				{
					$this->config['account_repository'] = 'sql';
				}
			}
			$backend = $this->config['account_repository'];
		}
		$backend_class = 'accounts_'.$backend;

		$this->backend = new $backend_class($this);
	}

	/**
	 * Old constructor name
	 *
	 * @param int $account_id =0 depricated param to instanciate for the given account_id
	 * @deprecated use __construct
	 */
	function accounts($account_id=0)
	{
		$this->account_id = (int) $account_id;

		$this->__construct();
	}

	/**
	 * set the accountId
	 *
	 * @param int $accountId
	 * @deprecated
	 */
    function setAccountId($accountId)
    {
        if($accountId && is_numeric($accountId))
        {
            $this->account_id = (int)$accountId;
        }
    }

	/**
	 * Searches / lists accounts: users and/or groups
	 *
	 * @param array with the following keys:
	 * @param $param['type'] string/int 'accounts', 'groups', 'owngroups' (groups the user is a member of), 'both',
	 * 	'groupmembers' (members of groups the user is a member of), 'groupmembers+memberships' (incl. memberships too)
	 *	or integer group-id for a list of members of that group
	 * @param $param['start'] int first account to return (returns offset or max_matches entries) or all if not set
	 * @param $param['order'] string column to sort after, default account_lid if unset
	 * @param $param['sort'] string 'ASC' or 'DESC', default 'DESC' if not set
	 * @param $param['query'] string to search for, no search if unset or empty
	 * @param $param['query_type'] string:
	 *	'all'   - query all fields for containing $param[query]
	 *	'start' - query all fields starting with $param[query]
	 *	'exact' - query all fields for exact $param[query]
	 *	'lid','firstname','lastname','email' - query only the given field for containing $param[query]
	 * @param $param['app'] string with an app-name, to limit result on accounts with run-right for that app
	 * @param $param['offset'] int - number of matches to return if start given, default use the value in the prefs
	 * @param $param['active']=true boolean - true: return only acctive accounts, false: return expired or deactivated too
	 * @return array with account_id => data pairs, data is an array with account_id, account_lid, account_firstname,
	 *	account_lastname, person_id (id of the linked addressbook entry), account_status, account_expires, account_primary_group
	 */
	function search($param)
	{
		//error_log(__METHOD__.'('.array2string($param).') '.function_backtrace());
		if (!isset($param['active'])) $param['active'] = true;	// default is true = only return active accounts

		self::setup_cache();
		$account_search = &self::$cache['account_search'];
		$serial = serialize($param);

		if (isset($account_search[$serial]))
		{
			$this->total = $account_search[$serial]['total'];
		}
		// no backend understands $param['app'], only sql understands type owngroups or groupmemember[+memberships]
		// --> do an full search first and then filter and limit that search
		elseif($param['app'] || $this->config['account_repository'] != 'sql' &&
			in_array($param['type'], array('owngroups','groupmembers','groupmembers+memberships')))
		{
			$app = $param['app'];
			unset($param['app']);
			$start = $param['start'];
			unset($param['start']);
			$offset = $param['offset'] ? $param['offset'] : $GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'];
			unset($param['offset']);
			$stop = $start + $offset;

			if ($param['type'] == 'owngroups')
			{
				$members = $this->memberships($GLOBALS['egw_info']['user']['account_id'],true);
				$param['type'] = 'groups';
			}
			elseif(in_array($param['type'],array('groupmembers','groupmembers+memberships')))
			{
				$members = array();
				foreach((array)$this->memberships($GLOBALS['egw_info']['user']['account_id'],true) as $grp)
				{
					$members = array_unique(array_merge($members, (array)$this->members($grp,true,$param['active'])));
					if ($param['type'] == 'groupmembers+memberships') $members[] = $grp;
				}
				$param['type'] = $param['type'] == 'groupmembers+memberships' ? 'both' : 'accounts';
			}
			// call ourself recursive to get (evtl. cached) full search
			$full_search = $this->search($param);

			// filter search now on accounts with run-rights for app or a group
			$valid = array();
			if ($app)
			{
				// we want the result merged, whatever it takes, as we only care for the ids
				$valid = $this->split_accounts($app,!in_array($param['type'],array('accounts','groups')) ? 'merge' : $param['type'],$param['active']);
			}
			if (isset($members))
			{
				//error_log(__METHOD__.'() members='.array2string($members));
				if (!$members) $members = array();
				$valid = !$app ? $members : array_intersect($valid,$members);	// use the intersection
			}
			//error_log(__METHOD__."() limiting result to app='$app' and/or group=$group valid-ids=".array2string($valid));
			$n = 0;
			$account_search[$serial]['data'] = array();
			foreach ($full_search as $id => $data)
			{
				if (!in_array($id,$valid))
				{
					$this->total--;
					continue;
				}
				// now we have a valid entry
				if (!is_int($start) || $start <= $n && $n < $stop)
				{
					$account_search[$serial]['data'][$id] = $data;
				}
				$n++;
			}
			$account_search[$serial]['total'] = $this->total;
		}
		// direct search via backend
		else
		{
			$account_search[$serial]['data'] = $this->backend->search($param);
			if ($param['type'] !== 'accounts')
			{
				foreach($account_search[$serial]['data'] as &$account)
				{
					// add default description for Admins and Default group
					if ($account['account_type'] === 'g' && empty($account['account_description']))
					{
						self::add_default_group_description($account);
					}
				}
			}
			$account_search[$serial]['total'] = $this->total = $this->backend->total;
		}
		//echo "<p>accounts::search(".array2string(unserialize($serial)).")= returning ".count($account_search[$serial]['data'])." of $this->total entries<pre>".print_r($account_search[$serial]['data'],True)."</pre>\n";
		//echo "<p>accounts::search() end: ".microtime()."</p>\n";
		return $account_search[$serial]['data'];
	}

	/**
	 * Query for accounts
	 *
	 * @param string|array $pattern
	 * @param array $options
	 *  $options['filter']['group'] only return members of that group
	 *  $options['account_type'] "accounts", "groups", "both" or "groupmembers"
	 * @return array with id - title pairs of the matching entries
	 */
	public static function link_query($pattern, array &$options = array())
	{
		if (isset($options['filter']) && !is_array($options['filter']))
		{
			$options['filter'] = (array)$options['filter'];
		}
		switch($GLOBALS['egw_info']['user']['preferences']['common']['account_display'])
		{
			case 'firstname':
			case 'firstall':
				$order = 'account_firstname,account_lastname';
				break;
			case 'lastname':
			case 'lastall':
				$order = 'account_lastname,account_firstname';
				break;
			default:
				$order = 'account_lid';
				break;
		}
		$only_own = $GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] === 'groupmembers' &&
			!isset($GLOBALS['egw_info']['user']['apps']['admin']);
		switch($options['account_type'])
		{
			case 'accounts':
				$type = $only_own ? 'groupmembers' : 'accounts';
				break;
			case 'groups':
				$type = $only_own ? 'memberships' : 'groups';
				break;
			case 'groupmembers':
			case 'memberships':
				$type = $options['account_type'];
				break;
			case 'both':
			default:
				$type = $only_own ? 'groupmembers+memberships' : 'both';
				break;
		}
		$accounts = array();
		foreach(self::getInstance()->search(array(
			'type' => $options['filter']['group'] < 0 ? $options['filter']['group'] : $type,
			'query' => $pattern,
			'query_type' => 'all',
			'order' => $order,
		)) as $account)
		{
			$accounts[$account['account_id']] = common::display_fullname($account['account_lid'],
				$account['account_firstname'],$account['account_lastname'],$account['account_id']);
		}
		return $accounts;
	}

	/**
	 * Reads the data of one account
	 *
	 * It's depricated to use read with out parameter to read the internal data of this class!!!
	 * All key of the returned array use the 'account_' prefix.
	 * For backward compatibility some values are additionaly availible without the prefix, using them is depricated!
	 *
	 * @param int|string $id numeric account_id or string with account_lid (use of default value of 0 is depricated!!!)
	 * @param boolean $set_depricated_names =false set _additionaly_ the depricated keys without 'account_' prefix
	 * @return array/boolean array with account data (keys: account_id, account_lid, ...) or false if account not found
	 */
	function read($id=0,$set_depricated_names=false)
	{
		if (!$id)	// deprecated use!!!
		{
			return $this->data ? $this->data : $this->read_repository();
		}
		if (!is_int($id) && !is_numeric($id))
		{
			$id = $this->name2id($id);
		}
		if (!$id) return false;

		$data = self::cache_read($id);

		// add default description for Admins and Default group
		if ($data['account_type'] === 'g' && empty($data['account_description']))
		{
			self::add_default_group_description($data);
		}

		if ($set_depricated_names && $data)
		{
			foreach($this->depricated_names as $name)
			{
				$data[$name] =& $data['account_'.$name];
			}
		}
		return $data;
	}

	/**
	 * Get an account as json, returns only whitelisted fields:
	 * - 'account_id','account_lid','person_id','account_status',
	 * - 'account_firstname','account_lastname','account_email','account_fullname','account_phone'
	 *
	 * @param int|string $id
	 * @return string|boolean json or false if not found
	 */
	function json($id)
	{
		static $keys = array(
			'account_id','account_lid','person_id','account_status',
			'account_firstname','account_lastname','account_email','account_fullname','account_phone',
		);
		if (($account = $this->read($id)))
		{
			$account = array_intersect_key($account, array_flip($keys));
		}
		// for current user, add the apps available to him
		if ($id == $GLOBALS['egw_info']['user']['account_id'])
		{
			foreach((array)$GLOBALS['egw_info']['user']['apps'] as $app => $data)
			{
				unset($data['table_defs']);	// no need for that on the client
				$account['apps'][$app] = $data;
			}
		}
		return json_encode($account);
	}

	/**
	 * Add a default description for stock groups: Admins, Default, NoGroup
	 *
	 * @param array &$data
	 */
	protected static function add_default_group_description(array &$data)
	{
		switch($data['account_lid'])
		{
			case 'Default':
				$data['account_description'] = lang('EGroupware all users group, do NOT delete');
				break;
			case 'Admins':
				$data['account_description'] = lang('EGroupware administrators group, do NOT delete');
				break;
			case 'NoGroup':
				$data['account_description'] = lang('EGroupware anonymous users group, do NOT delete');
				break;
		}
		error_log(__METHOD__."(".array2string($data).")");
	}

	/**
	 * Saves / adds the data of one account
	 *
	 * If no account_id is set in data the account is added and the new id is set in $data.
	 *
	 * @param array $data array with account-data
	 * @param boolean $check_depricated_names =false check _additionaly_ the depricated keys without 'account_' prefix
	 * @return int|boolean the account_id or false on error
	 */
	function save(&$data,$check_depricated_names=false)
	{
		if ($check_depricated_names)
		{
			foreach($this->depricated_names as $name)
			{
				if (isset($data[$name]) && !isset($data['account_'.$name]))
				{
					$data['account_'.$name] =& $data[$name];
				}
			}
		}
		// add default description for Admins and Default group
		if ($data['account_type'] === 'g' && empty($data['account_description']))
		{
			self::add_default_group_description($data);
		}
		if (($id = $this->backend->save($data)) && $data['account_type'] != 'g')
		{
			// if we are not on a pure LDAP system, we have to write the account-date via the contacts class now
			if (($this->config['account_repository'] == 'sql' || $this->config['contact_repository'] == 'sql-ldap') &&
				(!($old = $this->read($data['account_id'])) ||	// only for new account or changed contact-data
				$old['account_firstname'] != $data['account_firstname'] ||
				$old['account_lastname'] != $data['account_lastname'] ||
				$old['account_email'] != $data['account_email']))
			{
				if (!$data['person_id']) $data['person_id'] = $old['person_id'];

				$contact = array(
					'n_given'    => $data['account_firstname'],
					'n_family'   => $data['account_lastname'],
					'email'      => $data['account_email'],
					'account_id' => $data['account_id'],
					'id'         => $data['person_id'],
					'owner'      => 0,
				);
				$GLOBALS['egw']->contacts->save($contact,true);		// true = ignore addressbook acl
			}
			// save primary group if necessary
			if ($data['account_primary_group'] && (!($memberships = $this->memberships($id,true)) ||
				!in_array($data['account_primary_group'],$memberships)))
			{
				$memberships[] = $data['account_primary_group'];
				$this->set_memberships($memberships, $id);	// invalidates cache for account_id and primary group
			}
		}
		self::cache_invalidate($data['account_id']);

		return $id;
	}

	/**
	 * Delete one account, deletes also all acl-entries for that account
	 *
	 * @param int|string $id numeric account_id or string with account_lid
	 * @return boolean true on success, false otherwise
	 */
	function delete($id)
	{
		if (!is_int($id) && !is_numeric($id))
		{
			$id = $this->name2id($id);
		}
		if (!$id) return false;

		if ($this->get_type($id) == 'u')
		{
			$invalidate = $this->memberships($id, true);
		}
		else
		{
			$invalidate = $this->members($id, true, false);
		}
		$invalidate[] = $id;

		$this->backend->delete($id);

		self::cache_invalidate($invalidate);

		// delete all acl_entries belonging to that user or group
		$GLOBALS['egw']->acl->delete_account($id);

		// delete all categories belonging to that user or group
		categories::delete_account($id);

		return true;
	}

	/**
	 * test if an account is expired
	 *
	 * Can be used static if array with user-data is supplied
	 *
	 * @param array $data =null array with account data, not specifying the account is depricated!!!
	 * @return boolean true=expired (no more login possible), false otherwise
	 */
	function is_expired($data=null)
	{
		if (is_null($data)) $data = $this->data;	// depricated use

		$expires = isset($data['account_expires']) ? $data['account_expires'] : $data['expires'];

		return $expires != -1 && $expires < time();
	}

	/**
	 * Test if an account is active - NOT deactivated or expired
	 *
	 * Can be used static if array with user-data is supplied
	 *
	 * @param int|array $data account_id or array with account-data
	 * @return boolean false if account does not exist, is expired or decativated, true otherwise
	 */
	function is_active($data)
	{
		if (!is_array($data)) $data = $this->read($data);

		return $data && !(self::is_expired($data) || $data['account_status'] != 'A');
	}

	/**
	 * convert an alphanumeric account-value (account_lid, account_email) to the account_id
	 *
	 * Please note:
	 * - if a group and an user have the same account_lid the group will be returned (LDAP only)
	 * - if multiple user have the same email address, the returned user is undefined
	 *
	 * @param string $name value to convert
	 * @param string $which ='account_lid' type of $name: account_lid (default), account_email, person_id, account_fullname
	 * @param string $account_type =null u = user or g = group, or default null = try both
	 * @return int|false numeric account_id or false on error ($name not found)
	 */
	function name2id($name,$which='account_lid',$account_type=null)
	{
		// Don't bother searching for empty or non-scalar account_lid
		if(empty($name) || !is_scalar($name))
		{
			return False;
		}

		self::setup_cache();
		$name_list = &self::$cache['name_list'];

		if(@isset($name_list[$which][$name]) && $name_list[$which][$name])
		{
			return $name_list[$which][$name];
		}

		return $name_list[$which][$name] = $this->backend->name2id($name,$which,$account_type);
	}

	/**
	 * Convert an numeric account_id to any other value of that account (account_lid, account_email, ...)
	 *
	 * Uses the read method to fetch all data.
	 *
	 * @param int|string $account_id numeric account_id or account_lid
	 * @param string $which ='account_lid' type to convert to: account_lid (default), account_email, ...
	 * @return string|boolean converted value or false on error ($account_id not found)
	 */
	static function id2name($account_id, $which='account_lid')
	{
		if (!is_numeric($account_id) && !($account_id = self::getInstance()->name2id($account_id)))
		{
			return false;
		}
		try {
			if (!($data = self::cache_read($account_id))) return false;
		}
		catch (Exception $e) {
			unset($e);
			return false;
		}
		//echo "<p>accounts::id2name($account_id,$which)='{$data[$which]}'";
		return $data[$which];
	}

	/**
	 * get the type of an account: 'u' = user, 'g' = group
	 *
	 * @param int|string $account_id numeric account-id or alphanum. account-lid,
	 *	if !$accountid account of the user of this session
	 * @return string/false 'u' = user, 'g' = group or false on error ($accountid not found)
	 */
	function get_type($account_id)
	{
		if (!is_int($account_id) && !is_numeric($account_id))
		{
			$account_id = $this->name2id($account_id);
		}
		return $account_id > 0 ? 'u' : ($account_id < 0 ? 'g' : false);
	}

	/**
	 * check if an account exists and if it is an user or group
	 *
	 * @param int|string $account_id numeric account_id or account_lid
	 * @return int 0 = acount does not exist, 1 = user, 2 = group
	 */
	function exists($account_id)
	{
		if (!($data = $this->read($account_id)))
		{
			return 0;
		}
		return $data['account_type'] == 'u' ? 1 : 2;
	}

	/**
	 * Checks if a given account is visible to current user
	 *
	 * Not all existing accounts are visible because off account_selection preference: 'none' or 'groupmembers'
	 *
	 * @param int|string $account_id nummeric account_id or account_lid
	 * @return boolean true = account is visible, false = account not visible, null = account does not exist
	 */
	function visible($account_id)
	{
		if (!is_numeric($account_id))	// account_lid given
		{
			$account_lid = $account_id;
			if (!($account_id = $this->name2id($account_lid))) return null;
		}
		else
		{
			if (!($account_lid = $this->id2name($account_id))) return null;
		}
		if (!isset($GLOBALS['egw_info']['user']['apps']['admin']) &&
			// do NOT allow other user, if account-selection is none
			($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none' &&
				$account_lid != $GLOBALS['egw_info']['user']['account_lid'] ||
			// only allow group-members for account-selection is groupmembers
			$GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'groupmembers' &&
				!array_intersect((array)$this->memberships($account_id,true),
					(array)$this->memberships($GLOBALS['egw_info']['user']['account_id'],true))))
		{
			//error_log(__METHOD__."($account_id='$account_lid') returning FALSE");
			return false;	// user is not allowed to see given account
		}
		return true;	// user allowed to see given account
	}

	/**
	 * Get all memberships of an account $account_id / groups the account is a member off
	 *
	 * @param int|string $account_id numeric account-id or alphanum. account-lid
	 * @param boolean $just_id =false return just account_id's or account_id => account_lid pairs
	 * @return array with account_id's ($just_id) or account_id => account_lid pairs (!$just_id)
	 */
	function memberships($account_id, $just_id=false)
	{
		if (!is_int($account_id) && !is_numeric($account_id))
		{
			$account_id = $this->name2id($account_id,'account_lid','u');
		}
		if ($account_id && ($data = self::cache_read($account_id)))
		{
			$ret = $just_id && $data['memberships'] ? array_keys($data['memberships']) : $data['memberships'];
		}
		//error_log(__METHOD__."($account_id, $just_id) data=".array2string($data)." returning ".array2string($ret));
		return $ret;
	}

	/**
	 * Sets the memberships of a given account
	 *
	 * @param array $groups array with gidnumbers
	 * @param int $account_id uidnumber
	 */
	function set_memberships($groups,$account_id)
	{
		//echo "<p>accounts::set_memberships(".print_r($groups,true).",$account_id)</p>\n";
		if (!is_int($account_id) && !is_numeric($account_id))
		{
			$account_id = $this->name2id($account_id);
		}
		if (($old_memberships = $this->memberships($account_id, true)) != $groups)
		{
			$this->backend->set_memberships($groups, $account_id);

			if (!$old_memberships) $old_memberships = array();
			self::cache_invalidate(array_unique(array_merge(
				array($account_id),
				array_diff($old_memberships, $groups),
				array_diff($groups, $old_memberships)
			)));
		}
	}

	/**
	 * Get all members of the group $account_id
	 *
	 * @param int|string $account_id ='' numeric account-id or alphanum. account-lid,
	 *	default account of the user of this session
	 * @param boolean $just_id =false return just an array of id's and not id => lid pairs, default false
	 * @param boolean $active =false true: return only active (not expired or deactived) members, false: return all accounts
	 * @return array with account_id ($just_id) or account_id => account_lid pairs (!$just_id)
	 */
	function members($account_id, $just_id=false, $active=true)
	{
		if (!is_int($account_id) && !is_numeric($account_id))
		{
			$account_id = $this->name2id($account_id);
		}
		if ($account_id && ($data = self::cache_read($account_id, $active)))
		{
			$members = $active ? $data['members-active'] : $data['members'];

			return $just_id && $members ? array_keys($members) : $members;
		}
		return null;
	}

	/**
	 * Set the members of a group
	 *
	 * @param array $members array with uidnumber or uid's
	 * @param int $gid gidnumber of group to set
	 */
	function set_members($members,$gid)
	{
		//echo "<p>accounts::set_members(".print_r($members,true).",$gid)</p>\n";
		if (($old_members = $this->members($gid, true, false)) != $members)
		{
			$this->backend->set_members($members, $gid);

			self::cache_invalidate(array_unique(array_merge(
				array($gid),
				array_diff($old_members, $members),
				array_diff($members, $old_members)
			)));
		}
	}

	/**
	 * splits users and groups from a array of id's or the accounts with run-rights for a given app-name
	 *
	 * @param array $app_users array of user-id's or app-name (if you use app-name the result gets cached!)
	 * @param string $use what should be returned only an array with id's of either 'accounts' or 'groups'.
	 *	Or an array with arrays for 'both' under the keys 'groups' and 'accounts' or 'merge' for accounts
	 *	and groups merged into one array
	 * @param boolean $active =false true: return only active (not expired or deactived) members, false: return all accounts
	 * @return array/boolean see $use, false on error (wront $use)
	 */
	function split_accounts($app_users,$use='both',$active=true)
	{
		if (!is_array($app_users))
		{
			self::setup_cache();
			$cache = &self::$cache['account_split'][$app_user];

			if (is_array($cache))
			{
				return $cache;
			}
			$app_users = $GLOBALS['egw']->acl->get_ids_for_location('run',1,$app_users);
		}
		$accounts = array(
			'accounts' => array(),
			'groups' => array(),
		);
		foreach($app_users as $id)
		{
			$type = $this->get_type($id);
			if($type == 'g')
			{
				$accounts['groups'][$id] = $id;
				if ($use != 'groups')
				{
					foreach((array)$this->members($id, true, $active) as $id)
					{
						$accounts['accounts'][$id] = $id;
					}
				}
			}
			else
			{
				$accounts['accounts'][$id] = $id;
			}
		}

		// not sure why they need to be sorted, but we need to remove the keys anyway
		sort($accounts['groups']);
		sort($accounts['accounts']);

		if (isset($cache))
		{
			$cache = $accounts;
		}
		//echo "<p>accounts::split_accounts(".print_r($app_users,True).",'$use') = <pre>".print_r($accounts,True)."</pre>\n";

		switch($use)
		{
			case 'both':
				return $accounts;
			case 'groups':
				return $accounts['groups'];
			case 'accounts':
				return $accounts['accounts'];
			case 'merge':
				return array_merge($accounts['accounts'],$accounts['groups']);
		}
		return False;
	}

	/**
	 * Add an account for an authenticated user
	 *
	 * Expiration date and primary group are read from the system configuration.
	 *
	 * @param string $account_lid
	 * @param string $passwd
	 * @param array $GLOBALS['auto_create_acct'] values for 'firstname', 'lastname', 'email' and 'primary_group'
	 * @return int|boolean account_id or false on error
	 */
	function auto_add($account_lid, $passwd)
	{
		$expires = !isset($this->config['auto_create_expire']) ||
			$this->config['auto_create_expire'] == 'never' ? -1 :
			time() + $this->config['auto_create_expire'] + 2;

		$memberships = array();
		$default_group_id = null;
		// check if we have a comma or semicolon delimited list of groups --> add first as primary and rest as memberships
		foreach(preg_split('/[,;] */',$this->config['default_group_lid']) as $group_lid)
		{
			if (($group_id = $this->name2id($group_lid,'account_lid','g')))
			{
				if (!$default_group_id) $default_group_id = $group_id;
				$memberships[] = $group_id;
			}
		}
		if (!$default_group_id && ($default_group_id = $this->name2id('Default','account_lid','g')))
		{
			$memberships[] = $default_group_id;
		}

		$primary_group = $GLOBALS['auto_create_acct']['primary_group'] &&
			$this->get_type((int)$GLOBALS['auto_create_acct']['primary_group']) === 'g' ?
			(int)$GLOBALS['auto_create_acct']['primary_group'] : $default_group_id;
		if ($primary_group && !in_array($primary_group, $memberships))
		{
			$memberships[] = $primary_group;
		}
		$data = array(
			'account_lid'           => $account_lid,
			'account_type'          => 'u',
			'account_passwd'        => $passwd,
			'account_firstname'     => $GLOBALS['auto_create_acct']['firstname'] ? $GLOBALS['auto_create_acct']['firstname'] : 'New',
			'account_lastname'      => $GLOBALS['auto_create_acct']['lastname'] ? $GLOBALS['auto_create_acct']['lastname'] : 'User',
			'account_email'         => $GLOBALS['auto_create_acct']['email'],
			'account_status'        => 'A',
			'account_expires'       => $expires,
			'account_primary_group' => $primary_group,
		);
		// use given account_id, if it's not already used
		if (isset($GLOBALS['auto_create_acct']['account_id']) &&
			is_numeric($GLOBALS['auto_create_acct']['account_id']) &&
			!$this->id2name($GLOBALS['auto_create_acct']['account_id']))
		{
			$data['account_id'] = $GLOBALS['auto_create_acct']['account_id'];
		}
		if (!($data['account_id'] = $this->save($data)))
		{
			return false;
		}
		// set memberships if given
		if ($memberships)
		{
			$this->set_memberships($memberships,$data['account_id']);
		}
		// set the appropriate value for the can change password flag (assume users can, if the admin requires users to change their password)
		$data['changepassword'] = (bool)$GLOBALS['egw_info']['server']['change_pwd_every_x_days'];
		if(!$data['changepassword'])
		{
			$GLOBALS['egw']->acl->add_repository('preferences','nopasswordchange',$data['account_id'],1);
		}
		else
		{
			$GLOBALS['egw']->acl->delete_repository('preferences','nopasswordchange',$data['account_id']);
		}
		// call hook to notify interested apps about the new account
		$GLOBALS['hook_values'] = $data;
		$GLOBALS['egw']->hooks->process($data+array(
			'location' => 'addaccount',
			// at login-time only the hooks from the following apps will be called
			'order' => array('felamimail','fudforum'),
		),False,True);  // called for every app now, not only enabled ones
		unset($data['changepassword']);

		return $data['account_id'];
	}

	/**
	 * Update the last login timestamps and the IP
	 *
	 * @param int $account_id
	 * @param string $ip
	 * @return int lastlogin time
	 */
	function update_lastlogin($account_id, $ip)
	{
		return $this->backend->update_lastlogin($account_id, $ip);
	}

	/**
	 * Query if backend allows to change username aka account_lid
	 *
	 * @return boolean false if backend does NOT allow it (AD), true otherwise (SQL, LDAP)
	 */
	function change_account_lid_allowed()
	{
		$change_account_lid = constant(get_class($this->backend).'::CHANGE_ACCOUNT_LID');
		if (!isset($change_account_lid)) $change_account_lid = true;
		return $change_account_lid;
	}

	/**
	 * Query if backend requires password to be set, before allowing to enable an account
	 *
	 * @return boolean true if backend requires a password (AD), false or null otherwise (SQL, LDAP)
	 */
	function require_password_for_enable()
	{
		return constant(get_class($this->backend).'::REQUIRE_PASSWORD_FOR_ENABLE');
	}

	/**
	 * Invalidate cache (or parts of it) after change in $account_ids
	 *
	 * We use now an instance-wide read-cache storing account-data and members(hips).
	 *
	 * @param int|array $account_ids user- or group-id(s) for which cache should be invalidated, default 0 = only search/name2id cache
	 */
	static function cache_invalidate($account_ids=0)
	{
		//error_log(__METHOD__.'('.array2string($account_ids).')');

		// instance-wide cache
		if ($account_ids)
		{
			foreach((array)$account_ids as $account_id)
			{
				$instance = self::getInstance();

				egw_cache::unsetCache($instance->config['install_id'], __CLASS__, 'account-'.$account_id);

				unset(self::$request_cache[$account_id]);
			}
		}

		// session-cache
		if (self::$cache) self::$cache = array();
		egw_cache::unsetSession('accounts_cache','phpgwapi');

		if (method_exists($GLOBALS['egw'],'invalidate_session_cache'))	// egw object in setup is limited
		{
			egw::invalidate_session_cache();	// invalidates whole egw-enviroment if stored in the session
		}
	}

	/**
	 * Timeout of instance wide cache for reading account-data and members(hips)
	 */
	const READ_CACHE_TIMEOUT = 43200;

	/**
	 * Local per request cache, to minimize calls to instance cache
	 *
	 * @var array
	 */
	static $request_cache = array();

	/**
	 * Read account incl. members/memberships from cache (or backend and cache it)
	 *
	 * @param int $account_id
	 * @param boolean $need_active =false true = 'members-active' required
	 * @return array
	 * @throws egw_exception_wrong_parameter if no integer was passed as $account_id
	 */
	static function cache_read($account_id, $need_active=false)
	{
		if (!is_numeric($account_id)) throw new egw_exception_wrong_parameter('Not an integer!');

		$account =& self::$request_cache[$account_id];

		if (!isset($account))	// not in request cache --> try instance cache
		{
			$instance = self::getInstance();

			$account = egw_cache::getCache($instance->config['install_id'], __CLASS__, 'account-'.$account_id);

			if (!isset($account))	// not in instance cache --> read from backend
			{
				if (($account = $instance->backend->read($account_id)))
				{
					if ($instance->get_type($account_id) == 'u')
					{
						if (!isset($account['memberships'])) $account['memberships'] = $instance->backend->memberships($account_id);
					}
					else
					{
						if (!isset($account['members'])) $account['members'] = $instance->backend->members($account_id);
					}
					egw_cache::setCache($instance->config['install_id'], __CLASS__, 'account-'.$account_id, $account, self::READ_CACHE_TIMEOUT);
				}
				//error_log(__METHOD__."($account_id) read from backend ".array2string($account));
			}
			//else error_log(__METHOD__."($account_id) read from instance cache ".array2string($account));
		}
		// if required and not already set, query active members AND cache them too
		if ($need_active && $account_id < 0 && !isset($account['members-active']))
		{
			$instance = self::getInstance();
			$account['members-active'] = array();
			foreach((array)$account['members'] as $id => $lid)
			{
				if ($instance->is_active($id)) $account['members-active'][$id] = $lid;
			}
			egw_cache::setCache($instance->config['install_id'], __CLASS__, 'account-'.$account_id, $account, self::READ_CACHE_TIMEOUT);
		}
		//error_log(__METHOD__."($account_id, $need_active) returning ".array2string($account));
		return $account;
	}

	/**
	 * Internal functions not meant to use outside this class!!!
	 */

	/**
	 * Sets up session cache, now only used for search and name2id list
	 *
	 * Other account-data is cached on instance-level
	 *
	 * The cache is shared between all instances of the account-class and it can be save in the session,
	 * if use_session_cache is set to True
	 *
	 * @internal
	 */
	private static function setup_cache()
	{
		if (is_array(self::$cache)) return;	// cache is already setup

		if (self::$use_session_cache && is_object($GLOBALS['egw']->session))
		{
			self::$cache =& egw_cache::getSession('accounts_cache','phpgwapi');
			//echo "<p>restoring cache from session, ".count(call_user_func_array('array_merge',(array)self::$cache))." items</p>\n";
		}
		//error_log(__METHOD__."() use_session_cache=".array2string(self::$use_session_cache).", is_array(self::\$cache)=".array2string(is_array(self::$cache)));

		if (!is_array(self::$cache))
		{
			//echo "<p>initialising this->cache to array()</p>\n";
			self::$cache = array();
		}
	}

	/**
	 * @deprecated not used any more, as static cache is a reference to the session
	 */
	function save_session_cache()
	{

	}

	/**
	 * Depricated functions of the old accounts class.
	 *
	 * Do NOT use them in new code, they will be removed after the next major release!!!
	 */

	/**
	 * Reads the data of the account this class is instanciated for
	 *
	 * @deprecated use read of $GLOBALS['egw']->accounts and not own instances of the accounts class
	 * @return array with the internal data
	 */
	function read_repository()
	{
		return $this->data = $this->account_id ? $this->read($this->account_id,true) : array();
	}

	/**
	 * saves the account-data in the internal data-structure of this class to the repository
	 *
	 * @deprecated use save of $GLOBALS['egw']->accounts and not own instances of the accounts class
	 */
	function save_repository()
	{
		$this->save($this->data,true);
	}

	/**
	 * Searches / lists accounts: users and/or groups
	 *
	 * @deprecated use search
	 */
	function get_list($_type='both',$start = null,$sort = '', $order = '', $query = '', $offset = null,$query_type='')
	{
		if (is_array($_type))	// XML-RPC
		{
			return array_values($this->search($_type));
		}
		return array_values($this->search(array(
			'type'       => $_type,
			'start'      => $start,
			'order'      => $order,
			'sort'       => $sort,
			'query'      => $query,
			'offset'     => $offset,
			'query_type' => $query_type ,
		)));
	}

	/**
	 * Create a new account with the given $account_info
	 *
	 * @deprecated use save
	 * @param array $account_info account data for the new account
	 * @param booelan $default_prefs =true has no meaning any more, as we use "real" default prefs since 1.0
	 * @return int new nummeric account-id
	 */
	function create($account_info,$default_prefs=True)
	{
		unset($default_prefs);	// not used, but required by function signature
		return $this->save($account_info);
	}

	/**
	 * copies the given $data into the internal array $this->data
	 *
	 * @deprecated store data in your own code and use save to save it
	 * @param array $data array with account data
	 * @return array $this->data = $data
	 */
	function update_data($data)
	{
		return $this->data = $data;
	}

	/**
	 * Get all memberships of an account $accountid / groups the account is a member off
	 *
	 * @deprecated use memberships() which account_id => account_lid pairs
	 * @param int|string $_accountid ='' numeric account-id or alphanum. account-lid,
	 *	default account of the user of this session
	 * @return array or arrays with keys 'account_id' and 'account_name' for the groups $accountid is a member of
	 */
	function membership($_accountid = '')
	{
		$accountid = get_account_id($_accountid);

		if (!($memberships = $this->memberships($accountid)))
		{
			return $memberships;
		}
		$old = array();
		foreach($memberships as $id => $lid)
		{
			$old[] = array('account_id' => $id, 'account_name' => $lid);
		}
		//echo "<p>accounts::membership($accountid)="; _debug_array($old);
		return $old;
	}

	/**
	 * Get all members of the group $accountid
	 *
	 * @deprecated use members which returns acount_id => account_lid pairs
	 * @param int|string $accountid ='' numeric account-id or alphanum. account-lid,
	 *	default account of the user of this session
	 * @return array of arrays with keys 'account_id' and 'account_name'
	 */
	function member($accountid)
	{
		if (!($members = $this->members($accountid)))
		{
			return $members;
		}
		$old = array();
		foreach($members as $uid => $lid)
		{
			$old[] = array('account_id' => $uid, 'account_name' => $lid);
		}
		return $old;
	}

	/**
	 * phpGW compatibility function, better use split_accounts
	 *
	 * @deprecated  use split_accounts
	 */
	function return_members($accounts)
	{
		$arr = $this->split_accounts($accounts);

		return array(
			'users'  => $arr['accounts'],
			'groups' => $arr['groups'],
		);
	}


	/**
	 * Gets account-name (lid), firstname and lastname of an account $accountid
	 *
	 * @deprecated use read to read account data
	 * @param int|string $accountid ='' numeric account-id or alphanum. account-lid,
	 *	if !$accountid account of the user of this session
	 * @param string &$lid on return: alphanumeric account-name (lid)
	 * @param string &$fname on return: first name
	 * @param string &$lname on return: last name
	 * @return boolean true if $accountid was found, false otherwise
	 */
	function get_account_name($accountid,&$lid,&$fname,&$lname)
	{
		if (!($data = $this->read($accountid))) return false;

		$lid   = $data['account_lid'];
		$fname = $data['account_firstname'];
		$lname = $data['account_lastname'];

		if (empty($fname)) $fname = $lid;
		if (empty($lname)) $lname = $this->get_type($accountid) == 'g' ? lang('Group') : lang('user');

		return true;
	}

	/**
	 * Reads account-data for a given $account_id from the repository AND sets the class-vars with it
	 *
	 * Same effect as instanciating the class with that account, dont do it with $GLOBALS['egw']->account !!!
	 *
	 * @deprecated use read to read account data and store it in your own code
	 * @param int $account_id numeric account-id
	 * @return array with keys lid, firstname, lastname, fullname, type
	 */
	function get_account_data($account_id)
	{
		$this->account_id = $account_id;
		$this->read_repository();

		$data = array();
		$data[$this->data['account_id']]['lid']       = $this->data['account_lid'];
		$data[$this->data['account_id']]['firstname'] = $this->data['firstname'];
		$data[$this->data['account_id']]['lastname']  = $this->data['lastname'];
		$data[$this->data['account_id']]['fullname']  = $this->data['fullname'];
		$data[$this->data['account_id']]['type']      = $this->data['account_type'];

		return $data;
	}
}
