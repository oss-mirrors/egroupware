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

	$GLOBALS['phpgw_info'] = array();

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'              => 'skel',
		'noheader'                => True,
		'nonavbar'                => True,
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');

	ExecMethod('skel.ui.index');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
