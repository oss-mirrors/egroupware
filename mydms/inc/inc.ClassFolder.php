<?
function getFolder($id)
{
	if (!is_numeric($id))
		die ("invalid folderid");

	$queryStr = "SELECT * FROM phpgw_mydms_Folders WHERE id = " . $id;
	$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
		
	if (is_bool($resArr) && $resArr == false)
		return false;
	else if (count($resArr) != 1)
		return false;
		
	$resArr = $resArr[0];
	$newFolder =  new Folder($resArr["id"], $resArr["name"], $resArr["parent"], $resArr["comment"], $resArr["owner"], $resArr["inheritAccess"], $resArr["defaultAccess"], $resArr["sequence"]);
	
	#print $resArr["name"]."<br>";
	#print $newFolder->getAccessMode(getUser($GLOBALS['phpgw_info']['user']['account_id']))."<br>";
	if($newFolder->getAccessMode(getUser($GLOBALS['phpgw_info']['user']['account_id'])) > 1)
        	return $newFolder;
	else
		return false;
}


/**********************************************************************\
|                            Folder-Klasse                             |
\**********************************************************************/

class Folder
{
	var $_id;
	var $_name;
	var $_parentID;
	var $_comment;
	var $_ownerID;
	var $_inheritAccess;
	var $_defaultAccess;
	var $_sequence;

	function Folder($id, $name, $parentID, $comment, $ownerID, $inheritAccess, $defaultAccess, $sequence)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_parentID = $parentID;
		$this->_comment = $comment;
		$this->_ownerID = $ownerID;
		$this->_inheritAccess = $inheritAccess;
		$this->_defaultAccess = $defaultAccess;
		$this->_sequence = $sequence;
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function setName($newName)
	{
		$queryStr = "UPDATE phpgw_mydms_Folders SET name = '" . $newName . "' WHERE id = ". $this->_id;
		$res = $GLOBALS['mydms']->db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_name = $newName;
		
		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		$queryStr = "UPDATE phpgw_mydms_Folders SET comment = '" . $newComment . "' WHERE id = ". $this->_id;
		$res = $GLOBALS['mydms']->db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_comment = $newComment;
		
		return true;
	}

	function getParent()
	{
		if (!isset($this->_parentID) || ($this->_parentID == "") || ($this->_parentID == 0))
			return false;
		
		if (!isset($this->_parent))
			$this->_parent = getFolder($this->_parentID);
		return $this->_parent;
	}

	function setParent($newParent)
	{
		$queryStr = "UPDATE phpgw_mydms_Folders SET parent = " . $newParent->getID() . " WHERE id = ". $this->_id;
		$res = $GLOBALS['mydms']->db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_parentID = $newParent->getID();
		$this->_parent = $newParent;
		
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
		$queryStr = "UPDATE phpgw_mydms_Folders set owner = " . $user->getID() . " WHERE id = " . $this->_id;
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
			$res = $this->getParent();
			if (!$res) return false;
			return $this->_parent->getDefaultAccess();
		}
		
