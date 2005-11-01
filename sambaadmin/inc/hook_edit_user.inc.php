<?php
	/***************************************************************************\
	* eGroupWare - SambaAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

{ 
	global $menuData;

	if ($GLOBALS['egw_info']['server']['ldap_host'])
	{
		$menuData[] = Array
		(
			'description'	=> 'samba settings',
			'url'		=> '/index.php',
			'extradata'	=> 'menuaction=sambaadmin.uiuserdata.editUserData'
		);
	}
}
