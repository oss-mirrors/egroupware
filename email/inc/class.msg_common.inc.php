<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  /**************************************************************************\
  * Some constants we need to define                                         *
  \**************************************************************************/

  if (! defined("TYPEVIDEO")) { // without imap compiled in some constants
    define ("TYPETEXT",0);      // are missing
    define ("TYPEMULTIPART",1);
    define ("TYPEMESSAGE",2);
    define ("TYPEAPPLICATION",3);
    define ("TYPEAUDIO",4);
    define ("TYPEIMAGE",5);
    define ("TYPEVIDEO",6);
    define ("TYPEOTHER",7);
//  define ("TYPEMODEL",
    define ("ENC7BIT",0);
    define ("ENC8BIT",1);
    define ("ENCBINARY",2);
    define ("ENCBASE64",3);
    define ("ENCQUOTEDPRINTABLE",4);
    define ("ENCOTHER",5);
    define ("ENCUU",6);
  }

  /**************************************************************************\
  * SubClasses needed by msg funcs.                                          *
  \**************************************************************************/

  class msg_struct {
     var $type = 0;
     var $encoding = 5;
     var $ifsubtype = false, $subtype = "plain";
     var $ifdescription = false, $description;
     var $ifid = false, $id;
     var $lines = "0";
     var $bytes = "0";
     var $ifdisposition = false, $disposition;
     var $ifdparameters = false, $dparameters;
     var $ifparameters = false, $parameters;
     var $parts;
  }

  class msg_params {
     var $attribute;
     var $value;
     function msg_params($attrib,$val) {
       $this->attribute = $attrib;
       $this->value     = $val;
     }
  }

  class msg_headinfo {
    var $remail, $date, $Date, $subject, $Subject,
        $in_reply_to, $message_id, $newsgroups, $followup_to, $references,
        $Recent, $Unseen, $Answered, $Deleted, $Draft, $Flagged,
        $toaddress, $to = Array(),
        $fromaddress, $from = Array(),
        $ccaddress, $cc = Array(),
        $bccaddress, $bcc = Array(),
        $reply_toaddress, $reply_to = Array(),
        $senderaddress, $sender = Array(),
        $return_path, $return_path = Array(),
        $udate, $fetchfrom, $fetchsubject, $Size;
  }

  class msg_aka {
    var $personal, $adl, $mailbox, $host;
  }

  class msg_mb_info {
    var $Date = "", $Driver ="", $Mailbox = "", $Nmsgs = "",
        $Recent = "", $Unread = "", $Size;
  }

  class msg_common 
  { 
    var $msg_struct;
    var $err = array("code","msg","desc");
    var $msg_info = Array(Array());
    var $tempfile, $force_check;
    var $boundary, $got_structure;

    function msg_common_() {
      global $phpgw_info;
      $this->err["code"] = " ";
      $this->err["msg"]  = " ";
      $this->err["desc"] = " ";
      $this->tempfile = $phpgw_info["server"]["temp_dir"].$phpgw_info["server"]["dir_separator"].$phpgw_info["user"]["userid"].".mhd";
      $this->force_check = false;
      $this->got_structure = false;
    }

    /**************************************************************************\
    * phpGW functions for developers.                                          *
    \**************************************************************************/

    function get_flag($stream,$msg_num,$flag)
    {
	$header = $this->fetchheader($stream,$msg_num);
	$flag = strtolower($flag);
	for ($i=0;$i<count($header);$i++)
	{
		$pos = strpos($header[$i],":");
		if (is_int($pos) && $pos)
		{
			$keyword = trim(substr($header[$i],0,$pos));
			$content = trim(substr($header[$i],$pos+1));
			if (strtolower($keyword) == $flag)
			{
				return $content;
			}
		}
	}
	return false;
    }

    /**************************************************************************\
    * Common functions used by several pieces of this class                    *
    \**************************************************************************/
  
    function base64($string)
    {
	return base64_decode($string);
    }

    function construct_folder_str( $folder )
    {
	if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus")
	{
		$folder_str = "INBOX.".$folder;
	}
	else
	{
		$folder_str = "mail/".$folder;
	}
	return $folder_str;
    }

    function createmailbox($stream,$mailbox)
    {
	return false;
    }

    function deconstruct_folder_str( $folder )
    {
	global $phpgw_info;

	if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus")
	{
		$srch_str = "INBOX.";
	}
	else
	{
		$srch_str = "mail/";
	}
	$folder_str = substr($folder, strlen($srch_str), strlen($folder));

	return $folder_str;
    }

    function deletemailbox($stream,$mailbox)
    {
	return false;
    }

    function fetchheader($stream,$msg_num)
    {
	$header = $this->get_header($stream,$msg_num);
	return implode("\n",$header);
    }

    function fetchstructure($stream,$msg_num,$flags="")
    {
	$header = $this->get_header($stream,$msg_num);
	if (!$header)
	{
		return false;
	}
	$info = $this->get_structure($header,1);
	if (!$info->bytes)
	{
		$rc = ($this->msg2socket($stream,"LIST $msg_num\n"));
		if (!($this->pop_socket2msg($stream)))
		{
			$pos = strpos($this->err[msg]," ");
			$info->bytes = substr($this->err[msg],$pos+1);
		}
	}
	if ($info->type == 1)
	{ 
		// multipart
		$body = $this->get_body($stream,$msg_num);
		$boundary = $this->get_boundary(&$info);
		$boundary = str_replace("\"","",$boundary);
		$this->boundary = $boundary;
		for ($i=1;$i<=$body[0];$i++)
		{
			$pos1 = strpos($body[$i],"--$boundary");
			$pos2 = strpos($body[$i],"--$boundary--");
			if (is_int($pos2) && !$pos2)
			{
				break;
			}
			if (is_int($pos1) && !$pos1)
			{
				$info->parts[] = $this->get_structure($body,&$i,true);
			}
		}
	}
	$this->got_structure = true;
	return $info;
    }

    function header($stream,$msg_nr,$fromlength="",$tolength="",$defaulthost="")
    {
	$info = new msg_headinfo;
	$info->Size = $this->size_msg($stream,$msg_nr);
	$header = $this->get_header($stream,$msg_nr);
	if (!$header)
	{
		return false;
	}
	for ($i=1;$i<=$header[0];$i++)
	{
		$pos = strpos($header[$i]," ");
		if (is_int($pos) && !$pos)
		{
			continue;
		}
		$keyword = strtolower(substr($header[$i],0,$pos));
		$content = trim(substr($header[$i],$pos+1));
		switch ($keyword)
		{
			case "from"	:
			case "from:"	:
			  $info->from = $this->get_addr_details("from",$content,&$header,&$i);
			  break;
			case "to"	:
			case "to:"	: 
			  // following two lines need to be put into a loop!
			  $info->to   = $this->get_addr_details("to",$content,&$header,&$i);
			  break;
			case "cc"	:
			case "cc:"	:
			  $info->cc   = $this->get_addr_details("cc",$content,&$header,&$i);
			  break;
			case "bcc"	:
			case "bcc:"	:
			  $info->bcc  = $this->get_addr_details("bcc",$content,&$header,&$i);
			  break;
			case "reply-to"	:
			case "reply-to:"	:
			  $info->reply_to = $this->get_addr_details("reply_to",$content,&$header,&$i);
			  break;
			case "sender"	:
			case "sender:"	:
			  $info->sender = $this->get_addr_details("sender",$content,&$header,&$i);
			  break;
			case "return-path"	:
			case "return-path:"	:
			  $info->return_path = $this->get_addr_details("return_path",$content,&$header,&$i);
			  break;
			case "subject"	:
			case "subject:"	:
			case "Subject:"	:
			  $pos = strpos($header[$i+1]," "); if (is_int($pos) && !$pos)
			  {
				$i++; $content .= chop($header[$i]);
			  }
			  $info->subject = htmlspecialchars($content);
			  $info->Subject = htmlspecialchars($content);
			  break;

			// only temp
			case "message-id"  :
			case "message-id:" :
			  $info->message_id = htmlspecialchars($content);
			  break;
			case "newsgroups:" :
			  $info->newsgroups = htmlspecialchars($content);
			  break;
			case "references:" :
			  $info->references = htmlspecialchars($content);
			  break;
			case "in-reply-to:" :
			  $info->in_reply_to = htmlspecialchars($content);
			  break;
			case "followup-to:" :
			  $info->follow_up_to = htmlspecialchars($content);
			  break;
			case "date:"	:
			  $info->date  = $content;
			  $info->udate = $this->make_udate($content);
			  break;
			default	:
			  break;
		}
	}
	return $info;
    } 

    function listmailbox($stream,$ref,$pattern)
    {
	return false;
    }

// ----  Password Crypto Workaround broken common->en/decrypt  -----
	/*!
	@function encrypt_email_passwd
	@abstract encrypt data passed to the function
	@param $data data string to be encrypted
	*/
	function encrypt_email_passwd($data)
	{
		global $phpgw_info, $phpgw;

		$encrypted_passwd = $data;
		if ($phpgw_info['server']['mcrypt_enabled'] && extension_loaded('mcrypt'))
		{
			// this will return a string that has (1) been serialized (2) had addslashes applied
			// and (3) been encrypted with mcrypt (assuming mcrypt is enabled and working)
			$encrypted_passwd = $phpgw->crypto->encrypt($encrypted_passwd);
		}
		else
		{
			// ***** STRIP SLASHES BEFORE CALLING THIS FUNCTION !!!!!!! ******
			// we have no way of knowing if it's necessary, but you do, you who call this function
			//$encrypted_passwd = $this->stripslashes_gpc($encrypted_passwd);
			$encrypted_passwd = $data;
			if ($this->is_serialized($encrypted_passwd))
			{
				$encrypted_passwd = unserialize($encrypted_passwd);
			}
			$encrypted_passwd = $this->html_quotes_encode($encrypted_passwd);
		}
		return $encrypted_passwd;
	}
	/*!
	@function decrypt_email_pass
	@abstract decrypt $data
	@param $data data to be decrypted
	*/
	function decrypt_email_passwd($data)
	{
		global $phpgw_info, $phpgw;

		$passwd = $data;
		if ($phpgw_info['server']['mcrypt_enabled'] && extension_loaded('mcrypt'))
		{
			// this will return a string that has:
			// (1) been decrypted with mcrypt (assuming mcrypt is enabled and working)
			// (2) had stripslashes applied and (3) *MAY HAVE* been unserialized
			$passwd = $phpgw->crypto->encrypt($passwd);
		}
		else
		{
			// ASSUMING set_magic_quotes_runtime(0) is in functions.inc.php (it is) then
			// there should be NO escape slashes coming from the database
			if ($this->is_serialized($passwd))
			{
				$passwd = unserialize($passwd);
			}


			// #### (begin) Upgrade Routine for 0.9.12 and earlier versions ####
			/* // these version *may* have double ot tripple serialized passwd stored in their preferences table
			// (1) check for this (2) unserialize to the real string (3) feed the unserialized / fixed passwd in the prefs class */
			// (1) check for this 
			$multi_serialized = $this->is_serialized($passwd);
			if ($multi_serialized)
			{
				$pre_upgrade_passwd = $passwd;
				// (2) unserialize to the real string
				$failure = 10;
				$loop_num = 0;
				do
				{
					$loop_num++;
					if ($loop_num == $failure)
					{
						break;
					}
					$passwd = unserialize($passwd);
				}
				while ($this->is_serialized($passwd));
				
				// 10 loops is too much, something is wrong
				if ($loop_num == $failure)
				{
					// screw it and continue as normal, user will need to reenter password
					$passwd = $pre_upgrade_passwd;
				}
				else
				{
					// (3) feed the unserialized / fixed passwd in the prefs class
					$phpgw->preferences->delete("email","passwd");
					// make any html quote entities back to real form (i.e. ' or ")
					$encrypted_passwd = $this->html_quotes_decode($passwd);
					// encrypt it as it would be as if the user had just submitted the preferences page (no need to strip slashes, no POST occured)
					$encrypted_passwd = $this->encrypt_email_passwd($passwd);
					// store in preferences so this does not happen again
					$phpgw->preferences->add("email","passwd",$encrypted_passwd);
					$phpgw->preferences->save_repository();
				}
			}
			// #### (end) Upgrade Routine for 0.9.12 and earlier versions ####

			$passwd = $this->html_quotes_decode($passwd);
			//echo 'decrypt_email_passwd result: '.$passwd;
		}
		return $passwd;
	}

	function get_email_passwd()
	{
		global $phpgw_info, $phpgw;
		
		$tmp_prefs = $phpgw->preferences->read();

		if (!isset($tmp_prefs['email']['passwd']))
		{
			return $phpgw_info['user']['passwd'];
		}
		else
		{
			return $this->decrypt_email_passwd($tmp_prefs['email']['passwd']);
		}
	}

	// ----  High-Level Function To Get The Subject String  -----
	function get_subject($msg, $desired_prefix='Re: ')
	{
		if ( (! $msg->Subject) || ($msg->Subject == '') )
		{
			$subject = lang('no subject');
		}
		else
		{
			$subject = decode_header_string($msg->Subject);
		}
		// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
		// $personal = $this->qprint_rfc_header($personal);
		$personal = decode_header_string($personal);
		// do we add a prefix like Re: or Fw:
		if ($desired_prefix != '')
		{
			if (strtoupper(substr($subject, 0, 3)) != strtoupper(trim($desired_prefix)))
			{
				$subject = $desired_prefix . $subject;
			}
		}
		$subject = $this->htmlspecialchars_encode($subject);
		return $subject;
	}

	// ----  High-Level Function To Get The "so-and-so" wrote String   -----
	function get_who_wrote($msg)
	{
		if ( (!isset($msg->from)) && (!isset($msg->reply_to)) )
		{
			$lang_somebody = 'somebody';
			return $lang_somebody;
		}
		elseif ($msg->from[0])
		{
			$from = $msg->from[0];
		}
		else
		{
			$from = $msg->reply_to[0];
		}
		if ((!isset($from->personal)) || ($from->personal == ''))
		{
			$personal = $from->mailbox.'@'.$from->host;
			//$personal = 'not set or blank';
		}
		else
		{
			//$personal = $from->personal." ($from->mailbox@$from->host)";
			$personal = trim($from->personal);
			// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
			$personal = decode_header_string($personal);
			//$personal = $this->qprint_rfc_header($personal);
			$personal = $personal ." ($from->mailbox@$from->host)";
		}
		return $personal;
	}

	// ----  Make Address accoring to RFC2822 Standards  -----
	function make_rfc2822_address($addy_data, $html_encode=True)
	{
		//echo '<br>'.$this->htmlspecialchars_encode(serialize($addy_data)).'<br>'.'<br>';
		
		if ((!isset($addy_data->mailbox)) && (!$addy_data->mailbox)
		&& (!isset($addy_data->host)) && (!$addy_data->host))
		{
			// fallback value, we do not want to sent a string like this "@" if no data if available
			return '';
		}
		// now we can continue, 1st make a simple, plain address
		// RFC2822 allows this simple form if not using "personal" info
		$rfc_addy = $addy_data->mailbox.'@'.$addy_data->host;
		// add "personal" data if it exists
		if (isset($addy_data->personal) && ($addy_data->personal))
		{
			// why DECODE when we are just going to feed it right back into a header?
			$personal = decode_header_string($addy_data->personal);
			// add slashes because RFC2822 requires "specials" in header string to be escaped if inside a string
			// NO - do this somewhere else
			//$personal = addslashes($addy_data->personal);
			// need to format according to RFC2822 spec for non-plain email address
			$rfc_addy = '"'.$personal.'" <'.$rfc_addy.'>';
			// if using this addy in an html page, we need to encode the ' " < > chars
			if ($html_encode)
			{
				$rfc_addy = $this->htmlspecialchars_encode($rfc_addy);
				//NOTE: in rfc_comma_sep we will decode any html entities back into these chars
			}
		}
		return $rfc_addy;
	}

	// ----  Ensures To, CC, and BCC are properly comma seperated   -----
	// param $data should be the desired header string, with one or more addresses 
	function rfc_comma_sep($data)
	{
		// if we are fed a null value, return nothing (i.e. a null value)
		if (isset($data))
		{
			$data = trim($data);
			// if we are fed a whitespace only string, return a blank string
			if ($data == '')
			{
				return $data;
				// return performs an implicit break, so we are outta here
			}
			// in some cases the data may be in html entity form
			// i.e. the compose page uses html entities when filling the To: box with a predefined value
			$data = $this->htmlspecialchars_decode($data);
			//reduce all multiple spaces to just one space
			//$data = ereg_replace("[' ']{2,20}", ' ', $data);
			$this_space = " ";
			$data = ereg_replace("$this_space{2,20}", " ", $data);
			// explode into an array of email addys
			$data = explode(",", $data);
			// formatting loop
			for ($i=0;$i<count($data);$i++)
			{
				// trim off leading and trailing whitespaces and \r and \n
				$data[$i] = trim($data[$i]);
				// the non-simple (i.e. "personal" info is included) need special escaping
				if (strstr($data[$i], '" <'))
				{
					// SEPERATE "personal" part from the <x@x.com> part
					$addr_spec_parts = explode('" <', $data[$i]);
					// that got rid of the closing " in personal, now get rig of the first "
					$addr_spec_parts[0] = substr($addr_spec_parts[0], 1);
					
					// QPRINT NON US-ASCII CHARS in "personal" string
					// header ENCODE non us-ascii chars as per RFC2822
					// -- future -- not yet implemented
					
					// ESCAPE SPECIALS:  rfc2822 requires the "personal" comment string to escape "specials" inside the quotes
					// escape these:  ' " ( ) 
					$addr_spec_parts[0] = ereg_replace('\'', "\\'", $addr_spec_parts[0]);
					$addr_spec_parts[0] = str_replace('"', '\"', $addr_spec_parts[0]);
					$addr_spec_parts[0] = str_replace("(", "\(", $addr_spec_parts[0]);
					$addr_spec_parts[0] = str_replace(")", "\)", $addr_spec_parts[0]);
					//echo 'addr_spec_parts: '.$this->htmlspecialchars_encode($addr_spec_parts[0]).'<br>';
					
					// REASSEMBLE the personal and plain address into 1 string
					$data[$i] = '"'.$addr_spec_parts[0].'" <'.$addr_spec_parts[1];
					//echo 'trimmed and slashes stripped data[i]: '.$this->htmlspecialchars_encode($data[$i]).'<br>';
				}
			}
			
			// reconstruct data in the correct email address format
			if (count($data) == 0)
			{
				$data = '';
			}
			elseif (count($data) == 1)
			{
				// add the final CRLF to finish off this header string
				//$data = (string)$data[0]."\r\n";
				$data = (string)$data[0];
			}
			else
			{
				/*// CLASS SEND CAN NOT HANDLE FOLDED HEADERS YET (7/10/01)
				// this snippit just assembles the headers
				for ($i=0;$i<count($data);$i++)
				{
					$data[$i] = trim($data[$i]);
				}
				// addresses should be seperated by one comma with NO SPACES AT ALL
				$data = implode(",", $data);
				// catch any situations where a blank string was included, resulting in two commas with nothing inbetween
				$data = ereg_replace("[,]{2}", ',', $data);
				*/

				// if folding headers - use SEND_2822  instead of class.send
				// FRC2822 recommended max header line length, excluding the required CRLF
				$rfc_max_length = 78;

				// establish an arrays in case we need a multiline header string
				$header_lines = Array();
				$line_num = 0;
				$header_lines[$line_num] = '';
				// loop thru the addresses, construct the header string
				for ($z=0;$z<count($data);$z++)
				{
					// see how long this line would be if this address were added
					//if ($z == 0)
					$cur_len = strlen($header_lines[$line_num]);
					if ($cur_len < 1)
					{
						$would_be_str = $data[$z];
					}
					else
					{
						$would_be_str = $header_lines[$line_num] .','.$data[$z];
					}
					//echo 'would_be_str: '.$this->htmlspecialchars_encode($would_be_str).'<br>';
					//echo 'strlen(would_be_str): '.strlen($would_be_str).'<br>';
					if ((strlen($would_be_str) > $rfc_max_length)
					&& ($cur_len > 1))
					{
						// Fold Header: RFC2822 "fold" = CRLF followed by a "whitespace" (#9 or #20)
						// preferable to "fold" after the comma, and DO NOT TRIM that white space, preserve it
						$rfc_fold_a = "\r\n";
						$rfc_fold_b = " ";
						//$rfc_fold = "\r\n".(chr(9));
						$header_lines[$line_num] = $header_lines[$line_num].','.$rfc_fold_a;
						// advance to the next line
						$line_num++;
						// now start the new line with this address
						$header_lines[$line_num] = $rfc_fold_b;
						$header_lines[$line_num] = $header_lines[$line_num] .$data[$z];
					}
					else
					{
						// simply comma sep the items
						$header_lines[$line_num] = $would_be_str;
					}
				}
				// assemble $header_lines array into a single string
				$new_data = '';
				for ($x=0;$x<count($header_lines);$x++)
				{
					$new_data = $new_data .$header_lines[$x];
				}
				$data = trim($new_data);
				// add the final CRLF to finish off this header string
				//$data = $data ."\r\n";
			}
			// data leaves here with NO FINAL (trailing) CRLF - will add that later
			return $data;
		}
	}

	// ----  Ensure CR and LF are always together, RFCs prefer the CRLF combo  -----
	function normalize_crlf($data)
	{
		// this is to catch all plain \n instances and replace them with \r\n.  
		$data = ereg_replace("\r\n", "\n", $data);
		$data = ereg_replace("\r", "\n", $data);
		$data = ereg_replace("\n", "\r\n", $data);
		return $data;
	}

  // ----  HTML - Related Utility Functions   -----
	function qprint($string)
	{
		$string = str_replace("_", " ", $string);
		$string = str_replace("=\r\n","",$string);
		$string = quoted_printable_decode($string);
		return $string;
	}
	
	/*
	// ----  RFC Header Decoding  -----
	function qprint_rfc_header($data)
	{
		// SAME FUNCTIONALITY as decode_header_string()  in /inc/functions, (but Faster, hopefully)
		// non-us-ascii chars in email headers MUST be encoded using the special format:  
		//  =?charset?Q?word?=
		// currently only qprint and base64 encoding is specified by RFCs
		if (ereg("=\?.*\?(Q|q)\?.*\?=", $data))
		{
			$data = ereg_replace("=\?.*\?(Q|q)\?", '', $data);
			$data = ereg_replace("\?=", '', $data);
			$data = $this->qprint($data);
		}
		return $data;
	}
	*/

	function htmlspecialchars_encode($str)
	{
		/*// replace  '  and  "  with htmlspecialchars */
		$str = ereg_replace('&', '&amp;', $str);
		// any ampersand & that ia already in a "&amp;" should NOT be encoded
		//$str = preg_replace("/&(?![:alnum:]*;)/", "&amp;", $str);
		$str = ereg_replace('"', '&quot;', $str);
		$str = ereg_replace('\'', '&#039;', $str);
		$str = ereg_replace('<', '&lt;', $str);
		$str = ereg_replace('>', '&gt;', $str);
		return $str;
	}

	function htmlspecialchars_decode($str)
	{
		/*// reverse of htmlspecialchars */
		$str = ereg_replace('&gt;', '>', $str);
		$str = ereg_replace('&lt;', '<', $str);
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&quot;', '"', $str);
		$str = ereg_replace('&amp;', '&', $str);
		return $str;
	}

	function html_quotes_encode($str)
	{
		/*// replace  '  and  "  with htmlspecialchars */
		$str = ereg_replace('"', '&quot;', $str);
		$str = ereg_replace('\'', '&#039;', $str);
		return $str;
	}

	function html_quotes_decode($str)
	{
		/*// reverse of htmlspecialchars */
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&quot;', '"', $str);
		return $str;
	}

	function space_to_nbsp($data)
	{
		// change every other space to a html "non breaking space" so lines can still wrap
		$data = str_replace("  "," &nbsp;",$data);
		return $data;
	}

	function body_hard_wrap($in, $size=80)
	{
		// this function formats lines according to the defined
		// linesize. Linebrakes (\n\n) are added when neccessary,
		// but only between words.

		$out="";
		$exploded = explode ("\r\n",$in);

		for ($i = 0; $i < count($exploded); $i++)
		{
			$this_line = $exploded[$i];
			$this_line_len = strlen($this_line); 
			if ($this_line_len > $size)
			{
				$temptext="";
				$temparray = explode (" ",$this_line);
				$z = 0;
				while ($z <= count($temparray))
				{
					while ((strlen($temptext." ".$temparray[$z]) < $size) && ($z <= count($temparray)))
					{
						$temptext = $temptext." ".$temparray[$z];
						$z++;
					}
					$out = $out."\r\n".$temptext;
					$temptext = $temparray[$z];
					$z++;
				}
			}
			else
			{
				//$out = trim($out);
				// get the rest of the line now
				$out = $out . $this_line . "\r\n";
			}
			//$out = trim($out);
			//$out = $out . "\r\n";
		}
		// one last trimming
		$temparray = explode("\r\n",$out);
		for ($i = 0; $i < count($temparray); $i++)
		{
			$temparray[$i] = trim($temparray[$i]);
		}
		$out = implode("\r\n",$temparray);
		
		return $out;
	}


	// magic_quotes_gpc  PHP MANUAL:
	/* Sets the magic_quotes state for GPC (Get/Post/Cookie) operations. 
	  When magic_quotes are on, all ' (single-quote), " (double quote), \ (backslash) and NUL's 
	  are escaped with a backslash automatically.
	  GPC means GET/POST/COOKIE which is actually EGPCS these days (Environment, GET, POST, Cookie, Server).
	  This cannot be turned off in your script because it operates on the data before your script is called. 
	  You can check if it is on using that function and treat the data accordingly." (by Rasmus Lerdorf) */
	function stripslashes_gpc($data)
	{	/* get rid of the escape \ that magic_quotes HTTP POST will add, " becomes \" and  '  becomes  \'  
		  but ONLY if magic_quotes is on, less likely to strip user intended slashes this way */
		if (get_magic_quotes_gpc()==1)
		{
			return stripslashes($data);
		}
		else
		{
			return $data;
		}
	}

	function addslashes_gpc($data)
	{	/* add the escape \ that magic_quotes HTTP POST would add, " becomes \" and  '  becomes  \'  
		  but ONLY if magic_quotes is OFF, else we may *double* add slashes */
		if (get_magic_quotes_gpc()==1)
		{
			return $data;
		}
		else
		{
			return addslashes($data);
		}
	}

	/*!
	@function is_serialized
	@abstract find out if something is already serialized
	@param $data could be almost anything
	*/
	function is_serialized($data)
	{
		global $phpgw_info, $phpgw;
		
		/* not totally complete: currently works with strings, arrays, and booleans (update this if more is added) */
		
		 /* FUTURE: detect a serialized data that had addslashes appplied AFTER it was serialized
		 you can NOT unserialize that data until those post-serialization slashes are REMOVED */

		//echo 'is_serialized initial input [' .$data .']<br>';
		//echo 'is_serialized unserialized input [' .unserialize($data) .']<br>';

		if (is_array($data))
		{
			// arrays types are of course not serialized (at least not at the top level)
			// BUT there  may be serialization INSIDE in a sub part
			return False;
		}
		elseif (is_bool($data))
		{
			// a boolean type is of course not serialized
			return False;
		}
		elseif ((is_string($data))
		&& (($data == 'b:0;') || ($data == 'b:1;')) )
		{
			// check for easily identifiable serialized boolean values
			return True;
		}
		elseif ((is_string($data))
		&& (unserialize($data) == False))
		{
			// when you unserialize a normal (not-serialized) string, you get False
			return False;
		}
		elseif ((is_string($data))
		&& (ereg('^s:[0-9]+:"',$data) == True))
		{
			// identify pattern of a serialized string (that did NOT have slashes added AFTER serialization )
			return True;
		}
		elseif ((is_string($data))
		&& (is_array(unserialize($data))))
		{
			// if unserialization produces an array out of a string, it was serialized
			//(ereg('^a:[0-9]+:\{',$data) == True))  also could work
			return True;
		}
		//Best Guess - UNKNOWN / ERROR / NOY YET SUPPORTED TYPE
		elseif (is_string($data))
		{
			return True;
		}
		else
		{
			return False;
		}
	}

  } // end of class msg_common
?>
