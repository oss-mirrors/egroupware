<?php
	/**************************************************************************\
	* AngleMail - E-Mail Module for phpGroupWare					*
	* http://www.anglemail.org									*
	* http://www.phpgroupware.org									* 
	*/
	/**************************************************************************\
	* AngleMail - E-Mail Debug Page								*
	* This file written by "Angles" Angelo Puglisi <angles@aminvestments.com>	*
	* Debug Utility Functions and Information and Document Access			*
	* Copyright (C) 2002 Angelo Tony Puglisi (Angles)					*
	* ------------------------------------------------------------------------ 		*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.											*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of	*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License	*
	* along with this library; if not, write to the Free Software Foundation,		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/
	
	/* $Id$ */
	
	/*!
	@class ui_mail_debug
	@abstract Useful debug and inline docs access page.
	@discussion Uncomment the "public_functions" line to enable the Email Debug Page.  
	Should be disabled by default, this is a developers tool.
	@author Angles
	*/	
	class ui_mail_debug
	{
		/**************************************************************************\
		*	VARS
		\**************************************************************************/
		
		/*!
		@capability Debug Page
		@discussion Uncomment the next line of code to enable the Email Debug Page. 
		This is file email / class.ui_mail_debug.inc.php
		*/
		// UNCOMMENT TO ENABLE THIS PAGE
		//var $public_functions = array('index'	=> True);
		var $widgets;
		var $debug=0;
		//var $debug=1;
		
		/**************************************************************************\
		*	CONSTRUCTOR
		\**************************************************************************/
		function ui_mail_debug()
		{
			if ($this->debug > 0) { echo 'ENTERING: email.ui_mail_debug.CONSTRUCTOR'.'<br>'."\r\n"; }
			
			$this->widgets = CreateObject("email.html_widgets");
			
			if ($this->debug > 0) { echo 'EXIT: email.ui_mail_debug.CONSTRUCTOR'.'<br>'."\r\n"; }
		}
		
		/*!
		@function invoke_bootatrap
		@abstract convience function to bootstrap msg object
		@discussion in debugging we may not have or want a ->msg object, but if we do 
		need one, like now we need it just to get the GPC vars (or change the code here to _GET), 
		or just make -> msg object an use ->ref_GET or whatever else you need it for
		@author Angles
		*/
		function invoke_bootatrap()
		{
			if ($this->debug > 0) { echo 'ENTERING: email.ui_mail_debug.invoke_bootatrap'.'<br>'; }
			// make sure we have msg object and a server stream
			$this->msg_bootstrap = CreateObject("email.msg_bootstrap");
			// FIX ME: do_login False when using msg for UTILITY, does that still work?
			//$this->msg_bootstrap->set_do_login(False);
			$this->msg_bootstrap->ensure_mail_msg_exists('emai.ui_mail_debug.invoke_bootatrap', $this->debug);		
			if ($this->debug > 0) { echo 'EXITing: email.ui_mail_debug.invoke_bootatrap'.'<br>'; }
		}
		
		/*!
		@function end_msg_session_object
		@abstract convience function to logout and then clear and unset the msg object, if it exists
		@discussion checks for its existance before trying any of this
		@author Angles
		*/
		function end_msg_session_object()
		{
			if ($this->debug > 0) { echo 'ENTERING: email.ui_mail_debug.end_msg_session_object'.'<br>'; }
			// kill this script, we re outa here...
			if (is_object($GLOBALS['phpgw']->msg))
			{
				$GLOBALS['phpgw']->msg->end_request();
				$GLOBALS['phpgw']->msg = '';
				unset($GLOBALS['phpgw']->msg);
			}
			// WHEN do we need to call phpgw_exit now with updated phpgw API?
			//$GLOBALS['phpgw']->common->phpgw_exit(False);
			if ($this->debug > 0) { echo 'EXITing: email.ui_mail_debug.end_msg_session_object'.'<br>'; }
		}
		
		/**************************************************************************\
		*	CODE
		\**************************************************************************/
		/*!
		@function index
		@abstract This page is displayed by exposing this as a public function then calling it .
		@discussion Uncomment the "public_functions" line to enable the Email Debug Page.  
		Should be disabled by default, this is a developers tool. If enabled, call this function to 
		display the page.
		@example /index.php?menuaction=email.ui_mail_debug.index
		@author Angles
		*/	
		function index()
		{
			if ($this->debug > 0) { echo 'ENTERING: email.ui_mail_debug.index'.'<br>'; }
			
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();
			
			$GLOBALS['phpgw']->template->set_file(array(
				'T_debug_main' => 'debug.tpl'
			));
			$GLOBALS['phpgw']->template->set_block('T_debug_main','B_before_echo','V_before_echo');
			$GLOBALS['phpgw']->template->set_block('T_debug_main','B_after_echo','V_after_echo');
			
			
			$GLOBALS['phpgw']->template->set_var('page_desc', 'Email Debug Stuff');
			
			// make a list of available debub calls
			// Enviornment data
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=phpinfo'));
			//$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('phpinfo page');
			$GLOBALS['phpgw']->template->set_var('func_E1', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=get_defined_constants'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('get_defined_constants DUMP');
			$GLOBALS['phpgw']->template->set_var('func_E2', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=globals_dump'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('dump the entire globals[] array');
			$GLOBALS['phpgw']->template->set_var('func_E3', $this->widgets->get_href());
			
			// DUMP functions
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=common.debug_list_core_functions'));
			$this->widgets->set_href_clickme('common.debug_list_core_functions');
			$GLOBALS['phpgw']->template->set_var('func_D1', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=globals_phpgw_dump'));
			$this->widgets->set_href_clickme('dump the entire globals[phpgw] structure');
			$GLOBALS['phpgw']->template->set_var('func_D2', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=globals_phpgw_info_dump'));
			$this->widgets->set_href_clickme('dump the entire globals[phpgw_info] structure');
			$GLOBALS['phpgw']->template->set_var('func_D3', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=globals_phpgw_session_dump'));
			$this->widgets->set_href_clickme('dump the entire globals[phpgw_session] structure');
			$GLOBALS['phpgw']->template->set_var('func_D4', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=msg_object_dump'));
			$this->widgets->set_href_clickme('dump the entire globals[phpgw]->msg object');
			$GLOBALS['phpgw']->template->set_var('func_D5', $this->widgets->get_href());
			
			// inline docs
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=phpgwapi'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for phpgwapi');			
			$GLOBALS['phpgw']->template->set_var('func_I1', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=phpwebhosting'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for phpwebhosing VFS');
			$GLOBALS['phpgw']->template->set_var('func_I2', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=email'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for email');
			$GLOBALS['phpgw']->template->set_var('func_I3', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=email&fn=class.mail_msg_base.inc.php'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for email, file "class.mail_msg_base.inc.php"');
			$GLOBALS['phpgw']->template->set_var('func_I4', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=email&fn=class.mail_msg_display.inc.php'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for email, file "class.mail_msg_display.inc.php"');
			$GLOBALS['phpgw']->template->set_var('func_I5', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=email&fn=class.mail_msg_wrappers.inc.php'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for email, file "class.mail_msg_wrappers.inc.php"');
			$GLOBALS['phpgw']->template->set_var('func_I6', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/doc/inlinedocparser.php?app=email&fn=class.mail_dcom_imap_sock.inc.php'));
			$this->widgets->set_href_target('new');
			$this->widgets->set_href_clickme('inlinedocparser for email, file "class.mail_dcom_imap_sock.inc.php"');
			$GLOBALS['phpgw']->template->set_var('func_I7', $this->widgets->get_href());
			
			// other stuff
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=copyinteresting'));
			$this->widgets->set_href_clickme('copy emails in BOB interesting to Local folder (no workie)');
			$GLOBALS['phpgw']->template->set_var('func_O1', $this->widgets->get_href());
			
			$this->widgets->set_href_link($GLOBALS['phpgw']->link('/index.php','menuaction=email.ui_mail_debug.index&dfunc=env_test'));
			$this->widgets->set_href_clickme('utility for testing env code parts');
			$GLOBALS['phpgw']->template->set_var('func_O2', $this->widgets->get_href());
			
			$GLOBALS['phpgw']->template->parse('V_before_echo','B_before_echo');
			$GLOBALS['phpgw']->template->pfp('out','T_debug_main');
			
			// IF we need to show debug data, now is the time
			$this->show_desired_data();
			
			
			if ($this->debug > 0) { echo 'EXITing...: email.ui_mail_debug.index'.'<br>'; }
			
			// clear the previous tpl var and fill the ending one
			$GLOBALS['phpgw']->template->set_var('V_before_echo','');
			$GLOBALS['phpgw']->template->parse('V_after_echo','B_after_echo');
			$GLOBALS['phpgw']->template->pfp('out','T_debug_main');
		}
		
		function show_desired_data()
		{
			// DAMN, we need a ->msg just to do the ref_GET stuff
			$this->invoke_bootatrap();
			
			if ((isset($GLOBALS['phpgw']->msg->ref_GET['dfunc']))
			&& ($GLOBALS['phpgw']->msg->ref_GET['dfunc'] != ''))
			{
				$desired_function = $GLOBALS['phpgw']->msg->ref_GET['dfunc'];
				echo "You requested: ".$desired_function.'<br>'."\r\n";
			}
			else
			{
				echo "no desired data";
				return;
			}
			
			// check against a list of available debug stuff
			if ($desired_function == 'phpinfo')
			{
				phpinfo();
			}
			elseif ($desired_function == 'get_defined_constants')
			{
				// this function echos out its data
				echo 'get_defined_constants DUMP:<pre>';
				print_r(get_defined_constants());
				echo '</pre>';
			}
			elseif ($desired_function == 'globals_dump')
			{
				// this function echos out its data
				echo 'GLOBALS[] array dump:<pre>';
				print_r($GLOBALS) ;
				echo '</pre>';

			}
			elseif ($desired_function == 'common.debug_list_core_functions')
			{
				// this function echos out its data, has its own pre tags in its output
				$GLOBALS['phpgw']->common->debug_list_core_functions();
			}
			elseif ($desired_function == 'globals_phpgw_dump')
			{
				// this function echos out its data
				echo 'GLOBALS[phpgw] dump:<pre>';
				print_r($GLOBALS['phpgw']) ;
				echo '</pre>';

			}
			elseif ($desired_function == 'globals_phpgw_info_dump')
			{
				// this function echos out its data
				echo 'GLOBALS[phpgw_info] dump:<pre>';
				print_r($GLOBALS['phpgw_info']) ;
				echo '</pre>';
			}
			elseif ($desired_function == 'globals_phpgw_session_dump')
			{
				// this function echos out its data
				echo 'GLOBALS[phpgw_session] dump:<pre>';
				print_r($GLOBALS['phpgw_session']) ;
				echo '</pre>';
			}
			elseif ($desired_function == 'msg_object_dump')
			{
				// this function echos out its data
				echo 'GLOBALS[phpgw]->msg dump:<pre>';
				print_r($GLOBALS['phpgw']->msg) ;
				echo '</pre>';
			}
			elseif ($desired_function == 'copyinteresting')
			{
				$this->copyinteresting();
			}
			elseif ($desired_function == 'env_test')
			{
				$this->env_test();
			}
			else
			{
				echo 'unknown desired debug request: "'.$desired_function.'"<br>';
			}
			
			// DAMN, since we invoked bootstrap above, we should kill the msg session
			// BUT WILL WE NEED IT AGAIN?
			// php does not have a definitive destructor, so we have to guess where script will end
			echo 'emai.ui_mail_debug. line '.__LINE__.': calling "end_msg_session_object" so I hope you do not need it anymore<br>';
			$this->end_msg_session_object();
		}	
		
		
		function copyinteresting()
		{
			// this function echos out its data
			echo 'This will copy from devel mail account folder "Phpgw Interesting" to Brick sysmail folder "Interesting Emails"<br><br>'."\r\n";
			// FROM: &fldball[folder]=INBOX.Phpgw+Interesting&fldball[acctnum]=1
			// TO: &fldball[folder]=mail%2FInteresting+Emails&fldball[acctnum]=3
			
			
			// begin TYPICAL CLASS MSG INITALIZATION ROUTINE
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug > 0) { echo 'emai.ui_mail_debug.copyinteresting: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug > 0) { echo 'emai.ui_mail_debug.copyinteresting: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			// for EXTERNAL CONTROL of msg class (i.e. not a result of  GET POST) it seems very important
			// to specify the account number and folder in the args array
			// acctnum is expected to be an integer
			$my_acctnum = 1;
			// it is customary to feed the folder name in the style of a URL encoded name, ex. SPACE is represented as a PLUS, etc...
			$my_folder = urlencode("INBOX.Phpgw Interesting");
			
			$args_array = Array();
			$args_array['acctnum']  = $my_acctnum;
			$args_array['folder'] = $my_folder;
			$args_array['do_login'] = True;
			
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', copyinteresting()');
			}
			// end TYPICAL CLASS MSG INITALIZATION ROUTINE
			
			
			// function get_msgball_list($acctnum='', $folder='')
			//not necessary and is discouraged to actually provide any args to get_msgball_list()
			// instead, a well done begin request opens the desired accftnum folder and get_msgball_list uses that info.
			$my_from_list = $GLOBALS['phpgw']->msg->get_msgball_list();
			echo 'Msgball List for account number ['.$my_acctnum.'] folder name ['.$my_folder.']:<pre>';
			print_r($my_from_list) ;
			echo '</pre>';
			
			$GLOBALS['phpgw']->msg->end_request();
		}
		
		function env_test()
		{
			$expected_args = 
				'/mail/index_php?menuaction'.','.
				'fldball'.','.
				'msgball'.','.
				'td'.','.
				'tm'.','.
				'tf'.','.
				'sort'.','.
				'order'.','.
				'start';
			
			echo '$expected_args ['.$expected_args.']<br>';
			/*
			$exploded_expected_args = array();
			$exploded_expected_args = explode(',',$expected_args);
			if (2 > 1) { echo '$exploded_expected_args DUMP:<pre>'; print_r($exploded_expected_args); echo '</pre>'; } 
			$expected_args = array();
			$loops = count($exploded_expected_args);
			for ($i = 0; $i < $loops; $i++)
			{
				$arg_name = $exploded_expected_args[$i];
				$expected_args[$arg_name] = '-1';
			}
			if (2 > 1) { echo '$expected_args DUMP:<pre>'; print_r($expected_args); echo '</pre>'; } 
			
			// make sure we have msg object and a server stream
			$this->msg_bootstrap = CreateObject("email.msg_bootstrap");
			$this->msg_bootstrap->set_do_login(False);
			$this->msg_bootstrap->ensure_mail_msg_exists('emai.ui_mail_debug.env_test', 1);
			
			if (2 > 1) { echo '$GLOBALS[phpgw]->msg->known_external_args DUMP:<pre>'; print_r($GLOBALS['phpgw']->msg->known_external_args); echo '</pre>'; } 
			*/
			// make sure we have msg object and a server stream
			$this->msg_bootstrap = CreateObject("email.msg_bootstrap");
			$this->msg_bootstrap->set_do_login(False);
			$this->msg_bootstrap->ensure_mail_msg_exists('emai.ui_mail_debug.env_test', 3);
			$boaction_obj = CreateObject('email.boaction');
			// test run thru the functions
			$boaction_obj->set_expected_args($expected_args);
			// the URI of the redirect string contains data needed for the next page view
			
			//$redirect_to = '/mail/index_php?menuaction=email.uiindex.index&fldball[folder]=INBOX&fldball[acctnum]=4&sort=1&order=1&start=0';
			$redirect_to = '/mail/index_php?menuaction=email.uimessage.message&msgball[msgnum]=102&msgball[folder]=INBOX&msgball[acctnum]=4&sort=1&order=1&start=0';

			$boaction_obj->set_new_args_uri($redirect_to);
			// clear existing args, apply the new arg enviornment, 
			// we get back the menuaction the redirect would have asked for
			$my_menuaction = $boaction_obj->apply_new_args_env();
			echo 'returned $my_menuaction ['.$my_menuaction.']<br>';
			
			$GLOBALS['phpgw']->msg->end_request();
		}
	
	}
?>
