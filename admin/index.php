<?php
	/**************************************************************************\
	* phpGroupWare - administration                                            *
	* http://www.phpgroupware.org                                              *
	* Written by Joseph Engo <jengo@phpgroupware.org>                          *
	* Modified by Stephen Brown <steve@dataclarity.net>                        *
	*  to distribute admin across the application directories                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info'] = array();
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'admin';
	include('../header.inc.php');

	$GLOBALS['admin_tpl'] = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$GLOBALS['admin_tpl']->set_file(array(
		'admin' => 'index.tpl'
	));

	$GLOBALS['admin_tpl']->set_block('admin','list');
	$GLOBALS['admin_tpl']->set_block('admin','app_row');
	$GLOBALS['admin_tpl']->set_block('admin','app_row_noicon');
	$GLOBALS['admin_tpl']->set_block('admin','link_row');
	$GLOBALS['admin_tpl']->set_block('admin','spacer_row');

	$GLOBALS['admin_tpl']->set_var('title',lang('Administration'));

	// This func called by the includes to dump a row header
	function section_start($name='',$icon='',$appname='')
	{
		$GLOBALS['admin_tpl']->set_var('icon_backcolor',$GLOBALS['phpgw_info']['theme']['row_off']);
		$GLOBALS['admin_tpl']->set_var('link_backcolor',$GLOBALS['phpgw_info']['theme']['row_off']);
		$GLOBALS['admin_tpl']->set_var('app_name',lang($name));
		$GLOBALS['admin_tpl']->set_var('a_name',$appname);
		$GLOBALS['admin_tpl']->set_var('app_icon',$icon);
		if ($icon)
		{
			$GLOBALS['admin_tpl']->parse('rows','app_row',True);
		}
		else
		{
			$GLOBALS['admin_tpl']->parse('rows','app_row_noicon',True);
		} 
	}

	function section_item($pref_link='',$pref_text='')
	{
		$GLOBALS['admin_tpl']->set_var('pref_link',$pref_link);
		$GLOBALS['admin_tpl']->set_var('pref_text',$pref_text);		
		$GLOBALS['admin_tpl']->parse('rows','link_row',True);
	} 

	function section_end()
	{
		$GLOBALS['admin_tpl']->parse('rows','spacer_row',True);
	}

	function display_section($appname,$title,$file)
	{
		section_start($title,$GLOBALS['phpgw']->common->image($appname,Array('navbar.gif',$appname.'.gif')),$appname);

		while(list($text,$url) = each($file))
		{
			section_item($url,lang($text));
		}
		section_end(); 
	}

	$GLOBALS['phpgw']->common->hook('admin');
	$GLOBALS['admin_tpl']->pparse('out','list');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
