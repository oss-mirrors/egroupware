<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$phpgw_info["flags"] = array(
		'currentapp' => 'email',
		'noheader'                => True, 
		'nonavbar'                => True, 
		'enable_nextmatchs_class' => True);

	include("../header.inc.php");

	// ----  Save Preferences to Repository  (if this is a submit)  -----
	if ($submit)
	{
		$phpgw->preferences->read_repository();

		// ----  Typical (Non-Custom) Preferences   -----

		$phpgw->preferences->delete("email","show_addresses");
		if ($show_addresses)
		{
			$phpgw->preferences->add("email","show_addresses");
		}
		
		$phpgw->preferences->delete("email","mainscreen_showmail");
		if ($mainscreen_showmail)
		{
			$phpgw->preferences->add("email","mainscreen_showmail");
		}

		$phpgw->preferences->delete("email","use_trash_folder");
		if ($use_trash_folder)
		{
			$phpgw->preferences->add("email","use_trash_folder");
		}

		$phpgw->preferences->add("email","default_sorting");

		/* email sig must not have  '  nor  "  in it, as they screw up the preferences in class session
		    not an sql error, but the core bug lies somewhere in session caching */
		if ($email_sig != '')
		{
			/* get rid of the escape \ that magic_quotes HTTP POST will add, " becomes \" and  '  becomes  \'  */
			$email_sig_clean = $phpgw->msg->stripslashes_gpc($email_sig);
			/*// replace database offensive ASCII chars  with htmlspecialchars */
			$email_sig_clean = $phpgw->msg->html_quotes_encode($email_sig_clean);
			$phpgw->preferences->add("email","email_sig",$email_sig_clean);
		}
		else
		{
			// have it set, but be empty
			$phpgw->preferences->add("email","email_sig");
		}

		// ----  Custom Preferences   -----
		// differ from account defaults set by administrator, should be unset if not using custom prefs
		$phpgw->preferences->delete("email","use_custom_settings");
		if (! $use_custom_settings)
		{
			$phpgw->preferences->delete("email","userid");
			$phpgw->preferences->delete("email","passwd");
			$phpgw->preferences->delete("email","address");
			$phpgw->preferences->delete("email","mail_server");
			$phpgw->preferences->delete("email","mail_server_type");
			$phpgw->preferences->delete("email","imap_server_type");
			$phpgw->preferences->delete("email","mail_folder");
		}
		else
		{
			$phpgw->preferences->add("email","use_custom_settings");
			if ($userid)
			{
				$phpgw->preferences->add("email","userid");
			}
			else
			{
				// should probably be an error message here
				$phpgw->preferences->delete("email","userid");
			}
			if ($passwd)
			{
				// there were multiple problems with previous custom email passwd handling
				// fixed so far:
				// - database unfriendly ASCII chars are converted to/from html special chars
				// - bypass $phpgw->common->en/decrypt()  which added extra serializations
				// - upgrade routine makes double or tripple serialized passwords back to normal

				//echo 'in pref page b4 strip: <pre>'.$passwd.'</pre><br>';
				$encrypted_passwd = $phpgw->msg->stripslashes_gpc($passwd);
				//echo 'in pref page after strip: <pre>'.$encrypted_passwd.'</pre><br>';
				$encrypted_passwd = $phpgw->msg->encrypt_email_passwd($encrypted_passwd);
				//echo 'encrypted_passwd: <pre>'.$encrypted_passwd.'</pre><br>';
				$phpgw->preferences->add("email","passwd",$encrypted_passwd);
				//echo '<br>just saved to prefs table, all ok?<br><br>';
				//echo 'convert back to actual ASCII char <pre>'
				//	.$phpgw->msg->decrypt_email_passwd($encrypted_passwd).'</pre>';
			}
			else
			{
				// is not specified, LEAVE PASSWD ALONE, retain previous setting
			}
			if ($address)
			{
				$phpgw->preferences->add("email","address");
			}
			else
			{
				// should probably be an error message here
				$phpgw->preferences->delete("email","address");
			}
			if ($mail_server)
			{
				$phpgw->preferences->add("email","mail_server");
			}
			else
			{
				// should probably be an error message here
				$phpgw->preferences->delete("email","mail_server");
			}
			if ($mail_server_type)
			{
				$phpgw->preferences->add("email","mail_server_type");
			}
			else
			{
				// should probably be an error message here
				$phpgw->preferences->delete("email","mail_server_type");
			}
			if ($imap_server_type)
			{
				$phpgw->preferences->add("email","imap_server_type");
			}
			else
			{
				// if ( (mail_server_type=='imap') || (mail_server_type=='imaps') ) then
				// should probably be an error message here
				$phpgw->preferences->delete("email","imap_server_type");
			}
			if ($mail_folder) 
			{
				$phpgw->preferences->add("email","mail_folder");
			}
			else
			{
				// if (imap_server_type=='UW-Maildir')  then
				// should probably be an error message here
				$phpgw->preferences->delete("email","mail_folder");
			}
		}
		$phpgw->preferences->save_repository();

		Header("Location: " . $phpgw->link("/preferences/index.php"));
	}

