<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail					*
  * http://www.phpgroupware.org				*
  * Based on Aeromail by Mark C3ushman <mark@cushman.net>	*
  *          http://the.cushman.net/				*
  * Currently maintained by Angles <angles@phpgroupware.org>	*
  * --------------------------------------------				*
  *  This program is free software; you can redistribute it and/or modify it	*
  *  under the terms of the GNU General Public License as published by the	*
  *  Free Software Foundation; either version 2 of the License, or (at your	*
  *  option) any later version.					*
  \**************************************************************************/

	/* $Id$ */

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');
  
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'email',
		//'enable_network_class' => True, 
		//'enable_nextmatchs_class' => True
		'noheader'   => True,
		'nonavbar'   => True
	);
	include('../header.inc.php');
	// time limit should be controlled elsewhere
	//@set_time_limit(0);
	
	$obj = CreateObject('email.uiindex');
	$obj->index();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
