<?php
	/**************************************************************************\
	* eGroupWare - Polls                                                       *
	* http://www.egroupware.org                                                *
	* Copyright (c) 1999 Till Gerken (tig@skv.org)                             *
	* Modified by Greg Haygood (shrykedude@bellsouth.net)                      *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */
{
// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = Array(
		'Poll Settings'  => $GLOBALS['egw']->link('/index.php', array('menuaction'=>'polls.ui.admin','action'=>'settings')),
		'Show Questions' => $GLOBALS['egw']->link('/index.php', array('menuaction'=>'polls.ui.admin','action'=>'show','type'=>'question'))
	);
//Do not modify below this line
	display_section($appname,$title,$file);
}
?>
