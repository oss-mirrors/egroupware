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
			'preferences'	=> True,
			'edit_stock'	=> True,
			'list_stocks'	=> True,
			'add_stock'		=> True
		);

		function ui()
		{
			$this->bo			= CreateObject('stocks.bo');
			$this->t			= $GLOBALS['phpgw']->template;
			$this->sbox			= CreateObject('phpgwapi.sbox');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
			$this->country		= $this->bo->country;
		}

		function save_sessiondata()
		{
			$data = array
			(
				'country'	=> $this->country
			);
			$this->bo->save_sessiondata($data);
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
			$this->t->set_var('lang_country',lang('Country'));
			$this->t->set_var('lang_add_stock',lang('Add new stock'));
			$this->t->set_var('lang_delete',lang('Delete'));
			$this->t->set_var('lang_save',lang('Save'));
			$this->t->set_var('lang_stocks',lang('Stock Quotes'));
			$this->t->set_var('lang_done',lang('Done'));
			$this->t->set_var('lang_submit',lang('Submit'));
			$this->t->set_var('lang_select_country',lang('Select country'));
		}

		function display_app_header()
		{
			$this->t->set_file(array('header' => 'header.tpl'));
			$this->t->set_block('header','stock_header');

			$this->set_app_langs();

			$this->t->set_var('link_stocks',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.list_stocks'));
			$this->t->set_var('lang_select_stocks',lang('Select stocks to display'));

			$this->t->fp('app_header','stock_header');

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
		}

		function return_html($quotes)
		{
			$return_html = '<table cellspacing="1" cellpadding="0" border="0" bgcolor="black"><tr><td>'
			. '<table cellspacing="1" cellpadding="2" border="0" bgcolor="white">'
			. '<tr><td><b>' . lang('Name') . '</b></td><td><b>' . lang('Symbol') . '</b></td><td align="right"><b>' . lang('Price') . '</b></td><td align="right">'
			. '<b>&nbsp;' . lang('Change') . '</b></td><td align="right"><b>&nbsp;%' . lang('Change') . '</b></td><td align="center"><b>' . lang('Date') . '</b></td><td align="center">'
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

		function selected_country()
		{
			switch($this->country)
			{
				case 'US': $country_sel[0]=' selected'; break;
				case 'DE': $country_sel[1]=' selected'; break;
			}

			$country_list = '<option value="US"' . $country_sel[0] . '>' . lang('united states') . '</option>' . "\n"
				. '<option value="DE"' . $country_sel[1] . '>' . lang('germany') . '</option>' . "\n"
				. '<option value="">' . lang('Select country') . '</option>' . "\n";

			return $country_list;
		}

		function index()
		{
			$country	= $GLOBALS['HTTP_GET_VARS']['country'];

			$this->display_app_header();
			$this->t->set_file(array('quotes_list' => 'main.tpl'));
			$this->t->set_var('country_list',$this->selected_country($this->country));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.index&country=' . $this->country));
			$this->t->set_var('quotes',$this->return_quotes());
			$this->t->pfp('out','quotes_list');
			$this->save_sessiondata();
		}

		function list_stocks()
		{
			$action 	= $GLOBALS['HTTP_GET_VARS']['action'] ? $GLOBALS['HTTP_GET_VARS']['action'] : $GLOBALS['HTTP_POST_VARS']['action'];
			$stock_id	= $GLOBALS['HTTP_GET_VARS']['stock_id'];

			$link_data = array
			(
				'menuaction'	=> 'stocks.ui.list_stocks',
				'country'		=> $this->country
			);

			if ($action == 'delete')
			{
				$this->bo->delete_stock($stock_id);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->display_app_header();

			$this->t->set_file(array('stock_list_t' => 'list.tpl'));
			$this->t->set_block('stock_list_t','stock_list','list');

			$this->t->set_var('lang_list',lang('Stock Quotes list'));
			$this->t->set_var('h_lang_edit',lang('Edit'));
			$this->t->set_var('h_lang_delete',lang('Delete'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			if (!$country)
			{
				$country = '';
			}

			$this->t->set_var('country_list',$this->selected_country($this->country));

			$stocks = $this->bo->read_stocks();

			if (is_array($stocks))
			{
				while (list($null,$stock) = each($stocks))
				{
					$this->nextmatchs->template_alternate_row_color(&$this->t);

					$this->t->set_var(array
					(
						'ssymbol' => $GLOBALS['phpgw']->strip_html($stock['symbol']),
						'sname' => $GLOBALS['phpgw']->strip_html($stock['name']),
						'scountry' => $this->sbox->get_full_name($stock['country'])
					));

					$this->t->set_var('delete',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.list_stocks&action=delete&stock_id='
																		. $stock['id']));

					$this->t->set_var('edit',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.edit_stock&stock_id='
																	. $stock['id']));

					$this->t->fp('list','stock_list',True);
				}
			}
			$link_data['menuaction'] = 'stocks.ui.add_stock';
			$this->t->set_var('addurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('doneurl',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.index'));
			$this->save_sessiondata();
			$this->t->pfp('out','stock_list_t',True);
		}

		function preferences()
		{
			$prefs = $GLOBALS['HTTP_POST_VARS']['prefs'];

			$link_data = array
			(
				'menuaction' => 'stocks.ui.preferences'
			);

			if ($prefs['submit'])
			{
				$this->bo->save_prefs($prefs);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file(array('stock_prefs' => 'preferences.tpl'));

			$this->set_app_langs();

			$prefs = $this->bo->read_prefs();

			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));
			$this->t->set_var('lang_action',lang('Stock Quote preferences'));
			$this->t->set_var('lang_def_country',lang('Default country'));
			$this->t->set_var('lang_display',lang('Display stocks on main screen is enabled'));
			$this->t->set_var('mainscreen', '<input type="checkbox" name="prefs[mainscreen]" value="True"'
										. ($prefs['mainscreen'] == 'enabled'?' checked':'') . '>');

			$this->t->set_var('country_list',$this->selected_country($prefs['country']));

			$this->t->set_var('doneurl',$GLOBALS['phpgw']->link('/preferences/index.php'));
			$this->t->pfp('out','stock_prefs',True);
		}

		function add_stock()
		{
			$submit		= $GLOBALS['HTTP_POST_VARS']['submit'];
			$values		= $GLOBALS['HTTP_POST_VARS']['values'];

			if ($submit)
			{
				$values['symbol']	= strtoupper($values['symbol']);
				$values['access']	= 'public';
				$this->bo->save_stock($values);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.list_stocks'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->display_app_header();
			$this->t->set_file(array('edit' => 'preferences_edit.tpl'));
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php','menuaction=stocks.ui.add_stock'));
			$this->t->set_var('h_lang_edit',lang('Add stock'));
			$this->t->set_var('country_list',$this->selected_country($country));
			$this->t->set_var('symbol',$symbol);
			$this->t->set_var('name',$name);

			$this->t->pfp('out','edit');
		}

		function edit_stock()
		{
			$submit		= $GLOBALS['HTTP_POST_VARS']['submit'];
			$values		= $GLOBALS['HTTP_POST_VARS']['values'];
			$stock_id	= $GLOBALS['HTTP_GET_VARS']['stock_id'];

			$link_data = array
			(
				'menuaction'	=> 'stocks.ui.list_stocks',
				'stock_id'		=> $stock_id
			);

			if (! $stock_id)
			{
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			if ($submit)
			{
				$values['symbol']	= strtoupper($values['symbol']);
				$values['access']	= 'public';
				$values['id']		= $stock_id;

				$this->bo->save_stock($values);
				Header('Location: ' . $GLOBALS['phpgw']->link('/index.php',$link_data));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->display_app_header();

			$this->t->set_file(array('edit' => 'preferences_edit.tpl'));
			$link_data['menuaction'] = 'stocks.ui.edit_stock';
			$this->t->set_var('actionurl',$GLOBALS['phpgw']->link('/index.php',$link_data));

			$this->t->set_var('hidden_vars','<input type="hidden" name="stock_id" value="' . $stock_id . '">' . "\n");
			$this->t->set_var('h_lang_edit',lang('Edit stock'));

			$stock = $this->bo->read_single($stock_id);
			$this->t->set_var('country_list',$this->selected_country($stock['country']));
			$this->t->set_var('symbol',$GLOBALS['phpgw']->strip_html($stock['symbol']));
			$this->t->set_var('name',$GLOBALS['phpgw']->strip_html($stock['name']));

			$this->t->pfp('out','edit');
		}
	}
?>
