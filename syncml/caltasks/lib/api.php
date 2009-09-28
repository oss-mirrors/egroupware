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
    'args' => array('content', 'contentType','id'),
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
 * Returns an array of GUIDs for all events and tasks that the current user is
 * authorized to see.
 *
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs for all events and tasks the user can access.
 */
function _egwcaltaskssync_list($filter='')
{
	$guids = array();
	$boCalendar = new calendar_bo();
	$boInfolog = new infolog_bo();

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

	#1 search through the calendar
	$searchFilter = array (
		'start'   => date('Ymd', $startDate),
		'end'     => date('Ymd', $endDate),
		'filter'  => 'all',
		'daywise' => false,
		'enum_recuring' => false,
		'enum_groups' => true,
	);

	$events =& $boCalendar->search($searchFilter);

	Horde::logMessage('SymcML: egwcaltaskssync list found: '. count($events) .' events', __FILE__, __LINE__, PEAR_LOG_DEBUG);

	foreach((array)$events as $event) {
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


	#2 search for tasks(infolog)
	$searchFilter = array (
		'order'		=> 'info_datemodified',
		'sort'		=> 'DESC',
		// filter my: entries user is responsible for, filter own: entries the user own or is responsible for
		'filter'	=> 'my',
		// todo add a filter to limit how far back entries from the past get synced
		'col_filter'	=> Array (
			'info_type'	=> 'task',
		),
	);

	$tasks =& $boInfolog->search($searchFilter);

	Horde::logMessage('SymcML: egwcaltaskssync list found: '. count($tasks) .' tasks', __FILE__, __LINE__, PEAR_LOG_DEBUG);

	foreach((array)$tasks as $task) {
		$guids[] = 'infolog_task-' . $task['info_id'];
	}

	return $guids;
}

/**
 * Returns an array of GUIDs for notes that have had $action happen
 * since $timestamp.
 *
 * @param string  $action     The action to check for - add, modify, or delete.
 * @param integer $timestamp  The time to start the search.
 * @param string  $type       The type of the content.
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs matching the action and time criteria.
 */
function &_egwcaltaskssync_listBy($action, $timestamp, $type, $filter='')
{
	Horde::logMessage("SymcML: egwcaltaskssync listBy action: $action timestamp: $timestamp filter: $filter",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);
	$state = &$_SESSION['SyncML.state'];
	$allChangedCalendarItems = $state->getHistory('calendar', $action, $timestamp);
	$allChangedTasksItems	= $state->getHistory('infolog_task', $action, $timestamp);
	Horde::logMessage("SymcML: egwcaltaskssync getHistory('calendar and infolog_task', $action, $timestamp)",
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	$vcal = new Horde_iCalendar;
	$boCalendar = new calendar_bo();
	$boInfolog = new infolog_bo();
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];

	$show_rejected = $GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected'];
	$ids = $guids = array();

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

	Horde::logMessage("SymcML: egwcaltasksync listBy startDate=$startDate, endDate=$endDate",
    	__FILE__, __LINE__, PEAR_LOG_DEBUG);

	// query the calendar, to check if we are a participants in these changed events
	if($action == 'delete')
	{
		$guids = $allChangedCalendarItems;

		// Delete all exceptions of deleted series events
		foreach($allChangedCalendarItems as $guid) {
			$recur_exceptions = $state->getGUIDExceptions($type, $guid);
			$guids = array_merge($guids, $recur_exceptions);
		}

		$allChangedCalendarItems = $state->getHistory('calendar', 'modify', $timestamp);
		$allChangedCalendarItems =  array_unique($allChangedCalendarItems + $guids);
		foreach($allChangedCalendarItems as $guid) {
			$ids[] = $state->get_egwId($guid);
		}
		// read all events in one go, and check if the user participats
		if (count($ids) && ($events =& $boCalendar->read($ids))) {
			foreach((array)$events as $event) {
				//Horde::logMessage("SymcML: egwcalendarsync check participation for $event[id] / $event[title]",
				//	__FILE__, __LINE__, PEAR_LOG_DEBUG);
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

		$deletedTasksItems = $allChangedTasksItems;
	    // Add all changed items for which I'm no longer responsible
	    $allChangedTasksItems = $state->getHistory('infolog_task', 'modify', $timestamp);
	    foreach($allChangedTasksItems as $guid) {
		    $uid = $state->get_egwId($guid);

		    // check whether I am no longer responsible for a task
		    if (($info =& $boInfolog->read($uid))
				    && !$boInfolog->is_responsible($info)
				    || !count($info['info_responsible'])
				    	&& $user != $info['info_owner'])
			{
			    $deletedTasksItems[] = $guid;
		    }
	    }
		return $guids + $deletedTasksItems;
	}


	// get the calendar id's for all these items
	foreach($allChangedCalendarItems as $guid) {
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

	foreach ($allChangedTasksItems as $guid) {
		$uid = $state->get_egwId($guid);

		// check READ rights too and return false if none
		// for filter my = all items the user is responsible for:
		if (($info =& $boInfolog->read($uid))
			&& ($user == $info['info_owner']
			&& !count($info['info_responsible']))
			|| $boInfolog->is_responsible($info))
		{
			$guids[] = $guid;
		}
	}

	return $guids;
}

/**
 * Import a memo represented in the specified contentType.
 *
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/plain
 *                             text/x-vnote
 * @param string $guid         (optional) The guid of a collision entry.
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcaltaskssync_import($content, $contentType, $guid = null)
{
	Horde::logMessage("SymcML: egwcaltaskssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	$boCalendar = new calendar_boupdate();
	$boInfolog = new infolog_bo();


	$taskID = -1; // default for new entry

	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'])) {
		if (!$guid) {
			$guid = _egwcaltaskssync_search($content, $contentType, null);
		}
		if (preg_match('/infolog_task-(\d+)/', $guid, $matches)) {
			Horde::logMessage("SymcML: egwcaltaskssync import conflict found for " . $matches[1],
				__FILE__, __LINE__, PEAR_LOG_DEBUG);
			// We found a conflicting entry on the server, let's make it a duplicate
			if (($conflict =& $boInfolog->read($matches[1]))) {
				$conflict['info_cat'] = $GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'];
				if (!empty($conflict['info_uid'])) {
					$conflict['info_uid'] = 'DUP-' . $conflict['info_uid'];
				}
				// the EGW's item gets a new id, to keep the subtasks attached to the client's entry
				$boInfolog->write($conflict);
			}
		} else if (preg_match('/calendar-(\d+)(:(\d+))?/', $guid, $matches)) {
			Horde::logMessage("SymcML: egwcaltaskssync import conflict found for " . $matches[1],
				__FILE__, __LINE__, PEAR_LOG_DEBUG);
			// We found a matching entry. Are we allowed to change it?
			if ($boCalendar->check_perms(EGW_ACL_EDIT, $matches[1]))
			{
				// We found a conflicting entry on the server, let's make it a duplicate
				if (($conflict =& $boCalendar->read($matches[1])))
				{
					$cat_ids = explode(",", $conflict['category']);   //existing categories
					$conflict_cat = $GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'];
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

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			if(strrpos($content, 'BEGIN:VTODO')) {
				$infolog_ical	= new infolog_ical();
				$infolog_ical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
				$taskID = $infolog_ical->importVTODO($content, $taskID);
				$type = 'infolog_task';
			} else {
				$boical	= new calendar_ical();
				$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
				$taskID = $boical->importVCal($content, $taskID);
				$type = 'calendar';
			}
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($taskID, 'PEAR_Error')) {
		return $taskID;
	}

	if(!$taskID || $taskID == -1) {
 	 	return false;
	}

	$guid = $type .'-' . $taskID;
	Horde::logMessage("SymcML: egwcaltaskssync imported: $guid",
			__FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $guid;
}

/**
 * Search a memo represented in the specified contentType.
 * used for SlowSync to check / rebuild content_map.
 *
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/plain
 *                             text/x-vnote
 * @param string $contentid    the contentid read from contentmap we are expecting the content to be
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcaltaskssync_search($content, $contentType, $contentid)
{
	Horde::logMessage("SymcML: egwcaltaskssync search content: $content contenttype: $contentType contentid: $contentid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			if(strrpos($content, 'BEGIN:VTODO')) {
				$infolog_ical = new infolog_ical();
				$infolog_ical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
				$id =  $infolog_ical->searchVTODO($content,$state->get_egwID($contentid));
				$type =  'infolog_task';
			} else {
				$boical	= new calendar_ical();
				$boical->setSupportedFields($deviceInfo['manufacturer'], $deviceInfo['model']);
				$id	=  $boical->search($content,$state->get_egwID($contentid));
				$type =  'calendar';
			}
			Horde::logMessage('SymcML: egwcaltaskssync search searched for type: '. $type, __FILE__, __LINE__, PEAR_LOG_DEBUG);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($id, 'PEAR_Error')) return $id;

	if(!$id) {
		Horde::logMessage('SymcML: egwcaltaskssync search nothing found',
			__FILE__, __LINE__, PEAR_LOG_DEBUG);
		return false;
	} else {
		$id = $type . '-' . $id;

		Horde::logMessage('SymcML: egwcaltaskssync search found: '. $id,
			__FILE__, __LINE__, PEAR_LOG_DEBUG);
		return $id;
	}
}

/**
 * Export a memo, identified by GUID, in the requested contentType.
 *
 * @param string $guid         Identify the memo to export.
 * @param mixed  $contentType  What format should the data be in?
 *                             Either a string with one of:
 *                              'text/plain'
 *                              'text/x-vnote'
 *                             or an array with options:
 *                             'ContentType':  as above
 *                             'ENCODING': (optional) character encoding
 *                                         for strings fields
 *                             'CHARSET':  (optional) charset. Like UTF-8
 *
 * @return string  The requested data.
 */
function _egwcaltaskssync_export($guid, $contentType)
{
	$state	= &$_SESSION['SyncML.state'];
  	$deviceInfo = $state->getClientDeviceInfo();
  	$_id = $state->get_egwId($guid);
  	$parts = preg_split('/:/', $_id);
  	$taskID = $parts[0];
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


	Horde::logMessage("SymcML: egwcaltaskssync export guid: $guid contenttype: ".$contentType,
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	if(strrpos($guid, 'infolog_task') !== false) {
		Horde::logMessage("SymcML: egwcaltaskssync export exporting tasks",
			__FILE__, __LINE__, PEAR_LOG_DEBUG);
        $infolog_ical    = new infolog_ical($clientProperties);
		$infolog_ical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

		switch ($contentType) {
			case 'text/x-vcalendar':
				return $infolog_ical->exportVTODO($taskID, '1.0');

			case 'text/vcalendar':
			case 'text/calendar':
				return $infolog_ical->exportVTODO($taskID, '2.0');

			default:
				return PEAR::raiseError(_("Unsupported Content-Type."));
		}
	} else {
		Horde::logMessage("SymcML: egwcaltaskssync export exporting event",
			__FILE__, __LINE__, PEAR_LOG_DEBUG);
		$boical	= new calendar_ical($clientProperties);
		$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

		switch ($contentType) {
			case 'text/x-vcalendar':
				return $boical->exportVCal($taskID,'1.0', 'PUBLISH', $recur_date);

			case 'text/vcalendar':
			case 'text/calendar':
				return $boical->exportVCal($taskID,'2.0', 'PUBLISH', $recur_date);

			default:
				return PEAR::raiseError(_("Unsupported Content-Type."));
		}
	}
}

/**
 * Delete a memo identified by GUID.
 *
 * @param string | array $guid  Identify the note to delete, either a
 *                              single GUID or an array.
 *
 * @return boolean  Success or failure.
 */
function _egwcaltaskssync_delete($guid)
{
	$state = &$_SESSION['SyncML.state'];
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwcaltaskssync_delete($g);
			if (is_a($result, 'PEAR_Error')) return $result;
		}
		return true;
	}

	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	Horde::logMessage("SymcML: egwcaltaskssync delete id: ".$state->get_egwId($guid),
		__FILE__, __LINE__, PEAR_LOG_DEBUG);

	if(strrpos($guid, 'infolog_task') !== false) {
		Horde::logMessage("SymcML: egwcaltaskssync delete deleting task",
			__FILE__, __LINE__, PEAR_LOG_DEBUG);

		$boInfolog = new infolog_bo();
		return $boInfolog->delete($state->get_egwId($guid));
	} else {
		Horde::logMessage("SymcML: egwcaltaskssync delete deleting event",
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
}

/**
 * Replace the memo identified by GUID with the content represented in
 * the specified contentType.
 *
 * @param string $guid         Idenfity the memo to replace.
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/plain
 *                             text/x-vnote
 * @param string  $type        The type of the content.
 * @param boolean $merge       merge data instead of replace
 *
 * @return boolean  Success or failure.
 */
function _egwcaltaskssync_replace($guid, $content, $contentType, $type, $merge=false)
{
	Horde::logMessage("SymcML: egwcaltaskssync replace guid: $guid content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if (is_array($contentType))
	{
		$contentType = $contentType['ContentType'];
	}

	$state = &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();
	$_id = $state->get_egwId($guid);
  	$parts = preg_split('/:/', $_id);
  	$taskID = $parts[0];
  	$recur_date = (isset($parts[1]) ? $parts[1] : 0);

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			if(strrpos($guid, 'infolog_task') !== false) {
				Horde::logMessage("SymcML: egwcaltaskssync replace replacing task",
					__FILE__, __LINE__, PEAR_LOG_DEBUG);
				$infolog_ical	= new infolog_ical();
				$infolog_ical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

				return $infolog_ical->importVTODO($content, $taskID, $merge);
			} else {
				Horde::logMessage("SymcML: egwcaltaskssync replace replacing event",
					__FILE__, __LINE__, PEAR_LOG_DEBUG);
				$boical	= new calendar_ical();
				$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
				$calendarId = $boical->importVCal($content, $taskID, null, $merge, $recur_date);
				if ($recur_date && $_id != $calendarId) {
					Horde::logMessage("SymcML: egwcalendarsync replace propagated guid: $guid to calendar-$calendarId",
						__FILE__, __LINE__, PEAR_LOG_DEBUG);

					// The pseudo exception was propagated to a real exception
					$ts = $state->getChangeTS($type, 'calendar-' . $taskID);
					$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $taskID, 'modify', $ts);
					$ts = $state->getServerAnchorLast($type) + 1;
					$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $_id, 'delete', $ts);
					$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar', $calendarId, 'modify', $ts);
				}
				return $calendarId;
			}

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

}
