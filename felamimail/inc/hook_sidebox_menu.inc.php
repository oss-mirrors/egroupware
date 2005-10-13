<?php
{
	/**************************************************************************\
	* eGroupWare - Calendar's Sidebox-Menu for idots-template                  *
	* http://www.egroupware.org                                                *
	* Written by Pim Snel <pim@lingewoud.nl>                                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	
	/* $Id$ */

 /*
	This hookfile is for generating an app-specific side menu used in the idots
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

	$menu_title = $GLOBALS['egw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$preferences = ExecMethod('felamimail.bopreferences.getPreferences');
	$linkData = array
	(
		'menuaction'    => 'felamimail.uicompose.compose'
	);
	if($preferences['messageNewWindow'] == 1)
	{
		$file = Array(
			'Compose'   => "javascript:displayMessage('".$GLOBALS['egw']->link('/index.php',$linkData)."');"
		);
	}
	else
	{
		$file = Array(
			'Compose'   => $GLOBALS['egw']->link('/index.php',$linkData)
			#'_NewLine_'=>'', // give a newline
			#'INBOX'=>$GLOBALS['egw']->link('/index.php','menuaction=felamimail.uifelamimail.viewMainScreen')
		);
	}

	if($preferences['deleteOptions'] == 'move_to_trash')
	{
		$file += Array(
			'_NewLine_'	=> '', // give a newline
			'empty trash'	=> "javascript:emptyTrash();",
		);
	}
	
	if($preferences['deleteOptions'] == 'mark_as_deleted')
	{
		$file += Array(
			'_NewLine_'		=> '', // give a newline
			'compress folder'	=> "javascript:compressFolder();",
		);
	}
	
	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['egw_info']['user']['apps']['preferences'])
	{
		$mailPreferences = ExecMethod('felamimail.bopreferences.getPreferences');
		$menu_title = lang('Preferences');
		$file = array(
			'Preferences'		=> $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname=felamimail'),
			'Manage Folders'	=> $GLOBALS['egw']->link('/index.php','menuaction=felamimail.uipreferences.listFolder')
		);

		if($mailPreferences['imapEnableSieve'] == true)
		{
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.editScript',
				'editmode'	=> 'filter'
			);
			$file['EMailfilter']	= $GLOBALS['egw']->link('/index.php',$linkData);

			$linkData = array
			(
				'menuaction'	=> 'felamimail.uisieve.editScript',
				'editmode'	=> 'vacation'
			);
			$file['Vacation']	= $GLOBALS['egw']->link('/index.php',$linkData);
		}
		if($mailPreferences['editForwardingAddress'] == true)
		{
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uipreferences.editForwardingAddress',
			);
			$file['Forwarding']	= $GLOBALS['egw']->link('/index.php',$linkData);
		}

		display_sidebox($appname,$menu_title,$file);
	}

	if ($GLOBALS['egw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Configuration' => $GLOBALS['egw']->link('/index.php','menuaction=felamimail.uifelamimail.hookAdmin')
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
