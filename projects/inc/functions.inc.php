<?php
	/**************************************************************************\                                            
	* phpGroupWare - projects                                                  *                                            
	* (http://www.phpgroupware.org)                                            *                                            
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         *                                            
	* --------------------------------------------------------                 *                                            
	* This program is free software; you can redistribute it and/or modify it  *                                            
	* under the terms of the GNU General Public License as published by the    *                                            
	* Free Software Foundation; either version 2 of the License, or (at your   *                                            
	* option) any later version.                                               *                                            
	\**************************************************************************/
	/* $Id$ */

	function isprojectadmin()
	{
		global $phpgw, $phpgw_info;

		$admin_groups = $phpgw->accounts->membership($phpgw_info['user']['account_id']);

		$phpgw->db->query("select account_id,type from phpgw_p_projectmembers WHERE type='aa' OR type='ag'");
		while ($phpgw->db->next_record())
		{
			$admins[] = array('account_id' => $phpgw->db->f('account_id'),
									'type' => $phpgw->db->f('type'));
		}

		for ($i=0;$i<count($admins);$i++)
		{
			if ($admins[$i]['type']=='aa')
			{
				if ($admins[$i]['account_id'] == $phpgw_info['user']['account_id'])
				return 1;
			}
			elseif ($admins[$i]['type']=='ag') 
			{
				if (is_array($admin_groups))
				{
					for ($j=0;$j<count($admin_groups);$j++)
					{
						if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
						return 1;
					}
				}
			}
			else
			{
				return 0;
			}
		}
	}

// returns project-,invoice- and delivery-ID

	$id_type = "hex";

	function add_leading_zero($num)  
	{
		global $id_type;

		if ($id_type == "hex")
		{
			$num = hexdec($num);
			$num++;
			$num = dechex($num);
		}
		else
		{
			$num++;
		}

		if (strlen($num) == 4)
			$return = $num;
		if (strlen($num) == 3)
			$return = "0$num";
		if (strlen($num) == 2)
			$return = "00$num";
		if (strlen($num) == 1)
			$return = "000$num";
		if (strlen($num) == 0)
			$return = "0001";

		return strtoupper($return);
	}

	$year = $phpgw->common->show_date(time(),'Y');

	function create_projectid($year)
	{
		global $phpgw, $year;

		$prefix = 'P-' . $year . '-';
		$phpgw->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
		$phpgw->db->next_record();
		$max = add_leading_zero(substr($phpgw->db->f(0),7));

		return $prefix . $max;
	}

	function create_jobid($pro_parent)
	{
		global $phpgw;

		$phpgw->db->query("select num from phpgw_p_projects where id='$pro_parent'");
		$phpgw->db->next_record();
		$prefix = $phpgw->db->f('num') . '/';

		$phpgw->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
		$phpgw->db->next_record();
		$max = add_leading_zero(substr($phpgw->db->f(0),8));

		return $prefix . $max;
	}

	function create_activityid($year)
	{
		global $phpgw, $year;

		$prefix = 'A-' . $year . '-';
		$phpgw->db->query("select max(num) from phpgw_p_activities where num like ('$prefix%')");
		$phpgw->db->next_record();
		$max = add_leading_zero(substr($phpgw->db->f(0),7));

		return $prefix . $max;
	}

	function create_invoiceid($year)
	{
		global $phpgw, $year;

		$prefix = 'I-' . $year . '-';
		$phpgw->db->query("select max(num) from phpgw_p_invoice where num like ('$prefix%')");
		$phpgw->db->next_record();
		$max = add_leading_zero(substr($phpgw->db->f(0),7));

		return $prefix . $max;
	}

	function create_deliveryid($year)
	{
		global $phpgw, $year;

		$prefix = 'D-' . $year . '-';
		$phpgw->db->query("select max(num) from phpgw_p_delivery where num like ('$prefix%')");
		$phpgw->db->next_record();
		$max = add_leading_zero(substr($phpgw->db->f(0),7));

		return $prefix . $max;
	}

	function format_tax($tax = '')
	{
		$comma = strrpos($tax,',');
		if (is_string($comma) && !$comma)
		{
			$newtax = $tax;
		}
		else
		{
			$newtax = str_replace(',','.',$tax);
		}
		return $newtax;
	}
?>
