<?php
/**
 * eGroupWare - SyncML
 *
 * SyncML Calendar eGroupWare Datastore API for Horde
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package syncml
 * @subpackage calendar
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @version $Id$
 */

$_services['list'] = array(
    'args' => array('filter'),
    'type' => 'stringArray'
);

$_services['listBy'] = array(
    'args' => array('action', 'timestamp', 'type', 'filter'),
    'type' => 'stringArray'
);

$_services['import'] = array(
    'args' => array('content', 'contentType'),
    'type' => 'integer'
);

$_services['search'] = array(
    'args' => array('content', 'contentType', 'id'),
    'type' => 'integer'
);

$_services['export'] = array(
    'args' => array('guid', 'contentType'),
    'type' => 'string'
);

$_services['delete'] = array(
    'args' => array('guid'),
    'type' => 'boolean'
);

$_services['replace'] = array(
    'args' => array('guid', 'content', 'contentType', 'type', 'merge'),
    'type' => 'boolean'
);


/**
 * Returns an array of GUIDs for all events that the current user is
 * authorized to see.
 *
 * @param string $_startDate='' only events after $_startDate or two year back, if empty
 * @param string $_endDate='' only events util $_endDate or two year ahead, if empty
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs for all events the user can access.
 */
function _egwcalendarsync_list($filter='')
{
	Horde::logMessage("SymcML: egwcalendarsync list filter: $filter",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	$guids = array();

	$vcal = new Horde_iCalendar;
	$boCalendar = new calendar_bo();

	$now = time();

	if (preg_match('/SINCE.*;([0-9TZ]*).*AND;BEFORE.*;([0-9TZ]*)/i', $filter, $matches)) {
		$cstartDate	= $vcal->_parseDateTime($matches[1]);
		$cendDate	= $vcal->_parseDateTime($matches[2]);
	} else {
		$cstartDate	= 0;
		$cendDate = 0;
	}
	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_past'])) {
		$period = (int)$GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_past'];
		$startDate	= $now - $period;
		$startDate = ($startDate > $cstartDate ? $startDate : $cstartDate);
	} else {
		$startDate	= ($cstartDate ? $cstartDate : ($now - 2678400));
	}
	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_future'])) {
		$period = (int)$GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_future'];
		$endDate	= $now + $period;
		$endDate	= ($cendDate && $cendDate < $endDate ? $cendDate : $endDate);
	} else {
		$endDate	= ($cendDate ? $cendDate : ($now + 65000000));
	}




    Horde::logMessage("SymcML: egwcalendarsync list startDate=$startDate, endDate=$endDate",
    	__FILE__, __LINE__, PEAR_LOG_DEBUG);

	$searchFilter = array
	(
		'start'   => date('Ymd', $startDate),
		'end'     => date('Ymd', $endDate),
		'filter'  => 'all',
		'daywise' => false,
		'enum_recuring' => false,
		'enum_groups' => true,
	);

	$events =& $boCalendar->search($searchFilter);

	foreach((array)$events as $event)
	{
		$guids[] = $guid = 'calendar-' . $event['id'];
		if ($event['recur_type'] != MCAL_RECUR_NONE)
		{
			// Check if the stati for all participants are identical for all recurrences
			$days = $boCalendar->so->get_recurrence_exceptions(&$event);

			foreach ($days as $recur_date)
			{
				if ($recur_date) $guids[] = $guid . ':' . $recur_date;
			}
		}
	}
	return $guids;
}

/**
 * Returns an array of GUIDs for events that have had $action happen
 * since $timestamp.
 *
 * @param string  $action     The action to check for - add, modify, or delete.
 * @param integer $timestamp  The time to start the search.
 * @param string  $type       The type of the content.
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs matching the action and time criteria.
 */
