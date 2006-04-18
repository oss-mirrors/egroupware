<?php
  /**************************************************************************\
  * eGroupWare - Trouble Ticket System                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
  $GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
  include('../header.inc.php');

  require_once (EGW_INCLUDE_ROOT.'/tts/inc/acl_funcs.inc.php');

  $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

  // select what tickets to view
  // We have to do this early on, as some links might need to forward these vars -- MSc
  $filter = get_var('filter',array('POST','GET'),'viewmyopen');
  $start  = (int) get_var('start',array('POST','GET'));
  $sort   = get_var('sort',array('POST','GET'),'ASC');
  $order  = get_var('order',array('POST','GET'),'ticket_priority');
  $searchfilter = reg_var('searchfilter','POST','any');

  $GLOBALS['phpgw']->template->set_file('index','index.tpl');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_title', 'tts_title');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_links', 'tts_links');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_search', 'tts_search');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_list', 'tts_list');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_row', 'tts_row');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_col_ifviewall', 'tts_col_ifviewall');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_head_ifviewall', 'tts_head_ifviewall');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');
  $GLOBALS['phpgw']->template->set_block('index','options_select');
  
  $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System'));
  $GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort)));
  $GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'));
  $GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
  $GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));
  $GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
  $GLOBALS['phpgw']->template->set_var('tts_head_status','');
  $GLOBALS['phpgw']->template->set_var('tts_notickets','');
  $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));


  $messages = rtrim($GLOBALS['phpgw']->session->appsession('messages','tts'),"\0");
  if($messages)
  {
    $GLOBALS['phpgw']->template->set_var('messages',$messages);
    $GLOBALS['phpgw']->session->appsession('messages','tts','');
  }

  // Append the filter to the search URL, so that the mode carries forward on a search
  $GLOBALS['phpgw']->template->set_var('tts_search_link',$GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort)));

  if ($filter == 'viewmyopen')
  {
    $filtermethod = "WHERE ticket_status='O' "
    		  . "AND ((ticket_assignedto='".$GLOBALS['phpgw_info']['user']['account_id']."' "
		  . "OR ticket_assignedto=0) "
		  . "OR  (ticket_owner='".$GLOBALS['phpgw_info']['user']['account_id']."')) ";
  }

  if ($filter == 'viewopen') 
  {
    $filtermethod = "WHERE ticket_status='O'";
    }
    if ($filter == 'viewopen' || $filter =='viewmyopen' )
    {
    if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','<META HTTP-EQUIV="Refresh" CONTENT="'.$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'].'; URL='.$GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort)).'">');
    }
    else
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','');
    }
  }

  // set for a possible search filter, outside of the all/open only "state" filter above
  if ($searchfilter) 
  {
    $s_quoted = $GLOBALS['phpgw']->db->quote('%'.$searchfilter.'%');
    $filtermethod = "WHERE ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted";
  }
  $GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));

  if (!preg_match('/^[a-z_]+$/i',$order) || !preg_match('/^(asc|desc)$/i',$sort))
  {
    $sortmethod = "ORDER BY ticket_priority ASC, CASE WHEN ticket_due IS NOT NULL THEN ticket_due ELSE '2100-01-01' END ASC, ticket_id ASC";
  }
  else
  {
    if ($order == 'ticket_priority' && $sort == 'ASC') {
	$sortmethod = "ORDER BY ticket_priority ASC, CASE WHEN ticket_due IS NOT NULL THEN ticket_due ELSE '2100-01-01' END ASC, ticket_id ASC";
    } elseif ($order == 'ticket_due') {
	$sortmethod = "ORDER BY CASE WHEN ticket_due IS NOT NULL THEN ticket_due ELSE '2100-01-01' END $sort, ticket_priority ASC, ticket_id ASC";
    } else {
	$sortmethod = "ORDER BY $order $sort, ticket_priority ASC, CASE WHEN ticket_due IS NOT NULL THEN ticket_due ELSE '2100-01-01' END ASC, ticket_id ASC";
    }
  }
  $db = clone($GLOBALS['phpgw']->db);
  $db2 = clone($GLOBALS['phpgw']->db);

  // we are _not_ limiting the number of results here, as we filter via ACL later on -- MSc
  $db->query("SELECT *, ticket_due FROM phpgw_tts_tickets $filtermethod $sortmethod",__LINE__,__FILE__);

  
    $tag = '';
    $GLOBALS['phpgw']->template->set_var('optionname', lang('View all tickets'));
    $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewall');
    if ($filter == 'viewall' ) {
	$tag = 'selected';
    }
    $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
    $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

    $tag = '';
    $GLOBALS['phpgw']->template->set_var('optionname', lang('View only open tickets'));
    $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewopen');
    if ($filter == 'viewopen' )
    {
    $tag = 'selected';
    }
    $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
    $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

    $tag = '';
    $GLOBALS['phpgw']->template->set_var('optionname', lang('View only my open tickets'));
    $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewmyopen');
    if ($filter == 'viewmyopen' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  $GLOBALS['phpgw']->template->set_var('tts_ticketsopen', lang('Tickets open %1',$numopen));
  
  // fill header
  $GLOBALS['phpgw']->nextmatchs->_filter = $filter;
  $GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('tts_head_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_id',$order,'/tts/index.php',lang('Ticket #')));
  $GLOBALS['phpgw']->template->set_var('tts_head_prio', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority',$order,'/tts/index.php',lang('Priority')));
  $GLOBALS['phpgw']->template->set_var('tts_head_group',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_group',$order,'/tts/index.php',lang('Group')));
  $GLOBALS['phpgw']->template->set_var('tts_head_category',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_category',$order,'/tts/index.php',lang('Category')));
  $GLOBALS['phpgw']->template->set_var('tts_head_assignedto', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_assignedto',$order,'/tts/index.php',lang('Assigned to')));
  $GLOBALS['phpgw']->template->set_var('tts_head_openedby', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_owner',$order,'/tts/index.php',lang('Opened by')));

# MSc: due date header
  $GLOBALS['phpgw']->template->set_var('tts_head_duedate', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_due',$order,'/tts/index.php',lang('Due Date')));

  // I am not sure how the sorting will work for this, if at all. (jengo)
  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened',lang('Date opened'));
//  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'',$order,'/tts/index.php',lang('Date opened')));
  if ($filter != 'viewopen' && $filter != 'viewmyopen')
  {
    $GLOBALS['phpgw']->template->set_var('tts_head_dateclosed', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_status',$order,'/tts/index.php',lang('Status/Date closed')));
    $GLOBALS['phpgw']->template->parse('tts_head_status','tts_head_ifviewall',false);
  }
  $GLOBALS['phpgw']->template->set_var('tts_head_subject', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_subject',$order,'/tts/index.php',lang('Subject')));
  $GLOBALS['phpgw']->template->set_var('tts_head_state', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_state',$order,'/tts/index.php',lang('State')));

  if ($db->num_rows() == 0)
  {
    $GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
  }
  else
  {
      $nrtickets = 0;	// Number of listed tickets 
      $maxtickets = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
      $moretickets = 0;	// will be set to 1 if there are unshown tickets left
      $total = 0;
      
    while ($db->next_record())
    {
	
	// If the user is not allow to READ the ticket, don't view it in this list
	if (! check_ticket_right($db->f('ticket_assignedto'), $db->f('ticket_owner'), $db->f('ticket_group'), PHPGW_ACL_READ)) {
	    continue;
	}

	$total++;

      // Have we reached maxtickets? if so, then exit loop
      if ($nrtickets > $start+$maxtickets-1) {
	  $moretickets = 1;
	  continue;	// we continue anyway, to get total nr of tickets
      }

      // ok, the ticket is viewable, so let's increase the count...
      $nrtickets++;
      // and now go to the next one if we haven't reached $start yet
      if ($nrtickets<=$start) continue;

	
      $GLOBALS['phpgw']->template->set_var('tts_col_status','');
      $priority = $db->f('ticket_priority');
      $GLOBALS['phpgw']->template->set_var('tts_t_prio',$priority);

      // We now try to find a good bg-color:	    -- MSc
      // If the due date is in the past, color it 'unas'
      // If the due date is in the past, color it 'due'
      // If the due date is in the future, color it according to Prio
      if ($db->f('ticket_assignedto') == 0) {	# unassigned ticket
	  $tr_color = $GLOBALS['phpgw_info']['theme']['unas'];
      } else {
	  $tdu = $db->f('ticket_due');
	  if ($tdu && $tdu > 0 && $tdu < time()) {  # it's DUE!
	      $tr_color = $GLOBALS['phpgw_info']['theme']['due'];
	  } else {
	      # as we are using prios from 1..5, let's multiply prio by 2
	      $tr_color = $GLOBALS['phpgw_info']['theme']['bg'.sprintf('%02s',(5-$priority)*2)];
	  }
      }

      // the following will not work here, let's do this later	-- MSc 050830
/*    if ($filter!="viewopen" && $db->f('t_timestamp_closed'))
      {
        $tr_color = $GLOBALS['phpgw_info']['theme']['th_bg'];
      }
*/

      $db2->query("select count(*) from phpgw_tts_views where view_id='" . $db->f('ticket_id')
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

      $GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php',array('ticket_id'=>$db->f('ticket_id'),'filter'=>$filter,'order'=>$order,'sort'=>$sort)));

      $GLOBALS['phpgw']->template->set_var('row_ticket_id', $db->f('ticket_id'));

      if (! $ticket_read)
      {
        $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/updated.png" />');
      }
      else
      {
        $GLOBALS['phpgw']->template->set_var('row_status','&nbsp;');
      }

      $cat_name   = $GLOBALS['phpgw']->categories->id2name($db->f('ticket_category'));
      $GLOBALS['phpgw']->template->set_var('row_category',$cat_name);

      $group_name = $GLOBALS['phpgw']->accounts->id2name($db->f('ticket_group'));
      $group_name = ($group_name ? $group_name : '--');
      $GLOBALS['phpgw']->template->set_var('row_group',$group_name);

      $GLOBALS['phpgw']->template->set_var('tts_t_assignedto', $db->f('ticket_assignedto')?$GLOBALS['phpgw']->accounts->id2name($db->f('ticket_assignedto')):lang('None'));
      $GLOBALS['phpgw']->template->set_var('tts_t_user',$GLOBALS['phpgw']->accounts->id2name($db->f('ticket_owner')));

      $history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('O'),'history_timestamp','ASC',$db->f('ticket_id'));
      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));

      # Check if the ticket is Closed
      if ($db->f('ticket_status') == 'X')
      {
	  $history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('X'),'history_timestamp','DESC',$db->f('ticket_id'));
	  $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
	  $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
	  $tr_color = $GLOBALS['phpgw_info']['theme']['th_bg'];
      }
      elseif ($filter != 'viewopen' && $filter != 'viewmyopen')
      {
//        if ($db->f('ticket_assignedto') != -1)
//        {
//          $assigned_to = lang('Not assigned');
//        }
//        else
//        {
//          $assigned_to = $GLOBALS['phpgw']->accounts->id2name($db->f('ticket_assignedto'));
//        }
//        $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$assigned_to);
	  $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',lang('Open'));
	  $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
      }
      
      # now set the bg-color
      $GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
      
      // cope with old, wrongly saved entries, stripslashes would remove single backslashes too
      $subject = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'),$db->f('ticket_subject'));
      if (strlen($subject) > 25) {
	  $subject = substr($subject,0,23) . '...';
      }
      $GLOBALS['phpgw']->template->set_var('tts_t_subject', $subject);
      $GLOBALS['phpgw']->template->set_var('tts_t_state',
        id2field('phpgw_tts_states','state_name','state_id',$db->f('ticket_state')));

