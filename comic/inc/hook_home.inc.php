<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */
{

	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$phpgw->common->phpgw_exit();
	} unset($d1);

	$tmp_app_inc = $phpgw->common->get_inc_dir('comic');

	$phpgw->db->query("select * from phpgw_comic "
		."WHERE comic_owner='"
		.$phpgw_info["user"]["account_id"]."'");

	if ($phpgw->db->num_rows())
	{
		$phpgw->db->next_record();

		$data_id      = $phpgw->db->f('comic_frontpage');
		$scale        = $phpgw->db->f('comic_fpscale');
		$censor_level = $phpgw->db->f('comic_censorlvl');

		if ($data_id != -1)
		{
			include($tmp_app_inc . '/functions.inc.php');
			comic_display_frontpage($data_id, $scale, $censor_level);
		}
	}
}
?>
