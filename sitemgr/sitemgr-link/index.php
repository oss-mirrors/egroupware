<?php
	/**************************************************************************\
	* phpGroupWare - Web Content Manager                                       *
	* http://www.phpgroupware.org                                              *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	$GLOBALS['phpgw_info']['flags'] = array
	(
		'currentapp' => 'sitemgr-link',
		'noheader'   => True,
		'nonavbar'   => True,
		'noapi'      => False
	);
	if (file_exists('../header.inc.php'))
	{
		include('../header.inc.php');
	}
	else
	{
		echo "You need to make sure the sitemgr-link app is in the phpgroupware directory.  If you made a symbolic link... it isn't working.";
		die();
	}
	$pref_so = CreateObject('sitemgr.sitePreference_SO', True);
	$location = $pref_so->getPreference('sitemgr-site-url');
	$dir = $pref_so->getPreference('sitemgr-site-dir');
	$sitemgr_info['sitemgr-site-url'] = $pref_so->getPreference('sitemgr-site-url');
	if ($location && file_exists($dir . '/functions.inc.php'))
	{
		require_once($dir . '/functions.inc.php');

		Header('Location: ' . sitemgr_link2('/index.php',array("PHPSESSID" => session_id())));
		//echo sitemgr_link2('/index.php');
		exit;
	}
	else
	{
		$GLOBALS['phpgw']->common->phpgw_header();
		echo parse_navbar();
		$aclbo = CreateObject('sitemgr.ACL_BO', True);
		echo '<table width="50%"><tr><td>';
		if ($aclbo->is_admin())
		{
			echo lang('Before the public web site can be viewed, you must configure the various locations and preferences.  Please go to the sitemgr setup page by following this link:') . 
			  '<a href="' . 
			  $GLOBALS['phpgw']->link('/index.php', 'menuaction=sitemgr.Common_UI.DisplayPrefs') . 
			  '">' .
			  lang('sitemgr setup page') .
			  '</a>. ' .
			  lang('Note that you may get this message if your preferences are incorrect.  For example, if config.inc.php is not found in the directory that you specified.');
		}
		else
		{
			echo lang('Your administrator has not yet setup the web content manager for public viewing.  Go bug your administrator to get their butt in gear.');
		}
		echo '</td></tr></table>';
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
?>
