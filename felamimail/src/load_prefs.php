<?php
   /**
    **  load_prefs.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Loads preferences from the $username.pref file used by almost
    **  every other script in the source directory and alswhere.
    **
    **  $Id$
    **/

   if (!isset($config_php))
   {
      include(PHPGW_APP_ROOT . '/config/config.php');
   }

   if (!isset($prefs_php))
   {
      include(PHPGW_APP_ROOT . '/inc/prefs.php');
   }

   if (!isset($plugin_php))
   {
      include(PHPGW_APP_ROOT . '/inc/plugin.php');
   }
      
   $load_prefs_php = true;
   checkForPrefs($data_dir, $username);

	// Until its merged in with our theme support (jengo)
	$color[0]   = "#DCDCDC"; // (light gray)     TitleBar
	$color[1]   = "#800000"; // (red)
	$color[2]   = "#CC0000"; // (light red)      Warning/Error Messages
	$color[3]   = "#A0B8C8"; // (green-blue)     Left Bar Background
	$color[4]   = "#FFFFFF"; // (white)          Normal Background
	$color[5]   = "#FFFFCC"; // (light yellow)   Table Headers
	$color[6]   = "#000000"; // (black)          Text on left bar
	$color[7]   = "#0000CC"; // (blue)           Links
	$color[8]   = "#000000"; // (black)          Normal text
	$color[9]   = "#ABABAB"; // (mid-gray)       Darker version of #0
	$color[10]  = "#666666"; // (dark gray)      Darker version of #9
	$color[11]  = "#770000"; // (dark red)       Special Folders color
 

//   $use_javascript_addr_book = getPref($data_dir, $username, "use_javascript_addr_book");
   if ($use_javascript_addr_book == "")
      $use_javascript_addr_book = $default_use_javascript_addr_book;

   
   /** Load the user's sent folder preferences **/
   $move_to_sent = $phpgw_info["user"]["preferences"]["felamimail"]["move_to_sent"];
   if ($move_to_sent == "")
      $move_to_sent = $default_move_to_sent;

   /** Load the user's trash folder preferences **/
   $move_to_trash = $phpgw_info["user"]["preferences"]["felamimail"]["move_to_trash"];
   if ($move_to_trash == "")
      $move_to_trash = $default_move_to_trash;


   $unseen_type = $phpgw_info["user"]["preferences"]["felamimail"]["unseen_type"];
   if ($default_unseen_type == "")
      $default_unseen_type = 1;
   if ($unseen_type == "")
      $unseen_type = $default_unseen_type;

   $unseen_notify = $phpgw_info["user"]["preferences"]["felamimail"]["unseen_notify"];
   if ($default_unseen_notify == "")
      $default_unseen_notify = 2;
   if ($unseen_notify == "")
      $unseen_notify = $default_unseen_notify;


//   $folder_prefix = getPref($data_dir, $username, "folder_prefix");
   if ($folder_prefix == "")
      $folder_prefix = $default_folder_prefix;

	/** Load special folders **/
	$new_trash_folder = $phpgw_info["user"]["preferences"]["felamimail"]["trash_folder"];
	if (($new_trash_folder == "") && ($move_to_trash == true))
		$trash_folder = $folder_prefix . $trash_folder;
	else
		$trash_folder = $new_trash_folder;

	/** Load special folders **/
	$new_sent_folder = $phpgw_info["user"]["preferences"]["felamimail"]["sent_folder"];
	if (($new_sent_folder == "") && ($move_to_sent == true))
		$sent_folder = $folder_prefix . $sent_folder;
	else
		$sent_folder = $new_sent_folder;

   $show_num = $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
   if ($show_num == "")
      $show_num = 25;
   
   $wrap_at = $phpgw_info["user"]["preferences"]["felamimail"]["wrapat"];
   if ($wrap_at == "")
      $wrap_at = 86;
   if ($wrap_at < 15)
      $wrap_at = 15;

//   $left_size = getPref($data_dir, $username, "left_size");
   if ($left_size == "") {
      if (isset($default_left_size))
         $left_size = $default_left_size;
      else  
         $left_size = 200;
   }      

   $editor_size = $phpgw_info["user"]["preferences"]["felamimail"]["editorsize"];
   if ($editor_size == "")
      $editor_size = 76;

   $use_signature = $phpgw_info["user"]["preferences"]["felamimail"]["usesignature"];
   if ($use_signature == "")
      $use_signature = false;

//   $left_refresh = getPref($data_dir, $username, "left_refresh");
   if ($left_refresh == "")
      $left_refresh = false;

//   $sort = getPref($data_dir, $username, "sort");
   if ($sort == "")
      $sort = 6;
   
   /** Load up the Signature file **/
   if ($use_signature == true) {
      $signature_abs = $signature = $phpgw_info["user"]["preferences"]["felamimail"]["signature"];
   } else {
      $signature_abs = $phpgw_info["user"]["preferences"]["felamimail"]["signature"];
   }

   //  highlightX comes in with the form: name,color,header,value
#   for ($i=0; $hlt = getPref($data_dir, $username, "highlight$i"); $i++) {
   for ($i=0; $hlt = $phpgw_info["user"]["preferences"]["felamimail"]["highlight$i"]; $i++) {
      $ary = explode(",", $hlt);
      $message_highlight_list[$i]["name"] = $ary[0]; 
      $message_highlight_list[$i]["color"] = $ary[1];
      $message_highlight_list[$i]["value"] = $ary[2];
      $message_highlight_list[$i]["match_type"] = $ary[3];
   }

   #index order lets you change the order of the message index
   #$order = getPref($data_dir, $username, "order1");
   #for ($i=1; $order; $i++) {
   #   $index_order[$i] = $order;
   #   $order = getPref($data_dir, $username, "order".($i+1));
   #}
   
   $i=1;
   while ($phpgw_info["user"]["preferences"]["felamimail"]["order$i"])
   {
   	$index_order[$i] = $phpgw_info["user"]["preferences"]["felamimail"]["order$i"];
   	$i++;
   }
   if (!isset($index_order)) {
      $index_order[1] = 1;
      $index_order[2] = 2;
      $index_order[3] = 3;
      $index_order[4] = 5;
      $index_order[5] = 4;
   }
   
//	not needed with phpgw
//   $location_of_bar = getPref($data_dir, $username, 'location_of_bar');
   if ($location_of_bar == '')
       $location_of_bar = 'left';
       
   $location_of_buttons = $phpgw_info["user"]["preferences"]["felamimail"]["button_new_location"];
   if ($location_of_buttons == '')
       $location_of_buttons = 'between';

   do_hook("loading_prefs");

?>
