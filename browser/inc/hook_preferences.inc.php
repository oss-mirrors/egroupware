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

{
	// Only Modify the $file and $title variables.....
	$file = array(
		'Preferences'     => $GLOBALS['phpgw']->link('/index.php',array('menuaction'=>'browser.ui.preferences')),
	);

	// Do not modify below this line
	display_section($appname,$file);
}
?>
