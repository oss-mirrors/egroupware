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

// This will eventually be written using templates.

  $phpgw_flags["currentapp"] = "email";
  include("../header.inc.php");
  $msg = $phpgw->msg->header($mailbox, $msgnum);
  $struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
  $totalmessages = $phpgw->msg->num_msg($mailbox);

  $subject = !$msg->Subject ? lang_email("no subject") : $msg->Subject;
  $subject = decode_header_string($subject);
  $from = $msg->from[0];

  $message_date = $phpgw->preferences->show_date($msg->udate);

  $personal = !$from->personal ? "$from->mailbox@$from->host" : $from->personal;

  if (! $folder)
     $folder = "INBOX";
?>

<table cellpadding="1" cellspacing="1" width="95%" align="center"><form>
<tr><td colspan="2" bgcolor="<?php echo $phpgw_info["theme"]["em_folder"]; ?>">

      <table border="0" cellpadding="0" cellspacing="1" width="100%">
       <tr>
         <td>
  	  <font size="3" face="<?php echo $phpgw_info["theme"]["font"] . "\" color=\"" . $phpgw_info["theme"]["em_folder_text"]; ?>">
	   <a href="<?php echo $phpgw->link($phpgw_info["server"]["webserver_url"]."/email/","folder=" . urlencode($folder)); ?>"><?php echo $folder; ?></a>
         </font>
        </td>

        <td align=right><font size="3" face="<?php echo $phpgw_info["theme"]["font"] . "\" color=\"" . $phpgw_info["theme"]["em_folder_text"]; ?>">
         <a href="<?php echo $phpgw->link("compose.php","action=reply&folder=".urlencode($folder)."&msgnum=$msgnum"); ?>">
          <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_reply.gif" height=19 width=26 alt="<?php echo lang_email("reply"); ?>"></a>
         <a href="<?php echo $phpgw->link("compose.php","action=replyall&folder=".urlencode($folder)."&msgnum=$msgnum"); ?>">
          <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_reply_all.gif" height=19 width=26 alt="<?php echo lang_email("reply all"); ?>"></a>
         <a href="<?php echo $phpgw->link("compose.php","action=forward&folder=".urlencode($folder)."&msgnum=$msgnum"); ?>">
         <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_forward.gif" height=19 width=26 alt="<?php echo lang_email("forward"); ?>"></a>
         <a href="<?php echo $phpgw->link("action.php","what=delete&folder=".urlencode($folder)."&msgnum=$msgnum"); ?>">
          <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_delete.gif" height=19 width=26 alt="<?php echo lang_email("delete"); ?>"></a></font>
	</td>
        <td align="right">
         <?php
           // Move this up top.
	   $session_folder = "folder=".urlencode($folder)."&msgnum";

           if ($msgnum != 1) {
              $pm = $msgnum - 1;
              echo "<a href=\"".$phpgw->link("message.php","$session_folder=$pm")."\"><img border=0 src=\"".$phpgw_info["server"]["images_dir"]."/left.gif\" alt=\"Previous Message\"></a>";
           } else {
              echo "<img border=0 src=\"".$phpgw_info["server"]["images_dir"]."/left-grey.gif\" alt=\"No Previous Message\">";
           }

           if ($msgnum < $totalmessages) {
              $nm = $msgnum + 1;
              echo "<a href=\"".$phpgw->link("message.php","$session_folder=$nm")."\"><img border=0 src=\"".$phpgw_info["server"]["images_dir"]."/right.gif\" alt=\"Next Message\"></a>";
           } else {
              echo "<img border=0 src=\"".$phpgw_info["server"]["images_dir"]."/right-grey.gif\" alt=\"No Next Message\">";
           }
         ?>
        </td>
       </tr>
      </table>

</td>

<tr>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" valign="top">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo lang_email("from"); ?>:</b>
  </font> 
 </td> 
 <td bgcolor="<?php echo $phpgw_info["theme"]["row_on"]; ?>" width="570">
  
<?php 

if ($msg->from) {
   echo "<font size=\"2\" face=\""
      . $phpgw_info["theme"]["font"]."\">"
      . "<a href=\""
      . $phpgw->link("compose.php","folder="
        . urlencode($folder) . "&to=" . urlencode($from->mailbox . "@"
        . $from->host)) 
      . "\">". htmlentities(decode_header_string($personal))
      . "</a></font>";
   echo "<font size=\"2\" face=\"" . $phpgw_info["theme"]["font"]."\">"
      . " <a href=\""
      . $phpgw->link($phpgw_info["server"]["webserver_url"]
        . "/addressbook/add.php", "add_email=" 
        . urlencode($from->mailbox . "@" . $from->host)) 
      . "\" target=\"_new\">"
        . "<img src=\""
        . $phpgw_info["server"]["app_images"]."/sm_envelope.gif\" width=10 height=8 alt=\"Add to address book\" border=\"0\" align=ABSMIDDLE></a></font>";
} else {
   echo lang_email("Undisclosed Sender");
   echo "\n";
}

?>
  </font>
 </td>
</tr>

<tr>
 <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" valign="top">
  <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
   <b><?php echo lang_email("to"); ?>:</b>
  </font> 
 </td> 
 <td bgcolor="<?php echo $phpgw_info["theme"]["row_on"]; ?>" width="570">
  <font size="2" face=<?php echo $phpgw_info["theme"]["font"]; ?>>
<?php

