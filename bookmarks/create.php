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
		'currentapp'              => 'bookmarks',
		'enable_nextmatchs_class' => True,
		'enable_categories_class' => True,
		'noheader'                => True,
		'nonavbar'                => True,
		'preferences_header' => True
	);
	include('../header.inc.php');
	$phpgw->bookmarks = createobject('bookmarks.bookmarks');

	if ($edit_category_x || $edit_category_y)
	{
		grab_form_values('create.php',True);
		$phpgw->redirect($phpgw->link('/preferences/categories.php','cats_app=bookmarks&global_cats=True'));
	}

	$location_info = $phpgw->bookmarks->read_session_data();
	if ($cancel_x || $cancel_y)
	{
		if ($location_info['bm_id'])
		{
			$extravars = 'bm_id=' . $location_info['bm_id'] . '&bm_cat=' . $location_info['bm_cat'];
		}
		$phpgw->redirect($phpgw->link('/bookmarks/' . $location_info['returnto'],$extravars));
	}

	if ($location_info['returnto'] == 'create.php')
	{
		$bookmark['name']        = $location_info['bookmark_name'];
		$bookmark['url']         = $location_info['bookmark_url'];
		$bookmark['desc']        = $location_info['bookmark_desc'];
		$bookmark['keywords']    = $location_info['bookmark_keywords'];
		$bookmark['category']    = $location_info['bookmark_category'];
		$bookmark['rating']      = $location_info['bookmark_rating'];
	}


	$phpgw->common->phpgw_header();
	include(PHPGW_APP_INC . '/header.inc.php');
	echo parse_navbar();

	$phpgw->template->set_file(array(
		'common_'            => 'common.tpl',
		'possible_dup'       => 'create.possible_dup.tpl',
		'possible_dup_lines' => 'create.possible_dup.line.tpl',
		'form'               => 'form.tpl'
	));
	$phpgw->template->set_block('form','body');

	app_header(&$phpgw->template);


	// if browser is MSIE, then need to add this bit
	// of javascript to the page so that MSIE correctly
	// brings quik-mark and mail-this-link popups to the front.
	if (check_browser() == 'MSIE')
	{
		#$phpgw->template->parse(MSIE_JS, 'msie_js');
	}

	// initialize variable that holds id of newly created bookmark
	$id = 0;

	if ($create)
	{
		$phpgw->bookmarks->add(&$id,$bookmark);
		$location_info = array(
			'start'    => 0,
			'returnto' => 'list.php'
		);
		$phpgw->bookmarks->save_session_data($location_info);
	}

	// Check to see if any existing bookmarks are a "close match".
	// don't do this check after a save.
/*if ($default_url != "http://") {
   $db_dup   = $phpgw->db;

   ## the "close match" consists of looking for other URLs at the
   ## hostname that match the first $bookmarker->possible_dup_chars
   ## after the hostname.
   $url_elements = parse_url($default_url);
   $hostname  = $url_elements[host];
   $scheme    = $url_elements[scheme];
   $path_part = substr($url_elements[path], 0, $bookmarker->possible_dup_chars);
   $look_for = $scheme."://".$hostname.$path_part."%";

   $query = sprintf("select url, name from bookmark where url like '%s' and username = '%s'", $look_for, $phpgw_info["user"]["account_id"]);
   $db_dup->query($query,__LINE__,__FILE__);
   if ($db_dup->Errno == 0) {
      while ($db_dup->next_record()){
         $phpgw->template->set_var(array(DUP_URL   => $db_dup->f("url"),
                                         DUP_NAME  => htmlspecialchars(stripslashes($db_dup->f("name")))
                                  ));
         $phpgw->template->parse(POSSIBLE_DUP_LINES, "possible_dup_lines", TRUE);
         $possible_dups_found = TRUE;
     }
     if ($possible_dups_found){
        $phpgw->template->parse(POSSIBLE_DUP, "possible_dup");
     }
   }
} */

	$phpgw->template->set_var('lang_header',lang('Create new bookmark'));

	$phpgw->template->set_var('input_category',$phpgw->bookmarks->categories_list($bookmark['category']));

	$phpgw->template->set_var('category_image','<input type="image" name="edit_category" src="' . PHPGW_IMAGES . '/edit.gif" border="0">');

	$selected[$bookmark['rating']] = ' selected';
	$phpgw->template->set_var('input_rating','<select name="bookmark[rating]">'
                                         . ' <option value="0"' . $selected[0] . '>--</option>'
                                         . ' <option value="1"' . $selected[1] . '>1 - ' . lang('Lowest') . '</option>'
                                         . ' <option value="2"' . $selected[2] . '>2</option>'
                                         . ' <option value="3"' . $selected[3] . '>3</option>'
                                         . ' <option value="4"' . $selected[4] . '>4</option>'
                                         . ' <option value="5"' . $selected[5] . '>5</option>'
                                         . ' <option value="6"' . $selected[6] . '>6</option>'
                                         . ' <option value="7"' . $selected[7] . '>7</option>'
                                         . ' <option value="8"' . $selected[8] . '>8</option>'
                                         . ' <option value="9"' . $selected[9] . '>9</option>'
                                         . ' <option value="10"' . $selected[10] . '>10 - ' . lang('Highest') . '</option>'
                                         . '</select>');

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('th_text',$phpgw_info['theme']['th_text']);
	$phpgw->template->set_var('public_id',$phpgw->accounts->name2id($phpgw_info['user']['account_lid'] . '_'));

	$phpgw->template->set_var('form_action',$phpgw->link('/bookmarks/create.php','create=True'));
	$phpgw->template->set_var('lang_url',lang('URL'));
	$phpgw->template->set_var('lang_name',lang('Name'));
	$phpgw->template->set_var('lang_desc',lang('Description'));
	$phpgw->template->set_var('lang_keywords',lang('Keywords'));
	$phpgw->template->set_var('lang_access',lang('Private'));
	$phpgw->template->set_var('lang_category',lang('Category'));
	$phpgw->template->set_var('lang_subcategory',lang('Sub Category'));
	$phpgw->template->set_var('lang_rating',lang('Rating'));

	$phpgw->template->set_var('input_url','<input name="bookmark[url]" size="60" maxlength="255" value="' . ($bookmark['url']?$bookmark['url']:'http://') . '">');
	$phpgw->template->set_var('input_name','<input name="bookmark[name]" size="60" maxlength="255" value="' . $bookmark['name'] . '">');

	$phpgw->template->set_var('input_desc','<textarea name="bookmark[desc]" rows="3" cols="60" wrap="virtual">' . $bookmark['desc'] . '</textarea>');
	$phpgw->template->set_var('input_keywords','<input type="text" name="bookmark[keywords]" size="60" maxlength="255" value="' . $bookmark['keywords'] . '">');

	if ($create)
	{
		if ($access)
		{
			$checked = ' checked';
		}
		else
		{
			$checked = '';
		}
	}
	else
	{
		$checked = ' checked';
	}

	$phpgw->template->set_var('input_access','<input type="checkbox" name="bookmark[access]" value="private"' . $checked . '>');

	$phpgw->template->set_var('cancel_button','<input type="image" name="cancel" title="' . lang('Done') . '" src="' . PHPGW_IMAGES . '/cancel.gif" border="0">');
	$phpgw->template->set_var('form_link','<input type="image" name="bk_create" alt="'
                                      . lang('Create bookmark') . '" src="' . PHPGW_IMAGES . '/save.gif" border="0">');

	$phpgw->common->phpgw_footer();
?>
