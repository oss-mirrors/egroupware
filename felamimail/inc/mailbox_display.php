<?php

   /**
    **  mailbox_display.php
    **
    **  This contains functions that display mailbox information, such as the
    **  table row that has sender, date, subject, etc...
    **
    **  $Id$
    **/

   $mailbox_display_php = true;

   function printMessageInfo($imapConnection, $t, $i, $msg, $mailbox, $sort, $startMessage, $where, $what) {
      global $checkall, $color, $msort, $sent_folder, $message_highlight_list, $index_order,
             $phpgw, $phpgw_info;

#      print "msg: ".$msg['ID'].";<br>";

//mkorff@vpoint.com.br: from in sent_folder is to
      $senderName = sqimap_find_displayable_name( ($mailbox == $sent_folder) ? $msg['TO'] : $msg['FROM']);
      $urlMailbox = urlencode($mailbox);
      $subject = trim($msg['SUBJECT']);
      if ($subject == '')
         $subject = lang("(no subject)");

      echo "<TR>\n";

      if (isset($msg['FLAG_FLAGGED']) && $msg['FLAG_FLAGGED'] == true) 
      { 
         $flag = "<font color=$color[2]>"; 
         $flag_end = '</font>'; 
      }
      else
      {
         $flag = '';
         $flag_end = '';
      }
      if (!isset($msg['FLAG_SEEN']) || $msg['FLAG_SEEN'] == false) 
      { 
         $bold = '<b>'; 
         $bold_end = '</b>'; 
      }
      else
      {
         $bold = '';
         $bold_end = '';
      }
      if ($mailbox == $sent_folder) 
      { 
         $italic = '<i>'; 
         $italic_end = '</i>'; 
      }
      else
      {
         $italic = '';
         $italic_end = '';
      }
      if (isset($msg['FLAG_DELETED']) && $msg['FLAG_DELETED'])
      { 
         $fontstr = "<font color=\"$color[9]\">"; 
         $fontstr_end = '</font>'; 
      }
      else
      {
         $fontstr = '';
         $fontstr_end = '';
      }

      for ($i=0; $i < count($message_highlight_list); $i++) {
         if (trim($message_highlight_list[$i]['value']) != '') {
            if ($message_highlight_list[$i]['match_type'] == 'to_cc') {
               if (strpos('^^'.strtolower($msg['TO']), strtolower($message_highlight_list[$i]['value'])) || strpos('^^'.strtolower($msg['CC']), strtolower($message_highlight_list[$i]['value']))) {
                  $hlt_color = $message_highlight_list[$i]['color'];
                  continue;
               }
            } else if (strpos('^^'.strtolower($msg[strtoupper($message_highlight_list[$i]['match_type'])]),strtolower($message_highlight_list[$i]['value']))) {
               $hlt_color = $message_highlight_list[$i]['color'];
               continue;
            }
         }
      }

      if (!isset($hlt_color))
         $hlt_color = $color[4];

      if ($where && $what) {
         $search_stuff = '&where='.urlencode($where).'&what='.urlencode($what);
      }

      if ($checkall == 1) 
         $checked = ' checked';
      else
         $checked = '';
      
      for ($i=1; $i <= count($index_order); $i++) {
         switch ($index_order[$i]) {
            case 1: # checkbox
               echo "   <td width=1% bgcolor=$hlt_color align=center><input type=checkbox name=\"msg[$t]\" value=".$msg["ID"]."$checked></TD>\n";
               break;
            case 2: # from
               echo "   <td width=30% bgcolor=$hlt_color>$italic$bold$flag$fontstr$senderName$fontstr_end$flag_end$bold_end$italic_end</td>\n";
               break;
            case 3: # date
               echo "   <td nowrap width=1% bgcolor=$hlt_color><center>$bold$flag$fontstr".$msg["DATE_STRING"]."$fontstr_end$flag_end$bold_end</center></td>\n";
               break;
            case 4: # subject
               echo "   <td bgcolor=$hlt_color>$bold";
                   if (! isset($search_stuff)) { $search_stuff = ''; }
               echo "<a href=\"" . $phpgw->link('/felamimail/read_body.php',"mailbox=$urlMailbox&passed_id=".$msg["ID"]."&startMessage=$startMessage&show_more=0$search_stuff") . "\"";
               do_hook("subject_link");
               echo ">$flag";
               if (strlen($subject) > 55)
                   echo substr($subject, 0, 50) . '...';
               else
                      echo $subject;
               echo "$flag_end</a>$bold_end</td>\n";
               break;
            case 5: # flags
               $stuff = false;
               echo "   <td bgcolor=$hlt_color align=center width=1% nowrap><b><small>\n";
               if (isset($msg['FLAG_ANSWERED']) && 
                   $msg['FLAG_ANSWERED'] == true) {
                  echo "A\n";
                  $stuff = true;
               }
               if ($msg['TYPE0'] == 'multipart') {
                  echo "+\n";
                  $stuff = true;
               }
               if (ereg('(1|2)',substr($msg['PRIORITY'],0,1))) {
                  echo "<font color=$color[1]>!</font>\n";
                  $stuff = true;
               }
               if (isset($msg['FLAG_DELETED']) && $msg['FLAG_DELETED']) {
                  echo "<font color=\"$color[1]\">D</font>\n";
                  $stuff = true;
               }

               if (!$stuff) echo "&nbsp;\n";
               echo "</small></b></td>\n";
               break;
            case 6: # size
               echo "   <td bgcolor=$hlt_color width=1%>$bold$fontstr".show_readable_size($msg['SIZE'])."$fontstr_end$bold_end</td>\n";
               break;
         }
      }


      echo "</tr>\n";
   }

   	/**
   	 ** This function loops through a group of messages in the mailbox and shows them
   	 **/
   	function showMessagesForMailbox($imapConnection, $mailbox, $numMessages, $startMessage, $sort, $color,$show_num, $use_cache) 
	{
		global $msgs, $msort;
		global $sent_folder;
		global $mailboxStatus, $username, $key, $imapServerAddress, $imapPort;
		global $auto_expunge;
		global $phpgw, $phpgw_info;

		switch($sort)
		{
			case "0":
				$imapSort = SORTARRIVAL;
				$reverse  = 1;
				break;
			case "1":
				$imapSort = SORTARRIVAL;
				$reverse  = 0;
				break;
			case "2":
				$imapSort = SORTFROM;
				$reverse  = 1;
				break;
			case "3":
				$imapSort = SORTFROM;
				$reverse  = 0;
				break;
			case "4":
				$imapSort = SORTSUBJECT;
				$reverse  = 1;
				break;
			case "5":
				$imapSort = SORTSUBJECT;
				$reverse  = 0;
				break;
			default:
				$imapSort = SORTDATE;
				$reverse  = 1;
				break;
		}


		$caching = CreateObject('felamimail.bocaching',$imapServerAddress,$username,$mailbox);
		$transformdate = CreateObject('felamimail.transformdate');
		
		$mbox = imap_open ("{".$imapServerAddress.":$imapPort}$mailbox", $username, $key);
		$status = imap_status ($mbox, "{".$imapServerAddress.":$imapPort}$mailbox", SA_ALL);
		$cachedStatus = $caching->getImapStatus();
		
		// no data chached already?
		// get all message informations from the imap server for this folder
		if ($cachedStatus['uidnext'] == 0)
		{
			print "nix gecached!!<br>";
			print "current UIDnext :".$cachedStatus['uidnext']."<br>";
			print "new UIDnext :".$status->uidnext."<br>";
			for($i=1; $i<=$status->messages; $i++)
			{
				$messageData['uid'] = imap_uid($mbox, $i);
				$header = imap_headerinfo($mbox, $i);
				
				if (isset($header->date)) 
				{
					$header->date = ereg_replace('  ', ' ', $header->date);
					$tmpdate = explode(' ', trim($header->date));
				}
				else
				{
					$tmpdate = $date = array("","","","","","");
				}
				$messageData['date'] 		= date("Y-m-d H:i:s",$transformdate->getTimeStamp($tmpdate));
				
				$messageData['subject'] 	= $header->subject;
				$messageData['sender_name'] 	= $header->from[0]->personal;
				$messageData['sender_address'] 	= $header->from[0]->mailbox."@".$header->from[0]->host;
				$messageData['size'] 		= $header->Size;
				
				$caching->addToCache($messageData);
				
				unset($messageData);
			}
			$caching->updateImapStatus($status);
		}
		// update cache, but only add new emails
		elseif($status->uidnext != $cachedStatus['uidnext'])
		{
			print "found new messages<br>";
			print "new uidnext: ".$status->uidnext." old uidnext: ".$cachedStatus['uidnext']."<br>";
			$uidRange = $cachedStatus['uidnext'].":".$status->uidnext;
			print "$uidRange<br>";
			$newHeaders = imap_fetch_overview($mbox,$uidRange,FT_UID);
			for($i=0; $i<count($newHeaders); $i++)
			{
				$messageData['uid'] = $newHeaders[$i]->uid;
				$header = imap_headerinfo($mbox, $newHeaders[$i]->msgno);
				
				if (isset($header->date)) 
				{
					$header->date = ereg_replace('  ', ' ', $header->date);
					$tmpdate = explode(' ', trim($header->date));
				}
				else
				{
					$tmpdate = $date = array("","","","","","");
				}
				$messageData['date'] 		= date("Y-m-d H:i:s",$transformdate->getTimeStamp($tmpdate));
				
				$messageData['subject'] 	= $header->subject;
				$messageData['sender_name'] 	= $header->from[0]->personal;
				$messageData['sender_address'] 	= $header->from[0]->mailbox."@".$header->from[0]->host;
				$messageData['size'] 		= $header->Size;
				
				$caching->addToCache($messageData);
				
				unset($messageData);
			}
			$caching->updateImapStatus($status);
		}
		
		if ($startMessage+$show_num > $numMessages) $show_num=$numMessages-$startMessage+1;
		$displayHeaders = $caching->getHeaders($startMessage, $show_num, $sort);
		
		for ($i=0;$i<count($displayHeaders);$i++)
		{
			$header = imap_headerinfo($mbox, imap_msgno($mbox, $displayHeaders[$i]['uid']));
#			while(list($key, $value) = each($header))
#			{
#				print "$key: $value<br>";
#			}
				
			if (isset($header->date)) 
			{
				$header->date = ereg_replace('  ', ' ', $header->date);
				$tmpdate = explode(' ', trim($header->date));
			}
			else
			{
				$tmpdate = $date = array("","","","","","");
			}
			
			if ($header->Deleted == "D")	$msgs[$i]['FLAG_DELETED'] = true;
			if ($header->Answered == "A")	$msgs[$i]['FLAG_ANSWERED'] = true;
			if ($header->Unseen != "U")	$msgs[$i]['FLAG_SEEN'] = true;
			if ($header->Flagged == "F")	$msgs[$i]['FLAG_FLAGGED'] = true;
			
			$msgs[$i]['TIME_STAMP'] = getTimeStamp($tmpdate);
			$msgs[$i]['DATE_STRING'] = $phpgw->common->show_date($msgs[$i]['TIME_STAMP']);
			$msgs[$i]['ID'] = trim($header->Msgno);
			$msgs[$i]['FROM'] = decodeHeader($header->fromaddress);
			#print "$i: ";
			$msgs[$i]['SUBJECT'] = decodeHeader($header->subject);
			#print "<br>";
			$msgs[$i]['TO'] = decodeHeader($header->toaddress);
			$msgs[$i]['CC'] = decodeHeader($header->ccaddress);
			$msgs[$i]['SIZE'] = $header->Size;

			/*
			    i think these are not needed
			    
		            $messages[$j]['FROM-SORT'] = strtolower(sqimap_find_displayable_name(decodeHeader($from[$j])));
        		    $messages[$j]['SUBJECT-SORT'] = strtolower(decodeHeader($subject[$j]));
        		    $messages[$j]['PRIORITY'] = $priority[$j];
        		    $messages[$j]['TYPE0'] = $type[$j];
			*/
				
		}
		imap_close($mbox);
		
		
		displayMessageArray($imapConnection, $numMessages, $startMessage, $msgs, $msort, $mailbox, $sort, $color,$show_num);

	}

	

   // generic function to convert the msgs array into an HTML table
   function displayMessageArray($imapConnection, $numMessages, $startMessage, &$msgs, $msort, $mailbox, $sort, $color,$show_num) 
   {
      global $folder_prefix, $sent_folder, $imapServerAddress, $index_order, $real_endMessage,
             $real_startMessage, $checkall, $enablePHPGW, $phpgw, $phpgw_info;
      
      if ($startMessage + ($show_num - 1) < $numMessages) {
         $endMessage = $startMessage + ($show_num-1);
      } else {
         $endMessage = $numMessages;
      }

      if ($endMessage < $startMessage) {
         $startMessage = $startMessage - $show_num;
         if ($startMessage < 1)
            $startMessage = 1;
      }

      $nextGroup = $startMessage + $show_num;
      $prevGroup = $startMessage - $show_num;
      $urlMailbox = urlencode($mailbox);

      do_hook('mailbox_index_before');

      $Message = '';
      if ($startMessage < $endMessage) {
         $Message = lang("Viewing messages") ." <B>$startMessage</B> - <B>$endMessage</B> ($numMessages " . lang("total") . ")\n";
      } elseif ($startMessage == $endMessage) {
         $Message = lang("Viewing message") ." <B>$startMessage</B> ($numMessages " . lang("total") . ")\n";
      }

      $More = '';
      if ($sort == 6) {
         $use = 0;
      } else {
         $use = 1;
      }
      
      $target="TARGET=\"_self\"";

      if (($nextGroup <= $numMessages) && ($prevGroup >= 0)) {
         $More = "<A HREF=\"" . $phpgw->link('/felamimail/index.php',"use_mailbox_cache=$use&startMessage=$prevGroup&mailbox=$urlMailbox") . "\" $target>". lang("Previous") ."</A> | \n";
         $More .= "<A HREF=\"" . $phpgw->link('/felamimail/index.php',"use_mailbox_cache=$use&&startMessage=$nextGroup&mailbox=$urlMailbox") . "\" $target>". lang("Next") ."</A>\n";
      }
      elseif (($nextGroup > $numMessages) && ($prevGroup >= 0)) {
         $More = "<A HREF=\"" . $phpgw->link('/felamimail/index.php',"use_mailbox_cache=$use&startMessage=$prevGroup&mailbox=$urlMailbox") . "\" $target>". lang("Previous") ."</A> | \n";
         $More .= "<FONT COLOR=\"$color[9]\">".lang("Next")."</FONT>\n";
      }
      elseif (($nextGroup <= $numMessages) && ($prevGroup < 0)) {
         $More = "<FONT COLOR=\"$color[9]\">".lang("Previous")."</FONT> | \n";
         $More .= "<A HREF=\"" . $phpgw->link('/felamimail/index.php',"use_mailbox_cache=$use&startMessage=$nextGroup&mailbox=$urlMailbox") . "\" $target>". lang("Next") ."</A>\n";
      }

      if (! isset($msg))
          $msg = "";
      mail_message_listing_beginning($imapConnection,$phpgw->link('/felamimail/src/move_messages.php',"msg=$msg&mailbox=$urlMailbox&startMessage=$startMessage"),
          $mailbox, $sort, $Message, $More, $startMessage);

      $groupNum = $startMessage % ($show_num - 1);
      $real_startMessage = $startMessage;

      $endVar = $endMessage + 1;

      // loop through and display the info for each message.
      $t = 0; // $t is used for the checkbox number
      if ($numMessages == 0) { // if there's no messages in this folder
         echo "<TR><TD BGCOLOR=\"$color[4]\" COLSPAN=" . count($index_order);
         echo "><CENTER><BR><B>". lang("THIS FOLDER IS EMPTY") ."</B><BR>&nbsp;</CENTER></TD></TR>";
      } else if ($startMessage == $endMessage) { // if there's only one message in the box, handle it different.
//mkorff@vpoint.com.br: I do not see any reason for making this a special case
//mkorff@vpoint.com.br: $key should relate to startMessage ...
	 $key = $startMessage-1;
         printMessageInfo($imapConnection, $t, $i, $msgs[$key], $mailbox, $sort, $real_startMessage, 0, 0);
      } else {
      	for ($key=$startMessage-1;$key<($startMessage+$show_num)-1;$key++)
	{
#		print "k$key: ".$msgs[$key]['SUBJECT']."<br>";
		printMessageInfo($imapConnection, $t, $i, $msgs[$key], $mailbox, $sort, $real_startMessage, 0, 0);
		$t++;
	}
      }
      echo '</TABLE>';

      echo "</td></tr>\n";

      echo "<TR BGCOLOR=\"$color[4]\"><TD>";
      echo '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>';
      echo "$More</td><td align=right>\n";
      if (!$startMessage) $startMessage=1;
      if ( $checkall == '1')
         echo "\n<A HREF=\"" . $phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&startMessage=$real_startMessage&sort=$sort") . "\">" . lang("Unselect All") . "</A>\n";
      else
         echo "\n<A HREF=\"" . $phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&startMessage=$real_startMessage&sort=$sort&checkall=1") . "\">" . lang("Select All") . "</A>\n";

      echo '</td></tr></table>';
      echo '</td></tr>';
      echo '</table>'; /** End of message-list table */

      do_hook('mailbox_index_after');
   }

	/* Displays the standard message list header.
	* To finish the table, you need to do a "</table></table>";
	* $moveURL is the URL to submit the delete/move form to
	* $mailbox is the current mailbox
	* $sort is the current sorting method (-1 for no sorting available [searches])
	* $Message is a message that is centered on top of the list
	* $More is a second line that is left aligned
	*/
	function mail_message_listing_beginning($imapConnection, $moveURL,
		$mailbox = '', $sort = -1, $Message = '', $More = '', $startMessage = 1)
	{
		global $color, $index_order, $auto_expunge, $move_to_trash, $checkall, $sent_folder,
		$phpgw, $phpgw_info, $trash_folder;
		
		$urlMailbox = urlencode($mailbox);

		$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		#$t->set_unknowns('remove');
		
		$t->set_file(array('header' => 'view_main.tpl'));
		$t->set_block('header','main_navbar','main_navbar');

		$t->set_var('row_on',$phpgw_info['theme']['row_on']);
		$t->set_var('row_off',$phpgw_info['theme']['row_off']);
		$t->set_var('more',$More);
		if ($Message)
		{
			$t->set_var('message',$Message);
		}
		else
		{
			$t->set_var('message','&nbsp;');
		}
		
		if ($mailbox == $trash_folder && $move_to_trash)
		{
			$t->set_var('trash_link',
				'<a href="'.$phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&startMessage=$startMessage&sort=$sort&expunge=1").'">'.lang("empty trash").'</a>');
		}
		else
		{
			$t->set_var('trash_link','&nbsp;');
		}
		
		if ($checkall == '1')
		{
			$t->set_var('select_all_link',
				"<A HREF=\"" . $phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&startMessage=$startMessage&sort=$sort") . "\">" . lang("Unselect All") . "</A>");
		}
		else
		{
			$t->set_var('select_all_link',
				"<A HREF=\"" . $phpgw->link('/felamimail/index.php',"mailbox=$urlMailbox&startMessage=$startMessage&sort=$sort&checkall=1") . "\">" . lang("Select All") . "</A>");
		}
		
		$t->set_var('moveURL',$moveURL);
		$t->set_var('lang_move_selected_to',lang("move selected to"));

		$boxes = sqimap_mailbox_list($imapConnection);
		for ($i = 0; $i < count($boxes); $i++) 
		{
			if (!in_array("noselect", $boxes[$i]["flags"])) 
			{
				$box = $boxes[$i]['unformatted'];
				$box2 = replace_spaces($boxes[$i]['unformatted-disp']);
				$options_targetMailbox .= "<OPTION VALUE=\"$box\">$box2</option>\n";
			}
		}
		$t->set_var('options_target_mailbox',$options_targetMailbox);

		$t->set_var('lang_move',lang("move"));
		$t->set_var('lang_follow',lang("follow"));
		if (! $auto_expunge) 
		{
			$t->set_var('expunge',
				'<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="expungeButton" VALUE="'. lang("Expunge") .'">&nbsp;'. lang("mailbox") ."</SMALL></NOBR>&nbsp;&nbsp;");
		}
		else
		{
			$t->set_var('expunge','');
		}
		$t->set_var('image_path',PHPGW_IMAGES);
		$t->set_var('desc_read',lang("mark selected as read"));
		$t->set_var('desc_unread',lang("mark selected as unread"));
		$t->set_var('desc_important',lang("mark selected as flagged"));
		$t->set_var('desc_unimportant',lang("mark selected as unflagged"));
		$t->set_var('desc_deleted',lang("delete selected"));


		$t->pparse('out','main_navbar');

		// what to do with these hooks

		//echo "</TABLE>\n";
		// this is before the header line (date, subject, from, size)
		//do_hook('mailbox_form_before');
		//echo '</TD></TR>';

		echo "<TR><TD BGCOLOR=\"$color[0]\">";
		echo "<TABLE WIDTH=100% BORDER=0 CELLPADDING=2 CELLSPACING=1 BGCOLOR=\"$color[0]\">";
		echo "<TR BGCOLOR=\"$color[5]\" ALIGN=\"center\">";
		
		$urlMailbox=urlencode($mailbox);
		
		$up_pointer_gif = $GLOBALS['phpgw']->common->image('felamimail','up_pointer.gif');
		$down_pointer_gif = $GLOBALS['phpgw']->common->image('felamimail','down_pointer.gif');
		$sort_none_gif = $GLOBALS['phpgw']->common->image('felamimail','sort_none.gif');
		
		// Print the headers
		for ($i=1; $i <= count($index_order); $i++) 
		{
			switch ($index_order[$i]) 
			{
				case 1: # checkbox
				case 5: # flags
					echo '   <TD WIDTH="1%"><B>&nbsp;</B></TD>';
					break;
				
				case 2: # from
					if ($mailbox == $sent_folder)
						echo '   <TD WIDTH="30%"><B>'. lang("To") .'</B>';
					else
						echo '   <TD WIDTH="30%"><B>'. lang("From") .'</B>';
						
					if ($sort == 2)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=3&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$up_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 3)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=2&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$down_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=3&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$sort_none_gif\" BORDER=0></A></TD>\n";
					echo "</TD>";
					break;
					
				case 3: # date
					echo '   <TD nowrap WIDTH="1%"><B>'. lang("Date") .'</B>';
					if ($sort == 0)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=1&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$up_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=6&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$down_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 6)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=0&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$sort_none_gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=0&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$sort_none_gif\" BORDER=0></A></TD>\n";
					echo '</TD>';
					break;
					
				case 4: # subject
					echo '   <TD WIDTH=%><B>'. lang("Subject") ."</B>\n";
					if ($sort == 4)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=5&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$up_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 5)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=4&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$down_pointer_gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/index.php',"newsort=5&startMessage=1&mailbox=$urlMailbox") . "\"><IMG SRC=\"$sort_none_gif\" BORDER=0></A></TD>\n";
					echo "</TD>";
					break;
					
				case 6: # size
					echo '   <TD WIDTH="1%"><b>' . lang("Size")."</b></TD>\n";
					break;
			}
		}
		echo "</TR>\n";
	}

