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

	$phpgw_info["flags"]["currentapp"] = "forum";
	if($action)
	{
		$phpgw_info["flags"]["noheader"] = True;
		$phpgw_info["flags"]["nonavbar"] = True;
	}
	include("../../header.inc.php");

	//setting up template file
	$phpgw->template->set_file('CATEGORY','admin.category.tpl');

	$phpgw->template->set_var(array(
	'FORUM_ADMIN' 	=> lang("Forums") . " " . lang("Admin"),
	'TABLEBG'	=> $phpgw_info["theme"]["th_bg"],
	//TRY TO FIND A PERFECT CHOICE
	'THBG'		=>  $phpgw_info["theme"]["bg09"],
	//'TRBG'		=> $phpgw_info["theme"]["row_off"],

	'CAT_LINK'	=> $phpgw->link("/forum/admin/category.php"),
	'FOR_LINK'	=> $phpgw->link("/forum/admin/forum.php"),
	'MAIN_LINK'	=> $phpgw->link("/forum/index.php"),
	'ADM_LINK'	=> $phpgw->link("/forum/admin/index.php"),
	'LANG_ADM_MAIN'	=> lang("Return to Admin"),
	'LANG_CAT'	=> lang("New Category"),
	'LANG_FOR'	=> lang("New Forum"),
	'LANG_MAIN'       => lang("Return to Forums"),
	'LANG_FORUM'	=> lang("Forum Name"),
	'LANG_FORUM_DESC'	=> lang("Forum Description"),
	'LANG_CAT_NAME'	=> lang("Category Name"),
	'LANG_CAT_DESC' => lang("Category Description"),
	'BELONG_TO'	=> lang("Belongs to Category"),
	'ACTION'	=> 'addforum',
	'ACTION_LINK'	=> $phpgw->link("/forum/admin/category.php")
	));


	if($act == "edit")
	{
		$phpgw->db->query("select * from phpgw_forum_categories where id=$cat_id");
		$phpgw->db->next_record();
		$catname = $phpgw->db->f("name");
		$catdescr = $phpgw->db->f("descr");
		$cat_id = $phpgw->db->f("id");

		$phpgw->template->set_var(array(
		'BUTTONLANG'	=> lang("Update Category"),
		'LANG_ADD_CAT' 	=> lang("Edit Category"),
		'CAT_NAME'	=> $phpgw->db->f("name"),
		'CAT_DESC'	=> $phpgw->db->f("descr"),
		'CAT_ID'	=> $phpgw->db->f("id"),
		'ACTIONTYPE'	=> 'updcat'
		));

	}
	//Need to set up some var that different for the edit act and add act
	else
	{

		$phpgw->template->set_var(array(
		'BUTTONLANG' 	=> lang("Add Category"),
		'LANG_ADD_CAT' 	=> lang("Edit Category"),
		'ACTIONTYPE' 	=> 'addcat'
		));

	}



	if($action)
	{
		if($action == "addcat")
		{
			$phpgw->db->query("insert into phpgw_forum_categories (name,descr) values ('$catname','$catdescr')");
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
			$phpgw->common->phpgw_exit();
		}
		elseif ($action == "updcat" && $cat_id)
		{
			$phpgw->db->query("update phpgw_forum_categories set name='$catname',descr='$catdescr' where id = $cat_id");
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
		}
		else
		{
			echo "This should not happened";
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
		}
		$phpgw->common->phpgw_exit();



	}	// end if($action)


	$phpgw->template->pfp('Out','CATEGORY');
	$phpgw->common->phpgw_footer();
?>
