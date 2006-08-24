<?php
/**
 * API - accounts LDAP backend
 * 
 * The LDAP backend of the accounts class now stores accounts, groups and the memberships completly in LDAP. 
 * It does NO longer use the ACL class/table for group membership information.
 * Nor does it use the phpgwAcounts schema (part of that information is stored via shadowAccount now).
 * 
 * A user is recogniced by eGW, if he's in the user_context tree AND has the posixAccount object class.
 * A group is recogniced by eGW, if it's in the group_context tree AND has the posixGroup object class.
 * The group members are stored as memberuid's.
 * 
 * The (positive) group-id's (gidnumber) of LDAP groups are mapped in this class to negative numeric 
 * account_id's to not conflict with the user-id's, as both share in eGW internaly the same numberspace!
 * 
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> complete rewrite in 6/2006
 * 
 * This class replaces the former accounts_ldap class written by 
 * Joseph Engo <jengo@phpgroupware.org>, Lars Kneschke <lkneschke@phpgw.de>,
 * Miles Lott <milos@groupwhere.org> and Bettina Gille <ceb@phpgroupware.org>.
 * Copyright (C) 2000 - 2002 Joseph Engo, Lars Kneschke
 * Copyright (C) 2003 Lars Kneschke, Bettina Gille
 * 
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage accounts
 * @version $Id$
 */

/**
 * LDAP Backend for accounts
 * 
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage accounts
 * @access internal only use the interface provided by the accounts class
 */
class accounts_backend
{
	/**
	 * resource with connection to the ldap server
	 *
	 * @var resource
	 */
	var $ds;
	/**
	 * LDAP context for users, eg. ou=account,dc=domain,dc=com
	 *
	 * @var string
	 */
	var $user_context;
	/**
	 * LDAP context for groups, eg. ou=groups,dc=domain,dc=com
	 *
	 * @var string
	 */
	var $group_context;
	/**
	 * total number of found entries from get_list method
	 *
	 * @var int
	 */
	var $total;
	
	var $ldapServerInfo;

	/**
	 * required classe for user and groups
	 *
	 * @var array
	 */
	var $requiredObjectClasses = array(
		'user' => array(
			'top','person','organizationalperson','inetorgperson','posixaccount','shadowaccount'
		),
		'group' => array(
			'top','posixgroup','groupofnames'
		)
	);
	/**
	 * reference to the translation class
	 * 
	 * @var object
	 */
	var $translation;

	/**
	 * Constructor
	 *
	 * @return accounts_backend
	 */
	function accounts_backend()
	{
		// enable the caching in the session, done by the accounts class extending this class.
		$this->use_session_cache = true;

		$this->ds = $GLOBALS['egw']->common->ldapConnect();
		if(!@is_object($GLOBALS['egw']->translation))
		{
			$GLOBALS['egw']->translation =& CreateObject('phpgwapi.translation');
		}
		$this->translation =& $GLOBALS['egw']->translation;

		$this->user_context  = $GLOBALS['egw_info']['server']['ldap_context'];
		$this->group_context = $GLOBALS['egw_info']['server']['ldap_group_context'] ? 
			$GLOBALS['egw_info']['server']['ldap_group_context'] : $GLOBALS['egw_info']['server']['ldap_context'];
	}

	/**
	 * Reads the data of one account
	 *
	 * @param int $account_id numeric account-id
	 * @return array/boolean array with account data (keys: account_id, account_lid, ...) or false if account not found
	 */
	function read($account_id)
	{
		if (!(int)$account_id) return false;
		
		if ($account_id < 0)
		{
			return $this->_read_group($account_id);
		}
		return $this->_read_user($account_id);
	}

