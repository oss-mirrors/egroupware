<?php
	/**************************************************************************\
	* phpGroupWare - projects's Sidebox-Menu for idots-template                *
	* http://www.phpgroupware.org                                              *
	* Written by Pim Snel <pim@lingewoud.nl>                                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */
	{

 /*
	This hookfile is for generating an app-specific side menu used in the idots 
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

			$boprojects = CreateObject('projects.boprojects');
			$appname = 'projects';
			$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
			$file = array();
			if ($boprojects->isprojectadmin('pad'))
			{
				$file['Activities']	= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_activities&action=act');
				$file['Budget']		= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_budget&action=mains');
			}

			if ($boprojects->isprojectadmin('pbo'))
			{
				$file['Billing']	= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uibilling.list_projects&action=mains');
				$file['Deliveries']	= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uideliveries.list_projects&action=mains');
			}
			$file['Projects']		= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=mains');
			$file['Jobs']			= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_projects&action=subs');
			$file['Work hours']		= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojecthours.list_hours');

			$file['Statistics']		= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uistatistics.list_projects&action=mains');
			$file['Archive']		= $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.archive&action=amains');

			display_sidebox($appname,$menu_title,$file);

			if ($GLOBALS['phpgw_info']['user']['apps']['preferences'])
			{
				$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Preferences');
				$file = Array(
					'Preferences'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.preferences'),
					'Grant Access'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uiaclprefs.index&acl_app='.$appname),
					'Edit categories'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=projects&cats_level=True&global_cats=True')
				);
				display_sidebox($appname,$menu_title,$file);
			}

			if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Administration');
				$file = Array(
					'Administration'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pad'),
					'Accountancy'		=> $GLOBALS['phpgw']->link('/index.php','menuaction=projects.uiprojects.list_admins&action=pbo'),
					'Global Categories'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname)
				);
				display_sidebox($appname,$menu_title,$file);
			}
			unset($boprojects);
	}
?>
