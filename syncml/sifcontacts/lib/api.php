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
function _egwsifcontactssync_list() {
	$guids = array();
	
	$bocontacts = CreateObject('addressbook.bocontacts');

	$allContacts = $bocontacts->search('', TRUE);

	foreach((array)$allContacts as $contact) {
		$guids[] = $GLOBALS['egw']->common->generate_uid('contacts',$contact['id']);
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
function &_egwsifcontactssync_listBy($action, $timestamp) {
	// todo
	// check for acl
	
	#Horde::logMessage("SymcML: egwsifcontactssync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$allChangedItems = $GLOBALS['egw']->contenthistory->getHistory('contacts', $action, $timestamp);

	if($action != 'delete') {
		$boAddressBook = CreateObject('addressbook.boaddressbook');
		$readAbleItems = array();

		// check if we have access to the changed data
		// need to get improved in the future
		foreach($allChangedItems as $guid) {
			$uid = $GLOBALS['egw']->common->get_egwId($guid);
			if($boAddressBook->check_perms($uid, PHPGW_ACL_READ)) {
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
function _egwsifcontactssync_import($content, $contentType, $notepad = null) {
	#error_log("SymcML: egwsifcontactssync import content: ".base64_decode($ccontent)." contentType: $contentType");
	#Horde::logMessage("SymcML: egwsifcontactssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state			= $_SESSION['SyncML.state'];
	$deviceInfo		= $state->getClientDeviceInfo();

	
	switch ($contentType) {
		case 'text/x-vcard':
			$vcaladdressbook	=& CreateObject('addressbook.vcaladdressbook',true);
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

			$contactId		= $vcaladdressbook->addVCard($content,-1);
			break;

		case 'text/x-s4j-sifc':
			$sifaddressbook		=& CreateObject('addressbook.sifaddressbook');
			$contactId = 		$sifaddressbook->addSIF($content,-1);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($contactId, 'PEAR_Error')) {
		return $contactId;
	}

	#Horde::logMessage("SymcML: egwsifcontactssync import imported: ".$GLOBALS['phpgw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	return $GLOBALS['egw']->common->generate_uid('contacts',$contactId);
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
function _egwsifcontactssync_search($content, $contentType)
{
	#error_log("SymcML: egwsifcontactssync search content contentType: $contentType");
	#Horde::logMessage("SymcML: egwsifcontactssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state			= $_SESSION['SyncML.state'];
	$deviceInfo		= $state->getClientDeviceInfo();
	
	switch ($contentType) {
		case 'text/x-s4j-sifc':
			$sifaddressbook		=& CreateObject('addressbook.sifaddressbook');
			$contactId = 		$sifaddressbook->search($content);
			break;
			
		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
	
	if (is_a($contactId, 'PEAR_Error')) {
		return $contactId;
	}

	#error_log("SymcML: egwsifcontactssync search found: $contactId");
	#Horde::logMessage("SymcML: egwsifcontactssync import imported: ".$GLOBALS['phpgw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if(!$contactId) {
		Horde::logMessage("SymcML: egwcontactssync search found nothing ", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return false;
	} else {
		Horde::logMessage("SymcML: egwcontactssync search found: ".$GLOBALS['egw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
		return $GLOBALS['egw']->common->generate_uid('contacts',$contactId);
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
function _egwsifcontactssync_export($guid, $contentType)
{
	#Horde::logMessage("-- SymcML: egwsifcontactssync export guid: $guid contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}
	
	$state		= $_SESSION['SyncML.state'];
	$deviceInfo	= $state->getClientDeviceInfo();

	$sifaddressbook	=& CreateObject('addressbook.sifaddressbook');
	$contactID	= $GLOBALS['phpgw']->common->get_egwId($guid);
	
	switch ($contentType) {
		case 'text/x-s4j-sifc':
			if($sifcard = $sifaddressbook->getSIF($contactID)) {
				return $sifcard;
			} else {
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
function _egwsifcontactssync_delete($guid)
{
	// Handle an arrray of GUIDs for convenience of deleting multiple
	// contacts at once.
	if (is_array($guid)) {
		foreach ($guid as $g) {
			$result = _egwsifcontactssync_delete($g);
			if (is_a($result, 'PEAR_Error')) {
				return $result;
			}
		}
		
		return true;
	}
	
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
	
	return ExecMethod('addressbook.boaddressbook.delete',$GLOBALS['egw']->common->get_egwId($guid));
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
function _egwsifcontactssync_replace($guid, $content, $contentType)
{
	#Horde::logMessage("SymcML: egwsifcontactssync replace guid: $guid content: $content", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	#error_log("SymcML: egwsifcontactssync replace guid: $guid content: $ccontent contentType: $contentType");
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}

	$state		= $_SESSION['SyncML.state'];
	$deviceInfo	= $state->getClientDeviceInfo();

	$contactID = $GLOBALS['phpgw']->common->get_egwId($guid);

	switch ($contentType) {
		case 'text/x-vcard':
			$vcaladdressbook	=& CreateObject('addressbook.vcaladdressbook',True);
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$result = $vcaladdressbook->addVCard($content,$contactID);
    			
    			return $result;
    			
    			break;
    			
		case 'text/x-s4j-sifc':
			#$tmpfname = tempnam('/tmp/sync/contents','sifcontact_');
			#$handle = fopen($tmpfname, "w");
			#fwrite($handle, base64_decode($content));
			#fclose($handle);

			$sifaddressbook		=& CreateObject('addressbook.sifaddressbook');
			$result = $sifaddressbook->addSIF($content,$contactID);

			#error_log("SymcML: egwsifcontactssync replace result: $result");
			
			return $result;
			
			break;

    		default:
    			return PEAR::raiseError(_("Unsupported Content-Type."));
    	}
}

