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
		'currentapp' => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True,
		'noheader'                => True,
		'nonavbar'                => True
	);
	include('../header.inc.php');
	$phpgw->bookmarks = createobject('bookmarks.bookmarks');

	if ($edit_category_x || $edit_category_y)
	{
		grab_form_values('maintain.php',True);
		$phpgw->redirect($phpgw->link('/preferences/categories.php','cats_app=bookmarks&global_cats=True'));
	}

	$location_info = $phpgw->bookmarks->read_session_data();

	if ($delete_x || $delete_y)
	{
		if (! $phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
		{
			$phpgw->redirect($phpgw->link('/bookmarks/list.php'));
		}
		else
		{
			$phpgw->bookmarks->delete($bm_id);
			$phpgw->redirect($phpgw->link('/bookmarks/' . $location_info['returnto']));
		}
	}

	if ($cancel_x || $cancel_y)
	{
		// TODO: If they go to categorys, it will not redirect them properly.
		if ($location_info['returnto'] == 'maintain.php')
		{
			$location_info = array(
				'start'    => 0,
				'returnto' => 'list.php'
			);
			$phpgw->bookmarks->save_session_data($location_info);
		}
		if ($location_info['bm_id'])
		{
			$extravars = 'bm_id=' . $location_info['bm_id'];
		}
		$phpgw->redirect($phpgw->link('/bookmarks/' . $location_info['returnto'],$extravars));
	}

	if ($edit_x || $edit_y)
	{
		if (! $phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_EDIT))
		{
			$phpgw->redirect($phpgw->link('/bookmarks/list.php'));
		}
		else
		{
			$phpgw->bookmarks->update($bm_id,$bookmark);

			if ($location_info['bm_id'])
			{
				$extravars = 'bm_id=' . $bm_id;
			}
			$phpgw->redirect($phpgw->link('/bookmarks/view.php',$extravars));
		}	
	}

	$location_info = $phpgw->bookmarks->read_session_data();
	if ($location_info['returnto'] == 'maintain.php')
	{
		$bookmark['name']        = $location_info['bookmark_name'];
		$bookmark['url']         = $location_info['bookmark_url'];
		$bookmark['desc']        = $location_info['bookmark_desc'];
		$bookmark['keywords']    = $location_info['bookmark_keywords'];
		$bookmark['category']    = $location_info['bookmark_category'];
		$bookmark['rating']      = $location_info['bookmark_rating'];
	}
	else if (! $edit_x || ! $edit_y)
	{
		$phpgw->db->query("select * from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
		$phpgw->db->next_record();

		$bookmark['name']        = $phpgw->db->f('bm_name');
		$bookmark['url']         = $phpgw->db->f('bm_url');
		$bookmark['desc']        = $phpgw->db->f('bm_desc');
		$bookmark['keywords']    = $phpgw->db->f('bm_keywords');
		$bookmark['category']    = $phpgw->db->f('bm_category') . '|' . $phpgw->db->f('bm_subcategory');
		$bookmark['rating']      = $phpgw->db->f('bm_rating');
	}

	$phpgw->template->set_file(array(
		'common_' => 'common.tpl',
		'form'    => 'form.tpl'
	));
	$phpgw->template->set_block('form','body');
	$phpgw->template->set_block('form','form_info');

	$phpgw->common->phpgw_header();
	include(PHPGW_APP_INC . '/header.inc.php');
	echo parse_navbar();

	app_header(&$phpgw->template);

	if (empty($error_msg))
	{
		date_information(&$phpgw->template,$phpgw->db->f('bm_info'));

		$rs[$bookmark['rating']] = ' selected';
		$rating_select = '<select name="bookmark[rating]">'
                    . ' <option value="0">--</option>'
                    . ' <option value="1"' . $rs[1] . '>1 - ' . lang('Lowest') . '</option>'
                    . ' <option value="2"' . $rs[2] . '>2</option>'
                    . ' <option value="3"' . $rs[3] . '>3</option>'
                    . ' <option value="4"' . $rs[4] . '>4</option>'
                    . ' <option value="5"' . $rs[5] . '>5</option>'
                    . ' <option value="6"' . $rs[6] . '>6</option>'
                    . ' <option value="7"' . $rs[7] . '>7</option>'
                    . ' <option value="8"' . $rs[8] . '>8</option>'
                    . ' <option value="9"' . $rs[9] . '>9</option>'
                    . ' <option value="10"' . $rs[10] . '>10 - ' . lang('Highest') . '</option>'
                    . '</select>';
    
		$phpgw->template->set_var('lang_header',lang('Edit bookmark'));
		$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
		$phpgw->template->set_var('updated',$f_ts[2]);
		$phpgw->template->set_var('total_visits',$phpgw->db->f('bm_visits'));

		$phpgw->template->set_var('lang_owner',lang('Created by'));
		$account = createobject('phpgwapi.accounts',$phpgw->db->f('bm_owner'));
		$ad      = $account->read_repository();
		$phpgw->template->set_var('owner_value',$phpgw->common->display_fullname($ad['account_lid'],$ad['firstname'],$ad['lastname']));

		$phpgw->template->set_var('lang_added',lang('Date added'));
		$phpgw->template->set_var('lang_updated',lang('Date last updated'));
		$phpgw->template->set_var('lang_visited',lang('Date last visited'));
		$phpgw->template->set_var('lang_visits',lang('Total visits'));

		$phpgw->template->parse('info','form_info');

		$phpgw->template->set_var('form_action',$phpgw->link('/bookmarks/maintain.php','bm_id=' . $bm_id));
		$phpgw->template->set_var('lang_url',lang('URL'));
		$phpgw->template->set_var('lang_name',lang('Name'));
		$phpgw->template->set_var('lang_desc',lang('Description'));
		$phpgw->template->set_var('lang_keywords',lang('Keywords'));

		$phpgw->template->set_var('lang_category',lang('Category'));
		$phpgw->template->set_var('lang_subcategory',lang('Sub Category'));
		$phpgw->template->set_var('lang_rating',lang('Rating'));

		$phpgw->template->set_var('lang_access',lang('Private'));
		$phpgw->template->set_var('input_access','<input type="checkbox" name="bookmark[access]" value="private"' . ($bookmark['access']=='private'?' checked':'') . '>');

		$phpgw->template->set_var('input_rating',$rating_select);

		$phpgw->template->set_var('input_category',$phpgw->bookmarks->categories_list($bookmark['category']));
		$phpgw->template->set_var('category_image','<input type="image" name="edit_category" src="' . PHPGW_IMAGES . '/edit.gif" border="0">');
    
  
		$phpgw->template->set_var('input_url','<input name="bookmark[url]" size="60" maxlength="255" value="' . $bookmark['url'] . '">');
		$phpgw->template->set_var('input_name','<input name="bookmark[name]" size="60" maxlength="255" value="' . $bookmark['name'] . '">');
		$phpgw->template->set_var('input_desc','<textarea name="bookmark[desc]" rows="3" cols="60" wrap="virtual">' . $bookmark['desc'] . '</textarea>');
		$phpgw->template->set_var('input_keywords','<input type="text" name="bookmark[keywords]" size="60" maxlength="255" value="' . $bookmark['keywords'] . '">');

		$phpgw->template->parse('BODY','body');

		if ($phpgw->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
		{
			$phpgw->template->set_var('delete_button','<input type="image" name="delete" title="' . lang('Delete') . '" src="' . PHPGW_IMAGES . '/delete.gif" border="0">');
		}

		$phpgw->template->set_var('cancel_button','<input type="image" name="cancel" title="' . lang('Done') . '" src="' . PHPGW_IMAGES . '/cancel.gif" border="0">');
		$phpgw->template->set_var('form_link','<input type="image" name="edit" title="' . lang('Change Bookmark') . '" src="'
                                         . PHPGW_IMAGES . '/save.gif" border="0">');
	}
	$phpgw->common->phpgw_footer();
?>
