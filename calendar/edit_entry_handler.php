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

  $phpgw_info["flags"] = array("currentapp" => "calendar", "noheader" => True, "nonavbar" => True, "enable_calendar_class" => True, "enable_nextmatchs_class" => True);
  include("../header.inc.php");

  $cal_info = new calendar_item;

  if(!isset($readsess)) {
    for(reset($HTTP_POST_VARS);$key=key($HTTP_POST_VARS);next($HTTP_POST_VARS)) {
      $data = $HTTP_POST_VARS[$key];
      $cal_info->set($key,$data);
    }
    $phpgw->common->appsession($cal_info);
    $overlapping_events = $phpgw->calendar->overlap($cal_info->month,$cal_info->day,$cal_info->year,$cal_info->hour,$cal_info->minute,$cal_info->ampm,$cal_info->duration,$cal_info->participants,$cal_info->id);
  } else {
    $cal_info = $phpgw->common->appsession();
  }
  if($overlapping_events) {
    $phpgw->common->phpgw_header();
    $phpgw->common->navbar();
    $phpgw->template->set_file(array("overlap" => "overlap.tpl",
				   "form_button"     => "form_button_script.tpl"));

    $phpgw->template->set_block("overlap","form_button");

    $phpgw->template->set_var("color",$phpgw_info["theme"]["bg_text"]);

    $time = $phpgw->calendar->fixtime($cal_info->hour,$cal_info->minute,$cal_info->ampm);
    $calendar_overlaps = $phpgw->calendar->getevent($overlapping_events);

    $overlap = "";
    for($i=0;$i<count($calendar_overlaps);$i++) {
      $cal_over = $calendar_overlaps[$i];
      if($cal_over) {
	$overlap .= "<li>";
	if(strtoupper($cal_over->access) == "PRIVATE")
	  $overlap .= "(PRIVATE)";
	else
	  $overlap .= $phpgw->calendar->link_to_entry($cal_over->id,"circle.gif",$cal_over->name);
	$disp_start_time = $phpgw->calendar->build_time_for_display($phpgw->calendar->fixtime($cal_over->hour,$cal_over->minute,$cal_over->ampm));
	$disp_stop_time = $phpgw->calendar->build_time_for_display($phpgw->calendar->addduration($cal_over->hour,$cal_over->minute,$cal_over->ampm,$cal_over->duration));
	$overlap .= " (".$disp_start_time." - ".$disp_stop_time.")<br>";
      }
    }
    if(strlen($overlap)) {
      $phpgw->template->set_var("overlap_text",lang("Your suggested time of <B> x - x </B> conflicts with the following existing calendar entries:",$phpgw->calendar->build_time_for_display($time),$phpgw->calendar->build_time_for_display($phpgw->calendar->addduration($cal_info->hour,$cal_info->minute,$cal_info->ampm,$cal_info->duration))));
      $phpgw->template->set_var("overlap_list",$overlap);
    } else {
      $phpgw->template->set_var("overlap_text","");
      $phpgw->template->set_var("overlap_list","");
    }

    $phpgw->template->set_var("action_url_button",$phpgw->link("","readsess=".$cal_info->id));
    $phpgw->template->set_var("action_text_button",lang("Ignore Conflict"));
    $phpgw->template->set_var("action_confirm_button","");
    $phpgw->template->parse("resubmit_button","form_button");

    $phpgw->template->set_var("action_url_button",$phpgw->link("edit_entry.php","readsess=".$cal_info->id));
    $phpgw->template->set_var("action_text_button",lang("Re-Edit Event"));
    $phpgw->template->set_var("action_confirm_button","");
    $phpgw->template->parse("reedit_button","form_button");

    $phpgw->template->pparse("out","overlap");
  } else {
    $phpgw->calendar->add($cal_info,$cal_info->id);
    Header("Location: ".$phpgw->link("index.php","year=$year&month=$month&cd=14"));
  }
  $phpgw->common->phpgw_footer();
?>

