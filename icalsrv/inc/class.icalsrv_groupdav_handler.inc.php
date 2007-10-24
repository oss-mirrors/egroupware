<?php
/**
 * eGroupWare: GroupDAV access: virtual baseclass for handlers
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package icalsrv
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * eGroupWare: GroupDAV access: virtual baseclass for handlers
 */
class icalsrv_groupdav_handler
{
	/**
	 * Debug level: 0 = nothing, 1 = function calls, 2 = more info, eg. complete $_SERVER array
	 * 
	 * The debug messages are send to the apache error_log
	 *
	 * @var integer
	 */
	var $debug;
	
	/**
	 * eGW's charset
	 *
	 * @var string
	 */
	var $egw_charset;
	/**
	 * Reference to the translation class
	 *
	 * @var translation
	 */
	var $translation;
	/**
	 * Translates method names into ACL bits
	 *
	 * @var array
	 */
	var $method2acl = array(
		'GET' => EGW_ACL_READ,
		'PUT' => EGW_ACL_EDIT,
		'DELETE' => EGW_ACL_DELETE,
	);
	/**
	 * eGW application responsible for the handler
	 *
	 * @var string
	 */
	var $app;

	function icalsrv_groupdav_handler($app,$debug=null)
	{
		$this->app = $app;
		if (!is_null($debug)) $this->debug = $debug;

		$this->translation =& $GLOBALS['egw']->translation;
		$this->egw_charset = $this->translation->charset();
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
		die('Virtual method!');
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
		die('Virtual method!');
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
		die('Virtual method!');
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
		die('Virtual method!');
	}
	
	/**
	 * Read an entry
	 *
	 * @param string/int $id
	 * @return array/boolean array with entry, false if no read rights, null if $id does not exist
	 */
	function read($id)
	{
		die('Virtual method!');
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
		die('Virtual method!');		
	}
	
	/**
	 * Get the etag for an entry, can be reimplemented for other algorithm or field names
	 *
	 * @param array/int $event array with event or cal_id
	 * @return string/boolean string with etag or false
	 */
	function get_etag($entry)
	{
		if (!is_array($entry))
		{
			$entry = $this->read($entry);
		}
		if (!is_array($entry) || !isset($entry['id']) || !isset($entry['modified']))
		{
			return false;
		}
		return '"'.$entry['id'].':'.$entry['modified'].'"';
	}

	/**
	 * Handle common stuff for get, put and delete requests:
	 *  - application rights
	 *  - entry level acl, incl. edit and delete rights
	 *  - etag handling for precondition failed and not modified
	 *
	 * @param string $method GET, PUT, DELETE
	 * @param array &$options
	 * @param int $id
	 * @return array/string entry on success, string with http-error-code on failure, null for PUT on an unknown id
	 */
	function _common_get_put_delete($method,&$options,$id)
	{
		if (!$GLOBALS['egw_info']['user']['apps'][$this->app])
		{
			if ($this->debug) error_log("icalsrv_groupdav_handler::_common_get_put_delete($method,,$id) 403 Forbidden: no app rights");
			return '403 Forbidden';		// no calendar rights
		}
		$extra_acl = $this->method2acl[$method];
		if (!($entry = $this->read($id)) && ($method != 'PUT' || $event === false) ||
			($extra_acl != EGW_ACL_READ && $this->check_access($extra_acl,$entry) === false))
		{
			if ($this->debug) error_log("icalsrv_groupdav_handler::_common_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: read($id)==".($entry===false?'false':'null'));
			return !is_null($entry) ? '403 Forbidden' : '404 Not Found';
		}
		if ($entry)
		{
			$etag = $this->get_etag($entry);
			// If the clients sends an "If-Match" header ($_SERVER['HTTP_IF']) we check with the current etag
			// of the calendar --> on failure we return 412 Precondition failed, to not overwrite the modifications 
			if (isset($_SERVER['HTTP_IF']) && preg_match('/\(\[("[0-9a-z_-]+:[0-9]+")\]\)/',$_SERVER['HTTP_IF'],$matches) &&
				$matches[1] != $etag)
			{
				if ($this->debug) error_log("icalsrv_groupdav_handler::_common_get_put_delete($method,,$id) HTTP_IF='$_SERVER[HTTP_IF]', etag='$etag': 412 Precondition failed");
				return '412 Precondition Failed';
			}
			// if an IF_NONE_MATCH is given, check if we need to send a new export, or the current one is still up-to-date
			if ($method == 'GET' && isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)
			{
				if ($this->debug) error_log("icalsrv_groupdav_handler::_common_get_put_delete($method,,$id) HTTP_IF_NONE_MATCH='$_SERVER[HTTP_IF_NONE_MATCH]', etag='$etag': 304 Not Modified");
				return '304 Not Modified';
			}
		}
		return $entry;
	}
	
	/**
	 * Instanciates a virtual calendar
	 *
	 * @param int $user=0 account_id, defaults to $GLOBALS['egw_info']['user']['account_id']
	 * @param string $path='/events.ics'
	 * @return icalvircal
	 */
	function &_instanciate_icalvc($user=0,$path='/events.ics')
	{
		if (!$user) $user = $GLOBALS['egw_info']['user']['account_id'];

		include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.personal_vircal_ardb.inc.php');
		$system_vircal_ardb = new personal_vircal_ardb($user);
		$vircal_arstore = $system_vircal_ardb->calendars[$path];
		require_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.icalvircal.inc.php');
		$icalvc =& new icalvircal;
		$icalvc->fromArray($vircal_arstore);
		$icalvc->uid_mapping_export = UMM_UID2UID; 
		$icalvc->uid_mapping_import = UMM_UID2UID; 
		$icalvc->reimport_missing_elements = false;

		return $icalvc;
	}
	
	/**
	 * Get the handler for the given app
	 *
	 * @static 
	 * @param string $app 'calendar', 'addressbook' or 'infolog'
	 * @param int $debug=null debug-level to set
	 * @return icalsrv_groupdav_handler
	 */
	function &app_handler($app,$debug=null)
	{
		$class = 'icalsrv_groupdav_'.$app;
		$file = EGW_INCLUDE_ROOT.'/icalsrv/inc/class.'.$class.'.inc.php';
		
		if (!file_exists($file)) return null;
		
		include_once($file);
		return new $class($app);
	}
}