// new listing for search form

	/* Displays the standard message list header.
	* To finish the table, you need to do a "</table></table>";
	* $moveURL is the URL to submit the delete/move form to
	* $mailbox is the current mailbox
	* $sort is the current sorting method (-1 for no sorting available [searches])
	* $Message is a message that is centered on top of the list
	* $More is a second line that is left aligned
	*/
	function mail_message_search_listing_beginning($imapConnection, 
		$moveURL, $mailbox = '', $sort = -1, 
		$Message = '', $More = '', $startMessage = 1,
		$where = '', $what = '')
	{
		global $color, $index_order, $auto_expunge, $move_to_trash, $checkall, $sent_folder,
		$phpgw, $phpgw_info, $trash_folder;
		
		$urlMailbox = urlencode($mailbox);

		$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
		#$t->set_unknowns('remove');
		
		$t->set_file(array('header' => 'view_main.tpl'));
		$t->set_block('header','main_navbar','main_navbar');

		$t->set_var('row_on',$phpgw_info['theme']['row_on']);
		$t->set_var('row_off',$phpgw_info['theme']['row_off']);
		$t->set_var('more',$More);
		if ($Message)
		{
			$t->set_var('message',$Message);
		}
		else
		{
			$t->set_var('message','&nbsp;');
		}
		
		if ($mailbox == $trash_folder && $move_to_trash)
		{
			$t->set_var('trash_link',
				'<a href="'.$phpgw->link('/felamimail/search.php',"mailbox=$urlMailbox&what=".urlencode($what)."&where=".urlencode($where)."&sort=$sort&expunge=1").'">'.lang("empty trash").'</a>');
		}
		else
		{
			$t->set_var('trash_link','&nbsp;');
		}
		
		if ($checkall == '1')
		{
			$t->set_var('select_all_link',
				'<a HREF="'.$phpgw->link('/felamimail/search.php',"mailbox=$urlMailbox&what=".urlencode($what)."&where=".urlencode($where)."&sort=$sort").'">'.lang("Unselect All").'</a>');
		}
		else
		{
			$t->set_var('select_all_link',
				'<a HREF="'.$phpgw->link('/felamimail/search.php',"mailbox=$urlMailbox&what=".urlencode($what)."&where=".urlencode($where)."&sort=$sort&checkall=1").'">'.lang("Select All").'</a>');
		}
		
		$t->set_var('moveURL',$moveURL);
		$t->set_var('lang_move_selected_to',lang("move selected to"));

		$boxes = sqimap_mailbox_list($imapConnection);
		for ($i = 0; $i < count($boxes); $i++) 
		{
			if (!in_array("noselect", $boxes[$i]["flags"])) 
			{
				$box = $boxes[$i]['unformatted'];
				$box2 = replace_spaces($boxes[$i]['unformatted-disp']);
				$options_targetMailbox .= "<OPTION VALUE=\"$box\">$box2</option>\n";
			}
		}
		$t->set_var('options_target_mailbox',$options_targetMailbox);

		$t->set_var('lang_move',lang("move"));
		$t->set_var('lang_follow',lang("follow"));
		if (! $auto_expunge) 
		{
			$t->set_var('expunge',
				'<NOBR><SMALL><INPUT TYPE=SUBMIT NAME="expungeButton" VALUE="'. lang("Expunge") .'">&nbsp;'. lang("mailbox") ."</SMALL></NOBR>&nbsp;&nbsp;");
		}
		else
		{
			$t->set_var('expunge','');
		}
		
		$t->set_var('image_path',PHPGW_IMAGES);
		$t->set_var('desc_read',lang("mark selected as read"));
		$t->set_var('desc_unread',lang("mark selected as unread"));
		$t->set_var('desc_important',lang("mark selected as flagged"));
		$t->set_var('desc_unimportant',lang("mark selected as unflagged"));


		$t->pparse('out','main_navbar');

		// what to do with these hooks

		//echo "</TABLE>\n";
		// this is before the header line (date, subject, from, size)
		//do_hook('mailbox_form_before');
		//echo '</TD></TR>';

		echo "<TR><TD BGCOLOR=\"$color[0]\">";
		echo "<TABLE WIDTH=100% BORDER=0 CELLPADDING=2 CELLSPACING=1 BGCOLOR=\"$color[0]\">";
		echo "<TR BGCOLOR=\"$color[5]\" ALIGN=\"center\">";
		
		$urlMailbox=urlencode($mailbox);
		
		// Print the headers
		for ($i=1; $i <= count($index_order); $i++) 
		{
			switch ($index_order[$i]) 
			{
				case 1: # checkbox
				case 5: # flags
					echo '   <TD WIDTH="1%"><B>&nbsp;</B></TD>';
					break;
				
				case 2: # from
					if ($mailbox == $sent_folder)
						echo '   <TD WIDTH="30%"><B>'. lang("To") .'</B>';
					else
						echo '   <TD WIDTH="30%"><B>'. lang("From") .'</B>';
						
					if ($sort == 2)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=3&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/up_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 3)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=2&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/down_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=3&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/sort_none.gif\" BORDER=0></A></TD>\n";
					echo "</TD>";
					break;
					
				case 3: # date
					echo '   <TD nowrap WIDTH="1%"><B>'. lang("Date") .'</B>';
					if ($sort == 0)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=1&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/up_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=6&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/down_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 6)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=0&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/sort_none.gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=0&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/sort_none.gif\" BORDER=0></A></TD>\n";
					echo '</TD>';
					break;
					
				case 4: # subject
					echo '   <TD WIDTH=%><B>'. lang("Subject") ."</B>\n";
					if ($sort == 4)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=5&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/up_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort == 5)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=4&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/down_pointer.gif\" BORDER=0></A></TD>\n";
					elseif ($sort != -1)
						echo "   <A HREF=\"" . $phpgw->link('/felamimail/search.php',"newsort=5&startMessage=1&mailbox=$urlMailbox&where=$where&what=$what") . "\"><IMG SRC=\"images/sort_none.gif\" BORDER=0></A></TD>\n";
					echo "</TD>";
					break;
					
				case 6: # size
					echo '   <TD WIDTH="1%"><b>' . lang("Size")."</b></TD>\n";
					break;
			}
		}
		echo "</TR>\n";
	}
?>
