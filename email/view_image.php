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

  if ($newsmode == "on"){$phpgw_info["flags"]["newsmode"] = True;}
  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True, "messageclass" => True);

  $phpgw_info["flags"]["currentapp"] = "email";
  include("../header.inc.php");
  Header("Content-type: image/$subtype");

  $data = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_no);

  echo $phpgw->msg->base64($data);
?>
