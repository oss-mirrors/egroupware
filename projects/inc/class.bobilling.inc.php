<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000, 2001 Bettina Gille                            *
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

	class bobilling
	{
		var $public_functions = array
		(
			'read_invoices'			=> True,
			'check_values'			=> True,
			'read_hours'			=> True,
			'read_invoice_hours'	=> True,
			'read_invoice_pos'		=> True,
			'invoice'				=> True,
			'update_invoice'		=> True,
			'read_single_invoice'	=> True
		);

		function bobilling()
		{
			$this->sobilling	= CreateObject('projects.sobilling');
			$this->soprojects	= CreateObject('projects.soprojects');
			$this->contacts		= CreateObject('phpgwapi.contacts');
		}

		function read_invoices($start, $query, $sort, $order, $limit, $project_id)
		{
			$bill = $this->sobilling->read_invoices($start, $query, $sort, $order, $limit, $project_id);
			$this->total_records = $this->sobilling->total_records;
			return $bill;
		}

		function read_single_invoice($invoice_id)
		{
			$bill = $this->sobilling->read_single_invoice($invoice_id);
			return $bill;
		}

		function check_values($values,$select)
		{
			if (!$values['choose'])
			{
				if (!$values['invoice_num'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$num = $this->sobilling->exists($values['invoice_num']);
					if ($num)
					{
						$error[] = lang('That ID has been used already !');
					}
				}
			}

			if (! is_array($select))
			{
				$error[] = lang('The invoice contains no items !');				
			}

			if (! $values['customer'])
			{
				$error[] = lang('You have no customer selected !');
			}

			if (! checkdate($values['month'],$values['day'],$values['year']))
			{
				$error[] = lang('You have entered an invalid date !');
			}

			if (is_array($error))
			{
				return $error;
			}
		}

		function invoice($values,$select)
		{
			if ($values['choose'])
			{
				$values['invoice_num'] = $this->soprojects->create_invoiceid();
			}

			$values['date'] = mktime(2,0,0,$values['month'],$values['day'],$values['year']);

			$invoice_id = $this->sobilling->invoice($values,$select);
			return $invoice_id;
		}

		function update_invoice($values,$select)
		{
			$values['date'] = mktime(2,0,0,$values['month'],$values['day'],$values['year']);

			$this->sobilling->update_invoice($values,$select);
		}

		function read_hours($project_id)
		{
			$hours = $this->sobilling->read_hours($project_id);
			return $hours;
		}

		function read_invoice_hours($project_id,$invoice_id)
		{
			$hours = $this->sobilling->read_invoice_hours($project_id,$invoice_id);
			return $hours;
		}

		function read_invoice_pos($invoice_id)
		{
			$hours = $this->sobilling->read_invoice_pos($invoice_id);
			return $hours;
		}
	}
?>