	/**
	 * Saves / adds the data of one account
	 * 
	 * If no account_id is set in data the account is added and the new id is set in $data.
	 *
	 * @param array $data array with account-data
	 * @return int/boolean the account_id or false on error
	 */
	function save(&$data)
	{
		$is_group = $data['account_id'] < 0 || $data['account_type'] === 'g';

		$data_utf8 = $this->translation->convert($data,$this->translation->charset(),'utf-8');
		$members = $data['account_members'];
				
		if (!is_object($this->ldapServerInfo))
		{
			$this->ldapServerInfo = $GLOBALS['egw']->ldap->getLDAPServerInfo($GLOBALS['egw_info']['server']['ldap_host']);
		}
		// common code for users and groups
		// checks if accout_lid (dn) has been changed or required objectclass'es are missing
		if ($data_utf8['account_id'] && $data_utf8['account_lid'])
		{
			// read the entry first, to check if the dn (account_lid) has changed
			$sri = $is_group ? ldap_search($this->ds,$this->group_context,'gidnumber='.abs($data['account_id'])) :
				ldap_search($this->ds,$this->user_context,'uidnumber='.$data['account_id']);
			$old = ldap_get_entries($this->ds, $sri);

			if (!$old['count'])
			{
				unset($old);
			}
			else
			{
				$old = $this->_ldap2array($old[0]);
				foreach($old['objectclass'] as $n => $class)
				{
					$old['objectclass'][$n] = strtolower($class);
				}
				$key = false;
				if ($is_group && ($key = array_search('namedobject',$old['objectclass'])) !== false ||
					$is_group && ($old['cn'] != $data_utf8['account_lid'] || substr($old['dn'],0,3) != 'cn=') ||
					!$is_group && ($old['uid'] != $data_utf8['account_lid'] || substr($old['dn'],0,4) != 'uid='))
				{
					// query the memberships to set them again later
					if (!$is_group)
					{
						$memberships = $this->memberships($data['account_id']);
					}
					else
					{
						$members = $old ? $old['memberuid'] : $this->members($data['account_id']);
					}
					// if dn has changed --> delete the old entry, as we cant rename the dn
					// $this->delete would call accounts::delete, which will delete als ACL of the user too!
					accounts_backend::delete($data['account_id']);	
					unset($old['dn']);
					// removing the namedObject object-class, if it's included
					if ($key !== false) unset($old['objectclass'][$key]);
					$to_write = $old;
					unset($old);
				}
			}
		}
		if (!$data['account_id'])	// new account
		{
			if (!($data['account_id'] = $data_utf8['account_id'] = $this->_get_nextid($is_group ? 'g' : 'u')))
			{
				return false;
			}
		}
		// check if we need to write the objectclass: new entry or required object classes are missing
		if (!$old || array_diff($this->requiredObjectClasses[$is_group ? 'group' : 'user'],$old['objectclass']))
		{
			// additional objectclasse might be already set in $to_write or $old
			if (!is_array($to_write['objectclass']))
			{
				$to_write['objectclass'] = $old ? $old['objectclass'] : array();
			}
			$to_write['objectclass'] = array_values(array_unique(array_merge($to_write['objectclass'],
				$this->requiredObjectClasses[$is_group ? 'group' : 'user'])));
		}
		if (!($dn = $old['dn']))
		{
			if (!$data['account_lid']) return false;

			$dn = $is_group ? 'cn='.$data_utf8['account_lid'].','.$this->group_context :
				'uid='.$data_utf8['account_lid'].','.$this->user_context;
		}
		// now we merge the user or group data
		if ($is_group)
		{
			$to_write = $this->_merge_group($to_write,$data_utf8);
			$data['account_type'] = 'g';
			
			$groupOfNames = in_array('groupofnames',$old ? $old['objectclass'] : $to_write['objectclass']);
			if (!$old && $groupOfNames || $members)
			{
				$to_write = array_merge($to_write,accounts_backend::set_members($members,
					$data['account_id'],$groupOfNames,$dn));
			}
		}
		else
		{
			$to_write = $this->_merge_user($to_write,$data_utf8,!$old);
			$data['account_type'] = 'u';
		}
		//echo "<p>ldap_".($old ? 'modify' : 'add')."(,$dn,".print_r($to_write,true).")</p>\n";
		// modifying or adding the entry
		if ($old && !@ldap_modify($this->ds,$dn,$to_write) ||
			!$old && !@ldap_add($this->ds,$dn,$to_write))
		{
			$err = true;
			if ($is_group && ($key = array_search('groupofnames',$to_write['objectclass'])) !== false)
			{
				// try again with removed groupOfNames stuff, as I cant detect if posixGroup is a structural object
				unset($to_write['objectclass'][$key]);
				unset($to_write['member']);
				$err = $old ? !ldap_modify($this->ds,$dn,$to_write) : !ldap_add($this->ds,$dn,$to_write);
			}
			if ($err)
			{
				echo "ldap_".($old ? 'modify' : 'add')."(,$dn,".print_r($to_write,true).")\n";
				echo ldap_error($this->ds);
				return false;
			}
		}
		if ($memberships)
		{
			$this->set_memberships($memberships,$data['account_id']);
		}
		return $data['account_id'];
	}

