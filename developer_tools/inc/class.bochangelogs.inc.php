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

	class bochangelogs
	{
		var $so;
		var $public_functions = array(
				'list_changelogs' => True,
				'add'             => True,
				'search'          => True,
				'create_sgml'     => True
			);

		function bochangelogs()
		{
			$this->so = createobject('developer_tools.sochangelogs');
		}

		function list_changelogs()
		{
		
		}

		function add()
		{
		
		}


	}
