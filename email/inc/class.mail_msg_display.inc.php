<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail Message Processing Functions                             *
  * http://www.phpgroupware.org                                              *
  */
  /**************************************************************************\
  * phpGroupWare API - E-Mail Message Processing Functions                         *
  * This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>      *
  * Handles specific operations in manipulating email messages                         *
  * Copyright (C) 2001 Angelo Tony Puglisi (Angles)                                           *
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

  /* $Id$ */

// include this last, it extends mail_msg_wrappers which extends mail_msg_base
// so (1) include mail_msg_base, (2) incluse mail_msg_wrappers extending mail_msg_base
// then (3) include mail_msg which extends mail_msg_wrappers and, by inheritance, mail_msg_base
class mail_msg extends mail_msg_wrappers
{

	function all_folders_listbox($mailbox,$pre_select="",$skip="",$indicate_new=False)
	{
		global $phpgw, $phpgw_info;

		if (!$mailbox)
		{
			$mailbox = $this->mailsvr_stream;
		}

		// DEBUG: force unseen display
		//$indicate_new = True;

		// init some important variables
		$outstr = '';
		//$unseen_prefix = ' &lt;';
		//$unseen_suffix = ' new&gt;';	
		//$unseen_prefix = ' &#091;';
		//$unseen_suffix = ' new&#093;';
		//$unseen_prefix = ' &#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = ' &#045; ';
		//$unseen_suffix = ' new';
		//$unseen_prefix = ' &#045;';
		//$unseen_suffix = '&#045;';	
		//$unseen_prefix = '&nbsp;&nbsp;&#040;';
		//$unseen_suffix = ' new&#041;';
		//$unseen_prefix = '&nbsp;&nbsp;&#091;';
		//$unseen_suffix = ' new&#093;';
		$unseen_prefix = '&nbsp;&nbsp;&#060;';
		$unseen_suffix = ' new&#062;';

		if ($this->newsmode)
		{
			while($pref = each($phpgw_info["user"]["preferences"]["nntp"]))
			{
				$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
				while($phpgw->db->next_record())
				{
					$outstr = $outstr .'<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
					  . '</option>';
				}
			}
		}
		else
		{
			$folder_list = $this->get_folder_list('');

			for ($i=0; $i<count($folder_list);$i++)
			{
				$folder_long = $folder_list[$i]['folder_long'];
				$folder_short = $folder_list[$i]['folder_short'];
				if ($folder_short == $this->get_folder_short($pre_select))
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				if ($folder_short != $this->get_folder_short($skip))
				{
					$outstr = $outstr .'<option value="' .$this->prep_folder_out($folder_long) .'"'.$sel.'>' .$folder_short;
					// do we show the number of new (unseen) messages for this folder
					if (($indicate_new)
					&& ($this->care_about_unseen($folder_short)))
					{
						$mailbox_status = $this->dcom->status($mailbox,$this->get_mailsvr_callstr().$folder_long,SA_ALL);
						if ($mailbox_status->unseen > 0)
						{
							$outstr = $outstr . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
						}
					}
					$outstr = $outstr . "</option>\r\n";
				}
			}
		}
		return $outstr;
	}


