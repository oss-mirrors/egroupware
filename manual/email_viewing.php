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
<p>Make a choice ! :)
<ul>
<?php $phpgw->common->phpgw_footer(); ?>
