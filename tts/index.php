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

	/* Note to self:
	** Self ... heres the query to use when limiting access to entrys within a group
	** The acl class *might* handle this instead .... not sure
	** select distinct group_ticket_id, phpgw_tts_groups.group_ticket_id, phpgw_tts_tickets.*
	** from phpgw_tts_tickets, phpgw_tts_groups where ticket_id = group_ticket_id and group_id in (14,15);
	*/
 
	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	include('../header.inc.php');

	$GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

	$GLOBALS['phpgw']->template->set_file('index','index.tpl');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_title', 'tts_title');
//	$GLOBALS['phpgw']->template->set_block('index', 'tts_links', 'tts_links');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_search', 'tts_search');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_list', 'tts_list');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_row', 'tts_row');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_col_ifviewall', 'tts_col_ifviewall');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_head_ifviewall', 'tts_head_ifviewall');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
	$GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');

	$GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System'));
	$GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php'));
	$GLOBALS['phpgw']->template->set_var('tts_search_link', $GLOBALS['phpgw']->link('/tts/index.php'));
	$GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'));
	$GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
	$GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));
	$GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
	$GLOBALS['phpgw']->template->set_var('tts_head_status','');
	$GLOBALS['phpgw']->template->set_var('tts_notickets','');
	$GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));

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
		$filtermethod = "where ticket_status='O'";

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
		$filtermethod = "where ticket_details like '%".addslashes($searchfilter)."%'";
		$GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));
	}

	if (!$sort)
	{
		$sortmethod = 'order by ticket_priority desc';
	}
	else
	{
		$sortmethod = "order by $order $sort";
	}

	$GLOBALS['phpgw']->db->query("select count(*) from phpgw_tts_tickets",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$numtotal = $GLOBALS['phpgw']->db->f('0') ;

	$GLOBALS['phpgw']->db->query("select count(*) from phpgw_tts_tickets where ticket_status='O'",__LINE__,__FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$numopen = $GLOBALS['phpgw']->db->f('0') ;

	$GLOBALS['phpgw']->template->set_var('tts_numtotal',lang('Tickets total x',$numtotal));
	$GLOBALS['phpgw']->template->set_var('tts_numopen',lang('Tickets open x',$numopen));


	$db2 = $phpgw->db;
	$GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets $filtermethod $sortmethod",__LINE__,__FILE__);
	$numfound = $GLOBALS['phpgw']->db->num_rows();

	if ($filter == 'search')
	{
		$filtermethod = "where ticket_details like '%".addslashes($searchfilter)."%'";
		$GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));
		$GLOBALS['phpgw']->template->set_var('tts_numfound',lang('Tickets found %1',$numfound));
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
	$GLOBALS['phpgw']->template->set_var('tts_head_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_id',$order,'/tts/index.php',lang('Ticket').' #'));
	$GLOBALS['phpgw']->template->set_var('tts_head_prio', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority',$order,'/tts/index.php',lang('Prio')));
	$GLOBALS['phpgw']->template->set_var('tts_head_group',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_category',$order,'/tts/index.php',lang('Group')) );
	$GLOBALS['phpgw']->template->set_var('tts_head_assignedto', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_assignedto',$order,'/tts/index.php',lang('Assigned to')));
	$GLOBALS['phpgw']->template->set_var('tts_head_openedby', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_owner',$order,'/tts/index.php',lang('Opened by')));

	// I am not sure how the sorting will work for this, if at all. (jengo)
	$GLOBALS['phpgw']->template->set_var('tts_head_dateopened',lang('Date opened'));
//	$GLOBALS['phpgw']->template->set_var('tts_head_dateopened', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'',$order,'/tts/index.php',lang('Date opened')));
	if ($filter != 'viewopen')
	{
		$GLOBALS['phpgw']->template->set_var('tts_head_dateclosed', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'t_timestamp_closed',$order,'/tts/index.php',lang('Status/Date closed')));
		$GLOBALS['phpgw']->template->parse('tts_head_status','tts_head_ifviewall',false);
	}
	$GLOBALS['phpgw']->template->set_var('tts_head_subject', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_subject',$order,'/tts/index.php',lang('Subject')));

	if ($GLOBALS['phpgw']->db->num_rows() == 0)
	{
		$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
	}
	else
	{
		while ($GLOBALS['phpgw']->db->next_record())
		{
			$GLOBALS['phpgw']->template->set_var('tts_col_status','');
			$priority = $GLOBALS['phpgw']->db->f('ticket_priority');
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

			$db2->query("select count(*) from phpgw_tts_views where view_id='" . $GLOBALS['phpgw']->db->f('ticket_id')
				. "' and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
			$db2->next_record();

			if ($db2->f(0))
			{
				$ticket_read = True;
			}
			else
			{
				$ticket_read = False;
			}

			$GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
			$GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php','ticket_id=' . $GLOBALS['phpgw']->db->f('ticket_id')));

			$GLOBALS['phpgw']->template->set_var('tts_t_id',$GLOBALS['phpgw']->db->f('ticket_id') );

			if (! $ticket_read)
			{
				$GLOBALS['phpgw']->template->parse('tts_ticket_id','tts_ticket_id_unread');
			}
			else
			{
				$GLOBALS['phpgw']->template->parse('tts_ticket_id','tts_ticket_id_read');
			}

			$priostr = '';
			while ($priority > 0) { $priostr=$priostr . "||"; $priority--; }
			$GLOBALS['phpgw']->template->set_var('tts_t_priostr',$priostr );

			$cat_name = $phpgw->categories->id2name($GLOBALS['phpgw']->db->f('ticket_category'));
			$GLOBALS['phpgw']->template->set_var('row_category',$cat_name);

			$GLOBALS['phpgw']->template->set_var('tts_t_assignedto', $GLOBALS['phpgw']->db->f('ticket_assignedto')?$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_assignedto')):lang('None'));
			$GLOBALS['phpgw']->template->set_var('tts_t_user',$GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner')));

			$history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('O'),'history_timestamp','ASC',$GLOBALS['phpgw']->db->f('ticket_id'));
			$GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime']));

			if ($GLOBALS['phpgw']->db->f('ticket_status') == 'X')
			{
				$history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('X'),'history_timestamp','DESC',$GLOBALS['phpgw']->db->f('ticket_id'));
				$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime']));
				$GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
			}
			elseif ($filter != 'viewopen')
			{
//				if ($GLOBALS['phpgw']->db->f('ticket_assignedto') != -1)
//				{
//					$assigned_to = lang('Not assigned');
//				}
//				else
//				{
//					$assigned_to = $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_assignedto'));
//				}
//				$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$assigned_to);
				$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed','Open');
				$GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
			}
			$GLOBALS['phpgw']->template->set_var('tts_t_subject', $GLOBALS['phpgw']->db->f('ticket_subject'));

			$GLOBALS['phpgw']->template->parse('rows','tts_row',True);
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