	/**
	 * Convert a single ldap value into a associative array
	 *
	 * @param array $ldap array with numerical and associative indexes and count's
	 * @return array with only associative index and no count's
	 */
	function _ldap2array($ldap)
	{
		if (!is_array($ldap)) return false;

		$arr = array();
		foreach($ldap as $var => $val)
		{
			if (is_int($var) || $var == 'count') continue;
			
			if (is_array($val) && $val['count'] == 1)
			{
				$arr[$var] = $val[0];
			}
			else
			{
				if (is_array($val)) unset($val['count']);

				$arr[$var] = $val;
			}
		}
		return $arr;
	}

	
	/**
	 * Delete one account, deletes also all acl-entries for that account
	 *
	 * @param int $id numeric account_id
	 * @return boolean true on success, false otherwise
	 */
	function delete($account_id)
	{
		if (!(int)$account_id) return false;

		if ($account_id < 0)
		{
			$sri = ldap_search($this->ds, $this->group_context, 'gidnumber=' . abs($account_id));
		}
		else
		{
			// remove the user's memberships
			$this->set_memberships(array(),$account_id);

			$sri = ldap_search($this->ds, $this->user_context, 'uidnumber=' . $account_id);
		}
		if (!$sri) return false;
		
		$allValues = ldap_get_entries($this->ds, $sri);
		if (!$allValues['count']) return false;

		return ldap_delete($this->ds, $allValues[0]['dn']);
	}

	/**
	 * Reads the data of one group
	 *
	 * @internal 
	 * @param int $account_id numeric account-id (< 0 as it's for a group)
	 * @return array/boolean array with account data (keys: account_id, account_lid, ...) or false if account not found
	 */
	function _read_group($account_id)
	{
		$sri = ldap_search($this->ds, $this->group_context, 'gidnumber=' . abs($account_id),
			array('dn','gidnumber','cn','objectclass'));
		
		$data = ldap_get_entries($this->ds, $sri);
		if (!$data['count'])
		{
			return false;	// group not found
		}
		$data = $this->translation->convert($data[0],'utf-8');
		
		$group = array(
			'account_dn'        => $data['dn'],
			'account_id'        => -$data['gidnumber'][0],
			'account_lid'       => $data['cn'][0],
			'account_type'      => 'g',
			'account_firstname' => $data['cn'][0],
			'account_lastname'  => lang('Group'),
			'groupOfNames'      => in_array('groupOfNames',$data['objectclass']),
		);
		if (!is_object($this->ldapServerInfo))
		{
			$this->ldapServerInfo = $GLOBALS['egw']->ldap->getLDAPServerInfo($GLOBALS['egw_info']['server']['ldap_host']);
		}
		return $group;
	}

