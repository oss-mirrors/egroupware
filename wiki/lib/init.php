<?php
// $Id$

// General initialization code.

require('lib/defaults.php');
require('config.php');
require('lib/url.php');
require('lib/messages.php');
/*
require('lib/pagestore.php');
require('lib/rate.php');

$PgTbl = $DBTablePrefix . 'pages';
$IwTbl = $DBTablePrefix . 'interwiki';
$SwTbl = $DBTablePrefix . 'sisterwiki';
$LkTbl = $DBTablePrefix . 'links';
$RtTbl = $DBTablePrefix . 'rate';
$RemTbl = $DBTablePrefix . 'remote_pages';

$pagestore = new PageStore();
$db = $pagestore->dbh;
*/
$pagestore = CreateObject('wiki.sowiki');

$FlgChr = chr(255);                     // Flag character for parse engine.

$Entity = array();                      // Global parser entity list.

// Strip slashes from incoming variables.

if(get_magic_quotes_gpc())
{
  $document = stripslashes($document);
  $categories = stripslashes($categories);
  $comment = stripslashes($comment);
  $page = stripslashes($page);
}

// Read user preferences from cookie.

$prefstr = isset($HTTP_COOKIE_VARS[$CookieName])
           ? $HTTP_COOKIE_VARS[$CookieName] : '';

if(!empty($prefstr))
{
  if(ereg("rows=([[:digit:]]+)", $prefstr, $result))
    { $EditRows = $result[1]; }
  if(ereg("cols=([[:digit:]]+)", $prefstr, $result))
    { $EditCols = $result[1]; }
  if(ereg("user=([^&]*)", $prefstr, $result))
    { $UserName = urldecode($result[1]); }
  if(ereg("days=([[:digit:]]+)", $prefstr, $result))
    { $DayLimit = $result[1]; }
  if(ereg("auth=([[:digit:]]+)", $prefstr, $result))
    { $AuthorDiff = $result[1]; }
  if(ereg("min=([[:digit:]]+)", $prefstr, $result))
    { $MinEntries = $result[1]; }
  if(ereg("hist=([[:digit:]]+)", $prefstr, $result))
    { $HistMax = $result[1]; }
  if(ereg("tzoff=([[:digit:]]+)", $prefstr, $result))
    { $TimeZoneOff = $result[1]; }
}

#if($Charset != '')
#  { header("Content-Type: text/html; charset=$Charset"); }

?>
