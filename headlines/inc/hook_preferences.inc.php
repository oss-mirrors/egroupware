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
  /* $Id$ */
{
  echo "<p>";
  $imgfile = $phpgw->common->get_image_dir($appname)."/".$appname.".gif";
  if (file_exists($imgfile)) {
    $imgpath = $phpgw->common->get_image_path($appname)."/".$appname.".gif";
  } else {
    $imgfile = $phpgw->common->get_image_dir($appname)."/navbar.gif";
    if (file_exists($imgfile)) {
      $imgpath = $phpgw->common->get_image_path($appname)."/navbar.gif";
    } else {
      $imgpath = "";
    }
  }
  section_start(ucfirst($appname),$imgpath);

  echo '<a href="' . $phpgw->link('/headlines/preferences.php') . '">' . lang('Select Headlines to Display') . '</a>';
  echo '<br><a href="' . $phpgw->link('/headlines/preferences_layout.php') . '">' . lang('Select layout') . '</a>';

  section_end(); 
}
?>
