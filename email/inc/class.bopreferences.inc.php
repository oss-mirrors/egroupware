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

	class bopreferences
	{
		var $public_functions = array(
			'preferences' => True,
			'init_available_prefs' => True,
			'grab_set_prefs' => True,
			'ex_accounts_edit' => True,
			'ex_accounts_list' => True,
			'ex_accounts_delete' => True
		);
		var $not_set='-1';
		var $std_prefs=array();
		var $cust_prefs=array();
		var $submit_token='submit_prefs';
		var $submit_token_extra_accounts='submit_prefs_extra_accounts';
		var $submit_token_delete_ex_account='submit_prefs_delete_ex_account';
		var $add_new_account_token='add_new';
		
		// possible values: "default" or "extra_accounts"
		var $account_group = 'default';
		var $acctnum = '';
		
		// were we called from phpgroupware ("phpgw")or externally via xml-rpc ("xmlrpc")
		var $caller='phpgw';
		var $pref_errors='';
		var $args=array();
		var $debug_set_prefs = 0;
		//var $debug_set_prefs = 3;
		
		function bopreferences()
		{
			/*
			@capability: initialize class mail_msg object but do not login
			@discussion: we need mail_msg fully initialized to set prefs, but we
			do not need class_dcom, nor do we need to login, this is how to do it:
			1) create the mail_msg object
			2) initialize simple "begin_request" args array holder variable
			setting prefs does not require a login, in fact you may not yet be able to login
			"do_login" = False is the only "request_args_array" element we need to set
			3) begin the class mail_msg transaction request with "begin_request"
			even though we are not logging in, the will initialize the class
			*/
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences *constructor*: ENTERING <br>'; }
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences *constructor*: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences *constructor*: is_object: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			if ($GLOBALS['phpgw']->msg->get_isset_arg('already_grab_class_args_gpc'))
			{
				if ($this->debug_set_prefs > 0) { echo 'email.bopreferences *constructor*: LEAVING , msg object already initialized<br>'; }
				return True;
			}
				
			if ($this->debug_set_prefs > 1) { echo 'email.bopreferences *constructor*: msg object NOT yet initialized<br>'; }
			$args_array = Array();
			// should we log in or not
			$args_array['do_login'] = False;
			if ($this->debug_set_prefs > 1) { echo 'email.bopreferences. *constructor*: call msg->begin_request with args array:'.serialize($args_array).'<br>'; }
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			$already_initialized = True;
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences. *constructor*: LEAVING<br>'; }
		}
		
		/*!
		 @function init_available_prefs
		 @abstract Defines all available preferences for the email app and put in $this->std_prefs[] and $this->cust_prefs[]
		 @return none, this is function directly manipulates the class vars ->std_prefs[] and ->cust_prefs[]
		 @discussion  This function serves as a single place to establish and maintain all preferences
		 available to the email class <br>
		 $this->std_prefs[] class array holds all Standard Preferences available for email, and <br>
		 $this->cust_prefs[] class array holds all Custom Preferences available for email<br> 
		 Since the preferenced are stored in a dynamic database, the database schema is not present
		 at the database level, so we define it here. <br>
		 Also, $this->std_prefs[] and $this->cust_prefs[] arrays can be used to build a UI for managing and 
		 showing these prefs, and <br>
		 those arrays can be looped through for the setting and storing of these preferences.
		[init_default] comma seperated, first word is an instructional token
			--possible tokens are--
			string		[any_string]  ex. 'string,new_old'
			set_or_not	[set|not_set]  ex.  'set_or_not,not_set'
			function	[string_will_be_eval'd] ex. 'function,$this->sub_default_userid($accountid)'
			init_no_fill	we will not fill this item during initialization (ex. a password)
			varEVAL	[string_to_eval] ex. "$GLOBALS['phpgw_info']['server']['mail_server']"
		@author	Angles
		@access	Public
		*/
		function init_available_prefs()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.init_available_prefs: ENTERING, use debug level 4 for a data dump on leaving<br>'; }
			
			$this->std_prefs = Array();
			$i = 0;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'ex_account_enabled',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('enable this email account'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'fullname',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('Your full name'),
				'init_default'	=> 'varEVAL,$GLOBALS["phpgw_info"]["user"]["fullname"];',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'email_sig',
				'type'		=> 'user_string',
				'widget'	=> 'textarea',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'empty_string_ok',
				'lang_blurb'	=> lang('email signature'),
				'init_default'	=> 'string, ',
				'values'	=> array()
			);
			$lang_oldest = lang('oldest');
			$lang_newest = lang('newest');
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'default_sorting',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('Default sorting order'),
				'init_default'	=> 'string,new_old',
				'values'	=> array(
					'old_new' => $lang_oldest.' -> '.$lang_newest,
					'new_old' => $lang_newest.' -> '.$lang_oldest
				)
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'layout',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('Message List Layout'),
				'init_default'	=> 'string,2',
				'values'	=> array(
					'1' => lang('Layout 1'),
					'2' => lang('Layout 2')
				)
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'show_addresses',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'accts_usage'	=> 'default',
				'write_props'	=> '',
				'lang_blurb'	=> lang('Show sender\'s email address with name'),
				'init_default'	=> 'string,none',
				'values'	=> array(
					'none' => lang('none'),
					'From' => lang('From'),
					'ReplyTo' => lang('ReplyTo')
				)
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'mainscreen_showmail',
				'type'		=> 'exists',
				'accts_usage'	=> 'default',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'default',
				'write_props'	=> '',
				'lang_blurb'	=> lang('show new messages on main screen'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'use_trash_folder',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				//'lang_blurb'	=> lang('Deleted messages saved to folder:'),
				//'lang_blurb'	=> lang('save Deleted messages in folder named below'),
				'lang_blurb'	=> lang('Deleted messages go to Trash'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'trash_folder_name',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				//'lang_blurb'	=> lang('Deleted messages folder name'),
				'lang_blurb'	=> lang('Deleted messages (Trash) folder'),
				'init_default'	=> 'string,Trash',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'use_sent_folder',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				//'lang_blurb'	=> lang('Sent messages saved to folder:'),
				//'lang_blurb'	=> lang('save Sent messages in folder named below'),
				'lang_blurb'	=> lang('Sent messages saved in &quot;Sent&quot; folder'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'sent_folder_name',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				//'lang_blurb'	=> lang('Sent messages folder name'),
				'lang_blurb'	=> lang('Sent messages folder'),
				'init_default'	=> 'string,Sent',
				'values'	=> array()
			);
			/*
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'font_size_offset',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'write_props'	=> '',
				'lang_blurb'	=> lang('Change Font Size in your E-Mail Pages'),
				'init_default'	=> 'string,-1',
				'values'	=> array(
					'-2' => lang('Smallest'),
					'-1' => lang('Smaller'),
					'0' => lang('Normal'),
					'1' => lang('Bigger'),
					'2' => lang('Biggest')
				)
			);
			*/
			/*
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'p_persistent',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				'write_props'	=> '',
				'lang_blurb'	=> lang('persistent email server session'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			*/
			// this item has been phased out, not used at the moment
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'cache_data',
				'type'		=> 'exists, INACTIVE',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('cache server data whenever possible'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->std_prefs[$i] = Array(
				'id' 		=> 'enable_utf7',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('enable UTF-7 encoded folder names'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			
			// Custom Settings
			$this->cust_prefs = Array();
			$i = 0;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'use_custom_settings',
				'type'		=> 'exists',
				'widget'	=> 'checkbox',
				//'accts_usage'	=> 'default, extra_accounts',
				'accts_usage'	=> 'default',
				'write_props'	=> 'group_master',
				'lang_blurb'	=> lang('Use custom settings'),
				'init_default'	=> 'set_or_not,not_set',
				'values'	=> array()
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'userid',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'no_db_defang',
				'lang_blurb'	=> lang('Email Account Name'),
			//	'init_default'	=> 'function,$this->sub_default_userid($account_id);',
				'init_default'	=> 'function,sub_default_userid',
				'values'	=> array()
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'passwd',
				'type'		=> 'user_string',
				'widget'	=> 'passwordbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'password, hidden, encrypted, empty_no_delete',
				'lang_blurb'	=> lang('Email Password'),
				'init_default'	=> 'init_no_fill',
				'values'	=> array()
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'address',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'no_db_defang',
				'lang_blurb'	=> lang('Email address'),
			//	'init_default'	=> 'function,$this->sub_default_address($account_id);',
				'init_default'	=> 'function,sub_default_address',
				'values'	=> array()
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'mail_server',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'no_db_defang',
				'lang_blurb'	=> lang('Mail Server'),
				'init_default'	=> 'varEVAL,$GLOBALS["phpgw_info"]["server"]["mail_server"];',
				'values'	=> array()
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'mail_server_type',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'no_db_defang',
				'lang_blurb'	=> lang('Mail Server type'),
				'init_default'	=> 'varEVAL,$GLOBALS["phpgw_info"]["server"]["mail_server_type"];',
				'values'	=> array(
					'imap'		=> 'IMAP',
					'pop3'		=> 'POP-3',
					'imaps'		=> 'IMAPS',
					'pop3s'		=> 'POP-3S'
				)
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'imap_server_type',
				'type'		=> 'known_string',
				'widget'	=> 'combobox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> '',
				'lang_blurb'	=> lang('IMAP Server Type') .' - ' .lang('If Applicable'),
				'init_default'	=> 'varEVAL,$GLOBALS["phpgw_info"]["server"]["imap_server_type"];',
				'values'	=> array(
					'Cyrus'		=> 'Cyrus '.lang('or').' Courier',
					'UWash'		=> 'UWash',
					'UW-Maildir'	=> 'UW-Maildir'
				)
			);
			$i++;
			$this->cust_prefs[$i] = Array(
				'id' 		=> 'mail_folder',
				'type'		=> 'user_string',
				'widget'	=> 'textbox',
				'accts_usage'	=> 'default, extra_accounts',
				'write_props'	=> 'empty_string_ok, no_db_defang',
				'lang_blurb'	=> lang('U-Wash Mail Folder').' - ' .lang('If Applicable'),
				'init_default'	=> 'varEVAL,$GLOBALS["phpgw_info"]["server"]["mail_folder"];',
				'values'	=> array()
			);
			if ($this->debug_set_prefs > 3) { echo 'email.bopreferences.init_available_prefs: data dump: calling debug_dump_prefs<pre>';  $this->debug_dump_prefs(); }
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.init_available_prefs: LEAVING<br>'; }
		}
		
		
		
		function debug_dump_prefs()
		{
			// DEBUG begin
			echo '<br><br>';
			echo '<b>std_prefs var dump:</b><pre>'; print_r($this->std_prefs); echo '</pre>';
			echo '<b>cust_prefs var dump:</b><pre>'; print_r($this->cust_prefs); echo '</pre>';
			//Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
			//return;
			// DEBUG end
		}
		
		/*!
		@function grab_set_prefs_args
		@abstract calls either (a) grab_set_prefs_args_gpc or (b) grab_set_prefs_args_xmlrpc depending
		on if this class was called from within phogw or via external XMP-RPC. If neither,
		we should produce an error.
		@param : none : However, function uses class var ->caller (string) with expected values being 
		"phpgw" and "xmlrpc".
		@author	Angles
		@access	Public
		*/
		function grab_set_prefs()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences: call to grab_set_prefs<br>'; }
			// better make sure we have created the available prefs schema
			$this->init_available_prefs();

			if ($this->caller == 'phpgw')
			{
				$this->grab_set_prefs_args_gpc();
			}
			elseif($this->caller == 'xmlrpc')
			{
				$this->grab_set_prefs_args_xmlrpc();
			}
			else
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences: call to grab_set_prefs CALLER UNKNOWN<br>'; }
				$this->pref_errors .= 'email: bopreferences: grab_set_prefs: unsupported "caller" variable<br>';
			}
		}
		
		/*!
		@function grab_set_prefs_args_gpc
		@abstract Called By "grab_set_prefs", only handles GPC vars that are involved in setting email 
		preferences. Grabs data from $GLOBALS['HTTP_POST_VARS'] and $GLOBALS['HTTP_GET_VARS']
		as necessaey, and fills various class arg variables with the available data. HOWEVER, does 
		not attempt to grab data if the "submit_prefs" GPC submit_token variable is not present.
		@param none
		@result none, this is an object call
		@discussion  For abstraction from phpgw UI and from PHP's GPC data, put the submitted GPC data
		into a class var $this->args[] array. This array is then used to represent the submitted data, 
		instead of $GLOBALS['HTTP_POST_VARS'].  <br>
		This serves to further seperate the mail functionality from php itself, this function will perform
		the variable handling of the traditional php page view Get Post Cookie (no cookie data used here though)
		The same data could be grabbed from any source, XML-RPC for example, insttead of php's GPC vars,
		so this function could (should) have an equivalent XML-RPC "to handle filling these class variables
		from an alternative source. These class vars are only relevant to setting email prefs.
		@author	Angles
		@access	Private
		*/
		function grab_set_prefs_args_gpc()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences: call to grab_set_prefs_args_gpc<br>'; }
			// ----  HANDLE GRABBING PREFERENCE GPC HTTP_POST_VARS ARGS  -------
			// for abstraction from phpgw UI and from PHP's GPC data, put the submitted GPC data
			// into a class var $this->args[] array. This array is then used to represent the submitted
			// data, instead of $GLOBALS['HTTP_POST_VARS']. 
			// HOWEVER, do not attempt to grab data if the "submit_prefs" GPC submit_token variable is not present
			
			// ----  DEFAULT EMAIL ACCOUNT  ----
			if (isset($GLOBALS['HTTP_POST_VARS'][$this->submit_token]))
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences: INSIDE grab_set_prefs_args_gpc for Default Email Account data<br>'; }
				
				//$this->args['submit_prefs'] = $GLOBALS['HTTP_POST_VARS']['submit_prefs'];
				$this->args[$this->submit_token] = $GLOBALS['HTTP_POST_VARS'][$this->submit_token];
				// standard prefs
				$loops = count($this->std_prefs);				
				for($i=0;$i<$loops;$i++)
				{
					// ----  skip this item logic  ----
					// we are ONLY concerned with items that apply to the default email account
					// existence of $this->submit_token indicates this data is intended for the default email account
					if (!stristr($this->std_prefs[$i]['accts_usage'], 'default'))
					{
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) _SKIP_ this item ['.$this->std_prefs[$i]['id'].'], it does not apply to the default email account<br>'; }
					}
					else
					{
						// ok, we have a pref item that applies to the default email account
						$this_pref_name = $this->std_prefs[$i]['id'];
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) $this_pref_name: '.$this_pref_name.'<br>'; }
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) $GLOBALS[HTTP_POST_VARS][$this_pref_name]: '.$GLOBALS['HTTP_POST_VARS'][$this_pref_name].'<br>'; }
						if (isset($GLOBALS['HTTP_POST_VARS'][$this_pref_name]))
						{
							$this->args[$this_pref_name] = $GLOBALS['HTTP_POST_VARS'][$this_pref_name];
						}
					}
				}
				// custom prefs
				$loops = count($this->cust_prefs);				
				for($i=0;$i<$loops;$i++)
				{
					// ----  skip this item logic  ----
					// we are ONLY concerned with items that apply to the default email account
					// existence of $this->submit_token indicates this data is intended for the default email account
					if (!stristr($this->cust_prefs[$i]['accts_usage'], 'default'))
					{
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) _SKIP_ this item ['.$this->cust_prefs[$i]['id'].'], it does not apply to the default email account<br>'; }
					}
					else
					{
						// ok, we have a pref item that applies to the default email account
						$this_pref_name = $this->cust_prefs[$i]['id'];
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) $this_pref_name: '.$this_pref_name.'<br>'; }
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) $GLOBALS[HTTP_POST_VARS][$this_pref_name]: '.$GLOBALS['HTTP_POST_VARS'][$this_pref_name].'<br>'; }
						if (isset($GLOBALS['HTTP_POST_VARS'][$this_pref_name]))
						{
							$this->args[$this_pref_name] = $GLOBALS['HTTP_POST_VARS'][$this_pref_name];
						}
					}
				}
			}
			// ----  EXTRA EMAIL ACCOUNTS  ----
			elseif (isset($GLOBALS['HTTP_POST_VARS'][$this->submit_token_extra_accounts]))
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences: INSIDE grab_set_prefs_args_gpc for EXTRA EMAIL ACCOUNTS data<br>'; }
				
				//$this->args['submit_prefs'] = $GLOBALS['HTTP_POST_VARS']['submit_prefs'];
				$this->args[$this->submit_token_extra_accounts] = $GLOBALS['HTTP_POST_VARS'][$this->submit_token_extra_accounts];
				
				// ==== ACCTNUM ====
				if ((!isset($this->acctnum))
				|| ((string)$this->acctnum == ''))
				{
					$this->acctnum = $this->obtain_ex_acctnum();
				}
				
				// standard prefs
				$loops = count($this->std_prefs);				
				for($i=0;$i<$loops;$i++)
				{
					// ----  skip this item logic  ----
					// we are ONLY concerned with items that apply to EXTRA email accounts
					// existence of "$this->submit_token_extra_accounts" indicates this data is intended for 
					// extra email accounts
					if (!stristr($this->std_prefs[$i]['accts_usage'], 'extra_accounts'))
					{
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) _SKIP_ this item ['.$this->std_prefs[$i]['id'].'], it does not apply to extra email accounts<br>'; }
					}
					else
					{
						// ok, we have a pref item that applies to the default email account
						$this_pref_name = $this->std_prefs[$i]['id'];
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) $this_pref_name: '.$this_pref_name.'<br>'; }
						if ($this->debug_set_prefs > 1) { echo ' * * (std pref) $GLOBALS[HTTP_POST_VARS][$this->acctnum('.$this->acctnum.')][$this_pref_name('.$this_pref_name.')]: ['.$GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name].']<br>'; }
						if (isset($GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name]))
						{
							$this->args[$this->acctnum][$this_pref_name] = $GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name];
						}
					}
				}
				// custom prefs
				$loops = count($this->cust_prefs);				
				for($i=0;$i<$loops;$i++)
				{
					// ----  skip this item logic  ----
					// we are ONLY concerned with items that apply to EXTRA email accounts
					// existence of "$this->submit_token_extra_accounts" indicates this data is intended for 
					// extra email accounts
					if (!stristr($this->cust_prefs[$i]['accts_usage'], 'extra_accounts'))
					{
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) _SKIP_ this item ['.$this->cust_prefs[$i]['id'].'], it does not apply to extra email accounts<br>'; }
					}
					else
					{
						// ok, we have a pref item that applies to extra email accounts
						$this_pref_name = $this->cust_prefs[$i]['id'];
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) $this_pref_name: '.$this_pref_name.'<br>'; }
						if ($this->debug_set_prefs > 1) { echo ' * * (cust pref) $GLOBALS[HTTP_POST_VARS][$this->acctnum('.$this->acctnum.')][$this_pref_name('.$this_pref_name.')]: ['.$GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name].']<br>'; }
						if (isset($GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name]))
						{
							$this->args[$this->acctnum][$this_pref_name] = $GLOBALS['HTTP_POST_VARS'][$this->acctnum][$this_pref_name];
						}
					}
				}
			}
		}
			
		/*
		@function grab_set_prefs_args_xmlrpc
		@abstract Called By "grab_set_prefs", Grabs data an XML-RPC call and fills various class arg variables 
		with the available data relevant to setting email preferences.
		@param none
		@result none, this is an object call
		@discussion functional relative to function "grab_set_prefs_args_gpc()", except this function grabs the
		data from an alternative, non-php-GPC, source
		NOT YET IMPLEMENTED
		@author	Angles
		@access	Private
		*/
		function grab_set_prefs_args_xmlrpc()
		{
			// STUB, for future use
			echo 'email boprefs: call to un-implemented function grab_set_prefs_args_xmlrpc';
		}
		
		/*
		@function process_submitted_prefs
		@abstract Process incoming submitted prefs, process the data, and save to repository 
		if needed. Currently used for processing email preferences, both standard and custom
		@param $pref_set : array : structured pref data as defined and supplied in "this->init_available_prefs()"
		@result boolean False if no $pref_set was supplied, True otherwise
		@discussion Reusable function, any preference data structured as in "this->init_available_prefs()" can 
		use this code to automate preference submissions.
		@author	Angles
		@access	Private
		*/
		function process_submitted_prefs($prefs_set='')
		{
			if(!$prefs_set)
			{
				$prefs_set=array();
			}
			$c_prefs = count($prefs_set);
			if ($c_prefs == 0)
			{
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_submitted_prefs: empty array, no prefs set supplied, exiting<br>'; }
				return False;
			}
			
			for($i=0;$i<$c_prefs;$i++)
			{
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_submitted_prefs: inside preferences loop ['.$i.']<br>'; }
				
				$this_pref = $prefs_set[$i];
				
				// ----  skip this item logic  ----
				// we are ONLY concerned with items that apply to the default email account
				// extra email accounts are handled elsewhere
				if (!stristr($this_pref['accts_usage'] , 'default'))
				{
					// we are not supposed to show this item for the default account, skip this pref item
					// continue is used within looping structures to skip the rest of the current loop 
					// iteration and continue execution at the beginning of the next iteration
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_submitted_prefs: _SKIP_ this item ["'.$this_pref['id'].'"], it does not apply to the default email account<br>'; }
					continue;
				}
				
				// ---- ok, this item is relevant to the default email account  ----
				if ((!isset($this->args[$this_pref['id']]))
				|| (trim($this->args[$this_pref['id']]) == ''))
				{
					// nothing submitted for this preference item
					// OR an empty string was submitted for this pref item
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_submitted_prefs: submitted_pref for ["'.$this_pref['id'].'"] not set or empty string<br>'; }
					if (stristr($this_pref['write_props'], 'empty_no_delete'))
					{
						// DO NOT DELETE
						// "empty_no_delete" means keep the existing pref un-molested, as-is, no change
						// note there may or may not actually be an existing value in the prefs table
						// but it does not matter here, because we do not touch this items value at all.
						// Typical Usage: passwords
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: no change to repository for empty or blank ["'.$this_pref['id'].'"] because of "empty_no_delete"<br>'; }
					}
					elseif (stristr($this_pref['write_props'], 'empty_string_ok'))
					{
						// "empty_string_ok" means a blank string "" IS a VALID pref value
						// i.e. this pref can take an empty string as a valid value
						// whereas most other prefs are simply deleted from the repository if value is empty
						// Typical Usage: email sig, UWash Mail Folder
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: save empty string to repository for ["'.$this_pref['id'].'"] because of "empty_string_ok"<br>'; }
						// a) as always, delete the pref before we assign a value
						$GLOBALS['phpgw']->preferences->delete('email',$this_pref['id']);
						// b) now assign a blank string value
						$GLOBALS['phpgw']->preferences->add('email',$this_pref['id'],'');
					}
					else
					{
						// just delete it from the preferences repository
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: deleting empty or blank pref ["'.$this_pref['id'].'"] from the repository<br>'; }
						$GLOBALS['phpgw']->preferences->delete('email',$this_pref['id']);
					}
				}
				else
				{
					// ---  we have real data submitted for this preference item  ---
					$submitted_pref = $this->args[$this_pref['id']];
					// init a var to hold the processed submitted_pref
					$processed_pref = '';
					if ($this->debug_set_prefs > 1) { echo '* * ** email: bopreferences: process_submitted_prefs:  submitted_pref: ['.$submitted_pref.']<br>'; }
					
					// most "user_string"s need special processing before they can go into the repository
					if ($this_pref['type'] == 'user_string')
					{
						if (stristr($this_pref['write_props'], 'no_db_defang'))
						{
							// typical "user_string" needs to strip any slashes 
							// that PHP "magic_quotes_gpc"may have added
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							// most "user_string" items require pre-processing before going into
							// the repository (strip slashes, html encode, encrypt, etc...)
							// we call this database "de-fanging", remove database unfriendly chars
							// currenty defanging is handled by "mail_msg_obj->html_quotes_encode"
							// EXCEPT when "no_db_defang" is in "write_props"
							$processed_pref = $submitted_pref;
						}
						elseif (stristr($this_pref['write_props'], 'encrypted'))
						{
							// certain data (passwords) should be encrypted before going into the repository
							// "user_string"s to be "encrypted" do NOT get "html_quotes_encode"
							// before going into the encryption routine
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							$processed_pref = $GLOBALS['phpgw']->msg->encrypt_email_passwd($processed_pref);
						}
						else
						{
							// typical "user_string" needs to strip any slashes 
							// that PHP "magic_quotes_gpc"may have added
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							// and this is a _LAME_ way to make the value "database friendly"
							// because slashes and quotes will FRY the whole preferences repository
							$processed_pref = $GLOBALS['phpgw']->msg->html_quotes_encode($processed_pref);
						}
					}
					else
					{
						// all other data needs no special processing before going into the repository
						$processed_pref = $submitted_pref;
					}
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_submitted_prefs: about to assign pref ["'.$this_pref['id'].'"] this value, post processing (if any): <pre>'.$GLOBALS['phpgw']->strip_html($processed_pref).'</pre><br>'."\r\n"; }
					
					// a) as always, delete the pref before we assign a value
					$GLOBALS['phpgw']->preferences->delete('email',$this_pref['id']);
					// b) now assign that processed data to this pref item in the repository
					$GLOBALS['phpgw']->preferences->add('email',$this_pref['id'], $processed_pref);
				}
			}
			// since we apparently did process some prefs data, return True
			return True;
		}
		
		/*
		@function preferences
		@abstract Call this function to process submitted prefs. It makes use of other class functions
		some of which should not be called directly.
		@author	skeeter, Angles
		@access	Public
		*/
		function preferences()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.preferences(): ENTERING<br>'; }
			// establish all available prefs for email
			if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences(): about to call $this->init_available_prefs()<br>'; }
			$this->init_available_prefs();
			
			// this will fill $this->args[] array with any submitted prefs args
			if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences(): about to call $this->grab_set_prefs()<br>'; }
			$this->grab_set_prefs();
			
			// ----  HANDLE SETING PREFERENCE   -------
			if (isset($this->args[$this->submit_token]))
			{
				// is set_magic_quotes_runtime(0) done here or somewhere else
				//set_magic_quotes_runtime(0);
				
				// constructor will initialize $GLOBALS['phpgw']->msg
				
				// ---  Process Standard Prefs  ---
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences: about to process Standard Prefs<br>'; }
				$this->process_submitted_prefs($this->std_prefs);
				
				// ---  Process Custom Prefs  ---
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences: about to process Custom Prefs<br>'; }
				if (isset($this->args['use_custom_settings']))
				{
					// custom settings are in use, process them
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences: custom prefs are in use, calling $this->process_submitted_prefs($this->cust_prefs)<br>'; }
					$this->process_submitted_prefs($this->cust_prefs);
				}
				else
				{
					// custom settings are NOT being used, DELETE them from the repository
					$c_prefs = count($this->cust_prefs);			
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences: custom prefs NOT in use, deleting them<br>'; }
					for($i=0;$i<$c_prefs;$i++)
					{
						if ($this->debug_set_prefs > 2) { echo ' *(loop)* email.bopreferences: preferences: deleting custom pref $this->cust_prefs['.$i.'][id] : ['.$this->cust_prefs[$i]['id'].']<br>'; }
						$GLOBALS['phpgw']->preferences->delete('email',$this->cust_prefs[$i]['id']);
					}
				}
				
				// DONE processing prefs, SAVE to the Repository
				if ($this->debug_set_prefs > 1) 
				{
					echo 'email.bopreferences.preferences(): *debug* at ['.$this->debug_set_prefs.'] so skipping save_repository<br>';
				}
				else
				{
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.preferences(): SAVING REPOSITORY<br>'; }
					$GLOBALS['phpgw']->preferences->save_repository();
				}
				// end the email session
				$GLOBALS['phpgw']->msg->end_request();
				
				// redirect user back to main preferences page
				//if ($this->debug_set_prefs > 1) 
				//{
				//	echo 'email.bopreferences.preferences(): *debug* skipping Header redirection<br>';
				//}
				//else
				//{
				//	Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
				//}
				
				// redirect user back to main preferences page
				$take_me_to_url = $GLOBALS['phpgw']->link(
											'/preferences/index.php');
			
				if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.preferences(): almost LEAVING, about to issue a redirect to:<br>'.$take_me_to_url.'<br>'; }
				if ($this->debug_set_prefs > 1) 
				{
					echo 'email.bopreferences.preferences(): LEAVING, *debug* at ['.$this->debug_set_prefs.'] so skipping Header redirection to: ['.$take_me_to_url.']<br>';
				}
				else
				{
					if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.preferences: LEAVING with redirect to: <br>'.$take_me_to_url.'<br>'; }
					Header('Location: ' . $take_me_to_url);
				}
			}
				
				// DEPRECIATED CODE follows, but do not delete yet, it has useful comments.
				/*
				// these are the standard (non-custom) email options
				// that do NOT hold user-entered strings as their values
				$prefs = Array(
					'default_sorting',
					'layout',
					'show_addresses',
					'mainscreen_showmail',
					'use_sent_folder',
					'use_trash_folder',
					'enable_utf7'
				);
				$c_prefs = count($prefs);
				for($i=0;$i<$c_prefs;$i++)
				{
					$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
					if (isset($this->args[$prefs[$i]])
						&& $this->args[$prefs[$i]] != '')
					{
						$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$this->args[$prefs[$i]]);
					}
				}
				// these are the standard (non-custom) email options
				// that each DO hold a user-entered strings as their value
				$prefs = Array(
					'email_sig',
					'trash_folder_name',
					'sent_folder_name'
				);
				$c_prefs = count($prefs);
				for($i=0;$i<$c_prefs;$i++)
				{
					$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
					if(isset($this->args[$prefs[$i]]))
					{
						$temp_var = $email_base->stripslashes_gpc($this->args[$prefs[$i]]);
						if($i == 0)
						{
							$temp_var = $email_base->html_quotes_encode($temp_var);
						}
						$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$temp_var);
					}
					else
					{
						switch($i)
						{
							case 1:
								$temp_var = 'Trash';
								break;
							case 2:
								$temp_var = 'Sent';
								break;
						}
						$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$temp_var);						
					}
				}
				// these are the "custom" email options, here handle both user-entered strings
				// and non user-entered string options in the same proc
				// also, the password is handled seperately below
				$prefs = Array(
					'use_custom_settings',
					'userid',
					'address',
					'mail_server',
					'mail_server_type',
					'imap_server_type',
					'mail_folder'
				);
				// NOTE: it is possible that a user-entered string, particularly the "mail_folder" pref
				// may contain certain chars, such as slashes, quotes, etc..., which (a)  may need to be
				// run through "stripslashes_gpc" and or (b) may be database-unfriendly chars
				// which *may* need to be encoded IF these bad chars are not escaped or otherwise de-fanged
				// at the preference class level or the database class level.
				// UNKNOWN at present (11-30-2001) if this is still an issue (it was in 0.9.12) ed: Angles
				$c_prefs = count($prefs);
				$GLOBALS['phpgw']->preferences->delete('email',$prefs[0]);
				if (!isset($this->args[$prefs[0]]))
				{
					// use is NOT using custom settings, so delete them all from the repository
					for($i=1;$i<$c_prefs;$i++)
					{
						$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
					}
					// and also the passwd, which is not in that array above because it gets special handling
					$GLOBALS['phpgw']->preferences->delete('email','passwd');
				}
				else
				{
					// custom prefs ARE in use
					$GLOBALS['phpgw']->preferences->add('email',$prefs[0],$this->args[$prefs[0]]);
					for($i=1;$i<$c_prefs;$i++)
					{
						// if ((isset($email_base->args[$check_array[$i]])) && ($email_base->args[$check_array[$i]] != ''))
						if ((isset($this->args[$prefs[$i]]))
						&& ($this->args[$prefs[$i]] != ''))
						{
							// user has specified a value for this particular email custom option
							$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$this->args[$prefs[$i]]);
						}
						else
						{
							// user did not supply a value for this particular custom option,
							// so the user wants to use the phpgwapi supplied value instead, 
							// We accomplished by entirely removing (no key, no value) this pref
							// from the repository, so next time function "create_email_preferences"
							// is called, it knows by the lack of the existence of a custom particular
							// custom option to use the server supplied default instead for that item
							$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
						}
					}
					if (isset($this->args['passwd'])
					&& $this->args['passwd'] != '')
					{
						//@capability: set and unset a custom email password preference
						//@discussion:  an email password is NEVER sent to the client UI from the server
						//so this option shows up as en empty value in the UI
						//These senarios are possible here:
						//(1) user submits an empty passwd pref AND user already has a custom passwd set
						//then the previous, existing users custom passwd is left UNMOLESTED, as-is, untouched.
						//(2) user does submit a password, then this gets "encrypted" (depends on existence of mcrypt or not)
						//and put in the repository.
						//This minimizes passwd from traveling thru the ether.
						//(3) user wants to delete an existing custom passwd from the repository,
						//the user must (a) uncheck "use custom preferences", and (b) submit that page,
						//which clears ALL custom options. Now if the user leter checks "use custom preferences"
						//but does NOT fill in a custom passwd, the user's phpgw login password will be used
						//as the email server password, following the concept that unfilled custom options
						//get a phpgw system default value.
						$GLOBALS['phpgw']->preferences->delete('email','passwd');
						$GLOBALS['phpgw']->preferences->add('email','passwd',$email_base->encrypt_email_passwd($email_base->stripslashes_gpc($this->args['passwd'])));
					}
				}
				if ($this->debug_set_prefs > 1) 
				{
					echo 'email.bopreferences: *debug* skipping save_repository<br>';
				}
				else
				{
					$GLOBALS['phpgw']->preferences->save_repository();
				}
				$email_base->end_request();
			}
			if ($this->debug_set_prefs > 1) 
			{
				echo 'email.bopreferences: *debug* skipping Header redirection<br>';
			}
			else
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
			}
			*/
		}
		
		
		/*!
		@function process_ex_account_submitted_prefs
		@abstract Extra Email Accounts Process incoming submitted prefs, process the data, and save to repository 
		@author	Angles
		@access	Private
		*/
		function process_ex_accounts_submitted_prefs($prefs_set='')
		{
			if ($this->debug_set_prefs > 0) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: ENTERING<br>'; }
			// basicly, copy and paste the real "process_submitted_prefs" and tweak for extra_accounts applicablility
			if(!$prefs_set)
			{
				$prefs_set=array();
			}
			$c_prefs = count($prefs_set);
			if ($c_prefs == 0)
			{
				if ($this->debug_set_prefs > 0) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: LEAVING, empty array, no prefs set supplied<br>'; }
				return False;
			}
			
			// ==== ACCTNUM ====
			if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: pre discovery $this->acctnum : ['.serialize($this->acctnum).']<br>'; }
			if ((!isset($this->acctnum))
			|| ((string)$this->acctnum == ''))
			{
				$this->acctnum = $this->obtain_ex_acctnum();
			}
			if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: post discovery $this->acctnum : ['.serialize($this->acctnum).']<br>'; }
			
			for($i=0;$i<$c_prefs;$i++)
			{
				if ($this->debug_set_prefs > 1) { echo ' <b>* (next loop) *</b> email: bopreferences: process_ex_accounts_submitted_prefs: inside preferences loop ['.$i.']<br>'; }
				
				$this_pref = $prefs_set[$i];
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: $this_pref = $prefs_set['.$i.'] : $this_pref DUMP:<pre>'; print_r($prefs_set[$i]); echo '</pre>'; }
				
				// ----  skip this item logic  ----
				// we are ONLY concerned with items that apply to the extra email accounts
				if (!stristr($this_pref['accts_usage'] , 'extra_accounts'))
				{
					// we are not supposed to handle this item for the extra email accounts, skip this pref item
					// continue is used within looping structures to skip the rest of the current loop 
					// iteration and continue execution at the beginning of the next iteration
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: _SKIP_ this item ["'.$this_pref['id'].'"], it does not apply to Extra Email Accounts <br>'; }
					continue;
				}
				
				// ---- ok, this item is relevant to extra email accounts  ----
				if ((!isset($this->args[$this->acctnum][$this_pref['id']]))
				|| (trim($this->args[$this->acctnum][$this_pref['id']]) == ''))
				{
					// nothing submitted for this preference item
					// OR an empty string was submitted for this pref item
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: submitted_pref for ["'.$this_pref['id'].'"] not set or empty string<br>'; }
					if (stristr($this_pref['write_props'], 'empty_no_delete'))
					{
						// DO NOT DELETE
						// "empty_no_delete" means keep the existing pref un-molested, as-is, no change
						// note there may or may not actually be an existing value in the prefs table
						// but it does not matter here, because we do not touch this items value at all.
						// Typical Usage: passwords
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: no change to repository for empty or blank ["'.$this_pref['id'].'"] because of "empty_no_delete"<br>'; }
					}
					elseif (stristr($this_pref['write_props'], 'empty_string_ok'))
					{
						// "empty_string_ok" means a blank string "" IS a VALID pref value
						// i.e. this pref can take an empty string as a valid value
						// whereas most other prefs are simply deleted from the repository if value is empty
						// Typical Usage: email sig, UWash Mail Folder
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences process_ex_accounts_submitted_prefs: save empty string to repository for ["'.$this_pref['id'].'"] because of "empty_string_ok"<br>'; }
						// a) as always, delete the pref before we assign a value
						$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']["'.$this_pref['id'].'"]';
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences process_ex_accounts_submitted_prefs: using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
						$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
						// b) now assign a blank string value
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: using preferences->add_struct("email", $pref_struct_str, \'\') which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
						$GLOBALS['phpgw']->preferences->add_struct('email',$pref_struct_str,'');
					}
					else
					{
						// just delete it from the preferences repository
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs deleting empty or blank pref ["'.$this_pref['id'].'"] from the repository<br>'; }
						$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']["'.$this_pref['id'].'"]';
						if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
						$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
					}
				}
				else
				{
					// ---  we have real data submitted for this preference item  ---
					$submitted_pref = $this->args[$this->acctnum][$this_pref['id']];
					// init a var to hold the processed submitted_pref
					$processed_pref = '';
					if ($this->debug_set_prefs > 1) { echo '* * email: bopreferences: process_ex_accounts_submitted_prefs:  submitted_pref: ['.$submitted_pref.']<br>'; }
					
					// most "user_string"s need special processing before they can go into the repository
					if ($this_pref['type'] == 'user_string')
					{
						if (stristr($this_pref['write_props'], 'no_db_defang'))
						{
							// typical "user_string" needs to strip any slashes 
							// that PHP "magic_quotes_gpc"may have added
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							// most "user_string" items require pre-processing before going into
							// the repository (strip slashes, html encode, encrypt, etc...)
							// we call this database "de-fanging", remove database unfriendly chars
							// currenty defanging is handled by "mail_msg_obj->html_quotes_encode"
							// EXCEPT when "no_db_defang" is in "write_props"
							$processed_pref = $submitted_pref;
						}
						elseif (stristr($this_pref['write_props'], 'encrypted'))
						{
							// certain data (passwords) should be encrypted before going into the repository
							// "user_string"s to be "encrypted" do NOT get "html_quotes_encode"
							// before going into the encryption routine
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							$processed_pref = $GLOBALS['phpgw']->msg->encrypt_email_passwd($processed_pref);
						}
						else
						{
							// typical "user_string" needs to strip any slashes 
							// that PHP "magic_quotes_gpc"may have added
							$processed_pref = $GLOBALS['phpgw']->msg->stripslashes_gpc($submitted_pref);
							// and this is a _LAME_ way to make the value "database friendly"
							// because slashes and quotes will FRY the whole preferences repository
							$processed_pref = $GLOBALS['phpgw']->msg->html_quotes_encode($processed_pref);
						}
					}
					else
					{
						// all other data needs no special processing before going into the repository
						$processed_pref = $submitted_pref;
					}
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: about to assign pref ["'.$this_pref['id'].'"] this value, post processing (if any): <pre>'.$GLOBALS['phpgw']->strip_html($processed_pref).'</pre><br>'."\r\n"; }
					
					// a) as always, delete the pref before we assign a value
					$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']["'.$this_pref['id'].'"]';
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences process_ex_accounts_submitted_prefs: using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
					$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
					// b) now assign that processed data to this pref item in the repository
					if ($this->debug_set_prefs > 1) { echo 'email: bopreferences: process_ex_accounts_submitted_prefs: using preferences->add_struct("email", $pref_struct_str, $processed_pref) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
					$GLOBALS['phpgw']->preferences->add_struct('email', $pref_struct_str, $processed_pref);
					// SORT THAT ARRAY by key, so the integer array heys go from lowest to hightest
					ksort($GLOBALS['phpgw']->preferences->data['email']['ex_accounts']);
				}
			}
			// since we apparently did process some prefs data, return True
			return True;
		}
		
		function ex_accounts_delete($acctnum='')
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_delete ENTERING feed acctnum: ['.serialize($acctnum).']<br>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.ex_accounts_delete: $GLOBALS[HTTP_POST_VARS] dump<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.ex_accounts_delete: $GLOBALS[HTTP_GET_VARS] dump<pre>'; print_r($GLOBALS['HTTP_GET_VARS']); echo '</pre>'; }
			
			$this->account_group = 'extra_accounts';
			
			if ((isset($acctnum))
			|| ((string)$acctnum != ''))
			{
				$this->acctnum = $acctnum;
			}
			
			if ((!isset($this->acctnum))
			|| ((string)$this->acctnum == ''))
			{
				$acctnum = $this->obtain_ex_acctnum();
				$this->acctnum = $acctnum;
			}
			
			$actually_did_something = False;
			if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_delete obtained acctnum ['.$this->acctnum.']<br>'; }
			
			if ((isset($this->acctnum))
			&& ((string)$this->acctnum != ''))
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_delete obtained VALID acctnum ['.$this->acctnum.'], proceed...<br>'; }
				
				// delete the extra account pref item
				$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']';
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences.ex_accounts_delete: using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
				
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences.ex_accounts_delete: $GLOBALS[phpgw]->preferences->data dump<pre>'; print_r($GLOBALS['phpgw']->preferences->data); echo '</pre>'; }
				// let the code below this block know we actually did something that requires saving the repository
				$actually_did_something = True;
			}
			
			// DONE with delete pref, SAVE to the Repository
			if (!$actually_did_something)
			{
				// nothing happened above that requires saving the repository
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_delete: nothing happened that requires save_repository, $actually_did_something='.serialize($actually_did_something).'<br>'; }
			}
			elseif ($this->debug_set_prefs > 2)
			{
				// we actually did something that requires saving repository, but are we in debug mode
				echo 'email.bopreferences.ex_accounts_delete: *debug* skipping save_repository<br>';
			}
			else
			{
				// we actually did something that requires saving repository, and we have the go-ahead
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			// end the email session
			if (is_object($GLOBALS['phpgw']->msg))
			{
				$GLOBALS['phpgw']->msg->end_request();
			}
			// redirect user back to main preferences page
			if ($this->debug_set_prefs > 2)
			{
				echo 'email.bopreferences.ex_accounts_delete: *debug* skipping Header redirection<br>';
			}
			else
			{
				$take_me_to_url = $GLOBALS['phpgw']->link(
											'/index.php',
											'menuaction=email.uipreferences.ex_accounts_list');
				
				if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_delete: LEAVING with redirect to: ['.$take_me_to_url.']<br>'; }
				Header('Location: ' . $take_me_to_url);
			}
		}
		
		/*
		@function ex_accounts_edit
		@abstract Extra Email Account Data process submitted prefs. It makes use of other class functions
		some of which should not be called directly, call this function in menuaction.
		@author	Angles
		@access	Public
		*/
		function ex_accounts_edit($acctnum='')
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_edit ENTERING <br>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.ex_accounts_edit: $GLOBALS[HTTP_POST_VARS] dump<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.ex_accounts_edit: $GLOBALS[HTTP_GET_VARS] dump<pre>'; print_r($GLOBALS['HTTP_GET_VARS']); echo '</pre>'; }
			
			// ==== ACCTNUM ====
			// this tells people that we are dealing with the extra email accounts
			$this->account_group = 'extra_accounts';
			if ((!isset($acctnum))
			|| ((string)$acctnum == ''))
			{
				$acctnum = $this->obtain_ex_acctnum();
				$this->acctnum = $acctnum;
			}
			else
			{
				$this->acctnum = $acctnum;
			}
			
			$actually_did_something = False;
			
			// --- Add/Modify Email Extra Account Prefs? ----
			
			// establish all available prefs for email
			$this->init_available_prefs();
			
			// this will fill $this->args[] array with any submitted prefs args
			$this->grab_set_prefs();
			
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_edit(): just passed this->grab_set_prefs<br>'; }
			
			// ----  HANDLE SETING PREFERENCE   -------
			if (isset($this->args[$this->submit_token_extra_accounts]))
			{
				// let the code below this block know we actually did something that requires saving the repository
				$actually_did_something = True;
				
				// is set_magic_quotes_runtime(0) done here or somewhere else
				//set_magic_quotes_runtime(0);
				
				// constructor will (has taken care of) initialize $GLOBALS['phpgw']->msg
				
				// ---  Process Standard Prefs  ---
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): about to process_ex_accounts_submitted_prefs Standard Prefs<br>'; }
				$this->process_ex_accounts_submitted_prefs($this->std_prefs);
				
				// ---  Process Custom Prefs  ---
				// CUSTOM PREFS ARE MANDATORY! FOR EXTRA ACCOUNTS
				// first, delete whatever value was there for "use custom settings" (during pre-release, at times this actually was an option, make sure it's gone grom the db)
				$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']["'.$this->cust_prefs[0]['id'].'"]';
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences.ex_accounts_edit(): "use_custom_settings" pref, delete it, reference it by ["ex_accounts"][$this->acctnum]["$this->cust_prefs[0][id]"]<br>'; }
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences.ex_accounts_edit(): using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
				$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);

				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): about to process_ex_accounts_submitted_prefs Custom Prefs, which are MANDATORY for extra email accounts<br>'; }
				$this->process_ex_accounts_submitted_prefs($this->cust_prefs);
				
				/*
				// ---  Process Custom Prefs  ---
				// if they were not mandatory, but that does not work
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): about to process Custom Prefs<br>'; }
				if (isset($this->args['use_custom_settings']))
				{
					// custom settings are in use, process them
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): custom prefs are in use<br>'; }
					$this->process_ex_accounts_submitted_prefs($this->cust_prefs);
				}
				else
				{
					// custom settings are NOT being used, DELETE them from the repository
					$c_prefs = count($this->cust_prefs);			
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): custom prefs NOT in use, deleting them<br>'; }
					for($i=0;$i<$c_prefs;$i++)
					{
						$pref_struct_str = '["ex_accounts"]['.$this->acctnum.']["'.$this->cust_prefs[$i]['id'].'"]';
						if ($this->debug_set_prefs > 1) { echo ' ** (looping) email: bopreferences.ex_accounts_edit(): using preferences->delete_struct("email", $pref_struct_str) which will eval $pref_struct_str='.$pref_struct_str.'<br>'; }
						$GLOBALS['phpgw']->preferences->delete_struct('email',$pref_struct_str);
					}
				}
				*/
				
				if ($this->debug_set_prefs > 1) { echo 'email: bopreferences.ex_accounts_edit: $GLOBALS[phpgw]->preferences->data dump<pre>'; print_r($GLOBALS['phpgw']->preferences->data); echo '</pre>'; }
			}
				
			// DONE processing prefs, SAVE to the Repository
			if (!$actually_did_something)
			{
				// nothing happened above that requires saving the repository
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_edit(): nothing happened that requires save_repository, $actually_did_something='.serialize($actually_did_something).'<br>'; }
			}
			elseif ($this->debug_set_prefs > 1) 
			{
				// we actually did something that requires saving repository, but are we in debug mode
				echo 'email.bopreferences.ex_accounts_edit(): *debug* at ['.$this->debug_set_prefs.'] so skipping save_repository<br>';
			}
			else
			{
				// we actually did something that requires saving repository, and we have the go-ahead
				if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_edit(): SAVING REPOSITORY<br>'; }
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			
			// end the email session
			if (is_object($GLOBALS['phpgw']->msg))
			{
				$GLOBALS['phpgw']->msg->end_request();
			}
			
			// redirect user back to main preferences page
			$take_me_to_url = $GLOBALS['phpgw']->link(
										'/index.php',
										'menuaction=email.uipreferences.ex_accounts_list');
			
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_edit(): almost LEAVING, about to issue a redirect to:<br>'.$take_me_to_url.'<br>'; }
			if ($this->debug_set_prefs > 1) 
			{
				echo 'email.bopreferences.ex_accounts_edit(): LEAVING, *debug* at ['.$this->debug_set_prefs.'] so skipping Header redirection to: ['.$take_me_to_url.']<br>';
			}
			else
			{
				if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_edit: LEAVING with redirect to: <br>'.$take_me_to_url.'<br>'; }
				Header('Location: ' . $take_me_to_url);
			}
		}

		/*
		@function ex_accounts_list
		@abstract list Extra Email Accounts with links to edit and or delete them.
		@author	Angles
		@access	Public
		*/
		function ex_accounts_list()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_list: ENTERING<br>'; }
			
			// list accounts, except "empty" ones (show "enabled" and "disabled"
			$return_list = array();
			$loops = count($GLOBALS['phpgw']->msg->extra_accounts);
			for($i=0; $i < $loops; $i++)
			{
				$this_acctnum = $GLOBALS['phpgw']->msg->extra_accounts[$i]['acctnum'];
				$this_status = $GLOBALS['phpgw']->msg->extra_accounts[$i]['status'];
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: $GLOBALS[phpgw]->msg->extra_accounts['.$i.'][acctnum]=['.$this_acctnum.'] ;  [status]=['.$this->extra_accounts[$i]['status'].'] <br>'; }
				if ($this_status == 'empty')
				{
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: $GLOBALS[phpgw]->msg->extra_accounts['.$i.'][status] == empty <br>'; }
				}
				else
				{
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: $GLOBALS[phpgw]->msg->extra_accounts['.$i.'][status] != empty <br>'; }
					$next_pos = count($return_list);
					//$next_pos = $this_acctnum - 1;
					$return_list[$next_pos]['acctnum'] = $this_acctnum;
					$return_list[$next_pos]['status'] = $this_status;
					if ($this_status == 'disabled')
					{
						// "disabled" accounts will not return a fullname because they were not initialized during "begin_request"
						// try to directly obtain it from RAW prefs data
						$fullname = '(disabled) '.$GLOBALS['phpgw']->msg->unprocessed_prefs['email']['ex_accounts'][$this_acctnum]['fullname'];
						// we can not read mail of a disabled account
						$return_list[$next_pos]['go_there_url'] = '';
						$return_list[$next_pos]['go_there_href'] = '&nbsp;';
					}
					else
					{
						$fullname = $GLOBALS['phpgw']->msg->get_pref_value('fullname', $this_acctnum);
						$return_list[$next_pos]['go_there_url'] = $GLOBALS['phpgw']->link(
														'/index.php',
														 'menuaction=email.uiindex.index'
														.'&fldball[folder]=INBOX'
														.'&fldball[acctnum]='.$this_acctnum);
						$return_list[$next_pos]['go_there_href'] = '<a href="'.$return_list[$next_pos]['go_there_url'].'">'.lang('go').'</a>';
					}
					// html encode entities on the fullname so it's safe to display in the browser, and prefix with the acctnum
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: fullname raw: <code>'.serialize($fullname).'</code><br>'; }
					$fullname = $GLOBALS['phpgw']->msg->htmlspecialchars_decode($fullname);
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: fullname B: <code>'.serialize($fullname).'</code><br>'; }
					$fullname = $GLOBALS['phpgw']->msg->htmlspecialchars_encode($fullname);
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.ex_accounts_list: fullname C: <code>'.serialize($fullname).'</code><br>'; }
					//$return_list[$next_pos]['display_string'] = '['.$this_acctnum.'] '.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($fullname);
					$return_list[$next_pos]['display_string'] = '['.$this_acctnum.'] '.$fullname;
					// control action links
					$return_list[$next_pos]['edit_url'] = $GLOBALS['phpgw']->link(
														'/index.php',
														 'menuaction=email.uipreferences.ex_accounts_edit'
														.'&ex_acctnum='.$this_acctnum);
					$return_list[$next_pos]['edit_href'] = '<a href="'.$return_list[$next_pos]['edit_url'].'">'.lang('Edit').'</a>';

					$return_list[$next_pos]['delete_url'] = $GLOBALS['phpgw']->link(
														'/index.php',
														 'menuaction=email.bopreferences.ex_accounts_delete'
														.'&ex_acctnum='.$this_acctnum);
					$return_list[$next_pos]['delete_href'] = '<a href="'.$return_list[$next_pos]['delete_url'].'">'.lang('Delete').'</a>';
				}
			}
			if ($this->debug_set_prefs > 2) { echo 'email.bopreferences.ex_accounts_list: returning $return_list[] : <pre>'; print_r($return_list); echo '</pre>'; }
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.ex_accounts_list: LEAVING, returning $return_list <br>'; }
			return $return_list;
		}
		
		/*
		@function get_first_empty_ex_acctnum
		@abstract Used in adding a new extra account, obtains a free acctnum
		@author	Angles
		@access	Public
		*/
		function get_first_empty_ex_acctnum()
		{
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.get_first_empty_ex_acctnum: ENTERING<br>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.get_first_empty_ex_acctnum: $GLOBALS[phpgw]->msg->extra_accounts dump<pre>'; print_r($GLOBALS['phpgw']->msg->extra_accounts); echo '</pre>'; }
			$loops = count($GLOBALS['phpgw']->msg->extra_accounts);
			if ($loops == 0)
			{
				if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.get_first_empty_ex_acctnum: count($GLOBALS[phpgw]->msg->extra_accounts =['.serialize(count($GLOBALS['phpgw']->msg->extra_accounts)).']<br>'; }
				$first_empty_ex_acctnum = 1;
			}
			else
			{
				$did_get_acctnum = False;
				for($i=0; $i < $loops; $i++)
				{
					$this_acctnum = $GLOBALS['phpgw']->msg->extra_accounts[$i]['acctnum'];
					$this_status = $GLOBALS['phpgw']->msg->extra_accounts[$i]['status'];
					// loop =0 *would* = acctnum 1 *if* acctnum slots are filled in order, they'd always be 1 apart
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.get_first_empty_ex_acctnum: in loop ['.$i.'] : status: ['.$this_status.'] ; acctnum: ['.$this_acctnum.']<br>'; }
					if ($this_status == 'empty')
					{
						if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.get_first_empty_ex_acctnum: [status] == empty for acctnum ['.$this_acctnum.']<br>'; }
						$first_empty_ex_acctnum = (int)$this_acctnum;
						$did_get_acctnum = True;
						break;
					}
					elseif ((int)($i+1) != (int)$this_acctnum)
					{
						$first_empty_ex_acctnum = (int)($i+1);
						if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.get_first_empty_ex_acctnum: slots have an empty spot, unused $acctnum is ['.$first_empty_ex_acctnum.']<br>'; }
						$did_get_acctnum = True;
						break;
					}
				}
				if ($did_get_acctnum == False)
				{
					// all slots taken, add +1 to last filled acctnum
					$first_empty_ex_acctnum = count($GLOBALS['phpgw']->msg->extra_accounts);
					// since extra accounts are not zero based, add one to that count to get real next available
					$first_empty_ex_acctnum++;
					if ($this->debug_set_prefs > 1) { echo 'email.bopreferences.get_first_empty_ex_acctnum: no empty spaces extra_accounts[], advance to next int: $first_empty_ex_acctnum ['.$first_empty_ex_acctnum.']<br>'; }
				}
			}
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.get_first_empty_ex_acctnum: LEAVING, returning $first_empty_ex_acctnum ['.serialize($first_empty_ex_acctnum).']<br>'; }
			return $first_empty_ex_acctnum;
		}
		
		/*
		@function obtain_ex_acctnum
		@abstract Preferences handlers pass around the acctnum as POST or GET var "ex_acctnum".
		@author	Angles
		@access	Public
		*/
		function obtain_ex_acctnum()
		{
			if ($this->debug_set_prefs > 0) { echo 'email: bopreferences.obtain_ex_acctnum: ENTERING<br>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.obtain_ex_acctnum: $GLOBALS[HTTP_POST_VARS] dump<pre>'; print_r($GLOBALS['HTTP_POST_VARS']); echo '</pre>'; }
			if ($this->debug_set_prefs > 2) { echo 'email: bopreferences.obtain_ex_acctnum: $GLOBALS[HTTP_GET_VARS] dump<pre>'; print_r($GLOBALS['HTTP_GET_VARS']); echo '</pre>'; }
			// get fromPOST or GET
			$prelim_acctnum = '##NOTHING##';
			if ((isset($GLOBALS['HTTP_POST_VARS']['ex_acctnum'])
			&& ((string)$GLOBALS['HTTP_POST_VARS']['ex_acctnum'] != '')))
			{
				$prelim_acctnum = (int)$GLOBALS['HTTP_POST_VARS']['ex_acctnum'];
			}
			elseif ((isset($GLOBALS['HTTP_GET_VARS']['ex_acctnum'])
			&& ((string)$GLOBALS['HTTP_GET_VARS']['ex_acctnum'] != '')))
			{
				$prelim_acctnum = (int)$GLOBALS['HTTP_GET_VARS']['ex_acctnum'];
			}
			// in all these cases we don't have a valid acct num (or we are asked to make a new one)
			// so any of these requires a new, blank acctnum
			// NOTE: EXTRA ACCOUNTS CAN NEVER HAVE ACCNUM 0
			if ( (!isset($prelim_acctnum))
			|| ($prelim_acctnum == $this->add_new_account_token)
			|| ($prelim_acctnum == '##NOTHING##')
			|| ((string)$prelim_acctnum == '')
			|| ((string)$prelim_acctnum == '0') )
			{
				// get the next blank acctnum
				$final_acctnum = $this->get_first_empty_ex_acctnum();
			}
			else
			{
				$final_acctnum = $prelim_acctnum;
			}
			if ($this->debug_set_prefs > 0) { echo 'email.bopreferences.obtain_ex_acctnum: LEAVING, returning $final_acctnum: ['.serialize($final_acctnum).'] <br>'; }
			return $final_acctnum;
		}


	}
?>