<?php
	include('../version.inc.php');
	$phpgw_info['setup']['manual']['name'] = 'User Manual';
	$phpgw_info['setup']['manual']['version'] = $phpgw_info['server']['versions']['manual'];
	$phpgw_info['setup']['manual']['app_order'] = 5;
	$phpgw_info['setup']['manual']['tables'] = "";
	$hooks = Array();
	$hooks_string = implode (',', $hooks);
	$phpgw_info['setup']['manual']['hooks'] = $hooks_string;
?>