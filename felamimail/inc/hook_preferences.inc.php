<?php
/**************************************************************************\
* FeLaMiMail                                                               *
* http://www.egroupware.org                                                *
* Written by Lars Kneschke <lars@kneschke.de>                              *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; version 2 of the License. 			   *
\**************************************************************************/

/* $Id$ */

{
	// Only Modify the $file and $title variables.....
	$title = $appname;
	$mailPreferences = ExecMethod('felamimail.bopreferences.getPreferences');

	$file['Preferences'] = $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname=' . $appname);

	if($mailPreferences->userDefinedAccounts) {
		$linkData = array
		(
			'menuaction' => 'felamimail.uipreferences.editAccountData',
		);
		$file['Manage EMailaccounts'] = $GLOBALS['egw']->link('/index.php',$linkData);
	}

	$file['Manage Folders'] = $GLOBALS['egw']->link('/index.php','menuaction=felamimail.uipreferences.listFolder');

	$icServer = $mailPreferences->getIncomingServer(0);

	if($icServer->enableSieve) {
		$sieveLinkData = array
		(
			'menuaction' => 'felamimail.uisieve.listScripts',
			'action'     => 'updateFilter'
		);
		$file['Manage EMailfilter / Vacation'] = $GLOBALS['egw']->link('/index.php',$sieveLinkData);
	}
	
	//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
