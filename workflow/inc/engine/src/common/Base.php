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
  //2 vars for debugging
  var $num_queries = 0;
  var $num_queries_total = 0;
  var $error= Array(); // the error messages array
  var $child_name = 'Base'; //name of the current object
  
  // Constructor receiving a database abstraction object.
  function Base(&$db)
  {
    if(!$db) {
      die('Invalid db object passed to '.$this->child_name.' constructor');
    }
    //Force transactionnal mysql (Innodb) -> mysqlt
    if ($db->databaseType=='mysql')
    {
    	$GLOBALS['egw']->db->disconnect();
    	$db = $GLOBALS['egw']->db->connect(
			$GLOBALS['egw_info']['server']['db_name'],
			$GLOBALS['egw_info']['server']['db_host'],
			$GLOBALS['egw_info']['server']['db_port'],
			$GLOBALS['egw_info']['server']['db_user'],
			$GLOBALS['egw_info']['server']['db_pass'],
			'mysqlt'
		);
    }
    $this->db = &$db;
  }

  //! return errors recorded by this object
  /*!
  * You should always call this function after failed operations on a workflow object to obtain messages
  * @param $as_array if true the result will be send as an array of errors or an empty array. Else, if you do not give any parameter 
  * or give a false parameter you will obtain a single string which can be empty or will contain error messages with <br /> html tags.
  * @param $debug is false by default, if true you wil obtain more messages
  * @return a string containing error (and maybe debug) messages or an array of theses messages and empty the error messages
  * @param $prefix is a string appended to the debug message
  */
  function get_error($as_array=false, $debug=false, $prefix='') 
  {
    //collect errors from used objects
    $this->collect_errors($debug, $prefix.$this->child_name.'::');
    if ($as_array)
    {
      $result = $this->error;
      $this->error= Array();
      return $result;
    }
    $result_str = implode('<br />',array_filter($this->error));
    $this->error= Array();
    return $result_str;
  }

  /*!
  * @abstract
  * Collect errors from all linked objects which could have been used by this object
  * Each child class should instantiate this function with her linked objetcs, calling get_error(true)
  * for example if you had a $this->process_manager created in the constructor you shoudl call
  * $this->error[] = $this->process_manager->get_error(false, $debug, $prefix);
  * @param $debug is false by default, if true debug messages can be added to 'normal' messages
  * @param $prefix is a string appended to the debug message
  */
  function collect_errors($debug=false, $prefix = '')
  {
  	if ($debug)
  	{
  		$this->num_queries_total += $this->num_queries;
  		$this->error[] = $prefix.': number of queries: new='.$this->num_queries.'/ total='.$this->num_queries_total;
  		$this->num_queries = 0;
	}
  }
	
/*	function query($query, $values = null, $numrows = -1, $offset = -1, $reporterrors = true) {
		#$result = $this->db->query($query, $line = __LINE__, $file = __FILE__, $offset=$offset, $num_rows=$numrows,$inputarr=$values);
		$this->num_queries++;
		if (!$result)
		{
			$this->error[] = "they were some SQL errors in the database, please warn your sysadmin.";
			if ($reporterrors) $this->sql_error($query, $values, $result);
		}
		return $result;

	} 
*/
	//! perform a query on the AdoDB database object
	/*! initially copied from tikilib.php. Modifications for galaxia
	* @param $query
	* @param $values
	* @param $numrows
	* @param $offset
	* @param $reporterrors
	* @return false if something went wrong or the resulting recordset array if it was ok
	*/
	function query($query, $values = null, $numrows = -1, $offset = -1, $reporterrors = true) 
	{
		$this->convert_query($query);
		$clean_values = Array();
		foreach($values as $value)
		{
			$clean_values[] = $this->security_cleanup($value);
		}
		// Galaxia needs to be call ADOdb in associative mode
		$this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		
		if ($numrows == -1 && $offset == -1)
			$result = $this->db->Execute($query, $clean_values);
		else
			$result = $this->db->SelectLimit($query, $numrows, $offset, $clean_values);
		if (empty($result))
		{
			$result = false;
		}
		$this->num_queries++;
		if (!$result)
		{
			$this->error[] = "they were some SQL errors in the database, please warn your sysadmin.";
			if ($reporterrors) $this->sql_error($query, $clean_values, $result);
		}
		return $result;
	}

	
	function getOne($query, $values = null, $reporterrors = true) {
		$this->convert_query($query);
		$clean_values = Array();
		foreach($values as $value)
		{
			$clean_values[] = $this->security_cleanup($value);
		}
		$result = $this->db->SelectLimit($query, 1, 0, $clean_values);
		if (empty($result))
		{
			$result = false;
		}
		if (!$result && $reporterrors )
			$this->sql_error($query, $clean_values, $result);
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
		// DO NOT DIE, if transactions are there, they will do things in a better way
	}
	
	/*! Clean the data before it is recorded on the database
	* @param $value is a data we want to be stored in the database.
	*	- If it is an array we'll make a serialize and then an base64_encode 
	*	  (you'll have to make an unserialize(base64_decode())
	*	- If it is not an array we make an htmlspecialchars() on it
	* @return the resulting value, ready for an ADODB query
	*/
	function security_cleanup($value)
	{
		if (is_array($value))
		{
			//serialize and \' are a big #!%*
			$res = base64_encode(serialize($value));
		}
		else
		{
			$res=htmlspecialchars($value);
		}
		return $res;
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
/*
	function qstr($string, $quoted = null)
	{
		if (!isset($quoted)) {
			$quoted = get_magic_quotes_gpc();
		}
		return $this->db->qstr($string,$quoted);
	}
*/
} //end of class

?>
