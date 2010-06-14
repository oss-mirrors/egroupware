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

	class bookmarks_so extends so_sql_cf
	{
		// Data we care about, but it doesn't have a DB column.  Overrides parent.
		public $non_db_cols = array('added', 'updated', 'visited', 'bm_id');

		function __construct()
		{
			parent::__construct('bookmarks', 'egw_bookmarks', 'egw_bookmarks_extra', 'bm_', '_name', '_value', 'bm_id');
			$this->user = $GLOBALS['egw_info']['user']['account_id'];
		}

		public function data2db($data = null) {
			if (($intern = !is_array($data)))
			{
				$data =& $this->data;
			}
			
			if($data['added'] || $data['visited'] || $data['updated']) {
				$data['info'] = implode(',', array(
					$data['added'] ? $data['added'] : $this->data['added'],
					$data['visited'] ? $data['visited'] : $this->data['visited'],
					$data['updated'] ? $data['updated'] : $this->data['updated'],
				));
			}
			return parent::data2db($data);
		}
		
		public function db2data($data = null) {
			if (($intern = !is_array($data)))
			{
				$data =& $this->data;
			}

			foreach(array('name','url','desc','keywords') as $name)
			{
				$data['stripped_'.$name] = egw::strip_html($data[$name]);
			}
			list($data['added'], $data['visited'], $data['updated']) = explode(',', $data['info']);
			$data['bm_id'] = $data['id'];

			return parent::db2data($intern ? null : $data);
		}

		function exists($url)
		{
			return $this->db->select($this->table_name,'count(*)',array('bm_url'=>$url,'bm_owner'=>$this->user),__LINE__,__FILE__)->fetchColumn(0) != 0;
		}

		function add($values)
		{
			if ($values['access'] != 'private')
			{
				$values['access'] = 'public';
			}

			// Make sure there are no leftovers 
			$this->init($values);

			parent::save($values);
			return $this->data['id'];
		}

		function save($id, $values)
		{
			$values['updated'] = time();
			if ($values['access'] != 'private')
			{
				$values['access'] = 'public';
			}
			return parent::save($values) === 0;
		}

		function delete($id) {
			return parent::delete(array('id'=>$id));
		}

		function updatetimestamp($id,$timestamp = null)
		{
			parent::update(array(
				'bm_id'=> $id,
				'visited'=> $timestamp ? $timestamp : time(),
				'bm_visits=bm_visits+1'
			), true);
		}

		/**
		* Get bookmarks for nextmatch widget.
		* Re-implemented to handle category filter
		*/
		public function get_rows(&$query, &$rows, &$readonlys) {

			$criteria = array();
			$op = 'AND';
			if ($query['search'])
			{
				$wildcard = '%';
				$criteria = is_null($this->columns_to_search) ? $this->search2criteria($query['search'],$wildcard,$op) : $query['search'];
			}
			if($query['cat_id']) {
				$query['col_filter']['category'] = $query['cat_id'];
			} else {
				$query['col_filter']['category'] = (array)$GLOBALS['egw']->categories->return_array( 'all', 0 , false, '', '', '', true, null, -1, 'id' );
			}

			// Permissions
			$query['col_filter'][] = '(bm_access = \'public\' OR (bm_owner = ' . (int)$GLOBALS['egw_info']['user']['account_id'] . 
				' OR bm_owner IN (' . implode(',', $query['grants']) . ')))';
		
			// Split out timestamps into sortable seperate columns
			$extra_cols = array(
				"replace(substring(substring_index(bm_info, ',', 1), length(substring_index(bm_info, ',',0)) + 1), ',', '') AS added",
				"replace(substring(substring_index(bm_info, ',', 2), length(substring_index(bm_info, ',', 2 - 1)) + 1), ',', '') AS visited",
				"replace(substring(substring_index(bm_info, ',', 3), length(substring_index(bm_info, ',', 3 - 1)) + 1), ',', '') AS updated"
			);
			$rows = $this->search($criteria,false,$query['order']?$query['order'].' '.$query['sort']:'',$extra_cols,
				$wildcard,false,$op,$query['num_rows']?array((int)$query['start'],$query['num_rows']):(int)$query['start'],
				$query['col_filter'],$join,$need_full_no_count);

			if (!$rows) $rows = array();    // otherwise false returned from search would be returned as array(false)

			$selectcols = $query['selectcols'] ? explode(',',$query['selectcols']) : array();
			if ($rows && $this->customfields && (!$selectcols || in_array('customfields',$selectcols)))
			{
				$id2keys = array();
				foreach($rows as $key => $row)
				{
					$id2keys[$row['id']] = $key;
				}
				// check if only certain cf's to show
				foreach($selectcols as $col)
				{
					if ($this->is_cf($col)) $fields[] = $this->get_cf_name($col);
				}
				if (($cfs = $this->read_customfields(array_keys($id2keys),$fields)))
				{
					foreach($cfs as $id => $data)
					{
						$rows[$id2keys[$id]] = array_merge($rows[$id2keys[$id]],$data);
					}
				}
                        }

			return $this->total;
                }
	}
?>
