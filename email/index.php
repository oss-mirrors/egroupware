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

  Header("Cache-Control: no-cache");
  Header("Pragma: no-cache");
  Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
  if ($newsmode == "on"){$phpgw_info["flags"]["newsmode"] = True;}

  $phpgw_info["flags"]["currentapp"] = "email";
  include("../header.inc.php");

  if ($newsmode == "on")
    $phpgw->common->read_preferences($phpgw_info["user"]["account_id"],"nntp",True);
  set_time_limit(0);
?>

<script>

function do_action(act)
{
  flag = 0;
  for (i=0; i<document.delmov.elements.length; i++) {
      //alert(document.delmov.elements[i].type);
      if (document.delmov.elements[i].type == "checkbox") {
         if (document.delmov.elements[i].checked) {
            flag = 1;
         }
      }
   }
   if (flag != 0) {
      document.delmov.what.value = act;
      document.delmov.submit();
   } else {
      alert("<?php echo lang("Please select a message first")."."; ?>");
      document.delmov.tofolder.selectedIndex = 0;
   }
}

function check_all()
{
  for (i=0; i<document.delmov.elements.length; i++) {
      if (document.delmov.elements[i].type == "checkbox") {
         if (document.delmov.elements[i].checked) {
            document.delmov.elements[i].checked = false;
         } else {
            document.delmov.elements[i].checked = true;
         }
      } 
  }
}

</script>
<?php
  if ($td) {
     if ($td == 1) {
        echo "<p><center>" . lang("1 message has been deleted",$td) . "</center>";
     } else {
        echo "<p><center>" . lang("x messages have been deleted",$td) . "</center>";
     }
  }
?>


<form name="switchbox" action="<?php echo $phpgw->link("index.php")?>" method="post">
 <table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
  <tr bgcolor="<?php echo $phpgw_info["theme"]["bg_color"]; ?>" align="center">
    <td>&nbsp;</td>
<?php
#     $out = $nummsg == 1 ? " ".lang("message") : " ".lang("messages");
#     echo $out;

      $nummsg = $phpgw->msg->num_msg($mailbox);

      if (! $start)
         $start = 0;

     echo $phpgw->nextmatchs->left("index.php",$start,$nummsg,"&sort=$sort&order=$order"
                                 . "&folder=" . urlencode($folder));

     echo "<td>&nbsp;</td>";

     echo $phpgw->nextmatchs->right("index.php",$start,$nummsg,"&sort=$sort&order=$order"
                                  . "&folder=" . urlencode($folder));
?>
    <td>&nbsp;</td>
  </tr>
  <tr>
   <?php 
     if ($sort == "ASC") {
        $sort = 1;
     } else {
        $sort = 0;
     }

     if (! $order) {
        $order = "0";
        if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old") {
	   $sort = "1";
        } else {
           $sort = "0";
        }
     }/* else {

     } */
  ?>
  </tr>
 </table>

 <table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
  <tr>
   <td colspan="6" bgcolor="<?php echo $phpgw_info["theme"]["em_folder"]; ?>">
    <table border="0" cellpadding="0" cellspacing="1" width="100%">
     <tr>
      <td>
        <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?> color="
        <?php echo $phpgw_info["theme"]["em_folder_text"]; ?>">
<?php
      $mailbox_info = $phpgw->msg->mailboxmsginfo($mailbox);

      $mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["server"]["mail_port"] . "}INBOX",SA_UNSEEN);

      if ($nummsg > 0) {
         $msg_array = $phpgw->msg->sort($mailbox, $order, $sort);
         $folder_info .= "<br>Saved messages: " . $nummsg;
         $folder_info .= "<br>New messages: " . $mailbox_status->unseen;

         $ksize = round(10*($mailbox_info->Size/1024))/10;
         $size = $mailbox_info->Size > 1024 ? "$ksize k" : $mailbox_info->Size;
         $folder_info .= "<br>Total size of folder: " . $size;

      } else {
         $folder_info = $nummsg;
      }

      echo "$folder - $folder_info";
?>

        </font>
      </td>
      <td align="right">
       <table border="0" cellpadding="0" cellspacing="0">
        <tr>
         <td>
           <?php
             if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" || $phpgw_info["flags"]["newsmode"]) {
                echo '<select name="folder" onChange="document.switchbox.submit()">'
                   . '<option>' . lang("switch current folder to") . ':';
                echo list_folders($mailbox);
	 	echo "</select>";
             }
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

<?php
  /*
     Sorting defs:
     SORTDATE:  0
     SORTFROM:  2
     SORTSUBJECT: 3
     SORTSIZE:  6
  */
