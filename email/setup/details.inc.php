<?php
	include('../version.inc.php');
	$phpgw_info['setup']['email']['name'] = 'email';
	$phpgw_info['setup']['email']['version'] = $phpgw_info['server']['versions']['email'];
	$phpgw_info['setup']['email']['app_order'] = '10';
	$phpgw_info['setup']['email']['tables'] = "";
	$hooks = Array();
	$hooks_string = implode (',', $hooks);
	$phpgw_info['setup']['email']['hooks'] = $hooks_string;
?>