<?php
  /**************************************************************************\
  * phpGroupWare - Calendar                                                  *
  * http://www.phpgroupware.org                                              *
  * Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
  *          http://www.radix.net/~cknudsen                                  *
  * Modified by Mark Peters <skeeter@phpgroupware.org>                       *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  class calendar_time {
    var $year;
    var $month;
    var $mday;
    var $hour;
    var $min;
    var $sec;
    var $alarm = 0;
  }

  class calendar_item {
//New ICal Support
    var $id = 0;
    var $public = 0;
    var $category = "none";
    var $title = "Unnamed Event";
    var $description = "Unnamed Event";
    var $alarm = 0;
    var $start;
    var $end;
    var $recur_type = 0;
    var $recur_interval = 0;
    var $recur_enddate;
    var $recur_data = 0;

// Legacy Support
    var $owner;
    var $name = "Unnamed Event";
    var $datetime = 0;
    var $day = 0;
    var $month = 0;
    var $year = 0;
    var $hour = 0;
    var $minute = 0;
    var $ampm = "";
    var $mdatetime = 0;
    var $mod_day = 0;
    var $mod_month = 0;
    var $mod_year = 0;
    var $mod_hour = 0;
    var $mod_minute = 0;
    var $mod_second = 0;
    var $mod_ampm = "";
    var $edatetime = 0;
    var $end_day = 0;
    var $end_month = 0;
    var $end_year = 0;
    var $end_hour = 0;
    var $end_minute = 0;
    var $end_second = 0;
    var $end_ampm = "";
    var $priority = 0;
    var $access = "private";
    var $groups = array();
    var $participants = array();
    var $status = array();
    var $rpt_type = "none";
    var $rpt_end_use = 0;
    var $rpt_end = 0;
    var $rpt_end_day = 0;
    var $rpt_end_month = 0;
    var $rpt_end_year = 0;
    var $rpt_days = "nnnnnnn";
    var $rpt_sun = 0;
    var $rpt_mon = 0;
    var $rpt_tue = 0;
    var $rpt_wed = 0;
    var $rpt_thu = 0;
    var $rpt_fri = 0;
    var $rpt_sat = 0;
    var $rpt_freq = 0;

    function set($var,$val="") {
      $this->$var = $val;
    }
  }

?>
