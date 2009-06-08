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
    'args' => array('guid', 'content', 'contentType'),
    'type' => 'boolean'
);


/**
 * Returns an array of GUIDs for all notes that the current user is
 * authorized to see.
 *
 * @return array  An array of GUIDs for all notes the user can access.
 */
function _egwcalendarsync_list($_startDate='', $_endDate='')
{
	$guids = array();

	// until it's configurable we do 1 month back and ~2 years in the future
	$startDate	= (!empty($_startDate)?$_startDate:date('Ymd',time()-2678400));
	$endDate	= (!empty($_endDate)?$_endDate:date('Ymd',time()+65000000));

	$searchFilter = array
	(
		'start'   => $startDate,
		'end'     => $endDate,
		'filter'  => 'all',
		'daywise' => false,
		'enum_recuring' => false,
		'enum_groups' => true,
	);

	$events =& ExecMethod('calendar.calendar_bo.search',$searchFilter);

	foreach((array)$events as $event)
	{
		$guids[] = 'calendar-' . $event['id'];
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
function &_egwcalendarsync_listBy($action, $timestamp)
{
	Horde::logMessage("SymcML: egwcalendarsync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
  $state		= $_SESSION['SyncML.state'];
	$allChangedItems = $state->getHistory('calendar', $action, $timestamp);
	Horde::logMessage("SymcML: egwcalendarsync getHistory('calendar', $action, $timestamp)=".print_r($allChangedItems,true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if($action == 'delete')
	{
		return $allChangedItems;	// we cant query the calendar for deleted events
	}

	// query the calendar, to check if we are a participants in these changed events
	$boCalendar = new calendar_bo();
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];
	$show_rejected = $GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected'];

	// get the calendar id's for all these items
	$ids = $guids = array();
	foreach($allChangedItems as $guid)
	{
		$ids[] = $state->get_egwId($guid);
	}

	// read all events in one go, and check if the user participats
	if (count($ids) && ($events =& $boCalendar->read($ids)))
	{
		foreach((array)$boCalendar->read($ids) as $event)
		{
			Horde::logMessage("SymcML: egwcalendarsync check participation for $event[id] / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			if (isset($event['participants'][$user]) && ($show_rejected || $event['participants'][$user] != 'R'))
			{
				$guids[] = $guid = 'calendar-' . $event['id'];
				Horde::logMessage("SymcML: egwcalendarsync added id $event[id] ($guid) / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			}
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
function _egwcalendarsync_import($content, $contentType, $notepad = null)
{
	Horde::logMessage("SymcML: egwcalendarsync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	#$syncProfile	= _egwcalendarsync_getSyncProfile();

	if (is_array($contentType))
	{
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	}
	else
	{
		$options = array();
	}

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$state = $_SESSION['SyncML.state'];
			$deviceInfo = $state->getClientDeviceInfo();
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$calendarId = $boical->importVCal($content);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwcalendarsync import treating bad calendar content-type '$contentType' as if is was 'text/x-s4j-sife'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			$calendarId = $sifcalendar->addSIF($content,-1);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($calendarId, 'PEAR_Error'))
	{
		return 'calendar-' . $calendarId;
	}
	
	if(!$calendarId) {
  		return false;
  	}

	$guid = 'calendar-' . $calendarId;
	Horde::logMessage("SymcML: egwcalendarsync import imported: ".$guid, __FILE__, __LINE__, PEAR_LOG_DEBUG);
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
function _egwcalendarsync_search($content, $contentType, $contentid)
{
	Horde::logMessage("SymcML: egwcalendarsync search content: $content contenttype: $contentType contentid: $contentid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state			= $_SESSION['SyncML.state'];

	if (is_array($contentType))
	{
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	}
	else
	{
		$options = array();
	}

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$state = $_SESSION['SyncML.state'];
			$deviceInfo = $state->getClientDeviceInfo();
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
			$eventId = $sifcalendar->search($content,$state->get_egwID($contentid));
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($eventId, 'PEAR_Error'))
	{
		return 'calendar-' . $eventId;
	}

	if(!$eventId)
	{
		return false;
	}
	else
	{
		$eventId = 'calendar-' . $eventId;
		Horde::logMessage('SymcML: egwcalendarsync search found: '. $eventId, __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return $eventId;
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
function _egwcalendarsync_export($guid, $contentType)
{

#    require_once dirname(__FILE__) . '/base.php';
#
#    $storage = &Mnemo_Driver::singleton();
#    $memo = $storage->getByGUID($guid);
#    if (is_a($memo, 'PEAR_Error')) {
#        return $memo;
#    }
#
#    if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
#        return PEAR::raiseError(_("Permission Denied"));
#    }
#
  $state		= $_SESSION['SyncML.state'];
	if (is_array($contentType))
	{
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	}
	else
	{
		$options = array();
	}

	Horde::logMessage("SymcML: egwcalendarsync export guid: $guid contenttype: ".$contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);


	$eventID	= $state->get_egwId($guid);

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$state = $_SESSION['SyncML.state'];
			$deviceInfo = $state->getClientDeviceInfo();
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$vcal_version = ($contentType == 'text/x-vcalendar') ? '1.0' : '2.0';
			return $boical->exportVCal($eventID,$vcal_version);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwcalendarsync export treating bad calendar content-type '$contentType' as if is was 'text/x-s4j-sife'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
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
 * Delete a memo identified by GUID.
 *
 * @param string | array $guid  Identify the note to delete, either a
 *                              single GUID or an array.
 *
 * @return boolean  Success or failure.
 */
function _egwcalendarsync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	$state = $_SESSION['SyncML.state'];
	if (is_array($guid))
	{
		foreach ($guid as $g)
		{
			$result = _egwcalendarsync_delete($g);
			if (is_a($result, 'PEAR_Error'))
			{
				return $result;
			}
		}

		return true;
	}

	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	Horde::logMessage("SymcML: egwcalendarsync delete id: ".$state->get_egwId($guid), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$bocalendar = new calendar_boupdate();

	return $bocalendar->delete($state->get_egwId($guid));
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
function _egwcalendarsync_replace($guid, $content, $contentType)
{
	Horde::logMessage("SymcML: egwcalendarsync replace guid: $guid content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
  
	if (is_array($contentType))
	{
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	}
	else
	{
		$options = array();
	}
  $state = $_SESSION['SyncML.state'];
	$eventID = $state->get_egwId($guid);

	switch ($contentType)
	{
		case 'text/x-vcalendar':
		case 'text/vcalendar':
		case 'text/calendar':
			$deviceInfo = $state->getClientDeviceInfo();
			$boical	= new calendar_ical();
			$boical->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			return $boical->importVCal($content, $eventID);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			error_log("[_egwsifcalendarsync_replace] Treating bad calendar content-type '".$contentType."' as if is was 'text/x-s4j-sife'");
		case 'text/x-s4j-sife':
			$sifcalendar = new calendar_sif();
			return $sifcalendar->addSIF($content,$eventID);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}

