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
 
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	include('../header.inc.php');

	$GLOBALS['phpgw']->template->set_file(array(
		'index' => 'index.tpl'
	));

	$GLOBALS['phpgw']->template->set_unknowns('remove');

	$GLOBALS['phpgw']->template->set_block('index', 'tts_title', 'tts_title');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_links', 'tts_links');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_search', 'tts_search');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_list', 'tts_list');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_row', 'tts_row');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_col_ifviewall', 'tts_col_ifviewall');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_head_ifviewall', 'tts_head_ifviewall');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');

	$GLOBALS['phpgw']->template->set_var('tts_appname', lang('Trouble Ticket System'));
	$GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link("/tts/newticket.php"));
	$GLOBALS['phpgw']->template->set_var('tts_search_link', $GLOBALS['phpgw']->link("/tts/index.php"));
	$GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link("/tts/preferences.php"));
	$GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
	$GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));
	$GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
	$GLOBALS['phpgw']->template->set_var('tts_head_status','');
	$GLOBALS['phpgw']->template->set_var('tts_notickets','');

	// select what tickets to view
	$filter = $HTTP_GET_VARS['filter'];
	$start  = $HTTP_GET_VARS['start'];
	$sort   = $HTTP_GET_VARS['sort'];
	$order  = $HTTP_GET_VARS['order'];

	if (!$filter)
	{
		$filter='viewopen';
	}
	if ($filter == 'viewopen') 
	{
		$filtermethod = "where t_timestamp_closed='0'";

		$GLOBALS['phpgw']->preferences->read_repository();
		if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
		{
			$GLOBALS['phpgw']->template->set_var('autorefresh','<META HTTP-EQUIV="Refresh" CONTENT="'.$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'].'; URL='.$GLOBALS['phpgw']->link('/tts/index.php').'">');
		}
		else
		{
			$GLOBALS['phpgw']->template->set_var('autorefresh','');
		}
	}
	if ($filter == 'search') 
	{
		$filtermethod = "where t_detail like '%".addslashes($searchfilter)."%'";
		$GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));
	}

	if (!$sort)
	{
		$sortmethod = 'order by t_priority desc';
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	$GLOBALS['phpgw']->db->query("SELECT COUNT('t_id') FROM phpgw_tts_tickets");
	$GLOBALS['phpgw']->db->next_record();
	$numtotal = $GLOBALS['phpgw']->db->f('0') ;

	$GLOBALS['phpgw']->db->query("SELECT COUNT('t_id') FROM phpgw_tts_tickets where t_timestamp_closed='0'");
	$GLOBALS['phpgw']->db->next_record();
	$numopen = $GLOBALS['phpgw']->db->f('0') ;

	$GLOBALS['phpgw']->template->set_var('tts_numtotal',lang('Tickets total x',$numtotal));
	$GLOBALS['phpgw']->template->set_var('tts_numopen',lang('Tickets open x',$numopen));

	$GLOBALS['phpgw']->db->query("select t_id,t_category,t_priority,t_assignedto,t_timestamp_opened,t_user,t_timestamp_closed,t_subject,t_watchers "
		. "from phpgw_tts_tickets $filtermethod $sortmethod");
	$numfound = $GLOBALS['phpgw']->db->num_rows();

	if ($filter == 'search')
	{
		$filtermethod = "where t_detail like '%".addslashes($searchfilter)."%'";
		$GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));
		$GLOBALS['phpgw']->template->set_var('tts_numfound',lang('Tickets found x',$numfound));
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('tts_searchfilter','');
		$GLOBALS['phpgw']->template->set_var('tts_numfound','');
	}

	if ($filter != 'viewopen')
	{
		$GLOBALS['phpgw']->template->set_var('tts_changeview_link', $GLOBALS['phpgw']->link('/tts/index.php'));
		$GLOBALS['phpgw']->template->set_var('tts_changeview', lang('View only open tickets'));
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('tts_changeview_link', $GLOBALS['phpgw']->link('/tts/index.php','filter=viewall'));
		$GLOBALS['phpgw']->template->set_var('tts_changeview', lang('View all tickets'));
	}

	$GLOBALS['phpgw']->template->set_var('tts_ticketstotal', lang('Tickets total x',$numtotal));
	$GLOBALS['phpgw']->template->set_var('tts_ticketsopen', lang('Tickets open x',$numopen));
	
	// fill header
	$GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
	$GLOBALS['phpgw']->template->set_var('tts_head_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_id',$order,'/tts/index.php',lang('Ticket').' #'));
	$GLOBALS['phpgw']->template->set_var('tts_head_prio', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_priority',$order,'/tts/index.php',lang('Prio')));
	$GLOBALS['phpgw']->template->set_var('tts_head_group',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_category',$order,'/tts/index.php',lang('Group')) );
	$GLOBALS['phpgw']->template->set_var('tts_head_assignedto', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_assignedto',$order,'/tts/index.php',lang('Assigned to')));
	$GLOBALS['phpgw']->template->set_var('tts_head_openedby', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_user',$order,'/tts/index.php',lang('Opened by')));
	$GLOBALS['phpgw']->template->set_var('tts_head_dateopened', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_timestamp_opened',$order,'/tts/index.php',lang('Date opened')));
	if ($filter != 'viewopen')
	{
		$GLOBALS['phpgw']->template->set_var('tts_head_dateclosed', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_timestamp_closed',$order,'/tts/index.php',lang('Status/Date closed')));
		$GLOBALS['phpgw']->template->parse('tts_head_status','tts_head_ifviewall',false);
	}
	$GLOBALS['phpgw']->template->set_var('tts_head_subject', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_subject',$order,'/tts/index.php',lang('Subject')));

	if ($GLOBALS['phpgw']->db->num_rows() == 0)
	{
		$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
	}
	else
	{
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw']->template->set_var('tts_col_status','');
			$priority=$GLOBALS['phpgw']->db->f('t_priority');
			switch ($priority)
			{
				case 1:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg01']; break;
				case 2:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg02']; break;
				case 3:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg03']; break;
				case 4:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg04']; break;
				case 5:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg05']; break;
				case 6:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg06']; break;
				case 7:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg07']; break;
				case 8:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg08']; break;
				case 9:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg09']; break;
				case 10: $tr_color = $GLOBALS['phpgw_info']['theme']['bg10']; break;
				default: $tr_color = $GLOBALS['phpgw_info']['theme']['bg_color'];
			}

			if ($filter!="viewopen" && $GLOBALS['phpgw']->db->f('t_timestamp_closed'))
			{
				$tr_color = $GLOBALS['phpgw_info']['theme']['th_bg']; /*"#CCCCCC";*/
			}
			$t_watchers=explode(":",$GLOBALS['phpgw']->db->f('t_watchers'));
			if (in_array( $GLOBALS['phpgw_info']['user']['userid'], $t_watchers))
			{
				$t_read=1;
			}
			else
			{
				$t_read=0;
			}

			$GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
			$GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php','ticketid=' . $GLOBALS['phpgw']->db->f('t_id')));

			$GLOBALS['phpgw']->template->set_var('tts_t_id',$GLOBALS['phpgw']->db->f('t_id') );

			if (!$t_read==1)
			{
				$GLOBALS['phpgw']->template->parse('tts_ticket_id', 'tts_ticket_id_unread', false );
			}
			else
			{
				$GLOBALS['phpgw']->template->parse('tts_ticket_id', 'tts_ticket_id_read', false );
			}

			$priostr="";
			while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
			$GLOBALS['phpgw']->template->set_var('tts_t_priostr',$priostr );

			$catstr = $GLOBALS['phpgw']->db->f('t_category')?$GLOBALS['phpgw']->db->f('t_category'):lang('none');
			$GLOBALS['phpgw']->template->set_var('tts_t_catstr', $catstr );

			$GLOBALS['phpgw']->template->set_var('tts_t_assignedto', $GLOBALS['phpgw']->db->f('t_assignedto')!='none'?$GLOBALS['phpgw']->db->f('t_assignedto'):lang('none'));
			$GLOBALS['phpgw']->template->set_var('tts_t_user', $GLOBALS['phpgw']->db->f('t_user'));
			$GLOBALS['phpgw']->template->set_var('tts_t_timestampopened', $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_opened')));

			if ( $GLOBALS['phpgw']->db->f('t_timestamp_closed') > 0 )
			{
				$timestampclosed=$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed'));
				$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed', $timestampclosed);
				$GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',false);
			}
			elseif ($filter != 'viewopen')
			{
				if ( $GLOBALS['phpgw']->db->f('t_assignedto') == 'none' )
				{
					$timestampclosed = lang( 'not assigned' );
				}
				else
				{
					$timestampclosed = lang( 'in progress' );
				}
				$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed', $timestampclosed);
				$GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',false);
			}
			$GLOBALS['phpgw']->template->set_var('tts_t_subject', $GLOBALS['phpgw']->db->f('t_subject'));

			$GLOBALS['phpgw']->template->parse('rows','tts_row',true);
		}
	}

	// this is a workaround to clear the subblocks autogenerated vars
	$GLOBALS['phpgw']->template->set_var('tts_row','');
	$GLOBALS['phpgw']->template->set_var('tts_col_ifviewall','');
	$GLOBALS['phpgw']->template->set_var('tts_head_ifviewall','');
	$GLOBALS['phpgw']->template->set_var('tts_ticket_id_read','');
	$GLOBALS['phpgw']->template->set_var('tts_ticket_id_unread','');

	$GLOBALS['phpgw']->template->pfp('out','index');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
