<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
  *  - Vinicius Cubas <vinicius@think-e.com.br>                               *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	$GLOBALS['phpgw_info'] = array();

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'contactcenter',
		'noheader'   => true,
		//'nonavbar'   => true
	);
	include('../header.inc.php');

	$obj = CreateObject('contactcenter.ui_data');
	$obj->index();

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
