<?php
// $Id$
if(empty($Save) && empty($Block) && empty($Unblock)) {
$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
} else {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
}
include ("../../header.inc.php"); #PHP Groupware header

include ("../config.php");

if ($GLOBALS['phpgw_info']['user']['apps']['admin']) {
	chdir('..');
	require('action/admin.php');
}
else {
		echo "You are not authorized to access this page.<br><a href=\"";
		echo $GLOBALS['phpgw']->link("/wiki/index.php");
		echo "\">Go Back</a>";
}
?>
