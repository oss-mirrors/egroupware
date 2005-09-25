<?

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
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function setName($newName)
	{
		$queryStr = "UPDATE phpgw_mydms_Documents SET name = '" . $newName . "' WHERE id = ". $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_name = $newName;
		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		$queryStr = "UPDATE phpgw_mydms_Documents SET comment = '" . $newComment . "' WHERE id = ". $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_comment = $newComment;
		return true;
	}

	function getKeywords() { return $this->_keywords; }

	function setKeywords($newKeywords)
	{
		$queryStr = "UPDATE phpgw_mydms_Documents SET keywords = '" . $newKeywords . "' WHERE id = ". $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		$queryStr = "UPDATE phpgw_mydms_Documents SET folder = " . $newFolder->getID() . " WHERE id = ". $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		$queryStr = "UPDATE phpgw_mydms_Documents set owner = " . $user->getID() . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		$queryStr = "UPDATE phpgw_mydms_Documents set defaultAccess = " . $mode . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_defaulAccess = $mode;
		return true;
	}

	function inheritsAccess() { return $this->_inheritAccess; }

	function setInheritAccess($inheritAccess)
	{
		$inheritAccess = ($inheritAccess) ? "1" : "0";
		
		$queryStr = "UPDATE phpgw_mydms_Documents SET inheritAccess = " . $inheritAccess . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		
		$queryStr = "UPDATE phpgw_mydms_Documents SET expires = " . $expires . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_expires = $expires;
		return true;
	}

	function isLocked() { return $this->_locked != -1; }

	function setLocked($falseOrUser)
	{
		$locked = (is_object($falseOrUser)) ? $falseOrUser->getID() : -1;
		
		$queryStr = "UPDATE phpgw_mydms_Documents SET locked = " . $locked . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		$queryStr = "UPDATE phpgw_mydms_Documents SET sequence = " . $seq . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_sequence = $seq;
		return true;
	}

	function clearAccessList()
	{
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE targetType = " . T_DOCUMENT . " AND target = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
			$queryStr = "SELECT * FROM phpgw_mydms_ACLs WHERE targetType = ".T_DOCUMENT." AND target = " . $this->_id . " ORDER BY targetType";
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && !$resArr)
				return false;
			
			$this->_accessList = array("groups" => array(), "users" => array());
			foreach ($resArr as $row)
			{
				if ($row["userID"] != -1)
					array_push($this->_accessList["users"], new UserAccess($row["userID"], $row["mode"]));
				else //if ($row["groupID"] != -1)
					array_push($this->_accessList["groups"], new GroupAccess($row["groupID"], $row["mode"]));
			}
		}
		
		return $this->_accessList;
	}

	function addAccess($mode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "INSERT INTO phpgw_mydms_ACLs (target, targetType, ".$userOrGroup.", mode) VALUES 
					(".$this->_id.", ".T_DOCUMENT.", " . $userOrGroupID . ", " .$mode. ")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	function changeAccess($newMode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "UPDATE phpgw_mydms_ACLs SET mode = " . $newMode . " WHERE targetType = ".T_DOCUMENT." AND target = " . $this->_id . " AND " . $userOrGroup . " = " . $userOrGroupID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	function removeAccess($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE targetType = ".T_DOCUMENT." AND target = ".$this->_id." AND ".$userOrGroup." = " . $userOrGroupID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	/*
	 * Liefert die Art der Zugriffsberechtigung für den User $user; Mögliche Rechte: n (keine), r (lesen), w (schreiben+lesen), a (alles)
	 * Zunächst wird Geprüft, ob die Berechtigung geerbt werden soll; in diesem Fall wird die Anfrage an den Eltern-Ordner weitergeleitet.
	 * Ansonsten werden die ACLs durchgegangen: Die höchstwertige Berechtigung gilt.
	 * Wird bei den ACLs nicht gefunden, wird die Standard-Berechtigung zurückgegeben.
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
		// wird über GetAccessList() bereits realisiert.
		// durch das Verwenden der folgenden Zeilen wären auch Owner-Rechte vererbt worden.
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
				if ($highestPrivileged == M_ALL) //höher geht's nicht -> wir können uns die arbeit schenken
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
				if ($highestPrivileged == M_ALL) //höher geht's nicht -> wir können uns die arbeit schenken
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
			$queryStr ="SELECT * FROM phpgw_mydms_Notify WHERE targetType = " . T_DOCUMENT . " AND target = " . $this->_id;
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return false;
			
			$this->_notifyList = array("groups" => array(), "users" => array());
			foreach ($resArr as $row)
			{
				if ($row["userID"] != -1)
					array_push($this->_notifyList["users"], getUser($row["userID"]) );
				else //if ($row["groupID"] != -1)
					array_push($this->_notifyList["groups"], getGroup($row["groupID"]) );
			}
		}
		return $this->_notifyList;
	}

	function addNotify($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "INSERT INTO phpgw_mydms_Notify (target, targetType, " . $userOrGroup . ") VALUES (" . $this->_id . ", " . T_DOCUMENT . ", " . $userOrGroupID . ")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_notifyList);
		return true;
	}

	function removeNotify($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT . " AND " . $userOrGroup . " = " . $userOrGroupID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		
		if (count($this->_content) == 0)
			$newVersion = 1;
		else
		{
			$res = $this->getLatestContent();
			if (is_bool($res) && !$res)
				return false;
			$newVersion = $this->_latestContent->getVersion()+1;
		}
		
		$dir = getSuitableDocumentDir();
		if (is_bool($res) && !$res)
			return false;

		//Kopieren der temporären Datei
		if(!file_exists($GLOBALS['mydms']->settings->_contentDir . $dir))
		{
			if (!makeDir($GLOBALS['mydms']->settings->_contentDir . $dir))
				return false;
		}

		if (!copyFile($tmpFile, $GLOBALS['mydms']->settings->_contentDir . $dir . "data" . $fileType))
			return false;

		//Eintrag in phpgw_mydms_DocumentContent
		$queryStr = "INSERT INTO phpgw_mydms_DocumentContent (document, version, comment, date, createdBy, dir, orgFileName, fileType, mimeType) VALUES ".
					"(".$this->_id.", ".$newVersion.", '".$comment."', ".mktime().", ".$user->getID().", '".$dir."', '".$orgFileName."', '".$fileType."', '" . $mimeType . "')";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_content);
		unset($this->_latestContent);
		
		$this->getLatestContent();
		if ($GLOBALS['mydms']->settings->_enableConverting && in_array($this->_latestContent->getFileType(), array_keys($GLOBALS['mydms']->settings->_convertFileTypes)))
			$this->_latestContent->convert(); //Auch wenn das schiefgeht, wird deswegen nicht gleich alles "hingeschmissen" (sprich: false zurückgegeben)
		
//		$this->setLocked(false);
		
		return true;
	}

	function getContent()
	{
		if (!isset($this->_content))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." ORDER BY version";
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && !$res)
				return false;
			
			$this->_content = array();
			foreach ($resArr as $row)
				array_push($this->_content, new DocumentContent($row["id"], $row["document"], $row["version"], $row["comment"], $row["date"], $row["createdBy"], $row["dir"], $row["orgFileName"], $row["fileType"], $row["mimeType"]));
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
		
		$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." AND version = " . $version;
		$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		if (is_bool($resArr) && !$res)
			return false;
		if (count($resArr) != 1)
			return false;
		
		$resArr = $resArr[0];
		return new DocumentContent($resArr["id"], $resArr["document"], $resArr["version"], $resArr["comment"], $resArr["date"], $resArr["createdBy"], $resArr["dir"], $resArr["orgFileName"], $resArr["fileType"], $resArr["mimeType"]);
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
			$queryStr = "SELECT * FROM phpgw_mydms_DocumentContent WHERE document = ".$this->_id." ORDER BY version DESC LIMIT 0,1";
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && !$resArr)
				return false;
			if (count($resArr) != 1)
				return false;
			
			$resArr = $resArr[0];
			$this->_latestContent = new DocumentContent($resArr["id"], $resArr["document"], $resArr["version"], $resArr["comment"], $resArr["date"], $resArr["createdBy"], $resArr["dir"], $resArr["orgFileName"], $resArr["fileType"], $resArr["mimeType"]);
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
		$public = ($public) ? "1" : "0";
		
		$queryStr = "INSERT INTO phpgw_mydms_DocumentLinks(document, target, userID, public) VALUES (".$this->_id.", ".$targetID.", ".$userID.", " . $public.")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_documentLinks);
		return true;
	}

	function removeDocumentLink($linkID)
	{
		$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE id = " . $linkID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		unset ($this->_documentLinks);
		return true;
	}


	function remove()
	{
		$res = $this->getContent();
		if (is_bool($res) && !$res) return false;
		
		for ($i = 0; $i < count($this->_content); $i++)
			if (!$this->_content[$i]->remove())
				return false;
		
		$queryStr = "DELETE FROM phpgw_mydms_Documents WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = " . $this->_id . " AND targetType = " . T_DOCUMENT;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE document = " . $this->_id . " OR target = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		return true;
	}
}

 /* ---------------------------------------------------------------------------------------------------- */
 
/**
 * Die Datei wird als "data.ext" (z.b. data.txt) gespeichert. Getrennt davon wird in der DB der ursprüngliche
 * Dateiname festgehalten (-> $orgFileName). Die Datei wird deshalb nicht unter diesem ursprünglichen Namen
 * gespeichert, da es zu Problemen mit verschiedenen Dateisystemen kommen kann: Linux hat z.b. Probleme mit
 * deutschen Umlauten, während Windows wiederum den Doppelpunkt in Dateinamen nicht verwenden kann.
 * Der ursprüngliche Dateiname wird nur zum Download verwendet (siehe op.Download.pgp)
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
		if (!removeDir($GLOBALS['mydms']->settings->_contentDir . $this->_dir))
			return false;
		
		$queryStr = "DELETE FROM phpgw_mydms_DocumentContent WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
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
		$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		return true;
	}
}