# MSc: due date
      $GLOBALS['phpgw']->template->set_var('tts_t_duedate',
	      ($db->f('ticket_due') != '0000-00-00 00:00:00')?substr($db->f('ticket_due'), 0, 16):'');
      
      $GLOBALS['phpgw']->template->parse('rows','tts_row',True);

    }
    if ($nrtickets == 0)
    {
      $GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
    }
  }

  // we can only now, after going through the list, know the following numbers -- MSc
  $GLOBALS['phpgw']->template->set_var('tts_numfound',$GLOBALS['phpgw']->nextmatchs->show_hits($total,$start));
  $GLOBALS['phpgw']->template->set_var('left', $GLOBALS['phpgw']->nextmatchs->left('/tts/index.php',$start,$total));
  $GLOBALS['phpgw']->template->set_var('right',$GLOBALS['phpgw']->nextmatchs->right('/tts/index.php',$start,$total));

  // this is a workaround to clear the subblocks autogenerated vars
  $GLOBALS['phpgw']->template->set_var('tts_row','');
  $GLOBALS['phpgw']->template->set_var('tts_col_ifviewall','');
  $GLOBALS['phpgw']->template->set_var('tts_head_ifviewall','');
  $GLOBALS['phpgw']->template->set_var('tts_ticket_id_read','');
  $GLOBALS['phpgw']->template->set_var('tts_ticket_id_unread','');
  $GLOBALS['phpgw']->template->set_var('options_select','');

  $GLOBALS['phpgw']->template->pfp('out','index');

  $GLOBALS['phpgw']->common->phpgw_footer();
?>
