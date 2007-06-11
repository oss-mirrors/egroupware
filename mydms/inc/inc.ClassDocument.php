<?php

function getDocument($id)
{
	if (!is_numeric($id))
		die ("invalid documentid");
	
	$queryStr = "SELECT * FROM phpgw_mydms_Documents WHERE id = " . $id;
	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
	if (is_bool($resArr) && $resArr == false)
		return false;
	
	if (count($resArr) != 1)
		return false;

	$resArr = $resArr[0];
	$newDocument = new Document($resArr["id"], $resArr["name"], $resArr["comment"], $resArr["date"], $resArr["expires"], $resArr["owner"], $resArr["folder"], $resArr["inheritAccess"], $resArr["defaultAccess"], $resArr["locked"], $resArr["keywords"], $resArr["sequence"]);

	if($newDocument->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id'])) > M_NONE)
		return $newDocument;
	else
		return false;
}

class Document
{
	var $_id;
	var $_name;
	var $_comment;
	var $_ownerID;
	var $_folderID;
	var $_expires;
	var $_inheritAccess;
	var $_defaultAccess;
	var $_locked;
	var $_keywords;
	var $_sequence;
	
	function Document($id, $name, $comment, $date, $expires, $ownerID, $folderID, $inheritAccess, $defaultAccess, $locked, $keywords, $sequence)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_comment = $comment;
		$this->_date = $date;
		$this->_expires = $expires;
		$this->_ownerID = $ownerID;
		$this->_folderID = $folderID;
		$this->_inheritAccess = $inheritAccess;
		$this->_defaultAccess = $defaultAccess;
		$this->_locked = $locked;
		$this->_keywords = $keywords;
		$this->_sequence = $sequence;

		$this->db = clone($GLOBALS['egw']->db);
		$this->db->set_app('mydms');
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function setName($newName)
	{
		$data = array('name' => $newName);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_name = $newName;
		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		$data = array('comment' => $newComment);
		$where = array('id' => $this->_id);

		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		                        
		$this->_comment = $newComment;
		return true;
	}

	function getKeywords() { return $this->_keywords; }

	function setKeywords($newKeywords)
	{
		$data = array('keywords' => $newKeywords);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_keywords = $newKeywords;
		return true;
	}

	function getDate()
	{
		return $this->_date;
	}

	function getFolder()
	{
		if (!isset($this->_folder))
			$this->_folder = getFolder($this->_folderID);
		return $this->_folder;
	}

	function setFolder($newFolder)
	{
		$data = array('folder' => $newFolder->getID());
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_folderID = $newFolder->getID();
		$this->_folder = $newFolder;
		return true;
	}

	function getOwner()
	{
		if (!isset($this->_owner))
			$this->_owner = getUser($this->_ownerID);
		return $this->_owner;
	}

	function setOwner($user)
	{
		$data = array('owner' => $user->getID());
		$where = array('id' => $this->_id);

		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_ownerID = $user->getID();
		$this->_owner = $user;
		return true;
	}

	function getDefaultAccess()
	{
		if ($this->inheritsAccess())
		{
			$res = $this->getFolder();
			if (!$res) return false;
			return $this->_folder->getDefaultAccess();
		}
		return $this->_defaultAccess;
	}

	function setDefaultAccess($mode)
	{
		$data = array('defaultAccess' => $mode);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_defaulAccess = $mode;
		return true;
	}

	function inheritsAccess() { return $this->_inheritAccess; }

	function setInheritAccess($inheritAccess)
	{
		$inheritAccess = ($inheritAccess) ? "1" : "0";
		$data = array('inheritAccess' => $inheritAccess);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_inheritAccess = $inheritAccess;
		return true;
	}

	function expires()
	{
		if (intval($this->_expires) == 0)
			return false;
		else
			return true;
	}

	function getExpires()
	{
		if (intval($this->_expires) == 0)
			return false;
		else
			return $this->_expires;
	}

	function setExpires($expires)
	{
		$expires = (!$expires) ? 0 : $expires;
		$data = array('expires' => $expires);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_expires = $expires;
		return true;
	}

	function isLocked() { return $this->_locked != -1; }

	function setLocked($falseOrUser)
	{
		$locked = (is_object($falseOrUser)) ? $falseOrUser->getID() : -1;
		$data = array('locked' => $locked);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		unset($this->_lockingUser);
		$this->_locked = $locked;
		return true;
	}

	function getLockingUser()
	{
		if (!$this->isLocked())
			return false;
		
		if (!isset($this->_lockingUser))
			$this->_lockingUser = getUser($this->_locked);
		return $this->_lockingUser;
	}

	function getSequence() { return $this->_sequence; }

	function setSequence($seq)
	{
		$data = array('sequence' => $seq);
		$where = array('id' => $this->_id);
		
		if(!$this->db->update('phpgw_mydms_Documents', $data, $where, __LINE__, __FILE__)) {
			return false;
		}
		
		$this->_sequence = $seq;
		return true;
	}

	function clearAccessList()
	{
		$where = array(
			'targetType'	=> T_DOCUMENT,
			'target'	=> $this->_id,
		);
		
		if(!$this->db->delete('phpgw_mydms_ACLs', $where, __LINE__, __FILE__)) {
			return false;
		}
		
		unset($this->_accessList);
		return true;
	}

	function getAccessList()
	{
		if ($this->inheritsAccess())
		{
			$res = $this->getFolder();
			if (!$res) return false;
			return $this->_folder->getAccessList();
		}
		
		if (!isset($this->_accessList))
		{
			$cols = array('userID', 'groupID', 'mode');
			$where = array(
				'targetType'	=> T_DOCUMENT,
				'target'	=> $this->_id,
			);
			
			if(!$this->db->select('phpgw_mydms_ACLs', $cols, $where, __LINE__, __FILE__, false, 'ORDER BY targetType')) {
				return false;
			}

			$this->_accessList = array("groups" => array(), "users" => array());
			while ($this->db->next_record()) {
				$userID = ($this->db->f('userID')?$this->db->f('userID'):$this->db->f('userid'));
				if ($userID != -1) {
					array_push($this->_accessList["users"], new UserAccess($userID, $this->db->f('mode')));
				} else {//if ($row["groupID"] != -1)
					$groupID = ($this->db->f('groupID')?$this->db->f('groupID'):$this->db->f('groupid'));
					array_push($this->_accessList["groups"], new GroupAccess($groupID, $this->db->f('mode')));
				}
			}
			
		#	$queryStr = "SELECT * FROM phpgw_mydms_ACLs WHERE targetType = ".T_DOCUMENT." AND target = " . $this->_id . " ORDER BY targetType";
		#	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		#	if (is_bool($resArr) && !$resArr)
		#		return false;
		#	
		#	$this->_accessList = array("groups" => array(), "users" => array());
		#	foreach ($resArr as $row)
		#	{
		#		if ($row["userID"] != -1)
		#			array_push($this->_accessList["users"], new UserAccess($row["userID"], $row["mode"]));
		#		else //if ($row["groupID"] != -1)
		#			array_push($this->_accessList["groups"], new GroupAccess($row["groupID"], $row["mode"]));
		#	}
		}

		return $this->_accessList;
	}

	function addAccess($mode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$data = array (
			'target'	=> $this->_id,
			'targetType'	=> T_DOCUMENT,
			$userOrGroup	=> $userOrGroupID,
			'mode'		=> $mode,
		);
		$res = $this->db->insert('phpgw_mydms_ACLs', $data, '', __LINE__, __FILE__);
		if (!$res) {
			return false;
		}
		
		#$queryStr = "INSERT INTO phpgw_mydms_ACLs (target, targetType, ".$userOrGroup.", mode) VALUES 
		#			(".$this->_id.", ".T_DOCUMENT.", " . $userOrGroupID . ", " .$mode. ")";
		#if (!$GLOBALS['mydms']->db->getResult($queryStr))
		#	return false;
		
		unset($this->_accessList);
		return true;
	}

	function changeAccess($newMode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$data	= array('mode'	=> $newMode);
		$where	= array(
			'targetType'	=> T_DOCUMENT, 
			'target'	=> $this->_id, 
			$userOrGroup	=> $userOrGroupID,
		);
		
		if(!$this->db->update('phpgw_mydms_ACLs', $data, $where, __LINE__, __FILE__)) {
			return false;
		}

	#	$queryStr = "UPDATE phpgw_mydms_ACLs SET mode = " . $newMode . " WHERE targetType = ".T_DOCUMENT." AND target = " . $this->_id . " AND " . $userOrGroup . " = " . $userOrGroupID;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_accessList);
		return true;
	}

	function removeAccess($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$where = array(
			'targetType'	=> T_DOCUMENT,
			'target'	=> $this->_id,
			$userOrGroup	=> $userOrGroupID,
		);
		
		if(!$this->db->delete('phpgw_mydms_ACLs', $where, __LINE__, __FILE__)) {
			return false;
		}

	#	$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE targetType = ".T_DOCUMENT." AND target = ".$this->_id." AND ".$userOrGroup." = " . $userOrGroupID;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_accessList);
		return true;
	}

	/*
	 * Liefert die Art der Zugriffsberechtigung f�r den User $user; M�gliche Rechte: n (keine), r (lesen), w (schreiben+lesen), a (alles)
	 * Zun�chst wird Gepr�ft, ob die Berechtigung geerbt werden soll; in diesem Fall wird die Anfrage an den Eltern-Ordner weitergeleitet.
	 * Ansonsten werden die ACLs durchgegangen: Die h�chstwertige Berechtigung gilt.
	 * Wird bei den ACLs nicht gefunden, wird die Standard-Berechtigung zur�ckgegeben.
	 * Ach ja: handelt es sich bei $user um den Besitzer ist die Berechtigung automatisch "a".
	 */
	function getAccessMode($user)
	{
		//Administrator??
		if ($user->isAdmin())
			return M_ALL;
		
		//Besitzer??
		if ($user->getID() == $this->_ownerID)
			return M_ALL;
		
		//Gast-Benutzer??
		if (($user->getID() == $GLOBALS['mydms']->settings->_guestID) && ($GLOBALS['mydms']->settings->_enableGuestLogin))
		{
			$mode = $this->getDefaultAccess();
			if ($mode >= M_READ)
				return M_READ;
			else
				return M_NONE;
		}
		
		//Berechtigung erben??
		// wird �ber GetAccessList() bereits realisiert.
		// durch das Verwenden der folgenden Zeilen w�ren auch Owner-Rechte vererbt worden.
		/*
		if ($this->inheritsAccess())
		{
			if (!$this->getFolder())
				return false;
			return $this->_folder->getAccessMode($user);
		}
		*/
		$highestPrivileged = M_NONE;
		
		//ACLs durchforsten
		$foundInACL = false;
		$accessList = $this->getAccessList();
		if (!$accessList)
			return false;
		
		foreach ($accessList["users"] as $userAccess)
		{
			if ($userAccess->getUserID() == $user->getID())
			{
				$foundInACL = true;
				if ($userAccess->getMode() > $highestPrivileged)
					$highestPrivileged = $userAccess->getMode();
				if ($highestPrivileged == M_ALL) //h�her geht's nicht -> wir k�nnen uns die arbeit schenken
					return $highestPrivileged;
			}
		}
		foreach ($accessList["groups"] as $groupAccess)
		{
			if ($user->isMemberOfGroup($groupAccess->getGroup()))
			{
				$foundInACL = true;
				if ($groupAccess->getMode() > $highestPrivileged)
					$highestPrivileged = $groupAccess->getMode();
				if ($highestPrivileged == M_ALL) //h�her geht's nicht -> wir k�nnen uns die arbeit schenken
					return $highestPrivileged;
			}
		}
		if ($foundInACL)
			return $highestPrivileged;
		
		//Standard-Berechtigung verwenden
		return $this->getDefaultAccess();
	}

	function getNotifyList()
	{
		if (!isset($this->_notifyList))
		{
			$cols = array('userID', 'groupID');
			$where = array(
				'targetType'	=> T_DOCUMENT,
				'target'	=> $this->_id,
			);
			
			if(!$this->db->select('phpgw_mydms_Notify', $cols, $where, __LINE__, __FILE__)) {
				return false;
			}

			$this->_notifyList = array("groups" => array(), "users" => array());
			while ($this->db->next_record()) {
				$userID = ($this->db->f('userID')?$this->db->f('userID'):$this->db->f('userid'));
				if ($userID != -1) {
					array_push($this->_notifyList["users"], getUser($userID));
				} else {//if ($row["groupID"] != -1)
					$groupID = ($this->db->f('groupID')?$this->db->f('groupID'):$this->db->f('groupid'));
					array_push($this->_notifyList["groups"], getGroup($groupID));
				}
			}
			
		#	$queryStr ="SELECT * FROM phpgw_mydms_Notify WHERE targetType = " . T_DOCUMENT . " AND target = " . $this->_id;
		#	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		#	if (is_bool($resArr) && $resArr == false)
		#		return false;
		#	
		#	$this->_notifyList = array("groups" => array(), "users" => array());
		#	foreach ($resArr as $row)
		#	{
		#		if ($row["userID"] != -1)
		#			array_push($this->_notifyList["users"], getUser($row["userID"]) );
		#		else //if ($row["groupID"] != -1)
		#			array_push($this->_notifyList["groups"], getGroup($row["groupID"]) );
		#	}
		}
		return $this->_notifyList;
	}

	function addNotify($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";

		$data = array (
			'target'	=> $this->_id,
			'targetType'	=> T_DOCUMENT,
			$userOrGroup	=> $userOrGroupID,
		);
		$res = $this->db->insert('phpgw_mydms_Notify', $data, '', __LINE__, __FILE__);
		if (!$res) {
			return false;
		}
		
	#	$queryStr = "INSERT INTO phpgw_mydms_Notify (target, targetType, " . $userOrGroup . ") VALUES (" . $this->_id . ", " . T_DOCUMENT . ", " . $userOrGroupID . ")";
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_notifyList);
		return true;
	}

	function removeNotify($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$where = array(
			'targetType'	=> T_DOCUMENT,
			'target'	=> $this->_id,
			$userOrGroup	=> $userOrGroupID,
		);
		
		if(!$this->db->delete('phpgw_mydms_Notify', $where, __LINE__, __FILE__)) {
			return false;
		}

	#	$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT . " AND " . $userOrGroup . " = " . $userOrGroupID;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_notifyList);
		return true;
	}


	function addContent($comment, $user, $tmpFile, $orgFileName, $fileType, $mimeType)
	{
//		if ($this->isLocked() && ($user->getID() != $this->getLockingUser()->getID()))
//			return false;
		
		$res = $this->getContent();
		if (is_bool($res) && !$res)
			return false;
		
		if (count($this->_content) == 0) {
			$newVersion = 1;
		} else {
			$res = $this->getLatestContent();
			if (is_bool($res) && !$res)
				return false;
			$newVersion = $this->_latestContent->getVersion()+1;
		}

		$dir = getSuitableDocumentDir();
		if (is_bool($res) && !$res)
			return false;

		//Kopieren der tempor�ren Datei
		if(!file_exists($GLOBALS['mydms']->settings->_contentDir . $dir))
		{
			if (!makeDir($GLOBALS['mydms']->settings->_contentDir . $dir))
				return false;
		}

		if (!copyFile($tmpFile, $GLOBALS['mydms']->settings->_contentDir . $dir . "data" . $fileType))
			return false;

		//Eintrag in phpgw_mydms_DocumentContent
		$insertData = array(
			'document'	=> $this->_id,
			'version'	=> $newVersion,
			'comment'	=> $comment,
			'date'		=> mktime(),
			'createdBy'	=> $user->getID(),
			'dir'		=> $dir,
			'orgFileName'	=> $orgFileName,
			'fileType'	=> $fileType,
			'mimeType'	=> $mimeType,
		);
		$res = $this->db->insert('phpgw_mydms_DocumentContent', $insertData, '', __LINE__, __FILE__);
		if (!$res)
			return false;
			
	#	$queryStr = "INSERT INTO phpgw_mydms_DocumentContent (document, version, comment, date, createdBy, dir, orgFileName, fileType, mimeType) VALUES ".
	#				"(".$this->_id.", ".$newVersion.", '".$comment."', ".mktime().", ".$user->getID().", '".$dir."', '".$orgFileName."', '".$fileType."', '" . $mimeType . "')";
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_content);
		unset($this->_latestContent);
		
		$this->getLatestContent();
	#	if ($GLOBALS['mydms']->settings->_enableConverting && in_array($this->_latestContent->getFileType(), array_keys($GLOBALS['mydms']->settings->_convertFileTypes)))
	#		$this->_latestContent->convert(); //Auch wenn das schiefgeht, wird deswegen nicht gleich alles "hingeschmissen" (sprich: false zur�ckgegeben)
		
//		$this->setLocked(false);
		
		return true;
	}

	function getContent()
	{
		if (!isset($this->_content))
		{
			$cols = array('id', 'document', 'version', 'comment', 'date', 'createdBy', 'dir', 'orgFileName', 'fileType', 'mimeType');
			$where = array(
				'document'	=> $this->_id,
			);
			
			if(!$this->db->select('phpgw_mydms_DocumentContent', $cols, $where, __LINE__, __FILE__, false, 'ORDER BY version')) {
				return false;
			}

			$this->_content = array();
			while ($this->db->next_record()) {
				array_push(
					$this->_content, 
					new DocumentContent(
						$this->db->f('id'), 
						$this->db->f('document'), 
						$this->db->f('version'), 
						$this->db->f('comment'), 
						$this->db->f('date'), 
						($this->db->f('createdBy')?$this->db->f('createdBy'):$this->db->f('createdby')), 
						$this->db->f('dir'), 
						($this->db->f('orgFileName')?$this->db->f('orgFileName'):$this->db->f('orgfilename')), 
						($this->db->f('fileType')?$this->db->f('fileType'):$this->db->f('filetype')), 
						($this->db->f('mimeType')?$this->db->f('mimeType'):$this->db->f('mimetype'))
					)
				);
			}
			
		#	$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." ORDER BY version";
		#	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		#	if (is_bool($resArr) && !$res)
		#		return false;
		#	
		#	$this->_content = array();
		#	foreach ($resArr as $row)
		#		array_push($this->_content, new DocumentContent($row["id"], $row["document"], $row["version"], $row["comment"], $row["date"], $row["createdBy"], $row["dir"], $row["orgFileName"], $row["fileType"], $row["mimeType"]));
		}
		
		return $this->_content;
	}

	function getContentByVersion($version)
	{
		if (!is_numeric($version))
			die ("invalid version");
		
		if (isset($this->_content))
		{
			foreach ($this->_content as $revision)
			{
				if ($revision->getVersion() == $version)
					return $revision;
			}
			return false;
		}
		
		$cols = array('id', 'document', 'version', 'comment', 'date', 'createdBy', 'dir', 'orgFileName', 'fileType', 'mimeType');
		$where = array(
			'document'	=> $this->_id,
			'version'	=> $version,
		);
			
		if(!$this->db->select('phpgw_mydms_DocumentContent', $cols, $where, __LINE__, __FILE__)) {
			return false;
		}

		if ($this->db->next_record()) {
			return new DocumentContent(
				$this->db->f('id'), 
				$this->db->f('document'), 
				$this->db->f('version'), 
				$this->db->f('comment'), 
				$this->db->f('date'), 
				($this->db->f('createdBy')?$this->db->f('createdBy'):$this->db->f('createdby')), 
				$this->db->f('dir'), 
				($this->db->f('orgFileName')?$this->db->f('orgFileName'):$this->db->f('orgfilename')), 
				($this->db->f('fileType')?$this->db->f('fileType'):$this->db->f('filetype')), 
				($this->db->f('mimeType')?$this->db->f('mimeType'):$this->db->f('mimetype'))
			);
		} else {
			return false;
		}
			
	#	$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." AND version = " . $version;
	#	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
	#	if (is_bool($resArr) && !$res)
	#		return false;
	#	if (count($resArr) != 1)
	#		return false;
	#	
	#	$resArr = $resArr[0];
	#	return new DocumentContent($resArr["id"], $resArr["document"], $resArr["version"], $resArr["comment"], $resArr["date"], $resArr["createdBy"], $resArr["dir"], $resArr["orgFileName"], $resArr["fileType"], $resArr["mimeType"]);
	}

	function getLatestContent()
	{
		if (!isset($this->_latestContent))
		{
/*			if (isset($this->_content))
			{
				$this->getContent();
				$this->_latestContent =  $this->_content[count($this->_content)-1];
				return $this->_latestContent;
			}
			*/
			$cols = array('id', 'document', 'version', 'comment', 'date', 'createdBy', 'dir', 'orgFileName', 'fileType', 'mimeType');
			$where = array(
				'document'	=> $this->_id,
			);
			
			if(!$this->db->select('phpgw_mydms_DocumentContent', $cols, $where, __LINE__, __FILE__, false, 'ORDER BY version DESC')) {
				return false;
			}

			$this->_latestContent = array();
			if ($this->db->next_record()) {
				$this->_latestContent = new DocumentContent(
					$this->db->f('id'), 
					$this->db->f('document'), 
					$this->db->f('version'), 
					$this->db->f('comment'), 
					$this->db->f('date'), 
					($this->db->f('createdBy')?$this->db->f('createdBy'):$this->db->f('createdby')), 
					$this->db->f('dir'), 
					($this->db->f('orgFileName')?$this->db->f('orgFileName'):$this->db->f('orgfilename')), 
					($this->db->f('fileType')?$this->db->f('fileType'):$this->db->f('filetype')), 
					($this->db->f('mimeType')?$this->db->f('mimeType'):$this->db->f('mimetype'))
				);
			}
	
		#	$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." ORDER BY version DESC";
		#	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		#	if (is_bool($resArr) && !$resArr)
		#		return false;
		#	
		#	$resArr = $resArr[0];
		#	$this->_latestContent = new DocumentContent($resArr["id"], $resArr["document"], $resArr["version"], $resArr["comment"], $resArr["date"], $resArr["createdBy"], $resArr["dir"], $resArr["orgFileName"], $resArr["fileType"], $resArr["mimeType"]);
		}
		return $this->_latestContent;
	}

	function getDocumentLinks()
	{
		if (!isset($this->_documentLinks))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_DocumentLinks WHERE document = " . $this->_id;
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && !$resArr)
				return false;
			$this->_documentLinks = array();
			
			foreach ($resArr as $row)
				array_push($this->_documentLinks, new DocumentLink($row["id"], $row["document"], $row["target"], $row["userID"], $row["public"]));
		}
		return $this->_documentLinks;
	}

	function addDocumentLink($targetID, $userID, $public)
	{
		$data = array(
			'document'	=> $this->_id,
			'target'	=> $targetID,
			'userID'	=> $userID,
			'public'	=> $GLOBALS['egw']->db->quote($public,'bool'),
		);
		$res = $this->db->insert('phpgw_mydms_DocumentLinks', $data, '', __LINE__, __FILE__);
		if (!$res)
			return false;

	#	$queryStr = "INSERT INTO phpgw_mydms_DocumentLinks(document, target, userID, public) VALUES (".$this->_id.", ".$targetID.", ".$userID.", " . $GLOBALS['egw']->db->quote($public,'bool').")";
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		unset($this->_documentLinks);
		return true;
	}

	function removeDocumentLink($linkID)
	{
		$where = array(
			'id'	=> $linkID,
		);
		
		if(!$this->db->delete('phpgw_mydms_DocumentLinks', $where, __LINE__, __FILE__)) {
			return false;
		}

	#	$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE id = " . $linkID;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		unset ($this->_documentLinks);
		return true;
	}


	function remove()
	{
		$res = $this->getContent();
		if (is_bool($res) && !$res) return false;

		for ($i = 0; $i < count($this->_content); $i++) {
			if (!$this->_content[$i]->remove()) {
				return false;
			}
		}

		$where = array('id' => $this->_id);
		if(!$this->db->delete('phpgw_mydms_Documents', $where, __LINE__, __FILE__)) {
			return false;
		}

		$where = array('target'	=> $this->_id, 'targetType' => T_DOCUMENT);
		if(!$this->db->delete('phpgw_mydms_ACLs', $where, __LINE__, __FILE__)) {
			return false;
		}
		if(!$this->db->delete('phpgw_mydms_Notify', $where, __LINE__, __FILE__)) {
			return false;
		}

		$where = array('document' => $this->_id, 'target' => $this->_id);
		if(!$this->db->delete('phpgw_mydms_DocumentLinks', $where, __LINE__, __FILE__)) {
			return false;
		}

	#	$queryStr = "DELETE FROM phpgw_mydms_Documents WHERE id = " . $this->_id;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
	#	$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
	#	$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
	#	$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE document = " . $this->_id . " OR target = " . $this->_id;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		return true;
	}
}

 /* ---------------------------------------------------------------------------------------------------- */
 
