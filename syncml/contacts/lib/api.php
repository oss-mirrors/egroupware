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
function _egwcontactssync_list()
{
	$guids = array();

	#Horde::logMessage("SymcML: egwcontactssync list ", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	// hardcode your search creteria here
	$criteria = array();

	$filter = array();
	if ($GLOBALS['egw_info']['user']['preferences']['addressbook']['hide_accounts'])
	{
		$filter['account_id'] = null;
	}

	// hardcode your filter here
	//$filter['cat_id'] = '!215';
	$allContacts = ExecMethod2('addressbook.addressbook_bo.search',$criteria,True,'','','',False,'AND',false,$filter);

	#Horde::logMessage("SymcML: egwcontactssync list ", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$guids = array();
	foreach((array)$allContacts as $contact)
	{
    #Horde::logMessage("SymcML: egwcontactssync list generate id for: ". $contact['id'], __FILE__, __LINE__, PEAR_LOG_DEBUG);
    #Horde::logMessage("SymcML: egwcontactssync list generate id for: ". print_r($contact, true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	  $guids[] = "contacts-".$contact['id'];
    
	}

	#Horde::logMessage("SymcML: egwcontactssync list found ids: ". print_r($guids, true), __FILE__, __LINE__, PEAR_LOG_DEBUG);

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
function &_egwcontactssync_listBy($action, $timestamp) {

	#Horde::logMessage("SymcML: egwcontactssync listBy action: $action timestamp: $timestamp", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	$state = $_SESSION['SyncML.state'];

	$allChangedItems = (array)$state->getHistory('contacts', $action, $timestamp);
	#Horde::logMessage('SymcML: egwcontactssync listBy $allChangedItems: '. print_r($allChangedItems,true), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	$allReadAbleItems = (array)_egwcontactssync_list();
	#Horde::logMessage('SymcML: egwcontactssync listBy $allReadAbleItems: '. print_r($allReadAbleItems,true), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	$allClientItems = (array)$state->_getClientItems($state->_currentTargetURI);
	#Horde::logMessage('SymcML: egwcontactssync listBy $allClientItems: '. print_r($allClientItems,true), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	switch ($action) {
		case 'delete' :
			// filters may have changed, so we need to calculate which
			// items are to delete from client cause they are not longer is list.
			return $allChangedItems + array_diff($allClientItems, $allReadAbleItems);

		case 'add' :
			// - added items may not need to be added, cause they are filtered out.
			// - filters or entries may have changed, so that more entries
			//   pass the filter and need to be added on the client.
			return array_unique(array_intersect($allChangedItems, $allReadAbleItems)+ array_diff($allReadAbleItems, $allClientItems));

		case 'modify' :
			// - modified entries, which not (longer) pass filters must not be send.
			// - modified entries which are not at the client must not be send, cause
			//   the 'add' run will send them!
			return array_intersect($allChangedItems, $allReadAbleItems, $allClientItems);

		default:
			return new PEAR_Error("$action is not defined!");
	}
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
//error_log("_egwcontactssync_import");
	#error_log("SymcML: egwcontactssync import content: ".base64_decode($ccontent)." contentType: $contentType");
	Horde::logMessage("SymcML: egwcontactssync import content: $content contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state			= $_SESSION['SyncML.state'];
	$deviceInfo		= $state->getClientDeviceInfo();
	#error_log(print_r($deviceInfo, true));


	switch ($contentType) {
		case 'text/x-vcard':
			$vcaladdressbook	= new addressbook_vcal();
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

			$contactId		= $vcaladdressbook->addVCard($content, false);
			break;

		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			error_log("[_egwcontactssync_import] Treating bad contact content-type '".$contentType."' as if is was 'text/x-s4j-sifc'");
		case 'text/x-s4j-sifc':
			$sifaddressbook		= new addressbook_sif();
			$contactId = 		$sifaddressbook->addSIF($content);
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($contactId, 'PEAR_Error')) {
		return 'contacts-' .$contactId;
	}
	
	if(!$contactId) {
  		return false;
  	}

	#Horde::logMessage("SymcML: egwcontactssync import imported: ".$GLOBALS['egw']->common->generate_uid('contacts',$contactId), __FILE__, __LINE__, PEAR_LOG_DEBUG);
	//return $GLOBALS['egw']->common->generate_uid('contacts',$contactId);
  return 'contacts-' .$contactId;
}

/**
 * Search a memo represented in the specified contentType,
 * used for SlowSync to check / rebuild content_map.
 *
 * @param string $content      The content of the memo.
 * @param string $contentType  What format is the data in? Currently supports:
 *                             text/plain
 *                             text/x-vnote
 * @param string $contentid    the contentid read from contentmap we are expecting the content to be
 *
 *
 * @return string  The new GUID, or false on failure.
 */
function _egwcontactssync_search($content, $contentType, $contentid)
{
	Horde::logMessage("SymcML: egwcontactssync search content: $content contenttype: $contentType contentid: $contentid", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	$state			= $_SESSION['SyncML.state'];
	$deviceInfo		= $state->getClientDeviceInfo();


	switch ($contentType) {
		case 'text/x-vcard':
			$vcaladdressbook = new addressbook_vcal();
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

			$contactId = $vcaladdressbook->search($content,$state->get_egwID($contentid));
			break;

		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			#Horde::logMessage("SymcML: egwcontactssync search content: Treating bad contact content-type '$contentType' as if it was 'text/x-s4j-sifc'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifc':
			$sifaddressbook	= new addressbook_sif();
			$contactId = $sifaddressbook->search($content,$state->get_egwID($contentid));
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}

	if (is_a($contactId, 'PEAR_Error')) {
		return 'contacts-' .$contactId;
	}

	#error_log("SymcML: egwcontactssync search found: $contactId");
	Horde::logMessage("SymcML: egwcontactssync search found: contacts-".$contactId, __FILE__, __LINE__, PEAR_LOG_DEBUG);
	if(!$contactId) {
		return false;
	} else {
		return 'contacts-' . $contactId;
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
function _egwcontactssync_export($guid, $contentType)
{
	Horde::logMessage("SymcML: egwcontactssync export guid: $guid contenttype: $contentType", __FILE__, __LINE__, PEAR_LOG_DEBUG);

	if (is_array($contentType)) {
		$options = $contentType;
		$contentType = $options['ContentType'];
		unset($options['ContentType']);
	} else {
		$options = array();
	}

	$state		= $_SESSION['SyncML.state'];
	$deviceInfo	= $state->getClientDeviceInfo();

	$contactID		= $state->get_egwId($guid);

	switch ($contentType)
	{
		case 'text/x-vcard':
			$vcaladdressbook	= new addressbook_vcal();
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);

			if($vcard = $vcaladdressbook->getVCard($contactID))
			{
				return $vcard;
			}
			else
			{
				return PEAR::raiseError(_("Access Denied"));
			}

			break;

		case 'text/x-s4j-sift':
		case 'text/x-s4j-sife':
		case 'text/x-s4j-sifn':
			#Horde::logMessage("SyncML: egwcontactssync_export Treating bad contact content-type '$contentType' as if is was 'text/x-s4j-sifc'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
			/* fall through */
		case 'text/x-s4j-sifc':
			$sifaddressbook	= new addressbook_sif();
			$contactID	= $state->get_egwId($guid);
			if($sifcard = $sifaddressbook->getSIF($contactID))
			{
				return $sifcard;
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
function _egwcontactssync_delete($guid)
{
	$state = $_SESSION['SyncML.state'];
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
	Horde::logMessage("SymcML: egwcontactssync delete guid: $guid egwid: ". $state->get_egwId($guid), __FILE__, __LINE__, PEAR_LOG_DEBUG);

	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_DELETE))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}
 
	return ExecMethod('addressbook.addressbook_vcal.delete', $state->get_egwId($guid));

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

	#Horde::logMessage("SymcML: egwcontactssync replace guid: $guid with content: $content", __FILE__, __LINE__, PEAR_LOG_DEBUG);
	#if (!array_key_exists($memo['memolist_id'], Mnemo::listNotepads(false, PERMS_EDIT))) {
	#	return PEAR::raiseError(_("Permission Denied"));
	#}

	$state		= $_SESSION['SyncML.state'];
	$deviceInfo	= $state->getClientDeviceInfo();

	$contactID	= $state->get_egwId($guid);

	switch ($contentType) {
		case 'text/x-vcard':
			$vcaladdressbook = new addressbook_vcal();
			$vcaladdressbook->setSupportedFields($deviceInfo['manufacturer'],$deviceInfo['model']);
			$result = $vcaladdressbook->addVCard($content,$contactID);
			return $result;
			break;

		case 'text/x-s4j-sife':
		case 'text/x-s4j-sift':
		case 'text/x-s4j-sifn':
			#Horde::logMessage("SymcML: egwcontactssync replace treating bad contact content-type '$contentType' as if is was 'text/x-s4j-sifc'", __FILE__, __LINE__, PEAR_LOG_DEBUG);
		case 'text/x-s4j-sifc':
			#$tmpfname = tempnam('/tmp/sync/contents','sifcontact_');
			#$handle = fopen($tmpfname, "w");
			#fwrite($handle, base64_decode($content));
			#fclose($handle);

			$sifaddressbook		= new addressbook_sif();
			$result = $sifaddressbook->addSIF($content,$contactID);
			return $result;
			break;

		default:
			return PEAR::raiseError(_("Unsupported Content-Type."));
	}
}

