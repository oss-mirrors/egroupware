<?php 
	/**************************************************************************\
	* phpGroupWare - Bookmarks                                                 *
	* http://www.phpgroupware.org                                              *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'               => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True
	);
	include('../header.inc.php');

	$phpgw->bookmarks = createobject('bookmarks.bookmarks');
	$phpgw->treemenu  = createobject('bookmarks.treemenu');
//	$phpgw->treemenu  = createobject('phpgwapi.menutree','F');
	$phpgw->treemenu->last_column_size = 500;

	$phpgw->template->set_file(array(
		'common_' => 'common.tpl',
		'body'   => 'list.body_tree.tpl'
	));
	app_header(&$phpgw->template);

	$phpgw->template->set_var('list_mass_select_form',$phpgw->link('/bookmarks/mass_maintain.php'));
	$phpgw->template->set_var('lang_massupdate',lang('Mass update:'));
	$phpgw->template->set_var('massupdate_delete_icon','<input type="image" name="delete" border="0" src="' . PHPGW_IMAGES . '/delete.gif">');
	$phpgw->template->set_var('massupdate_mail_icon','<input type="image" name="mail" border="0" src="' . PHPGW_IMAGES . '/mail.gif">');


	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('messages',lang('Tree view'));

	$location_info = $phpgw->bookmarks->read_session_data();
	if (! is_array($location_info))
	{
		$location_info = array(
			'returnto' => 'tree.php'
		);
		$phpgw->bookmarks->save_session_data($location_info);
	}

	if ($location_info['tree_postion'] && ! $p)
	{
		$p = $location_info['tree_postion'];
	}
	else
	{
		if ($p)
		{
			$location_info = array(
				'returnto'     => 'tree.php',
				'tree_postion' => $p
			);
			$phpgw->bookmarks->save_session_data($location_info);
		}
	}


	if ($filter != 'private')
	{
		$filtermethod = "( bm_owner=" . $phpgw_info['user']['account_id'];
		if (is_array($phpgw->bookmarks->grants))
		{
			$grants = $phpgw->bookmarks->grants;
			while (list($user) = each($grants))
			{
				$public_user_list[] = $user;
			}
			reset($public_user_list);
			$filtermethod .= " OR (bm_access='public' AND bm_owner in(" . implode(',',$public_user_list) . ")))";
		}
		else
		{
			$filtermethod .= ' )';
		}
	}
	else
	{
		$filtermethod = ' bm_owner=' . $phpgw_info['user']['account_id'] . ' ';
	}

	$categorys[] = array(
		'id'      => 0,
		'parent'  => 0,
		'name'    => '--'
	);

	$_categorys = $phpgw->categories->return_array('appandmains',0,$phpgw->categories->total(),'','cat_name','');

	while (is_array($_categorys) && $cat = each($_categorys))
	{
		$categorys[] = $cat[1];
	}


	$db2 = $phpgw->db;
	while ($cat = each($categorys))
	{
 		$tree[] = '.<a href="' . $phpgw->link('/bookmarks/list.php','bm_cat=' . $cat[1]['id']) . '">' . $cat[1]['name'] . '</a>' . '|';

		// FIXME: This needs to use the categorys class!
		$phpgw->db->query("select * from phpgw_categories where cat_parent='" . $cat[1]['id'] . "' and cat_appname='bookmarks' order by cat_name",__LINE__,__FILE__);
		while ($phpgw->db->next_record())
		{
			$tree[] = '..' . '<a href="' . $phpgw->link('/bookmarks/list.php','bm_cat=' . $phpgw->db->f('bm_cat')) . '">' . $phpgw->db->f('cat_name') . '</a>' . '|';
			$db2->query("select * from phpgw_bookmarks where bm_subcategory='" . $phpgw->db->f('cat_id') . "' order by bm_name, bm_url",__LINE__,__FILE__);
			while ($db2->next_record())
			{
				$_tree = '...' . $db2->f('bm_name') . '| | | |'; // . '<input type="checkbox" name="item_cb[]" value="' . $db2->f('bm_id') . '">';
				if (($phpgw->bookmarks->grants[$db2->f('bm_owner')] & PHPGW_ACL_EDIT) || ($db2->f('bm_owner') == $phpgw_info['user']['account_id']))
				{
					$maintain_url  = $phpgw->link('/bookmarks/maintain.php','bm_id=' . $db2->f('bm_id'));
					$maintain_link = sprintf('<a href="%s"><img src="%s/edit.gif" align="top" border="0" alt="%s"></a>', $maintain_url,PHPGW_IMAGES,lang('Edit this bookmark'));

					$view_url      = $phpgw->link('/bookmarks/view.php','bm_id=' . $db2->f('bm_id'));
					$view_link     = sprintf('<a href="%s"><img src="%s/document.gif" align="top" border="0" alt="%s"></a>', $view_url,PHPGW_IMAGES,lang('View this bookmark'));

//					$mail_link     = sprintf('<a href="%s"><img align="top" border="0" src="%s/mail.gif" alt="%s"></a>',
//							$phpgw->link('/bookmarks/maillink.php','bm_id='.$db2->f("bm_id")),PHPGW_IMAGES,lang('Mail this bookmark'));

					$rating_link   = sprintf('<img src="%s/bar-%s.jpg">',PHPGW_IMAGES,$db2->f('bm_rating'));

					$redirect_link = '<a href="' . $phpgw->link('/bookmarks/redirect.php','bm_id=' . $db2->f('bm_id')) . '" target="_new">' . $phpgw->strip_html($db2->f('bm_name')) . '</a>';
					$_tree        .= '<table border="0" cellpadding="0" cellspacing="0"><tr><td>' . $maintain_link . $view_link
									. '</td><td>' . $redirect_link . '</td><td align="right"></td><td>'
									. $db2->f('bm_desc') . '</td></tr></table>'; //$mail_link . $rating_link . $redirect_link;
				}

				$tree[] = $_tree;

			}
		}
	}

	$phpgw->template->set_var('BOOKMARK_LIST',$phpgw->treemenu->showmenu($tree,$p));
//	$phpgw->template->set_var('BOOKMARK_LIST',$phpgw->treemenu->showtree($tree,$p));

	$phpgw->common->phpgw_footer();
?>