/**
 * Die Datei wird als "data.ext" (z.b. data.txt) gespeichert. Getrennt davon wird in der DB der urspr�ngliche
 * Dateiname festgehalten (-> $orgFileName). Die Datei wird deshalb nicht unter diesem urspr�nglichen Namen
 * gespeichert, da es zu Problemen mit verschiedenen Dateisystemen kommen kann: Linux hat z.b. Probleme mit
 * deutschen Umlauten, w�hrend Windows wiederum den Doppelpunkt in Dateinamen nicht verwenden kann.
 * Der urspr�ngliche Dateiname wird nur zum Download verwendet (siehe op.Download.pgp)
 */
class DocumentContent
{

	function DocumentContent($id, $documentID, $version, $comment, $date, $userID, $dir, $orgFileName, $fileType, $mimeType)
	{
		$this->_id = $id;
		$this->_documentID = $documentID;
		$this->_version = $version;
		$this->_comment = $comment;
		$this->_date = $date;
		$this->_userID = $userID;
		$this->_dir = $dir;
		$this->_orgFileName = $orgFileName;
		$this->_fileType = $fileType;
		$this->_mimeType = $mimeType;

		$this->db = clone($GLOBALS['egw']->db);
		$this->db->set_app('mydms');
	}

	function getVersion() { return $this->_version; }
	function getComment() { return $this->_comment; }
	function getDate() { return $this->_date; }
	function getOriginalFileName() { return $this->_orgFileName; }
	function getFileType() { return $this->_fileType; }
	function getFileName(){ return "data" . $this->_fileType; }
	function getDir() { return $this->_dir; }
	function getMimeType() { return $this->_mimeType; }
	function getUser()
	{
		if (!isset($this->_user))
			$this->_user = getUser($this->_userID);
		return $this->_user;
	}
	function getPath() { return $this->_dir . "data" . $this->_fileType; }

