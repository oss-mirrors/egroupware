<?

/**********************************************************************\
|                 statische, Group-bezogene Funktionen                 |
\**********************************************************************/


function getGroup($id)
{
	
	$name = $GLOBALS['egw']->accounts->id2name($id);
	
	return new Group($id, $name, '');
}


function getAllGroups()
{
	
	$phpgw_groups = $GLOBALS['egw']->accounts->get_list('groups');
	
	$groups = array();
	
	for ($i = 0; $i < count($phpgw_groups); $i++)
		$groups[$i] = new Group($phpgw_groups[$i]["account_id"], $phpgw_groups[$i]["account_lid"], '');
	
	return $groups;
}


function addGroup($name, $comment)
{
	GLOBAl $db;
	
	$queryStr = "INSERT INTO phpgw_mydms_Groups (name, comment) VALUES ('".$name."', '" . $comment . "')";
	if (!$db->getResult($queryStr))
		return false;
	
	return getGroup($db->getInsertID());
}


/**********************************************************************\
|                           Group-Klasse                               |
\**********************************************************************/

class Group
{
	var $_id;
	var $_name;

	function Group($id, $name, $comment)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_comment = $comment;
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function setName($newName)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE phpgw_mydms_Groups SET name = '" . $newName . "' WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_name = $newName;
		return true;
	}

	function getComment() { return $this->_comment; }

	function setComment($newComment)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE phpgw_mydms_Groups SET comment = '" . $newComment . "' WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_comment = $newComment;
		return true;
	}

	function getUsers()
	{
		$members = $GLOBALS['egw']->accounts->member($this->_id);	
		$egw_group_member = array();

		if (is_array($members))
		{
			foreach($members as $member)
			{
				if(!in_array($member['account_id'],$egw_group_member))
				{
					$egw_group_member[] = $member['account_id'];
				}
			}
		}


		$this->_users = array();

		foreach($egw_group_member as $egw_member)
		{
			$user = getUser($egw_member);
			array_push($this->_users, $user);
		}
	
		return $this->_users;
	}

	function addUser($user)
	{
		GLOBAL $db;
		
		$queryStr = "INSERT INTO phpgw_mydms_GroupMembers (groupID, userID) VALUES (".$this->_id.", ".$user->getID().")";
		$res = $db->getResult($queryStr);
		if ($res)
			return false;
		
		unset($this->_users);
		return true;
	}

	function removeUser($user)
	{
		GLOBAL $db;
		
		$queryStr = "DELETE FROM phpgw_mydms_GroupMembers WHERE  groupID = ".$this->_id." AND userID = ".$user->getID();
		$res = $db->getResult($queryStr);
		if ($res)
			return false;
		
		unset($this->_users);
		return true;
	}

	function isMember($user)
	{
		//Wenn die User bereits abgefragt wurden, geht's so schneller:
		if (isset($this->_users))
		{
			foreach ($this->_users as $usr)
				if ($usr->getID() == $user->getID())
					return true;
			return false;
		}
		
		
		$members = $GLOBALS['egw']->accounts->member((int)$this->_id);
                $egw_group_member = array();

                if (is_array($members))
                {
                        foreach($members as $member)
                        {
                                if(!in_array($member['account_id'],$egw_group_member))
                                {
                                        $egw_group_member[] = $member['account_id'];
                                }
                        }
                }
						
		if(!in_array($user->getID(),$egw_group_member))
		{
			return false;
		}
		else
		{
			return true;
		}

	}

	/**
	 * Entfernt die Gruppe aus dem System.
	 * Dies ist jedoch nicht mit einem Löschen des entsprechenden Eintrags aus phpgw_mydms_Groups geschehen - vielmehr
	 * muss dafür gesorgt werden, dass die Gruppe nirgendwo mehr auftaucht. D.h. auch die Tabellen phpgw_mydms_ACLs,
	 * phpgw_mydms_Notify und phpgw_mydms_GroupMembers müssen berücksichtigt werden.
	 */
	function remove()
	{
		GLOBAl $db;
		
		$queryStr = "DELETE FROM phpgw_mydms_Groups WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_GroupMembers WHERE groupID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_ACLs WHERE groupID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		$queryStr = "DELETE FROM phpgw_mydms_Notify WHERE groupID = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		return true;
	}
}


?>