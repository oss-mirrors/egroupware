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
  $phpgw_info["server"]["app_inc"] = $phpgw_info["server"]["server_root"]."/email/inc";

  if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"] &&
     (isset($phpgw_info["user"]["apps"]["email"]) && $phpgw_info["user"]["apps"]["email"])) {
    include($phpgw_info["server"]["app_inc"] . "/functions.inc.php");
    echo "\n".'<tr><td align="left"><!-- Mailbox info -->'."\n";

  	$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);

    $nummsg = intval($phpgw->msg->num_msg($mailbox));

  	$str = '';
  	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
      if ($mailbox_status->unseen == 1) {
        $str .= lang("You have 1 new message!");
      }
      if ($mailbox_status->unseen > 1) {
        $str .= lang("You have x new messages!",$mailbox_status->unseen);
      }
      if ($mailbox_status->unseen == 0) {
        $str .= lang("You have no new messages");
      }
      for($i=$nummsg,$j=5;$j>=0;$i--) {
        if($i==0) break;
        $msg = $phpgw->msg->header($mailbox,$i);
        if (($msg->Unseen == "U") || ($msg->Recent == "N")) {
          $subject = !$msg->Subject ? '['.lang("no subject").']' : substr($msg->Subject,0,65).' ...';
          $data[$j--] = array(decode_header_string($subject),$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/message.php","folder=".urlencode($folder)."&msgnum=".$i));
        }
      }
    } else {
      if ($nummsg > 0) {
        $str .= lang("You have messages!");
      } elseif ($nummsg == 0) {
        $str .= lang("You have no new messages");
      }
      if($nummsg >= 5) { $check_msgs = 5; } else { $check_msgs = $nummsg; }
      for($i=$nummsg - $check_msgs + 1,$j=0;$i<=$nummsg;$i++,$j++) {
        $msg = $phpgw->msg->header($mailbox,$i);
        $subject = !$msg->Subject ? '['.lang("no subject").']' : substr($msg->Subject,0,65).' ...';
        $data[$j] = array(decode_header_string($subject),$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/message.php","folder=".urlencode($folder)."&msgnum=".$i));
      }
    }
    
    //$title = '<a href="'.$phpgw->link($phpgw_info["server"]["webserver_url"]."/email/index.php").'">EMail'.($str ? ' - '.$str : '').'</a>';
    $title = '<font color="FFFFFF">EMail' . ($str ? ' - ' . $str : '') . '</font>';
    $linkbox_params [0] = $title;
    $linkbox_params [1] = $phpgw_info["theme"]["navbar_bg"];
    $linkbox_params [2] = $phpgw_info["theme"]["bg_color"];
    $linkbox_params [3] = $phpgw_info["theme"]["bg_color"];
    $portalbox = CreateObject("phpgwapi.linkbox", $linkbox_params);
    $portalbox->setvar('width',600);
    $portalbox->outerborderwidth = 0;
    $portalbox->header_background_image = $phpgw_info["server"]["webserver_url"]
                                        . "/phpgwapi/templates/verdilak/images/bg_filler.gif";
    $portalbox->data = $data;
    echo $portalbox->draw();
    echo "\n".'<!-- Mailox info --></td></tr>'."\n";
  }

  $phpgw_info["server"]["app_inc"] = $tmp_app_inc;
?>
