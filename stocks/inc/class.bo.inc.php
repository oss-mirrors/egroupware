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

	class bo
	{
		function bo()
		{
			$this->so		= CreateObject('stocks.so');
			$this->network	= CreateObject('phpgwapi.network');
		}

		// return content of a url as a string array
		function http_fetch($url,$post,$port,$proxy)
		{
	 		return $this->network->gethttpsocketfile($url);
		}

		function get_quotes($stocklist)
		{
			if (! $stocklist)
			{
				return array();
			}

			while (list($symbol,$name) = each($stocklist))
			{
				$symbollist[] = $symbol;
				$symbol = rawurldecode($symbol);
				$symbolstr .= $symbol;

				if ($i++<count($stocklist)-1)
				{
					$symbolstr .= '+';
				}
			}

			$regexp_stocks = '/(' . implode('|',$symbollist) . ')/';

			$url = 'http://finance.yahoo.com/d/quotes.csv?f=sl1d1t1c1ohgv&e=.csv&s=' . $symbolstr;
			$lines = $this->http_fetch($url,false,80,'');

			$quotes = array();
			$i = 0;

			if ($lines)
			{
				while ($line = each($lines))
				{
					$line = $lines[$i];

					if (preg_match($regexp_stocks,$line))
					{
						$line = ereg_replace('"','',$line);
						list($symbol,$price0,$date,$time,$dchange,$price1,$price2) = split(',',$line);

						if ($price1>0 && $dchange!=0)
						{
							$pchange = round(10000*($dchange)/$price1)/100;
						}
						else
						{
							$pchange = 0;
						}

						if ($pchange>0)
						{
							$pchange = '+' . $pchange;
						}

						$name = $stocklist[$symbol];

						if (! $name)
						{
							$name = $symbol;
						}

						$quotes[] = array
						(
							'symbol'	=> $symbol,
							'price0'	=> $price0,
							'date'		=> $date,
							'time'		=> $time,
							'dchange'	=> $dchange,
							'price1'	=> $price1,
							'price2'	=> $price2,
							'pchange'	=> $pchange,
							'name'		=> $name
						);
					}
					$i++;
				}
				return $quotes;
			}
		}

		function read_prefs()
		{
			$GLOBALS['phpgw']->preferences->read_repository();

			$prefs = array();

			if ($GLOBALS['phpgw_info']['user']['preferences']['stocks'])
			{
				$prefs['mainscreen']	= $GLOBALS['phpgw_info']['user']['preferences']['stocks']['mainscreen'];
				$prefs['LNUX']			= $GLOBALS['phpgw_info']['user']['preferences']['stocks']['LNUX'];
				$prefs['RHAT']			= $GLOBALS['phpgw_info']['user']['preferences']['stocks']['RHAT'];
			}
			else
			{
				$prefs['mainscreen']	= 'enabled';
				$prefs['LNUX']			= 'VA%20Linux';
				$prefs['RHAT']			= 'RedHat';
			}
			return $prefs;
		}

		function read_stocks()
		{
			return $this->so->read_stocks();
		}

		function read_single($stock_id)
		{
			return $this->so->read_single($stock_id);
		}

		function save_stock($values)
		{
			if ($values['id'] && $values['id'] != 0)
			{
				$this->so->edit_stock($values);
			}
			else
			{
				$this->so->add_stock($values);
			}
		}

		function delete_stock($stock_id)
		{
			$this->so->delete_stock($stock_id);
		}

		function get_savedstocks()
		{
			$stocks = $this->read_stocks();

			if (is_array($stocks))
			{
				while (list($null,$stock) = each($stocks))
				{
					$symbol = rawurldecode($stock['symbol']);
					$name = rawurldecode($stock['name']);

					if ($symbol)
					{
						if (! $name)
						{
							$name = $symbol;
						}
						$stocklist[$symbol] = $name;
					}
				}
			}
			else
			{
				$prefs = $this->read_prefs();
				$stocklist['LNUX'] = rawurldecode($prefs['LNUX']);
				$stocklist['RHAT'] = rawurldecode($prefs['RHAT']);
			}
			return $stocklist;
		}
	}
?>
