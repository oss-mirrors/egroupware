<?php
/**
 * eGroupWare: GroupDAV access: calendar handler
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package calendar
 * @subpackage groupdav
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007-9 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * eGroupWare: GroupDAV access: calendar handler
 */
class calendar_groupdav extends groupdav_handler
{
	/**
	 * bo class of the application
	 *
	 * @var calendar_boupdate
	 */
	var $bo;

	var $filter_prop2cal = array(
		'SUMMARY' => 'cal_title',
		'UID' => 'cal_uid',
		'DTSTART' => 'cal_start',
		'DTEND' => 'cal_end',
		// 'DURATION'
		//'RRULE' => 'recur_type',
		//'RDATE' => 'cal_start',
		//'EXRULE'
		//'EXDATE'
		//'RECURRENCE-ID'
	);

	/**
	 * Does client understand exceptions to be included in VCALENDAR component of series master sharing its UID
	 *
	 * That also means no EXDATE for these exceptions!
	 *
	 * Setting it to false, should give the old behavior used in 1.6 (hopefully) no client needs that.
	 *
	 * @var boolean
	 */
	var $client_shared_uid_exceptions = true;

	/**
	 * Are we using id or uid for the path/url
	 */
	const PATH_ATTRIBUTE = 'id';

	/**
	 * Constructor
	 *
	 * @param string $app 'calendar', 'addressbook' or 'infolog'
	 * @param int $debug=null debug-level to set
	 * @param string $base_uri=null base url of handler
	 * @param string $principalURL=null principal url of handler
	 */
	function __construct($app,$debug=null, $base_uri=null, $principalURL=null)
	{
		parent::__construct($app,$debug,$base_uri,$principalURL);

		$this->bo = new calendar_boupdate();
	}

	/**
	 * Create the path for an event
	 *
	 * @param array|int $event
	 * @return string
	 */
	function get_path($event)
	{
		if (is_numeric($event) && self::PATH_ATTRIBUTE == 'id')
		{
			$name = $event;
		}
		else
		{
			if (!is_array($event)) $event = $this->bo->read($event);
			$name = $event[self::PATH_ATTRIBUTE];
		}
		return $name.'.ics';
	}