	// ---- Messages Sort Order Start and Msgnum  -----
	function fill_sort_order_start_msgnum()
	{
		global $phpgw, $phpgw_info;

		//$debug_sort = True;
		$debug_sort = False;
	
		// AND ensure $this->sort  $this->order  and  $this->start have usable values
		/*
		Sorting defs:
		SORTDATE:  0	//This is the Date that the senders email client stanp the message with
		SORTARRIVAL: 1	 //This is the date your email server's MTA stamps the message with
				// using SORTDATE cause some messages to be displayed in the wrong cronologicall order
		SORTFROM:  2
		SORTSUBJECT: 3
		SORTSIZE:  6

		// imap_sort(STREAM,  CRITERIA,  REVERSE,  OPTIONS)
		// Stream: is $this->mailsvr_stream
		// Criteria = $sort : is HOW to sort, we prefer SORTARRIVAL, or "1" as default (see note above)
		// Reverse = "order" : 0 = imap default = lowest to highest  ;;  1 = Reverse sorting  =  highest to lowest
		// Options: we do not use this (yet)
		*/

		// == SORT ==
		// if not set in the args, then assign some defaults
		// then store the determination in a class variable $this->sort
		if ((isset($this->args['sort']))
		&& ($this->args['sort'] != '')
		 && (($this->args['sort'] >= 0) && ($this->args['sort'] <= 6)) )
		{
			// this is a valid "sort" variable passed as an argument (in a URL, form, or cookie, or external request)
			$this->sort = $this->args['sort'];
		}
		elseif ((isset($this->args['sort']))
		&& ($this->args['sort'] != '')
		  && ($this->args['sort'] == "ASC") && ($this->newsmode))
		{
			// I think this is needed for newsmode because it reads message list that has been
			// stored locally in a database, in this case it is NOT an arg ment for the NNTP server
			$this->sort = "ASC";
		}
		else
		{
			// SORTARRIVAL as noted above, the preferred default for email
			$this->sort = 1;
		}

		// == ORDER ==
		// (reverse sorting or not)  if specified in the url, then use it, else use defaults
		if ((isset($this->args['order']))
		&& ($this->args['order'] != '')
		  && (($this->args['order'] >= 0) && ($this->args['order'] <= 1)) )
		{
			// this is a valid $this->args['order'] variable passed as an arg
			$this->order = $this->args['order'];
		}
		elseif ((isset($phpgw_info["user"]["preferences"]["email"]["default_sorting"]))
		  && ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old"))
		{
			// user has a preference set to see new mail first
			// this is considered "reverse" order because it is "highest to lowest"
			// with "highest" being the more recent date values
			$this->order = 1;
		}
		else
		{
			// if no pref is set or the pref is old->new, then order should = 0
			// this is considered "NOT reverse" a.k.a. "normal" because it is "lowest to highest"
			// with "lowest" being the older date values
			$this->order = 0;
		}

		// == START ==
		// when requesting a subset of messages, start will get you there
		if ((isset($this->args['start']))
		&& ($this->args['start'] != ''))
		{
			// this is a valid $this->args['start'] variable passed as an arg
			// you are probably requesting a subset of the available messages
			$this->start = $this->args['start'];
		}
		else
		{
			// start at the beginning (relative to your "sort" and "order" of course)
			$this->start = 0;
		}

		// == MSGNUM ==
		// the current message number for the message we are concerned with here
		if ((isset($this->args['msgnum']))
		&& ($this->args['msgnum'] != ''))
		{
			$this->msgnum = $this->args['msgnum'];
		}
		// else it stays at default of empty string ('')

		if ($debug_sort)
		{
			echo 'sort: '.$this->sort.'<br>';
			echo 'order: '.$this->order.'<br>';
			echo 'start: '.$this->start.'<br>';
			echo 'msgnum: '.$this->msgnum.'<br>';
		}
	}

	function format_byte_size($feed_size)
	{
		if ($feed_size < 999999)
		{
			$nice_size = round(10*($feed_size/1024))/10;
			// kbytes is small enough that the 1/10 digit is irrelevent
			$nice_size = round($nice_size).' k';
		}
		else
		{
			//  round to W.XYZ megs by rounding WX.YZ
			$nice_size = round($feed_size/(1024*100));
			// then bring it back one digit and add the MB string
			$nice_size = ($nice_size/10) .' MB';
		}
		return $nice_size;
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
			$subject = $this->decode_header_string($msg->Subject);
		}
		// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
		// $personal = $this->qprint_rfc_header($personal);
		$personal = $this->decode_header_string($personal);
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
			$personal = $this->decode_header_string($personal);
			//$personal = $this->qprint_rfc_header($personal);
			$personal = $personal ." ($from->mailbox@$from->host)";
		}
		return $personal;
	}

	/*!
	@function has_real_attachment
	@abstract s quick test to see if a message has an attachment, (NOT 100% accurate, but fast and mostly accurate)
	@param $struct : PHP structure obtained from the "fetchstructure" command
	@result boolean
	@discussion for use when displaying a list of messages, a quick way to determine if visual information (paperclip) is necessary
	*/
	function has_real_attachment($struct)
	{
		$haystack = serialize($struct);

		if (stristr($haystack, 's:9:"attribute";s:4:"name"'))
		{
			// param attribute "name"
			// s:9:"attribute";s:4:"name"
			return True;
		}
		elseif (stristr($haystack, 's:8:"encoding";i:3'))
		{
			// encoding is base 64
			// s:8:"encoding";i:3
			return True;
		}
		elseif (stristr($haystack, 's:11:"disposition";s:10:"attachment"'))
		{
			// header disposition calls itself "attachment"
			// s:11:"disposition";s:10:"attachment"
			return True;
		}
		elseif (stristr($haystack, 's:9:"attribute";s:8:"filename"'))
		{
			// another mime filename indicator
			// s:9:"attribute";s:8:"filename"
			return True;
		}
		else
		{
			return False;
		}
	}





} // end class mail_msg
?>