	/**
	 * Reads the data of one user
	 *
	 * @internal 
	 * @param int $account_id numeric account-id
	 * @return array/boolean array with account data (keys: account_id, account_lid, ...) or false if account not found
	 */
	function _read_user($account_id)
	{
		$sri = ldap_search($this->ds, $this->user_context, 'uidnumber=' . (int)$account_id,
			array('dn','uidnumber','uid','gidnumber','givenname','sn','cn','mail','userpassword',
			'shadowexpire','shadowlastchange','homedirectory','loginshell'));
		
		$data = ldap_get_entries($this->ds, $sri);
		if (!$data['count'])
		{
			return false;	// user not found
		}
		$data = $this->translation->convert($data[0],'utf-8');
		
		$utc_diff = date('Z');
		$user = array(
			'account_dn'        => $data['dn'],
			'account_id'        => (int)$data['uidnumber'][0],
			'account_lid'       => $data['uid'][0],
			'account_type'      => 'u',
			'account_primary_group' => -$data['gidnumber'][0],
			'account_firstname' => $data['givenname'][0],
			'account_lastname'  => $data['sn'][0],
			'account_email'     => $data['mail'][0],
			'account_fullname'  => $data['cn'][0],
			'account_pwd'       => $data['userpassword'][0],
			// both status and expires are encoded in the single shadowexpire value in LDAP
			// - if it's unset an account is enabled AND does never expire
			// - if it's set to 0, the account is disabled
			// - if it's set to > 0, it will or already has expired --> acount is active if it not yet expired
			// shadowexpire is in days since 1970/01/01 (equivalent to a timestamp (int UTC!) / (24*60*60)
			'account_status'    => isset($data['shadowexpire']) && $data['shadowexpire'][0]*24*3600+$utc_diff < time() ? false : 'A',
			'account_expires'   => isset($data['shadowexpire']) && $data['shadowexpire'][0] ? $data['shadowexpire'][0]*24*3600+$utc_diff : -1, // LDAP date is in UTC
			'account_lastpasswd_change' => isset($data['shadowlastchange']) ? $data['shadowlastchange'][0]*24*3600 : null,
			// lastlogin and lastlogin from are not availible via the shadowAccount object class
			// 'account_lastlogin' => $data['phpgwaccountlastlogin'][0],
			// 'account_lastloginfrom' => $data['phpgwaccountlastloginfrom'][0],
			'person_id'         => $data['uid'][0],	// id of associated contact
		);
		//echo "<p align=right>accounts_ldap::_read_user($account_id): shadowexpire={$data['shadowexpire'][0]} --> account_expires=$user[account_expires]=".date('Y-m-d H:i',$user['account_expires'])."</p>\n";
		if ($GLOBALS['egw_info']['server']['ldap_extra_attributes'])
		{
			$user['homedirectory']  = $data['homedirectory'][0];
			$user['loginshell']     = $data['loginshell'][0];
		}
		return $user;
	}

	/**
	 * Merges the group releavant account data from $data into $to_write
	 *
	 * @internal 
	 * @param array $to_write data to write to ldap incl. objectclass ($data is NOT yet merged)
	 * @param array $data array with account-data in utf-8
	 * @return array merged data
	 */
	function _merge_group($to_write,$data)
	{
		$to_write['gidnumber'] = abs($data['account_id']);
		$to_write['cn'] = $data['account_lid'];
		
		return $to_write;
	}
	
