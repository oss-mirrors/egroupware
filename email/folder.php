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
	
  Header("Cache-Control: no-cache");
  Header("Pragma: no-cache");
  Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");

  if ($newsmode == "on"){$phpgw_info["flags"]["newsmode"] = True;}

  $phpgw_info["flags"] = array("currentapp" => "email", "enable_network_class" => True,
                               "enable_nextmatchs_class" => True);
  include("../header.inc.php");
?>

<table border=0 cellpadding="1" cellspacing="1" width="95%" align="center">
<form action="<?php echo $phpgw->link("/email/folder.php")?>" method="post">
<tr><td colspan=2 bgcolor="<?php echo $phpgw_info["theme"]["em_folder"]; ?>">

	<table border=0 cellpadding=0 cellspacing=1 width=100%>
	 <tr>
          <td valign="top">
		&nbsp;<font size=3 face=<?php echo $phpgw_info["theme"]["font"]; ?> color=<?php echo $phpgw_info[theme][em_folder_text]; ?>>Folders</font>
	  </td>
	 </tr>
	</table>
</td></tr>

  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
   <font size=2 face=<?php echo $phpgw_info["theme"]["font"]; ?>>
    <b>Folder name</b>
   </font>
  </td>
  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
   <font size=2 face=<?php echo $phpgw_info["theme"]["font"]; ?>>
    <b>Messages</b>
   </font>
  </td>


<?php

  $PROG_DIR = "mail";
  //$FILTER = $phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus" ? "INBOX." : $PROG_DIR;
  $FILTER = $phpgw->msg->construct_folder_str("");
  $folder = $phpgw->msg->construct_folder_str($name);
  $IMAP_STR = "{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . "143" . "}";

  if ($action == "create") {
     $phpgw->msg->createmailbox($mailbox,"$IMAP_STR$folder");
  }
  if ($action == "delete") {
     $phpgw->msg->deletemailbox($mailbox,"$IMAP_STR$folder");
  }

  $mailboxes = $phpgw->msg->listmailbox($mailbox, $IMAP_STR, "$FILTER*");

  sort($mailboxes); // added sort for folder names 
  if ($mailboxes) {
     if ($FILTER != "INBOX") {
        $tr_color = $phpgw_info["theme"]["row_on"];
	echo "<tr bgcolor=$tr_color><td><font size=2 face="
	   . $phpgw_info["theme"]["font"] . ">";
	echo "<a href=\"" . $phpgw->link("/email/index.php","folder=INBOX")
	   . "\">INBOX</a></font></td>";
	echo "<td width=20%><font size=2 face="
	   . $phpgw_info["theme"]["font"] . ">";
        $mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);
	echo $mailbox_status->unseen."/".$phpgw->msg->num_msg($mailbox) . "</font></td></tr>\n";
     }

     for ($i = 0; $i < count($mailboxes); $i++) {
        $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$phpgw->msg->reopen($mailbox, $mailboxes[$i]);
	$nm = substr($mailboxes[$i], strrpos($mailboxes[$i], "}") + 1, strlen($mailboxes[$i]));
	echo "<tr bgcolor=$tr_color><td><font size=2 face="
	   . $phpgw_info["theme"]["font"] . ">";

        $t_folder_s = $nm;
        if ($nm != "INBOX") {
           $nm = $phpgw->msg->deconstruct_folder_str($nm);
	} else {
	   $nm = "INBOX";
	}

	$url_nm = urlencode($nm);

	echo "<a href=\"" . $phpgw->link("/email/index.php","folder=$url_nm")
	   . "\">$nm</a></font></td>";
	echo "<td width=20%><font size=2 face=$theme[font]>";

        $mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}$t_folder_s",SA_UNSEEN);


	echo $mailbox_status->unseen."/".$phpgw->msg->num_msg($mailbox) . "</font></td></tr>\n";
     }
  } else {
     echo "<tr><td bgcolor=$COLOR_ROW_ON><font size=2 face=$theme[font]>";
     echo "<a href=\"" . $phpgw->link("/email/index.php","folder=INBOX")
        . "\">INBOX</a></font></td>";
     echo "<td bgcolor=$COLOR_ROW_ON width=20%><font size=2 face=$theme[font]>";
     $mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":" . $phpgw_info["user"]["preferences"]["email"]["mail_port"] . "}INBOX",SA_UNSEEN);
     echo $mailbox_status->unseen."/".$phpgw->msg->num_msg($mailbox) . "</font></td></tr>\n";
     echo $phpgw->msg->num_msg($mailbox) . "</font></td></tr>\n";
     echo $phpgw->msg->num_msg($mailbox) . "</font></td></tr>\n";
  }
  $phpgw->msg->close($mailbox);

?>

<tr><td colspan=2 align=right bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
<select name="action">
<option value="create">Create a folder</option>
<option value="delete">Delete the folder</option>
</select> <font size=2 face=<?php echo $phpgw_info["theme"]["font"] ?>><b><?php echo $L_NAMED ?></b></font>
<input type=text name="name"><input type=hidden name=folder value="<?php echo $folder ?>">
<input type=submit value="<?php echo lang("submit"); ?>"></td></tr></form>
</table>
<p>
<?php $phpgw->common->phpgw_footer(); ?>
