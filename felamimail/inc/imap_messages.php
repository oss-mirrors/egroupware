<?php
   /**
    **  imap_messages.php
    **
    **  This implements functions that manipulate messages
    **
    **  $Id$
    **/

	if (! isset($mime_php))
   {
		include(PHPGW_APP_ROOT . '/inc/mime.php');
	}

   /******************************************************************************
    **  Copies specified messages to specified folder
    ******************************************************************************/
   function sqimap_messages_copy ($imap_stream, $start, $end, $mailbox) {
      fputs ($imap_stream, "a001 COPY $start:$end \"$mailbox\"\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);
   }

   /******************************************************************************
    **  Deletes specified messages and moves them to trash if possible
    ******************************************************************************/
   function sqimap_messages_delete ($imap_stream, $start, $end, $mailbox) {
      global $move_to_trash, $trash_folder, $auto_expunge;

      if (($move_to_trash == true) && (sqimap_mailbox_exists($imap_stream, $trash_folder) && ($mailbox != $trash_folder))) {
         sqimap_messages_copy ($imap_stream, $start, $end, $trash_folder);
         sqimap_messages_flag ($imap_stream, $start, $end, "Deleted");
      } else {
         sqimap_messages_flag ($imap_stream, $start, $end, "Deleted");
      }
   }

   /******************************************************************************
    **  Sets the specified messages with specified flag
    ******************************************************************************/
   function sqimap_messages_flag ($imap_stream, $start, $end, $flag) {
      fputs ($imap_stream, "a001 STORE $start:$end +FLAGS (\\$flag)\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);
   }


   /******************************************************************************
    **  Remove specified flag from specified messages
    ******************************************************************************/
   function sqimap_messages_remove_flag ($imap_stream, $start, $end, $flag) {
      fputs ($imap_stream, "a001 STORE $start:$end -FLAGS (\\$flag)\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);
   }


   /******************************************************************************
    **  Returns some general header information -- FROM, DATE, and SUBJECT
    ******************************************************************************/
   class small_header {
      var $from = '', $subject = '', $date = '', $to = '', 
          $priority = 0, $message_id = 0, $cc = '';
   }
	 
   function sqimap_get_small_header ($imap_stream, $id, $sent) {

      fputs ($imap_stream, "a001 FETCH $id BODY.PEEK[HEADER.FIELDS (Date To From Cc Subject Message-Id X-Priority Content-Type)]\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);

      $subject = lang("(no subject)");
      $from = lang("Unknown Sender");
      $priority = "0";
      $messageid = "<>";
      $cc = "";
      $to = "";
      $date = "";
      $type[0] = "";
      $type[1] = "";

      $g = 0;
      for ($i = 0; $i < count($read); $i++) {
         if (eregi ("^to:(.*)$", $read[$i], $regs)) {
            //$to = sqimap_find_displayable_name(substr($read[$i], 3));
            $to = $regs[1];
	 } else if (eregi ("^from:(.*)$", $read[$i], $regs)) {
            //$from = sqimap_find_displayable_name(substr($read[$i], 5));
            $from = $regs[1];
	 } else if (eregi ("^x-priority:(.*)$", $read[$i], $regs)) {
            $priority = trim($regs[1]);
         } else if (eregi ("^message-id:(.*)$", $read[$i], $regs)) {
            $messageid = trim($regs[1]);
         } else if (eregi ("^cc:(.*)$", $read[$i], $regs)) {
            $cc = $regs[1];
         } else if (eregi ("^date:(.*)$", $read[$i], $regs)) {
            $date = $regs[1];
         } else if (eregi ("^subject:(.*)$", $read[$i], $regs)) {
            $subject = htmlspecialchars(trim($regs[1]));
            if ($subject == "")
               $subject = lang("(no subject)");
         } else if (eregi ("^content-type:(.*)$", $read[$i], $regs)) {
            $type = strtolower(trim($regs[1]));
            if ($pos = strpos($type, ";"))
               $type = substr($type, 0, $pos);
            $type = explode("/", $type);
         }
         
      }

      // If there isn't a date, it takes the internal date and uses
      // that as the normal date.
      if (trim($date) == "") {
         fputs ($imap_stream, "a002 FETCH $id INTERNALDATE\r\n");
         $internal_read = sqimap_read_data ($imap_stream, "a002", true, $r, $m);

         // * 22 FETCH (INTERNALDATE " 8-Sep-2000 13:17:07 -0500")
         $date = $internal_read[0];
         $date = eregi_replace(".*internaldate \"", "", $date);
         $date = eregi_replace("\".*", "", $date);
         $date_ary = explode(" ", trim($date));
         $date_ary[0] = str_replace("-", " ", $date_ary[0]);
         $date = implode (" ", $date_ary);
      }

      fputs ($imap_stream, "a003 FETCH $id RFC822.SIZE\r\n");
      $read = sqimap_read_data($imap_stream, "a003", true, $r, $m);
      preg_match("/([0-9]+)\)\s*$/i", $read[0], $regs);
      $size = $regs[1];
      
      $header = new small_header;
      if ($sent == true)
         $header->from = (trim($to) != '')? $to : lang("(only Cc/Bcc)");
      else   
         $header->from = $from;

      $header->date = $date;
      $header->subject = $subject;
      $header->to = $to;
      $header->priority = $priority;
      $header->message_id = $messageid;
      $header->cc = $cc;
      $header->size = $size;
      $header->type0 = $type[0];
      $header->type1 = $type[1];
      
      return $header;
   }

   /******************************************************************************
    **  Returns the flags for the specified messages 
    ******************************************************************************/
   function sqimap_get_flags ($imap_stream, $i) {
      fputs ($imap_stream, "a001 FETCH $i:$i FLAGS\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);
      if (ereg("FLAGS(.*)", $read[0], $regs))
          return explode(" ", trim(ereg_replace('[\(\)\\]', '', $regs[1])));
      return Array('None');
   }

   /******************************************************************************
    **  Returns a message array with all the information about a message.  See
    **  the documentation folder for more information about this array.
    ******************************************************************************/
   function sqimap_get_message ($imap_stream, $id, $mailbox) {
   	global $header;
      $header = sqimap_get_message_header($imap_stream, $id, $mailbox);
      return sqimap_get_message_body($imap_stream, $header);
   }

   /******************************************************************************
    **  Wrapper function that reformats the header information.
    ******************************************************************************/
   function sqimap_get_message_header ($imap_stream, $id, $mailbox) {
      fputs ($imap_stream, "a001 FETCH $id:$id BODY[HEADER]\r\n");
      $read = sqimap_read_data ($imap_stream, "a001", true, $response, $message);
     
      $header = sqimap_get_header($imap_stream, $read); 
      $header->id = $id;
      $header->mailbox = $mailbox;

      return $header;
   }

   /******************************************************************************
    **  Wrapper function that returns entity headers for use by decodeMime
    ******************************************************************************/
    /*
   function sqimap_get_entity_header ($imap_stream, &$read, &$type0, &$type1, &$bound, &$encoding, &$charset, &$filename) {
      $header = sqimap_get_header($imap_stream, $read);
      $type0 = $header["TYPE0"]; 
      $type1 = $header["TYPE1"];
      $bound = $header["BOUNDARY"];
      $encoding = $header["ENCODING"];
      $charset = $header["CHARSET"];
      $filename = $header["FILENAME"];
   }
    */

   /******************************************************************************
    **  Queries the IMAP server and gets all header information.
    ******************************************************************************/
   function sqimap_get_header ($imap_stream, $read) {
      global $where, $what;

      $hdr = new msg_header();
      $i = 0;
      // Set up some defaults
      $hdr->type0 = "text";
      $hdr->type1 = "plain";
      $hdr->charset = "us-ascii";

      preg_match("/\{([0-9]+)\}/", $read[$i], $regs);
      preg_match("/[0-9]+/", $regs[0], $regs);

      while ($i < count($read)) {
         if (substr($read[$i], 0, 17) == "MIME-Version: 1.0") {
            $hdr->mime = true;
            $i++;
         }

         /** ENCODING TYPE **/
         else if (substr(strtolower($read[$i]), 0, 26) == "content-transfer-encoding:") {
            $hdr->encoding = strtolower(trim(substr($read[$i], 26)));
            $i++;
         }

         /** CONTENT-TYPE **/
         else if (strtolower(substr($read[$i], 0, 13)) == "content-type:") {
            $cont = strtolower(trim(substr($read[$i], 13)));
            if (strpos($cont, ";"))
               $cont = substr($cont, 0, strpos($cont, ";"));


            if (strpos($cont, "/")) {
               $hdr->type0 = substr($cont, 0, strpos($cont, "/"));
               $hdr->type1 = substr($cont, strpos($cont, "/")+1);
            } else {
               $hdr->type0 = $cont;
            }


            $line = $read[$i];
            $i++;
            while ( (substr(substr($read[$i], 0, strpos($read[$i], " ")), -1) != ":") && (trim($read[$i]) != "") && (trim($read[$i]) != ")")) {
               str_replace("\n", "", $line);
               str_replace("\n", "", $read[$i]);
               $line = "$line $read[$i]";
               $i++;
            }

            /** Detect the boundary of a multipart message **/
            if (eregi("boundary=\"([^\"]+)\"", $line, $regs)) {                             
               $hdr->boundary = $regs[1];                                             
            }

            /** Detect the charset **/
            if (strpos(strtolower(trim($line)), "charset=")) {
               $pos = strpos($line, "charset=") + 8;
               $charset = trim($line);
               if (strpos($line, ";", $pos) > 0) {
                  $charset = substr($charset, $pos, strpos($line, ";", $pos)-$pos);
               } else {
                  $charset = substr($charset, $pos);
               }
               $charset = str_replace("\"", "", $charset);
               $hdr->charset = $charset;
            } else {
               $hdr->charset = "us-ascii";
            }

         }

         else if (strtolower(substr($read[$i], 0, 20)) == "content-disposition:") {   
            /** Add better dontent-disposition support **/
            
            $line = $read[$i];
            $i++;
            while ( (substr(substr($read[$i], 0, strpos($read[$i], " ")), -1) != ":") && (trim($read[$i]) != "") && (trim($read[$i]) != ")")) {
               str_replace("\n", "", $line);
               str_replace("\n", "", $read[$i]);
               $line = "$line $read[$i]";
               $i++;
            }

            /** Detects filename if any **/
            if (strpos(strtolower(trim($line)), "filename=")) {
               $pos = strpos($line, "filename=") + 9;
               $name = trim($line);
               if (strpos($line, " ", $pos) > 0) {
                  $name = substr($name, $pos, strpos($line, " ", $pos));
               } else {
                  $name = substr($name, $pos);
               }
               $name = str_replace("\"", "", $name);
               $hdr->filename = $name;
            }
         }

         /** REPLY-TO **/
         else if (strtolower(substr($read[$i], 0, 9)) == "reply-to:") {
            $hdr->replyto = trim(substr($read[$i], 9, strlen($read[$i])));
            $i++;
         }

         /** FROM **/
         else if (strtolower(substr($read[$i], 0, 5)) == "from:") {
            $hdr->from = trim(substr($read[$i], 5, strlen($read[$i]) - 6));
            if (! isset($hdr->replyto) || $hdr->replyto == "")
               $hdr->replyto = $hdr->from;
            $i++;
         }
         /** DATE **/
         else if (strtolower(substr($read[$i], 0, 5)) == "date:") {
            $d = substr($read[$i], 5);
            $d = trim($d);
            $d = ereg_replace("  ", " ", $d);
            $d = explode(" ", $d);
            $hdr->date = getTimeStamp($d);
            $i++;
         }
         /** SUBJECT **/
         else if (strtolower(substr($read[$i], 0, 8)) == "subject:") {
            $hdr->subject = trim(substr($read[$i], 8, strlen($read[$i]) - 9));
            if (strlen(Chop($hdr->subject)) == 0)
               $hdr->subject = lang("(no subject)");

            if ($where == "SUBJECT") {
               $hdr->subject = eregi_replace($what, "<b>\\0</b>", $hdr->subject);
            }
            $i++;
         }
         /** CC **/
         else if (strtolower(substr($read[$i], 0, 3)) == "cc:") {
            $pos = 0;
            $hdr->cc[$pos] = trim(substr($read[$i], 4));
            $i++;
            while (((substr($read[$i], 0, 1) == " ") || (substr($read[$i], 0, 1) == "\t"))  && (trim($read[$i]) != "")){
               $pos++;
               $hdr->cc[$pos] = trim($read[$i]);
               $i++;
            }
         }
         /** TO **/
         else if (strtolower(substr($read[$i], 0, 3)) == "to:") {
            $pos = 0;
            $hdr->to[$pos] = trim(substr($read[$i], 4));
            $i++;
            while (((substr($read[$i], 0, 1) == " ") || (substr($read[$i], 0, 1) == "\t"))  && (trim($read[$i]) != "")){
               $pos++;
               $hdr->to[$pos] = trim($read[$i]);
               $i++;
            }
         }
         /** MESSAGE ID **/
         else if (strtolower(substr($read[$i], 0, 11)) == "message-id:") {
            $hdr->message_id = trim(substr($read[$i], 11));
            $i++;
         }
         /** PHPGW TYPE **/
         else if (substr(strtolower($read[$i]), 0, 13) == "x-phpgw-type:") {
            $temp = strtolower(trim(substr($read[$i], 13)));
            $temp_str = explode(';',$temp);
            $hdr->phpgw_type['type'] = substr(trim($temp_str[0]),1,strlen(trim($temp_str[0])) - 2);
            for($k=1;$k<count($temp_str);$k++) {
            	$temp_type = explode('=',trim($temp_str[$k]));
            	$hdr->phpgw_type[trim($temp_type[0])] = substr(trim($temp_type[1]),1,strlen(trim($temp_type[1])) - 2);
//            	echo "!".trim($temp_type[0])."! = !".$hdr->phpgw_type[trim($temp_type[0])]."!<br>\n";
            }
            $i++;
         }


         /** ERROR CORRECTION **/
         else if (substr($read[$i], 0, 1) == ")") {
            if (strlen(trim($hdr->subject)) == 0)
                $hdr->subject = lang("(no subject)");

            if (strlen(trim($hdr->from)) == 0)
                $hdr->from = lang("(unknown sender)");

            if (strlen(trim($hdr->date)) == 0)
                $hdr->date = time();
            $i++;
         }
         else {
            $i++;
         }
      }
      return $hdr;
   }


   /******************************************************************************
    **  Returns the body of a message.
    ******************************************************************************/
   function sqimap_get_message_body ($imap_stream, &$header) {
      $id = $header->id;
      return decodeMime($imap_stream, $header);
   }


   /******************************************************************************
    **  Returns an array with the body structure 
    ******************************************************************************/
?>
