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
		'currentapp' => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True,
		'noheader'                => True,
		'nonavbar'                => True
	);
	include('../header.inc.php');

	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');

	$edit_category_x = get_var('edit_category_x',Array('POST'));
	$edit_category_y = get_var('edit_category_y',Array('POST'));
	$delete_x        = get_var('delete_x',Array('POST'));
	$delete_y        = get_var('delete_y',Array('POST'));
	$cancel_x        = get_var('cancel_x',Array('POST'));
	$cancel_y        = get_var('cancel_y',Array('POST'));
	$bm_id           = get_var('bm_id',Array('POST','GET'));

	if ($edit_category_x || $edit_category_y)
	{
		grab_form_values('maintain.php',True);
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app=bookmarks&global_cats=True'));
	}

	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();

	if ($delete_x || $delete_y)
	{
		if (! $GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
		{
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/list.php'));
		}
		else
		{
			$GLOBALS['phpgw']->bookmarks->delete($bm_id);
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/' . $location_info['returnto']));
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
			$GLOBALS['phpgw']->bookmarks->save_session_data($location_info);
		}
		if ($location_info['bm_id'])
		{
			$extravars = 'bm_id=' . $location_info['bm_id'];
		}
		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/' . $location_info['returnto'],$extravars));
	}

	if ($edit_x || $edit_y)
	{
		if (! $GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_EDIT))
		{
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/list.php'));
		}
		else
		{
			$GLOBALS['phpgw']->bookmarks->update($bm_id,$bookmark);

			if ($location_info['bm_id'])
			{
				$extravars = 'bm_id=' . $bm_id;
			}
			$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/bookmarks/view.php',$extravars));
		}	
	}

	$location_info = $GLOBALS['phpgw']->bookmarks->read_session_data();
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
		$GLOBALS['phpgw']->db->query("select * from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$bookmark['name']        = $GLOBALS['phpgw']->db->f('bm_name');
		$bookmark['url']         = $GLOBALS['phpgw']->db->f('bm_url');
		$bookmark['desc']        = $GLOBALS['phpgw']->db->f('bm_desc');
		$bookmark['keywords']    = $GLOBALS['phpgw']->db->f('bm_keywords');
		$bookmark['category']    = $GLOBALS['phpgw']->db->f('bm_category') . '|' . $GLOBALS['phpgw']->db->f('bm_subcategory');
		$bookmark['rating']      = $GLOBALS['phpgw']->db->f('bm_rating');
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'common_' => 'common.tpl',
		'form'    => 'form.tpl'
	));
	$GLOBALS['phpgw']->template->set_block('form','body');
	$GLOBALS['phpgw']->template->set_block('form','form_info');

	$GLOBALS['phpgw']->common->phpgw_header();
	include(PHPGW_APP_INC . '/header.inc.php');
	echo parse_navbar();

	app_header(&$GLOBALS['phpgw']->template);

	if (empty($error_msg))
	{
		date_information(&$GLOBALS['phpgw']->template,$GLOBALS['phpgw']->db->f('bm_info'));

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
    
		$GLOBALS['phpgw']->template->set_var('lang_header',lang('Edit bookmark'));
		$GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
		$GLOBALS['phpgw']->template->set_var('updated',$f_ts[2]);
		$GLOBALS['phpgw']->template->set_var('total_visits',$GLOBALS['phpgw']->db->f('bm_visits'));

		$GLOBALS['phpgw']->template->set_var('lang_owner',lang('Created by'));
		$account = createobject('phpgwapi.accounts',$GLOBALS['phpgw']->db->f('bm_owner'));
		$ad      = $account->read_repository();
		$GLOBALS['phpgw']->template->set_var('owner_value',$GLOBALS['phpgw']->common->display_fullname($ad['account_lid'],$ad['firstname'],$ad['lastname']));

		$GLOBALS['phpgw']->template->set_var('lang_added',lang('Date added'));
		$GLOBALS['phpgw']->template->set_var('lang_updated',lang('Date last updated'));
		$GLOBALS['phpgw']->template->set_var('lang_visited',lang('Date last visited'));
		$GLOBALS['phpgw']->template->set_var('lang_visits',lang('Total visits'));

		$GLOBALS['phpgw']->template->parse('info','form_info');

		$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/bookmarks/maintain.php','bm_id=' . $bm_id));
		$GLOBALS['phpgw']->template->set_var('lang_url',lang('URL'));
		$GLOBALS['phpgw']->template->set_var('lang_name',lang('Name'));
		$GLOBALS['phpgw']->template->set_var('lang_desc',lang('Description'));
		$GLOBALS['phpgw']->template->set_var('lang_keywords',lang('Keywords'));

		$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
		$GLOBALS['phpgw']->template->set_var('lang_subcategory',lang('Sub Category'));
		$GLOBALS['phpgw']->template->set_var('lang_rating',lang('Rating'));

		$GLOBALS['phpgw']->template->set_var('lang_access',lang('Private'));
		$GLOBALS['phpgw']->template->set_var('input_access','<input type="checkbox" name="bookmark[access]" value="private"' . ($bookmark['access']=='private'?' checked':'') . '>');

		$GLOBALS['phpgw']->template->set_var('input_rating',$rating_select);

		$GLOBALS['phpgw']->template->set_var('input_category',$GLOBALS['phpgw']->bookmarks->categories_list($bookmark['category']));
		$GLOBALS['phpgw']->template->set_var('category_image','<input type="image" name="edit_category" title="' . lang('Edit category') . '" src="'
															. $GLOBALS['phpgw']->common->image('bookmarks','edit') . '" border="0">');
    
		$GLOBALS['phpgw']->template->set_var('input_url','<input name="bookmark[url]" size="60" maxlength="255" value="' . $bookmark['url'] . '">');
		$GLOBALS['phpgw']->template->set_var('input_name','<input name="bookmark[name]" size="60" maxlength="255" value="' . $bookmark['name'] . '">');
		$GLOBALS['phpgw']->template->set_var('input_desc','<textarea name="bookmark[desc]" rows="3" cols="60" wrap="virtual">' . $bookmark['desc'] . '</textarea>');
		$GLOBALS['phpgw']->template->set_var('input_keywords','<input type="text" name="bookmark[keywords]" size="60" maxlength="255" value="' . $bookmark['keywords'] . '">');

		$GLOBALS['phpgw']->template->parse('BODY','body');

		if ($GLOBALS['phpgw']->bookmarks->check_perms($bm_id,PHPGW_ACL_DELETE))
		{
			$GLOBALS['phpgw']->template->set_var('delete_button','<input type="image" name="delete" title="' . lang('Delete') . '" src="'
																. $GLOBALS['phpgw']->common->image('bookmarks','delete') . '" border="0">');
		}

		$GLOBALS['phpgw']->template->set_var('cancel_button','<input type="image" name="cancel" title="' . lang('Done') . '" src="'
															. $GLOBALS['phpgw']->common->image('bookmarks','cancel') . '" border="0">');
		$GLOBALS['phpgw']->template->set_var('form_link','<input type="image" name="edit" title="' . lang('Save') . '" src="'
															. $GLOBALS['phpgw']->common->image('bookmarks','save') . '" border="0">');
	}
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
