<?php
	/**************************************************************************\
	* phpGroupWare - Developer Tools                                           *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$t = $GLOBALS['phpgw']->template;
	$t->set_file(array(
		'header' => 'header.tpl'
	));

	$t->set_var('lang_developer_tools',lang('Developer tools'));
	$t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$t->set_var('link_diary',lang('Diary'));
	$t->set_var('link_sourceforge_project','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uisf_project_tracker.display_tracker') . '">' . lang('SF Project tracker') . '</a>');
	$t->set_var('link_changelog','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uichangelogs.list_changelogs') . '">' . lang('Changelogs') . '</a>');
	$t->set_var('link_language_management','<a href="' . $GLOBALS['phpgw']->link('/index.php','menuaction=developer_tools.uilangfile.index') . '">' . lang('Language file management'));
	$t->set_var('link_preferences','<a href="' . $GLOBALS['phpgw']->link('/preferences/index.php#developer_tools') . '">' . lang('Preferences') . '</a>');

	$t->pfp('out','header');
	unset($t);
