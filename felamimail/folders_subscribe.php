<?php
   /**
    **  folders_subscribe.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Subscribe and unsubcribe form folders. 
    **  Called from folders.php
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array(
		'noheader'    => 'True',
		'nonavbar'    => 'True',
		"currentapp" => "felamimail"
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

   $location = get_location();
   if ($method == "sub") {
      for ($i=0; $i < count($mailbox); $i++) {
         $mailbox[$i] = trim($mailbox[$i]);
         sqimap_subscribe ($imapConnection, $mailbox[$i]);
	 header("Location: ".$phpgw->link('/felamimail/folders.php','success=subscribe'));
      }
   } else {
      for ($i=0; $i < count($mailbox); $i++) {
         $mailbox[$i] = trim($mailbox[$i]);
         sqimap_unsubscribe ($imapConnection, $mailbox[$i]);
	 header("Location: ".$phpgw->link('/felamimail/folders.php','success=unsubscribe'));
      }
   }
   sqimap_logout($imapConnection);
   $phpgw->common->phpgw_footer();
?>

