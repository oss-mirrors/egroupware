<?

function getKeywordCategory($id)
{
	GLOBAL $db;
	
	if (!is_numeric($id))
		die ("invalid id");
	
	$queryStr = "SELECT * FROM phpgw_mydms_KeywordCategories WHERE id = " . $id;
	$resArr = $db->getResultArray($queryStr);
	if ((is_bool($resArr) && !$resArr) || (count($resArr) != 1))
		return false;
	
	$resArr = $resArr[0];
	return new Keywordcategory($resArr["id"], $resArr["owner"], $resArr["name"]);
}

function getAllKeywordCategories($userID = -1)
{
	GLOBAL $db, $settings;
	
	$queryStr = "SELECT * FROM phpgw_mydms_KeywordCategories";
	if ($userID != -1)
		$queryStr .= " WHERE owner = $userID OR owner = " . $settings->_adminID;
	
	$resArr = $db->getResultArray($queryStr);
	if (is_bool($resArr) && !$resArr)
		return false;
	
	$categories = array();
	foreach ($resArr as $row)
		array_push($categories, new KeywordCategory($row["id"], $row["owner"], $row["name"]));
	
	return $categories;
}

function addKeywordCategory($owner, $name)
{
	GLOBAL $db;
	
	$queryStr = "INSERT INTO phpgw_mydms_KeywordCategories (owner, name) VALUES ($owner, '$name')";
	if (!$db->getResult($queryStr))
		return false;
	
	return getKeywordCategory($db->getInsertID());
}

//----------------------------------------------------------------------------------------------
class KeywordCategory
{
	var $_id;
	var $_ownerID;
	var $_name;

	function KeywordCategory($id, $ownerID, $name)
	{
		$this->_id = $id;
		$this->_name = $name;
		$this->_ownerID = $ownerID;
	}

	function getID() { return $this->_id; }

	function getName() { return $this->_name; }

	function getOwner() {
		if (!isset($this->_owner))
			$this->_owner = getUser($this->_ownerID);
		return $this->_owner;
	}

	function setName($newName)
	{
		GLOBAL $db;
		
		$queryStr = "UPDATE phpgw_mydms_KeywordCategories SET name = '$newName' WHERE id = ". $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_name = $newName;
		return true;
	}

	function setOwner($user) {
		GLOBAL $db;
		
		$queryStr = "UPDATE phpgw_mydms_KeywordCategories SET owner = " . $user->getID() . " WHERE id " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$this->_ownerID = $user->getID();
		$this->_owner = $user;
		return true;
	}

	function getKeywordLists() {
		GLOBAL $db;
		
		$queryStr = "SELECT * FROM phpgw_mydms_Keywords WHERE category = " . $this->_id;
		return $db->getResultArray($queryStr);
	}

	function editKeywordList($listID, $keywords) {
		GLOBAL $db;
		
		$queryStr = "UPDATE phpgw_mydms_Keywords SET keywords = '$keywords' WHERE id = $listID";
		return $db->getResult($queryStr);
	}

	function addKeywordList($keywords) {
		GLOBAL $db;
		
		$queryStr = "INSERT INTO phpgw_mydms_Keywords (category, keywords) VALUES (" . $this->_id . ", '$keywords')";
		return $db->getResult($queryStr);
	}

	function removeKeywordList($listID) {
		GLOBAL $db;
		
		$queryStr = "DELETE FROM phpgw_mydms_Keywords WHERE id = $listID";
		return $db->getResult($queryStr);
	}

	function remove()
	{
		GLOBAL $db;
		
		$queryStr = "DELETE FROM phpgw_mydms_Keywords WHERE category = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		$queryStr = "DELETE FROM phpgw_mydms_KeywordCategories WHERE id = " . $this->_id;
		if (!$db->getResult($queryStr))
			return false;
		
		return true;
	}
}


?>