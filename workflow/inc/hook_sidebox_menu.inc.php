<?php
    /**************************************************************************\
    * eGroupWare - Knowledge Base                                              *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */
{
	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array();

	// checking for workflow admin acl
	if ( ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow')) || ($GLOBALS['phpgw']->acl->check('run',1,'admin')) )
	{
		$file['Admin Processes']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_adminprocesses.form');
	}

	//checking for workflow monitoring acl
	if ( ($GLOBALS['phpgw']->acl->check('monitor_workflow',1,'workflow')) || ($GLOBALS['phpgw']->acl->check('run',1,'admin')) )
	{
		$file['Monitor Processes']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorprocesses.form');
		$file['Monitor Activities']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitoractivities.form');
		$file['Monitor Instances']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorinstances.form');
		$file['Monitor Work Items']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_monitorworkitems.form');		  
	}
	
	// no acl
	$file['User Processes'] 	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_userprocesses.form');
	$file['User Activities']	= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_useractivities.form');
	$file['User Instances']		= $GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_userinstances.form');
	display_sidebox($appname,$menu_title,$file);
}
?>
