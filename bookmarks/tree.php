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


	$phpgw->template->set_file(array(
		'common' => 'common.tpl',
		'body'   => 'list.body.tpl'
	));
	app_header(&$phpgw->template);

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('lang_tree_view',lang('Tree view'));


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

	while ($cat = each($_categorys))
	{
		$categorys[] = $cat[1];
	}


	$db2 = $phpgw->db;
	while ($cat = each($categorys))
	{
 		$tree[] = '.' . $cat[1]['name'] . '| ';

		// FIXME: This needs to use the categorys class!
		$phpgw->db->query("select * from phpgw_categories where cat_parent='" . $cat[1]['id'] . "' and cat_appname='bookmarks' order by cat_name",__LINE__,__FILE__);
		while ($phpgw->db->next_record())
		{
			$tree[] = '..' . $phpgw->db->f('cat_name') . '| ';
			$db2->query("select * from phpgw_bookmarks where bm_subcategory='" . $phpgw->db->f('cat_id') . "' order by bm_name, bm_url",__LINE__,__FILE__);
			while ($db2->next_record())
			{
				$tree[] = '...' . $db2->f('bm_name') . '| ';
			}
		}
	}

	$phpgw->template->set_var('BOOKMARK_LIST',$phpgw->treemenu->showmenu($tree));

	$phpgw->common->phpgw_footer();
?>