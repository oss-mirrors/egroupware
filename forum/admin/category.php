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

	$GLOBALS['phpgw_info']['flags'] = Array(
		'currentapp' => 'forum'
	);
	if($GLOBALS['HTTP_POST_VARS']['action'])
	{
		$GLOBALS['phpgw_info']['flags']['noheader'] = True;
		$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
	}
	include('../../header.inc.php');

	//setting up template file
	$GLOBALS['phpgw']->template->set_file('CATEGORY','admin.category.tpl');

	$GLOBALS['phpgw']->template->set_var(
		Array(
			'FORUM_ADMIN' 	=> lang('Forums') . ' ' . lang('Admin'),
			'TABLEBG'	=> $GLOBALS['phpgw_info']['theme']['th_bg'],
			//TRY TO FIND A PERFECT CHOICE
			'THBG'		=>  $GLOBALS['phpgw_info']['theme']['bg09'],
			//'TRBG'		=> $GLOBALS['phpgw_info']['theme']['row_off'],

			'CAT_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/category.php'),
			'FOR_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/forum.php'),
			'MAIN_LINK'	=> $GLOBALS['phpgw']->link('/forum/index.php'),
			'ADM_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/index.php'),
			'LANG_ADM_MAIN'	=> lang('Return to Admin'),
			'LANG_CAT'	=> lang('New Category'),
			'LANG_FOR'	=> lang('New Forum'),
			'LANG_MAIN'       => lang('Return to Forums'),
			'LANG_FORUM'	=> lang('Forum Name'),
			'LANG_FORUM_DESC'	=> lang('Forum Description'),
			'LANG_CAT_NAME'	=> lang('Category Name'),
			'LANG_CAT_DESC' => lang('Category Description'),
			'BELONG_TO'	=> lang('Belongs to Category'),
			'ACTION'	=> 'addforum',
			'ACTION_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/category.php')
		)
	);


	if($act == 'edit')
	{
		$GLOBALS['phpgw']->db->query('select * from phpgw_forum_categories where id='.$cat_id,__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();
		$catname = $GLOBALS['phpgw']->db->f('name');
		$catdescr = $GLOBALS['phpgw']->db->f('descr');
		$cat_id = $GLOBALS['phpgw']->db->f('id');

		$GLOBALS['phpgw']->template->set_var(
			Array(
				'BUTTONLANG'	=> lang('Update Category'),
				'LANG_ADD_CAT' 	=> lang('Edit Category'),
				'CAT_NAME'	=> $GLOBALS['phpgw']->db->f('name'),
				'CAT_DESC'	=> $GLOBALS['phpgw']->db->f('descr'),
				'CAT_ID'	=> $GLOBALS['phpgw']->db->f('id'),
				'ACTIONTYPE'	=> 'updcat'
			)
		);

	}
	//Need to set up some var that different for the edit act and add act
	else
	{

		$GLOBALS['phpgw']->template->set_var(
			Array(
				'BUTTONLANG' 	=> lang('Add Category'),
				'LANG_ADD_CAT' 	=> lang('Edit Category'),
				'ACTIONTYPE' 	=> 'addcat'
			)
		);

	}



	if($GLOBALS['HTTP_POST_VARS']['action'])
	{
		if($GLOBALS['HTTP_POST_VARS']['action'] == 'addcat')
		{
			$GLOBALS['phpgw']->db->query("insert into phpgw_forum_categories (name,descr) values ('".$catname."','".$catdescr."')",__LINE__,__FILE__);
			Header('Location: ' . $GLOBALS['phpgw']->link('/forum/admin/index.php'));
		}
		elseif ($action == 'updcat' && $cat_id)
		{
			$GLOBALS['phpgw']->db->query("update phpgw_forum_categories set name='".$catname."',descr='".$catdescr."' where id = ".$cat_id,__LINE__,__FILE__);
			Header('Location: ' . $GLOBALS['phpgw']->link('/forum/admin/index.php'));
		}
		else
		{
			echo 'This should not happened';
			Header("Location: " . $GLOBALS['phpgw']->link('/forum/admin/index.php'));
		}
		$GLOBALS['phpgw']->common->phpgw_exit();

	}	// end if($action)


	$GLOBALS['phpgw']->template->pfp('Out','CATEGORY');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