function &_egwcalendarsync_listBy($action, $timestamp, $type, $filter='')
{
	Horde::logMessage("SymcML: egwcalendarsync listBy action: $action timestamp: $timestamp filter: $filter",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);
	$state	= &$_SESSION['SyncML.state'];
	$allChangedItems = $state->getHistory('calendar', $action, $timestamp);
	//Horde::logMessage("SymcML: egwcalendarsync getHistory('calendar', $action, $timestamp)=".print_r($allChangedItems, true),
	//	__FILE__, __LINE__, PEAR_LOG_DEBUG);

	$vcal = new Horde_iCalendar;
	$boCalendar = new calendar_bo();

	$now = time();

	if (preg_match('/SINCE.*;([0-9TZ]*).*AND;BEFORE.*;([0-9TZ]*)/i', $filter, $matches)) {
		$cstartDate	= $vcal->_parseDateTime($matches[1]);
		$cendDate	= $vcal->_parseDateTime($matches[2]);
	} else {
		$cstartDate	= 0;
		$cendDate = 0;
	}
	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_past'])) {
		$period = (int)$GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_past'];
		$startDate	= $now - $period;
		$startDate = ($startDate > $cstartDate ? $startDate : $cstartDate);
	} else {
		$startDate	= $cstartDate;
	}
	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_future'])) {
		$period = (int)$GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_future'];
		$endDate	= $now + $period;
		$endDate	= ($cendDate && $cendDate < $endDate ? $cendDate : $endDate);
	} else {
		$endDate	= $cendDate;
	}

 	Horde::logMessage("SymcML: egwcalendarsync listBy startDate=$startDate, endDate=$endDate",
    	__FILE__, __LINE__, PEAR_LOG_DEBUG);

	// query the calendar, to check if we are a participants in these changed events
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];
	$show_rejected = $GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected'];
	$ids = $guids = array();

	if($action == 'delete')
	{
		$guids = $allChangedItems;

		// Delete all exceptions of deleted series events
		foreach($allChangedItems as $guid) {
			$recur_exceptions = $state->getGUIDExceptions($type, $guid);
			$guids = array_merge($guids, $recur_exceptions);
		}

		$allChangedItems = $state->getHistory('calendar', 'modify', $timestamp);
		$allChangedItems =  array_unique($allChangedItems + $guids);
		foreach($allChangedItems as $guid) {
			$ids[] = $state->get_egwId($guid);
		}
		// read all events in one go, and check if the user participats
		if (count($ids) && ($events =& $boCalendar->read($ids))) {
			foreach((array)$events as $event) {
				//Horde::logMessage("SymcML: egwcalendarsync check participation for $event[id] / $event[title]",
				//	__FILE__, __LINE__, PEAR_LOG_DEBUG);
				$boCalendar->enum_groups($event);
				if ((!$endDate || $event['end'] <= $endDate)
							&& $startDate <= $event['start']
						&& isset($event['participants'][$user])
						&& ($show_rejected || $event['participants'][$user] != 'R'))
				{
					if ($event['recur_type'] != MCAL_RECUR_NONE)
					{
						$guid = 'calendar-' . $event['id'];
						$recur_exceptions = $state->getGUIDExceptions($type, $guid);
						foreach ($recur_exceptions as $rexception) {
							$parts = preg_split('/:/', $rexception);
  							$recur_dates = $boCalendar->so->get_recurrence_exceptions($event);
  							if (!in_array($parts[1], $recur_dates))
  							{
  								// "status only" exception does no longer exist
  								$guids[] = $rexception;
  							}
  						}
  					}
				}
			}
		}
		return $guids;	// we cant query the calendar for deleted events
	}




	// get the calendar id's for all these items
	foreach($allChangedItems as $guid) {
		$ids[] = $state->get_egwId($guid);
	}

	// read all events in one go, and check if the user participats
	if (count($ids) && ($events =& $boCalendar->read($ids))) {
		foreach((array)$events as $event) {
			//Horde::logMessage("SymcML: egwcalendarsync check participation for $event[id] / $event[title]",
			//	__FILE__, __LINE__, PEAR_LOG_DEBUG);
			$boCalendar->enum_groups($event);
			if ((!$startDate || $startDate <= $event['start']
						&& $event['end'] <= $endDate)
					&& isset($event['participants'][$user])
					&& ($show_rejected || $event['participants'][$user] != 'R'))
			{
				$guids[] = $guid = 'calendar-' . $event['id'];
				if ($event['recur_type'] != MCAL_RECUR_NONE)
				{
					// Check if the stati for all participants are identical for all recurrences
					$days = $boCalendar->so->get_recurrence_exceptions(&$event);

					foreach ($days as $recur_date)
					{
						if ($recur_date) $guids[] = $guid . ':' . $recur_date;
					}
				}
				//Horde::logMessage("SymcML: egwcalendarsync added id $event[id] ($guid) / $event[title]",
				//	__FILE__, __LINE__, PEAR_LOG_DEBUG);
			}
		}
	}

	return $guids;
}

