<?php
 /* $Id$ */
  $d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
  if($d1 == "htt" || $d1 == "ftp" ) {
    echo "Failed attempt to break in via an old Security Hole!<br>\n";
    $phpgw->common->phpgw_exit();
  } unset($d1);

  $tmp_app_inc = $phpgw_info["server"]["app_inc"];
  $phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/email/inc";

  if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"]) {
    include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");
    echo "<!-- Mailox info -->\n";
//    if (! $mbox) {
//      echo "Mail error: can not open connection to mail server";
//      $phpgw->common->phpgw_exit();
//    }

  	$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);
  	$str = '';
    if ($mailbox_status->unseen == 1) {
//      echo "<tr><td><A href=\"" . $phpgw->link("email/index.php") . "\"> "
//	 . lang("You have 1 new message!") . "</A></td></tr>\n";
	  $str .= lang("You have 1 new message!");
    }
    if ($mailbox_status->unseen > 1) {
//      echo "<tr><td><A href=\"" . $phpgw->link("email/index.php") . "\"> "
//	 . lang("You have x new messages!",$mailbox_status->unseen) . "</A></td></tr>";
	  $str .= lang("You have x new messages!",$mailbox_status->unseen);
    }
    $nummsg = $phpgw->msg->num_msg($mailbox);
    include($phpgw_info["server"]["api_inc"].'/phpgw_utilities_portalbox.inc.php');
    $title = '<a href="'.$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/index.php").'">EMail'.($str ? ' - '.$str : '').'</a>';
    $portalbox = new linkbox($title,$phpgw_info["theme"]["navbar_bg"],$phpgw_info["theme"]["bg_color"],$phpgw_info["theme"]["bg_color"]);
    if($nummsg >= 5) { $check_msgs = 4; } else { $check_msgs = $nummsg; }
    for($i=$nummsg - $check_msgs,$j=0;$i<=$nummsg;$i++,$j++) {
      $msg = $phpgw->msg->header($mailbox,$i);
      $subject = !$msg->Subject ? '['.lang("no subject").']' : substr($msg->Subject,0,35).' ...';
      $portalbox->data[$j] = array(decode_header_string($subject),$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/message.php","folder=".urlencode($folder)."&msgnum=".$i));
    }
    echo $portalbox->draw();
    echo "<!-- Mailox info -->\n";
  }

  $phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>
