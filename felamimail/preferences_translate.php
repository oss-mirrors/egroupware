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

	$phpgw_info["flags"] = array(
		"currentapp" => "felamimail",
		"noheader" => True, 
		"nonavbar" => True,
		"enable_nextmatchs_class" => True
	);

	include("../header.inc.php");

	if ($submit) {
		$phpgw->preferences->read_repository();
		$phpgw->preferences->add("felamimail","translate_server", $translate_server);
		$phpgw->preferences->add("felamimail","translate_location", $translate_location);
		$phpgw->preferences->add("felamimail","translate_show_read", $translate_show_read);
		$phpgw->preferences->add("felamimail","translate_show_send", $translate_show_send);
		$phpgw->preferences->add("felamimail","translate_same_window", $translate_same_window);
		$phpgw->preferences->save_repository();
		Header("Location: " . $phpgw->link("/preferences/index.php"));
	}

	if (!isset($config_php))
		include(PHPGW_APP_ROOT . '/config/config.php');

	// preps
        $translate_server = $phpgw_info["user"]["preferences"]["felamimail"]["translate_server"];
        $translate_location = $phpgw_info["user"]["preferences"]["felamimail"]["translate_location"];
	$translate_show_read = $phpgw_info["user"]["preferences"]["felamimail"]["translate_show_read"];
	$translate_show_send = $phpgw_info["user"]["preferences"]["felamimail"]["translate_show_send"];
	$translate_same_window = $phpgw_info["user"]["preferences"]["felamimail"]["translate_same_window"];

	$translate_intro = '<ul>
	   <li><b>Babelfish</b> -
	       13 language pairs,
	       maximum of 1000 characters translated,
	       powered by Systran
	       [ <a href="http://babelfish.altavista.com/" 
	       target="_blank">Babelfish</a> ]</li>
	   <li><b>Go.com</b> -
	       10 language pairs,
	       maximum of 25 kilobytes translated,
	       powered by Systran
	       [ <a href="http://translator.go.com/"
	       target="_blank">Translator.Go.com</a> ]</li>
	   <li><b>Dictionary.com</b> -
	       12 language pairs,
	       no known limits,
	       powered by Systran
	       [ <a href="http://www.dictionary.com/translate"
	       target="_blank">Dictionary.com</a> ]</li>
	   <li><b>InterTran</b> -
	       767 language pairs,
	       no known limits,
	       powered by Translation Experts\'s InterTran
	       [ <a href="http://www.tranexp.com/"
	       target="_blank">Translation Experts</a> ]</li>
	   <li><b>GPLTrans</b> -
	       8 language pairs,
	       no known limits,
	       powered by GPLTrans (free, open source)
	       [ <a href="http://www.translator.cx/"
	       target="_blank">GPLTrans</a> ]</li>
	   <li><b>Free Translation</b> -
	       10 language pairs,
	       10 KBytes,
 	       rapid translations,
	       [ <a href="http://www.freetranslation.com/"
	       target="_blank">Free Translation</a> ]</li>
	</ul>';

        function TServerSelect($value, $Desc, $sel)
        {
	       $val .= '<option value="' . $value . '"';
	       if ($sel == $value) $val .= ' SELECTED';
	       $val .= '>' . $Desc . "</option>\n";
	       return $val;
	}
        $str = '<select name="translate_server">';
        $str .= TServerSelect('babelfish', 	'Babelfish', 	$translate_server);
	$str .= TServerSelect('go', 		'Go.com', 	$translate_server);
	$str .= TServerSelect('dictionary', 	'Dictionary.com', $translate_server);
	$str .= TServerSelect('intertran', 	'Intertran', 	$translate_server);
	$str .= TServerSelect('gpltrans', 	'GPLTrans', 	$translate_server);
	$str .= TServerSelect('freetrans', 	'FreeTrans', 	$translate_server);
        $str .= '</select>';
        $disp_translate_servers = $str;

        function TLocSelect($value, $Desc, $sel)
        {
	       $val .= '<option value="' . $value . '"';
	       if ($sel == $value) $val .= ' SELECTED';
	       $val .= '>' . $Desc . "</option>\n";
	       return $val;
	}
        $str = '<select name="translate_location">';
        $str .= TLocSelect('left',   lang('to the left'),   $translate_location);
	$str .= TLocSelect('center', lang('in the center'), $translate_location);
	$str .= TLocSelect('right',  lang('to the right'),  $translate_location);
        $str .= '</select>';
        $disp_translate_locations = $str;

	$str = '<input type=checkbox name="translate_show_read"';
	if ($translate_show_read) $str .=" CHECKED";
	$str .= '> When reading';
        $disp_translate_show_read = $str;

	$str = '<input type=checkbox name="translate_show_send"';
	if ($translate_show_send) $str .=" CHECKED";
	$str .= '> When composing';
        $disp_translate_show_send = $str;

	$str = '<input type=checkbox name="translate_same_window"';
        if ($translate_same_window) $str .=" CHECKED";
	$str .= '> Translate inside the SquirrelMail frames';
        $disp_translate_same_window = $str;


	// display and template 
	$phpgw->common->phpgw_header();
	echo parse_navbar();

	if ($totalerrors) {  
		echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
	}

	$tmpl = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	#$tmpl->set_unknowns('remove');

	$tmpl->set_file(array('body' => 'preferences_translate.tpl'));

	// var
	$var = Array
	(
		'th_bg'			=> $phpgw_info["theme"]["th_bg"],
		'tr_color1'		=> $phpgw_info['theme']['row_on'],
		'tr_color2'		=> $phpgw_info['theme']['row_off'],
		'link'			=> $phpgw->link('/felamimail/preferences_translate.php'),
		'translate_intro' 	=> $translate_intro,
		'translate_servers' 	=> $disp_translate_servers,	 	
		'translate_locations' 	=> $disp_translate_locations,	 	
		'translate_show_read' 	=> $disp_translate_show_read,	 	
		'translate_show_send' 	=> $disp_translate_show_send,	 	
		'translate_same_window'	=> $disp_translate_same_window,	 	
	);

	$tmpl->set_var($var);

	// translations
	$translations = Array
	(
		'lang_translate_prefs'		=> lang('Translation Preferences'),
		'lang_translation_server'	=> lang('Translation server'),
		'lang_translation_location'	=> lang('Translation location'),
		'lang_translation_show_read'	=> lang('For received mail'),
		'lang_translation_show_send'	=> lang('For mail to be send - not functional yet'),
		'lang_translation_same_window'	=> lang('Same Window - not functional yet'),
		'lang_save'			=> lang('save'),
	);
	$tmpl->set_var($translations);

	$tmpl->pparse('out','body');

	$phpgw->common->phpgw_footer(); 
?>