/**
 * Import an event represented in the specified contentType.
 *
 * @param string $content      The content of the event.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/calendar
 *                             text/x-vcalendar
 *                             text/x-s4j-sife
 * @param string $guid         (optional) The guid of a collision entry.
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcalendarsync_import($content, $contentType, $guid = null)
{
	Horde::logMessage("SymcML: egwcalendarsync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	#$syncProfile	= _egwcalendarsync_getSyncProfile();

	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	$calendarId = -1; // default for new entry

	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_conflict_category'])) {
		if (!$guid) {
			$guid = _egwcalendarsync_search($content, $contentType, null);
		}
		if (preg_match('/calendar-(\d+)(:(\d+))?/', $guid, $matches))
		{
			$boCalendar = new calendar_boupdate();
			// We found a matching entry. Are we allowed to change it?
			if ($boCalendar->check_perms(EGW_ACL_EDIT, $matches[1]))
			{
				// We found a conflicting entry on the server, let's make it a duplicate
				Horde::logMessage("SymcML: egwcalendarsync import conflict found for " . $matches[1], __FILE__, __LINE__, PEAR_LOG_DEBUG);
				if (($conflict =& $boCalendar->read($matches[1])))
				{
					$cat_ids = explode(",", $conflict['category']);   //existing categories
					$conflict_cat = $GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_conflict_category'];
					if (!in_array($conflict_cat, $cat_ids))
					{
						$cat_ids[] = $conflict_cat;
						$conflict['category'] = implode(",", $cat_ids);

					}
					if (!empty($conflict['uid'])) {
						$conflict['uid'] = 'DUP-' . $conflict['uid'];
					}
					$boCalendar->save($conflict);
				}
			}
			else
			{
				// If the user is not allowed to change this event,
				// he still may update his participaction status
				$calendarId = $matches[1];
			}
		}
	}

	$state = &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$calendarId = $boical->importVCal($content, $calendarId);
			if (preg_match('/(\d+):(\d+)/', $calendarId, $matches)) {
				// We have created a pseudo exception; date it back to this session
				$guid = 'calendar-' . $matches[1];
				$ts = $state->getSyncTSforAction($guid, 'modify');
				$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $calendarId, 'modify', $ts);
			}
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwcalendarsync import treating bad calendar content-type '$contentType' as if is was 'text/x-s4j-sife'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			$sifcalendar->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$calendarId = $sifcalendar->addSIF($content, $calendarId);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($calendarId, 'PEAR_Error')) {
		return $calendarId;
	}

	if(!$calendarId) {
  		return false;
  	}

	$guid = 'calendar-' . $calendarId;
	Horde::logMessage("SymcML: egwcalendarsync imported: $guid",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $guid;
}

/**
 * Search an event represented in the specified contentType.
 * used for SlowSync to check / rebuild content_map.
 *
 * @param string $content      The content of the event.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/calendar
 *                             text/x-vcalendar
 *                             text/x-s4j-sife
 * @param string $contentid    the contentid read from contentmap we are expecting the content to be
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcalendarsync_search($content, $contentType, $contentid)
{
	Horde::logMessage("SymcML: egwcalendarsync search content: $content contenttype: $contentType contentid: $contentid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state	= &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	if (is_array($contentType))
	{
		$contentType = $contentType['ContentType'];
	}

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$eventId = $boical->search($content,$state->get_egwID($contentid));
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwcalendarsync treating bad calendar content-type '$contentType' as if is was 'text/x-s4j-sife'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			$sifcalendar->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$eventId = $sifcalendar->search($content,$state->get_egwID($contentid));
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($eventId, 'PEAR_Error')) {
		return $eventId;
	}

	if(!$eventId) {
		return false;
	} else {
		$eventId = 'calendar-' . $eventId;
		Horde::logMessage('SymcML: egwcalendarsync search found: '. $eventId, __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return $eventId;
	}
}

/**
 * Export an event, identified by GUID, in the requested contentType.
 *
 * @param string $guid         Identify the memo to export.
 * @param mixed  $contentType  What format should the data be in?
 *                             Either a string with one of:
 *                              'text/calendar'
 *                              'text/x-vcalendar'
 *                              'text/x-s4j-sife'
 *                             or an array with options:
 *                             'ContentType':  as above
 *                             'ENCODING': (optional) character encoding
 *                                         for strings fields
 *                             'CHARSET':  (optional) charset. Like UTF-8
 *
 * @return string  The requested data.
 */
