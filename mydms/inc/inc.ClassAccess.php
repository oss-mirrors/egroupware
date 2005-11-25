<?php

/*
 * Repr�sentiert einen Eintrag in der phpgw_mydms_ACLs f�r einen User.
 * �nderungen an der Berechtigung k�nnen nicht vorgenommen werden; daf�r sind die Klassen Folder und Document selbst
 * verantwortlich.
 */
class UserAccess
{
	var $_userID;
	var $_mode;

	function UserAccess($userID, $mode)
	{
		$this->_userID = $userID;
		$this->_mode = $mode;
	}

	function getUserID() { return $this->_userID; }

	function getMode() { return $this->_mode; }

	function getUser()
	{
		if (!isset($this->_user))
			$this->_user = getUser($this->_userID);
		return $this->_user;
	}
}


class GroupAccess
{
	var $_groupID;
	var $_mode;

	function GroupAccess($groupID, $mode)
	{
		$this->_groupID = $groupID;
		$this->_mode = $mode;
	}

	function getGroupID() { return $this->_groupID; }

	function getMode() { return $this->_mode; }

	function getGroup()
	{
		if (!isset($this->_group))
			$this->_group = getGroup($this->_groupID);
		return $this->_group;
	}
}