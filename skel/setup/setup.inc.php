<?php
	$setup_info['skel']['name'] = 'Skeleton';
	$setup_info['skel']['version'] = '0.0.1.000';
	$setup_info['skel']['app_order'] = 8;
	$setup_info['skel']['tables'] = "";
	$hooks = Array();
	$hooks_string = implode (',', $hooks);
	$setup_info['notes']['skel'] = $hooks_string;
?>