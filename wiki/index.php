<?php
// $Id$
$action = $HTTP_GET_VARS['action'];
switch ($action) {
	case 'edit':
		$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
	break;
	case 'save':
			if ($HTTP_POST_VARS['Preview'] == 'Preview') { 
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
				$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
			} else {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
				$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";
			}
	break;
	case 'prefs': 
			if (empty($Save)) {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
			} else {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
			}
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";
	break;
	default:
		$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";

}

include ("../header.inc.php"); #PHP Groupware header

require('lib/main.php');

/*
include("dbconnect.php");
$accountid = get_account_id();
$result = mysql_query("SELECT account_lid FROM phpgw_accounts WHERE account_id='$accountid'");
$row = mysql_fetch_array($result);
$account_lid = $row["account_lid"];
	
if ($account_lid == $admin1 or $account_lid == $admin2)	{
    echo "<br><a href=\"";
	echo $GLOBALS['phpgw']->link("/wiki/admin/index.php");
	echo "\">Admin Page</a><br><br>";
} */

?>
