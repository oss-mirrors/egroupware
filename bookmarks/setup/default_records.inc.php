<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$oProc->query("INSERT INTO phpgw_config (config_app, config_name, config_value) VALUES ('bookmarks','mail_footer','\n\n--\nThis was sent from phpGroupWare\nhttp://www.phpgroupware.org\n')");
?>