<?php
	/*******************************************************************\
	* phpGroupWare - Stock Quotes                                       *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* based on PStocks v.0.1                                            *
	* http://www.dansteinman.com/php/pstocks/                           *
	* Copyright (C) 1999 Dan Steinman (dan@dansteinman.com)             *
	*                                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2001,2002 Bettina Gille                             *
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

	class so
	{
		function so()
		{
			$this->db		= $GLOBALS['phpgw']->db;
			$this->account	= $GLOBALS['phpgw_info']['user']['account_id'];
		}

		function read_stocks()
		{
			$this->db->query("SELECT * from phpgw_stocks where stock_owner='" . $this->account . "'",__LINE__,__FILE__);

			while($this->db->next_record())
			{
				$stocks[] = array
				(
					'id'		=> $this->db->f('stock_id'),
					'owner'		=> $this->db->f('stock_owner'),
					'access'	=> $this->db->f('stock_access'),
					'name'		=> $this->db->f('stock_name'),
					'symbol'	=> $this->db->f('stock_symbol')
				);
			}
			return $stocks;
		}

		function read_single($stock_id)
		{
			$this->db->query("SELECT * from phpgw_stocks where stock_id='" . $stock_id . "'",__LINE__,__FILE__);

			if ($this->db->next_record())
			{
				$stock['id']		= $this->db->f('stock_id');
				$stock['owner']		= $this->db->f('stock_owner');
				$stock['access']	= $this->db->f('stock_access');
				$stock['name']		= $this->db->f('stock_name');
				$stock['symbol']	= $this->db->f('stock_symbol');
			}
			return $stock;
		}

		function add_stock($values)
		{
			$this->db->query("INSERT into phpgw_stocks (stock_owner,stock_access,stock_name,stock_symbol) values('"
							. $this->account . "','" . $values['access'] . "','" . $values['name'] . "','" . $values['symbol']
							. "')",__LINE__,__FILE__);
		}

		function delete_stock($stock_id)
		{
			$this->db->query("DELETE from phpgw_stocks where stock_id='" . $stock_id . "'",__LINE__,__FILE__);
		}

		function edit_stock($values)
		{
			$this->db->query("UPDATE phpgw_stocks set stock_name='" . $values['name'] . "', stock_symbol='" . $values['symbol']
							. "' where stock_id='" . $values['id'] . "'",__LINE__,__FILE__);
		}
	}
?>
