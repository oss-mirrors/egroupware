<?php
   /**
    **  imap.php
    **
    **  This implements all functions that do general imap functions.
    **
    **  $Id$
    **/

   $imap_general_debug = false;

   /******************************************************************************
    **  Reads the output from the IMAP stream.  If handle_errors is set to true,
    **  this will also handle all errors that are received.  If it is not set,
    **  the errors will be sent back through $response and $message
    ******************************************************************************/
   function sqimap_read_data ($imap_stream, $pre, $handle_errors, &$response, &$message) {
      global $color, $felamimail_language, $imap_general_debug;

      $read = fgets($imap_stream, 9096);

      if (ereg("^\\* [0-9]+ FETCH.*\\{([0-9]+)\\}", $read, $regs)) {
         $size = $regs[1];
      } else {
         $size = 0;
      }
      
      $data = array();
      $total_size = 0;
      $continue = true;
      while ($continue) {
         // Continue if needed for this single line
         while (strpos($read, "\n") === false) {
            $read .= fgets($imap_stream, 9096);
         }
         // For debugging purposes
         if ($imap_general_debug) {
            echo "<small><tt><font color=\"#CC0000\">$read</font></tt></small><br>\n";
            flush();
         }


         // If we know the size, no need to look at the end parameters
         if ($size > 0) {
            if ($total_size == $size) {
               $data[] = $read;
               $read = fgets($imap_stream, 9096);
               while (!ereg("^$pre (OK|BAD|NO)(.*)$", $read, $regs)) {
                  $read = fgets($imap_stream, 9096);
               }
               $continue = false;
            } else if ($total_size > $size) {
               $difference = $total_size - $size;
               $total_size = $total_size - strlen($read);
               $read = substr ($read, 0, strlen($read)-$difference);
               $data[] = $read;
               $junk = fgets($imap_stream, 9096);
               $continue = false;
            } else {
               $data[] = $read;
               $read = fgets($imap_stream, 9096);
            }
            $total_size += strlen($read);
         } else {
            if (ereg("^$pre (OK|BAD|NO)(.*)$", $read, $regs)) {
               $continue = false;
            } else {
               $data[] = $read;
               $read = fgets ($imap_stream, 9096);
            }
         }
      }

      $response = $regs[1];
      $message = trim($regs[2]);
      
      if ($imap_general_debug) echo '--<br>';

      if ($handle_errors == false)
          return $data;
     
      if ($response == 'NO') {
         // ignore this error from m$ exchange, it is not fatal (aka bug)
         if (!ereg('command resulted in',$message)) { 
            set_up_language($felamimail_language);
            echo "<br><b><font color=$color[2]>\n";
            echo lang("ERROR : Could not complete request.");
            echo "</b><br>\n";
            echo lang("Reason Given: ");
            echo $message . "</font><br>\n";
            exit;
         }
      } else if ($response == 'BAD') {
         set_up_language($felamimail_language);
         echo "<br><b><font color=$color[2]>\n";
         echo lang("ERROR : Bad or malformed request.");
         echo "</b><br>\n";
         echo lang("Server responded: ");
         echo $message . "</font><br>\n";
         exit;
      }
      
      return $data;
   }
   
	/******************************************************************************
	 **  Logs the user into the imap server.  If $hide is set, no error messages
	 **  will be displayed.  This function returns the imap connection handle.
	 ******************************************************************************/
	function sqimap_login ($username, $password, $imap_server_address, $imap_port, $hide)
	{
		global $color, $felamimail_language, $HTTP_ACCEPT_LANGUAGE, $phpgw;
		
		$imap_stream = fsockopen ($imap_server_address, $imap_port, $error_number, $error_string, 15);
		$server_info = fgets ($imap_stream, 1024);
           
		/** Do some error correction **/
		if (!$imap_stream)
		{
			if (!$hide)
			{
				set_up_language($felamimail_language, true);
				printf (lang("Error connecting to IMAP server: %s.")."<br>\r\n", $imap_server_address);
				echo "$error_number : $error_string<br>\r\n";
			}
			$phpgw->common->phpgw_exit(True);
		}

		fputs ($imap_stream, "a001 LOGIN \"" . quotemeta($username) . '" "' . quotemeta($password) . "\"\r\n");
		$read = sqimap_read_data ($imap_stream, 'a001', false, $response, $message);

		/** If the connection was not successful, lets see why **/
		if ($response != "OK")
		{
			if (!$hide)
			{
				if ($response != 'NO')
				{
					// "BAD" and anything else gets reported here.
					set_up_language($felamimail_language, true);
					if ($response == 'BAD')
					{
						printf (lang("Bad request: %s")."<br>\r\n", $message);
					}
					else
					{
						printf (lang("Unknown error: %s") . "<br>\n", $message);
						echo '<br>';
					}
					echo lang("Read data:") . "<br>\n";

					if (is_array($read))
					{
						foreach ($read as $line)
						{
							echo htmlspecialchars($line) . "<br>\n";
						}
					}
					$phpgw->common->phpgw_exit(True);

		      } else {
		      	echo '<b>' . lang('Invaild user name or password') . '</b>';
					$phpgw->common->phpgw_exit(True);
            }
         } else {
            $phpgw->common->phpgw_exit(True);
         }
      }

      return $imap_stream;
   }


   
   
   /******************************************************************************
    **  Simply logs out the imap session
    ******************************************************************************/
   function sqimap_logout ($imap_stream) {
      fputs ($imap_stream, "a001 LOGOUT\r\n");
   }

