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
  
  $phpgw_flags = array("noheader" => True, "nonavbar" => True, "currentapp" => "email", "notify_js" => True);
  $phpgw_flags["currentapp"] = "email";
  include("../header.inc.php");


  if ($phpgw_info["user"]["permissions"]["email"]) {
    echo "<!-- Mailox info -->\n";
    $mbox = $phpgw->msg->login();
    if (! $mbox) {
      echo "Mail error: can not open connection to mail server";
      exit;
    }

  	$mailbox_status = $phpgw->msg->status($mbox,"{" . $phpgw_info["server"]["mail_server"] . ":" . $phpgw_info["server"]["mail_port"] . "}INBOX",SA_UNSEEN);
    if ($mailbox_status->unseen == 1) {
      echo $mailbox_status->struct->parameters->attribute == "X-Mailer";
    }
    if ($mailbox_status->unseen > 1) {
      echo $mailbox_status->struct->parameters->attribute == "X-Mailer";
      echo "No new msgs";
    }
  }
?>


<?php
  include($phpgw_info["server"]["api_dir"]."/footer.inc.php");
?>
