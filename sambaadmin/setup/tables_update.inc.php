<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$test[] = '0.0.1';
	function sambaadmin_upgrade0_0_1()
	{
		$GLOBALS['setup_info']['sambaadmin']['currentver'] = '1.2.000';
		return $GLOBALS['setup_info']['sambaadmin']['currentver'];
	}
?>
