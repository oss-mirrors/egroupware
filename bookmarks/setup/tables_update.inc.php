<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /**************************************************************************\
  * This file should be generated for you. It should never be edited by hand *
  \**************************************************************************/

  /* $Id$ */

	$test[] = '0.8.1';
	function bookmarks_upgrade0_8_1()
	{
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_bookmarks','bm_owner', array('type' => 'int', 'precision' => 4,'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_bookmarks','bm_category', array('type' => 'int', 'precision' => 4,'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_bookmarks','bm_subcategory', array('type' => 'int', 'precision' => 4,'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_bookmarks','bm_rating', array('type' => 'int', 'precision' => 4,'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_bookmarks','bm_visits', array('type' => 'int', 'precision' => 4,'nullable' => True));

		$GLOBALS['setup_info']['bookmarks']['currentver'] = '0.8.2';
		return $GLOBALS['setup_info']['bookmarks']['currentver'];
	}
