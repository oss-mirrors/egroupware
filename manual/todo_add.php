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
<img src="images/title_todo.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
A searchable todo list for keeping a quick note of things todo.
<p>
<ul>
<li><b>Add:</b>
<br> Click on the add button, you will be presented with the following fields:
<p><i> Blank text box:</i>
<br> Enter you todo in this box, (e.g. don't forget to feed the cat:)
<p><i> Urgency:</i>
<br> Set the urgency of the task, Low,Normal,High
<p><i> Completed:</i>
<br> Drop down menu, percentage of task completion.
<p><i> Date due:</i>
<br> Format: Drop down menu for month, type in day and year, (e.g. January 01 2000)
<br> or simply a number to represent how many days from start date the task needs 
to be completed by.
<p><i> Access type:</i>
<br> Access can be restricted to, private, group readable (that is the members of the
same groups as you are in) will be able to see the too, 
<br>Todo items made globally readable, will allow all users to the system will be able to read the todo.
<p><i> Which Groups:</i>
<br>Drop down menu of groups you are a member of, select one or more. 
<p>
</ul>
</td>
</table>
</body>
</html>

