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

	$test[] = '0.9.13';
	function forum_upgrade0_9_13()
	{
		global $setup_info, $phpgw_setup;

		$phpgw_setup->oProc->RenameTable('f_body','phpgw_forum_body');
		$phpgw_setup->oProc->RenameTable('f_categories','phpgw_forum_categories');
		$phpgw_setup->oProc->RenameTable('f_forums','phpgw_forum_forums');
		$phpgw_setup->oProc->RenameTable('f_threads','phpgw_forum_threads');

		$setup_info['forum']['currentver'] = '0.9.13.001';
		return $setup_info['forum']['currentver'];
	}

	$test[] = '0.9.13.001';
	function forum_upgrade0_9_13_001()
	{
		// If for some odd reason this fields are blank, the upgrade will fail without these
		$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set subject=' ' where subject=''",__LINE__,__FILE__);
		$GLOBALS['phpgw_setup']->db->query("update phpgw_forum_threads set host=' ' where host=''",__LINE__,__FILE__);

		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_forum_threads','postdate',array('type' => 'timestamp','nullable' => False,'default' => 'current_timestamp'));

		$GLOBALS['setup_info']['forum']['currentver'] = '0.9.13.002';
		return $GLOBALS['setup_info']['forum']['currentver'];
	}
