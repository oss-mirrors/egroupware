<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
  if($d1 == "htt" || $d1 == "ftp" ) {
    echo "Failed attempt to break in via an old Security Hole!<br>\n";
    $phpgw->common->phpgw_exit();
  } unset($d1);

  $tmp_app_inc = $phpgw_info["server"]["app_inc"];
  $phpgw_info["server"]["app_inc"] = $phpgw->common->get_inc_dir('email');

  if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"]) {
    include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");
    echo "\n".'<tr><td align="left"><!-- Mailbox info -->'."\n";
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
    if ($mailbox_status->unseen == 0) {
       $str .= lang("You have no new messages");
    }
    $nummsg = $phpgw->msg->num_msg($mailbox);
    //$title = '<a href="'.$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/index.php").'">EMail'.($str ? ' - '.$str : '').'</a>';
    $title = '<font color="FFFFFF">EMail' . ($str ? ' - ' . $str : '') . '</font>';

    $portalbox = CreateObject('phpgwapi.linkbox',Array($title,$phpgw_info["theme"]["navbar_bg"],$phpgw_info["theme"]["bg_color"],$phpgw_info["theme"]["bg_color"]));
    $portalbox->setvar('width',600);
    $portalbox->outerborderwidth = 0;
    $portalbox->header_background_image = $phpgw_info["server"]["webserver_url"]
                                        . "/phpgwapi/templates/verdilak/images/bg_filler.gif";
    if($nummsg >= 5) { $check_msgs = 5; } else { $check_msgs = $nummsg; }
    for($i=$nummsg - $check_msgs + 1,$j=0;$i<=$nummsg;$i++,$j++) {
      $msg = $phpgw->msg->header($mailbox,$i);
      $subject = !$msg->Subject ? '['.lang("no subject").']' : substr($msg->Subject,0,65).' ...';
      $portalbox->data[$j] = array(decode_header_string($subject),$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/message.php","folder=".urlencode($folder)."&msgnum=".$i));
    }
    echo $portalbox->draw();
    echo "\n".'<!-- Mailox info --></td></tr>'."\n";
  }

  $phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>
