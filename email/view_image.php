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
			'noheader' => True,
			'nonavbar' => True
	);
	if ($newsmode == 'on')
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	include('../header.inc.php');

	//  if (isset($GLOBALS['phpgw_info']['flags']['newsmode']) && $GLOBALS['phpgw_info']['flags']['newsmode'])
	//    $GLOBALS['phpgw']->common->read_preferences('nntp');
	//  set_time_limit(0);

	// //  echo 'Mailbox = '.$mailbox.'<br>'."\n";
	//  echo 'Mailbox = '.$GLOBALS['phpgw']->msg->mailsvr_stream.'<br>'."\n";
	//  echo 'Msgnum = '.$m.'<br>'."\n";
	//  echo 'Part Number = '.$p.'<br>'."\n";
	//  echo 'Subtype = '.$s.'<br>'."\n";

	//$data = $GLOBALS['phpgw']->dcom->fetchbody($GLOBALS['phpgw']->msg->mailsvr_stream, $m, $p);
	$data = $GLOBALS['phpgw']->msg->phpgw_fetchbody($p);
	//$picture = $GLOBALS['phpgw']->dcom->base64($data);
	$picture = $GLOBALS['phpgw']->msg->de_base64($data);

	//  echo strlen($picture)."<br>\n";
	//  echo $data;

	Header('Content-length: '.strlen($picture));
	Header('Content-type: image/'.$s);
	Header('Content-disposition: attachment; filename="'.urldecode($n).'"');
	echo $picture;
	flush();

	$GLOBALS['phpgw']->msg->end_request();
?>
