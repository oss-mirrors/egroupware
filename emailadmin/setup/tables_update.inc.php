<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* http://www.phpgw.de                                                      *
	* Author: lkneschke@phpgw.de                                               *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */
/*
	$test[] = "0.9.4pre3";
	function notes_upgrade0_9_4pre3()
	{
		global $setup_info;
		$setup_info['notes']['currentver'] = '0.9.4pre4';
		return $setup_info['notes']['currentver'];
	}

	$test[] = '0.9.13.001';
	function notes_upgrade0_9_13_001()
	{
		global $setup_info,$phpgw_setup;

		$phpgw_setup->oProc->AlterColumn('phpgw_notes','note_access', array('type' => 'varchar', 'precision' => 7));

		$setup_info['notes']['currentver'] = '0.9.13.002';
		return $setup_info['notes']['currentver'];
	}
*/
	$test[] = "0.0.1";
	function qmailldap_upgrade0_0_1()
	{
		global $setup_info,$phpgw_setup;
		
		$phpgw_setup->oProc->AddColumn("phpgw_qmailldap", "description", array('type' => 'varchar', 'precision' => 200));		

		$setup_info['notes']['currentver'] = '0.0.2';
		return $setup_info['notes']['currentver'];
	}
?>
