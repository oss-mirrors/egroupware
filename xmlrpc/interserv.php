<?php
	/* Method borrowed partially from:
	  http://sourceforge.net/tracker/index.php?func=detail&aid=427359&group_id=23199&atid=377731
	*/
	$phpgw_info['flags'] = array(
		'currentapp' => 'login',
		'disable_Template_class' => True
	);

	include('../header.inc.php');
	/* call an xmlrpc method on a remote http server */ 

	$is = CreateObject('phpgwapi.interserver',$server_id);

//	_debug_array($is->server);
	if($login)
	{
		$is->send(
			'system.auth', array(
				'server_name' => $HTTP_HOST,
				'username'    => $is->server['username'],
				'password'    => $is->server['password']
			),
			$is->server['server_url']
		);
		_debug_array($is->result);
		list($x,$xsessionid,$y,$xkp3) = $is->result;
	}
	elseif($verify)
	{
		$is->send(
			'system.auth_verify', array(
				'server_name' => $HTTP_HOST,
				'sessionid'   => $xsessionid,
				'kp3'         => $xkp3
			),
			$is->server['server_url']
		);
	}

	echo '<table><tr><td>';
	echo '<form action="' . $phpgw->link('/xmlrpc/interserv.php') . '">' . "\n";
	echo $is->formatted_list() . "\n";
	echo '<input type="submit" name="login" value="Login">' . "\n";
	echo '<input type="submit" name="verify" value="Verify">' . "\n";
	echo '<input type="hidden" name="xsessionid" value="' . $xsessionid . '">' . "\n";
	echo '<input type="hidden" name="xkp3" value="' . $xkp3 . '">' . "\n";
	echo '</form>' . "\n";
	echo '</td></tr></table><td>';
?>
