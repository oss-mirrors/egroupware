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

	class somessage
	{
		var $db;
		var $template;
		var $owner;

		function somessage()
		{
			global $phpgw, $phpgw_info;
			$this->template = $phpgw->template;
			$this->db       = $phpgw->db;
			$this->owner    = $phpgw_info['user']['account_id'];
		}

		function update_message_status($status, $message_id)
		{
			$this->db->query("update phpgw_messenger_messages set message_status='$status' where message_id='"
								. $message_id . "' and message_owner='" . $this->owner ."'",__LINE__,__FILE__);
		}

		function read_inbox($start,$order)
		{
			$this->db->limit_query("select * from phpgw_messenger_messages where message_owner='" . $this->owner
								. "'",$start,__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				$messages[] = array(
					'id'      => $this->db->f('message_id'),
					'from'    => $this->db->f('message_from'),
					'status'  => $this->db->f('message_status'),
					'date'    => $this->db->f('message_date'),
					'subject' => $this->db->f('message_subject')
				);
			}
			return $messages;
		}

		function read_message($message_id)
		{
			$this->db->query("select * from phpgw_messenger_messages where message_id='"
								. $message_id . "' and message_owner='" . $this->owner ."'",__LINE__,__FILE__);
			$this->db->next_record();
			$message = array(
				'id'      => $this->db->f('message_id'),
				'from'    => $this->db->f('message_from'),
				'status'  => $this->db->f('message_status'),
				'date'    => $this->db->f('message_date'),
				'subject' => $this->db->f('message_subject'),
				'content' => $this->db->f('message_content')
			);
			if ($this->db->f('message_status') == 'N')
			{
				$this->update_message_status('O',$message_id);
			}
			return $message;
		}

		function send_message($message, $global_message = False)
		{
			global $phpgw;

			if ($global_message)
			{
				$this->owner = -1;
			}

			if (! ereg('^[0-9]+$',$message['to']))
			{
				$message['to'] = $phpgw->accounts->name2id($message['to']);
			}

			$this->db->query("insert into phpgw_messenger_messages (message_owner, message_from, message_status, "
								. "message_date, message_subject, message_content) values ('"
								. $message['to'] . "','" . $this->owner . "','N','" . time() . "','"
								. addslashes($message['subject']) . "','"	. addslashes($message['content'])
								. "')",__LINE__,__FILE__);
		}

		function total_messages($extra_where_clause = '')
		{
			$this->db->query("select count(*) from phpgw_messenger_messages where message_owner='"
								. $this->owner . "' " . $extra_where_clause,__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function delete_message($message_id)
		{
			$this->db->query("delete from phpgw_messenger_messages where message_id='$message_id' and "
								. "message_owner='" . $this->owner . "'",__LINE__,__FILE__);
		}
	}
