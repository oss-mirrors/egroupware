<?

/**********************************************************************\
|                  statische, User-bezogene Funktionen                 |
\**********************************************************************/
//added by dawnlinux to port mydms user management to egroupware
function egw_name2id($login)
{
	$id = $GLOBALS['phpgw']->accounts->name2id($login);
	return $id;
}

function egw_id2name($id)
{
	$login = $GLOBALS['phpgw']->accounts->id2name($id);
        return $login;

}

function egw_get_accname($id)
{
	$GLOBALS['phpgw']->accounts->get_account_name($id,$lid,$fname,$lname);
	$fullname = $fname.' '.$lname;
	return $fullname;
}

function egw_get_accemail($id)
{
	$email = $GLOBALS['phpgw']->accounts->id2name($id, 'account_email');
	return $email;
}

function egw_is_admin($id)
{
	if($id == 0)
	{
		$id = $GLOBALS['phpgw_info']['user']['account_id'];
	}

	$acl = CreateObject('phpgwapi.acl',$id);
	if ($acl->check('run',1,'admin'))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getUser($id)
{
	$resArr["id"] = $id;
	$resArr["login"] = egw_id2name($id);
	$resArr["fullName"] = egw_get_accname($id);
	$resArr["email"] = egw_get_accemail($id);
	$resArr["isAdmin"] = egw_is_admin($id);

	$resArr["pwd"] = '';
	$resArr["comment"] = '';
	return new User($resArr["id"], $resArr["login"], $resArr["pwd"], $resArr["fullName"], $resArr["email"], $resArr["comment"], $resArr["isAdmin"]);
}

function getUserByLogin($login)
{
	$id = $this->egw_name2id($login);
	$resArr["id"] = $id;
        $resArr["login"] = $login;
        $resArr["fullName"] = $this->egw_get_accname($id);
        $resArr["email"] = $this->egw_get_accemail($id);
        $resArr["isAdmin"] = $this->egw_is_admin($id);

        $resArr["pwd"] = '';
        $resArr["comment"] = '';
		
	return new User($resArr["id"], $resArr["login"], $resArr["pwd"], $resArr["fullName"], $resArr["email"], $resArr["comment"], $resArr["isAdmin"]);
}


function getAllUsers()
{

	$accounts = $GLOBALS['phpgw']->accounts->get_list('accounts'); 
	
	for ($i = 0; $i < count($accounts); $i++)
	{
		$id = $accounts[$i]["account_id"];
		$login = $accounts[$i]["account_lid"];
		$fullName = $accounts[$i]['account_firstname'].' '.$accounts[$i]['account_lastname'];
		$email = $accounts[$i]["account_email"];
		$isAdmin = egw_is_admin($id);
		$pwd = '';
		$comment = '';
		$users[$i] = new User($id, $login, $pwd, $fullName, $email, $comment, $isAdmin);
	}
	
	return $users;
}


function addUser($login, $pwd, $fullName, $email, $comment)
{
	echo "add user is disabled \n";
	/*GLOBAl $db;
	
	$queryStr = "INSERT INTO tblUsers (login, pwd, fullName, email, comment, isAdmin) VALUES ('".$login."', '".$pwd."', '".$fullName."', '".$email."', '".$comment."', 0)";
	$res = $db->getResult($queryStr);
	if (!$res)
		return false;
	
	return getUser($db->getInsertID());*/
}


/**********************************************************************\
|                            User-Klasse                               |
\**********************************************************************/

class User
{
	var $_id;
	var $_login;
	var $_pwd;
	var $_fullName;
	var $_email;
	var $_comment;
	var $_isAdmin;

	function User($id, $login, $pwd, $fullName, $email, $comment, $isAdmin)
	{
		$this->_id = $id;
		$this->_login = $login;
		$this->_pwd = $pwd;
		$this->_fullName = $fullName;
		$this->_email = $email;
		$this->_comment = $comment;
		$this->_isAdmin = $isAdmin;
	}

	function getID() { return $this->_id; }

	function getLogin() { return $this->_login; }

	function setLogin($newLogin)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE tblUsers SET login ='" . $newLogin . "' WHERE id = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_login = $newLogin;
		return true;
	}

	function getFullName() { return $this->_fullName; }

	function setFullName($newFullName)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE tblUsers SET fullname = '" . $newFullName . "' WHERE id = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_fullName = $newFullName;
		return true;
	}

	function getPwd() { return $this->_pwd; }

	function setPwd($newPwd)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE tblUsers SET pwd ='" . $newPwd . "' WHERE id = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_pwd = $newPwd;
		return true;
	}

	function getEmail() { return $this->_email; }

	function setEmail($newEmail)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE tblUsers SET email ='" . $newEmail . "' WHERE id = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_email = $newEmail;
		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE tblUsers SET comment ='" . $newComment . "' WHERE id = " . $this->_id;
		$res = $db->getResult($queryStr);
		if (!$res)
			return false;
		
		$this->_comment = $newComment;
		return true;
	}

	function isAdmin() { return $this->_isAdmin; }

	function setAdmin($isAdmin)
	{
		GLOBAL $db;
		
		$isAdmin = ($isAdmin) ? "1" : "0";
		$queryStr = "UPDATE tblUsers SET isAdmin = " . $isAdmin . " WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_isAdmin = $isAdmin;
		return true;
	}

	/**
	 * Entfernt den Benutzer aus dem System.
	 * Dies ist jedoch nicht mit einem Löschen des entsprechenden Eintrags aus tblUsers geschehen - vielmehr
	 * muss dafür gesorgt werden, dass der Benutzer nirgendwo mehr auftaucht. D.h. auch die Tabellen tblACLs,
	 * tblNotify, tblGroupMembers, tblFolders, tblDocuments und tblDocumentContent müssen berücksichtigt werden.
	 */
	function remove()
	{
		GLOBAL $db, $settings;
		
		//Private Stichwortlisten löschen
		$queryStr = "SELECT tblKeywords.id FROM tblKeywords, tblKeywordCategories WHERE tblKeywords.category = tblKeywordCategories.id AND tblKeywordCategories.owner = " . $this->_id;
		$resultArr = $db->getResultArray($queryStr);
		if (count($resultArr) > 0) {
			$queryStr = "DELETE FROM tblKeywords WHERE ";
			for ($i = 0; $i < count($resultArr); $i++) {
				$queryStr .= "id = " . $resultArr[$i]["id"];
				if ($i + 1 < count($resultArr))
					$queryStr .= " OR ";
			}
			if (!$db->getResult($queryStr))
				return false;
		}
		$queryStr = "DELETE FROM tblKeywordCategories WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		//Benachrichtigungen entfernen
		$queryStr = "DELETE FROM tblNotify WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Der Besitz von Dokumenten oder Ordnern, deren bisheriger Besitzer der zu löschende war, geht an den Admin über
		$queryStr = "UPDATE tblFolders SET owner = " . $settings->_adminID . " WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "UPDATE tblDocuments SET owner = " . $settings->_adminID . " WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "UPDATE tblDocumentContent SET createdBy = " . $settings->_adminID . " WHERE createdBy = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Verweise auf Dokumente: Private löschen...
		$queryStr = "DELETE FROM tblDocumentLinks WHERE userID = " . $this->_id . " AND public = 0";
		if (!$db->getResult($queryStr))
			return false;
		//... und öffentliche an Admin übergeben
		$queryStr = "UPDATE tblDocumentLinks SET userID = " . $settings->_adminID . " WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Evtl. von diesem Benutzer gelockte Dokumente werden freigegeben
		$queryStr = "UPDATE tblDocuments SET locked = -1 WHERE locked = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//User aus allen Gruppen löschen
		$queryStr = "DELETE FROM tblGroupMembers WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//User aus allen ACLs streichen
		$queryStr = "DELETE FROM tblACLs WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Eintrag aus tblUsers löschen
		$queryStr = "DELETE FROM tblUsers WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		
//		unset($this);
		return true;
	}

	function joinGroup($group)
	{
		if ($group->isMember($this))
			return false;
		
		if (!$group->addUser($this))
			return false;
		
		unset($this->_groups);
		return true;
	}

	function leaveGroup($group)
	{
		if (!$group->isMember($this))
			return false;
		
		if (!$group->removeUser($this))
			return false;
		
		unset($this->_groups);
		return true;
	}

	function getGroups()
	{
		$usergroups = $GLOBALS['phpgw']->accounts->membership($this->_id);
		$this->_groups = array();
		foreach($usergroups as $egw_group)
		{
			$group = getGroup($egw_group['account_id']);
			array_push($this->_groups, $group);
		}		
		return $this->_groups;
	}

	function isMemberOfGroup($group)
	{
		return $group->isMember($this);
	}

	function hasImage()
	{
		if (!isset($this->_hasImage))
		{
			GLOBAL $db;
			
			$queryStr = "SELECT COUNT(*) AS num FROM tblUserImages WHERE userID = " . $this->_id;
			$resArr = $db->getResultArray($queryStr);
			if (is_bool($resArr) && $resArr == false)
				return false;
			
			if ($resArr[0]["num"] == 0)	$this->_hasImage = false;
			else $this->_hasImage = true;
		}
		
		return $this->_hasImage;
	}

	function getImageURL()
	{
		GLOBAL $settings;
		
//		if (!$this->hasImage())
//			return false;
		return $settings->_httpRoot . "out/out.UserImage.php?userid=" . $this->_id;
	}

	function setImage($tmpfile, $mimeType)
	{
		GLOBAL $db;
		
		$fp = fopen($tmpfile, "rb");
		if (!$fp) return false;
		$content = fread($fp, filesize($tmpfile));
		fclose($fp);
		
		if ($this->hasImage())
			$queryStr = "UPDATE tblUserImages SET image = '".base64_encode($content)."', mimeType = '". $mimeType."' WHERE userID = " . $this->_id;
		else
			$queryStr = "INSERT INTO tblUserImages (userID, image, mimeType) VALUES (" . $this->_id . ", '".base64_encode($content)."', '".$mimeType."')";
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_hasImage = true;
		return true;
	}
}


?>