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

{
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = Array(	
		'Admin Processes'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_adminprocesses.form'),
		'Monitor Processes'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorprocesses.form'),
		'Monitor Activities'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitoractivities.form'),
		'Monitor Instances'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorinstances.form'),
		'Monitor Work Items'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorworkitems.form')		  
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
