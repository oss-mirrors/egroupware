<?php
  /**************************************************************************\                                            
  * phpGroupWare - projects                                                  *                                            
  * (http://www.phpgroupware.org)                                            *                                            
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         *                                            
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *                                            
  * --------------------------------------------------------                 *                                            
  *  This program is free software; you can redistribute it and/or modify it *                                            
  *  under the terms of the GNU General Public License as published by the   *                                            
  *  Free Software Foundation; either version 2 of the License, or (at your  *                                            
  *  option) any later version.                                              *                                            
  \**************************************************************************/
/* $Id$ */

    function read_projects( $start, $limit, $query = '', $filter = '', $sort = '', $order = '') {

    global $phpgw, $phpgw_info, $total_records;

    $db2 = $phpgw->db;

    if (!$account_id) { $account_id = $phpgw_info['user']['account_id']; }

    if (!$sort) { $sort = "ASC";  }

    if ($order)	{ $ordermethod = "order by $order $sort"; }
    else { $ordermethod = "order by start_date asc"; }

    if ($filter) {
    $filtermethod = " AND (coordinator='" . $account_id . "' OR owner='" . $account_id . "')";
    }

    if ($query) {
	$sql = "SELECT p.id,p.owner,p.num,p.entry_date,p.start_date,p.end_date,p.coordinator,p.customer,p.status, "
		. "p.descr,p.title,p.budget,a.account_lid,a.account_firstname,a.account_lastname FROM "
		. "phpgw_p_projects AS p,phpgw_accounts AS a WHERE a.account_id=p.coordinator $filtermethod AND "
		. "(title like '%$query%' OR descr like '%$query%') $ordermethod";
    }
    else {
	$sql = "SELECT p.id,p.owner,p.num,p.entry_date,p.start_date,p.end_date,p.coordinator,p.customer,p.status, "
			    . "p.descr,p.title,p.budget,a.account_lid,a.account_firstname,a.account_lastname FROM "
			    . "phpgw_p_projects AS p,phpgw_accounts AS a WHERE a.account_id=p.coordinator $filtermethod "
			    . "$ordermethod";
    }

    $db2->query($sql,__LINE__,__FILE__);
    $total_records = $db2->num_rows();
    $phpgw->db->query($sql. " " . $phpgw->db->limit($start,$limit),__LINE__,__FILE__);

    $i = 0;
    while ($phpgw->db->next_record()) {	
	    $projects[$i]['id'] 	 = $phpgw->db->f('id');
	    $projects[$i]['owner'] 	 = $phpgw->db->f('owner');
	    $projects[$i]['number'] 	 = $phpgw->db->f('num');
	    $projects[$i]['entry_date']  = $phpgw->db->f('entry_date');
	    $projects[$i]['start_date']  = $phpgw->db->f('start_date');
	    $projects[$i]['end_date'] 	 = $phpgw->db->f('end_date');
	    $projects[$i]['coordinator'] = $phpgw->db->f('coordinator');
	    $projects[$i]['customer']	 = $phpgw->db->f('customer');
	    $projects[$i]['status'] 	 = $phpgw->db->f('status');
	    $projects[$i]['description'] = $phpgw->db->f('descr');
	    $projects[$i]['title'] 	 = $phpgw->db->f('title');
	    $projects[$i]['budget'] 	 = $phpgw->db->f('budget');
	    $projects[$i]['lid'] 	 = $phpgw->db->f('account_lid');
	    $projects[$i]['firstname'] 	 = $phpgw->db->f('account_firstname');
	    $projects[$i]['lastname'] 	 = $phpgw->db->f('account_lastname');
	    $i++;
	}
    return $projects;
    }


// returns project-,invoice- and delivery-ID

    $id_type = "hex";
    
    function add_leading_zero($num)  {                                                                      
    global $id_type;                                             
                                                                         
    if ($id_type == "hex") {                                     
        $num = hexdec($num);                                             
        $num++;                                                          
        $num = dechex($num);                                             
    } else {                                                             
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