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

  if(empty($folder)){ $folder="INBOX"; }

//  Header("Cache-Control: no-cache");
//  Header("Pragma: no-cache");
//  Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
  $phpgw_info["flags"] = array("currentapp" => "email", "enable_network_class" => True, "enable_nextmatchs_class" => True, "enable_menutree_class" => True);
  if (isset($newsmode) && $newsmode == "on"){$phpgw_info["flags"]["newsmode"] = True;}
  include("../header.inc.php");
  $phpgw->template->set_file(array("main"        => "main.tpl",
                                   "folders"     => "folders.tpl",
                                   "content"     => "messages.tpl",
                                   "row"         => "messages_row.tpl",
                                   "row_message" => "messages_row_message.tpl"
                            ));
  //$phpgw->template->set_unknowns("remove");
  $phpgw->template->set_var("lang_select_message_first",lang("Please select a "
                                                           . "message first"));
  $phpgw->template->set_var("form_action",$phpgw->link("index.php"));
  $phpgw->template->set_var("th_bg",$phpgw_info["theme"]["th_bg"]);


  // This will be later moved into header.inc.php
  function top_menu()
  {
     global $phpgw;
     $tpl = $phpgw->template;
     $tpl->set_file(array("menu" => "menu.tpl"));

     $tpl->set_var("left_arrows",$phpgw->nextmatchs->left("index.php",$start,
                                 $nummsg,"&sort=$sort&order=$order"
                               . "&folder=" . urlencode($folder)));
     $tpl->set_var("right_arrows",$phpgw->nextmatchs->right("index.php",$start,
                                  $nummsg,"&sort=$sort&order=$order"
                                . "&folder=" . urlencode($folder)));
     return $tpl->parse("menu_","menu");
  }

  function create_folder_list(&$mbp)
  {
     global $phpgw_info;
     $mt = CreateObject("phpgwapi.menutree");
     $mt->root_level_value = lang("Folders");
     $mt->read_from_file   = False;
     $s = ".Main account\n";
     $ta = list_folders($mbp);
     while (list($null,$folder) = each($ta)) {
        $s .= "..$folder|index.php?folder=" . urlencode($folder) . "\n";
     }
//     if ($phpgw_info["user"]["apps"]["nntp"]) {
        $s .= ".NNTP|index.php\n";
//     }
     return $mt->showtree($s);

  }

  @set_time_limit(0);

  if ($td) {
     if ($td == 1) {
        $messages .= lang("1 message has been deleted",$td);
     } else {
        $messages .= lang("x messages have been deleted",$td);
     }
  }
  $phpgw->template->set_var("menu",top_menu());
  $phpgw->template->set_var("messages",$messages);

  $nummsg = $phpgw->msg->num_msg($mailbox);

  if (! $start) {
     $start = 0;
  }

  if ($sort == "ASC") {
     $sort = 1;
  } else {
     $sort = 0;
  }

  if (! $order) {
     $order = 0;
     if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old") {
        $sort = 1;
     } else {
        $sort = 0;
     }
  }

  $mailbox_info = $phpgw->msg->mailboxmsginfo($mailbox);

  if ($folder != "INBOX") {
     $t_folder_s = $phpgw->msg->construct_folder_str($folder);
  } else {
     $t_folder_s = "INBOX";
  }

  $mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}$t_folder_s",SA_UNSEEN);

  if ($nummsg > 0) {
     $msg_array = array();
     $msg_array = $phpgw->msg->sort($mailbox, $order, $sort);
     $folder_info .= "<br>Saved messages: " . $nummsg;
     $folder_info .= "<br>New messages: " . $mailbox_status->unseen;

     $ksize = round(10*($mailbox_info->Size/1024))/10;
     $size = $mailbox_info->Size > 1024 ? "$ksize k" : $mailbox_info->Size;
     $folder_info .= "<br>Total size of folder: " . $size;

  } else {
     $folder_info = $nummsg;
  }
  //echo "$folder - $folder_info";

  if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" || $phpgw_info["flags"]["newsmode"]) {
     //echo '<select name="folder" onChange="document.switchbox.submit()">'
     //   . '<option>' . lang("switch current folder to") . ':';

     // This will become a function later
     $phpgw->template->set_var("folders",create_folder_list($mailbox));
//    create_folder_list();

     //echo list_folders($mailbox);
  }
