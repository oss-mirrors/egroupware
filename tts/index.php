<?php
  /**************************************************************************\
  * phpGroupWare - Trouble Ticket System                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info["flags"]["enable_nextmatchs_class"] = True;
	$phpgw_info["flags"]["currentapp"] = "tts";
	include("../header.inc.php");


	$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	// echo PHPGW_APP_TPL;
	$p->set_file(array(
		'index'   => 'index.tpl',
		'title'	  => 'title.tpl',
		'links'	  => 'links.tpl',
		'list'	  => 'list.tpl',
		'row'	  => 'row.tpl',
		'col_ifviewall'  => 'col_ifviewall.tpl',
		'head_ifviewall'  => 'head_ifviewall.tpl',
		'ticket_id_unread'   => 't_id_unread.tpl',
		'ticket_id_read'   => 't_id_read.tpl'
    	));

	$p->set_var('tts_appname', lang("Trouble Ticket System"));
	$p->set_var('tts_newticket_link', $phpgw->link("/tts/newticket.php"));
	$p->set_var('tts_newticket', lang("New ticket"));
	$p->set_var('tts_head_ifviewall',"");
	$p->set_var('tts_notickets',"");

	// select what tickets to view
	if (!$filter) { $filter="viewopen"; }
	if ($filter == "viewopen") 
	{
		$filtermethod = "where t_timestamp_closed='0'";
	}
	if ($filter == "search") 
	{
//		$filtermethod = "where t_detail like '%".$searchfilter."%' or t_detail like '%".$searchfilter."%';
	}

	if (!$sort)
	{
		$sortmethod = "order by t_priority desc";
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	$phpgw->db->query("SELECT COUNT(*) FROM ticket");
	$numtotal = $phpgw->db->num_rows();

	$phpgw->db->query("SELECT t_id FROM ticket where t_timestamp_closed='0'");
	$numopen = $phpgw->db->num_rows();

	$p->set_var('tts_numtotal',lang("Tickets total x",$numtotal));
	$p->set_var('tts_numopen',lang("Tickets open x",$numopen));

	$phpgw->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject,t_watchers "
		. "from ticket $filtermethod $sortmethod");

	if ($filter == "viewall")
	{
	    $p->set_var('tts_changeview_link', $phpgw->link("/tts/index.php"));
	    $p->set_var('tts_changeview', lang("View only open tickets"));
	}
	else
	{
	    $p->set_var('tts_changeview_link', $phpgw->link("/tts/index.php","filter=viewall"));
	    $p->set_var('tts_changeview', lang("View all tickets"));
	}

	$p->set_var('tts_ticketstotal', lang("Tickets total x",$numtotal));
	$p->set_var('tts_ticketsopen', lang("Tickets open x",$numopen));
	
	
	if ($phpgw->db->num_rows() == 0)
	{
		echo "<p><center>".lang("No tickets found")."</center>";
		$phpgw->common->phpgw_exit(True);
	}


	// fill header
	$p->set_var('tts_head_bgcolor',$phpgw_info["theme"]["th_bg"] );
	$p->set_var('tts_head_ticket', $phpgw->nextmatchs->show_sort_order($sort,"t_id",$order,"/tts/index.php",lang("Ticket")." #"));
	$p->set_var('tts_head_prio', $phpgw->nextmatchs->show_sort_order($sort,"t_priority",$order,"/tts/index.php",lang("Prio")));
	$p->set_var('tts_head_group',$phpgw->nextmatchs->show_sort_order($sort,"t_category",$order,"/tts/index.php",lang("Group")) );
	$p->set_var('tts_head_assignedto', $phpgw->nextmatchs->show_sort_order($sort,"t_assignedto",$order,"/tts/index.php",lang("Assigned to")));
	$p->set_var('tts_head_openedby', $phpgw->nextmatchs->show_sort_order($sort,"t_user",$order,"/tts/index.php",lang("Opened by")));
	$p->set_var('tts_head_dateopened', $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_opened",$order,"/tts/index.php",lang("Date opened")));
        if ($filter == "viewall") {
    	  $p->set_var('tts_head_dateclosed', $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_closed",$order,"/tts/index.php",lang("Status/Date closed")));
	  $p->parse('tts_head_ifviewall','head_ifviewall',false);
	}
	$p->set_var('tts_head_subject', $phpgw->nextmatchs->show_sort_order($sort,"t_subject",$order,"/tts/index.php",lang("Subject")));

	while ($phpgw->db->next_record())
	{
		$p->set_var('tts_col_ifviewall',"");
		$priority=$phpgw->db->f("t_priority");
		switch ($priority)
		{
			case 1:  $tr_color = $phpgw_info["theme"]["bg01"]; break;
			case 2:  $tr_color = $phpgw_info["theme"]["bg02"]; break;
			case 3:  $tr_color = $phpgw_info["theme"]["bg03"]; break;
			case 4:  $tr_color = $phpgw_info["theme"]["bg04"]; break;
			case 5:  $tr_color = $phpgw_info["theme"]["bg05"]; break;
			case 6:  $tr_color = $phpgw_info["theme"]["bg06"]; break;
			case 7:  $tr_color = $phpgw_info["theme"]["bg07"]; break;
			case 8:  $tr_color = $phpgw_info["theme"]["bg08"]; break;
			case 9:  $tr_color = $phpgw_info["theme"]["bg09"]; break;
			case 10: $tr_color = $phpgw_info["theme"]["bg10"]; break;
			default: $tr_color = $phpgw_info["theme"]["bg_color"];
		}

		if ($filter=="viewall" && $phpgw->db->f("t_timestamp_closed")) { $tr_color = $phpgw_info["theme"]["th_bg"]; /*"#CCCCCC";*/}
		$t_watchers=explode(":",$phpgw->db->f("t_watchers"));
		if (in_array( $phpgw_info["user"]["userid"], $t_watchers)) {$t_read=1;} else { $t_read=0;}
				
		$p->set_var('tts_row_color', $tr_color );
		$p->set_var('tts_ticketdetails_link', $phpgw->link("/tts/viewticket_details.php","ticketid=" . $phpgw->db->f("t_id")));

		$p->set_var('tts_t_id',$phpgw->db->f("t_id") );

		if (!$t_read==1) { 
		    $p->parse('tts_ticket_id', 'ticket_id_unread', false );
		 } else {
		    $p->parse('tts_ticket_id', 'ticket_id_read', false );
		 }

		$priostr="";
		while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
		$p->set_var('tts_t_priostr',$priostr );

    		$catstr = $phpgw->db->f("t_category")?$phpgw->db->f("t_category"):"none";
		$p->set_var('tts_t_catstr', $catstr );

		$p->set_var('tts_t_assignedto', $phpgw->db->f("t_assignedto"));
		$p->set_var('tts_t_user', $phpgw->db->f("t_user"));
		$p->set_var('tts_t_timestampopened', $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")));

		if ( $phpgw->db->f("t_timestamp_closed") > 0 )
		{
			$timestampclosed=$phpgw->common->show_date($phpgw->db->f("t_timestamp_closed"));
		}
		elseif ($filter == "viewall")
		{
			if ( $phpgw->db->f("t_assignedto") == "none" )
			{
				$timestampclosed = lang( "not assigned" );
			}
			else
			{
				$timestampclosed = lang( "in progress" );
			}
			$p->set_var('tts_t_timestampclosed', $timestampclosed);
			$p->parse('col_ifviewall','col_ifviewall',false);
		}
		$p->set_var('tts_t_subject', $phpgw->db->f("t_subject"));

		$p->parse('rows','row',true);
		
	}

	$p->parse('title','title');
	$p->parse('links','links');
	$p->parse('list','list');
	$p->pparse('out','index');
//	$p->p('index');
	
	$phpgw->common->phpgw_footer();
?>
