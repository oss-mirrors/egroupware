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

	$phpgw_info['flags'] = array(
		'currentapp' => 'bookmarks',
		'nonavbar'   => True,
		'noheader'   => True,
		'nofooter'   => True
	);
	include('../header.inc.php');

	$phpgw->db->query("select bm_info, bm_url from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
	$phpgw->db->next_record();
	$url = $phpgw->db->f('bm_url');

	$ts = explode(",",$phpgw->db->f("bm_info"));
	$newtimestamp = sprintf("%s,%s,%s",$ts[0],time(),$ts[2]);

	$phpgw->db->query("update phpgw_bookmarks set bm_info='$newtimestamp', bm_visits=bm_visits+1 "
		. "where bm_id='$bm_id'");

	if (isset($showheader))
	{
		$phpgw->template->set_file(array(
			'header' => 'redirect_frames_header.tpl'
		));
		$phpgw->template->set_var('img_root',$phpgw->common->get_image_path('phpgwapi'));
		$phpgw->template->set_var('message',lang('You are viewing this site outside of phpGroupWare') . '<br>'
			. lang('close this window to return'));
		$phpgw->template->pfp('out','header');
		$phpgw->common->phpgw_exit();
	}

	$phpgw->template->set_file(array(
		'redirect' => 'redirect_frames.tpl'
	));
	$phpgw->template->set_var('redirect_url',$phpgw->link('/bookmarks/redirect.php','showheader=True'));
	$phpgw->template->set_var('bm_site_url',$url);
	$phpgw->template->pfp('out','redirect');

	$phpgw->common->phpgw_exit();
