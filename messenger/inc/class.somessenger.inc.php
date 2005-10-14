<?php
	/**************************************************************************\
	* eGroupWare - Messenger                                                   *
	* http://www.egroupware.org                                                *
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

	class somessenger
	{
		var $db;
		var $table = 'phpgw_messenger_messages';
		var $owner;

		function somessenger()
		{
			$this->db    = &$GLOBALS['egw']->db;
			$this->owner = $GLOBALS['egw_info']['user']['account_id'];
			$config =& CreateObject('phpgwapi.config');
			$config->read_repository();
			$GLOBALS['egw_info']['server']['messenger'] = $config->config_data;
			unset($config);
		}

		function update_message_status($status, $message_id)
		{
			$this->db->query('UPDATE ' . $this->table . " SET message_status='$status' WHERE message_id='"
				. $message_id . "' AND message_owner='" . $this->owner ."'",__LINE__,__FILE__);

			return ($this->db->affected_rows() ? True : False);
		}

		function read_inbox($start,$order,$sort)
		{
			$messages = array();

			if($sort && $order)
			{
				$sortmethod = " ORDER BY $order $sort";
			}
			else
			{
				$sortmethod = ' ORDER BY message_date ASC';
			}

			$this->db->limit_query('SELECT * FROM ' . $this->table . " WHERE message_owner='" . $this->owner
				. "' $sortmethod",$start,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				$messages[] = array(
					'id'      => $this->db->f('message_id'),
					'from'    => (int)$this->db->f('message_from'),
					'status'  => $this->db->f('message_status'),
					'date'    => $this->db->f('message_date'),
					'subject' => $this->db->f('message_subject'),
					'content' => $this->db->f('message_content')
				);
			}
			return $messages;
		}

		function read_message($message_id)
		{
			$this->db->query('SELECT * FROM ' . $this->table . " WHERE message_id='"
				. $message_id . "' AND message_owner='" . $this->owner ."'",__LINE__,__FILE__);
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
			if($global_message)
			{
				$this->owner = -1;
			}
			foreach($message['recipient'] as $recipient)
			{
				$this->db->query('INSERT INTO ' . $this->table . ' (message_owner, message_from, message_status, '
					. "message_date, message_subject, message_content) VALUES ('"
					. (int)$recipient . "','" . $this->owner . "','N','" . time() . "','"
					. addslashes($message['subject']) . "','" . $this->db->db_addslashes($message['content'])
					. "')",__LINE__,__FILE__);
			}
			return True;
		}

		function total_messages($extra_where_clause = '')
		{
			$this->db->query('SELECT COUNT(message_owner) FROM ' . $this->table . " WHERE message_owner='"
				. $this->owner . "' " . $extra_where_clause,__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function delete_message($message_id)
		{
			$this->db->query('DELETE FROM ' . $this->table . " WHERE message_id='$message_id' AND "
				. "message_owner='" . $this->owner . "'",__LINE__,__FILE__);
			return True;
		}
	}
