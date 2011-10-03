<?php
/**
 * EGroupware: GroupDAV access: groupdav/caldav/carddav principals handlers
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage groupdav
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2008-11 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * EGroupware: GroupDAV access: groupdav/caldav/carddav principals handlers
 *
 * First-level properties used in this class should have the property name as their key,
 * to allow to check if required properties are set!
 * groupdav_principals::add_principal() converts simple associative props (name => value pairs)
 * to name => HTTP_WebDAV_Server(name, value) pairs.
 *
 * @todo All principal urls should either contain no account_lid (eg. base64 of it) or use urlencode($account_lid)
 */
class groupdav_principals extends groupdav_handler
{
	/**
	 * Constructor
	 *
	 * @param string $app 'calendar', 'addressbook' or 'infolog'
	 * @param groupdav $groupdav calling class
	 */
	function __construct($app, groupdav $groupdav)
	{
		parent::__construct($app, $groupdav);
	}

	/**
	 * Supported reports and methods implementing them or what to return eg. "501 Not Implemented"
	 *
	 * @var array
	 */
	public $supported_reports = array(
		'acl-principal-prop-set' => array(
			// not sure why we return that report, if we not implement it ...
		),
		/*'principal-match' => array(
			// an other report calendarserver announces
		),*/
		'principal-property-search' => array(
			'method' => 'principal_property_search_report',
		),
		'principal-search-property-set' => array(
			'method' => 'principal_search_property_set_report',
		),
		/*'expand-property' => array(
			// an other report calendarserver announces
		),*/
		'addressbook-findshared' => array(
			'ns' => groupdav::ADDRESSBOOKSERVER,
			'method' => 'addressbook_findshared_report',
		),
	);

	/**
	 * Generate supported-report-set property
	 *
	 * Currently we return all reports independed of path
	 *
	 * @param string $path eg. '/principals/'
	 * @param array $reports=null
	 * @return array HTTP_WebDAV_Server::mkprop('supported-report-set', ...)
	 */
	protected function supported_report_set($path, array $reports=null)
	{
		if (is_null($reports)) $reports = $this->supported_reports;

		$supported = array();
		foreach($reports as $name => $data)
		{
			$supported[$name] = HTTP_WebDAV_Server::mkprop('supported-report',array(
				HTTP_WebDAV_Server::mkprop('report',array(
					!$data['ns'] ? HTTP_WebDAV_Server::mkprop($name, '') :
						HTTP_WebDAV_Server::mkprop($data['ns'], $name, '')))));
		}
		return $supported;
	}

