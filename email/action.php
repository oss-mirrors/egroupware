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
  $totalmessages = $phpgw->msg->num_msg($mailbox);

  if ($what == "move") {
     $tofolder = ($tofolder == "INBOX" ? 
			"INBOX" : 
			$phpgw->msg->construct_folder_str($tofolder));

     $msgs = $msglist ? implode($msglist, ",") : $msglist;
     if (! $phpgw->msg->mail_move($mailbox, $msgs, $tofolder)) {
	echo "summin went rong<br>";
     }
  }

  if ($what == "delall") {
     for ($i = 0; $i < count($msglist); $i++) {
	$phpgw->msg->delete($mailbox, $msglist[$i]);
     }
     $totaldeleted = "&td=$i";
     $dontforward = False;
  }

  if ($what == "delete") {
     $phpgw->msg->delete($mailbox, $msgnum);
     if ($totalmessages != $msgnum) {
        Header("Location: ".$phpgw->link("message.php","folder="
		.urlencode($folder)."&msgnum=".$msgnum));
        $dontforward = True;
     }
  }
  $phpgw->msg->expunge($mailbox);
  $phpgw->msg->close($mailbox);


  if (! $dontforward) {
     Header("Location: ".$phpgw->link("index.php","folder=" . urlencode($folder) . $totaldeleted));
  }
?>
