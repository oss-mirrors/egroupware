<?

include $settings->_ADOdbPath . "adodb.inc.php";

/**********************************************************************\
|                     Klasse zum Datenbankzugriff                      |
\**********************************************************************/

//Zugriff erfolgt auf MySQL-Server


class DatabaseAccess
{
	var $_driver;
	var $_hostname;
	var $_database;
	var $_user;
	var $_passw;
	var $_conn;
	var $_connected;

	/**
	 * Konstruktor
	 */
	function DatabaseAccess($driver, $hostname, $user, $passw, $database = false)
	{
		$this->_driver = $driver;
		$this->_hostname = $hostname;
		$this->_database = $database;
		$this->_user = $user;
		$this->_passw = $passw;
		$this->_connected = false;
	}

	/**
	 * Baut Verbindung zur Datenquelle auf und liefert
	 * true bei Erfolg, andernfalls false
	 */
	function connect()
	{
		$this->_conn = ADONewConnection($this->_driver);
		if ($this->_database)
			$this->_conn->Connect($this->_hostname, $this->_user, $this->_passw, $this->_database);
		else
			$this->_conn->Connect($this->_hostname, $this->_user, $this->_passw);
		
		if (!$this->_conn)
			return false;
		
		$this->_connected = true;
		return true;
	}

	/**
	 * Stellt sicher, dass eine Verbindung zur Datenquelle aufgebaut ist
	 * true bei Erfolg, andernfalls false
	 */
	function ensureConnected()
	{
		if (!$this->_connected) return $this->connect();
		else return true;
	}

	/**
	 * Führt die SQL-Anweisung $queryStr aus und liefert das Ergebnis-Set als Array (d.h. $queryStr
	 * muss eine select-anweisung sein).
	 * Falls die Anfrage fehlschlägt wird false geliefert
	 */
	function getResultArray($queryStr)
	{
		//print "<!-- " . $query_str . "-->";
		$resArr = array();
		
		$res = $this->_conn->Execute($queryStr);
		if (!$res) {
			print "<br>" . $this->getErrorMsg() . ": " . $queryStr . "</br>";
			return false;
		}
		$resArr = $res->GetArray();
		$res->Close();
		return $resArr;
	}

	/**
	 * Führt die SQL-Anweisung $queryStr aus (die kein ergebnis-set liefert, z.b. insert, del usw) und
	 * gibt das resultat zurück
	 */
	function getResult($queryStr)
	{
//		print $queryStr . "<p>";
		$res = $this->_conn->Execute($queryStr);
		if (!$res)
			print "<br>" . $this->getErrorMsg() . ": " . $queryStr . "</br>";
		
		return $res;
	}

	function getInsertID()
	{
		return $this->_conn->Insert_ID();
	}

	function getErrorMsg()
	{
		return $this->_conn->ErrorMsg();
	}
}


$db = new DatabaseAccess($settings->_dbDriver, $settings->_dbHostname, $settings->_dbUser, $settings->_dbPass, $settings->_dbDatabase);
$db->connect() or die ("Could not connect to db-server \"" . $settings->_dbHostname . "\"");