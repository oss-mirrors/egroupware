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

	class uipreferences
	{
		var $public_functions = array(
			'preferences' => True,
		);

		var $bo;
		var $nextmatchs;
		var $template;
		var $theme;
		var $prefs;

		function uipreferences()
		{
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->template = $GLOBALS['phpgw']->template;
			$this->theme = $GLOBALS['phpgw_info']['theme'];
//			$this->bo = CreateObject('email.bopreferences');
			$temp_prefs = $GLOBALS['phpgw']->preferences->create_email_preferences();
			$this->prefs = $temp_prefs['email'];
		}

		function preferences()
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			$GLOBALS['phpgw']->common->phpgw_header();

			$this->template->set_file(
				Array(
					'T_preferences_out' => 'preferences.tpl'
				)
			);

			$var = Array(
				'pref_errors'	=> '',
				'page_title'	=> lang('E-Mail preferences'),
				'form_action'	=> $GLOBALS['phpgw']->link('/index.php',
					Array(
						'menuaction'	=> 'email.bopreferences.preferences'
					)
				),
				'th_bg'	=> $this->theme['th_bg']
			);
			$this->template->set_var($var);

			// ----  Typical (Non-Custom) Settings - Fill in HTML form -----
			// row1 = Email Sig
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row1'	=> $tr_color,
				'email_sig_blurb'	=> lang('email signature'),
				'email_sig_textarea_name'	=> 'email_sig',
//				'email_sig_textarea_content'	=> rawurldecode($this->prefs['email_sig']),
				'email_sig_textarea_content'	=> $this->prefs['email_sig']
			);
			$this->template->set_var($var);

			// row2 = Sort Order 
			// old_new means "lowest to highest", and new_old means "highest to lowest", which is imap-speak for reverse sorting
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$lang_oldest = lang('oldest');
			$lang_newest = lang('newest');
			$default_order_selected[$this->prefs['default_sorting']] = ' selected';
			$var = Array(
				'bg_row2'	=> $tr_color,
				'sorting_blurb'	=> lang('Default sorting order'),
				'sorting_select_name'	=> 'default_sorting',
				'sorting_select_options'	=>
					'<option value="old_new"' .$default_order_selected['old_new'] .'>'.$lang_oldest.' -> '.$lang_newest.'</option>' ."\n"
					.'<option value="new_old"' .$default_order_selected['new_old'] .'>'.$lang_newest.' -> '.$lang_oldest.'</option>' ."\n"
			);
			$this->template->set_var($var);

			// row2A = "Layout" loads different template (.tpl) files depending on the users choice, 1=default layout
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$layout_selected[1] = '';
			$layout_selected[2] = '';
			$layout_selected[$this->prefs['layout']] = ' selected';
			$var = Array(
				'bg_row2A'	=> $tr_color,
				'layout_blurb'	=> lang('Message List Layout'),
				'layout_select_name'	=> 'layout',
				'layout_select_options'	=>
					'<option value="1"' .$layout_selected[1] .'>' .lang('Layout 1') .'</option>' ."\r\n"
					.'<option value="2"' .$layout_selected[2] .'>' .lang('Layout 2') .'</option>' ."\r\n"
			);
			$this->template->set_var($var);

			// row3 = show sender's email address with name options
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$show_addresses_selected['none'] = '';
			$show_addresses_selected['from'] = '';
			$show_addresses_selected['replyto'] = '';
			$show_addresses_selected[$this->prefs['show_addresses']] = ' selected';
			$var = Array(
				'bg_row3'	=> $tr_color,
				'show_addresses_blurb'	=>	lang('Show sender\'s email address with name'),
				'show_addresses_select_name'	=> 'show_addresses',
				'show_addresses_select_options'	=> 
					 '<option value="none"' .$show_addresses_selected['none'] .'>' .lang('none') .'</option>' ."\r\n"
					.'<option value="from"' .$show_addresses_selected['from'] .'>' .lang('From') .'</option>' ."\r\n"
					.'<option value="replyto"' .$show_addresses_selected['replyto'] .'>' .lang('ReplyTo') .'</option>' ."\r\n"
			);
			$this->template->set_var($var);

			// row4 = show new messages on  main screen
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			if (isset($this->prefs['mainscreen_showmail']))
			{
				$mainscreen_showmail_checked = 'checked';
			}
			else
			{
				$mainscreen_showmail_checked = '';
			}
			$var = Array(
				'bg_row4'	=> $tr_color,
				'mainscreen_showmail_blurb'	=> lang('show new messages on main screen'),
				'mainscreen_showmail_checkbox_name'	=> 'mainscreen_showmail',
				'mainscreen_showmail_checkbox_value'	=> 'True',
				'mainscreen_showmail_checked'	=> $mainscreen_showmail_checked
			);
			$this->template->set_var($var);

			// row5 = TRASH folder options
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			if (isset($this->prefs['use_trash_folder']))
			{
				$use_trash_folder_checked = 'checked';
			}
			else
			{
				$use_trash_folder_checked = '';
			}
			if (!isset($this->prefs['trash_folder_name']))
			{
				$trash_folder = 'Trash';
			}
			else
			{
				$trash_folder = $this->prefs['trash_folder_name'];
			}

			$var = Array(
				'bg_row5'	=> $tr_color,
//				'use_trash_folder_blurb'	=> lang('Send deleted messages to the trash'),
				'use_trash_folder_blurb'	=> lang('Deleted messages saved to folder:'),
				'use_trash_folder_checkbox_name'	=> 'use_trash_folder',
				'use_trash_folder_checkbox_value'	=> 'True',
				'use_trash_folder_checked'	=> $use_trash_folder_checked,
				'trashname_text_name'	=> 'trash_folder_name',
				'trashname_text_value'	=> $trash_folder
			);
			$this->template->set_var($var);

			// row5A = SENT folder options
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			if (isset($this->prefs['use_sent_folder']))
			{
				$use_sent_folder_checked = 'checked';
			}
			else
			{
				$use_sent_folder_checked = '';
			}
			if (!isset($this->prefs['sent_folder_name']))
			{
				$sent_folder = 'Sent';
			}
			else
			{
				$sent_folder = $this->prefs['sent_folder_name'];
			}
			$var = Array(
				'bg_row5A'	=> $tr_color,
//				'use_sent_folder_blurb'	=> lang('Send deleted messages to the sent'),
				'use_sent_folder_blurb'	=> lang('Sent messages saved to folder:'),
				'use_sent_folder_checkbox_name'	=> 'use_sent_folder',
				'use_sent_folder_checkbox_value'	=> 'True',
				'use_sent_folder_checked'	=> $use_sent_folder_checked,
				'sentname_text_name'	=> 'sent_folder_name',
				'sentname_text_value'	=> $sent_folder
			);
			$this->template->set_var($var);

			// row5B = enable UTF-7 translation
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			if (isset($this->prefs['enable_utf7']))
			{
				$enable_utf7_checked = 'checked';
			}
			else
			{
				$enable_utf7_checked = '';
			}
			$var = Array(
				'bg_row5B'	=> $tr_color,
				'enable_utf7_blurb'	=> lang('enable UTF-7 encoded folder names'),
				'enable_utf7_checkbox_name'	=> 'enable_utf7',
				'enable_utf7_checkbox_value'	=> 'True',
				'enable_utf7_checked'	=> $enable_utf7_checked
			);
			$this->template->set_var($var);

			// next section: Custom Email Settings
			// ----  Custom Settings - Fill in HTML form -----
			// row6 = use custon settings
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			if (isset($this->prefs['use_custom_settings']))
			{
				$use_custom_settings_checked = 'checked';
			}
			else
			{
				$use_custom_settings_checked = '';
			}
			$var = Array(
				'section_title'	=> lang('Custom Email settings'),
				'bg_row6'	=> $tr_color,
				'use_custom_settings_blurb'	=> lang('Use custom settings') .' - ' .lang('Non-Standard'),
				'use_custom_settings_checkbox_name'	=> 'use_custom_settings',
				'use_custom_settings_checkbox_value'	=> 'True',
				'use_custom_settings_checked'	=> $use_custom_settings_checked
			);
			$this->template->set_var($var);


			// row7 = Email Account Name
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row7'	=> $tr_color,
				'userid_blurb'	=> lang('Email Account Name'),
				'userid_text_name'	=> 'userid',
				'userid_sys_default'	=> 'FIX_ME',
				'userid_text_value'	=> $this->prefs['userid']
			);
			$this->template->set_var($var);

			// row8 = Email Password
			// NOTE: any email custom password here will NOT be sent TO the browser
			// if user enters a value, it will be sent FROM the browser only
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row8'	=> $tr_color,
				'passwd_blurb'	=> lang('Email Password'),
				'passwd_text_name'	=> 'passwd',
				'passwd_text_value'	=> ''
			);
			$this->template->set_var($var);

			// row9 = Email Address
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row9'	=> $tr_color,
				'address_blurb'	=> lang('Email address'),
				'address_text_name'	=> 'address',
				'address_text_value'	=> $this->prefs['address']
			);
			$this->template->set_var($var);

			// row10 = Email Server
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row10'	=> $tr_color,
				'mail_server_blurb'	=> lang('Mail Server'),
				'mail_server_text_name'	=> 'mail_server',
				'mail_server_text_value'	=> $this->prefs['mail_server']
			);
			$this->template->set_var($var);

			// row11 = Email Server Type
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$mail_server_type_selected['imap'] = '';
			$mail_server_type_selected['pop3'] = '';
			$mail_server_type_selected['imaps'] = '';
			$mail_server_type_selected['pop3s'] = '';
			//$mail_server_type_selected['nntp'] = '';
			$mail_server_type_selected[$this->prefs['mail_server_type']] = ' selected';
			$var = Array(
				'bg_row11'	=> $tr_color,
				'mail_server_type_blurb'	=> lang('Mail Server type'),
				'mail_server_type_select_name'	=> 'mail_server_type',
				'mail_server_type_select_options'	=>
					'<option value="imap"' .$mail_server_type_selected['imap'] .'>IMAP</option>' ."\r\n"
					.'<option value="pop3"' .$mail_server_type_selected['pop3'] .'>POP-3</option>' ."\r\n"
					.'<option value="imaps"' .$mail_server_type_selected['imaps'] .'>IMAPS</option>' ."\r\n"
					.'<option value="pop3s"' .$mail_server_type_selected['pop3s'] .'>POP-3S</option>' ."\r\n"
			);
			$this->template->set_var($var);

			// row12 = IMAP Server Type
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$imap_server_type_selected['Cyrus'] = '';
			$imap_server_type_selected['UWash'] = '';
			$imap_server_type_selected['UW-Maildir'] = '';
			$imap_server_type_selected[$this->prefs['imap_server_type']] = ' selected';
			$var = Array(
				'bg_row12'	=> $tr_color,
				'imap_server_type_blurb'	=> lang('IMAP Server Type') .' - ' .lang('If Applicable'),
				'imap_server_type_select_name'	=> 'imap_server_type',
				'imap_server_type_select_options'	=>
					 '<option value="Cyrus"' .$imap_server_type_selected['Cyrus'] .'>Cyrus or Courier</option>' ."\r\n"
					.'<option value="UWash"' .$imap_server_type_selected['UWash'] .'>UWash</option>' ."\r\n"
					.'<option value="UW-Maildir"' .$imap_server_type_selected['UW-Maildir'] .'>UW-Maildir</option>' ."\r\n"
			);
			$this->template->set_var($var);

			// row13 = Mail Folder(UW-Maildir)
			$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
			$var = Array(
				'bg_row13'	=> $tr_color,
				'mail_folder_blurb'	=> lang('U-Wash Mail Folder').' - ' .lang('If Applicable'),
				'mail_folder_text_name'	=> 'mail_folder',
				'mail_folder_text_value'	=> $this->prefs['mail_folder']
			);
			$this->template->set_var($var);

			// the submit button for the form
			$this->template->set_var('btn_submit_name','submit_prefs');
			$this->template->set_var('btn_submit_value',lang('submit'));

			$this->template->pparse('out','T_preferences_out');
		}
	}
?>
