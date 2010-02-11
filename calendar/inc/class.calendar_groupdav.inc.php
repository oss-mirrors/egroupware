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
	 */
	function __construct($app,$debug=null, $base_uri=null)
	{
		parent::__construct($app,$debug,$base_uri);

		$this->bo = new calendar_boupdate();
	}

	/**
	 * Create the path for an event
	 *
	 * @param array|int $event
	 * @return string
	 */
	static function get_path($event)
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
		return '/calendar/'.$name.'.ics';
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
		if ($this->debug) error_log(__METHOD__."($path,".array2string($options).",,$user,$id)");
		$starttime = microtime(true);

		// ToDo: add parameter to only return id & etag
		$cal_filters = array(
			'users' => $user,
			'start' => time()-100*24*3600,	// default one month back -30 breaks all sync  recurrences
			'end' => time()+365*24*3600,	// default one year into the future +365
			'enum_recuring' => false,
			'daywise' => false,
			'date_format' => 'server',
		);
		if ($this->client_shared_uid_exceptions)
		{
			$cal_filters['query']['cal_reference'] = 0;
		}
		// process REPORT filters or multiget href's
		if (($id || $options['root']['name'] != 'propfind') && !$this->_report_filters($options,$cal_filters,$id))
		{
			return false;
		}
		if ($this->debug > 1) error_log(__METHOD__."($path,,,$user,$id) cal_filters=".array2string($cal_filters));

		// check if we have to return the full calendar data or just the etag's
		if (!($calendar_data = $options['props'] == 'all' && $options['root']['ns'] == groupdav::CALDAV) && is_array($options['props']))
		{
			foreach($options['props'] as $prop)
			{
				if ($prop['name'] == 'calendar-data')
				{
					$calendar_data = true;
					break;
				}
			}
		}
		$events =& $this->bo->search($cal_filters);
		if ($events)
		{
			// get all max user modified times at once
			foreach($events as &$event)
			{
				$ids[] = $event['id'];
			}
			$max_user_modified = $this->bo->so->max_user_modified($ids);

			foreach($events as &$event)
			{
				$event['max_user_modified'] = $max_user_modified[$event['id']];
				//header('X-EGROUPWARE-EVENT-'.$event['id'].': '.$event['title'].': '.date('Y-m-d H:i:s',$event['start']).' - '.date('Y-m-d H:i:s',$event['end']));
				$props = array(
					HTTP_WebDAV_Server::mkprop('getetag',$this->get_etag($event)),
					HTTP_WebDAV_Server::mkprop('getcontenttype', $this->agent != 'kde' ?
	            			'text/calendar; charset=utf-8; component=VEVENT' : 'text/calendar'),
					// getlastmodified and getcontentlength are required by WebDAV and Cadaver eg. reports 404 Not found if not set
					HTTP_WebDAV_Server::mkprop('getlastmodified', $event['modified']),
					HTTP_WebDAV_Server::mkprop('resourcetype',''),	// iPhone requires that attribute!
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
				$files['files'][] = array(
	            	'path'  => self::get_path($event),
	            	'props' => $props,
				);
			}
		}
		if ($this->debug) error_log(__METHOD__."($path) took ".(microtime(true) - $starttime).' to return '.count($files['files']).' items');
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
								//return $infolog_handler->propfind($path,$options,$files,$user,$method);
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
		if (is_null($bo)) $bo = new calendar_bo();

		$events =& $bo->search(array(
			'query' => array('cal_uid' => $uid),
			'daywise' => false,
			'date_format' => 'server',
		));
		$master = null;
		foreach($events as $k => &$recurrence)
		{
			if (!isset($master))	// first event is always the series master
			{
				$master =& $events[$k];
				//error_log('master: '.array2string($master));
				continue;	// nothing to change
			}
			if ($recurrence['id'] != $master['id'])	// real exception
			{
				//error_log('real exception: '.array2string($recurrence));
				// remove from masters recur_exception, as exception is include
				// at least Lightning "understands" EXDATE as exception from what's included
				// in the whole resource / VCALENDAR component
				// not removing it causes Lightning to remove the exception itself
				if (($k = array_search($recurrence['recurrence'],$master['recur_exception'])) !== false)
				{
					unset($master['recur_exception'][$k]);
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
			// this is a virtual excetion now (no extra event/cal_id in DB)
			//error_log('virtual exception: '.array2string($recurrence));
			$recurrence['recurrence'] = $recurrence['start'];
			$recurrence['reference'] = $master['id'];
			$recurrence['recur_type'] = MCAL_RECUR_NONE;	// is set, as this is a copy of the master
			// not for included exceptions (Lightning): $master['recur_exception'][] = $recurrence['start'];
		}
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
		if($this->debug) error_log(__METHOD__."($id, $user)".print_r($options,true));
		$return_no_access=true;	// as handled by importVCal anyway and allows it to set the status for participants
		$event = $this->_common_get_put_delete('PUT',$options,$id,$return_no_access);

		if (!is_null($event) && !is_array($event))
		{
			if($this->debug) error_log(__METHOD__.print_r($event,true).function_backtrace());
			return $event;
		}
		$handler = $this->_get_handler();
		if (!is_numeric($id) && ($foundEntries = $handler->find_event($options['content'], 'check')))
		{
			$id = array_shift($foundEntries);
		}
		if (!($cal_id = $handler->importVCal($options['content'],is_numeric($id) ? $id : -1,
			self::etag2value($this->http_if_match))))
		{
			if ($this->debug) error_log(__METHOD__."(,$id) importVCal($options[content]) returned false");
			return '403 Forbidden';
		}

		header('ETag: '.$this->get_etag($cal_id));
		if (is_null($event) || !$return_no_access)	// let lightning think the event is added
		{
			if ($this->debug) error_log(__METHOD__."(,$id,$user) cal_id=$cal_id, is_null(\$event)=".(int)is_null($event));
			header('Location: '.$this->base_uri.self::get_path($cal_id));
			return '201 Created';
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
		foreach($events as $n => $event) error_log(__METHOD__." $n before: ".array2string($event));
		$master =& $events[0];

		$bo = new calendar_boupdate();

		// get array with orginal recurrences indexed by recurrence-id
		$org_recurrences = array();
		foreach(self::get_series($master['uid'],$bo) as $event)
		{
			if ($event['recurrence'])
			{
				$org_recurrences[$event['recurrence']] = $event;
			}
		}

		// assign cal_id's to already existing recurrences and evtl. re-add recur_exception to master
		foreach($events as &$recurrence)
		{
			if ($recurrence['id'] || !$recurrence['recurrence']) continue;	// master

			// from now on we deal with exceptions
			$org_recurrence = $org_recurrences[$recurrence['recurrence']];
			if (isset($org_recurrence))	// already existing recurrence
			{
				error_log(__METHOD__.'() setting id #'.$org_recurrence['id']).' for '.$recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$recurrence['recurrence']);
				$recurrence['id'] = $org_recurrence['id'];

				// re-add (non-virtual) exceptions to master's recur_exception
				if ($recurrence['id'] != $master['id'])
				{
					error_log(__METHOD__.'() re-adding recur_exception '.$recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$recurrence['recurrence']));
					$master['recur_exception'][] = $recurrence['recurrence'];
				}
				// remove recurrence to be able to detect deleted exceptions
				unset($org_recurrences[$recurrence['recurrence']]);
			}
		}

		// delete not longer existing recurrences
		foreach($org_recurrences as $org_recurrence)
		{
			if ($org_recurrence['id'] != $master['id'])	// non-virtual recurrence
			{
				error_log(__METHOD__.'() deleting #'.$org_recurrence['id']);
				$bo->delete($org_recurrence['id']);	// might fail because of permissions
			}
			else	// virtual recurrence
			{
				error_log(__METHOD__.'() ToDO: delete virtual exception '.$org_recurrence['recurrence'].' = '.date('Y-m-d H:i:s',$org_recurrence['recurrence']));
				// todo: reset status and participants to master default
			}
		}
		foreach($events as $n => $event) error_log(__METHOD__." $n after: ".array2string($event));
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
		$return_no_access=true;	// to allow to check if current use is a participant and reject the event for him
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
		//$cal_read = $this->bo->read($id,null,false,'server');//njv: do we actually get anything
		if ($this->debug > 1) error_log("bo-ical read  :$id:");//njv:
		return $this->bo->read($id,null,false,'server');
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
			$entry = $this->read($entry);
		}
		$etag = $entry['id'].':'.$entry['etag'];

		// use new MAX(modification date) of egw_cal_user table (deals with virtual exceptions too)
		if (isset($entry['max_user_modified']))
		{
			$etag .= ':'.$entry['max_user_modified'];
		}
		else
		{
			$etag .= ':'.$this->bo->so->max_user_modified($entry['id']);
		}
		// include exception etags into our own etag, if exceptions are included
		if ($this->client_shared_uid_exceptions && !empty($entry['uid']) &&
			$entry['recur_type'] != MCAL_RECUR_NONE && $entry['recur_exception'])
		{
			$events =& $this->bo->search(array(
				'query' => array('cal_uid' => $entry['uid']),
				'daywise' => false,
				'enum_recuring' => false,
				'date_format' => 'server',
			));
			foreach($events as $k => &$recurrence)
			{
				if ($recurrence['reference'])	// ignore series master
				{
					$etag .= ':'.substr($this->get_etag($recurrence),1,-1);
				}
			}
		}
		//error_log(__METHOD__ . "($entry[id] ($entry[etag]): $entry[title] --> etag=$etag");
		return '"'.$etag.'"';
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

	/**
	 * Add extra properties for calendar collections
	 *
	 * @param array $props=array() regular props by the groupdav handler
	 * @return array
	 */
	static function extra_properties(array $props=array())
	{
		// calendaring URL of the current user
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-home-set',$_SERVER['SCRIPT_NAME'].'/');
		// email of the current user, see caldav-sheduling draft
		$props[] =	HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'calendar-user-address-set','MAILTO:'.$GLOBALS['egw_info']['user']['email']);
		// supported components, currently only VEVENT
		$props[] =	$sc = HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'supported-calendar-component-set',array(
			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VEVENT')),
//			HTTP_WebDAV_Server::mkprop(groupdav::CALDAV,'comp',array('name' => 'VTODO')),	// not yet supported
		));

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

