<?php
	/**************************************************************************\
	* phpGroupWare - Messenger                                                 *
	* http://www.phpgroupware.org                                              *
	* This application written by Joseph Engo <jengo@phpgroupware.org>         *
	* --------------------------------------------                             *
	* Funding for this program was provided by http://www.checkwithmom.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class uimessage
	{
		var $db;
		var $template;
		var $public_functions = array(
					'inbox'          => True,
					'compose'        => True,
					'compose_global' => True,
					'read_message'   => True,
					'reply'          => True,
					'forward'        => True
				);

		function uimessage()
		{
			global $phpgw;
			$this->template = $phpgw->template;
			$this->db       = $phpgw->db;
		}

		function display_headers($extras = '')
		{
			global $phpgw;

			$this->template->set_file(array(
				'_header' => 'header.tpl'
			));
			$this->template->set_block('_header','global_header');
			$this->template->set_var('lang_inbox','<a href="' . $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.inbox') . '">' . lang('Inbox') . '</a>');
			$this->template->set_var('lang_compose','<a href="' . $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.compose') . '">' . lang('Compose') . '</a>');

			if ($extras['nextmatchs_left'])
			{
				$this->template->set_var('nextmatchs_left',$extras['nextmatchs_left']);
			}

			if ($extras['nextmatchs_right'])
			{
				$this->template->set_var('nextmatchs_right',$extras['nextmatchs_right']);
			}

			$this->template->fp('app_header','global_header');

			$phpgw->common->phpgw_header();
			echo parse_navbar();
		}

		function set_common_langs()
		{
			global $phpgw_info;

			$this->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
			$this->template->set_var('row_on',$phpgw_info['theme']['row_on']);
			$this->template->set_var('row_off',$phpgw_info['theme']['row_off']);

			$this->template->set_var('lang_to',lang('Send message to'));
			$this->template->set_var('lang_from',lang('Message from'));
			$this->template->set_var('lang_subject',lang('Subject'));
			$this->template->set_var('lang_content',lang('Message'));
			$this->template->set_var('lang_date',lang('Date'));
		}

		function inbox()
		{
			global $phpgw, $start, $order;
			$bo         = createobject('messenger.bomessage');
			$so         = createobject('messenger.somessage');
			$nextmatchs = createobject('phpgwapi.nextmatchs');
			$total      = $so->total_messages();

			$extra_menuaction = '&menuaction=messenger.uimessage.inbox';
			$extra_header_info['nextmatchs_left']  = $nextmatchs->left('/messenger/main.php',$start,$total,$extra_menuaction);
			$extra_header_info['nextmatchs_right'] = $nextmatchs->right('/messenger/main.php',$start,$total,$extra_menuaction);

			$this->display_headers($extra_header_info);

			$this->template->set_file(array(
				'_inbox' => 'inbox.tpl'
			));
			$this->template->set_block('_inbox','list');
			$this->template->set_block('_inbox','row');
			$this->template->set_block('_inbox','row_empty');

			$this->set_common_langs();

			$this->template->set_var('header_checkbox','<input type="checkbox">');

			$messages = $bo->read_inbox($start,$order);

			while (is_array($messages) && list(,$message) = each($messages))
			{
				$this->template->set_var('row_status',$message['status']);
				$this->template->set_var('row_from',$message['from']);
				$this->template->set_var('row_date',$message['date']);
				$this->template->set_var('row_subject','<a href="' . $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.read_message&message_id=' . $message['id']) . '">' . $message['subject'] . '</a>');
				$this->template->set_var('row_status',$message['status']);
				$this->template->set_var('row_checkbox','<input type="checkbox" name="messages[]" value="' . $message['id'] . '">');

				$this->template->fp('rows','row',True);
			}

			if (! is_array($messages))
			{
				$this->template->set_var('lang_empty',lang('You have no messages'));
				$this->template->fp('rows','row_empty',True);				
			}
			else
			{
				$this->template->set_var('form_action',$phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.delete_message'));
				$this->template->set_var('button_delete','<input type="image" src="' . PHPGW_IMAGES . '/delete.gif" name="delete" title="' . lang('Delete selected') . '" border="0">');
			}

			$this->template->pfp('out','list');
		}

		function set_compose_read_blocks()
		{
			$this->template->set_file(array(
				'_form' => 'form.tpl'
			));

			$this->template->set_block('_form','form');
			$this->template->set_block('_form','form_to');
			$this->template->set_block('_form','form_date');
			$this->template->set_block('_form','form_from');
			$this->template->set_block('_form','form_buttons');
			$this->template->set_block('_form','form_read_buttons');
			$this->template->set_block('_form','form_read_buttons_for_global');
		}

		function compose_global($errors = '')
		{
			global $phpgw, $message;

			if (! $phpgw->acl->check('run',1,'admin'))
			{
				$this->inbox();
				return False;
			}

			$this->display_headers();
			$this->set_compose_read_blocks();

			if (is_array($errors))
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			$this->set_common_langs();
			$this->template->set_var('header_message',lang('Compose global message'));

			$this->template->set_var('form_action',$phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.send_global_message'));
			$this->template->set_var('value_subject','<input name="message[subject]" value="' . $message['subject'] . '">');
			$this->template->set_var('value_content','<textarea name="message[content]" rows="20" wrap="hard" cols="76">' . $message['content'] . '</textarea>');

			$this->template->set_var('button_send','<input type="submit" name="send" value="' . lang('Send') . '">');
			$this->template->set_var('button_cancel','<input type="submit" name="cancel" value="' . lang('Cancel') . '">');

			$this->template->fp('buttons','form_buttons');
			$this->template->pfp('out','form');
		}

		function compose($errors = '')
		{
			global $phpgw, $message;

			$this->display_headers();
			$this->set_compose_read_blocks();

			if (is_array($errors))
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			$this->set_common_langs();
			$this->template->set_var('header_message',lang('Compose message'));

			$this->template->set_var('form_action',$phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.send_message'));
			$this->template->set_var('value_to','<input name="message[to]" value="' . $message['to'] . '" size="30">');
			$this->template->set_var('value_subject','<input name="message[subject]" value="' . $message['subject'] . '" size="30">');
			$this->template->set_var('value_content','<textarea name="message[content]" rows="20" wrap="hard" cols="76">' . $message['content'] . '</textarea>');

			$this->template->set_var('button_send','<input type="submit" name="send" value="' . lang('Send') . '">');
			$this->template->set_var('button_cancel','<input type="submit" name="cancel" value="' . lang('Cancel') . '">');

			$this->template->fp('to','form_to');
			$this->template->fp('buttons','form_buttons');
			$this->template->pfp('out','form');
		}

		function read_message()
		{
			global $phpgw, $message_id;

			$bo = createobject('messenger.bomessage');
			$message = $bo->read_message($message_id);

			$this->display_headers();
			$this->set_compose_read_blocks();
			$this->set_common_langs();

			$this->template->set_var('header_message',lang('Read message'));

			$this->template->set_var('value_from',$message['from']);
			$this->template->set_var('value_subject',$message['subject']);
			$this->template->set_var('value_date',$message['date']);
			$this->template->set_var('value_content','<pre>' . $phpgw->strip_html($message['content']) . '</pre>');

			$this->template->set_var('link_delete','<a href="'
					. $phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.delete_message&messages%5B%5D=' . $message['id'])
					. '">' . lang('Delete') . '</a>');

			$this->template->set_var('link_reply','<a href="'
					. $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.reply&message_id=' . $message['id'])
					. '">' . lang('Reply') . '</a>');

			$this->template->set_var('link_forward','<a href="'
					. $phpgw->link('/messenger/main.php','menuaction=messenger.uimessage.forward&message_id=' . $message['id'])
					. '">' . lang('Forward') . '</a>');

			switch($message['status'])
			{
				case 'N': $this->template->set_var('value_status',lang('New'));		break;
				case 'R': $this->template->set_var('value_status',lang('Replied'));	break;
				case 'F': $this->template->set_var('value_status',lang('Forwarded'));	break;
			}

			if ($message['global_message'])
			{
				$this->template->fp('read_buttons','form_read_buttons_for_global');
			}
			else
			{
				$this->template->fp('read_buttons','form_read_buttons');			
			}

			$this->template->fp('date','form_date');
			$this->template->fp('from','form_from');
			$this->template->pfp('out','form');
		}

		function reply($errors = '', $message = '')
		{
			global $phpgw, $message_id;

			$bo = createobject('messenger.bomessage');

			if (! $message)
			{
				$message = $bo->read_message_for_reply($message_id,'RE');
			}

			$this->display_headers();
			$this->set_compose_read_blocks();
			$this->set_common_langs();

			if (is_array($errors))
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			$this->template->set_var('header_message',lang('Reply to a message'));

			$this->template->set_var('form_action',$phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.reply&message_id=' . $message['id']));
			$this->template->set_var('value_to','<input name="n_message[to]" value="' . $message['from'] . '" size="30">');
			$this->template->set_var('value_subject','<input name="n_message[subject]" value="' . stripslashes($message['subject']) . '" size="30">');
			$this->template->set_var('value_content','<textarea name="n_message[content]" rows="20" wrap="hard" cols="76">' . stripslashes($message['content']) . '</textarea>');

			$this->template->set_var('button_send','<input type="submit" name="send" value="' . lang('Send') . '">');
			$this->template->set_var('button_cancel','<input type="submit" name="cancel" value="' . lang('Cancel') . '">');

			$this->template->fp('to','form_to');
			$this->template->fp('buttons','form_buttons');
			$this->template->pfp('out','form');
		}

		function forward($errors = '', $message = '')
		{
			global $phpgw, $message_id;

			$bo = createobject('messenger.bomessage');

			if (! $message)
			{
				$message = $bo->read_message_for_reply($message_id,'FW');
			}

			$this->display_headers();
			$this->set_compose_read_blocks();
			$this->set_common_langs();

			if (is_array($errors))
			{
				$this->template->set_var('errors',$phpgw->common->error_list($errors));
			}

			$this->template->set_var('header_message',lang('Forward a message'));

			$this->template->set_var('form_action',$phpgw->link('/messenger/main.php','menuaction=messenger.bomessage.forward&message_id=' . $message['id']));
			$this->template->set_var('value_to','<input name="n_message[to]" value="' . $message['from'] . '" size="30">');
			$this->template->set_var('value_subject','<input name="n_message[subject]" value="' . stripslashes($message['subject']) . '" size="30">');
			$this->template->set_var('value_content','<textarea name="n_message[content]" rows="20" wrap="hard" cols="76">' . stripslashes($message['content']) . '</textarea>');

			$this->template->set_var('button_send','<input type="submit" name="send" value="' . lang('Send') . '">');
			$this->template->set_var('button_cancel','<input type="submit" name="cancel" value="' . lang('Cancel') . '">');

			$this->template->fp('to','form_to');
			$this->template->fp('buttons','form_buttons');
			$this->template->pfp('out','form');
		}

	
	}