	function convert()
	{
		if (file_exists($GLOBALS['mydms']->settings->_contentDir . $this->_dir . "index.html"))
			return true;
		
		if (!in_array($this->_fileType, array_keys($GLOBALS['mydms']->settings->_convertFileTypes)))
			return false;
		
		$source = $GLOBALS['mydms']->settings->_contentDir . $this->_dir . $this->getFileName();
		$target = $GLOBALS['mydms']->settings->_contentDir . $this->_dir . "index.html";
	//	$source = str_replace("/", "\\", $source);
	//	$target = str_replace("/", "\\", $target);
		
		$command = $GLOBALS['mydms']->settings->_convertFileTypes[$this->_fileType];
		$command = str_replace("{SOURCE}", "\"$source\"", $command);
		$command = str_replace("{TARGET}", "\"$target\"", $command);
		
		$output = array();
		$res = 0;
		exec($command, $output, $res);
		
		if ($res != 0)
		{
			print (implode("\n", $output));
			return false;
		}
		return true;
	}

	function viewOnline()
	{
		if (in_array($this->_fileType, $GLOBALS['mydms']->settings->_viewOnlineFileTypes))
			return true;
		if ($GLOBALS['mydms']->settings->_enableConverting && in_array($this->_fileType, array_keys($GLOBALS['mydms']->settings->_convertFileTypes)))
			if ($this->wasConverted())
				return true;
		
		return false;
	}

