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

	class uichangelogs
	{
		var $template;
		var $bo;
		var $public_functions = array(
				'list_changelogs' => True,
				'add'             => True,
				'search'          => True,
				'create_sgml'     => True
			);

		function uichangelogs()
		{
			global $phpgw;

			$this->template = $phpgw->template;
			$this->bo       = createobject('developer_tools.bochangelogs');
		}

		function header()
		{
			global $phpgw, $phpgw_info;

			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_file('_header','changelog_header.tpl');
			$this->template->set_var('lang_header',lang('Changelogs'));
			$this->template->set_var('lang_list_changelogs','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uichangelogs.list_changelogs') . '">' . lang('List changelogs') . '</a>');
			$this->template->set_var('lang_add_changelogs','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uichangelogs.add') . '">' . lang('Add change') . '</a>');
			$this->template->set_var('lang_search_changelogs','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uichangelogs.search') . '">' . lang('Search changelogs') . '</a>');
			$this->template->set_var('lang_sgml','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uichangelogs.create_sgml') . '">' . lang('Create SGML file') . '</a>');

			$this->template->pfp('out','_header');		
		}

		function list_changelogs()
		{
			$this->header();
			echo '<p>&nbsp;</p><p>&nbsp;</p><center><b>Coming soon to a theater near you!</b></center>';
		}

		function add()
		{
			$this->header();
			echo '<p>&nbsp;</p><p>&nbsp;</p><center><b>Coming soon to a theater near you!</b></center>';
		}

		function search()
		{
			$this->header();
			echo '<p>&nbsp;</p><p>&nbsp;</p><center><b>Coming soon to a theater near you!</b></center>';
		}

		function create_sgml()
		{
			$this->header();
			echo '<p>&nbsp;</p><p>&nbsp;</p><center><b>Coming soon to a theater near you!</b></center>';
		}

	}