		return $this->_defaultAccess;
	}

	function setDefaultAccess($mode)
	{
		$queryStr = "UPDATE phpgw_mydms_Folders set defaultAccess = " . $mode . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_defaulAccess = $mode;
		return true;
	}

	function inheritsAccess() { return $this->_inheritAccess; }

	function setInheritAccess($inheritAccess)
	{
		$inheritAccess = ($inheritAccess) ? "1" : "0";
		
		$queryStr = "UPDATE phpgw_mydms_Folders SET inheritAccess = " . $inheritAccess . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_inheritAccess = $inheritAccess;
		return true;
	}

	function getSequence() { return $this->_sequence; }

	function setSequence($seq)
	{
		$queryStr = "UPDATE phpgw_mydms_Folders SET sequence = " . $seq . " WHERE id = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$this->_sequence = $seq;
		return true;
	}

	function getSubFolders()
	{
		if (!isset($this->_subFolders))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_Folders WHERE parent = " . $this->_id . " ORDER BY sequence";
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return false;
			
			$this->_subFolders = array();
			for ($i = 0; $i < count($resArr); $i++)
			{
				$newSubFolder = new Folder($resArr[$i]["id"], $resArr[$i]["name"], $resArr[$i]["parent"], $resArr[$i]["comment"], $resArr[$i]["owner"], $resArr[$i]["inheritAccess"], $resArr[$i]["defaultAccess"], $resArr[$i]["sequence"]);

				if($newSubFolder->getAccessMode(getUser($GLOBALS['phpgw_info']['user']['account_id'])) > 1)
        				$this->_subFolders[$i] = $newSubFolder;
			}			
		}
		
		return $this->_subFolders;
	}

	function addSubFolder($name, $comment, $owner, $sequence)
	{
		$ownerid = $GLOBALS['phpgw_info']['user']['account_id'];
		//inheritAccess = true, defaultAccess = M_READ
		$queryStr = "INSERT INTO phpgw_mydms_Folders (name, parent, comment, owner, inheritAccess, defaultAccess, sequence) ".
					"VALUES ('".$name."', ".$this->_id.", '".$comment."', ".$ownerid.", 1, ".M_READ.", ".$sequence.")";
		$res = $GLOBALS['mydms']->db->getResult($queryStr);
		if (!$res)
			return false;
		
		unset($this->_subFolders);
		
		return getFolder($GLOBALS['mydms']->db->getInsertID());
	}

	/**
	 * Gibt ein Array mit allen Eltern, "Großelter" usw bis zum RootFolder zurück
	 * Der Ordner selbst ist das letzte Element dieses Arrays
	 */
	function getPath()
	{
		if (!isset($this->_parentID) || ($this->_parentID == "") || ($this->_parentID == 0))
			return array($this);
		else
		{
			$res = $this->getParent();
			if (!$res) return false;
			
			$path = $this->_parent->getPath();
			if (!$path) return false;
			
			array_push($path, $this);
			return $path;
		}
	}

	/**
	 * Gibt ein Array mit allen Eltern, "Großelter" usw bis zum RootFolder zurück
	 * Der Ordner selbst ist das letzte Element dieses Arrays
	 */
	function getPathNew()
	{
		if (!isset($this->_parentID) || ($this->_parentID == "") || ($this->_parentID == 0))
			return array($this->_id => $this);
		else
		{
			$res = $this->getParent();
			if (!$res) return false;
			
			#print "search parent ".$this->_id."<br>";
			$path = $this->_parent->getPathNew();
			#print "_parent->getPathNew(".$this->_id."):<br>";
			#print "my parent ".$this->_id."<br>";
			#_debug_array($path);
			if (!$path) return false;
			
			#$path = array_merge($path, array($this->_id => $this));
			#$path[] = array($this);
			unset($this->_parent);
			#print "me ".$this->_id."<br>";
			#_debug_array($this);
			$path[$this->_id] = $this;
			return $path;
		}
	}

	/**
	 * Überprüft, ob dieser Ordner ein Unterordner von $folder ist
	 */
	function isDescendant($folder)
	{
		if ($this->_parentID == $folder->getID())
			return true;
		else if (isset($this->_parentID))
		{
			$res = $this->getParent();
			if (!$res) return false;
			
			return $this->_parent->isDescendant($folder);
		}
		else
			return false;
	}

	function getDocuments()
	{
		if (!isset($this->_documents))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_Documents WHERE folder = " . $this->_id . " ORDER BY sequence";
			$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			if (is_bool($resArr) && !$resArr)
				return false;
			
			$this->_documents = array();
			foreach ($resArr as $row)
				array_push($this->_documents, new Document($row["id"], $row["name"], $row["comment"], $row["date"], $row["expires"], $row["owner"], $row["folder"], $row["inheritAccess"], $row["defaultAccess"], $row["locked"], $row["keywords"], $row["sequence"]));
		}
		return $this->_documents;
	}

	function addDocument($name, $comment, $expires, $owner, $keywords, $tmpFile, $orgFileName, $fileType, $mimeType, $sequence)
	{
		$ownerid = $GLOBALS['phpgw_info']['user']['account_id'];		

		$expires = (!$expires) ? 0 : $expires;
		
		$queryStr = "INSERT INTO phpgw_mydms_Documents (name, comment, date, expires, owner, folder, inheritAccess, defaultAccess, locked, keywords, sequence) VALUES ".
					"('".$name."', '".$comment."', " . mktime().", ".$expires.", ".$ownerid.", ".$this->_id.", 1, ".M_READ.", -1, '".$keywords."', " . $sequence . ")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		$document = getDocument($GLOBALS['mydms']->db->getInsertID());
		
		$res = $document->addContent($comment, $owner, $tmpFile, $orgFileName, $fileType, $mimeType);
		if (is_bool($res) && !$res)
		{
			$queryStr = "DELETE FROM phpgw_mydms_Documents WHERE id = " . $document->getID();
			$GLOBALS['mydms']->db->getResult($queryStr);
			return false;
		}
		
		return $document;
	}

	function remove()
	{
		//Entfernen der Unterordner und Dateien
		$res = $this->getSubFolders();
		if (is_bool($res) && !$res) return false;
		$res = $this->getDocuments();
		if (is_bool($res) && !$res) return false;
		
		foreach ($this->_subFolders as $subFolder)
		{
			$res = $subFolder->remove();
			if (!$res) return false;
		}
		
		foreach ($this->_documents as $document)
		{
			$res = $document->remove();
			if (!$res) return false;
		}
		
		//Entfernen der Datenbankeinträge
		$queryStr = "DELETE FROM phpgw_mydms_Folders WHERE id =  " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE target = ". $this->_id. " AND targetType = " . T_FOLDER;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = ". $this->_id. " AND targetType = " . T_FOLDER;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		return true;
	}


	function getAccessList()
	{
		if ($this->inheritsAccess())
		{
			$res = $this->getParent();
			if (!$res) return false;
			return $this->_parent->getAccessList();
		}
		
		if (!isset($this->_accessList))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_ACLs WHERE targetType = ".T_FOLDER." AND target = " . $this->_id . " ORDER BY targetType";
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

	function clearAccessList()
	{
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE targetType = " . T_FOLDER . " AND target = " . $this->_id;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	function addAccess($mode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "INSERT INTO phpgw_mydms_ACLs (target, targetType, ".$userOrGroup.", mode) VALUES 
					(".$this->_id.", ".T_FOLDER.", " . $userOrGroupID . ", " .$mode. ")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	function changeAccess($newMode, $userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "UPDATE phpgw_mydms_ACLs SET mode = " . $newMode . " WHERE targetType = ".T_FOLDER." AND target = " . $this->_id . " AND " . $userOrGroup . " = " . $userOrGroupID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_accessList);
		return true;
	}

	function removeAccess($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE targetType = ".T_FOLDER." AND target = ".$this->_id." AND ".$userOrGroup." = " . $userOrGroupID;
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
		GLOBAL $settings;
		
		//Admin??
		if ($user->isAdmin())
			return M_ALL;
		
		//Besitzer ??
		if ($user->getID() == $this->_ownerID)
			return M_ALL;
		
		//Gast-Benutzer??
		if (($user->getID() == $settings->_guestID) && ($settings->_enableGuestLogin))
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
			if (isset($this->_parentID))
			{
				if (!$this->getParent())
					return false;
				return $this->_parent->getAccessMode($user);
			}
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
			$queryStr ="SELECT * FROM phpgw_mydms_Notify WHERE targetType = " . T_FOLDER . " AND target = " . $this->_id;
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
		
		$queryStr = "INSERT INTO phpgw_mydms_Notify (target, targetType, " . $userOrGroup . ") VALUES (" . $this->_id . ", " . T_FOLDER . ", " . $userOrGroupID . ")";
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_notifyList);
		return true;
	}

	function removeNotify($userOrGroupID, $isUser)
	{
		$userOrGroup = ($isUser) ? "userID" : "groupID";
		
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE target = " . $this->_id . " AND targetType = " . T_FOLDER . " AND " . $userOrGroup . " = " . $userOrGroupID;
		if (!$GLOBALS['mydms']->db->getResult($queryStr))
			return false;
		
		unset($this->_notifyList);
		return true;
	}
}

?>
