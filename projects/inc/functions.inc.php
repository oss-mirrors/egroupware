<?php
  /**************************************************************************\                                            
  * phpGroupWare - projects                                                  *                                            
  * (http://www.phpgroupware.org)                                            *                                            
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         *                                            
  * --------------------------------------------------------                 *                                            
  *  This program is free software; you can redistribute it and/or modify it *                                            
  *  under the terms of the GNU General Public License as published by the   *                                            
  *  Free Software Foundation; either version 2 of the License, or (at your  *                                            
  *  option) any later version.                                              *                                            
  \**************************************************************************/
  /* $Id$ */

// returns project-,invoice- and delivery-ID

    $id_type = "hex";

    function add_leading_zero($num)  {
	global $id_type;

	if ($id_type == "hex") {
    	    $num = hexdec($num);
    	    $num++;
    	    $num = dechex($num);
	}
	else { $num++; }
                                                                         
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

    $year = $phpgw->common->show_date(time(),"Y");

    function create_projectid($year) {
	global $phpgw, $year;

	$prefix = "P-$year-";
	$phpgw->db->query("select max(num) from phpgw_p_projects where num like ('$prefix%')");
	$phpgw->db->next_record();
	$max = add_leading_zero(substr($phpgw->db->f(0),7));

	return $prefix.$max;
    }

    function create_activityid($year) {
	global $phpgw, $year;

	$prefix = "A-$year-";
	$phpgw->db->query("select max(num) from phpgw_p_activities where num like ('$prefix%')");
	$phpgw->db->next_record();
	$max = add_leading_zero(substr($phpgw->db->f(0),7));

	return $prefix.$max;
    }

    function create_invoiceid($year)  {
	global $phpgw, $year;

	$prefix = "I-$year-";
	$phpgw->db->query("select max(num) from phpgw_p_invoice where num like ('$prefix%')");
	$phpgw->db->next_record();
	$max = add_leading_zero(substr($phpgw->db->f(0),7));

	return $prefix.$max;
    }

    function create_deliveryid($year)  {
	global $phpgw, $year;

	$prefix = "D-$year-";
	$phpgw->db->query("select max(num) from phpgw_p_delivery where num like ('$prefix%')");
	$phpgw->db->next_record();
	$max = add_leading_zero(substr($phpgw->db->f(0),7));

	return $prefix.$max;
    }
?>