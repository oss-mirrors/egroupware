<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail Message Processing Functions				*
	* http://www.phpgroupware.org							*
	*/
	/**************************************************************************\
	* phpGroupWare API - E-Mail Message Processing Functions			*
	* This file written by Angelo Tony Puglisi (Angles) <angles@phpgroupware.org> *
	* Handles specific operations in manipulating email messages			*
	* Copyright (C) 2001 Angelo Tony Puglisi (Angles)					*
	* -------------------------------------------------------------------------			*
	* This library is part of the phpGroupWare API					*
	* http://www.phpgroupware.org/api							* 
	* ------------------------------------------------------------------------ 			*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of		*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation,		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	// include this last, it extends mail_msg_wrappers which extends mail_msg_base
	// so (1) include mail_msg_base, (2) incluse mail_msg_wrappers extending mail_msg_base
	// then (3) include mail_msg which extends mail_msg_wrappers and, by inheritance, mail_msg_base
class mail_msg extends mail_msg_wrappers
{
	/*!
	@function all_folders_listbox
	@abstract gets a list of all folders available to the user, and makes an HTML listbox widget with that data
	@param $feed_args[] array or args that you will "feed" into the function, contains the following members:
		['mailsvr_stream'] : integer : the stream where the data communications with the mailserver takes place
		['pre_select_folder'] : string : if you want a particular folder already selected in the listbox, put that foldername
			here. Note you must know the name of the folder as it will aooear in the kistbox for this to work.
		['skip_folder'] : string : if you want a particular folder to NOT appear in the listbox, put that foldername
			here. Note you must know the name of the folder as it will aooear in the kistbox for this to work.
		['show_num_new'] : boolean : True = show number of unseen (new) message data with each folder
			in the listbox. There are some folders the code will not examine, such as "Trash" and "Sent"
		['widget_name'] : string : name of the select widget : important for form post usage. Default "folder"
		['embeded_extra_data'] : string : OPTIONAL, if this is specified, the value='' becomes "fake_uri_data"
			in order to contain the extra data. Mostly used to include the acctnum for the folder.
			In this case, the "widget_name" is appended with "_fake_uri" which tells the script to 
			use "explode_fake_uri()" to get the data, which it then inserts back into the HTTP_POST_VARS
			as if the data had never been embeeded. NOTE: such data should be urlencode'd just as if this
			were a URI, however "prep_folder_out" takes care of this for the folder name automatically.
		['on_change'] : string : the HTML select widget's "onChange" value. Default: "document.switchbox.submit()'"
		'[first_line_txt'] : string : the text that initially is displayed in the select widget, used for information only,
			like a descriptive label, it does not have any important data usage. Default: "lang('switch current folder to')"
	@result string representing an HTML listbox widget 
	@discussion ?
	@access : private
	*/
	function all_folders_listbox($feed_args='')
	{
		if(!$feed_args)
		{
			$feed_args=array();
		}
		//$debug_widget = True;
		$debug_widget = False;
		
		$acctnum = $this->get_acctnum();
		// establish fallback default args
		$local_args = Array(
			'acctnum'		=> $acctnum,
			'mailsvr_stream'	=> $this->get_arg_value('mailsvr_stream', $acctnum),
			'pre_select_folder'	=> '',
			'skip_folder'		=> '',
			'show_num_new'		=> False,
			'widget_name'		=> 'folder_fake_uri',
			'folder_key_name'	=> 'folder',
			'acctnum_key_name'	=> 'acctnum',
			'on_change'		=> 'document.switchbox.submit()',
			'first_line_txt'	=> lang('switch current folder to')
		);		
		// loop thru $local_args[], replacing defaults with any args specified in $feed_args[]
		if ($debug_widget) { echo 'all_folders_listbox $feed_args data dump<pre>'; print_r($feed_args); echo '</pre>'; }
		if (count($feed_args) == 0)
		{
			if ($debug_widget) { echo 'all_folders_listbox $feed_args is EMPTY<br>'.serialize($feed_args).'<br>'; }
		}
		else
		{
			reset($local_args);
			// the feed args may not be an array, the @ will supress warnings
			@reset($feed_args);		
			while(list($key,$value) = each($local_args))
			{
				// DEBUG
				if ($debug_widget) { echo 'a: local_args: key=['.$key.'] value=['.(string)$value.']<br>'; }
				if ($debug_widget) { echo 'b: feed_args: key=['.$key.'] value=['.(string)$feed_args[$key].']<br>'; }
				if ((isset($feed_args[$key]))
				&& ($feed_args[$key] != $value))
				{
					if (($key == 'mailsvr_stream')
					&& ($feed_args[$key] == ''))
					{
						// do nothing, keep the default value, can not over write a good default stream with an empty value
						if ($debug_widget) { echo '* keeping default [mailsvr_stream] value, can not override with a blank string<br>'; }
					}
					else
					{
						// we have a specified arg that should replace the default value
						if ($debug_widget) { echo '*** override default value of ['.$local_args[$key] .'] with feed_args['.$key.'] of ['.(string)$feed_args[$key].']<br>'; }
						$local_args[$key] = $feed_args[$key];
					}
				}
			}
			reset($local_args);
			@reset($feed_args);
		}
		// at this point, local_args[] has anything that was passed in the feed_args[]
		if ($debug_widget) { echo 'FINAL Listbox Local Args:<br>'.serialize($local_args).'<br>'; }
		
		// init some important variables
		$item_tags = '';
		//$unseen_prefix = ' &lt;';  $unseen_suffix = ' new&gt;';	
		//$unseen_prefix = ' &#091;';  $unseen_suffix = ' new&#093;';
		//$unseen_prefix = ' &#040;';  $unseen_suffix = ' new&#041;';
		//$unseen_prefix = ' &#045; ';  $unseen_suffix = ' new';
		//$unseen_prefix = ' &#045;';  $unseen_suffix = '&#045;';	
		//$unseen_prefix = '&nbsp;&nbsp;&#040;';  $unseen_suffix = ' new&#041;';
		//$unseen_prefix = '&nbsp;&nbsp;&#091;';  $unseen_suffix = ' new&#093;';
		$unseen_prefix = '&nbsp;&nbsp;&#060;';
		$unseen_suffix = ' new&#062;';

		if ($this->get_arg_value('newsmode'))
		{
			while($pref = each($GLOBALS['phpgw_info']['user']['preferences']['nntp']))
			{
				$GLOBALS['phpgw']->db->query('SELECT name FROM newsgroups WHERE con='.$pref[0]);
				while($GLOBALS['phpgw']->db->next_record())
				{
					$item_tags = $item_tags .'<option value="' . urlencode($GLOBALS['phpgw']->db->f('name')) . '">' . $GLOBALS['phpgw']->db->f('name')
					  . '</option>';
				}
			}
		}
		else
		{
			// get the actual list of folders we are going to put into the combobox
			$folder_list = $this->get_folder_list();
			
			// iterate thru the folder list, building the HTML tags using that data
			for ($i=0; $i<count($folder_list);$i++)
			{
				$folder_long = $folder_list[$i]['folder_long'];
				$folder_short = $folder_list[$i]['folder_short'];
				$folder_acctnum = $folder_list[$i]['acctnum'];
				// this logic determines if the combobox should be initialized with certain folder already selected
				if ($folder_short == $this->get_folder_short($local_args['pre_select_folder']))
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				// this logic determines we should not include a certain folder in the combobox list
				if ($folder_short != $this->get_folder_short($local_args['skip_folder']))
				{
					// we need to make value="X" imitate URI type data, so we can embed the acctnum data 
					// for the folder in there with folder name, whereas normally option value="X" can only 
					// hold no nore than one data item as limited by BOTH html and php 's treatment of a combobox					
					
					$option_value =  '&'.$local_args['folder_key_name'].'='.$this->prep_folder_out($folder_long)
							.'&'.$local_args['acctnum_key_name'].'='.$folder_acctnum;
					
					$item_tags = $item_tags .'<option value="'.$option_value.'"'.$sel.'>' .$folder_short;
					
					// "show_num_new" is currently BROKEN
					// do we show the number of new (unseen) messages for this folder
					//if (($local_args['show_num_new'])
					//&& ($this->care_about_unseen($folder_short)))
					//{
					//	$mailbox_status = $this->a[$this->acctnum]['dcom']->status($mailsvr_stream,$this->get_arg_value('mailsvr_callstr').$folder_long,SA_ALL);
					//	if ($mailbox_status->unseen > 0)
					//	{
					//		$item_tags = $item_tags . $unseen_prefix . $mailbox_status->unseen . $unseen_suffix;
					//	}
					//}
					$item_tags = $item_tags . "</option>\r\n";
				}
			}
		}
		// now $item_tags contains the internal folder list
		
		// ----  add the HTML tags that surround this internal list data  ----
		if ((isset($local_args['on_change']))
		&& ($local_args['on_change'] != ''))
		{
			$on_change_tag = 'onChange="'.$local_args['on_change'].'"';
		}
		else
		{
			$on_change_tag = '';
		}
		
		// the widget_name with "_fake_uri" tells the script what to do with this data
		$listbox_widget =
			 '<select name="'.$local_args['widget_name'].'" '.$on_change_tag.'>'
				.'<option value="">'.$local_args['first_line_txt'].' '
				. $item_tags
			.'</select>';
		// return a pre-built HTML listbox (selectbox) widget
		return $listbox_widget;
	}

	/*!
	@function folders_mega_listbox
	@abstract like "all_folders_listbox" except it really shows ALL folder from EVERY account
	@param $feed_args[] array or args that you will "feed" into the function ??
	@result string representing an HTML listbox widget 
	@discussion ?
	@access : private
	*/
	function folders_mega_listbox($feed_args='')
	{
		if(!$feed_args)
		{
			$feed_args=array();
		}
		//$debug_widget = True;
		$debug_widget = False;
		
		$acctnum = $this->get_acctnum();
		// establish fallback default args
		$local_args = Array(
			'acctnum'		=> $acctnum,
			'mailsvr_stream'	=> $this->get_arg_value('mailsvr_stream', $acctnum),
			'pre_select_folder'	=> '',
			'pre_select_folder_acctnum'	=> $acctnum,
			'skip_folder'		=> '',
			'show_num_new'		=> False,
			'widget_name'		=> 'folder_fake_uri',
			'folder_key_name'	=> 'folder',
			'acctnum_key_name'	=> 'acctnum',
			'on_change'		=> 'document.switchbox.submit()',
			'first_line_txt'	=> lang('switch current folder to')
		);		
		// loop thru $local_args[], replacing defaults with any args specified in $feed_args[]
		if ($debug_widget) { echo 'folders_mega_listbox $feed_args data dump<pre>'; print_r($feed_args); echo '</pre>'; }
		if (count($feed_args) == 0)
		{
			if ($debug_widget) { echo 'folders_mega_listbox $feed_args is EMPTY<br>'.serialize($feed_args).'<br>'; }
		}
		else
		{
			reset($local_args);
			// the feed args may not be an array, the @ will supress warnings
			@reset($feed_args);		
			while(list($key,$value) = each($local_args))
			{
				// DEBUG
				if ($debug_widget) { echo 'a: local_args: key=['.$key.'] value=['.(string)$value.']<br>'; }
				if ($debug_widget) { echo 'b: feed_args: key=['.$key.'] value=['.(string)$feed_args[$key].']<br>'; }
				if ((isset($feed_args[$key]))
				&& ($feed_args[$key] != $value))
				{
					if (($key == 'mailsvr_stream')
					&& ($feed_args[$key] == ''))
					{
						// do nothing, keep the default value, can not over write a good default stream with an empty value
						if ($debug_widget) { echo '* keeping default [mailsvr_stream] value, can not override with a blank string<br>'; }
					}
					else
					{
						// we have a specified arg that should replace the default value
						if ($debug_widget) { echo '*** override default value of ['.$local_args[$key] .'] with feed_args['.$key.'] of ['.(string)$feed_args[$key].']<br>'; }
						$local_args[$key] = $feed_args[$key];
					}
				}
			}
			reset($local_args);
			@reset($feed_args);
		}
		// at this point, local_args[] has anything that was passed in the feed_args[]
		if ($debug_widget) { echo 'FINAL Listbox Local Args:<br>'.serialize($local_args).'<br>'; }
		
		$item_tags = '';
		
		// we need the loop to include the default account AS WELL AS the extra accounts
		for ($x=0; $x < count($this->extra_and_default_acounts); $x++)
		{
			$this_acctnum = $this->extra_and_default_acounts[$x]['acctnum'];
			$this_status = $this->extra_and_default_acounts[$x]['status'];
			if ($this_status != 'enabled')
			{
				// Do Nothing, This account is not in use
			}
			else
			{
				// get the actual list of folders we are going to put into the combobox
				$folder_list = $this->get_folder_list($this_acctnum);
				if ($debug_widget) { echo 'folders_mega_listbox $folder_list for $this_acctnum ['.$this_acctnum.'] DUMP<pre>'; print_r($folder_list); echo '</pre>'; }
				// NNTP = BORKED CODE!!!  (ignore for now) ...
				if ($this->get_arg_value('newsmode', $this_acctnum))
				{
					while($pref = each($GLOBALS['phpgw_info']['user']['preferences']['nntp']))
					{
						$GLOBALS['phpgw']->db->query('SELECT name FROM newsgroups WHERE con='.$pref[0]);
						while($GLOBALS['phpgw']->db->next_record())
						{
							$item_tags .= '<option value="' . urlencode($GLOBALS['phpgw']->db->f('name')) . '">' . $GLOBALS['phpgw']->db->f('name')
							  . '</option>';
						}
					}
					break;
				}
				// ... back to working code
				
				// iterate thru the folder list, building the HTML tags using that data
				for ($i=0; $i<count($folder_list);$i++)
				{
					$folder_long = $folder_list[$i]['folder_long'];
					$folder_short = $folder_list[$i]['folder_short'];
					// yes we need $folder_acctnum to help make the "folder ball", yes I know it *should* be the same as $this_acctnum
					$folder_acctnum = $folder_list[$i]['acctnum'];
					// this logic determines if the combobox should be initialized with certain folder already selected
					// we use "folder short" as the comparator because that way at least we know we are comparing syntatic-ally similar items
					if (($folder_short == $this->get_folder_short($local_args['pre_select_folder'], $local_args['pre_select_folder_acctnum']))
					&& ($folder_acctnum == $local_args['pre_select_folder_acctnum']))
					{
						$sel = ' selected';
					}
					else
					{
						$sel = '';
					}
					// this logic determines we should not include a certain folder in the combobox list
					if ($folder_short != $this->get_folder_short($local_args['skip_folder']))
					{
						// we need to make value="X" imitate URI type data, so we can embed the acctnum data 
						// for the folder in there with folder name, whereas normally option value="X" can only 
						// hold no nore than one data item as limited by BOTH html and php 's treatment of a combobox					
						
						$option_value =  '&'.$local_args['folder_key_name'].'='.$this->prep_folder_out($folder_long)
								.'&'.$local_args['acctnum_key_name'].'='.$folder_acctnum;
								
						$text_blurb = '['.$folder_acctnum.'] '.$folder_short;
						
						$item_tags .= '<option value="'.$option_value.'"'.$sel.'>'.$text_blurb.'</option>'."\r\n";
					}
				}
			}
		}
		// now $item_tags contains the internal (i.e. "option" items) folder list for this "select" combobox widget
		
		// ----  add the HTML tags that surround this internal list data  ----
		if ((isset($local_args['on_change']))
		&& ($local_args['on_change'] != ''))
		{
			$on_change_tag = 'onChange="'.$local_args['on_change'].'"';
		}
		else
		{
			$on_change_tag = '';
		}
		
		// the widget_name with "_fake_uri" tells the script what to do with this data
		$listbox_widget =
			 '<select name="'.$local_args['widget_name'].'" '.$on_change_tag.'>'
				.'<option value="">'.$local_args['first_line_txt'].' '
				. $item_tags
			.'</select>';
		// return a pre-built HTML listbox (selectbox) widget
		return $listbox_widget;
	}
	
	
	// ---- Messages Sort Order Start and Msgnum  -----
	function fill_sort_order_start_msgnum()
	{
		//$debug_sort = True;
		$debug_sort = False;
	
		// AND ensure $this->get_arg_value('sort')  $this->get_arg_value('order')  and  $this->get_arg_value('start') have usable values
		/*
		Sorting defs:
		SORTDATE:  0	//This is the Date that the senders email client stanp the message with
		SORTARRIVAL: 1	 //This is the date your email server's MTA stamps the message with
				// using SORTDATE cause some messages to be displayed in the wrong cronologicall order
		SORTFROM:  2
		SORTSUBJECT: 3
		SORTSIZE:  6

		// imap_sort(STREAM,  CRITERIA,  REVERSE,  OPTIONS)
		// Stream: is $this->get_arg_value('mailsvr_stream')
		// Criteria = $sort : is HOW to sort, we prefer SORTARRIVAL, or "1" as default (see note above)
		// Reverse = "order" : 0 = imap default = lowest to highest  ;;  1 = Reverse sorting  =  highest to lowest
		// Options: we do not use this (yet)
		*/

		// == SORT ==
		// if not set in the args, then assign some defaults
		// then store the determination in a class variable $this->get_arg_value('sort')
		if (($this->get_isset_arg('sort'))
		&& ($this->get_arg_value('sort') != '')
		 && (($this->get_arg_value('sort') >= 0) && ($this->get_arg_value('sort') <= 6)) )
		{
			// this is a valid "sort" variable passed as an argument (in a URL, form, or cookie, or external request)
		}
		elseif (($this->get_isset_arg('sort'))
		&& ($this->get_arg_value('sort') != '')
		  && ($this->get_arg_value('sort') == 'ASC') && ($this->get_isset_arg('newsmode')))
		{
			// I think this is needed for newsmode because it reads message list that has been
			// stored locally in a database, in this case it is NOT an arg ment for the NNTP server
			//$this->get_arg_value('sort') = 'ASC';
		}
		else
		{
			// SORTARRIVAL as noted above, the preferred default for email
			$this->set_arg_value('sort', 1);
		}

		// == ORDER ==
		// (reverse sorting or not)  if specified in the url, then use it, else use defaults
		if (($this->get_isset_arg('order'))
		&& ((string)$this->get_arg_value('order') != '')
		  && (($this->get_arg_value('order') >= 0) && ($this->get_arg_value('order') <= 1)) )
		{
			// this is a valid 'order' variable passed as an arg
		}
		elseif (($this->get_isset_pref('default_sorting'))
		  && ($this->get_pref_value('default_sorting') == "new_old"))
		{
			// user has a preference set to see new mail first
			// this is considered "reverse" order because it is "highest to lowest"
			// with "highest" being the more recent date values
			$this->set_arg_value('order', 1);
		}
		else
		{
			// if no pref is set or the pref is old->new, then order should = 0
			// this is considered "NOT reverse" a.k.a. "normal" because it is "lowest to highest"
			// with "lowest" being the older date values
			$this->set_arg_value('order', 0);
		}

		// == START ==
		// when requesting a subset of messages, start will get you there
		if (($this->get_isset_arg('start'))
		&& ($this->get_arg_value('start') != ''))
		{
			// this is a valid 'start' variable passed as an arg
			// you are probably requesting a subset of the available messages
		}
		else
		{
			// start at the beginning (relative to your "sort" and "order" of course)
			$this->set_arg_value('start', 0);
		}

		// == MSGNUM ==
		// the current message number for the message we are concerned with here
		if (($this->get_isset_arg('msgnum'))
		&& ($this->get_arg_value('["msgball"]["msgnum"]') != ''))
		{
			// we got a good value fed into the script externally
		}
		// else it stays at default of empty string ('')

		if ($debug_sort)
		{
			echo 'sort: ['.$this->get_arg_value('sort').']<br>';
			echo 'order: ['.$this->get_arg_value('order').']<br>';
			echo 'start: ['.$this->get_arg_value('start').']<br>';
			echo 'msgnum: ['.$this->get_arg_value('["msgball"]["msgnum"]').']<br>';
		}
	}
	
	// ---- Go To Previous / Next Message Logic Handling  -----
	// NOTE: msgnum int 0 is NOT to be confused with "empty" nor "boolean False"
	function prev_next_navigation($old_method_totalmessages=0)
	{
		//$debug_nav = True;
		$debug_nav = False;
		
		$nav_data = array();
		$nav_data['msgball_list'] = $this->get_msgball_list();
		//$nav_data['msgnum_idx'] = $this->array_search_ex($this->get_arg_value('["msgball"]["msgnum"]'), $nav_data['msgball_list']);
		
		// what is the array index number where the message in question is located in the $nav_data['msgball_list'] array
		$nav_data['msgnum_idx'] = False;
		$needle = $this->get_arg_value('["msgball"]["msgnum"]');
		for($i=0;$i<$nav_data['msgball_list'];$i++)
		{
			if ((string)$nav_data['msgball_list'][$i]['msgnum'] == (string)$needle)
			{
				$nav_data['msgnum_idx'] = $i;
				break;
			}
		}		
		
		// NOTE: msgnum_idx int 0 is NOT to be confused with "empty" nor "boolean False"
		if ((isset($nav_data['msgnum_idx']))
		&& ((string)$nav_data['msgnum_idx'] != ''))
		{
			$nav_data['active_msgnum_idx'] = $nav_data['msgnum_idx'];
			$nav_data['lowest_left'] = 0;
			$nav_data['highest_right'] = (count($nav_data['msgball_list']) - 1);
			// get msgball data for prev message in the msgball_list array
			$prev_msg_idx = (int)($nav_data['msgnum_idx'] - 1);
			if (isset($nav_data['msgball_list'][$prev_msg_idx]))
			{
				$nav_data['prev_msg']['msgball']['uri'] = $nav_data['msgball_list'][$prev_msg_idx]['uri'];
			}
			else
			{
				$nav_data['prev_msg'] = $this->not_set;
			}
			// get msgball data for next message in the msgball_list array
			$next_msg_idx = (int)($nav_data['msgnum_idx'] + 1);
			if (isset($nav_data['msgball_list'][$next_msg_idx]))
			{
				$nav_data['next_msg']['msgball']['uri'] = $nav_data['msgball_list'][$next_msg_idx]['uri'];
			}
			else
			{
				$nav_data['next_msg'] = $this->not_set;
			}
			$nav_data['method'] = 'new';
		}
		else
		{
			// fall back to old broken way
			$nav_data['active_msgnum_idx'] = $this->get_arg_value('["msgball"]["msgnum"]');
			$nav_data['lowest_left'] = 1;
			$nav_data['highest_right'] = $old_method_totalmessages;
			$nav_data['next_msg'] = $nav_data['active_msgnum_idx'] + 1;
			$nav_data['prev_msg'] = $nav_data['active_msgnum_idx'] - 1;
			$nav_data['method'] = 'old_broken';
		}
		
		if ($debug_nav) { echo 'messages.php step1 $nav_data[] dump <pre>'; print_r($nav_data); echo '</pre>'; }
		
		// if it's not possible to have a prev message, then make "prev_msg" False
		if ($nav_data['active_msgnum_idx'] <= $nav_data['lowest_left'])
		{
			// we are at the last message in this direction, there is no prev message
			$nav_data['prev_msg'] = $this->not_set;
		}
		// is it possible to have a next message, and if so, what is it's msgnum
		if ($nav_data['active_msgnum_idx'] >= $nav_data['highest_right'])
		{
			// we are at the final message in this direction, there is no next message
			$nav_data['next_msg'] = $this->not_set;
		}
		if ($debug_nav) { echo 'messages.php step2 $nav_data[] dump <pre>'; print_r($nav_data); echo '</pre>'; }
		return $nav_data;
	}

	function all_ex_accounts_listbox($feed_args)
	{
		// $this->ex_accounts_count
		// $this->extra_accounts
		
		if(!$feed_args)
		{
			$feed_args=array();
		}
		//$debug_widget = True;
		$debug_widget = False;
		
		// establish fallback default args
		$acctnum = $this->get_acctnum();
		$local_args = Array(
			'pre_select_acctnum'	=> $acctnum,
			'widget_name'		=> 'fldball_fake_uri',
			'folder_key_name'	=> 'folder',
			'acctnum_key_name'	=> 'acctnum',
			'on_change'			=> 'document.acctbox.submit()',
			'is_multiple'		=> False,
			'multiple_rows'		=> '4',
			'show_status_is'	=> 'enabled,disabled',
			'pre_select_multi'	=> (string)$acctnum
		);		
		// loop thru $local_args[], replacing defaults with any args specified in $feed_args[]
		if ($debug_widget) { echo 'all_ex_accounts_listbox $feed_args data dump<pre>'; print_r($feed_args); echo '</pre>'; }
		if (count($feed_args) == 0)
		{
			if ($debug_widget) { echo 'all_ex_accounts_listbox $feed_args is EMPTY<br>'.serialize($feed_args).'<br>'; }
		}
		else
		{
			reset($local_args);
			// the feed args may not be an array, the @ will supress warnings
			@reset($feed_args);		
			while(list($key,$value) = each($local_args))
			{
				// DEBUG
				if ($debug_widget) { echo '* a: local_args: key=['.$key.'] value=['.(string)$value.']<br>'; }
				if ($debug_widget) { echo '* b: feed_args: key=['.$key.'] value=['.(string)$feed_args[$key].']<br>'; }
				if ((isset($feed_args[$key]))
				//&& ($feed_args[$key] != $value))
				&& ((string)$feed_args[$key] != (string)$value))
				{
					// we have a specified arg that should replace the default value
					if ($debug_widget) { echo '*** override default value of ['.$local_args[$key] .'] with feed_args['.$key.'] of ['.(string)$feed_args[$key].']<br>'; }
					$local_args[$key] = $feed_args[$key];
				}
			}
			reset($local_args);
			@reset($feed_args);
		}
		// at this point, local_args[] has anything that was passed in the feed_args[]
		if ($debug_widget) { echo 'all_ex_accounts_listbox: FINAL Listbox Local Args:<br>'.serialize($local_args).'<br>'; }
		
		$item_tags = '';
		
		// iterate thru the ex_accounts list, building the HTML tags using that data
		for ($i=0; $i < count($this->extra_and_default_acounts); $i++)
		{
			$this_acctnum = $this->extra_and_default_acounts[$i]['acctnum'];
			// is this account "enabled", "disabled", or "empty"
			if ((stristr($local_args['show_status_is'], 'disabled'))
			&& ($this->extra_and_default_acounts[$i]['status'] == 'disabled'))
			{
				// the option values below are in the form of embedded fake_uri
				// FUTURE: take user to the extra accounts management page for this particular account
				// now: put the user back to the default account
				$option_value =  '&'.$local_args['folder_key_name'].'=INBOX'
						.'&'.$local_args['acctnum_key_name'].'=0';
				$option_text = lang('account').' ['.$this_acctnum.'] '.lang('disabled');
				
				// note: a disabled account can not be pre-selected
				$item_tags .= '<option value="'.$option_value.'">'.$option_text.'</option>'."\r\n";
			}
			elseif ((stristr($local_args['show_status_is'], 'enabled'))
			&& ($this->extra_and_default_acounts[$i]['status'] == 'enabled'))
			{
				// this logic determines if the combobox should be initialized with certain account already selected
				if ((!$local_args['is_multiple'])
				&& ((string)$local_args['pre_select_acctnum'] == (string)$this_acctnum))
				{
					$sel = ' selected';
				}
				elseif ( ($local_args['is_multiple'])
				&& (	(strstr((string)$local_args['pre_select_multi'], (string)$this_acctnum))
						|| ($local_args['pre_select_multi'] == $this_acctnum)
					)
				)
				{
					$sel = ' selected';
				}
				else
				{
					$sel = '';
				}
				
				// we need to make value="X" imitate URI type data, so we can embed the acctnum data 
				// for the folder in there with folder name, whereas normally option value="X" can only 
				// hold no nore than one data item as limited by BOTH html and php 's treatment of a combobox					
				
				$option_value =  '&'.$local_args['folder_key_name'].'=INBOX'
						.'&'.$local_args['acctnum_key_name'].'='.$this_acctnum;
				//$option_text = lang('account').' ['.$this_acctnum.']'.' '.lang('as').' &quot;'.$this->get_pref_value('fullname', $this_acctnum).'&quot;';
				//$option_text = lang('account').' ['.$this_acctnum.']'.'&nbsp;&nbsp;'.$this->get_pref_value('fullname', $this_acctnum);
				$option_text = lang('account').' '.$this_acctnum.':'.'&nbsp;&nbsp;'.$this->get_pref_value('fullname', $this_acctnum);
				
				$item_tags .= '<option value="'.$option_value.'"'.$sel.'>'.$option_text.'</option>'."\r\n";
			}
			// if not enabled or disabed, it must be empty, in which case we ignore it
		}
		// now $item_tags contains the internal folder list
		
		// ----  add the HTML tags that surround this internal list data  ----
		if ((isset($local_args['on_change']))
		&& ($local_args['on_change'] != ''))
		{
			$on_change_tag = 'onChange="'.$local_args['on_change'].'"';
		}
		else
		{
			$on_change_tag = '';
		}
		
		// if this is a multi-selectable scroll box, make the necessary tags
		if (!$local_args['is_multiple'])
		{
			$if_multiple_tags = '';
		}
		else
		{
			$if_multiple_tags = 'size="'.$local_args['multiple_rows'].'" multiple';
		}
		// the widget_name with "_fake_uri" tells the script what to do with this data
		$listbox_widget =
			 '<select name="'.$local_args['widget_name'].'" '.$on_change_tag.' '.$if_multiple_tags.'>'
				. $item_tags
			.'</select>';
		// return a pre-built HTML listbox (selectbox) widget
		return $listbox_widget;
	}
	
	function format_byte_size($feed_size)
	{
		if ($feed_size < 999999)
		{
			$nice_size = round(10*($feed_size/1024))/10;
			// kbytes is small enough that the 1/10 digit is irrelevent
			$nice_size = round($nice_size);
			// it looks stupid to report "0 k" as a size, make it "1 k"
			if ((int)$nice_size == 0)
			{
				$nice_size = 1;
			}
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
			//$personal = $from->personal.' ('.$from->mailbox.'@'.$from->host.')';
			$personal = trim($from->personal);
			// non-us-ascii chars in headers MUST be specially encoded, so decode them (if any) now
			$personal = $this->decode_header_string($personal);
			//$personal = $this->qprint_rfc_header($personal);
			$personal = $personal .' ('.$from->mailbox.'@'.$from->host.')';
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


	/* * * * * * * * * * *
	  *
	  *   = = = = = = MIME ANALYSIS = = = = = 
	  *
	  * * * * * * *  * * * */
	// ---- Message Structure Analysis   -----
	function get_flat_pgw_struct($struct)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		// get INITIAL part structure / array from the fetchstructure  variable
		if ((!isset($struct->parts[0]) || (!$struct->parts[0])))
		{
			$part[0] = $struct;
		}
		else
		{
			$part = $struct->parts;
		}
	
		//$part = Array();
		//$part[0] = $struct;

		//echo '<br>INITIAL var part serialized:<br>' .serialize($part) .'<br><br>';	

		$d1_num_parts = count($part);	
		$part_nice = Array();

		// get PRIMARY level part information
		$deepest_level=0;
		$array_position = -1;  // it will be advanced to 0 before its used
		// ---- Flatten Message Structure Array   -----
		for ($d1 = 0; $d1 < $d1_num_parts; $d1++)
		{
			$array_position++;
			$d1_mime_num = (string)($d1+1);
			$part_nice[$array_position] = $this->pgw_msg_struct($part[$d1], $not_set, $d1_mime_num, ($d1+1), $d1_num_parts, 1);
			if ($deepest_level < 1) { $deepest_level=1; }
			
			// get SECONDARY/EMBEDDED level part information
			$d1_array_pos = $array_position;
			if ($part_nice[$d1_array_pos]['ex_num_subparts'] != $not_set)
			{
				$d2_num_parts = $part_nice[$d1_array_pos]['ex_num_subparts'];
				for ($d2 = 0; $d2 < $d2_num_parts; $d2++)
				{
					$d2_part = $part_nice[$d1_array_pos]['subpart'][$d2];
					$d2_mime_num = (string)($d1+1) .'.' .(string)($d2+1);
					$array_position++;
					$part_nice[$array_position] = $this->pgw_msg_struct($d2_part, $d1_array_pos, $d2_mime_num, ($d2+1), $d2_num_parts, 2);
					if ($deepest_level < 2) { $deepest_level=2; }
					
					// get THIRD/EMBEDDED level part information
					$d2_array_pos = $array_position;
					if ($d2_part['ex_num_subparts'] != $not_set)
					{
						$d3_num_parts = $part_nice[$d2_array_pos]['ex_num_subparts'];
						for ($d3 = 0; $d3 < $d3_num_parts; $d3++)
						{
							$d3_part = $part_nice[$d2_array_pos]['subpart'][$d3];
							$d3_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1);
							$array_position++;
							$part_nice[$array_position] = $this->pgw_msg_struct($d3_part, $d2_array_pos, $d3_mime_num, ($d3+1), $d3_num_parts, 3);
							if ($deepest_level < 3) { $deepest_level=3; }
							
							// get FOURTH/EMBEDDED level part information
							$d3_array_pos = $array_position;
							if ($d3_part['ex_num_subparts'] != $not_set)
							{
								$d4_num_parts = $part_nice[$d3_array_pos]['ex_num_subparts'];
								for ($d4 = 0; $d4 < $d4_num_parts; $d4++)
								{
									$d4_part = $part_nice[$d3_array_pos]['subpart'][$d4];
									$d4_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1);
									$array_position++;
									$part_nice[$array_position] = $this->pgw_msg_struct($d4_part, $d3_array_pos, $d4_mime_num, ($d4+1), $d4_num_parts, 4);
									if ($deepest_level < 4) { $deepest_level=4; }
									
									// get FIFTH LEVEL EMBEDDED level part information
									$d4_array_pos = $array_position;
									if ($d4_part['ex_num_subparts'] != $not_set)
									{
										$d5_num_parts = $part_nice[$d4_array_pos]['ex_num_subparts'];
										for ($d5 = 0; $d5 < $d5_num_parts; $d5++)
										{
											$d5_part = $part_nice[$d4_array_pos]['subpart'][$d5];
											$d5_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1);
											$array_position++;
											$part_nice[$array_position] = $this->pgw_msg_struct($d5_part, $d4_array_pos, $d5_mime_num, ($d5+1), $d5_num_parts, 5);
											if ($deepest_level < 5) { $deepest_level=5; }
											
											// get SIXTH LEVEL EMBEDDED level part information
											$d5_array_pos = $array_position;
											if ($d5_part['ex_num_subparts'] != $not_set)
											{
												$d6_num_parts = $part_nice[$d5_array_pos]['ex_num_subparts'];
												for ($d6 = 0; $d6 < $d6_num_parts; $d6++)
												{
													$d6_part = $part_nice[$d5_array_pos]['subpart'][$d6];
													$d6_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1);
													$array_position++;
													$part_nice[$array_position] = $this->pgw_msg_struct($d6_part, $d5_array_pos, $d6_mime_num, ($d6+1), $d6_num_parts, 6);
													if ($deepest_level < 6) { $deepest_level=6; }
													
													// get SEVENTH LEVEL EMBEDDED level part information
													$d6_array_pos = $array_position;
													if ($d6_part['ex_num_subparts'] != $not_set)
													{
														$d7_num_parts = $part_nice[$d6_array_pos]['ex_num_subparts'];
														for ($d7 = 0; $d7 < $d7_num_parts; $d7++)
														{
															$d7_part = $part_nice[$d6_array_pos]['subpart'][$d7];
															$d7_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1);
															$array_position++;
															$part_nice[$array_position] = $this->pgw_msg_struct($d7_part, $d6_array_pos, $d7_mime_num, ($d7+1), $d7_num_parts, 7);
															if ($deepest_level < 7) { $deepest_level=7; }
															
															// get EIGTH LEVEL EMBEDDED level part information
															$d7_array_pos = $array_position;
															if ($d7_part['ex_num_subparts'] != $not_set)
															{
																$d8_num_parts = $part_nice[$d7_array_pos]['ex_num_subparts'];
																for ($d8 = 0; $d8 < $d8_num_parts; $d8++)
																{
																	$d8_part = $part_nice[$d7_array_pos]['subpart'][$d8];
																	$d8_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1);
																	$array_position++;
																	$part_nice[$array_position] = $this->pgw_msg_struct($d8_part, $d7_array_pos, $d8_mime_num, ($d8+1), $d8_num_parts, 8);
																	if ($deepest_level < 8) { $deepest_level=8; }
																	
																	// get NINTH LEVEL EMBEDDED level part information
																	$d8_array_pos = $array_position;
																	if ($d8_part['ex_num_subparts'] != $not_set)
																	{
																		$d9_num_parts = $part_nice[$d8_array_pos]['ex_num_subparts'];
																		for ($d9 = 0; $d9 < $d9_num_parts; $d9++)
																		{
																			$d9_part = $part_nice[$d8_array_pos]['subpart'][$d9];
																			$d9_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1);
																			$array_position++;
																			$part_nice[$array_position] = $this->pgw_msg_struct($d9_part, $d8_array_pos, $d9_mime_num, ($d9+1), $d9_num_parts, 9);
																			if ($deepest_level < 9) { $deepest_level=9; }
																			
																			// get 10th LEVEL EMBEDDED level part information
																			$d9_array_pos = $array_position;
																			if ($d9_part['ex_num_subparts'] != $not_set)
																			{
																				$d10_num_parts = $part_nice[$d9_array_pos]['ex_num_subparts'];
																				for ($d10 = 0; $d10 < $d10_num_parts; $d10++)
																				{
																					$d10_part = $part_nice[$d9_array_pos]['subpart'][$d10];
																					$d10_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1);
																					$array_position++;
																					$part_nice[$array_position] = $this->pgw_msg_struct($d10_part, $d9_array_pos, $d10_mime_num, ($d10+1), $d10_num_parts, 10);
																					if ($deepest_level < 10) { $deepest_level=10; }
																					
																					// get 11th LEVEL EMBEDDED level part information
																					$d10_array_pos = $array_position;
																					if ($d10_part['ex_num_subparts'] != $not_set)
																					{
																						$d11_num_parts = $part_nice[$d10_array_pos]['ex_num_subparts'];
																						for ($d11 = 0; $d11 < $d11_num_parts; $d11++)
																						{
																							$d11_part = $part_nice[$d10_array_pos]['subpart'][$d11];
																							$d11_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1);
																							$array_position++;
																							$part_nice[$array_position] = $this->pgw_msg_struct($d11_part, $d10_array_pos, $d11_mime_num, ($d11+1), $d11_num_parts, 11);
																							if ($deepest_level < 11) { $deepest_level=11; }
																							
																							// get 12th LEVEL EMBEDDED level part information
																							$d11_array_pos = $array_position;
																							if ($d11_part['ex_num_subparts'] != $not_set)
																							{
																								$d12_num_parts = $part_nice[$d11_array_pos]['ex_num_subparts'];
																								for ($d12 = 0; $d12 < $d12_num_parts; $d12++)
																								{
																									$d12_part = $part_nice[$d11_array_pos]['subpart'][$d12];
																									$d12_mime_num = (string)($d1+1) .'.' .(string)($d2+1) .'.' .(string)($d3+1) .'.' .(string)($d4+1) .'.' .(string)($d5+1) .'.' .(string)($d6+1) .'.' .(string)($d7+1) .'.' .(string)($d8+1) .'.' .(string)($d9+1) .'.' .(string)($d10+1) .'.' .(string)($d11+1) .'.' .(string)($d12+1);
																									$array_position++;
																									$part_nice[$array_position] = $this->pgw_msg_struct($d12_part, $d11_array_pos, $d12_mime_num, ($d12+1), $d12_num_parts, 12);
																									if ($deepest_level < 12) { $deepest_level=12; }
																								}
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		// CONTINUE WITH THE ANALYSIS

		// ---- Mime Characteristics Analysis  and more Attachments Detection  -----	
		// ANALYSIS LOOP Part 1
		for ($i = 0; $i < count($part_nice); $i++)
		{
			// ------  ATTACHMENT DETECTION  -------
			
			// NOTE: initially I wanted to treat base64 attachments with more "respect", but many other attachments are NOT
			// base64 encoded and are still attachments - if param_value NAME has a value, pretend it's an attachment
			// however, a base64 part IS an attachment even if it has no name, just make one up
			// also, if "disposition" header = "attachment", same thing, it's an attachment, and if no name is in the params, make one up
			// note: we do not use "elseif" during this because an attachment may be detected in *any* of the following code blocks
			// in no particular, nor predictable, order
			
			// Fallback / Default: assume No Attachment here
			//$part_nice['ex_part_name'] = 'unknown.html';
			$part_nice[$i]['ex_part_name'] = 'attachment.txt';
			$part_nice[$i]['ex_attachment'] = False;
			
			// Attachment Detection PART1-A = if a part has a NAME=FOO in the param pairs, then treat as an attachment
			if (($part_nice[$i]['ex_num_param_pairs'] > 0)
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				for ($p = 0; $p < $part_nice[$i]['ex_num_param_pairs']; $p++)
				{
					if (($part_nice[$i]['params'][$p]['attribute'] == 'name') 
					  && ($part_nice[$i]['params'][$p]['value'] != $not_set))
					{
						$part_nice[$i]['ex_part_name'] = $part_nice[$i]['params'][$p]['value'];
						$part_nice[$i]['ex_attachment'] = True;
						break;
					}
				}
			}
			// Attachment Detection PART1-B = if a part has a NAME=FOO in the dparam pairs, then treat as an attachment
			if (($part_nice[$i]['ex_num_dparam_pairs'] > 0)
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				for ($p = 0; $p < $part_nice[$i]['ex_num_dparam_pairs']; $p++)
				{
					if (($part_nice[$i]['dparams'][$p]['attribute'] == 'name') 
					  && ($part_nice[$i]['dparams'][$p]['value'] != $not_set))
					{
						$part_nice[$i]['ex_part_name'] = $part_nice[$i]['dparams'][$p]['value'];
						$part_nice[$i]['ex_attachment'] = True;
						break;
					}
				}
			}
			
			// Attachment Detection PART2 = if a part has encoding=base64 , then treat as an attachment
			//	eventhough the above code did not find a name for the part
			if (($part_nice[$i]['encoding'] == 'base64')
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
				// we MUST have a base64 part with NO NAME - but it still should be treated as an attachment
				// except for text and html PARTS (not specifically attachments) that
				// some MUAs encode anyway, probably as a brute-force way to ensure 7bit content
				// even though "quotedprintable" *should* be used to make non-attachments 7bit, not base64

				// some idiots encode text/plain parts in base64 - that's not an attachment
				if ($part_nice[$i]['subtype'] == 'plain')
				{
					// not an attachment, we SHOULD decode this text and sisplay it inline
					// do nothing, leave ex_attachment as False, as it was coming into this if..then block
				}
				// some idiots encode text/html parts in base64 - not *really* a cut-and-dry attachment
				elseif ($part_nice[$i]['subtype'] == 'html')
				{
					// this is not *really* an attachment, however because it was
					// base64 encoded I'll pretend it IS an attachment
					$part_nice[$i]['ex_attachment'] = True;
					// BUT I will change the default (unknown) attachment name to "*.HTML"
					// so the users browser knows what to do with it
					// otherwise the browser may try to open it as a text file, not as an HTML file
					$part_nice[$i]['ex_part_name'] = 'attachment.html';
				}
				else
				{
					// NOTE: if a part has a name in the params, the it would have been handled
					// before this if..then block. Thus, to get to this point in the code means
					// we MUST have a base64 part with NO NAME - 
					// NOR does it have an OBVIOUS type/subtype (text/plain)
					// we have NO CHOICE but to treat it as an attachment
					$part_nice[$i]['ex_attachment'] = True;
					// Digression: why we can't do any more then this
					// we have no idea of it's name, and *maybe* no idea of it's content type
					// (eg. name.gif = image/gif  which is "OBVIOUS" even if the mail headers don't tell us that)
					// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
					// but we can not do much here because we have NO name
					// even if we have type/subtype data (see above text/plain and text/html)
					// it would be futile to make a fake name "attachment.SOME_EXTENSION"
					// trying to make up an extension ".doc" to match what we *think* it should have
					// is not something we want to have to worry about here
				}
			}
			// Attachment Detection PART3 = if "disposition" header has a value of "attachment" , then treat as an attachment
			// PROVIDED it is not type "message" - in that case the attachment is *inside* the message, not the message itself
			if (($part_nice[$i]['disposition'] == 'attachment')
			&& ($part_nice[$i]['type'] != 'message')
			&& ($part_nice[$i]['ex_attachment'] == False))
			{
				// NOTE: if a part has a name in the params, the above code would have found it, so to get here means
				// we MUST have a attachment with NO NAME - but it still should be treated as an attachment
				$part_nice[$i]['ex_attachment'] = True;
				// BUT we have no idea of it's name, and *maybe* no idea of it's content type (eg. name.gif = image/gif)
				// sometimes the name's extention is the only info we have, i.e. ".doc" implies a WORD file
				//$part_nice['ex_part_name'] = 'no_name.att';
			}
			
			// ------  MIME PART CATAGORIZATION  -------
			
			// POSSIBLE VALUES FOR ['m_description'] ARE:
			//	container
			//	packagelist
			//	presentable/image
			//	attachment
			//	presentable
		
			// RULES:
			// a) if no subpart(s) then we have either "presentable" or "attachment"
			// b) if subpart(s) and a boundary param, then we have a "packagelist" (HeadersOnly)
			// c) else we have a container
			if ((int)$part_nice[$i]['ex_num_subparts'] < 1)
			{
				// a) if no subparts then we have either "presentable" or "attachment"
				if ($part_nice[$i]['ex_attachment'])
				{
					// fallback value pending the following test
					$part_nice[$i]['m_description'] = 'attachment';
					// does the "attachment" have a name with "image"type extension
					for ($p = 0; $p < count($part_nice[$i]['params']); $p++)
					{
						if ( (stristr($part_nice[$i]['params'][$p]['attribute'], 'name'))
						&& ((stristr($part_nice[$i]['params'][$p]['value'], '.JPG'))
						  || (stristr($part_nice[$i]['params'][$p]['value'], '.GIF'))
						  || (stristr($part_nice[$i]['params'][$p]['value'], '.PNG')) ) )
						{
							// we should attempt to inline display images
							$part_nice[$i]['m_description'] = 'presentable/image';
							break;
						}
					}
				}
				else
				{
					// not an attachment, nor an attachment that's an image for inline display
					$part_nice[$i]['m_description'] = 'presentable';
				}
			}
			elseif ($this->has_this_param($part_nice[$i]['params'], 'boundary'))
			{
				// b) if subpart(s) and a boundary param, then we have a "packagelist" (HeadersOnly)
				$part_nice[$i]['m_description'] = 'packagelist';
			}
			else
			{
				// c) else we have a container
				$part_nice[$i]['m_description'] = 'container';
			}
			
			// ------  KEYWORD LIST  -------
			
			// probably will be depreciated
			// at least for now, keywords "plain" and "html" are needed below
			$part_nice[$i]['m_keywords'] = '';
			if ((stristr($part_nice[$i]['subtype'], 'plain'))
			|| (stristr($part_nice[$i]['subtype'], 'html')))
			{
				$part_nice[$i]['m_keywords'] .= $part_nice[$i]['subtype'] .' ';
			}
			// encoding keyword is used below as well
			if ($part_nice[$i]['encoding'] != $not_set)
			{
				$part_nice[$i]['m_keywords'] .= $part_nice[$i]['encoding'] .' ';
			}
			
			
			// ------  EXCEPTIONS TO THE RULES  -------
			
			// = = = = =  Exceptions for Less-Standard Subtypes = = = = =
			//"m_description" set above will work *most all* the time. However newer standards
			// are encouraged to make use of the "subtype" param, not create new "type"s 
			// the following "multipart/SUBTYPES" should be treated as
			// "container" instead of "packagelist"
			
			// (1) Exception: multipart/RELATED: for ex. Outl00k Stationary handling
			// where an HTML part has references to other parts (images) in it
			// treat it's *child* multipart/alternative as "container", not as "packagelist"
			$part_nice[$i]['m_html_related_kids'] = False;
			$parent_idx = $part_nice[$i]['ex_parent_flat_idx'];
			if (($part_nice[$i]['ex_level_debth'] > 1)  // does not apply to level1, b/c level1 has no parent
			&& ($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'alternative')
			&& ($part_nice[$parent_idx]['type'] == 'multipart')
			&& ($part_nice[$parent_idx]['subtype'] == 'related'))
			{
				// NOTE: treat it's *child* multipart/alternative as "container", not as "packagelist"
				$part_nice[$i]['m_description'] = 'container';
				$part_nice[$i]['m_keywords'] .= 'Force Container, id_swap' .' ';
				// SET THIS FLAG: then, in presentation loop, see if a HTML part 
				// has a parent with this flag - if so, replace "id" reference(s) with 
				// http... mime reference(s). Example: MS Stationary mail's image background
				$part_nice[$i]['m_html_related_kids'] = True;
				$part_nice[$i]['m_keywords'] .= 'id_swap' .' ';
			}
			
			// (2) Exception: multipart/APPLEDOUBLE  (ex. mac thru X.400 gateway)
			// treat as "container", not as "packagelist"
			if (($part_nice[$i]['type'] == 'multipart')
			&& ($part_nice[$i]['subtype'] == 'appledouble'))
			{
				$part_nice[$i]['m_description'] = 'container';
				$part_nice[$i]['m_keywords'] .= 'Force Container' .' ';
			}
			
			// ------  MAKE "SMART" MIME PART NUMBER  -------
			
			// ---Use Mime Number Dumb To Make ex_mime_number_smart
			$new_mime_dumb = $part_nice[$i]['ex_mime_number_dumb'];
			$part_nice[$i]['ex_mime_number_smart'] = $this->mime_number_smart($part_nice, $i, $new_mime_dumb);
			
			// -----   Make Smart Mime Number THE PRIMARY MIME NUMBER we will use
			//$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_smart'];

			// TEMPORARY HACK FOR SOCKET POP3 CLASS - feed it DUMB mime part numbers
			
			if ((isset($GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->imap_builtin))
			&& ($GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->imap_builtin == False)
			&& (stristr($this->get_pref_value('mail_server_type'), 'pop3')))
			{
				// Make ***DUMB*** Mime Number THE PRIMARY MIME NUMBER we will use
				$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_dumb'];
			}
			else
			{
				// Make Smart Mime Number THE PRIMARY MIME NUMBER we will use
				$part_nice[$i]['m_part_num_mime'] = $part_nice[$i]['ex_mime_number_smart'];
			}
			
			// ------  MAKE CLICKABLE HREF TO THIS PART  -------
			
			// make an URL and a Clickable Link to directly acces this part
			//$click_info = $this->make_part_clickable($part_nice[$i], $this->get_arg_value('folder'), $this->get_arg_value('["msgball"]["msgnum"]'));
			$click_info = $this->make_part_clickable($part_nice[$i], $this->get_arg_value('msgball'));
			$part_nice[$i]['ex_part_href'] = $click_info['part_href'];
			$part_nice[$i]['ex_part_clickable'] = $click_info['part_clickable'];
		}		
		
		// finally, return the customized flat phpgw msg structure array
		return $part_nice;
	}

	function pgw_msg_struct($part, $parent_flat_idx, $feed_dumb_mime, $feed_i, $feed_loops, $feed_debth)
	{
		if (isset($this->not_set))
		{
			$not_set = $this->not_set;
		}
		else
		{
			$not_set = '-1';
		}
		
		//echo 'BEGIN pgw_msg_struct<br>';
		//echo var_dump($part);
		//echo '<br>';
		
		// TRANSLATE PART STRUCTURE CONSTANTS INTO STRINGS OR TRUE/FALSE
		// see php manual page function.imap-fetchstructure.php
		
		// 1: TYPE
		$part_nice['type'] = $not_set; // Default value if not filled
		if (isset($part->type) && $part->type)
		{
			switch ($part->type)
			{
				case TYPETEXT		: $part_type = 'text'; break;
				case TYPEMULTIPART	: $part_type = 'multipart'; break;
				case TYPEMESSAGE		: $part_type = 'message'; break;
				case TYPEAPPLICATION	: $part_type = 'application'; break;
				case TYPEAUDIO		: $part_type = 'audio'; break;
				case TYPEIMAGE		: $part_type = 'image'; break;
				case TYPEVIDEO		: $part_type = 'video'; break;
				//case TYPEMODEL:		$part_type = "model"; break;
				// TYPEMODEL is not supported as of php v 4
				case TYPEOTHER		: $part_type = 'other'; break;
				default			: $part_type = 'unknown';
			}
			$part_nice['type'] = $part_type;
		}
		// RFC SAYS TYPE "TEXT" IS *DEFAULT* AND MAY BE *ASSUMED* IN THE ABSENCE OF IT BEING SPECIFIED
		if (($part_nice['type'] == 'unknown')
		|| ($part_nice['type'] == $not_set))
		{
			$part_nice['type'] = 'text';
		}
		
		// 2: ENCODING
		$part_nice['encoding'] = $not_set; // Default value if not filled
		if (isset($part->encoding) && $part->encoding)
		{
			switch ($part->encoding)
			{
				case ENC7BIT		: $part_encoding = '7bit'; break;
				case ENC8BIT		: $part_encoding = '8bit'; break;
				case ENCBINARY		: $part_encoding = 'binary';  break;
				case ENCBASE64		: $part_encoding = 'base64'; break;
				//case ENCQUOTEDPRINTABLE : $part_encoding = 'quoted-printable'; break;
				case ENCQUOTEDPRINTABLE 	: $part_encoding = 'qprint'; break;
				case ENCOTHER		: $part_encoding = 'other';  break;
				case ENCUU		: $part_encoding = 'uu';  break;
				default			: $part_encoding = 'other';
			}
			$part_nice['encoding'] = $part_encoding;
		}
		// 3: IFSUBTYPE : true if there is a subtype string (SKIP)
		// 4: MIME subtype if the above is true, already in string form
		$part_nice['subtype'] = $not_set; // Default value if not filled
		if ((isset($part->ifsubtype)) && ($part->ifsubtype)
		&& (isset($part->subtype)) && ($part->subtype) )
		{
			$part_nice['subtype'] = $part->subtype;
			// this header item is not case sensitive
			$part_nice['subtype'] = trim(strtolower($part_nice['subtype']));
		}
		//5: IFDESCRIPTION : true if there is a description string (SKIP)
		// 6: Content Description String, if the above is true
		$part_nice['description'] = $not_set; // Default value if not filled
		if ((isset($part->ifdescription)) && ($part->ifdescription)
		&& (isset($part->description)) && ($part->description) )
		{
			$part_nice['description'] = $part->description;
		}
		// 7:  ifid : True if there is an identification string (SKIP)
		// 8: id : Identification string  , if the above is true
		$part_nice['id'] = $not_set; // Default value if not filled
		if ( (isset($part->ifid)) && ($part->ifid)
		&& (isset($part->id)) && ($part->id) )
		{
			$part_nice['id'] = trim($part->id);
		}
		// 9: lines : Number of lines
		$part_nice['lines'] = $not_set; // Default value if not filled
		if ((isset($part->lines)) && ($part->lines))
		{
			$part_nice['lines'] = $part->lines;
		}
		// 10:  bytes : Number of bytes
		$part_nice['bytes'] = $not_set; // Default value if not filled
		if ((isset($part->bytes)) && ($part->bytes))
		{
			$part_nice['bytes'] = $part->bytes;
		}
		// 11:  ifdisposition : True if there is a disposition string (SKIP)
		// 12:  disposition : Disposition string  ,  if the above is true
		$part_nice['disposition'] = $not_set; // Default value if not filled
		if ( (isset($part->ifdisposition)) && ($part->ifdisposition)
		&& (isset($part->disposition)) && ($part->disposition) )
		{
			$part_nice['disposition'] = $part->disposition;
			// this header item is not case sensitive
			$part_nice['disposition'] = trim(strtolower($part_nice['disposition']));
		}
		//13:  ifdparameters : True if the dparameters array exists SKIPPED -  ifparameters is more useful (I think)
		//14:  dparameters : Disposition parameter array
		// *not* SKIPPED, although parameters is more useful (I think), dparameters may sometimes hold an attachment name
		// ex_num_dparam_pairs defaults to 0 (no dparams)
		$part_nice['ex_num_dparam_pairs'] = 0;
		if ( (isset($part->ifdparameters)) && ($part->ifdparameters)
		&& (isset($part->dparameters)) && ($part->dparameters) )
		{
			// Custom/Extra Information (ex_):  ex_num_dparam_pairs
			$part_nice['ex_num_dparam_pairs'] = count($part->dparameters);
			// capture data from all dparam attribute=value pairs
			for ($pairs = 0; $pairs < $part_nice['ex_num_dparam_pairs']; $pairs++)
			{
				$part_dparams = $part->dparameters[$pairs];
				$part_nice['dparams'][$pairs]['attribute'] = $not_set; // default / fallback
				if ((isset($part_dparams->attribute) && ($part_dparams->attribute)))
				{
					$part_nice['dparams'][$pairs]['attribute'] = $part_dparams->attribute;
					$part_nice['dparams'][$pairs]['attribute'] = trim(strtolower($part_nice['dparams'][$pairs]['attribute']));
				}
				$part_nice['dparams'][$pairs]['value'] = $not_set; // default / fallback
				if ((isset($part_dparams->value) && ($part_dparams->value)))
				{
					$part_nice['dparams'][$pairs]['value'] = $part_dparams->value;
					// stuff like file names should retain their case
					//$part_nice['dparams'][$pairs]['value'] = strtolower($part_nice['dparams'][$pairs]['value']);
				}
			}
		}
		// 15:  ifparameters : True if the parameters array exists (SKIP)
		// 16:  parameters : MIME parameters array  - this *may* have more than a single attribute / value pair  but I'm not sure
		// ex_num_param_pairs defaults to 0 (no params)
		$part_nice['ex_num_param_pairs'] = 0;
		if ( (isset($part->ifparameters)) && ($part->ifparameters)
		&& (isset($part->parameters)) && ($part->parameters) )
		{
			// Custom/Extra Information (ex_):  ex_num_param_pairs
			$part_nice['ex_num_param_pairs'] = count($part->parameters);
			// capture data from all param attribute=value pairs
			for ($pairs = 0; $pairs < $part_nice['ex_num_param_pairs']; $pairs++)
			{
				$part_params = $part->parameters[$pairs];
				$part_nice['params'][$pairs]['attribute'] = $not_set; // default / fallback
				if ((isset($part_params->attribute) && ($part_params->attribute)))
				{
					$part_nice['params'][$pairs]['attribute'] = $part_params->attribute;
					$part_nice['params'][$pairs]['attribute'] = trim(strtolower($part_nice['params'][$pairs]['attribute']));
				}
				$part_nice['params'][$pairs]['value'] = $not_set; // default / fallback
				if ((isset($part_params->value) && ($part_params->value)))
				{
					$part_nice['params'][$pairs]['value'] = $part_params->value;
					// stuff like file names should retain their case
					//$part_nice['params'][$pairs]['value'] = strtolower($part_nice['params'][$pairs]['value']);
				}
			}
		}
		// 17:  parts : Array of objects describing each message part to this part
		// (i.e. embedded MIME part(s) within a wrapper MIME part)
		// key 'ex_' = CUSTOM/EXTRA information
		$part_nice['ex_num_subparts'] = $not_set;
		$part_nice['subpart'] = Array();
		if (isset($part->parts) && $part->parts)
		{
			$num_subparts = count($part->parts);
			$part_nice['ex_num_subparts'] = $num_subparts;
			for ($p = 0; $p < $num_subparts; $p++)
			{
				$part_subpart = $part->parts[$p];
				$part_nice['subpart'][$p] = $part_subpart;
			}
		}
		// ADDITIONAL INFORMATION (often uses array key "ex_" )
		
		// "dumb" mime part number based only on array position, will be made "smart" later
		$part_nice['ex_mime_number_dumb'] = $feed_dumb_mime;
		$part_nice['ex_parent_flat_idx'] = $parent_flat_idx;
		// Iteration Tracking
		$part_nice['ex_level_iteration'] = $feed_i;
		$part_nice['ex_level_max_loops'] = $feed_loops;
		$part_nice['ex_level_debth'] = $feed_debth;
		
		//echo 'BEGIN DUMP<br>';
		//echo var_dump($part_nice);
		//echo '<br>END DUMP<br>';
		
		return $part_nice;
	}


	function mime_number_smart($part_nice, $flat_idx, $new_mime_dumb)
	{
		$not_set = $this->not_set;
		
		// ---- Construct a "Smart" mime number
		
		//$debug = True;
		$debug = False;
		//if (($flat_idx >= 25) && ($flat_idx <= 100))
		//{
		//	$debug = True;
		//}
		
		if ($debug) { echo 'ENTER mime_number_smart<br>'; }
		if ($debug) { echo 'fed var flat_idx: '. $flat_idx.'<br>'; }
		if ($debug) { echo 'fed var new_mime_dumb: '. $new_mime_dumb.'<br>'; }
		//error check
		if ($new_mime_dumb == $not_set)
		{
			$smart_mime_number = 'error 1 in mime_number_smart';
			break;
		}
		
		// explode new_mime_dumb into an array
		$exploded_mime_dumb = Array();
		if (strlen($new_mime_dumb) == 1)
		{
			if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: TRUE ; FIRST debth level<br>'; }
			$exploded_mime_dumb[0] = (int)$new_mime_dumb;
		}
		else
		{
			if ($debug) { echo 'strlen(new_mime_dumb) = 1 :: FALSE<br>'; }
			$exploded_mime_dumb = explode('.', $new_mime_dumb);
		}
		
		// cast all values in exploded_mime_dumb as integers
		for ($i = 0; $i < count($exploded_mime_dumb); $i++)
		{
			$exploded_mime_dumb[$i] = (int)$exploded_mime_dumb[$i];
		}
		if ($debug) { echo 'exploded_mime_dumb '.serialize($exploded_mime_dumb).'<br>'; }
		
		// make an array of all parts of this family tree,  from the current part (the outermost) to innermost (closest to debth level 1)
		$dumbs_part_nice = Array();
		//loop BACKWARDS
		for ($i = count($exploded_mime_dumb) - 1; $i > -1; $i--)
		{
			if ($debug) { echo 'exploded_mime_dumb reverse loop i=['.$i.']<br>'; }
			// is this the outermost (current) part ?
			if ($i == (count($exploded_mime_dumb) - 1))
			{
				$dumbs_part_nice[$i] = $part_nice[$flat_idx];
				if ($debug) { echo '(outermost/current part) dumbs_part_nice[i('.$i.')] = part_nice[flat_idx('.$flat_idx.')]<br>'; }
				//if ($debug) { echo ' - prev_parent_flat_idx: '.$prev_parent_flat_idx.'<br>'; }
			}
			else
			{
				$this_dumbs_idx = $dumbs_part_nice[$i+1]['ex_parent_flat_idx'];
				$dumbs_part_nice[$i] = $part_nice[$this_dumbs_idx];
				if ($debug) { echo 'dumbs_part_nice[i('.$i.')] = part_nice[this_dumbs_idx('.$this_dumbs_idx.')]<br>'; }
			}
		}
		//if ($debug) { echo 'dumbs_part_nice serialized: '.serialize($dumbs_part_nice) .'<br>'; }
		//if ($debug) { echo 'serialize exploded_mime_dumb: '.serialize($exploded_mime_dumb).'<br>'; }
		
		// NOTE:  Packagelist -> Container EXCEPTION Conversions
		// a.k.a "Exceptions for Less-Standart Subtypes"
		// are located in the analysis loop done that BEFORE you enter this function
		
		// Reconstruct the Dumb Mime Number string into a "SMART" Mime Number string
		// RULE:  Dumb Mime parts that have "m_description" = "packagelist" (i.e. it's a header part)
		//	should be ommitted when constructing the Smart Mime Number
		// WITH 2 EXCEPTIONS:
		//	(a) debth 1 parts that are "packagelist" *never* get altered in any way
		//	(b) outermost debth parts that are "packagelist" get a value of "0", not ommitted
		//	(c) for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted
		
		// apply the rules
		$smart_mime_number_array = Array();
		for ($i = 0; $i < count($dumbs_part_nice); $i++)
		{
			if (((int)$dumbs_part_nice[$i]['ex_level_debth'] == 1)
			|| ($i == 0))
			{
				// debth 1 part numbers are never altered
				$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
			}
			// is this the outermost level (i.e. the last dumb mime number)
			elseif ($i == (count($exploded_mime_dumb) - 1))
			{
				// see outermost rule above
				if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
				{
					// it gets a value of zero
					$smart_mime_number_array[$i] = 0;
				}
				else
				{
					// no need to change
					$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
				}
			}
			// we covered the exceptions, now apply the ommiting rule
			else
			{
				if ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
				{
					// mark this for later removal (ommition)
					$smart_mime_number_array[$i] = $not_set;
				}
				else
				{
					// no need to change
					$smart_mime_number_array[$i] = $exploded_mime_dumb[$i];
				}
			}
		}
		
		// for 2 "packagelist"s in sucession, the first one gets a "1", not ommitted
		for ($i = 0; $i < count($dumbs_part_nice); $i++)
		{
			if (($i > 0) // not innermost
			&& ($dumbs_part_nice[$i]['m_description'] == 'packagelist')
			&& ($dumbs_part_nice[$i-1]['m_description'] == 'packagelist'))
			{
				$smart_mime_number_array[$i-1] = 1;
			}
		}
		
		// make the "smart mime number" based on the info gathered and the above rules
		// as applied to the smart_mime_number_array
		$smart_mime_number = '';
		for ($i = 0; $i < count($smart_mime_number_array); $i++)
		{
			if ($smart_mime_number_array[$i] != $not_set)
			{
				$smart_mime_number = $smart_mime_number . (string)$smart_mime_number_array[$i];
				// we  add a dot "." if this is not the outermost debth level
				if ($i != (count($smart_mime_number_array) - 1))
				{
					$smart_mime_number = $smart_mime_number . '.';
				}
			}
		}
		if ($debug) { echo 'FINAL smart_mime_number: '.$smart_mime_number.'<br><br>'; }
		return $smart_mime_number;
	}

	function make_part_clickable($part_nice, $msgball)
	{
		$not_set = $this->not_set;
		
		// Part Number used to request parts from the server
		$m_part_num_mime = $part_nice['m_part_num_mime'];
		
		$part_name = $part_nice['ex_part_name'];
		
		// make a URL to directly access this part
		if ($part_nice['type'] != $not_set)
		{
			$url_part_type = $part_nice['type'];
		}
		else
		{
			$url_part_type = 'unknown';
		}
		if ($part_nice['subtype'] != $not_set)
		{
			$url_part_subtype = $part_nice['subtype'];
		}
		else
		{
			$url_part_subtype = 'unknown';
		}
		if ($part_nice['encoding'] != $not_set)
		{
			$url_part_encoding = $part_nice['encoding'];
		}
		else
		{
			$url_part_encoding = 'other';
		}
		// make a URL to directly access this part
		$url_part_name = urlencode($part_name);
		// ex_part_href
		$ex_part_href = $GLOBALS['phpgw']->link(
			'/index.php',
			'menuaction=email.boaction.get_attach'
			.'&'.$msgball['uri'].'&msgball[part_no]='.$m_part_num_mime
			.'&type=' .$url_part_type
			.'&subtype=' .$url_part_subtype
			.'&name=' .$url_part_name
			.'&encoding=' .$url_part_encoding); 
		// Make CLICKABLE link directly to this attachment or part
		$href_part_name = $this->decode_header_string($part_name);
		// ex_part_clickable
		$ex_part_clickable = '<a href="'.$ex_part_href.'">'.$href_part_name.'</a>';
		// put these two vars in an array, and pass it back to the calling process
		$click_info = Array();
		$click_info['part_href'] = $ex_part_href;
		$click_info['part_clickable'] = $ex_part_clickable;
		return $click_info;
	}

	// function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
	// http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
	// modified to make mailto: addresses compose in phpGW
	function make_clickable($data, $folder)
	{
		if(empty($data))
		{
			return $data;
		}

		$lines = split("\n",$data);

		while ( list ($key,$line) = each ($lines))
		{
			$line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
			$line = eregi_replace("([ \t]|^)ftp\."," ftp://ftp.",$line);
			$line = eregi_replace("(http://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(https://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("(ftp://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
			$line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))",
				"<a href=\"".$GLOBALS['phpgw']->link("/".$GLOBALS['phpgw_info']['flags']['currentapp']."/compose.php","folder=".$this->prep_folder_out($folder))
				."&to=\\1\">\\1</a>", $line);

			$newText .= $line . "\n";
		}
		return $newText;
	}

	function has_this_param($param_array, $needle='')
	{
		if ((!isset($param_array))
		|| (count($param_array) < 1)
		|| ($needle == ''))
		{
			return False;
		}
		elseif (isset($param_array[0]['attribute']))
		{
			// we have a phpgw flat part array input
			for ($p = 0; $p < count($param_array); $p++)
			{
				if (stristr($param_array[$p]['attribute'], $needle))
				{
					return True;
					// implicit break with that return
				}
			}
		}
		elseif (isset($param_array[0]->attribute))
		{
			// we have a PHP fetchstructure input
			for ($p = 0; $p < count($param_array); $p++)
			{
				if (stristr($param_array[$p]->attribute, $needle))
				{
					return True;
					// implicit break with that return
				}
			}
		}
		else
		{
			return False;
		}
	}

	function array_keys_str($my_array)
	{
		$all_keys = Array();
		$all_keys = array_keys($my_array);
		return implode(', ',$all_keys);
	}

	/*!
	@function report_moved_or_deleted
	@abstract if mail was moved or deleted, we should report to the user what happened
	@param none, it uses the class args described below in "discussion"
	@result string which has either (a) a langed report to show the user about the move/delete that just occured
	or (b) an empty string indicating no move or delete actions were taken, so none need to report anything
	@discussion uses the following class args:
	  ['args']['td']	"td" means "Total Deleted", if it's filled it contains the number of messages that were deleted
	  ['args']['tm']	"tm" means "Total Moved", if it's filled it contains the number of messages that were moved
	  ['args']['tf']	"tf" means "To Folder", if it's filled it contains the name of the folder that messages were moved to
	if the user requests a delete, then arg "td" SHOULD/MUST be filled with that information
	if the user requests a move, then BOTH args "tm" AND "tf" SHOULD/MUST be filled with that information
	"tm" is the number of messages moved, and it's most useful to know where they were moved to, hence "tf"
	*/
	function report_moved_or_deleted()
	{
		// initialize return report string
		$report_this = '';
		// "td" means "Total Deleted", if it's filled it contains the number of messages that were deleted
		// when user deleted mail this arg should be filled with that information
		if (($this->get_isset_arg('td'))
		&& ($this->get_arg_value('td') != ''))
		{
			// report on number of messages DELETED (if any)
			if ($this->get_arg_value('td') == 1) 
			{
				$report_this = lang("1 message has been deleted",$this->get_arg_value('td'));
			}
			else
			{
				$report_this = lang("x messages have been deleted",$this->get_arg_value('td'));
			}
		}
		elseif (($this->get_isset_arg('tm'))
		&& ($this->get_arg_value('tm') != ''))
		{
			// report on number of messages MOVED (if any)
			// "tm" means "Total Moved", if it's filled it contains the number of messages that were moved
			// if the user moves messages this arg should be filled with that information			
			
			// "tf" means "To Folder", if it's filled it contains the name of the folder that messages were moved to
			// if the user moves messages this arg should be filled with that information
			// if "tm" is filled then "tf" SHOULD/MUST also be filled
			if (($this->get_isset_arg('tf'))
			&& ($this->get_arg_value('tf') != ''))
			{
				$_tf = $this->prep_folder_in($this->get_arg_value('tf'));
			}
			else
			{
				$_tf = 'empty';
			}
			// with the name of the "To Folder" we can build our report string
			if ($this->get_arg_value('tm') == 0) 
			{
				// these args are filled, indicating a MOVE was attempted
				// but since 0 messages were in fact moved, there must have been an error
				$report_this = lang("Error moving messages to").' '.$_tf;
			}
			elseif ($this->get_arg_value('tm') == 1)
			{
				$report_this = lang("1 message has been moved to").' '.$_tf;
			}
			else
			{
				$report_this = $this->get_arg_value('tm').' '.lang("messages have been moved to").' '.$_tf;
			}
		}
		else
		{
			// nothing deleted or moved, so there's nothing to report (blank string)
			$report_this = '';
		}
		return $report_this;
	}

	/*!
	@function report_total_foldersize
	@abstract get the total of all messges sizes in a folder added up to "folder size"
	@param array, the single argument $report_args_array is an array with these 3 members
	$report_args_array['allow_stats_size_speed_skip']  boolean
		getting folder size *can* take long time if alot of mail is in the folder, and put unneeded load on the IMAP server
		set to True to skip getting the folder size if there are more messages in the folder than specified 
		by "stats_size_threshold". 
		Default: False
	$report_args_array['stats_size_threshold']  integer
		test to skip getting the folder size if there are more messages in the folder than this number
		Default: 100   (i.e. 100 messages is the test threshold)
	$report_args_array['number_all']  integer  total number of messages in the folder
		if you know the total number of messages in the folder before calling this function, then fill this
		otherwise this function will obtain the data by itself if it needs it
		Default: not applicable
	@result string, either (a) folder size nicely formatted for human readability, or (b) an empty string
	if it was not OK to obtain the data according to the speed skip test
	@discussion  total size of all emails in this folder added up, if its OK to get that data
	*/
	function report_total_foldersize($report_args_array='')
	{
		if(!$report_args_array)
		{
			$report_args_array=array();
		}
		// initialize return value
		$return_folder_size = '';
		// if it's ok to obtain size, and size IS obtained, $return_folder_size will be filled
		
		// ----  set defaults for the args array  ----
		// can take a long time if alot of mail is in the folder, and put unneeded load on the IMAP server
		// should we do this or not?
		if (!isset($report_args_array['allow_stats_size_speed_skip']))
		{
			$report_args_array['allow_stats_size_speed_skip'] = False;
		}
		// if "allow_stats_size_speed_skip" is True
		// then if the number of messages in the folder exceeds this number, then we skip getting the folder size
		if (!isset($report_args_array['stats_size_threshold']))
		{
			$report_args_array['stats_size_threshold'] = 100;
		}
		// to test whether to skip or not, we'll need to know the total number og messages in the folder
		if (($report_args_array['allow_stats_size_speed_skip'] == True)
		&& (!isset($report_args_array['number_all'])))
		{
			// get the data using a "high level" function call for this purpose
			$folder_info = array();
			$folder_info = $this->get_folder_status_info();
			$report_args_array['number_all'] = $folder_info['number_all'];
		}
		
		// ----  Is It OK To Get The Folder Size?  ----
		// determine if we should show the folder size
		if (($this->get_isset_arg('force_showsize'))
		&& ($this->get_arg_value('force_showsize') != ''))
		{
			// user has requested override of this speed skip option
			$do_show_size = True;
		}
		elseif (($report_args_array['allow_stats_size_speed_skip'] == True)
		&& ($report_args_array['number_all'] > $report_args_array['stats_size_threshold']))
		{
			// spped skip option is enabled and number messages exceeds skip threshold
			$do_show_size = False;
		}
		else
		{
			// if either of those are not met, just show the size of the folder
			$do_show_size = True;

		}
		
		// ----  Get The Folder Size if it's OK  ----
		if ($do_show_size)
		{
			// FOLDER SIZE info obtained now
			$raw_folder_size = $this->get_folder_size();
			$return_folder_size = $this->format_byte_size($raw_folder_size);
		}
		return $return_folder_size;
	}

	/*!
	@function get_msg_list_display
	@abstract make an array containing all necessary data to display an "index.php" type list of mesasages
	@param $folder_info : array : (OPTIONAL) array elements as defined in return from function 
	  $this->get_folder_status_info() . This is primarily a time saver, if you already have the data, then pass it, 
	  else this function will obtain the data for itself.
	@param $folder_info : array of integers (OPTIONAL) integers representing a list of message numbers we 
	  should display, pass this data if you have search results to show, for example. If this is not present,
	  then this function get a numbered array list of all message numbers in that folder, sorted and ordered
	  according to preferences and/or user submitted page view args.
	@result array 
		first_item	boolean, flag indicating this is the first item in the array, the first message
				to display, states the obvious, but the index tpl uses it to show a form tag only once
		back_color	used in html UI's to alternate the color or each row of data
		has_attachment	attachment dection code has determined that this message has attachment(s)
				which tells the UI to show the user something, like a paperclip image.
		$msgnum	the number the mail server has assigned this message, used for fetching 
		subject		message subject text suitable for display, text only
		subject_link	URL that will request this message from the server, use with "subject" to make an HREF
		size		message size suitable for display
		is_unseen	this message has NOT yet been viewed by the client
		from_name	Part 1 of 2 of the From String to show the user. This part 1 is the "personal"
				data of the From person if it's available, if not we have no choice but to show
				the plain address of the from person.
		display_address_from	Part 2 of 2 of the From String to show the user. This part 2 contains
				any additional info the user prefers to see in the From String, which can be either
				(a) the plain address of the From person, or
				(b) the plain address of the ReplyTo header address
				According to user's preferences and considering what data is available
				to fulfill those user prefs
		who_to		Target address to send messages to when trying to "reply" to the author.
				Standard way to handle this is:
				(1) if ReplyTo is specified in the email header, then use it as the reply target
				(2) if no ReplyTo is specified, then we use the email address of the From person
				as the reply target. A seperate ReplyTo header address is optional when authoring
				a message, but it clearly states the intent of the "From person" that replying to the
				mail should NOT result in mail being sent to that "From person"'s address, so that
				intent SHOULD be honored.
				Another example: Quite often mailing lists use this header to make the
				"From" the person who sent the message to the list, and when you click "reply"
				the "ReplyTo" header indicates the mail should be sent to the list, NOT the
				person in the "From" header.
				Used to make the From String into a clickable HREF,
				which will produce a blank Compose Mail page with the To address filled targeted
				to this "who_to" value. This is different from a "reply to button" because no part
				of the original mail is included in the resulting Compose Mail page.
		from_link	URL that will produce an empty Compose New Mail page with the To address
				already filled in, which address is the determination made in the "who_to" logic
		msg_date	If the message arrived more than 1 day ago, this will be a date only.
				If the message arrived within one day, this will be the time of arrival with NO date.
	@discussion ?
	@access : private
	*/
	function get_msg_list_display($folder_info='', $msgball_list='')
	{
		$debug_msg_list_display	= 0;
		
		if(!$folder_info)
		{
			$folder_info=array();
		}
		if(!$msgball_list)
		{
			$msgball_list = array();
		}
		// obtain required data that is not passed into this function
		// if no $folder_info was passed as an arg, then $folder_info will be an array with 0 elements
		if (count($folder_info) == 0)
		{
			// use API-like high level function for this:
			$folder_info = array();
			$folder_info = $this->get_folder_status_info();
			/* returns this array:
			folder_info['is_imap'] boolean - pop3 server do not know what is "new" or not, IMAP servers do
			folder_info['folder_checked'] string - the folder checked, as processed by the msg class, which may have done a lookup on the folder name
			folder_info['alert_string'] string - lang'd string to show the user about status of new messages in this folder
			folder_info['number_new'] integer - for IMAP: the number "recent" and/or "unseen"messages; for POP3: the total number of messages
			folder_info['number_all'] integer - for IMAP and POP3: the total number messages in the folder
			and some validity data used for caching.
			*/
		}
		
		// initialize return structure
		$msg_list_display = Array();
		// if no message are available to show, return an empty aray
		if ($folder_info['number_all'] == 0)
		{
			return $msg_list_display;
		}
		
		// we have messages to list, continue...
		// if we were passed an array of message numbers to show, use that, if not then
		// get a numbered array list of all message numbers in that folder, sorted and ordered
		if (count($msgball_list) == 0)
		{
			$msgball_list = $this->get_msgball_list();
		}

		if ($folder_info['number_all'] < $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])
		{
			$totaltodisplay = $folder_info['number_all'];
		}
		elseif (($folder_info['number_all'] - $this->get_arg_value('start')) > $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'])
		{
			$totaltodisplay = $this->get_arg_value('start') + $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
		}
		else
		{
			$totaltodisplay = $folder_info['number_all'];
		}
		
		if ($this->get_isset_arg('start'))
		{
			$start = $this->get_arg_value('start');
		}
		else
		{
			$start = 0;
		}
		// keep track of how many loops we've done, for the return array, will be advanced to 0 before it's used
		$x = -1;
		for ($i=$start; $i < $totaltodisplay; $i++)
		{
			if ($debug_msg_list_display > 2) { echo 'mail_msg: get_msg_list_display: $msgball_list['.$i.'] dump:<pre>'; print_r($msgball_list[$i]); echo '</pre>'; }
			// we use $x to sequentially fill the $msg_list_display array
			$x++;
			// place the delmov form header tags ONLY ONCE, blank string all subsequent loops
			$msg_list_display[$x]['first_item'] = ($i == $this->get_arg_value('start'));

			// ROW BACK COLOR
			$msg_list_display[$x]['back_color'] = (($i + 1)/2 == floor(($i + 1)/2)) ? $GLOBALS['phpgw_info']['theme']['row_off'] : $GLOBALS['phpgw_info']['theme']['row_on'];
			//$msg_list_display[$x]['back_color'] = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($msg_list_display[$x-1]['back_color']);

			// SHOW ATTACHMENT CLIP ?
			// SKIP this for POP3 - fetchstructure for POP3 requires download the WHOLE msg
			// so PHP can build the fetchstructure data (IMAP server does this internally)
			
			//$tmp_a = $this->a[$this->acctnum];
			//if ((isset($tmp_a['dcom']->imap_builtin))
			//&& ($tmp_a['dcom']->imap_builtin == False)
			if ((isset($GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->imap_builtin))
			&& ($GLOBALS['phpgw_dcom_'.$this->acctnum]->dcom->imap_builtin == False)
			&& (stristr($this->get_pref_value('mail_server_type'), 'pop3')))
			{
				// do Nothing - socket class pop3 not ready for this stress yet
				$msg_list_display[$x]['has_attachment'] = False;
			}
			else
			{
				// need Message Information: STRUCTURAL for this
				//$msg_structure = $this->phpgw_fetchstructure($msgball_list[$i]['msgnum']);
				$msg_structure = $this->phpgw_fetchstructure($msgball_list[$i]);
				// now examine that msg_struct for signs of an attachment
				$msg_list_display[$x]['has_attachment'] = $this->has_real_attachment($msg_structure);
			}
			//$this->a[$this->acctnum] = $tmp_a;

			// Message Information: THE MESSAGE'S HEADERS ENVELOPE DATA
			//$hdr_envelope = $this->phpgw_header($msgball_list[$i]['msgnum']);
			$hdr_envelope = $this->phpgw_header($msgball_list[$i]);
			
			// MESSAGE REFERENCE (a) NUMBER (b) FOLDER (c) ACCTNUM and (d) FAKE_URL EMBEDDED MULTI DATA
			$msg_list_display[$x]['msgnum'] = $msgball_list[$i]['msgnum'];
			$msg_list_display[$x]['folder'] = $msgball_list[$i]['folder'];
			$msg_list_display[$x]['acctnum'] = $msgball_list[$i]['acctnum'];
			$msg_list_display[$x]['uri'] = $msgball_list[$i]['uri'];
			
			// SUBJECT
			// NOTE: the acctnum MUST be matched to this individual message and folder
			$msg_list_display[$x]['subject'] = $this->get_subject($hdr_envelope,'');
			$msg_list_display[$x]['subject_link'] = $GLOBALS['phpgw']->link(
				//'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/message.php',
				'/index.php',
				 'menuaction=email.uimessage.message'
				.'&'.$msgball_list[$i]['uri']
				.'&sort='.$this->get_arg_value('sort')
				.'&order='.$this->get_arg_value('order')
				.'&start='.$this->get_arg_value('start'));
			
			// SIZE
			if ($this->newsmode)
			{
				// nntp apparently gives size in number of lines ?
				//$msg_list_display[$x]['size'] = $hdr_envelope->Size;
				$msg_list_display[$x]['size'] = $hdr_envelope->Lines;
			}
			else
			{
				$msg_list_display[$x]['size'] = $this->format_byte_size($hdr_envelope->Size);
			}

			// SEEN OR UNSEEN/NEW
			if (($hdr_envelope->Unseen == 'U') || ($hdr_envelope->Recent == 'N'))
			{
				$msg_list_display[$x]['is_unseen'] = True;
			}
			else
			{
				$msg_list_display[$x]['is_unseen'] = False;
			}

			// FROM and REPLY TO  HANDLING
			
			// ---- What to use as From Person's target email address  ----
			// $reply is used to construct the "from link" below, it determines the target address
			// to send mail to when the user clicks on a clickable "from string" that is an HREF
			// Standard way to handle this is:
			// (1) if ReplyTo is specified in the email header, then use it as the reply target
			// (2) if no ReplyTo is specified, then we use the email address of the From person
			// as the reply target. A seperate ReplyTo header address is optional but
			// clearly states the intent of the "From person" that replying to the mail should
			// NOT result in mail being sent to that "From person"'s address, so that intent
			// SHOULD be honored for this clickable From String as HREF capability
			if ($hdr_envelope->reply_to[0])
			{
				$reply = $hdr_envelope->reply_to[0];
			}
			else
			{
				$reply = $hdr_envelope->from[0];
			}
			//$replyto = $this->make_rfc2822_address($reply);
			$replyto = $reply->mailbox.'@'.$reply->host;
			
			/*
			@capability FROM DISPLAYABLE String
			@abstract: display the "from" data according to user preferences
			@result : string which is actually part 2 of 2 of the From String, 
			with "from_name" being part 1 of 2.
			@discussion: first some background on the terms used here:
			* "plain address" means the "user@domain.com" part
			* "personal" means the name string that may be associated with that address
				in the headers that would look like this if present: "Joe Dough" <user@domain.com>
				where "Joe Dough is the "personal" part of the address, but it's not always available
			ISSUE 1: Assume the user always wants "personal" string shown, if it's available
			If personal not available, we have no choice but to use the "plain address" as the displayed From string
			ISSUE 2: question is when to also show the plain address with that personal data as the display string
			of course, if the personal data is not available, then we show the plain anyway
			ISSUE 3: and if that plain address should be the "from" or "reply to (if any)" as the plain address part
			of the display string. There IS actually an option to display the plain address of the specified
			"ReplyTo" header in the From String the user wants to see,
			*/
			$from = $hdr_envelope->from[0];
			if (!$from->personal)
			{
				// no "personal" info available, only can show plain address
				$personal = $from->mailbox.'@'.$from->host;
			}
			else
			{
				$personal = $this->decode_header_string($from->personal);
			}
			if ($personal == '@')
			{
				$personal = $replyto;
			}
			
			if (($this->get_pref_value('show_addresses') == 'from')
			&& ($personal != $from->mailbox.'@'.$from->host))
			{
				/*
				@capabability	"From String" is Personal data AND the "plain address" of the From person
				@discussion:	according to preferences, for the displayed "From" string the user wants to
				see the "personal" data AND the "plain address" data of the person who the message is from
				as the "From String" that is displated to the user.
				Additionally, we checked and made sure both those pieces of data are available.
				*/
				$msg_list_display[$x]['display_address_from'] = '('.$from->mailbox.'@'.$from->host.')';
				$msg_list_display[$x]['who_to'] = $from->mailbox.'@'.$from->host;
			}
			elseif (($this->get_pref_value('show_addresses') == 'replyto')
			&& ($personal != $from->mailbox.'@'.$from->host))
			{
				/*
				@capabability	From String includes ReplyTo plain address
				@discussion:	according to preferences, for the displayed "From" string the user wants to
				see the "personal" data AND the plain address of the "ReplyTo" header, if available.
				To visually indicate this is reply to address, we surround in in < > 
				instead of ( ) which we use to surround the "from" plain address, as used above.
				Note: even though we use the "personal" name from the From header, we show
				with it the plain address from the ReplyTo header. This is how this preference works :)
				Of course, if no ReplyTo address is present, we can not fulfill this user perference
				*/
				$msg_list_display[$x]['display_address_from'] = '&lt;'.$replyto.'&gt;';
				$msg_list_display[$x]['who_to'] = $from->mailbox.'@'.$from->host;
			}
			else
			{
				/*
				@capabability	user sees ONLY the "plain address" of the From person
				@discussion:	The displayed "From String" the user will see is 
				the "plain address" of the From person ONLY, no "personal" data is ahown.
				This happens as a fallback option when the user's assumed desire to see the
				"personal" data is unable to be fulfilled because that "personal" data for the
				From person was not available in the email headers.
				*/
				$msg_list_display[$x]['display_address_from'] = '';
				$msg_list_display[$x]['who_to'] = $from->mailbox.'@'.$from->host;
			}
			
			// ----  From Name ----
			// Part 1 of 2 of the From string (see above)
			// NOTE: wasn't this decode_header_string proc already done above?
			$msg_list_display[$x]['from_name'] = $this->decode_header_string($personal);

			// ----  From Link  ----
			// this is a URL that can be used to turn the "From String" into a clickable link
			// that produces a blank Compose Mail page with the corrent Reply target address as the
			// to address. This is different than a typical "reply to" function in that no part of
			// the original email is included in this Compose page. Also note that the email app's
			// message list page, email/index.php, does not have a "reply to" button anywhere on it,
			// said button is in the "show the message contents" page, email/message.php
			$msg_list_display[$x]['from_link'] = $GLOBALS['phpgw']->link(
								'/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/compose.php',
								// 'folder='.$this->prep_folder_out('')
								 $msgball_list[$i]['uri']
								.'&sort='.$this->get_arg_value('sort')
								.'&order='.$this->get_arg_value('order')
								.'&start='.$this->get_arg_value('start')
								.'&to='.urlencode($msg_list_display[$x]['who_to']));
			if ($personal != $from->mailbox.'@'.$from->host)
			{
				$msg_list_display[$x]['from_link'] = $msg_list_display[$x]['from_link'] .'&personal='.urlencode($personal);
			}
			
			// if it's a long plain address with no spaces, then add a space to the TD can wrap the text
			if ((!strstr($msg_list_display[$x]['from_name'], " "))
			&& (strlen($msg_list_display[$x]['from_name']) > 15)
			&& (strstr($msg_list_display[$x]['from_name'], "@")))
			{
				$msg_list_display[$x]['from_name'] = str_replace('@',' @',$msg_list_display[$x]['from_name']);
			}

			// DATE
			// date_time has both date and time, which probably is long enough to make a TD cell wrap text to 2 lines
			$msg_date_time = $GLOBALS['phpgw']->common->show_date($hdr_envelope->udate);
			if($GLOBALS['phpgw']->common->show_date($hdr_envelope->udate,'Ymd') != date('Ymd'))
			{
				// this strips the time part, leaving only the date, better for single line TD cells
				$msg_list_display[$x]['msg_date'] = ereg_replace(" - .*$", '', $msg_date_time);
			}
			else
			{
				// this strips the time part, leaving only the date, better for single line TD cells
				$msg_list_display[$x]['msg_date'] = ereg_replace("^.* -", '', $msg_date_time);
			}
		}
		if ($debug_msg_list_display > 2) { echo 'mail_msg: get_msg_list_display: exiting $msg_list_display[] dump:<pre>'; print_r($msg_list_display); echo '</pre>'; }
		return $msg_list_display;
	}

} // end class mail_msg
?>
