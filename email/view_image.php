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

	if ($newsmode == "on")
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	$phpgw_info['flags'] = array(
			'currentapp' => 'email',
			'enable_network_class' => True, 
			'noheader' => True,
			'nonavbar' => True
	);
	include("../header.inc.php");

	//  if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
	//    $phpgw->common->read_preferences("nntp");
	//  set_time_limit(0);

	//  echo "Mailbox = ".$mailbox."<br>\n";
	//  echo "Msgnum = ".$m."<br>\n";
	//  echo "Part Number = ".$p."<br>\n";
	//  echo "Subtype = ".$s."<br>\n";

	$data = $phpgw->dcom->fetchbody($mailbox, $m, $p);
	$picture = $phpgw->dcom->base64($data);

	//  echo strlen($picture)."<br>\n";
	//  echo $data;

	Header("Content-length: ".strlen($picture));
	Header("Content-type: image/".$s);
	Header("Content-disposition: attachment; filename=\"".urldecode($n)."\"");
	echo $picture;
	flush();
	$phpgw->dcom->close($mailbox);
?>
