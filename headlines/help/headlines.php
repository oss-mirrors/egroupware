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

	$phpgw_flags = Array(
		'currentapp'	=> 'manual',
		'enable_utilities_class'	=> True
	);
	$phpgw_info['flags'] = $phpgw_flags;
	include('../../header.inc.php');
?>
<img src="<?php echo $phpgw->common->image('headlines','navbar.gif'); ?>" border=0> 
<font face="<?php echo $phpgw_info['theme']['font']; ?>" size="2">
<p>
<p>
This area is for catching up on the lates online sites, news headlines.
<p>
A new user is usually set with an empty default, so that the user can 
choose their own personal favorites from the list available in the 
profiles setting option in preferences.
<p>
<font size="1">
<img src="<?php echo $phpgw->common->image('headlines','headlinesscreen.gif'); ?>"  border=0>
<br>ScreenShot
<?php $phpgw->common->phpgw_footer(); ?>
