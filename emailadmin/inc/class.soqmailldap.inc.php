<?php
	/***************************************************************************\
	* phpGroupWare - Notes                                                      *
	* http://www.phpgroupware.org                                               *
	* Written by : Bettina Gille [ceb@phpgroupware.org]                         *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class soqmailldap
	{
		var $grants;

		function soqmailldap()
		{
			global $phpgw, $phpgw_info;

			$this->db		= $phpgw->db;
			$this->db2		= $this->db;
			$this->grants	= $phpgw->acl->get_grants('notes');
			$this->owner	= $phpgw_info['user']['account_id'];
		}

		function read_notes($start, $search = '', $filter = '',$cat_id = '')
		{
			global $phpgw, $phpgw_info;

			if (! $filter)
			{
				$filter = 'all';
			}

			if ($filter == 'all')
			{
				$filtermethod = " ( note_owner=" . $this->owner;
				if (is_array($this->grants))
				{
					$grants = $this->grants;
					while (list($user) = each($grants))
					{
						$public_user_list[] = $user;
					}
					reset($public_user_list);
					$filtermethod .= " OR (note_access='public' AND note_owner in(" . implode(',',$public_user_list) . ")))";
				}
				else
				{
					$filtermethod .= ' )';
				}
			}
			elseif ($filter == 'public')
			{
				$filtermethod = " note_owner='" . $this->owner . "'";
			}
			else
			{
				$filtermethod = " note_owner='" . $this->owner . "' AND note_access='private'";
			}

			if ($cat_id)
			{
				$filtermethod .= " AND note_category='$cat_id' ";
			}

			if ($search)
			{
				$searchmethod = " AND note_content like '%$search%'";
			}

			$sql = "SELECT * FROM phpgw_notes WHERE $filtermethod $searchmethod ORDER BY note_date DESC";

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();
			$this->db->limit_query($sql,$start,__LINE__,__FILE__);

			$i = 0;
			while ($this->db->next_record())
			{
				$notes[$i]['id']		= $this->db->f('note_id');
				$notes[$i]['owner']		= $this->db->f('note_owner');
				$notes[$i]['access']	= $this->db->f('note_access');
				$notes[$i]['date']		= $this->db->f('note_date');
				$notes[$i]['category']	= $this->db->f('note_category');
				$notes[$i]['content']	= $this->db->f('note_content');
				$i++;
			}
			return $notes;
		}

		function read_single_note($note_id)
		{
			$this->db->query("select * from phpgw_notes where note_id='$note_id'",__LINE__,__FILE__);

			if ($this->db->next_record())
			{
				$note['owner']		= $this->db->f('note_owner');
				$note['content']	= $this->db->f('note_content');
				$note['access']		= $this->db->f('note_access');
				$note['date']		= $this->db->f('note_date');
				$note['category']	= $this->db->f('note_category');

				return $note;
			}
		}

		function add_note($note)
		{
			$note['content'] = addslashes($note['content']);

			$this->db->query("INSERT INTO phpgw_notes (note_owner,note_access,note_date,note_content,note_category) "
							. "VALUES ('" . $this->owner . "','" . $note['access'] . "','" . time() . "','" . $note['content']
							. "','" . $note['category'] . "')",__LINE__,__FILE__);			
		}

		function edit_note($note)
		{
			$note['content'] = addslashes($note['content']);

			$this->db->query("UPDATE phpgw_notes set note_content='" . $note['content'] . "', note_date='" . time() . "', note_category='" . $note[category] . "', "
							. "note_access='" . $note['access'] . "' WHERE note_id='" . $note['id'] . "'",__LINE__,__FILE__);
		}

		function delete_note($note_id)
		{
			$this->db->query("DELETE FROM phpgw_notes WHERE note_id='$note_id'",__LINE__,__FILE__);
		}
	}
?>
