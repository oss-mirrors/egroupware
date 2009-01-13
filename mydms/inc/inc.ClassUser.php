<?php

/**********************************************************************\
|                  statische, User-bezogene Funktionen                 |
\**********************************************************************/
//added by dawnlinux to port mydms user management to egroupware
function egw_name2id($login)
{
	$id = $GLOBALS['egw']->accounts->name2id($login);
	return $id;
}

function egw_id2name($id)
{
	$login = $GLOBALS['egw']->accounts->id2name($id);
        return $login;

}

function egw_get_accname($id)
{
	$GLOBALS['egw']->accounts->get_account_name($id,$lid,$fname,$lname);
	$fullname = $fname.' '.$lname;
	return $fullname;
}

function egw_get_accemail($id)
{
	$email = $GLOBALS['egw']->accounts->id2name($id, 'account_email');
	return $email;
}

function egw_is_admin($id)
{
	if($id == 0)
	{
		$id = $GLOBALS['egw_info']['user']['account_id'];
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

	$accounts = $GLOBALS['egw']->accounts->get_list('accounts'); 
	
	//tim сортировка по имени или фамилии
	//по имени
        for ($i = 0; $i < count($accounts); $i++) $s_users[$accounts[$i]["account_id"]] = $accounts[$i]['account_firstname'];
	//или по фамилии
	//for ($i = 0; $i < count($accounts); $i++) $s_users[$accounts[$i]["account_id"]] = $accounts[$i]['account_lastname'];
	asort($s_users);//сортировка массива по возрастанию значений массива - имя/фамилия
	$i =0;
	foreach ($s_users as $key => $value) $s_id[$i++] = $key; //заполняем массив id упорядоченными в порядке роста имен/фамилий
	//----

	for ($i = 0; $i < count($accounts); $i++)
	{
		$id = $accounts[$i]["account_id"];
		$login = $accounts[$i]["account_lid"];
		$fullName = $accounts[$i]['account_firstname'].' '.$accounts[$i]['account_lastname']; //сорт.по фам.надо помнять местами 
		$email = $accounts[$i]["account_email"];
		$isAdmin = egw_is_admin($id);
		$pwd = '';
		$comment = '';
		//tim номер ключа задаем поиском по $id из отсортированного массива $s_id а не по порядку $i
		//$users[$i] = new User($id, $login, $pwd, $fullName, $email, $comment, $isAdmin);
		$users[array_search($id,$s_id)] = new User($id, $login, $pwd, $fullName, $email, $comment, $isAdmin);
		ksort($users); //сортируем по ключам - получаем записи, где имена/фамилии в алфавитном порядке
		//------
	}
	
	return $users;
}


function addUser($login, $pwd, $fullName, $email, $comment)
{
	echo "add user is disabled \n";
	/*GLOBAl $db;
	
	$queryStr = "INSERT INTO phpgw_mydms_Users (login, pwd, fullName, email, comment, isAdmin) VALUES ('".$login."', '".$pwd."', '".$fullName."', '".$email."', '".$comment."', false)";
	$res = $db->getResult($queryStr);
	if (!$res)
		return false;
	
	return getUser($db->getInsertID('phpgw_mydms_Users','id'));*/
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
		
		$queryStr = "UPDATE phpgw_mydms_Users SET login ='" . $newLogin . "' WHERE id = " . $this->_id;
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
		
		$queryStr = "UPDATE phpgw_mydms_Users SET fullname = '" . $newFullName . "' WHERE id = " . $this->_id;
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
		
		$queryStr = "UPDATE phpgw_mydms_Users SET pwd ='" . $newPwd . "' WHERE id = " . $this->_id;
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
		
		$queryStr = "UPDATE phpgw_mydms_Users SET email ='" . $newEmail . "' WHERE id = " . $this->_id;
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
		
		$queryStr = "UPDATE phpgw_mydms_Users SET comment ='" . $newComment . "' WHERE id = " . $this->_id;
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
		
		$isAdmin = $isAdmin ? "1" : "0";
		$queryStr = "UPDATE phpgw_mydms_Users SET isAdmin = " . $GLOBALS['egw']->db->quote($isAdmin) . " WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_isAdmin = $isAdmin;
		return true;
	}

	/**
	 * Entfernt den Benutzer aus dem System.
	 * Dies ist jedoch nicht mit einem L�schen des entsprechenden Eintrags aus phpgw_mydms_Users geschehen - vielmehr
	 * muss daf�r gesorgt werden, dass der Benutzer nirgendwo mehr auftaucht. D.h. auch die Tabellen phpgw_mydms_ACLs,
	 * phpgw_mydms_Notify, phpgw_mydms_GroupMembers, phpgw_mydms_Folders, phpgw_mydms_Documents und phpgw_mydms_DocumentContent m�ssen ber�cksichtigt werden.
	 */
	function remove()
	{
		GLOBAL $db, $settings;
		
		//Private Stichwortlisten l�schen
		$queryStr = "SELECT phpgw_mydms_Keywords.id FROM phpgw_mydms_Keywords, phpgw_mydms_KeywordCategories WHERE phpgw_mydms_Keywords.category = phpgw_mydms_KeywordCategories.id AND phpgw_mydms_KeywordCategories.owner = " . $this->_id;
		$resultArr = $db->getResultArray($queryStr);
		if (count($resultArr) > 0) {
			$queryStr = "DELETE FROM phpgw_mydms_Keywords WHERE ";
			for ($i = 0; $i < count($resultArr); $i++) {
				$queryStr .= "id = " . $resultArr[$i]["id"];
				if ($i + 1 < count($resultArr))
					$queryStr .= " OR ";
			}
			if (!$db->getResult($queryStr))
				return false;
		}
		$queryStr = "DELETE FROM phpgw_mydms_KeywordCategories WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		//Benachrichtigungen entfernen
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Der Besitz von Dokumenten oder Ordnern, deren bisheriger Besitzer der zu l�schende war, geht an den Admin �ber
		$queryStr = "UPDATE phpgw_mydms_Folders SET owner = " . $settings->_adminID . " WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "UPDATE phpgw_mydms_Documents SET owner = " . $settings->_adminID . " WHERE owner = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "UPDATE phpgw_mydms_DocumentContent SET createdBy = " . $settings->_adminID . " WHERE createdBy = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Verweise auf Dokumente: Private l�schen...
		$queryStr = "DELETE FROM phpgw_mydms_DocumentLinks WHERE userID = " . $this->_id . " AND public = 0";
		if (!$db->getResult($queryStr))
			return false;
		//... und �ffentliche an Admin �bergeben
		$queryStr = "UPDATE phpgw_mydms_DocumentLinks SET userID = " . $settings->_adminID . " WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Evtl. von diesem Benutzer gelockte Dokumente werden freigegeben
		$queryStr = "UPDATE phpgw_mydms_Documents SET locked = -1 WHERE locked = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//User aus allen Gruppen l�schen
		$queryStr = "DELETE FROM phpgw_mydms_GroupMembers WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//User aus allen ACLs streichen
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE userID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		//Eintrag aus phpgw_mydms_Users l�schen
		$queryStr = "DELETE FROM phpgw_mydms_Users WHERE id = " . $this->_id;
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
		$usergroups = $GLOBALS['egw']->accounts->membership($this->_id);
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
			
			$queryStr = "SELECT COUNT(*) AS num FROM phpgw_mydms_UserImages WHERE userID = " . $this->_id;
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
			$queryStr = "UPDATE phpgw_mydms_UserImages SET image = '".base64_encode($content)."', mimeType = '". $mimeType."' WHERE userID = " . $this->_id;
		else
			$queryStr = "INSERT INTO phpgw_mydms_UserImages (userID, image, mimeType) VALUES (" . $this->_id . ", '".base64_encode($content)."', '".$mimeType."')";
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_hasImage = true;
		return true;
	}
}


?>
