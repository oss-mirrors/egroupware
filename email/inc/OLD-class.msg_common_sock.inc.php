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

  // without imap compiled in some constants are missing
  define ("TYPETEXT",0);
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


  /**************************************************************************\
  * SubClasses needed by msg funcs.                                          *
  \**************************************************************************/

  class msg_struct
  {
	var $type = 0;
	var $encoding = 5;
	var $ifsubtype = false,
	   $subtype = "plain";
	var $ifdescription = false,
	   $description;
	var $ifid = false,
	   $id;
	var $lines = "0";
	var $bytes = "0";
	var $ifdisposition = false,
	   $disposition;
	var $ifdparameters = false,
	   $dparameters;
	var $ifparameters = false,
	   $parameters;
	var $parts;
  }

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

  class msg_headinfo
  {
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

  class msg_aka
  {
	var $personal;
	var $adl;
	var $mailbox;
	var $host;
  }

  class msg_mb_info
  {
	var $Date = "",
	$Driver ="",
	$Mailbox = "",
	$Nmsgs = "",
	$Recent = "",
	$Unread = "",
	$Size;
  }

  class msg_common_sock extends msg_common
  { 

	/**************************************************************************\
	* common functions for socket based mail classes                                  *
	\**************************************************************************/
  
    function fetchheader($stream,$msg_num)
    {
	$header = $this->get_header($stream,$msg_num);
	if (is_array($header))
	{
		return implode("\r\n",$header);
	}
	else
	{
		return $header;
	}
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

  } // end of class msg_common_sock
?>
