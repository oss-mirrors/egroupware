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

  $phpgw_flags = array("noheader" => True, "nonavbar" => True, "messageclass" => True);
  $phpgw_flags["currentapp"] = "email";
  include("../header.inc.php");

  header("Content-type: $type/$subtype");
  header("Content-Disposition: attachment; filename=$name");

  if ($encoding == "base64") {
     echo imap_base64( imap_fetchbody($mailbox, $msgnum, $part_no) );
  } else {
     echo imap_fetchbody($mailbox, $msgnum, $part_no);
  }

?>