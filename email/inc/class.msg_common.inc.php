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

	function htmlspecialchars_encode($str)
	{
		/*// replace  '  and  "  with htmlspecialchars */
		$str = ereg_replace('&', '&amp;', $str);
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
		$str = ereg_replace('&quot;', '"', $str);
		$str = ereg_replace('&#039;', '\'', $str);
		$str = ereg_replace('&amp;', '&', $str);
		return $str;
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
			$personal = $from->personal." ($from->mailbox@$from->host)";
		}
		return $personal;
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

    function qprint($string)
    {
	$string = str_replace("_", " ", $string);
	$string = str_replace("=\r\n","",$string);
	$string = quoted_printable_decode($string);
	return $string;
    } 
  } // end of class msg_common
?>
