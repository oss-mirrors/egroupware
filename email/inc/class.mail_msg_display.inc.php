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

class mail_msg extends mail_msg_base
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
					$mailbox_status = $phpgw->dcom->status($mailbox,$this->get_mailsvr_callstr().$folder_long,SA_ALL);
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


  // ---- Messages Sort Order  -----
  function fill_sort_order_start()
  {
	global $phpgw, $phpgw_info;

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
	 && (($this->args['sort'] >= 0) && ($this->args['sort'] <= 6)) )
	{
		// this is a valid "sort" variable passed as an argument (in a URL, form, or cookie, or external request)
		$this->sort = $this->args['sort'];
	}
	elseif ((isset($this->args['sort']))
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


  }

  function format_byte_size($feed_size)
  {
	if ($feed_size < 999999)
	{
		$nice_size = round(10*($feed_size/1024))/10 .' k';
	} else {
		//  round to W.XYZ megs by rounding WX.YZ
		$nice_size = round($feed_size/(1024*100));
		// then bring it back one digit and add the MB string
		$nice_size = ($nice_size/10) .' MB';
	}
	return $nice_size;
  }


  function get_message_list()
  {
	global $phpgw;

	$msg_array = array();
	$msg_array = $phpgw->dcom->sort($this->mailsvr_stream, $this->sort, $this->order);
	return $msg_array;
  }


  function new_message_check()
  {
	global $phpgw, $phpgw_info;

	// initialize return structure
	$return_data = Array();
	$return_data['is_imap'] = False;
	$return_data['folder_checked'] = $this->folder;
	$return_data['alert_string'] = '';
	$return_data['number_new'] = 0;
	$return_data['number_all'] = 0;

	$server_str = $this->get_mailsvr_callstr();
	$mailbox_status = $phpgw->dcom->status($this->mailsvr_stream,$server_str.$this->folder,SA_ALL);

	if (($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imap')
	|| ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imaps'))
	{
		$return_data['is_imap'] = True;
		$return_data['number_new'] = $mailbox_status->unseen;
		$return_data['number_all'] = $mailbox_status->messages;
		if ($mailbox_status->unseen == 1) 
		{
			$return_data['alert_string'] .= lang('You have 1 new message!');
		}
		if ($mailbox_status->unseen > 1) 
		{
			$return_data['alert_string'] .= lang('You have x new messages!',$mailbox_status->unseen);
		}
		if ($mailbox_status->unseen == 0) 
		{
			$return_data['alert_string'] .= lang('You have no new messages');
		}
	}
	else
	{
		$return_data['is_imap'] = False;
		// pop3 does not know what is "new" or not
		$return_data['number_new'] = $mailbox_status->messages;
		$return_data['number_all'] = $mailbox_status->messages;
		if ($mailbox_status->messages > 0) 
		{
			$return_data['alert_string'] .= lang('You have messages!');
		}
		elseif ($mailbox_status->messages == 0) 
		{
			$return_data['alert_string'] .= lang('You have no new messages');
		}
	}
	return $return_data;
  }


} // end class mail_msg
?>
