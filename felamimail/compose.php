<?php
   /** 
    ** compose.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    ** This code sends a mail.
    **
    ** There are 3 modes of operation:
    **  - Start new mail
    **  - Add an attachment
    **  - Send mail
    **
    ** $Id$
    **/

   $enablePHPGW = 1;


	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	if(isset($send))
	{
   		$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, 
   			"enable_nextmatchs_class" => True,"noheader" => True, "nonavbar" => True);
	}
	else
	{
   		$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, 
   			"enable_nextmatchs_class" => True);
	}
	include("../header.inc.php");

	$mailbox = $MAILBOX;

	$phpgw->session->restore();


	if (!isset($strings_php))
	{
		include(PHPGW_APP_ROOT . '/inc/strings.php');
	}

	if (!isset($config_php))
	{
		include(PHPGW_APP_ROOT . '/config/config.php');
	}

	if (!isset($page_header_php))
	{
		include(PHPGW_APP_ROOT . '/inc/page_header.php');
	}

	if (!isset($imap_php))
	{
		include(PHPGW_APP_ROOT . '/inc/imap.php');
	}

	if (!isset($date_php))
	{
		include(PHPGW_APP_ROOT . '/inc/date.php');
	}

	if (!isset($mime_php))
	{
		include(PHPGW_APP_ROOT . '/inc/mime.php');
	}

	if (!isset($smtp_php))
	{
		include(PHPGW_APP_ROOT . '/inc/smtp.php');
	}

	if (!isset($display_messages_php))
	{
		include(PHPGW_APP_ROOT . '/inc/display_messages.php');
	}

	if (!isset($auth_php))
	{
		include (PHPGW_APP_ROOT . '/inc/auth.php');
	}

	if (!isset($plugin_php))
	{
		include (PHPGW_APP_ROOT . '/inc/plugin.php');
	}

	include(PHPGW_APP_ROOT . '/src/load_prefs.php');

	if (!isset($attachments))
		$attachments = array();

	// This function is used when not sending or adding attachments
	function newMail () 
	{
		global $forward_id, $imapConnection, $msg, $ent_num, $body_ary, $body, $query_RB_addr,
			$reply_id, $send_to, $send_to_cc, $mailbox, $send_to_bcc, $editor_size;
			
		$send_to = sqStripSlashes(decodeHeader($send_to));
		$send_to_cc = sqStripSlashes(decodeHeader($send_to_cc));
		$send_to_bcc = sqStripSlashes(decodeHeader($send_to_bcc));
		
		if ($forward_id)
			$id = $forward_id;
		elseif ($reply_id)
			$id = $reply_id;

		if (isset($id) && !isset($query_RB_addr)) 
		{
			sqimap_mailbox_select($imapConnection, $mailbox);
			$message = sqimap_get_message($imapConnection, $id, $mailbox);
			$orig_header = $message->header;
			if ($ent_num)
				$message = getEntity($message, $ent_num);
				
			if ($message->header->type0 == "text" || $message->header->type1 == "message") 
			{
				if ($ent_num)
					$body = decodeBody(mime_fetch_body($imapConnection, $id, $ent_num), $message->header->encoding);
				else
					$body = decodeBody(mime_fetch_body($imapConnection, $id, 1), $message->header->encoding);
			} 
			else 
			{
				$body = "";
			}
			
			if ($message->header->type1 == "html")
				$body = strip_tags($body);
				
			sqUnWordWrap($body);
			$body_ary = explode("\n", $body);
			$i = count($body_ary) - 1;
			while (isset($body_ary[$i]) && ereg("^[>\s]*$", $body_ary[$i])) 
			{
				unset($body_ary[$i]);
				$i --;
			}
			
			$body = "";
			for ($i=0; $i < count($body_ary); $i++) 
			{
				if (! $forward_id)
				{
					if (ereg('^[\s>]+', $body_ary[$i]))
					{
						$body_ary[$i] = '>' . $body_ary[$i];
					}
					else
					{
						$body_ary[$i] = '> ' . $body_ary[$i];
					}
				}
				sqWordWrap($body_ary[$i], $editor_size - 1);
				$body .= $body_ary[$i] . "\n";
				$body_ary[$i] = '';
			}
			
			if ($forward_id)
			{
				$bodyTop =  "-------- " . lang("Original Message") . " --------\n\n";
				$bodyTop .= lang("Subject") . ": " . $orig_header->subject . "\n";
				$bodyTop .= lang("From") . ": " . $orig_header->from . "\n";
				$bodyTop .= lang("To") . ": " . $orig_header->to[0] . "\n";
				if (count($orig_header->to) > 1) 
				{
					for ($x=1; $x < count($orig_header->to); $x++) 
					{
						$bodyTop .= "         " . $orig_header->to[$x] . "\n";
					}
				}
				$bodyTop .= "\n";
				
				$bodyBottom  = "\n-------- " . lang("Original Message") . " --------\n";
				$body = $bodyTop . $body . $bodyBottom;
			}
			return;
		}
		
		if (!$send_to) 
		{
			$send_to = sqimap_find_email($send_to);
		}
		
		/** This formats a CC string if they hit "reply all" **/
		if ($send_to_cc != "") 
		{
			$send_to_cc = ereg_replace( '"[^"]*"', "", $send_to_cc);
			$send_to_cc = ereg_replace(";", ",", $send_to_cc);
			$sendcc = explode(",", $send_to_cc);
			$send_to_cc = "";
			
			for ($i = 0; $i < count($sendcc); $i++) 
			{
				$sendcc[$i] = trim($sendcc[$i]);
				if ($sendcc[$i] == "")
					continue;
				
				$sendcc[$i] = sqimap_find_email($sendcc[$i]);
				$whofrom = sqimap_find_displayable_name($msg["HEADER"]["FROM"]);
				$whoreplyto = sqimap_find_email($msg["HEADER"]["REPLYTO"]);
				
				if ((strtolower(trim($sendcc[$i])) != strtolower(trim($whofrom))) &&
					(strtolower(trim($sendcc[$i])) != strtolower(trim($whoreplyto))) &&
					(trim($sendcc[$i]) != "")) 
				{
					$send_to_cc .= trim($sendcc[$i]) . ", ";
				}
			}
			$send_to_cc = trim($send_to_cc);
			if (substr($send_to_cc, -1) == ",") 
			{
				$send_to_cc = substr($send_to_cc, 0, strlen($send_to_cc) - 1);
			}
		}
	} // function newMail()

   function getAttachments($message) {
      global $mailbox, $attachments, $attachment_dir, $imapConnection,
             $ent_num, $forward_id;
//mkorff@vpoint.com.br: added phpgw, $message
      global $phpgw, $message;
      
      if (!$message) {
           sqimap_mailbox_select($imapConnection, $mailbox);
           $message = sqimap_get_message($imapConnection, $forward_id, $mailbox); }
      
      if (!$message->entities) {
      if ($message->header->entity_id != $ent_num) {
      $filename = decodeHeader($message->header->filename);
      
      if ($filename == "")
              $filename = "untitled-".$message->header->entity_id;
      
      $localfilename = md5($filename.", $REMOTE_IP, REMOTE_PORT, $UNIQUE_ID, extra-stuff here" . time());
      
//mkorff@vpoint.com.br: added separator
	$sep = $phpgw->common->filesystem_separator();
        // Write File Info
//mkorff@vpoint.com.br: added separator ($sep)
        $fp = fopen ($attachment_dir.$sep.$localfilename.".info", "w");
        fputs ($fp, strtolower($message->header->type0)."/".strtolower($message->header->type1)."\n".$filename."\n");
        fclose ($fp);

        // Write Attachment to file
//mkorff@vpoint.com.br: added separator ($sep)
        $fp = fopen ($attachment_dir.$sep.$localfilename, "w");
      fputs ($fp, decodeBody(mime_fetch_body($imapConnection, $forward_id, $message->header->entity_id), $message->header->encoding));
      fclose ($fp);
      
      $attachments[$localfilename] = $filename;
      
      }
      } else {
              for ($i = 0; $i < count($message->entities); $i++) {
              getAttachments($message->entities[$i]);
              }       
      }
      return;
      }       

	function showInputForm () 
	{
		global $send_to, $send_to_cc, $reply_subj, $forward_subj, $body,
			$passed_body, $color, $use_signature, $signature, $editor_size,
			$attachments, $subject, $newmail, $use_javascript_addr_book,
			$send_to_bcc, $reply_id, $mailbox, $from_htmladdr_search,
			$location_of_buttons, $phpgw, $phpgw_info;
		
		$subject = sqStripSlashes(decodeHeader($subject));
		$reply_subj = decodeHeader($reply_subj);
		$forward_subj = decodeHeader($forward_subj);
		$body = sqStripSlashes($body);
		
		/* RB if ($use_javascript_addr_book) 
		{
			echo "\n<SCRIPT LANGUAGE=JavaScript><!--\n";
			echo "function open_abook() { \n";
			echo "  var nwin = window.open(\"addrbook_popup.php\",\"abookpopup\",";
			echo "\"width=670,height=300,resizable=yes,scrollbars=yes\");\n";
			echo "  if((!nwin.opener) && (document.windows != null))\n";
			echo "    nwin.opener = document.windows;\n";
			echo "}\n";
			echo "// --></SCRIPT>\n\n";
		} */

		
      echo "\n<FORM name=compose action=\"" . $phpgw->link('/felamimail/compose.php') . "\" METHOD=POST ENCTYPE=\"multipart/form-data\"";
      do_hook("compose_form");
	  echo ">\n";
      if ($reply_id) {
         echo "<input type=hidden name=reply_id value=$reply_id>\n";
      }                 
      printf("<INPUT TYPE=hidden NAME=mailbox VALUE=\"%s\">\n", htmlspecialchars($mailbox));
      echo "<TABLE WIDTH=\"100%\" ALIGN=center CELLSPACING=0 BORDER=0>\n";

      if ($location_of_buttons == 'top') showComposeButtonRow();

      echo "   <TR>\n";
      echo "      <TD BGCOLOR=\"$color[4]\" ALIGN=RIGHT>\n";
      echo lang("to").":";
      echo "      </TD><TD BGCOLOR=\"$color[4]\">\n";
      printf("         <INPUT TYPE=text NAME=\"send_to\" VALUE=\"%s\" SIZE=60><BR>\n",
             htmlspecialchars($send_to));
      echo "      </TD>\n";
      echo "   </TR>\n";
      echo "   <TR>\n";
      echo "      <TD BGCOLOR=\"$color[4]\" ALIGN=RIGHT>\n";
      echo lang("cc").":";
      echo "      </TD><TD BGCOLOR=\"$color[4]\" ALIGN=LEFT>\n";
      printf("         <INPUT TYPE=text NAME=\"send_to_cc\" SIZE=60 VALUE=\"%s\"><BR>\n",
             htmlspecialchars($send_to_cc));
      echo "      </TD>\n";
      echo "   </TR>\n";
      echo "   <TR>\n";
      echo "      <TD BGCOLOR=\"$color[4]\" ALIGN=RIGHT>\n";
      echo lang("BCC").":";
      echo "      </TD><TD BGCOLOR=\"$color[4]\" ALIGN=LEFT>\n";
      printf("         <INPUT TYPE=text NAME=\"send_to_bcc\" VALUE=\"%s\" SIZE=60><BR>\n",
             htmlspecialchars($send_to_bcc));
      echo "</TD></TR>\n";

      echo "   <TR>\n";
      echo "      <TD BGCOLOR=\"$color[4]\" ALIGN=RIGHT>\n";
      echo lang("Subject").":";
      echo "      </TD><TD BGCOLOR=\"$color[4]\" ALIGN=LEFT>\n";
      if ($reply_subj) {
         $reply_subj = str_replace("\"", "'", $reply_subj);
         $reply_subj = sqStripSlashes($reply_subj);
         $reply_subj = trim($reply_subj);
         if (substr(strtolower($reply_subj), 0, 3) != "re:")
            $reply_subj = "Re: $reply_subj";
         printf("         <INPUT TYPE=text NAME=subject SIZE=60 VALUE=\"%s\">",
                htmlspecialchars($reply_subj));
      } else if ($forward_subj) {
         $forward_subj = str_replace("\"", "'", $forward_subj);
         $forward_subj = sqStripSlashes($forward_subj);
         $forward_subj = trim($forward_subj);
         if ((substr(strtolower($forward_subj), 0, 4) != "fwd:") &&
             (substr(strtolower($forward_subj), 0, 5) != "[fwd:") &&
             (substr(strtolower($forward_subj), 0, 6) != "[ fwd:"))
            $forward_subj = "[Fwd: $forward_subj]";
         printf("         <INPUT TYPE=text NAME=subject SIZE=60 VALUE=\"%s\">",
                htmlspecialchars($forward_subj));
      } else {
          printf("         <INPUT TYPE=text NAME=subject SIZE=60 VALUE=\"%s\">",
                htmlspecialchars($subject));
      }
      echo "</td></tr>\n\n";

      if ($location_of_buttons == 'between') showComposeButtonRow();

      echo "   <TR>\n";
      echo "      <TD BGCOLOR=\"$color[4]\" COLSPAN=2>\n";
      echo "         &nbsp;&nbsp;<TEXTAREA NAME=body ROWS=20 COLS=\"$editor_size\" WRAP=HARD>";
      if ($reply_subj) echo "> ".htmlspecialchars($send_to)." ".lang("wrote").":\n>\n";
      echo htmlspecialchars($body);
      if ($use_signature == true && $newmail == true && !isset($from_htmladdr_search)) {
         echo "\n\n-- \n" . htmlspecialchars($signature);
      }
      echo "</TEXTAREA><BR>\n";
      echo "      </TD>\n";
      echo "   </TR>\n";

      if ($location_of_buttons == 'bottom') 
         showComposeButtonRow();
      else {
         echo "   <TR><TD>&nbsp;</TD><TD ALIGN=LEFT><INPUT TYPE=SUBMIT NAME=send VALUE=\"".lang("Send")."\"></TD></TR>\n";
      }
      
      // This code is for attachments
      echo "   <tr>\n";
      echo "     <TD BGCOLOR=\"$color[0]\" VALIGN=TOP ALIGN=RIGHT>\n";
      echo "      <SMALL><BR></SMALL>".lang("Attach").":";
      echo "      </td><td ALIGN=left BGCOLOR=\"$color[0]\">\n";
      echo "      <INPUT NAME=\"attachfile\" SIZE=48 TYPE=\"file\">\n";
      echo "      &nbsp;&nbsp;<input type=\"submit\" name=\"attach\"";
      echo " value=\"" . lang("Add") ."\">\n";
      echo "     </td>\n";
      echo "   </tr>\n";
//echo "Attachments:". count($attachments) . "<P>";
      if (isset($attachments) && count($attachments)>0) {
         echo "<tr><td bgcolor=\"$color[0]\" align=right>\n";
         echo "&nbsp;";
         echo "</td><td align=left bgcolor=\"$color[0]\">";
         while (list($localname, $remotename) = each($attachments)) {
            echo "<input type=\"checkbox\" name=\"delete[]\" value=\"$localname\">\n";
            echo "$remotename <input type=\"hidden\" name=\"attachments[$localname]\" value=\"$remotename\"><br>\n";
         }
         
         echo "<input type=\"submit\" name=\"do_delete\" value=\"".lang("Delete selected attachments")."\">\n";
         echo "</td></tr>";
      }
      // End of attachment code

      echo "</TABLE>\n";
      echo "</FORM>";
      do_hook("compose_bottom");
   }
   
	$sb2 = CreateObject('phpgwapi.sbox2');
	
	function showComposeButtonRow() 
	{
		global $use_javascript_addr_book;
		echo "   <TR><td>\n   </td><td>\n";
		
		/* RB if ($use_javascript_addr_book) 
		{
			echo "      <SCRIPT LANGUAGE=JavaScript><!--\n document.write(\"";
			echo "         <input type=button value=\\\"".lang("Addresses")."\\\" onclick='javascript:open_abook();'>\");";
			echo "         // --></SCRIPT><NOSCRIPT>\n";
			echo "         <input type=submit name=\"html_addr_search\" value=\"".lang("Addresses")."\">";
			echo "      </NOSCRIPT>\n";
		} 
		else 
		{
			echo "      <input type=submit name=\"html_addr_search\" value=\"".lang("Addresses")."\">";
		} */
		
		global $query_RB_addr,$sb2;
		$arr = $sb2->getEmail('RB_addr',0,$query_RB_addr);
		while (list($k,$val) = each($arr)) 
		{
			if ($k != 'RB_addr_nojs')
				echo $val." &nbsp; \n";
		}
		if ($query_RB_addr && isset($arr['RB_addr_OK'])) 
		{	
			// yes we have selectbox with addresses 
			echo '<input type=submit name="RB_addr_To" value="To"> &nbsp; '."\n";
			echo '<input type=submit name="RB_addr_Cc" value="Cc"> &nbsp; '."\n";		
		} 
		else
		{
			echo $arr['RB_addr_nojs']." &nbsp; \n";
		}
		
		echo "\n    <INPUT TYPE=SUBMIT NAME=send VALUE=\"". lang("Send") . "\">\n";
		
		do_hook("compose_button_row");
		
		echo "   </TD>\n";
		echo "   </TR>\n\n";
	}

   function showSentForm () {
      echo "<BR><BR><BR><CENTER><B>Message Sent!</B><BR><BR>";
      echo "You will be automatically forwarded.<BR>If not, <A HREF=\"index.php\">click here</A>";
      echo "</CENTER>";
   }

   function checkInput ($show) {
      /** I implemented the $show variable because the error messages
          were getting sent before the page header.  So, I check once
          using $show=false, and then when i'm ready to display the
          error message, show=true **/
      global $send_to, $show, $color;

      if ($send_to == "") {
         if ($show)
            plain_error_message(lang("You have not filled in the \"To:\" field."), $color);
         return false;
      }
      return true;
   } // function checkInput()


   // True if FAILURE
   function saveAttachedFiles()
   {
      global $HTTP_POST_FILES, $attachments, $phpgw, $phpgw_info;
      global $failed, $mailbox, $send_to, $send_to_cc, $send_to_bcc, $subject, $body, $attachfile, 
		$imapConnection, $boxes, $send, $reply_id, $color, $id_RB_addr, $RB_addr_To, $RB_addr_Cc,
		$attach, $do_delete, $delete, $smtpErrors, $ent_num, $translated_setup;
      
      $localfilename = GenerateRandomString(32, '', 7);
      $sep = $phpgw->common->filesystem_separator();

      $HTTP_POST_FILES['attachfile']['tmp_name'];
      $phpgw_info["server"]["temp_dir"] . $sep . $localfilename;

      if (!@rename($HTTP_POST_FILES['attachfile']['tmp_name'], $phpgw_info["server"]["temp_dir"] . $sep . $localfilename)) {
         if (!copy($HTTP_POST_FILES['attachfile']['tmp_name'], $phpgw_info["server"]["temp_dir"] . $sep . $localfilename)) {
            return true;
         }
      }
      
      if (!isset($failed) || !$failed) {
         // Write information about the file
         $fp = fopen ($phpgw_info["server"]["temp_dir"] . $sep . $localfilename.".info", "w");
         fputs ($fp, $HTTP_POST_FILES['attachfile']['type']."\n".$HTTP_POST_FILES['attachfile']['name']."\n");
         fclose ($fp);

         $attachments[$localfilename] = $HTTP_POST_FILES['attachfile']['name'];
      }
    }

	if (isset($mailbox))     $mailbox = trim($mailbox);
	if (isset($send_to))     $send_to = trim($send_to);
	if (isset($send_to_cc))  $send_to_cc = trim($send_to_cc);
	if (isset($send_to_bcc)) $send_to_bcc = trim($send_to_bcc);
	if (isset($subject))     $subject = trim($subject);
	if (isset($body))        $body = trim($body);
	if (isset($attachfile))  $attachfile = trim($attachfile);

	if (!isset($mailbox) || $mailbox == "" || ($mailbox == "None"))
		$mailbox = "INBOX";
	
	if (isset($HTTP_POST_VARS["query_RB_addr"]))
	{
		// the signature is already included in the body at this stage
		$use_signature = false;
	}

	$debugLK = 0;
	

	//mkorff@vpoint.com.br: there are cases where boxes is not an array
	if (!isset($imapConnection))  {
		// this should be set already lars kneschke 2001-09-09
		//$key      = $phpgw_info['user']['preferences']['email']['passwd'];
		//$username = $phpgw_info['user']['preferences']['email']['userid'];
		$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
	}
	if (!isset($boxes)) {
		$boxes = sqimap_mailbox_list($imapConnection);
		$phpgw->session->register("boxes");
	}

	if(isset($send)) 
	{
		if ($debugLK) print '$send set<br>';
		
		if (isset($HTTP_POST_FILES['attachfile']) &&
			$HTTP_POST_FILES['attachfile']['tmp_name'] &&
			$HTTP_POST_FILES['attachfile']['tmp_name'] != 'none' &&
			$HTTP_POST_FILES['attachfile']['name'])
		{
			$AttachFailure = saveAttachedFiles();
		}
		if (checkInput(false) && !isset($AttachFailure)) 
		{
			$urlMailbox = urlencode (trim($mailbox));
			if (! isset($reply_id)) $reply_id = 0;
			sendMessage($send_to, $send_to_cc, $send_to_bcc, $subject, $body, $reply_id);
			header ("Location: " . $phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&sort=$sort&startMessage=1"));
		} 
		else 
		{
			//$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
			
			displayPageHeader($color, $mailbox);
			
			if ($AttachFailure)
				plain_error_message(lang("Could not move/copy file. File not attached"), $color);
				
			checkInput(true);
			
			showInputForm();
			//sqimap_logout($imapConnection);
		}
	} 
	else if ($id_RB_addr && ($RB_addr_To || $RB_addr_Cc)) 
	{
		if ($debugLK) print '$id_RB_addr && ($RB_addr_To || $RB_addr_Cc<br>';
		//$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
		displayPageHeader($color, $mailbox);
		
		$send_to = sqStripSlashes($send_to);
		$send_to_cc = sqStripSlashes($send_to_cc);
		$send_to_bcc = sqStripSlashes($send_to_bcc);
      
		if ($RB_addr_To) 
		{
			if ($send_to) $send_to .= ', ';
			$send_to .= $sb2->addr2email($id_RB_addr);
		} 
		else 
		{		
			if ($send_to_cc) $send_to_cc .= ', ';
			$send_to_cc .= $sb2->addr2email($id_RB_addr);
		}
		// the signature is already included in the body at this stage
		$use_signature = false;
		showInputForm();
		//sqimap_logout($imapConnection);
	} 
	else if (isset($attach)) 
	{
		if (saveAttachedFiles())
			plain_error_message(lang("Could not move/copy file. File not attached"), $color);
		//$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
		displayPageHeader($color, $mailbox);
		showInputForm();
		//sqimap_logout($imapConnection);
	} 
	else if (isset($do_delete)) 
	{
		//$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
		displayPageHeader($color, $mailbox);
		
		$sep = $phpgw->common->filesystem_separator();
		while (list($lkey, $localname) = each($delete)) 
		{
			unset ($attachments[$localname]);
			unlink ($phpgw_info["server"]["temp_dir"] . $sep . $localname);
			unlink ($phpgw_info["server"]["temp_dir"] . $sep . $localname.".info");
		}
		
		showInputForm();
		//sqimap_logout($imapConnection); 
	} 
	else if (isset($smtpErrors)) 
	{
		//$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
		displayPageHeader($color, $mailbox);
		
		$newmail = true;
		if ($forward_id && $ent_num)  getAttachments(0);
		
		newMail();
		showInputForm();
		//sqimap_logout($imapConnection);
	} 
	else 
	{
		if ($debugLK) print 'default<br>';
		$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
		displayPageHeader($color, $mailbox);
		
		$newmail = true;
		
		if (isset($forward_id) && isset($ent_num))  getAttachments(0);
		
		newMail();
		showInputForm();
		sqimap_logout($imapConnection);
	}
/*
//mkorff@vpoint.com.br: inserted three next lines
   if (!isset($translated_setup) && !function_exists('translate_read_form') )
      include(PHPGW_APP_ROOT . "/inc/translate_setup.php");
   translate_read_form();
*/

   	$phpgw->session->save();
	$phpgw->common->phpgw_footer();
?>
