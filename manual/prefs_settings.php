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
<img src="images/title_preferences.gif" border=0>
<font face="Arial, Helvetica, san-serif" size="2">
<p>
The place to make changes to your personal piece of groupware.
<p>
<ul>
<li><b>Change your Settings:</b>
<br><i>Max matches per page:</i>
<br>Choose how many instances of items will be displayed on your screen at one time, default is 15.
<p><i>Show text on navigation icons:</i>
<br>Add text under the navigation icons at the top of the screen.
<p><i>Time zone offset:</i>
<br>Set your time zone, default setting is Central Europian Standard CEST.
<p><i>Date format:</i>
<br>Choose the order of day/month/year, default is m/d/y.
<p><i>Time format:</i>
<br>Choose from am/pm=12hours or 24hours settings.
<p><i>Language:</i>
<br>Set your language, options German,Spanish,Norwegen,Italian,French, default is English.
<p><i>Show birthday reminders on main screen:</i>
<br>Selecting this option, enables birthday reminders to be shown on the day as
an alert. When you log on to the system on the day a birthday is entered, an alert
will be displayed on the home screen. Birthday settings are made in the address book.
<p><i>Show high priority events on main screen:</i>
<br>Selecting this option, enables reminders for priority tasks assigned to you in
either todo list or ticketing system.
<p><i>Weekday starts on:</i>
<br>Choose the day your week starts.<font color=green> I can't find where this affects
anything ?</font>
<p><i>Workday starts on:</i>
<br>Choose the start time of your work day. This will effect the beginning time slot
in the calendar when displaying the days schedule.
<p><i>Workday ends on:</i>
<br>Choose the end time of your work day. This will effect the ending time slot 
in the calendar when displaying the days schedule.
<p><i>Select Headline News sites:</i>
<br>Click on as many news headline news sites as you wish, these will be displayed for you
when using the headlines function.The systems admin set these as default, so be sure to
let them know if you want some that are not there ;)
</td>
</table>
</body>
</html>
