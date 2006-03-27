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
    'args' => array(),
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
 * Returns an array of GUIDs for all notes that the current user is
 * authorized to see.
 *
 * @return array  An array of GUIDs for all notes the user can access.
 */
function _egwsifcalendarsync_list()
{
	$guids = array();

	// until it's configurable we do 1 month back and ~2 years in the future
	$startDate	= (!empty($_startDate)?$_startDate:date('Ymd',time()-2678400));
	$endDate	= (!empty($_endDate)?$_endDate:date('Ymd',time()+65000000));

	$searchFilter = array
	(
		'start'   => $startDate ,
		'end'     => $endDate ,
		'filter'  => 'all' ,
		'daywise' => false ,
		'enum_recuring' => false ,
	);
	
	$events =& ExecMethod('calendar.bocal.search',$searchFilter);
	
	foreach((array)$events as $event)
	{
		$guids[] = $GLOBALS['egw']->common->generate_uid('calendar',$event['id']);
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
function &_egwsifcalendarsync_listBy($action, $timestamp)
{
	#Horde::logMessage("SymcML: egwcalendarsync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$allChangedItems = $GLOBALS['egw']->contenthistory->getHistory('calendar', $action, $timestamp);
	#Horde::logMessage("SymcML: egwcalendarsync getHistory('calendar', $action, $timestamp)=".print_r($allChangedItems,true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if($action == 'delete')
	{
		return $allChangedItems;	// we cant query the calendar for deleted events
	}
	// query the calendar, to check if we are a participants in these changed events
	$boCalendar =& CreateObject('calendar.bocal');
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];
	$show_rejected = $GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected'];

	// get the calendar id's for all these items
	$ids = $guids = array();
	foreach($allChangedItems as $guid)
	{
		$ids[] = $GLOBALS['egw']->common->get_egwId($guid);
	}
	// read all events in one go, and check if the user participats
	if (count($ids) && ($events =& $boCalendar->read($ids)))
	{
		foreach((array)$boCalendar->read($ids) as $event)
		{
			#Horde::logMessage("SymcML: egwcalendarsync check participation for $event[id] / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			if (isset($event['participants'][$user]) && ($show_rejected || $event['participants'][$user] != 'R'))
			{
				$guids[] = $guid = $GLOBALS['egw']->common->generate_uid('calendar',$event['id']);
				#Horde::logMessage("SymcML: egwcalendarsync added id $event[id] ($guid) / $event[title]", __FILE__, __LINE__, PEAR_LOG_DEBUG);
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
function _egwsifcalendarsync_import($content, $contentType, $notepad = null)
{
	#error_log("SymcML: egwsifcalendarsync import content: ".base64_decode($ccontent)." contentType: $contentType");
	Horde::logMessage("SymcML: egwsifcalendarsync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	switch ($contentType) {
		case 'text/x-s4j-sife':
			$sifcalendar		=& CreateObject('calendar.sifcalendar');
			$eventId 		=  $sifcalendar->addSIF($content,-1);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($eventId, 'PEAR_Error')) {
		return $eventId;
	}

	#Horde::logMessage("SymcML: egwsifcalendarsync import imported: ".$GLOBALS['phpgw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $GLOBALS['egw']->common->generate_uid('calendar',$eventId);
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
function _egwsifcalendarsync_search($content, $contentType)
{
	#error_log("SymcML: egwsifcalendarsync search content contentType: $contentType");
	#Horde::logMessage("SymcML: egwsifcalendarsync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	Horde::logMessage("SymcML: egwsifcalendarsync import contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	switch ($contentType) {
		case 'text/x-s4j-sife':
			$sifcalendar		=& CreateObject('calendar.sifcalendar');
			$eventId = 		$sifcalendar->search($content);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($eventId, 'PEAR_Error')) {
		return $contactId;
	}

	#error_log("SymcML: egwsifcalendarsync search found: $contactId");
	#Horde::logMessage("SymcML: egwsifcalendarsync import imported: ".$GLOBALS['phpgw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if(!$eventId) {
		return false;
	} else {
		return $GLOBALS['egw']->common->generate_uid('calendar',$eventId);
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
function _egwsifcalendarsync_export($guid, $contentType)
{
	#Horde::logMessage("-- SymcML: egwsifcalendarsync export guid: $guid contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}
	
	$eventId	= $GLOBALS['egw']->common->get_egwId($guid);

	switch ($contentType) {
		case 'text/x-s4j-sife':
			$sifcalendar		=& CreateObject('calendar.sifcalendar');
			if($sifevent = $sifcalendar->getSIF($eventId))
			{
				return $sifevent;
			}
			else
			{
				return PEAR::raiseError(_("Access Denied"));
			}
			
			break;
		
		default:
			#Horde::logMessage("SymcML: export unsupported", __FILE__, __LINE__, PEAR_LOG_DEBUG);
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
function _egwsifcalendarsync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwsifcalendarsync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		
		return true;
	}
	
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	
	$bocalendar =& CreateObject('calendar.bocalupdate');
	
	return $bocalendar->delete($GLOBALS['egw']->common->get_egwId($guid));
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
function _egwsifcalendarsync_replace($guid, $content, $contentType)
{
	#Horde::logMessage("SymcML: egwsifcalendarsync replace guid: $guid content: $content", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	#error_log("SymcML: egwsifcalendarsync replace guid: $guid content: $ccontent contentType: $contentType");
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}

	$eventId = $GLOBALS['egw']->common->get_egwId($guid);

	switch ($contentType) {
		case 'text/x-s4j-sife':
			$sifcalendar		=& CreateObject('calendar.sifcalendar');
			$eventId 		=  $sifcalendar->addSIF($content,$eventId);
			  
			return $eventId;
			
			break;
			
    		default:
    			return PEAR::raiseError(_("Unsupported Content-Type."));
    	}
}

