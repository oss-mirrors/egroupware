<?php
	/**************************************************************************\
	* eGroupWare                                                               *
	* http://www.egroupware.org                                                *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	// $Id$
	// $Source$

	$values = array
	(
		'Admin options'     => $GLOBALS['phpgw']->link('/tts/admin.php'),
		'Global Categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=tts'),
		'Configure the states'     	=> $GLOBALS['phpgw']->link('/tts/states.php'),
		'Configure the transitions'   => $GLOBALS['phpgw']->link('/tts/transitions.php')
	);

	display_section('tts','Trouble Ticket System',$values);
?>
