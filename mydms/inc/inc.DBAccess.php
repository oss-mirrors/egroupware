<?php
///////////////////////////////////////////////////////////////////////
// We dont use ths db class any more, and use the egroupware db      //
// class instead, rewritten by Shang Wenbin <wbsh@realss.com>        //
///////////////////////////////////////////////////////////////////////

//include $settings->_ADOdbPath . "adodb.inc.php";

class DatabaseAccess
{
	var $db;
	/**
	 * just copy the existent class $GLOBALS['phpgw']->db
	 */
	function connect()
	{
		$this->db = clone $GLOBALS['egw']->db;
	}

	/**
	 * always return true
	 */
	function ensureConnected()
	{
		if(!is_object($this->db))
		{
			$this->connect();
		}
		return true;
	}

	function getResultArray($queryStr)
	{
		$resArr = array();

		$res = $this->db->query($queryStr,__LINE__,__FILE__);
		$resArr = $res->GetArray();
		$res->Close();
		return $resArr;
	}

	/**
	 * seems only used for update query
	 */
	function getResult($queryStr)
	{
		return $this->db->query($queryStr,__LINE__,__FILE__);
	}

	/**
	 * get the last insert id
	 * @param string $table
	 * @param string $column
	 * @return int
	 */
	function getInsertID($table,$column)
	{
		return $this->db->get_last_insert_id($table,$column);
	}

}


$db = new DatabaseAccess();
$db->connect();

if (!isset($GLOBALS['mydms'])) $GLOBALS['mydms'] = new stdClass();
$GLOBALS['mydms']->db = new DatabaseAccess();
$GLOBALS['mydms']->db->connect();

?>
