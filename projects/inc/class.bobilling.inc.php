<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000, 2001 Bettina Gille                            *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */

	class bobilling
	{
		var $public_functions = array
		(
			'read_invoices'	=> True
		);

		function bobilling()
		{
			$this->sobilling	= CreateObject('projects.sobilling');
			$this->contacts		= CreateObject('phpgwapi.contacts');
		}

		function read_invoices($start, $query, $sort, $order, $limit, $project_id)
		{
			$bill = $this->sobilling->read_invoices($start, $query, $sort, $order, $limit, $project_id);
			$this->total_records = $this->sobilling->total_records;
			return $bill;
		}
	}
?>
