<?php
	/**************************************************************************\
	* phpGroupWare - phpgw common help                                         *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	include(PHPGW_SERVER_ROOT.'/'.'phpgwapi'.'/setup/setup.inc.php');

	$GLOBALS['phpgw']->help->set_params(array('app_name'		=> 'manual',
												'title'			=> phpGroupWare,
												'app_version'	=> 'API ' . $setup_info['phpgwapi']['version']));

	$GLOBALS['phpgw']->help->data[] = array
	(
		'text'					=> lang('owerview'),
		'link'					=> $GLOBALS['phpgw']->help->check_help_file('overview.php'),
		'lang_link_statustext'	=> lang('owerview')
	);

	$GLOBALS['phpgw']->help->data[] = array
	(
		'text'					=> lang('home'),
		'link'					=> $GLOBALS['phpgw']->help->check_help_file('home.php'),
		'lang_link_statustext'	=> lang('home')
	);

	$GLOBALS['phpgw']->help->draw();
?>
