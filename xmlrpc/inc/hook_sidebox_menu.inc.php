<?php
  /**************************************************************************\
  * phpGroupWare - xmlrpc's Sidebox-Menu for idots-template                  *
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

	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array(
		'Test Suite'    => $GLOBALS['phpgw']->link('/xmlrpc/testsuite.php'),
		'Introspection' => $GLOBALS['phpgw']->link('/xmlrpc/introspect.php'),
		'client / server test' => $GLOBALS['phpgw']->link('/xmlrpc/interserv.php'),
		'server test'   => $GLOBALS['phpgw']->link('/xmlrpc/phpgw_test.php'),
		'Simple Client' => $GLOBALS['phpgw']->link('/xmlrpc/client.php')
	);

	if ($GLOBALS['phpgw']->acl->check('run',1,'meerkat'))
	{
		$file['Meerkat Browser'] = $GLOBALS['phpgw']->link('/meerkat/index.php');
	}
	display_sidebox($appname,$menu_title,$file);
}
?>
