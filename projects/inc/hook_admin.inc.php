<?php
  /**************************************************************************\
  * phpGroupWare - projects administration                                   *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

    {
    echo "<p>\n";

    $imgfile=$phpgw->common->get_image_dir('projects') . '/navbar.gif';
    if(file_exists($imgfile)) {
	$imgpath=$phpgw->common->get_image_path('projects') . '/navbar.gif';
    }
    else {
    $imgpath='';
    }
    section_start('projects',$imgpath);
    $pg = $phpgw->link('/projects/admin.php');
    echo '<a href=' . $pg . '>' . lang('Project administration') . '</a><br>';

    section_end();
    }
?>