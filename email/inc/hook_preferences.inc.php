<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{
	$title = $appname;
	$file = Array(
	//TEST-RALFBECKER	'E-Mail Preferences'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.preferences'),
		'E-Mail Preferences'	=> $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=email&prefix='),	// need empty prefix
		'Extra E-Mail Accounts'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.ex_accounts_list'),
		'E-Mail Filters'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uifilters.filters_list')
	);
	display_section($appname,$file);
}
?>
