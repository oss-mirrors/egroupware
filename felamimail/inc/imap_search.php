<?php 
/******************************************************************
 ** IMAP SEARCH ROUTIES
 ** $Id$
 *****************************************************************/
	if (!isset($imap_php))
	{
		include(PHPGW_APP_ROOT . '/inc/imap.php');
	}

	if (!isset($date_php))
	{
		include(PHPGW_APP_ROOT . '/inc/date.php');
	}

	if (!isset($array_php))
	{
		include(PHPGW_APP_ROOT . '/inc/array.php');
	}

	if (!isset($mailbox_display_php))
	{
		include(PHPGW_APP_ROOT . '/inc/mailbox_display.php');
	}

	if (!isset($mime_php))
	{
		include(PHPGW_APP_ROOT . '/inc/mime.php');
	}

   $imap_search_php = true;

function sqimap_search($imapConnection,$search_where,$search_what,$mailbox,$color) {
   global $msgs, $message_highlight_list, $felamimail_language, $languages, $index_order;
//mkorff@vpoint.com.br: added global phpgw;
   global $phpgw;
   $urlMailbox = urlencode($mailbox);
   
   # Construct the Search QuERY
   
   if (isset($languages[$felamimail_language]["CHARSET"]) && $languages[$felamimail_language]["CHARSET"]) {
      $ss = "a001 SEARCH CHARSET ".$languages[$felamimail_language]["CHARSET"]." ALL $search_where \"$search_what\"\r\n";
   } else {
      $ss = "a001 SEARCH ALL $search_where \"$search_what\"\r\n";
   }
   fputs($imapConnection,$ss);

   # Read Data Back From IMAP
   $readin = sqimap_read_data ($imapConnection, "a001", false, $result, $message);
   if (isset($languages[$felamimail_language]["CHARSET"]) && strtolower($result) == "no") { 
      $ss = "a001 SEARCH CHARSET \"US-ASCII\" ALL $search_where \"$search_what\"\r\n";
      fputs ($imapConnection, $ss);
      $readin = sqimap_read_data ($imapConnection, "a001", true, $result, $message);
   }
   unset($messagelist); $msgs=""; $c = 0;

   #Keep going till we find the SEARCH responce
   while ($c < count($readin)) {

      #Check to see if a SEARCH Responce was recived
      if (substr($readin[$c],0,9) == "* SEARCH ")
         $messagelist = explode(" ",substr($readin[$c],9));
      else if (isset($errors))
         $errors = $errors.$readin[$c];
      else
         $errors = $readin[$c];
      $c++;
   }

   #If nothing is found * SEARCH should be the first error else echo errors
   if (isset($errors) && strstr($errors,"* SEARCH")) {
      echo "<br><CENTER>No Messages Found</CENTER>";
      return;
   } else if (isset($errors)) {
      echo "<!-- ".$errors." -->";
   }

   # HACKED CODED FROM ANOTHER FUNCTION, Could Probably dump this and mondify 
   # exsitising code with a search true/false varible.


   global $sent_folder;
   for ($q = 0; $q < count($messagelist); $q++) {
      $messagelist[$q] = trim($messagelist[$q]);
      if ($mailbox == $sent_folder)
         $hdr = sqimap_get_small_header ($imapConnection, $messagelist[$q], true);
      else
         $hdr = sqimap_get_small_header ($imapConnection, $messagelist[$q], false);
						
         $from[$q] = $hdr->from;
         $date[$q] = $hdr->date;
         $subject[$q] = $hdr->subject;
         $to[$q] = $hdr->to;
         $priority[$q] = $hdr->priority;
         $cc[$q] = $hdr->cc;
		 $size[$q] = $hdr->size;
		 $type[$q] = $hdr->type0;
         $id[$q] = $messagelist[$q];
         $flags[$q] = sqimap_get_flags ($imapConnection, $messagelist[$q]);
      }

      $j = 0;
      while ($j < count($messagelist)) {
         $date[$j] = ereg_replace("  ", " ", $date[$j]);
         $tmpdate = explode(" ", trim($date[$j]));

         $messages[$j]["TIME_STAMP"] = getTimeStamp($tmpdate);
         $messages[$j]["DATE_STRING"] = getDateString($messages[$j]["TIME_STAMP"]);
         $messages[$j]["ID"] = $id[$j];
         $messages[$j]["FROM"] = decodeHeader($from[$j]);
         $messages[$j]["FROM-SORT"] = strtolower(sqimap_find_displayable_name(decodeHeader($from[$j])));
         $messages[$j]["SUBJECT"] = decodeHeader($subject[$j]);
         $messages[$j]["SUBJECT-SORT"] = strtolower(decodeHeader($subject[$j]));
         $messages[$j]["TO"] = decodeHeader($to[$j]);
         $messages[$j]["PRIORITY"] = $priority[$j];
         $messages[$j]["CC"] = $cc[$j];
		 $messages[$j]["SIZE"] = $size[$j];
		 $messages[$j]["TYPE0"] = $type[$j];

         $num = 0;
         while ($num < count($flags[$j])) {
            if ($flags[$j][$num] == "Deleted") {
               $messages[$j]["FLAG_DELETED"] = true;
            }
            else if ($flags[$j][$num] == "Answered") {
               $messages[$j]["FLAG_ANSWERED"] = true;
            }
            else if ($flags[$j][$num] == "Seen") {
               $messages[$j]["FLAG_SEEN"] = true;
            }
            else if ($flags[$j][$num] == "Flagged") {
               $messages[$j]["FLAG_FLAGGED"] = true;
            }
            $num++;
         }
         $j++;
      }

      /** Find and remove the ones that are deleted */
      $i = 0;
      $j = 0;
      while ($j < count($messagelist)) {
         if (isset($messages[$j]["FLAG_DELETED"]) && $messages[$j]["FLAG_DELETED"] == true) {
            $j++;
            continue;
         }
         $msgs[$i] = $messages[$j];

         $i++;
         $j++;
      }
      $numMessages = $i;

      // There's gotta be messages in the array for it to sort them.

      if (count($messagelist) > 0) {
         $j=0;
		 if (!isset ($msg)) { $msg = ""; }
//mkorff@vpoint.com.br: new function mail_message_SEARCH_listing_beginning
         mail_message_search_listing_beginning($imapConnection, 
		$phpgw->link('/felamimail/src/move_messages.php',
			"msg=$msg&mailbox=$urlMailbox&where="
			.urlencode($search_where)."&what="
			.urlencode($search_what)),
		$mailbox, 
		-1, 
		'<b>' . lang("Found") . ' ' 
                        . count($messagelist) . ' ' 
                        . lang("messages") . '</b>',
		'&nbsp;',  
		'',
		urlencode($search_where),
		urlencode($search_what));
/*
//mkorff@vpoint.com.br: 'Select all', or better 'Invert Selection' is much quicker in Javacript ...
	$sel='function inv_sel() {
	     for (i=0; i<document.delmov.elements.length; i++) {
		if (document.delmov.elements[i].type == "checkbox") {
		       if (document.delmov.elements[i].checked) {
		      document.delmov.elements[i].checked = false;
		   } else {
		      document.delmov.elements[i].checked = true;
		   }
		}
	     }
	 }';
*/
         
 
         while ($j < count($msgs)) {
            printMessageInfo($imapConnection, $msgs[$j]["ID"], 0, $msgs[$j], $mailbox, "", 0, $search_where, $search_what);
            $j++;
         }
         echo "</table>";
         echo "</tr></td></table>";
      }
   }

?>
