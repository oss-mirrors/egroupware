<?php
	/**************************************************************************\
	* eGroupWare - Online User manual                                          *
	* http://www.eGroupWare.org                                                *
	* Written and (c) by RalfBecker@outdoor-training.de                        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'manual',
		'nonavbar'   => True,
		'noheader'   => True,
	);
	include('../header.inc.php');

	ExecMethod('manual.uimanual.view');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