?>

 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="34%">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo $phpgw->nextmatchs->show_sort_order($sort,"3",$order,"index.php",lang("subject"),"&folder=".urlencode($folder)); ?></b>
  </font>
 </td>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="23%">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo $phpgw->nextmatchs->show_sort_order($sort,"2",$order,"index.php",lang("from"),"&folder=".urlencode($folder)); ?></b>
  </font>
 </td>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="12%">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo $phpgw->nextmatchs->show_sort_order($sort,"0",$order,"index.php",lang("date"),"&folder=".urlencode($folder)); ?></b>
  </font>
 </td>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" width="4%">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo $phpgw->nextmatchs->show_sort_order($sort,"6",$order,"index.php",lang("size"),"&folder=".urlencode($folder)); ?></b>
  </font>
 </td>
</tr>

<?php
        if ($nummsg == 0) {
          if (!$mailbox) {
	   echo "<tr><td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" colspan=\"6\" align=\"center\">"
	      . lang("Could not open this mailbox")."</td></tr>";
	  } else {
           echo "<tr><td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" colspan=\"6\" align=\"center\">"
              . lang("this folder is empty")."</td></tr>";
	  }
        }

        if ($nummsg < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
           $totaltodisplay = $nummsg;
        } else if (($nummsg - $start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
           $totaltodisplay = $start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
        } else {
           $totaltodisplay = $nummsg;
        }

        for ($i=$start; $i < $totaltodisplay; $i++) {

           $struct = $phpgw->msg->fetchstructure($mailbox, $msg_array[$i]);
           $attach = "&nbsp;";

           for ($j = 0; $j< (count($struct->parts) - 1); $j++) {
              if (!$struct->parts[$j]) {
                 $part = $struct;
              } else {
                 $part = $struct->parts[$j];          }

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

           $ksize = round(10*($msg->Size/1024))/10;
           $size = $msg->Size > 1024 ? "$ksize k" : $msg->Size;

           // Whats up with this ??
           $bg = (($i + 1)/2 == floor(($i + 1)/2)) ? $phpgw_info["theme"]["row_off"] : $phpgw_info["theme"]["row_on"];
                        
           echo "<tr><td bgcolor=\"$bg\" align=\"center\">"
              . "<input type=\"checkbox\" name=\"msglist[]\" value=\"".$msg_array[$i]."\"></td>\n";
           if (($msg->Unseen == "U") || ($msg->Recent == "N"))
              echo "<td bgcolor=\"$bg\" width=\"1%\" align=\"center\"><font color=\"FF0000\">"
                 . "*</font>&nbsp;$attach</td>";
           else
              echo "<td bgcolor=\"$bg\" width=\"1%\">&nbsp;$attach</td>";

           echo "<td bgcolor=\"$bg\"><font size=\"2\" face=\"".$phpgw_info["theme"]["font"]."\">"
              . "<a href=\"".$phpgw->link("message.php","folder="
              . urlencode($folder)."&msgnum=".$msg_array[$i]) . "\">"
              . decode_header_string($subject) . "</a></font></td>\n"
              . "<td bgcolor=\"$bg\"><font size=\"2\" face=\"".$phpgw_info["theme"]["font"]."\">";

           if ($msg->reply_to[0]) {
             $reply   = $msg->reply_to[0];
           } else {
             $reply   = $msg->from[0];
           }
           $replyto = $reply->mailbox . "@" . $reply->host;
           
           $from = $msg->from[0];
           $personal = !$from->personal ? "$from->mailbox@$from->host" : $from->personal;
           if ($personal == "@")
              $personal = $replyto;

           echo "<a href=\"" . $phpgw->link("compose.php","folder="
              . urlencode($folder) . "&to=" . urlencode($replyto)) . "\">"
              . decode_header_string($personal) . "</a>";

           echo "</font></td>\n"
              . "<td bgcolor=\"$bg\"><font size=\"2\" face=\"".$phpgw_info["theme"]["font"]."\">";

           echo $phpgw->common->show_date($msg->udate);

           echo "<td bgcolor=\"$bg\"><font size=\"2\" face=\"".$phpgw_info["theme"]["font"]."\">$size</font>"
              . "</td></tr></font></td></tr>";
        }
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
            
             }
             $phpgw->msg->close($mailbox);
             ?>
          </td>
         </tr>
        </table>

  </td>
</tr>
</form></table>

<br> 
<table border="0" align="center" width="95%">
 <tr>
  <td align="left"><font color="FF0000">*</font>&nbsp;<?php echo lang("New message"); ?></td>
 </tr>
</table>
<?php $phpgw->common->phpgw_footer(); ?>
