<?php
  /**************************************************************************\
                                          *
  \**************************************************************************/
  /* $Id$ */

    {
    echo "<p>\n";

    $imgfile=$phpgw->common->get_image_dir('forum') . '/navbar.gif';
    if(file_exists($imgfile)) {
	$imgpath=$phpgw->common->get_image_path('forum') . '/navbar.gif';
    }
    else {
    $imgpath='';
    }
    section_start('Forum',$imgpath);
    $pg = $phpgw->link('/forum/admin/index.php');
    echo '<a href=' . $pg . '>' . lang('Forum Administration') . '</a><br>';

    section_end();
    }
?>