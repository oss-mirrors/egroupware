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

	$d1 = strtolower(substr(PHPGW_APP_INC,0,3));
	if($d1 == 'htt' || $d1 == 'ftp' )
	{
		echo "Failed attempt to break in via an old Security Hole!<br>\n";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
	unset($d1);

	$tmp_app_inc = $GLOBALS['phpgw']->common->get_inc_dir('comic');

	$GLOBALS['phpgw']->db->query('SELECT * FROM phpgw_comic '
		. "WHERE comic_owner='"
		. $GLOBALS['phpgw_info']['user']['account_id'] . "'");

	if($GLOBALS['phpgw']->db->num_rows())
	{
		$GLOBALS['phpgw']->db->next_record();

		$data_id      = $GLOBALS['phpgw']->db->f('comic_frontpage');
		$scale        = $GLOBALS['phpgw']->db->f('comic_fpscale');
		$censor_level = $GLOBALS['phpgw']->db->f('comic_censorlvl');

		if($data_id != -1)
		{
			$app_id = $GLOBALS['phpgw']->applications->name2id('comic');
			$GLOBALS['portal_order'][] = $app_id;
			$GLOBALS['phpgw']->portalbox->set_params(array('app_id'	=> $app_id,
														'title'	=> lang('comic')));

			include($tmp_app_inc . '/functions.inc.php');
			$GLOBALS['phpgw']->portalbox->draw(comic_display_frontpage($data_id, $scale, $censor_level));
		}
	}
?>
