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
    'args' => array('guid', 'content', 'contentType', 'merge'),
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
function _egwnotessync_list($filter='')
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
 * @param string  $type       The type of the content.
 * @param string  $filter     The filter expression the client provided.
 *
 * @return array  An array of GUIDs matching the action and time criteria.
 */
function &_egwnotessync_listBy($action, $timestamp, $type, $filter)
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
	foreach($allChangedItems as $guid) {
		$uid = $state->get_egwId($guid);

		if(($info = $infolog_bo->read($uid)) &&		// checks READ rights too and returns false if none
			// for filter my = all items the user is responsible for:
			//($user == $info['info_owner'] && !count($info['info_responsible']) || in_array($user,$info['info_responsible'])))
			// for filter own = all items the user own or is responsible for:
			($user == $info['info_owner']
				|| in_array($user,$info['info_responsible']))) {
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
 * @param string $guid         (optional) The guid of a collision entry.
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwnotessync_import($content, $contentType, $guid = null)
{
	Horde::logMessage("SymcML: egwnotessync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	$noteId = -1; // default for new entry

	if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'])) {
		if (!$guid) {
			$guid = _egwnotessync_search($content, $contentType, null);
		}
		if (preg_match('/infolog_note-(\d+)/', $guid, $matches)) {
			Horde::logMessage("SymcML: egwnotessync import conflict found for " . $matches[1], __FILE__, __LINE__, PEAR_LOG_DEBUG);
			// We found a conflicting entry on the server, let's make it a duplicate
			if ($conflict = ExecMethod2('infolog.infolog_bo.read', $matches[1])) {
				$conflict['info_cat'] = $GLOBALS['egw_info']['user']['preferences']['syncml']['infolog_conflict_category'];
				if (!empty($conflict['info_uid'])) {
					$conflict['info_uid'] = 'DUP-' . $conflict['info_uid'];
				}
				ExecMethod2('infolog.infolog_bo.write', $conflict);
			}
		}
	}

	switch ($contentType)
	{
		case 'text/plain':
		case 'text/x-vnote':
			$infolog_ical = new infolog_ical();
			$noteId = $infolog_ical->importVNOTE($content, $contentType, $noteId);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync import treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			$noteId = $infolog_sif->addSIF($content, $noteId, 'note');
			error_log("Done add note: noteId=$noteId");
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($noteId, 'PEAR_Error'))
	{
		return $noteId;
	}

	if(!$noteId || $noteId == -1) {
  		return false;
  	}

	$guid = 'infolog_note-' . $noteId;
	Horde::logMessage("SymcML: egwnotessync imported: $guid",
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
	Horde::logMessage("SymcML: egwnotessync export guid: $guid contenttype: ".$contentType, __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = $_SESSION['SyncML.state'];

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

	$noteId = $state->get_egwId($guid);

	switch ($contentType) {
		case 'text/x-vnote':
		case 'text/plain':
			$infolog_ical = new infolog_ical($clientProperties);
			return $infolog_ical->exportVNOTE($noteId, $contentType);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync export treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			if($note = $infolog_sif->getSIF($noteId, 'note')) {
				return $note;
			} else {
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
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwnotessync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		return true;
	}

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
 * @param boolean $merge       merge data instead of replace
 *
 * @return boolean  Success or failure.
 */
function _egwnotessync_replace($guid, $content, $contentType, $merge=false)
{
	Horde::logMessage("SymcML: egwtaskssync replace content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state = $_SESSION['SyncML.state'];

	if (is_array($contentType)) {
		$contentType = $contentType['ContentType'];
	}

	$noteId = $state->get_egwId($guid);

	switch ($contentType) {
		case 'text/plain':
		case 'text/x-vnote':
			$infolog_ical = new infolog_ical();
			return $infolog_ical->importVNOTE($content, $contentType, $noteId, $merge);
			break;

		case 'text/x-s4j-sifc':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
			Horde::logMessage("SyncML: egwnotessync replace treating bad task content-type '$contentType' as if is was 'text/x-s4j-sifn'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifn':
			$infolog_sif	= new infolog_sif();
			return $infolog_sif->addSIF($content, $noteId, 'note', $merge);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}
