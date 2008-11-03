<?php
/**
 * Mnemo external API interface.
 *
 * $Horde: mnemo/lib/api.php,v 1.52 2004/09/14 04:27:07 chuck Exp $
 *
 * This file defines Mnemo's external API interface. Other
 * applications can interact with Mnemo through this API.
 *
 * @package Mnemo
 */

$_services['list'] = array(
    'args' => array('startDate','endDate'),
    'type' => 'stringArray'
);

$_services['listBy'] = array(
    'args' => array('action', 'timestamp'),
    'type' => 'stringArray'
);

$_services['import'] = array(
    'args' => array('content', 'contentType'),
    'type' => 'integer'
);

$_services['search'] = array(
    'args' => array('content', 'contentType'),
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
    'args' => array('guid', 'content', 'contentType'),
    'type' => 'boolean'
);


/**
 * Returns an array of GUIDs for all events and tasks that the current user is
 * authorized to see.
 *
 * @return array  An array of GUIDs for all events and tasks the user can access.
 */
function _egwcaltaskssync_list($_startDate='', $_endDate='')
{
	$guids = array();

	# 1.) search for events(calendar)
	// until it's configurable we do 1 month back and ~2 years in the future
	$startDate	= (!empty($_startDate)?$_startDate:date('Ymd',time()-2678400));
	$endDate	= (!empty($_endDate)?$_endDate:date('Ymd',time()+65000000));

	$searchFilter = array (
		'start'   => $startDate,
		'end'     => $endDate,
		'filter'  => 'all',
		'daywise' => false,
		'enum_recuring' => false,
		'enum_groups' => true,
	);

	$events =& ExecMethod('calendar.calendar_bo.search',$searchFilter);

	Horde::logMessage('SymcML: egwcaltaskssync list found: '. count($events) .' events', __FILE__, __LINE__, PEAR_LOG_DEBUG);

	foreach((array)$events as $event) {
		$guids[] = 'calendar-'.$event['id'];
	}


	# 2.) search for tasks(infolog)
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

	$tasks = ExecMethod('infolog.infolog_bo.search',$searchFilter);

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
 *
 * @return array  An array of GUIDs matching the action and time criteria.
 */
function &_egwcaltaskssync_listBy($action, $timestamp)
{
	Horde::logMessage("SymcML: egwcaltaskssync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
  $state = $_SESSION['SyncML.state'];
	$allChangedCalendarItems = $state->getHistory('calendar', $action, $timestamp);
	$allChangedTasksItems	= $state->getHistory('infolog_task', $action, $timestamp);
	Horde::logMessage("SymcML: egwcaltaskssync getHistory('calendar and infolog_task', $action, $timestamp)", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if($action == 'delete') {
		// we cant query the calendar for deleted events
		// InfoLog has no further info about deleted entries
		return $allChangedCalendarItems + $allChangedTasksItems;
	}
	// query the calendar, to check if we are a participants in these changed events
	$boCalendar =& new calendar_bo();
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];
	$show_rejected = $GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected'];

	// get the calendar id's for all these items
	$ids = $guids = array();

	foreach($allChangedCalendarItems as $guid) {
		$ids[] = $state->get_egwId($guid);
	}
	// read all events in one go, and check if the user participats
	if (count($ids) && ($events =& $boCalendar->read($ids))) {
		foreach((array)$boCalendar->read($ids) as $event) {
			Horde::logMessage("SymcML: egwcaltaskssync check participation for $event[id] / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			if (isset($event['participants'][$user]) && ($show_rejected || $event['participants'][$user] != 'R')) {
				$guids[] = $guid = 'calendar-' . $event['id'];
				Horde::logMessage("SymcML: egwcaltaskssync added id $event[id] ($guid) / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			}
		}
	}

	$infolog_bo = new infolog_bo();
	$user = $GLOBALS['egw_info']['user']['account_id'];

	foreach($allChangedTasksItems as $guid) {
		$uid = $state->get_egwId($guid);

		if(($info = $infolog_bo->read($uid)) &&		// checks READ rights too and returns false if none
			// for filter my = all items the user is responsible for:
			($user == $info['info_owner'] && !count($info['info_responsible']) || in_array($user,$info['info_responsible'])))
			// for filter own = all items the user own or is responsible for:
			//($user == $info['info_owner'] || in_array($user,$info['info_responsible'])))
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
 * @param string $notepad      (optional) The notepad to save the memo on.
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcaltaskssync_import($content, $contentType, $notepad = null)
{
	Horde::logMessage("SymcML: egwcaltaskssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = $_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			if(strrpos($content, 'BEGIN:VTODO')) {
				$infolog_ical	= new infolog_ical();
				$id = $infolog_ical->importVTODO($content);
				$type = 'infolog_task';
			} else {
				$boical	= new calendar_ical();
				$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
				$id = $boical->importVCal($content);
				$type = 'calendar';
			}
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($id, 'PEAR_Error')) {
		return $type . '-' .$id;
	}

	$guid = $type .'-' .$id;
	Horde::logMessage("SymcML: egwcaltaskssync import imported: ".$guid, __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $guid;
}

/**
 * Import a memo represented in the specified contentType.
 *
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/plain
 *                             text/x-vnote
 * @param string $notepad      (optional) The notepad to save the memo on.
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcaltaskssync_search($content, $contentType)
{
	Horde::logMessage("SymcML: egwcaltaskssync search content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			if(strrpos($content, 'BEGIN:VTODO')) {
				$infolog_ical	= new infolog_ical();
				$id 		=  $infolog_ical->searchVTODO($content);
				$type		=  'infolog_task';
			} else {
				$boical		= new calendar_ical();
				$id		=  $boical->search($content);
				$type		=  'calendar';
			}
			Horde::logMessage('SymcML: egwcaltaskssync search searched for type: '. $type, __FILE__, __LINE__, PEAR_LOG_DEBUG);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($id, 'PEAR_Error')) {
		return $type . '-' . $id;
	}

	if(!$id) {
		Horde::logMessage('SymcML: egwcaltaskssync search nothing found', __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return false;
	} else {
		$id = $type . '-' . $id;

		Horde::logMessage('SymcML: egwcaltaskssync search found: '. $id, __FILE__, __LINE__, PEAR_LOG_DEBUG);

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
	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}

	Horde::logMessage("SymcML: egwcaltaskssync export guid: $guid contenttype: ".$contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = $_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	if(strrpos($guid, 'infolog_task') !== false) {
		Horde::logMessage("SymcML: egwcaltaskssync export exporting tasks", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		$taskID	= $state->get_egwId($guid);

		switch ($contentType) {
			case 'text/x-vcalendar':
				$infolog_ical    = new infolog_ical();
				return $infolog_ical->exportVTODO($taskID,'1.0');

				break;
			default:
				return PEAR::raiseError(_("Unsupported Content-Type."));
		}
	} else {
		Horde::logMessage("SymcML: egwcaltaskssync export exporting event", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		$boical	= new calendar_ical();
		$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

		$eventID	= $state->get_egwId($guid);

		switch ($contentType) {
			case 'text/x-vcalendar':
				return $boical->exportVCal($eventID,'1.0');

				break;
			case 'text/calendar':
				return $boical->exportVCal($eventID,'2.0');

				break;
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
	$state = $_SESSION['SyncML.state'];
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwcaltaskssync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}

		return true;
	}

	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	Horde::logMessage("SymcML: egwcaltaskssync delete id: ".$state->get_egwId($guid), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if(strrpos($guid, 'infolog_task') !== false) {
		Horde::logMessage("SymcML: egwcaltaskssync delete deleting task", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return ExecMethod('infolog.infolog_bo.delete',$state->get_egwId($guid));
	} else {
		Horde::logMessage("SymcML: egwcaltaskssync delete deleting event", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		$bocalendar =& new calendar_boupdate();

		return $bocalendar->delete($state->get_egwId($guid));
	}
	#return $bocalendar->expunge();
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
 *
 * @return boolean  Success or failure.
 */
function _egwcaltaskssync_replace($guid, $content, $contentType)
{
	Horde::logMessage("SymcML: egwcaltaskssync replace guid: $guid content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	$state = $_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			if(strrpos($guid, 'infolog_task') !== false) {
				Horde::logMessage("SymcML: egwcaltaskssync replace replacing task", __FILE__, __LINE__, PEAR_LOG_DEBUG);
				$taskID = $state->get_egwId($guid);
				$infolog_ical	= new infolog_ical();

				return $infolog_ical->importVTODO($content, $taskID);
			} else {
				Horde::logMessage("SymcML: egwcaltaskssync replace replacing event", __FILE__, __LINE__, PEAR_LOG_DEBUG);
				$boical	= new calendar_ical();
				$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

				$eventID = $state->get_egwId($guid);

				return $boical->importVCal($content, $eventID);
			}
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

}
