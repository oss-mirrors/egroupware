<?php
	/**************************************************************************\
	* phpGroupWare - Stock Quotes                                              *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'stocks', 
		'enable_network_class' => True
	);

	include('../header.inc.php');

	$GLOBALS['phpgw']->template->set_file(array('quotes_list' => 'main.tpl'));
	$GLOBALS['phpgw']->template->set_var('quotes',return_quotes($quotes));
	$GLOBALS['phpgw']->template->pparse('out','quotes_list');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
