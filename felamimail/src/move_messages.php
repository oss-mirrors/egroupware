<?php
   /**
    **  move_messages.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Enables message moving between folders on the IMAP server.
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
      include("../config/config.php");

	$key      = $phpgw_info['user']['preferences']['email']['passwd'];
	$username = $phpgw_info['user']['preferences']['email']['userid'];

   if (!isset($page_header_php))
      include(PHPGW_APP_ROOT . "/inc/page_header.php");
   if (!isset($display_messages_php))
      include(PHPGW_APP_ROOT . "/inc/display_messages.php");
   if (!isset($imap_php))
      include(PHPGW_APP_ROOT . "/inc/imap.php");

   include("../src/load_prefs.php");

   function putSelectedMessagesIntoString($msg) {
      $j = 0;
      $i = 0;
      $firstLoop = true;
      
      // If they have selected nothing msg is size one still, but will
      // be an infinite loop because we never increment j. so check to
      // see if msg[0] is set or not to fix this.
      while (($j < count($msg)) && ($msg[0])) {
         if ($msg[$i]) {
            if ($firstLoop != true)
               $selectedMessages .= "&";
            else
               $firstLoop = false;

            $selectedMessages .= "selMsg[$j]=$msg[$i]";
            
            $j++;
         }
         $i++;
      }
   }

	$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
	sqimap_mailbox_select($imapConnection, $mailbox);

	if ($mark_read_x) $messageAction = "seen";
	if ($mark_unread_x) $messageAction = "recent";
	if ($mark_flagged_x) $messageAction = "flag";
	if ($mark_unflagged_x) $messageAction = "unflag";
	if ($mark_deleted_x) $messageAction = "delete";

	if(isset($messageAction) && $messageAction != "-1")
	{
		// lets check to see if they selected any messages
		if (is_array($msg) == 1) 
		{
			// Removes \Deleted flag from selected messages
			$j = 0;
			$i = 0;
			
			// If they have selected nothing msg is size one still, but will be an infinite
			//    loop because we never increment j.  so check to see if msg[0] is set or not to fix this.
			while ($j < count($msg)) 
			{
				if ($msg[$i]) 
				{
					switch($messageAction)
					{
						case "seen":
							sqimap_messages_remove_flag ($imapConnection, $msg[$i], $msg[$i], "Recent");
							sqimap_messages_flag($imapConnection, $msg[$i], $msg[$i], "Seen");
							break;
						case "recent":
							sqimap_messages_remove_flag ($imapConnection, $msg[$i], $msg[$i], "Seen");
							sqimap_messages_flag($imapConnection, $msg[$i], $msg[$i], "Recent");
							break;
						case "flag":
							sqimap_messages_flag($imapConnection, $msg[$i], $msg[$i], "Flagged");
							break;
						case "unflag":
							sqimap_messages_remove_flag ($imapConnection, $msg[$i], $msg[$i], "Flagged");
							break;
						case "delete":
							sqimap_messages_delete($imapConnection, $msg[$i], $msg[$i], $mailbox);
							break;
					}
					$j++;
				}
				$i++;
			}
			
			if ($messageAction == "delete")
			{
				if ($auto_expunge) 
				{
					sqimap_mailbox_expunge($imapConnection, $mailbox, true);
				}
			}
			
			
//mkorff@vpoint.com.br: search.php lives in /felamimail, not in /felamimail/src, 
			$location = "/felamimail";
			
			if ($where && $what)
				header ("Location: " . $phpgw->link($location . "/search.php","mailbox=".urlencode($mailbox)."&what=".urlencode($what)."&where=".urlencode($where)));
			else
				header ("Location: " . $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=". urlencode($mailbox)));
		} 
		else 
		{
			$phpgw->common->phpgw_header();
			//displayPageHeader($color, $mailbox);
			echo parse_navbar();
			error_message(lang("No messages were selected."), $mailbox, $sort, $startMessage, $color);
			$phpgw->common->phpgw_footer();
			$phpgw->common->phpgw_exit();
		}
	}

	// expunge-on-demand if user isn't using move_to_trash or auto_expunge
	elseif(isset($expungeButton)) 
	{
		sqimap_mailbox_expunge($imapConnection, $mailbox, true);
//mkorff@vpoint.com.br: search.php lives in /felamimail, not in /felamimail/src, 
		$location = "/felamimail";
		if ($where && $what)
			header ("Location: " . $phpgw->link($location . "/search.php","mailbox=".urlencode($mailbox)."&what=".urlencode($what)."&where=".urlencode($where)));
		else
			header ("Location: " . $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=". urlencode($mailbox)));
	}
		
	// undelete messages if user isn't using move_to_trash or auto_expunge
	elseif(isset($undeleteButton)) 
	{
		if (is_array($msg) == 1) 
		{
			// Removes \Deleted flag from selected messages
			$j = 0;
			$i = 0;
			
			// If they have selected nothing msg is size one still, but will be an infinite
			//    loop because we never increment j.  so check to see if msg[0] is set or not to fix this.
			while ($j < count($msg)) 
			{
				if ($msg[$i]) 
				{
					sqimap_messages_remove_flag ($imapConnection, $msg[$i], $msg[$i], "Deleted");
					$j++;
				}
				$i++;
			}
			
//mkorff@vpoint.com.br: search.php lives in /felamimail, not in /felamimail/src, 
			$location = "/felamimail";
			
			if ($where && $what)
				header ("Location: " . $phpgw->link($location . "/search.php","mailbox=".urlencode($mailbox)."&what=".urlencode($what)."&where=".urlencode($where)));
			else
				header ("Location: " . $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=". urlencode($mailbox)));
		} 
		else 
		{
			displayPageHeader($color, $mailbox);
			error_message(lang("No messages were selected."), $mailbox, $sort, $startMessage, $color);
		}
	}
	elseif (isset($moveButton) || $HTTP_POST_VARS["targetMailbox"] != "-1")
	{    
		// Move messages
		// lets check to see if they selected any messages
		if (is_array($msg) == 1) 
		{
			$j = 0;
			$i = 0;
			
			// If they have selected nothing msg is size one still, but will be an infinite
			//    loop because we never increment j.  so check to see if msg[0] is set or not to fix this.
			while ($j < count($msg)) 
			{
				if (isset($msg[$i])) 
				{
				
					/** check if they would like to move it to the trash folder or not */
					sqimap_messages_copy($imapConnection, $msg[$i], $msg[$i], $HTTP_POST_VARS["targetMailbox"]);
					sqimap_messages_flag($imapConnection, $msg[$i], $msg[$i], "Deleted");
					$j++;
				}
				$i++;
			}
			if ($auto_expunge == true)
				sqimap_mailbox_expunge($imapConnection, $mailbox, true);
				
//mkorff@vpoint.com.br: search.php lives in /felamimail, not in /felamimail/src, 
			$location = "/felamimail";
			if (isset($where) && isset($what))
//mkorff@vpoint.com.br: search.php lives JUST in /felamimail,
				header ("Location: " . $phpgw->link($location . "/search.php","mailbox=".urlencode($mailbox)."&what=".urlencode($what)."&where=".urlencode($where)));
			elseif (isset($followCheckBox))
				header ("Location: " . $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=". urlencode($HTTP_POST_VARS["targetMailbox"])));
			else
				header ("Location: " . $phpgw->link("/felamimail/index.php","sort=$sort&startMessage=$startMessage&mailbox=". urlencode($mailbox)));
		} 
		else 
		{
			$phpgw->common->phpgw_header();
			//displayPageHeader($color, $mailbox);
			echo parse_navbar();
			error_message(lang("No messages were selected."), $mailbox, $sort, $startMessage, $color);
			$phpgw->common->phpgw_footer();
			$phpgw->common->phpgw_exit();
		}
	}
	
	// Log out this session
	sqimap_logout($imapConnection);
?>
