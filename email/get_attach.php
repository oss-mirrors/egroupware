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

	$mime = strtolower($phpgw->msg->args['type'])."/".strtolower($phpgw->msg->args['subtype']);
	$phpgw->browser->content_header($phpgw->msg->args['name'],$mime);

	if ($phpgw->msg->args['encoding'] == "base64")
	{
		//echo $phpgw->dcom->base64($phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->args['msgnum'], $phpgw->msg->args['part_no']));
		echo $phpgw->msg->de_base64($phpgw->msg->phpgw_fetchbody($phpgw->msg->args['part_no']));
	}
	elseif ($phpgw->msg->args['encoding'] == "qprint")
	{
		//echo $phpgw->msg->qprint($phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->args['msgnum'], $phpgw->msg->args['part_no']));
		echo $phpgw->msg->qprint($phpgw->msg->phpgw_fetchbody($phpgw->msg->args['part_no']));
	}
	else
	{
		//echo $phpgw->dcom->fetchbody($phpgw->msg->mailsvr_stream, $phpgw->msg->args['msgnum'], $phpgw->msg->args['part_no']);
		echo $phpgw->msg->phpgw_fetchbody($phpgw->msg->args['part_no']);
	}

	$phpgw->msg->end_request();
?>
