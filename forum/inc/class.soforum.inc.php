<?php
	/*****************************************************************************\
	* phpGroupWare - boForums                                                     *
	* http://www.phpgroupware.org                                                 *
	* Written by Mark A Peters <skeeter@phpgroupware.org>                         *
	* Based off of Jani Hirvinen <jpkh@shadownet.com>                             *
	* -------------------------------------------                                 *
	*  This program is free software; you	can redistribute it and/or modify it  *
	*  under the terms of	the GNU	General	Public License as published by the    *
	*  Free Software Foundation; either version 2	of the License,	or (at your   *
	*  option) any later version.                                                 *
	\*****************************************************************************/

	/* $Id$ */

	class soforum
	{
		var $db;

		function soforum()
		{
			$this->db = $GLOBALS['phpgw']->db;
		}

		function add_reply($data)
		{
			$this->db->query('insert into phpgw_forum_threads (pos,thread,depth,postdate,main,parent,cat_id,for_id,thread_owner,subject,stat,n_replies) '
				.'VALUES('.$data['pos'].','.$data['thread'].','.$data['depth'].",'".$this->db->to_timestamp($data['postdate'])."',"
					. ($this->get_max_body_id() + 1).','.$data['parent'].','.$data['cat_id'].','
					. $data['forum_id'].','.$GLOBALS['phpgw_info']['user']['account_id'].",'"
					. $this->db->db_addslashes($data['subject']) . "',0,0)",__LINE__,__FILE__);
			$this->db->query('update phpgw_forum_threads set n_replies = n_replies+1 where thread='.$data['thread'],__LINE__,__FILE__);
			$this->db->query('insert into phpgw_forum_body (cat_id,for_id,message) VALUES ('.$data['cat_id'].','.$data['forum_id'].",'".$this->db->db_addslashes($data['message'])."')",__LINE__,__FILE__);
		}

		function add_post($data)
		{
			$next_f_body_id = $this->get_max_body_id() + 1;
			$this->db->query('insert into phpgw_forum_threads (pos,thread,depth,postdate,main,parent,cat_id,for_id,thread_owner,subject,stat,n_replies) '
				.'VALUES (0,'.$next_f_body_id.",0,'".$this->db->to_timestamp($data['postdate'])."',".$next_f_body_id.',-1,'.$data['cat_id'].','.$data['forum_id']
				.','.$GLOBALS['phpgw_info']['user']['account_id'] . ",'".$this->db->db_addslashes($data['subject'])."',0,0)",__LINE__,__FILE__);
				
			$this->db->query('insert into phpgw_forum_body (cat_id,for_id,message) VALUES ('.$data['cat_id'].','.$data['forum_id'].",'".$this->db->db_addslashes($data['message'])."')",__LINE__,__FILE__);
		}

		function get_max_body_id()
		{
			$this->db->query('select max(id) from phpgw_forum_body',__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function get_max_thread_id()
		{
			$this->db->query('select max(id) from phpgw_forum_threads',__LINE__,__FILE__);
			$this->db->next_record();
			return $this->db->f(0);
		}

		function fix_pos($thread,$pos)
		{
			$db2 = $GLOBALS['phpgw']->db;
			$tmp = $this->db->query('select id,pos from phpgw_forum_threads where thread='.$thread.' and pos>='.$pos.' order by pos desc',__LINE__,__FILE__);
			while($this->db->next_record($tmp))
			{
				$oldpos = $this->db->f('pos') + 1;
				$oldid = $this->db->f('id');
//				print "$oldid $oldpos<br>";
				$db2->query('update phpgw_forum_threads set pos='.$oldpos.' where thread='.$thread.' and id='.$oldid,__LINE__,__FILE__);
			}
		}

		function get_cat_ids()
		{
			$this->db->query('select * from phpgw_forum_categories',__LINE__,__FILE__);
			while($this->db->next_record())
			{
				$cat[] = Array(
					'id'	=> $this->db->f('id'),
					'name'	=> $this->db->f('name'),
					'descr'	=> $this->db->f('descr')
				);
			}
			return $cat;
		}

		function get_cat_info($cat_id)
		{
			$this->db->query('select * from phpgw_forum_categories where id='.$cat_id,__LINE__,__FILE__);
			if($this->db->num_rows())
			{
				$this->db->next_record();
				$cat = Array(
					'id'	=>	$cat_id,
					'name'	=> $this->db->f('name'),
					'descr'	=> $this->db->f('descr')
				);
			}
			return $cat;
		}

		function get_thread_summary($cat_id,$forum_id=0,$thread_id=0)
		{
			$db2 = $GLOBALS['phpgw']->db;
			$query = 'select max(postdate), count(id) from phpgw_forum_threads where cat_id='.$cat_id;
			if($forum_id!=0)
			{
				$query .= ' and for_id='.$forum_id;
			}
			if($thread_id!=0)
			{
				$query .= ' and thread='.$thread_id;
			}
			$db2->query($query,__LINE__,__FILE__);
			$db2->next_record();
			if($db2->f(0))
			{
				$forum['last_post'] = $GLOBALS['phpgw']->common->show_date($db2->from_timestamp($db2->f(0)));
			}
			else
			{
				$forum['last_post'] = '&nbsp;';
			}
			$forum['total'] = $db2->f(1);
			return $forum;
		}

		function get_forum_info($cat_id,$forum_id=0)
		{
			$query = 'select * from phpgw_forum_forums where cat_id='.$cat_id;
			if($forum_id!=0)
			{
				$query .= ' and id='.$forum_id;
			}
			$this->db->query($query,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				$forum[] = Array(
					'cat_id'	=>	$cat_id,
					'id'	=> $this->db->f('id'),
					'name'	=> $this->db->f('name'),
					'descr'	=> $this->db->f('descr')
				);
			}
			return $forum;
		}

		function get_thread($cat_id,$forum_id,$collapsed)
		{
			$query = 'select * from phpgw_forum_threads where cat_id='.$cat_id.' and for_id='.$forum_id;
			if($collapsed)
			{
				$query .= ' and parent = -1 order by postdate DESC';
			}
			else
			{
				$query .= ' order by thread DESC, postdate, depth';
			}
			$this->db->query($query,__LINE__,__FILE__);
			while($this->db->next_record())
			{
				if($collapsed)
				{
					$temp = $this->get_thread_summary($cat_id,$forum_id,$this->db->f('id'));
					$last_post = $temp['last_post'];
				}
				$thread[] = Array(
					'id'	=> $this->db->f('id'),
					'subject'	=> $this->db->f('subject'),
					'author'		=> $this->db->f('thread_owner'),
					'replies'	=> $this->db->f('n_replies'),
					'pos'		=> $this->db->f('pos'),
					'depth'	=> $this->db->f('depth'),
					'last_reply'=> ($last_post?$last_post:$GLOBALS['phpgw']->common->show_date($this->db->from_timestamp($this->db->f('postdate'))))
				);

			}
			return $thread;
		}

		function read_msg($cat_id,$forum_id,$msg_id)
		{
			$db2 = $GLOBALS['phpgw']->db;
			$db2->query('select thread from phpgw_forum_threads where id='.$msg_id,__LINE__,__FILE__);
			$db2->next_record();
			$this->db->query('select * from phpgw_forum_threads where id>='.$msg_id.' and cat_id='.$cat_id.' and for_id='.$forum_id.' and thread='.$db2->f('thread').' order by parent,id',__LINE__,__FILE__);
			if(!$this->db->num_rows())
			{
				return False;
			}
			while($this->db->next_record())
			{
				$subject = $this->db->f('subject');
				if (!$subject)
				{
					$subject = '[ ' . lang('No subject') . ' ]';
				}

				$db2->query('select * from phpgw_forum_body where id='.$this->db->f('id'),__LINE__,__FILE__);
				$db2->next_record();
				$message = $GLOBALS['phpgw']->strip_html($db2->f('message'));

				$msg[] = Array(
					'id'	=> $this->db->f('id'),
					'main'	=> $this->db->f('main'),
					'parent'	=> $this->db->f('parent'),
					'thread'	=> $this->db->f('thread'),
					'depth'	=> ($this->db->f('depth') + 1),
					'pos'		=> $this->db->f('pos'),
					'subject'	=> $subject,
					'thread_owner'	=> $this->db->f('thread_owner'),
					'postdate'	=> $this->db->f('postdate'),
					'message'	=> $message
				);

			}
			return $msg;
		}
	}
?>
