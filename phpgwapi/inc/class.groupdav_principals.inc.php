<?php
/**
 * eGroupWare: GroupDAV access: groupdav/caldav/carddav principals handlers
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage groupdav
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2008 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * eGroupWare: GroupDAV access: groupdav/caldav/carddav principals handlers
 */
class groupdav_principals extends groupdav_handler
{
	/**
	 * Reference to the accounts class
	 *
	 * @var accounts
	 */
	var $accounts;

	/**
	 * Constructor
	 *
	 * @param string $app
	 * @param int $debug=null
	 */
	function __construct($app,$debug=null)
	{
		parent::__construct($app,$debug);

		$this->accounts = $GLOBALS['egw']->accounts;
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
		list(,,$id) = explode('/',$path);
		if ($id && !($id = $this->accounts->id2name($id)))
		{
			return false;
		}
		foreach($id ? array($this->accounts->read($id)) : $this->accounts->search(array('type' => 'accounts')) as $account)
		{
	      $props = array(
				HTTP_WebDAV_Server::mkprop('displayname',trim($account['account_firstname'].' '.$account['account_lastname'])),
				HTTP_WebDAV_Server::mkprop('getetag',$this->get_etag($account)),
				HTTP_WebDAV_Server::mkprop('resourcetype','principal'),
				HTTP_WebDAV_Server::mkprop('alternate-URI-set',''),
				HTTP_WebDAV_Server::mkprop('principal-URL',$_SERVER['SCRIPT_NAME'].'/principals/'.$account['account_lid']),
				HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-home-set',$_SERVER['SCRIPT_NAME'].'/'),
				HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-address-set','MAILTO:'.$account['account_email']),
			);
			foreach($this->accounts->memberships($account['account_id']) as $gid => $group)
			{
				$props[] = HTTP_WebDAV_Server::mkprop('group-membership',$_SERVER['SCRIPT_NAME'].'/groups/'.$group);
			}
			$files['files'][] = array(
	           	'path'  => '/principals/'.$account['account_lid'],
	           	'props' => $props,
			);
			error_log(__METHOD__."($path) path=/principals/".$account['account_lid'].', props='.array2string($props));
		}
		//error_log(__METHOD__."($path,,,$user) files=".array2string($files['files']));
		return true;
	}

	/**
	 * Handle get request for an applications entry
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function get(&$options,$id)
	{
		if (!is_array($account = $this->_common_get_put_delete('GET',$options,$id)))
		{
			return $account;
		}
		$options['data'] = 'Principal: '.$account['account_lid'].
			"\nURL: ".$_SERVER['SCRIPT_NAME'].$options['path'].
			"\nName: ".$account['account_firstname'].' '.$account['account_lastname'].
			"\nEmail: ".$account['account_email'].
			"\nMemberships: ".implode(', ',$this->accounts->memberships($id))."\n";
		$options['mimetype'] = 'text/plain; charset=utf-8';
		header('Content-Encoding: identity');
		header('ETag: '.$this->get_etag($account));
		return true;
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
		return $this->accounts->read($id);
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
		return '"'.$account['account_id'].':'.md5(serialize($account)).'"';
	}
}