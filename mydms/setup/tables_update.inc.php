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

	$test[] = '0.1.0.001';
	function mydms_upgrade0_1_0_001()
	{
		$GLOBALS['setup_info']['mydms']['currentver'] = '1.2.000';
		return $GLOBALS['setup_info']['mydms']['currentver'];
	}


	$test[] = '1.2.000';
	function mydms_upgrade1_2_000()
	{
		$GLOBALS['setup_info']['mydms']['currentver'] = '1.4';
		return $GLOBALS['setup_info']['mydms']['currentver'];
	}
?>
