<?php
  /**************************************************************************\
  * phpGroupWare - User manual                                               *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "manual", "enable_utilities_class" => True);
  include("../header.inc.php");
?>
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_email.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
At present there are many new improvements being built into the 
email application function of this suite. As a result these documents are based on the functionality of the current stable
demo version, available for testing <a href="http://www.phpgroupware.org">&nbsp here. </a> with a few insites into the
 new development versions as well :)

<p>
<ul>
<b>Notes:</b>
<li>This application is connected with your address book, thus making finding email addresses
of people you want to email to, easy.  Point and click. 
<li>New mail is highlighted with a <font color="red"> * </font>
<li>Muliple addressee's or cc addresses should be seperated with a comma.
<li>Add attachment function is available in all stages of sending,composing,forwarding mail.
<li><b>Collecting and viewing:</b>
Clicking on the email icon in the navigation bar at the top of the screen, will make a connection
to your mailserver (as set by the systems admin). All your mail should then be displayed on screen.
<br>To read a particular mail, click on the subject line of the mail.
</ul>
<p>The following icons will be displayed at the top right of the screen and the email will then be displayed.
<br><img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_reply.gif">  Reply to sender
<br><img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_reply_all.gif">  Reply to all (sender and other cc recipients)
<br><img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_forward.gif">  Forward to another address
<br><img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/sm_delete.gif">  Delete 
<br>Make a choice ! :)
<ul>
<p><li><b>Replying:</b>
<br><i>To the sender:</i>
<br>Click on the reply icon,  the email will then be displayed on the screen, with the relevant details. Your cursor
will be positioned ready for you to type your reply at the top of the original mail. Type your reply, then click
on the send button, top right of your screen.
<p>
<i>To both sender and other cc recipents:</i>
<br>Click on the reply to all icon,  the email will then be displayed on the screen, with the relevant details. Your cursor
will be positioned ready for you to type your reply at the top of the original mail. Type your reply, then click
on the send button, top right of your screen.
<p>
<i>To forward the mail to another address:</i>
<br>Click on the reply to all icon,  the email will then be displayed on the screen, with the relevant details. Your cursor
will be positioned ready for you to type your reply at the top of the original mail. Type your reply, then click
on the send button, top right of your screen.
To send email to an address not in the address book, type the address in the field marked To: 
to send a copy to another email address, type in the field marked CC:, add a subject in 
the subject line, type the body of the large text field, then click on the send button.
<p>
<li><b>Adding attachments to mail:</b>
<br>To add an attachemnt to your mail click on the link "Add Attachment",  a popup window will 
appear, browse through your computer to find the file/files you want, then click ok.
<br>The pop box will disappear, and the file/files will be attached to your mail.
<br>This function is available in all steps of sending/forwarding mail.
<p><li><b>Composing:</b>
<br>To compose a new email message, click on the "compose" button at the bottom of the screen.
A screen will be displayed with a point
and click button to your address book. To use an address already in the address book,
click on the address button, a list of your current entries will be displayed, choose one.
<p><li><b>Saving:</b>
<br>Click the small box on the left of the screen, next to the message you want to save. A tick will appear in
the box, then click on folder. A list of folders will be displayed, chose the folder you want to save the mail
into and the email will be saved  into that folder.
<p><li><b>Deleting:</b>
<br>Click the small box on the left of the screen, next to the message you want to delete. A tick will appear in
the box, then scroll down to the bottom of the screen, click on delete. The mail will then be deleted.
<?php $phpgw->common->phpgw_footer(); ?>
