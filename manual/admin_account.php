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
<table cellpadding="10">
<td valign="top">
<?php echo show_menu($p); ?>
</td>
<td valign="top">
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_administration.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
This function is usually only available to the systems administrator of the system.
Administration of all applications, user and group accounts, session logging and control.
<ul>
<li><b>Account management:</b>
<p><i>User accounts:</i>
<br>Create, edit and delete users accounts. Set membership of groups, and access to applications.
<p><i>User groups:</i>
<br>Create, edit and delete groups.
<p>
</ul>
</td>
</table>
</body>
</html>
