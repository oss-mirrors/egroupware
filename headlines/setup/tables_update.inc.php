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

	$test[] = '0.8.1';
	function headlines_upgrade0_8_1()
	{
		global $phpgw_info, $phpgw_setup;

		$phpgw_setup->oProc->RenameTable('news_site','phpgw_headlines_sites');
		$phpgw_setup->oProc->RenameTable('news_headlines','phpgw_headlines_cached');
		$phpgw_setup->oProc->DropTable('users_headlines');

		$setup_info['headlines']['currentver'] = '0.8.1.001';
		return $setup_info['headlines']['currentver'];
	}

?>