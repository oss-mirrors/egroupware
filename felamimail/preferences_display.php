<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "felamimail","noheader" => True, "nonavbar" => True,
																"enable_nextmatchs_class" => True);

  include("../header.inc.php");

  if ($submit) {
     $phpgw->preferences->read_repository();
     
     $phpgw->preferences->add("felamimail","wrapat");
     $phpgw->preferences->add("felamimail","editorsize");
     $phpgw->preferences->add("felamimail","button_new_location");

     $phpgw->preferences->save_repository();

     Header("Location: " . $phpgw->link("/preferences/index.php"));
  }

  $phpgw->common->phpgw_header();
  echo parse_navbar();

  if ($totalerrors) {  
     echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
  }

	$tmpl = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	#$tmpl->set_unknowns('remove');

	$tmpl->set_file(array('body' => 'preferences_display.tpl'));
	
	$var = Array
	(
		'th_bg'			=> $phpgw_info["theme"]["th_bg"],
		'tr_color1'		=> $phpgw_info['theme']['row_on'],
		'tr_color2'		=> $phpgw_info['theme']['row_off'],
		'link'			=> $phpgw->link('/felamimail/preferences_display.php'),
		'wrapat'		=> $phpgw_info["user"]["preferences"]["felamimail"]["wrapat"],
		'editorsize'		=> $phpgw_info["user"]["preferences"]["felamimail"]["editorsize"],
		$phpgw_info["user"]["preferences"]["felamimail"]["button_new_location"].'_selected' => 'SELECTED'
	);
	
	$tmpl->set_var($var);
	
	$translations = Array
	(
		'lang_save'		=> lang('save'),
		'lang_wrap_at'		=> lang('Wrap incoming text at'),
		'lang_size_editor'	=> lang('Size of editor window'),
		'lang_location_button'	=> lang('Location of buttons when composing'),
		'lang_option_1'		=> lang('Before headers'),
		'lang_option_2'		=> lang('Between headers and message body'),
		'lang_option_3'		=> lang('After message body'),
		'lang_display_prefs'	=> lang('Display Preferences')
	);
	$tmpl->set_var($translations);

	$tmpl->pparse('out','body');
	
	
	
	
	$phpgw->common->phpgw_footer(); ?>
