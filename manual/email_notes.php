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
<br><li>This application is connected with your address book, thus making finding email addresses
of people you want to email to, easy.  Point and click. 
<br><li>New mail is highlighted with a <font color="red"> * </font>
<br><li>Muliple addressee's or cc addresses should be seperated with a comma.
<br><li>Add attachment function is available in all stages of sending,composing,forwarding mail.
<?php $phpgw->common->phpgw_footer(); ?>
