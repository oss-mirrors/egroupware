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
		'E-Mail Preferences'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.preferences'),
		'Extra E-Mail Accounts'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uipreferences.ex_accounts_list'),
		'E-Mail Filters'	=> $GLOBALS['phpgw']->link('/index.php','menuaction=email.uifilters.filters_list')
	);
	$this_ver = $GLOBALS['phpgw_info']['server']['versions']['phpgwapi'];
	$pre_xslt_ver = '0.9.14.0.1.1';
	if (function_exists(amorethanb))
	{
		if (($this_ver)
		&& (amorethanb($this_ver, $pre_xslt_ver)))
		{
			// this is the xslt template era
			display_section($appname,$file);
		}
		else
		{
			display_section($appname,$title,$file);
		}
	}
	else
	{
		if (($this_ver)
		&& ($GLOBALS['phpgw']->common->cmp_version_long($this_ver, $pre_xslt_ver)))
		{
			// this is the xslt template era
			display_section($appname,$file);
		}
		else
		{
			display_section($appname,$title,$file);
		}
	}
}
?>
