<?php
	/**************************************************************************\
	* phpGroupWare - Setup                                                     *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$test[] = '0.8.3.001';
	function stocks_upgrade0_8_3_001()
	{
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_stocks','stock_country',array('type' => 'char','precision' => 2,'default' => 'US','nullable' => False));

		$GLOBALS['setup_info']['stocks']['currentver'] = '0.8.3.002';
		return $GLOBALS['setup_info']['inv']['currentver'];
	}
?>
