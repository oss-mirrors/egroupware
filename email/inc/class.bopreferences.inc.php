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
			// setting prefs does not require a login, in fact you may not be able to login until you set
			// some basic prefs, so it makes sence to handle that here
			if (isset($GLOBALS['HTTP_POST_VARS']['submit_prefs']))
			{
				$email_base = CreateObject('email.mail_msg_base');
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
				$prefs = Array(
					'use_custom_settings',
					'userid',
					'address',
					'mail_server',
					'mail_server_type',
					'imap_server_type',
					'mail_folder'
				);
				$c_prefs = count($prefs);
				$GLOBALS['phpgw']->preferences->delete('email',$prefs[0]);
				if (!isset($GLOBALS['HTTP_POST_VARS'][$prefs[0]]))
				{
					for($i=1;$i<$c_prefs;$i++)
					{
						$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
					}
					$GLOBALS['phpgw']->preferences->delete('email','passwd');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('email',$prefs[0],$GLOBALS['HTTP_POST_VARS'][$prefs[0]]);
					for($i=1;$i<$c_prefs;$i++)
					{
						if ((isset($GLOBALS['phpgw']->msg->args[$check_array[$i]]))
						&& ($GLOBALS['phpgw']->msg->args[$check_array[$i]] != ''))
						{
							$GLOBALS['phpgw']->preferences->add('email',$prefs[$i],$GLOBALS['HTTP_POST_VARS'][$prefs[$i]]);
						}
						else
						{
							// so we'll use phpgwapi supplied value instead
							$GLOBALS['phpgw']->preferences->delete('email',$prefs[$i]);
						}
					}
					if (isset($GLOBALS['HTTP_POST_VARS']['passwd'])
					&& $GLOBALS['HTTP_POST_VARS']['passwd'] != '')
					{
						$GLOBALS['phpgw']->preferences->delete('email','passwd');
						$GLOBALS['phpgw']->preferences->add('email','passwd',$email_base->encrypt_email_passwd($email_base->stripslashes_gpc($GLOBALS['HTTP_POST_VARS']['passwd'])));
					}
				}
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			Header('Location: ' . $GLOBALS['phpgw']->link('/preferences/index.php'));
		}
	}
