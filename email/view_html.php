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

	$phpgw_info["flags"] = array(
		"currentapp" => "email",
		"enable_network_class" => True,
		"enable_browser_class" => True,
		"noheader" => True,
		"nonavbar" => True
	);
	include("../header.inc.php");

	//$phpgw->browser->content_header($name,$mime);
	if (isset($html_part))
	{
		$phpgw->browser->content_header('','');
		$html_part = $phpgw->msg->stripslashes_gpc($html_part);
		echo $phpgw->dcom->base64($html_part);
		$phpgw->msg->end_request();
	}
	elseif (isset($html_reference))
	{
		$html_reference = $phpgw->msg->stripslashes_gpc($html_reference);
		$phpgw->msg->end_request();
		header("Location: " . $html_reference); 
	}
	else
	{
		$phpgw->msg->end_request();
	}
?>
