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

	class bomessage
	{
		var $db;
		var $template;
		var $public_functions = array(
					'delete_message'      => True,
					'send_message'        => True,
					'send_global_message' => True,
					'reply'               => True,
					'forward'             => True
				);

		function bomessage()
		{
			global $phpgw;
			$this->template = $phpgw->template;
			$this->db       = $phpgw->db;
		}

		function send_global_message()
		{
			global $message, $send, $cancel, $phpgw;

			$ui = createobject('messenger.uimessage');
			if (! $phpgw->acl->check('run',1,'admin') || $cancel)
			{
				$ui->inbox();
				return False;
			}

			if (! $message['subject'])
			{
				$errors[] = lang('You must enter a subject');
			}

			if (! $message['content'])
			{
				$errors[] = lang("You didn't enter anything for the message");
			}

			if (is_array($errors))
			{
				$ui->compose($errors);
			}
			else
			{
				$so = createobject('messenger.somessage');
				$account_info = $phpgw->accounts->get_list('accounts');
	
				$so->db->transaction_begin();
				while (list(,$account) = each($account_info))
				{
					$message['to'] = $account['account_lid'];
					$so->send_message($message,True);

				}
				$so->db->transaction_commit();
				$ui->inbox();
			}
		}

		function check_for_missing_fields($message)
		{
			global $phpgw;

			if (! $phpgw->accounts->name2id($message['to']))
			{
				if ($message['to'])
				{
					$errors[] = lang("I can't find the username %1 on the system",$message['to']);
				}
				else
				{
					$errors[] = lang('You must enter the username this message is for');
				}
			}

			$acct = createobject('phpgwapi.accounts',$phpgw->accounts->name2id($message['to']));
			$acct->read_repository();
			if ($acct->is_expired() && $phpgw->accounts->name2id($message['to']))
			{
				$errors[] = lang("Sorry, %1's account is not currently active",$message['to']);
			}

			if (! $message['subject'])
			{
				$errors[] = lang('You must enter a subject');
			}

			if (! $message['content'])
			{
				$errors[] = lang("You didn't enter anything for the message");
			}
			return $errors;		
		}

		function send_message()
		{
			global $message, $send, $cancel, $phpgw;

			if ($cancel)
			{
				$ui = createobject('messenger.uimessage');
				$ui->inbox();
				return False;
			}

			$errors = $this->check_for_missing_fields($message);

			$ui = createobject('messenger.uimessage');
			if (is_array($errors))
			{
				$ui->compose($errors);
			}
			else
			{
				$so = createobject('messenger.somessage');
				$so->send_message($message);
				$ui->inbox();
			}
		}

		function read_inbox($start,$order)
		{
			global $phpgw;

			$so = createobject('messenger.somessage');
			$messages = $so->read_inbox($start,$order);

			while (is_array($messages) && list(,$message) = each($messages))
			{
				if ($message['from'] == -1)
				{
					$cached['-1']       = -1;
					$cached_names['-1'] = lang('Global Message');
				}

				// Cache our results, so we don't query the same account multiable times
				if (! $cached[$message['from']])
				{
					$acct = createobject('phpgwapi.accounts',$message['from']);
					$acct->read_repository();
					$cached[$message['from']]       = $message['from'];
					$cached_names[$message['from']] = $phpgw->common->display_fullname($acct->data['account_lid'],$acct->data['firstname'],$acct->data['lastname']);
				}

				/*
				** N - New
				** R - Replied
				** O - Old (read)
				** F - Forwarded
				*/

				if ($message['status'] == 'N')
				{
					$message['subject'] = '<b>' . $message['subject'] . '</b>';
					$message['status'] = '&nbsp';
					$message['date'] = '<b>' . $phpgw->common->show_date($message['date']) . '</b>';
					$message['from'] = '<b>' . $cached_names[$message['from']] . '</b>';
				}
				else
				{
					$message['date'] = $phpgw->common->show_date($message['date']);
					$message['from'] = $cached_names[$message['from']];
				}

				if ($message['status'] == 'O')
				{
					$message['status'] = '&nbsp;';
				}

				$_messages[] = array(
					'id'      => $message['id'],
					'from'    => $message['from'],
					'status'  => $message['status'],
					'date'    => $message['date'],
					'subject' => $message['subject']
				);
			}
			return $_messages;
		}

		function read_message($message_id)
		{
			global $phpgw;
			$so      = createobject('messenger.somessage');
			$message = $so->read_message($message_id);

			$message['date'] = $phpgw->common->show_date($message['date']);

			if ($message['from'] == -1)
			{
				$message['from']           = lang('Global Message');
				$message['global_message'] = True;
			}
			else
			{
				$acct = createobject('phpgwapi.accounts',$message['from']);
				$acct->read_repository();
				$message['from'] = $phpgw->common->display_fullname($acct->data['account_lid'],$acct->data['firstname'],$acct->data['lastname']);
			}

			return $message;
		}

		function read_message_for_reply($message_id,$type)
		{
			global $phpgw, $n_message;

			$so      = createobject('messenger.somessage');
			$message = $so->read_message($message_id);

			$acct = createobject('phpgwapi.accounts',$message['from']);
			$acct->read_repository();

			if (! $n_message['content'])
			{
				$content_array = explode("\n",$message['content']);

				$new_content_array[] = ' ';
				$new_content_array[] = '> ' . $phpgw->common->display_fullname($acct->data['account_lid'],$acct->data['firstname'],$acct->data['lastname']) . ' wrote:';
				$new_content_array[] = '>';
				while (list(,$line) = each($content_array))
				{
					$new_content_array[] = '> ' . $line;
				}
				$message['content'] = implode("\n",$new_content_array);
			}

			$message['subject'] = $type . ': ' . $message['subject'];
			$message['from']    = $acct->data['account_lid'];

			return $message;
		}

		function delete_message()
		{
			global $messages;

			$ui = createobject('messenger.uimessage');
			if (! is_array($messages))
			{
				$ui->inbox();
				return False;
			}
			$so = createobject('messenger.somessage');
			$so->db->transaction_begin();
			while (list(,$message_id) = each($messages))
			{
				$so->delete_message($message_id);
			}
			$so->db->transaction_commit();
			$ui->inbox();
		}

		function reply()
		{
			global $message_id, $n_message;

			$so = createobject('messenger.somessage');
			$ui = createobject('messenger.uimessage');

			$errors = $this->check_for_missing_fields($n_message);

			if (is_array($errors))
			{
				$ui->reply($errors, $n_message);
			}
			else
			{
				$so->send_message($n_message);
				$so->update_message_status('R',$message_id);
				$ui->inbox();
			}
		}

		function forward()
		{
			global $message_id, $n_message;

			$so = createobject('messenger.somessage');
			$ui = createobject('messenger.uimessage');

			$errors = $this->check_for_missing_fields($n_message);

			if (is_array($errors))
			{
				$ui->forward($errors, $n_message);
			}
			else
			{
				$so->send_message($n_message);
				$so->update_message_status('F',$message_id);
				$ui->inbox();
			}
		}

	}