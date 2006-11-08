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
function _egwtaskssync_list()
{
	$guids = array();

	Horde::logMessage("SymcML: egwtaskssync list ", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	$searchFilter = array
	(
		'order'		=> 'info_datemodified',
		'sort'		=> 'DESC',
		'filter'    => 'my',		// filter my: entries user is responsible for, filter own: entries the user own or is responsible for
		// todo add a filter to limit how far back entries from the past get synced
		'col_filter'	=> Array
		(
			'info_type'	=> 'task',
		),
	);
	
	$tasks = ExecMethod('infolog.boinfolog.search',$searchFilter);
	Horde::logMessage("SymcML: egwtaskssync list found: ".count($tasks), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	foreach((array)$tasks as $task)
	{
		$guids[] = $GLOBALS['egw']->common->generate_uid('infolog_task',$task['info_id']);
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
function &_egwtaskssync_listBy($action, $timestamp)
{
	#Horde::logMessage("SymcML: egwnotessync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	$allChangedItems = $GLOBALS['egw']->contenthistory->getHistory('infolog_task', $action, $timestamp);
	
	if($action == 'delete')
	{
		return $allChangedItems;	// InfoLog has no further info about deleted entries
	}
	$boInfolog =& CreateObject('infolog.boinfolog');
	$user = $GLOBALS['egw_info']['user']['account_id'];

	$readAbleItems = array();
	foreach($allChangedItems as $guid)
	{
		$uid = $GLOBALS['egw']->common->get_egwId($guid);

		if(($info = $boInfolog->read($uid)) &&		// checks READ rights too and returns false if none
			// for filter my = all items the user is responsible for:
			($user == $info['info_owner'] && !count($info['info_responsible']) || in_array($user,$info['info_responsible'])))
			// for filter own = all items the user own or is responsible for:
			//($user == $info['info_owner'] || in_array($user,$info['info_responsible'])))
		{
			$readAbleItems[] = $guid;
		}
	}
	return $readAbleItems;
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
function _egwtaskssync_import($content, $contentType, $notepad = null)
{
	switch ($contentType) {
		case 'text/calendar':
		case 'text/x-vcalendar':
			$vcalInfolog	=& CreateObject('infolog.vcalinfolog');

			$taskID = $vcalInfolog->importVTODO($content);

			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($taskID, 'PEAR_Error')) {
		return $taskID;
	}

	#Horde::logMessage("SymcML: egwnotessync import imported: ".$GLOBALS['egw']->common->generate_uid('infolog',$noteId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $GLOBALS['egw']->common->generate_uid('infolog_task',$taskID);
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
function _egwtaskssync_search($content, $contentType)
{
	switch ($contentType) {
		case 'text/x-vcalendar':
			$vcalInfolog	=& CreateObject('infolog.vcalinfolog');
			$taskID 	=  $vcalInfolog->searchVTODO($content);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($taskID, 'PEAR_Error')) {
		return $taskID;
	}

	#error_log("SymcML: egwsiftaskssync search found: $taskID");
	#Horde::logMessage("SymcML: egwsiftaskssync import imported: ".$GLOBALS['egw']->common->generate_uid('infolog_task',$taskID), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if(!$taskID) {
		return false;
	} else {
		return $GLOBALS['egw']->common->generate_uid('infolog_task',$taskID);
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
function _egwtaskssync_export($guid, $contentType)
{
	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}

	Horde::logMessage("SymcML: egwtaskssync export guid: $guid contenttype: ". $contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	#$syncProfile	= _egwcalendarsync_getSyncProfile();
	$taskID	= $GLOBALS['egw']->common->get_egwId($guid);
	
	switch ($contentType) {
		case 'text/calendar':
			$vcalInfolog    =& CreateObject('infolog.vcalinfolog');
			return $vcalInfolog->exportVTODO($taskID, '2.0');
			
			break;
		
		case 'text/x-vcalendar':
			$vcalInfolog    =& CreateObject('infolog.vcalinfolog');
			return $vcalInfolog->exportVTODO($taskID, '1.0');
			
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
function _egwtaskssync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwtaskssync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		
		return true;
	}
	
	return ExecMethod('infolog.boinfolog.delete',$GLOBALS['phpgw']->common->get_egwId($guid));
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
function _egwtaskssync_replace($guid, $content, $contentType)
{
	Horde::logMessage("SymcML: egwtaskssync replace content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	$taskID = $GLOBALS['egw']->common->get_egwId($guid);


	switch ($contentType) {
		case 'text/x-vcalendar':
			$vcalInfolog	=& CreateObject('infolog.vcalinfolog');

			return $vcalInfolog->importVTODO($content, $taskID);

			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}


function _egwtaskssync_getSyncProfile()
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
