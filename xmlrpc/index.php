<?php
/**************************************************************************\
* phpGroupWare - XML-RPC Test App                                          *
* http://www.phpgroupware.org                                              *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'  => 'xmlrpc',
		'noheader'    => False,
		'noappheader' => False,
		'nonavbar'    => False
	);

	include('../header.inc.php');

	echo '<br><a href="' . $phpgw->link('/xmlrpc/testsuite.php') . '">' . lang('Test Suite') . '</a>' . "\n";
	echo '<br><a href="' . $phpgw->link('/xmlrpc/phpgw_test.php') . '">' . lang('phpgw server test') . '</a>' . "\n";
	echo '<br><a href="' . $phpgw->link('/xmlrpc/client.php') . '">' . lang('Simple Client') . '</a>' . "\n";
	echo '<br><a href="' . $phpgw->link('/meerkat/index.php') . '">' . lang('Meerkat Browser') . '</a>' . "\n";

	$phpgw->common->phpgw_footer();
?>
