<?php
	/**************************************************************************\
	* eGroupWare - Headlines  Sidebox-Menu for idots-template                  *
	* http://www.egroupware.org                                                *
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

	if ($GLOBALS['egw_info']['user']['apps']['preferences'])
	{
		$menu_title = $GLOBALS['egw_info']['apps'][$appname]['title'];
		$file = Array(
			'Select Headlines to Display' => $GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.preferences'),
			'Select layout' => $GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.preferences_layout')
		);

		if ($GLOBALS['egw_info']['user']['apps']['admin'])
		{
			$file['Headline Site Management'] = $GLOBALS['egw']->link('/index.php','menuaction=headlines.uiheadlines.admin');
		}
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
