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

  /* Note to self:
  ** Self ... heres the query to use when limiting access to entrys within a group
  ** The acl class *might* handle this instead .... not sure
  ** select distinct group_ticket_id, phpgw_tts_groups.group_ticket_id, phpgw_tts_tickets.*
  ** from phpgw_tts_tickets, phpgw_tts_groups where ticket_id = group_ticket_id and group_id in (14,15);
  */

  /* ACL levels
  ** 1 - Read ticket within your group only
  ** 2 - Close ticket
  ** 4 - Allow to make changes to priority, billing hours, billing rate, category, and assigned to
  */

  $GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
  $GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
  include('../header.inc.php');

//  $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

  $GLOBALS['phpgw']->template->set_file('index','wnt_index.tpl');
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

  $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System'). ' - ' .lang('WWW request'));
  $GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'));
  $GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
  $GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));
  $GLOBALS['phpgw']->template->set_var('tts_head_status','');
  $GLOBALS['phpgw']->template->set_var('tts_notickets','');
  $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));


  //add by Josip
  $can_add=False;
  $can_mon=False;
  $can_vip=False;
  $can_view_all=False;

  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
  // if user is admin, or VIP user, or HD_OPER user then ...
  if (($def_group == '16') || ($def_group == '6') || $GLOBALS['phpgw']->acl->check('add',1,'tts'))
  {
           $can_view_all=True;
  }
  if ($GLOBALS['phpgw']->acl->check('mon',1,'tts'))
  {
           $can_mon=True;
           $can_view_all=True;
  }
  if ($GLOBALS['phpgw']->acl->check('vip',1,'tts'))
  {
           $can_vip=True;
           $can_view_all=True;
  }
  if ($GLOBALS['phpgw']->acl->check('add',1,'tts'))
  {
           $can_add=True;
  }

    //add by Josip
  if (!$can_add)
  {
    $GLOBALS['phpgw']->redirect_link('/tts/index.php');
  }
  ////

  // if user can add new ticket
/*  if ($can_add)
  {
    $GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
    $GLOBALS['phpgw']->template->set_var('tts_newticket_delimiter', "&nbsp;|&nbsp;");
    $GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
  }
*/
  ////

  // get group membership for filters
/*  $group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

  $group_ids == null;

  foreach($group_list as $rowg)
  {
        if ($group_ids == null)
        {
                    $group_ids = "'".$rowg['account_id']."'";
        }
        else
        {
                $group_ids = $group_ids.",'".$rowg['account_id']."'";
        }
  }
  ////

  */
  $messages = rtrim($GLOBALS['phpgw']->session->appsession('messages','tts'),"\0");
  if($messages)
  {
    $GLOBALS['phpgw']->template->set_var('messages',$messages);
    $GLOBALS['phpgw']->session->appsession('messages','tts','');
  }



  $filter = get_var('filter',array('POST','GET'),'view');
  $order  = get_var('order',array('POST','GET'),'ticket_id');



  $f_status  = get_var('f_status',array('POST','GET'),'O');
  $start  = (int) get_var('start',array('POST','GET'));
  $sort   = get_var('sort',array('POST','GET'),'DESC');
  $searchfilter = reg_var('searchfilter','POST','any');

  // Append the filter to the search URL, so that the mode carries forward on a search
  $GLOBALS['phpgw']->template->set_var('tts_search_link',$GLOBALS['phpgw']->link('/tts/wnt_index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));


  if ($filter == 'view')
  {
    $filtermethod = "";
  }


  if ($f_status == "O")
  {
    if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','<META HTTP-EQUIV="Refresh" CONTENT="'.$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'].'; URL='.$GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)).'">');
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
    //$filtermethod = "WHERE (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted)";
    $filtermethod = "WHERE (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted OR ticket_caller_name LIKE $s_quoted OR ticket_caller_email LIKE $s_quoted OR ticket_caller_address LIKE $s_quoted OR ticket_caller_address_2 LIKE $s_quoted OR ticket_caller_telephone LIKE $s_quoted)";
  }

  $GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));


  $search_status = "";

  if ($f_status == "O")
  {
           $search_status = "ticket_status='O'";
  }
  elseif ($f_status == "X")
  {
        $search_status = "ticket_status='X'";
  }

  if ($search_status <> "")
  {
        if ($filtermethod == "")
        {
           $filtermethod = "WHERE $search_status";
        }
        else
        {
           $filtermethod = "$filtermethod AND $search_status";
        }
  }

  if (!preg_match('/^[a-z_]+$/i',$order) || !preg_match('/^(asc|desc)$/i',$sort))
  {
      $sortmethod = 'ORDER BY ticket_id DESC';
  }
  else
  {
    $sortmethod = "ORDER BY $order $sort";
  }
  $db2 = $db = $GLOBALS['phpgw']->db;
  $db->query($sql="SELECT count(*) FROM phpgw_tts_tickets_wnt $filtermethod",__LINE__,__FILE__);
  $total = $db->next_record() ? $db->f(0) : 0;

  $db->limit_query("SELECT * FROM phpgw_tts_tickets_wnt $filtermethod $sortmethod",$start,__LINE__,__FILE__);


  $GLOBALS['phpgw']->template->set_var('tts_numfound',$GLOBALS['phpgw']->nextmatchs->show_hits($total,$start));
  $GLOBALS['phpgw']->template->set_var('left',$GLOBALS['phpgw']->nextmatchs->left('/tts/wnt_index.php',$start,$total,'f_status='.$f_status));
