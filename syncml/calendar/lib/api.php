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

	$startDate	= (!empty($_startDate)?$_startDate:date('Ymd',time()-2678400));
	$endDate	= (!empty($_endDate)?$_endDate:date('Ymd',time()+2678400));

	$searchFilter = array
	(
		'start'		=> $startDate,
		'end'		=> $endDate,
		'filter'	=> 'all',
		'daywise'	=> 0,
	);
	
	$events = ExecMethod('calendar.bocal.search',$searchFilter);
	
	foreach((array)$events as $event)
	{
		$guids[] = $GLOBALS['phpgw']->common->generate_uid('calendar',$event['id']);
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
	// todo
	// check for acl
	
	Horde::logMessage("SymcML: egwcalendarsync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$allChangedItems = $GLOBALS['egw']->contenthistory->getHistory('calendar', $action, $timestamp);
	Horde::logMessage("SymcML: egwcalendarsync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	if($action != 'delete')
	{
		$readAbleItems = array();
		$boCalendar = CreateObject('calendar.bocal');

		// check if we have access to the changed data
		// need to get improved in the future
		foreach($allChangedItems as $guid)
		{
			$uid = $GLOBALS['phpgw']->common->get_egwId($guid);
			Horde::logMessage("SymcML: egwcalendarsync check perms for $uid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

			if($boCalendar->check_perms(PHPGW_ACL_READ, $uid))
			{
				$readAbleItems[] = $guid;
				Horde::logMessage("SymcML: egwcalendarsync added id $uid/$guid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

			}
		}
		
		return $readAbleItems;
	}

	return $allChangedItems;
	
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

	$boical	= CreateObject('calendar.boical');
	
	#$syncProfile	= _egwcalendarsync_getSyncProfile();
	
	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			$calendarId = $boical->importVCal($content);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($calendarId, 'PEAR_Error')) {
		return $calendarId;
	}

	$guid = $GLOBALS['phpgw']->common->generate_uid('calendar',$calendarId);
	Horde::logMessage("SymcML: egwcalendarsync import imported: ".$guid, __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $guid;
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

	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}

	Horde::logMessage("SymcML: egwcalendarsync export guid: $guid contenttype: ".$contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	#$syncProfile	= _egwcalendarsync_getSyncProfile();
	$boCalendar	= CreateObject('calendar.boical');
	$eventID	= $GLOBALS['phpgw']->common->get_egwId($guid);
	
	switch ($contentType) {
		case 'text/x-vcalendar':
			#$boCalendar	= CreateObject('calendar.boicalendar');
			#return $boCalendar->export(array('l_event_id' => $eventID));
			$boCalendar	= CreateObject('calendar.boical');
			return $boCalendar->exportVCal($eventID,'1.0');
			
			break;
		case 'text/calendar':

			#$vcal = $boCalendar->read($eventID, FALSE);
			$boCalendar	= CreateObject('calendar.boical');
			return $boCalendar->exportVCal($eventID,'2.0');

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
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwcalendarsync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		
		return true;
	}
	
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	Horde::logMessage("SymcML: egwcalendarsync delete id: ".$GLOBALS['phpgw']->common->get_egwId($guid), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	$bocalendar = CreateObject('calendar.bocalupdate');
	
	return $bocalendar->delete($GLOBALS['phpgw']->common->get_egwId($guid));
	
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
	Horde::logMessage("SymcML: egwcalendarsync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$boical	= CreateObject('calendar.boical');
	$eventID = $GLOBALS['phpgw']->common->get_egwId($guid);
	
	#$syncProfile	= _egwcalendarsync_getSyncProfile();
	
	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			return $boical->importVCal($content, $eventID);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
}


function _egwcalendarsync_getSyncProfile()
{
	$syncProfile = 0;

	$state = $_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();
	
	Horde::logMessage("SymcML: egwcontactssync remote device: ". $deviceInfo['model'], __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	switch($deviceInfo['model'])
	{
		case 'SySync Client PalmOS PRO':
			$syncProfile = 1;
			break;
	}
	
	return $syncProfile;
}
