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
		'enable_nextmatchs_class' = True,
		'enable_categories_class' = True
	);
	include('../header.inc.php');
  
	$phpgw->sbox = createobject('phpgwapi.sbox');

	$phpgw->template->set_file(array(
		'common'             => 'common.tpl',
		'possible_dup'       => 'create.possible_dup.tpl',
		'possible_dup_lines' => 'create.possible_dup.line.tpl',
		'body'               => 'form.tpl'
	));

	app_header(&$phpgw->template);


	// if browser is MSIE, then need to add this bit
	// of javascript to the page so that MSIE correctly
	// brings quik-mark and mail-this-link popups to the front.
	if (check_browser() == 'MSIE')
	{
		$phpgw->template->parse(MSIE_JS, 'msie_js');
	}

	// initialize variable that holds id of newly created bookmark
	$id = 0;
	$bmark = new bmark;

	if ($create)
	{
		$bmark->add(&$id,$url,$name,$desc,$keyw,$bookmarks_category,$bookmarks_subcategory,$bookmarks_rating, $access,$groups);
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

	$phpgw->template->set_var('input_category','<select name="bookmarks_category">'
                                           . '<option value="0">--</option>'
                                           . $phpgw->categories->formated_list('select','mains')
                                           . '</select>');

	$phpgw->template->set_var('input_subcategory','<select name="bookmarks_subcategory">'
                                              . '<option value="0">--</option>'
                                              . $phpgw->categories->formated_list('select','subs')
                                              . '</select>');

	$phpgw->template->set_var('input_rating','<select name="bookmarks_rating">'
                                         . ' <option value="0">--</option>'
                                         . ' <option value="1">1 - ' . lang('Lowest') . '</option>'
                                         . ' <option value="2">2</option>'
                                         . ' <option value="3">3</option>'
                                         . ' <option value="4">4</option>'
                                         . ' <option value="5">5</option>'
                                         . ' <option value="6">6</option>'
                                         . ' <option value="7">7</option>'
                                         . ' <option value="8">8</option>'
                                         . ' <option value="9">9</option>'
                                         . ' <option value="10">10 - ' . lang('Highest') . '</option>'
                                         . '</select>');

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);

	$phpgw->template->set_var('form_action',$phpgw->link('/bookmarks/create.php','create=True'));
	$phpgw->template->set_var('lang_url',lang('URL'));
	$phpgw->template->set_var('lang_name',lang('Name'));
	$phpgw->template->set_var('lang_desc',lang('Description'));
	$phpgw->template->set_var('lang_keywords',lang('Keywords'));
	$phpgw->template->set_var('lang_access',lang('Access'));
	$phpgw->template->set_var('lang_category',lang('Category'));
	$phpgw->template->set_var('lang_subcategory',lang('Sub Category'));
	$phpgw->template->set_var('lang_rating',lang('Rating'));

	$phpgw->template->set_var('input_url','<input name="url" size="60" maxlength="255" value="' . ($url?$url:'http://') . '">');
	$phpgw->template->set_var('input_name','<input name="name" size="60" maxlength="255" value="' . $name . '">');

	$phpgw->template->set_var('input_desc','<textarea name="desc" rows="3" cols="60" wrap="virtual">' . $desc . '</textarea>');
	$phpgw->template->set_var('input_keywords','<input type="text" name="keyw" size="60" maxlength="255" value="' . $keyw . '">');

	$phpgw->template->set_var('input_access',$phpgw->sbox->getAccessList('access') . "&nbsp;"
                                         . $phpgw->sbox->getGroups($phpgw->accounts->memberships($phpgw_info['user']['account_id']),-1,'groups'));

	$phpgw->template->set_var('delete_link','');
	$phpgw->template->set_var('cancel_link','');
	$phpgw->template->set_var('form_link','<input type="image" name="bk_create" title="'
                                      . lang('Create bookmark') . '" src="' . $phpgw_info['server']['app_images']
                                      . '/save.gif" border="0" width="24" height="24">');

	$phpgw->common->phpgw_footer();
?>