	function wasConverted()
	{
		return file_exists($GLOBALS['mydms']->settings->_contentDir . $this->_dir . "index.html");
	}

	function getURL()
	{
		if (!$this->viewOnline())
			return false;
		
		if (in_array($this->_fileType, $GLOBALS['mydms']->settings->_viewOnlineFileTypes))
			return "/" . $this->_documentID . "/" . $this->_version . "/" . $this->getOriginalFileName();
		else
			return "/" . $this->_documentID . "/" . $this->_version . "/index.html";
	}

	function remove()
	{
		# does this check make sense here??? Lars
		#if (!removeDir($GLOBALS['mydms']->settings->_contentDir . $this->_dir)) {
		#	return false;
		#}

		$where = array(
			'id'	=> $this->_id,
		);
		
		if(!$this->db->delete('phpgw_mydms_DocumentContent', $where, __LINE__, __FILE__)) {
			return false;
		}
		
		return true;
	}
}


 /* ---------------------------------------------------------------------------------------------------- */
function getDocumentLink($linkID)
{
	if (!is_numeric($linkID))
		die ("invalid linkID");
	
	$queryStr = "SELECT * FROM phpgw_mydms_DocumentLinks WHERE id = " . $linkID;
	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
	if (is_bool($resArr) && !$resArr)
		return false;
	
	$resArr = $resArr[0];
	return new DocumentLink($resArr["id"], $resArr["document"], $resArr["target"], $resArr["userID"], $resArr["public"]);
}

