<?php
	$setup_info['email']['name']      = 'email';
	$setup_info['email']['title']     = 'Email';
	$setup_info['email']['version']   = '0.9.11';
	$setup_info['email']['app_order'] = '2';
	$setup_info['email']['tables']    = "";

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['email']['hooks'][] = 'preferences';
	$setup_info['email']['hooks'][] = 'admin';

	/* Dependacies for this app to work */
	$setup_info['email']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13')
	);
?>
