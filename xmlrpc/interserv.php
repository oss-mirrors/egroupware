<?php
	/* Method borrowed partially from:
	  http://sourceforge.net/tracker/index.php?func=detail&aid=427359&group_id=23199&atid=377731
	*/
	$phpgw_info['flags'] = array(
		'disable_Template_class' => True,
		'currentapp' => 'login'
	);

	include('../header.inc.php');
	/* call an xmlrpc method on a remote http server */ 

	$is = CreateObject('phpgwapi.interserver',$server_id);

	if($submit)
	{
		_debug_array($is->server);
		echo $is->send(
			'system.auth', array(
				'server_name' => $HTTP_HOST,
				'username'    => $is->server['username'],
				'password'    => $is->server['password']
			),
			$is->server['server_url']
		);
	}

	echo '<form action="' . $phpgw->link('/xmlrpc/interserv.php') . '">' . "\n";
	echo $is->formatted_list() . "\n";
	echo '<input type="submit" name="submit" value="Login">' . "\n";
	echo '</form>' . "\n";
?>
