<?php
/**************************************************************************\
* eGroupWare - XML-RPC Test App                                            *
* http://www.egroupware.org                                                *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

	$GLOBALS['egw_info'] = array();
	$GLOBALS['egw_info']['flags'] = array(
		'currentapp'  => 'xmlrpc',
		'noheader'    => False,
		'noappheader' => False,
		'nonavbar'    => False
	);

	include('../header.inc.php');

	echo '<br><a href="' . $GLOBALS['egw']->link('/xmlrpc/testsuite.php') . '">' . lang('Test Suite') . '</a>' . "\n";
	echo '<br><a href="' . $GLOBALS['egw']->link('/xmlrpc/introspect.php') . '">' . lang('Introspection') . '</a>' . "\n";
	echo '<br><a href="' . $GLOBALS['egw']->link('/xmlrpc/interserv.php') . '">' . lang('Client / Server test') . '</a>' . "\n";
	echo '<br><a href="' . $GLOBALS['egw']->link('/xmlrpc/phpgw_test.php') . '">' . lang('Server test') . '</a>' . "\n";
	echo '<br><a href="' . $GLOBALS['egw']->link('/xmlrpc/client.php') . '">' . lang('Simple Client') . '</a>' . "\n";

	if ($GLOBALS['egw']->acl->check('run',1,'meerkat'))
	{
		echo '<br><a href="' . $GLOBALS['egw']->link('/meerkat/index.php') . '">' . lang('Meerkat Browser') . '</a>' . "\n";
	}

	$GLOBALS['egw']->common->phpgw_footer();
?>
