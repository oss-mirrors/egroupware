<?php
	/* Method borrowed partially from:
	  http://sourceforge.net/tracker/index.php?func=detail&aid=427359&group_id=23199&atid=377731
	*/
	$phpgw_info['flags'] = array(
		'disable_Template_class' => True,
		'currentapp' => 'login',
		'noheader'   => True
	);

	include('../header.inc.php');
	/* call an xmlrpc method on a remote http server */ 

	$is = CreateObject('phpgwapi.interserver');
//	echo $is->_send_xmlrpc_ssl('system.listApps','','https://milosch.dyndns.org/phpgroupware/xmlrpc.php');
	echo $is->_send_xmlrpc_ssl('system.auth',array(
		'server_name' => 'Jengo',
		'username'    => 'bubba',
		'password'    => 'bubba'
		),
		'https://milosch.dyndns.org/phpgroupware/xmlrpc.php'
	);
?>
