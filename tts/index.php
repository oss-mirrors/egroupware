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

 
	$phpgw_info["flags"]["currentapp"] = "tts";
	$phpgw_info["flags"]["enable_contacts_class"] = True;
	$phpgw_info["flags"]["enable_nextmatchs_class"] = True;
	include("../header.inc.php");


	$p = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	// echo PHPGW_APP_TPL;
	$p->set_file(array(
		'index'   => 'index.tpl'
    	));

	$p->set_unknowns('remove');
	
	$p->set_block('index',	'tts_title',  'tts_title');
	$p->set_block('index', 'tts_links', 'tts_links');
	$p->set_block('index', 'tts_search', 'tts_search');
	$p->set_block('index', 'tts_list', 'tts_list');
	$p->set_block('index', 'tts_row', 'tts_row');
	$p->set_block('index', 'tts_col_ifviewall', 'tts_col_ifviewall');
	$p->set_block('index', 'tts_head_ifviewall', 'tts_head_ifviewall');
	$p->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
	$p->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');

	$p->set_var('tts_appname', lang("Trouble Ticket System"));
	$p->set_var('tts_newticket_link', $phpgw->link("/tts/newticket.php"));
	$p->set_var('tts_search_link', $phpgw->link("/tts/index.php"));
	$p->set_var('tts_prefs_link', $phpgw->link("/tts/preferences.php"));
	$p->set_var('lang_preferences', lang('Preferences'));
	$p->set_var('lang_search', lang('search'));
	$p->set_var('tts_newticket', lang("New ticket"));
	$p->set_var('tts_head_status',"");
	$p->set_var('tts_notickets',"");

	// select what tickets to view
	if (!$filter) { $filter="viewopen"; }
	if ($filter == "viewopen") 
	{
		$filtermethod = "where t_timestamp_closed='0'";

		$phpgw->preferences->read_repository();
		if ($phpgw_info['user']['preferences']['tts']['refreshinterval']) {
			$p->set_var(autorefresh,'<META HTTP-EQUIV="Refresh" CONTENT="'.$phpgw_info['user']['preferences']['tts']['refreshinterval'].'; URL='.$phpgw->link("/tts/index.php").'">');
	    	} else {
			$p->set_var(autorefresh,"");
		}
	}
	if ($filter == "search") 
	{
		$filtermethod = "where t_detail like '%".addslashes($searchfilter)."%'";
		$p->set_var('tts_searchfilter',addslashes($searchfilter));
	}

	if (!$sort)
	{
		$sortmethod = "order by t_priority desc";
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	$phpgw->db->query("SELECT COUNT('t_id') FROM ticket");
	$phpgw->db->next_record();
	$numtotal = $phpgw->db->f('0') ;

	$phpgw->db->query("SELECT COUNT('t_id') FROM ticket where t_timestamp_closed='0'");
	$phpgw->db->next_record();
	$numopen = $phpgw->db->f('0') ;

	$p->set_var('tts_numtotal',lang("Tickets total x",$numtotal));
	$p->set_var('tts_numopen',lang("Tickets open x",$numopen));

	$phpgw->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject,t_watchers "
		. "from ticket $filtermethod $sortmethod");
	$numfound = $phpgw->db->num_rows();

	if ($filter == "search") 
	{
		$filtermethod = "where t_detail like '%".addslashes($searchfilter)."%'";
		$p->set_var('tts_searchfilter',addslashes($searchfilter));
		$p->set_var('tts_numfound',lang("Tickets found x",$numfound));
	} else {
		$p->set_var('tts_searchfilter',"");
		$p->set_var('tts_numfound',"");
	}

	if ($filter != "viewopen")
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
	
    	// fill header
	$p->set_var('tts_head_bgcolor',$phpgw_info["theme"]["th_bg"] );
	$p->set_var('tts_head_ticket', $phpgw->nextmatchs->show_sort_order($sort,"t_id",$order,"/tts/index.php",lang("Ticket")." #"));
	$p->set_var('tts_head_prio', $phpgw->nextmatchs->show_sort_order($sort,"t_priority",$order,"/tts/index.php",lang("Prio")));
	$p->set_var('tts_head_group',$phpgw->nextmatchs->show_sort_order($sort,"t_category",$order,"/tts/index.php",lang("Group")) );
	$p->set_var('tts_head_assignedto', $phpgw->nextmatchs->show_sort_order($sort,"t_assignedto",$order,"/tts/index.php",lang("Assigned to")));
	$p->set_var('tts_head_openedby', $phpgw->nextmatchs->show_sort_order($sort,"t_user",$order,"/tts/index.php",lang("Opened by")));
	$p->set_var('tts_head_dateopened', $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_opened",$order,"/tts/index.php",lang("Date opened")));
        if ($filter != "viewopen") {
    	  $p->set_var('tts_head_dateclosed', $phpgw->nextmatchs->show_sort_order($sort,"t_timestamp_closed",$order,"/tts/index.php",lang("Status/Date closed")));
	  $p->parse('tts_head_status','tts_head_ifviewall',false);
	}
	$p->set_var('tts_head_subject', $phpgw->nextmatchs->show_sort_order($sort,"t_subject",$order,"/tts/index.php",lang("Subject")));
	
	if ($phpgw->db->num_rows() == 0)
	{
    	  $p->set_var('rows', "<p><center>".lang("No tickets found")."</center>");
	} else {

	while ($phpgw->db->next_record())
	{
		$p->set_var('tts_col_status',"");
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

		if ($filter!="viewopen" && $phpgw->db->f("t_timestamp_closed")) { $tr_color = $phpgw_info["theme"]["th_bg"]; /*"#CCCCCC";*/}
		$t_watchers=explode(":",$phpgw->db->f("t_watchers"));
		if (in_array( $phpgw_info["user"]["userid"], $t_watchers)) {$t_read=1;} else { $t_read=0;}
				
		$p->set_var('tts_row_color', $tr_color );
		$p->set_var('tts_ticketdetails_link', $phpgw->link("/tts/viewticket_details.php","ticketid=" . $phpgw->db->f("t_id")));

		$p->set_var('tts_t_id',$phpgw->db->f("t_id") );

		if (!$t_read==1) { 
		    $p->parse('tts_ticket_id', 'tts_ticket_id_unread', false );
		 } else {
		    $p->parse('tts_ticket_id', 'tts_ticket_id_read', false );
		 }

		$priostr="";
		while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
		$p->set_var('tts_t_priostr',$priostr );

    		$catstr = $phpgw->db->f("t_category")?$phpgw->db->f("t_category"):lang("none");
		$p->set_var('tts_t_catstr', $catstr );

		$p->set_var('tts_t_assignedto', $phpgw->db->f("t_assignedto")!="none"?$phpgw->db->f("t_assignedto"):lang("none"));
		$p->set_var('tts_t_user', $phpgw->db->f("t_user"));
		$p->set_var('tts_t_timestampopened', $phpgw->common->show_date($phpgw->db->f("t_timestamp_opened")));

		if ( $phpgw->db->f("t_timestamp_closed") > 0 )
		{
			$timestampclosed=$phpgw->common->show_date($phpgw->db->f("t_timestamp_closed"));
			$p->set_var('tts_t_timestampclosed', $timestampclosed);
			$p->parse('tts_col_status','tts_col_ifviewall',false);
		}
		elseif ($filter != "viewopen")
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
			$p->parse('tts_col_status','tts_col_ifviewall',false);
		}
		$p->set_var('tts_t_subject', $phpgw->db->f("t_subject"));

		$p->parse('rows','tts_row',true);
		
	  }
	}

	// this is a workaround to clear the subblocks autogenerated vars  
	$p->set_var('tts_row',"");
	$p->set_var('tts_col_ifviewall',"");
	$p->set_var('tts_head_ifviewall',"");
	$p->set_var('tts_ticket_id_read',"");
	$p->set_var('tts_ticket_id_unread',"");
	
	$p->pfp('out','index');
	
	$phpgw->common->phpgw_footer();
?>
