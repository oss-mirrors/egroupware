<?php
	/**************************************************************************\
	* phpGroupWare - Forums                                                    *
	* http://www.phpgroupware.org                                              *
	* Written by Jani Hirvinen <jpkh@shadownet.com>                            *
	* -------------------------------------------                              *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/


	$phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class" => True);
	if($confirm || ! $cat_id)
	{
		$phpgw_info["flags"]["noheader"] = True;
		$phpgw_info["flags"]["nonavbar"] = True;
	}
	include("../../header.inc.php");

	$phpgw->template->set_file('DELETECATEGORY', 'admin.deletecategory.tpl');

	if (($cat_id) && (! $confirm))
	{

		$phpgw->template->set_var(array(
		'ARE_U_SURE' 	=> lang("Are you sure you want to delete this category?"),
		'ALL_DEL'	=> lang("All forums, user posts, and topics in this category will be lost!"),
		'NO'		=> lang("No"),
		'YES'		=> lang("Yes"),
		'NO_LINK'	=> $phpgw->link("/forum/admin/index.php"),
		'YES_LINK'	=> $phpgw->link("/forum/admin/deletecategory.php","cat_id=$cat_id&confirm=true")
		));

		$phpgw->template->pfp('Out','DELETECATEGORY');
		$phpgw->common->phpgw_footer();

	}

	if (($cat_id) && ($confirm))
	{

		//Delete all the info related to this category
		$phpgw->db->query("delete from phpgw_forum_threads where cat_id=$cat_id");
		$phpgw->db->query("delete from phpgw_forum_body where cat_id=$cat_id");
		$phpgw->db->query("delete from phpgw_forum_forums where cat_id=$cat_id");
		$phpgw->db->query("delete from phpgw_forum_categories where id=$cat_id");

		Header("Location: " . $phpgw->link("/forum/admin/index.php"));
	}



?>
