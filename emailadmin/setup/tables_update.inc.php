<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	$test[] = '0.0.1';
	function qmailldap_upgrade0_0_1()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_qmailldap','description', array('type' => 'varchar', 'precision' => 200));		

		$GLOBALS['setup_info']['qmailldap']['currentver'] = '0.0.2';
		return $GLOBALS['setup_info']['qmailldap']['currentver'];
	}

	$test[] = '0.0.2';
	function qmailldap_upgrade0_0_2()
	{
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('qmailldap','add_def_pref','hook_add_def_pref.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('qmailldap','manual','hook_manual.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('qmailldap','about','hook_about.inc.php')");
		$GLOBALS['phpgw_setup']->oProc->query("INSERT INTO phpgw_hooks (hook_appname,hook_location,hook_filename) VALUES ('qmailldap','edit_user','hook_edit_user.inc.php')");
		$GLOBALS['setup_info']['qmailldap']['currentver'] = '0.0.3';
		return $GLOBALS['setup_info']['qmailldap']['currentver'];
	}
?>
