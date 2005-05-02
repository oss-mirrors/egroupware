<?php
    /**************************************************************************\
    * eGroupWare - Skeleton Application                                        *
    * http://www.egroupware.org                                                *
    * -----------------------------------------------                          *
    *  This program is free software; you can redistribute it and/or modify it *
    *  under the terms of the GNU General Public License as published by the   *
    *  Free Software Foundation; either version 2 of the License, or (at your  *
    *  option) any later version.                                              *
    \**************************************************************************/

	/* $Id$ */

	$test[] = '0.0.1.000';
	function skel_upgrade0_0_1_000()
	{
		$GLOBALS['phpgw_setup']->oProc->RenameTable('skel','phpgw_skel');

		$GLOBALS['setup_info']['skel']['currentver'] = '0.0.1.001';
		return $GLOBALS['setup_info']['skel']['currentver'];
	}
?>
