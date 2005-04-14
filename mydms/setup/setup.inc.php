<?php
	$setup_info['mydms']['name']      = 'mydms';
	$setup_info['mydms']['title']     = 'mydms';
	$setup_info['mydms']['version']   = '0.1.0.001';
	$setup_info['mydms']['app_order'] = 5;
	$setup_info['mydms']['enable']    = 1;
        
        /* hook for mydms */
	$setup_info['messenger']['hooks'][] = 'sidebox_menu';
?>
