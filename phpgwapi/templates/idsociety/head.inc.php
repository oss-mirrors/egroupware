<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$app_css = '';
	if(@isset($GLOBALS['HTTP_GET_VARS']['menuaction']))
	{
		list($app,$class,$method) = explode('.',$GLOBALS['HTTP_GET_VARS']['menuaction']);
		if(is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions['css'])
		{
			$app_css = $GLOBALS[$class]->css();
		}
	}

	$bodyheader = 'bgcolor="'.$GLOBALS['phpgw_info']['theme']['bg_color'].'" alink="'.$GLOBALS['phpgw_info']['theme']['alink'].'" link="'.$GLOBALS['phpgw_info']['theme']['link'].'" vlink="'.$GLOBALS['phpgw_info']['theme']['vlink'].'"';
	if (!$GLOBALS['phpgw_info']['server']['htmlcompliant'])
	{
		$bodyheader .= ' topmargin="0" marginheight="0" marginwidth="0" leftmargin="0"';
	}

	$tpl = CreateObject('phpgwapi.Template',PHPGW_TEMPLATE_DIR);
	$tpl->set_unknowns('remove');
	$tpl->set_file(array('head' => 'head.tpl'));
	$var = Array (
		'img_icon'      => PHPGW_IMAGES_DIR . '/favicon.ico',
		'img_shortcut'  => PHPGW_IMAGES_DIR . '/favicon.ico',
		'charset'		=> lang('charset'),
		'font_family'	=> $GLOBALS['phpgw_info']['theme']['font'],
		'website_title'	=> $GLOBALS['phpgw_info']['server']['site_title'],
		'body_tags'		=> $bodyheader,
		'app_css'		=> $app_css
	);
	$tpl->set_var($var);
	$tpl->pfp('out','head');
	unset($tpl);
?>
