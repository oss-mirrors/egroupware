<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'email',
		'enable_network_class' => True,
		'enable_browser_class' => True,
		'noheader' => True,
		'nonavbar' => True
	);
	include('../header.inc.php');

	//header('Content-disposition: attachment; filename="'.$name.'"');
	//header('Content-type: '.strtolower($application).'/'.strtolower($subtype));
		
	$mime = strtolower($GLOBALS['phpgw']->msg->get_arg_value('type')) .'/' .strtolower($GLOBALS['phpgw']->msg->get_arg_value('subtype'));
	$GLOBALS['phpgw']->browser->content_header($GLOBALS['phpgw']->msg->get_arg_value('name'), $mime);

	//echo 'get all args dump<pre>'; print_r($GLOBALS['phpgw']->msg->get_all_args()); echo '</pre>';
	//echo '$mime: ['.$mime.']<br>';
	//echo '$GLOBALS[phpgw]->msg->get_arg_value(encoding): ['.$GLOBALS['phpgw']->msg->get_arg_value('encoding').']<br>';
	
	if ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'base64')
	{
		echo $GLOBALS['phpgw']->msg->de_base64($GLOBALS['phpgw']->msg->phpgw_fetchbody($GLOBALS['phpgw']->msg->get_arg_value('part_no')));
	}
	elseif ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'qprint')
	{
		echo $GLOBALS['phpgw']->msg->qprint($GLOBALS['phpgw']->msg->phpgw_fetchbody($GLOBALS['phpgw']->msg->get_arg_value('part_no')));
	}
	else
	{
		echo $GLOBALS['phpgw']->msg->phpgw_fetchbody($GLOBALS['phpgw']->msg->get_arg_value('part_no'));
	}

	$GLOBALS['phpgw']->msg->end_request();
?>
