<?php
    /**************************************************************************\
    * eGroupWare - Skeleton Application                                        *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */

	class so
	{
		var $db;

		var $debug = false;

		function so($args)
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function somestoragefunc()
		{
			// do some data manipulation here
		}

	}
?>
