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

  class msg_common 
  { 
	var $msg_struct;
	var $err = array("code","msg","desc");
	var $msg_info = Array(Array());

	var $tempfile,
	   $att_files_dir,
	   $force_check;

	var $boundary,
	   $got_structure;

    function msg_common_()
    {
	global $phpgw_info;
	$this->err["code"] = " ";
	$this->err["msg"]  = " ";
	$this->err["desc"] = " ";
	$this->tempfile = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'].'.mhd';
	$this->att_files_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];
	$this->force_check = false;
	$this->got_structure = false;
    }

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


    // OBSOLETED -- To Be Removed -- 
    function get_mime_info($this_part)
    {
	// rfc2045 says to assume "text" if this if not specified
	$mime_type = "text";
	if (isset($this_part->type) && $this_part->type)
	{
		switch ($this_part->type)
		{
			case TYPETEXT:		$mime_type = "text"; break;
			case TYPEMESSAGE:	$mime_type = "message"; break;
			case TYPEAPPLICATION:	$mime_type = "application"; break;
			case TYPEAUDIO:		$mime_type = "audio"; break;
			case TYPEIMAGE:		$mime_type = "image"; break;
			case TYPEVIDEO:		$mime_type = "video"; break;
			case TYPEMODEL:		$mime_type = "model"; break;
			default:		$mime_type = "text";
		} 
	}
	$mime_info['mime_type'] = $mime_type;

	// assume no info
	$mime_info['subtype'] = 'plain';
	if ((isset($part->ifsubtype)) && ($part->ifsubtype)
	&& (isset($part->subtype)) && ($part->subtype) )
	{
		$mime_info['subtype'] = trim(strtolower($part->subtype));
	}

	// rfc2045 says to assume "7bit" if this is not specified
	$mime_encoding = "7bit";
	if (isset($this_part->encoding) && $this_part->encoding)
	{
		switch ($this_part->encoding)
		{
			case ENC7BIT:		$mime_encoding = "7bit"; break;
			case ENC8BIT:		$mime_encoding = "8bit"; break;
			case ENCBINARY:		$mime_encoding = "binary"; break;
			case ENCBASE64:		$mime_encoding = "base64"; break;
			case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
			case ENCOTHER:		$mime_encoding = "other";  break;
			default:		$mime_encoding = "7bit";
		}
	}
	$mime_info['mime_encoding'] = $mime_encoding;

	$mime_info['mime_params'] = Array();
	if ($this_part->ifparameters)
	{
		for ($i = 0; $i < count($this_part->parameters); $i++) 
		{
			$param = $this_part->parameters[$i];
			$mime_info['mime_params'][$i]['attribute'] = $param->attribute;
			$mime_info['mime_params'][$i]['value'] = $param->value;
		}
	}
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

	// ----  Make a To: string of addresses into an array  -----
	/*
	// param $data should be the desired header string, with one or more addresses, ex:
	// john@doe.com,"Php Group" <info@phpgroupware.org>
	// this will make an array, each numbered item will be this:
	// array[0]['personal'] = ""
	// array[0]['plain'] = "john@doe.com"
	// array[1]['personal'] = "Php Group"
	// array[1]['plain'] = "info@phpgroupware.org"
	*/
	function make_rfc_addy_array($data)
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
			
			// --- Create Compund Array Structure To Hold Decomposed Addresses -----
			// addy_array is a simple numbered array, each element is a addr_spec_array
			$addy_array = Array();
			// $addr_spec_array has this structure:
			//  addr_spec_array['plain'] 
			//  addr_spec_array['personal']

			// decompose addy's into that array, and format according to rfc specs
			for ($i=0;$i<count($data);$i++)
			{
				// trim off leading and trailing whitespaces and \r and \n
				$data[$i] = trim($data[$i]);
				// is this a rfc 2822 compound address (not a simple one)
				if (strstr($data[$i], '" <'))
				{
					// SEPERATE "personal" part from the <x@x.com> part
					$addr_spec_parts = explode('" <', $data[$i]);
					// that got rid of the closing " in personal, now get rig of the first "
					$addy_array[$i]['personal'] = substr($addr_spec_parts[0], 1);
					//  the "<" was already removed, , NOW remove the closing ">"
					$grab_to = strlen($addr_spec_parts[1]) - 1;
					$addy_array[$i]['plain'] = substr($addr_spec_parts[1], 0, $grab_to);

					// QPRINT NON US-ASCII CHARS in "personal" string, as per RFC2047
					// the actual "plain" address may NOT have any other than US-ASCII chars, as per rfc2822
					$addy_array[$i]['personal'] = $this->encode_header($addy_array[$i]['personal']);

					// REVISION: rfc2047 says the following escaping technique is not much help
					// use the encoding above instead
					/*
					// ESCAPE SPECIALS:  rfc2822 requires the "personal" comment string to escape "specials" inside the quotes
					// the non-simple (i.e. "personal" info is included) need special escaping
					// escape these:  ' " ( ) 
					$addy_array[$i]['personal'] = ereg_replace('\'', "\\'", $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace('"', '\"', $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace("(", "\(", $addy_array[$i]['personal']);
					$addy_array[$i]['personal'] = str_replace(")", "\)", $addy_array[$i]['personal']);
					*/
				}
				else
				{
					// this is an old style simple address
					$addy_array[$i]['personal'] = '';
					$addy_array[$i]['plain'] = $data[$i];
				}

				//echo 'addy_array['.$i.'][personal]: '.$this->htmlspecialchars_encode($addy_array[$i]['personal']).'<br>';
				//echo 'addy_array['.$i.'][plain]: '.$this->htmlspecialchars_encode($addy_array[$i]['plain']).'<br>';
			}
			// NO NEED TO SERIALIZE THIS!!!!!
			//$addy_array = serialize($addy_array);
			//echo 'serialized addy_array: '.$addy_array.'<br>';
			return $addy_array;
		}
	}

	function addy_array_to_str($data, $include_personal=True)
	{
		$addy_string = '';
		
		// reconstruct data in the correct email address format
		//if (count($data) == 0)
		//{
		//	$addy_string = '';
		//}
		if (count($data) == 1)
		{
			if (($include_personal == False) || (strlen(trim($data[0]['personal'])) < 1))
			{
				$addy_string = trim($data[0]['plain']);
			}
			else
			{
				$addy_string = '"'.trim($data[0]['personal']).'" <'.trim($data[0]['plain']).'>';
			}
		}
		elseif ($include_personal == False)
		{
			// CLASS SEND CAN NOT HANDLE FOLDED HEADERS OR PERSONAL ADDRESSES
			// this snippit just assembles the headers
			for ($i=0;$i<count($data);$i++)
			{
				// addresses should be seperated by one comma with NO SPACES AT ALL
				$addy_string = $addy_string .trim($data[$i]['plain']) .',';
			}
			// catch any situations where a blank string was included, resulting in two commas with nothing inbetween
			$addy_string = ereg_replace("[,]{2}", ',', $addy_string);
			// trim again, strlen needs to be accurate without trailing spaces included
			$addy_string = trim($addy_string);
			// eliminate that final comma
			$grab_to = strlen($addy_string) - 1;
			$addy_string = substr($addy_string, 0, $grab_to);
		}
		else
		{
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
				// make a string for this individual address
				if (trim($data[$z]['personal']) != '')
				{
					$this_address = '"'.trim($data[$z]['personal']).'" <'.trim($data[$z]['plain']).'>';
				}
				else
				{
					$this_address = trim($data[$z]['plain']);
				}
				// see how long this line would be if this address were added
				//if ($z == 0)
				$cur_len = strlen($header_lines[$line_num]);
				if ($cur_len < 1)
				{
					$would_be_str = $this_address;
				}
				else
				{
					$would_be_str = $header_lines[$line_num] .','.$this_address;
				}
				//echo 'would_be_str: '.$this->htmlspecialchars_encode($would_be_str).'<br>';
				//echo 'strlen(would_be_str): '.strlen($would_be_str).'<br>';
				if ((strlen($would_be_str) > $rfc_max_length)
				&& ($cur_len > 1))
				{
					// Fold Header: RFC2822 "fold" = CRLF followed by a "whitespace" (#9 or #32)
					// preferable to "fold" after the comma, and DO NOT TRIM that white space, preserve it
					//$whitespace = " ";
					$whitespace = chr(9);
					$header_lines[$line_num] = $header_lines[$line_num].','."\r\n";
					// advance to the next line
					$line_num++;
					// now start the new line with the "folding whitespace" then the address
					$header_lines[$line_num] = $whitespace .$this_address;
				}
				else
				{
					// simply comma sep the items (as we did when making "would_be_str")
					$header_lines[$line_num] = $would_be_str;
				}
			}
			// assemble $header_lines array into a single string
			$addy_string = '';
			for ($x=0;$x<count($header_lines);$x++)
			{
				$addy_string = $addy_string .$header_lines[$x];
			}
			$addy_string = trim($addy_string);
		}
		// data leaves here with NO FINAL (trailing) CRLF - will add that later
		return $addy_string;
	}

	// ----  Ensure CR and LF are always together, RFCs prefer the CRLF combo  -----
	function normalize_crlf($data)
	{
		// this is to catch all plain \n instances and replace them with \r\n.  
		$data = ereg_replace("\r\n", "\n", $data);
		$data = ereg_replace("\r", "\n", $data);
		$data = ereg_replace("\n", "\r\n", $data);
		//$data = preg_replace("/(?<!\r)\n/m", "\r\n", $data);
		//$data = preg_replace("/\r(?!\n)/m", "\r\n", $data);
		return $data;
	}

	// ----  Explode by Linebreak, ANY kind of line break  -----
	function explode_linebreaks($data)
	{
		$data = preg_split("/\r\n|\r(?!\n)|(?<!\r)\n/m",$data);
		// match \r\n, OR \r with no \n after it , OR /n with no /r before it
		// modifier m = multiline
		return $data;
	}

	// ----  Create a Unique Mime Boundary  -----
	function make_boundary($part_length=4)
	{
		global $phpgw;
		$part_length = (int)$part_length;
		
		$rand_stuff = Array();
		$rand_stuff[0]['length'] = $part_length;
		$rand_stuff[0]['string'] = $phpgw->common->randomstring($rand_stuff[0]['length']);
		$rand_stuff[0]['rand_numbers'] = '';
		for ($i = 0; $i < $rand_stuff[0]['length']; $i++)
		{
			if ((ord($rand_stuff[0]['string'][$i]) > 47) 
			&& (ord($rand_stuff[0]['string'][$i]) < 58))
			{
				// this char is already a digit
				$rand_stuff[0]['rand_numbers'] .= $rand_stuff[0]['string'][$i];
			}
			else
			{
				// turn this into number form, based on this char's ASCII value
				$rand_stuff[0]['rand_numbers'] .= ord($rand_stuff[0]['string'][$i]);
			}
		}
		$rand_stuff[1]['length'] = $part_length;
		$rand_stuff[1]['string'] = $phpgw->common->randomstring($rand_stuff[1]['length']);
		$rand_stuff[1]['rand_numbers'] = '';
		for ($i = 0; $i < $rand_stuff[1]['length']; $i++)
		{
			if ((ord($rand_stuff[1]['string'][$i]) > 47) 
			&& (ord($rand_stuff[1]['string'][$i]) < 58))
			{
				// this char is already a digit
				$rand_stuff[1]['rand_numbers'] .= $rand_stuff[1]['string'][$i];
			}
			else
			{
				// turn this into number form, based on this char's ASCII value
				$rand_stuff[1]['rand_numbers'] .= ord($rand_stuff[1]['string'][$i]);
			}
		}
		$unique_boundary = '---=_Next_Part_'.$rand_stuff[0]['rand_numbers'].'_'.$phpgw->common->randomstring($part_length)
			.'_'.$phpgw->common->randomstring($part_length).'_'.$rand_stuff[1]['rand_numbers'];
		
		return $unique_boundary;
	}

	// ----  Create a Unique RFC2822 Message ID  -----
	function make_message_id()
	{
		global $phpgw, $phpgw_info;
		
		if ($phpgw_info['server']['hostname'] != '')
		{
			$id_suffix = $phpgw_info['server']['hostname'];
		}
		else
		{
			$id_suffix = $phpgw->common->randomstring(3).'local';
		}
		// gives you timezone dot microseconds space datetime
		$stamp = microtime();
		$stamp = explode(" ",$stamp);
		// get rid of tomezone info
		$grab_from = strpos($stamp[0], ".") + 1;
		$stamp[0] = substr($stamp[0], $grab_from);
		// formay the datetime into YYYYMMDD
		$stamp[1] = date('Ymd', $stamp[1]);
		// a small random string for the middle
		$rand_middle = $phpgw->common->randomstring(3);
		
		$mess_id = '<'.$stamp[1].'.'.$rand_middle.'.'.$stamp[0].'@'.$id_suffix.'>';
		return $mess_id;
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

	
	// SUB-FUNCTION - do not call directly
	function encode_iso88591_word($string)
	{
		$qprint_prefix = '=?iso-8859-1?Q?';
		$qprint_suffix = '?=';
		$new_str = '';
		$did_encode = False;
		
		for( $i = 0 ; $i < strlen($string) ; $i++ )
		{
			$val = ord($string[$i]);
			// my interpetation of what to encode from RFC2045 and RFC2822
			if ( (($val >= 1) && ($val <= 31))
			|| (($val >= 33) && ($val <= 47))
			|| ($val == 61)
			|| ($val == 62)
			|| ($val == 64)
			|| (($val >= 91) && ($val <= 94))
			|| ($val == 96)
			|| ($val >= 123))
			{
				$did_encode = True;
				//echo 'val needs encode: '.$val.'<br>';
				$val = dechex($val);
				// rfc2045 requires quote printable HEX letters to be uppercase
				$val = strtoupper($val);
				//echo 'val AFTER encode: '.$val.'<br>';
				//$text .= '='.$val;
				$new_str = $new_str .'='.$val;
			}
			else
			{
				$new_str = $new_str . $string[$i];
			}
		}
		if ($did_encode)
		{
			$new_str =  $qprint_prefix .$new_str .$qprint_suffix;
		}
		return $new_str;
	}
	
	
	function encode_header($data)
	{
		// explode string into an array or words
		$words = explode(' ', $data);
		
		for($i=0; $i<count($words); $i++)
		{
			//echo 'words['.$i.'] in loop: '.$words[$i].'<br>';
			
			// my interpetation of what to encode from RFC2045, RFC2047, and RFC2822
			if (preg_match('/'
				. '['.chr(1).'-'.chr(31).']'
				. '['.chr(33).'-'.chr(38).']'
				.'|[\\'.chr(39).']'
				.'|['.chr(40).'-'.chr(46).']'
				.'|[\\'.chr(47).']'
				.'|['.chr(61).'-'.chr(62).']'
				.'|['.chr(64).']'
				.'|['.chr(91).'-'.chr(94).']'
				.'|['.chr(96).']'
				.'|['.chr(123).'-'.chr(255).']'
				.'/', $words[$i]))
			{
				/*
				// qprint this word, and add rfc2047 header special words
				$len_before = strlen($words[$i]);
				echo 'words['.$i.'] needs encode: '.$words[$i].'<br>';
				$words[$i] = imap_8bit($words[$i]);
				echo 'words['.$i.'] AFTER encode: '.$words[$i].'<br>';
				// php may not encode everything that I expect, so check to see if encoding happened
				$len_after = strlen($words[$i]);
				if ($len_before != $len_after)
				{
					// indeed, encoding did happen, add rfc2047 header special words
					$words[$i] = $qprint_prefix .$words[$i] .$qprint_suffix;
				}
				*/
				
				// qprint this word, and add rfc2047 header special words
				//echo 'words['.$i.'] needs encode: '.$words[$i].'<br>';
				$words[$i] = $this->encode_iso88591_word($words[$i]);
				//echo 'words['.$i.'] AFTER encode: '.$words[$i].'<br>';
			}
		}
		
		// reassemble the string
		$encoded_str = implode(' ',$words);
		return $encoded_str;
	}

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
		// these {  and  }  must be html encoded or else they conflict with the template system
		//$str = str_replace("{", '&#123;', $str);
		//$str = str_replace("}", '&#125;', $str);
		return $str;
	}

	function htmlspecialchars_decode($str)
	{
		/*// reverse of htmlspecialchars */
		//$str = str_replace('&#125;', "}", $str);
		//$str = str_replace('&#123;', "{", $str);
		
		$str = ereg_replace('&gt;', '>', $str);
		$str = ereg_replace('&lt;', '<', $str);
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&quot;', '"', $str);
		$str = ereg_replace('&amp;', '&', $str);
		return $str;
	}

	function html_quotes_encode($str)
	{
		// replace  '  and  "  with htmlspecialchars
		$str = ereg_replace('"', '&quot;', $str);
		$str = ereg_replace('\'', '&#039;', $str);
		// NEEDED: add  /  and  \  to this
		return $str;
	}

	function html_quotes_decode($str)
	{
		// reverse of htmlspecialchars
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&quot;', '"', $str);
		// NEEDED: add  /  and  \  to this
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

// ----  Functions PHP Should Have OR Functions From PHP4+ Backported to PHP3  ---------
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

	// PHP3 SAFE Version of "substr_count"
	/*!
	@function substr_count_ex
	@abstract returns the number of times the "needle" substring occurs in the "haystack" string
	@param $haystack  string
	@param $needle  string
	*/
	function substr_count_ex($haystack='', $needle='')
	{
		if (($haystack == '') || ($needle == ''))
		{
			return 0;
		}

		$crtl_struct = Array();
		// how long is needle
		$crtl_struct['needle_len'] = strlen($needle);
		// how long is haystack before the replacement
		$crtl_struct['haystack_orig_len'] = strlen($haystack);
		
		// we will replace needle with a BLANK STRING
		$crtl_struct['haystack_new'] = str_replace("$needle",'',$haystack);
		// how long is the new haystack string
		$crtl_struct['haystack_new_len'] = strlen($crtl_struct['haystack_new']);
		// the diff in length between orig haystack and haystack_new diveded by len of needle = the number of occurances of needle
		$crtl_struct['substr_count'] = ($crtl_struct['haystack_orig_len'] - $crtl_struct['haystack_new_len']) / $crtl_struct['needle_len'];
		
		//echo '<br>';
		//var_dump($crtl_struct);
		//echo '<br>';
		
		// return the finding
		return $crtl_struct['substr_count'];
	}

  } // end of class msg_common
?>
