<?php
  /**************************************************************************\
  * phpGroupWare Email - IMAP abstraction                                    *
  * http://www.phpgroupware.org/api                                          *
  * This file written by Itzchak Rehberg <izzy@phpgroupware.org>             *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * Mail function abstraction for IMAP servers                               *
  * Copyright (C) 2000, 2001 Itzchak Rehberg                                 *
  * -------------------------------------------------------------------------*
  * This library is part of phpGroupWare (http://www.phpgroupware.org)       * 
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

  /* $Id$ */

  class msg extends msg_common
  {
    function base64($text) 
    {
	return imap_base64($text);
    }

    function close($stream,$flags="") 
    {
	return imap_close($stream,$flags);
    }

    function createmailbox($stream,$mailbox) 
    {
	return imap_createmailbox($stream,$mailbox);
    }

    function deletemailbox($stream,$mailbox)
    {
	return imap_deletemailbox($stream,$mailbox);
    } 

    function delete($stream,$msg_num,$flags="", $currentfolder="") 
    {
	global $phpgw_info, $phpgw;
    
	if ($currentfolder == "Trash")
	{
		return imap_delete($stream,$msg_num);
	}
	else
	{
		if ((isset($phpgw_info["user"]["preferences"]["email"]["use_trash_folder"]))
		&& ($phpgw_info["user"]["preferences"]["email"]["use_trash_folder"]))
		{
			$filter = $phpgw->msg->construct_folder_str("");

			$server_str = get_mailsvr_callstr();
			$name_space = get_mailsvr_namespace();
			$dot_or_slash = get_mailsvr_delimiter();
			//$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$filter*");

			if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
			{
				$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$name_space" ."$dot_or_slash" ."*");
			}
			else
			{
				$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$name_space" ."*");
			}

			if (count($mailboxes) != 0)
			{
				$havetrashfolder = False;
				while ($folder = each($mailboxes))
				{
					if ($folder[1] == "Trash")
					{
						$havetrashfolder = True;
					}
				}
			}

			if (! $havetrashfolder)
			{
				$phpgw->msg->createmailbox($stream,$server_str .$phpgw->msg->construct_folder_str("Trash"));
			}
			$tofolder =  $this->construct_folder_str("Trash");
			return imap_mail_move($stream,$msg_num,$tofolder);
		}
		else
		{
			return imap_delete($stream,$msg_num);
		}
	}
    }

    function expunge($stream) 
    {
	return imap_expunge($stream);
    } 

    function fetchbody($stream,$msgnr,$partnr,$flags="") 
    {
	return imap_fetchbody($stream,$msgnr,$partnr,$flags);
    }

    function header($stream,$msg_nr,$fromlength="",$tolength="",$defaulthost="")
    {
	return imap_header($stream,$msg_nr,$fromlength,$tolength,$defaulthost);
    } 

    function fetch_raw_mail($stream,$msg_num)
    {
	return imap_fetchheader($stream,$msg_num,FT_PREFETCHTEXT);
    }

    function fetchheader($stream,$msg_num)
    {
	return imap_fetchheader($stream,$msg_num);
    }
    
    function get_header($stream,$msg_num)
    {
	// alias for compatibility with some old code
	return $this->fetchheader($stream,$msg_num);
    }

    function fetchstructure($stream,$msg_num,$flags="") 
    {
	return imap_fetchstructure($stream,$msg_num);
    }

    function get_body($stream,$msg_num,$flags="") 
    {
	return imap_body($stream,$msg_num,$flags);
    }

    function listmailbox($stream,$ref,$pattern)
    {
	return imap_listmailbox($stream,$ref,$pattern);
    }

    function num_msg($stream) // returns number of messages in the mailbox
    { 
	return imap_num_msg($stream);
    }

    function mailboxmsginfo($stream) 
    {
	return imap_mailboxmsginfo($stream);
    }

    function mailcopy($stream,$msg_list,$mailbox,$flags)
    {
	return imap_mailcopy($stream,$msg_list,$mailbox,$flags);
    }

    function mail_move($stream,$msg_list,$mailbox)
    {
	return imap_mail_move($stream,$msg_list,$mailbox);
    }

    function open($mailbox,$username,$password,$flags="")
    {
	return imap_open($mailbox,$username,$password,$flags);
    }

    function qprint($message)
    {
	//      return quoted_printable_decode($message);
	$str = quoted_printable_decode($message);
	return str_replace("=\n","",$str);
    } 

    function reopen($stream,$mailbox,$flags = "")
    {
	return imap_reopen($stream,$mailbox,$flags);
    }

    function sort($stream,$criteria,$reverse="",$options="",$msg_info="")
    {
	return imap_sort($stream,$criteria,$reverse,$options);
    }

    function status($stream,$mailbox,$options)
    {
	return imap_status($stream,$mailbox,$options);
    }

    function append($stream, $folder = "Sent", $header, $body, $flags = "")
    {
	global $phpgw_info, $phpgw;

	$filter = $phpgw->msg->construct_folder_str("");

	$server_str = get_mailsvr_callstr();
	$name_space = get_mailsvr_namespace();
	$dot_or_slash = get_mailsvr_delimiter();
	//$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$filter*");

	if ($phpgw_info['user']['preferences']['email']['imap_server_type'] == 'UWash')
	{
		$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$name_space" ."$dot_or_slash" ."*");
	}
	else
	{
		$mailboxes = $phpgw->msg->listmailbox($stream, $server_str, "$name_space" ."*");
	}
	
	if (count($mailboxes) != 0)
	{
		$havefolder = False;
		while ($eachfolder = each($mailboxes))
		{
			if ($eachfolder[1] == $folder)
			{
				$havefolder = True;
			}
		}
	}
	if (! $havefolder)
	{
		$phpgw->msg->createmailbox($stream,$server_str .$phpgw->msg->construct_folder_str($folder));
	}

	$folder = $this->construct_folder_str($folder);
	return imap_append($stream, $server_str.$folder, $header ."\n". $body, $flags);
    }

    function login( $folder = "INBOX")
    {
	global $phpgw, $phpgw_info;
	
	//$debug_logins = True;
	$debug_logins = False;
	if ($debug_logins) {  echo 'CALL TO LOGIN IN CLASS MSG IMAP'.'<br>'.'userid='.$phpgw_info['user']['preferences']['email']['userid']; }
	
	error_reporting(error_reporting() - 2);
	if ($folder != "INBOX")
	{
		$folder = $this->construct_folder_str($folder);
	}

	// WORKAROUND FOR BUG IN EMAIL CUSTOM PASSWORDS (PHASED OUT 7/2/01)
	// $pass = $this->get_email_passwd();
	// === ISSET CHECK ==
	if ( (isset($phpgw_info['user']['preferences']['email']['userid']))
	&& ($phpgw_info['user']['preferences']['email']['userid'] != '')
	&& (isset($phpgw_info['user']['preferences']['email']['passwd']))
	&& ($phpgw_info['user']['preferences']['email']['passwd'] != '') )
	{
		$user = $phpgw_info['user']['preferences']['email']['userid'];
		$pass = $phpgw_info['user']['preferences']['email']['passwd'];
	}
	else
	{
		// problem - invalid or nonexistant info for userid and/or passwd
		return False;
	}

	$server_str = get_mailsvr_callstr();
	$mbox = $this->open($server_str.$folder, $user, $pass);

	error_reporting(error_reporting() + 2);
	return $mbox;
    }

    function construct_folder_str( $folder )
    { 
	/* This is only used by the login() function */
	// Cyrus style: INBOX.Junque
	// UWash style: ./aeromail/Junque
	global $phpgw_info;

	/*
	// TEST replacement with get_folder_long
	if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UW-Maildir")
	{
		if ( isset($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
		{
			if ( empty($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
			{
				$folder_str = $folder;
			}
			else
			{
				$folder_str = $phpgw_info["user"]["preferences"]["email"]["mail_folder"]. $folder;
			}
		}
		else
		{
			$folder_str = $folder;
		}
	}
	elseif ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus")
	{
		$folder_str = "INBOX.".$folder;
	}
	else
	{
		$folder_str = "mail/".$folder;
	}
	return $folder_str;
	*/
	
	$folder_str = get_folder_long($folder);
	return $folder_str;
    }

    function deconstruct_folder_str( $folder )
    {
	//  This is only used by the login() function
	// Cyrus style: INBOX.Junque
	// UWash style: ./aeromail/Junque
	global $phpgw_info;

	/*
	// TEST replacement with get_folder_short
	if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UW-Maildir")
	{
		if ( isset($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
		{
			if ( empty($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
			{
				$srch_str = $folder;
			}
			else
			{
				$srch_str = $phpgw_info["user"]["preferences"]["email"]["mail_folder"]. $folder;
			}
		}
		else
		{
			$folder_str = $folder;
		}
	}
	elseif ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus")
	{
		$srch_str = "INBOX.";
	}
	else
	{
		$srch_str = "mail/";
	}
	$folder_str = substr($folder, strlen($srch_str), strlen($folder));
	return $folder_str;
	*/
	
	$folder_str = get_folder_short($folder);
	return $folder_str;
    }

    /* rfc_get_flag() is more "rfc safe", as RFC822 allows
	the content of the header to be on several lines.

	Quote from RFC822 3.1.1:
	<quote>
	For convenience, the field-body  portion  of  this  conceptual
        entity  can be split into a multiple-line representation; this
        is called "folding".  The general rule is that wherever  there
        may  be  linear-white-space  (NOT  simply  LWSP-chars), a CRLF
        immediately followed by AT LEAST one LWSP-char may instead  be
        inserted.  </quote>

	Note:	$flag should _NOT_ begin with a space
		$field_no should be given strarting at 1
    */
    function rfc_get_flag ($stream, $msg_num, $flag, $field_no = 1) 
    {
	$fieldCount = 0;
     
	$header = imap_fetchheader ($stream, $msg_num);
	$header = explode("\n", $header);
	$flag = strtolower($flag);

	for ($i=0; $i < count($header); $i++)
	{
		// The next check for the $flag _requires_ the field to
		// start at the first character (unless some person
		// adds a space in the beginning of $flag.
		// I believe this is correct according to the RFC.

		if (strcmp (substr(strtolower($header[$i]), 
			0, strlen($flag) + 1), $flag . ":") == 0)
		{
			$fieldFound = true;
			$fieldCount++;
		}
		else
		{
			$fieldFound = false;
		}
		
		if ($fieldFound && $fieldCount == $field_no)
		{
			// We now need to see if the next lines belong to this  message. 
			$header_begin = $i;
			// make sure we don't go too far:)
			// and if the line begins with a space then
			// we'll increment the counter with one.
			$i++;
			
			while ($i < count($header) 
			&& strcmp(substr($header[$i],0,1), " ") == 0)
			{
				$i++;
			}

			// Remove the "field:" from this string.
			$return_tmp = explode (":", $header[$header_begin]);
			$tmp_flag = $return_tmp[0];
			$return_string = trim ($return_tmp[1]);
			
			if (strcasecmp ($flag, $tmp_flag) != 0)
			{
				return false;
			}
			// Houston, we have a _problem_
			// add the rest of the content

			for ($j=$header_begin+1; $j < $i; $j++)
			{
				$return_string .= $header[$j];
			}
			
			return $return_string;
		}
	}
	// failed to find $flag
	return false;
    }


    function get_flag($stream,$msg_num,$flag)
    {
	// Call my new rfc_get_flag() function.
	// It should replace get_flag() as soon as it's 
	// accepted into cvs phpGW
	return $this->rfc_get_flag ($stream, $msg_num, $flag);

	$header = imap_fetchheader($stream,$msg_num);
	$header = explode("\n",$header);
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
  }
