<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

//Do not modify below this line
	// Actual content
	$title = $appname;
	if ($phpgw->acl->check('changepassword',1))
	{
		$file['Change your Password'] = $phpgw->link('/preferences/changepassword.php');
	}
	$file['change your settings'] = $phpgw->link('/preferences/settings.php');

	display_section($appname,$title,$file);
}
?>
