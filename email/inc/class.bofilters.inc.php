<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail Filters							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* Copyright (C) 2001 Angelo Puglisi (Angles)					*
	* -----------------------------------------------                         				*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/
	
	/* $Id$ */
	
	class bofilters
	{
		var $public_functions = array(
			'process_submitted_data'	=> True,
			'run_single_filter'	=> True
		);
		
		var $all_filters = Array();
		var $filter_num = 0;
		//var $this_filter = Array();
		var $template = '';
		var $finished_mlist = '';
		var $submit_mlist_to_class_form = '';
		var $debug = 0;
		var $debug_set_prefs = 0;
		var $examine_imap_search_keys_map=array();
		var $result_set = Array();
		var $result_set_mlist = Array();
		var $fake_folder_info = array();
		
		var $inbox_full_msgball_list = array();
		var $each_row_result_mball_list = array();
		var $each_acct_final_mball_list = array();
		
		function bofilters()
		{
			if ($this->debug > 0) { echo 'email.bofilters *constructor*: ENTERING <br>'; }
			$this->examine_imap_search_keys_map = Array(
				'from'		=> 'FROM',
				'to'		=> 'TO',
				'cc'		=> 'CC',
				'bcc'		=> 'BCC',
				'recipient'	=> 'FIX_ME: TO or CC or BCC',
				'sender'	=> 'HEADER SENDER',
				'subject'	=> 'SUBJECT',
				'header'	=> 'FIX_ME SEARCHHEADER FIX_ME',
				'size_larger'	=> 'LARGER',
				'size_smaller'	=> 'SMALLER',
				'allmessages'	=> 'FIX_ME (matches all messages)',
				'body'		=> 'BODY'
			);
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug > 1) { echo 'email.bofilters *constructor*: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug > 1) { echo 'email.bofilters *constructor*: is_object: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			if ($GLOBALS['phpgw']->msg->get_isset_arg('already_grab_class_args_gpc'))
			{
				if ($this->debug > 0) { echo 'email.bofilters *constructor*: LEAVING , msg object already initialized<br>'; }
				return True;
			}
				
			if ($this->debug > 1) { echo 'email.bofilters *constructor*: msg object NOT yet initialized<br>'; }
			$args_array = Array();
			// should we log in or not, no, we only need prefs initialized
			// if any data is needed mail_msg will open stream for us
			$args_array['do_login'] = False;
			//$args_array['do_login'] = True;
			if ($this->debug > 1) { echo 'email.bofilters. *constructor*: call msg->begin_request with args array:'.serialize($args_array).'<br>'; }
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			$already_initialized = True;
			if ($this->debug > 0) { echo 'email.bofilters. *constructor*: LEAVING<br>'; }
			
		}
		
		function obtain_filer_num()
		{
			if ((isset($GLOBALS['HTTP_POST_VARS']['filter_num']))
			&& ((string)$GLOBALS['HTTP_POST_VARS']['filter_num'] != ''))
			{
				$filter_num = (int)$GLOBALS['HTTP_POST_VARS']['filter_num'];
			}
			elseif ((isset($GLOBALS['HTTP_GET_VARS']['filter_num']))
			&& ((string)$GLOBALS['HTTP_GET_VARS']['filter_num'] != ''))
			{
				$filter_num = (int)$GLOBALS['HTTP_GET_VARS']['filter_num'];
			}
			else
			{
				$filter_num = $this->get_next_avail_num();
			}
			return $filter_num;
		}
		
		function get_next_avail_num()
		{
			// NOT coded yet
			return 0;
		}
		
		function just_testing()
		{
			if ((isset($GLOBALS['HTTP_POST_VARS']['filter_test']))
			&& ((string)$GLOBALS['HTTP_POST_VARS']['filter_test'] != ''))
			{
				$just_testing = True;
			}
			elseif ((isset($GLOBALS['HTTP_GET_VARS']['filter_test']))
			&& ((string)$GLOBALS['HTTP_GET_VARS']['filter_test'] != ''))
			{
				$just_testing = True;
			}
			else
			{
				$just_testing = False;
			}
			return $just_testing;
		}
		
		function filter_exists($feed_filter_num)
		{
			if (count($this->all_filters) == 0)
			{
				$this->read_filter_data_from_prefs();
			}
			if ((isset($this->all_filters[$feed_filter_num]))
			&& (isset($this->all_filters[$feed_filter_num]['source_accounts'])))
			{
				return True;
			}
			else
			{
				return False;
			}
		}
		
		function process_submitted_data()
		{
			if ($this->debug_set_prefs > 0) { echo 'bofilters.process_submitted_data: ENTERING<br>'."\r\n"; }
			if ($this->debug_set_prefs > 2) { echo 'bofilters.process_submitted_data: HTTP_POST_VARS dump:<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'."\r\n"; }
			//if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: caling $this->distill_filter_args<br>'."\r\n"; }
			//$this->distill_filter_args();
			// we must have data because the form action made this code run
			$this_filter = array();
			
			// --- get submitted data that is not in the form of an array  ----
			
			// FILTER NUMBER
			if ((isset($GLOBALS['HTTP_POST_VARS']['filter_num']))
			&& ((string)$GLOBALS['HTTP_POST_VARS']['filter_num'] != ''))
			{
				$this_filter['filter_num'] = (int)$GLOBALS['HTTP_POST_VARS']['filter_num'];
			}
			else
			{
				echo 'bofilters.process_submitted_data: LEAVING with ERROR, unable to obtain POST filter_num';
				return;
			}
			if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $this_filter[filter_num]: ['.$this_filter['filter_num'].']<br>'; }
			
			// FILTER NAME
			if ((isset($GLOBALS['HTTP_POST_VARS']['filtername']))
			&& ((string)$GLOBALS['HTTP_POST_VARS']['filtername'] != ''))
			{
				$this_filter['filtername'] = $GLOBALS['HTTP_POST_VARS']['filtername'];
			}
			else
			{
				$this_filter['filtername'] = 'Filter '.$this_filter['filter_num'];
			}
			if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $this_filter[filtername]: ['.$this_filter['filtername'].']<br>'; }
			
			// ---- The Rest of the data is submitted in  Array Form ----
			
			// SOURCE ACCOUNTS
			if ((isset($GLOBALS['HTTP_POST_VARS']['source_accounts']))
			&& ((string)$GLOBALS['HTTP_POST_VARS']['source_accounts'] != ''))
			{
				// extract the "fake uri" data with parse_str
				// and fill our filter struct
				for ($i=0; $i < count($GLOBALS['HTTP_POST_VARS']['source_accounts']); $i++)
				{
					parse_str($GLOBALS['HTTP_POST_VARS']['source_accounts'][$i], $this_filter['source_accounts'][$i]);
					// re-urlencode the foldername, because we generally keep the fldball urlencoded
					$this_filter['source_accounts'][$i]['folder'] = urlencode($this_filter['source_accounts'][$i]['folder']);
					// make sure acctnum is an int
					$this_filter['source_accounts'][$i]['acctnum'] = (int)$this_filter['source_accounts'][$i]['acctnum'];
				}
				
			}
			else
			{
					$this_filter['source_accounts'][0]['folder'] = 'INBOX';
					$this_filter['source_accounts'][0]['acctnum'] = 0;
			}
			if ($this->debug_set_prefs > 2) { echo '.process_submitted_data: $this_filter[source_accounts] dump:<pre>'; print_r($this_filter['source_accounts']); echo '</pre>'."\r\n"; }
			
			// --- "deep" array form data ---
			@reset($GLOBALS['HTTP_POST_VARS']);
			// init sub arrays
			$this_filter['matches'] = Array();
			$this_filter['actions'] = Array();
			// look for top level "match_X[]" and "action_X[]" items
			while(list($key,$value) = each($GLOBALS['HTTP_POST_VARS']))
			{
				// do not walk thru data we already obtained
				if (($key == 'filter_num')
				|| ($key == 'filtername')
				|| ($key == 'source_accounts'))
				{
					if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $GLOBALS[HTTP_POST_VARS] key,value walk thru: $key: ['.$key.'] is data we already processed, skip to next loop<br>'; }
					continue;
				}
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $GLOBALS[HTTP_POST_VARS] key,value walk thru: $key: ['.$key.'] ; $value DUMP:<pre>'; print_r($value); echo "</pre>\r\n"; }
				// extract match and action data from this filter_X data array
				if (strstr($key, 'match_'))
				{
					// now we grab the index value from the key string
					$match_this_idx = (int)$key[6];
					if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: match_this_idx grabbed value: ['.$match_this_idx.']<br>'; }
					$match_data = $GLOBALS['HTTP_POST_VARS'][$key];
					// is this row even being used?
					if ((isset($match_data['andor']))
					&& ($match_data['andor'] == 'ignore_me'))
					{
						if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: SKIP this row, $match_data[andor]: ['.$match_data['andor'].']<br>'; }
					}
					else
					{
						$this_filter['matches'][$match_this_idx] = $match_data;
						if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $this_filter[matches]['.$match_this_idx.'] = ['.serialize($this_filter['matches'][$match_this_idx]).']<br>'; }
					}
				}
				elseif (strstr($key, 'action_'))
				{
					// now we grab the index value from the key string
					$action_this_idx = (int)$key[7];
					if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: action_this_idx grabbed value: ['.$action_this_idx.']<br>'; }
					$action_data = $GLOBALS['HTTP_POST_VARS'][$key];
					if ((isset($action_data['judgement']))
					&& ($action_data['judgement'] == 'ignore_me'))
					{
						if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: SKIP this row, $action_data[judgement]: ['.$match_data['andor'].']<br>'; }
					}
					else
					{
						$this_filter['actions'][$action_this_idx] = $action_data;
						if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: $this_filter[actions][$action_this_idx]: ['.serialize($this_filter['actions'][$action_this_idx]).']<br>'; }
					}
				}
			}
			if ($this->debug_set_prefs > 2) { echo 'bofilters.process_submitted_data: $this_filter[] dump <strong><pre>'; print_r($this_filter); echo "</pre></strong>\r\n"; }
			
			// SAVE TO PREFS DATABASE
			// we called begin_request in the constructor, so we know the prefs object exists
			// filters are based at [filters][X] where X is the filter_num, based on the [email] top level array tree
			// first we delete any existing data at the desired prefs location
			$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']';
			if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
			$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
			// now add this filter piece by piece
			// we can only set a non-array value, but we can use array string for the base
			// but we can grab structures
			
			// $this_filter['filter_num']	integer	use this as the array key based on [filters]
			// $this_filter['filtername']	string (will require htmlslecialchars_encode and decode
			$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["filtername"]';
			if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['filtername'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
			$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['filtername']);

			// $this_filter['source_accounts']	array
			// $this_filter['source_accounts'][X]	array
			// $this_filter['source_accounts'][X]['folder']	string
			// $this_filter['source_accounts'][X]['acctnum']	integer
			for ($i=0; $i < count($this_filter['source_accounts']); $i++)
			{
				// folder
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["source_accounts"]['.$i.']["folder"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['source_accounts'][$i]['folder'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['source_accounts'][$i]['folder']);
				// acctnum
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["source_accounts"]['.$i.']["acctnum"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['source_accounts'][$i]['acctnum'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['source_accounts'][$i]['acctnum']);
			}
			
			// $this_filter['matches']	Array
			// $this_filter['matches'][X]	Array
			// $this_filter['matches'][X]['andor']	UNSET for $this_filter['matches'][0], SET for all the rest : and | or | ignore_me
			// $this_filter['matches'][X]['examine']		known_string : IMAP search keys
			// $this_filter['matches'][X]['comparator']	known_string : contains | notcontains
			// $this_filter['matches'][X]['matchthis']	user_string
			for ($i=0; $i < count($this_filter['matches']); $i++)
			{
				// andor
				if (isset($this_filter['matches'][$i]['andor']))
				{
					$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["matches"]['.$i.']["andor"]';
					if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['matches'][$i]['andor'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
					$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['matches'][$i]['andor']);
				}
				// examine
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["matches"]['.$i.']["examine"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['matches'][$i]['examine'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['matches'][$i]['examine']);
				// comparator
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["matches"]['.$i.']["comparator"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['matches'][$i]['comparator'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['matches'][$i]['comparator']);
				// matchthis
				// user_string, may need htmlslecialchars_encode decode and/or the user may forget to tnter data here
				if ((!isset($this_filter['matches'][$i]['matchthis']))
				|| (trim($this_filter['matches'][$i]['matchthis']) == ''))
				{
					$this_filter['matches'][$i]['matchthis'] = 'user_string_not_filled_by_user';
				}
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["matches"]['.$i.']["matchthis"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['matches'][$i]['matchthis'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['matches'][$i]['matchthis']);
			}
			
			// $this_filter['actions']	Array
			// $this_filter['actions'][X]		Array
			// $this_filter['actions'][X]['judgement']	known_string
			// $this_filter['actions'][X]['folder']		string contains URI style data ex. "&folder=INBOX.Trash&acctnum=0"
			// $this_filter['actions'][X]['actiontext']	user_string
			// $this_filter['actions'][X]['stop_filtering']	UNSET | SET string "True"
			for ($i=0; $i < count($this_filter['actions']); $i++)
			{
				// judgement
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["actions"]['.$i.']["judgement"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['actions'][$i]['judgement'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['actions'][$i]['judgement']);
				// folder
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["actions"]['.$i.']["folder"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['actions'][$i]['folder'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['actions'][$i]['folder']);
				// actiontext
				$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["actions"]['.$i.']["actiontext"]';
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['actions'][$i]['actiontext'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['actions'][$i]['actiontext']);
				// stop_filtering
				if (isset($this_filter['actions'][$i]['stop_filtering']))
				{
					$pref_struct_str = '["filters"]['.$this_filter['filter_num'].']["actions"]['.$i.']["stop_filtering"]';
					if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: using preferences->add_struct("email", $pref_struct_str, '.$this_filter['actions'][$i]['stop_filtering'].') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
					$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $this_filter['actions'][$i]['stop_filtering']);
				}
			}
			// SORT THAT ARRAY by key, so the integer array heys go from lowest to hightest
			ksort($GLOBALS['phpgw']->preferences->data['email']['filters']);
			if ($this->debug_set_prefs > 2) { echo 'bofilters.process_submitted_data: direct pre-save $GLOBALS[phpgw]->preferences->data[email][filters] DUMP:<pre>'; print_r($GLOBALS['phpgw']->preferences->data['email']['filters']); echo '</pre>'; }
			
			// DONE processing prefs, SAVE to the Repository
			if ($this->debug_set_prefs > 1) 
			{
				echo 'bofilters.process_submitted_data: *debug* at ['.$this->debug_set_prefs.'] so skipping save_repository<br>';
			}
			else
			{
				if ($this->debug_set_prefs > 1) { echo 'bofilters.process_submitted_data: SAVING REPOSITORY<br>'; }
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			// end the email session
			$GLOBALS['phpgw']->msg->end_request();
			
			// redirect user back to filters list page
			$take_me_to_url = $GLOBALS['phpgw']->link(
										'/index.php',
										'menuaction=email.uifilters.filters_list');
			
			if ($this->debug_set_prefs > 0) { echo 'bofilters.process_submitted_data: almost LEAVING, about to issue a redirect to:<br>'.$take_me_to_url.'<br>'; }
			if ($this->debug_set_prefs > 1) 
			{
				echo 'bofilters.process_submitted_data: LEAVING, *debug* at ['.$this->debug_set_prefs.'] so skipping Header redirection to: ['.$take_me_to_url.']<br>';
			}
			else
			{
				if ($this->debug_set_prefs > 0) { echo 'bofilters.process_submitted_data: LEAVING with redirect to: <br>'.$take_me_to_url.'<br>'; }
				Header('Location: ' . $take_me_to_url);
			}
		}
		
		
		function read_filter_data_from_prefs()
		{
			$this->all_filters = array();
			// read sublevel data from prefs
			// since we know the constructor called begin_request, we know we can get that data here:
			if ((isset($GLOBALS['phpgw']->msg->unprocessed_prefs['email']['filters']))
			&& (is_array($GLOBALS['phpgw']->msg->unprocessed_prefs['email']['filters']))
			&& (count($GLOBALS['phpgw']->msg->unprocessed_prefs['email']['filters']) > 0)
			&& (isset($GLOBALS['phpgw']->msg->unprocessed_prefs['email']['filters'][0]['source_accounts'])))
			{
				$this->all_filters = $GLOBALS['phpgw']->msg->unprocessed_prefs['email']['filters'];
			}
			return $this->all_filters;
		}
		
		
		
		function run_single_filter()
		{
			if ($this->debug > 0) { echo 'bofilters.run_single_filter: ENTERING<br>'; }
			if (count($this->all_filters) == 0)
			{
				$this->read_filter_data_from_prefs();
			}
			$filter_num = $this->obtain_filer_num();
			$filter_exists = $this->filter_exists($filter_num);
			if (!$filter_exists)
			{
				if ($this->debug > 0) { echo 'bofilters.run_single_filter: LEAVING with ERROR, filter data for $filter_num ['.$filter_num.'] does not exist, return False<br>'; }
				return False;
			}
			$this_filter = $this->all_filters[$filter_num];
			if ($this->debug > 2) { echo 'bofilters.run_single_filter: $filter_num ['.$filter_num.'] ; $this_filter DUMP:<pre>'; print_r($this_filter); echo "</pre>\r\n"; }
			
			
			// WE NEED TO DO THIS FOR EVERY SOURCE ACCOUNT
			$all_accounts_result_set = array();
			$msgball_list = array();
			for ($src_acct_loop_num=0; $src_acct_loop_num < count($this_filter['source_accounts']); $src_acct_loop_num++)
			{
				if ($this->debug > 1) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.']<br>'; }
				
				// ACCOUNT TO SEARCH (always filter source is INBOX)
				$fake_fldball = array();
				$fake_fldball['acctnum'] = $this_filter['source_accounts'][$src_acct_loop_num]['acctnum'];
				$fake_fldball['folder'] = $this_filter['source_accounts'][$src_acct_loop_num]['folder'];
				
				// WE NEED TO DO THIS FOR EACH SEARCH ROW
				for ($matches_row=0; $matches_row < count($this_filter['matches']); $matches_row++)
				{
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] l $matches_row ['.$matches_row.']<br>'; }
					
					// IMAP SEARCH STRING  for this row only)
					$search_key_sieve = $this_filter['matches'][$matches_row]['examine'];
					$search_key_imap = $this->examine_imap_search_keys_map[$search_key_sieve];
					$search_for = $this_filter['matches'][$matches_row]['matchthis'];
					$search_str = $search_key_imap.' "'.$search_for.'"';
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: acct loop ['.$src_acct_loop_num.'] ; row loop ['.$matches_row.'] made $search_str ['.$search_str.'] <br>'; }
					
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: will feed phpgw_search this $fake_fldball [<code>'.serialize($fake_fldball).'</code>] <br>'; }
					if ($this->debug > 1) { echo 'bofilters.run_single_filter:  will feed phpgw_search this $search_str ['.$search_str.'] <br>'; }
					
					// NOT CONTAINS requires a manual "NOT"-ing of a positive result
					// so we need the full msglist, then search for "does contain", then Swap out those results from the initial full msglist 
					$comparator = $this_filter['matches'][$matches_row]['comparator'];
					if ($comparator == 'notcontains')
					{
						if ($this->debug > 1) { echo 'bofilters.run_single_filter: $comparator : ['.$comparator.']<br>'; }
						if ((!isset($this->inbox_full_msgball_list[$src_acct_loop_num]))
						|| (count($this->inbox_full_msgball_list[$src_acct_loop_num] == 0)))
						{
							// get FULL msgball list for this INBOX (we always filter INBOXs only)
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: get_msgball_list for later XOR ing for <code>['.serialize($fake_fldball).']</code><br>'; }
							$this->inbox_full_msgball_list[$src_acct_loop_num] = $GLOBALS['phpgw']->msg->get_msgball_list($fake_fldball['acctnum'], $fake_fldball['folder']);
							if ($this->debug > 2) { echo 'bofilters.run_single_filter: $this->inbox_full_msgball_list['.$src_acct_loop_num.'] DUMP:<pre>'; print_r($this->inbox_full_msgball_list[$src_acct_loop_num]); echo "</pre>\r\n"; }
						}
					}
					
					// do the IMAP search
					$initial_result_set = Array();
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: about to call $GLOBALS[phpgw]->msg->phpgw_search($fake_fldball, $search_str)<br>'; }
					$initial_result_set = $GLOBALS['phpgw']->msg->phpgw_search($fake_fldball, $search_str);
					// sanity check on 1 returned hit, is it for real?
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: server_last_error (if any) was: "'.$GLOBALS['phpgw']->msg->phpgw_server_last_error((int)$fake_fldball['acctnum']).'"<br>'."\r\n"; }
				
					if (($initial_result_set == False)
					|| (count($initial_result_set) == 0))
					{
						if ($this->debug > 1) { echo 'bofilters.run_single_filter: no hits or possible search error<br>'."\r\n"; }
						if ($this->debug > 1) { echo 'bofilters.run_single_filter: server_last_error (if any) was: "'.$GLOBALS['phpgw']->msg->phpgw_server_last_error((int)$fake_fldball['acctnum']).'"<br>'."\r\n"; }
						// we leave this->result_set_mlist an an empty array, as it was initialized on class creation
						$this->each_row_result_mball_list[$matches_row] = array();
						// if comparitor is "contains", we leave that an empty array, BUT...
						if ($comparator == 'notcontains')
						{
							// opposite of this search is EVERY message in the $this->inbox_full_msgball_list[$src_acct_loop_num] is a "search hit"
							// remember, for "notcontains" we search for what DOES contain, then remove those hits from $this->inbox_full_msgball_list[$src_acct_loop_num]
							$this->each_row_result_mball_list[$matches_row] = $this->inbox_full_msgball_list[$src_acct_loop_num];
						}
					}
					else
					{
						// we got results!!!
						if ($this->debug > 2) { echo 'bofilters.run_single_filter: $initial_result_set DUMP:<pre>'; print_r($initial_result_set); echo "</pre>\r\n"; }
						// accumulate the results for all accounts
						$this->each_row_result_mball_list[$matches_row] = array();
						if ($comparator == 'contains')
						{
							// these are "psitive" results, they represent actual matches
							// make a msgball list out of the data
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: $comparator ['.$comparator.'] means normal, use these results, make a msgball list out of them <br>'."\r\n"; }
							for ($x=0; $x < count($initial_result_set); $x++)
							{
								$next_pos = count($this->each_row_result_mball_list[$matches_row]);
								// and this has the essential data we'll need to move msgs around
								$this->each_row_result_mball_list[$matches_row][$next_pos]['acctnum'] = $fake_fldball['acctnum'];
								$this->each_row_result_mball_list[$matches_row][$next_pos]['folder'] = $fake_fldball['folder'];
								$this->each_row_result_mball_list[$matches_row][$next_pos]['msgnum'] = (int)$initial_result_set[$x];
								$this->each_row_result_mball_list[$matches_row][$next_pos]['uri'] = 
														  'msgball[acctnum]='.$fake_fldball['acctnum']
														.'&msgball[folder]='.$fake_fldball['folder']
														.'&msgball[msgnum]='.$initial_result_set[$x];
							}
						}
						else
						{
							// comparator "notcontains" means OPPOSITE results
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: $comparator ['.$comparator.'] means we have negative results, to be subtracted from pre-search total folder msg list <br>'."\r\n"; }
							// make a string of the result array
							$remove_me = ' '.implode(' ', $initial_result_set).' ';
							// loop thru the pre-search msg_ball list of all msgs in this INBOX
							// keep only what IS NOT IN the "remove_me" string
							for ($x=0; $x < count($this->inbox_full_msgball_list[$src_acct_loop_num]); $x++)
							{
								$this_inbox_msgball = $this->inbox_full_msgball_list[$src_acct_loop_num][$x];
								if (stristr($remove_me, ' '.$this_inbox_msgball['msgnum'].' ') == False)
								{
									// we may keep this in our result set
									$next_pos = count($this->each_row_result_mball_list[$matches_row]);
									// and this has the essential data we'll need to move msgs around
									$this->each_row_result_mball_list[$matches_row][$next_pos] = $this_inbox_msgball;
								}
								
							}
						}
						
					}
					// code here is the last line in this MATCHES row loop
					// we'll use this later
					$highest_row_number = $matches_row;
					if ($this->debug > 2) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] $this->each_row_result_mball_list[$matches_row] DUMP<pre>'; print_r($this->each_row_result_mball_list[$matches_row]); echo '</pre>'."\r\n"; }
					// we need to AND / OR this row's results to the previous row, if this is now the 1st row, of course
					if ($matches_row > 0)
					{
						$andor = $this_filter['matches'][$matches_row]['andor'];
						if ($andor == 'and')
						{
							// "AND" - only items in this list AND also in the previous list make it to the next round
							if ($this->debug > 1) { echo 'bofilters.run_single_filter:  source_accounts loop ['.$src_acct_loop_num.'] ; $matches_row ['.$matches_row.'] ; $andor ['.$andor.'] means only items in this list AND also in the previous list make it to the next round<br>'."\r\n"; }
							// serialize the current results, walk thru prev results, prepare a new "common_items_array"
							// simple string search for common items, if items are not common to both arrays then
							// they will NOT be added to the "common array"
							//the "common array" is your new result set for the current row as processed as a pair with it's previous row.
							$common_items_array = array();
							$this_row_serialized = serialize($this->each_row_result_mball_list[$matches_row]);
							if ($this->debug > 1) { echo 'bofilters.run_single_filter:  source_accounts loop ['.$src_acct_loop_num.'] ; $matches_row ['.$matches_row.'] ; $andor ['.$andor.'] ; $this_row_serialized : <p>'.$this_row_serialized.'</p>'."\r\n"; }
							// EXAMPLE: look for: 
							//	s:6:"msgnum";i:19
							// loop thru previous row results
							for ($x=0; $x < count($this->each_row_result_mball_list[$matches_row-1]); $x++)
							{
								$existing_msgnum = $this->each_row_result_mball_list[$matches_row-1][$x]['msgnum'];
								if ($this->debug > 1) { echo ' * bofilters.run_single_filter: $existing_msgnum = $this->each_row_result_mball_list[$matches_row-1]['.$x.'][msgnum] = ['.$existing_msgnum.'] <br>'."\r\n"; }
								if (stristr($this_row_serialized, 's:6:"msgnum";i:'.$existing_msgnum.';'))
								{
									// ok, this msgnum is common to both result sets, this is an AND
									$add_me_msgball = $this->each_row_result_mball_list[$matches_row-1][$x];
									$next_pos = count($common_items_array);
									if ($this->debug > 1) { echo ' * bofilters.run_single_filter: adding $add_me_msgball [<code>'.serialize($add_me_msgball).'</code>] to $common_items_array['.$next_pos.']<br>'."\r\n"; }
									$common_items_array[$next_pos] = $add_me_msgball;
								}
								
							}
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] ; $matches_row ['.$matches_row.'] ; $common_items_array for rows ['.$matches_row.'] and ['.($matches_row-1).'] DUMP <pre>'; print_r($common_items_array); echo '</pre>'."\r\n"; }
							
							// $common_items_array[] now holds the processed AND'ed data, make this result set the current row's result set,
							// so our result set logic thus far is what gets AND or OR 'd to the next row
							$this->each_row_result_mball_list[$matches_row] = array();
							$this->each_row_result_mball_list[$matches_row] = $common_items_array;
							if ($this->debug > 2) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] AND-ed FINAL $this->each_row_result_mball_list[$matches_row] DUMP<pre>'; print_r($this->each_row_result_mball_list[$matches_row]); echo '</pre>'."\r\n"; }
						}
						else
						{
							// "OR"
							if ($this->debug > 1) { echo 'bofilters.run_single_filter:  source_accounts loop ['.$src_acct_loop_num.'] ; $matches_row ['.$matches_row.'] ; $andor ['.$andor.'] means only items in this list AND also in the previous list make it to the next round<br>'."\r\n"; }
							// serialize the current results, walk thru prev results, str replace common values with empty string in the serialuzed array
							// then unserialize the array and add whatever still hasa value to the first array.
							// this means you have merged the two arrays except common items were not added again
							// this is your new result set for the current row as processed as a pair with it's previous row.
							$this_row_serialized = serialize($this->each_row_result_mball_list[$matches_row]);
							if ($this->debug > 1) { echo 'bofilters.run_single_filter:  source_accounts loop ['.$src_acct_loop_num.'] ; $matches_row ['.$matches_row.'] ; $andor ['.$andor.'] ; $this_row_serialized : <p>'.$this_row_serialized.'</p>'."\r\n"; }
							// EXAMPLE: look for: 
							//	s:6:"msgnum";i:19;
							// REPLACE with 
							//	s:6:"msgnum";s:1:" ";
							// loop thru previous row results
							for ($x=0; $x < count($this->each_row_result_mball_list[$matches_row-1]); $x++)
							{
								$existing_msgnum = $this->each_row_result_mball_list[$matches_row-1][$x]['msgnum'];
								if ($this->debug > 1) { echo ' * bofilters.run_single_filter: $this->each_row_result_mball_list[$matches_row-1]['.$x.'][msgnum] : $existing_msgnum ['.$existing_msgnum.'] <br>'."\r\n"; }
								if (stristr($this_row_serialized, 's:6:"msgnum";i:'.$existing_msgnum.';'))
								{
									if ($this->debug > 1) { echo ' * bofilters.run_single_filter: DUPLICATE $existing_msgnum ['.$existing_msgnum.'] <br>'."\r\n"; }
									$modified_serialized = str_replace('s:6:"msgnum";i:'.(string)$existing_msgnum.';', 's:6:"msgnum";s:1:" ";', $this_row_serialized);
									$this_row_serialized = $modified_serialized;
								}
							}
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: POST replace $this_row_serialized  <p>'.$this_row_serialized.'</p> <br>'."\r\n"; }
							$this_row_unserialized = unserialize($this_row_serialized);
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: POST replace $this_row_UNserialized array DUMP <pre>'; print_r($this_row_unserialized); echo '</pre>'."\r\n"; }
							// loop thru $this_row_unserialized array, anything that still has a "msgnum" gets added to the previous row's results
							for ($x=0; $x < count($this_row_unserialized); $x++)
							{
								// does this msgball still have a msgnum , i.e. we did not blank it out above here
								if (trim($this_row_unserialized[$x]['msgnum']) != '')
								{
									// ok, this gets added to the previous row's results
									$add_me_msgball = $this_row_unserialized[$x];
									$next_pos = count($this->each_row_result_mball_list[$matches_row-1]);
									if ($this->debug > 1) { echo ' * bofilters.run_single_filter: adding $add_me_msgball [<code>'.serialize($add_me_msgball).'</code>] to $this->each_row_result_mball_list[$matches_row-1]['.$next_pos.']<br>'."\r\n"; }
									$this->each_row_result_mball_list[$matches_row-1][$next_pos] = $add_me_msgball;
								}
							}
							// previous row now holds the processed OR'ed data, make this result set the current row's result set,
							// so our result set logic thus far is what gets AND or OR 'd to the next row
							$this->each_row_result_mball_list[$matches_row] = array();
							$this->each_row_result_mball_list[$matches_row] = $this->each_row_result_mball_list[$matches_row-1];
							if ($this->debug > 2) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] AND-ed FINAL $this->each_row_result_mball_list[$matches_row] DUMP<pre>'; print_r($this->each_row_result_mball_list[$matches_row]); echo '</pre>'."\r\n"; }
						}
					}
					
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] here <b> -- END THIS MATCHES ROW -- </b><br>'."\r\n"; }
					// here END THIS MATCHES ROW
				}
				// code here is the last line in this SRC ACCT loop iteration
				// we recoreded the final row in $highest_row_number, this row's array has the sum of all our logic
				// add the last row's sesult set to "each_acct_final_mball_list"
				$next_pos = count($this->each_acct_final_mball_list);
				$this->each_acct_final_mball_list[$next_pos] = $this->each_row_result_mball_list[$highest_row_number];
				if ($this->debug > 2) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] ; $this->each_acct_final_mball_list['.$next_pos.'] iteration DUMP<pre>'; print_r($this->each_acct_final_mball_list[$next_pos]); echo '</pre>'."\r\n"; }
				if ($this->debug > 1) { echo 'bofilters.run_single_filter: source_accounts loop ['.$src_acct_loop_num.'] row loop ['.$matches_row.'] here <b> -- END THIS SOURCE ACCOUNT LOOP -- </b><br>'."\r\n"; }
				// here END THIS SOURCE ACCOUNT
			}
			
			// ADD ALL ACCOUNTS RESULTS SETS TOGETHER
			$all_accounts_result_set = array();
			for ($x=0; $x < count($this->each_acct_final_mball_list); $x++)
			{
				for ($y=0; $y < count($this->each_acct_final_mball_list[$x]); $y++)
				{	
					$this_msgball = $this->each_acct_final_mball_list[$x][$y];
					$next_pos = count($all_accounts_result_set);
					$all_accounts_result_set[$next_pos] = $this_msgball;
				}
			}
			
			// report
			if ((count($all_accounts_result_set) > 0)
			&& (isset($all_accounts_result_set[0]))
			&& ((string)$all_accounts_result_set[0]['folder'] != '')
			&& ($this->just_testing()))
			{
				if ($this->debug > 1) { echo 'bofilters.run_single_filter: Filter Test Run<br>'; }
				if ($this->debug > 1) { echo 'bofilters.run_single_filter: number of matches $all_accounts_result_set = ' .count($all_accounts_result_set).'<br>'."\r\n"; }
				// make a "fake" folder_info array to make things simple for get_msg_list_display
				$this->fake_folder_info['is_imap'] = True;
				$this->fake_folder_info['folder_checked'] = 'INBOX';
				$this->fake_folder_info['alert_string'] = 'you have search results';
				$this->fake_folder_info['number_new'] = count($all_accounts_result_set);
				$this->fake_folder_info['number_all'] = count($all_accounts_result_set);
				if ($this->debug > 2) { echo 'bofilters.run_single_filter:  $all_accounts_result_set DUMP:<pre>'; print_r($all_accounts_result_set); echo "</pre>\r\n"; }
				// retrieve user displayable data for each message in the result set
				$this->result_set_mlist = $GLOBALS['phpgw']->msg->get_msg_list_display($this->fake_folder_info,$all_accounts_result_set);
				$html_list = $this->make_mlist_box();
				echo '<html><table>'.$html_list.'</table></html>';
			}
			elseif ((count($all_accounts_result_set) > 0)
			&& (isset($all_accounts_result_set[0]))
			&& ((string)$all_accounts_result_set[0]['folder'] != ''))
			{				
				// NOT A TEST - APPLY THE ACTION(S)
				if ($this->debug > 1) { echo 'bofilters.run_single_filter: NOT a Test, *Apply* the Action(s) ; $this_filter[actions][0][judgement] : ['.$this_filter['actions'][0]['judgement'].']<br>'; }
				if ($this_filter['actions'][0]['judgement'] == 'fileinto')
				{
					parse_str($this_filter['actions'][0]['folder'], $target_folder);
					$target_folder['folder'] = urlencode($target_folder['folder']);
					//if ($this->debug > 2) { echo 'bofilters.run_single_filter: $target_folder DUMP:<pre>'; print_r($target_folder); echo "</pre>\r\n"; }
					$to_fldball = array();
					$to_fldball['folder'] = $target_folder['folder'];
					$to_fldball['acctnum'] = (int)$target_folder['acctnum'];
					if ($this->debug > 2) { echo 'bofilters.run_single_filter: $to_fldball DUMP:<pre>'; print_r($to_fldball); echo "</pre>\r\n"; }
					$tm = count($all_accounts_result_set);
					for ($i = 0; $i < count($all_accounts_result_set); $i++)
					{
						if ($this->debug > 2) { echo 'bofilters.run_single_filter: in mail move loop ['.(string)($i+1).'] of ['.$tm.']<br>'; }
						$mov_msgball = $all_accounts_result_set[$i];
						if ($this->debug > 1) { echo 'bofilters.run_single_filter: pre-move info: $mov_msgball [<code>'.serialize($mov_msgball).'</code>]<br>'; }
						
						//echo 'EXIT NOT READY TO APPLY THE FILTER YET<br>';
						$good_to_go = $GLOBALS['phpgw']->msg->industrial_interacct_mail_move($mov_msgball, $to_fldball);
						
						if (!$good_to_go)
						{
							// ERROR
							if ($this->debug > 1) { echo 'bofilters.run_single_filter: ERROR: industrial_interacct_mail_move returns FALSE<br>'; }
							break;
						}
					}
				}
				else
				{
					// not yet coded action
					if ($this->debug > 1) { echo 'bofilters.run_single_filter: action not yet coded: $this_filter[actions][0][judgement] : ['.$this_filter['actions'][0]['judgement'].']<br>'; }
				}
			}
			else
			{
				// NO MATCHES
			}
			
			
			if ($this->debug > 1) { echo 'bofilters.run_single_filter: calling end_request<br>'; }
			$GLOBALS['phpgw']->msg->end_request();
			if ($this->debug > 0) { echo 'bofilters.run_single_filter: LEAVING<br>'; }
			$take_me_to_url = $GLOBALS['phpgw']->link(
										'/index.php',
										'menuaction=email.uifilters.filters_list');
										
			$take_me_to_href = '<a href="'.$take_me_to_url.'"> Go Back </a>';
			//Header('Location: ' . $take_me_to_url);
			echo '<p>&nbsp;</p><br><p>'.$take_me_to_href.'</p>';
		}
		
		
		
		function make_imap_search_str($feed_filter)
		{
			if ($this->debug > 0) { echo 'bofilters.make_imap_search_str: ENTERING<br>'; }
			if ($this->debug > 2) { echo 'bofilters.make_imap_search_str: $feed_filter DUMP:<pre>'; print_r($feed_filter); echo "</pre>\r\n"; }
			/*
			RFC2060:
			search  =  "SEARCH" [SP "CHARSET" SP astring] 1*(SP search-key)
			search-key = 
				"ALL" / "ANSWERED" / "BCC" SP astring /
				"BEFORE" SP date / "BODY" SP astring /
				"CC" SP astring / "DELETED" / "FLAGGED" /
				"FROM" SP astring / "KEYWORD" SP flag-keyword / "NEW" /
				"OLD" / "ON" SP date / "RECENT" / "SEEN" /
				"SINCE" SP date / "SUBJECT" SP astring /
				"TEXT" SP astring / "TO" SP astring /
				"UNANSWERED" / "UNDELETED" / "UNFLAGGED" /
				"UNKEYWORD" SP flag-keyword / "UNSEEN" /
			; Above this line were in [IMAP2]
				"DRAFT" / "HEADER" SP header-fld-name SP astring /
				"LARGER" SP number / "NOT" SP search-key /
				"OR" SP search-key SP search-key /
				"SENTBEFORE" SP date / "SENTON" SP date /
				"SENTSINCE" SP date / "SMALLER" SP number /
				"UID" SP set / "UNDRAFT" / set /
				"(" search-key *(SP search-key) ")"
			*/
			/*
			Examples of how to construct IMAP4rev1 search strings
			"PERFECT WORLD EXAMPLES" meaning the following
			examples apply ONLY to servers implementing IMAP4rev1 Search functionality
			As of Jan 25, 2002, this is somewhat rare.
			From a google search in a "turnpike" newsgroup:
			
			IMAP's [AND] OR and NOT are all prefix operators, i.e. there is no 
			precedence or hierarchy (I put the [AND] in brackets as it is implied, 
			there is no AND keyword).
			
			[AND] and OR operate on the next two search-keys.
			NOT operates on the next search-key.
			
			Parentheses can be used to group an expression of search-keys into a 
			single search-key.
			
			Some examples translated into infix notation with "not" "and" "or" as 
			infix operators, k1, k2 .. are search-keys.  These infix operators are 
			purely for explanation, they are not part of IMAP.			
			
			k1 k2 k3                means (k1 and k2) and k3
			OR k1 k2 k3             means (k1 or k2) and k3
			OR (OR k1 k2) k3        means (k1 or k2) or k3
			NOT k1 k2               means (not k1) and k2
			NOT OR k1 k2            means not (k1 or k2)
			OR NOT k1 k2            means (not k1) or k2
			NOT k1 NOT k2           means (not k1) and (not k2)
			*/
			
			if ($this->debug > 2) { echo 'bofilters: make_imap_search_str: mappings are:<pre>'; print_r($this->examine_imap_search_keys_map); echo "</pre>\r\n"; }
			
			// do we have one search or two, or more
			$num_search_criteria = count($feed_filter['matches']);
			if ($this->debug > 1) { echo 'bofilters.make_imap_search_str: $num_search_criteria: ['.$num_search_criteria.']<br>'; }
			// 1st search criteria
			// convert form submitted data into usable IMAP search keys
			$search_key_sieve = $feed_filter['matches'][0]['examine'];
			$search_key_imap = $this->examine_imap_search_keys_map[$search_key_sieve];
			// what to learch for
			$search_for = $feed_filter['matches'][0]['matchthis'];
			// does or does not contain
			$comparator = $feed_filter['matches'][0]['comparator'];
			$search_str_1_criteria = $search_key_imap.' "'.$search_for.'"';
			// DOES NOT CONTAIN - "NOT" is a IMAP4rev1 only key, UWASH doesn;t support it.
			
			// DO ONE LINE AT A TIME FOR NOW
			$one_line_only = True;
			if ($one_line_only)
			{
				// skip this
			}
			else
			{
				// 2nd Line 
				if ($num_search_criteria == 1)
				{
					// no seconnd line, our string is complete
					$final_search_str = $search_str_1_criteria;
				}
				else
				{
					// convert form submitted data into usable IMAP search keys
					$search_key_sieve = $feed_filter['matches'][1]['examine'];
					$search_key_imap = $this->examine_imap_search_keys_map[$search_key_sieve];
					// what to learch for
					$search_for = $feed_filter['matches'][1]['matchthis'];
					// does or does not contain
					$comparator = $feed_filter['matches'][1]['comparator'];
					// DOES NOT CONTAIN - BROKEN - FIXME
					$search_str_2_criteria = $search_key_imap.' "'.$search_for.'"';
					// preliminary  compound search string
					$final_search_str = $search_str_1_criteria .' '.$search_str_2_criteria;
					// final syntax of this limited 2 line search
					$andor = $feed_filter['matches'][1]['andor'];
					// ANDOR - BROKEN - FIXME
				}
			}
			/*
			$conv_error = '';
			if ((!isset($look_here_sieve))
			|| (trim($look_here_sieve) == '')
			|| ($look_here_imap == ''))
			{
				$conv_error = 'invalid or no examine data';
				if ($this->debug > 1) { echo '<b> *** error</b>: bofilters.make_imap_search_str: error: '.$conv_error."<br> \r\n"; }
				return '';
			}
			elseif ((!isset($for_this))
			|| (trim($for_this) == ''))
			{
				$conv_error = 'invalid or no search string data';
				if ($this->debug > 1) { echo '<b> *** error</b>: bofilters.make_imap_search_str: error: '.$conv_error."<br> \r\n"; }
				return '';
			}
			$imap_str = $look_here_imap.' "'.$for_this.'"';
			*/
			if ($this->debug > 0) { echo 'bofilters.make_imap_search_str: LEAVING, $one_line_only: ['.serialize($one_line_only).'] returning search string: <code>'.$final_search_str.'</code><br>'."\r\n"; }
			return $final_search_str;
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
				// preserve the folder we searched (raw posted source_account was never preped in here, so it's ok to send out as is)
				$mlist_hidden_vars .= '<input type="hidden" name="folder" value="'.$this->filters[0]['source_account'].'">'."\r\n";
				// make the first prev next last arrows
				$this->template->set_var('mlist_submit_form_action', $GLOBALS['phpgw']->link('/index.php','menuaction=email.uiindex.mlist'));
				$this->template->set_var('mlist_hidden_vars',$mlist_hidden_vars);
				$this->template->parse('V_mlist_submit_form','B_mlist_submit_form');
				
				$this->submit_mlist_to_class_form = $this->template->get_var('V_mlist_submit_form');
				
				return $this->finished_mlist;
			}
			
		}
		
		/* // DEPRECIATED
		function do_imap_search()
		{
			$imap_search_str = $this->make_imap_search_str();
			if (!$imap_search_str)
			{
				if ($this->debug > 0) { echo '<b> *** error</b>: bofilters: do_imap_search: make_imap_search_str returned empty<br>'."\r\n"; }
				return array();
			}
			
			//$attempt_reuse = True;
			$attempt_reuse = False;
			if (!is_object($GLOBALS['phpgw']->msg))
			{
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			if ((is_object($GLOBALS['phpgw']->msg))
			&& ($attempt_reuse == True))
			{
				// no not create, we will reuse existing
				echo 'bofilters: do_imap_search: reusing existing mail_msg object'.'<br>';
				// we need to feed the existing object some params begin_request uses to re-fill the msg->args[] data
				$reuse_feed_args = $GLOBALS['phpgw']->msg->get_all_args();
				$args_array = Array();
				$args_array = $reuse_feed_args;
				if ((isset($this->filters[0]['source_account']))
				&& ($this->filters[0]['source_account'] != ''))
				{
					if ($this->debug > 0) { echo 'bofilters: do_imap_search: this->filters[0][source_account] = ' .$this->filters[0]['source_account'].'<br>'."\r\n"; }
					$args_array['folder'] = $this->filters[0]['source_account'];
				}
				else
				{
					$args_array['folder'] = 'INBOX';
				}
				// add this to keep the error checking code (below) happy
				$args_array['do_login'] = True;
			}
			else
			{
				if ($this->debug_index_data == True) { echo 'bofilters: do_imap_search: creating new login email.mail_msg, cannot or not trying to reusing existing'.'<br>'; }
				// new login 
				// (1) folder (if specified) - can be left empty or unset, mail_msg will then assume INBOX
				$args_array = Array();
				if ((isset($this->filters[0]['source_account']))
				&& ($this->filters[0]['source_account'] != ''))
				{
					if ($this->debug > 0) { echo 'bofilters: do_imap_search: this->filters[0][source_account] = ' .$this->filters[0]['source_account'].'<br>'."\r\n"; }
					$args_array['folder'] = $this->filters[0]['source_account'];
				}
				else
				{
					$args_array['folder'] = 'INBOX';
				}
				// (2) should we log in
				$args_array['do_login'] = True;
			}
			//$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			//$args_array = Array();
			//if ((isset($this->filters[0]['source_account']))
			//&& ($this->filters[0]['source_account'] != ''))
			//{
			//	if ($this->debug > 0) { echo 'bofilters: do_imap_search: this->filters[0][source_account] = ' .$this->filters[0]['source_account'].'<br>'."\r\n"; }
			//	$args_array['folder'] = $this->filters[0]['source_account'];
			//}
			//else
			//{
			//	$args_array['folder'] = 'INBOX';
			//}
			//$args_array['do_login'] = True;
			
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			
			$initial_result_set = Array();
			$initial_result_set = $GLOBALS['phpgw']->msg->phpgw_search($imap_search_str);
			// sanity check on 1 returned hit, is it for real?
			if (($initial_result_set == False)
			|| (count($initial_result_set) == 0))
			{
				echo 'bofilters: do_imap_search: no hits or possible search error<br>'."\r\n";
				echo 'bofilters: do_imap_search: server_last_error (if any) was: "'.$GLOBALS['phpgw']->msg->phpgw_server_last_error().'"'."\r\n";
				// we leave this->result_set_mlist an an empty array, as it was initialized on class creation
			}
			else
			{
				$this->result_set = $initial_result_set;
				if ($this->debug > 0) { echo 'bofilters: do_imap_search: number of matches = ' .count($this->result_set).'<br>'."\r\n"; }
				// make a "fake" folder_info array to make things simple for get_msg_list_display
				$this->fake_folder_info['is_imap'] = True;
				$this->fake_folder_info['folder_checked'] = $GLOBALS['phpgw']->msg->get_arg_value('folder');
				$this->fake_folder_info['alert_string'] = 'you have search results';
				$this->fake_folder_info['number_new'] = count($this->result_set);
				$this->fake_folder_info['number_all'] = count($this->result_set);
				// retrieve user displayable data for each message in the result set
				$this->result_set_mlist = $GLOBALS['phpgw']->msg->get_msg_list_display($this->fake_folder_info,$this->result_set);
			}
			$GLOBALS['phpgw']->msg->end_request();
			//echo 'bofilters: do_imap_search: returned:<br>'; var_dump($this->result_set); echo "<br>\r\n";
		}
		*/
		
	
	// end of class
	}
?>
