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

// store the value of $mailbox, because it will overwriten
        $MAILBOX = $mailbox;
        
	$phpgw_info["flags"] = array("currentapp" => "felamimail","noheader" => True, "nonavbar" => True,
		"enable_nextmatchs_class" => True, "enable_network_class" => True);
		
	include("../header.inc.php");
	
	$mailbox = $MAILBOX;
	
	
	

	
	if ($submit) 
	{
		$phpgw->preferences->read_repository();

		$phpgw->preferences->add("felamimail","deleteOptions", $GLOBALS['HTTP_POST_VARS']['deleteOptions']);
		
		$phpgw->preferences->add("felamimail","trashFolder", $GLOBALS['HTTP_POST_VARS']['trashFolder']);
               		
		if ($sent != "none") 
		{
         		$phpgw->preferences->add("felamimail","move_to_sent", "true");
                  	$phpgw->preferences->add("felamimail","sent_folder", $sent);
		} 
		else 
		{
			$phpgw->preferences->add("felamimail","move_to_sent", "0");
			$phpgw->preferences->add("felamimail","sent_folder", "none");
		}
		
		$phpgw->preferences->add("felamimail","unseennotify");
		$phpgw->preferences->add("felamimail","unseentype");


		$phpgw->preferences->save_repository();
		
		Header("Location: " . $phpgw->link("/preferences/index.php"));
	}

	$phpgw->common->phpgw_header();
	echo parse_navbar();
	   if (!isset($strings_php))
	      include(PHPGW_APP_ROOT . "/inc/strings.php");
	   if (!isset($config_php))
	      include(PHPGW_APP_ROOT . '/config/config.php');
	   if (!isset($page_header_php))
	      include(PHPGW_APP_ROOT . "/inc/page_header.php");
	   if (!isset($display_messages_php))
	      include(PHPGW_APP_ROOT . "/inc/display_messages.php");
	   if (!isset($imap_php))
	      include(PHPGW_APP_ROOT . "/inc/imap.php");
	   if (!isset($array_php))
	      include(PHPGW_APP_ROOT . "/inc/array.php");
	   if (!isset($i18n_php))
	      include(PHPGW_APP_ROOT . "/inc/i18n.php");
	   if (!isset($plugin_php))
	      include(PHPGW_APP_ROOT . "/inc/plugin.php");

	$load_prefs_php=1;
	
	if ($totalerrors) 
	{
		echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
	}
	
	$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
	$boxes = sqimap_mailbox_list($imapConnection);
	sqimap_logout($imapConnection);
	
	
	
	if ($phpgw_info["user"]["preferences"]["felamimail"]["deleteOptions"] != 'move_to_trash')
		$trashOptions = "<option value=none>" . lang("Don't use Trash");
	else
		$trashOptions = "<option value=none selected>" . lang("Don't use Trash");
		
	for ($i = 0; $i < count($boxes); $i++) 
	{
		$use_folder = true;
		if (strtolower($boxes[$i]["unformatted"]) == "inbox") 
		{
			$use_folder = false;
		}
		
		if ($use_folder == true) 
		{
			$box = $boxes[$i]["unformatted-dm"];
			$box2 = replace_spaces($boxes[$i]["formatted"]);
			if (($boxes[$i]["unformatted"] == $phpgw_info["user"]["preferences"]["felamimail"]["trashFolder"]) &&
				$phpgw_info["user"]["preferences"]["felamimail"]["deleteOptions"] == 'move_to_trash')
				$trashOptions .= "         <OPTION SELECTED VALUE=\"$box\">$box2\n";
			else
				$trashOptions .= "         <OPTION VALUE=\"$box\">$box2\n";
		}
	}
	
	
	
	if ($phpgw_info["user"]["preferences"]["felamimail"]["move_to_sent"] == true)
		$sentOptions = "<option value=none>" . lang("Don't use Sent");
	else
		$sentOptions = "<option value=none selected>" . lang("Don't use Sent");

	for ($i = 0; $i < count($boxes); $i++) 
	{
		$use_folder = true;
		if (strtolower($boxes[$i]["unformatted"]) == "inbox") 
		{
			$use_folder = false;
		}
		if ($use_folder == true) 
		{	
			$box = $boxes[$i]["unformatted-dm"];
			$box2 = replace_spaces($boxes[$i]["formatted"]);
			if (($boxes[$i]["unformatted"] == $phpgw_info["user"]["preferences"]["felamimail"]["sent_folder"]) &&
				($phpgw_info["user"]["preferences"]["felamimail"]["move_to_sent"] == true))
				$sentOptions .= "         <OPTION SELECTED VALUE=\"$box\">$box2\n";
			else
				$sentOptions .= "         <OPTION VALUE=\"$box\">$box2\n";
		}
	}


         
	$tmpl = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	#$tmpl->set_unknowns('remove');

	$tmpl->set_file(array('body' => 'preferences_folder.tpl'));
	
	$var = Array
	(
		'th_bg'			=> $phpgw_info["theme"]["th_bg"],
		'tr_color1'		=> $phpgw_info['theme']['row_on'],
		'tr_color2'		=> $phpgw_info['theme']['row_off'],
		'link'			=> $phpgw->link('/felamimail/preferences_folder.php'),
		'wrapat'		=> $phpgw_info["user"]["preferences"]["felamimail"]["wrapat"],
		'trash_options'		=> $trashOptions,
		'sent_options'		=> $sentOptions,
		"notify".$phpgw_info["user"]["preferences"]["felamimail"]["unseennotify"]."_checked"	=> 'checked',
		"type".$phpgw_info["user"]["preferences"]["felamimail"]["unseentype"]."_checked"	=> 'checked',
		$phpgw_info["user"]["preferences"]["felamimail"]["deleteOptions"]."_selected"	=> 'selected'
	);
	
	$tmpl->set_var($var);
	
	$translations = Array
	(
		'lang_save'		=> lang('save'),
		'lang_folder_prefs'	=> lang('Folder Preferences'),
		'lang_when_deleting'	=> lang('when deleting messages'),
		'lang_move_to_trash'	=> lang('move to trash'),
		'lang_mark_as_deleted'	=> lang('mark as deleted'),
		'lang_remove_immediately'	=> lang('remove immediately')
	);
	$tmpl->set_var($translations);

	$tmpl->pparse('out','body');
	
	
	
	
	$phpgw->common->phpgw_footer(); 
?>
