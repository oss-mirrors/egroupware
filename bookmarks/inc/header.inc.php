<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	function app_header(&$tpl)
	{
		global $phpgw, $phpgw_info, $PHP_SELF;

		$tpl->set_block('common_','common');
		$tpl->set_block('common_','preferences_header');

		if ($phpgw_info['flags']['preferences_header'])
		{
			$tpl->parse('preference_header_','preference_header');
		}

		$tabs[1]['label'] = 'Tree view';
		$tabs[1]['link']  = $phpgw->link('/bookmarks/tree.php');
		if (ereg('tree.php',$PHP_SELF))
		{
			$selected = 1;
		}

		$tabs[2]['label'] = 'List';
		$tabs[2]['link']  = $phpgw->link('/bookmarks/list.php');
		if (ereg('list.php',$PHP_SELF))
		{
			$selected = 2;
		}

		if (! $phpgw->acl->check('anonymous',1,'bookmarks'))
		{
			$tabs[3]['label'] = 'New';
			$tabs[3]['link']  = $phpgw->link('/bookmarks/create.php');
			if (ereg('create.php',$PHP_SELF))
			{
				$selected = 3;
			}
		}

		$tabs[4]['label'] = 'Search';
		$tabs[4]['link']  = $phpgw->link('/bookmarks/search.php');
		if (ereg('search.php',$PHP_SELF))
		{
			$selected = 4;
		}

		$tpl->set_var('app_navbar',$phpgw->common->create_tabs($tabs,$selected));
		set_standard('',&$tpl);
	}
