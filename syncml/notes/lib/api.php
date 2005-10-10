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
	
	$notes =& ExecMethod('infolog.boinfolog.search',$searchFilter);
	
	foreach((array)$notes as $note)
	{
		$guids[] = $GLOBALS['egw']->common->generate_uid('infolog_note',$note['info_id']);
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
	
	$allChangedItems = $GLOBALS['egw']->contenthistory->getHistory('infolog_note', $action, $timestamp);
	
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
	Horde::logMessage("SymcML: egwnotessync import content: ... contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
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
	$botranslation	=& CreateObject('phpgwapi.translation');
	
	switch ($contentType) {
		case 'text/plain':
			$content = $botranslation->convert($content,'utf-8');
			$noteId = ExecMethod('infolog.boinfolog.write',array('info_des' => $content, 'info_type' => 'note'));
			
			break;

		case 'text/x-vnote':
			require_once(EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php');

			
			// Create new note.
			$vNote = Horde_iCalendar::newComponent('vnote', $container);
			
			if (!$vNote->parsevCalendar($content)) {
				return PEAR::raiseError(_("There was an error importing the vNote data."));
			}
			$vNoteValues = $vNote->getAllAttributes();
			
			$note['info_type'] = 'note';
			foreach($vNoteValues as $vNoteRow)
			{
				#if(is_array($vNoteRow))
				#{
				#	foreach($value as $key2 => $value2)
				#	{
				#		Horde::logMessage("SymcML: egwnotessync import vnote $key2 => $value2", __FILE__, __LINE__, PEAR_LOG_DEBUG);
				#	}
				#}
				switch($vNoteRow['name'])
				{
					case 'BODY':
						$note['info_des'] = $vNoteRow['value'];
						break;
					case 'SUMMARY':
						$note['info_subject'] = $vNoteRow['value'];
						break;
				}
			}

			$noteId = ExecMethod('infolog.boinfolog.write',$note);

			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($noteId, 'PEAR_Error')) {
		return $noteId;
	}

	#Horde::logMessage("SymcML: egwnotessync import imported: ".$GLOBALS['egw']->common->generate_uid('infolog',$noteId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $GLOBALS['egw']->common->generate_uid('infolog_note',$noteId);
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
	
	$botranslation =& CreateObject('phpgwapi.translation');
	
	switch ($contentType) {
		case 'text/plain':
			if($note = ExecMethod('infolog.boinfolog.read',$GLOBALS['egw']->common->get_egwId($guid)))
			{
				$utf8EncodedNote = $botranslation->convert(trim($note['info_des']),$GLOBALS['egw']->translation->charset(),'utf-8');

				return $utf8EncodedNote;
			}
			else
			{
				return PEAR::raiseError(_("Access Denied"));
			}

			break;
			
		case 'text/x-vnote':
			if($note = ExecMethod('infolog.boinfolog.read',$GLOBALS['egw']->common->get_egwId($guid)))
			{
				require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar/vnote.php';
			
				// Create the new iCalendar container.
				$vNote = &new Horde_iCalendar_vnote();
				$vNote->setAttribute('VERSION', '1.1');
				#$vNote->setAttribute('PRODID', '-//The Horde Project//Mnemo //EN');
				#$vNote->setAttribute('METHOD', 'PUBLISH');
				#$vNote->setAttribute('BODY',$botranslation->convert(trim($note['info_des']),$GLOBALS['egw']->translation->charset(),'utf-8'));
				$vNote->setAttribute('BODY','ballo');
				$vNote->setAttribute('SUMMARY','sallo');
				#$vNote->setAttribute('DCREATED','20050609T042643');
				#$vNote->setAttribute('LAST-MODIFIED','20050609T082941');
				// Create a new vNote.
				#$vNote = $storage->toiCalendar($memo, $iCal);
			
				// Set encoding options for all string values. For vNotes,
				// just BODY.
				#$params['ENCODING'] = 'QUOTED-PRINTABLE';
				#$vNote->setParameter('BODY', $params);
			
				return $vNote->exportvCalendar();
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
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// notes at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwnotessync_delete($guid);
			if (is_a($result, 'PEAR_Error')) {
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
	
	return ExecMethod('infolog.boinfolog.delete',$GLOBALS['egw']->common->get_egwId($guid));
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

	#$memo = $storage->getByGUID($guid);
	#if (is_a($memo, 'PEAR_Error')) {
	#	return $memo;
	#}
	#
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	
	$botranslation	=& CreateObject('phpgwapi.translation');
	
	$infoId = $GLOBALS['egw']->common->get_egwId($guid);
	
	switch ($contentType) {
		case 'text/plain':
			$content = $botranslation->convert($content,'utf-8');
			$result = ExecMethod('infolog.boinfolog.write',array('info_des' => $content, 'info_type' => 'note', 'info_id' => $infoId));
			
			return $result;

		case 'text/x-vnote':
			require_once(EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php');
			// Create new note.
			$vNote = Horde_iCalendar::newComponent('vnote', $container);
			
			if (!$vNote->parsevCalendar($content)) {
				return PEAR::raiseError(_("There was an error importing the vNote data."));
			}
			$vNoteValues = $vNote->getAllAttributes();
			
			$note['info_type'] = 'note';
			$note['info_id'] = $infoId;
			foreach($vNoteValues as $vNoteRow)
			{
				switch($vNoteRow['name'])
				{
					case 'BODY':
						$note['info_des'] = $vNoteRow['value'];
						break;
					case 'SUMMARY':
						$note['info_subject'] = $vNoteRow['value'];
						break;
				}
			}

			$result = ExecMethod('infolog.boinfolog.write',$note);
			return $result;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}
