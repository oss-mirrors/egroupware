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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'forum',
		'enable_nextmatchs_class' => True
	);
	include('../../header.inc.php');


	$GLOBALS['phpgw']->template->set_file('INDEX','admin.index.tpl');
	//Set block for	the category and forum
	$GLOBALS['phpgw']->template->set_block('INDEX','ForumBlock','ForumB');
	$GLOBALS['phpgw']->template->set_block('INDEX','CatBlock','CatB');

	$GLOBALS['phpgw']->template->set_var(
		Array(
			'FORUM_ADMIN'	=> lang('Forums') . " "	. lang('Admin'),
			'TB_BG'	=> $GLOBALS['phpgw_info']['theme']['table_bg'],
			//TRY TO FIND A	PERFECT	CHOICE
			// $GLOBALS['phpgw_info']['theme']['bg_color']
	
			'TR_BG'		=> $GLOBALS['phpgw_info']['theme']['bg_color'],
			'CAT_IMG'	=> $GLOBALS['phpgw']->common->image('forum','category'),
			'FORUM_IMG'	=> $GLOBALS['phpgw']->common->image('forum','forum'),
			'CAT_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/category.php'),
			'FOR_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/forum.php'),
			'MAIN_LINK'	=> $GLOBALS['phpgw']->link('/forum/index.php'),
			'LANG_CAT'	=> lang('New Category'),
			'LANG_FOR'	=> lang('New Forum'),
			'LANG_MAIN'	=> lang('Return to Forums'),
			'LANG_CURRENT_SUBFORUM'	=> lang('Current Categories and Sub Forums'),
			'LANG_CATEGORY'	=> lang('Category'),
			'LANG_SUBCAT'	=> lang('Sub Category'),
			'LANG_ACTION'	=> lang('Action')
		)
	);

	$f_tree = array();
	$GLOBALS['phpgw']->db->query('select * from phpgw_forum_categories');
	while($GLOBALS['phpgw']->db->next_record())
	{
		$f_tree[$GLOBALS['phpgw']->db->f('id')] = Array(
			'name'	=> $GLOBALS['phpgw']->db->f('name'),
			'descr'	=> $GLOBALS['phpgw']->db->f('descr'),
			'forums'	=> Array()
		);
	}
	$GLOBALS['phpgw']->db->query('select * from phpgw_forum_forums');
	while($GLOBALS['phpgw']->db->next_record())
	{
		$f_tree[$GLOBALS['phpgw']->db->f('cat_id')]['forums'][$GLOBALS['phpgw']->db->f('id')] = Array(
			'name'	=> $GLOBALS['phpgw']->db->f('name'),
			'descr'	=> $GLOBALS['phpgw']->db->f('descr')
		);
	}
	ksort($f_tree);

	for(reset($f_tree);$id=key($f_tree);next($f_tree))
	{

		if($id > 0)
		{
			$GLOBALS['phpgw']->template->set_var(
				Array(
					'BG6'       => $GLOBALS['phpgw_info']['theme']['bg03'],
					'CAT_NAME'  => $f_tree[$id]['name'],
					'CAT_DESC'  => ($f_tree[$id]['descr']?$f_tree[$id]['descr']:'&nbsp;'),
					'EDIT_LINK'	=> $GLOBALS['phpgw']->link('/forum/admin/category.php',"act=edit&cat_id=$id"),
					'DEL_LINK'  => $GLOBALS['phpgw']->link('/forum/admin/deletecategory.php',"cat_id=$id"),
					'LANG_EDIT'	=> lang('Edit'),
					'LANG_DEL'  => lang('Delete')
				)
			);
		}
		else
		{
			// Not sure changing to	what
			echo '<h1>running this?</h1>';
			echo '<tr>'."\n";
			echo ' <td colspan="3" align="right" valign="top">'."\n";
			echo '<table border="0" width="100%">'."\n";
		}

		$GLOBALS['tr_color'] = $GLOBALS['phpgw_info']['theme']['row_off'];
		//Cleaning the ForumB variable because the blocks use more than	once
		$GLOBALS['phpgw']->template->set_var('ForumB','');

		for(reset($f_tree[$id]['forums']); $fid=key($f_tree[$id]['forums']); next($f_tree[$id]['forums']))
		{
			$GLOBALS['tr_color'] = $GLOBALS['phpgw']->nextmatchs->alternate_row_color();

			$phpgw->template->set_var(
				Array(
					'TD_BG'        => 'ffffff',
					'TR_BG'        => $GLOBALS['tr_color'],
					'SUBCAT_NAME'  => $f_tree[$id]['forums'][$fid]['name'],
					'SUBCAT_DESC'  => ($f_tree[$id]['forums'][$fid]['descr']?$f_tree[$id]['forums'][$fid]['descr']:'&nbsp;'),
					'SUBEDIT_LINK' => $GLOBALS['phpgw']->link('/forum/admin/forum.php',"act=edit&for_id=$fid"),
					'SUBDEL_LINK'  => $GLOBALS['phpgw']->link('/forum/admin/deleteforum.php',	"for_id=$fid"),
					'LANG_EDIT'    => lang('Edit'),
					'LANG_DEL'     => lang('Delete'),
					'LANG_FORUM'   => lang('Forum')
				)
			);

			//Parsing the inner block
			$GLOBALS['phpgw']->template->fp('ForumB','ForumBlock',true);
		}
		// Parsing the outer block
		$GLOBALS['phpgw']->template->set_var(
			Array(
				'TD_BG'		=> 'ffffff',
				'TR_BG'		=> $GLOBALS['tr_color']
			)
		);

		$GLOBALS['phpgw']->template->fp('CatB','CatBlock',true);
	}

	$GLOBALS['phpgw']->template->set_var('BG6',$GLOBALS['phpgw_info']['theme']['bg03']);
	$GLOBALS['phpgw']->template->set_var('TD_BG','ffffff');

	$GLOBALS['phpgw']->template->pfp('Out','INDEX');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
