<?php
	/*****************************************************************************\
	* phpGroupWare - Forums                                                       *
	* http://www.phpgroupware.org                                                 *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                               *
	* -------------------------------------------                                 *
	*  This program is free software; you	can redistribute it and/or modify it   *
	*  under the terms of	the GNU	General	Public License as published by the  *
	*  Free Software Foundation; either version 2	of the License,	or (at your *
	*  option) any later version.                                                 *
	\*****************************************************************************/

	/* $Id$ */

	$phpgw_info["flags"] = array("currentapp" => "forum",	"enable_nextmatchs_class" => True);
	include("../../header.inc.php");


	$phpgw->template->set_file('INDEX','admin.index.tpl');
	//Set block for	the category and forum
	$phpgw->template->set_block('INDEX','ForumBlock','ForumB');
	$phpgw->template->set_block('INDEX','CatBlock','CatB');

	$phpgw->template->set_var(array(
		'FORUM_ADMIN'	=> lang("Forums") . " "	. lang("Admin"),
		'TB_BG'	=> $phpgw_info["theme"]["table_bg"],
		//TRY TO FIND A	PERFECT	CHOICE
		// $phpgw_info["theme"]["bg_color"]
	
		'TR_BG'		=> $phpgw_info["theme"]["bg_color"],
		IMG_URL_PREFIX          => PHPGW_IMAGES . "/",
		'CAT_LINK'	=> $phpgw->link("/forum/admin/category.php"),
		'FOR_LINK'	=> $phpgw->link("/forum/admin/forum.php"),
		'MAIN_LINK'	=> $phpgw->link("/forum/index.php"),
		'LANG_CAT'	=> lang("New Category"),
		'LANG_FOR'	=> lang("New Forum"),
		'LANG_MAIN'	=> lang("Return to Forums"),
		'LANG_CURRENT_SUBFORUM'	=> lang("Current Categories and Sub Forums"),
		'LANG_CATEGORY'	=> lang("Category"),
		'LANG_SUBCAT'	=> lang("Sub Category"),
		'LANG_ACTION'	=> lang("Action")
	));

	$f_tree = array();
	$phpgw->db->query("select * from phpgw_forum_categories");
	while($phpgw->db->next_record())
	{
		$f_tree[$phpgw->db->f("id")] = array("name"=>$phpgw->db->f("name"),	"descr"=>$phpgw->db->f("descr"), "forums"=>array());
	}
	$phpgw->db->query("select * from phpgw_forum_forums");
	while($phpgw->db->next_record())
	{
		$f_tree[$phpgw->db->f("cat_id")]["forums"][$phpgw->db->f("id")] = array("name"=>$phpgw->db->f("name"), "descr"=>$phpgw->db->f("descr"));
	}
	ksort($f_tree);

	for(reset($f_tree);$id=key($f_tree);next($f_tree))
	{

		if($id > 0)
		{


			$phpgw->template->set_var(array(
			'BG6'		=> $phpgw_info["theme"]["bg03"],
			'CAT_NAME'	=> $f_tree[$id]["name"],
			'CAT_DESC'	=> $f_tree[$id]["descr"],
			'EDIT_LINK'	=> $phpgw->link("/forum/admin/category.php","act=edit&cat_id=$id"),
			'DEL_LINK'	=> $phpgw->link("/forum/admin/deletecategory.php", "cat_id=$id"),
			'LANG_EDIT'	=> lang("Edit"),
			'LANG_DEL'	=> lang("Delete")
			));


		}
		else
		{
			// Not sure changing to	what
			echo "<h1>running this?</h1>";
			echo "<tr>\n";
			echo " <td colspan=3 align=right valign=top>\n";
			echo "<table border=0 width=100%>\n";
		}

		$tr_color = $phpgw_info["theme"]["row_off"];
		//Cleaning the ForumB variable because the blocks use more than	once
		$phpgw->template->set_var('ForumB','');

		for(reset($f_tree[$id]["forums"]); $fid=key($f_tree[$id]["forums"]); next($f_tree[$id]["forums"]))
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

			$phpgw->template->set_var(array(
			'TD_BG'		=> 'ffffff',
			'TR_BG'		=> $tr_color,
			'SUBCAT_NAME'	=> $f_tree[$id]["forums"][$fid]["name"],
			'SUBCAT_DESC'	=> $f_tree[$id]["forums"][$fid]["descr"],
			'SUBEDIT_LINK'	=> $phpgw->link("/forum/admin/forum.php","act=edit&for_id=$fid"),
			'SUBDEL_LINK'	=> $phpgw->link("/forum/admin/deleteforum.php",	"for_id=$fid"),
			'LANG_EDIT'	=> lang("Edit"),
			'LANG_DEL'	=> lang("Delete"),
			'LANG_FORUM'	=> lang("Forum")
			));

			//Parsing the inner block
			$phpgw->template->fp('ForumB','ForumBlock',true);
		}
		// Parsing the outer block
		$phpgw->template->set_var(array(
		'TD_BG'		=> 'ffffff',
		'TR_BG'		=> $tr_color
		));

		$phpgw->template->fp('CatB','CatBlock',true);
	}

	$phpgw->template->pfp('Out','INDEX');
	$phpgw->common->phpgw_footer();
?>