	/**
	 * Merges the user releavant account data from $data into $to_write
	 *
	 * @internal 
	 * @param array $to_write data to write to ldap incl. objectclass ($data is NOT yet merged)
	 * @param array $data array with account-data in utf-8
	 * @param boolean $new_entry
	 * @return array merged data
	 */
	function _merge_user($to_write,$data,$new_entry)
	{
		//echo "<p>accounts_ldap::_merge_user(".print_r($to_write,true).','.print_r($data,true).",$new_entry)</p>\n";

		$to_write['uidnumber'] = $data['account_id'];
		$to_write['uid']       = $data['account_lid'];
		$to_write['gidnumber'] = abs($data['account_primary_group']);
		if (!$new_entry || $data['account_firstname'])
		{
			$to_write['givenname'] = $data['account_firstname'] ? $data['account_firstname'] : array();
		}
		$to_write['sn']        = $data['account_lastname'];
		if (!$new_entry || $data['account_email'])
		{
			$to_write['mail']  = $data['account_email'] ? $data['account_email'] : array();
		}
		$to_write['cn']        = $data['account_fullname'] ? $data['account_fullname'] : $data['account_firstname'].' '.$data['account_lastname'];
		
		if (isset($data['account_passwd']) && $data['account_passwd'])
		{
			if(!@is_object($GLOBALS['egw']->auth))
			{
				$GLOBALS['egw']->auth =& CreateObject('phpgwapi.auth');
			}
			if (!preg_match('/^\\{[a-z5]{3,5}\\}.+/i',$data['account_passwd']))	// if it's not already entcrypted, do so now
			{
				$data['account_passwd'] = $GLOBALS['egw']->auth->encrypt_ldap($data['account_passwd']);
			}
			$to_write['userpassword'] = $data['account_passwd'];
		}
		// both status and expires are encoded in the single shadowexpire value in LDAP
		// - if it's unset an account is enabled AND does never expire
		// - if it's set to 0, the account is disabled
		// - if it's set to > 0, it will or already has expired --> acount is active if it not yet expired
		// shadowexpire is in days since 1970/01/01 (equivalent to a timestamp (int UTC!) / (24*60*60)
		$utc_diff = date('Z');
		$shadowexpire = ($data['account_expires']-$utc_diff) / (24*3600);
		$account_expire = $shadowexpire*3600*24+$utc_diff;
		//echo "<p align=right>account_expires=".date('Y-m-d H:i',$data['account_expires'])." --> $shadowexpire --> ".date('Y-m-d H:i',$account_expire)."</p>\n";
		$to_write['shadowexpire'] = !$data['account_status'] ? 	
			($data['account_expires'] != -1 && $data['account_expires'] < time() ? round($shadowexpire) : 0) :
			($data['account_expires'] != -1 ? round($shadowexpire) : array());	// array() = unset value
		
		if ($new_entry && is_array($to_write['shadowexpire']) && !count($to_write['shadowexpire']))
		{
			unset($to_write['shadowexpire']);	// gives protocoll error otherwise
		}
		
		if ($data['account_lastpasswd_change']) $to_write['shadowlastchange'] = $data['lastpasswd_change']/(24*3600);

		// lastlogin and lastlogin from are not availible via the shadowAccount object class
		// $to_write['phpgwaccountlastlogin'] = $data['lastlogin'];
		// $to_write['phpgwaccountlastloginfrom'] = $data['lastloginfrom'];

		if ($GLOBALS['egw_info']['server']['ldap_extra_attributes'])
		{
			if (isset($data['homedirectory'])) $to_write['homedirectory']  = $data['homedirectory'];
			if (isset($data['loginshell'])) $to_write['loginshell'] = $data['loginshell'] ? $data['loginshell'] : array();
		}
		if ($new_entry && !isset($to_write['homedirectory']))
		{
			$to_write['homedirectory']  = '/dev/null';	// is a required attribute of posixAccount
		}
		return $to_write;
	}

