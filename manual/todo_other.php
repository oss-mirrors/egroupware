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
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_todo.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
A searchable todo list for keeping a quick note of things todo.
<p>
<ul>
<li><b>Search:</b>
<br>Enter a keyword for the task you are looking for, click on the search button.
<p>
<li><b>Filter:</b>
<br>Todo items can be listed in two ways:
<dd>Show all = all todo /tasks for all groups you are a member of.
<dd>Only yours = only your own tasks.
</ul>
<?php $phpgw->common->phpgw_footer(); ?>
