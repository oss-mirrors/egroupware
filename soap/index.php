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

	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'soap',
		'noheader'    => False,
		'noappheader' => False,
		'nonavbar'    => False
	);
	include('../header.inc.php');

	echo '<br><a href="' . $GLOBALS['phpgw']->link('/soap/test_methods.php') . '">' . lang('Test Suite') . '</a>' . "\n";
	echo '<br><a href="' . $GLOBALS['phpgw']->link('/soap/interop_harness.php') . '">' . lang('Interop Tests') . '</a>' . "\n";

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
