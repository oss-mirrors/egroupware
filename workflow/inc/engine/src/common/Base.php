<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'common'.SEP.'Observable.php');
//!! Abstract class representing the base of the API
//! An abstract class representing the API base
/*!
This class is derived by all the API classes so they get the
database connection, database methods and the Observable interface.
*/
class Base extends Observable {
  var $db;  // The database abstraction object used to access the database
  var $num_queries = 0;
  var $error= Array(); // the error messages array

  // Constructor receiving a database abstraction object.
  function Base(&$db)
  {
    if(!$db) {
      die("Invalid db object passed to Base constructor");
    }
    $this->db = &$db;
  }

  //! return errors recorded by this object
  /*!
  * You should always call this function after failed operations on a workflow object to otain messages
  * @param $as_array if true the result will be send as an array of errors or an empty array. Else, if you do not give any parameter 
  * or give a false parameter you will obtain a single string which can be empty
  * or will contain error messages with <br /> html tags.
  */
  function get_error($as_array=false) 
  {
    if ($as_array)
    {
      return $this->error;
    }
    $result_str = implode('<br />',$this->error);
    $this->error= Array();
    return $result_str;
  }

	// copied from tikilib.php
	function query($query, $values = null, $numrows = -1, $offset = -1, $reporterrors = true) {
		$this->convert_query($query);

		// Galaxia needs to be call ADOdb in associative mode
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		
		if ($numrows == -1 && $offset == -1)
			$result = $this->db->Execute($query, $values);
		else
			$result = $this->db->SelectLimit($query, $numrows, $offset, $values);
		if (empty($result))
		{
			$result = false;
		}
		if (!$result && $reporterrors)
			$this->sql_error($query, $values, $result);
		$this->num_queries++;
		return $result;
	}

	
	function getOne($query, $values = null, $reporterrors = true) {
		$this->convert_query($query);
		$result = $this->db->SelectLimit($query, 1, 0, $values);
		if (empty($result))
		{
			$result = false;
		}
		if (!$result && $reporterrors )
			$this->sql_error($query, $values, $result);
		if (!!$result) 
		{
			$res = $result->fetchRow();
		}
		else
		{
			$res = false;
		}
		$this->num_queries++;
		if ($res === false)
			return (NULL); //simulate pears behaviour
		list($key, $value) = each($res);
		return $value;
	}

	function sql_error($query, $values, $result) {
		global $ADODB_LASTDB;

		trigger_error($ADODB_LASTDB . " error:  " . $this->db->ErrorMsg(). " in query:<br/>" . $query . "<br/>", E_USER_WARNING);
		$this->error[] = "they were some SQL errors in the database, please warn you admin system.";
		// only for debugging.
		//print_r($values);
		//echo "<br/>";
		// DO NOT DIE, if transactions are there, they will do things in a better way
		//die;
	}

	// functions to support DB abstraction
	function convert_query(&$query) {
		global $ADODB_LASTDB;

		switch ($ADODB_LASTDB) {
		case "oci8":
			$query = preg_replace("/`/", "\"", $query);
			// convert bind variables - adodb does not do that 
			$qe = explode("?", $query);
			$query = '';
			for ($i = 0; $i < sizeof($qe) - 1; $i++) {
				$query .= $qe[$i] . ":" . $i;
			}
			$query .= $qe[$i];
			break;
		case "postgres7":
		case "sybase":
			$query = preg_replace("/`/", "\"", $query);
			break;
		}
	}

	function convert_sortmode($sort_mode) {
		global $ADODB_LASTDB;

		$sort_mode = str_replace("__", "` ", $sort_mode);
		$sort_mode = "`" . $sort_mode;
		return $sort_mode;
	}

	function convert_binary() {
		global $ADODB_LASTDB;

		switch ($ADODB_LASTDB) {
		case "pgsql72":
		case "oci8":
		case "postgres7":
			return;
			break;
		case "mysql3":
		case "mysql":
			return "binary";
			break;
		}
	}

	function qstr($string, $quoted = null)
	{
		if (!isset($quoted)) {
			$quoted = get_magic_quotes_gpc();
		}
		return $this->db->qstr($string,$quoted);
	}

} //end of class

?>
