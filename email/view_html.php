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

	//$GLOBALS['phpgw']->browser->content_header($name,$mime);
	if ((isset($GLOBALS['phpgw']->msg->args['html_part']))
	&& ($GLOBALS['phpgw']->msg->args['html_part'] != ''))
	{
		$GLOBALS['phpgw']->browser->content_header('','');
		$html_part = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->args['html_part']);
		//echo $GLOBALS['phpgw']->dcom->base64($html_part);
		echo $GLOBALS['phpgw']->msg->de_base64($html_part);
		$GLOBALS['phpgw']->msg->end_request();
	}
	elseif ((isset($GLOBALS['phpgw']->msg->args['html_reference']))
	&& ($GLOBALS['phpgw']->msg->args['html_reference'] != ''))
	{
		$html_reference = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->args['html_reference']);
		$GLOBALS['phpgw']->msg->end_request();
		header('Location: ' . $html_reference); 
	}
	else
	{
		$GLOBALS['phpgw']->msg->end_request();
	}
?>
