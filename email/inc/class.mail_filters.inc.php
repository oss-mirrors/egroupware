<?php
  /**************************************************************************\
  * phpGroupWare API - mail filters					*
  * This file written by Angelo (Angles) Puglisi <angles@phpgroupware.org>	*
  * Copyright (C) 2001 Angelo Puglisi (Angles)				*
  * -------------------------------------------------------------------------			*
  * This library is part of the phpGroupWare API				*
  * http://www.phpgroupware.org/api					* 
  * ------------------------------------------------------------------------ 			*
  * This library is free software; you can redistribute it and/or modify it	*
  * under the terms of the GNU Lesser General Public License as published by 	*
  * the Free Software Foundation; either version 2.1 of the License,		*
  * or any later version.						*
  * This library is distributed in the hope that it will be useful, but		*
  * WITHOUT ANY WARRANTY; without even the implied warranty 	*
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
  * See the GNU Lesser General Public License for more details.		*
  * You should have received a copy of the GNU Lesser General Public License	*
  * along with this library; if not, write to the Free Software Foundation,	*
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA		*
  \**************************************************************************/

	/* $Id$ */

	class mail_filters
	{
		//  pointer to the mail_msg class that is calling this filter class
		var $mail_msg = nil;
		var $filters = Array();
		var $template = '';
		var $finished_mlist = '';
		var $submit_mlist_to_class_form = '';
		var $submit_flag = '';
		//var $debug_level = 0;
		var $debug_level = 1;
		//var $debug_level = 2;
		var $sieve_to_imap_fields=array();
		var $result_set = Array();
		var $result_set_mlist = Array();
		var $fake_folder_info = array();
		
		function mail_filters()
		{
			$this->sieve_to_imap_fields = Array(
				'from'		=> 'FROM',
				'to'		=> 'TO',
				'cc'		=> 'CC',
				'bcc'		=> 'BCC',
				'recipient'	=> 'FIX_ME: TO or CC or BCC',
				'sender'	=> 'SEARCHHEADER SENDER',
				'subject'	=> 'SUBJECT',
				'header'	=> 'FIX_ME SEARCHHEADER FIX_ME',
				'size_larger'	=> 'LARGER',
				'size_smaller'	=> 'SMALLER',
				'allmessages'	=> 'FIX_ME (matches all messages)',
				'body'		=> 'BODY'
			);
		}
		
		function distill_filter_args()
		{
			// do we have data
			if  (!isset($GLOBALS['HTTP_POST_VARS'][$this->submit_flag]))
			{
				if ($this->debug_level > 0) { echo 'mail_filters: distill_filter_args: NO data submitted<br>'."\r\n"; }
				return Array();
			}
			
			// look for top level "filter_X" array
			while(list($key,$value) = each($GLOBALS['HTTP_POST_VARS']))
			{
				if (strstr($key, 'filter_'))
				{
					// put the raw data dor this particular filter into a local var
					$filter_X = $GLOBALS['HTTP_POST_VARS'][$key];
					if ($this->debug_level > 0) { echo 'mail_filters: distill_filter_args: filter_X dump <strong><pre>'; print_r($filter_X); echo "</pre></strong>\r\n"; }
					
					// prepare to fill your structured array
					$this_idx = count($this->filters);
					// grab the "filter name" associated with this data
					$this->filters[$this_idx]['filtername'] = $filter_X['filtername'];
					// what folder so we search
					$this->filters[$this_idx]['source_folder'] = $filter_X['source_folder'];
					// init sub arrays
					$this->filters[$this_idx]['matches'] = Array();
					$this->filters[$this_idx]['actions'] = Array();
					// extract match and action data from this filter_X data array
					while(list($filter_X_key,$filter_X_value) = each($filter_X))
					{
						/*
						@capability: extract multidimentional filter data embedded in this 1 dimentional array
						@discussion: php3 limits POST arrays to one level of array key/value pairs
						thus complex filtering instructions are containded in special strings submitted as controls names
						matching instructions willlook something like this:
							$filter_X ['match_0_comparator'] => 'contains'
						the "key" string "match_0_comparator" needs to be "decompressed" into an associative array
						the string means this:
						a: we are dealing with "match" data
						b: when this data is "decompressed" this would be match[0] data
						c: that this should be match[0] ["comparator"] where "comparator" is the key, and
						d: that value of this match[0]["comparator"] = "contains"
						thus, we are looking at a match to see if something "contains" a string that will be described in the next key/value iteration
						such string may look like this in its raw form:
							[match_0_matchthis] => "@spammer.com"
						translates to this:
							match[0]["matchthis"] = "@spammer.com"
						@author Angles
						*/
						if (strstr($filter_X_key, 'match_'))
						{
							// now we grab the index value from the key string
							$match_this_idx = (int)$filter_X_key[6];
							if ($this->debug_level > 1) { echo 'mail_filters: distill_filter_args: match_this_idx grabbed value: ['.$match_this_idx.']<br>'; }
							// grab "key" that comes after that match_this_idx we just got
							// remember "substr" uses 1 as the first letter in a string, not 0, AND starts returning the letter AFTER the specified location
							$match_grabbed_key = substr($filter_X_key, 8);
							if ($this->debug_level > 1) { echo 'mail_filters: distill_filter_args: match_grabbed_key value: ['.$match_grabbed_key.']<br>'; }
							$this->filters[$this_idx]['matches'][$match_this_idx][$match_grabbed_key] = $filter_X[$filter_X_key];
						}
						/*
						@capability: extract multidimentional filter data embedded in this 1 dimentional array
						@discussion: php3 limits POST arrays to one level of array key/value pairs
						thus complex filtering instructions are containded in special strings submitted as controls names
						action instructions willlook something like this:
							$filter_X ['action_1_judgement'] => 'fileinto'
						the "key" string "action_1_judgement" needs to be "decompressed" into an associative array
						the string means this:
						a: we are dealing with "action" instructions
						b: when this data is "decompressed" this would be action[1] data
						c: that this should be action[1] ["judgement"] where "judgement" is the key, and
						d: that value of this action[1] ["judgement"] = "fileinto"
						@author Angles
						*/
						elseif (strstr($filter_X_key, 'action_'))
						{
							// now we grab the index value from the key string
							$action_this_idx = (int)$filter_X_key[7];
							if ($this->debug_level > 1) { echo 'mail_filters: distill_filter_args: action_this_idx grabbed value: ['.$action_this_idx.']<br>'; }
							// grab "key" that comes after that match_this_idx we just got
							// remember "substr" uses 1 as the first letter in a string, not 0, AND starts returning the letter AFTER the specified location
							$action_grabbed_key = substr($filter_X_key, 9);
							if ($this->debug_level > 1) { echo 'mail_filters: distill_filter_args: action_grabbed_key value: ['.$action_grabbed_key.']<br>'; }
							$this->filters[$this_idx]['actions'][$action_this_idx][$action_grabbed_key] = $filter_X[$filter_X_key];
						}
					}
				}
			}
			if ($this->debug_level > 0) { echo 'mail_filters: distill_filter_args: this->filters[] dump <strong><pre>'; print_r($this->filters); echo "</pre></strong>\r\n"; }
		}

		function sieve_to_imap_string()
		{
			if ($this->debug_level > 2) { echo 'mail_filters: sieve_to_imap_string: mappings are:<pre>'; print_r($this->sieve_to_imap_fields); echo "</pre>\r\n"; }
			$look_here_sieve = $this->filters[0]['matches'][0]['examine'];
			$look_here_imap = $this->sieve_to_imap_fields[$look_here_sieve];
			$for_this = $this->filters[0]['matches'][0]['matchthis'];
			
			$conv_error = '';
			if ((!isset($look_here_sieve))
			|| (trim($look_here_sieve) == '')
			|| ($look_here_imap == ''))
			{
				$conv_error = 'invalid or no examine data';
				if ($this->debug_level > 0) { echo '<b> *** error</b>: mail_filters: sieve_to_imap_string: error: '.$conv_error."<br> \r\n"; }
				return '';
			}
			elseif ((!isset($for_this))
			|| (trim($for_this) == ''))
			{
				$conv_error = 'invalid or no search string data';
				if ($this->debug_level > 0) { echo '<b> *** error</b>: mail_filters: sieve_to_imap_string: error: '.$conv_error."<br> \r\n"; }
				return '';
			}
			
			$imap_str = $look_here_imap.' "'.$for_this.'"';
			if ($this->debug_level > 0) { echo 'mail_filters: sieve_to_imap_string: string is: '.$imap_str."<br>\r\n"; }
			return $imap_str;
		}

		
		function do_imap_search()
		{
			$imap_search_str = $this->sieve_to_imap_string();
			if (!$imap_search_str)
			{
				if ($this->debug_level > 0) { echo '<b> *** error</b>: mail_filters: do_imap_search: sieve_to_imap_string returned empty<br>'."\r\n"; }
				return array();
			}
			
			$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			//$GLOBALS['phpgw']->msg->grab_class_args_gpc();
			$args_array = Array();
			if ((isset($this->filters[0]['source_folder']))
			&& ($this->filters[0]['source_folder'] != ''))
			{
				if ($this->debug_level > 0) { echo 'mail_filters: do_imap_search: this->filters[0][source_folder] = ' .$this->filters[0]['source_folder'].'<br>'."\r\n"; }
				$args_array['folder'] = $this->filters[0]['source_folder'];
			}
			else
			{
				$args_array['folder'] = 'INBOX';
			}
			
			$args_array['do_login'] = True;
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			
			$initial_result_set = Array();
			$initial_result_set = $GLOBALS['phpgw']->msg->dcom->i_search(
							$GLOBALS['phpgw']->msg->mailsvr_stream,
							$imap_search_str);
			// sanity check on 1 returned hit, is it for real?
			if (($initial_result_set == False)
			|| (count($initial_result_set) == 0))
			{
				echo 'mail_filters: do_imap_search: no hits or possible search error<br>'."\r\n";
				echo 'mail_filters: do_imap_search: server_last_error (if any) was: "'.$GLOBALS['phpgw']->msg->dcom->server_last_error().'"'."\r\n";
				// we leave this->result_set_mlist an an empty array, as it was initialized on class creation
			}
			else
			{
				$this->result_set = $initial_result_set;
				if ($this->debug_level > 0) { echo 'mail_filters: do_imap_search: number of matches = ' .count($this->result_set).'<br>'."\r\n"; }
				// make a "fake" folder_info array to make things simple for get_msg_list_display
				$this->fake_folder_info['is_imap'] = True;
				$this->fake_folder_info['folder_checked'] = $GLOBALS['phpgw']->msg->folder;
				$this->fake_folder_info['alert_string'] = 'you have search results';
				$this->fake_folder_info['number_new'] = count($this->result_set);
				$this->fake_folder_info['number_all'] = count($this->result_set);
				// retrieve user displayable data for each message in the result set
				$this->result_set_mlist = $GLOBALS['phpgw']->msg->get_msg_list_display($this->fake_folder_info,$this->result_set);
			}
			$GLOBALS['phpgw']->msg->end_request();
			//echo 'mail_filters: do_imap_search: returned:<br>'; var_dump($this->result_set); echo "<br>\r\n";
		}

		function make_mlist_box()
		{
			$this->template = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			$this->template->set_file(array(		
				'T_index_blocks' => 'index_blocks.tpl'
			));
			$this->template->set_block('T_index_blocks','B_mlist_form_init','V_mlist_form_init');
			$this->template->set_block('T_index_blocks','B_arrows_form_table','V_arrows_form_table');
			$this->template->set_block('T_index_blocks','B_mlist_block','V_mlist_block');
			$this->template->set_block('T_index_blocks','B_mlist_submit_form','V_mlist_submit_form');
			
			$tpl_vars = Array(
				'mlist_font'		=> $GLOBALS['phpgw_info']['theme']['font'],
				'mlist_font_size'	=> '2',
				'mlist_font_size_sm'	=> '1',
				'V_mlist_form_init'	=> ''
			);
			$this->template->set_var($tpl_vars);
			
			if (count($this->result_set_mlist) == 0)
			{
				$this->template->set_var('V_mlist_block','');				
			}
			else
			{
				$this->template->set_var('V_no_messages','');				
				$this->template->set_var('mlist_attach','&nbsp;');
				for ($i=0; $i < count($this->result_set_mlist); $i++)
				{
					if ($this->result_set_mlist[$i]['is_unseen'])
					{
						$this->template->set_var('open_newbold','<strong>');
						$this->template->set_var('close_newbold','</strong>');
					}
					else
					{
						$this->template->set_var('open_newbold','');
						$this->template->set_var('close_newbold','');
					}
					$tpl_vars = Array(
						'mlist_msg_num'		=> $this->result_set_mlist[$i]['msg_num'],
						'mlist_backcolor'	=> $this->result_set_mlist[$i]['back_color'],
						'mlist_subject'		=> $this->result_set_mlist[$i]['subject'],
						'mlist_subject_link'	=> $this->result_set_mlist[$i]['subject_link'],
						'mlist_from'		=> $this->result_set_mlist[$i]['from_name'],
						'mlist_from_extra'	=> $this->result_set_mlist[$i]['display_address_from'],
						'mlist_reply_link'	=> $this->result_set_mlist[$i]['from_link'],
						'mlist_date'		=> $this->result_set_mlist[$i]['msg_date'],
						'mlist_size'		=> $this->result_set_mlist[$i]['size']
					);
					$this->template->set_var($tpl_vars);
					$this->template->parse('V_mlist_block','B_mlist_block',True);
				}
				$this->finished_mlist = $this->template->get_var('V_mlist_block');
				
				// MAKE SUBMIT TO MLIST FORM
				// make the voluminous MLIST hidden vars array
				$mlist_hidden_vars = '';
				for ($i=0; $i < count($this->result_set); $i++)
				{
					$this_msg_num = (string)$this->result_set[$i];
					$mlist_hidden_vars .= '<input type="hidden" name="mlist_set['.(string)$i.']" value="'.$this_msg_num.'">'."\r\n";
				}
				// preserve the folder we searched (raw posted source_folder was never preped in here, so it's ok to send out as is)
				$mlist_hidden_vars .= '<input type="hidden" name="folder" value="'.$this->filters[0]['source_folder'].'">'."\r\n";
				// make the first prev next last arrows
				$this->template->set_var('mlist_submit_form_action', $GLOBALS['phpgw']->link('/index.php','menuaction=email.uiindex.mlist'));
				$this->template->set_var('mlist_hidden_vars',$mlist_hidden_vars);
				$this->template->parse('V_mlist_submit_form','B_mlist_submit_form');
				
				$this->submit_mlist_to_class_form = $this->template->get_var('V_mlist_submit_form');
			}
			
		}
		
		
	
	// end of class
	}
?>
