<?php
	$setup_info['email']['name'] = 'email';
	$setup_info['email']['version'] = '0.9.11';
	$setup_info['email']['app_order'] = '2';
	$setup_info['email']['tables'] = "";
	$hooks = Array();
	$hooks_string = implode (',', $hooks);
	$setup_info['email']['hooks'] = $hooks_string;
?>