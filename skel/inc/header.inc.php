<?php
  /**************************************************************************\
  * phpGroupWare - skel                                                      *
  * http://www.phpgroupware.org                                              *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
	/* $Id$ */

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('skel_header' => 'header.tpl'));

	if(isset($GLOBALS['phpgw_info']['user']['preferences']['skel']['skel_font']))
	{
		$font = $GLOBALS['phpgw_info']['user']['preferences']['skel']['skel_font'];
	}
	else
	{
		$font = set_font();
	}

	$t->set_var('bg_color',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$t->set_var('font',$font);
	$t->set_var('link_categories',$GLOBALS['phpgw']->link('/preferences/categories.php','cats_app=skel&cats_level=True&global_cats=True'));
	$t->set_var('lang_categories',lang('Categories'));
	$t->set_var('link_skel',$GLOBALS['phpgw']->link('/skel/index.php'));
	$t->set_var('lang_skel',lang('Skeleton'));

	$t->pparse('out','skel_header');
?>
