<?php
/* 
V4.51 29 July 2004  (c) 2000-2004 John Lim (jlim@natsoft.com.my). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence. 
Set tabs to 4 for best viewing.
  
  Latest version is available at http://adodb.sourceforge.net
  
  SAPDB data driver. Requires ODBC.

*/

// security - hide paths
if (!defined('ADODB_DIR')) die();

if (!defined('_ADODB_ODBC_LAYER')) {
	include(ADODB_DIR."/drivers/adodb-odbc.inc.php");
}
if (!defined('ADODB_SAPDB')){
define('ADODB_SAPDB',1);

class ADODB_SAPDB extends ADODB_odbc {
	var $databaseType = "sapdb";	
	var $concat_operator = '||';
	var $sysDate = 'DATE';
	var $sysTimeStamp = 'TIMESTAMP';
	var $fmtDate = "\\D\\A\\T\\E('Y-m-d')";	/// used by DBDate() as the default date format used by the database
	var $fmtTimeStamp = "\\T\\I\\M\\E\\S\\T\\A\\M\\P('Y-m-d','H:i:s')"; /// used by DBTimeStamp as the default timestamp fmt.
	var $hasInsertId = true;

	function ADODB_SAPDB()
	{
		//if (strncmp(PHP_OS,'WIN',3) === 0) $this->curmode = SQL_CUR_USE_ODBC;
		$this->ADODB_odbc();
	}
	
	function ServerInfo()
	{
		$info = ADODB_odbc::ServerInfo();
		if (!$info['version'] && preg_match('/([0-9.]+)/',$info['description'],$matches)) {
			$info['version'] = $matches[1];
		}
		return $info;
	}

 	function &MetaIndexes ($table, $primary = FALSE)
	{
		$table = $this->Quote(strtoupper($table));

		$sql = "SELECT INDEXNAME,TYPE,COLUMNNAME FROM INDEXCOLUMNS ".
			" WHERE TABLENAME=$table".
			" ORDER BY INDEXNAME,COLUMNNO";

		$save = $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
        if ($this->fetchMode !== FALSE) {
                $savem = $this->SetFetchMode(FALSE);
        }
        
        $rs = $this->Execute($sql);
        if (isset($savem)) {
                $this->SetFetchMode($savem);
        }
        $ADODB_FETCH_MODE = $save;

        if (!is_object($rs)) {
        	return FALSE;
        }

		$indexes = array();
		while ($row = $rs->FetchRow()) {
            $indexes[$row[0]]['unique'] = $row[1] == 'UNIQUE';
            $indexes[$row[0]]['columns'][] = $row[2];
    	}
		if ($primary) {
			$columns = array();
			foreach($this->GetAll("SELECT columnname FROM COLUMNS WHERE tablename=$table AND mode='KEY' ORDER BY pos") as $row) {
				$columns[] = $row['COLUMNNAME'];
			}
			$indexes['SYSPRIMARYKEYINDEX'] = array(
					'unique' => True,	// by definition
					'columns' => $columns,
				);
		}
        return $indexes;
	}

	// unlike it seems, this depends on the db-session and works in a multiuser environment
	function _insertid($table,$column)
	{
		return empty($table) ? False : $this->GetOne("SELECT $table.CURRVAL FROM DUAL");
	}

	/*
		SelectLimit implementation problems:
	
	 	The following will return random 10 rows as order by performed after "WHERE rowno<10"
	 	which is not ideal...
		
	  		select * from table where rowno < 10 order by 1
	  
	  	This means that we have to use the adoconnection base class SelectLimit when
	  	there is an "order by".
		
		See http://listserv.sap.com/pipermail/sapdb.general/2002-January/010405.html
	 */
	
};
 

class  ADORecordSet_sapdb extends ADORecordSet_odbc {	
	
	var $databaseType = "sapdb";		
	
	function ADORecordSet_sapdb($id,$mode=false)
	{
		$this->ADORecordSet_odbc($id,$mode);
	}
}

} //define
?>