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

	$test[] = '0.0.1.000';
	function skel_upgrade0_0_1_000()
	{
		global $setup_info, $phpgw_setup;

		$phpgw_setup->oProc->RenameTable('skel','phpgw_skel');

		$setup_info['forum']['currentver'] = '0.0.1.001';
		return $setup_info['forum']['currentver'];
	}
?>