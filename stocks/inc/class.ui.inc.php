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

	class ui
	{
		var $public_functions = array
		(
			'index'			=> True,
			'preferences'	=> True
		);

		function ui()
		{
			$this->bo			= CreateObject('stocks.bo');
			$this->t			= $GLOBALS['phpgw']->template;
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
		}

		function set_app_langs()
		{
			$this->t->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->t->set_var('tr_color1',$GLOBALS['phpgw_info']['theme']['row_on']);
			$this->t->set_var('tr_color2',$GLOBALS['phpgw_info']['theme']['row_off']);
			$this->t->set_var('lang_company',lang('Company name'));
			$this->t->set_var('lang_symbol',lang('Symbol'));
			$this->t->set_var('lang_edit',lang('Edit'));
			$this->t->set_var('lang_add',lang('Add'));
			$this->t->set_var('lang_add_stock',lang('Add new stock'));
			$this->t->set_var('lang_delete',lang('Delete'));
		}

		function return_html($quotes)
		{
			$return_html = '<table cellspacing="1" cellpadding="0" border="0" bgcolor="black"><tr><td>'
			. '<table cellspacing="1" cellpadding="2" border="0" bgcolor="white">'
			. '<tr><td><b>' . lang('Name') . '</b></td><td><b>' . lang('Symbol') . '</b></td><td align="right"><b>' . lang('Price') . '</b></td><td align="right">'
			. '<b>&nbsp;' . lang('Change') . '</b></td><td align="right"><b>' . lang('%') . '&nbsp;' . lang('Change') . '</b></td><td align="center"><b>' . lang('Date') . '</b></td><td align="center">'
					. '<b>' . lang('Time') . '</b></td></tr>';

			for ($i=0;$i<count($quotes);$i++)
			{
				$q = $quotes[$i];
				$symbol = $q['symbol'];
				$name = $q['name'];
				$price0 = $q['price0']; // todays price
				$price1 = $q['price1'];
				$price2 = $q['price2'];
				$dollarchange = $q['dchange'];
				$percentchange = $q['pchange'];
				$date = $q['date'];
				$time = $q['time'];
				$volume = $q['volume'];

				if ($dollarchange < 0)
				{
					$color = 'red';
				}
				else
				{
					$color = 'green';
				}

				$return_html .= '<tr><td>' . $name . '</td><td>' . $symbol . '</td><td align="right">' . $price0 . '</td><td align="right"><font color="'
					. $color . '">' . $dollarchange . '</font></td><td align="right"><font color="' . $color . '">' . $percentchange
					. '</font></td><td align="center">' . $date . '</td><td align="center">' . $time . '</td></tr>';
			}

			$return_html .= '</table></td></tr></table>';
			return $return_html;
		}

		function return_quotes()
		{	
			$stocklist = $this->bo->get_savedstocks();
			$quotes = $this->bo->get_quotes($stocklist);
			return $this->return_html($quotes);
		}

		function index()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file(array('quotes_list' => 'main.tpl'));
			$this->t->set_var('quotes',$this->return_quotes());
			$this->t->pfp('out','quotes_list');
		}

		function preferences()
		{
			$action		= $GLOBALS['HTTP_GET_VARS']['action'] ? $GLOBALS['HTTP_GET_VARS']['action'] : $GLOBALS['HTTP_POST_VARS']['action'];
			$name		= $GLOBALS['HTTP_POST_VARS']['name'];
			$symbol		= $GLOBALS['HTTP_POST_VARS']['symbol'];
			$mainscreen = $GLOBALS['HTTP_POST_VARS']['mainscreen'];
			$stock_id	= $GLOBALS['HTTP_POST_VARS']['stock_id'];
			$submit		= $GLOBALS['HTTP_POST_VARS']['submit'];

			if ($submit)
			{
				$this->bo->save_stock(array('access' => 'public','name' => $name,'symbol' => $symbol));
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			if ($action == 'delete')
			{
				$this->bo->delete_stock($stock_id);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			if ($mainscreen)
			{
				$GLOBALS['phpgw']->preferences->read_repository();
				if ($mainscreen == 'enable')
				{
					$GLOBALS['phpgw']->preferences->delete('stocks','mainscreen');
					$GLOBALS['phpgw']->preferences->add('stocks','mainscreen','enabled');
				}

				if ($mainscreen == 'disable')
				{
					$GLOBALS['phpgw']->preferences->delete('stocks','mainscreen');
					$GLOBALS['phpgw']->preferences->add('stocks','mainscreen','disabled');
				}

				$GLOBALS['phpgw']->preferences->save_repository(True);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file(array('stock_prefs' => 'preferences.tpl',
								'stock_prefs_t' => 'preferences.tpl'));
			$this->t->set_block('stock_prefs_t','stock_prefs','prefs');

			$this->set_app_langs();

			$prefs = $this->bo->read_prefs();

			$hidden_vars = '<input type="hidden" name="symbol" value="' . $symbol . '">' . "\n"
						. '<input type="hidden" name="name" value="' . $name . '">' . "\n"
						. '<input type="hidden" name="stock_id" value="' . $stock_id . '">' . "\n";

			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/stocks/preferences.php'));
			$this->t->set_var('lang_action',lang('Stock Quote preferences'));
			$this->t->set_var('h_lang_edit',lang('Edit'));
			$this->t->set_var('hidden_vars',$hidden_vars);
			$this->t->set_var('h_lang_delete',lang('Delete'));

			$stocks = $this->bo->read_stocks();

			if (is_array($stocks))
			{
				while (list($null,$stock) = each($stocks))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);

					$this->t->set_var(array
					(
						'dsymbol' => rawurldecode($stock['symbol']),
						'dname' => rawurldecode($stock['name'])
					));

					$this->t->set_var('edit',$GLOBALS['phpgw']->link('/stocks/preferences_edit.php','sym=' . $dsymbol));
					$this->t->set_var('delete',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences&action=delete&stock_id='
												. $stock['id']));
					$this->t->fp('prefs','stock_prefs',True);
				}
			}

			if ($prefs['mainscreen'] == 'enabled')
			{
				$this->t->set_var('lang_display',lang('Display stocks on main screen is enabled'));
				$newstatus = 'disable';
			}
			else
			{
				$this->t->set_var('lang_display',lang('Display stocks on main screen is disabled'));
				$newstatus = 'enable';
			}

			$this->t->set_var('newstatus',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences&mainscreen=' . $newstatus));
			$this->t->set_var('lang_newstatus',lang($newstatus));

			$this->t->set_var('add_action',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.preferences&name=' . $name
																	. '&symbol=' . $symbol));
			$this->t->pfp('out','stock_prefs_t',True);
		}
	}
?>
