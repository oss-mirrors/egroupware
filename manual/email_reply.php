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
<?php $phpgw->common->phpgw_footer(); ?>