function _egwcalendarsync_export($guid, $contentType)
{
  	$state	= &$_SESSION['SyncML.state'];
  	$deviceInfo = $state->getClientDeviceInfo();
  	$_id = $state->get_egwId($guid);
  	$parts = preg_split('/:/', $_id);
  	$eventID = $parts[0];
  	$recur_date = (isset($parts[1]) ? $parts[1] : 0);

	if (is_array($contentType)) {
		if (is_array($contentType['Properties'])) {
			$clientProperties = &$contentType['Properties'];
		} else {
			$clientProperties = array();
		}
		$contentType = $contentType['ContentType'];
	} else {
		$clientProperties = array();
	}

	Horde::logMessage("SymcML: egwcalendarsync export guid: $eventID ($recur_date) contenttype:\n"
		. print_r($contentType, true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$boical	= new calendar_ical($clientProperties);
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$vcal_version = ($contentType == 'text/x-vcalendar') ? '1.0' : '2.0';
			return $boical->exportVCal($eventID, $vcal_version, 'PUBLISH', $recur_date);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwcalendarsync export treating bad calendar content-type '$contentType' as if is was 'text/x-s4j-sife'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			$sifcalendar->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			if($sifevent = $sifcalendar->getSIF($eventID))
			{
				return $sifevent;
			}
			else
			{
				return PEAR::raiseError(_("Access Denied"));
			}
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}

/**
 * Delete an event identified by GUID.
 *
 * @param string | array $guid  Identify the event to delete, either a
 *                              single GUID or an array.
 *
 * @return boolean  Success or failure.
 */
function _egwcalendarsync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// events at once.
	$state = &$_SESSION['SyncML.state'];
	if (is_array($guid))
	{
		foreach ($guid as $g) {
			$result = _egwcalendarsync_delete($g);
			if (is_a($result, 'PEAR_Error')) return $result;
		}
		return true;
	}


	Horde::logMessage("SymcML: egwcalendarsync delete id: ".$state->get_egwId($guid),
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	$boCalendar = new calendar_boupdate();
	$_id = $state->get_egwId($guid);
	$parts = preg_split('/:/', $_id);
	$eventId = $parts[0];
	$recur_date = (isset($parts[1]) ? $parts[1] : 0);
	$user = $GLOBALS['egw_info']['user']['account_id'];

	// Check if the user has at least read access to the event
	if (!($event =& $boCalendar->read($eventId))) return false;


	if (!$boCalendar->check_perms(EGW_ACL_EDIT, $eventId)
		&& isset($event['participants'][$user]))
	{
		if ($recur_date && $event['recur_type'] != MCAL_RECUR_NONE) {
			$boCalendar->set_status($event, $user, $event['participants'][$user], $recur_date);
		} else {
			// user rejects the event by deleting it from his device
			$boCalendar->set_status($eventId, $user, 'R', $recur_date);
		}
		return true;
	}

	if ($recur_date && $event['recur_type'] != MCAL_RECUR_NONE)
	{
		// Delete a "status only" exception of a recurring event
		$participants = $boCalendar->so->get_participants($event['id'], 0);
		foreach ($participants as &$participant)
		{
			if (isset($event['participants'][$participant['uid']]))
			{
				$participant['status'] = $event['participants'][$participant['uid']][0];
			}
			else
			{
				// Will be deleted from this recurrence
				$participant['status'] = 'G';
			}
		}
		foreach ($participants as $attendee)
		{
			// Set participant status back
			$boCalendar->set_status($event, $attendee['uid'], $attendee['status'], $recur_date);
		}
		return true;
	}

	return $boCalendar->delete($eventId);
}

/**
 * Replace the event identified by GUID with the content represented in
 * the specified contentType.
 *
 * @param string $guid         Idenfity the memo to replace.
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/calendar
 *                             text/x-vcalendar
 *                             text/x-s4j-sife
 * @param string  $type        The type of the content.
 * @param boolean $merge       merge data instead of replace
 *
 * @return boolean  Success or failure.
 */
function _egwcalendarsync_replace($guid, $content, $contentType, $type, $merge=false)
{
	Horde::logMessage("SymcML: egwcalendarsync replace guid: $guid content: $content contenttype: $contentType",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	if (is_array($contentType))
	{
		$contentType = $contentType['ContentType'];
	}

	$state = &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();
	$_id = $state->get_egwId($guid);
  	$parts = preg_split('/:/', $_id);
  	$eventID = $parts[0];
  	$recur_date = (isset($parts[1]) ? $parts[1] : 0);

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$calendarId = $boical->importVCal($content, $eventID, null, $merge, $recur_date);
			if ($recur_date && $_id != $calendarId) {
				Horde::logMessage("SymcML: egwcalendarsync replace propagated guid: $guid to calendar-$calendarId",
					__FILE__, __LINE__, PEAR_LOG_DEBUG);

				// The pseudo exception was propagated to a real exception
				$ts = $state->getChangeTS($type, 'calendar-' . $eventID);
				$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $eventID, 'modify', $ts);
				$ts = $state->getServerAnchorLast($type) + 1;
				$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $_id, 'delete', $ts);
				$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $calendarId, 'modify', $ts);
			}
			return $calendarId;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			error_log("[_egwsifcalendarsync_replace] Treating bad calendar content-type '".$contentType."' as if is was 'text/x-s4j-sife'");
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			$sifcalendar->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			return $sifcalendar->addSIF($content, $eventID, $merge);

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}
