<?php
	/**************************************************************************\
	* phpGroupWare - Forums						     							*
	* http://www.phpgroupware.org						     					*
	* Written by Jani Hirvinen <jpkh@shadownet.com>			     				*
	* -------------------------------------------				     			*
	*  This program is free software; you	can redistribute it and/or modify it*
	*  under the terms of	the GNU	General	Public License as published by the  *
	*  Free Software Foundation; either version 2	of the License,	or (at your *
	*  option) any later version.						     					*
	\**************************************************************************/

	$phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class"	=> True);
	include("../header.inc.php");

	$phpgw->template->set_file('FORUM' ,'forums.body.tpl');

	$phpgw->template->set_block('FORUM','ForumList','ForumL');

	$phpgw->db->query("select * from phpgw_forum_categories where id = $cat");
	$phpgw->db->next_record();

	$phpgw->template->set_var(array(
	BGROUND		 => $phpgw_info["theme"]["th_bg"],
	IMG_URL_PREFIX          => PHPGW_IMAGES . "/",
	CATEGORY	 => $phpgw->db->f("name"),
	LANG_MAIN	 => lang("Forum"),
	MAIN_LINK	 => $phpgw->link("/forum/index.php")
	));

	$phpgw->db->query("select * from phpgw_forum_forums where	cat_id = $cat");

	while($phpgw->db->next_record())
	{
		$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
		$phpgw->template->set_var(array(
		COLOR		=> $tr_color,
		NAME		=> $phpgw->db->f("name"),
		DESC		=> $phpgw->db->f("descr"),
		THREADS_LINK	=> $phpgw->link("/forum/threads.php" , "cat=" .	$cat . "&for=" . $phpgw->db->f("id"))
		));

		$phpgw->template->parse('ForumL','ForumList',true);

	}

	$phpgw->template->parse('Out','FORUM');
	$phpgw->template->p('Out');

	$phpgw->common->phpgw_footer();
?>
