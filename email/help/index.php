<?php
  /**************************************************************************\
  * eGroupWare - User manual                                                 *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$phpgw_flags = Array(
		'currentapp'	=> 'manual'
	);
	$GLOBALS['phpgw_info']['flags'] = $phpgw_flags;
	include('../../header.inc.php');
	$appname = 'email';
	include(PHPGW_SERVER_ROOT.'/'.$appname.'/setup/setup.inc.php');
?>
<img src="<?php echo $phpgw->common->image($appname,'navbar.gif'); ?>" border="0"><p/>
<font face="<?php echo $GLOBALS['phpgw_info']['theme']['font']; ?>" size="2">
Version: <b><?php echo $GLOBALS['phpgw_info']['server']['versions']['phpgwapi']; ?></b><p/>
</font>
<?php $phpgw->common->phpgw_footer(); ?>