//                               array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
  $GLOBALS['phpgw']->template->set_var('right',$GLOBALS['phpgw']->nextmatchs->right('/tts/wnt_index.php',$start,$total,'f_status='.$f_status));

  //add filter status into variable filter


  $tag = '';
    $GLOBALS['phpgw']->template->set_var('optionname', lang('View all tickets'));
    $GLOBALS['phpgw']->template->set_var('optionvalue', 'view');
    if ($filter == 'view' || substr($filter,1) == 'view' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);


  $ticket_status[$f_status] = ' selected';

  $s = '<option value="O"' . $ticket_status['O'] . '>' . lang('Status'). ' ' . lang('Open') . '</option>';
  $s .= '<option value="X"' . $ticket_status['X'] . '>' . lang('Status'). ' ' . lang('Closed') . '</option>';
  $s .= '<option value="A"' . $ticket_status['A'] . '>' . lang('Any status') . '</option>';

  $GLOBALS['phpgw']->template->set_var('options_f_status',$s);
  //$GLOBALS['phpgw']->template->set_var('lang_status',lang('Open / Closed'));


  $GLOBALS['phpgw']->template->set_var('tts_ticketstotal', lang('Tickets total %1',$numtotal));
  $GLOBALS['phpgw']->template->set_var('tts_ticketsopen', lang('Tickets open %1',$numopen));

  // fill header
  $GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('tts_head_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_id',$order,'/tts/wnt_index.php',lang('Ticket').' #'));

  $GLOBALS['phpgw']->template->set_var('tts_head_subject', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_subject',$order,'/tts/wnt_index.php',lang('Subject')));

  /*
  $GLOBALS['phpgw']->template->set_var('tts_head_prio', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority',$order,'/tts/index.php',lang('Priority')));
  $GLOBALS['phpgw']->template->set_var('tts_head_group',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_group',$order,'/tts/index.php',lang('Group')));
  $GLOBALS['phpgw']->template->set_var('tts_head_category',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_category',$order,'/tts/index.php',lang('Category')));
  $GLOBALS['phpgw']->template->set_var('tts_head_assignedto', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_assignedto',$order,'/tts/index.php',lang('Assigned to')));
  $GLOBALS['phpgw']->template->set_var('tts_head_openedby', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_owner',$order,'/tts/index.php',lang('Opened by')));
*/
  $GLOBALS['phpgw']->template->set_var('tts_head_caller_name', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_caller_name',$order,'/tts/wnt_index.php',lang('Caller Name')));
  $GLOBALS['phpgw']->template->set_var('tts_head_caller_telephone', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_caller_telephone',$order,'/tts/wnt_index.php',lang('Caller Telephone')));

  // I am not sure how the sorting will work for this, if at all. (jengo)
  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened',lang('Date opened'));
//  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'',$order,'/tts/index.php',lang('Date opened')));
//  if ($filter != 'viewopen')
  if ($f_status != 'O')
  {
      $status_label = lang('Status/Date closed');
      if ($f_status == 'X')
    {
        $status_label = lang('Date closed');
    }

    $GLOBALS['phpgw']->template->set_var('tts_head_dateclosed', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_status',$order,'/tts/wnt_index.php',$status_label));
    $GLOBALS['phpgw']->template->parse('tts_head_status','tts_head_ifviewall',false);
  }
  //$GLOBALS['phpgw']->template->set_var('tts_head_state', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_state',$order,'/tts/index.php',lang('State')));

  if ($db->num_rows() == 0)
  {
    $GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
  }
  else
  {
    while ($db->next_record())
    {
      $GLOBALS['phpgw']->template->set_var('tts_col_status','');

//      $tr_color = $GLOBALS['phpgw_info']['theme']['bg_color'];
      $tr_color = $GLOBALS['phpgw_info']['theme']['bg01'];
/*
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
*/

      if ($filter!="viewopen" && $db->f('t_timestamp_closed'))
      {
        $tr_color = $GLOBALS['phpgw_info']['theme']['th_bg']; /*"#CCCCCC";*/
      }

      $db2->query("select count(*) from phpgw_tts_views_wnt where view_id='" . $db->f('ticket_id')
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
      $GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/wnt_viewticket_details.php',array('ticket_id'=>$db->f('ticket_id'),'filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));

      $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/wnt_viewticket_details.php',array('ticket_id'=>$db->f('ticket_id'),'filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)). '">';
      $GLOBALS['phpgw']->template->set_var('row_ticket_id',$view_link . $db->f('ticket_id') . '</a>');

      if (! $ticket_read)
      {
        $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/updated.gif">');
      }
      else
      {
        $GLOBALS['phpgw']->template->set_var('row_status','&nbsp;');
      }


//      $history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('O'),'history_timestamp','ASC',$db->f('ticket_id'));
//      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));

      if ($db->f('ticket_status') == 'X')
      {
        //$history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('X'),'history_timestamp','ASC',$db->f('ticket_id'));
        $finish_date = $db->f('finish_date');
        //$GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
        $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$finish_date);
        $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
      }
//      elseif ($filter != 'viewopen')
      elseif ($f_status != 'O')
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
      // cope with old, wrongly saved entries, stripslashes would remove single backslashes too
      $subject = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'),$db->f('ticket_subject'));
      $GLOBALS['phpgw']->template->set_var('tts_t_subject', $view_link.$subject.'</a>');

//      $test = int($db->f('creation_date'));
//      $test2 = date("Y.m.d G:i:s",$db->f('creation_date'));

      $GLOBALS['phpgw']->template->set_var('tts_t_caller_name', $db->f('ticket_caller_name') );
      $GLOBALS['phpgw']->template->set_var('tts_t_caller_telephone', $db->f('ticket_caller_telephone') );
//      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date(($db->f('creation_date')) - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
//      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date($this->db->to_timestamp(time()) - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));

      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened', $db->f('creation_date'));

/*        <td align="center">{tts_t_caller_name}</td>
        <td align="center">{tts_t_caller_telephone}</td>
        <td align="center">{tts_t_timestampopened}</td>
   */
      $GLOBALS['phpgw']->template->parse('rows','tts_row',True);
    }
  }

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
