<?php
// $Id$

// MySQL database abstractor.  It should be easy to port this to other
//   databases, such as PostgreSQL.
class WikiDB
{
  var $handle;

  function WikiDB($persistent, $server, $user, $pass, $database)
  {
/*
	global $ErrorDatabaseConnect, $ErrorDatabaseSelect;

    if($persistent)
      { $this->handle = mysql_pconnect($server, $user, $pass); }
    else
      { $this->handle = mysql_connect($server, $user, $pass); }

    if($this->handle <= 0)
      { die($ErrorDatabaseConnect); }

    if(mysql_select_db($database, $this->handle) == false)
      { die($ErrorDatabaseSelect); }
*/
	$this->handle = $GLOBALS['phpgw']->db;
  }

  function query($text,$line='',$file='')
  {
/*
    global $ErrorDatabaseQuery;

    if(!($qid = mysql_query($text, $this->handle)))
      { die($ErrorDatabaseQuery.", '$text'"); }
    return $qid;
*/
	return $this->handle->query($text,$line,$file);
  }

  function result($qid)
  {
//    return mysql_fetch_row($qid);
	return $this->handle->next_record() ? $this->handle->Record : False;
  }
}
?>
