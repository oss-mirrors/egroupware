<?php
	/**************************************************************************\
	* AngleMail - E-Mail Message Data Storage for Caching Functions		*
	* http://www.anglemail.org									*
	*/
	/**************************************************************************\
	* AngleMail - E-Mail Message Data Storage for Caching Functions			*
	* This file written by Angelo Puglisi (Angles) <angles@aminvestments.com>	*
	* Handles data storage functions for email caching of data			*
	* Copyright (C) 2001, 2002, 2003 Angelo Tony Puglisi (Angles)				*
	* ------------------------------------------------------------------------ 		*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by 	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.								*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of	*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation, 		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	// =====  INTERFACE FUNCTIONS AND/OR  WRAPPER FUNCTIONS =====
	
	/*!
	@class so_mail_msg
	@abstract E-Mail Message Data Storage for Data Caching
	@discussion ?
	@author Angles
	*/
	class so_mail_msg
	{
	
		/*!
		@cabability appsession TEMPORARY DATA CACHING - data we generate
		@abstract Caching via the the api appsession cache occurs in 2 basic forms. 
		(ONE) information we are responsible for generating in this mail_msg class, and 
		(TWO) data that the mail server sends us. This discussion is about ONE.
		@discussion Data this class must generate includes preferences, mail server callstring, namespace, delimiter, 
		(not a complete list). The first time a data element is generated, for example ->get_arg_value("mailsvr_namespace"), 
		which is needed before we can login to the mailserver, the private function "get_mailsvr_namespace" determines this 
		string, then places that value in what I refer to as the "Level1 cache" (L1 cache) which is simply a class variable that is filled 
		with that value. Additionally, that data is saved in the appsession cache. The L1 cache only exists as long as the script 
		is run, usually one pageview. The appsession cache exists as long as the user is logged in. When the user requests 
		another page view, private function ""get_mailsvr_namespace" checks (always) the L1 cache for this value, if this 
		is the first time this function has been called for this pageview, that L1 cache does not yet exist. Then the functions 
		checks the appsession cache for this value. In this example, it will find it there, put that value in the L1 cache, then 
		return the value and exit. For the rest of the pageview, any call to this function will return the L1 cache value, no 
		logic in that function is actually encountered. 
		*/
		
		/*!
		@cabability appsession TEMPORARY DATA CACHING - data from the mailserver
		@abstract Caching via the the api appsession cache occurs in 2 basic forms. 
		(ONE) information we are responsible for generating in this mail_msg class, and 
		(TWO) data that the mail server sends us. This discussion is about TWO
		@discussion CACHE FORM TWO is anything the mail server sends us that we want to cache. The IMAP rfc requires we cache as much 
		as we can so we do not ask the server for the same information unnecessarily. Take function "get_msgball_list" as an example. 
		This is a list of messages in a folder, the list we save is in the form of typed array "msgball" which means the list included 
		message number, full folder name, and account number. 
		BEGIN DIGRESSION Why is all this data cached? Traditionally, a mail message has a 
		"ball and chain" association with a particular mail server, a particular account on that mail server, and a particular folder 
		within that account. This is the traditional way to think of email. HOWEVER, this email app desires to completely seperate 
		an individual mail message from any of those traditional associations. So what does a "msgball" list allow us to so? This way 
		we can move messages between accounts without caring where that account is located, what type of server its on, or what  
		folder its in. We can have exotic search results where the "msgball" list contains references to messages from different 
		accounts on different servers of different types in different folders therein. Because every peice of data about the message 
		we need is stored in the typed array "msgball", we have complete freedom of movement and manipulation of those 
		messages.  END DIGRESSION. 
		So the function "get_msgball_list", the first time it is called for any particular folder, asks the mail server for a list of 
		message UIDs (Unique Message ID good for as long as that message is in that folder), and assembles the "msgball" list 
		by adding the associated account number and folder name to that message number. This list is then stored in the 
		appsession cache. Being in the appsession cache means this data will persist for as long as the user is logged in. 
		The data becomes STALE if 1. new mail arrives in the folder, or 2. messages are moved out of the folder. 
		So the next pageview the user requests for that folder calls "get_msgball_list" which attempts to find the 
		data stored in the appsession cache. If it is found cached there, the data is checked for VALIDITY during 
		function "read_session_cache_item" which calls function "get_folder_status_info" and checks for 2 things, 
		1. that this "msgball" is in fact referring to the same server, account, and folder as the newly requested data, 
		and (CRUCIAL ITEM) 2. checks for staleness using the data returned from "get_folder_status_info", especially 
		"uidnext", "uidvalidity", and "number_all" to determine if the data is stale or not. MORE ON THIS LATER. If the 
		data is not stale and correctly refers to the right account, the "msgball" list stored in the appsession cache is used 
		as the return value of "get_msgball_list" and THE SERVER IS NOT ASKED FOR THE MESSAGE LIST 
		UNNECESSARILY. This allows for folders with thousands of messages to reduce client - server xfers dramatically. 
		HOWEVER - this is an area where additional speed could be achieved by NOT VALIDIATING EVERY TIME, meaning 
		we could set X minutes where the "msgball" list is considered NOT STALE. This eliminates a server login just to 
		get validity information via "get_folder_status_info". HOWEVER, even though we have the message list for that 
		folder in cache, we still must request the envelope info (from, to, subject, date) in order to show the index page. 
		THIS DATA COULD BE CACHED TOO. Conclusion - you have seen how a massage list is cached, validated, and 
		reused. Additionally, we have discussed ways to gain further speed with X minutes of assumed "freshness" and 
		by caching envelope data. *UPDATE* AngleMail has begun to cache message structure and message envelope 
		data, but this is under development. THIS DOC NEEDS UPDATING. CACHING HAS EXPANDED 
		DRAMITICALLY. 
		*/

		/*!
		@function so_save_session_cache_item
		@abstract SO Data Access only, logic is in main class mail_msg.
		@access private
		@author Angles
		*/
		function so_save_session_cache_item($data_name='misc',$data,$acctnum='',$extra_keys='')
		{
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_save_session_cache_item('.__LINE__.'): ENTERED, $this->PARENT->session_cache_enabled='.serialize($GLOBALS['phpgw']->msg->session_cache_enabled).', $data_name: ['.$data_name.'], $acctnum (optional): ['.$acctnum.'], $extra_keys: ['.$extra_keys.']<br>'); } 
			
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_save_session_cache_item('.__LINE__.'): LEAVING <br>'); }
		}
		
		
		/*!
		@function so_read_session_cache_item
		@abstract SO Data Access functions only, actual logic is in main class mail_msg 
		@access private
		@author Angles
		*/
		function so_read_session_cache_item($data_name='misc', $acctnum='', $ex_folder='', $ex_msgnum='')
		{
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_read_session_cache_item('.__LINE__.'): ENTERED, $data_name: ['.$data_name.']; optional: $acctnum: ['.$acctnum.'], $ex_folder: ['.$ex_folder.'], $ex_msgnum: ['.$ex_msgnum.'] '.'<br>'); } 
			
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_read_session_cache_item('.__LINE__.'): LEAVING <br>'); } 
		}
		
		/*!
		@function so_expire_session_cache_item
		@abstract SO Data Access functions only, actual logic is in main class mail_msg 
		@discussion ?
		@author Angles
		@access private
		*/
		function so_expire_session_cache_item($data_name='misc',$acctnum='', $ex_folder='', $ex_msgnum='')
		{		
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_expire_session_cache_item('.__LINE__.'): ENTERED, $this->PARENT->session_cache_enabled='.serialize($GLOBALS['phpgw']->msg->session_cache_enabled).', $data_name: ['.$data_name.'], $acctnum (optional): ['.$acctnum.'], $extra_keys: ['.$extra_keys.']<br>'); }
			
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_expire_session_cache_item('.__LINE__.'): LEAVING <br>'); } 
		}


		/*!
		@function prep_db_session_compat
		@abstract for DB sessions_db storage, backward compatibility where php4 sessions are not in use. 
		@discussion Imitates the session array that php4 sessions create. ALSO used when caching to 
		the anglemail DB table whether php4 sessions are in use or not. 
		Many of the caching functions operate on that session data array that is created 
		with php4 sessions in use, and evolved to using looping in that array to speed 
		certain things. As caching evolved to also be able to cache to the sessions_db 
		table or the anglemail table, those existing functions kept that array centric approach, and this 
		function is used to create an imitation array like the one they expect. The difference is that 
		the data is not actually stored in php4 session, but either in sessions_db, or in the 
		anglemail table if it exists. Practially, once data is retrieved from the database, it 
		is put in this array, and kept there so if we need it again it is already in memory. 
		This is similar to but better than the using php4 sessions as a caching store because 
		php4 sessions load ALL data into that session array for every script run, whether you 
		need it or not. Using this hybrid method, we still have that array (imitated here) 
		to work with but only needed data is put into it. Also, this is part of the reason 
		anglemail can use any of 3 different storage methods for the cache, php4 sessions, 
		sessions_db, or the dedicated anglemail table, because the functions using the 
		data have that similar array approach. Note that when php4 sessions are in use 
		AND the anglemail table is used for caching, this function preforms the crucial 
		action of making the imitation session array while keeping the actual cached 
		data AWAY from the real php4 session array. Anything in that php4 session 
		array will automatically be stored as session data, but using the table means 
		we do not want that php4 session storage, only a familiar looking array to work with. 
		This is done by creating the imitation array and having $GLOBALS['phpgw']->msg->ref_SESSION be a 
		pointer to it, instead of having it point to the real GLOBALS[session] array tree. 
		@author Angles 
		*/
		function prep_db_session_compat($called_by='not_specified')
		{
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: prep_db_session_compat('.__LINE__.'): ENTERING, $called_by ['.$called_by.']<br>'); } 
			// UNDER DEVELOPMEMT - backwards_compat with sessions_db where php4 sessions are not being used
			if (($GLOBALS['phpgw_info']['server']['sessions_type'] == 'db')
			|| ($GLOBALS['phpgw']->msg->use_private_table == True))
			{
				// REF_SESSION should not really be in $_SESSION namespace so RE-CREATE all this outside of php4 sessions
				// we are going to make this for our own use
				// GLOBALS[phpgw_session][phpgw_app_sessions][email]
				//it imitates what we use in php4 sessions, but the data will actually be stored in the DB
				if (isset($GLOBALS['email_dbsession_compat']) == False)
				{
					$GLOBALS['email_dbsession_compat'] = array();
				}
				if (isset($GLOBALS['email_dbsession_compat']['phpgw_session']) == False)
				{
					$GLOBALS['email_dbsession_compat']['phpgw_session'] = array();
				}
				if (isset($GLOBALS['email_dbsession_compat']['phpgw_session']['phpgw_app_sessions']) == False)
				{
					$GLOBALS['email_dbsession_compat']['phpgw_session']['phpgw_app_sessions'] = array();
				}
				if (isset($GLOBALS['email_dbsession_compat']['phpgw_session']['phpgw_app_sessions']['email']) == False)
				{
					$GLOBALS['email_dbsession_compat']['phpgw_session']['phpgw_app_sessions']['email'] = array();
				}
				// recreate the REF_SESSION to point to this, since it may not have existed earlier
				$GLOBALS['phpgw']->msg->ref_SESSION =& $GLOBALS['email_dbsession_compat'];
				if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: prep_db_session_compat('.__LINE__.'): LEAVING, session_db IS in use, so we created $GLOBALS[email_dbsession_compat][phpgw_session][phpgw_app_sessions][email]<br>'); } 
			}
			else
			{
				if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: prep_db_session_compat('.__LINE__.'): LEAVING, session_db is NOT in use, took no action, nothing needed.<br>'); } 
			}
		}
		
		/*!
		@function expire_db_session_bulk_data
		@abstract for DB sessions_db ONLY, backward compatibility where php4 sessions are not in use, 
		Also with the anglemail table but calls lower level functions in that case. 
		@discussion Aggressive way to wipe cached data with the database caching 
		methods. Called by the higher level caching functions, this 
		does a blanket delete of ALL cached data, BUT it does have code to save 
		a few items that are not strictly cache items, such as  the "mailsvr_callstr", 
		"folder_list", and "mailsvr_namespace" for the email account(s). 
		@access Private 
		@author Angles
		*/
		function expire_db_session_bulk_data($called_by='not_specified', $wipe_absolutely_everything=False)
		{
			
			// for DB sessions_db, OR used for anglemail table
			if (($GLOBALS['phpgw_info']['server']['sessions_type'] == 'db')
			|| ($GLOBALS['phpgw']->msg->use_private_table == True))
			{
				if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: expire_db_session_bulk_data('.__LINE__.'): ENTERING, session_db IS in use, $called_by ['.$called_by.']<br>'); } 
				// RETAIN IMPORTANT DATA
				$retained_data=array();
				for ($i=0; $i < count($GLOBALS['phpgw']->msg->extra_and_default_acounts); $i++)
				{
					if ($GLOBALS['phpgw']->msg->extra_and_default_acounts[$i]['status'] == 'enabled')
					{
						$this_acctnum = $GLOBALS['phpgw']->msg->extra_and_default_acounts[$i]['acctnum'];
						if ($GLOBALS['phpgw']->msg->use_private_table == True)
						{
							$retained_data[$this_acctnum]['mailsvr_callstr'] = $this->so_get_data((string)$this_acctnum.';mailsvr_callstr');
							$retained_data[$this_acctnum]['folder_list'] = $this->so_get_data((string)$this_acctnum.';folder_list');
							$retained_data[$this_acctnum]['mailsvr_namespace'] = $this->so_get_data((string)$this_acctnum.';mailsvr_namespace');
						}
						else
						{
							$retained_data[$this_acctnum]['mailsvr_callstr'] = $GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';mailsvr_callstr', 'email');
							$retained_data[$this_acctnum]['folder_list'] = $GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';folder_list', 'email');
							$retained_data[$this_acctnum]['mailsvr_namespace'] = $GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';mailsvr_namespace', 'email');
						}
					}
				}
				
				if ($GLOBALS['phpgw']->msg->use_private_table == True)
				{
					// WIPE CLEAN THE CACHE all data for this user
					$this->so_clear_all_data_this_user();
				}
				else
				{
					// WIPE CLEAN THE CACHE
					$account_id = get_account_id($accountid,$GLOBALS['phpgw']->session->account_id);
					$query = "DELETE FROM phpgw_app_sessions WHERE loginid = '".$account_id."'"
						." AND app = 'email'";
					$GLOBALS['phpgw']->db->query($query);
				}
				
				if ($wipe_absolutely_everything == False)
				{
					// RE-INSERT IMPORTANT DATA
					for ($i=0; $i < count($GLOBALS['phpgw']->msg->extra_and_default_acounts); $i++)
					{
						if ($GLOBALS['phpgw']->msg->extra_and_default_acounts[$i]['status'] == 'enabled')
						{
							$this_acctnum = $GLOBALS['phpgw']->msg->extra_and_default_acounts[$i]['acctnum'];
							if ($retained_data[$this_acctnum]['mailsvr_callstr'])
							{
								if ($GLOBALS['phpgw']->msg->use_private_table == True)
								{
									$this->so_set_data((string)$this_acctnum.';mailsvr_callstr', $retained_data[$this_acctnum]['mailsvr_callstr']);
								}
								else
								{
									$GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';mailsvr_callstr', 'email', $retained_data[$this_acctnum]['mailsvr_callstr']);
								}
							}
							if ($retained_data[$this_acctnum]['folder_list'])
							{
								if ($GLOBALS['phpgw']->msg->use_private_table == True)
								{
									$this->so_set_data((string)$this_acctnum.';folder_list', $retained_data[$this_acctnum]['folder_list']);
								}
								else
								{
									$GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';folder_list', 'email', $retained_data[$this_acctnum]['folder_list']);
								}
							}
							if ($retained_data[$this_acctnum]['mailsvr_namespace'])
							{
								if ($GLOBALS['phpgw']->msg->use_private_table == True)
								{
									$this->so_set_data((string)$this_acctnum.';mailsvr_namespace', $retained_data[$this_acctnum]['mailsvr_namespace']);
								}
								else
								{
									$GLOBALS['phpgw']->session->appsession((string)$this_acctnum.';mailsvr_namespace', 'email', $retained_data[$this_acctnum]['mailsvr_namespace']);
								}
							}
						}
					}
				}
				if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: expire_db_session_bulk_data('.__LINE__.'): LEAVING, session_db IS in use, did erase all email appsession data<br>'); } 
			}
		}
		
		// ==BEGIN== TEMP DATA STORE COMMANDS
		/*!
		@function so_am_table_exists
		@abstract ?
		@author Angles
		*/
		function so_am_table_exists()
		{
			$look_for_me = 'phpgw_anglemail';
			
			// have we cached this in SESSION cache - NOT the AM table itself!
			$appsession_key = $look_for_me.'_exists';
			$affirmative_value = 'yes';
			$negative_value = 'no';
			$appsession_returns = $this->so_appsession_passthru($appsession_key);
			if ($appsession_returns == $affirmative_value)
			{
				//echo 'so_am_table_exists: result: Actual APPSESSION reports stored info saying table ['.$look_for_me.'] DOES exist<br>';
				return True;
			}
			elseif ($appsession_returns == $negative_value)
			{
				//echo 'so_am_table_exists: result: Actual APPSESSION reports stored info saying table ['.$look_for_me.'] does NOT exist<br>';
				return False;
			}
			
			// NO APPSESSION data, continue ...
			$table_names = $GLOBALS['phpgw']->db->table_names();
			$table_names_serialized = serialize($table_names);
			if (strstr($table_names_serialized, $look_for_me))
			{
				// STORE THE POSITIVE ANSWER
				$this->so_appsession_passthru($appsession_key, $affirmative_value);
				//echo 'so_am_table_exists: result: table ['.$look_for_me.'] DOES exist<br>';
				return True;
			}
			else
			{
				// STORE THE NEGATIVE ANSWER
				$this->so_appsession_passthru($appsession_key, $negative_value);
				//echo 'so_am_table_exists: result: table ['.$look_for_me.'] does NOT exist<br>';
				return False;
			}
			//echo '$table_names dump:<pre>';
			//print_r($table_names) ;
			//echo '</pre>';
		}
		
		// these bext functions will go inti the future SO class
		/*!
		@function so_set_data
		@abstract ?
		*/
		function so_set_data($data_key, $content, $compression=False)
		{
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_set_data('.__LINE__.'): ENTERING, $data_key ['.$data_key.'], $compression ['.serialize($compression).']<br>'); }
			$account_id = get_account_id($accountid,$GLOBALS['phpgw']->session->account_id);
			$data_key = $GLOBALS['phpgw']->db->db_addslashes($data_key);
			// for compression, first choice is BZ2, second choice is GZ
			//if (($compression)
			//&& (function_exists('bzcompress')))
			//{
			//	$content_preped = base64_encode(bzcompress(serialize($content)));
			//	$content = '';
			//	unset($content);
			//	if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_set_data('.__LINE__.'): $compression is ['.serialize($compression).'] AND we did serialize and <font color="green">did BZ2 compress</font>, no addslashes for compressed content<br>'); }
			//}
			//else
			if (($compression)
			&& (function_exists('gzcompress')))
			{
				$content_preped = base64_encode(gzcompress(serialize($content)));
				$content = '';
				unset($content);
				if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_set_data('.__LINE__.'): $compression is ['.serialize($compression).'] AND we did serialize and <font color="green">did GZ compress</font>, no addslashes for compressed content<br>'); }
			}
			else
			{
				// addslashes only if NOT compressing data
				// serialize only is NOT a string
				if (is_string($content))
				{
					$content_preped = $GLOBALS['phpgw']->db->db_addslashes($content);
				}
				else
				{
					$content_preped = $GLOBALS['phpgw']->db->db_addslashes(serialize($content));
				}
				$content = '';
				unset($content);
				if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_set_data('.__LINE__.'): $compress is ['.serialize($compress).'] AND we did serialize with NO compression<br>'); }
			}
			
			$GLOBALS['phpgw']->db->query("SELECT content FROM phpgw_anglemail WHERE "
				. "account_id = '".$account_id."' AND data_key = '".$data_key."'",__LINE__,__FILE__);
			
			if ($GLOBALS['phpgw']->db->num_rows()==0)
			{
				$GLOBALS['phpgw']->db->query("INSERT INTO phpgw_anglemail (account_id,data_key,content) "
					. "VALUES ('" . $account_id . "','" . $data_key . "','" . $content_preped . "')",__LINE__,__FILE__);
			}
			else
			{
				$GLOBALS['phpgw']->db->query("UPDATE phpgw_anglemail set content='" . $content_preped 
					. "' WHERE account_id='" . $account_id . "' AND data_key='" . $data_key . "'",__LINE__,__FILE__);
			}
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_set_data('.__LINE__.'): LEAVING <br>'); }
		}
		
		/*!
		@function so_get_data
		@abstract ?
		*/
		function so_get_data($data_key, $compression=False)
		{
			if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): ENTERING, $data_key ['.$data_key.'], $compression ['.serialize($compression).']<br>'); }
			$account_id = get_account_id($accountid,$GLOBALS['phpgw']->session->account_id);
			$data_key = $GLOBALS['phpgw']->db->db_addslashes($data_key);
			
			$GLOBALS['phpgw']->db->query("SELECT content FROM phpgw_anglemail WHERE "
				. "account_id = '".$account_id."' AND data_key = '".$data_key."'",__LINE__,__FILE__);
			
			if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): $GLOBALS[phpgw]->db->num_rows() = ['.$GLOBALS['phpgw']->db->num_rows().'] <br>'); } 
			
			if ($GLOBALS['phpgw']->db->num_rows()==0)
			{
				if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, returning False<br>'); }
				return False;
			}
			elseif (($compression)
			//&& ((function_exists('bzdecompress')) || (function_exists('gzuncompress')) )
			&& (function_exists('gzuncompress')))
			{
				$GLOBALS['phpgw']->db->next_record();
				// no stripslashes for compressed data
				$my_content = $GLOBALS['phpgw']->db->f('content');
				$comp_desc = array();
				$comp_desc['before_decomp'] = 'NA';
				$comp_desc['after_decomp'] = 'NA';
				$comp_desc['ratio_txt'] = 'NA';
				$comp_desc['ratio_math'] = 'NA';
				if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $comp_desc['before_decomp'] = strlen($my_content); } 
				if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): strlen($my_content) is ['.$comp_desc['before_decomp'].'], BEFORE decompress, $compression is ['.serialize($compression).']<br>'); }
				//if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): $GLOBALS[phpgw]->db->next_record() yields $my_content DUMP:', $my_content); }
				if (!$my_content)
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, returning False<br>'); }
					return False;
				}
				// for compression, first choice is BZ2, second choice is GZ
				// NEW: BZ2 is SLOWER than zlib
				//if (function_exists('bzdecompress'))
				//{
				//	$my_content_preped = unserialize(bzdecompress(base64_decode($my_content)));
				//	if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $comp_desc['after_decomp'] = strlen(serialize($my_content_preped)); $comp_desc['ratio_math'] = (string)(round(($comp_desc['after_decomp']/$comp_desc['before_decomp']), 1) * 1).'X'; $comp_desc['ratio_txt'] = 'pre/post is ['.$comp_desc['before_decomp'].' to '.$comp_desc['after_decomp']; }
				//	if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): $compression: ['.serialize($compression).'] using <font color="brown">BZ2 decompress</font> pre/post is ['.$comp_desc['ratio_txt'].']; ratio: ['.$comp_desc['ratio_math'].'] <br>'); }
				//}
				//else
				if (function_exists('gzuncompress'))
				{
					$my_content_preped = unserialize(gzuncompress(base64_decode($my_content)));
					if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $comp_desc['after_decomp'] = strlen(serialize($my_content_preped)); $comp_desc['ratio_math'] = (string)(round(($comp_desc['after_decomp']/$comp_desc['before_decomp']), 1) * 1).'X'; $comp_desc['ratio_txt'] = 'pre/post is ['.$comp_desc['before_decomp'].' to '.$comp_desc['after_decomp']; }
					if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): $compression: ['.serialize($compression).'] using <font color="brown">GZ uncompress</font> pre/post is ['.$comp_desc['ratio_txt'].']; ratio: ['.$comp_desc['ratio_math'].'] <br>'); }
				}
				else
				{
					$my_content_preped = '';
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): ERROR: $compression: ['.serialize($compression).'] <font color="brown">decompression ERROR</font> neither "bzdecompress" (first choice) nor "gzuncompress" (second choice) is available<br>'); }
				}
				$my_content = '';
				unset($my_content);
				if (!$my_content_preped)
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): AFTER DECOMPRESS and UNserialization $my_content_preped is GONE!'); }
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, returning False, <font color="red">content did not unserialize, compression was in use </font> <br>'); }
					return False;
				}
				else
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): AFTER DECOMPRESS and UNserialization $my_content_preped DUMP:', $my_content_preped); }
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, got content, <font color="brown"> did decompress </font> , returning that content<br>'); }
					return $my_content_preped;
				}
			}
			else
			{
				$GLOBALS['phpgw']->db->next_record();
				// NOTE: we only stripslashes when NOT using compression
				$my_content = $GLOBALS['phpgw']->db->f('content', 'stripslashes');
				if ($GLOBALS['phpgw']->msg->debug_so_class > 1) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): strlen($my_content) is ['.strlen($my_content).']<br>'); }
				//if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): $GLOBALS[phpgw]->db->next_record() yields $my_content DUMP:', $my_content); }
				if (!$my_content)
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, returning False<br>'); }
					return False;
				}
				// we serialize only NON-strings, 
				// so unserialize only if content is already serialized
				if ($GLOBALS['phpgw']->msg->is_serialized_str($my_content) == True)
				{
					$my_content_preped = unserialize($my_content);
				}
				else
				{
					$my_content_preped = $my_content;
				}
				$my_content = '';
				unset($my_content);
				if (!$my_content_preped)
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): AFTER UNserialization $my_content_preped is GONE!'); }
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, returning False, <font color="red">content did not unserialize </font> <br>'); }
					return False;
				}
				else
				{
					if ($GLOBALS['phpgw']->msg->debug_so_class > 2) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): AFTER UNserialization $my_content_preped DUMP:', $my_content_preped); }
					if ($GLOBALS['phpgw']->msg->debug_so_class > 0) { $GLOBALS['phpgw']->msg->dbug->out('so_mail_msg: so_get_data('.__LINE__.'): LEAVING, got content, returning that content<br>'); }
					return $my_content_preped;
				}
			}
		}
		
		/*!
		@function so_delete_data
		@abstract ?
		*/
		function so_delete_data($data_key)
		{
			$account_id = get_account_id($accountid,$GLOBALS['phpgw']->session->account_id);
			$data_key = $GLOBALS['phpgw']->db->db_addslashes($data_key);
			$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_anglemail "
				. " WHERE account_id='" . $account_id . "' AND data_key='" . $data_key . "'",__LINE__,__FILE__);
		}
		
		/*!
		@function so_clear_all_data_this_user
		@abstract ?
		*/
		function so_clear_all_data_this_user()
		{
			$account_id = get_account_id($accountid,$GLOBALS['phpgw']->session->account_id);
			$GLOBALS['phpgw']->db->query("DELETE FROM phpgw_anglemail "
				. " WHERE account_id='" . $account_id . "'",__LINE__,__FILE__);
		}
		
		/*!
		@function so_appsession_passthru
		@abstract this will ONLY use the ACTUAL REAL APPSESSION of phpgwapi 
		@param $location (string) in phpgwapi session speak this is the "name" of the information aka the 
		key in a key value pair
		@param $location (string) OPTIONAL the value in the key value pair. Empty will erase I THINK the 
		apsession data stored for the "name" aka the "location". 
		@discussion This is a SIMPLE PASSTHRU for the real phpgwapi session call. This function will 
		never use the anglemail table, it is intended for stuff we REALLY want to last only for one session. 
		@author Angles
		*/
		function so_appsession_passthru($location='',$data='##NOTHING##')
		{
			if ($data == '##NOTHING##')
			{
				return $GLOBALS['phpgw']->session->appsession($location, 'email');
			}
			else
			{
				return $GLOBALS['phpgw']->session->appsession($location, 'email', $data);
			}
		}
	}
?>
