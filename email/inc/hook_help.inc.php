<?php
	/**************************************************************************\
	* phpGroupWare - help system                                               *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	include(PHPGW_SERVER_ROOT.'/'.'email'.'/setup/setup.inc.php');

	$GLOBALS['phpgw']->help->set_params(array('app_name'		=> 'email',
												'title'			=> lang('email'),
												'app_version'	=> $setup_info['email']['version']));

	$GLOBALS['phpgw']->help->data[] = array
	(
		'text'					=> lang('list'),
		'link'					=> $GLOBALS['phpgw']->help->check_help_file('list.php'),
		'lang_link_statustext'	=> lang('list')
	);

	$GLOBALS['phpgw']->help->data[] = array
	(
		'text'					=> lang('view'),
		'link'					=> $GLOBALS['phpgw']->help->check_help_file('view.php'),
		'lang_link_statustext'	=> lang('view')
	);

	$GLOBALS['phpgw']->help->draw();
?>