// ----  Show The Preferences Page   -----
	$phpgw->common->phpgw_header();
	echo parse_navbar();

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_preferences_out' => 'preferences.tpl'
	));

	if ($totalerrors)
	{
		//echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
		$pref_errors = '<p><center>"' .$phpgw->common->error_list($errors) .'"</center></p>';
	}
	else
	{
		$pref_errors = '';
	}

	$t->set_var('pref_errors',$pref_errors);
	$t->set_var('page_title',lang("E-Mail preferences"));

	// setup the form
	$t->set_var('form_action',$phpgw->link('/email/preferences.php'));
	// the "table header" row color
	$t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);

// ----  Typical (Non-Custom) Settings - Fill in HTML form -----
	// row1 = Email Sig
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row1',$tr_color);
	$t->set_var('email_sig_blurb',lang("email signature"));
	$t->set_var('email_sig_textarea_name','email_sig');
	//$t->set_var('email_sig_textarea_content',rawurldecode($phpgw_info["user"]["preferences"]["email"]["email_sig"]));
	$t->set_var('email_sig_textarea_content',$phpgw_info["user"]["preferences"]["email"]["email_sig"]);

	// row2 = Sort Order 
	// old_new means "lowest to highest", and new_old means "highest to lowest", which is imap-speak for reverse sorting
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$default_order_selected[$phpgw_info["user"]["preferences"]["email"]["default_sorting"]] = " selected";
	$sorting_select_options =
		 '<option value="old_new"' .$default_order_selected["old_new"] .'>oldest -> newest</option>' ."\n"
		.'<option value="new_old"' .$default_order_selected["new_old"] .'>newest -> oldest</option>' ."\n";
	$t->set_var('bg_row2',$tr_color);
	$t->set_var('sorting_blurb',lang("Default sorting order"));
	$t->set_var('sorting_select_name','default_sorting');
	$t->set_var('sorting_select_options',$sorting_select_options);

	// row3 = show sender's email address with name options
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$show_addresses_selected[$phpgw_info["user"]["preferences"]["email"]["show_addresses"]] = " selected";
	$show_addresses_select_options =
		 '<option value="none"' .$show_addresses_selected["none"] .'>' .lang('none') .'</option>' ."\n"
		.'<option value="from"' .$show_addresses_selected["from"] .'>' .lang('From') .'</option>' ."\n"
		.'<option value="replyto"' .$show_addresses_selected["replyto"] .'>' .lang('ReplyTo') .'</option>' ."\n";
	$t->set_var('bg_row3',$tr_color);
	$t->set_var('show_addresses_blurb',lang("Show sender's email address with name"));
	$t->set_var('show_addresses_select_name','show_addresses');
	$t->set_var('show_addresses_select_options',$show_addresses_select_options);

	// row4 = show new messages on  main screen
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"])
	{
		$mainscreen_showmail_checked = 'checked';
	}
	else
	{
		$mainscreen_showmail_checked = '';
	}
	$t->set_var('bg_row4',$tr_color);
	$t->set_var('mainscreen_showmail_blurb',lang("show new messages on main screen"));
	$t->set_var('mainscreen_showmail_checkbox_name','mainscreen_showmail');
	$t->set_var('mainscreen_showmail_checkbox_value','True');
	$t->set_var('mainscreen_showmail_checked',$mainscreen_showmail_checked);

	// row5 = Send deleted messages to the trash
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	if ($phpgw_info["user"]["preferences"]["email"]["use_trash_folder"])
	{
		$use_trash_folder_checked = 'checked';
	}
	else
	{
		$use_trash_folder_checked = '';
	}
	$t->set_var('bg_row5',$tr_color);
	$t->set_var('use_trash_folder_blurb',lang("Send deleted messages to the trash"));
	$t->set_var('use_trash_folder_checkbox_name','use_trash_folder');
	$t->set_var('use_trash_folder_checkbox_value','True');
	$t->set_var('use_trash_folder_checked',$use_trash_folder_checked);

	// next section: Custom Email Settings
	$t->set_var('section_title',lang("Custom Email settings"));