	/**
	 * Searches users and/or groups
	 * 
	 * ToDo: implement a search like accounts::search
	 *
	 * @param string $_type
	 * @param int $start=null
	 * @param string $sort=''
	 * @param string $order=''
	 * @param string $query
	 * @param int $offset=null
	 * @param string $query_type
	 * @return array
	 */
	function get_list($_type='both', $start = null,$sort = '', $order = '', $query = '', $offset = null, $query_type='')
	{
		//print "\$_type=$_type, \$start=$start , \$sort=$sort, \$order=$order, \$query=$query, \$offset=$offset, \$query_type=$query_type<br>";
		$query = ldap::quote(strtolower($query));

		if($_type != 'groups')
		{
			$filter = "(&(uidnumber=*)(objectclass=posixaccount)";
			if (!empty($query) && $query != '*')
			{
				switch($query_type)
				{
					case 'all':
					default:
						$query = '*'.$query;
						// fall-through
					case 'start':
						$query .= '*';
						// fall-through
					case 'exact':
						$filter .= "(|(uid=$query)(sn=$query)(cn=$query)(givenname=$query)(mail=$query))";
						break;
					case 'firstname':
					case 'lastname':
					case 'lid':
					case 'email':
						$to_ldap = array(
							'firstname' => 'givenname',
							'lastname'  => 'sn',
							'lid'       => 'uid',
							'email'     => 'mail',
						);
						$filter .= '('.$to_ldap[$query_type].'=*'.$query.'*)';
						break;
				}
			}
			$filter .= ')';

			$sri = ldap_search($this->ds, $this->user_context, $filter);
			$allValues = ldap_get_entries($this->ds, $sri);
			$utc_diff = date('Z');
			while (list($null,$allVals) = @each($allValues))
			{
				settype($allVals,'array');
				$test = @$allVals['uid'][0];
				if (!$GLOBALS['egw_info']['server']['global_denied_users'][$test] && $allVals['uid'][0])
				{
					$accounts[] = Array(
						'account_id'        => $allVals['uidnumber'][0],
						'account_lid'       => $GLOBALS['egw']->translation->convert($allVals['uid'][0],'utf-8'),
						'account_type'      => 'u',
						'account_firstname' => $GLOBALS['egw']->translation->convert($allVals['givenname'][0],'utf-8'),
						'account_lastname'  => $GLOBALS['egw']->translation->convert($allVals['sn'][0],'utf-8'),
						'account_status'    => isset($allVals['shadowexpire'][0]) && $allVals['shadowexpire'][0]*24*3600-$utc_diff < time() ? false : 'A',
						'account_email'     => $allVals['mail'][0],
					);
				}
			}
		}
		if ($_type != 'accounts')
		{
			if(empty($query) || $query == '*')
			{
				$filter = '(&(gidnumber=*)(objectclass=posixgroup))';
			}
			else
			{
				$filter = "(&(gidnumber=*)(objectclass=posixgroup)(|(cn=*$query*)))";
			}
			$sri = ldap_search($this->ds, $this->group_context, $filter);
			$allValues = ldap_get_entries($this->ds, $sri);
			while (list($null,$allVals) = @each($allValues))
			{
				settype($allVals,'array');
				$test = $allVals['cn'][0];
				if (!$GLOBALS['egw_info']['server']['global_denied_groups'][$test] && $allVals['cn'][0])
				{
					$accounts[] = Array(
						'account_id'        => -$allVals['gidnumber'][0],
						'account_lid'       => $GLOBALS['egw']->translation->convert($allVals['cn'][0],'utf-8'),
						'account_type'      => 'g',
						'account_firstname' => $GLOBALS['egw']->translation->convert($allVals['cn'][0],'utf-8'),
						'account_lastname'  => lang('Group'),
						'account_status'    => 'A',
					);
				}
			}
		}
		// sort the array
		$arrayFunctions =& CreateObject('phpgwapi.arrayfunctions');
		if(empty($order))
		{
			$order = 'account_lid';
		}
		$sortedAccounts = $arrayFunctions->arfsort($accounts,explode(',',$order),$sort);
		$this->total = count($accounts);
		// return only the wanted accounts
		if (is_array($sortedAccounts))
		{
			reset($sortedAccounts);
			if(is_numeric($start) && is_numeric($offset))
			{
				return array_slice($sortedAccounts, $start, $offset);
			}
			elseif(is_numeric($start))
			{
				if (!($maxmatchs = $GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'])) $maxmatchs = 15;

				return array_slice($sortedAccounts, $start, $maxmatchs);
			}
			else
			{
				return $sortedAccounts;
			}
		}
		return False;
	}

	/**
	 * convert an alphanumeric account-value (account_lid, account_email) to the account_id
	 *
	 * Please note:
	 * - if a group and an user have the same account_lid the group will be returned (LDAP only)
	 * - if multiple user have the same email address, the returned user is undefined
	 * 
	 * @param string $name value to convert
	 * @param string $which='account_lid' type of $name: account_lid (default), account_email, person_id, account_fullname
	 * @param string $account_type u = user, g = group, default null = try both
	 * @return int/false numeric account_id or false on error ($name not found)
	 */
	function name2id($name,$which='account_lid',$account_type=null)
	{
		$name = ldap::quote($this->translation->convert($name,$this->translation->charset(),'utf-8'));

		if ($which == 'account_lid' && $account_type !== 'u') // groups only support account_lid
		{

			$sri = ldap_search($this->ds, $this->group_context, '(&(cn=' . $name . ')(objectclass=posixgroup))');
			$allValues = ldap_get_entries($this->ds, $sri);

			if (@$allValues[0]['gidnumber'][0])
			{
				return -$allValues[0]['gidnumber'][0];
			}
		}
		$to_ldap = array(
			'account_lid'   => 'uid',
			'account_email' => 'mail',
			'account_fullname' => 'cn',
		);
		if (!isset($to_ldap[$which]) || $account_type === 'g') return False;

		$sri = ldap_search($this->ds, $this->user_context, '(&('.$to_ldap[$which].'=' . $name . ')(objectclass=posixaccount))');

		$allValues = ldap_get_entries($this->ds, $sri);

		if (@$allValues[0]['uidnumber'][0])
		{
			return (int)$allValues[0]['uidnumber'][0];
		}
		return False;
	}

	/**
	 * Update the last login timestamps and the IP
	 *
	 * @param int $account_id
	 * @param string $ip
	 * @return int lastlogin time
	 */
	function update_lastlogin($_account_id, $ip)
	{
		return false;	// not longer supported

		$entry['phpgwaccountlastlogin']     = time();
		$entry['phpgwaccountlastloginfrom'] = $ip;

		$sri = ldap_search($this->ds, $GLOBALS['egw_info']['server']['ldap_context'], 'uidnumber=' . (int)$_account_id);
		$allValues = ldap_get_entries($this->ds, $sri);

		$dn = $allValues[0]['dn'];
		@ldap_modify($this->ds, $dn, $entry);

		return $allValues[0]['phpgwaccountlastlogin'][0];
	}
	
	/**
	 * Query memberships of a given account
	 *
	 * @param int $account_id
	 * @return array/boolean array with account_id => account_lid pairs or false if account not found
	 */
	function memberships($account_id)
	{
		if (!(int) $account_id || !($account_lid = $this->id2name($account_id))) return false;
		
		$sri = ldap_search($this->ds,$this->group_context,'(&(objectClass=posixGroup)(memberuid='.ldap::quote($account_lid).'))',array('cn','gidnumber'));
		$memberships = array();
		foreach(ldap_get_entries($this->ds, $sri) as $key => $data)
		{
			if ($key === 'count') continue;
			
			$memberships[(string) -$data['gidnumber'][0]] = $data['cn'][0];
		}
		//echo "accounts::memberships($account_id)"; _debug_array($memberships);
		return $memberships;
	}
	
	/**
	 * Query the members of a group
	 *
	 * @param int $gid
	 * @return array with uidnumber => uid pairs
	 */
	function members($gid)
	{
		if (!is_numeric($gid)) return false;
		
		$gid = abs($gid);	// our gid is negative!
		
		$sri = ldap_search($this->ds,$this->group_context,"(&(objectClass=posixGroup)(gidnumber=$gid))",array('memberuid'));
		$group = ldap_get_entries($this->ds, $sri);
		
		$members = array();
		if (isset($group[0]['memberuid']))
		{
			foreach($group[0]['memberuid'] as $lid)
			{
				if (($id = $this->name2id($lid)))
				{
					$members[$id] = $lid;
				}
			}
		}
		//echo "accounts_ldap::members($gid)"; _debug_array($members);
		return $members;
	}
	
	/**
	 * Sets the memberships of the given account
	 *
	 * @param array $groups array with gidnumbers
	 * @param int $account_id uidnumber
	 */
	function set_memberships($groups,$account_id)
	{
		//echo "<p>accounts_ldap::set_memberships(".print_r($groups,true).",$account_id)</p>\n";

		// remove not longer existing memberships
		if (($old_memberships = $this->memberships($account_id)))
		{
			$old_memberships = array_keys($old_memberships);
			foreach(array_diff($old_memberships,$groups) as $gid)
			{
				if (($members = $this->members($gid)))
				{
					unset($members[$account_id]);
					$this->set_members($members,$gid);
				}
			}
		}
		// adding new memberships
		foreach($old_memberships ? array_diff($groups,$old_memberships) : $groups as $gid)
		{
			$members = $this->members($gid);
			$members[$account_id] = $this->id2name($account_id);
			$this->set_members($members,$gid);
		}
	}
	
	/**
	 * Set the members of a group
	 * 
	 * @param array $members array with uidnumber or uid's
	 * @param int $gid gidnumber of group to set
	 * @param boolean $groupOfNames=null should we set the member attribute of groupOfNames (default detect it)
	 * @param string $use_cn=null if set $cn is used instead $gid and the attributes are returned, not written to ldap
	 * @return boolean/array false on failure, array or true otherwise
	 */
	function set_members($members,$gid,$groupOfNames=null,$use_cn=null)
	{
		//echo "<p>accounts_ldap::set_members(".print_r($members,true).",$gid)</p>\n";
		if (!($cn = $use_cn) && !($cn = $this->id2name($gid))) return false;

		// do that group is a groupOfNames?
		if (is_null($groupOfNames)) $groupOfNames = $this->id2name($gid,'groupOfNames');
		
		$to_write = array();
		foreach((array)$members as $key => $member)
		{
			if (is_numeric($member)) $member = $this->id2name($member);

			if ($member)
			{
				$to_write['memberuid'][] = $member;
				if ($groupOfNames) $to_write['member'][] = 'uid='.$member.','.$this->user_context;
			}
		}
		if ($groupOfNames && !$to_write['member'])
		{
			// hack as groupOfNames requires the member attribute
			$to_write['member'][] = 'uid=dummy'.','.$this->user_context;
		}
		if ($use_cn) return $to_write;

		if (!ldap_modify($this->ds,'cn='.ldap::quote($cn).','.$this->group_context,$to_write))
		{
			echo "ldap_modify(,'cn=$cn,$this->group_context',".print_r($to_write,true)."))\n";
			return false;
		}
		return true;
	}

	/**
	 * Using the common functions next_id and last_id, find the next available account_id
	 *
	 * @internal 
	 * @param $string $account_type='u' (optional, default to 'u')
	 * @return int/boolean integer account_id (negative for groups) or false if none is free anymore
	 */
	function _get_nextid($account_type='u')
	{
		$min = $GLOBALS['egw_info']['server']['account_min_id'] ? $GLOBALS['egw_info']['server']['account_min_id'] : 0;
		$max = $GLOBALS['egw_info']['server']['account_max_id'] ? $GLOBALS['egw_info']['server']['account_max_id'] : 0;

		if ($account_type == 'g')
		{
			$type = 'groups';
			$sign = -1;
		}
		else
		{
			$type = 'accounts';
			$sign = 1;
		}
		/* Loop until we find a free id */
		do
		{
			$account_id = (int) $GLOBALS['egw']->common->next_id($type,$min,$max);
		} 
		while ($account_id && $this->exists($sign * $account_id));	// check need to include the sign!

		if	(!$account_id || $GLOBALS['egw_info']['server']['account_max_id'] &&
			$account_id > $GLOBALS['egw_info']['server']['account_max_id'])
		{
			return False;
		}
		return $sign * $account_id;
	}
}
