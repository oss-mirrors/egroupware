<?php
/**
 * eGroupWare: GroupDAV access: calendar handler
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package icalsrv
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

require_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.icalsrv_groupdav_handler.inc.php');
require_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.bocalupdate.inc.php');

/**
 * eGroupWare: GroupDAV access: calendar handler
 */
class icalsrv_groupdav_calendar extends icalsrv_groupdav_handler
{
	/**
	 * bo class of the application
	 *
	 * @var bocalupdate
	 */	
	var $bo;

	function icalsrv_groupdav_calendar($debug=null)
	{
		$this->icalsrv_groupdav_handler('calendar',$debug);
		
		$this->bo =& new bocalupdate();
	}
	
	/**
	 * Handle propfind in the calendar folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function propfind($path,$options,&$files,$user)
	{
		$icalvc =& $this->_instanciate_icalvc($user);
		// ToDo: add parameter to only return id & etag
		if (($events = $this->bo->search($icalvc->_caldef['rscs']['calendar.bocalupdate'])))
		{
			foreach($events as $event)
			{
				$files['files'][] = array(
	            	'path'  => '/calendar/'.$event['id'],
	            	'props' => array(
	            		HTTP_WebDAV_Server::mkprop('getetag',$this->get_etag($event)),
	            		HTTP_WebDAV_Server::mkprop('getcontenttype', 'text/calendar'),
	            	),
				);
			}
		}
		return true;
	}
	
	/**
	 * Handle get request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function get(&$options,$id)
	{
		if (!is_array($event = $this->_common_get_put_delete('GET',$options,$id)))
		{
			return $event;
		}
		include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.bocalupdate_vevents.inc.php');
		$handler =& new bocalupdate_vevents($this->bo);
		$options['data'] = $handler->export_vcal($events=array($event));
		$options['mimetype'] = 'text/calendar; charset=utf-8';
		header('Content-Encoding: identity');
		header('ETag: '.$this->get_etag($event));
		return true;
	}
	
	/**
	 * Handle put request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @param int $user=null account_id of owner, default null
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function put(&$options,$id,$user=null)
	{
		$event = $this->_common_get_put_delete('PUT',$options,$id);
		if (!is_null($event) && !is_array($event))
		{
			return $event;
		}
		include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.bocalupdate_vevents.inc.php');
		$handler =& new bocalupdate_vevents($this->bo);
		$vcalelm =& $handler->parse_vcal2velt($options['content']);
		if (!($cal_id = $handler->import_vevent($vcalelm, $uid_mapping_import=UMM_UID2UID, $reimport_missing_events=false, $id)) > 0)
		{
			if ($this->debug) error_log("icalsrv_groupdav_calendar::put(,$id) import_vevent($options[content]) returned false");
			return false;	// something went wrong ...
		}
		header('ETag: '.$this->get_etag($cal_id));
		if (is_null($event) || $id != $cal_id)
		{
			header('Location: '.$this->base_uri.'/calendar/'.$cal_id);
			return '201 Created';
		}
		return true;
	}
	
	/**
	 * Handle delete request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function delete(&$options,$id)
	{
		if (!is_array($event = $this->_common_get_put_delete('DELETE',$options,$id)))
		{
			return $event;
		}
		return $this->bo->delete($id);
	}
	
	/**
	 * Read an entry
	 *
	 * @param string/id $id
	 * @return array/boolean array with entry, false if no read rights, null if $id does not exist
	 */
	function read($id)
	{
		return $this->bo->read($id,null,false,'server');
	}
	
	/**
	 * Check if user has the neccessary rights on an event
	 *
	 * @param int $acl EGW_ACL_READ, EGW_ACL_EDIT or EGW_ACL_DELETE
	 * @param array/int $event event-array or id
	 * @return boolean null if entry does not exist, false if no access, true if access permitted
	 */
	function check_access($acl,$event)
	{
		return $this->bo->check_perms($acl,$event,0,'server');
	}
}