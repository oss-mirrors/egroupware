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
<?php include("side.php"); ?>
<td valign="top">
<img src="images/title_administration.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
This function is usually only available to the systems administrator of the system.
Administration of all applications, user and group accounts, session logging and control.
<ul>
<li><b>Session management:</b>
<p><i>View sessions:</i>
<br>Current sessions, IP, Login Time, Idle Time, and gives option to kill session.
<p><i>View Access Log:</i>
<br>LoginId, IP, Login Time, Logout Time, Total time spent.
</ul>
</td>
</table>
</body>
</html>
