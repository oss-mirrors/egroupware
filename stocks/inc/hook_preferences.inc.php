<?php
  /**************************************************************************\
  * phpGroupWare - Stock Quotes                                              *
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
	$imgfile = $phpgw->common->get_image_dir('stocks') . '/' . $appname . '.gif';
	if (file_exists($imgfile))
	{
	    $imgpath = $phpgw->common->get_image_path('stocks') . '/' . $appname . '.gif';
	}
	else
	{
	    $imgfile = $phpgw->common->get_image_dir('stocks') . '/navbar.gif';
	    if (file_exists($imgfile))
	    {
    		$imgpath = $phpgw->common->get_image_path('stocks') . '/navbar.gif';
	    }
	    else
	    {
    		$imgpath = '';
	    }
	}

	section_start('Stock Quotes',$imgpath);

	section_item($phpgw->link('/stocks/preferences.php'),
		lang('Select displayed stocks'));

	section_end();
    }
?>

