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
<img src="images/title_calendar.gif" border=0> 
<font face="Arial, Helvetica, san-serif" size="2">
<p>
A searchable daily,weekly,monthly calendar/scheduling application with alerts for high priority events.
<br> For viewing in either hourly/day,current week or monthly option, click on the relevant 
icons at the top left hand corner.
<br>
<ul>
<li><b>Viewing:</b><img src="images/today.gif">Day <img src="images/week.gif">Week <img src="images/month.gif">Month
<br><i>Day:</i>
<br>Current day is displayed, broken down in hour slots. Start and end time of the day
can be set in the preferences.
<br><i>Week:</i>
<br>Current week is displayed. Start day of week can be set in preferences.
<br><i>Month:</i>
<br>Default entry is to the current month, viewed in monthly option, with both prior and future
months easily accessible with one click.
<p>
<li><b>Adding an entry:</b> <img src="images/addevent.gif"> 
<br>To add a new entry for yourself other members of your group click on the small icon as shown above.
A form page will be presented, where you can input the relevant fields.
<table width="80%">
<td bgcolor="#ccddeb" width=50% valign="top">
<font face="Arial, Helvetica, san-serif"i size="2">
Brief Description:
<br>Full Description:
<br>Date:
<br>Time:
<br>Duration:
<br>Priority:
<br>Acccess:
</td>
<td bgcolor="#ccddeb" width="50%" valign="top">
<font face="Arial, Helvetica, san-serif" size="2">
Group selection:
<br>Participants;
<br>Repeat type:
<br>Repeat end date:
<br>Frequency:
</td>
</table>
Simply fill in the fields, and click Submit.
<br><b>Note:</b> Access can be set as with other applications in this suite, Private,Group Readable,Globally
Readable.
<p>
<li><b>Edit:Delete</b>&nbsp&nbsp<img src="images/circle.gif">
<br>To edit an entry group click on the small icon as shown above.
A form page will be presented, where you can edit the relevant fields.
Chose edit or delete from the bottom of the page.
<br><b>Note:</b>You can only make changes or delete those calendar entries created by you.
<p>
</ul>
</td>
</table>
</body>
</html>

