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
		'currentapp' => 'email',
		'enable_network_class' => True,
		'enable_browser_class' => True,
		'noheader' => True,
		'nonavbar' => True
	);
	include('../header.inc.php');

	//header("Content-disposition: attachment; filename=\"".$name."\"");
	//header("Content-type: ".strtolower($application)."/".strtolower($subtype));

	$mime = strtolower($type)."/".strtolower($subtype);
	$phpgw->browser->content_header($name,$mime);

	if ($encoding == "base64")
	{
		echo $phpgw->dcom->base64($phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $msgnum, $part_no));
	}
	elseif ($encoding == "qprint")
	{
		echo $phpgw->msg->qprint($phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $msgnum, $part_no));
	}
	else
	{
		echo $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $msgnum, $part_no);
	}

	$phpgw->msg->end_request();
?>
