<?php
// $Id$

// Perform a lookup on an IP addresses edit-rate.
function rateCheck($db, $type)
{
  global $RatePeriod, $RateView, $RateSearch, $RateEdit, $REMOTE_ADDR;
  global $ErrorDeniedAccess, $ErrorRateExceeded, $RtTbl;

  $fields = explode(".", $REMOTE_ADDR);
  if($RatePeriod == 0)
    { return; }

  $db->query("LOCK TABLES $RtTbl WRITE",__LINE__,__FILE__);

  // Make sure this IP address hasn't been excluded.

  $qid = $db->query("SELECT * FROM $RtTbl WHERE ip='$fields[0].*'",__LINE__,__FILE__);
  if($db->result($qid))
    { die($ErrorDeniedAccess); }
  $qid = $db->query("SELECT * FROM $RtTbl WHERE ip='$fields[0].$fields[1].*'",__LINE__,__FILE__);
  if($db->result($qid))
    { die($ErrorDeniedAccess); }
  $qid = $db->query("SELECT * FROM $RtTbl " .
                    "WHERE ip='$fields[0].$fields[1].$fields[2].*'",__LINE__,__FILE__);
  if($db->result($qid))
    { die($ErrorDeniedAccess); }

  // Now check how many more actions we can perform.

  $qid = $db->query(//"SELECT TIME_TO_SEC(NOW()) - TIME_TO_SEC(time), " .
                    "SELECT time, " .
                    "viewLimit, searchLimit, editLimit FROM $RtTbl " .
                    "WHERE ip='$REMOTE_ADDR'",__LINE__,__FILE__);
  
  if(!($result = $db->result($qid)))
    { $result = array(-1, $RateView, $RateSearch, $RateEdit); }
  else
  {
    $result[0] = time()-$result[0];
	if($result[0] < 0)
      { $result[0] = $RatePeriod; }
    $result[1] = min($result[1] + $result[0] * $RateView / $RatePeriod,
                     $RateView);
    $result[2] = min($result[2] + $result[0] * $RateSearch / $RatePeriod,
                     $RateSearch);
    $result[3] = min($result[3] + $result[0] * $RateEdit / $RatePeriod,
                     $RateEdit);
  }

  if($type == 'view')
    { $result[1]--; }
  else if($type == 'search')
    { $result[2]--; }
  else if($type == 'edit')
    { $result[3]--; }

  if($result[1] < 0 || $result[2] < 0 || $result[3] < 0)
    { die($ErrorRateExceeded); }

  // Record this action.

  if($result[0] == -1)
  {
    $db->query("INSERT INTO $RtTbl VALUES('$REMOTE_ADDR', " .
               time()./*"NULL*/", $result[1], $result[2], $result[3])",__LINE__,__FILE__);
  }
  else
  {
    $db->query("UPDATE $RtTbl SET viewLimit=$result[1], " .
               "searchLimit=$result[2], editLimit=$result[3] " .
			   ', time='.time().' '.
               "WHERE ip='$REMOTE_ADDR'",__LINE__,__FILE__);
  }

  $db->query("UNLOCK TABLES",__LINE__,__FILE__);
}

// Return a list of blocked address ranges.
function rateBlockList($db)
{
  global $RatePeriod, $RtTbl;

  $list = array();

  if($RatePeriod == 0)
    { return $list; }

  $qid = $db->query("SELECT ip FROM $RtTbl",__LINE__,__FILE__);
  while(($result = $db->result($qid)))
  {
    if(preg_match('/^\\d+\\.(\\d+\\.(\\d+\\.)?)?\\*$/', $result[0]))
      { $list[] = $result[0]; }
  }

  return $list;
}

// Block an address range.
function rateBlockAdd($db, $address)
{
  global $RtTbl;

  if(!preg_match('/^\\d+\\.(\\d+\\.(\\d+\\.)?)?\\*$/', $address))
    { return; }
  $qid = $db->query("SELECT * FROM $RtTbl WHERE ip='$address'",__LINE__,__FILE__);
  if($db->result($qid))
    { return; }
  $db->query("INSERT INTO $RtTbl(ip,time) VALUES('$address',".time().")",__LINE__,__FILE__);
}

function rateBlockRemove($db, $address)
{
  global $RtTbl;

  $db->query("DELETE FROM $RtTbl WHERE ip='$address'",__LINE__,__FILE__);
}
?>
