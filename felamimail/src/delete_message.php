<?php
   /**
    **  delete_message.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Deletes a meesage from the IMAP server 
    **
    **  $Id$
    **/

   $enablePHPGW = 1;

   	// store the value of $mailbox, because it will overwriten
   	$MAILBOX = $mailbox;
   	$phpgw_info["flags"] = array("currentapp" => "felamimail","noheader" => True, "nonavbar" => True);
   	include("../../header.inc.php");
   	$mailbox = $MAILBOX;


   if (!isset($strings_php))
      include(PHPGW_APP_ROOT . "/inc/strings.php");
   if (!isset($config_php))
      include(PHPGW_APP_ROOT . "/config/config.php");

	$key      = $phpgw_info['user']['preferences']['email']['passwd'];
	$username = $phpgw_info['user']['preferences']['email']['userid'];

   if (!isset($page_header_php))
      include(PHPGW_APP_ROOT . "/inc/page_header.php");
   if (!isset($display_message_php))
      include(PHPGW_APP_ROOT . "/inc/display_messages.php");
   if (!isset($imap_php))
      include(PHPGW_APP_ROOT . "/inc/imap.php");

   include(PHPGW_APP_ROOT . "/src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   sqimap_mailbox_select($imapConnection, $mailbox);

   sqimap_messages_delete($imapConnection, $message, $message, $mailbox);
   if ($auto_expunge)
      sqimap_mailbox_expunge($imapConnection, $mailbox, true);

	$location = $phpgw_info["server"]["webserver_url"] . "/felamimail/src";
	if ($where && $what)
	{
		header ("Location: " . $phpgw->link($location . "/search.php",
			"where=".urlencode($where)."&what=".urlencode($what)."&mailbox=".urlencode($mailbox)));
	}
	else   
	{
		header ("Location: " . $phpgw->link("/felamimail/index.php",
			"sort=$sort&startMessage=$startMessage&mailbox=".urlencode($mailbox)));
	}

   sqimap_logout($imapConnection);
   if ($enablePHPGW)
   {
        $sessionData->save();
        $phpgw->common->phpgw_footer();
   }  
?>
