<?php
  /**************************************************************************\
  * eGroupWare - Polls                                                       *
  * http://www.egroupware.org                                                *
  * Copyright (c) 1999 Till Gerken (tig@skv.org)                             *
  * Modified by Greg Haygood (shrykedude@bellsouth.net)                      *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	class so
	{
		var $debug = False;
		var $db;

		var $total = 0;

		function so($args='')
		{
			$this->db = clone($GLOBALS['egw']->db);
		}

		function load_settings()
		{
			$this->db->query('SELECT * FROM phpgw_polls_settings');
			while($this->db->next_record())
			{
				$GLOBALS['poll_settings'][$this->db->f('setting_name')] = $this->db->f('setting_value');
			}
			return $GLOBALS['poll_settings'];
		}

		function save_settings($data)
		{
			if(isset($data) && is_array($data))
			{
				$this->db->query('DELETE FROM phpgw_polls_settings',__LINE__,__FILE__);
				while(list($name,$value) = each($data))
				{
					$this->db->query("INSERT INTO phpgw_polls_settings VALUES ('$name','$value')",__LINE__,__FILE__);
				}
			}
		}

		function get_user_votecount($poll_id)
		{
			return (int)$this->get_value_('SELECT COUNT(*) FROM phpgw_polls_user WHERE user_id='
				. (int)($GLOBALS['egw_info']['user']['account_id'])
				. ' AND poll_id=' . (int)$poll_id,0);
		}

		function get_poll_title($poll_id)
		{
			return stripslashes($this->get_value_('SELECT poll_title FROM phpgw_polls_desc WHERE poll_id=' . (int)$poll_id,0));
		}

		function get_poll_total($poll_id)
		{
			return (int)$this->get_value_('SELECT SUM(option_count) AS sum FROM phpgw_polls_data'
				. ' WHERE poll_id=' . (int)$poll_id,0);
		}

		function get_poll_data($poll_id,$vote_id=-1)
		{
			$options = array();
			$query = 'SELECT * FROM phpgw_polls_data WHERE poll_id=' . (int)$poll_id;
			if($vote_id >= 0)
			{
				$query .= ' AND vote_id=' . (int)$vote_id;
			}
			$query .= ' ORDER BY LOWER(option_text)';
			if($this->debug) { print("QUERY: $query<br>"); }
			$this->db->query($query,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				$options[] = array(
					'vote_id' => (int)$this->db->f('vote_id'),
					'text' => stripslashes($this->db->f('option_text')),
					'votes' => $this->db->f('option_count')
				);
			}
			return $options;
		}

		function get_latest_poll()
		{
			return $this->get_value_('SELECT MAX(poll_id) FROM phpgw_polls_desc', 0);
		}

		function add_answer($poll_id,$answer)
		{
			$vote_id = (int)$this->get_value_('SELECT MAX(vote_id)+1 FROM phpgw_polls_data'
				. ' WHERE poll_id=' . (int)$poll_id,0);
			$answer = addslashes($answer);
			$result = $this->db->query('INSERT INTO phpgw_polls_data (poll_id,option_text,option_count,vote_id)'
				. ' VALUES (' . (int)$poll_id . ",'" . $answer . "',0," . (int)$vote_id . ')',__LINE__,__FILE__);
			if($result)
			{
				return $this->db->get_last_insert_id('phpgw_polls_desc','poll_id');
			}
			return -1;
		}

		function add_question($title)
		{
			$result = $this->db->query("INSERT INTO phpgw_polls_desc (poll_title,poll_timestamp) VALUES ('"
				. addslashes($title) . "','" . time() . "')",__LINE__,__FILE__);
			return $result;
			if($result)
			{
				return $this->db->get_last_insert_id('phpgw_polls_desc','poll_id');
			}
			return -1;
		}

		function get_last_added_poll()
		{
			return $this->db->get_last_insert_id('phpgw_polls_desc','poll_id');
		}

		function delete_answer($poll_id,$vote_id)
		{
			$this->db->query('DELETE FROM phpgw_polls_data WHERE vote_id=' . (int)$vote_id . ' AND poll_id=' . (int)$poll_id);
		}

		function delete_question($poll_id)
		{
			$this->db->query('DELETE FROM phpgw_polls_desc WHERE poll_id=' . (int)$poll_id);
			$this->db->query('DELETE FROM phpgw_polls_data WHERE poll_id=' . (int)$poll_id);
			$this->db->query('DELETE FROM phpgw_polls_user WHERE poll_id=' . (int)$poll_id);
			if($GLOBALS['currentpoll'] == $poll_id)
			{
				$this->db->query('SELECT MAX(poll_id) AS max FROM phpgw_polls_desc');
				$max = $this->db->f(0);
				$this->db->query("UPDATE phpgw_polls_settings SET setting_value='$max'"
					. " WHERE setting_name='currentpoll'");
			}
		}

		function add_vote($poll_id,$vote_id,$user_id)
		{
			// verify that we're adding a valid vote before update
			$this->db->query('SELECT option_count FROM phpgw_polls_data'
				. ' WHERE poll_id=' . (int) $poll_id . ' AND vote_id=' . (int)$vote_id);
			$count = $this->db->f(0);
			if($count >= 0)
			{
				$this->db->query('UPDATE phpgw_polls_data SET option_count=option_count+1 WHERE'
					. ' poll_id=' . (int)$poll_id . ' AND vote_id=' . (int)$vote_id,__LINE__,__FILE__);
				$this->db->query('INSERT INTO phpgw_polls_user VALUES (' . (int)$poll_id . ',0,'
					. $GLOBALS['egw_info']['user']['account_id'] . ',' . time() . ')',__LINE__,__FILE__);
			}
		}

		function update_answer($poll_id,$vote_id,$answer)
		{
			$this->db->query('UPDATE phpgw_polls_data SET poll_id=' . (int)$poll_id . ",option_text='"
				. addslashes($answer) . "' WHERE vote_id=" . (int)$vote_id,__LINE__,__FILE__);
		}

		function update_question($poll_id,$question)
		{
			$this->db->query("UPDATE phpgw_polls_desc SET poll_title='" . addslashes($question)
				. "' WHERE poll_id=" . (int)$poll_id,__LINE__,__FILE__);
		}

		function get_value_($query,$field)
		{
			$this->db->query($query,__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f($field);
		}

		function get_data_($query,$key,$args)
		{
			$data = array();
			if(!empty($query) && !empty($key))
			{
				$result = $this->db->query($query,__LINE__,__FILE__);
				$this->total = $this->db->num_rows();

				if($args && is_array($args) && !empty($args['limit']))
				{
					$start = (int)$args['start'];
					$result = $this->db->limit_query($query,$start,__LINE__,__FILE__);
				}

				while($this->db->next_record())
				{
					$info = array();
					foreach($this->db->Record as $key => $val)
					{
						$info[$key] = $val;
					}
					$data[] = $info;
				}
			}
			return $data;
		}

		function list_questions($args)
		{
			$query = 'SELECT * FROM phpgw_polls_desc ORDER BY ' . $args['order'] . ' ' . $args['sort'];
			if($this->debug) { print("QUERY: $query<br>"); }
			$data = $this->get_data_($query,'poll_id',$args);
			return $data;
		}

		function list_answers($args)
		{
			$query = 'SELECT phpgw_polls_data.*, phpgw_polls_desc.poll_title '
				. 'FROM phpgw_polls_data,phpgw_polls_desc '
				. 'WHERE phpgw_polls_desc.poll_id = phpgw_polls_data.poll_id '
				. 'ORDER BY '.$args['order'].' '.$args['sort'];
			if($this->debug) { print("QUERY: $query<br>"); }
			$data = $this->get_data_($query,'vote_id',$args);
			return $data;
		}

		function somestoragefunc()
		{
			//nothing to be added yet
		}
	}
?>
