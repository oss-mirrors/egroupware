<?php
   /**
    **  options.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Displays the options page. Pulls from proper user preference files
    **  and config.php. Displays preferences as selected and other options.
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, "enable_nextmatchs_class" => True);
	include("../../header.inc.php");
	$mailbox = $MAILBOX;

	$phpgw->session->restore();

   if (!isset($strings_php))
      include("../inc/strings.php");
   if (!isset($config_php))
      include("../config/config.php");

	$key      = $phpgw_info['user']['preferences']['email']['passwd'];
	$username = $phpgw_info['user']['preferences']['email']['userid'];

   if (!isset($page_header_php))
      include("../inc/page_header.php");
   if (!isset($display_messages_php))
      include("../inc/display_messages.php");
   if (!isset($imap_php))
      include("../inc/imap.php");
   if (!isset($array_php))
      include("../inc/array.php");
   if (!isset($i18n_php))
      include("../inc/i18n.php");
   if (!isset($auth_php))
      include ("../inc/auth.php"); 

   if (isset($language)) {
      setcookie("felamimail_language", $language, time()+2592000);
      $felamimail_language = $language;
   }   

   include("../src/load_prefs.php");
   displayPageHeader($color, "None");
   #is_logged_in(); 
?>

<br>
<table width=95% align=center cellpadding=2 cellspacing=2 border=0>
<tr><td bgcolor="<?php echo $color[0] ?>">
   <center><b><?php echo lang("Options") ?></b></center>
</td></tr></table>

<?php
   if (isset($submit_personal)) {
      # Save personal information
      if (isset($full_name)) setPref($data_dir, $username, "full_name", sqStripSlashes($full_name));
      if (isset($email_address)) setPref($data_dir, $username, "email_address", sqStripSlashes($email_address));
      if (isset($reply_to)) setPref($data_dir, $username, "reply_to", sqStripSlashes($reply_to));  
      setPref($data_dir, $username, "use_signature", sqStripSlashes($usesignature));  
      if (isset($signature_edit)) setSig($data_dir, $username, sqStripSlashes($signature_edit)); 
      
      do_hook("options_personal_save");
      
      echo "<br><center><b>".lang("Successfully saved personal information!")."</b></center><br>";
   } else if (isset($submit_display)) {  
      # Save display preferences
      setPref($data_dir, $username, "chosen_theme", $chosentheme);
      setPref($data_dir, $username, "show_num", $shownum);
      setPref($data_dir, $username, "wrap_at", $wrapat);
      setPref($data_dir, $username, "editor_size", $editorsize);
      setPref($data_dir, $username, "left_refresh", $leftrefresh);
      setPref($data_dir, $username, "language", $language);
      setPref($data_dir, $username, 'location_of_bar', $folder_new_location);
      setPref($data_dir, $username, 'location_of_buttons', $button_new_location);
      setPref($data_dir, $username, "left_size", $leftsize);
      setPref($data_dir, $username, "use_javascript_addr_book", $javascript_abook);
    
      do_hook("options_display_save");

      echo "<br><center><b>".lang("Successfully saved display preferences!")."</b><br>";
      echo "<a href=\"../src/webmail.php\" target=_top>" . lang("Refresh Page") . "</a></center><br>";
   } else if (isset($submit_folder)) { 
      # Save folder preferences
      if ($trash != "none") {
         setPref($data_dir, $username, "move_to_trash", true);
         setPref($data_dir, $username, "trash_folder", $trash);
      } else {
         setPref($data_dir, $username, "move_to_trash", "0");
         setPref($data_dir, $username, "trash_folder", "none");
      }
      if ($sent != "none") {
         setPref($data_dir, $username, "move_to_sent", true);
         setPref($data_dir, $username, "sent_folder", $sent);
      } else {
         setPref($data_dir, $username, "move_to_sent", "0");
         setPref($data_dir, $username, "sent_folder", "none");
      } 
      setPref($data_dir, $username, "folder_prefix", $folderprefix);
      setPref($data_dir, $username, "unseen_notify", $unseennotify);
      setPref($data_dir, $username, "unseen_type", $unseentype);
      do_hook("options_folders_save");
      echo "<br><center><b>".lang("Successfully saved folder preferences!")."</b><br>";
      echo "<a href=\"../src/left_main.php\" target=left>" . lang("Refresh Folder List") . "</a></center><br>";
   } else {
      do_hook("options_save");
   }
   
?>


<table width=90% cellpadding=0 cellspacing=10 border=0 align=center>
<tr>
   <td width=50% valign=top>
      <table width=100% cellpadding=3 cellspacing=0 border=0>
         <tr>
            <td bgcolor="<?php echo $color[9] ?>">
               <a href="<?php print $phpgw->link('/felamimail/preferences_personal.php')?>"><?php echo lang("Personal Information"); ?></a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<?php echo $color[0] ?>">
               <?php echo lang("This contains personal information about yourself such as your name, your email address, etc.") ?>
            </td>
         </tr>   
      </table><br>
      <table width=100% cellpadding=3 cellspacing=0 border=0>
         <tr>
            <td bgcolor="<?php echo $color[9] ?>">
               <a href="<?php print $phpgw->link('/felamimail/preferences_highlight.php')?>"><?php echo lang("Message Highlighting"); ?></a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<?php echo $color[0] ?>">
               <?php echo lang("Based upon given criteria, incoming messages can have different background colors in the message list.  This helps to easily distinguish who the messages are from, especially for mailing lists.") ?>
            </td>
         </tr>   
      </table><br>
      <table width=100% cellpadding=3 cellspacing=0 border=0>
         <tr>
            <td bgcolor="<?php echo $color[9] ?>">
               <a href="<?php print $phpgw->link('/felamimail/preferences_index_order.php')?>"><?php echo lang("Index Order"); ?></a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<?php echo $color[0] ?>">
               <?php echo lang("The order of the message index can be rearanged and changed to contain the headers in any order you want.") ?>
            </td>
         </tr>   
      </table><br>
   </td>
   <td valign=top width=50%>
      <table width=100% cellpadding=3 cellspacing=0 border=0>
         <tr>
            <td bgcolor="<?php echo $color[9] ?>">
               <a href="<?php print $phpgw->link('/felamimail/preferences_display.php')?>"><?php echo lang("Display Preferences"); ?></a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<?php echo $color[0] ?>">
               <?php echo lang("You can change the way that SquirrelMail looks and displays information to you, such as the colors, the language, and other settings.") ?>
            </td>
         </tr>   
      </table><br>
      <table width=100% cellpadding=3 cellspacing=0 border=0>
         <tr>
            <td bgcolor="<?php echo $color[9] ?>">
               <a href="<?php print $phpgw->link('/felamimail/preferences_folder.php')?>"><?php echo lang("Folder Preferences"); ?></a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<?php echo $color[0] ?>">
               <?php echo lang("These settings change the way your folders are displayed and manipulated.") ?>
            </td>
         </tr>   
      </table><br>
   </td>
</tr>
</table>
   <?php
      do_hook("options_link_and_description")
   ?>
</body></html>