// ----  Custom Settings - Fill in HTML form -----
	// row6 = use custon settings
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	if ($phpgw_info["user"]["preferences"]["email"]["use_custom_settings"])
	{
		$use_custom_settings_checked = 'checked';
	}
	else
	{
		$use_custom_settings_checked = '';
	}
	$t->set_var('bg_row6',$tr_color);
	$t->set_var('use_custom_settings_blurb',lang("Use custom settings") .' - ' .lang("Non-Standard"));
	$t->set_var('use_custom_settings_checkbox_name','use_custom_settings');
	$t->set_var('use_custom_settings_checkbox_value','True');
	$t->set_var('use_custom_settings_checked',$use_custom_settings_checked);

	// row7 = Email Account Name
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row7',$tr_color);
	$t->set_var('userid_blurb',lang("Email Account Name"));
	$t->set_var('userid_text_name','userid');
	$t->set_var('userid_text_value',$phpgw_info["user"]["preferences"]["email"]["userid"]);

	// row8 = Email Password
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row8',$tr_color);
	$t->set_var('passwd_blurb',lang("Email Password"));
	$t->set_var('passwd_text_name','passwd');
	// FIXME: bug
	$t->set_var('passwd_text_value','');

	// row9 = Email Address
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row9',$tr_color);
	$t->set_var('address_blurb',lang("Email address"));
	$t->set_var('address_text_name','address');
	$t->set_var('address_text_value',$phpgw_info["user"]["preferences"]["email"]["address"]);

	// row10 = Mail Server
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row10',$tr_color);
	$t->set_var('mail_server_blurb',lang("Mail Server"));
	$t->set_var('mail_server_text_name','mail_server');
	$t->set_var('mail_server_text_value',$phpgw_info["user"]["preferences"]["email"]["mail_server"]);

	// row11 = Mail Server type
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$mail_server_type_selected[$phpgw_info["user"]["preferences"]["email"]["mail_server_type"]] = " selected";
	$mail_server_type_select_options =
		 '<option value="imap"' .$mail_server_type_selected["imap"] .'>IMAP</option>' ."\n"
		.'<option value="pop3"' .$mail_server_type_selected["pop3"] .'>POP-3</option>' ."\n"
		.'<option value="imaps"' .$mail_server_type_selected["imaps"] .'>IMAPS</option>' ."\n"
		.'<option value="pop3s"' .$mail_server_type_selected["pop3s"] .'>POP-3S</option>' ."\n";
	$t->set_var('bg_row11',$tr_color);
	$t->set_var('mail_server_type_blurb',lang("Mail Server type"));
	$t->set_var('mail_server_type_select_name','mail_server_type');
	$t->set_var('mail_server_type_select_options',$mail_server_type_select_options);

	// row12 = IMAP Server Type
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$imap_server_type_selected[$phpgw_info["user"]["preferences"]["email"]["imap_server_type"]] = " selected";
	$imap_server_type_select_options =
		 '<option value="Cyrus"' .$imap_server_type_selected["Cyrus"] .'>Cyrus or Courier</option>' ."\n"
		.'<option value="UWash"' .$imap_server_type_selected["UWash"] .'>UWash</option>' ."\n"
		.'<option value="UW-Maildir"' .$imap_server_type_selected["UW-Maildir"] .'>UW-Maildir</option>' ."\n";
	$t->set_var('bg_row12',$tr_color);
	$t->set_var('imap_server_type_blurb',lang("IMAP Server Type") .' - ' .lang("If Applicable"));
	$t->set_var('imap_server_type_select_name','imap_server_type');
	$t->set_var('imap_server_type_select_options',$imap_server_type_select_options);

	// row13 = Mail Folder(UW-Maildir)
	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('bg_row13',$tr_color);
	$t->set_var('mail_folder_blurb',lang("Mail Folder(UW-Maildir)"));
	$t->set_var('mail_folder_text_name','mail_folder');
	$t->set_var('mail_folder_text_value',$phpgw_info["user"]["preferences"]["email"]["mail_folder"]);

	// the submit button for the form 
	$t->set_var('btn_submit_name','submit');
	$t->set_var('btn_submit_value',lang("submit"));

	$t->pparse('out','T_preferences_out');

	$phpgw->common->phpgw_footer();
?>
