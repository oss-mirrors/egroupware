<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Michael Totschnig                                                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

$GLOBALS['phpgw_info']['flags'] = array(
					'currentapp' => 'bookmarks',
					'noheader'   => True,
					'nonavbar'   => True,
					'nofooter' => True,
					'enable_categories_class' => True
	);

include('../header.inc.php');

$bo = createobject('bookmarks.exportbo');

if ($_POST['export'])
{
	#  header("Content-type: text/plain");
	header("Content-type: application/octet-stream");

	if ($_POST['exporttype'] == 'Netscape/Mozilla')
	{
		header("Content-Disposition: attachment; filename=bookmarks.html");

		echo $bo->export($_POST['bmcategory'],'ns');
	}
	else
	{
		header("Content-Disposition: attachment; filename=bookmarks.xbel");

		echo $bo->export($_POST['bmcategory'],'xbel');
	}
}

else
{
	$GLOBALS['phpgw']->common->phpgw_header();
	echo parse_navbar();
	$t = $GLOBALS['phpgw']->template;
	$t->set_file('body','export.body.tpl');
	$t->set_var(Array(
		'FORM_ACTION' => $GLOBALS['phpgw']->link('/bookmarks/export.php'),
		'input_categories' => $bo->categories_list_main()
	));
	$t->pfp('out','body');
	$GLOBALS['phpgw']->common->phpgw_footer();
}

?>