/*
           ?>
         </td>
         <td>
           &nbsp;&nbsp;
           <?php
             if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
		echo '<input type="button" value="' . lang("folder") . '" onClick="'
		   . 'window.location=\'' . $phpgw->link("folder.php","folder="
		   . urlencode($folder)) . '\'">';
             }
           ?>
         </td>
        </tr>
       </table>
      </td>
     </tr>
    </table>
   </td>
  </tr>

  <tr>
  </form>
 <form name="delmov" action="<?php echo $phpgw->link("action.php"); ?>" method="post">
  <input type="hidden" name="what" value="delete">
  <input type="hidden" name="folder" value="<?php echo $folder; ?>">

 <td align="center" bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="3%">
   &nbsp;
 </td>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="2%">
   &nbsp;
 </td>

<?php */
  /*
  ** Sorting defs:
  **  SORTDATE:    0
  **  SORTFROM:    2
  **  SORTSUBJECT: 3
  **  SORTSIZE:    6
  */

  if ($nummsg == 0) {
     if (!$mailbox) {
        $phpgw->template->set_var("row_message",lang("Could not open this mailbox"));
        $phpgw->template->parse("rows","row_message");
     } else {
        $phpgw->template->set_var("row_message",lang("This folder is empty"));
        $phpgw->template->parse("rows","row_message");
     }
  }

  if ($nummsg < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
     $totaltodisplay = $nummsg;
  } else if (($nummsg - $start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
     $totaltodisplay = $start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
  } else {
     $totaltodisplay = $nummsg;
  }

  $phpgw->template->set_var("sort_date",$phpgw->nextmatchs->show_sort_order($sort,"0",$order,"index.php",lang("Date"),"&folder=".urlencode($folder)));
  $phpgw->template->set_var("sort_subject",$phpgw->nextmatchs->show_sort_order($sort,"3",$order,"index.php",lang("Subject"),"&folder=".urlencode($folder)));
  $phpgw->template->set_var("sort_sender",$phpgw->nextmatchs->show_sort_order($sort,"2",$order,"index.php",lang("From"),"&folder=".urlencode($folder)));
  $phpgw->template->set_var("sort_size",$phpgw->nextmatchs->show_sort_order($sort,"6",$order,"index.php",($newsmode=="on"?lang("lines"):lang("size")),"&folder=".urlencode($folder)));

  for ($i=$start; $i < $totaltodisplay; $i++) {
     $phpgw->template->set_var("tr_color",$phpgw->nextmatchs->alternate_row_color());

     $struct = $phpgw->msg->fetchstructure($mailbox, $msg_array[$i]);
     $attach = "&nbsp;";

     for ($j = 0; $j< (count($struct->parts) - 1); $j++) {
        if (!$struct->parts[$j]) {
           $part = $struct;
        } else {
           $part = $struct->parts[$j];
        }

        $att_name = get_att_name($part);
        if ($att_name != "Unknown") {
           $attach = "&nbsp;<font face=\"".$phpgw_info["theme"]["font"]
                   . "\" size=\"1\"><div align=\"right\">"
                   . "<img src=\"" . $phpgw_info["server"]["images_dir"] 
                   . "/attach.gif\" alt=\"file\"></div></font>";
        }
     }

     $msg = $phpgw->msg->header($mailbox, $msg_array[$i]);

     $subject = !$msg->Subject ? "[".lang("no subject")."]" : $msg->Subject;

     if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) {
        $size = $msg->Size;
     } else {
        $ksize = round(10*($msg->Size/1024))/10;
        $size = $msg->Size > 1024 ? "$ksize k" : $msg->Size;
     }

     // Whats up with this ??
     $bg = (($i + 1)/2 == floor(($i + 1)/2)) ? $phpgw_info["theme"]["row_off"] : $phpgw_info["theme"]["row_on"];
                        
     $phpgw->template->set_var("row_checkbox",'<input type="checkbox" name="msglist[]" value="' . $msg_array[$i] . '">');

     if (($msg->Unseen == "U") || ($msg->Recent == "N")) {
        $phpgw->template->set_var("row_info",'<font color="FF0000">*</font>&nbsp;' . $attach);
     } else {
        $phpgw->template->set_var("row_info","&nbsp;$attach");
     }

     $phpgw->template->set_var("row_subject",'<a href="' . $phpgw->link("message.php","folder=" . urlencode($folder) ."&msgnum=".$msg_array[$i]) . '">'
                . decode_header_string($subject) . '</a>');

     if ($msg->reply_to[0]) {
        $reply = $msg->reply_to[0];
     } else {
        $reply = $msg->from[0];
     }
     $replyto = $reply->mailbox . "@" . $reply->host;

     $from = $msg->from[0];
     $personal = !$from->personal ? "$from->mailbox@$from->host" : $from->personal;
     if ($personal == "@") {
        $personal = $replyto;
     }

     $phpgw->template->set_var("row_sender",'<a href="' . $phpgw->link("compose.php","folder="
             . urlencode($folder) . "&to=" . urlencode($replyto)) . '">'
             . decode_header_string($personal) . '</a>');

     $phpgw->template->set_var("row_date",$phpgw->common->show_date($msg->udate));
     $phpgw->template->set_var("row_size",$size);
     $phpgw->template->parse("rows","row",True);
  }
  $phpgw->template->parse("content_","content");

/*
?>
<tr>
  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" align="center">
   <a href="javascript:check_all()">
    <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/check.gif" border="0" height="16" width="21">
   </a>
  </td>

  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" colspan="5">

        <table width="100%" border="0" cellpadding="0" cellspacing="0">
         <tr>
          <td>
            <input type="button" value="<?php echo lang("delete"); ?>" onClick="do_action('delall')">
            <a href="<?php echo $phpgw->link("compose.php","folder=".urlencode($folder)); ?>"><?php echo lang("compose"); ?></a>
          </td>
          <td align="right">
           <?php
             if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
                echo '<select name="tofolder" onChange="do_action(\'move\')">'
                   . '<option>' . lang("move selected messages into") . ':';
                echo list_folders($mailbox);
		echo "</select>";
            
             } */

  $phpgw->template->pparse("out","main");
  $phpgw->common->phpgw_footer();
  $phpgw->msg->close($mailbox);
?>
