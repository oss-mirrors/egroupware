<?php
/**
 * eGroupWare - SyncML
 *
 * SyncML Infolog eGroupWare Datastore API for Horde
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package syncml
 * @subpackage infolog
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
 * Returns an array of GUIDs for all notes that the current user is
 * authorized to see.
 *
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs for all notes the user can access.
 */
function _egwtaskssync_list($filter='')
{
	$guids = array();
	$boInfolog = new infolog_bo();

	$searchFilter = array
	(
		'order'		=> 'info_datemodified',
		'sort'		=> 'DESC',
		'filter'    => 'my',	// filter my: entries user is responsible for,
								// filter own: entries the user own or is responsible for

		// todo add a filter to limit how far back entries from the past get synced
		'col_filter'	=> Array (
			'info_type'	=> 'task',
		),
	);

	$tasks =& $boInfolog->search($searchFilter);

	Horde::logMessage("SymcML: egwtaskssync list found: " . count($tasks), __FILE__, __LINE__, PEAR_LOG_DEBUG);

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
function &_egwtaskssync_listBy($action, $timestamp, $type, $filter='')
{
	#Horde::logMessage("SymcML: egwtaskssync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = &$_SESSION['SyncML.state'];
	$allChangedItems = $state->getHistory('infolog_task', $action, $timestamp);
	$boInfolog = new infolog_bo();
	$user = (int) $GLOBALS['egw_info']['user']['account_id'];

	if($action == 'delete') {
		$deletedItems = $allChangedItems;
	    // Add all changed items for which I'm no longer responsible
	    $allChangedItems = $state->getHistory('infolog_task', 'modify', $timestamp);
	    foreach($allChangedItems as $guid) {
		    $uid = $state->get_egwId($guid);

		    // check whether I am no longer responsible for a task
		    if (($info =& $boInfolog->read($uid))
				    && !$boInfolog->is_responsible($info)
				    || !count($info['info_responsible'])
				    	&& $user != $info['info_owner'])
			{
			    $deletedItems[] = $guid;
		    }
	    }
		return $deletedItems;
	}

	$readableItems = array();

	foreach ($allChangedItems as $guid) {
		$uid = $state->get_egwId($guid);

		// check READ rights too and return false if none
		// for filter my = all items the user is responsible for:
		if (($info =& $boInfolog->read($uid))
			&& ($user == $info['info_owner']
			&& !count($info['info_responsible']))
			|| $boInfolog->is_responsible($info))
		{
			$readableItems[] = $guid;
		}
	}

	return $readableItems;
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
function _egwtaskssync_import($content, $contentType, $guid = null)
{
	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	$boInfolog = new infolog_bo();
	$taskID = -1; // default for new entry

	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'])) {
		if (!$guid) {
			$guid = _egwtaskssync_search($content, $contentType, null, true);
		}
		if (preg_match('/infolog_task-(\d+)/', $guid, $matches)) {
			Horde::logMessage("SymcML: egwtaskssync import conflict found for " . $matches[1], __FILE__, __LINE__, PEAR_LOG_DEBUG);
			// We found a conflicting entry on the server, let's make it a duplicate
			if (($conflict =& $boInfolog->read($matches[1]))) {
				$conflict['info_cat'] = $GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'];
				if (!empty($conflict['info_uid'])) {
					$conflict['info_uid'] = 'DUP-' . $conflict['info_uid'];
				}
				$boInfolog->write($conflict);
			}
		}
	}

	$state                  = &$_SESSION['SyncML.state'];
	$deviceInfo             = $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			$infolog_ical = new infolog_ical();
			$infolog_ical->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			$taskID = $infolog_ical->importVTODO($content, $taskID);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwtaskssync import treating bad task content-type '$contentType' as if is was 'text/x-s4j-sift'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sift':
			$infolog_sif	= new infolog_sif();
			$infolog_sif->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			$taskID = $infolog_sif->addSIF($content, $taskID, 'task');
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

	$guid = 'infolog_task-' . $taskID;
	Horde::logMessage("SymcML: egwtaskssync imported: $guid",
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
 * @param boolean $relax=false relaxed matching (lesser fields)
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwtaskssync_search($content, $contentType, $contentid, $relax=false)
{
	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	Horde::logMessage("SymcML: egwtaskssync search content: $content contenttype: $contentType contentid: $contentid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state		= &$_SESSION['SyncML.state'];
	$deviceInfo	= $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
		case 'text/calendar':
			$infolog_ical = new infolog_ical();
			$infolog_ical->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			$taskID	= $infolog_ical->searchVTODO($content, $state->get_egwID($contentid), $relax);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwtaskssync search treating bad task content-type '$contentType' as if is was 'text/x-s4j-sift'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sift':
			$infolog_sif	= new infolog_sif();
			$infolog_sif->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			$taskID = $infolog_sif->searchSIF($content,'task', $state->get_egwID($contentid), $relax);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($taskID, 'PEAR_Error')) {
		return 'infolog_task-' . $taskID;
	}

	if(!$taskID) {
		return false;
	}

	return 'infolog_task-' . $taskID;
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
		if (is_array($contentType['Properties'])) {
			$clientProperties = &$contentType['Properties'];
		} else {
			$clientProperties = array();
		}
		$contentType = $contentType['ContentType'];
	} else {
		$clientProperties = array();
	}

	Horde::logMessage("SymcML: egwtaskssync export guid: $guid contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state		= &$_SESSION['SyncML.state'];
	$taskID		= $state->get_egwId($guid);
	$deviceInfo	= $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/x-vcalendar':
			$infolog_ical = new infolog_ical($clientProperties);
			$infolog_ical->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			return $infolog_ical->exportVTODO($taskID, '1.0');

		case 'text/calendar':
		case 'text/vcalendar':
			$infolog_ical = new infolog_ical($clientProperties);
			$infolog_ical->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			return $infolog_ical->exportVTODO($taskID, '2.0');

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwtaskssync export treating bad task content-type '$contentType' as if is was 'text/x-s4j-sift'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sift':
			$infolog_sif	= new infolog_sif();
			$infolog_sif->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			if($task = $infolog_sif->getSIF($taskID, 'task')) {
				return $task;
			}
			return PEAR::raiseError(_("Access Denied"));

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
			if (is_a($result, 'PEAR_Error')) return $result;
		}
		return true;
	}

	$boInfolog = new infolog_bo();
	$state	= &$_SESSION['SyncML.state'];
	return $boInfolog->delete($state->get_egwId($guid));
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
function _egwtaskssync_replace($guid, $content, $contentType, $type, $merge=false)
{
	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	Horde::logMessage("SymcML: egwtaskssync replace content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state		= &$_SESSION['SyncML.state'];
	$taskID		= $state->get_egwId($guid);
	$deviceInfo	= $state->getClientDeviceInfo();

	switch ($contentType) {
		case 'text/calendar':
		case 'text/x-vcalendar':
			$infolog_ical = new infolog_ical();
			$infolog_ical->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			return $infolog_ical->importVTODO($content, $taskID, $merge);

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sifn':
			Horde::logMessage("SyncML: egwtaskssync replace treating bad task content-type '$contentType' as if is was 'text/x-s4j-sift'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sift':
			$infolog_sif	= new infolog_sif();
			$infolog_sif->setSupportedFields($deviceInfo['model'], $deviceInfo['softwareVersion']);
			return $infolog_sif->addSIF($content, $taskID, 'task', $merge);

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}


function _egwtaskssync_getSyncProfile()
{
	$syncProfile = 0;

	$state = &$_SESSION['SyncML.state'];
	$deviceInfo = $state->getClientDeviceInfo();

	Horde::logMessage("SymcML: egwtaskssync remote device: ". $deviceInfo['model'], __FILE__, __LINE__, PEAR_LOG_DEBUG);

	switch($deviceInfo['model']) {
		case 'SySync Client PalmOS PRO':
			$syncProfile = 1;
			break;
	}

	return $syncProfile;
}
