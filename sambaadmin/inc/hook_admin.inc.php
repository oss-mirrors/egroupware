<?php

	{
		$file = Array
		(
			'Site Configuration'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'check ldap setup (experimental!!!)'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=sambaadmin.uisambaadmin.checkLDAPSetup'),
		);
		display_section($appname,$appname,$file);
	}
?>