function sqimap_capability($imap_stream, $capability) {
	global $sqimap_capabilities;
	global $imap_general_debug;

	if (!is_array($sqimap_capabilities)) {
		fputs ($imap_stream, "a001 CAPABILITY\r\n");
		$read = sqimap_read_data($imap_stream, 'a001', true, $a, $b);

		$c = explode(' ', $read[0]);
		for ($i=2; $i < count($c); $i++) {
			$cap_list = explode('=', $c[$i]);
			if (isset($cap_list[1]))
			    $sqimap_capabilities[$cap_list[0]] = $cap_list[1];
			else
 			    $sqimap_capabilities[$cap_list[0]] = TRUE;
		}
	}
	return $sqimap_capabilities[$capability];
}

   /******************************************************************************
    **  Returns the delimeter between mailboxes:  INBOX/Test, or INBOX.Test... 
    ******************************************************************************/
function sqimap_get_delimiter ($imap_stream = false) {
   global $imap_general_debug;
   global $sqimap_delimiter;
   global $optional_delimiter;

   /* Use configured delimiter if set */
   if((!empty($optional_delimiter)) && $optional_delimiter != "detect") {
      return $optional_delimiter;
   }

	/* Do some caching here */
    if (!$sqimap_delimiter) {
		if (sqimap_capability($imap_stream, "NAMESPACE")) {
			/* According to something that I can't find, this is supposed to work on all systems
			   OS: This won't work in Courier IMAP.
			   OS:  According to rfc2342 response from NAMESPACE command is:
			   OS:  * NAMESPACE (PERSONAL NAMESPACES) (OTHER_USERS NAMESPACE) (SHARED NAMESPACES)
			   OS:  We want to lookup all personal NAMESPACES...
			*/
			fputs ($imap_stream, "a001 NAMESPACE\r\n");
			$read = sqimap_read_data($imap_stream, 'a001', true, $a, $b);
			if (eregi('\* NAMESPACE +(\( *\(.+\) *\)|NIL) +(\( *\(.+\) *\)|NIL) +(\( *\(.+\) *\)|NIL)', $read[0], $data)) {
				if (eregi('^\( *\((.*)\) *\)', $data[1], $data2))
					$pn = $data2[1];
				$pna = explode(')(', $pn);
				while (list($k, $v) = each($pna))
				{
                    $lst = explode('"', $v);
                    if (isset($lst[3])) {
                        $pn[$lst[1]] = $lst[3];
                    } else {
                        $pn[$lst[1]] = '';
                    }
				}
			}
			$sqimap_delimiter = $pn[0];
		} else {
			fputs ($imap_stream, ". LIST \"INBOX\" \"\"\r\n");
			$read = sqimap_read_data($imap_stream, '.', true, $a, $b);
			$quote_position = strpos ($read[0], '"');
			$sqimap_delimiter = substr ($read[0], $quote_position+1, 1);
		}
	}
	return $sqimap_delimiter;
}


   /******************************************************************************
    **  Gets the number of messages in the current mailbox. 
    ******************************************************************************/
   function sqimap_get_num_messages ($imap_stream, $mailbox) {
      fputs ($imap_stream, "a001 EXAMINE \"$mailbox\"\r\n");
      $read_ary = sqimap_read_data ($imap_stream, 'a001', true, $result, $message);
      for ($i = 0; $i < count($read_ary); $i++) {
         if (ereg("[^ ]+ +([^ ]+) +EXISTS", $read_ary[$i], $regs)) {
	    return $regs[1];
         }
      }
      return "BUG!  Couldn't get number of messages in $mailbox!";
   }

   
   /******************************************************************************
    **  Returns a displayable email address 
    ******************************************************************************/
   function sqimap_find_email ($string) {
      /** Luke Ehresman <lehresma@css.tayloru.edu>
       ** <lehresma@css.tayloru.edu>
       ** lehresma@css.tayloru.edu
       **
       ** What about
       **    lehresma@css.tayloru.edu (Luke Ehresman)
       **/

      if (ereg("<([^>]+)>", $string, $regs)) {
          $string = $regs[1];
      }
      return trim($string); 
   }

   
   /******************************************************************************
    **  Takes the From: field, and creates a displayable name.
    **    Luke Ehresman <lkehresman@yahoo.com>
    **           becomes:   Luke Ehresman
    **    <lkehresman@yahoo.com>
    **           becomes:   lkehresman@yahoo.com
    ******************************************************************************/
   function sqimap_find_displayable_name ($string) {
      $string = ' '.trim($string);
      $orig_string = $string;
      if (strpos($string, '<') && strpos($string, '>')) {
         if (strpos($string, '<') == 1) {
            $string = sqimap_find_email($string);
         } else {
            $string = trim($string);
            $string = substr($string, 0, strpos($string, '<'));
            $string = ereg_replace ('"', '', $string);   
         }   

         if (trim($string) == '') {
            $string = sqimap_find_email($orig_string);
         }
      }
      return $string; 
   }


   /******************************************************************************
    **  Returns the number of unseen messages in this folder 
    ******************************************************************************/
   function sqimap_unseen_messages ($imap_stream, &$num_unseen, $mailbox) {
      //fputs ($imap_stream, "a001 SEARCH UNSEEN NOT DELETED\r\n");
      fputs ($imap_stream, "a001 STATUS \"$mailbox\" (UNSEEN)\r\n");
      $read_ary = sqimap_read_data ($imap_stream, 'a001', true, $result, $message);
      ereg("UNSEEN ([0-9]+)", $read_ary[0], $regs);
      return $regs[1];
   }
 
  
   /******************************************************************************
    **  Saves a message to a given folder -- used for saving sent messages
    ******************************************************************************/
   function sqimap_append ($imap_stream, $sent_folder, $length) {
      fputs ($imap_stream, "a001 APPEND \"$sent_folder\" (\\Seen) \{$length}\r\n");
      $tmp = fgets ($imap_stream, 1024);
   } 

   function sqimap_append_done ($imap_stream) {
      fputs ($imap_stream, "\r\n");
      $tmp = fgets ($imap_stream, 1024);
   }
?>
