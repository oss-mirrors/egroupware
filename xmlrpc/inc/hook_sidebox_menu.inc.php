<?php
  /**************************************************************************\
  * eGroupWare - xmlrpc's Sidebox-Menu for idots-template                    *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

 /*
	This hookfile is for generating an app-specific side menu used in the idots 
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

	$menu_title = $GLOBALS['egw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array(
		'Test Suite'    => $GLOBALS['egw']->link('/xmlrpc/testsuite.php'),
		'Introspection' => $GLOBALS['egw']->link('/xmlrpc/introspect.php'),
		'client / server test' => $GLOBALS['egw']->link('/xmlrpc/interserv.php'),
		'server test'   => $GLOBALS['egw']->link('/xmlrpc/phpgw_test.php'),
		'Simple Client' => $GLOBALS['egw']->link('/xmlrpc/client.php')
	);

	if ($GLOBALS['egw']->acl->check('run',1,'meerkat'))
	{
		$file['Meerkat Browser'] = $GLOBALS['egw']->link('/meerkat/index.php');
	}
	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['egw_info']['user']['apps']['admin'] && !$GLOBALS['egw']->acl->check('peer_server_access',1,'admin'))
	{
		$menu_title = lang('Administration');
		$file = array(
			'Peer Servers' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiserver.list_servers')
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
