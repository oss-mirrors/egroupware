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
		);

		function bopreferences()
		{
		}

		function preferences()
		{
			// ----  HANDLE SET PREFERENCE GPC ARGS  -------
			if (isset($GLOBALS['HTTP_POST_VARS']['submit_prefs']))
			{
				//$debug_set_prefs = True;
				$debug_set_prefs = False;
				
				// is set_magic_quotes_runtime(0) done here or somewhere else
				//set_magic_quotes_runtime(0);
				/*
				@capability: initialize class mail_msg object but do not login
				@discussion: we need mail_msg fully initialized to set prefs, but we
				do not need class_dcom, nor do we need to login, this is how to do it:
				1) create the mail_msg object
				2) grap preference GPC HTTP_POST_VARS args (DEPRECIATED)
				note: grabbing and filling necessary class args is done before "begin_request"
				This puts all VALID (i.e. known) submitted HTTP_POST_VARS prefs
				into EmailObject->args[] array, from there you can read them
				However, this capability has been depreciated as of 11-30-2001
				Use the raw HTTP_POST_VARS instead. (ed: Angles)
				3) initialize simple "begin_request" args array holder variable
				setting prefs does not require a login, in fact you may not yet be able to login
				"do_login" = False is the only "request_args_array" element we need to set
				4) begin the class mail_msg transaction request
				even though we are not logging in, the will initialize the class
				5) set the prefs
				6) call "end_request"
				*/
				$email_base = CreateObject("email.mail_msg");
				$email_base->grab_set_prefs_args_gpc();
				$request_args = Array();
				$request_args['do_login'] = False;
				$email_base->begin_request($request_args);
				
				//if ($debug_set_prefs) {
				//	echo 'grab_set_prefs_args_gpc data dump:<br>' ;
				//	var_dump($email_base->args);
				//	echo '<br><br>';
				//}
				
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
					if (isset($GLOBALS['HTTP_POST_VARS'][$prefs[$i]])
						&& $GLOBALS['HTTP_POST_VARS'][$prefs[$i]] != '')
					{
						$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$GLOBALS['HTTP_POST_VARS'][$prefs[$i]]);
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
					if(isset($GLOBALS['HTTP_POST_VARS'][$prefs[$i]]))
					{
						$temp_var = $email_base->stripslashes_gpc($GLOBALS['HTTP_POST_VARS'][$prefs[$i]]);
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
				if (!isset($GLOBALS['HTTP_POST_VARS'][$prefs[0]]))
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
					$GLOBALS['phpgw']->preferences->add('email',$prefs[0],$GLOBALS['HTTP_POST_VARS'][$prefs[0]]);
					for($i=1;$i<$c_prefs;$i++)
					{
						// if ((isset($email_base->args[$check_array[$i]])) && ($email_base->args[$check_array[$i]] != ''))
						if ((isset($GLOBALS['HTTP_POST_VARS'][$prefs[$i]]))
						&& ($GLOBALS['HTTP_POST_VARS'][$prefs[$i]] != ''))
						{
							// user has specified a value for this particular email custom option
							$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$GLOBALS['HTTP_POST_VARS'][$prefs[$i]]);
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
					if (isset($GLOBALS['HTTP_POST_VARS']['passwd'])
					&& $GLOBALS['HTTP_POST_VARS']['passwd'] != '')
					{
						/*
						@capability: set and unset a custom email password preference
						@discussion:  an email password is NEVER sent to the client UI from the server
						so this option shows up as en empty value in the UI
						These senarios are possible here:
						(1) user submits an empty passwd pref AND user already has a custom passwd set
						then the previous, existing users custom passwd is left UNMOLESTED, as-is, untouched.
						(2) user does submit a password, then this gets "encrypted" (depends on existence of mcrypt or not)
						and put in the repository.
						This minimizes passwd from traveling thru the ether.
						(3) user wants to delete an existing custom passwd from the repository,
						the user must (a) uncheck "use custom preferences", and (b) submit that page,
						which clears ALL custom options. Now if the user leter checks "use custom preferences"
						but does NOT fill in a custom passwd, the user's phpgw login password will be used
						as the email server password, following the concept that unfilled custom options
						get a phpgw system default value.
						*/
						$GLOBALS['phpgw']->preferences->delete('email','passwd');
						$GLOBALS['phpgw']->preferences->add('email','passwd',$email_base->encrypt_email_passwd($email_base->stripslashes_gpc($GLOBALS['HTTP_POST_VARS']['passwd'])));
					}
				}
				$GLOBALS['phpgw']->preferences->save_repository();
				$email_base->end_request();
			}
			Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
		}
	}
