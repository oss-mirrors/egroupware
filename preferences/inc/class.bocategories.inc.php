<?php
  /**************************************************************************\
  * phpGroupWare - Preferences - categories                                  *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

	class bocategories
	{
		var $cats;

		var $start;
		var $query;
		var $sort;
		var $order;

		function bocategories($cats_app)
		{
			$this->cats = CreateObject('phpgwapi.categories');
			$this->cats->app_name = $cats_app;

			$this->read_sessiondata($cats_app);

			$start  = get_var('start',Array('GET','POST'));
			$query  = get_var('query',Array('GET','POST'));
			$sort   = get_var('sort',Array('GET','POST'));
			$order  = get_var('order',Array('GET','POST'));

			if(!empty($start) || $start == '0' || $start == 0)
			{
				$this->start = $start;
			}
			if((empty($query) && !empty($this->query)) || !empty($query))
			{
				$this->query = $query;
			}

			if(isset($sort) && !empty($sort))
			{
				$this->sort = $sort;
			}
			if(isset($order) && !empty($order))
			{
				$this->order = $order;
			}
		}

		function save_sessiondata($data, $cats_app)
		{
			$colum = $cats_app . '_cats';
			$GLOBALS['phpgw']->session->appsession('session_data',$column,$data);
		}

		function read_sessiondata($cats_app)
		{
			$colum = $cats_app . '_cats';
			$data = $GLOBALS['phpgw']->session->appsession('session_data',$column);

			$this->start  = $data['start'];
			$this->query  = $data['query'];
			$this->sort   = $data['sort'];
			$this->order  = $data['order'];
		}

		function get_list($global_cats)
		{
			return $this->cats->return_sorted_array($this->start,True,$this->query,$this->sort,$this->order,$global_cats);
		}

		function save_cat($values)
		{
			if ($values['access'])
			{
				$values['access'] = 'private';
			}
			else
			{
				$values['access'] = 'public';
			}

			if ($values['id'] && $values['id'] != 0)
			{
				return $this->cats->edit($values);
			}
			else
			{
				return $this->cats->add($values);
			}
		}

		function exists($data)
		{
			return $this->cats->exists($data);
		}

		function formatted_list($format,$type,$cat_parent,$global_cats)
		{
			return $this->cats->formated_list($format,$type,$cat_parent,$global_cats);
		}

		function delete($cat_id,$drop_subs,$modify_subs)
		{
			$this->cats->delete($cat_id,$drop_subs,$modify_subs);
		}

		function check_values($values)
		{
			if (strlen($values['descr']) >= 255)
			{
				$error[] = lang('Description can not exceed 255 characters in length !');
			}

			if (!$values['name'])
			{
				$error[] = lang('Please enter a name');
			}
			else
			{
				if (!$values['parent'])
				{
					$exists = $this->exists(array
					(
						'type'     => 'appandmains',
						'cat_name' => $values['name'],
						'cat_id'   => $values['id']
					));
				}
				else
				{
					$exists = $this->exists(array
					(
						'type'     => 'appandsubs',
						'cat_name' => $values['name'],
						'cat_id'   => $values['id']
					));
				}

				if ($exists == True)
				{
					$error[] = lang('This name has been used already');
				}
			}

			if (is_array($error))
			{
				return $error;
			}
		}
	}
