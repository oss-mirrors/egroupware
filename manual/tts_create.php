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
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_tts.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
The system functionality can be used to allocate, track and audit tasks of 
groups or members of a specific group.
<ul>
<li><b>Create:</b>
<br>To create a ticket, click on "New Ticket", you will then be presented with the form to add
the details as above.. 
<p>At the top of the screen there are two clickable areas
<br><font color="blue">[New ticket | View all tickets]</font>
Clicking on the View all tickets, will change to read
<br><font color="blue">[New ticket | View only open tickets]</font>and only the open tickets
in the system will be displayed.
<p>
<table width="80%">
<td bgcolor="#ccddeb" width=50% valign="top">
<font face="Arial, Helvetica, san-serif"i size="2">
Last name:
<br>ID:
<br>Assigned from:
<br>Open date:
<br>Closed date: (if applicable)
<br>Priority:
<br>Group:
<br>Assigned to:
<br>Subject:
<br>Details:
<br>Additional notes:
<br>Update:OK:Close buttons:
</td>
</table>
<?php $phpgw->common->phpgw_footer(); ?>
