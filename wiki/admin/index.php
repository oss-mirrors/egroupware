<?php
// $Id$
if(empty($Save) && empty($Block) && empty($Unblock)) {
$phpgw_info["flags"] = array ("currentapp" => "axiswiki",
							 "enable_nextmatchs_class" => True );
} else {
				$phpgw_info["flags"] = array ("currentapp" => "axiswiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
}
include ("../../header.inc.php"); #PHP Groupware header

include ("../config.php");
include("../dbconnect.php");

$accountid = get_account_id();
$result = mysql_query("SELECT account_lid FROM phpgw_accounts WHERE account_id='$accountid'");
$row = mysql_fetch_array($result);
$account_lid = $row["account_lid"];
	
if ($GLOBALS['phpgw_info']['user']['apps']['admin']) {
	chdir('..');
	require('action/admin.php');
}
else {
		echo "You are not authorized to access this page.<br><a href=\"";
		echo $GLOBALS['phpgw']->link("/axiswiki/index.php");
		echo "\">Go Back</a>";
}
?>
