<?php
    /**************************************************************************\
    * phpGroupWare - about                                                     *
    * http://www.phpgroupware.org                                              *
    * --------------------------------------------                             *
    * This program is free software; you can redistribute it and/or modify it  *
    * under the terms of the GNU General Public License as published by the    *
    * Free Software Foundation; either version 2 of the License, or (at your   *
    * option) any later version.                                               *
    \**************************************************************************/
    /* $Id$ */

	function about_app($tpl,$handle)
	{
		$s = '<b>' . lang('Projects') . '</b><p>' . lang('written by:') . '&nbsp;Bettina Gille&nbsp;&nbsp;[ceb@phpgroupware.org]';
		return $s;
	}