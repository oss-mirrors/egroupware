<?php
$mysql_host =  $phpgw_info["server"]["db_host"];
$mysql_database =  $phpgw_info["server"]["db_name"];
$mysql_login = $phpgw_info["server"]["db_user"];
$mysql_password =  $phpgw_info["server"]["db_pass"];

mysql_connect("$mysql_host", "$mysql_login", "$mysql_password") or
	die("Could not connect to database");
mysql_select_db("$mysql_database") or
	die("Could not select database");
?>
