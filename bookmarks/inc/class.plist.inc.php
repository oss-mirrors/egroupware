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

	// These functions will be slowly moved into its own class
	class plist
	{
		function plist()
		{
		}
	}

	function print_list_break (&$list_tpl, $category, $subcategory)
	{
		$massupdate_shown = $GLOBALS['massupdate_shown'];

		// construct URLs that include WHERE clauses for linking to the
		// search page. The Category link will show a search WHERE the
		// category matches. The sub-cat link will show a search WHERE
		// the subcategory matches. Need to encode the URL since it contains
		// single-quotes, equal sign, and possibly spaces.
		// we use base64 coding rather than urlencode and rawencode since
		// it seems to be more reliable.

		$cat_search    = $GLOBALS['phpgw']->link('/bookmarks/search.php','where=' . urlencode("category.name='$category'"));
		$subcat_search = $GLOBALS['phpgw']->link('/bookmarks/search.php','where=' . urlencode("subcategory.name='$subcategory'"));

		// We only want to display the massupdate section once
		if (! $massupdate_shown)
		{
			$list_tpl->set_var('lang_massupdate',lang('Mass update:'));

			$list_tpl->set_var('massupdate_delete_icon','<input type="image" name="delete" border="0" src="' . PHPGW_IMAGES . '/delete.gif">');
			$list_tpl->set_var('massupdate_mail_icon','<input type="image" name="mail" border="0" src="' . PHPGW_IMAGES . '/mail.gif">');
			$massupdate_shown = True;
		}
		else
		{
			$list_tpl->set_var('lang_massupdate','');
			$list_tpl->set_var('massupdate_delete_icon','');
			$list_tpl->set_var('massupdate_mail_icon','');
		}

		$list_tpl->set_var(array(
			'CATEGORY'           => htmlspecialchars(stripslashes($category)),
//			'CATEGORY_SEARCH'    => $cat_search,
			'SUBCATEGORY'        => htmlspecialchars(stripslashes($subcategory)),
//			'SUBCATEGORY_SEARCH' => $subcat_search
		));

		$list_tpl->fp('LIST_HDR','list_header');
		$list_tpl->fp('LIST_FTR','list_footer');        
		$list_tpl->fp('CONTENT','list_section',TRUE);
		$list_tpl->set_var('LIST_ITEMS','');
	}

	function print_list ($where_clause, $start, $returnto, &$content, &$error_msg)
	{
		$bm_cat            = $GLOBALS['bm_cat'];
		$page_header_shown = $GLOBALS['page_header_shown'];

		$list_tpl = $GLOBALS['phpgw']->template;

		$list_tpl->set_file(array(
			'list' => 'list.tpl'
		));
		$list_tpl->set_block('list','list_section');
		$list_tpl->set_block('list','list_header');
		$list_tpl->set_block('list','list_footer');
		$list_tpl->set_block('list','list_item');
		$list_tpl->set_block('list','list_keyw');
		$list_tpl->set_block('list','page_header');
		$list_tpl->set_block('list','page_footer');

		$list_tpl->set_var('list_mass_select_form',$GLOBALS['phpgw']->link('/bookmarks/mass_maintain.php'));

		if (! $page_header_shown)
		{
			$list_tpl->fp('header','page_header');
			$page_header_shown = True;
		}
		else
		{
			$list_tpl->set_var('header','');
		}

		$filtermethod = '( bm_owner=' . $GLOBALS['phpgw_info']['user']['account_id'];
		if (is_array($GLOBALS['phpgw']->bookmarks->grants))
		{
			$grants = $GLOBALS['phpgw']->bookmarks->grants;
			reset($grants);
			while (list($user) = each($grants))
			{
				$public_user_list[] = $user;
			}
			reset($public_user_list);
			$filtermethod .= " OR (bm_access='public' AND bm_owner in(" . implode(',',$public_user_list) . ')))';
		}
		else
		{
			$filtermethod .= ' )';
		}

		$query = sprintf('select * from phpgw_bookmarks where %s',$filtermethod);

		if ($bm_cat)
		{
			$where_clause .= " bm_category='$bm_cat' ";
		}

		if ($where_clause)
		{
			$where_clause_sql = ' and ' . $where_clause;
		}
		else
		{
			$where_clause_sql = ' ';
		}

		$query .= $where_clause_sql . ' order by bm_category, bm_name';

		$GLOBALS['phpgw']->db->limit_query($query,$start,__LINE__,__FILE__);

		$prev_category_id = -1;
		$prev_subcategory_id = -1;
		$rows_printed = 0;

		while ($GLOBALS['phpgw']->db->next_record())
		{
			$category_name    = $GLOBALS['phpgw']->categories->return_name($GLOBALS['phpgw']->db->f('bm_category'));
			$subcategory_name = $GLOBALS['phpgw']->categories->return_name($GLOBALS['phpgw']->db->f('bm_subcategory'));

			$rows_printed++;

			if (($category_name != $prev_category) or ($subcategory_name != $prev_subcategory))
			{
				if ($rows_printed > 1)
				{
					print_list_break(&$list_tpl, $prev_category, $prev_subcategory);
				}
				$prev_category       = $category_name;
				$prev_subcategory    = $subcategory_name;
			}

			if ($GLOBALS['phpgw']->db->f('bm_keywords'))
			{
				$list_tpl->set_var(BOOKMARK_KEYW, htmlspecialchars(stripslashes($GLOBALS['phpgw']->db->f('bm_keywords'))));
				$list_tpl->parse('bookmark_keywords','list_keyw');
			}
			else
			{
				$list_tpl->set_var('bookmark_keywords','');
			}

			// Check owner
			if (($this->grants[$GLOBALS['phpgw']->db->f('bm_owner')] & PHPGW_ACL_EDIT) || ($GLOBALS['phpgw']->db->f('bm_owner') == $GLOBALS['phpgw_info']['user']['account_id']))
			{
				$maintain_url  = $GLOBALS['phpgw']->link("/bookmarks/maintain.php","bm_id=" . $GLOBALS['phpgw']->db->f("bm_id"));
				$maintain_link = sprintf('<a href="%s"><img src="%s/edit.gif" align="top" border="0" alt="%s"></a>', $maintain_url,PHPGW_IMAGES,lang('Edit this bookmark'));
			}
			else
			{
				$maintain_link = '';
			}
			$list_tpl->set_var('maintain_link',$maintain_link);

			$list_tpl->set_var('bookmark_url',$GLOBALS['phpgw']->link('/bookmarks/redirect.php','bm_id=' . $GLOBALS['phpgw']->db->f('bm_id')));

			$view_url      = $GLOBALS['phpgw']->link('/bookmarks/view.php','bm_id=' . $GLOBALS['phpgw']->db->f('bm_id'));
			$view_link     = sprintf('<a href="%s"><img src="%s/document.gif" align="top" border="0" alt="%s"></a>', $view_url,PHPGW_IMAGES,lang('View this bookmark'));
			$list_tpl->set_var('view_link',$view_link);

			$mail_link = sprintf('<a href="%s"><img align="top" border="0" src="%s/mail.gif" alt="%s"></a>',
				$GLOBALS['phpgw']->link('/bookmarks/maillink.php','bm_id='.$GLOBALS['phpgw']->db->f("bm_id")),PHPGW_IMAGES,lang('Mail this bookmark'));
			$list_tpl->set_var('mail_link',$mail_link);

			$list_tpl->set_var('checkbox','<input type="checkbox" name="item_cb[]" value="' . $GLOBALS['phpgw']->db->f('bm_id') . '">');
			$list_tpl->set_var('img_root',PHPGW_IMAGES);
			$list_tpl->set_var('bookmark_name',$GLOBALS['phpgw']->strip_html($GLOBALS['phpgw']->db->f('bm_name')));
			$list_tpl->set_var('bookmark_desc',nl2br($GLOBALS['phpgw']->strip_html($GLOBALS['phpgw']->db->f('bm_desc'))));
			$list_tpl->set_var('bookmark_rating',sprintf('<img src="%s/bar-%s.jpg">',PHPGW_IMAGES,$GLOBALS['phpgw']->db->f('bm_rating')));

			$list_tpl->parse(LIST_ITEMS,'list_item',True);
		}

		if ($rows_printed > 0)
		{
			print_list_break(&$list_tpl, $prev_category, $prev_subcategory);
			$content = $list_tpl->get('CONTENT');
			$list_tpl->fp('footer','page_footer');
		}
	}
?>
