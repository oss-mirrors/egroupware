<?php
	/* Basic information about this app */
	$setup_info['skel']['name']      = 'skel';
	$setup_info['skel']['title']     = 'Skeleton';
	$setup_info['skel']['version']   = '0.0.1.000';
	$setup_info['skel']['app_order'] = 8;
	
	/* The tables this app creates */
	$setup_info['skel']['tables']    = Array('skel');

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['skel']['hooks'][] = 'preferences';

	/* Dependacies for this app to work */
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'phpgwapi',
			 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13')
		);
	$setup_info['skel']['depends'][] = array(
			 'appname' => 'email',
			 'versions' => Array('0.9.10', '0.9.11' , '0.9.12', '0.9.13')
		);
?>