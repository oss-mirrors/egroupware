<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000,2001,2002 Bettina Gille                        *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */

	class sobilling
	{
		var $db;
		var $grants;

		function sobilling()
		{
			$this->db			= $GLOBALS['phpgw']->db;
			$this->db2			= $this->db;
		}

		function return_join()
		{
			$dbtype = $GLOBALS['phpgw_info']['server']['db_type'];

			switch ($dbtype)
			{
				case 'psql':	$join = " JOIN "; break;
				case 'mysql':	$join = " LEFT JOIN "; break;
			}
			return $join;
		}

		function read_invoices($start, $query = '', $sort = '', $order = '', $limit = True, $project_id = '')
		{
			if ($order)
			{
				$ordermethod = " order by $order $sort";
			}
			else
			{
				$ordermethod = " order by date asc";
			}

			if ($query)
			{
				$querymethod = " AND (phpgw_p_invoice.num like '%$query%' OR title like '%$query%' "
								. "OR sum like '%$query%') ";
			}

			if ($project_id)
			{
				$sql = "SELECT phpgw_p_invoice.id as id,phpgw_p_invoice.num,title,phpgw_p_invoice.date,sum,phpgw_p_invoice.project_id,"
				. "phpgw_p_invoice.customer FROM phpgw_p_invoice,phpgw_p_projects WHERE phpgw_p_invoice.project_id=phpgw_p_projects.id "
				. "AND phpgw_p_projects.id='$project_id' AND phpgw_p_invoice.project_id='$project_id'";
			}
			else
			{
				$sql = "SELECT phpgw_p_invoice.id as id,phpgw_p_invoice.num,title,phpgw_p_invoice.date,sum,phpgw_p_invoice.project_id,"
				. "phpgw_p_invoice.customer FROM phpgw_p_invoice,phpgw_p_projects WHERE phpgw_p_invoice.project_id=phpgw_p_projects.id";
			}

			$this->db2->query($sql,__LINE__,__FILE__);
			$this->total_records = $this->db2->num_rows();

			if ($limit)
			{
				$this->db->limit_query($sql . $querymethod,$start,__LINE__,__FILE__);
			}
			else
			{
				$this->db->query($sql . $querymethod,__LINE__,__FILE__);
			}

			while ($this->db->next_record())
			{
				$bill[] = array
				(
					'invoice_id'	=> $this->db->f('id'),
					'invoice_num'	=> $this->db->f('num'),
					'title'			=> $this->db->f('title'),
					'date'			=> $this->db->f('date'),
					'sum'			=> $this->db->f('sum'),
					'project_id'	=> $this->db->f('project_id'),
					'customer'		=> $this->db->f('customer')
				);
			}
			return $bill;
		}

		function exists($values)
		{
			if ($values['invoice_id'] && ($values['invoice_id'] != 0))
			{
				$editexists = " and id != '" . $values['invoice_id'] . "'";
			}

			$this->db->query("select count(*) from phpgw_p_invoice where num='" . $values['invoice_num'] . "'" . $editexists,__LINE__,__FILE__);

			$this->db->next_record();

			if ($this->db->f(0))
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function invoice($values,$select)
		{
			$values['invoice_num'] = addslashes($values['invoice_num']);
			$this->db->query("INSERT INTO phpgw_p_invoice (num,sum,project_id,customer,date) VALUES ('" . $values['invoice_num'] . "',0,'"
							. $values['project_id'] . "','" . $values['customer'] . "','" . $values['date'] . "')",__LINE__,__FILE__);
			$this->db2->query("SELECT id from phpgw_p_invoice WHERE num='" . $values['invoice_num'] . "'",__LINE__,__FILE__);
			$this->db2->next_record();
			$invoice_id = $this->db2->f('id');

			while($select && $entry=each($select))
			{
				$this->db->query("INSERT INTO phpgw_p_invoicepos (invoice_id,hours_id) VALUES ('" . $invoice_id . "','" . $entry[0] . "')",__LINE__,__FILE__);
				$this->db2->query("UPDATE phpgw_p_hours SET status='billed' WHERE id='" . $entry[0] . "'",__LINE__,__FILE__);
			}

			$this->db->query("SELECT billperae,minutes,minperae FROM phpgw_p_hours,phpgw_p_invoicepos "
							."WHERE phpgw_p_invoicepos.invoice_id='" . $invoice_id . "' AND phpgw_p_hours.id=phpgw_p_invoicepos.hours_id",__LINE__,__FILE__);
			while ($this->db->next_record())
			{
				if ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'] == 'wu')
				{
					$aes = ceil($this->db->f('minutes')/$this->db->f('minperae'));
					$sum = $this->db->f('billperae')*$aes;
					$sum_sum += $sum;
				}
				else
				{
					$aes = $this->db->f('minutes')/60;
					$sum = $this->db->f('billperae')*$aes;
					$sum_sum += $sum;
				}
			}
			$this->db->query("UPDATE phpgw_p_invoice SET sum=round(" . $sum_sum . ",2) WHERE id='" . $invoice_id . "'",__LINE__,__FILE__);
			return $invoice_id;
		}

		function update_invoice($values,$select)
		{
			$this->db->query("UPDATE phpgw_p_invoice set num='" . $values['invoice_num'] . "',date='" . $values['date'] . "',customer='"
							. $values['customer'] . "' WHERE id='" . $values['invoice_id'] . "'",__LINE__,__FILE__);

			$this->db2->query("DELETE FROM phpgw_p_invoicepos WHERE invoice_id='" . $values['invoice_id'] . "'",__LINE__,__FILE__);

			while($select && $entry=each($select))
			{
				$this->db->query("INSERT INTO phpgw_p_invoicepos (invoice_id,hours_id) VALUES ('" . $values['invoice_id'] . "','"
								. $entry[0] . "')",__LINE__,__FILE__);
				$this->db2->query("UPDATE phpgw_p_hours SET status='billed' WHERE id='" . $entry[0] . "'",__LINE__,__FILE__);
			}

			$this->db->query("SELECT billperae,minutes,minperae FROM phpgw_p_hours,phpgw_p_invoicepos "
							."WHERE phpgw_p_invoicepos.invoice_id='" . $values['invoice_id'] . "' AND phpgw_p_hours.id="
							. "phpgw_p_invoicepos.hours_id",__LINE__,__FILE__);

			while($this->db->next_record())
			{
				if ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'] == 'wu')
				{
					$aes = ceil($this->db->f('minutes')/$this->db->f('minperae'));
					$sum = $this->db->f('billperae')*$aes;
					$sum_sum += $sum;
				}
				else
				{
					$aes = $this->db->f('minutes')/60;
					$sum = $this->db->f('billperae')*$aes;
					$sum_sum += $sum;
				}
			}

			$this->db2->query("UPDATE phpgw_p_invoice SET sum=round(" . $sum_sum . ",2) WHERE id='" . $values['invoice_id'] . "'",__LINE__,__FILE__);
		}

		function read_hours($project_id, $action)
		{
			$ordermethod = " order by end_date asc";

			if ($action == 'mains')
			{
				$parent_hours	= " OR phpgw_p_hours.pro_parent='" . $project_id . "'";
			}

			$this->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status, "
						. "phpgw_p_hours.start_date,phpgw_p_hours.end_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae,phpgw_p_hours.billperae,"
						. "phpgw_p_hours.employee FROM phpgw_p_hours " . $this->return_join() . " phpgw_p_activities ON "
						. "phpgw_p_hours.activity_id=phpgw_p_activities.id " . $this->return_join() . " phpgw_p_projectactivities ON "
						. "phpgw_p_hours.activity_id=phpgw_p_projectactivities.activity_id WHERE (phpgw_p_hours.status='done' OR "
						. "phpgw_p_hours.status='closed') AND (phpgw_p_hours.project_id='" . $project_id . "'" . $parent_hours . ") AND "
						. "phpgw_p_projectactivities.project_id='" . $project_id . "' AND phpgw_p_projectactivities.billable='Y' "
						. "AND phpgw_p_projectactivities.activity_id=phpgw_p_hours.activity_id " . $ordermethod,__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'hours_id'		=> $this->db->f('id'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'act_descr'		=> $this->db->f('descr'),
					'status'		=> $this->db->f('status'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'minutes'		=> $this->db->f('minutes'),
					'minperae'		=> $this->db->f('minperae'),
					'billperae'		=> $this->db->f('billperae'),
					'employee'		=> $this->db->f('employee')
				);
			}
			return $hours;
		}

		function read_invoice_hours($project_id, $invoice_id, $action)
		{
			$ordermethod = " order by end_date asc";

			if ($action == 'mains' || $action == 'amains')
			{
				$parent_search = " OR phpgw_p_hours.pro_parent='" . $project_id . "'";
			}

			$this->db->query("SELECT phpgw_p_hours.id as id,phpgw_p_hours.hours_descr,phpgw_p_activities.descr,phpgw_p_hours.status, "
						. "phpgw_p_hours.start_date,phpgw_p_hours.end_date,phpgw_p_hours.minutes,phpgw_p_hours.minperae,phpgw_p_hours.billperae FROM "
						. "phpgw_p_hours " . $this->return_join() . " phpgw_p_activities ON phpgw_p_hours.activity_id=phpgw_p_activities.id "
						. $this->return_join() . " phpgw_p_invoicepos ON phpgw_p_invoicepos.hours_id=phpgw_p_hours.id WHERE "
						. "(phpgw_p_hours.project_id='" . $project_id . "'" . $parent_search . ") AND phpgw_p_invoicepos.invoice_id='"
						. $invoice_id . "'" . $ordermethod,__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'hours_id'		=> $this->db->f('id'),
					'hours_descr'	=> $this->db->f('hours_descr'),
					'act_descr'		=> $this->db->f('descr'),
					'status'		=> $this->db->f('status'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'minutes'		=> $this->db->f('minutes'),
					'minperae'		=> $this->db->f('minperae'),
					'billperae'		=> $this->db->f('billperae'),
					'employee'		=> $this->db->f('employee')
				);
			}
			return $hours;
		}

		function read_single_invoice($invoice_id)
		{
			$this->db->query("SELECT phpgw_p_invoice.customer,phpgw_p_invoice.num,phpgw_p_invoice.project_id,phpgw_p_invoice.date,"
							. "phpgw_p_invoice.sum,phpgw_p_projects.title,phpgw_p_projects.num as pnum FROM phpgw_p_invoice,phpgw_p_projects WHERE "
							. "phpgw_p_invoice.id='" . $invoice_id . "' AND phpgw_p_invoice.project_id=phpgw_p_projects.id",__LINE__,__FILE__);

			if ($this->db->next_record())
			{
				$bill['date']			= $this->db->f('date');
				$bill['invoice_num']	= $this->db->f('num');
				$bill['title']			= $this->db->f('title');
				$bill['customer']		= $this->db->f('customer');
				$bill['project_id']		= $this->db->f('project_id');
				$bill['project_num']	= $this->db->f('pnum');
				$bill['sum']			= $this->db->f('sum');
			}
			return $bill;
		}

		function read_invoice_pos($invoice_id)
		{
			$this->db->query("SELECT phpgw_p_hours.minutes,phpgw_p_hours.minperae,phpgw_p_hours.hours_descr,phpgw_p_hours.billperae,"
					. "phpgw_p_activities.descr,phpgw_p_hours.start_date,phpgw_p_hours.end_date FROM phpgw_p_hours,phpgw_p_activities,"
					. "phpgw_p_invoicepos WHERE phpgw_p_invoicepos.hours_id=phpgw_p_hours.id AND phpgw_p_invoicepos.invoice_id='"
					. $invoice_id . "' AND phpgw_p_hours.activity_id=phpgw_p_activities.id",__LINE__,__FILE__);

			while ($this->db->next_record())
			{
				$hours[] = array
				(
					'hours_descr'	=> $this->db->f('hours_descr'),
					'act_descr'		=> $this->db->f('descr'),
					'sdate'			=> $this->db->f('start_date'),
					'edate'			=> $this->db->f('end_date'),
					'minutes'		=> $this->db->f('minutes'),
					'minperae'		=> $this->db->f('minperae'),
					'billperae'		=> $this->db->f('billperae')
				);
			}
			return $hours;
		}
	}
?>
