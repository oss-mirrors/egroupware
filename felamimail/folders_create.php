<?php
   /**
    **  folders_create.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Creates folders on the IMAP server. 
    **  Called from folders.php
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array(
		"currentapp" => "felamimail", 
		'noheader'    => 'True',
		'nonavbar'    => 'True'
	);
	include("../header.inc.php");
	$mailbox = $MAILBOX;

	$phpgw->session->restore();

   if (!isset($strings_php))
      include(PHPGW_APP_ROOT . "/inc/strings.php");
   if (!isset($config_php))
      include(PHPGW_APP_ROOT . "/config/config.php");
   if (!isset($page_header_php))
      include(PHPGW_APP_ROOT . "/inc/page_header.php");
   if (!isset($imap_php))
      include(PHPGW_APP_ROOT . "/inc/imap.php");
   if (!isset($display_messages_php))
      include(PHPGW_APP_ROOT . "/inc/display_messages.php");

   include(PHPGW_APP_ROOT . "/src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   $dm = sqimap_get_delimiter($imapConnection);

   if (strpos($folder_name, "\"") || strpos($folder_name, "\\") ||
       strpos($folder_name, "'") || strpos($folder_name, "$dm")) {
		print "<html><body bgcolor=$color[4]>";
      plain_error_message(lang("Illegal folder name.  Please select a different name.")."<BR><A HREF=\"../src/folders.php\">".lang("Click here to go back")."</A>.", $color);
      sqimap_logout($imapConnection);
      exit;
   }

   if (isset($contain_subs) && $contain_subs == true)
      $folder_name = "$folder_name$dm";

   if ($folder_prefix && (substr($folder_prefix, -1) != $dm)) {
      $folder_prefix = $folder_prefix . $dm;
   }
   if ($folder_prefix && (substr($subfolder, 0, strlen($folder_prefix)) != $folder_prefix)){
      $subfolder_orig = $subfolder;
      $subfolder = $folder_prefix . $subfolder;
   } else {
      $subfolder_orig = $subfolder;
   }

   if ((trim($subfolder_orig) == "[ None ]") || (trim(sqStripSlashes($subfolder_orig)) == "[ None ]")) {
      sqimap_mailbox_create ($imapConnection, $folder_prefix.$folder_name, "");
   } else {
      sqimap_mailbox_create ($imapConnection, $subfolder.$dm.$folder_name, "");
   }
   fputs($imapConnection, "1 logout\n");

   $location = get_location();
   header ("Location: ".$phpgw->link('/felamimail/folders.php','success=create'));

   sqimap_logout($imapConnection);

   $phpgw->common->phpgw_footer();
?>

