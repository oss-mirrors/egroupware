<?php
	/**************************************************************************\
	* phpGroupWare API - nextmatchs								*
	* This file written by Joseph Engo <jengo@phpgroupware.org>			*
	* with email only additions by Angles <angles@aminvestments.com>		*
	* Handles limiting number of rows displayed						*
	* Small Email Only changes adapted from phpGroupWare API			*
	* file by Joseph Engo <jengo@phpgroupware.org>					*
	* Copyright (C) 2000, 2001 Joseph Engo							*
	* Email only additions Copyright (C) 2002 Angelo "Angles" Puglisi			*
	* -------------------------------------------------------------------------		*
	* This library is part of the phpGroupWare API						*
	* http://www.phpgroupware.org/api								* 
	* ------------------------------------------------------------------------ 		*
	* This library is free software; you can redistribute it and/or modify it		*
	* under the terms of the GNU Lesser General Public License as published by 	*
	* the Free Software Foundation; either version 2.1 of the License,			*
	* or any later version.											*
	* This library is distributed in the hope that it will be useful, but			*
	* WITHOUT ANY WARRANTY; without even the implied warranty of	*
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	*
	* See the GNU Lesser General Public License for more details.			*
	* You should have received a copy of the GNU Lesser General Public License 	*
	* along with this library; if not, write to the Free Software Foundation, 		*
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA			*
	\**************************************************************************/

	/* $Id$ */
	/* $Source$ */

	/*!
	@class svc_nextmatches
	@abstract service class for email, implements nextmatches that ONLY EMAIL needs
	*/
	class svc_nextmatches
	{
		var $maxmatches;
		var $action;
		var $template;
		var $extra_filters = array();
		
		// fallback value, prefs will fill this later
 		//var $icon_size='16';
 		var $icon_size='24';
 		
		// fallback value, prefs will fill this later
 		//var $icon_theme='evo';
 		var $icon_theme='moz';
		
		function svc_nextmatches($website=False)
		{
			if(isset($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']) &&
				intval($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']) > 0)
			{
				$this->maxmatches = intval($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs']);
			}
			else
			{
				$this->maxmatches = 15;
			}

			if(isset($GLOBALS['phpgw']->msg->ref_GET['menuaction']))
			{
				$this->action = $GLOBALS['phpgw']->msg->ref_GET['menuaction'];
			}
		}
		
		
		function extras_to_string($extra)
		{
			if(is_array($extra))
			{
				@reset($extra);
				while(list($var,$value) = each($extra))
				{
					$t_extras[] = $var . '=' . $value;
				}
				$extra_s = '&' . implode('&',$t_extras);
			}
			return $extra_s;
		}

		/*!
		@function page
		@abstract ?
		*/
		function page($extravars='')
		{
			if($extravars && is_string($extravars) && substr($extravars,0,1)!='&')
			{
				$extras = '&'.$extravars;
			}
			elseif($extravars && is_array($extravars))
			{
				@reset($extravars);
				while(list($var,$value) = each($extravars))
				{
					if($var != 'menuaction')
					{
						$t_extras[] = $var.'='.$value;
					}
				}
				$extras = implode($t_extras,'&');
			}

			return $GLOBALS['phpgw']->link('/index.php','menuaction='.$this->action.$extras);
		}


		
		
		/*!
		@function show_sort_order_mail
		@abstract ?
		@param $old_sort : the current sort value
		@param $new_sort : the sort value you want if you click on this
		@param $default_order : users preference for ordering list items (force this when a new [different] sorting is requested)
		@param $order : the current order (will be flipped if old_sort = new_sort)
		@param $program : script file name
		@param $text : Text the link will show
		@param $extra : any extra stuff you want to pass, url style
		*/
		function show_sort_order_mail($old_sort,$new_sort,$default_order,$order,$program,$text,$extra='')
		{
			if(is_array($extra))
			{
				$extra = $this->extras_to_string($extra);
			}
			if($old_sort == $new_sort)
			{
				// alternate order, like on outkrook, click on present sorting reverses order
				if((int)$order == 1)
				{
					$our_order = 0;
				}
				elseif((int)$order == 0)
				{
					$our_order = 1;
				}
				else
				{
					// we should never get here
					$our_order = 1;
				}
			}
			else
			{
				//user has selected a new sort scheme, reset the order to users default
				$our_order = $default_order;
			}
			
			/*
			//$prog = explode('?',$program);
			//$extravar = $prog[1].'&order='.$our_order.'&sort='.$new_sort.$extra;
			//// this was b0rking menuaction when NOT using redirect, instead using direct object calls to goto the next page
			//// in thise cases the menuaction that appears in the url remains from the PREVIOUS action, not the object call produced next page
			////$link = ($this->action?$this->page($extravar):$GLOBALS['phpgw']->link($program,$extravar));
			////$link = $GLOBALS['phpgw']->link($program,'email.index.uiindex'.$extravar);
			//$link = $GLOBALS['phpgw']->link($prog[0],$extravar);
			//return '<a href="' .$link .'">' .$text .'</a>';
			
			// get rid of setup specified "your server name" because the link below will 
			// add it back
			*/
			
			//echo 'show_sort_order_mail: $program ['.serialize($program).'] <br>';
			//echo 'show_sort_order_mail: $extra ['.serialize($extra).'] <br>';
			$prog = explode('?',$program);
			//echo 'show_sort_order_mail: $prog ['.serialize($prog).'] <br>';
			$extravar = $prog[1].'&order='.$our_order.'&sort='.$new_sort.$extra;
			//echo 'show_sort_order_mail: $extravar ['.serialize($extravar).'] <br>';
			
			// this was b0rking menuaction when NOT using redirect, instead using direct object calls to goto the next page
			// in thise cases the menuaction that appears in the url remains from the PREVIOUS action, not the object call produced next page
			//$link = ($this->action?$this->page($extravar):$GLOBALS['phpgw']->link($program,$extravar));
			//$link = $GLOBALS['phpgw']->link($program,'email.index.uiindex'.$extravar);
			$link = $GLOBALS['phpgw']->link($prog[0],$extravar);
			//echo 'show_sort_order_mail: $link ['.serialize($link).'] <br>';
			return '<a href="' .$link .'">' .$text .'</a>';

		}

		/*!
		@function nav_left_right_mail
		@abstract same code as left and right (as of Dec 07, 2001) except all combined into one function
		@param feed_vars : array with these elements: <br>
			start 
			total 
			cmd_prefix 
			cmd_suffix
		@return array, combination of functions left and right above, with these elements:
			first_page
			prev_page
			next_page
			last_page
		@author: jengo, some changes by Angles
		*/
		function nav_left_right_mail($feed_vars)
		{
			if ((@$GLOBALS['phpgw']->msg->get_isset_pref('icon_theme'))
			&& (@$GLOBALS['phpgw']->msg->get_isset_pref('icon_size')))
			{
				$this->icon_theme = $GLOBALS['phpgw']->msg->get_pref_value('icon_theme');
				$this->icon_size = $GLOBALS['phpgw']->msg->get_pref_value('icon_size');
			}
			//echo "icon size is ".$this->icon_size."<br>\r\n";
			
			$return_array = Array(
				'first_page' => '',
				'prev_page'  => '',
				'next_page'  => '',
				'last_page'  => ''
			);
			$out_vars = array();
			// things that might change
			$out_vars['start'] = $feed_vars['start'];
			// things that stay the same
			$out_vars['common_uri'] = $feed_vars['common_uri'];
			$out_vars['total'] = $feed_vars['total'];

			// first page
			if(($feed_vars['start'] != 0) &&
				($feed_vars['start'] > $this->maxmatches))
			{
				$out_vars['start'] = 0;
				$return_array['first_page'] = $this->set_link_mail('left',$this->icon_theme.'-arrow-2left-'.$this->icon_size.'.gif',lang('First page'),$out_vars);
			}
			else
			{
				$return_array['first_page'] = $this->set_icon_mail('left',$this->icon_theme.'-arrow-2left-no-'.$this->icon_size.'.gif',lang('First page'));
			}
			// previous page
			if($feed_vars['start'] != 0)
			{
				// Changing the sorting order screaws up the starting number
				if(($feed_vars['start'] - $this->maxmatches) < 0)
				{
					$out_vars['start'] = 0;
				}
				else
				{
					$out_vars['start'] = ($feed_vars['start'] - $this->maxmatches);
				}
				$return_array['prev_page'] = $this->set_link_mail('left',$this->icon_theme.'-arrow-left-'.$this->icon_size.'.gif',lang('Previous page'),$out_vars);
			}
			else
			{
				$return_array['prev_page'] = $this->set_icon_mail('left',$this->icon_theme.'-arrow-left-no-'.$this->icon_size.'.gif',lang('Previous page'));
			}

			// re-initialize the out_vars
			// things that might change
			$out_vars['start'] = $feed_vars['start'];
			// next page
			if(($feed_vars['total'] > $this->maxmatches) &&
				($feed_vars['total'] > $feed_vars['start'] + $this->maxmatches))
			{
				$out_vars['start'] = ($feed_vars['start'] + $this->maxmatches);
				$return_array['next_page'] = $this->set_link_mail('right',$this->icon_theme.'-arrow-right-'.$this->icon_size.'.gif',lang('Next page'),$out_vars);
			}
			else
			{
				$return_array['next_page'] = $this->set_icon_mail('right',$this->icon_theme.'-arrow-right-no-'.$this->icon_size.'.gif',lang('Next page'));
			}
			// last page
			if(($feed_vars['start'] != $feed_vars['total'] - $this->maxmatches) &&
				(($feed_vars['total'] - $this->maxmatches) > ($feed_vars['start'] + $this->maxmatches)))
			{
				$out_vars['start'] = ($feed_vars['total'] - $this->maxmatches);
				$return_array['last_page'] = $this->set_link_mail('right',$this->icon_theme.'-arrow-2right-'.$this->icon_size.'.gif',lang('Last page'),$out_vars);
			}
			else
			{
				$return_array['last_page'] = $this->set_icon_mail('right',$this->icon_theme.'-arrow-2right-no-'.$this->icon_size.'.gif',lang('Last page'));
			}
			return $return_array;
		}
		
		/*!
		@function set_link_mail
		@abstract ?
		@param $img_src ?
		@param $label ?
		@param $link ?
		@param $extravars ?
		*/
		function set_link_mail($align,$img,$alt_text,$out_vars)
		{
			$img_full = $GLOBALS['phpgw']->common->image('email',$img);
			$image_part = '<img src="'.$img_full.'" border="0" alt="'.$alt_text.'">';
			return '<a href="'.$out_vars['common_uri'].'&start='.$out_vars['start'].'">'.$image_part.'</a>';
		}

		/*!
		@function set_icon_mail
		@abstract ?
		@param $align ?
		@param $img ?
		@param $alt_text ?
		*/
		function set_icon_mail($align,$img,$alt_text)
		{
			$img_full = $GLOBALS['phpgw']->common->image('email',$img);
			return '<img src="'.$img_full.'" border="0" alt="'.$alt_text.'">'."\r\n";
		}
	}
?>
