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
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_hr.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
This page serves as the "human resource" area and lists all users and groups in the system.
<p>
<ul>
<li><b>Users:</b>
<br>Click on the name of the user, and you will be presented with the information
about that .
<br>Information covers:
<dd>name 
<dd>title 
<dd>short comments field
<dd>image
<dd>listing of the groups membership.
<br>The information is non sensative and is created by the user themselves, using the
profile setting option in preferences.
<p>
<li><b>Groups:</b>
<br>Click on the name of the group to see a complete listing of all members of that group.
</ul>
<?php $phpgw->common->phpgw_footer(); ?>
