<?php
  /**************************************************************************\
  * phpGroupWare API - MAIL                                                  *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Handles general functionality for mail/mail structures                   *
  * Copyright (C) 2001 Mark Peters                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  define('SA_MESSAGES',1);
  define('SA_RECENT',2);
  define('SA_UNSEEN',4);
  define('SA_UIDNEXT',8);
  define('SA_UIDVALIDITY',16);
  define('SA_ALL',31);

  define('SORTDATE',0);
  define('SORTARRIVAL',1);
  define('SORTFROM',2);
  define('SORTSUBJECT',3);
  define('SORTTO',4);
  define('SORTCC',5);
  define('SORTSIZE',6);

  define ('TYPETEXT',0);
  define ('TYPEMULTIPART',1);
  define ('TYPEMESSAGE',2);
  define ('TYPEAPPLICATION',3);
  define ('TYPEAUDIO',4);
  define ('TYPEIMAGE',5);
  define ('TYPEVIDEO',6);
  define ('TYPEOTHER',7);
  //  define ('TYPEMODEL',
  define ('ENC7BIT',0);
  define ('ENC8BIT',1);
  define ('ENCBINARY',2);
  define ('ENCBASE64',3);
  define ('ENCQUOTEDPRINTABLE',4);
  define ('ENCOTHER',5);
  define ('ENCUU',6);
  
  define ('FT_UID',0);	// the msgnum is a UID
  define ('FT_PEEK',1);	// do not set the \Seen flag if not already set
  define ('FT_INTERNAL',2); // server will not attempt to standardize CRLFs
  define ('FT_NOT',3);	// do not fetch header lines (with IMAP_BODY)
  define ('FT_PREFETCHTEXT',4); // grab the header AND its associated RFC822.TEXT

  class mailbox_status
  {
	var $messages = '';
	var $recent = '';
	var $unseen = '';
	var $uidnext = '';
	var $uidvalidity = '';
	// quota and quota_all not in php builtin
	var $quota = '';
	var $quota_all = '';
	/*
	see PHP function: imap_status --  This function returns status information on a mailbox other than the current one
	SA_MESSAGES - set status->messages to the number of messages in the mailbox
	SA_RECENT - set status->recent to the number of recent messages in the mailbox
	SA_UNSEEN - set status->unseen to the number of unseen (new) messages in the mailbox
	SA_UIDNEXT - set status->uidnext to the next uid to be used in the mailbox
	SA_UIDVALIDITY - set status->uidvalidity to a constant that changes when uids for the mailbox may no longer be valid
	SA_ALL - set all of the above
	*/
  }

  class msg_mb_info
  {
	var $Date = '';
	var $Driver ='';
	var $Mailbox = '';
	var $Nmsgs = '';
	var $Recent = '';
	var $Unread = '';
	var $Size = '';
	/*
	see PHP function: imap_mailboxmsginfo -- Get information about the current mailbox
	Date		date of last change
	Driver		driver
	Mailbox		name of the mailbox
	Nmsgs		number of messages
	Recent		number of recent messages
	Unread		number of unread messages
	Deleted		number of deleted messages
	Size		mailbox size
	*/
  }

	/*
	Discussion: imap_mailboxmsginfo  vs.  imap_status
	note 1): 	IMAP uses imap_mailboxmsginfo for the folder it's currently logged into,
		and IMAP uses imap_status for info on a folder it is NOT currently logged into
	note 2)	imap_mailboxmsginfo returns size data, imap_status does NOT
	*/

  class msg_struct
  {
	var $type = '';
	var $encoding = '';
	var $ifsubtype = False;
	var $subtype = '';
	var $ifdescription = False;
	var $description = '';
	var $ifid = False;
	var $id = '';
	var $lines = '';
	var $bytes = '';
	var $ifdisposition = False;
	var $disposition = '';
	var $ifdparameters = False;
	var $dparameters = array();
	var $ifparameters = False;
	var $parameters = array();
	var $parts = array();
	/*
	see PHP function: imap_fetchstructure --  Read the structure of a particular message
	type		Primary body type
	encoding		Body transfer encoding
	ifsubtype		TRUE if there is a subtype string
	subtype		MIME subtype
	ifdescription	TRUE if there is a description string
	description	Content description string
	ifid		TRUE if there is an identification string
	id		Identification string
	lines		Number of lines
	bytes		Number of bytes
	ifdisposition	TRUE if there is a disposition string
	disposition	Disposition string
	ifdparameters	TRUE if the dparameters array exists
	dparameters	Disposition parameter array
	ifparameters	TRUE if the parameters array exists
	parameters	MIME parameters array
	parts		Array of objects describing each message part
	*/
  }
  
  // gonna have to decide on one of the next two
  class msg_params
  {
	var $attribute;
	var $value;

	function msg_params($attrib,$val)
	{
		$this->attribute = $attrib;
		$this->value     = $val;
	}
  }
  class att_parameter
  {
	var $attribute;
	var $value;
  }

  class address
  {
	var $personal;
	var $mailbox;
	var $host;
	var $adl;
  }

  class envelope
  {
	// see PHP function:  imap_headerinfo -- Read the header of the message
	// which is the same as PHP function imap_header
	// --- Various Header Data ---
	var $remail = '';
	var $date = '';
	var $subject = '';
	var $in_reply_to = '';
	var $message_id = '';
	var $newsgroups = '';
	var $followup_to = '';
	var $references = '';
	// --- Message Flags ---
	var $Recent = '';		//  'R' if recent and seen, 'N' if recent and not seen, ' ' if not recent
	var $Unseen = '';		//  'U' if not seen AND not recent, ' ' if seen OR not seen and recent
	var $Answered = '';	//  'A' if answered, ' ' if unanswered
	var $Deleted = '';		//  'D' if deleted, ' ' if not deleted
	var $Draft = '';		//  'X' if draft, ' ' if not draft
	var $Flagged = '';		//  'F' if flagged, ' ' if not flagged
	// --- To, From, etc... Data ---
	var $toaddress = '';	// up to 1024 characters of the To: line
	var $to;			// array of these objects from the To line, containing:
				//	to->personal ; to->adl ; to->mailbox ; to->host
				// 	this applies to From, Cc, Bcc, etc... below
	var $fromaddress = '';	// up to 1024 characters of the From: line
	var $from;
	var $ccaddress = '';	// up to 1024 characters of the Cc: line
	var $cc;
	var $bccaddress = '';	// up to 1024 characters of the Bcc: line
	var $bcc;
	var $reply_toaddress = '';	// up to 1024 characters of the Reply_To: line
	var $reply_to;
	var $senderaddress = '';	// up to 1024 characters of the Sender: line
	var $sender;
	var $return_pathaddress = '';	// up to 1024 characters of the Return-Path: line
	var $return_path;
	var $udate = '';		// mail message date in unix time
	// --- Specially Formatted Data ---
	var $fetchfrom = '';	// from line formatted to fit arg "fromlength" characters
	var $fetchsubject = '';	// subject line formatted to fit arg "subjectlength" characters
	var $lines = '';
	var $Size = '';
  }

  class mail_dcom_base extends network
  {
	var $header=array();
	var $msg;
	var $msg_struct;
	var $body;
	var $mailbox;
	var $numparts;

	var $sparts;
	var $hsub=array();
	var $bsub=array();

	var $php_builtin=False;
	// DEBUG FLAG
	//var $debug_dcom=True;
	var $debug_dcom=False;
	//var $debug_dcom_extra=True;
	var $debug_dcom_extra=False;
	
	function mail_dcom_base()
	{
		global $phpgw_info;

		$this->errorset = 0;
		$this->network(True);
		if (isset($phpgw_info))
		{
			$this->tempfile = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'].'.mhd';
			$this->att_files_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];
		}
		else
		{
			// NEED GENERIC DEFAULT VALUES HERE
			
		}
	}

	function error()
	{
		global $phpgw;
		
		echo 'Error: '.$this->error['code'].' : '.$this->error['msg'].' - '.$this->error['desc']."<br>\n";
		$phpgw->common->phpgw_exit();
	}

	// REDUNDANT FUNCTION FROM NON-SOCK CLASS
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
	function distill_fq_folder($fq_folder)
	{
		// initialize return structure array
		$svr_data = Array();
		$svr_data['folder'] = '';
		$svr_data['svr_and_port'] = '';
		$svr_data['server'] = '';
		$svr_data['port_with_junk'] = '';
		$svr_data['port'] = '';
		
		// see if we have any data to work with
		if ((!isset($fq_folder))
		|| ((trim($fq_folder) == '')))
		{
			// no data, return the reliable default of INBOX
			$svr_data['folder'] = 'INBOX';
			return $svr_data;
			// we're out'a here
		}
		
		// see if this is indeed a fully qualified folder name
		if (strstr($fq_folder,'}') == False)
		{
			// all we have is a _simple_ folder name, no server or port info included
			$svr_data['folder'] = $fq_folder;
			return $svr_data;
			// we're out'a here
		}
		
		// -- (1) -- get the folder name stripped of the server string
		// folder name at this stage is  {SERVER_NAME:PORT}FOLDERNAME
		// ORsome variation like this:
		// {SERVER_NAME:PORT/pop3}FOLDERNAME
		// {SERVER_NAME:PORT/imap/ssl/novalidate-cert}FOLDERNAME
		// get everything to the right of the bracket "}", INCLUDES the bracket itself
		$svr_data['folder'] = strstr($fq_folder,'}');
		// get rid of that 'needle' "}"
		$svr_data['folder'] = substr($svr_data['folder'], 1);
		// -- (2) -- get the {SERVER_NAME:PORT} part and strip the brackets
		$svr_callstr_len = strlen($fq_folder) - strlen($svr_data['folder']);
		// start copying at position 1 skipping the opening bracket
		// and stop copying at length of {SERVER_NAME:PORT} - 2 to skip the closing beacket
		$svr_data['svr_and_port'] = substr($fq_folder, 1, $svr_callstr_len - 2);
		// -- (3)-- get the port number INCLUDING any junk that may come after it, like "/pop3/ssl/novalidate-cert"
		// "svr_and_port" at this stage is  SERVER_NAME:PORT , or SERVER_NAME:PORT/pop3  , etc...
		// get everything to the right of the colon ":", INCLUDES the colon itself
		$svr_data['port_with_junk'] = strstr($svr_data['svr_and_port'],':');
		// get rid of that 'needle' ":"
		$svr_data['port_with_junk'] = substr($svr_data['port_with_junk'], 1);
		// -- (4)-- get the server name 
		// port_with_junk + 1 means the port number with the added 1 char length of the colon we got rid of just above
		$svr_only_len = strlen($svr_data['svr_and_port']) - strlen($svr_data['port_with_junk']);
		// $svr_only_len - 1 means leave out the 1 char length of the colon we stripped deom "port_with_junk" above
		$svr_data['server'] = substr($svr_data['svr_and_port'], 0, $svr_only_len - 1);
		// -- (5)-- get the port number , stripping any junk that _may_ be with it
		//  get everything to the right of the forst slash "/", INCLUDES the slash itself, else returns FALSE
		$port_junk = strstr($svr_data['port_with_junk'],'/');
		// test
		//$svr_data['port'] = $port_junk;
		if ($port_junk)
		{
			$port_only_len = strlen($svr_data['port_with_junk']) - strlen($port_junk);
			$svr_data['port'] = substr($svr_data['port_with_junk'], 0, $port_only_len);
		}

		return $svr_data;
	}

	function read_port_glob($end='.')
	{
		$glob_response = '';
		while ($line = $this->read_port())
		{
			//echo $line."<br>\r\n";
			if (chop($line) == $end)
			{
				break;
			}
			$glob_response .= $line;
		}
		return $glob_response;
	}

	function glob_to_array($data,$keep_blank_lines=True,$cut_from_here='',$keep_received_lines=False)
	{
		
		$data_array = explode("\r\n",$data);
		$return_array = Array();
		for($i=0;$i < count($data_array);$i++)
		{
			$new_str = $data_array[$i];
			if ($cut_from_here != '')
			{
				$cut_here = strpos($new_str,$cut_from_here);
				if ($cut_here > 0)
				{
					$new_str = substr($new_str,0,$cut_here);
				}
				else
				{
					$new_str = '';
				}
			}
			if (($keep_blank_lines == False)
			&& (trim($new_str) == ''))
			{
				// do noting
			}
			elseif (($keep_received_lines == False)
			&& (stristr($new_str, 'received:'))
			&& (strpos(strtolower($new_str),'received:') == 0))
			{
				// do noting
			}			
			else
			{
				$return_array[count($return_array)] = $new_str;
			}
		}
		return $return_array;
	}


	function create_header($line,$header,$line2='')
	{
		$thead = explode(':',$line);
		$key = trim($thead[0]);
		switch(count($thead))
		{
			case 1:
				$value = TRUE;
				break;
			case 2:
				$value = trim($thead[1]);
				break;
			default: 
				$thead[0] = '';
				$value = '';
				for($i=1,$j=count($thead);$i<$j;$i++)
				{
					$value .= $thead[$i].':';
				}
//				$value = trim($value.$thead[$j++]);
//				$value = trim($value);
				break;
		}
		$header[$key] = $value;
		if (ereg('^multipart/mixed;',$value))
		{
			if (! ereg('boundary',$header[$key]))
			{
				if ($line2 == 'True')
				{
					$line2 = $this->read_port();
					echo "Response = ".$line2."<br>\n";
				}
			}
			$header[$key] .= chop($line2);
		}
//		echo "Header[$key] = ".$header[$key]."<br>\n";
	}

	function build_address_structure($key)
	{
		$address = array(new address);
		// Build Address to Structure
		$temp_array = explode(';',$this->header[$key]);
		for ($i=0;$i<count($temp_array);$i++)
		{
			$this->decode_author($temp_array[$i],&$email,&$name);
			$temp = explode('@',$email);
			$address[$i]->personal = $this->decode_header($name);
			$address[$i]->mailbox = $temp[0];
			if (count($temp) == 2)
			{
				$address[$i]->host = $temp[1];
				$address[$i]->adl = $email;
			}
			return $address;
		}
	}

	function convert_date_array($field_list)
	{
		$new_list = Array();
		while(list($key,$value) = each($field_list))
		{
			$new_list[$key] = $this->convert_date($value);
		}
		return $new_list;
	}

	function convert_date($msg_date)
	{
		global $phpgw_info;
		
//		This may need to be a reference to the different months in native tongue....
		$month = Array(
			'Jan' => 1,
			'Feb' => 2,
			'Mar' => 3,
			'Apr' => 4,
			'May' => 5,
			'Jun' => 6,
			'Jul' => 7,
			'Aug' => 8,
			'Sep' => 9,
			'Oct' => 10,
			'Nov' => 11,
			'Dec' => 12
		);
		$dta = array();
		$ta = array();

		// Convert "Sat, 15 Jul 2000 20:50:22 +0200" to unixtime
		$comma = strpos($msg_date,',');
		if($comma)
		{
			$msg_date = substr($msg_date,$comma + 2);
		}
//		echo 'Msg Date : '.$msg_date."<br>\n";
		$dta = explode(' ',$msg_date);
		$ta = explode(':',$dta[3]);

		if(substr($dta[4],0,3) <> 'GMT')
		{
			$tzoffset = substr($dta[4],0,1);
			(int)$tzhours = substr($dta[4],1,2);
			(int)$tzmins = substr($dta[4],3,2);
			switch ($tzoffset)
			{
				case '+': 
					(int)$ta[0] -= (int)$tzhours;
					(int)$ta[1] -= (int)$tzmins;
					break;
				case '-':
					(int)$ta[0] += (int)$tzhours;
					(int)$ta[1] += (int)$tzmins;
					break;
			}
		}

		$new_time = mktime($ta[0],$ta[1],$ta[2],$month[$dta[1]],$dta[0],$dta[2]) - ((60 * 60) * intval($phpgw_info['user']['preferences']['common']['tzoffset']));
//		echo 'New Time : '.$new_time."<br>\n";
		return $new_time;
	}

	function make_udate($msg_date)
	{
		// used only by pop_header
		$pos = strpos($msg_date,",");
		if ($pos)
		{
			$msg_date = trim(substr($msg_date,$pos+1));
		}
		$pos = strpos($msg_date," ");
		$day = substr($msg_date,0,$pos);
		$msg_date = trim(substr($msg_date,$pos));
		$month = substr($msg_date,0,3);
		switch (strtolower($month))
		{
			case "jan" : $month =  1; break;
			case "feb" : $month =  2; break;
			case "mar" : $month =  3; break;
			case "apr" : $month =  4; break;
			case "may" : $month =  5; break;
			case "jun" : $month =  6; break;
			case "jul" : $month =  7; break;
			case "aug" : $month =  8; break;
			case "sep" : $month =  9; break;
			case "oct" : $month = 10; break;
			case "nov" : $month = 11; break;
			default    : $month = 12; break;
		}
		$msg_date = trim(substr($msg_date,3));
		$pos  = strpos($msg_date," ");
		$year = trim(substr($msg_date,0,$pos));
		$msg_date = trim(substr($msg_date,$pos));
		$hour = substr($msg_date,0,2);
		$minute = substr($msg_date,3,2);
		$second = substr($msg_date,6,2);
		$pos = strrpos($msg_date," ");
		$tzoff = trim(substr($msg_date,$pos));
		if (strlen($tzoff)==5)
		{
			$diffh = substr($tzoff,1,2); $diffm = substr($tzoff,3);
			if ((substr($tzoff,0,1)=="+") && is_int($diffh))
			{
				$hour -= $diffh; $minute -= $diffm;
			}
			else
			{
				$hour += $diffh; $minute += $diffm;
			}
		}
		$utime = mktime($hour,$minute,$second,$month,$day,$year);
		return $utime;
	}

	function ssort_prep($a)
	{
		$a = strtoupper($a);
		if(strpos(' '.$a,'FW: ') == 1 || strpos(' '.$a,'RE: ') == 1)
		{
			$a_mod = substr($a,4);
		}
		elseif(strpos(' '.$a,'FWD: ') == 1)
		{
			$a_mod = substr($a,5);
		}
		else
		{
			$a_mod = $a;
		}
		
		while(substr($a_mod,0,1) == ' ')
		{
			$a_mod = substr($a_mod,1);
		}

//		if(strpos(' '.$a_mod,'[') == 1)
//		{
//			$a_mod = substr($a_mod,1);
//		}
		return $a_mod;
	}
	
	function ssort_ascending($a,$b)
	{
		$a_mod = $this->ssort_prep($a);
		$b_mod = $this->ssort_prep($b);
		if ($a_mod == $b_mod)
		{
			return 0;
		}
		return ($a_mod < $b_mod) ? -1 : 1;
	}

	function ssort_decending($a,$b)
	{
		$a_mod = $this->ssort_prep($a);
		$b_mod = $this->ssort_prep($b);
		if ($a_mod == $b_mod)
		{
			return 0;
		}
		return ($a_mod > $b_mod) ? -1 : 1;
	}
	
	function mail_header($msgnum)
	{
		$this->msg = new msg;
		// This needs to be pulled back to the actual read header of the mailer type.
//		$this->mail_fetch_overview($msgnum);

		// From:
		$this->msg->from = array(new address);
		$this->msg->from = $this->build_address_structure('From');
		$this->msg->fromaddress = $this->header['From'];

		// To:
		$this->msg->to = array(new address);
		if (strtolower($this->type) == 'nntp')
		{
			$temp = explode(',',$this->header['Newsgroups']);
			$to = array(new address);
			for($i=0;$i<count($temp);$i++)
			{
				$to[$i]->mailbox = '';
				$to[$i]->host = '';
				$to[$i]->personal = $temp[$i];
				$to[$i]->adl = $temp[$i];
			}
			$this->msg->to = $to;
		}
		else
		{
			$this->msg->to = $this->build_address_structure('To');
			$this->msg->toaddress = $this->header['To'];
		}

		// Cc:
		$this->msg->cc = array(new address);
		if(isset($this->header['Cc']))
		{
			$this->msg->cc[] = $this->build_address_structure('Cc');
			$this->msg->ccaddress = $this->header['Cc'];
		}
    
		// Bcc:
		$this->msg->bcc = array(new address);
		if(isset($this->header['bcc']))
		{
			$this->msg->bcc = $this->build_address_structure('bcc');
			$this->msg->bccaddress = $this->header['bcc'];
		}

		// Reply-To:
		$this->msg->reply_to = array(new address);
		if(isset($this->header['Reply-To']))
		{
			$this->msg->reply_to = $this->build_address_structure('Reply-To');
			$this->msg->reply_toaddress = $this->header['Reply-To'];
		}

		// Sender:
		$this->msg->sender = array(new address);
		if(isset($this->header['Sender']))
		{
			$this->msg->sender = $this->build_address_structure('Sender');
			$this->msg->senderaddress = $this->header['Sender'];
		}

		// Return-Path:
		$this->msg->return_path = array(new address);
		if(isset($this->header['Return-Path']))
		{
			$this->msg->return_path = $this->build_address_structure('Return-Path');
			$this->msg->return_pathaddress = $this->header['Return-Path'];
		}

		// UDate
		$this->msg->udate = $this->convert_date($this->header['Date']);

		// Subject
		$this->msg->subject = $this->phpGW_quoted_printable_decode($this->header['Subject']);

		// Lines
		// This represents the number of lines contained in the body
		$this->msg->lines = $this->header['Lines'];
	}

	function mail_headerinfo($msgnum)
	{
		$this->mail_header($msgnum);
	}

	function read_and_load($end)
	{
		$this->header = Array();
		while ($line = $this->read_port())
		{
//			echo $line."<br>\n";
			if (chop($line) == $end) break;
			$this->create_header($line,&$this->header,"True");
		}
		return 1;
	}


	/*
	 * PHP `quoted_printable_decode` function does not work properly:
	 * it should convert '_' characters into ' '.
	*/
	function phpGW_quoted_printable_decode($string)
	{
		$string = str_replace('_', ' ', $string);
		return quoted_printable_decode($string);
	}

	/*
	 * Remove '=' at the end of the lines. `quoted_printable_decode` doesn't do it.
	*/
	function phpGW_quoted_printable_decode2($string)
	{
		$string = $this->phpGW_quoted_printable_decode($string);
		return preg_replace("/\=\n/", '', $string);
	}

	function decode_base64($string)
	{
		$string = ereg_replace("'", "\'", $string);
		$string = preg_replace("/\=\?(.*?)\?b\?(.*?)\?\=/ieU",base64_decode("\\2"),$string);
		return $string;
	}

	function decode_qp($string)
	{
		$string = ereg_replace("'", "\'", $string);
		$string = preg_replace("/\=\?(.*?)\?q\?(.*?)\?\=/ieU",$this->phpGW_quoted_printable_decode2("\\2"),$string);
		return $string;
	}

	function decode_header($string)
	{
		/* Decode from qp or base64 form */
		if (preg_match("/\=\?(.*?)\?b\?/i", $string))
		{
			return $this->decode_base64($string);
		}
		if (preg_match("/\=\?(.*?)\?q\?/i", $string))
		{
			return $this->decode_qp($string);
		}
		return $string;
	}

	function decode_author($author,&$email,&$name)
	{
		/* Decode from qp or base64 form */
		$author = $this->decode_header($author);
		/* Extract real name and e-mail address */
		/* According to RFC1036 the From field can have one of three formats:
			1. Real Name <name@domain.name>
			2. name@domain.name (Real Name)
			3. name@domain.name
		*/
		/* 1st case */
//		if (eregi("(.*) <([-a-z0-9\_\$\+\.]+\@[-a-z0-9\_\.]+[-a-z0-9\_]+)>",
		if (eregi("(.*) <([-a-z0-9_$+.]+@[-a-z0-9_.]+[-a-z0-9_]+)>",$author, $regs))
		{
			$email = $regs[2];
			$name = $regs[1];
		}
		/* 2nd case */
		elseif (eregi("([-a-z0-9_$+.]+@[-a-z0-9_.]+[-a-z0-9_]+) ((.*))",$author, $regs))
		{
//		if (eregi("([-a-z0-9\_\$\+\.]+\@[-a-z0-9\_\.]+[-a-z0-9\_]+) \((.*)\)",$author, $regs))
			$email = $regs[1];
			$name = $regs[2];
		}
		/* 3rd case */
		else
		{
			$email = $author;
		}
		if ($name == '')
		{
			$name = $email;
		}
		$name = eregi_replace("^\"(.*)\"$", "\\1", $name);
		$name = eregi_replace("^\((.*)\)$", "\\1", $name);
	}

	function get_mime_type($de_part)
	{
		if (!isset($de_part->type))
		{
			return 'unknown';
		}
		else
		{
			return $this->type_int_to_str($de_part->type);
		}
	}

	function type_int_to_str($type_int)
	{
		switch ($type_int)
		{
			case TYPETEXT		: $type_str = 'text'; break;
			case TYPEMULTIPART	: $type_str = 'multipart'; break;
			case TYPEMESSAGE		: $type_str = 'message'; break;
			case TYPEAPPLICATION	: $type_str = 'application'; break;
			case TYPEAUDIO		: $type_str = 'audio'; break;
			case TYPEIMAGE		: $type_str = 'image'; break;
			case TYPEVIDEO		: $type_str = 'video'; break;
			case TYPEOTHER		: $type_str = 'other'; break;
			default			: $type_str = 'unknown';
		}
		return $type_str;
	}

	function get_mime_encoding($de_part)
	{
		if (!isset($de_part->encoding))
		{
			return 'other';
		}
		else
		{
			$encoding_str = $this->type_int_to_str($de_part->encoding);
			if ($encoding_str == 'quoted-printable')
			{
				$encoding_str = 'qprint';
			}
			return $encoding_str;
		}
	}

	function encoding_int_to_str($encoding_int)
	{
		switch ($encoding_int)
		{
			case ENC7BIT	: $encoding_str = '7bit'; break;
			case ENC8BIT	: $encoding_str = '8bit'; break;
			case ENCBINARY	: $encoding_str = 'binary';  break;
			case ENCBASE64	: $encoding_str = 'base64'; break;
			case ENCQUOTEDPRINTABLE : $encoding_str = 'quoted-printable'; break;
			case ENCOTHER	: $encoding_str = 'other';  break;
			case ENCUU	: $encoding_str = 'uu';  break;
			default		: $encoding_str = 'other';
		}
		return $encoding_str;
	}

	function get_att_name($de_part)
	{
		$param = new parameter;
		$att_name = 'Unknown';
		if (!isset($de_part->parameters))
		{
			return $att_name;
		}
		for ($i=0;$i<count($de_part->parameters);$i++)
		{
			$param=(!$de_part->parameters[$i]?$de_part->parameters:$de_part->parameters[$i]);
			if(!$param)
			{
				break;
			}
			$pattribute = $param->attribute;
			if (strtolower($pattribute) == 'name')
			{
				$att_name = $param->value;
			}
		}
		return $att_name;
	}

	/*
	function attach_display($de_part,$part_no,$mailbox,$folder,$msgnum)
	{
		global $phpgw, $phpgw_info;
		$mime_type = $this->get_mime_type($de_part);  
		$mime_encoding = $this->get_mime_encoding($de_part);

		$att_name = 'unknown';
		$param = new parameter;

		for ($i = 0; $i < count($de_part->parameters); $i++)
		{
			if(!$de_part->parameters[$i])
			{
				break;
			}
			$param = $de_part->parameters[$i];
			$pattribute = $param->attribute;
			if (strtoupper($pattribute) == 'NAME')
			{
				$att_name = $param->value;
				$url_att_name = urlencode($att_name);
			}
		}

		return '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/get_attach.php',
					 'folder='.$folder.'&msgnum='.$msgnum.'&part_no='.$partno.'&type='.$mime_type
					.'&subtype='.$de_part->subtype.'&name='.$url_att_name.'&encoding='.$mime_encoding)
				.'">'.$att_name.'</a>';
	}

	function inline_display($de_part,$dsp,$mime_section,$folder)
	{
		global $phpgw;
		
		$mime_type = $this->get_mime_type($de_part);
		$mime_encoding = $this->get_mime_encoding($de_part);
		$tag = 'pre';
//		$jnk = isset($de_part->disposition) ? $de_part->disposition : 'unknown';

//		echo "<!-- MIME disp: $jnk -->\n";
//		echo "<!-- MIME type: $mime_type -->\n";
//		echo "<!-- MIME subtype: $de_part->subtype -->\n";
//		echo "<!-- MIME encoding: $mime_encoding -->\n";
//		echo "<!-- MIME filename: $att_name -->\n";

		if ($mime_encoding == 'qprint')
		{
			$dsp = $this->decode_qp($dsp);
			$tag = 'tt';
		}

		// Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
		// a better way to do message wrapping

		if (isset($de_part->subtype) && strtoupper($de_part->subtype) == 'PLAIN')
		{
			// nlbr and htmlentities functions are strip latin5 characters
			$dsp = $phpgw->strip_html($dsp);
			$dsp = ereg_replace( "^","<p>",$dsp);
			$dsp = ereg_replace( "\r\n","<br>",$dsp);
			$dsp = ereg_replace( "\n","<br>",$dsp);
			$dsp = ereg_replace( "\t","    ",$dsp);
			$dsp = ereg_replace( "$","</p>", $dsp);
			$dsp = $this->make_clickable($dsp,$folder);
			return '<table border="0" align="left" cellpadding="10" width="80%"><tr><td>'.$dsp.'</td></tr></table>';
		}
		elseif (isset($de_part->subtype) && strtoupper($de_part->subtype) == 'HTML')
		{
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.$dsp;
		}
		elseif (isset($de_part->subtype) && 
			(strtoupper($de_part->subtype) == 'JPG' ||
			 strtoupper($de_part->subtype) == 'JPEG' ||
			 strtoupper($de_part->subtype) == 'PJPEG' ||
			 strtoupper($de_part->subtype) == 'GIF' ||
			 strtoupper($de_part->subtype) == 'PNG'))
		{
			$att_name = $this->get_att_name($de_part);
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.$this->image_display($dsp,$att_name);
		}
		else
		{
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.'<'.$tag.'>'.$dsp.'</'.$tag.'>'."\n";
		}
	}

	function output_bound($title, $str)
	{
		global $phpgw_info;

		return '</td></tr></table>'."\n"
			. '<table border="0" cellpadding="4" cellspacing="3" width="700">'."\n"
			. '<tr><td bgcolor"'.$phpgw_info['theme']['th_bg'].'" valign="top">'
			. '<font size="2" face="'.$phpgw_info['theme']['font'].'"><b>'.$title.'</b></td>'."\n"
			. '<td bgcolor="'.$phpgw_info['theme']['row_on'].'" width="570">'
			. '<font size="2" face="'.$phpgw_info['theme']['font'].'">'.$str.'</td></tr></table>'."\n"
			. '<p>'."\n".'<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td>';
	}

	function image_display($bsub,$att_name)
	{
		global $phpgw, $phpgw_info;

		$bsub = strip_tags($bsub);
		$unique_filename = tempnam($phpgw_info['user']['private_dir'],'mail');
		$unique_filename = str_replace($phpgw_info['user']['private_dir'].SEP,'',$unique_filename);
		$phpgw->vfs->write($unique_filename,base64_decode($bsub));
		// we want to display images here, even though they are attachments.
		return  '</td></tr><tr align="center"><td align="center">'
			.'<img src="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/view_attachment.php',
			 'file='.urlencode($unique_filename).'&attfile='.$att_name).'"><p>';
	}

	// function make_clickable ripped off from PHPWizard.net
	// http://www.phpwizard.net/phpMisc/
	// modified to make mailto: addresses compose in AeroMail
	function make_clickable($text,$folder)
	{
		global $phpgw, $phpgw_info;

		$ret = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=\:])",
			"<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", str_replace("<br>","\n",$text));
		if($ret == $text)
		{
			$ret = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
				'a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder))."&to=\\1\">\\1</a>", $ret);
		}
		return(str_replace("\n","<br>",$ret));
	}
	*/

	function uudecode($str)
	{
		$file='';
		for($i=0;$i<count($str);$i++)
		{
			if ($i==count($str)-1 && $str[$i] == "`")
			{
				$phpgw->common->phpgw_exit();
			}
			$pos=1;
			$d=0;
			$len=(int)(((ord(substr($str[$i],0,1)) ^ 0x20) - ' ') & 077);
			while (($d+3<=$len) && ($pos+4<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$c3=(ord(substr($str[$i],$pos+3,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
				$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
				$file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077)      );
				$pos+=4;
				$d+=3;
			}
			if (($d+2<=$len) && ($pos+3<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
				$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
				$pos+=3;
				$d+=2;
			}
			if (($d+1<=$len) && ($pos+2<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
			}
		}
		return $file;
	}
}
?>
