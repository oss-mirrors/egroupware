<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $ Id $ */
{
  echo "<p>\n";
  $imgfile = $phpgw->common->get_image_dir("email")."/" . $appname .".gif";
  if (file_exists($imgfile)) {
    $imgpath = $phpgw->common->get_image_path("email")."/" . $appname .".gif";
  } else {
    $imgfile = $phpgw->common->get_image_dir("email")."/navbar.gif";
    if (file_exists($imgfile)) {
      $imgpath = $phpgw->common->get_image_path("email")."/navbar.gif";
    } else {
      $imgpath = "";
    }
  }
  section_start("E-Mail",$imgpath);

  $pg = $phpgw->link($phpgw_info["server"]["webserver_url"]."/email/preferences.php");
  echo "<A href=".$pg.">" . lang("E-Mail preferences") . "</A>";

  section_end(); 
}
?>