	/**
	 * Handle propfind request for an application folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function propfind($path,$options,&$files,$user)
	{
		if (($report = isset($_GET['report']) ? $_GET['report'] : $options['root']['name']) && $report != 'propfind')
		{
			$report_data = $this->supported_reports[$report];
			if (isset($report_data) && ($method = $report_data['method']) && method_exists($this, $method))
			{
				return $this->$method($path, $options, $files, $user);
			}
			error_log(__METHOD__."('$path', ".array2string($options).",, $user) not implemented report, returning 501 Not Implemented");
			return '501 Not Implemented';
		}
		list(,$principals,$type,$name,$rest) = explode('/',$path,5);
		// /principals/users/$name/
		//            /users/$name/calendar-proxy-read/
		//            /users/$name/calendar-proxy-write/
		//            /groups/$name/
		//            /resources/$resource/
		//            /__uids__/$uid/.../

		switch($type)
		{
			case 'users':
				$files['files'] = $this->propfind_users($name,$rest,$options);
				break;
			case 'groups':
				$files['files'] = $this->propfind_groups($name,$rest,$options);
				break;
			/*case 'resources':
				$files['files'] = $this->propfind_resources($name,$rest,$options);
				break;
			case '__uids__':
				$files['files'] = $this->propfind_uids($name,$rest,$options);
				break;*/
			case '':
				$files['files'] = $this->propfind_principals($options);
				break;
			default:
				return '404 Not Found';
		}
		if (!is_array($files['files']))
		{
			return $files['files'];
		}
		return true;
	}

	/**
	 * Handle addressbook-findshared Addressbookserver report
	 *
	 * Required for Apple Addressbook on Mac (addressbook-findshared REPORT)
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function addressbook_findshared_report($path,$options,&$files,$user)
	{
		error_log(__METHOD__."('$path', ".array2string($options).",, $user)");
		$files['files'] = array();
		$files['files'][] = $this->add_collection($path);	// will be removed for reports
		foreach($this->get_shared_addressbooks() as $path)
		{
			$files['files'][] = $f = $this->add_collection($path.'addressbook/', array(
				'resourcetype' => array(HTTP_WebDAV_Server::mkprop(groupdav::CARDDAV,'addressbook','')),
			));
			error_log(__METHOD__."() ".array2string($f));
		}
		return true;
	}

	/**
	 * Handle principal-property-search report
	 *
	 * Current implementation runs a full infinity propfind and filters out not matching resources.
	 *
	 * Eg. from Lightning on the principal collection /principals/:
	 * <D:principal-property-search xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
	 *   <D:property-search>
	 *     <D:prop>
	 *       <C:calendar-home-set/>
	 *     </D:prop>
	 *     <D:match>/egroupware/groupdav.php</D:match>
	 *   </D:property-search>
	 *   <D:prop>
	 *     <C:calendar-home-set/>
	 *     <C:calendar-user-address-set/>
	 *     <C:schedule-inbox-URL/>
	 *     <C:schedule-outbox-URL/>
	 *   </D:prop>
	 * </D:principal-property-search>
	 *
	 * Hack for Lightning: it requests calendar-home-set matching our root (/egroupware/groupdav.php),
	 * but interprets returning all principals (all have a matching calendar-home-set) as NOT supporting CalDAV scheduling
	 * --> search only current user's principal, when Lightning searches for calendar-home-set
	 *
	 * Example from iOS iCal autocompleting invitees using calendarserver-principal-property-search WebDAV extension
	 * <x0:principal-property-search xmlns:x2="urn:ietf:params:xml:ns:caldav" xmlns:x1="http://calendarserver.org/ns/" xmlns:x0="DAV:" test="anyof">
	 *   <x0:property-search>
	 *     <x0:prop>
	 *       <x0:displayname/>
	 *     </x0:prop>
	 *     <x0:match match-type="contains">beck</x0:match>
	 *   </x0:property-search>
	 *   <x0:property-search>
	 *     <x0:prop>
	 *       <x1:email-address-set/>
	 *     </x0:prop>
	 *     <x0:match match-type="starts-with">beck</x0:match>
	 *   </x0:property-search>
	 *   <x0:property-search>
	 *     <x0:prop>
	 *       <x1:first-name/>
	 *     </x0:prop>
	 *     <x0:match match-type="starts-with">beck</x0:match>
	 *   </x0:property-search>
	 *   <x0:property-search>
	 *     <x0:prop>
	 *       <x1:last-name/>
	 *     </x0:prop>
	 *     <x0:match match-type="starts-with">beck</x0:match>
	 *   </x0:property-search>
	 *   <x0:prop>
	 *     <x1:first-name/>
	 *     <x1:last-name/>
	 *     <x0:displayname/>
	 *     <x1:email-address-set/>
	 *     <x2:calendar-user-address-set/>
	 *     <x1:record-type/>
	 *     <x0:principal-URL/>
	 *   </x0:prop>
	 * </x0:principal-property-search>
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function principal_property_search_report($path,&$options,&$files,$user)
	{
		//error_log(__METHOD__."('$path', ".array2string($options).",, $user)");

		// cant find the test attribute to root principal-property-search element in WebDAV rfc, but iPhones use it ...
		$anyof = !empty($options['root']['attrs']['test']) && $options['root']['attrs']['test'] == 'anyof';	// "allof" (default) or "anyof"

		// parse property-search prop(s) contained in $options['other']
		foreach($options['other'] as $n => $prop)
		{
			switch($prop['name'])
			{
				case 'apply-to-principal-collection-set':	// optinal prop to apply search on principal-collection-set == '/principals/'
					$path = '/principals/';
					break;
				case 'property-search':
					$property_search = $n;	// should be 1
					break;
				case 'prop':
					if (isset($property_search))
					{
						$search_props[$property_search] = array();
					}
					break;
				case 'match':
					if (isset($property_search) && is_array($search_props[$property_search]))
					{
						$search_props[$property_search]['match'] = $prop['data'];
						// optional match-type: "contains" (default), "starts-with", "ends-with", "equals"
						$search_props[$property_search]['match-type'] = $prop['attrs']['match-type'];
					}
					break;
				default:
					if (isset($property_search) && $search_props[$property_search] === array())
					{
						$search_props[$property_search] = $prop;
					}
					break;
			}
		}
		if (!isset($property_search) || !$search_props || !isset($search_props[$property_search]['match']))
		{
			error_log(__METHOD__."('$path',...) Could not parse options[other]=".array2string($options['other']));
			return '400 Bad Request';
		}
		// make sure search property is included in toplevel props (can be missing and defaults to property-search/prop's)
		foreach($search_props as $prop)
		{
			if (!$this->groupdav->prop_requested($prop['name'], $prop['xmlns']))
			{
				$options['props'][] = array(
					'name' => $prop['name'],
					'xmlns' => $prop['xmlns'],
				);
			}
			// Hack for Lightning: it requests calendar-home-set matching our root (/egroupware/groupdav.php),
			// but interprets returning all principals (all have a matching calendar-home-set) as NOT supporting CalDAV scheduling
			// --> search only current user's principal
			if ($prop['name'] == 'calendar-home-set' && stripos($_SERVER['HTTP_USER_AGENT'], 'Lightning') !== false)
			{
				$path = '/principals/users/'.$GLOBALS['egw_info']['user']['account_lid'].'/';
			}
		}
		// run "regular" propfind
		$options['other'] = array();
		$options['root']['name'] = 'propfind';
		// search all principals, but not the proxys, rfc requires depth=0, but to search all principals
		$options['depth'] = 5 - count(explode('/', $path)); // /principals/ --> 3

		if (($ret = $this->propfind($path, $options, $files, $user)) !== true)
		{
			return $ret;
		}
		// now filter out not matching "files"
		foreach($files['files'] as $n => $resource)
		{
			if (count(explode('/', $resource['path'])) < 5)	// hack to only return principals, not the collections itself
			{
				unset($files['files'][$n]);
				continue;
			}
			// match with $search_props
			$matches = 0;
			foreach($search_props as $search_prop)
			{
				// search resource for $search_prop
				foreach($resource['props'] as $prop) if ($prop['name'] === $search_prop['name']) break;
				if ($prop['name'] === $search_prop['name'])	// search_prop NOT found
				{
					foreach((array)$prop['val'] as $value)
					{
						if (is_array($value)) $value = $value['val'];	// eg. href prop
						if (self::match($value, $search_prop['match'], $search_prop['match-type']) !== false)	// prop does match
						{
							++$matches;
							//error_log("$matches: $resource[path]: $search_prop[name]=".array2string($prop['name'] !== $search_prop['name'] ? null : $prop['val'])." does match '$search_prop[match]'");
							break;
						}
					}
				}
				if ($anyof && $matches || $matches == count($search_props))
				{
					//error_log("$resource[path]: anyof=$anyof, $matches matches --> keep");
					continue 2;	// enough matches --> keep
				}
			}
			//error_log("$resource[path]: anyof=$anyof, $matches matches --> skip");
			unset($files['files'][$n]);
		}
		return $ret;
	}

	/**
	 * Match using $match_type
	 *
	 * It's not defined in WebDAV ACL, but CardDAV:text-match seems similar
	 *
	 * @param string $value value to test
	 * @param string $match criteria/sub-string
	 * @param string $match_type='contains' 'starts-with', 'ends-with' or 'equals'
	 */
	private static function match($value, $match, $match_type='contains')
	{
		switch($match_type)
		{
			case 'equals':
				return $value === $match;

			case 'starts-with':
				return stripos($value, $match) === 0;

			case 'ends-with':
				return stripos($value, $match) === strlen($value) - strlen($match);

			case 'contains':
			default:
				return stripos($value, $match) !== false;
		}
	}

	/**
	 * Handle principal-search-property-set report
	 *
	 * REPORT /principals/ HTTP/1.1
	 * <?xml version="1.0" encoding="utf-8" ?>
	 * <x0:principal-search-property-set xmlns:x0="DAV:"/>
	 *
	 * <?xml version='1.0' encoding='UTF-8'?>
	 * <principal-search-property-set xmlns='DAV:'>
	 *   <principal-search-property>
	 *     <prop>
	 *       <displayname/>
	 *     </prop>
	 *     <description xml:lang='en'>Display Name</description>
	 *   </principal-search-property>
	 *   <principal-search-property>
	 *     <prop>
	 *       <email-address-set xmlns='http://calendarserver.org/ns/'/>
	 *     </prop>
	 *     <description xml:lang='en'>Email Addresses</description>
	 *   </principal-search-property>
	 *   <principal-search-property>
	 *     <prop>
	 *       <last-name xmlns='http://calendarserver.org/ns/'/>
	 *     </prop>
	 *     <description xml:lang='en'>Last Name</description>
	 *   </principal-search-property>
	 *   <principal-search-property>
	 *     <prop>
	 *       <calendar-user-type xmlns='urn:ietf:params:xml:ns:caldav'/>
	 *     </prop>
	 *     <description xml:lang='en'>Calendar User Type</description>
	 *   </principal-search-property>
	 *   <principal-search-property>
	 *     <prop>
	 *       <first-name xmlns='http://calendarserver.org/ns/'/>
	 *     </prop>
	 *     <description xml:lang='en'>First Name</description>
	 *   </principal-search-property>
	 *   <principal-search-property>
	 *     <prop>
	 *       <calendar-user-address-set xmlns='urn:ietf:params:xml:ns:caldav'/>
	 *     </prop>
	 *     <description xml:lang='en'>Calendar User Address Set</description>
	 *   </principal-search-property>
	 * </principal-search-property-set>
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function principal_search_property_set_report($path,&$options,&$files,$user)
	{
		static $search_props = array(
			// from iOS iCal
			'displayname' => 'Display Name',
			'email-address-set' => array('description' => 'Email Addresses', 'ns' => groupdav::CALENDARSERVER),
			'last-name' => array('description' => 'Last Name', 'ns' => groupdav::CALENDARSERVER),
			'calendar-user-type' => array('description' => 'Calendar User Type', 'ns' => groupdav::CALDAV),
			'first-name' => array('description' => 'First Name', 'ns' => groupdav::CALENDARSERVER),
			'calendar-user-address-set' => array('description' => 'Calendar User Address Set', 'ns' => groupdav::CALDAV),
			// Lightning
			'calendar-home-set' => array('description' => 'Calendar Home Set', 'ns' => groupdav::CALENDARSERVER),
			// others, we generally support all properties of the principal
		);
		header('Content-type: text/xml; charset=UTF-8');

		$xml = new XMLWriter;
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElementNs(null, 'principal-search-property-set', 'DAV:');

		foreach($search_props as $name => $data)
		{
			$xml->startElement('principal-search-property');
			$xml->startElement('prop');
			if (is_array($data) && !empty($data['ns']))
			{
				$xml->writeElementNs(null, $name, $data['ns']);
			}
			else
			{
				$xml->writeElement($name);
			}
			$xml->endElement();	// prop

			$xml->startElement('description');
			$xml->writeAttribute('xml:lang', 'en');
			$xml->text(is_array($data) ? $data['description'] : $data);
			$xml->endElement();	// description

			$xml->endElement();	// principal-search-property
		}
		$xml->endElement();	// principal-search-property-set
		$xml->endDocument();
		echo $xml->outputMemory();

		common::egw_exit();
	}

	/**
	 * Do propfind in /pricipals/users
	 *
	 * @param string $name name of account or empty
	 * @param string $rest rest of path behind account-name
	 * @param array $options
	 * @return array|string array with files or HTTP error code
	 */
	protected function propfind_users($name,$rest,array $options)
	{
		//error_log(__METHOD__."($name,$rest,".array2string($options).')');
		if (empty($name))
		{
			$files = array();
			// add /pricipals/users/ entry
			$files[] = $this->add_collection('/principals/users/');

			if ($options['depth'])
			{
				if ($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none')
				{
					$files[] = $this->add_account($this->accounts->read($GLOBALS['egw_info']['user']['account_id']));
				}
				else
				{
					// add all users (account_selection == groupmembers is handled by accounts->search())
					foreach($this->accounts->search(array('type' => 'accounts')) as $account)
					{
						$files[] = $this->add_account($account);
					}
				}
			}
		}
		else
		{
			if (!($id = $this->accounts->name2id($name,'account_lid','u')) ||
				!($account = $this->accounts->read($id)) ||
				// do NOT allow other user, if account-selection is none
				$GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none' &&
					$name != $GLOBALS['egw_info']['user']['account_lid'] ||
				// only allow group-members for account-selection is groupmembers
				$GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'groupmembers' &&
					!array_intersect($this->accounts->memberships($account['account_id'],true),
						$this->accounts->memberships($GLOBALS['egw_info']['user']['account_id'],true)))
			{
				return '404 Not Found';
			}
			while (substr($rest,-1) == '/') $rest = substr($rest,0,-1);
			switch((string)$rest)
			{
				case '':
					$files[] = $this->add_account($account);
					if ($options['depth'])
					{
						$files[] = $this->add_proxys('users/'.$account['account_lid'], 'calendar-proxy-read');
						$files[] = $this->add_proxys('users/'.$account['account_lid'], 'calendar-proxy-write');
					}
					break;
				case 'calendar-proxy-read':
				case 'calendar-proxy-write':
					$files = array();
					$files[] = $this->add_proxys('users/'.$account['account_lid'], $rest);
					break;
				default:
					return '404 Not Found';
			}
		}
		return $files;
	}

	/**
	 * Do propfind in /pricipals/groups
	 *
	 * @param string $name name of group or empty
	 * @param string $rest rest of path behind account-name
	 * @param array $options
	 * @return array|string array with files or HTTP error code
	 */
	protected function propfind_groups($name,$rest,array $options)
	{
		//echo "<p>".__METHOD__."($name,$rest,".array2string($options).")</p>\n";
		if (empty($name))
		{
			$files = array();
			// add /pricipals/users/ entry
			$files[] = $this->add_collection('/principals/groups/');

			if ($options['depth'])
			{
				// only show own groups, if account-selection is groupmembers or none
				$type = in_array($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'], array('groupmembers','none')) ?
					'owngroups' : 'groups';

				// add all groups or only membergroups
				foreach($this->accounts->search(array('type' => $type)) as $account)
				{
					$files[] = $this->add_group($account);
				}
			}
		}
		else
		{
			if (!($id = $this->accounts->name2id($name,'account_lid','g')) ||
				!($account = $this->accounts->read($id)) ||
				// do NOT allow other groups, if account-selection is groupmembers or none
				in_array($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'], array('groupmembers','none')) &&
				!in_array($account['account_id'], $this->accounts->memberships($GLOBALS['egw_info']['user']['account_id'],true)))
			{
				return '404 Not Found';
			}
			while (substr($rest,-1) == '/') $rest = substr($rest,0,-1);
			switch((string)$rest)
			{
				case '':
					$files[] = $this->add_group($account);
					if ($options['depth'])
					{
						$files[] = $this->add_proxys('groups/'.$account['account_lid'], 'calendar-proxy-read');
						$files[] = $this->add_proxys('groups/'.$account['account_lid'], 'calendar-proxy-write');
					}
					break;
				case 'calendar-proxy-read':
				case 'calendar-proxy-write':
					$files = array();
					$files[] = $this->add_proxys('groups/'.$account['account_lid'], $rest);
					break;
				default:
					return '404 Not Found';
			}
		}
		return $files;
	}

	/**
	 * Get shared addressbooks of current user
	 *
	 * @return array with path relative to base URI (without addressbook postfix!)
	 */
	protected function get_shared_addressbooks()
	{
		$addressbooks = array();
		$addressbook_home_set = $GLOBALS['egw_info']['user']['preferences']['groupdav']['addressbook-home-set'];
		if (empty($addressbook_home_set)) $addressbook_home_set = 'P';	// personal addressbook
		$addressbook_home_set = explode(',',$addressbook_home_set);
		// replace symbolic id's with real nummeric id's
		foreach(array(
			'P' => $GLOBALS['egw_info']['user']['account_id'],
			'G' => $GLOBALS['egw_info']['user']['account_primary_group'],
			'U' => '0',
		) as $sym => $id)
		{
			if (($key = array_search($sym, $addressbook_home_set)) !== false)
			{
				$addressbook_home_set[$key] = $id;
			}
		}
		if (in_array('O',$addressbook_home_set))	// "all in one" from groupdav.php/addressbook/
		{
			$addressbooks[] = '/';
		}
		foreach(ExecMethod('addressbook.addressbook_bo.get_addressbooks',EGW_ACL_READ) as $id => $label)
		{
			if ((in_array('A',$addressbook_home_set) || in_array((string)$id,$addressbook_home_set)) &&
				is_numeric($id) && ($owner = $this->accounts->id2name($id)))
			{
				$addressbooks[] = '/'.urlencode($owner).'/';
			}
		}
		return $addressbooks;
	}

	/**
	 * Add collection of a single account to a collection
	 *
	 * @param array $account
	 * @return array with values for keys 'path' and 'props'
	 */
	protected function add_account(array $account)
	{
		$addressbooks = $calendars = array();
		if ($account['account_id'] == $GLOBALS['egw_info']['user']['account_id'])
		{
			foreach($this->get_shared_addressbooks() as $path)
			{
				$addressbooks[] = HTTP_WebDAV_Server::mkprop('href',$this->base_uri.$path);
			}

			$calendars[] = HTTP_WebDAV_Server::mkprop('href',
				$this->base_uri.'/'.$account['account_lid'].'/');

/* iCal send propfind to wrong url (concatinated href's), if we return multiple href in calendar-home-set
			$cal_bo = new calendar_bo();
			foreach ($cal_bo->list_cals() as $label => $entry)
			{
				$id = $entry['grantor'];
				$owner = $this->accounts->id2name($id);
				$calendars[] = HTTP_WebDAV_Server::mkprop('href',
					$this->base_uri.'/'.$owner.'/');
			}
*/
		}
		else
		{
			$addressbooks[] = HTTP_WebDAV_Server::mkprop('href',
				$this->base_uri.'/'.$account['account_lid'].'/');
			$calendars[] = HTTP_WebDAV_Server::mkprop('href',
				$this->base_uri.'/'.$account['account_lid'].'/');
		}
		$displayname = translation::convert($account['account_fullname'], translation::charset(),'utf-8');

		return $this->add_principal('users/'.$account['account_lid'], array(
			'getetag' => $this->get_etag($account),
			'displayname' => $displayname,
			// CalDAV
			'calendar-home-set' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-home-set',$calendars),
			// CalDAV scheduling
			'schedule-outbox-URL' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'schedule-outbox-URL',array(
				HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/'.$account['account_lid'].'/outbox/'))),
			'schedule-inbox-URL' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'schedule-inbox-URL',array(
				HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/'.$account['account_lid'].'/inbox/'))),
			'calendar-user-address-set' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-address-set',array(
				HTTP_WebDAV_Server::mkprop('href','MAILTO:'.$account['account_email']),
				HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/principals/users/'.$account['account_lid'].'/'),
				HTTP_WebDAV_Server::mkprop('href','urn:uuid:'.$account['account_lid']))),
			'calendar-user-type' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-type','INDIVIDUAL'),
			// Calendarserver
			'email-address-set' => HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'email-address-set',array(
				HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'email-address',$account['account_email']))),
			'last-name' => HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'last-name',$account['account_lastname']),
			'first-name' => HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'first-name',$account['account_firstname']),
			'record-type' => HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'record-type','users'),
			// WebDAV ACL and CalDAV proxy
			'group-membership' => $this->principal_set('group-membership', $this->accounts->memberships($account['account_id']),
				'calendar', $account['account_id']),	// add proxy-rights
			'alternate-URI-set' => array(
				HTTP_WebDAV_Server::mkprop('href','MAILTO:'.$account['account_email'])),
			// CardDAV
			'addressbook-home-set' => HTTP_WebDAV_Server::mkprop(groupdav::CARDDAV,'addressbook-home-set',$addressbooks),
			// CardDAV directory
			'directory-gateway' => HTTP_WebDAV_Server::mkprop(groupdav::CARDDAV, 'directory-gateway',array(
				HTTP_WebDAV_Server::mkprop('href', $this->base_uri.'/addressbook/'))),
		));
	}

	/**
	 * Add collection of a single group to a collection
	 *
	 * @param array $account
	 * @return array with values for keys 'path' and 'props'
	 */
	protected function add_group(array $account)
	{
		$displayname = translation::convert(lang('Group').' '.$account['account_lid'],	translation::charset(), 'utf-8');

		// only return current user, if account-selection == 'none'
		if ($GLOBALS['egw_info']['user']['preferences']['common']['account_selection'] == 'none')
		{
			$groupmembers = array($GLOBALS['egw_info']['user']['account_id'] => $GLOBALS['egw_info']['user']['account_lid']);
		}
		else
		{
			$groupmembers = $this->accounts->members($account['account_id']);
		}

		return $this->add_principal('groups/'.$account['account_lid'], array(
			'getetag' => $this->get_etag($account),
			'displayname' => $displayname,
			'calendar-home-set' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-home-set',array(
				HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/'.$account['account_lid'].'/'))),
			'addressbook-home-set' => HTTP_WebDAV_Server::mkprop(groupdav::CARDDAV,'addressbook-home-set',array(
				HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/'.$account['account_lid'].'/'))),
			'record-type' => HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'record-type','group'),
			'calendar-user-type' => HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-type','GROUP'),
			'group-member-set' => $this->principal_set('group-member-set', $groupmembers),
		));
	}

	/**
	 * Add a collection
	 *
	 * @param string $path
	 * @param array $props=array() extra properties 'resourcetype' is added anyway, name => value pairs or name => HTTP_WebDAV_Server([namespace,]name,value)
	 * @return array with values for keys 'path' and 'props'
	 */
	protected function add_collection($path, array $props = array())
	{
		if ($this->groupdav->prop_requested('supported-report-set'))
		{
			$props['supported-report-set'] = $this->supported_report_set($path);
		}
		return $this->groupdav->add_collection($path, $props);
	}

	/**
	 * Add a principal collection
	 *
	 * @param string $principal relative to principal-collection-set, eg. "users/username"
	 * @param array $props=array() extra properties 'resourcetype' is added anyway
	 * @param string $principal_url=null include given principal url, relative to principal-collection-set, default $principal
	 * @return array with values for keys 'path' and 'props'
	 */
	protected function add_principal($principal, array $props = array(), $principal_url=null)
	{
		$props['resourcetype'][] = HTTP_WebDAV_Server::mkprop('principal', '');

		// required props per WebDAV ACL
		foreach(array('alternate-URI-set', 'group-membership') as $name)
		{
			if (!isset($props[$name])) $props[$name] = HTTP_WebDAV_Server::mkprop($name,'');
		}
		if (!$principal_url) $principal_url = $principal;

		$props['principal-URL'] = array(
			HTTP_WebDAV_Server::mkprop('href',$this->base_uri.'/principals/'.$principal.'/'));

		return $this->add_collection('/principals/'.$principal.'/', $props);
	}

	/**
	 * Add a proxy collection for given principal and type
	 *
	 * A proxy is a user or group who has the right to act on behalf of the user
	 *
	 * @param string $principal relative to principal-collection-set, eg. "users/username"
	 * @param string $type eg. 'calendar-proxy-read' or 'calendar-proxy-write'
	 * @param array $proxys=array()
	 * @return array with values for 'path' and 'props'
	 */
	protected function add_proxys($principal, $type, array $proxys=array())
	{
		list($app,,$what) = explode('-', $type);
		$right = $what == 'write' ? EGW_ACL_EDIT : EGW_ACL_READ;
		$mask = $what == 'write' ? EGW_ACL_EDIT : EGW_ACL_EDIT|EGW_ACL_READ;	// do NOT report write+read in read
		//echo "<p>type=$type --> app=$app, what=$what --> right=$right, mask=$mask</p>\n";

		list($account_type,$account) = explode('/', $principal);
		$account = $this->accounts->name2id($account, 'account_lid', $account_type[0]);

		$proxys = array();
		foreach($this->acl->get_all_location_rights($account, $app, $app != 'addressbook') as $account_id => $rights)
		{
			if ($account_id !== 'run' && $account_id != $account && ($rights & $mask) == $right &&
				($account_lid = $this->accounts->id2name($account_id)))
			{
				$proxys[$account_id] = $account_lid;
				// for groups add members too, if app is not addressbook
				if ($account_id < 0 && $app != 'addressbook')
				{
					foreach($this->accounts->members($account_id) as $account_id => $account_lid)
					{
						$proxys[$account_id] = $account_lid;
					}
				}
			}
			//echo "<p>$account_id ($account_lid): (rights=$rights & mask=$mask) == right=$right --> ".array2string(($rights & $mask) == $right)."</p>\n";
		}
		return $this->add_principal($principal.'/'.$type, array(
				'displayname' => $app.' '.$what.' proxy of '.basename($principal),
				'group-member-set' => $this->principal_set('group-member-set', $proxys),
				'getetag' => 'EGw-'.md5(serialize($proxys)).'-wGE',
				'resourcetype' => array(HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER, $type, '')),
			));
	}

	/**
	 * Create a named property with set or principal-urls
	 *
	 * @param string $prop egw. 'group-member-set' or 'membership'
	 * @param array $accounts=array() account_id => account_lid pairs
	 * @param string|array $app_proxys=null applications for which proxys should be added
	 * @param int $account who is the proxy
	 * @param array with href props
	 */
	protected function principal_set($prop, array $accounts=array(), $add_proxys=null, $account=null)
	{
		$set = array();
		foreach($accounts as $account_id => $account_lid)
		{
			$set[] = HTTP_WebDAV_Server::mkprop('href', $this->base_uri.'/principals/'.($account_id < 0 ? 'groups/' : 'users/').$account_lid.'/');
		}
		if ($add_proxys)
		{
			foreach((array)$add_proxys as $app)
			{
				foreach($this->acl->get_grants($app, $app != 'addressbook', $account) as $account_id => $rights)
				{
					if ($account_id != $account && ($rights & EGW_ACL_READ) &&
						($account_lid = $this->accounts->id2name($account_id)))
					{
						$set[] = HTTP_WebDAV_Server::mkprop('href', $this->base_uri.'/principals/'.
							($account_id < 0 ? 'groups/' : 'users/').
							$account_lid.'/'.$app.'-proxy-'.($rights & EGW_ACL_EDIT ? 'write' : 'read').'/');
					}
				}
			}
		}
		return $set;
	}

	/**
	 * Do propfind of /principals/
	 *
	 * @param string $name name of group or empty
	 * @param string $rest name of rest of path behind group-name
	 * @param array $options
	 * @return array|string array with files or HTTP error code
	 */
	protected function propfind_principals(array $options)
	{
		//echo "<p>".__METHOD__."(".array($options).")</p>\n";
		$files = array();
		$files[] = $this->add_collection('/principals/');

		if ($options['depth'])
		{
			if (is_numeric($options['depth'])) --$options['depth'];
			$files = array_merge($files,$this->propfind_users('','',$options));
			$files = array_merge($files,$this->propfind_groups('','',$options));
			//$files = array_merge($this->propfind_resources('','',$options));
			//$files = array_merge($this->propfind_uids('','',$options));
		}
		return $files;
	}

	/**
	 * Handle get request for an applications entry
	 *
	 * @param array &$options
	 * @param int $id
	 * @param int $user=null account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function get(&$options,$id,$user=null)
	{
		return false;
	}

	/**
	 * Handle get request for an applications entry
	 *
	 * @param array &$options
	 * @param int $id
	 * @param int $user=null account_id of owner, default null
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function put(&$options,$id,$user=null)
	{
		return false;
	}

	/**
	 * Handle get request for an applications entry
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function delete(&$options,$id)
	{
		return false;
	}

	/**
	 * Read an entry
	 *
	 * @param string/int $id
	 * @return array/boolean array with entry, false if no read rights, null if $id does not exist
	 */
	function read($id)
	{
		return false;
		//return $this->accounts->read($id);
	}

	/**
	 * Check if user has the neccessary rights on an entry
	 *
	 * @param int $acl EGW_ACL_READ, EGW_ACL_EDIT or EGW_ACL_DELETE
	 * @param array/int $entry entry-array or id
	 * @return boolean null if entry does not exist, false if no access, true if access permitted
	 */
	function check_access($acl,$entry)
	{
		if ($acl != EGW_ACL_READ)
		{
			return false;
		}
		if (!is_array($entry) && !$this->accounts->name2id($entry,'account_lid','u'))
		{
			return null;
		}
		return true;
	}

	/**
	 * Get the etag for an entry, can be reimplemented for other algorithm or field names
	 *
	 * @param array/int $event array with event or cal_id
	 * @return string/boolean string with etag or false
	 */
	function get_etag($account)
	{
		if (!is_array($account))
		{
			$account = $this->read($account);
		}
		return 'EGw-'.$account['account_id'].':'.md5(serialize($account)).
			// add md5 from calendar grants, as they are listed as memberships
			':'.md5(serialize($this->acl->get_grants('calendar', true, $account['account_id']))).
			// as the principal of current user is influenced by GroupDAV prefs, we have to include them in the etag
			($account['account_id'] == $GLOBALS['egw_info']['user']['account_id'] ?
				':'.md5(serialize($GLOBALS['egw_info']['user']['preferences']['groupdav'])) : '').'-wGE';
	}

	/**
	 * Return priviledges for current user, default is read and read-current-user-privilege-set
	 *
	 * Priviledges are for the collection, not the resources / entries!
	 *
	 * @param int $user=null owner of the collection, default current user
	 * @return array with privileges
	 */
	public function current_user_privileges($user=null)
	{
		return array('read', 'read-current-user-privilege-set');
	}
}