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
		global $PHP_SELF;

		$tpl->set_block('common_','common');
		$tpl->set_block('common_','preference_header');

		if ($GLOBALS['phpgw_info']['flags']['preferences_header'])
		{
//			$tpl->parse('preference_header_','preference_header');
		}

		$tabs[1]['label'] = lang('Tree view');
		$tabs[1]['link']  = $GLOBALS['phpgw']->link('/bookmarks/tree.php');
		if (ereg('tree.php',$PHP_SELF))
		{
			$selected = 1;
		}

		$tabs[2]['label'] = lang('List');
		$tabs[2]['link']  = $GLOBALS['phpgw']->link('/bookmarks/list.php');
		if (ereg('list.php',$PHP_SELF))
		{
			$selected = 2;
		}

		if (! $GLOBALS['phpgw']->acl->check('anonymous',1,'bookmarks'))
		{
			$tabs[3]['label'] = lang('New');
			$tabs[3]['link']  = $GLOBALS['phpgw']->link('/bookmarks/create.php');
			if (ereg('create.php',$PHP_SELF))
			{
				$selected = 3;
			}
		}

		$tabs[4]['label'] = lang('Search');
		$tabs[4]['link']  = $GLOBALS['phpgw']->link('/bookmarks/search.php');
		if (ereg('search.php',$PHP_SELF))
		{
			$selected = 4;
		}

		$tpl->set_var('app_navbar',$GLOBALS['phpgw']->common->create_tabs($tabs,$selected));

		set_standard('',&$tpl);
	}