if ($msg->to) {
  for ($i = 0; $i < count($msg->to); $i++) {
    $topeople = $msg->to[$i];
    $personal = !$topeople->personal ? "$topeople->mailbox@$topeople->host" : $topeople->personal;
    $personal = decode_header_string($personal);
      echo "<a href=\""
        . $phpgw->link("compose.php", "folder=".urlencode($folder)
          ."&to=".$topeople->mailbox."@".$topeople->host)
        . "\">".$personal."</a>";

      echo "&nbsp;<a href=\""
        . $phpgw->link($phpgw_info["server"]["webserver_url"]
          ."/addressbook/add.php","add_email="
        . urlencode($topeople->mailbox."@".$topeople->host) 
        . "&name=" . urlencode($personal))
        . "\" target=\"_new\">"
        . "<img src=\""
        . $phpgw_info["server"]["app_images"]."/sm_envelope.gif\" height=8 width=10 alt=\"Add to address book\" border=\"0\" align=ABSMIDDLE></a>";
      if($i + 1 < count($msg->to)) {
        echo ", "; // throw a spacer comma in between addresses.
      }
//      echo "</td></tr>\n";
   }
} else {
  echo lang_email("Undisclosed Recipients");
  echo "\n";
}

echo "</td></tr>";

if ($msg->cc) {
?>
   <tr>
    <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" valign="top">
     <font size=2 face=<?php echo $phpgw_info["theme"]["font"]; ?>>
      <b><?php echo lang_email("cc"); ?>:</b>
    </td>
    <td bgcolor="<?php echo $phpgw_info["theme"]["row_on"]; ?>" width="570">
     <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
      <?php

  for ($i = 0; $i < count($msg->cc); $i++) {
    $ccpeople = $msg->cc[$i];
    $personal = !$ccpeople->personal ? "$ccpeople->mailbox@$ccpeople->host" : $ccpeople->personal;
    $personal = decode_header_string($personal);

      echo "<a href=\""
        . $phpgw->link("compose.php", "folder=".urlencode($folder)
          ."&to=".urlencode($ccpeople->mailbox."@".$ccpeople->host))
        . "\">".$personal."</a>";

      echo "&nbsp;<a href=\""
        . $phpgw->link($phpgw_info["server"]["webserver_url"]
          ."/addressbook/add.php","add_email="
        . urlencode($topeople->mailbox."@".$topeople->host) 
        . "&name=" . urlencode($personal))
        . "\" target=\"_new\">"
        . "<img src=\""
        . $phpgw_info["server"]["app_images"]."/sm_envelope.gif\" height=8 width=10 alt=\"Add to address book\" border=\"0\" align=ABSMIDDLE></a>";
      if($i + 1 < count($msg->cc)) {
        echo ", "; // throw a spacer comma in between addresses.
      }
   }
   echo "</td></tr>\n";
}

?>

<tr>
  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" valign="top">
    <font size=2 face="<?php echo $phpgw_info["theme"]["font"]; ?>">
      <b><?php echo lang_email("date"); ?>:</b>
    </font>
    </td>
    <td bgcolor="<?php echo $phpgw_info["theme"]["row_on"]; ?>" width="570">
     <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
     <?php echo $message_date; ?>
     </font>
  </td>
</tr>
<?php
  $flag = 0;
  for ($z = 0; $z < count($struct->parts); $z++) {
      $part = !$struct->parts[$z] ? $part = $struct : $part = $struct->parts[$z];
      $att_name = get_att_name($part);

      if ($att_name != "Unknown") {
	 // if it has a name, it's an attachment
	 $f_name[$flag] = attach_display($part, $z+1);
	 $flag++;
      }
  }
  if ($flag != 0) {
     echo "<tr><td bgcolor=".$phpgw_info["theme"]["th_bg"]." valign=top>";
     echo "<font size=2 face=\"".$phpgw_info["theme"]["font"]."\"><b>";
     echo lang_email("files") . ":</b></td><td bgcolor=\"".$phpgw_info["theme"]["row_on"]."\" width=570>";
     echo "<font size=2 face=\"".$phpgw_info["theme"]["font"]."\">";
     echo implode(", ", $f_name);
     echo "</td></tr>";
  }

?>
 <tr>
  <td bgcolor=<?php echo $phpgw_info["theme"]["th_bg"] ?> valign=top>
   <font size=2 face=<?php echo $phpgw_info["theme"]["font"] ?>>
    <b><?php echo lang_email("subject") ?>:</b>   </font>
  </td>  <td bgcolor=<?php echo $phpgw_info["theme"]["row_on"]; ?> width=570>
   <font size=2 face=<?php echo $phpgw_info["theme"]["font"]; ?>>
    <?php echo $subject; ?>
   </font>
  </td>
 </tr>
</table>

<br><table border=0 cellpadding=1 cellspacing=1 width=95% align="center">
<tr>
  <td align="center">

<?php

  $numparts = !$struct->parts ? "1" : count($struct->parts);
  echo "<!-- This message has " . $numparts . " part(s) -->\n";

  for ($i = 0; $i < $numparts; $i++) {
      $part = !$struct->parts[$i] ? $part = $struct : $part = $struct->parts[$i];

      $att_name = get_att_name($part);
      if ($att_name == "Unknown") {
	 if (strtoupper(get_mime_type($part)) == "MESSAGE") {
	    inline_display($part, $i+1);
	    echo "\n<p>";	 } else {
	    inline_display($part, $i+1);
	    echo "\n<p>";
	 }
      }

      $mime_encoding = get_mime_encoding($part);
      if (($mime_encoding == "base64") and ($part->subtype == "JPEG" or $part->subtype == "GIF" or $part->subtype == "PJPEG")) {
	 // we want to display images here, even though they are attachments.
	 echo "<p>" . image_display($folder, $msgnum, $part, $i+1, $att_name) . "<p>\n";
      }

  }
?>
</td></form></tr></table></table>


<?php $phpgw->msg->close($mailbox); 
  include($phpgw_info["server"]["api_dir"]."/footer.inc.php");
?>
