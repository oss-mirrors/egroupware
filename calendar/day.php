<?php
  /**************************************************************************\
  * phpGroupWare - Calendar                                                  *
  * http://www.phpgroupware.org                                              *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "calendar", "enable_nextmatchs_class" => True);

  if (isset($friendly) && $friendly){
     $phpgw_info["flags"]["noheader"] = True;
     $phpgw_info["flags"]["nonavbar"] = True;
     $phpgw_info["flags"]["nocalendarheader"] = True;
  } else {
     $friendly = 0;
  }

  include("../header.inc.php");

  if(isset($friendly) && $friendly) {
    if(!isset($phpgw_info["user"]["preferences"]["calendar"]["weekdaystarts"]))
      $phpgw_info["user"]["preferences"]["calendar"]["weekdaystarts"] = "Sunday";

    if (isset($date) && strlen($date) > 0) {
       $thisyear  = substr($date, 0, 4);
       $thismonth = substr($date, 4, 2);
       $thisday   = substr($date, 6, 2);
    } else {
       if (!isset($day) || !$day)
          $thisday = $phpgw->calendar->today["day"];
       else
          $thisday = $day;
       if (!isset($month) || !$month)
          $thismonth = $phpgw->calendar->today["month"];
       else
          $thismonth = $month;
       if (!isset($year) || !$year)
          $thisyear = $phpgw->calendar->today["year"];
       else
          $thisyear = $year;
    }
  }

  if(!isset($owner)) { $owner = 0; } 
  unset($owner);

  if(!isset($owner) || !$owner) {
    $id = $phpgw_info["user"]["userid"];
    $fn = $phpgw_info["user"]["firstname"];
    $ln = $phpgw_info["user"]["lastname"];
    $owner = 0;
 } else {
    $phpgw->db->query("SELECT account_lid,account_firstname,account_lastname FROM accounts WHERE account_id=$owner");
    $phpgw->db->next_record();
    $id = $phpgw->db->f("account_lid");
    $fn = $phpgw->db->f("account_firstname");
    $ln = $phpgw->db->f("account_lastname");
  }

  $view = "day";

  $now	= $phpgw->calendar->splitdate(mktime (0, 0, 0, $thismonth, $thisday, $thisyear) - ((60 * 60) * $phpgw_info["user"]["preferences"]["common"]["tz_offset"]));

  $phpgw->template->set_file(array("day_t" => "day.tpl"));

  //$phpgw->template->set_block("day_t");

  if ($friendly) {
    $phpgw->template->set_var("printer_friendly","<body bgcolor=\"".$phpgw_info["theme"]["bg_color"]."\">");
  } else {
    $phpgw->template->set_var("printer_friendly","");
  }

  $phpgw->template->set_var("bg_text",$phpgw_info["theme"]["bg_text"]);

  $m = mktime(2,0,0,$thismonth,1,$thisyear);
  $phpgw->template->set_var("date",lang(date("F",$m))." ".$thisday.", ".$thisyear);
  $phpgw->template->set_var("username",$phpgw->common->display_fullname($id,$fn,$ln));
  $phpgw->template->set_var("daily_events",$phpgw->calendar->print_day_at_a_glance($now,$owner));
  $phpgw->template->set_var("small_calendar",$phpgw->calendar->pretty_small_calendar($now["day"],$now["month"],$now["year"],"day.php"));

  if (!$friendly) {
    $param = "year=".$now["year"]."&month=".$now["month"]."&day=".$now["day"]."&friendly=1&filter=".$filter;
    $phpgw->template->set_var("print","<a href=\"".$phpgw->link($PHP_SELF,$param)."\" TARGET=\"cal_printer_friendly\" onMouseOver=\"window.status = '".lang("Generate printer-friendly version")."'\">[".lang("Printer Friendly")."]</A>");
    $phpgw->template->parse("out","day_t");
    $phpgw->template->pparse("out","day_t");
    $phpgw->common->phpgw_footer();
  } else {
    $phpgw->template->set_var("print","");
    $phpgw->template->parse("out","day_t");
    $phpgw->template->pparse("out","day_t");
  }
?>
