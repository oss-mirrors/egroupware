<?php
	/**************************************************************************\
	* phpGroupWare API - Record history logging                                *
	* This file written by Joseph Engo <jengo@phpgroupware.org>                *
	* Copyright (C) 2001 Joseph Engo                                           *
	* -------------------------------------------------------------------------*
	* This library is part of the phpGroupWare API                             *
	* http://www.phpgroupware.org/api                                          *
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */

	class historylog
	{
		var $db;
		var $appname;
		var $template;
		var $nextmatchs;
		var $types = array(
			'C' => 'Created',
			'D' => 'Deleted',
			'E' => 'Edited'
		);

		function historylog($appname)
		{
			if (! $appname)
			{
				$appname = $GLOBALS['phpgw_info']['flags']['currentapp'];
			}

			$this->appname = $appname;
			$this->db      = $GLOBALS['phpgw']->db;
		}

		function add($status,$record_id,$new_value)
		{
			$this->db->query("insert into phpgw_history_log (history_record_id,"
				. "history_appname,history_owner,history_status,history_new_value) "
				. "values ('$record_id','" . $this->appname . "','"
				. $GLOBALS['phpgw_info']['user']['account_id'] . "','$status','"
				. addslashes($new_value) . "')",__LINE__,__FILE__);
		}

		// array $filter_out
		function return_array($filter_out,$orderby = '',$sort = '', $record_id = 0)
		{
			while (is_array($filter_out) && list(,$_filter) = each($filter_out))
			{
				$filtered[] = "history_status != '$_filter'";
			}

			if (is_array($filtered))
			{
				$filter = ' and ' . implode(' and ',$filtered);
			}

			if ($record_id)
			{
				$record_filter = " and history_record_id='$record_id' ";
			}

			$this->db->query("select * from phpgw_history_log where history_appname='"
				. $this->appname . "' $filter $record_filter order by history_timestamp,history_id",__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				$return_values[] = array(
					'id'         => $this->db->f('history_id'),
					'record_id'  => $this->db->f('history_record_id'),
					'owner'      => $GLOBALS['phpgw']->accounts->id2name($this->db->f('history_owner')),
//					'status'     => lang($this->types[$this->db->f('history_status')]),
					'status'     => ereg_replace(' ','',$this->db->f('history_status')),
					'new_value'  => $this->db->f('history_new_value'),
					'datetime'   => $this->db->from_timestamp($this->db->f('history_timestamp'))
				);
			}
			return $return_values;
		}

		function return_html($filter_out,$orderby = '',$sort = '', $record_id = 0)
		{
			$this->template   = createobject('phpgwapi.Template',PHPGW_TEMPLATE_DIR);
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');

			$this->template->set_file('_history','history_list.tpl');

			$this->template->set_block('_history','row_no_history');
			$this->template->set_block('_history','list');
			$this->template->set_block('_history','row');

			$this->template->set_var('lang_user',lang('User'));
			$this->template->set_var('lang_date',lang('Date'));
			$this->template->set_var('lang_action',lang('Action'));
			$this->template->set_var('lang_new_value',lang('New Value'));

			$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->template->set_var('sort_date',lang('Date'));
			$this->template->set_var('sort_owner',lang('User'));
			$this->template->set_var('sort_status',lang('Status'));
			$this->template->set_var('sort_new_value',lang('New value'));

			$values = $this->return_array($filter_out,$orderby,$sort,$record_id);

			if (! is_array($values))
			{
				$this->template->set_var('lang_no_history',lang('No history for this record'));
				$this->template->fp('rows','row_no_history');
				return $this->template->fp('out','list');
			}

			while (list(,$value) = each($values))
			{
				$this->nextmatchs->template_alternate_row_color(&$this->template);

				$this->template->set_var('row_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
				$this->template->set_var('row_owner',$value['owner']);
				$this->template->set_var('row_owner',$value['status']);
				$this->template->set_var('row_new_value',$value['new_value']);

				$this->template->fp('rows','row');
			}
			return $this->template->fp('out','list');
		}

	}