function filterDocumentLinks($user, $links)
{
	$tmp = array();
	foreach ($links as $link)
		if ($link->isPublic() || ($link->_userID == $user->getID()) || ($user->getID() == $GLOBALS['mydms']->settings->_adminID) )
			array_push($tmp, $link);
	return $tmp;
}

class DocumentLink
{
	var $_id;
	var $_documentID;
	var $_targetID;
	var $_userID;
	var $_public;

	function DocumentLink($id, $documentID, $targetID, $userID, $public)
	{
		$this->_id = $id;
		$this->_documentID = $documentID;
		$this->_targetID = $targetID;
		$this->_userID = $userID;
		$this->_public = $public;

		$this->db = clone($GLOBALS['egw']->db);
		$this->db->set_app('mydms');
	}

	function getID() { return $this->_id; }

	function getDocument()
	{
		if (!isset($this->_document))
			$this->_document = getDocument($this->_documentID);
		return $this->_document;
	}

	function getTarget()
	{
		if (!isset($this->_target))
			$this->_target = getDocument($this->_targetID);
		return $this->_target;
	}

	function getUser()
	{
		if (!isset($this->_user))
			$this->_user = getUser($this->_userID);
		return $this->_user;
	}

	function isPublic() { return $this->_public; }

	function remove()
	{
		$where = array(
			'id'	=> $this->_id,
		);
		
		if(!$this->db->delete('phpgw_mydms_DocumentLinks', $where, __LINE__, __FILE__)) {
			return false;
		}
	#	$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE id = " . $this->_id;
	#	if (!$GLOBALS['mydms']->db->getResult($queryStr))
	#		return false;
		
		return true;
	}
}
