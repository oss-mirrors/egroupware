<?php
	/**************************************************************************\
	* eGroupWare - Bookmarks                                                   *
	* http://www.egroupware.org                                                *
	* Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
	*                     http://www.renaghan.com/bookmarker                   *
	* Ported to phpgroupware by Joseph Engo                                    *
	* Ported to three-layered design by Michael Totschnig                      *
	* SQL reworked by RalfBecker@outdoor-training.de to get everything quoted  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class so
	{
		var $db;
		var $total_records;

		function so()
		{
			$this->db = $GLOBALS['phpgw']->db;
			$this->table = 'phpgw_bookmarks';
			$table_def = $this->db->get_table_definitions('bookmarks',$this->table);
			$this->db->set_column_definitions($table_def['fd']);
		}

		function _list($cat_list,$public_user_list,$start,$where_clause)
		{
			$query = "SELECT * FROM $this->table WHERE ( bm_owner=" . (int)$GLOBALS['phpgw_info']['user']['account_id'];
			if ($public_user_list)
			{
				$filtermethod .= ' OR ('.$this->db->column_data_implode(' AND ',array(
					'bm_access'=>'public',
					'bm_owner' => $public_user_list,
				));
			}
			$query .= ' )';

			if ($cat_list)
			{
				$where_clause .= ' '.$this->db->column_data_implode(' AND ',array(
					'bm_category' => $cat_list,
				));
			}
			$query .= ($where_clause ? ' AND '.$where_clause : '') . ' ORDER BY bm_category, bm_name';

			$this->db->query($query,__LINE__,__FILE__);
			$this->total_records = $this->db->num_rows();

			if ($start !== False)
			{
				$this->db->limit_query($query,$start,__LINE__,__FILE__);
			}

			while ($this->db->next_record())
			{
				$result[$this->db->f('bm_id')] = $this->_db2bookmark();
			}
			return $result;
		}

		function _db2bookmark($do_htmlspecialchars = True)
		{
			foreach(array('name','url','desc','keywords','owner','access','category','rating','visits','info') as $name)
			{
				$bookmark[$name] = $this->db->f('bm_'.$name);
			}
			if ($do_htmlspecialchars)
			{
				foreach(array('name','url','desc','keywords') as $name)
				{
					$bookmark[$name] = $GLOBALS['phpgw']->strip_html($bookmark[$name]);
				}
			}
			return $bookmark;
		}

		function read($id,$do_htmlspecialchars=True)
		{
			$query = "SELECT * FROM $this->table WHERE bm_id=".(int)$id;
			$this->db->query($query,__LINE__,__FILE__);
			if (!$this->db->next_record())
			{
				return False;
			}
			return $this->_db2bookmark($do_htmlspecialchars);
		}

		function exists($url)
		{
			$query = "SELECT count(*) FROM $this->table WHERE bm_url=".$this->db->quote($url).' AND bm_owner='.(int)$GLOBALS['phpgw_info']['user']['account_id'];
			$this->db->query($query,__LINE__,__FILE__);
			$this->db->next_record();

			return (bool)$this->db->f(0);
		}

		function add($values)
		{
			$columns = $this->_bookmark2db($values,$values['timestamps'] ? $values['timestamps'] : time() . ',0,0');
			$columns['bm_owner'] = (int) $GLOBALS['phpgw_info']['user']['account_id'];
			$columns['bm_visits'] = 0;

			$query = "INSERT INTO $this->table (".implode(',',array_keys($columns)).") VALUES(".
				$this->db->column_data_implode(',',$columns,False).')';

			if (!$this->db->query($query,__LINE__,__FILE__))
			{
				return False;
			}
			return $this->db->get_last_insert_id('phpgw_bookmarks','bm_id');
		}

		function update($id, $values)
		{
			#echo "so::update<pre>".htmlspecialchars(print_r($values,True))."</pre>\n";

			$this->db->query("SELECT bm_info FROM $this->table WHERE bm_id=".(int)$id,__LINE__,__FILE__);
			$this->db->next_record();
			$ts = explode(',',$GLOBALS['phpgw']->db->f('bm_info'));
			$ts[2] = time();

			$columns = $this->_bookmark2db($values,implode(',',$ts));

			// Update bookmark information.
			$query = "UPDATE $this->table SET ".$this->db->column_data_implode(',',$columns).' WHERE bm_id='.(int)$id;

			if (!$this->db->query($query,__LINE__,__FILE__))
			{
				return False;
			}
			return True;
		}

		function _bookmark2db($values,$timestamps)
		{
			if ($values['access'] != 'private')
			{
				$values['access'] = 'public';
			}
			foreach(array('name','url','desc','keywords','access','category','rating') as $name)
			{
				$columns['bm_'.$name] = $values[$name];
			}
			$columns['bm_info'] = $timestamps;

			return $columns;
		}

		function updatetimestamp($id,$timestamp)
		{
			$query = "UPDATE $this->table SET bm_info=".$this->db->quote($timestamp).', bm_visits=bm_visits+1 WHERE bm_id='.(int)$id;
			$this->db->query($query,__LINE__,__FILE__);
		}

		function delete($id)
		{
			$query = "DELETE FROM $this->table WHERE bm_id=".(int)$id;
			$this->db->query($query,__LINE__,__FILE__);
			if ($this->db->Errno != 0)
			{
				return False;
			}
			return True;
		}
	}
