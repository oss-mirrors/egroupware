<?php
  /**************************************************************************\
  * phpGroupWare - Calendar                                                  *
  * http://www.phpgroupware.org                                              *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if (isset($friendly) && $friendly){
     $phpgw_info["flags"]["noheader"] = True;
  } else {
     $friendly = 0;
  }

  $phpgw_info["flags"] = array("currentapp" => "calendar", "enable_calendar_class" => True, "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $view = "year";

  if ($friendly) {
     echo '<body bgcolor="' . $phpgw_info["theme"]["bg_color"] . '">';
  }
?>

<center>
<table border="0" cellspacing="4" cellpadding="4">
 <tr>
  <?php
    if (!$friendly)
       echo "<td align=\"left\"><A HREF=\"" . $phpgw->link("year.php","year=" . ($year - 1)) . "\">&lt;&lt;</A>";
  ?>
  </td>
  </td>
  <td colspan="<?php echo ($friendly?"6":"4"); ?>" align="center">
   <font face=\"".$phpgw_info["theme"][font]."\" size="+1"><? echo $year; ?></font>
  </td>
  <?php
    if (! $friendly)
       echo "<td align=\"right\"><A HREF=\"" . $phpgw->link("year.php","year=" . ($year + 1)) . "\">&gt;&gt;</A>";
  ?>
  </td>
 </tr>
 <tr valign="top">
<?php
  if(!$friendly) $link = "day.php"; else $link = "";
  for($i=1;$i<13;$i++) {
    echo "<td valign=\"top\">";
    if(!$friendly)
      echo $phpgw->calendar->pretty_small_calendar($i,$i,$year,"day.php");
//      echo $phpgw->calendar->display_small_month($i,$year,False,"day.php");
    else
      echo $phpgw->calendar->pretty_small_calendar($i,$i,$year);
//      echo $phpgw->calendar->display_small_month($i,$year,False);
    echo "</td>";
    if($i==6) echo "</tr><tr valign=\"top\">";
  }
?>
 </tr>
</table>
</center>

<?php
  if (! $friendly) {
     echo "&nbsp;<A HREF=\"" . $phpgw->link("year.php","friendly=1&"
	. "&year=$year") . "\"TARGET=\"cal_printer_friendly\" onMouseOver=\"window."
	. "status = '" . lang("Generate printer-friendly version") . "'\">["
	. lang("Printer Friendly") . "]</A>";
  }
  $phpgw->common->phpgw_footer();
?>
