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
    'args' => array('guid', 'content', 'contentType'),
    'type' => 'boolean'
);


/**
 * Returns an array of GUIDs for all notes that the current user is
 * authorized to see.
 *
 * @return array  An array of GUIDs for all notes the user can access.
 */
function _egwnotessync_list()
{
	$guids = array();

	#Horde::logMessage("SymcML: egwnotessync list ", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$searchFilter = array
	(
		'order'		=> 'info_datemodified',
		'sort'		=> 'DESC',
		'filter'    => 'own',
		'col_filter'	=> Array
		(
			'info_type'	=> 'note',
		),
	);

	$notes =& ExecMethod('infolog.infolog_bo.search',$searchFilter);

	foreach((array)$notes as $note)
	{
		$guids[] = 'infolog_note-'.$note['info_id'];
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
function &_egwnotessync_listBy($action, $timestamp)
{
	#Horde::logMessage("SymcML: egwnotessync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
  $state = $_SESSION['SyncML.state'];
	$allChangedItems = $state->getHistory('infolog_note', $action, $timestamp);

	if($action == 'delete')
	{
		return $allChangedItems;	// InfoLog has no further info about deleted entries
	}
	$infolog_bo = new infolog_bo();
	$user = $GLOBALS['egw_info']['user']['account_id'];

	$readAbleItems = array();
	foreach($allChangedItems as $guid)
	{
		$uid = $state->get_egwId($guid);

		if(($info = $infolog_bo->read($uid)) &&		// checks READ rights too and returns false if none
			// for filter my = all items the user is responsible for:
			//($user == $info['info_owner'] && !count($info['info_responsible']) || in_array($user,$info['info_responsible'])))
			// for filter own = all items the user own or is responsible for:
			($user == $info['info_owner'] || in_array($user,$info['info_responsible'])))
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
function _egwnotessync_import($content, $contentType, $notepad = null)
{
	Horde::logMessage("SymcML: egwnotessync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
#    global $prefs;
#	require_once dirname(__FILE__) . '/base.php';
#	require_once 'Horde/History.php';
#	$history = &Horde_History::singleton();
#
#    /* Make sure we have a valid notepad and permissions to edit
#     * it. */
#    if (empty($notepad)) {
#        $notepad = Mnemo::getDefaultNotepad(PERMS_EDIT);
#    }
#
#    if (!array_key_exists($notepad, Mnemo::listNotepads(false, PERMS_EDIT))) {
#        return PEAR::raiseError(_("Permission Denied"));
#    }
#
#    /* Create a Mnemo_Driver instance. */
#    require_once MNEMO_BASE . '/lib/Driver.php';
#    $storage = &Mnemo_Driver::singleton($notepad);
#
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
		case 'text/plain':
		case 'text/x-vnote':
			$infolog_ical = new infolog_ical();
			$noteId = $infolog_ical->importVNOTE($content, $contentType);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync import treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			$noteId = $infolog_sif->addSIF($content,-1,'note');
			error_log("Done add note: noteId=$noteId");
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($noteId, 'PEAR_Error'))
	{
		return 'infolog_note-' . $noteId;
	}

	if(!$noteId) {
  		return false;
  	}

	#Horde::logMessage("SymcML: egwnotessync import imported: ".$GLOBALS['egw']->common->generate_uid('infolog',$noteId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return 'infolog_note-' . $noteId;
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
function _egwnotessync_search($content, $contentType, $contentid)
{

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
		case 'text/x-vnote':
		case 'text/plain':
			$infolog_ical = new infolog_ical();
			$noteId	= $infolog_ical->searchVNOTE($content, $contentType, $state->get_egwID($contentid));
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync search treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			$noteId = $infolog_sif->searchSIF($content,'note', $state->get_egwID($contentid));
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($noteId, 'PEAR_Error'))
	{
		return 'infolog_note-' . $noteId;
	}

	#Horde::logMessage("SymcML: egwsifnotessync import imported: ".$GLOBALS['egw']->common->generate_uid('infolog_note',$noteId), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if(!$noteId)
	{
		return false;
	}

	return 'infolog_note-' . $noteId;
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
function _egwnotessync_export($guid, $contentType)
{
	  $state = $_SESSION['SyncML.state'];
	  Horde::logMessage("SymcML: egwnotessync export guid: $guid contenttype: ".$contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);
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

	$noteId = $state->get_egwId($guid);

	switch ($contentType)
	{
		case 'text/x-vnote':
		case 'text/plain':
			$infolog_ical = new infolog_ical();
			return $infolog_ical->exportVNOTE($noteId, $contentType);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync export treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			if($note = $infolog_sif->getSIF($noteId, 'note'))
			{
				return $note;
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
function _egwnotessync_delete($guid)
{
	$state = $_SESSION['SyncML.state'];
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// notes at once.
	if (is_array($guid))
	{
		foreach ($guid as $g)
		{
			$result = _egwnotessync_delete($g);
			if (is_a($result, 'PEAR_Error'))
			{
				return $result;
			}
		}
		return true;
	}

	#$memo = $storage->getByGUID($guid);
	#if (is_a($memo, 'PEAR_Error')) {
	#	return $memo;
	#}
	#
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	#

	return ExecMethod('infolog.infolog_bo.delete',$state->get_egwId($guid));
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
function _egwnotessync_replace($guid, $content, $contentType)
{
	#Horde::logMessage("SymcML: egwnotessync replace guid: $guid", __FILE__, __LINE__, PEAR_LOG_DEBUG);
  $state		= $_SESSION['SyncML.state'];
	#$memo = $storage->getByGUID($guid);
	#if (is_a($memo, 'PEAR_Error')) {
	#	return $memo;
	#}
	#
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}

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

	$noteId = $state->get_egwId($guid);

	switch ($contentType)
	{
		case 'text/plain':
		case 'text/x-vnote':
			$infolog_ical = new infolog_ical();
			return $infolog_ical->importVNOTE($content, $contentType, $noteId);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync replace treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			return $infolog_sif->addSIF($content, $infoId, 'note');
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}
