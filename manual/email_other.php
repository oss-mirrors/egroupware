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
