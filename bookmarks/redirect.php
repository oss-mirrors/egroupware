<?php
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'bookmarks',
		'nonavbar'   => True,
		'noheader'   => True,
		'nofooter'   => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->db->query("select bm_info, bm_url from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$url = $GLOBALS['phpgw']->db->f('bm_url');

	$ts = explode(",",$GLOBALS['phpgw']->db->f("bm_info"));
	$newtimestamp = sprintf("%s,%s,%s",$ts[0],time(),$ts[2]);

	$GLOBALS['phpgw']->db->query("update phpgw_bookmarks set bm_info='$newtimestamp', bm_visits=bm_visits+1 "
		. "where bm_id='$bm_id'");

	if (isset($showheader))
	{
		$GLOBALS['phpgw']->template->set_file(array(
			'header' => 'redirect_frames_header.tpl'
		));
		$GLOBALS['phpgw']->template->set_var('img_root',$GLOBALS['phpgw']->common->get_image_path('phpgwapi'));
		$GLOBALS['phpgw']->template->set_var('message',lang('You are viewing this site outside of phpGroupWare') . '<br>'
			. lang('close this window to return'));
		$GLOBALS['phpgw']->template->pfp('out','header');
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'redirect' => 'redirect_frames.tpl'
	));
	$GLOBALS['phpgw']->template->set_var('redirect_url',$GLOBALS['phpgw']->link('/bookmarks/redirect.php','showheader=True'));
	$GLOBALS['phpgw']->template->set_var('bm_site_url',$url);
	$GLOBALS['phpgw']->template->pfp('out','redirect');

	$GLOBALS['phpgw']->common->phpgw_exit();
