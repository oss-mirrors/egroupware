<?php
  $tmp_app_inc = $phpgw_info["server"]["app_inc"];
  $phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/email/inc";

  if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"]) {
    include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");
    echo "<!-- Mailox info -->\n";
    $mbox = $phpgw->msg->login();
    if (! $mbox) {
      echo "Mail error: can not open connection to mail server";
      exit;
    }

  	$mailbox_status = $phpgw->msg->status($mbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);
    if ($mailbox_status->unseen == 1) {
      echo "<tr><td><A href=\"" . $phpgw->link("email/index.php") . "\"> "
	 . lang("You have 1 new message!") . "</A></td></tr>\n";
    }
    if ($mailbox_status->unseen > 1) {
      echo "<tr><td><A href=\"" . $phpgw->link("email/index.php") . "\"> "
	 . lang("You have x new messages!",$mailbox_status->unseen) . "</A></td></tr>";
    }
    echo "<!-- Mailox info -->\n";
  }

  $phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>