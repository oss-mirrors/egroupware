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
function _egwcontactssync_list()
{
	$guids = array();
	
	#Horde::logMessage("SymcML: egwcontactssync list ", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
	$allContacts = ExecMethod('addressbook.boaddressbook.read_entries');

	foreach((array)$allContacts as $contact)
	{
		$guids[] = $GLOBALS['phpgw']->common->generate_uid('contacts',$contact['id']);
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
function &_egwcontactssync_listBy($action, $timestamp)
{
	// todo
	// check for acl
	
	#Horde::logMessage("SymcML: egwcontactssync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$allChangedItems = $GLOBALS['phpgw']->contenthistory->getHistory('contacts', $action, $timestamp);

	if($action != 'delete')
	{
		$boAddressBook = CreateObject('addressbook.boaddressbook');

		// check if we have access to the changed data
		// need to get improved in the future
		foreach($allChangedItems as $guid)
		{
			$uid = $GLOBALS['phpgw']->common->get_egwId($guid);
			if($boAddressBook->check_perms($uid, PHPGW_ACL_READ))
			{
				$readAbleItems[] = $guid;
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
function _egwcontactssync_import($content, $contentType, $notepad = null)
{
	Horde::logMessage("SymcML: egwcontactssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	#/* Make sure we have a valid notepad and permissions to edit
	#* it. */
	#if (empty($notepad)) {
	#	$notepad = Mnemo::getDefaultNotepad(PERMS_EDIT);
	#}
	#
	#if (!array_key_exists($notepad, Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	
	$syncProfile	= _egwcontactssync_getSyncProfile();
	$boaddressbook	= CreateObject('addressbook.boaddressbook',True);
	
	switch ($contentType) {
		case 'text/x-vcard':
			$contactId = $boaddressbook->addVCard($content,-1,0);
			Horde::logMessage("SymcML: 2 egwcontactssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($contactId, 'PEAR_Error')) {
		return $contactId;
	}

	Horde::logMessage("SymcML: egwcontactssync import imported: ".$GLOBALS['phpgw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $GLOBALS['phpgw']->common->generate_uid('contacts',$contactId);
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
function _egwcontactssync_export($guid, $contentType)
{
#	Horde::logMessage("SymcML: egwcontactssync export guid: $guid contenttype: ".$contentType['ContentType'], __FILE__, __LINE__, PEAR_LOG_DEBUG);
	
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
	
	$syncProfile	= _egwcontactssync_getSyncProfile();
	$boaddressbook	= CreateObject('addressbook.boaddressbook',True);
	$contactID	= $GLOBALS['phpgw']->common->get_egwId($guid);
	
	switch ($contentType) {
		case 'text/x-vcard':

			if($vcard = $boaddressbook->getVCard($contactID, $syncProfile))
			{
				return $vcard;
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
function _egwcontactssync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwcontactssync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		
		return true;
	}
	
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	
	return ExecMethod('addressbook.boaddressbook.delete_entry',$GLOBALS['phpgw']->common->get_egwId($guid));
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
function _egwcontactssync_replace($guid, $content, $contentType)
{
	#Horde::logMessage("SymcML: egwcontactssync replace guid: $guid content: $content", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}

	$contactID = $GLOBALS['phpgw']->common->get_egwId($guid);
	$boaddressbook	= CreateObject('addressbook.boaddressbook',True);
    
	switch ($contentType) {
		case 'text/x-vcard':
			#Horde::logMessage("SymcML: egwcontactssync replace id: $contactId", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			#$result = ExecMethod('addressbook.boaddressbook.update_entry',$contact);
			$result = $boaddressbook->addVCard($content,$contactID,0);
    			
    			return $result;
    			
    			break;

    		default:
    			return PEAR::raiseError(_("Unsupported Content-Type."));
    	}
}


function _egwcontactssync_getSyncProfile()
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