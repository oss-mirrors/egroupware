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

	$phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class" => True);
	if($action)
	{
		$phpgw_info["flags"]["noheader"] = True;
		$phpgw_info["flags"]["nonavbar"] = True;
	}
	include("../../header.inc.php");

	// setting up the template

	$phpgw->template->set_file('FORUM','admin.forum.tpl');

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
	'LANG_MAIN' 	=> lang("Return to Forums"),
	'LANG_FORUM'	=> lang("Forum Name"),
	'LANG_FORUM_DESC'	=> lang("Forum Description"),

	'BELONG_TO'	=> lang("Belongs to Category"),
	'ACTION'	=> 'addforum'
	));


	if($act == "edit")
	{

		$phpgw->db->query("select * from phpgw_forum_forums where id=$for_id",__LINE__,__FILE__);

		$phpgw->db->next_record();
		$forname = $phpgw->db->f("name");
		$fordescr = $phpgw->db->f("descr");
		$cat_id = $phpgw->db->f("cat_id");

		// for the drop down category
		$phpgw->db->query("select * from phpgw_forum_categories",__LINE__,__FILE__);
		while($phpgw->db->next_record())
		{
			if($catname == $phpgw->db->f("name"))
			{
				$phpgw->template->set_var(
				'SELECTED', "<option selected value=\"" . $phpgw->db->f("id") . "\">". $phpgw->db->f("name")."</option>");
			}
			if($catname != $phpgw->db->f("name"))
			{
				$phpgw->template->set_var(
				'SELECTED', "<option value=\"" . $phpgw->db->f("id") . "\">". $phpgw->db->f("name")."</option>");
			}
			$phpgw->template->parse('DROP_DOWN','SELECTED',true);

		}		//end while

		if ($cat_id > 0)
		{
			$phpgw->db->query("select * from phpgw_forum_categories where id=$cat_id",__LINE__,__FILE__);
			$phpgw->db->next_record();

			$catname = $phpgw->db->f("name");
			$phpgw->template->set_var(array(
			'FORUM_NAME'	=> $forname,
			'FOR_DESC'	=> $fordescr,
			'FORID'		=> $for_id,
			'BUTTONTEXT'	=> lang("Update Forum"),
			'ACTION'	=> 'updforum',
			'LANG_ADD_FORUM' => lang("Update Forum"),
			'ACTION_LINK'	=> $phpgw->link("/forum/admin/forum.php"),
			'BUTTONLANG'	=> lang("Update Forum")
			));


		}		//end of if ($cat_id > 0)
		else //Not yet check. Anyone have test this case please let me know, r0kawa
		{
			$catname = lang("No Category");
			$extraselect = "<option value=\"" . $cat_id . "\">" . $catname ."</option>";
			$phpgw->template->set_var(array(
			'CATID' 	=> $cat_id,
			'CATNAME'	=> $catname,
			'FORID'	=> $for_id,
			'BUTTONTEXT'	=> lang("Update Forum"),
			'ACTION'	=> 'updforum'
			));
		}




	}	//End act == edit

	if(!$act)
	{
		$phpgw->template->set_var(array(
		'BUTTONLANG'	=> lang("Add Forum"),
		'ACTION'	=> 'addforum',
		'LANG_ADD_FORUM' => lang("Add Forum"),
		'ACTION_LINK'	=> $phpgw->link("/forum/admin/forum.php")
		));


		$phpgw->db->query("select * from phpgw_forum_categories",__LINE__,__FILE__);
		while($phpgw->db->next_record())
		{
			$phpgw->template->set_var(
			'NOT_SEL' , "<option value=\"" . $phpgw->db->f("id") . "\">". $phpgw->db->f("name")."</option>");
			$phpgw->template->parse('DROP_DOWN','NOT_SEL',true);
		}

	}	//end if(!act)



	// Better using switch function << todo
	if($action)
	{
		if($action == "addforum")
		{
			$phpgw->db->query("insert into phpgw_forum_forums (name,descr,cat_id,perm,groups) values ('$forname','$fordescr',$goestocat,'','')",__LINE__,__FILE__);
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
			$phpgw->common->phpgw_exit();
		}
		elseif ($action == "updforum" && $for_id)
		{
			$phpgw->db->query("update phpgw_forum_forums set name='$forname',descr='$fordescr',cat_id=$goestocat where id=$for_id ",__LINE__,__FILE__);
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
			$phpgw->common->phpgw_exit();
		}
		else
		{
			Header("Location: " . $phpgw->link("/forum/admin/index.php"));
			$phpgw->common->phpgw_exit();
		}
	}


	$phpgw->template->parse('Out','FORUM');
	$phpgw->template->p('Out');
	$phpgw->common->phpgw_footer();
?>
