<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
	/* $Id$ */

	$phpgw_flags = Array(
		'currentapp'		=> 'calendar',
		'enable_nextmatchs_class'	=> True,
		'admin_header'		=>	True,
		'noheader'			=> True,
		'nonavbar'			=> True,
		'noappheader'		=> True,
		'noappfooter'		=> True,
		'parent_page'		=> 'holiday_admin.php'
	);
	$phpgw_info['flags'] = $phpgw_flags;
	include('../header.inc.php');

	if(!isset($start))
	{
		$start = 0;
	}
	
	function country_total($locale,$query)
	{
		global $phpgw;

		$querymethod='';
		if($query)
		{
			$querymethod = ' AND '.$query;
		}
		$phpgw->db->query("SELECT count(*) FROM phpgw_cal_holidays WHERE locale='".$locale."'".$querymethod,__LINE__,__FILE__);
		$phpgw->db->next_record();
		return intval($phpgw->db->f(0));
//		$count = 0;
//		while($phpgw->db->next_record())
//		{
//			$count++;
//		}
//		return $count;
	}

	function get_holiday_list($locale, $sort, $order, $query, $total)
	{
		global $phpgw;
		
		$querymethod = '';

		if($query)
		{
			$querymethod .= ' AND '.$query;
		}
		
		if($order)
		{
			$querymethod .= ' ORDER BY '.$order;
		}
		$phpgw->db->query("SELECT hol_id,name FROM phpgw_cal_holidays WHERE locale='".$locale."'".$querymethod,__LINE__,__FILE__);
		while($phpgw->db->next_record())
		{
			$holiday[$phpgw->db->f('hol_id')] = $phpgw->strip_html($phpgw->db->f('name'));
		}
		return $holiday;
	}

	if($query)
	{
		$query = str_replace('=',"='",$query)."'";
	}
	$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$templates = Array(
		'locale'	=> 'locales.tpl'
	);
	$p->set_file($templates);
	$p->set_block('locale','list','list');
	$p->set_block('locale','row','row');
	$p->set_block('locale','row_empty','row_empty');

	$total = country_total($locale,$query);
	if(!$total && !isset($query))
	{
		Header('Location: ' . $phpgw->link('/calendar/holiday_admin.php'));
	}
 
	$p->set_var('th_bg',$phpgw_info['theme']['th_bg']);

	$p->set_var('left_next_matchs',$phpgw->nextmatchs->left('/calendar/'.basename($SCRIPT_FILENAME),$start,$total,'&locale='.$locale));
	$p->set_var('right_next_matchs',$phpgw->nextmatchs->right('/calendar/'.basename($SCRIPT_FILENAME),$start,$total,'&locale='.$locale));
	$p->set_var('lang_groups',lang('Holidays').' ('.$locale.')');

	$p->set_var('sort_name',$phpgw->nextmatchs->show_sort_order($sort,'name',$order,'/calendar/'.basename($SCRIPT_FILENAME),lang('Holiday'),'&locale='.$locale));
	$p->set_var('header_edit',lang('Edit'));
	$p->set_var('header_delete',lang('Delete'));

	$holidays = get_holiday_list($locale, $sort, $order, $query, $total);

	if (!count($holidays))
	{
		$p->set_var('message',lang('No matchs found'));
		$p->parse('rows','row_empty',True);
	}
	else
	{
		while (list($index,$name) = each($holidays))
		{
			$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
			$p->set_var('tr_color',$tr_color);
			
			if (! $name)  $name  = '&nbsp;';

			$p->set_var('group_name',$name); 
			$p->set_var('edit_link','<a href="' . $phpgw->link('/calendar/editholiday.php','locale='.$locale.'&id='.$index) . '"> ' . lang('Edit') . ' </a>');
			$p->set_var('delete_link','<a href="' . $phpgw->link('/calendar/deleteholiday.php','locale='.$locale.'&id='.$index) . '"> ' . lang('Delete') . ' </a>');
			$p->parse('rows','row',True);

		}
	}

	$p->set_var('new_action',$phpgw->link('/calendar/editholiday.php','locale='.$locale.'&id=0'));
	$p->set_var('lang_add',lang('add'));

	$p->set_var('back_action',$phpgw->link('/calendar/holiday_admin.php'));
	$p->set_var('lang_back',lang('Back'));

	$p->set_var('search_action',$phpgw->link('/calendar/editlocale.php'));
	$p->set_var('lang_search',lang('search'));

	$phpgw->common->phpgw_header();
	echo parse_navbar();
	$p->pparse('out','list');
	$phpgw->common->phpgw_footer();
?>