	/**
	 * Handle propfind in the calendar folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @param string $id=''
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function propfind($path,$options,&$files,$user,$id='')
	{
		if ($this->debug)
		{
			error_log(__METHOD__."($path,".array2string($options).",,$user,$id)");
		}

		// ToDo: add parameter to only return id & etag
		$filter = array(
			'users' => $user,
			'start' => time()-100*24*3600,	// default one month back -30 breaks all sync recurrences
			'end' => time()+365*24*3600,	// default one year into the future +365
			'enum_recuring' => false,
			'daywise' => false,
			'date_format' => 'server',
		);

		if ($path == '/calendar/')
		{
			$filter['filter'] = 'owner';
		}
		else
		{
			$filter['filter'] = 'default'; // not rejected
		}

		// process REPORT filters or multiget href's
		if (($id || $options['root']['name'] != 'propfind') && !$this->_report_filters($options,$filter,$id))
		{
			return false;
		}
		if ($this->debug > 1)
		{
			error_log(__METHOD__."($path,,,$user,$id) filter=".
				array2string($filter));
		}

		// check if we have to return the full calendar data or just the etag's
		if (!($filter['calendar_data'] = $options['props'] == 'all' &&
			$options['root']['ns'] == groupdav::CALDAV) && is_array($options['props']))
		{
			foreach($options['props'] as $prop)
			{
				if ($prop['name'] == 'calendar-data')
				{
					$filter['calendar_data'] = true;
					break;
				}
			}
		}
/* disabling not working iterator
		// return iterator, calling ourself to return result in chunks
		$files['files'] = new groupdav_propfind_iterator($this,$path,$filter,$files['files']);
		return true;
	}
*/
	/**
	 * Callback for profind interator
	 *
	 * @param string $path
	 * @param array $filter
	 * @param array|boolean $start=false false=return all or array(start,num)
	 * @return array with "files" array with values for keys path and props
	 */
/* disabling not working iterator
	function propfind_callback($path,array $filter,$start=false)
	{
		if ($this->debug) $starttime = microtime(true);
*/
		$calendar_data = $filter['calendar_data'];
		unset($filter['calendar_data']);
/* disabling not working iterator
		$files = array();

		if (is_array($start))
		{
			$filter['offset'] = $start[0];
			$filter['num_rows'] = $start[1];
		}
error_log(__METHOD__."($path,,".array2string($start).") filter=".array2string($filter));
*/
		$events =& $this->bo->search($filter);
		if ($events)
		{
			// get all max user modified times at once
			foreach($events as $k => $event)
			{
				if ($this->client_shared_uid_exceptions &&
						$event['reference'] &&
						($master = $this->bo->read($event['reference'], 0, false, 'server')) &&
						array_search($event['recurrence'], $master['recur_exception']) !== false)
				{
					// this exception will be handled with the series master
					unset($events[$k]);
					continue;
				}
				$ids[] = $event['id'];
			}
			$max_user_modified = $this->bo->so->max_user_modified($ids);

			foreach($events as $event)
			{
				$event['max_user_modified'] = $max_user_modified[$event['id']];
				//header('X-EGROUPWARE-EVENT-'.$event['id'].': '.$event['title'].': '.date('Y-m-d H:i:s',$event['start']).' - '.date('Y-m-d H:i:s',$event['end']));
				$props = array(
					HTTP_WebDAV_Server::mkprop('getetag',$this->get_etag($event)),
					HTTP_WebDAV_Server::mkprop('getcontenttype', $this->agent != 'kde' ?
	            			'text/calendar; charset=utf-8; component=VEVENT' : 'text/calendar'),
					// getlastmodified and getcontentlength are required by WebDAV and Cadaver eg. reports 404 Not found if not set
					HTTP_WebDAV_Server::mkprop('getlastmodified', $event['modified']),
					HTTP_WebDAV_Server::mkprop('resourcetype',''),	// DAVKit requires that attribute!
				);
				//error_log(__FILE__ . __METHOD__ . "Calendar Data : $calendar_data");
				if ($calendar_data)
				{
					$content = $this->iCal($event);
					$props[] = HTTP_WebDAV_Server::mkprop('getcontentlength',bytes($content));
					$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-data',$content);
				}
				else
				{
					$props[] = HTTP_WebDAV_Server::mkprop('getcontentlength', '');		// expensive to calculate and no CalDAV client uses it
				}
/* disabling not working iterator
				$files[] = array(
*/
				$files['files'][] = array(
	            	'path'  => $path.$this->get_path($event),
	            	'props' => $props,
				);
			}
		}
		if ($this->debug)
		{
			error_log(__METHOD__."($path) took ".(microtime(true) - $starttime).
				' to return '.count($files).' items');
		}
/* disabling not working iterator
		return $files;
*/
		return true;
	}

	/**
	 * Process the filters from the CalDAV REPORT request
	 *
	 * @param array $options
	 * @param array &$cal_filters
	 * @param string $id
	 * @return boolean true if filter could be processed, false for requesting not here supported VTODO items
	 */
	function _report_filters($options,&$cal_filters,$id)
	{
		if ($options['filters'])
		{
			// unset default start & end
			$cal_start = $cal_filters['start']; unset($cal_filters['start']);
			$cal_end = $cal_filters['end']; unset($cal_filters['end']);
			$num_filters = count($cal_filters);

			foreach($options['filters'] as $filter)
			{
				switch($filter['name'])
				{
					case 'comp-filter':
						if ($this->debug > 1) error_log(__METHOD__."($options[path],...) comp-filter='{$filter['attrs']['name']}'");

						switch($filter['attrs']['name'])
						{
							case 'VTODO':
								return false;	// return nothing for now, todo: check if we can pass it on to the infolog handler
								// todos are handled by the infolog handler
								//$infolog_handler = new groupdav_infolog();
								//return $infolog_handler->propfind($options['path'],$options,$options['files'],$user,$method);
							case 'VCALENDAR':
							case 'VEVENT':
								break;			// that's our default anyway
						}
						break;
					case 'prop-filter':
						if ($this->debug > 1) error_log(__METHOD__."($options[path],...) prop-filter='{$filter['attrs']['name']}'");
						$prop_filter = $filter['attrs']['name'];
						break;
					case 'text-match':
						if ($this->debug > 1) error_log(__METHOD__."($options[path],...) text-match: $prop_filter='{$filter['data']}'");
						if (!isset($this->filter_prop2cal[strtoupper($prop_filter)]))
						{
							if ($this->debug) error_log(__METHOD__."($options[path],".array2string($options).",...) unknown property '$prop_filter' --> ignored");
						}
						else
						{
							$cal_filters['query'][$this->filter_prop2cal[strtoupper($prop_filter)]] = $filter['data'];
						}
						unset($prop_filter);
						break;
					case 'param-filter':
						if ($this->debug) error_log(__METHOD__."($options[path],...) param-filter='{$filter['attrs']['name']}' not (yet) implemented!");
						break;
					case 'time-range':
				 		if ($this->debug > 1) error_log(__FILE__ . __METHOD__."($options[path],...) time-range={$filter['attrs']['start']}-{$filter['attrs']['end']}");
						$cal_filters['start'] = $filter['attrs']['start'];
						$cal_filters['end']   = $filter['attrs']['end'];
						break;
					default:
						if ($this->debug) error_log(__METHOD__."($options[path],".array2string($options).",...) unknown filter --> ignored");
						break;
				}
			}
			if (count($cal_filters) == $num_filters)	// no filters set --> restore default start and end time
			{
				$cal_filters['start'] = $cal_start;
				$cal_filters['end']   = $cal_end;
			}
		}
		// multiget or propfind on a given id
		//error_log(__FILE__ . __METHOD__ . "multiget of propfind:");
		if ($options['root']['name'] == 'calendar-multiget' || $id)
		{
			// no standard time-range!
			unset($cal_filters['start']);
			unset($cal_filters['end']);

			$ids = array();

			if ($id)
			{
				if (is_numeric($id))
				{
					$ids[] = (int)$id;
				}
				else
				{
					$cal_filters['query']['cal_uid'] = basename($id,'.ics');
				}

			}
			else	// fetch all given url's
			{
				foreach($options['other'] as $option)
				{
					if ($option['name'] == 'href')
					{
						$parts = explode('/',$option['data']);
						if (is_numeric($id = basename(array_pop($parts),'.ics'))) $ids[] = $id;
					}
				}
			}
			if ($ids)
			{
				$cal_filters['query'][] = 'egw_cal.cal_id IN ('.implode(',',array_map(create_function('$n','return (int)$n;'),$ids)).')';
			}

			if ($this->debug > 1) error_log(__FILE__ . __METHOD__ ."($options[path],...,$id) calendar-multiget: ids=".implode(',',$ids));
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
		$options['data'] = $this->iCal($event);
		$options['mimetype'] = 'text/calendar; charset=utf-8';
		header('Content-Encoding: identity');
		header('ETag: '.$this->get_etag($event));
		return true;
	}

	/**
	 * Generate an iCal for the given event
	 *
	 * Taking into account virtual an real exceptions for recuring events
	 *
	 * @param array $event
	 * @return string
	 */
	private function iCal(array $event)
	{
		static $handler = null;
		if (is_null($handler)) $handler = $this->_get_handler();

		$events = array($event);

		// for recuring events we have to add the exceptions
		if ($this->client_shared_uid_exceptions && $event['recur_type'] && !empty($event['uid']))
		{
			$events =& self::get_series($event['uid'],$this->bo);
		}
		elseif(!$this->client_shared_uid_exceptions && $event['reference'])
		{
			$events[0]['uid'] .= '-'.$event['id'];	// force a different uid
		}
		return $handler->exportVCal($events,'2.0','PUBLISH');
	}

	/**
	 * Get array with events of a series identified by its UID (master and all exceptions)
	 *
	 * Maybe that should be part of calendar_bo
	 *
	 * @param string $uid UID
	 * @param calendar_bo $bo=null calendar_bo object to reuse for search call
	 * @return array
	 */
	private static function &get_series($uid,calendar_bo $bo=null)
	{
		if (is_null($bo)) $bo = new calendar_bopdate();

		if (!($masterId = array_shift($bo->find_event(array('uid' => $uid), 'master')))
				|| !($master = $bo->read($masterId, 0, false, 'server')))
		{
			return array(); // should never happen
		}

		$exceptions = $master['recur_exception'];

		$events =& $bo->search(array(
			'query' => array('cal_uid' => $uid),
			'filter' => 'owner',  // return all possible entries
			'daywise' => false,
			'date_format' => 'server',
		));
		$events = array_merge(array($master), $events);
		foreach($events as $k => &$recurrence)
		{
			//error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
			//	"($uid)[$k]:" . array2string($recurrence));
			if (!$k) continue; // nothing to change

			if ($recurrence['id'] != $master['id'])	// real exception
			{
				//error_log('real exception: '.array2string($recurrence));
				// remove from masters recur_exception, as exception is include
				// at least Lightning "understands" EXDATE as exception from what's included
				// in the whole resource / VCALENDAR component
				// not removing it causes Lightning to remove the exception itself
				if (($e = array_search($recurrence['recurrence'],$exceptions)) !== false)
				{
					unset($exceptions[$e]);
				}
				continue;	// nothing to change
			}
			// now we need to check if this recurrence is an exception
			if ($master['participants'] == $recurrence['participants'])
			{
				//error_log('NO exception: '.array2string($recurrence));
				unset($events[$k]);	// no exception --> remove it
				continue;
			}
			// this is a virtual exception now (no extra event/cal_id in DB)
			//error_log('virtual exception: '.array2string($recurrence));
			$recurrence['recurrence'] = $recurrence['start'];
			$recurrence['reference'] = $master['id'];
			$recurrence['recur_type'] = MCAL_RECUR_NONE;	// is set, as this is a copy of the master
			// not for included exceptions (Lightning): $master['recur_exception'][] = $recurrence['start'];
		}
		$events[0]['recur_exception'] = $exceptions;
		return $events;
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
		if ($this->debug) error_log(__METHOD__."($id, $user)".print_r($options,true));

		$return_no_access = true;	// as handled by importVCal anyway and allows it to set the status for participants
		$oldEvent = $this->_common_get_put_delete('PUT',$options,$id,$return_no_access);
		if (!is_null($oldEvent) && !is_array($oldEvent))
		{
			if ($this->debug) error_log(__METHOD__.': '.print_r($oldEvent,true).function_backtrace());
			return $oldEvent;
		}

		if (is_null($oldEvent) && !$this->bo->check_perms(EGW_ACL_ADD, 0, $user))
		{
			// we have no add permission on this user's calendar
			if ($this->debug) error_log(__METHOD__."(,$user) we have not enough rights on this calendar");
			return '403 Forbidden';
		}

		$handler = $this->_get_handler();
		$vCalendar = htmlspecialchars_decode($options['content']);
		$charset = null;
		if (!empty($options['content_type']))
		{
			$content_type = explode(';', $options['content_type']);
			if (count($content_type) > 1)
			{
				array_shift($content_type);
				foreach ($content_type as $attribute)
				{
					trim($attribute);
					list($key, $value) = explode('=', $attribute);
					switch (strtolower($key))
					{
						case 'charset':
							$charset = strtoupper(substr($value,1,-1));
					}
				}
			} 
		}

		if (is_array($oldEvent))
		{
			$eventId = $oldEvent['id'];
			if ($return_no_access)
			{
				$retval = true;
			}
			else
			{
				// let lightning think the event is added
				$retval = '201 Created';
			}
		}
		else
		{
			// new entry?
			if (($foundEvents = $handler->search($vCalendar, null, false, $charset)))
			{
				if (($eventId = array_shift($foundEvents)) &&
					(list($eventId) = explode(':', $eventId)) &&
					($oldEvent = $this->bo->read($eventId)))
				{
					$retval = '301 Moved Permanently';
				}
				else
				{
					// to be safe
					$eventId = -1;
					$retval = '201 Created';
				}
			}
			else
			{
				// new entry
				$eventId = -1;
				$retval = '201 Created';
			}
		}

		if (!($cal_id = $handler->importVCal($vCalendar, $eventId,
			self::etag2value($this->http_if_match), false, 0, $this->principalURL, $user, $charset)))
		{
			if ($this->debug) error_log(__METHOD__."(,$id) importVCal($options[content]) returned false");
			if ($eventId && $cal_id === false)
			{
				// ignore import failures
				$cal_id = $eventId;
				$retval = true;
			}
			else
			{
				return '403 Forbidden';
			}
		}

		header('ETag: '.$this->get_etag($cal_id));
		if ($retval !== true)
		{
			$path = preg_replace('|(.*)/[^/]*|', '\1/', $options['path']);
			if ($this->debug) error_log(__METHOD__."(,$id,$user) cal_id=$cal_id: $retval");
			header('Location: '.$this->base_uri.$path.$this->get_path($cal_id));
			return $retval;
		}
		return true;
	}

	/**
	 * Handle post request for a schedule entry
	 *
	 * @param array &$options
	 * @param int $id
	 * @param int $user=null account_id of owner, default null
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function post(&$options,$id,$user=null)
	{
		if ($this->debug) error_log(__METHOD__."($id, $user)".print_r($options,true));
		
		if (preg_match('/^METHOD:(PUBLISH|REQUEST)(\r\n|\r|\n)(.*)^BEGIN:VEVENT/ism', $options['content']))
		{
			$handler = $this->_get_handler();
			$vCalendar = htmlspecialchars_decode($options['content']);
			$charset = null;
			if (!empty($options['content_type']))
			{
				$content_type = explode(';', $options['content_type']);
				if (count($content_type) > 1)
				{
					array_shift($content_type);
					foreach ($content_type as $attribute)
					{
						trim($attribute);
						list($key, $value) = explode('=', $attribute);
						switch (strtolower($key))
						{
							case 'charset':
								$charset = strtoupper(substr($value,1,-1));
						}
					}
				} 
			}
			
			if (($foundEvents = $handler->search($vCalendar, null, false, $charset)))
			{
				$eventId = array_shift($foundEvents);
				list($eventId) = explode(':', $eventId);
				
				if (!($cal_id = $handler->importVCal($vCalendar, $eventId, null,
					false, 0, $this->principalURL, $user, $charset)))
				{
					if ($this->debug) error_log(__METHOD__."() importVCal($eventId) returned false");
				}
				header('ETag: '.$this->get_etag($eventId));
			}
		}
		return true;
	}

	/**
	 * Fix event series with exceptions, called by calendar_ical::importVCal():
	 *	a) only series master = first event got cal_id from URL
	 *	b) exceptions need to be checked if they are already in DB or new
	 *	c) recurrence-id of (real not virtual) exceptions need to be re-added to master
	 *
	 * @param array &$events
	 */
	static function fix_series(array &$events)
	{
		//foreach($events as $n => $event) error_log(__METHOD__." $n before: ".array2string($event));
		//$master =& $events[0];

		$bo = new calendar_boupdate();

		// get array with orginal recurrences indexed by recurrence-id
		$org_recurrences = $exceptions = array();
		foreach(self::get_series($events[0]['uid'],$bo) as $k => $event)
		{
			if (!$k) $master = $event;
			if ($event['recurrence'])
			{
				$org_recurrences[$event['recurrence']] = $event;
			}
		}

		// assign cal_id's to already existing recurrences and evtl. re-add recur_exception to master
		foreach($events as $k => &$recurrence)
		{
			if (!$recurrence['recurrence'])
			{
				// master
				$recurrence['id'] = $master['id'];
				$master =& $events[$k];
				continue;
			}

			// from now on we deal with exceptions
			$org_recurrence = $org_recurrences[$recurrence['recurrence']];
			if (isset($org_recurrence))	// already existing recurrence
			{
				//error_log(__METHOD__.'() setting id #'.$org_recurrence['id']).' for '.$recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$recurrence['recurrence']);
				$recurrence['id'] = $org_recurrence['id'];

				// re-add (non-virtual) exceptions to master's recur_exception
				if ($recurrence['id'] != $master['id'])
				{
					//error_log(__METHOD__.'() re-adding recur_exception '.$recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$recurrence['recurrence']));
					$exceptions[] = $recurrence['recurrence'];
				}
				// remove recurrence to be able to detect deleted exceptions
				unset($org_recurrences[$recurrence['recurrence']]);
			}
		}
		$master['recur_exception'] = array_merge($exceptions, $master['recur_exception']);

		// delete not longer existing recurrences
		foreach($org_recurrences as $org_recurrence)
		{
			if ($org_recurrence['id'] != $master['id'])	// non-virtual recurrence
			{
				//error_log(__METHOD__.'() deleting #'.$org_recurrence['id']);
				$bo->delete($org_recurrence['id']);	// might fail because of permissions
			}
			else	// virtual recurrence
			{
				//error_log(__METHOD__.'() delete virtual exception '.$org_recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$org_recurrence['recurrence']));
				$bo->update_status($master, $org_recurrence, $org_recurrence['recurrence']);
			}
		}
		//foreach($events as $n => $event) error_log(__METHOD__." $n after: ".array2string($event));
	}

	/**
	 * Handle delete request for an event
	 *
	 * If current user has no right to delete the event, but is an attendee, we reject the event for him.
	 *
	 * @todo remove (non-virtual) exceptions, if series master gets deleted
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function delete(&$options,$id)
	{
		$return_no_access = true;	// to allow to check if current use is a participant and reject the event for him
		if (!is_array($event = $this->_common_get_put_delete('DELETE',$options,$id,$return_no_access)) || !$return_no_access)
		{
			if (!$return_no_access)
			{
				$ret = isset($event['participants'][$this->bo->user]) &&
					$this->bo->set_status($event,$this->bo->user,'R') ? true : '403 Forbidden';
				if ($this->debug) error_log(__METHOD__."(,$id) return_no_access=$return_no_access, event[participants]=".array2string($event['participants']).", user={$this->bo->user} --> return $ret");
				return $ret;
			}
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
		if ($this->debug > 1) error_log("bo-ical read  :$id:");
		$event = $this->bo->read($id, null, true, 'server');
		if (!($retval = $this->bo->check_perms(EGW_ACL_FREEBUSY, $event, 0, 'server'))) return $retval;
		if (!$this->bo->check_perms(EGW_ACL_READ, $event, 0, 'server'))
		{
			$this->bo->clear_private_infos($event, array($this->bo->user, $event['owner']));
		}
		return $event;
	}

	/**
	 * Query ctag for calendar
	 *
	 * @return string
	 */
	public function getctag($path,$user)
	{
		$ctag = $GLOBALS['egw']->contenthistory->getLastChange('calendar');

		if ($this->debug > 1) error_log(__FILE__.'['.__LINE__.'] '.__METHOD__. "($path)[$user] = $ctag");
		
		return 'EGw-'.$ctag.'-wGE';
	}

	/**
	 * Get the etag for an entry, reimplemented to include the participants and stati in the etag
	 *
	 * @param array/int $event array with event or cal_id
	 * @return string/boolean string with etag or false
	 */
	function get_etag($entry)
	{
		if (!is_array($entry))
		{
			if (!$this->bo->check_perms(EGW_ACL_FREEBUSY, $entry, 0, 'server')) return false;
			$entry = $this->read($entry, null, true, 'server');
		}
		$etag = $entry['id'].':'.$entry['etag'];

		// use new MAX(modification date) of egw_cal_user table (deals with virtual exceptions too)
		if (isset($entry['max_user_modified']))
		{
			$modified = max($entry['max_user_modified'], $entry['modified']);			
		}
		else
		{
			$modified = max($this->bo->so->max_user_modified($entry['id']), $entry['modified']);
		}
		$etag .= ':' . $modified;
		// include exception etags into our own etag, if exceptions are included
		if ($this->client_shared_uid_exceptions && !empty($entry['uid']) &&
			$entry['recur_type'] != MCAL_RECUR_NONE && $entry['recur_exception'])
		{
			$events =& $this->bo->search(array(
				'query' => array('cal_uid' => $entry['uid']),
				'filter' => 'owner',  // return all possible entries
				'daywise' => false,
				'enum_recuring' => false,
				'date_format' => 'server',
			));
			foreach($events as $k => &$recurrence)
			{
				if ($recurrence['reference'])	// ignore series master
				{
					$etag .= ':'.substr($this->get_etag($recurrence),4,-4);
				}
			}
		}
		//error_log(__METHOD__ . "($entry[id] ($entry[etag]): $entry[title] --> etag=$etag");
		return 'EGw-'.$etag.'-wGE';
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
		if ($acl == EGW_ACL_READ)
		{
			// we need at least EGW_ACL_FREEBUSY to get some information
			$acl = EGW_ACL_FREEBUSY;
		}
		return $this->bo->check_perms($acl,$event,0,'server');
	}

	/**
	 * Add the privileges of the current user
	 *
	 * @param array $props=array() regular props by the groupdav handler
	 * @return array
	 */
	static function current_user_privilege_set(array $props=array())
	{
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::DAV,'current-user-privilege-set',
			array(HTTP_WebDAV_Server::mkprop(groupdav::DAV,'privilege',
				array(
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'read',''),
					HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'read-free-busy',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'read-current-user-privilege-set',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'bind',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'unbind',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-post',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-post-vevent',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-respond',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-respond-vevent',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-deliver',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'schedule-deliver-vevent',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'write',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'write-properties',''),
					HTTP_WebDAV_Server::mkprop(groupdav::DAV,'write-content',''),
				))));
		return $props;
	}

	/**
	 * Add extra properties for calendar collections
	 *
	 * @param array $props=array() regular props by the groupdav handler
	 * @param string $displayname
	 * @param string $base_uri=null base url of handler
	 * @return array
	 */
	static function extra_properties(array $props=array(), $displayname, $base_uri=null)
	{
		// calendar description
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-description',$displayname);
		/*
		// BOX URLs of the current user
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'schedule-inbox-URL',
			array(HTTP_WebDAV_Server::mkprop(self::DAV,'href',$base_uri.'/calendar/')));
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'schedule-outbox-URL',
			array(HTTP_WebDAV_Server::mkprop(groupdav::DAV,'href',$base_uri.'/calendar/')));
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'schedule-default-calendar-URL',
			array(HTTP_WebDAV_Server::mkprop(groupdav::DAV,'href',$base_uri.'/calendar/')));
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'dropbox-home-URL',
			array(HTTP_WebDAV_Server::mkprop(groupdav::DAV,'href',$base_uri.'/calendar/')));
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'notifications-URL',
			array(HTTP_WebDAV_Server::mkprop(groupdav::DAV,'href',$base_uri.'/calendar/')));
		*/
		// email of the current user, see caldav-sheduling draft
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-address-set',array(
			HTTP_WebDAV_Server::mkprop('href','MAILTO:'.$GLOBALS['egw_info']['user']['email']),
			HTTP_WebDAV_Server::mkprop('href',$base_uri.'/principals/users/'.$GLOBALS['egw_info']['user']['account_lid'].'/'),
			HTTP_WebDAV_Server::mkprop('href','urn:uuid:'.$GLOBALS['egw_info']['user']['account_lid'])));
		//$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-address-set',array(
		//	HTTP_WebDAV_Server::mkprop('href','MAILTO:'.$GLOBALS['egw_info']['user']['email'])));
		// supported components, currently only VEVENT
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'supported-calendar-component-set',array(
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VCALENDAR')),
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VTIMEZONE')),
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VEVENT')),
		//	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VTODO')),	// not yet supported
		));
		$props[] = HTTP_WebDAV_Server::mkprop('supported-report-set',array(
			HTTP_WebDAV_Server::mkprop('supported-report',array(
				HTTP_WebDAV_Server::mkprop('report',
					HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-multiget',''))))));
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'supported-calendar-data',array(
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-data', array('content-type' => 'text/calendar', 'version'=> '2.0')),
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-data', array('content-type' => 'text/x-calendar', 'version'=> '1.0'))));
		$props[] = HTTP_WebDAV_Server::mkprop(groupdav::ICAL,'calendar-color','#0040A0FF'); // TODO: make it configurable
		//$props[] = HTTP_WebDAV_Server::mkprop(groupdav::CALENDARSERVER,'publish-url',array(
		//	HTTP_WebDAV_Server::mkprop('href',$base_uri.'/calendar/')));

		//$props = self::current_user_privilege_set($props);
		return $props;
	}

	/**
	 * Get the handler and set the supported fields
	 *
	 * @return calendar_ical
	 */
	private function _get_handler()
	{
		$handler = new calendar_ical();
		$handler->setSupportedFields('GroupDAV',$this->agent);
		if ($this->debug > 1) error_log("ical Handler called:" . $this->agent);
		return $handler;
	}
}

