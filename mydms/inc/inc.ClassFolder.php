<?php

//Tim: otherwise there's no ACL check
if(!class_exists('UserAccess',false))
{
	include_once(EGW_INCLUDE_ROOT.'/mydms/inc/inc.ClassAccess.php');
	include_once(EGW_INCLUDE_ROOT.'/mydms/inc/inc.ClassUser.php');
	include_once(EGW_INCLUDE_ROOT.'/mydms/inc/inc.ClassGroup.php');
}
if (!defined('MYDMS_APP'))
{
	define('MYDMS_APP','mydms');
}

function getFolder($id)
{
	if (!is_numeric($id))
		die ("invalid folderid");

	$queryStr = "SELECT * FROM phpgw_mydms_Folders WHERE id = " . $id;
	//$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
	$resArr = $GLOBALS['mydms']->db->getResult($queryStr)->fetch();

	if (is_bool($resArr) && $resArr == false)
		return false;
/*	else if (count($resArr) != 1)
		return false;

	$resArr = $resArr[0];*/
	if($id == 1) {
		$resArr["defaultAccess"] = M_READ;
	}
	$newFolder =  new Folder(
		$resArr["id"],
		$resArr["name"],
		$resArr["parent"],
		$resArr["comment"],
		$resArr["owner"],
		($resArr["inheritAccess"] ? $resArr["inheritAccess"] : $resArr["inheritaccess"]),
		($resArr["defaultAccess"] ? $resArr["defaultAccess"] : $resArr["defaultaccess"]),
		$resArr["sequence"]);

	#print $resArr["name"]."<br>";
	#print $newFolder->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']))."<br>";
	if($newFolder->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id'])) > 1)
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
	static private $db;

	function Folder($id, $name, $parentID, $comment, $ownerID, $inheritAccess, $defaultAccess, $sequence)
	{
		self::__construct($id, $name, $parentID, $comment, $ownerID, $inheritAccess, $defaultAccess, $sequence);
	}

	function __construct($id, $name, $parentID, $comment, $ownerID, $inheritAccess, $defaultAccess, $sequence)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_parentID = $parentID;
		$this->_comment = $comment;
		$this->_ownerID = $ownerID;
		if($inheritAccess == 'f' || $inheritAccess == 0) {
			$this->_inheritAccess = 0;
		} else {
			$this->_inheritAccess = 1;
		}
		$this->_defaultAccess = $defaultAccess;
		$this->_sequence = $sequence;

		self::$db = $GLOBALS['egw']->db;
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function setName($newName)
	{
		$data 	= array('name' => $newName);
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
			return false;

		$this->_name = $newName;

		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		$data 	= array('comment' => $newComment);
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
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
		$data 	= array('parent' => $newParent->getID());
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
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
		$data 	= array('owner' => $user->getID());
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
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
		$data 	= array('defaultAccess' => $mode);
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
			return false;

		$this->_defaulAccess = $mode;
		return true;
	}

	function inheritsAccess() { return $this->_inheritAccess; }

	function setInheritAccess($inheritAccess)
	{
		$inheritAccess = $inheritAccess ? "1" : "0";

		$data 	= array('inheritAccess' => $inheritAccess);
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
			return false;

		$this->_inheritAccess = $inheritAccess;
		return true;
	}

	function getSequence() { return $this->_sequence; }

	function setSequence($seq)
	{
		$data 	= array('sequence' => $seq);
		$where	= array('id' => $this->_id);

		if(!self::$db->update('phpgw_mydms_Folders', $data, $where, __LINE__, __FILE__,MYDMS_APP))
			return false;

		$this->_sequence = $seq;
		return true;
	}

	function &getSubFolders()
	{
		if (!isset($this->_subFolders))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_Folders WHERE parent = " . $this->_id . " ORDER BY sequence";
			//$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			$resArr = $GLOBALS['mydms']->db->getResult($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return false;

			$this->_subFolders = array();
			//for ($i = 0; $i < count($resArr); $i++)
			foreach($resArr as $row)
			{
				$newSubFolder = new Folder(
					$row["id"],
					$row["name"],
					$row["parent"],
					$row["comment"],
					$row["owner"],
					($row["inheritAccess"]?$row["inheritAccess"]:$row["inheritaccess"]),
					($row["defaultAccess"]?$row["defaultAccess"]:$row["defaultaccess"]),
					$row["sequence"]);

				if($newSubFolder->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id'])) > 1)
        				$this->_subFolders[] = $newSubFolder;
			}
		}

		return $this->_subFolders;
	}

	function addSubFolder($name, $comment, $owner, $sequence)
	{
		$ownerid = $GLOBALS['egw_info']['user']['account_id'];
		//inheritAccess = true, defaultAccess = M_READ

		$insertData = array(
			'name'		=> $name,
			'parent'	=> $this->_id,
			'comment'	=> $comment,
			'owner'		=> $ownerid,
			'inheritAccess'	=> true,
			'defaultAccess'	=> M_READ,
			'sequence'	=> $sequence,
		);
		$res = self::$db->insert('phpgw_mydms_Folders', $insertData, '', __LINE__, __FILE__, MYDMS_APP);

		if (!$res)
			return false;

		unset($this->_subFolders);

		return getFolder(self::$db->get_last_insert_id('phpgw_mydms_Folders','id'));
	}

	/**
	 * Gibt ein Array mit allen Eltern, "Gro�elter" usw bis zum RootFolder zur�ck
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
	 * Gibt ein Array mit allen Eltern, "Gro�elter" usw bis zum RootFolder zur�ck
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
	 * �berpr�ft, ob dieser Ordner ein Unterordner von $folder ist
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

	function &getDocuments()
	{
		if (!isset($this->_documents))
		{
			$queryStr = "SELECT * FROM phpgw_mydms_Documents WHERE folder = " . $this->_id . " ORDER BY sequence";
			//$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			$resArr = $GLOBALS['mydms']->db->getResult($queryStr);
			if (is_bool($resArr) && !$resArr)
				return false;

			$this->_documents = array();
			foreach ($resArr as $row)
				array_push($this->_documents, new Document(
					$row["id"],
					$row["name"],
					$row["comment"],
					$row["date"],
					$row["expires"],
					$row["owner"],
					$row["folder"],
					($row["inheritAccess"]?$row["inheritAccess"]:$row["inheritaccess"]),
					($row["defaultAccess"]?$row["defaultAccess"]:$row["defaultaccess"]),
					$row["locked"],
					$row["keywords"],
					$row["sequence"]));
		}
		return $this->_documents;
	}

	function addDocument($name, $comment, $expires, $owner, $keywords, $tmpFile, $orgFileName, $fileType, $mimeType, $sequence)
	{
		$ownerid = $GLOBALS['egw_info']['user']['account_id'];

		$expires = (!$expires) ? 0 : $expires;


		$insertData = array(
			'name'		=> $name,
			'comment'	=> $comment,
			'date'		=> mktime(),
			'expires'	=> $expires,
			'owner'		=> $ownerid,
			'folder'	=> $this->_id,
			'inheritAccess'	=> true,
			'defaultAccess'	=> M_READ,
			'locked'	=> -1,
			'keywords'	=> $keywords,
			'sequence'	=> $sequence,
		);
		$res = self::$db->insert('phpgw_mydms_Documents', $insertData, '', __LINE__, __FILE__, MYDMS_APP);

		if (!$res)
			return false;

		#unset($this->_subFolders);

		#return getFolder(self::$db->get_last_insert_id('phpgw_mydms_Folders','id'));

		#$queryStr = "INSERT INTO phpgw_mydms_Documents (name, comment, date, expires, owner, folder, inheritAccess, defaultAccess, locked, keywords, sequence) VALUES ".
		#			"('".$name."', '".$comment."', " . mktime().", ".$expires.", ".$ownerid.", ".$this->_id.", true, ".M_READ.", -1, '".$keywords."', " . $sequence . ")";
		#if (!$GLOBALS['mydms']->db->getResult($queryStr))
		#	return false;

		$document = getDocument(self::$db->get_last_insert_id('phpgw_mydms_Documents','id'));

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

		//Entfernen der Datenbankeintr�ge
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
		#error_log(__METHOD__." called ");
		if ($this->inheritsAccess())
		{
			//error_log(__METHOD__." inherits Access ");
			$res = $this->getParent();
			if (!$res) return false;
			return $this->_parent->getAccessList();
		}

		if (!isset($this->_accessList))
		{
			$fields = array('userID','groupID','mode');
			$fields_str = implode(", ", $fields);
			$where =  array(
				'targetType'=> T_FOLDER,
				'target' =>  $this->_id,
			);

			#error_log(__METHOD__." accessList ");
			/*
			$queryStr = "SELECT * FROM phpgw_mydms_ACLs WHERE targetType = ".T_FOLDER." AND target = " . $this->_id . " ORDER BY targetType";
			//$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			$resArr = $GLOBALS['mydms']->db->getResult($queryStr);
			#error_log(__METHOD__." Access:".print_r($resArr,true));
			if (is_bool($resArr) && !$resArr)
				return false;
			*/
			$this->_accessList = array("groups" => array(), "users" => array());
			//foreach ($resArr as $row)
			foreach(self::$db->select('phpgw_mydms_ACLs',$fields_str,$where,__LINE__,__FILE__,false," ORDER BY targetType",MYDMS_APP) as $row)
			{
				$userID = ($row["userID"] ? $row["userID"] : $row["userid"]);
				if ($userID != -1) {
					array_push($this->_accessList["users"], new UserAccess($userID, $row["mode"]));
				} else {//if ($row["groupID"] != -1)
					$groupID = ($row["groupID"] ? $row["groupID"] : $row["groupid"]);
					array_push($this->_accessList["groups"], new GroupAccess($groupID, $row["mode"]));
				}
			}
		}
		//error_log(__METHOD__." Access:".print_r($this->_accessList,true));
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
	 * Liefert die Art der Zugriffsberechtigung f�r den User $user; M�gliche Rechte: n (keine), r (lesen), w (schreiben+lesen), a (alles)
	 * Zun�chst wird Gepr�ft, ob die Berechtigung geerbt werden soll; in diesem Fall wird die Anfrage an den Eltern-Ordner weitergeleitet.
	 * Ansonsten werden die ACLs durchgegangen: Die h�chstwertige Berechtigung gilt.
	 * Wird bei den ACLs nicht gefunden, wird die Standard-Berechtigung zur�ckgegeben.
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
		// wird �ber GetAccessList() bereits realisiert.
		// durch das Verwenden der folgenden Zeilen w�ren auch Owner-Rechte vererbt worden.
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
		if (!$accessList) {
			return false;
		}

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
			$queryStr ="SELECT * FROM phpgw_mydms_Notify WHERE targetType = " . T_FOLDER . " AND target = " . $this->_id;
			//$resArr = $GLOBALS['mydms']->db->getResultArray($queryStr);
			$resArr = $GLOBALS['mydms']->db->getResult($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return false;

			$this->_notifyList = array("groups" => array(), "users" => array());
			foreach ($resArr as $row)
			{
				$userID = ($row["userID"] ? $row["userID"] : $row["userid"]);
				if ($userID != -1) {
					array_push($this->_notifyList["users"], getUser($userID) );
				} else {//if ($row["groupID"] != -1)
					$groupID = ($row["groupID"] ? $row["groupID"] : $row["groupid"]);
					array_push($this->_notifyList["groups"], getGroup($groupID) );
				}
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
