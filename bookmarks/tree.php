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

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'bookmarks'
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');
	$GLOBALS['phpgw']->treemenu  = createobject('bookmarks.treemenu');

	$GLOBALS['phpgw']->treemenu->last_column_size = 500;

	$GLOBALS['phpgw']->template->set_file(array(
		'common_' => 'common.tpl',
		'body'    => 'list.body_tree.tpl'
	));
	app_header(&$GLOBALS['phpgw']->template);

//	$GLOBALS['phpgw']->template->set_var('list_mass_select_form',$GLOBALS['phpgw']->link('/bookmarks/mass_maintain.php'));
//	$GLOBALS['phpgw']->template->set_var('lang_massupdate',lang('Mass update:'));
//	$GLOBALS['phpgw']->template->set_var('massupdate_delete_icon','<input type="image" name="delete" border="0" src="' . PHPGW_IMAGES . '/delete.gif">');
//	$GLOBALS['phpgw']->template->set_var('massupdate_mail_icon','<input type="image" name="mail" border="0" src="' . PHPGW_IMAGES . '/mail.gif">');

	$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
	$GLOBALS['phpgw']->template->set_var('messages',lang('Tree view'));

	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();
	if (! is_array($location_info))
	{
		$location_info = array(
			'returnto' => 'tree.php'
		);
		$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
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
			$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
		}
	}

	/*
	if ($filter != 'private')
	{
		$filtermethod = "( bm_owner=" . $GLOBALS['phpgw_info']['user']['account_id'];
		if (is_array($GLOBALS['phpgw']->bookmarks->grants))
		{
			$grants = $GLOBALS['phpgw']->bookmarks->grants;
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
		$filtermethod = ' bm_owner=' . $GLOBALS['phpgw_info']['user']['account_id'] . ' ';
	}
	*/

	$categories = $GLOBALS['phpgw']->categories->return_array('mains',0,$GLOBALS['phpgw']->categories->total(),'','cat_name','',True);

	/* Added to keep track of displayed items, so they do not repeat */
	$shown = array();
	$db2 = $GLOBALS['phpgw']->db;
	while ($cat = @each($categories))
	{
		$shown[] = $cat[1]['id'];
		$tree[] = '.<a href="' . $GLOBALS['phpgw']->link('/bookmarks/list.php','bm_cat=' . $cat[1]['id']) . '">' . $cat[1]['name'] . '</a>' . '|';

		$subs = $GLOBALS['phpgw']->categories->return_array('subs',0,False,'','','',True,$cat[1]['id']);
		while ($sub = @each($subs))
		{
			$shown[] = $sub['value']['id'];
			$tree[] = '..' . '<a href="' . $GLOBALS['phpgw']->link('/bookmarks/list.php','bm_cat=' . $cat[1]['id'] . '&bm_subcat='
				. $sub['value']['id']) . '">' . $sub['value']['name'] . '</a>' . '|';
			$db2->query("select * from phpgw_bookmarks where bm_subcategory='" . $sub['value']['id'] . "' order by bm_name, bm_url",__LINE__,__FILE__);
			while ($db2->next_record())
			{
				$shown[] = $db2->f('bm_id');
				$_tree = '...' . $db2->f('bm_name') . '|'; // . '<input type="checkbox" name="item_cb[]" value="' . $db2->f('bm_id') . '">';
				if (($GLOBALS['phpgw']->bookmarks->grants[$db2->f('bm_owner')] & PHPGW_ACL_EDIT) || ($db2->f('bm_owner') == $GLOBALS['phpgw_info']['user']['account_id']))
				{
					$maintain_url  = $GLOBALS['phpgw']->link('/bookmarks/maintain.php','bm_id=' . $db2->f('bm_id'));
					$maintain_link = sprintf('<a href="%s"><img src="%s/edit.gif" align="top" border="0" alt="%s"></a>', $maintain_url,PHPGW_IMAGES,lang('Edit this bookmark'));
					$_tree        .= $maintain_link . '&nbsp;';
				}
				if (($GLOBALS['phpgw']->bookmarks->grants[$db2->f('bm_owner')] & PHPGW_ACL_READ) || ($db2->f('bm_owner') == $GLOBALS['phpgw_info']['user']['account_id']))
				{
					$view_url      = $GLOBALS['phpgw']->link('/bookmarks/view.php','bm_id=' . $db2->f('bm_id'));
					$view_link     = sprintf('<a href="%s"><img src="%s/document.gif" align="top" border="0" alt="%s"></a>', $view_url,PHPGW_IMAGES,lang('View this bookmark'));

//					$mail_link     = sprintf('<a href="%s"><img align="top" border="0" src="%s/mail.gif" alt="%s"></a>',
//						$GLOBALS['phpgw']->link('/bookmarks/maillink.php','bm_id='.$db2->f("bm_id")),PHPGW_IMAGES,lang('Mail this bookmark'));

					$rating_link   = sprintf('<img src="%s/bar-%s.jpg">',PHPGW_IMAGES,$db2->f('bm_rating'));
					$redirect_link = '<a href="' . $GLOBALS['phpgw']->link('/bookmarks/redirect.php','bm_id=' . $db2->f('bm_id')) . '" target="_new">' . $GLOBALS['phpgw']->strip_html($db2->f('bm_name')) . '</a>';
					$_tree        .= $view_link . '&nbsp; &nbsp;' . $redirect_link;// . '</td><td align="right"></td><td>'
//						. $db2->f('bm_desc') . '</td></tr></table>'; //$mail_link . $rating_link . $redirect_link;
				}

				$tree[] = $_tree;
			}
		}

		$db2->query("select * from phpgw_bookmarks where bm_category='" . $cat[1]['id'] . "' order by bm_name, bm_url",__LINE__,__FILE__);
		while ($db2->next_record())
		{
			if(in_array($db2->f('bm_id'),$shown))
			{
				continue;
			}
			$_tree = '..' . $db2->f('bm_name') . '|';
			if (($GLOBALS['phpgw']->bookmarks->grants[$db2->f('bm_owner')] & PHPGW_ACL_EDIT) || ($db2->f('bm_owner') == $GLOBALS['phpgw_info']['user']['account_id']))
			{
				$maintain_url  = $GLOBALS['phpgw']->link('/bookmarks/maintain.php','bm_id=' . $db2->f('bm_id'));
				$maintain_link = sprintf('<a href="%s"><img src="%s/edit.gif" align="top" border="0" alt="%s"></a>', $maintain_url,PHPGW_IMAGES,lang('Edit this bookmark'));
				$_tree        .= $maintain_link . '&nbsp;';
			}
			if (($GLOBALS['phpgw']->bookmarks->grants[$db2->f('bm_owner')] & PHPGW_ACL_READ) || ($db2->f('bm_owner') == $GLOBALS['phpgw_info']['user']['account_id']))
			{
				$view_url      = $GLOBALS['phpgw']->link('/bookmarks/view.php','bm_id=' . $db2->f('bm_id'));
				$view_link     = sprintf('<a href="%s"><img src="%s/document.gif" align="top" border="0" alt="%s"></a>', $view_url,PHPGW_IMAGES,lang('View this bookmark'));

				$rating_link   = sprintf('<img src="%s/bar-%s.jpg">',PHPGW_IMAGES,$db2->f('bm_rating'));
				$redirect_link = '<a href="' . $GLOBALS['phpgw']->link('/bookmarks/redirect.php','bm_id=' . $db2->f('bm_id')) . '" target="_new">' . $GLOBALS['phpgw']->strip_html($db2->f('bm_name')) . '</a>';
				$_tree        .= $view_link . '&nbsp;' . $redirect_link;
			}
			$tree[] = $_tree;
		}
	}

	$GLOBALS['phpgw']->template->set_var('BOOKMARK_LIST',$GLOBALS['phpgw']->treemenu->showmenu($tree,$p));

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
