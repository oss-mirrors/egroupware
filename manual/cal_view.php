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
<img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/title_calendar.gif" border=0> 
<font face="Arial, Helvetica, san-serif" size="2">
<p>
A searchable daily,weekly,monthly calendar/scheduling application with alerts for high priority events.
<br> For viewing in either hourly/day,current week or monthly option, click on the relevant 
icons at the top left hand corner.
<br>
<ul>
<li><b>Viewing:</b><img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/today.gif">Day <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/week.gif">Week <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/month.gif">Month
<p><i>Day:</i>
<br>Current day is displayed, broken down in hour slots. Start and end time of the day
can be set in the preferences.
<p><i>Week:</i>
<br>Current week is displayed. Start day of week can be set in preferences.
<p><i>Month:</i>
<br>Default entry is to the current month, viewed in monthly option, with both prior and future
months easily accessible with one click.
<p>
</ul>
<?php $phpgw->common->phpgw_footer(); ?>
